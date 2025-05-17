<?php
include("config.php");

// Create categories table
$sql = "CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'categories' created successfully\n";
} else {
    echo "Error creating categories table: " . $conn->error . "\n";
}

// Create ingredients table
$sql = "CREATE TABLE IF NOT EXISTS ingredients (
    ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_id INT NOT NULL,
    category_id INT NOT NULL,
    ingredient_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    quantity_value DECIMAL(10,2) NOT NULL,
    unit_type VARCHAR(20) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES apply_supplier(supplier_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'ingredients' created successfully\n";
} else {
    echo "Error creating ingredients table: " . $conn->error . "\n";
}

// Create ingredient_variants table
$sql = "CREATE TABLE IF NOT EXISTS ingredient_variants (
    variant_id INT PRIMARY KEY AUTO_INCREMENT,
    ingredient_id INT NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    quantity_value DECIMAL(10,2) NOT NULL,
    unit_type VARCHAR(20) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'ingredient_variants' created successfully\n";
} else {
    echo "Error creating ingredient_variants table: " . $conn->error . "\n";
}

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    Product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES apply_seller(seller_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'products' created successfully\n";
} else {
    echo "Error creating products table: " . $conn->error . "\n";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'orders' created successfully\n";
} else {
    echo "Error creating orders table: " . $conn->error . "\n";
}

// Create order_items table
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    variant_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES ingredient_variants(variant_id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'order_items' created successfully\n";
} else {
    echo "Error creating order_items table: " . $conn->error . "\n";
}

// Create receipts table
$sql = "CREATE TABLE IF NOT EXISTS receipts (
    receipt_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    supplier_id INT NOT NULL,
    buyer_id INT NOT NULL,
    receipt_number VARCHAR(20) NOT NULL,
    issue_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(20) NOT NULL,
    buyer_name VARCHAR(100) NOT NULL,
    buyer_address TEXT,
    buyer_contact VARCHAR(20),
    supplier_name VARCHAR(100) NOT NULL,
    supplier_address TEXT,
    supplier_contact VARCHAR(20),
    supplier_tin VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES apply_supplier(supplier_id) ON DELETE RESTRICT,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE RESTRICT
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'receipts' created successfully\n";
} else {
    echo "Error creating receipts table: " . $conn->error . "\n";
}

// Create receipt_items table
$sql = "CREATE TABLE IF NOT EXISTS receipt_items (
    receipt_item_id INT PRIMARY KEY AUTO_INCREMENT,
    receipt_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    variant_id INT,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    unit_type VARCHAR(20) NOT NULL,
    quantity_value DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (receipt_id) REFERENCES receipts(receipt_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE RESTRICT,
    FOREIGN KEY (variant_id) REFERENCES ingredient_variants(variant_id) ON DELETE SET NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'receipt_items' created successfully\n";
} else {
    echo "Error creating receipt_items table: " . $conn->error . "\n";
}

// Create ingredients_inventory table
$sql = "CREATE TABLE IF NOT EXISTS ingredients_inventory (
    inventory_id INT PRIMARY KEY AUTO_INCREMENT,
    ingredient_id INT NOT NULL,
    ingredient_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity_value DECIMAL(10,2) NOT NULL,
    unit_type VARCHAR(50) NOT NULL,
    supplier_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES apply_supplier(supplier_id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES ingredient_variants(variant_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_ingredient (user_id, ingredient_id),
    INDEX idx_supplier_ingredient (supplier_id, ingredient_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'ingredients_inventory' created successfully\n";
} else {
    echo "Error creating ingredients_inventory table: " . $conn->error . "\n";
}

$conn->close();
echo "\nAll tables have been created successfully!";
?> 