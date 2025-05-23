<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check for remember me cookie and perform auto-login if needed
if (!isAuthenticated() && checkRememberMe()) {
    // User has been automatically logged in
    // The checkRememberMe function has already set up the session
    
    // Get the current page URL
    $current_page = $_SERVER['PHP_SELF'];
    
    // If we're on the login page, redirect to index
    if (strpos($current_page, 'loginRegister.php') !== false) {
        header('Location: ../index.php');
        exit();
    }
} 