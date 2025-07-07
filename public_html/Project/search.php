<?php
// UCID: wg99
// Date: 2025-07-07
// Recipe Search page for API-Powered Recipe Explorer
// Fetches recipes from Spoonacular API and displays results

require_once('protected.php');

$results = [];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = trim($_POST['query'] ?? '');
    if ($query) {
        $apiKey = 'YOUR_SPOONACULAR_API_KEY'; // Replace with your API key
        $url = "https://api.spoonacular.com/recipes/complexSearch?query=" . urlencode($query) . "&number=10&addRecipeInformation=true&apiKey=$apiKey";
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['results'])) {
                $results = $data['results'];
            } else {
                $error = 'No recipes found.';
            }
        } else {
            $error = 'Error fetching recipes. Please try again.';
        }
    } else {
        $error = 'Please enter a search term.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Recipes - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .recipe-grid { display: flex; flex-wrap: wrap; gap: 1.5em; justify-content: center; }
        .recipe-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #b3c2d1; width: 260px; padding: 1em; text-align: center; }
        .recipe-card img { width: 100%; border-radius: 8px; }
        .save-btn { margin-top: 0.7em; background: #4caf50; color: #fff; border: none; border-radius: 5px; padding: 0.5em 1em; cursor: pointer; }
        .save-btn:hover { background: #388e3c; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Search Recipes</h2>
        <form method="post" style="margin-bottom:2em;">
            <input type="text" name="query" placeholder="Search for recipes..." value="<?php echo htmlspecialchars($_POST['query'] ?? ''); ?>" style="width:70%;padding:0.6em;">
            <button type="submit">Search</button>
        </form>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($results): ?>
            <div class="recipe-grid">
                <?php foreach ($results as $recipe): ?>
                    <div class="recipe-card">
                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Recipe Image">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($recipe['summary'], 0, 100)); ?>...</p>
                        <form method="post" action="save_recipe.php">
                            <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe['id']); ?>">
                            <input type="hidden" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>">
                            <input type="hidden" name="image" value="<?php echo htmlspecialchars($recipe['image']); ?>">
                            <button type="submit" class="save-btn">Save to Favorites</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
