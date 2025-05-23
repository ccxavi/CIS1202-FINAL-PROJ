<?php

require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../config/databaseConnection.php';

// Helper function to set feedback messages in session
function set_feedback($type, $message, $form_type) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Ensure session is started
    }
    $_SESSION[$form_type . '_feedback'] = ['type' => $type, 'message' => $message];
}

function handleRegister($userName, $email, $password)
{
    // Sanitize Inputs
    $userName = htmlspecialchars(trim($userName));
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

    // Validation of Data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_feedback('danger', 'Invalid email format', 'register');
        return;
    }

    if (strlen($userName) < 2) {
        set_feedback('danger', 'Username must be at least 2 characters.', 'register');
        return;
    }

    if (strlen($password) < 6) {
        set_feedback('danger', 'Password must be at least 6 characters.', 'register');
        return;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (findUserByEmail($email)) {
        set_feedback('danger', 'Email already exists.', 'register');
        return;
    }

    if (register($userName, $email, $hashedPassword)) {
        // On successful registration, redirect to login with a success message
        // Or directly log them in if desired (not current behavior)
        set_feedback('success', 'User registered successfully. Please log in.', 'login');
        header("Location: ../views/loginRegister.php#login"); // Redirect to login tab
        exit();
    } else {
        set_feedback('danger', 'Error signing up. Please try again.', 'register');
    }
}

function handleLogin($email, $password)
{
    try {
        $user = findUserByEmail($email);
        
        if (!$user) {
            $_SESSION['login_feedback'] = [
                'type' => 'error',
                'message' => 'No account found with this email.'
            ];
            return;
        }
        
        if (!password_verify($password, $user['password'])) {
            $_SESSION['login_feedback'] = [
                'type' => 'error',
                'message' => 'Incorrect password.'
            ];
            return;
        }
        
        // Login successful
        $_SESSION['userID'] = $user['id'];
        $_SESSION['userName'] = $user['username'];
        
        // Handle remember me
        if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $user_id = $user['id'];
            
            // Store the token in the database
            global $pdo;
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?");
            $stmt->execute([$token, $user_id]);
            
            // Set a secure cookie that expires in 30 days
            $cookie_options = array(
                'expires' => time() + (30 * 24 * 60 * 60),  // 30 days
                'path' => '/',
                'secure' => true,     // Only send over HTTPS
                'httponly' => true,   // Not accessible via JavaScript
                'samesite' => 'Strict'
            );
            setcookie('remember_token', $token, $cookie_options);
            setcookie('user_id', $user_id, $cookie_options);
        }
        
        // Redirect to home page
        header('Location: ../index.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['login_feedback'] = [
            'type' => 'error',
            'message' => 'An error occurred during login. Please try again.'
        ];
        error_log($e->getMessage());
    }
}

function handleSignOut(){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Clear remember me token if it exists
    if (isset($_SESSION['userID'])) {
        clearRememberMe($_SESSION['userID']);
    }
    
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    // Determine if we're in the root directory or in a subdirectory
    $script_path = $_SERVER['SCRIPT_NAME'];
    $redirect_path = (strpos($script_path, '/views/') !== false) ? 
        './loginRegister.php' : // If in views directory, use relative path
        './views/loginRegister.php'; // If in root directory, include views directory
    
    header("Location: $redirect_path");
    exit();
}

function isAuthenticated()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['userID']);
}

function redirectIfAuthenticated()
{
    if (isAuthenticated()) {
        header("Location: ./index.php"); // Adjusted path for use in views folder
        exit();
    }
}

// Add this new function to check for remember me cookie
function checkRememberMe() {
    if (!isset($_COOKIE['remember_token']) || !isset($_COOKIE['user_id'])) {
        return false;
    }
    
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND remember_token = ? AND token_expiry > NOW()");
        $stmt->execute([$_COOKIE['user_id'], $_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Auto login the user
            $_SESSION['userID'] = $user['id'];
            $_SESSION['userName'] = $user['username'];
            
            // Refresh the remember me token and cookie
            $new_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?");
            $stmt->execute([$new_token, $user['id']]);
            
            $cookie_options = array(
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            );
            setcookie('remember_token', $new_token, $cookie_options);
            
            return true;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
    
    // Clear invalid cookies
    setcookie('remember_token', '', time() - 3600, '/');
    setcookie('user_id', '', time() - 3600, '/');
    return false;
}

// Add this function to clear remember me when logging out
function clearRememberMe($user_id) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Clear cookies
        setcookie('remember_token', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function isAdmin() {
    if (!isset($_SESSION['userID'])) {
        return false;
    }

    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['userID']]);
        $user = $stmt->fetch();
        
        return $user && $user['role'] === 'admin';
    } catch (PDOException $e) {
        error_log("Error checking admin status: " . $e->getMessage());
        return false;
    }
}
?>