<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to remove a country.", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$country_id = intval($_GET["country_id"] ?? 0);
if (!$country_id) {
    flash("Invalid country selection.", "danger");
    die(header("Location: my_countries.php"));
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM UserCountry WHERE user_id = :user_id AND country_id = :country_id");
try {
    $stmt->execute([':user_id' => $user_id, ':country_id' => $country_id]);
    if ($stmt->rowCount() > 0) {
        flash("Country removed from your list.", "success");
    } else {
        flash("Country was not in your list.", "info");
    }
} catch (Exception $e) {
    flash("Error removing country: " . $e->getMessage(), "danger");
}
die(header("Location: my_countries.php"));
?>
