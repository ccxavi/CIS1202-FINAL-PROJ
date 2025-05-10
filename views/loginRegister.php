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
        <link rel="stylesheet" href="../assets/css/global.css">
        <link rel="stylesheet" href="../assets/css/header.css">
        <link rel="stylesheet" href="../assets/css/main.css">
        <link rel="stylesheet" href="../assets/css/footer.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="logo">
                <h2>Vero</h2>
            </div>
            <div class="links">
                <a href="#"><div class="home">Home</div></a>
                <a href="./index.php"><div class="explore">Explore</div></a>
                <a href="./views/guide.php"><div class="guide">Guide</div></a>
            </div>    
        </div>
        <div class="auth" id="auth">
            <a href="#">Login/Register</a>
        </div>
    </header>

    <main>

    </main>

    <footer>

    </footer>
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