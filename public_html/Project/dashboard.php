<?php
// UCID: wg99
// Date: 2025-07-07
// Dashboard page for API-Powered Recipe Explorer
// Shows welcome message and navigation links

require_once('protected.php');
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <ul style="list-style:none;padding:0;text-align:center;">
            <li style="margin:1em 0;"><a href="search.php">Search Recipes</a></li>
            <li style="margin:1em 0;"><a href="favorites.php">My Favorites</a></li>
            <li style="margin:1em 0;"><a href="logout.php">Logout</a></li>
        </ul>
    </div>
</body>
</html>
