<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Reaction.php';
require_once __DIR__ . '/../config/databaseConnection.php'; // Add this to access $pdo

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['userID'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !(isset($_POST['logout']))) {
    // Fetch user info from DB to get the correct profile picture
    $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User not found.");
    }

    $username = $user['username'];
    $profile_pic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg';

    if (isset($_POST['post_content'])) {
        Post::addPost($user_id, $username, $profile_pic, $_POST['post_content']);
    }

    if (isset($_POST['comment_content'], $_POST['post_id'])) {
        Comment::addComment($_POST['post_id'], $user_id, $_POST['comment_content']);
    }

    if (isset($_POST['like_post_id'])) {
        Reaction::addLike($_POST['like_post_id'], $user_id);
    }

    header('Location: ../views/community.php');
    exit;
}
