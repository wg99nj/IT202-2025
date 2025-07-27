<?php
require(__DIR__ . "/../../../partials/nav.php");

// Only Admins can delete
if (!has_role("Admin")) {
    flash("You don't have permission to delete this record.", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// Get id from query string
$id = intval($_GET["id"] ?? 0);
if (!$id) {
    flash("Invalid country id for deletion.", "danger");
    die(header("Location: list_countries.php"));
}

// Optional: preserve filter/sort/limit for redirect
$filter = $_GET["filter"] ?? "";
$sort = $_GET["sort"] ?? "name";
$order = $_GET["order"] ?? "ASC";
$limit = $_GET["limit"] ?? 10;

$db = getDB();
$stmt = $db->prepare("DELETE FROM country_data WHERE id = :id");
$stmt->bindValue(":id", $id, PDO::PARAM_INT);
try {
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        flash("Country deleted successfully.", "success");
    } else {
        flash("Country not found or already deleted.", "warning");
    }
} catch (Exception $e) {
    flash("Error deleting country: " . $e->getMessage(), "danger");
}

// Redirect back to list page, preserving filter/sort/limit
$redirect = "list_countries.php?filter=" . urlencode($filter) . "&sort=" . urlencode($sort) . "&order=" . urlencode($order) . "&limit=" . urlencode($limit);
die(header("Location: $redirect"));
?>
