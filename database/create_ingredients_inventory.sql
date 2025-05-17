CREATE TABLE IF NOT EXISTS `ingredients_inventory` (
    `inventory_id` INT PRIMARY KEY AUTO_INCREMENT,
    `ingredient_id` INT NOT NULL,
    `ingredient_name` VARCHAR(255) NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `quantity_value` DECIMAL(10,2) NOT NULL,
    `unit_type` VARCHAR(50) NOT NULL,
    `supplier_id` INT NOT NULL,
    `variant_id` INT DEFAULT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients`(`ingredient_id`) ON DELETE CASCADE,
    FOREIGN KEY (`supplier_id`) REFERENCES `apply_supplier`(`supplier_id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `ingredient_variants`(`variant_id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_ingredient` (`user_id`, `ingredient_id`),
    INDEX `idx_supplier_ingredient` (`supplier_id`, `ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 