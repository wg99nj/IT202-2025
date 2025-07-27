<?php
require(__DIR__ . "/../../../partials/nav.php");

// Admin-only access
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// Retrieve and validate ID from GET
$id = intval($_GET["id"] ?? 0);
if (!$id) {
    flash("Invalid country id", "danger");
    die(header("Location: list_countries.php"));
}

// Fetch the country record
$db = getDB();
$stmt = $db->prepare("SELECT * FROM country_data WHERE id = :id");
$stmt->bindValue(":id", $id, PDO::PARAM_INT);
$stmt->execute();
$country = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$country) {
    flash("Country not found", "danger");
    die(header("Location: list_countries.php"));
}

// Handle update submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // PHP Validation
    $name = trim($_POST["name"] ?? "");
    $capital = trim($_POST["capital"] ?? "");
    $flag = trim($_POST["flag"] ?? "");
    $population = $_POST["population"] ?? null;
    $currency = trim($_POST["currency"] ?? "");
    $languages = trim($_POST["languages"] ?? "");
    $continent = trim($_POST["continent"] ?? "");
    $is_api = isset($_POST["is_api"]) ? 1 : 0;

    if (!$name || !$currency || !$languages || $population === "" || $population < 0 || !is_numeric($population)) {
        $message = "Please fill in all required fields correctly.";
    } else {
        $data = [
            "id" => $id,
            "name" => $name,
            "capital" => $capital,
            "flag" => $flag,
            "population" => $population,
            "currency" => $currency,
            "languages" => $languages,
            "continent" => $continent,
            "is_api" => $is_api
        ];
        try {
            $r = update("country_data", $data, ["id"]);
            if ($r["rowCount"] > 0) {
                $message = "Country updated successfully.";
                // Reload updated record
                $stmt->execute();
                $country = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $message = "No changes made.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Country (Admin)</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
    function validateEditForm() {
        let name = document.forms["editForm"]["name"].value.trim();
        let currency = document.forms["editForm"]["currency"].value.trim();
        let languages = document.forms["editForm"]["languages"].value.trim();
        let population = document.forms["editForm"]["population"].value;

        let errors = [];

        if (!name) errors.push("Country name is required.");
        if (!currency) errors.push("Currency is required.");
        if (!languages) errors.push("Languages are required.");
        if (population === "" || isNaN(population) || Number(population) < 0) {
            errors.push("Population must be a non-negative number.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
<div class="container" style="max-width:700px;margin:auto;padding:2em;">
    <h2>Edit Country</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form name="editForm" method="post" onsubmit="return validateEditForm();">
        <label for="name">Country Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($country["name"] ?? "") ?>" required><br>

        <label for="capital">Capital:</label>
        <input type="text" id="capital" name="capital" value="<?= htmlspecialchars($country["capital"] ?? "") ?>"><br>

        <label for="flag">Flag URL:</label>
        <input type="text" id="flag" name="flag" value="<?= htmlspecialchars($country["flag"] ?? "") ?>"><br>

        <label for="population">Population:</label>
        <input type="number" id="population" name="population" min="0" value="<?= htmlspecialchars($country["population"] ?? "") ?>" required><br>

        <label for="currency">Currency:</label>
        <input type="text" id="currency" name="currency" value="<?= htmlspecialchars($country["currency"] ?? "") ?>" required><br>

        <label for="languages">Languages (comma separated):</label>
        <input type="text" id="languages" name="languages" value="<?= htmlspecialchars($country["languages"] ?? "") ?>" required><br>

        <label for="continent">Continent:</label>
        <input type="text" id="continent" name="continent" value="<?= htmlspecialchars($country["continent"] ?? "") ?>"><br>

        <label for="is_api">Is API Data:</label>
        <input type="checkbox" id="is_api" name="is_api" value="1" <?= $country["is_api"] ? "checked" : "" ?>><br>

        <button type="submit">Update Country</button>
    </form>
    <a href="list_countries.php">← Back to List</a>
</div>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>