<?php
// UCID: wg99
// Date: 2025-07-07
// Protected page logic for API-Powered Recipe Explorer
// Use: include this file at the top of any page that requires login

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// User is logged in, continue...
