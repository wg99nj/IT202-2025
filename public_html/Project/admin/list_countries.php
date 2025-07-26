<?php
require(__DIR__ . "/../../../partials/nav.php");


// Admin-only access (adjust as needed for other roles)
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}

// Handle filters, sorting, and pagination
$limit = max(1, min(100, intval($_GET["limit"] ?? 10)));
$sort = $_GET["sort"] ?? "name";
$order = $_GET["order"] ?? "ASC";
$filter = $_GET["filter"] ?? "";

$db = getDB();
$where = "";
$params = [];
if ($filter) {
    $where = "WHERE name LIKE :filter OR capital LIKE :filter";
    $params[":filter"] = "%$filter%";
}
$query = "SELECT * FROM country_data $where ORDER BY $sort $order LIMIT :limit";
$stmt = $db->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Country List (Admin)</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<div class="container" style="max-width:900px;margin:auto;padding:2em;">
    <h2>Country List</h2>
    <form method="get" style="margin-bottom:1em;">
        <input type="text" name="filter" placeholder="Filter by name/capital" value="<?= htmlspecialchars($filter) ?>">
        <select name="sort">
            <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
            <option value="capital" <?= $sort=="capital"?"selected":"" ?>>Capital</option>
            <option value="population" <?= $sort=="population"?"selected":"" ?>>Population</option>
        </select>
        <select name="order">
            <option value="ASC" <?= $order=="ASC"?"selected":"" ?>>ASC</option>
            <option value="DESC" <?= $order=="DESC"?"selected":"" ?>>DESC</option>
        </select>
        <input type="number" name="limit" min="1" max="100" value="<?= $limit ?>">
        <button type="submit">Apply</button>
    </form>
    <?php if (empty($countries)): ?>
        <div>No results available.</div>
    <?php else: ?>
        <table border="1" cellpadding="6" style="width:100%;background:#fff;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Capital</th>
                    <th>Population</th>
                    <th>Currency</th>
                    <th>API/Manual</th>
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
                    <td><?= $c["is_api"] ? "API" : "Manual" ?></td>
                    <td>
                        <a href="edit_country.php?id=<?= $c["id"] ?>">Edit</a> |
                        <a href="view_country.php?id=<?= $c["id"] ?>">View</a> |
                        <a href="delete_country.php?id=<?= $c["id"] ?>" onclick="return confirm('Delete this country?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>