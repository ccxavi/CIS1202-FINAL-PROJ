<?php

require_once __DIR__ . '/../models/UserModel.php';


function handleRegister($userName, $email, $password)
{
    // Sanitize Inputs
    $userName = htmlspecialchars(trim($userName));
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

    // Validation of Data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        return;
    }

    if (strlen($userName) < 2) {
        echo "Username must be at least 2 characters.";
        return;
    }

    if (strlen($password) < 6) {
        echo "Password must be at least 6 characters.";
        return;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if (findUserByEmail($email)) {
        echo "Email already exists.";
        return;
    }

    if (register($userName, $email, $hashedPassword)) {
        echo "User registered successfully.";
        header("Location: ../views/loginRegister.php"); // Redirect to sign-in page
        exit();
    } else {
        echo "Error signing up. Please try again.";
    }
}

function handleLogin($email, $password)
{
    // Sanitize Inputs
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

    // Validation of Data
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        return;
    }

    if (strlen($password) < 4) {
        echo "Password must be at least 4 characters.";
        return;
    }

    $user = findUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['userID'] = $user['id'];
        header("Location: ../index.php"); // Redirect dashboard
        exit();
    } else {
        echo "Invalid email or password.";
    }
}

function handleSignOut(){
    
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    header("Location: /VeriFind/index.php");
    exit();

}

function isAuthenticated()
{
    return isset($_SESSION['userID']);
}

function redirectIfAuthenticated()
{
    if (isAuthenticated()) {
        header("Location: /VeriFind/index.php");
        exit();
    }
}
?>