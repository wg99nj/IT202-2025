<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("landing.php")));
}
//attempt to apply
if (isset($_POST["users"], $_POST["roles"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $role_ids = $_POST["roles"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($role_ids)) {
        flash("Both users and roles need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient (normally bulk operations should fail/pass together)
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO UserRoles (user_id, role_id, is_active) VALUES (:uid, :rid, 1) 
        ON DUPLICATE KEY UPDATE is_active = !is_active");

        // triggers 1 query per pair, that way an exception will only affect that pair rather than the bulk operation
        foreach ($user_ids as $uid) {
            foreach ($role_ids as $rid) {
                try {
                    $stmt->execute([":uid" => $uid, ":rid" => $rid]);
                    if ($stmt->rowCount() > 0) {
                        flash("Toggled role for user $uid and role $rid", "success");
                    } else {
                        flash("No changes made for user $uid and role $rid", "warning");
                    }
                } catch (PDOException $e) {
                    flash("There was an error toggling the role, please try again later", "danger");
                    error_log("Error toggling role for user $uid and role $rid: " . var_export($e->errorInfo, true));
                }
            }
        }
    }
}



//search for user by username
$users = [];
$active_roles = [];
$username = "";
if (isset($_POST["username"])) {

    $username = trim(se($_POST, "username", "", false));
    if (!empty($username)) {
        //get active roles only if a username was submitted
        $active_roles = [];
        $db = getDB();
        $stmt = $db->prepare("SELECT id, name, description FROM Roles WHERE is_active = 1 LIMIT 10");
        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $active_roles = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
        //fetch usernames with a csv of roles and their active status
        // Note: the role status will show inactive only if the role has been assigned at least once
        // We're effectively doing a soft delete by toggling `is_active` to 0.
        // Alternatively, we could simply delete the UserRole entry, but that would lose history.
        $stmt = $db->prepare("SELECT Users.id, username, 
        (SELECT GROUP_CONCAT(name, ' (' , IF(ur.is_active = 1,'active','inactive') , ')') from 
        UserRoles ur 
        JOIN Roles on ur.role_id = Roles.id 
        WHERE ur.user_id = Users.id) as roles
        from Users WHERE username like :username");
        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}


?>
<h3>Assign Roles</h3>
<!-- search form -->
<form method="POST">
    <input type="search" name="username" placeholder="Username search" value="<?php se($username, false); ?>" />
    <input type="submit" value="Search" />
</form>
<!-- toggle form, inputs will use the form attribute to associate with this form -->
<form id="toggleForm" method="POST" style="display:none;"></form>
<?php if (isset($username) && !empty($username)) : ?>
    <input form="toggleForm" type="hidden" name="username" value="<?php se($username, false); ?>" />
<?php endif; ?>
<table>
    <thead>
        <th>Users</th>
        <th>Roles to Assign</th>
    </thead>
    <tbody>
        <tr>
            <td style="vertical-align:top;width:50%;padding-right:1em;">
                <!-- User list -->
                <div class="border rounded p-2 mb-3" style="max-height:250px;overflow:auto;background:#fff;">
                    <?php foreach ($users as $user) : ?>
                        <div class="form-check mb-2">
                            <input form="toggleForm" id="user_<?php se($user, 'id'); ?>" type="checkbox" name="users[]" value="<?php se($user, 'id'); ?>" class="form-check-input" />
                            <label form="toggleForm" for="user_<?php se($user, 'id'); ?>" class="form-check-label"><?php se($user, "username"); ?></label>
                            <span class="ms-2 text-muted" style="font-size:0.9em;">(<?php se($user, "roles", "No Roles"); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
            <td style="vertical-align:top;width:50%;padding-left:1em;">
                <!-- Roles list -->
                <div class="border rounded p-2 mb-3" style="max-height:250px;overflow:auto;background:#fff;">
                    <?php foreach ($active_roles as $role) : ?>
                        <div class="form-check mb-2">
                            <input form="toggleForm" id="role_<?php se($role, 'id'); ?>" type="checkbox" name="roles[]" value="<?php se($role, 'id'); ?>" class="form-check-input" />
                            <label form="toggleForm" for="role_<?php se($role, 'id'); ?>" class="form-check-label"><?php se($role, "name"); ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<div style="width:100%;display:flex;justify-content:center;margin-top:2em;">
    <input form="toggleForm" type="submit" value="Toggle Roles" />
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>