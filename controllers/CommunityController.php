<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Reaction.php';
require_once __DIR__ . '/../config/databaseConnection.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['userID'])) {
        echo json_encode(['success' => false, 'error' => 'User not authenticated']);
        exit;
    }
    
    $user_id = $_SESSION['userID'];
    $action = $_POST['ajax_action'];

    if ($action === 'like_post' && isset($_POST['post_id'])) {
        $post_id = $_POST['post_id'];
        try {
            // Toggle the like
            $is_liked = handleLike($post_id, $user_id);
            $new_count = getLikeCount($post_id);
            
            echo json_encode([
                'success' => true, 
                'likes' => $new_count,
                'is_liked' => $is_liked
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'like_comment' && isset($_POST['comment_id'])) {
        $comment_id = $_POST['comment_id'];
        try {
            $is_liked = Comment::toggleCommentLike($comment_id, $user_id);
            $likes_count = Comment::getCommentLikesCount($comment_id);
            echo json_encode([
                'success' => true,
                'likes' => $likes_count,
                'is_liked' => $is_liked
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($action === 'add_comment' && isset($_POST['post_id'], $_POST['content'])) {
        $post_id = $_POST['post_id'];
        $content = trim($_POST['content']);
        $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        
        if (empty($content)) {
            echo json_encode(['success' => false, 'error' => 'Comment cannot be empty']);
            exit;
        }
        
        try {
            Comment::addComment($post_id, $user_id, $content, $parent_id);
            
            // Get user info for the response
    $stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'comment' => [
                    'username' => $user['username'],
                    'profile_pic' => $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg',
                    'content' => $content,
                    'time_ago' => 'just now',
                    'likes_count' => 0,
                    'parent_id' => $parent_id
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}

// Handle regular POST requests (fallback for non-JS browsers)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure we have a user ID
    if (!isset($_SESSION['userID'])) {
        header('Location: ../views/loginRegister.php');
        exit();
    }
    
    $user_id = $_SESSION['userID'];
    
    // Handle likes
    if (isset($_POST['like_post_id'])) {
        try {
            handleLike($_POST['like_post_id'], $user_id);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Handle comment likes
    if (isset($_POST['like_comment_id'])) {
        try {
            Comment::toggleCommentLike($_POST['like_comment_id'], $user_id);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Handle comments
    if (isset($_POST['comment_post_id']) && isset($_POST['comment_content'])) {
        $post_id = $_POST['comment_post_id'];
        $content = trim($_POST['comment_content']);
        $parent_id = !empty($_POST['comment_parent_id']) ? $_POST['comment_parent_id'] : null;
        
        if (!empty($content)) {
            try {
                Comment::addComment($post_id, $user_id, $content, $parent_id);
            } catch (PDOException $e) {
                error_log($e->getMessage());
            }
    }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

function timeAgo($datetime) {
    try {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        if ($diff->s > 0) {
            return $diff->s . ' second' . ($diff->s > 1 ? 's' : '') . ' ago';
        }
        return 'just now';
    } catch (Exception $e) {
        error_log("Error calculating time ago: " . $e->getMessage());
        return 'some time ago';
    }
}

function isPostLikedByUser($postId, $userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking like status: " . $e->getMessage());
        return false;
    }
}

function handleLike($postId, $userId) {
    global $pdo;
    try {
        // Check if already liked
        $stmt = $pdo->prepare("SELECT id FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $existing_like = $stmt->fetch();
        
        if ($existing_like) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            return false; // Not liked anymore
        } else {
            // Like
            $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type) VALUES (?, ?, 'like')");
            $stmt->execute([$postId, $userId]);
            return true; // Now liked
        }
    } catch (PDOException $e) {
        error_log("Error processing like: " . $e->getMessage());
        throw $e;
    }
}

function getLikeCount($postId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ?");
        $stmt->execute([$postId]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting like count: " . $e->getMessage());
        return 0;
    }
}

function getCommentsForPost($postId) {
    return Comment::getComments($postId);
}

function getCommentReplies($commentId) {
    return Comment::getReplies($commentId);
}

function getAllPosts($sort = 'recent') {
    global $pdo;
    try {
        // Set the timezone to match your server's timezone
        date_default_timezone_set('Asia/Manila');
        
        $order = ($sort === 'oldest') ? 'ASC' : 'DESC';
        
        $sql = "SELECT p.*, u.username, u.profile_pic, 
                (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as reaction_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                p.created_at as post_timestamp
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at {$order}";
        
        $posts = $pdo->query($sql)->fetchAll();
        
        // Add time_ago for each post
        foreach ($posts as &$post) {
            // Convert the timestamp to DateTime for accurate calculations
            $post['time_ago'] = timeAgo($post['created_at']);
        }
        
        return $posts;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}
