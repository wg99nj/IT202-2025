<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h3>Register</h3>
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required />
    </div>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" required maxlength="30" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation (you'll do this on your own towards the end of Milestone1)
        //ensure it returns false for an error and true for success

        return true;
    }
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"], $_POST["password"], $_POST["confirm"], $_POST["username"])) {

    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    // TODO 3: validate/use
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
<form onsubmit="return validate(this)" method="POST" novalidate>
    <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required pattern="^[^@\s]+@[^@\s]+\.[^@\s]+$" maxlength="100" />
    </div>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required maxlength="30" pattern="^[a-z0-9_-]+$" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" maxlength="60" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" id="confirm" required minlength="8" maxlength="60" />
    </div>
    <input type="submit" value="Register" />
</form>
<script src="helpers.js"></script>
<script>
    // UCID: wg99 | Date: 2025-07-08 | JS validation for registration form
    function validate(form) {
        let email = form.email.value.trim();
        let username = form.username.value.trim();
        let pw = form.password.value;
        let confirm = form.confirm.value;
        let valid = true;
        // Email validation
        if (!email.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
            flash("Please enter a valid email address.", "danger");
            valid = false;
        }
        // Username validation
        if (!username.match(/^[a-z0-9_-]+$/)) {
            flash("Username must be lowercase, alphanumerical, and can only contain _ or -", "danger");
            valid = false;
        }
        // Password length
        if (pw.length < 8) {
            flash("Password must be at least 8 characters.", "danger");
            valid = false;
        }
        // Password match
        if (pw !== confirm) {
            flash("Passwords must match.", "danger");
            valid = false;
        }
        return valid;
    }
</script>
<?php
// UCID: wg99 | Date: 2025-07-08 | PHP registration logic
// ...existing code...