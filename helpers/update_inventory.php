<?php
session_start();
include('../database/config.php');
include('../database/data_session.php');

// Ensure user is logged in as supplier
$userId = $_SESSION['userId'] ?? null;
$userType = $_SESSION['usertype'] ?? null;

if (!$userId || $userType !== 'supplier') {
    die("Unauthorized access. Please log in as a supplier.");
}

// Get supplier ID
$stmtSupplier = $conn->prepare("
    SELECT DISTINCT i.supplier_id, s.business_name
    FROM ingredients i
    JOIN apply_supplier s ON i.supplier_id = s.supplier_id
    WHERE i.supplier_id = ?
    LIMIT 1
");
if (!$stmtSupplier) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmtSupplier->bind_param("i", $userId);
$stmtSupplier->execute();
$supplierResult = $stmtSupplier->get_result();
$supplier = $supplierResult->fetch_assoc();
$stmtSupplier->close();

$supplierId = $supplier['supplier_id'] ?? null;
if (!$supplierId) {
    die("Supplier account not found.");
}

// Start transaction
$conn->begin_transaction();

try {
    // Get all ingredients for this supplier that don't have inventory records
    $sql = "
        SELECT i.* 
        FROM ingredients i
        LEFT JOIN ingredients_inventory inv ON i.ingredient_id = inv.ingredient_id 
            AND inv.supplier_id = i.supplier_id
        WHERE i.supplier_id = ? 
        AND inv.inventory_id IS NULL
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing select: " . $conn->error);
    }
    
    $stmt->bind_param("i", $supplierId);
    if (!$stmt->execute()) {
        throw new Exception("Error executing select: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $ingredients = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Create inventory records for each ingredient
    $insertSql = "
        INSERT INTO ingredients_inventory 
        (ingredient_id, ingredient_name, description, quantity, quantity_value, unit_type, price, supplier_id, user_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) {
        throw new Exception("Error preparing insert: " . $conn->error);
    }
    
    foreach ($ingredients as $ingredient) {
        $insertStmt->bind_param("issiiidsi", 
            $ingredient['ingredient_id'],
            $ingredient['ingredient_name'],
            $ingredient['description'],
            $ingredient['quantity'],
            $ingredient['quantity_value'],
            $ingredient['unit_type'],
            $ingredient['price'],
            $supplierId,
            $supplierId  // user_id is same as supplier_id for supplier's own inventory
        );
        
        if (!$insertStmt->execute()) {
            throw new Exception("Error inserting inventory for ingredient {$ingredient['ingredient_name']}: " . $insertStmt->error);
        }
    }
    
    $insertStmt->close();
    
    // Commit transaction
    $conn->commit();
    echo "Inventory records created successfully for " . count($ingredients) . " ingredients.";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?> 