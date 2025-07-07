<?php
// UCID: wg99
// Date: 2025-07-07
// My Favorites page for API-Powered Recipe Explorer
// Shows user's saved recipes

require_once('protected.php');
require_once(__DIR__ . '/../../lib/db.php');

$user = $_SESSION['user'];
$saved = isset($_GET['saved']);
$error = isset($_GET['error']);

$recipes = [];
try {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM saved_recipes WHERE user_id = :uid ORDER BY created DESC');
    $stmt->execute([':uid' => $user['id']]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Favorites fetch error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .recipe-grid { display: flex; flex-wrap: wrap; gap: 1.5em; justify-content: center; }
        .recipe-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #b3c2d1; width: 260px; padding: 1em; text-align: center; }
        .recipe-card img { width: 100%; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>My Favorite Recipes</h2>
        <?php if ($saved): ?>
            <div class="success-msg">Recipe saved to favorites!</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-msg">There was a problem saving your recipe.</div>
        <?php endif; ?>
        <?php if ($recipes): ?>
            <div class="recipe-grid">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="recipe-card">
                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Recipe Image">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;">You have no saved recipes yet.</p>
        <?php endif; ?>
        <p style="text-align:center;margin-top:2em;"><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
