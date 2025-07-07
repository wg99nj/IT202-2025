<?php
// UCID: wg99
// Date: 2025-07-07
// Save Recipe handler for API-Powered Recipe Explorer
// Saves a recipe to the user's favorites

require_once('protected.php');
require_once(__DIR__ . '/../../lib/db.php');

$user = $_SESSION['user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipe_id'], $_POST['title'], $_POST['image'])) {
    $recipe_id = $_POST['recipe_id'];
    $title = $_POST['title'];
    $image = $_POST['image'];
    try {
        $db = getDB();
        $stmt = $db->prepare('INSERT IGNORE INTO saved_recipes (user_id, recipe_id, title, image, created, modified) VALUES (:user_id, :recipe_id, :title, :image, NOW(), NOW())');
        $stmt->execute([
            ':user_id' => $user['id'],
            ':recipe_id' => $recipe_id,
            ':title' => $title,
            ':image' => $image
        ]);
        header('Location: favorites.php?saved=1');
        exit;
    } catch (Exception $e) {
        error_log('Save recipe error: ' . $e->getMessage());
        header('Location: favorites.php?error=1');
        exit;
    }
} else {
    header('Location: search.php');
    exit;
}
