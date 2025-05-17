<?php
include("../database/config.php");

// Fetch user_id from session (assuming user is logged in)
$userId = $_SESSION['userId'] ?? null; // Replace with your actual user session variable

// Fetch recipes based on user_id
$sql = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$recipes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Quick Access</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/off.css" rel="stylesheet">
</head>

<body>
    <!-- Floating Action Button -->
    <button class="circle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#recipePanel" aria-controls="recipePanel">
        <i class="bi bi-journal-text"></i>
    </button>

    <!-- Recipe Panel -->
    <div class="offcanvas offcanvas-end recipe-offcanvas" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="recipePanel" aria-labelledby="recipePanelLabel">
        <div class="offcanvas-header recipe-offcanvas-header">
            <h5 class="offcanvas-title recipe-offcanvas-title" id="recipePanelLabel">
                <i class="bi bi-journal-text me-2"></i>My Recipes
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-info-circle me-2 text-primary"></i>
                <p class="mb-0 small">Click on a recipe to view its ingredients</p>
            </div>

            <!-- Recipe List -->
            <div id="recipe-list">
                <?php if ($recipes->num_rows > 0): ?>
                    <?php while ($row = $recipes->fetch_assoc()): ?>
                        <div class="recipe-item" 
                             data-bs-toggle="collapse" 
                             data-bs-target="#recipe-<?php echo $row['recipe_id']; ?>" 
                             aria-expanded="false" 
                             aria-controls="recipe-<?php echo $row['recipe_id']; ?>">
                            <h6>
                                <span class="recipe-title">
                                    <i class="bi bi-bookmark-star me-2 text-primary"></i>
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </span>
                                <span class="close-recipe" 
                                      data-bs-toggle="collapse" 
                                      data-bs-target="#recipe-<?php echo $row['recipe_id']; ?>" 
                                      aria-expanded="false" 
                                      aria-controls="recipe-<?php echo $row['recipe_id']; ?>">
                                    <i class="bi bi-x-lg"></i>
                                </span>
                            </h6>
                        </div>

                        <!-- Ingredients Section -->
                        <div id="recipe-<?php echo $row['recipe_id']; ?>" class="collapse">
                            <h6>
                                <i class="bi bi-list-check me-2"></i>
                                Ingredients
                            </h6>
                            <ul>
                                <?php
                                $ingredients = explode("\n", $row['ingredients']);
                                foreach ($ingredients as $ingredient):
                                    $ingredient = trim($ingredient);
                                    if (!empty($ingredient)):
                                ?>
                                    <li><?php echo htmlspecialchars($ingredient); ?></li>
                                <?php endif; endforeach; ?>
                            </ul>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-journal-x display-4 text-muted mb-3"></i>
                        <p class="text-muted mb-0">No recipes found. Start adding your recipes!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth transition for recipe items
    const recipeItems = document.querySelectorAll('.recipe-item');
    recipeItems.forEach(item => {
        item.addEventListener('click', function() {
            const targetId = this.getAttribute('data-bs-target');
            const collapseElement = document.querySelector(targetId);
            
            // Close other open recipes except this one
            document.querySelectorAll('.collapse.show').forEach(openCollapse => {
                if (openCollapse.id !== targetId.substring(1)) {
                    bootstrap.Collapse.getInstance(openCollapse).hide();
                }
            });

            // Toggle current clicked recipe collapse
            if (collapseElement) {
                const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
                if (bsCollapse) {
                    bsCollapse.toggle();
                } else {
                    new bootstrap.Collapse(collapseElement, {toggle: true});
                }
            }
        });
    });

    // Close button logic
    const closeButtons = document.querySelectorAll('.close-recipe');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent toggling recipe open
            const targetId = this.getAttribute('data-bs-target');
            const collapseElement = document.querySelector(targetId);
            if (collapseElement) {
                const bsCollapse = bootstrap.Collapse.getInstance(collapseElement);
                if (bsCollapse) {
                    bsCollapse.hide();
                } else {
                    new bootstrap.Collapse(collapseElement, {toggle: false}).hide();
                }
            }
        });
    });
});

    </script>
</body>

</html>
