<?php
session_start();

require_once __DIR__ . '/../controllers/userAuthHandler.php';
// redirectIfAuthenticated(); // redirect to dashboard if authenticated

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        handleLogin($_POST['email'], $_POST['password']);
    } elseif (isset($_POST['register'])) {
        handleRegister($_POST['userNameInput'], $_POST['email'], $_POST['password']);
    }}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>

    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/global.css">

    <link rel="stylesheet" href="../assets/css/loginRegister.css">
</head>
<body>
    <a href="../index.php">Go Back</a>
    <div class="card login-card">
    <div class="card">
        <form method="POST">
            <h1>Log In</h1>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit" name="login">Log In</button>
        </form>
    </div>
    </div>
    <div class="card signup-card">
    <form method="POST">
            <h1>Register</h1>
            <label for="userNameInput">User Name:</label>
            <input type="text" id="userNameInput" name="userNameInput" required>
            <br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <button type="submit" name="register">Register</button>
    </div>
</body>
</html>