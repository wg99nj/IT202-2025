<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../Project/styles.css">
</head>
<body>
<h3>Login</h3>
<!-- UCID: wg99 | Date: 2025-07-08 | HTML5 validation for login form -->
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <input type="submit" value="Login" />
    <div style="margin-top: 1em;">
        <span>Don't have an account?</span>
        <a href="register.php" class="btn-link">Register</a>
    </div>
</form>
<script>
    // UCID: wg99 | Date: 2025-07-08 | JS validation for login form
    function validate(form) {
        let email = form.email.value.trim();
        let pw = form.password.value;
        let valid = true;
        if (!email.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
            alert("Please enter a valid email address.");
            valid = false;
        }
        if (pw.length < 8) {
            alert("Password must be at least 8 characters.");
            valid = false;
        }
        return valid;
    }
</script>

<?php
// UCID: wg99 | Date: 2025-07-08 | PHP validation for login form
if (isset($_POST["email"], $_POST["password"])) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $hasError = false;

    if (empty($email)) {
        flash("Email must not be empty.", "danger");
        $hasError = true;
    }
    // Sanitize and validate email
    $email = sanitize_email($email);
    if (!is_valid_email($email)) {
        flash("Invalid email address.", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("Password must not be empty.", "danger");
        $hasError = true;
    }

    if (!is_valid_password($password)) {
        //echo "Password too short<br>";
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        // Check password and fetch user
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, password, username from Users where email = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $ambigify = false; // flag to indicate ambiguous login attempt (reduce TMI)
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        $_SESSION["user"] = $user; // add the data to the active session
                        try {
                            //lookup potential roles
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles
                            JOIN UserRoles on Roles.id = UserRoles.role_id
                            where UserRoles.user_id = :user_id and Roles.is_active = 1 
                            and UserRoles.is_active = 1");
                            $stmt->execute([":user_id" =>get_user_id()]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
                        //save roles or empty array
                        $_SESSION["user"]["roles"] = isset($roles)?$roles:[];
                        die(header("Location: landing.php"));
                    } else {
                        $ambigify = true; // ambiguous login attempt
                    }
                } else {
                    $ambigify = true; // ambiguous login attempt
                }
                if ($ambigify) {
                    flash("Invalid login attempt. Please check your email and password.", "danger");
                }
            }
        } catch (Exception $e) {
            flash("There was an error logging in. Please try again later.", "danger");
            error_log("Login Error: " . var_export($e, true));
        }
    }
}
?>


<?php
require(__DIR__ . "/../../partials/flash.php");
?>