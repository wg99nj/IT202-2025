<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../Project/styles.css">
</head>
<body>
<h3>Register</h3>
<!-- HTML5 validation | UCID: wg99 | Date: 2025-07-08 -->
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <!-- HTML5 validation: type=email, required | Date: 2025-07-08 -->
        <input id="email" type="email" name="email" required value="<?php if (isset($_POST['email'])) se($_POST['email']); ?>" />
    </div>
    <div>
        <label for="username">Username</label>
        <!-- HTML5 validation: required, maxlength=30 | Date: 2025-07-08 -->
        <input type="text" name="username" required maxlength="30" value="<?php if (isset($_POST['username'])) se($_POST['username']); ?>" />
    </div>
    <div>
        <label for="pw">Password</label>
        <!-- HTML5 validation: required, minlength=8 | Date: 2025-07-08 -->
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <!-- HTML5 validation: required, minlength=8 | Date: 2025-07-08 -->
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
    <div style="margin-top: 1em;">
        <span>Already have an account?</span>
        <a href="login.php" class="btn-link">Login</a>
    </div>
</form>
<script>
    // JS validation | UCID: wg99 | Date: 2025-07-08
    function validate(form) {
        let email = form.email.value.trim();
        let username = form.username.value.trim();
        let pw = form.password.value;
        let confirm = form.confirm.value;
        let valid = true;
        // Email validation
        if (!email.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
            alert("Please enter a valid email address.");
            valid = false;
        }
        // Username validation
        if (!username.match(/^[a-z0-9_-]+$/)) {
            alert("Username must be lowercase, alphanumerical, and can only contain _ or -");
            valid = false;
        }
        // Password length
        if (pw.length < 8) {
            alert("Password must be at least 8 characters.");
            valid = false;
        }
        // Password match
        if (pw !== confirm) {
            alert("Passwords must match.");
            valid = false;
        }
        return valid;
    }
</script>
<?php
// PHP validation | UCID: wg99 | Date: 2025-07-08
if (isset($_POST["email"], $_POST["password"], $_POST["confirm"], $_POST["username"])) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    // PHP validation steps | Date: 2025-07-08
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
    if (!is_valid_username($username)) {
        flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("Password must not be empty.", "danger");
        $hasError = true;
    }

    if (empty($confirm)) {
        flash("Confirm password must not be empty.", "danger");
        $hasError = true;
    }

    if (!is_valid_password($password)) {
        flash("Password must be at least 8 characters long.", "danger");
        $hasError = true;
    }

    if (!is_valid_confirm($password, $confirm)) {
        flash("Passwords must match.", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        // TODO 4: Hash password and store record in DB
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB(); // available due to the `require()` of `functions.php`
        // Code for inserting user data into the database
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES (:email, :password, :username)");
        try {
            $stmt->execute([':email' => $email, ':password' => $hashed_password, ':username' => $username]);
   
            flash("Successfully registered! You can now log in.", "success");
        } catch(PDOException $e) {
            // Handle duplicate email/username
            users_check_duplicate($e);
        }
        catch (Exception $e) {
            flash("There was an error registering. Please try again.", "danger");
            error_log("Registration Error: " . var_export($e, true)); // log the technical error for debugging
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
reset_session();
?>