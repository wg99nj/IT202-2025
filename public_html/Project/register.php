<?php
// UCID: wg99
// Date: 2025-07-06
// Registration page for API-Powered Recipe Explorer
// Handles user registration, validation, and feedback

require_once(__DIR__ . '/../../lib/db.php');

// Initialize variables
$username = $email = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // PHP validation
    if (!$username) $errors[] = 'Username is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (!$password) $errors[] = 'Password is required.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    // Check for unique username/email if no errors
    if (!$errors) {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
            $stmt->execute([':username' => $username, ':email' => $email]);
            $exists = $stmt->fetchColumn();
            if ($exists) {
                // Check which is taken
                $stmt2 = $db->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email');
                $stmt2->execute([':username' => $username, ':email' => $email]);
                $row = $stmt2->fetch();
                if ($row) {
                    if ($row['username'] === $username) $errors[] = 'Username is already taken.';
                    if ($row['email'] === $email) $errors[] = 'Email is already registered.';
                }
            }
        } catch (Exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred. Please try again later.';
        }
    }

    // Insert user if no errors
    if (!$errors) {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare('INSERT INTO users (username, email, password, created, modified) VALUES (:username, :email, :password, NOW(), NOW())');
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash
            ]);
            // Assign default 'user' role
            $user_id = $db->lastInsertId();
            $roleStmt = $db->prepare('SELECT id FROM roles WHERE name = :role LIMIT 1');
            $roleStmt->execute([':role' => 'user']);
            $role = $roleStmt->fetch();
            if ($role) {
                $db->prepare('INSERT INTO user_roles (user_id, role_id, is_active, created, modified) VALUES (:user_id, :role_id, 1, NOW(), NOW())')
                   ->execute([':user_id' => $user_id, ':role_id' => $role['id']]);
            }
            $success = 'Registration successful! You may now <a href="login.php">login</a>.';
            $username = $email = '';
        } catch (Exception $e) {
            error_log('Registration insert error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Extra styling for registration form */
        /* (Moved to styles.css for project uniqueness) */
    </style>
    <script>
    // UCID: wg99 | Date: 2025-07-06
    // JS validation for registration form
    function validate() {
        let username = document.forms['regForm']['username'].value.trim();
        let email = document.forms['regForm']['email'].value.trim();
        let password = document.forms['regForm']['password'].value;
        let confirm = document.forms['regForm']['confirm'].value;
        let errors = [];
        if (!username) errors.push('Username is required.');
        if (!email || !/^\S+@\S+\.\S+$/.test(email)) errors.push('A valid email is required.');
        if (!password) errors.push('Password is required.');
        if (password.length < 6) errors.push('Password must be at least 6 characters.');
        if (password !== confirm) errors.push('Passwords do not match.');
        if (errors.length) {
            let msg = errors.join('\n');
            alert(msg);
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
    <div class="register-container">
        <h2>Create Your Account</h2>
        <?php if ($errors): ?>
            <div class="error-msg">
                <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php endif; ?>
        <form name="regForm" method="post" onsubmit="return validate();" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="32" value="<?php echo htmlspecialchars($username); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required maxlength="100" value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" maxlength="60">
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" required minlength="6" maxlength="60">
            </div>
            <button type="submit">Register</button>
        </form>
        <p style="text-align:center;margin-top:1em;">Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
