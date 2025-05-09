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
    <title>Circle Button with Offcanvas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Custom CSS -->
    <link href="../css/off.css" rel="stylesheet">
</head>

<body>

    <!-- Circle Button -->
    <button class="circle-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
        <i class="bi bi-pencil"></i> <!-- Pencil Icon -->
    </button>

    <!-- Offcanvas Content -->
    <div class="offcanvas offcanvas-end" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasScrollingLabel">My Recipes</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h6>Click on a recipe to view its ingredients:</h6>
            <!-- Recipe List -->
            <div id="recipe-list">
                <?php while ($row = $recipes->fetch_assoc()): ?>
                    <div class="recipe-item" data-bs-toggle="collapse" data-bs-target="#recipe-ingredients-<?php echo $row['recipe_id']; ?>" aria-expanded="false" aria-controls="recipe-ingredients-<?php echo $row['recipe_id']; ?>">
                        <h6><?php echo htmlspecialchars($row['title']); ?>
                            <span class="close-recipe" data-bs-toggle="collapse" data-bs-target="#recipe-ingredients-<?php echo $row['recipe_id']; ?>" aria-expanded="false" aria-controls="recipe-ingredients-<?php echo $row['recipe_id']; ?>">X</span>
                        </h6>
                    </div>

                    <!-- Ingredients Collapse Section -->
                    <div id="recipe-ingredients-<?php echo $row['recipe_id']; ?>" class="collapse">
                        <h6>Ingredients:</h6>
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
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <!-- Optional: Use Icons from Bootstrap for the pencil icon -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

</body>

</html>
