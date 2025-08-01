
<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page.", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();

// Filters and sorting
$username = $_GET["username"] ?? "";
$country = $_GET["country"] ?? "";
$validSorts = ["uc.created", "u.username", "c.name"];
$validOrders = ["ASC", "DESC"];
$sort = in_array($_GET["sort"] ?? "", $validSorts) ? $_GET["sort"] : "uc.created";
$order = in_array($_GET["order"] ?? "", $validOrders) ? $_GET["order"] : "DESC";
$limit = max(1, min(100, intval($_GET["limit"] ?? 10)));

$where = "WHERE 1=1";
$params = [];
if ($username) {
    $where .= " AND u.username LIKE :username";
    $params[":username"] = "%$username%";
}
if ($country) {
    $where .= " AND c.name LIKE :country";
    $params[":country"] = "%$country%";
}

$query = "SELECT uc.*, u.username, c.name as country_name, c.id as country_id, (
    SELECT COUNT(*) FROM UserCountry WHERE country_id = uc.country_id
) as user_count
FROM UserCountry uc
JOIN Users u ON uc.user_id = u.id
JOIN country_data c ON uc.country_id = c.id
$where
ORDER BY $sort $order
LIMIT :limit";
$stmt = $db->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for stats
$count_query = "SELECT COUNT(*) FROM UserCountry uc JOIN Users u ON uc.user_id = u.id JOIN country_data c ON uc.country_id = c.id $where";
$count_stmt = $db->prepare($count_query);
foreach ($params as $k => $v) {
    $count_stmt->bindValue($k, $v);
}
$count_stmt->execute();
$total_count = $count_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All User-Country Associations (Admin)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= get_url("styles.css") ?>">
</head>
<body>

<div class="container" style="max-width:1100px;margin:auto;padding:2em;">
    <h2 class="text-center mb-4">All User-Country Associations</h2>
    <div class="d-flex flex-wrap justify-content-center align-items-start gap-4 mb-4">
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em 2em 1em 2em;min-width:320px;max-width:340px;flex:1;">
            <form method="get" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Filter by username" value="<?= htmlspecialchars($username) ?>">
                </div>
                <div>
                    <label class="form-label">Country</label>
                    <input type="text" class="form-control" name="country" placeholder="Filter by country name" value="<?= htmlspecialchars($country) ?>">
                </div>
                <div>
                    <label class="form-label">Sort</label>
                    <select class="form-select" name="sort">
                        <option value="uc.created" <?= $sort == "uc.created" ? "selected" : "" ?>>Date</option>
                        <option value="u.username" <?= $sort == "u.username" ? "selected" : "" ?>>Username</option>
                        <option value="c.name" <?= $sort == "c.name" ? "selected" : "" ?>>Country</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Order</label>
                    <select class="form-select" name="order">
                        <option value="ASC" <?= $order == "ASC" ? "selected" : "" ?>>ASC</option>
                        <option value="DESC" <?= $order == "DESC" ? "selected" : "" ?>>DESC</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Limit</label>
                    <input type="number" class="form-control" name="limit" min="1" max="100" value="<?= $limit ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em;min-width:260px;max-width:300px;flex:1;display:flex;flex-direction:column;align-items:center;gap:1.2em;">
            <div>
                <strong>Total Associations:</strong> <?= $total_count ?> |
                <strong>Showing:</strong> <?= count($rows) ?>
            </div>
            <form method="post" onsubmit="return confirm('Are you sure you want to remove ALL user-country associations? This cannot be undone.');" style="margin:0;">
                <input type="hidden" name="remove_all" value="1">
                <button type="submit" class="btn btn-danger" style="width:100%;font-weight:500;">Remove All Associations</button>
            </form>
            <form method="post" onsubmit="return confirm('Are you sure you want to remove ALL filtered user-country associations? This cannot be undone.');" style="margin:0;">
                <input type="hidden" name="remove_filtered" value="1">
                <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
                <input type="hidden" name="country" value="<?= htmlspecialchars($country) ?>">
                <button type="submit" class="btn btn-warning" style="width:100%;font-weight:500;">Remove All Filtered</button>
            </form>
        </div>
    </div>

<?php
// Handle remove all associations POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["remove_all"])) {
        $db->query("DELETE FROM UserCountry");
        flash("All user-country associations have been removed.", "success");
        die(header("Location: " . $_SERVER["REQUEST_URI"]));
    }
    if (isset($_POST["remove_filtered"])) {
        $username = $_POST["username"] ?? "";
        $country = $_POST["country"] ?? "";
        $where = "WHERE 1=1";
        $params = [];
        if ($username) {
            $where .= " AND u.username LIKE :username";
            $params[":username"] = "%$username%";
        }
        if ($country) {
            $where .= " AND c.name LIKE :country";
            $params[":country"] = "%$country%";
        }
        // Delete associations matching the filter
        $delete_query = "DELETE uc FROM UserCountry uc JOIN Users u ON uc.user_id = u.id JOIN country_data c ON uc.country_id = c.id $where";
        $delete_stmt = $db->prepare($delete_query);
        foreach ($params as $k => $v) {
            $delete_stmt->bindValue($k, $v);
        }
        $delete_stmt->execute();
        flash("All filtered user-country associations have been removed.", "success");
        die(header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')));
    }
}
?>

<?php if (empty($rows)): ?>
        <div class="alert alert-warning">No results available.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Country</th>
                        <th>Date Associated</th>
                        <th>User Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><a href="profile.php?id=<?= $row["user_id"] ?>"><?= htmlspecialchars($row["username"] ?? "") ?></a></td>
                        <td><a href="view_country.php?id=<?= $row["country_id"] ?>"><?= htmlspecialchars($row["country_name"] ?? "") ?></a></td>
                        <td><?= htmlspecialchars($row["created"] ?? "") ?></td>
                        <td><?= htmlspecialchars($row["user_count"] ?? "") ?></td>
                        <td>
                            <a href="remove_user_country.php?country_id=<?= $row["country_id"] ?>&user_id=<?= $row["user_id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this association?')">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

<?php require(__DIR__ . "/../../../partials/flash.php"); ?>
