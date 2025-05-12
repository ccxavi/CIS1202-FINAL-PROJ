<?php
session_start();


require_once __DIR__ . '/../controllers/CommunityController.php';
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
$posts = Post::getAllPosts();


if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);

    
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // fallback if null
}


if (isset($_POST['logout'])) {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    header("Location: ../index.php");
    exit();
}

?>



<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/community.css">
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
                <a href="../index.php"><div class="explore">Explore</div></a>
                <a href="../views/guide.php"><div class="guide">Guide</div></a>
                <?php
                        if (isAuthenticated()){
                            echo "
                                <a href='../views/collection.php'><div class='collection'>Collection</div></a>
                                <a href='../views/community.php'><div class='community'>Community</div></a>";
                        } 

                    ?>
            </div>
        </div>
        <div class="auth" id="auth">
            <?php
                if (isAuthenticated()){
                    echo "<img src='." . htmlspecialchars($profilePic) . "' height='40px' weight'auto'>";
                } else {
                    echo "<a href='../views/loginRegister.php'>Login/Register</a>";
                }

            ?>
        </div>
    </header>

<main>
     <!-- Right Side Bar for User Account Settings -->
    <div class="userProfileBar" id="userProfileBar">
        <h1>
            <?php
                echo "Hi " . $user['username'];
            ?>
        </h1>

        <form id="uploadForm" action="./controllers/upload.php" method="POST" enctype="multipart/form-data">
            <label for="profilePic">Upload Profile Picture:</label><br>
            <input type="file" name="profilePic" id="profilePic" accept="image/*" required><br><br>
            <button type="submit">Upload</button>
        </form>
        
            
        <form method='POST'><button type='submit' name='logout'>Log Out</button></form>"
        
    
    </div>

    
    <div class="community-post-container">
        <div class="add-post-container">
                <?php
                    echo "<img class='userPP' src='." . htmlspecialchars($profilePic) . "' height='30px' weight'auto'>";
                ?>
                <form method="POST" action="../controllers/CommunityController.php">
                <textarea name="post_content" required placeholder="Verifying something?"></textarea>
                <button type="submit"><i class="bi bi-send"></i>
                </button>
                </form>
        </div>

        <div class="post-container">
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="user-info">
                    <img class="userPP" src=".<?= htmlspecialchars($post['profile_pic']) ?>" height="30px">
                    <div>
                        <p><strong><?= htmlspecialchars($post['username']) ?></strong></p>
                        <p>22h ago</p>
                    </div>
                </div>
                <div class="user-post">

                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

</main>

<!-- <h2>Community Forum</h2>

Add Post
<form method="POST" action="../controllers/CommunityController.php">
    <textarea name="post_content" required placeholder="Write your post here..."></textarea>
    <button type="submit">Post</button>
</form>

<hr>

Posts and Comments
<?php foreach ($posts as $post): ?>
    <div>
        
        <img src=".<?= htmlspecialchars($post['profile_pic']) ?>" height="40px">

        <p><strong><?= htmlspecialchars($post['username']) ?></strong>: <?= htmlspecialchars($post['content']) ?></p>
        <form method="POST" action="../controllers/CommunityController.php" style="display:inline;">
            <input type="hidden" name="like_post_id" value="<?= $post['id'] ?>">
            <button type="submit">❤️ Like (<?= Reaction::countLikes($post['id']) ?>)</button>
        </form>

        Comments
        <?php $comments = Comment::getComments($post['id']); ?>
        <div style="margin-left:20px;">
            <?php foreach ($comments as $comment): ?>
                <p><small><strong><?= htmlspecialchars($comment['username']) ?></strong>: <?= htmlspecialchars($comment['content']) ?></small></p>
            <?php endforeach; ?>

            Add Comment
            <form method="POST" action="../controllers/CommunityController.php">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="text" name="comment_content" placeholder="Add a comment..." required>
                <button type="submit">Comment</button>
            </form>
        </div>
    </div>
    <hr>
<?php endforeach; ?> -->

<!-- <a href="../index.php">Go Back</a> -->

<script src="../assets/js/userProfile.js"></script>

</body>
</html>
