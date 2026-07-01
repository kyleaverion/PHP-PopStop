-- The Pop Stop Database
-- E-commerce site for collectible figurines

CREATE DATABASE IF NOT EXISTS thepopstop;
USE thepopstop;

-- Users table (MP2: profile photo, user activation, role management)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_photo VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_active (is_active)
);

-- Products table (MP1: single main photo)
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    series VARCHAR(100),
    brand VARCHAR(100),
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2) DEFAULT 0,
    sku VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    stock_quantity INT DEFAULT 0,
    category VARCHAR(50),
    type ENUM('Blind Box', 'Limited Edition', 'Open Box', 'Regular') DEFAULT 'Regular',
    image_url VARCHAR(255),
    status ENUM('In Stock', 'Out of Stock', 'Low Stock') DEFAULT 'In Stock',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_name (name),
    INDEX idx_product_brand (brand)
);

-- Product Photos table (MP1: multiple photos per product)
CREATE TABLE product_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_photos (product_id)
);

-- Suppliers table (FR5.1)
CREATE TABLE suppliers (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Purchase Orders table (FR5.2, FR5.3)
CREATE TABLE purchase_orders (
    po_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT,
    order_date DATE NOT NULL,
    status ENUM('Ordered', 'Shipped', 'Received', 'Cancelled') DEFAULT 'Ordered',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
);

-- Purchase Order Items table (FR5.2)
CREATE TABLE purchase_order_items (
    po_item_id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_cost DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(po_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Orders table (MP4: transaction management)
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_status (status),
    INDEX idx_orders_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);


-- Order Items table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
);

-- Reviews table (MP4: product/service reviews)
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_review_product (product_id),
    INDEX idx_review_user (user_id),
    UNIQUE KEY unique_user_product_order (user_id, product_id, order_id)
);

-- Email Notifications table (MP4: track emails sent)
CREATE TABLE email_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_id INT,
    email_to VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed') DEFAULT 'sent',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL,
    INDEX idx_email_user (user_id),
    INDEX idx_email_order (order_id)
);

-- Cart table
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Discounts/Promotions table
CREATE TABLE discounts (
    discount_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(200),
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10, 2) NOT NULL,
    min_purchase DECIMAL(10, 2) DEFAULT 0,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expense Categories table
CREATE TABLE expense_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

CREATE TABLE expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    reference VARCHAR(100),
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES expense_categories(category_id) ON DELETE RESTRICT,
    INDEX idx_expenses_date (expense_date)
);

-- MySQL View: Order Transaction Details (Database requirement)
CREATE OR REPLACE VIEW vw_order_details AS
SELECT 
    o.order_id,
    o.order_date,
    o.status AS order_status,
    u.user_id,
    u.email AS customer_email,
    u.full_name AS customer_name,
    u.phone AS customer_phone,
    o.shipping_address,
    o.payment_method,
    oi.order_item_id,
    p.product_id,
    p.name AS product_name,
    p.sku,
    oi.quantity,
    oi.unit_price,
    o.discount_amount
FROM orders o
LEFT JOIN users u ON o.user_id = u.user_id
LEFT JOIN order_items oi ON o.order_id = oi.order_id
LEFT JOIN products p ON oi.product_id = p.product_id;

-- Seed: admin users
-- Admin 1: admin1@gmail.com / ayessa
-- Admin 2: admin2@gmail.com / allysa
INSERT INTO users (username, email, password, full_name, role, is_active) VALUES
('admin1', 'admin1@gmail.com', '$2y$10$nQkBon17YtN2pprsp66Oa.nhZXYzLtTWMiQpAz9G5RwxWRuH.zpIO', 'Admin Ayessa', 'admin', 1),
('admin2', 'admin2@gmail.com', '$2y$10$aWMYR28yfWxacxRmfaOp9OeRKjyuTWfPD5X1qoyYMjBBC6xZE/bcy', 'Admin Allysa', 'admin', 1);

-- Seed: products
INSERT INTO products (name, series, brand, price, cost_price, sku, description, stock_quantity, category, type, image_url, status) VALUES
-- HIRONO SERIES (POP MART)
('Hirono × Stefanie Sun AUT NIHILO Figure', 'Hirono', 'Pop Mart', 2550.00, 2450.00, 'PM-HIR-001', 'Hirono × Stefanie Sun AUT NIHILO Figure', 15, 'Figurines', 'Limited Edition', 'products/hirono1.jpg', 'In Stock'),
('Hirono × Stefanie Sun Weather With You Figurine', 'Hirono', 'Pop Mart', 6000.00, 5900.00, 'PM-HIR-002', 'Hirono × Stefanie Sun Weather With You Figurine', 8, 'Figurines', 'Limited Edition', 'products/hirono2.jpg', 'Low Stock'),
('Hirono Birdy Figurine', 'Hirono', 'Pop Mart', 6000.00, 5900.00, 'PM-HIR-003', 'Hirono Birdy Figurine', 10, 'Figurines', 'Regular', 'products/hirono3.jpg', 'Low Stock'),
('Hirono Reshape Figurine', 'Hirono', 'Pop Mart', 6000.00, 5900.00, 'PM-HIR-004', 'Hirono Reshape Figurine', 12, 'Figurines', 'Regular', 'products/hirono4.jpg', 'In Stock'),
('Hirono x Keith Haring Figurine', 'Hirono', 'Pop Mart', 6000.00, 5900.00, 'PM-HIR-005', 'Hirono x Keith Haring Figurine', 7, 'Figurines', 'Limited Edition', 'products/hirono5.jpg', 'Low Stock'),
('Hirono × Gary Baseman Figure', 'Hirono', 'Pop Mart', 1700.00, 1600.00, 'PM-HIR-006', 'Hirono × Gary Baseman Figure', 20, 'Figurines', 'Blind Box', 'products/hirono6.jpg', 'In Stock'),
('Hirono The Pianist Figure', 'Hirono', 'Pop Mart', 2550.00, 2450.00, 'PM-HIR-007', 'Hirono The Pianist Figure', 18, 'Figurines', 'Regular', 'products/hirono7.jpg', 'In Stock'),
('Hirono Living Wild-Fight for Joy Plush Doll', 'Hirono', 'Pop Mart', 1470.00, 1370.00, 'PM-HIR-008', 'Hirono Living Wild-Fight for Joy Plush Doll', 25, 'Plush', 'Regular', 'products/hirono8.jpg', 'In Stock'),
('Hirono × Snoopy Figure', 'Hirono', 'Pop Mart', 1700.00, 1600.00, 'PM-HIR-009', 'Hirono × Snoopy Figure', 22, 'Figurines', 'Blind Box', 'products/hirono9.jpg', 'In Stock'),
('Hirono Doll Panda Figure', 'Hirono', 'Pop Mart', 1700.00, 1600.00, 'PM-HIR-010', 'Hirono Doll Panda Figure', 16, 'Figurines', 'Blind Box', 'products/hirono10.jpg', 'In Stock'),

-- SKULLPANDA SERIES (POP MART)
('SKULLPANDA Covenant of the White Moon Figure', 'Skullpanda', 'Pop Mart', 1700.00, 1600.00, 'PM-SKP-001', 'SKULLPANDA Covenant of the White Moon Figure', 20, 'Figurines', 'Blind Box', 'products/skullpanda1.jpg', 'In Stock'),
('SKULLPANDA The Glimpse Figure', 'Skullpanda', 'Pop Mart', 1700.00, 1600.00, 'PM-SKP-002', 'SKULLPANDA The Glimpse Figure', 18, 'Figurines', 'Blind Box', 'products/skullpanda2.jpg', 'In Stock'),
('SKULLPANDA Club Man Figurine', 'Skullpanda', 'Pop Mart', 1700.00, 1600.00, 'PM-SKP-003', 'SKULLPANDA Club Man Figurine', 15, 'Figurines', 'Blind Box', 'products/skullpanda3.jpg', 'In Stock'),

-- CRYBABY SERIES (POP MART)
('CRYBABY BE MINE FIGURINE', 'Crybaby', 'Pop Mart', 7280.00, 7180.00, 'PM-CRY-001', 'CRYBABY BE MINE FIGURINE', 5, 'Figurines', 'Limited Edition', 'products/crybaby1.jpg', 'Low Stock'),
('CRYBABY MAKE ME FLOAT FIGURE', 'Crybaby', 'Pop Mart', 1700.00, 1600.00, 'PM-CRY-002', 'CRYBABY MAKE ME FLOAT FIGURE', 14, 'Figurines', 'Blind Box', 'products/crybaby2.jpg', 'In Stock'),
('Crybaby Coconut Figure-Brown', 'Crybaby', 'Pop Mart', 1700.00, 1600.00, 'PM-CRY-003', 'Crybaby Coconut Figure-Brown', 12, 'Figurines', 'Blind Box', 'products/crybaby3.jpg', 'In Stock'),
('Crybaby Coconut Figure-Green', 'Crybaby', 'Pop Mart', 1700.00, 1600.00, 'PM-CRY-004', 'Crybaby Coconut Figure-Green', 11, 'Figurines', 'Blind Box', 'products/crybaby4.jpg', 'In Stock'),

-- THE MONSTER SERIES (POP MART)
('LABUBU Hip-hop Girl Figure', 'The Monster', 'Pop Mart', 1700.00, 1600.00, 'PM-LAB-001', 'LABUBU Hip-hop Girl Figure', 25, 'Figurines', 'Blind Box', 'products/labubu1.jpg', 'In Stock'),
('LABUBU Superstar Dance Moves Figure', 'The Monster', 'Pop Mart', 1700.00, 1600.00, 'PM-LAB-002', 'LABUBU Superstar Dance Moves Figure', 22, 'Figurines', 'Blind Box', 'products/labubu2.jpg', 'In Stock'),
('THE MONSTERS_How to Train Your Dragon Figurine', 'The Monster', 'Pop Mart', 6000.00, 5900.00, 'PM-MON-001', 'THE MONSTERS_How to Train Your Dragon Figurine', 8, 'Figurines', 'Limited Edition', 'products/labubu3.jpg', 'Low Stock'),

-- PINO JELLY SERIES (POP MART)
('PINO JELLY Chocolate Cookie Figurine', 'Pino Jelly', 'Pop Mart', 5000.00, 4900.00, 'PM-PIN-001', 'PINO JELLY Chocolate Cookie Figurine', 10, 'Figurines', 'Regular', 'products/pino1.jpg', 'Low Stock'),
('PINO JELLY Birthday Bash Figurine', 'Pino Jelly', 'Pop Mart', 5000.00, 4900.00, 'PM-PIN-002', 'PINO JELLY Birthday Bash Figurine', 12, 'Figurines', 'Regular', 'products/pino2.jpg', 'In Stock'),
('PINO JELLY Guess Who I am Figure', 'Pino Jelly', 'Pop Mart', 1700.00, 1600.00, 'PM-PIN-003', 'PINO JELLY Guess Who I am Figure', 18, 'Figurines', 'Blind Box', 'products/pino3.jpg', 'In Stock'),
('PINO JELLY Fairyland Figurine', 'Pino Jelly', 'Pop Mart', 5000.00, 4900.00, 'PM-PIN-004', 'PINO JELLY Fairyland Figurine', 9, 'Figurines', 'Regular', 'products/pino4.jpg', 'Low Stock'),

-- FUNKO SERIES
('Funko Marvel: Deadpool & Wolverine - Wolverine Pop! Vinyl Figure', 'Marvel', 'Funko', 695.00, 595.00, 'FK-MAR-001', 'Funko Marvel: Deadpool & Wolverine - Wolverine Pop! Vinyl Figure', 30, 'Figurines', 'Regular', 'products/funko1.jpg', 'In Stock'),
('Funko Marvel: Deadpool & Wolverine - Deadpool Pop! Vinyl Figure', 'Marvel', 'Funko', 695.00, 595.00, 'FK-MAR-002', 'Funko Marvel: Deadpool & Wolverine - Deadpool Pop! Vinyl Figure', 28, 'Figurines', 'Regular', 'products/funko2.jpg', 'In Stock'),
('Funko DC Comics Batman War Zone - The Joker War Joker Pop! Vinyl Figure', 'DC Comics', 'Funko', 695.00, 595.00, 'FK-DC-001', 'Funko DC Comics Batman War Zone - The Joker War Joker Pop! Vinyl Figure', 25, 'Figurines', 'Regular', 'products/funko3.jpg', 'In Stock'),
('Funko Bleach Ichigo Kurosaki (FB Shikai) Funko Pop! Vinyl Figure', 'Anime', 'Funko', 695.00, 595.00, 'FK-ANI-001', 'Funko Bleach Ichigo Kurosaki (FB Shikai) Funko Pop! Vinyl Figure', 20, 'Figurines', 'Regular', 'products/funko4.jpg', 'In Stock'),
('Funko Boruto: Naruto Next Generations Mirai Sarutobi Funko Pop! Vinyl Figure', 'Anime', 'Funko', 695.00, 595.00, 'FK-ANI-002', 'Funko Boruto: Naruto Next Generations Mirai Sarutobi Funko Pop! Vinyl Figure', 22, 'Figurines', 'Regular', 'products/funko5.jpg', 'In Stock'),
('Funko Spider-Man 2 Game Miles Morales Upgraded Suit Funko Pop! Vinyl Figure', 'Games', 'Funko', 695.00, 595.00, 'FK-GAM-001', 'Funko Spider-Man 2 Game Miles Morales Upgraded Suit Funko Pop! Vinyl Figure', 18, 'Figurines', 'Regular', 'products/funko6.jpg', 'In Stock'),
('Funko Demon Slayer Tengen Uzui Funko Pop! Vinyl Figure', 'Anime', 'Funko', 695.00, 595.00, 'FK-ANI-003', 'Funko Demon Slayer Tengen Uzui Funko Pop! Vinyl Figure', 24, 'Figurines', 'Regular', 'products/funko7.jpg', 'In Stock'),
('Funko My Hero Academia Katsuki Bakugo Funko Pop! Vinyl Figure - Previews Exclusive', 'Anime', 'Funko', 1195.00, 1095.00, 'FK-ANI-004', 'Funko My Hero Academia Katsuki Bakugo Funko Pop! Vinyl Figure - Previews Exclusive', 12, 'Figurines', 'Limited Edition', 'products/funko8.jpg', 'In Stock'),
('Funko Black Clover Asta with Nero Funko Pop! Vinyl Figure', 'Anime', 'Funko', 695.00, 595.00, 'FK-ANI-005', 'Funko Black Clover Asta with Nero Funko Pop! Vinyl Figure', 19, 'Figurines', 'Regular', 'products/funko9.jpg', 'In Stock'),
('Funko One Piece Onami (Wano) Funko Pop! Vinyl Figure', 'Anime', 'Funko', 695.00, 595.00, 'FK-ANI-006', 'Funko One Piece Onami (Wano) Funko Pop! Vinyl Figure', 21, 'Figurines', 'Regular', 'products/funko10.jpg', 'In Stock');

-- Seed: product photos (multiple photos per product for gallery)
INSERT INTO product_photos (product_id, photo_url, is_primary, display_order) VALUES
-- Hirono Series Photos
(1, 'products/hirono1.jpg', 1, 1),
(1, 'products/hirono1.2.jpg', 0, 2),
(1, 'products/hirono1.3.jpg', 0, 3),
(2, 'products/hirono2.jpg', 1, 1),
(2, 'products/hirono2.2.jpg', 0, 2),
(2, 'products/hirono2.3.jpg', 0, 3),
(3, 'products/hirono3.jpg', 1, 1),
(3, 'products/hirono3.2.jpg', 0, 2),
(3, 'products/hirono3.3.jpg', 0, 3),
(4, 'products/hirono4.jpg', 1, 1),
(4, 'products/hirono4.2.jpg', 0, 2),
(4, 'products/hirono4.3.jpg', 0, 3),
(5, 'products/hirono5.jpg', 1, 1),
(5, 'products/hirono5.2.jpg', 0, 2),
(5, 'products/hirono5.3.jpg', 0, 3),
(6, 'products/hirono6.jpg', 1, 1),
(6, 'products/hirono6.2.jpg', 0, 2),
(6, 'products/hirono6.3.jpg', 0, 3),
(7, 'products/hirono7.jpg', 1, 1),
(7, 'products/hirono7.2.jpg', 0, 2),
(7, 'products/hirono7.3.jpg', 0, 3),
(8, 'products/hirono8.jpg', 1, 1),
(8, 'products/hirono8.2.jpg', 0, 2),
(8, 'products/hirono8.3.jpg', 0, 3),
(9, 'products/hirono9.jpg', 1, 1),
(9, 'products/hirono9.2.jpg', 0, 2),
(9, 'products/hirono9.3.jpg', 0, 3),
(10, 'products/hirono10.jpg', 1, 1),
(10, 'products/hirono10.2.jpg', 0, 2),
(10, 'products/hirono10.3.jpg', 0, 3),

-- Skullpanda Series Photos
(11, 'products/skullpanda1.jpg', 1, 1),
(11, 'products/skullpanda1.2.jpg', 0, 2),
(11, 'products/skullpanda1.3.jpg', 0, 3),
(12, 'products/skullpanda2.jpg', 1, 1),
(12, 'products/skullpanda2.2.jpg', 0, 2),
(12, 'products/skullpanda2.3.jpg', 0, 3),
(13, 'products/skullpanda3.jpg', 1, 1),
(13, 'products/skullpanda3.2.jpg', 0, 2),
(13, 'products/skullpanda3.3.jpg', 0, 3),

-- Crybaby Series Photos
(14, 'products/crybaby1.jpg', 1, 1),
(14, 'products/crybaby1.2.jpg', 0, 2),
(14, 'products/crybaby1.3.jpg', 0, 3),
(15, 'products/crybaby2.jpg', 1, 1),
(15, 'products/crybaby2.2.jpg', 0, 2),
(15, 'products/crybaby2.3.jpg', 0, 3),
(16, 'products/crybaby3.jpg', 1, 1),
(16, 'products/crybaby3.2.jpg', 0, 2),
(16, 'products/crybaby3.3.jpg', 0, 3),
(17, 'products/crybaby4.jpg', 1, 1),
(17, 'products/crybaby4.2.jpg', 0, 2),
(17, 'products/crybaby4.3.jpg', 0, 3),

-- The Monster Series Photos
(18, 'products/labubu1.jpg', 1, 1),
(18, 'products/labubu1.2.jpg', 0, 2),
(18, 'products/labubu1.3.jpg', 0, 3),
(19, 'products/labubu2.jpg', 1, 1),
(19, 'products/labubu2.2.jpg', 0, 2),
(19, 'products/labubu2.3.jpg', 0, 3),
(20, 'products/labubu3.jpg', 1, 1),
(20, 'products/labubu3.2.jpg', 0, 2),
(20, 'products/labubu3.3.jpg', 0, 3),

-- Pino Jelly Series Photos
(21, 'products/pino1.jpg', 1, 1),
(21, 'products/pino1.2.jpg', 0, 2),
(21, 'products/pino1.3.jpg', 0, 3),
(22, 'products/pino2.jpg', 1, 1),
(22, 'products/pino2.2.jpg', 0, 2),
(22, 'products/pino2.3.jpg', 0, 3),
(23, 'products/pino3.jpg', 1, 1),
(23, 'products/pino3.2.jpg', 0, 2),
(23, 'products/pino3.3.jpg', 0, 3),
(24, 'products/pino4.jpg', 1, 1),
(24, 'products/pino4.2.jpg', 0, 2),
(24, 'products/pino4.3.jpg', 0, 3),

-- Funko Series Photos
(25, 'products/funko1.jpg', 1, 1),
(25, 'products/funko1.2.jpg', 0, 2),
(25, 'products/funko1.3.png', 0, 3),
(26, 'products/funko2.jpg', 1, 1),
(26, 'products/funko2.2.jpg', 0, 2),
(26, 'products/funko2.3.png', 0, 3),
(27, 'products/funko3.jpg', 1, 1),
(27, 'products/funko3.2.png', 0, 2),
(28, 'products/funko4.jpg', 1, 1),
(28, 'products/funko4.2.png', 0, 2),
(29, 'products/funko5.jpg', 1, 1),
(29, 'products/funko5.2.png', 0, 2),
(30, 'products/funko6.jpg', 1, 1),
(30, 'products/funko6.2.png', 0, 2),
(31, 'products/funko7.jpg', 1, 1),
(31, 'products/funko7.2.png', 0, 2),
(32, 'products/funko8.jpg', 1, 1),
(32, 'products/funko8.2.jpg', 0, 2),
(33, 'products/funko9.jpg', 1, 1),
(33, 'products/funko9.2.png', 0, 2),
(34, 'products/funko10.jpg', 1, 1),
(34, 'products/funko10.2.png', 0, 2);

-- Seed: suppliers
INSERT INTO suppliers (brand, contact_person, email, phone, address) VALUES
('Pop Mart', 'Pop Mart Philippines', 'contact@popmart.ph', '+63-2-1234-5678', 'SM Megamall, Mandaluyong City, Philippines'),
('Funko', 'Funko Philippines', 'sales@funko.ph', '+63-2-8765-4321', 'Bonifacio Global City, Taguig, Philippines');

-- Seed: discount codes
INSERT INTO discounts (code, description, discount_type, discount_value, min_purchase, start_date, end_date, is_active) VALUES
('WELCOME10', '10% off for new customers', 'percentage', 10.00, 50.00, '2024-01-01', '2025-12-31', 1),
('SAVE20', '20 off orders over 100', 'fixed', 20.00, 100.00, '2024-01-01', '2025-12-31', 1);

-- Seed: expense categories
INSERT INTO expense_categories (name, description) VALUES
('Website Hosting & Domain', 'Hosting and domain registration costs'),
('Website Maintenance', 'Website updates and maintenance'),
('Internet Connection', 'Monthly internet service'),
('Electricity', 'Monthly electricity bills'),
('Business Insurance', 'Annual business insurance'),
('License Renewal', 'Business license renewal'),
('Tax Compliance', 'Tax filing and compliance');

-- Seed: sample expenses
INSERT INTO expenses (category_id, amount, expense_date, reference, notes) VALUES
(1, 2000.00, '2025-01-01', 'INV-2025-001', 'Annual hosting renewal'),
(2, 1500.00, '2025-01-01', 'INV-2025-002', 'Website maintenance Q1'),
(3, 1600.00, '2025-01-15', 'INV-2025-003', 'Internet Jan 2025'),
(4, 4000.00, '2025-01-15', 'INV-2025-004', 'Electricity Jan 2025');