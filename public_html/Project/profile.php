<?php
// UCID: wg99
// Date: 2025-07-07
// User Profile page for API-Powered Recipe Explorer
// Shows logged-in user's info and allows email/password update (basic)

require_once(__DIR__ . '/../../../lib/db.php');
require_once('protected.php'); // Ensures user is logged in

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch user info
try {
    $db = getDB();
    $stmt = $db->prepare('SELECT username, email FROM users WHERE id = :id');
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();
    if (!$user) throw new Exception('User not found.');
} catch (Exception $e) {
    $error = 'Could not load profile.';
}

// Handle email/password update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email'] ?? '');
    $new_pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $updates = [];
    if ($new_email && $new_email !== $user['email']) {
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            $updates['email'] = $new_email;
        }
    }
    if ($new_pass) {
        if (strlen($new_pass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $updates['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
        }
    }
    if (!$error && $updates) {
        $set = [];
        $params = [':id' => $user_id];
        foreach ($updates as $k => $v) {
            $set[] = "$k = :$k";
            $params[":$k"] = $v;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $set) . ', modified = NOW() WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $success = 'Profile updated!';
        // Refresh user info
        $stmt = $db->prepare('SELECT username, email FROM users WHERE id = :id');
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile - Recipe Explorer</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="profile-container">
    <h2>Your Profile</h2>
    <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="form-group">
            <label>Username</label>
            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="100" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" minlength="6" maxlength="60">
        </div>
        <div class="form-group">
            <label for="confirm">Confirm New Password</label>
            <input type="password" id="confirm" name="confirm" minlength="6" maxlength="60">
        </div>
        <button type="submit">Update Profile</button>
    </form>
    <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
</div>
</body>
</html>
