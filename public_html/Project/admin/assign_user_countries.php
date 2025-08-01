<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page.", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

// Handle search
$user_search = $_GET["user_search"] ?? "";
$country_search = $_GET["country_search"] ?? "";

// Fetch users (partial match, max 25)
$user_query = "SELECT id, username FROM Users WHERE username LIKE :user_search ORDER BY username LIMIT 25";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindValue(":user_search", "%$user_search%", PDO::PARAM_STR);
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch countries (partial match, max 25)
$country_query = "SELECT id, name FROM country_data WHERE name LIKE :country_search ORDER BY name LIMIT 25";
$country_stmt = $db->prepare($country_query);
$country_stmt->bindValue(":country_search", "%$country_search%", PDO::PARAM_STR);
$country_stmt->execute();
$countries = $country_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle association/disassociation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_ids"]) && isset($_POST["country_ids"])) {
    $user_ids = $_POST["user_ids"];
    $country_ids = $_POST["country_ids"];
    $action = $_POST["action"] ?? "associate";
    $count = 0;
    foreach ($user_ids as $uid) {
        foreach ($country_ids as $cid) {
            if ($action === "associate") {
                // Only insert if not already associated
                $check = $db->prepare("SELECT 1 FROM UserCountry WHERE user_id = :uid AND country_id = :cid");
                $check->execute([':uid' => $uid, ':cid' => $cid]);
                if (!$check->fetch()) {
                    $ins = $db->prepare("INSERT INTO UserCountry (user_id, country_id) VALUES (:uid, :cid)");
                    $ins->execute([':uid' => $uid, ':cid' => $cid]);
                    $count++;
                }
            } elseif ($action === "disassociate") {
                $del = $db->prepare("DELETE FROM UserCountry WHERE user_id = :uid AND country_id = :cid");
                $del->execute([':uid' => $uid, ':cid' => $cid]);
                $count++;
            }
        }
    }
    if ($action === "associate") {
        flash("Associated $count user-country pairs.", "success");
    } else {
        flash("Disassociated $count user-country pairs.", "success");
    }
    die(header("Location: " . $_SERVER["REQUEST_URI"]));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assign User-Country Associations (Admin)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= get_url("styles.css") ?>">
</head>
<body>
<div class="container" style="max-width:900px;margin:auto;padding:2em;">
    <h2 class="mb-4 text-center">Assign User-Country Associations</h2>
    <div class="assign-main-box" style="width:100%;display:flex;flex-direction:column;align-items:center;">
        <form method="get" class="row g-3 align-items-end mb-4 assign-box-form" style="width:100%;max-width:500px;">
            <div class="col-md-12">
                <label class="form-label">User (partial match)</label>
                <input type="text" class="form-control" name="user_search" placeholder="Search users" value="<?= htmlspecialchars($user_search) ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label">Country (partial match)</label>
                <input type="text" class="form-control" name="country_search" placeholder="Search countries" value="<?= htmlspecialchars($country_search) ?>">
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
        <form method="post" style="width:100%;margin-top:1.5em;">
            <div class="row" style="width:100%;">
                <div class="col-md-6">
                    <h5>Users</h5>
                    <div class="border rounded p-2 mb-3" style="max-height:350px;overflow:auto;">
                        <?php if (empty($users)): ?>
                            <div class="text-muted">No users found.</div>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="user_ids[]" value="<?= $u['id'] ?>" id="user<?= $u['id'] ?>">
                                    <label class="form-check-label" for="user<?= $u['id'] ?>">
                                        <?= htmlspecialchars($u['username']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Countries</h5>
                    <div class="border rounded p-2 mb-3" style="max-height:350px;overflow:auto;">
                        <?php if (empty($countries)): ?>
                            <div class="text-muted">No countries found.</div>
                        <?php else: ?>
                            <?php foreach ($countries as $c): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="country_ids[]" value="<?= $c['id'] ?>" id="country<?= $c['id'] ?>">
                                    <label class="form-check-label" for="country<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3 justify-content-center">
                <button type="submit" name="action" value="associate" class="btn btn-success">Associate Selected</button>
                <button type="submit" name="action" value="disassociate" class="btn btn-danger">Disassociate Selected</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
