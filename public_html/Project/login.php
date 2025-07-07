<?php
// UCID: wg99
// Date: 2025-07-06
// Login page for API-Powered Recipe Explorer
// Handles user login, validation, and feedback

require_once(__DIR__ . '/../../lib/db.php');
session_start();

// Initialize variables
$login = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // PHP validation
    if (!$login) $errors[] = 'Username or Email is required.';
    if (!$password) $errors[] = 'Password is required.';

    if (!$errors) {
        try {
            $db = getDB();
            $stmt = $db->prepare('SELECT * FROM users WHERE username = :login OR email = :login LIMIT 1');
            $stmt->execute([':login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                // Fetch roles
                $roleStmt = $db->prepare('SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = :uid AND ur.is_active = 1');
                $roleStmt->execute([':uid' => $user['id']]);
                $roles = $roleStmt->fetchAll(PDO::FETCH_COLUMN);
                // Set session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'roles' => $roles
                ];
                header('Location: dashboard.php');
                exit;
            } else {
                if ($user) {
                    $errors[] = 'Incorrect password.';
                } else {
                    $errors[] = 'Account not found.';
                }
            }
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $errors[] = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* (Moved to styles.css for project uniqueness) */
    </style>
    <script>
    // UCID: wg99 | Date: 2025-07-06
    // JS validation for login form
    function validate() {
        let login = document.forms['loginForm']['login'].value.trim();
        let password = document.forms['loginForm']['password'].value;
        let errors = [];
        if (!login) errors.push('Username or Email is required.');
        if (!password) errors.push('Password is required.');
        if (errors.length) {
            alert(errors.join('\n'));
            return false;
        }
        return true;
    }
    </script>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_GET['logout']) && $_GET['logout'] == 1): ?>
            <div class="success-msg">You have been successfully logged out.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="error-msg">
                <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
            </div>
        <?php endif; ?>
        <form name="loginForm" method="post" onsubmit="return validate();" autocomplete="off">
            <div class="form-group">
                <label for="login">Username or Email</label>
                <input type="text" id="login" name="login" required maxlength="100" value="<?php echo htmlspecialchars($login); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required maxlength="60">
            </div>
            <button type="submit">Login</button>
        </form>
        <p style="text-align:center;margin-top:1em;">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
