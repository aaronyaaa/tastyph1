<?php
include("config.php");

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
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (supplier_id) REFERENCES apply_supplier(supplier_id),
    FOREIGN KEY (buyer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'receipts' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

// Create receipt items table for detailed line items
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
    FOREIGN KEY (receipt_id) REFERENCES receipts(receipt_id),
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id),
    FOREIGN KEY (variant_id) REFERENCES ingredient_variants(variant_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'receipt_items' created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?> 