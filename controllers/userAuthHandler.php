<?php

require_once __DIR__ . '/../models/userModel.php';

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
    // Sanitize Inputs
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

    // Validation of Data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_feedback('danger', 'Invalid email format', 'login');
        return;
    }

    // Password length check on login is unusual, usually just check credentials
    // For consistency with previous logic, keeping a generic check. Better: remove if not truly needed.
    if (empty($password)) { // Changed from strlen < 4 to just empty check
        set_feedback('danger', 'Password is required.', 'login');
        return;
    }

    $user = findUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start(); // Ensure session is started before regenerating ID
        }
        session_regenerate_id(true);
        $_SESSION['userID'] = $user['id'];
        header("Location: ../index.php"); // Redirect dashboard
        exit();
    } else {
        set_feedback('danger', 'Invalid email or password.', 'login');
    }
}

function handleSignOut(){
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    header("Location: ./index.php");
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
?>