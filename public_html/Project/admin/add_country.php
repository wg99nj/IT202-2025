<?php
require(__DIR__ . "/../../../partials/nav.php");

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
        // API fetch
        $country_code = $_POST["country_code"] ?? "";
        $fields = $_POST["fields"] ?? "name,capital,flag,population,currency,languages,continent";
        $result = fetch_countrywise_data($country_code, $fields);
        if (isset($result["error"])) {
            $message = $result["error"];
        } else {
            $country_api = $result[0] ?? $result;
            $country_api["is_api"] = 1;

            // Flatten values
            if (isset($country_api["capital"]) && is_array($country_api["capital"])) {
                $country_api["capital"] = implode(", ", $country_api["capital"]);
            }
            if (isset($country_api["flag"]) && is_array($country_api["flag"])) {
                $country_api["flag"] = $country_api["flag"]["svg"] ?? json_encode($country_api["flag"]);
            }
            if (isset($country_api["population"]) && is_array($country_api["population"])) {
                $country_api["population"] = $country_api["population"]["value"] ?? json_encode($country_api["population"]);
            }
            if (isset($country_api["languages"]) && is_array($country_api["languages"])) {
                function flatten_languages($arr) {
                    $result = [];
                    foreach ($arr as $item) {
                        if (is_array($item)) {
                            $result = array_merge($result, flatten_languages($item));
                        } elseif (is_object($item)) {
                            $result[] = implode(", ", (array)$item);
                        } elseif (is_string($item)) {
                            $result[] = $item;
                        }
                    }
                    return $result;
                }
                $country_api["languages"] = implode(", ", flatten_languages($country_api["languages"]));
            }
            if (isset($country_api["currency"]) && is_array($country_api["currency"])) {
                $country_api["currency"] = implode(", ", $country_api["currency"]);
            }
            if (isset($country_api["continent"]) && is_array($country_api["continent"])) {
                $country_api["continent"] = implode(", ", $country_api["continent"]);
            }

            try {
                $r = insert("country_data", $country_api, [
                    "update_duplicate" => true,
                    "columns_to_update" => ["capital", "flag", "population", "currency", "languages", "continent"]
                ]);
                $message = $r["lastInsertId"] ? "Inserted/Updated record " . $r["lastInsertId"] : "Error inserting/updating record";
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        // Manual submission
        $name = trim($_POST["name"] ?? "");
        $capital = trim($_POST["capital"] ?? "");
        $flag = trim($_POST["flag"] ?? "");
        $population = $_POST["population"] ?? null;
        $currency = trim($_POST["currency"] ?? "");
        $languages = trim($_POST["languages"] ?? "");
        $continent = trim($_POST["continent"] ?? "");
        $is_api = isset($_POST["is_api"]) ? 1 : 0;

        // PHP VALIDATIONS
        if (!$name) {
            $message = "Country name is required.";
        } elseif (!$capital) {
            $message = "Capital is required.";
        } elseif (!$continent) {
            $message = "Continent is required.";
        } elseif (!is_null($population) && (!is_numeric($population) || $population < 0)) {
            $message = "Population must be a non-negative number.";
        } elseif ($flag && !filter_var($flag, FILTER_VALIDATE_URL)) {
            $message = "Flag must be a valid URL.";
        } elseif (!$currency) {
            $message = "Currency is required.";
        } elseif (!preg_match('/^[A-Za-z,\s]+$/', $currency)) {
            $message = "Currency should contain only letters and commas.";
        } elseif (!$languages) {
            $message = "Languages are required.";
        } elseif (!preg_match('/^[A-Za-z,\s]+$/', $languages)) {
            $message = "Languages should contain only letters and commas.";
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
                $r = insert("country_data", $data, [
                    "update_duplicate" => true,
                    "columns_to_update" => ["capital", "flag", "population", "currency", "languages", "continent"]
                ]);
                $message = $r["lastInsertId"] ? "Inserted/Updated record " . $r["lastInsertId"] : "Error inserting/updating record";
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
    // JS VALIDATION
    function validateForm() {
        const form = document.forms["countryForm"];
        const name = form["name"].value.trim();
        const capital = form["capital"].value.trim();
        const flag = form["flag"].value.trim();
        const population = form["population"].value.trim();
        const currency = form["currency"].value.trim();
        const languages = form["languages"].value.trim();
        const continent = form["continent"].value.trim();

        if (!name) {
            alert("Country name is required.");
            return false;
        }
        if (!capital) {
            alert("Capital is required.");
            return false;
        }
        if (!continent) {
            alert("Continent is required.");
            return false;
        }
        if (population && (isNaN(population) || population < 0)) {
            alert("Population must be a non-negative number.");
            return false;
        }
        if (flag && !/^https?:\/\/.+/.test(flag)) {
            alert("Flag must be a valid URL.");
            return false;
        }
        if (!currency) {
            alert("Currency is required.");
            return false;
        }
        if (!/^[A-Za-z,\s]+$/.test(currency)) {
            alert("Currency should contain only letters and commas.");
            return false;
        }
        if (!languages) {
            alert("Languages are required.");
            return false;
        }
        if (!/^[A-Za-z,\s]+$/.test(languages)) {
            alert("Languages should contain only letters and commas.");
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
<div class="container" style="max-width:700px;margin:auto;padding:2em;">
    <h2 class="text-center mb-4">Add Country Data (Admin Only)</h2>
    <?php if ($message): ?>
        <div class="alert alert-warning text-center mb-3"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <ul class="nav nav-pills justify-content-center mb-4" style="gap:1em;">
        <li class="nav-item"><a class="nav-link active" href="#" onclick="switchTab('manual');styleTabs('manual');" id="tab-manual" style="font-size:1.1em;padding:0.7em 2em;border-radius:8px;">Manual Entry</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="switchTab('api');styleTabs('api');" id="tab-api" style="font-size:1.1em;padding:0.7em 2em;border-radius:8px;transition:background 0.2s;">Fetch from API</a></li>
    </ul>
    <script>
    function styleTabs(active) {
        document.getElementById('tab-manual').classList.remove('active');
        document.getElementById('tab-api').classList.remove('active');
        if (active === 'manual') {
            document.getElementById('tab-manual').classList.add('active');
            document.getElementById('tab-manual').style.background = '#4f7cff';
            document.getElementById('tab-manual').style.color = '#fff';
            document.getElementById('tab-api').style.background = '';
            document.getElementById('tab-api').style.color = '#4f7cff';
        } else {
            document.getElementById('tab-api').classList.add('active');
            document.getElementById('tab-api').style.background = '#4f7cff';
            document.getElementById('tab-api').style.color = '#fff';
            document.getElementById('tab-manual').style.background = '';
            document.getElementById('tab-manual').style.color = '#4f7cff';
        }
    }
    // Initial style
    styleTabs('manual');
    </script>
    <div id="manual" class="tab-target" style="width:100%;display:block;">
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em 2em 1em 2em;max-width:480px;margin:auto;">
            <form name="countryForm" method="post" onsubmit="return validateForm();" class="d-flex flex-column gap-3">
                <input type="hidden" name="action" value="manual">
                <div>
                    <label for="name" class="form-label">Country Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div>
                    <label for="capital" class="form-label">Capital:</label>
                    <input type="text" id="capital" name="capital" class="form-control" required>
                </div>
                <div>
                    <label for="flag" class="form-label">Flag URL:</label>
                    <input type="text" id="flag" name="flag" class="form-control">
                </div>
                <div>
                    <label for="population" class="form-label">Population:</label>
                    <input type="number" id="population" name="population" class="form-control" min="0">
                </div>
                <div>
                    <label for="currency" class="form-label">Currency:</label>
                    <input type="text" id="currency" name="currency" class="form-control" required>
                </div>
                <div>
                    <label for="languages" class="form-label">Languages (comma separated):</label>
                    <input type="text" id="languages" name="languages" class="form-control" required>
                </div>
                <div>
                    <label for="continent" class="form-label">Continent:</label>
                    <input type="text" id="continent" name="continent" class="form-control" required>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="is_api" name="is_api" value="1" class="form-check-input">
                    <label for="is_api" class="form-check-label">Is API Data</label>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100" style="font-size:1.1em;">Save Country</button>
                </div>
            </form>
        </div>
    </div>
    <div id="api" class="tab-target" style="width:100%;display:none;">
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em 2em 1em 2em;max-width:480px;margin:auto;">
            <form name="apiForm" method="post" class="d-flex flex-column gap-3">
                <input type="hidden" name="action" value="api">
                <div>
                    <label for="country_code" class="form-label">Country Code (e.g., gb, us, ca):</label>
                    <input type="text" id="country_code" name="country_code" class="form-control" required>
                </div>
                <div>
                    <label for="fields" class="form-label">Fields (comma separated):</label>
                    <input type="text" id="fields" name="fields" class="form-control" value="name,capital,flag,population,currency,languages,continent">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100" style="font-size:1.1em;">Fetch & Save Country</button>
                </div>
            </form>
            <?php if (!empty($country_api)): ?>
                <h4 class="mt-4">Fetched Country Data:</h4>
                <pre style="background:#f7faff;border-radius:8px;padding:1em;max-height:300px;overflow:auto;"><?= htmlspecialchars(json_encode($country_api, JSON_PRETTY_PRINT)) ?></pre>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function switchTab(tab) {
    document.getElementById('manual').style.display = tab === 'manual' ? '' : 'none';
    document.getElementById('api').style.display = tab === 'api' ? '' : 'none';
}
</script>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
