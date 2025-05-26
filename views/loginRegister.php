<?php
if (session_status() === PHP_SESSION_NONE) {
session_start();
}

require_once __DIR__ . '/../includes/autoLogin.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
// redirectIfAuthenticated(); // redirect to dashboard if authenticated

// No need to check remember me here as it's handled in autoLogin.php
if(isAuthenticated()){
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
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
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
                <div class="tab-content" id="authTabContent">
                    <div class="tab-pane fade <?php echo (!isset($_POST['register']) && !isset($_GET['tab']) || (isset($_GET['tab']) && $_GET['tab'] === 'login')) || $login_feedback ? 'show active' : ''; ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                        <div class="text-center mb-5">
                            <img src="../assets/img/logo.png" alt="Vero Logo" class="login-logo mb-4">
                            <h2 class="mb-2">Welcome back</h2>
                            <p class="text-muted">Use your Vero Account</p>
                        </div>
                        <form method="POST" action="loginRegister.php">
                            <div class="form-group">
                                <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="Email" required>
                                <?php if ($login_email_error): ?>
                                    <div class="validation-feedback <?php echo $login_email_error['type']; ?>">
                                        <i class="bi <?php echo $login_email_error['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
                                        <?php echo htmlspecialchars($login_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <div class="password-container">
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password" required>
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                <?php if ($login_password_error): ?>
                                    <div class="validation-feedback <?php echo $login_password_error['type']; ?>">
                                        <i class="bi <?php echo $login_password_error['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
                                        <?php echo htmlspecialchars($login_password_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-options mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" value="1">
                                    <label class="form-check-label" for="rememberMe">Stay signed in</label>
                                </div>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" class="forgot-link">Forgot password?</a>
                            </div>
                            <div class="form-actions">
                                <div class="helper-text mb-4">
                                    <p class="text-muted">Don't have an account? <a href="?tab=register" class="text-decoration-none">Create account</a></p>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary btn-lg w-100">Login</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade <?php echo (isset($_POST['register']) || (isset($_GET['tab']) && $_GET['tab'] === 'register')) || $register_feedback ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                        <div class="text-center mb-5">
                            <img src="../assets/img/logo.png" alt="Vero Logo" class="login-logo mb-4">
                            <h2 class="mb-2">Create your account</h2>
                            <p class="text-muted">Join the Vero community</p>
                        </div>
                        <form method="POST" action="loginRegister.php">
                            <div class="form-group">
                                <input type="text" class="form-control form-control-lg" id="userNameInput" name="userNameInput" placeholder="Username" required>
                                <?php if ($register_username_error): ?>
                                    <div class="validation-feedback <?php echo $register_username_error['type']; ?>">
                                        <i class="bi <?php echo $register_username_error['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
                                        <?php echo htmlspecialchars($register_username_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-lg" id="registerEmail" name="email" placeholder="Email" required>
                                <?php if ($register_email_error): ?>
                                    <div class="validation-feedback <?php echo $register_email_error['type']; ?>">
                                        <i class="bi <?php echo $register_email_error['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?>"></i>
                                        <?php echo htmlspecialchars($register_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <div class="password-container">
                                    <input type="password" class="form-control form-control-lg" id="registerPassword" name="password" placeholder="Password" required>
                                    <button type="button" class="password-toggle" id="toggleRegisterPassword">
                                        <i class="bi bi-eye-slash"></i>
                                    </button>
                                </div>
                                <?php if ($register_password_error): ?>
                                    <div class="validation-feedback">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <?php echo htmlspecialchars($register_password_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-options mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required>
                                    <label class="form-check-label" for="agreeTerms">
                                        I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="privacy-link">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="helper-text mb-4">
                                    <p class="text-muted">Already have an account? <a href="?tab=login" class="text-decoration-none">Login instead</a></p>
                                </div>
                                <button type="submit" name="register" class="btn btn-primary btn-lg w-100">Create Account</button>
                            </div>
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

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Password Reset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="reset-guide text-center">
                        <div class="shield-icon mb-3">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h4 class="mb-3">Forgot your password?</h4>
                        <p class="mb-4">Don't worry! We're here to help you regain access to your account securely.</p>
                        <div class="guide-steps">
                            <p>To reset your password, please follow these steps:</p>
                            <ol>
                                <li>Contact our support team at <a href="mailto:support.vero@ph.com">support.vero@ph.com</a></li>
                                <li>Include the following information in your email:
                                    <ul>
                                        <li>Your registered username</li>
                                        <li>Your email address</li>
                                    </ul>
                                </li>
                                <li>Use "Password Reset Request" as your email subject line</li>
                            </ol>
                            <p>Our support team will assist you with the password reset process.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="mailto:support.vero@ph.com?subject=Password Reset Request" class="btn btn-primary">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>