<?php
// UCID: wg99
// Date: 2025-07-06
// Logout script for API-Powered Recipe Explorer
// Destroys session and redirects to login with a friendly message

session_start();
// Unset all session variables
$_SESSION = array();
// Destroy the session
session_destroy();
// Redirect to login with a logout message
header("Location: ../login.php?logout=1");
exit;
