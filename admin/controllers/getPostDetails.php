<?php
session_start();
require_once __DIR__ . '/../../config/databaseConnection.php';
require_once __DIR__ . '/../../controllers/userAuthHandler.php';

// Check if user is authenticated and is admin
if (!isAuthenticated() || !isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$postId = $_GET['post_id'] ?? null;

if (!$postId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

try {
    // Get post details with user info and stats
    $postSql = "SELECT p.*, u.username, u.profile_pic,
                (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.id = ?";
    $postStmt = $pdo->prepare($postSql);
    $postStmt->execute([$postId]);
    $post = $postStmt->fetch();

    if (!$post) {
        throw new Exception('Post not found');
    }

    // Get post comments with user info
    $commentsSql = "SELECT c.*, u.username, u.profile_pic,
                   (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count
                   FROM comments c 
                   JOIN users u ON c.user_id = u.id 
                   WHERE c.post_id = ? 
                   ORDER BY c.created_at DESC";
    $commentsStmt = $pdo->prepare($commentsSql);
    $commentsStmt->execute([$postId]);
    $comments = $commentsStmt->fetchAll();

    // Prepare response
    $response = [
        'success' => true,
        'post' => $post,
        'comments' => $comments
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 