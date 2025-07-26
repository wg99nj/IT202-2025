<?php
require(__DIR__ . "/../../../partials/nav.php");


if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    flash("Invalid country id", "danger");
    die(header("Location: list_countries.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM country_data WHERE id = :id");
$stmt->bindValue(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$country = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$country) {
    flash("Country not found", "danger");
    die(header("Location: list_countries.php"));
}

// Handle form submission for editing
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = [
        "id" => $id,
        "name" => $_POST["name"] ?? $country["name"],
        "capital" => $_POST["capital"] ?? $country["capital"],
        "flag" => $_POST["flag"] ?? $country["flag"],
        "population" => $_POST["population"] ?? $country["population"],
        "currency" => $_POST["currency"] ?? $country["currency"],
        "languages" => $_POST["languages"] ?? $country["languages"],
        "continent" => $_POST["continent"] ?? $country["continent"],
        "is_api" => isset($_POST["is_api"]) ? 1 : 0
    ];
    try {
        $r = update("country_data", $data, ["id"], ["debug"=>false]);
        if ($r["rowCount"] > 0) {
            $message = "Country updated successfully.";
            // Refresh country data
            $stmt->execute();
            $country = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "No changes made.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Country (Admin)</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container" style="max-width:700px;margin:auto;padding:2em;">
    <h2>Edit Country</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="name">Country Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($country["name"] ?? "") ?>" required><br>
        <label for="capital">Capital:</label>
        <input type="text" id="capital" name="capital" value="<?= htmlspecialchars($country["capital"] ?? "") ?>"><br>
        <label for="flag">Flag URL:</label>
        <input type="text" id="flag" name="flag" value="<?= htmlspecialchars($country["flag"] ?? "") ?>"><br>
        <label for="population">Population:</label>
        <input type="number" id="population" name="population" min="0" value="<?= htmlspecialchars($country["population"] ?? "") ?>"><br>
        <label for="currency">Currency:</label>
        <input type="text" id="currency" name="currency" value="<?= htmlspecialchars($country["currency"] ?? "") ?>"><br>
        <label for="languages">Languages (comma separated):</label>
        <input type="text" id="languages" name="languages" value="<?= htmlspecialchars($country["languages"] ?? "") ?>"><br>
        <label for="continent">Continent:</label>
        <input type="text" id="continent" name="continent" value="<?= htmlspecialchars($country["continent"] ?? "") ?>"><br>
        <label for="is_api">Is API Data:</label>
        <input type="checkbox" id="is_api" name="is_api" value="1" <?= $country["is_api"] ? "checked" : "" ?>><br>
        <button type="submit">Update Country</button>
    </form>
    <a href="list_countries.php">Back to List</a>
</div>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>