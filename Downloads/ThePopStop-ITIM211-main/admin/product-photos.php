<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

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

// photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photos'])) {
    if (isset($_FILES['photos']) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = '../products/';
        $uploaded_count = 0;
        
        $order_stmt = $conn->prepare("SELECT COALESCE(MAX(display_order), 0) as max_order FROM product_photos WHERE product_id = ?");
        $order_stmt->bind_param("i", $product_id);
        $order_stmt->execute();
        $max_order = $order_stmt->get_result()->fetch_assoc()['max_order'];
        
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_ext, $allowed_ext)) {
                    $filename = 'product_' . $product_id . '_' . time() . '_' . uniqid() . '.' . $file_ext;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $photo_url = 'products/' . $filename;
                        $is_primary = 0;
                        $display_order = ++$max_order;
                        
                        $stmt = $conn->prepare("INSERT INTO product_photos (product_id, photo_url, is_primary, display_order) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isii", $product_id, $photo_url, $is_primary, $display_order);
                        
                        if ($stmt->execute()) {
                            $uploaded_count++;
                        }
                    }
                }
            }
        }
        
        if ($uploaded_count > 0) {
            setAlert('success', "$uploaded_count photo(s) uploaded successfully");
        } else {
            setAlert('error', 'No photos were uploaded');
        }
        header("Location: product-photos.php?id=" . $product_id);
        exit();
    }
}

// delete photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_photo'])) {
    $photo_id = intval($_POST['photo_id']);
    
    $stmt = $conn->prepare("SELECT photo_url FROM product_photos WHERE photo_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $photo_id, $product_id);
    $stmt->execute();
    $photo = $stmt->get_result()->fetch_assoc();
    
    if ($photo) {
        $delete_stmt = $conn->prepare("DELETE FROM product_photos WHERE photo_id = ?");
        $delete_stmt->bind_param("i", $photo_id);
        
        if ($delete_stmt->execute()) {
            if (file_exists('../' . $photo['photo_url'])) {
                unlink('../' . $photo['photo_url']);
            }
            setAlert('success', 'Photo deleted successfully');
        } else {
            setAlert('error', 'Failed to delete photo');
        }
    }
    header("Location: product-photos.php?id=" . $product_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_primary'])) {
    $photo_id = intval($_POST['photo_id']);
    
    $conn->prepare("UPDATE product_photos SET is_primary = 0 WHERE product_id = $product_id")->execute();
    
    $stmt = $conn->prepare("UPDATE product_photos SET is_primary = 1 WHERE photo_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $photo_id, $product_id);
    
    if ($stmt->execute()) {
        setAlert('success', 'Primary photo updated');
    }
    header("Location: product-photos.php?id=" . $product_id);
    exit();
}

$photos_stmt = $conn->prepare("SELECT * FROM product_photos WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC");
$photos_stmt->bind_param("i", $product_id);
$photos_stmt->execute();
$photos = $photos_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Product Photos - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="../index.php">View Site</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-layout">
        <div class="admin-sidebar">
            <h3>Admin Menu</h3>
            <ul>
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php" class="active">📦 Products</a></li>
                <li><a href="orders.php">🛍️ Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <h1 style="color: var(--dark-brown); margin-bottom: 1rem;">Manage Product Photos</h1>
            <p style="color: var(--secondary); margin-bottom: 2rem;">Product: <strong><?php echo htmlspecialchars($product['name']); ?></strong></p>
            
            <?php showAlert(); ?>
            
            <!-- Upload Photos -->
            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Upload New Photos</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select Photos (Multiple)</label>
                        <input type="file" name="photos[]" accept="image/*" multiple required>
                        <small style="color: var(--secondary);">You can select multiple images at once. Supported: JPG, PNG, GIF, WEBP</small>
                    </div>
                    <button type="submit" name="upload_photos" class="btn btn-primary">Upload Photos</button>
                </form>
            </div>
            
            <!-- Current Photos -->
            <div class="card">
                <h3 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Current Photos (<?php echo count($photos); ?>)</h3>
                
                <?php if (!empty($photos)): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($photos as $photo): ?>
                            <div class="card" style="padding: 1rem; text-align: center; <?php echo $photo['is_primary'] == 1 ? 'border: 3px solid var(--accent);' : ''; ?>">
                                <?php if ($photo['is_primary'] == 1): ?>
                                    <div style="background: var(--accent); color: white; padding: 0.3rem; border-radius: 5px; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: bold;">PRIMARY PHOTO</div>
                                <?php endif; ?>
                                <img src="../<?php echo htmlspecialchars($photo['photo_url']); ?>" alt="Product Photo" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                                
                                <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                    <?php if ($photo['is_primary'] == 0): ?>
                                        <form method="POST">
                                            <input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>">
                                            <button type="submit" name="set_primary" class="btn btn-primary btn-small" style="width: 100%;">Set as Primary</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST">
                                        <input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>">
                                        <button type="submit" name="delete_photo" class="btn btn-danger btn-small" style="width: 100%;" onclick="return confirm('Are you sure you want to delete this photo?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--secondary); padding: 2rem;">No photos uploaded yet. Upload some photos above.</p>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="products.php" class="btn btn-secondary">← Back to Products</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
