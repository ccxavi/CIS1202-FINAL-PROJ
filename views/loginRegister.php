<?php
session_start();

require_once __DIR__ . '/../controllers/userAuthHandler.php';
// redirectIfAuthenticated(); // redirect to dashboard if authenticated

if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    $profilePic = $user['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg';
    
    // If the profile pic path starts with './', convert it to '../'
    if (strpos($profilePic, './') === 0) {
        $profilePic = '../' . substr($profilePic, 2);
    }
    
    // Redirect to home if already logged in
    header('Location: ../index.php');
    exit();
}

// Login form feedback
$login_feedback = null;
$login_email_error = null;
$login_password_error = null;

if (isset($_SESSION['login_feedback'])) {
    $login_feedback = $_SESSION['login_feedback'];
    
    // Determine which field the error belongs to
    if (strpos(strtolower($login_feedback['message']), 'email') !== false) {
        $login_email_error = $login_feedback;
    } else {
        $login_password_error = $login_feedback;
    }
    
    unset($_SESSION['login_feedback']);
}

// Register form feedback
$register_feedback = null;
$register_username_error = null;
$register_email_error = null;
$register_password_error = null;

if (isset($_SESSION['register_feedback'])) {
    $register_feedback = $_SESSION['register_feedback'];
    
    // Determine which field the error belongs to
    if (strpos(strtolower($register_feedback['message']), 'email') !== false) {
        $register_email_error = $register_feedback;
    } elseif (strpos(strtolower($register_feedback['message']), 'username') !== false) {
        $register_username_error = $register_feedback;
    } else {
        $register_password_error = $register_feedback;
    }
    
    unset($_SESSION['register_feedback']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        handleLogin($_POST['email'], $_POST['password']);
        // Re-check for feedback if handleLogin sets it and returns (doesn't exit on redirect)
        if (isset($_SESSION['login_feedback'])) { 
            $login_feedback = $_SESSION['login_feedback'];
            
            // Determine which field the error belongs to
            if (strpos(strtolower($login_feedback['message']), 'email') !== false) {
                $login_email_error = $login_feedback;
            } else {
                $login_password_error = $login_feedback;
            }
            
            unset($_SESSION['login_feedback']);
        }
    } elseif (isset($_POST['register'])) {
        handleRegister($_POST['userNameInput'], $_POST['email'], $_POST['password']);
        // Re-check for feedback if handleRegister sets it and returns
        if (isset($_SESSION['register_feedback'])) {
            $register_feedback = $_SESSION['register_feedback'];
            
            // Determine which field the error belongs to
            if (strpos(strtolower($register_feedback['message']), 'email') !== false) {
                $register_email_error = $register_feedback;
            } elseif (strpos(strtolower($register_feedback['message']), 'username') !== false) {
                $register_username_error = $register_feedback;
            } else {
                $register_password_error = $register_feedback;
            }
            
            unset($_SESSION['register_feedback']);
        }
    }
    // If handlers redirect on error/success, this part might not be reached often for feedback set within handlers
    // The initial check before POST handling is key for messages set before redirect (e.g. from registration to login tab)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/explore-auth.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="logo">
                <h2>Vero</h2>
            </div>
            <div class="links">
                <a href="../index.php"><div class="home">Home</div></a>
                <a href="./explore.php"><div class="explore">Explore</div></a>
                <a href="./guide.php"><div class="guide">Guide</div></a>
            </div>    
        </div>
        <div class="auth" id="auth">
            <a href="#">Login/Register</a>
        </div>
    </header>
    <main>
        <div class="main-container">
            <div class="left-message">
                <h1>
                    Access <br> <span class="highlight">Verified</span> Research<br>
                    with your <br> <span class="highlight">Vero</span> account.
                </h1>
            </div>
            <div class="auth-card">
                <ul class="nav nav-tabs mb-3" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo (!isset($_POST['register']) && !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] === 'login')) || $login_feedback ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo (isset($_POST['register']) || (isset($_GET['tab']) && $_GET['tab'] === 'register')) || $register_feedback ? 'active' : ''; ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Register</button>
                    </li>
                </ul>
                <div class="tab-content" id="authTabContent">
                    <div class="tab-pane fade <?php echo (!isset($_POST['register']) && !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] === 'login')) || $login_feedback ? 'show active' : ''; ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                        <form method="POST" action="loginRegister.php">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <?php if ($login_email_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($login_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                <?php if ($login_password_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($login_password_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="tab-pane fade <?php echo (isset($_POST['register']) || (isset($_GET['tab']) && $_GET['tab'] === 'register')) || $register_feedback ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                        <form method="POST" action="loginRegister.php">
                            <div class="form-group mb-3">
                                <label for="userNameInput" class="form-label">User Name</label>
                                <input type="text" class="form-control" id="userNameInput" name="userNameInput" required>
                                <?php if ($register_username_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($register_username_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mb-3">
                                <label for="registerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="registerEmail" name="email" required>
                                <?php if ($register_email_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($register_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group mb-3">
                                <label for="registerPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="registerPassword" name="password" required>
                                <?php if ($register_password_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($register_password_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <p>© 2025 <strong><em>Vero.</em></strong> All rights reserved.</p>
            <div class="socials">
                <a href="#"><div class="facebook"><i class="bi bi-facebook"></i></div></a>
                <a href="#"><div class="github"><i class="bi bi-github"></i></div></a>
                <a href="#"><div class="x"><i class="bi bi-twitter-x"></i></div></a>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/auth.js"></script>
</body>
</html>