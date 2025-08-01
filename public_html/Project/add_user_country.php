<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to add a country.", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$country_id = intval($_GET["country_id"] ?? 0);
if (!$country_id) {
    flash("Invalid country selection.", "danger");
    die(header("Location: my_countries.php"));
}

$db = getDB();
// Check if already associated
$check = $db->prepare("SELECT 1 FROM UserCountry WHERE user_id = :user_id AND country_id = :country_id");
$check->execute([':user_id' => $user_id, ':country_id' => $country_id]);
if ($check->fetchColumn()) {
    flash("Country already in your list.", "info");
    die(header("Location: my_countries.php"));
}
// Insert association
$stmt = $db->prepare("INSERT INTO UserCountry (user_id, country_id) VALUES (:user_id, :country_id)");
try {
    $stmt->execute([':user_id' => $user_id, ':country_id' => $country_id]);
    flash("Country added to your list!", "success");
} catch (Exception $e) {
    flash("Error adding country: " . $e->getMessage(), "danger");
}
die(header("Location: my_countries.php"));
?>
