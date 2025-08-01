<?php
require(__DIR__ . "/../../../partials/nav.php");

// Get and validate the id from the query string
$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($id <= 0) {
    flash("Invalid or missing country ID.", "danger");
    die(header("Location: " . get_url("list_countries.php")));
}

// Load the record from the database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM country_data WHERE id = :id");
$stmt->execute([":id" => $id]);
$country = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$country) {
    flash("Country not found.", "danger");
    die(header("Location: " . get_url("list_countries.php")));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Country Details</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container" style="max-width:700px;margin:auto;padding:2em;">
    <h2>Country Details</h2>
    <table class="table table-bordered" style="background:#fff;">
        <tr><th>Name</th><td><?= htmlspecialchars($country["name"]) ?></td></tr>
        <tr><th>Capital</th><td><?= htmlspecialchars($country["capital"]) ?></td></tr>
        <tr><th>Flag</th><td>
            <?php if (filter_var($country["flag"], FILTER_VALIDATE_URL)): ?>
                <img src="<?= htmlspecialchars($country["flag"]) ?>" alt="Flag" style="height:40px;">
            <?php else: ?>
                <?= htmlspecialchars($country["flag"]) ?>
            <?php endif; ?>
        </td></tr>
        <tr><th>Population</th><td><?= htmlspecialchars($country["population"]) ?></td></tr>
        <tr><th>Currency</th><td><?= htmlspecialchars($country["currency"]) ?></td></tr>
        <tr><th>Languages</th><td><?= htmlspecialchars($country["languages"]) ?></td></tr>
        <tr><th>Continent</th><td><?= htmlspecialchars($country["continent"]) ?></td></tr>
        <tr><th>Source</th><td><?= $country["is_api"] ? "API" : "Manual" ?></td></tr>
    </table>
    <a href="edit_country.php?id=<?= $country["id"] ?>" class="btn btn-primary">Edit</a>
    <a href="delete_country.php?id=<?= $country["id"] ?>" class="btn btn-danger" onclick="return confirm('Delete this country?')">Delete</a>
    <a href="list_countries.php" class="btn btn-secondary">Back to List</a>
        <a href="../my_countries.php" class="btn btn-secondary">Back to My Countries</a>
</div>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
