<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to remove all countries.", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$db = getDB();
$stmt = $db->prepare("DELETE FROM UserCountry WHERE user_id = :user_id");
try {
    $stmt->execute([':user_id' => $user_id]);
    if ($stmt->rowCount() > 0) {
        flash("All country associations removed from your list.", "success");
    } else {
        flash("No countries were associated with your list.", "info");
    }
} catch (Exception $e) {
    flash("Error removing all associations: " . $e->getMessage(), "danger");
}
die(header("Location: my_countries.php"));
?>
