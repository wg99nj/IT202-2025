<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page.", "warning");
    die(header("Location: " . get_url("landing.php")));
}

$db = getDB();


// Filters and sorting
$country = $_GET["country"] ?? "";
$validSorts = ["c.name", "c.id"];
$validOrders = ["ASC", "DESC"];
$sort = in_array($_GET["sort"] ?? "", $validSorts) ? $_GET["sort"] : "c.name";
$order = in_array($_GET["order"] ?? "", $validOrders) ? $_GET["order"] : "ASC";
$limit = max(1, min(100, intval($_GET["limit"] ?? 10)));

$where = "WHERE uc.country_id IS NULL";
$params = [];
if ($country) {
    $where .= " AND c.name LIKE :country";
    $params[":country"] = "%$country%";
}

// Query for countries not associated with any user
$query = "SELECT c.* FROM country_data c LEFT JOIN UserCountry uc ON c.id = uc.country_id $where ORDER BY $sort $order LIMIT :limit";
$stmt = $db->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for stats
$count_query = "SELECT COUNT(*) FROM country_data c LEFT JOIN UserCountry uc ON c.id = uc.country_id $where";
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
    <title>Unassociated Countries (Admin)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= get_url("styles.css") ?>">
</head>
<body>
<div class="container" style="max-width:900px;margin:auto;padding:2em;">
    <h2 class="text-center mb-4">Countries Not Associated With Any User</h2>
    <div class="d-flex flex-wrap justify-content-center align-items-start gap-4 mb-4">
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em 2em 1em 2em;min-width:320px;max-width:340px;flex:1;">
            <form method="get" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Country Name</label>
                    <input type="text" class="form-control" name="country" placeholder="Filter by country name" value="<?= htmlspecialchars($country) ?>">
                </div>
                <div>
                    <label class="form-label">Sort</label>
                    <select class="form-select" name="sort">
                        <option value="c.name" <?= $sort == "c.name" ? "selected" : "" ?>>Country Name</option>
                        <option value="c.id" <?= $sort == "c.id" ? "selected" : "" ?>>Country ID</option>
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
                <strong>Total Unassociated Countries:</strong> <?= $total_count ?> |
                <strong>Showing:</strong> <?= count($rows) ?>
            </div>
        </div>
    </div>
    <?php if (empty($rows)): ?>
        <div class="alert alert-warning">No results available.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Country Name</th>
                        <th>Country ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["name"] ?? "") ?></td>
                        <td><?= htmlspecialchars($row["id"] ?? "") ?></td>
                        <td>
                            <a href="view_country.php?id=<?= $row["id"] ?>" class="btn btn-sm btn-info">View</a>
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
