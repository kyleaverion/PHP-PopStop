<?php
require_once 'config/database.php';
require_once 'config/functions.php';

startSession();

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$conn = getConnection();
$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

$photos_stmt = $conn->prepare("SELECT * FROM product_photos WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
$photos_stmt->bind_param("i", $product_id);
$photos_stmt->execute();
$product_photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviews_stmt = $conn->prepare("
    SELECT r.*, u.username, u.full_name, u.profile_photo
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total_rating / count($reviews), 1);
}

$can_review = false;
$user_review = null;
if (isLoggedIn()) {
    $can_review = canReviewProduct($conn, $_SESSION['user_id'], $product_id);
    
    $existing_review = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? AND product_id = ?");
    $existing_review->bind_param("ii", $_SESSION['user_id'], $product_id);
    $existing_review->execute();
    $user_review = $existing_review->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setAlert('error', 'Please login to submit a review');
        header("Location: login.php");
        exit();
    }
    
    $rating = intval($_POST['rating']);
    $review_text = $_POST['review_text'];
    $user_id = $_SESSION['user_id'];
    
    if ($rating < 1 || $rating > 5) {
        setAlert('error', 'Invalid rating value');
    } elseif (!canReviewProduct($conn, $user_id, $product_id)) {
        setAlert('error', 'You can only review products you have purchased and received');
    } else {
        $filtered_review = filterBadWords($review_text);
        
        $order_stmt = $conn->prepare("
            SELECT o.order_id 
            FROM orders o 
            JOIN order_items oi ON o.order_id = oi.order_id 
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'
            LIMIT 1
        ");
        $order_stmt->bind_param("ii", $user_id, $product_id);
        $order_stmt->execute();
        $order = $order_stmt->get_result()->fetch_assoc();
        
        if ($order) {
            $order_id = $order['order_id'];
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, review_text) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiis", $product_id, $user_id, $order_id, $rating, $filtered_review);
            
            if ($stmt->execute()) {
                setAlert('success', 'Review submitted successfully!');
                header("Location: product-detail.php?id=" . $product_id);
                exit();
            } else {
                setAlert('error', 'Failed to submit review');
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_review'])) {
    if (!isLoggedIn()) {
        setAlert('error', 'Please login to update review');
        header("Location: login.php");
        exit();
    }
    
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $review_text = $_POST['review_text'];
    $user_id = $_SESSION['user_id'];
    
    $filtered_review = filterBadWords($review_text);
    
    $stmt = $conn->prepare("UPDATE reviews SET rating = ?, review_text = ? WHERE review_id = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $filtered_review, $review_id, $user_id);
    
    if ($stmt->execute()) {
        setAlert('success', 'Review updated successfully!');
        header("Location: product-detail.php?id=" . $product_id);
        exit();
    } else {
        setAlert('error', 'Failed to update review');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    if (!isLoggedIn()) {
        setAlert('error', 'Please login');
        header("Location: login.php");
        exit();
    }
    
    $review_id = intval($_POST['review_id']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    
    if ($stmt->execute()) {
        setAlert('success', 'Review deleted successfully!');
        header("Location: product-detail.php?id=" . $product_id);
        exit();
    } else {
        setAlert('error', 'Failed to delete review');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        setAlert('error', 'Please login to add items to cart');
        header("Location: login.php");
        exit();
    }
    
    $quantity = intval($_POST['quantity']);
    $user_id = $_SESSION['user_id'];
    
    if ($quantity > 0 && $quantity <= $product['stock_quantity']) {
        $check = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $user_id, $product_id);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        
        if ($existing) {
            $new_qty = $existing['quantity'] + $quantity;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $update->bind_param("ii", $new_qty, $existing['cart_id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $user_id, $product_id, $quantity);
            $insert->execute();
        }
        
        setAlert('success', 'Product added to cart!');
        header("Location: cart.php");
        exit();
    } else {
        setAlert('error', 'Invalid quantity');
    }
}

$cart_count = 0;
$user_data = null;
if (isLoggedIn()) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
    
    $user_stmt = $conn->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_data = $user_stmt->get_result()->fetch_assoc();
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - The Pop Stop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="orders.php">My Orders</a></li>
                        <li><a href="cart.php" class="cart-icon">
                            Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <?php include 'includes/profile_dropdown.php'; ?>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php showAlert(); ?>
        
        <div class="card">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                <div>
                    <!-- Photo Gallery -->
                    <div style="position: relative;">
                        <div class="product-image" id="mainImage" style="height: 400px; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px var(--shadow); position: relative;">
                            <?php 
                            $display_photos = !empty($product_photos) ? $product_photos : [['photo_url' => $product['image_url']]];
                            if (!empty($display_photos[0]['photo_url']) && file_exists($display_photos[0]['photo_url'])): 
                            ?>
                                <img src="<?php echo htmlspecialchars($display_photos[0]['photo_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" id="currentImage">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 10rem; background: var(--light-beige);">📦</div>
                            <?php endif; ?>
                            
                            <?php if (count($display_photos) > 1): ?>
                                <button onclick="changePhoto(-1)" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 15px 20px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; z-index: 10;">&lt;</button>
                                <button onclick="changePhoto(1)" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.5); color: white; border: none; padding: 15px 20px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; z-index: 10;">&gt;</button>
                                
                                <div style="position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                                    <span id="photoCounter">1</span> / <?php echo count($display_photos); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($display_photos) > 1): ?>
                            <!-- Thumbnail Strip -->
                            <div style="display: flex; gap: 10px; margin-top: 15px; overflow-x: auto; padding: 5px;" id="thumbnails">
                                <?php foreach ($display_photos as $index => $photo): ?>
                                    <?php if (!empty($photo['photo_url']) && file_exists($photo['photo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>" 
                                             alt="Thumbnail <?php echo $index + 1; ?>" 
                                             onclick="goToPhoto(<?php echo $index; ?>)"
                                             class="thumbnail" 
                                             data-index="<?php echo $index; ?>"
                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 3px solid <?php echo $index === 0 ? 'var(--primary)' : 'transparent'; ?>; transition: all 0.3s;">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <h1 style="color: var(--dark-brown); margin-bottom: 1rem;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div style="margin-bottom: 1rem;">
                        <span style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($product['series']); ?>
                        </span>
                        <span style="background: var(--accent); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; margin-left: 0.5rem;">
                            <?php echo htmlspecialchars($product['type']); ?>
                        </span>
                    </div>
                    
                    <p style="color: var(--secondary); font-weight: 600; margin-bottom: 1rem;">
                        Brand: <?php echo htmlspecialchars($product['brand']); ?>
                    </p>
                    
                    <div style="font-size: 2rem; font-weight: bold; color: var(--accent); margin: 1.5rem 0;">
                        <?php echo formatCurrency($product['price']); ?>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?>
                    </div>
                    
                    <div class="product-stock stock-<?php echo $product['status'] === 'In Stock' ? 'in' : ($product['status'] === 'Low Stock' ? 'low' : 'out'); ?>" 
                         style="font-size: 1.1rem; margin-bottom: 1.5rem;">
                        <?php echo $product['status']; ?> - <?php echo $product['stock_quantity']; ?> available
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="color: var(--dark-brown); margin-bottom: 0.5rem;">Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <form method="POST" style="display: flex; gap: 1rem; align-items: center;">
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" 
                                   style="width: 100px;" required>
                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-danger" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Reviews -->
        <div class="card" style="margin-top: 2rem;">
            <h2 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Customer Reviews</h2>
            
            <?php if (!empty($reviews)): ?>
                <div style="margin-bottom: 2rem; padding: 1rem; background: var(--light-beige); border-radius: 10px;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--accent);"><?php echo $avg_rating; ?> ⭐</div>
                    <p style="color: var(--secondary); margin-top: 0.5rem;">Based on <?php echo count($reviews); ?> review(s)</p>
                </div>
            <?php endif; ?>
            
            <!-- Review Form -->
            <?php if (isLoggedIn() && $can_review && !$user_review): ?>
                <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Write a Review</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Rating *</label>
                            <select name="rating" required style="width: 150px;">
                                <option value="5">5 ⭐ Excellent</option>
                                <option value="4">4 ⭐ Good</option>
                                <option value="3">3 ⭐ Average</option>
                                <option value="2">2 ⭐ Poor</option>
                                <option value="1">1 ⭐ Terrible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="review_text" rows="4" placeholder="Share your thoughts about this product..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            <?php elseif (isLoggedIn() && $user_review): ?>
                <div style="background: #e3f2fd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Your Review</h3>
                    <div style="margin-bottom: 1rem;">
                        <strong>Rating:</strong> <?php echo str_repeat('⭐', $user_review['rating']); ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($user_review['review_text'])); ?></p>
                    <small style="color: var(--secondary);">Posted on <?php echo formatDate($user_review['created_at']); ?></small>
                    <div style="margin-top: 1rem;">
                        <button onclick="showEditReviewModal(<?php echo htmlspecialchars(json_encode($user_review)); ?>)" class="btn btn-secondary btn-small">Edit Review</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="review_id" value="<?php echo $user_review['review_id']; ?>">
                            <button type="submit" name="delete_review" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete your review?')">Delete Review</button>
                        </form>
                    </div>
                </div>
            <?php elseif (isLoggedIn() && !$can_review): ?>
                <div class="alert alert-error" style="margin-bottom: 2rem;">You can only review products you have purchased and received.</div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <?php if (!empty($reviews)): ?>
                <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">All Reviews (<?php echo count($reviews); ?>)</h3>
                <?php foreach ($reviews as $review): ?>
                    <?php if ($review['user_id'] == $_SESSION['user_id'] ?? 0) continue; // Skip user's own review as it's shown above ?>
                    <div style="border-bottom: 1px solid var(--light-beige); padding: 1.5rem 0;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <?php if ($review['profile_photo'] && file_exists($review['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($review['profile_photo']); ?>" alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                    <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                <div><?php echo str_repeat('⭐', $review['rating']); ?></div>
                            </div>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <small style="color: var(--secondary);">Posted on <?php echo formatDate($review['created_at']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--secondary); text-align: center; padding: 2rem;">No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>
        
        <a href="products.php" class="btn btn-secondary">&larr; Back to Products</a>
    </div>
    
    <!-- Edit Review -->
    <div id="editReviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Your Review</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="review_id" id="edit_review_id">
                <div class="form-group">
                    <label>Rating *</label>
                    <select name="rating" id="edit_rating" required style="width: 150px;">
                        <option value="5">5 ⭐ Excellent</option>
                        <option value="4">4 ⭐ Good</option>
                        <option value="3">3 ⭐ Average</option>
                        <option value="2">2 ⭐ Poor</option>
                        <option value="1">1 ⭐ Terrible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Your Review</label>
                    <textarea name="review_text" id="edit_review_text" rows="4" required></textarea>
                </div>
                <button type="submit" name="update_review" class="btn btn-primary" style="width: 100%;">Update Review</button>
            </form>
        </div>
    </div>
    
    <script>
    function showEditReviewModal(review) {
        document.getElementById('edit_review_id').value = review.review_id;
        document.getElementById('edit_rating').value = review.rating;
        document.getElementById('edit_review_text').value = review.review_text;
        document.getElementById('editReviewModal').classList.add('active');
    }
    
    function closeModal() {
        document.getElementById('editReviewModal').classList.remove('active');
    }
    
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
        }
    }
    
    const photos = <?php echo json_encode(array_map(function($p) { return $p['photo_url']; }, $display_photos)); ?>;
    let currentPhotoIndex = 0;
    
    function changePhoto(direction) {
        currentPhotoIndex += direction;
        
        if (currentPhotoIndex < 0) {
            currentPhotoIndex = photos.length - 1;
        } else if (currentPhotoIndex >= photos.length) {
            currentPhotoIndex = 0;
        }
        
        updatePhoto();
    }
    
    function goToPhoto(index) {
        currentPhotoIndex = index;
        updatePhoto();
    }
    
    function updatePhoto() {
        // Update main image
        document.getElementById('currentImage').src = photos[currentPhotoIndex];
        
        const counter = document.getElementById('photoCounter');
        if (counter) {
            counter.textContent = currentPhotoIndex + 1;
        }
        
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (index === currentPhotoIndex) {
                thumb.style.border = '3px solid var(--primary)';
                thumb.style.transform = 'scale(1.1)';
            } else {
                thumb.style.border = '3px solid transparent';
                thumb.style.transform = 'scale(1)';
            }
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            changePhoto(-1);
        } else if (e.key === 'ArrowRight') {
            changePhoto(1);
        }
    });
    </script>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
