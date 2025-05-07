<?php
session_start();

require_once __DIR__ . '/config/databaseConnection.php';
require_once __DIR__ . '/controllers/userAuthHandler.php';

if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);

    
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // fallback if null
}


if (isset($_POST['logout'])) {
    handleSignOut();
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>_FINAL PROJ</title>
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous"></head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
            </div>
            <div class="links">
                <a href="#"><div class="home">Home</div></a>
                <a href="./views/explore.php"><div class="explore">Explore</div></a>
                <a href="./views/guide.php"><div class="guide">Guide</div></a>
                <?php
                        if (isAuthenticated()){
                            echo "
                                <a href='./views/collection.php'><div class='collection'>Collection</div></a>
                                <a href='./views/community.php'><div class='community'>Community</div></a>";
                        } 

                    ?>
            </div>
        </div>
        <div class="auth" id="auth">
            <?php
                if (isAuthenticated()){
                    echo "<img src='" . htmlspecialchars($profilePic) . "' height='40px' weight'auto'>";
                } else {
                    echo "<a href='./views/loginRegister.php'>Login/Register</a>";
                }

            ?>
        </div>
    </header>
    <main>

        <!-- Right Side Bar for User Account Settings -->
        <div class="userProfileBar" id="userProfileBar">
            <form id="uploadForm" action="./controllers/upload.php" method="POST" enctype="multipart/form-data">
                <label for="profilePic">Upload Profile Picture:</label><br>
                <input type="file" name="profilePic" id="profilePic" accept="image/*" required><br><br>
                <button type="submit">Upload</button>
            </form>
            
            <?php
            
                if (isAuthenticated()){
                    echo "
                        <form method='post'><button type='submit' name='logout'>Log Out</button></form>";
                } 
            ?>
        </div>


        <div class="container">
            <h1>Research Without the Doubt.</h1>
            <div class="search">
                <form action="POST">
                    <input type="text" placeholder="Search" required>
                    <button><i class="bi bi-search"></i></button>
                </form>
            </div>
            <h5>Trending Topics, All Verified.</h5>
        </div>
    </main>
    <footer>
        <p>2025 All Rights Reserved</p>

    </footer>

    <script src="./assets/js/userProfile.js"></script>

</body>
</html>