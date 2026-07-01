<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = sanitize($_POST['name']);
        $series = sanitize($_POST['series']);
        $brand = sanitize($_POST['brand']);
        $price = floatval($_POST['price']);
        $cost_price = floatval($_POST['cost_price']);
        $sku = sanitize($_POST['sku']);
        $description = sanitize($_POST['description']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category = sanitize($_POST['category']);
        $type = sanitize($_POST['type']);
        $image_url = '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../products/';
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = 'products/' . $filename;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO products (name, series, brand, price, cost_price, sku, description, stock_quantity, category, type, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddsisiss", $name, $series, $brand, $price, $cost_price, $sku, $description, $stock_quantity, $category, $type, $image_url);
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            updateProductStatus($conn, $product_id);
            setAlert('success', 'Product added successfully');
        } else {
            setAlert('error', 'Failed to add product');
        }
        header("Location: products.php");
        exit();
    }
    
    if (isset($_POST['update_product'])) {
        $product_id = intval($_POST['product_id']);
        $name = sanitize($_POST['name']);
        $series = sanitize($_POST['series']);
        $brand = sanitize($_POST['brand']);
        $price = floatval($_POST['price']);
        $cost_price = floatval($_POST['cost_price']);
        $sku = sanitize($_POST['sku']);
        $description = sanitize($_POST['description']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $category = sanitize($_POST['category']);
        $type = sanitize($_POST['type']);
        
        // existing image
        $query = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
        $query->bind_param("i", $product_id);
        $query->execute();
        $result = $query->get_result();
        $existing_product = $result->fetch_assoc();
        $image_url = $existing_product['image_url'];
        
        // image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../products/';
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image
                    if ($image_url && file_exists('../' . $image_url)) {
                        unlink('../' . $image_url);
                    }
                    $image_url = 'products/' . $filename;
                }
            }
        }
        
        $stmt = $conn->prepare("UPDATE products SET name = ?, series = ?, brand = ?, price = ?, cost_price = ?, sku = ?, description = ?, stock_quantity = ?, category = ?, type = ?, image_url = ? WHERE product_id = ?");
        $stmt->bind_param("sssddsisissi", $name, $series, $brand, $price, $cost_price, $sku, $description, $stock_quantity, $category, $type, $image_url, $product_id);
        
        if ($stmt->execute()) {
            updateProductStatus($conn, $product_id);
            setAlert('success', 'Product updated successfully');
        } else {
            setAlert('error', 'Failed to update product');
        }
        header("Location: products.php");
        exit();
    }
    
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Product deleted successfully');
        } else {
            setAlert('error', 'Failed to delete product');
        }
        header("Location: products.php");
        exit();
    }
}

$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown);">Manage Products</h1>
                <button onclick="showAddModal()" class="btn btn-primary">+ Add New Product</button>
            </div>
            
            <?php showAlert(); ?>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Series</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td>
                                    <?php if ($product['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: var(--light-beige); border-radius: 5px; display: flex; align-items: center; justify-content: center;">📦</div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['series']); ?></td>
                                <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                <td><?php echo formatCurrency($product['price']); ?></td>
                                <td><?php echo formatCurrency($product['cost_price']); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td class="stock-<?php echo $product['status'] === 'In Stock' ? 'in' : ($product['status'] === 'Low Stock' ? 'low' : 'out'); ?>">
                                    <?php echo $product['status']; ?>
                                </td>
                                <td>
                                    <a href="product-photos.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-small">📷 Photos</a>
                                    <button onclick='showEditModal(<?php echo json_encode($product); ?>)' class="btn btn-secondary btn-small">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" name="delete_product" class="btn btn-danger btn-small" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Product</h2>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Series</label>
                    <input type="text" name="series">
                </div>
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand">
                </div>
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Cost Price *</label>
                    <input type="number" step="0.01" name="cost_price" required>
                </div>
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <option value="Regular">Regular</option>
                        <option value="Blind Box">Blind Box</option>
                        <option value="Limited Edition">Limited Edition</option>
                        <option value="Open Box">Open Box</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" value="0" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                    <small style="color: var(--secondary);">Supported: JPG, PNG, GIF, WEBP</small>
                </div>
                <button type="submit" name="add_product" class="btn btn-primary" style="width: 100%;">Add Product</button>
            </form>
        </div>
    </div>

    <!-- Edit Product -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Product</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" id="editForm" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Series</label>
                    <input type="text" name="series" id="edit_series">
                </div>
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" id="edit_brand">
                </div>
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required>
                </div>
                <div class="form-group">
                    <label>Cost Price *</label>
                    <input type="number" step="0.01" name="cost_price" id="edit_cost_price" required>
                </div>
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" id="edit_sku" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="edit_category">
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="edit_type">
                        <option value="Regular">Regular</option>
                        <option value="Blind Box">Blind Box</option>
                        <option value="Limited Edition">Limited Edition</option>
                        <option value="Open Box">Open Box</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <div id="edit_current_image" style="margin-bottom: 0.5rem;"></div>
                    <input type="file" name="image" accept="image/*">
                    <small style="color: var(--secondary);">Leave empty to keep current image. Supported: JPG, PNG, GIF, WEBP</small>
                </div>
                <button type="submit" name="update_product" class="btn btn-primary" style="width: 100%;">Update Product</button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function showEditModal(product) {
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_series').value = product.series;
            document.getElementById('edit_brand').value = product.brand;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_cost_price').value = product.cost_price;
            document.getElementById('edit_sku').value = product.sku;
            document.getElementById('edit_category').value = product.category;
            document.getElementById('edit_type').value = product.type;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_description').value = product.description;
            
            // Show current image
            const imageContainer = document.getElementById('edit_current_image');
            if (product.image_url) {
                imageContainer.innerHTML = '<img src="../' + product.image_url + '" alt="Current Image" style="max-width: 200px; border-radius: 10px;"><br><small>Current Image</small>';
            } else {
                imageContainer.innerHTML = '<small style="color: var(--secondary);">No image uploaded</small>';
            }
            
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
