<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Reaction.php';

// session_start();
$user_id = $_SESSION['userID'] ?? 1; // fallback test user

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !(isset($_POST['logout']))) {
    if (isset($_POST['post_content'])) {
        Post::addPost($user_id, $_POST['post_content']);
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
