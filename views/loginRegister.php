<?php
session_start();

require_once __DIR__ . '/../controllers/userAuthHandler.php';
// redirectIfAuthenticated(); // redirect to dashboard if authenticated

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
    <style>
        .auth-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            width: 420px;
            max-width: 100%;
        }
        
        .nav-tabs .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 10px 20px;
            border: none;
            position: relative;
        }
        
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background: transparent;
            border: none;
        }
        
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #0d6efd;
            border-radius: 3px 3px 0 0;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .btn-primary {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            margin-top: 0.5rem;
        }
        
        .feedback-alert {
            padding: 0.5rem 1rem;
            margin-top: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .main-container {
            padding: 2rem 1rem;
        }
        
        @media (min-width: 992px) {
            .auth-card {
                width: 450px;
            }
            
            .main-container {
                padding: 3rem;
            }
        }
    </style>
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
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <?php if ($login_email_error): ?>
                                    <div class="feedback-alert alert alert-<?php echo $login_email_error['type'] === 'danger' ? 'danger' : 'warning'; ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php echo htmlspecialchars($login_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <?php if ($login_password_error): ?>
                                    <div class="feedback-alert alert alert-<?php echo $login_password_error['type'] === 'danger' ? 'danger' : 'warning'; ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php echo htmlspecialchars($login_password_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="keepLoggedIn" name="keepLoggedIn" checked>
                                <label class="form-check-label" for="keepLoggedIn">Keep me Logged in</label>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="tab-pane fade <?php echo (isset($_POST['register']) || (isset($_GET['tab']) && $_GET['tab'] === 'register')) || $register_feedback ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                        <form method="POST" action="loginRegister.php">
                            <div class="mb-3">
                                <label for="userNameInput" class="form-label">User Name</label>
                                <input type="text" class="form-control" id="userNameInput" name="userNameInput" required>
                                <?php if ($register_username_error): ?>
                                    <div class="feedback-alert alert alert-<?php echo $register_username_error['type'] === 'danger' ? 'danger' : 'warning'; ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php echo htmlspecialchars($register_username_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="registerEmail" name="email" required>
                                <?php if ($register_email_error): ?>
                                    <div class="feedback-alert alert alert-<?php echo $register_email_error['type'] === 'danger' ? 'danger' : 'warning'; ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <?php echo htmlspecialchars($register_email_error['message']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="registerPassword" name="password" required>
                                <?php if ($register_password_error): ?>
                                    <div class="feedback-alert alert alert-<?php echo $register_password_error['type'] === 'danger' ? 'danger' : 'warning'; ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
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
    <script>
        // Ensure the correct tab is active based on URL hash or feedback messages
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash) {
                const tabToActivate = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
                if (tabToActivate) {
                    const tab = new bootstrap.Tab(tabToActivate);
                    tab.show();
                }
            }
            // If PHP determined a tab should be active due to feedback, it would have added 'active' class already.
            // This JS part is mainly for the #hash from URL.
            
            // Password length validation
            const registerPassword = document.getElementById('registerPassword');
            const loginPassword = document.getElementById('password');
            
            // Create feedback elements
            const registerFeedback = document.createElement('div');
            registerFeedback.className = 'form-text text-danger mt-1';
            registerFeedback.style.display = 'none';
            registerFeedback.textContent = 'Password must be at least 6 characters.';
            
            const loginFeedback = document.createElement('div');
            loginFeedback.className = 'form-text text-danger mt-1';
            loginFeedback.style.display = 'none';
            loginFeedback.textContent = 'Password must be at least 6 characters.';
            
            // Add feedback elements after password inputs (if they don't already have PHP feedback)
            if (!document.querySelector('#registerPassword + .form-text')) {
                registerPassword.parentNode.insertBefore(registerFeedback, registerPassword.nextSibling);
            }
            
            if (!document.querySelector('#password + .form-text')) {
                loginPassword.parentNode.insertBefore(loginFeedback, loginPassword.nextSibling);
            }
            
            // Validate register password on input
            registerPassword.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    registerFeedback.style.display = 'block';
                } else {
                    registerFeedback.style.display = 'none';
                }
            });
            
            // Validate login password on input
            loginPassword.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 6) {
                    loginFeedback.style.display = 'block';
                } else {
                    loginFeedback.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>