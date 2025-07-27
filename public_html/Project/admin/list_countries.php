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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <script>
    // Client-side validation for filter form
    function validateFilterForm() {
        let filter = document.querySelector('input[name="filter"]').value.trim();
        let limit = document.querySelector('input[name="limit"]').value.trim();
        let errors = [];
        if (filter.length > 64) errors.push("Filter text too long.");
        if (limit && (isNaN(limit) || parseInt(limit) < 1 || parseInt(limit) > 100)) errors.push("Limit must be between 1 and 100.");
        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
<div class="container" style="max-width:900px;margin:auto;padding:2em;">
    <h2 class="mb-4">Country List</h2>
    <form method="get" class="row g-3 align-items-center mb-4" onsubmit="return validateFilterForm()">
        <div class="col-auto">
            <input type="text" class="form-control" name="filter" placeholder="Filter by name/capital" value="<?= htmlspecialchars($filter) ?>">
        </div>
        <div class="col-auto">
            <select class="form-select" name="sort">
                <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
                <option value="capital" <?= $sort=="capital"?"selected":"" ?>>Capital</option>
                <option value="population" <?= $sort=="population"?"selected":"" ?>>Population</option>
            </select>
        </div>
        <div class="col-auto">
            <select class="form-select" name="order">
                <option value="ASC" <?= $order=="ASC"?"selected":"" ?>>ASC</option>
                <option value="DESC" <?= $order=="DESC"?"selected":"" ?>>DESC</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="number" class="form-control" name="limit" min="1" max="100" value="<?= $limit ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>
    <?php if (empty($countries)): ?>
        <div class="alert alert-warning">No results available.</div>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
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
                    <td><span class="badge bg-<?= $c["is_api"] ? "info" : "secondary" ?>"><?= $c["is_api"] ? "API" : "Manual" ?></span></td>
                    <td>
                        <a href="edit_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                        <a href="view_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-info me-1">View</a>
                        <a href="delete_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this country?')">Delete</a>
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
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>