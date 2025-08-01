<?php
require(__DIR__ . "/../../../partials/nav.php");


// Admin-only access (adjust as needed for other roles)
if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
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
<body style="background: #f4f6fc;">
<div class="container" style="max-width:1000px;margin:auto;padding:2em;">
    <h2 class="mb-4 text-center fw-bold" style="font-size:2.2rem;">Country List</h2>
    <div class="d-flex justify-content-center mb-4">
        <div class="card shadow-sm p-4" style="min-width:340px;max-width:400px;background:#fff;border-radius:18px;">
            <form method="get" onsubmit="return validateFilterForm()">
                <div class="mb-3">
                    <input type="text" class="form-control" name="filter" placeholder="Filter by name/capital" value="<?= htmlspecialchars($filter) ?>">
                </div>
                <div class="mb-3">
                    <select class="form-select" name="sort">
                        <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
                        <option value="capital" <?= $sort=="capital"?"selected":"" ?>>Capital</option>
                        <option value="population" <?= $sort=="population"?"selected":"" ?>>Population</option>
                    </select>
                </div>
                <div class="mb-3">
                    <select class="form-select" name="order">
                        <option value="ASC" <?= $order=="ASC"?"selected":"" ?>>ASC</option>
                        <option value="DESC" <?= $order=="DESC"?"selected":"" ?>>DESC</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" name="limit" min="1" max="100" value="<?= $limit ?>">
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">Apply</button>
                </div>
            </form>
        </div>
    </div>
    <?php if (empty($countries)): ?>
        <div class="alert alert-warning text-center">No results available.</div>
    <?php else: ?>
        <div class="card shadow-sm p-3 mb-4" style="border-radius:18px;background:#fff;">
            <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white" style="border-radius:12px;overflow:hidden;">
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
                    <tr style="transition:background 0.2s;" onmouseover="this.style.background='#f0f4ff'" onmouseout="this.style.background='#fff'">
                        <td><?= htmlspecialchars($c["name"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["capital"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["population"] ?? "") ?></td>
                        <td><?= htmlspecialchars($c["currency"] ?? "") ?></td>
                        <td><span class="badge bg-<?= $c["is_api"] ? "info" : "secondary" ?>" style="font-size:1em;padding:0.5em 1em;border-radius:12px;"><?= $c["is_api"] ? "API" : "Manual" ?></span></td>
                        <td>
                            <?php if (has_role('Admin')): ?>
                                <a href="edit_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-warning rounded-pill shadow-sm me-1">Edit</a>
                                <a href="view_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-info rounded-pill shadow-sm me-1">View</a>
                                <a href="delete_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-danger rounded-pill shadow-sm me-1" onclick="return confirm('Delete this country?')">Delete</a>
                            <?php else: ?>
                                <a href="view_country.php?id=<?= $c["id"] ?>" class="btn btn-sm btn-info rounded-pill shadow-sm me-1">View</a>
                                <?php
                                $user_id = get_user_id();
                                $db2 = getDB();
                                $check_stmt = $db2->prepare("SELECT 1 FROM UserCountry WHERE user_id = :user_id AND country_id = :country_id");
                                $check_stmt->execute([':user_id' => $user_id, ':country_id' => $c["id"]]);
                                $is_associated = $check_stmt->fetchColumn();
                                ?>
                                <?php if ($is_associated): ?>
                                    <span class="badge bg-success" style="font-size:1em;padding:0.5em 1em;border-radius:12px;">Added</span>
                                <?php else: ?>
                                    <a href="/Project/add_user_country.php?country_id=<?= $c["id"] ?>" class="btn btn-sm btn-primary rounded-pill shadow-sm" onclick="return confirm('Add this country to your countries?')">Add</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>