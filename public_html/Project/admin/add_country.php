<?php
require(__DIR__ . "/../../../partials/nav.php");
require(__DIR__ . "/../../../lib/Api_countrywise.php");
require(__DIR__ . "/../../../lib/db.php");
require(__DIR__ . "/../../../lib/db_helpers.php");

// Admin authorization check
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$message = "";
$country_api = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "manual";
    if ($action === "api") {
        $country_code = $_POST["country_code"] ?? "";
        $fields = $_POST["fields"] ?? "name,capital,flag,population,currency,languages,continent";
        $result = fetch_countrywise_data($country_code, $fields);
        if (isset($result["error"])) {
            $message = $result["error"];
        } else {
            $country_api = $result[0] ?? $result; // API returns array of countries
            $country_api["is_api"] = 1;
            // Insert or update using db_helpers.php
            try {
                $r = insert("country_data", $country_api, ["update_duplicate"=>true, "columns_to_update"=>["capital","flag","population","currency","languages","continent"]]);
                if ($r["lastInsertId"]) {
                    $message = "Inserted/Updated record " . $r["lastInsertId"];
                } else {
                    $message = "Error inserting/updating record";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $name = $_POST["name"] ?? "";
        $capital = $_POST["capital"] ?? "";
        $flag = $_POST["flag"] ?? "";
        $population = $_POST["population"] ?? null;
        $currency = $_POST["currency"] ?? "";
        $languages = $_POST["languages"] ?? "";
        $continent = $_POST["continent"] ?? "";
        $is_api = isset($_POST["is_api"]) ? 1 : 0;
        // Basic validation
        if (!$name) {
            $message = "Country name is required.";
        } else {
            $data = [
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
                $r = insert("country_data", $data, ["update_duplicate"=>true, "columns_to_update"=>["capital","flag","population","currency","languages","continent"]]);
                if ($r["lastInsertId"]) {
                    $message = "Inserted/Updated record " . $r["lastInsertId"];
                } else {
                    $message = "Error inserting/updating record";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Country Data (Admin)</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
    function validateForm() {
        let name = document.forms["countryForm"]["name"].value;
        if (!name) {
            alert("Country name is required.");
            return false;
        }
        // Add more JS validation as needed
        return true;
    }
    </script>
</head>
<body>
<div class="container" style="max-width:700px;margin:auto;padding:2em;">
    <h2>Add Country Data (Admin Only)</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" href="#" onclick="switchTab('manual')">Manual Entry</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('api')">Fetch from API</a></li>
    </ul>
    <div id="manual" class="tab-target">
        <form name="countryForm" method="post" onsubmit="return validateForm();">
            <input type="hidden" name="action" value="manual">
            <label for="name">Country Name:</label>
            <input type="text" id="name" name="name" required><br>
            <label for="capital">Capital:</label>
            <input type="text" id="capital" name="capital"><br>
            <label for="flag">Flag URL:</label>
            <input type="text" id="flag" name="flag"><br>
            <label for="population">Population:</label>
            <input type="number" id="population" name="population" min="0"><br>
            <label for="currency">Currency:</label>
            <input type="text" id="currency" name="currency"><br>
            <label for="languages">Languages (comma separated):</label>
            <input type="text" id="languages" name="languages"><br>
            <label for="continent">Continent:</label>
            <input type="text" id="continent" name="continent"><br>
            <label for="is_api">Is API Data:</label>
            <input type="checkbox" id="is_api" name="is_api" value="1"><br>
            <button type="submit">Save Country</button>
        </form>
    </div>
    <div id="api" class="tab-target" style="display:none;">
        <form name="apiForm" method="post">
            <input type="hidden" name="action" value="api">
            <label for="country_code">Country Code (e.g., gb, us, ca):</label>
            <input type="text" id="country_code" name="country_code" required><br>
            <label for="fields">Fields (comma separated):</label>
            <input type="text" id="fields" name="fields" value="name,capital,flag,population,currency,languages,continent"><br>
            <button type="submit">Fetch & Save Country</button>
        </form>
        <?php if (!empty($country_api)): ?>
            <h4>Fetched Country Data:</h4>
            <pre><?= htmlspecialchars(json_encode($country_api, JSON_PRETTY_PRINT)) ?></pre>
        <?php endif; ?>
    </div>
</div>
<script>
function switchTab(tab) {
    let manual = document.getElementById('manual');
    let api = document.getElementById('api');
    if (tab === 'manual') {
        manual.style.display = '';
        api.style.display = 'none';
    } else {
        manual.style.display = 'none';
        api.style.display = '';
    }
}
function validateForm() {
    let name = document.forms["countryForm"]["name"].value;
    if (!name) {
        alert("Country name is required.");
        return false;
    }
    // Add more JS validation as needed
    return true;
}
</script>
</body>
</html>
