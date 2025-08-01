<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view your countries.", "warning");
    die(header("Location: login.php"));
}

$user_id = get_user_id();
$db = getDB();

// Handle filter, sort, and limit
$filter = $_GET["filter"] ?? "";
$sort = $_GET["sort"] ?? "name";
$order = $_GET["order"] ?? "ASC";
$limit = max(1, min(100, intval($_GET["limit"] ?? 10)));

$where = "";
$params = [":user_id" => $user_id];
if ($filter) {
    $where = "AND (c.name LIKE :filter OR c.capital LIKE :filter)";
    $params[":filter"] = "%$filter%";
}

$query = "SELECT c.* FROM UserCountry uc JOIN country_data c ON uc.country_id = c.id WHERE uc.user_id = :user_id $where ORDER BY c.$sort $order LIMIT :limit";
$stmt = $db->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for stats
$count_query = "SELECT COUNT(*) FROM UserCountry WHERE user_id = :user_id";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute([':user_id' => $user_id]);
$total_count = $count_stmt->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Countries</title>
    <link rel="stylesheet" href="../Project/styles.css">
</head>
<body>
<div class="container" style="max-width:900px;margin:auto;padding:2em;">
    <h2 class="text-center mb-4">My Countries</h2>
    <div class="d-flex flex-wrap justify-content-center align-items-start gap-4 mb-4">
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em 2em 1em 2em;min-width:320px;max-width:340px;flex:1;">
            <form method="get" class="d-flex flex-column gap-3">
                <div>
                    <input type="text" class="form-control" name="filter" placeholder="Filter by name/capital" value="<?= htmlspecialchars($filter) ?>">
                </div>
                <div>
                    <select class="form-select" name="sort">
                        <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
                        <option value="capital" <?= $sort=="capital"?"selected":"" ?>>Capital</option>
                        <option value="population" <?= $sort=="population"?"selected":"" ?>>Population</option>
                    </select>
                </div>
                <div>
                    <select class="form-select" name="order">
                        <option value="ASC" <?= $order=="ASC"?"selected":"" ?>>ASC</option>
                        <option value="DESC" <?= $order=="DESC"?"selected":"" ?>>DESC</option>
                    </select>
                </div>
                <div>
                    <input type="number" class="form-control" name="limit" min="1" max="100" value="<?= $limit ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
        <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px 0 rgba(0,0,0,0.08);padding:2em;min-width:260px;max-width:300px;flex:1;display:flex;flex-direction:column;align-items:center;gap:1.2em;">
            <div>
                <strong>Total Associated:</strong> <?= $total_count ?> |
                <strong>Showing:</strong> <?= count($countries) ?>
            </div>
        </div>
    </div>
    <?php if (empty($countries)): ?>
        <div class="alert alert-warning">No results available.</div>
        <div style="margin-top:1em; text-align:center;">
            <a href="admin/list_countries.php" class="btn btn-primary" style="font-size:1.15em;padding:0.7em 2em;border-radius:8px;box-shadow:0 2px 8px rgba(76,70,255,0.08);text-decoration:none;">Browse all countries to add</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Capital</th>
                        <th>Population</th>
                        <th>Currency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($countries as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c["name"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["capital"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["population"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["currency"] ?? "") ?></td>
                        <td>
                            <a href="view_country.php?id=<?= $c["id"] ?>">View</a> |
                            <a href="remove_user_country.php?country_id=<?= $c["id"] ?>" onclick="return confirm('Remove this association?')">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <form method="post" action="remove_all_user_countries.php" style="margin-top:1em;">
            <button type="submit" class="btn btn-danger" onclick="return confirm('Remove all associations?')">Remove All Associations</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>
