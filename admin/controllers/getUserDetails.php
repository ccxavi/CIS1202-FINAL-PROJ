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

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

try {
    // Get user details
    $userSql = "SELECT * FROM users WHERE id = ?";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Get user's recent posts
    $postsSql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
    $postsStmt = $pdo->prepare($postsSql);
    $postsStmt->execute([$userId]);
    $posts = $postsStmt->fetchAll();

    // Get user's recent comments
    $commentsSql = "SELECT c.*, p.title as post_title 
                   FROM comments c 
                   JOIN posts p ON c.post_id = p.id 
                   WHERE c.user_id = ? 
                   ORDER BY c.created_at DESC 
                   LIMIT 5";
    $commentsStmt = $pdo->prepare($commentsSql);
    $commentsStmt->execute([$userId]);
    $comments = $commentsStmt->fetchAll();

    // Get user's recent bookmarks
    $bookmarksSql = "SELECT b.*, c.name as collection_name 
                    FROM bookmarks b 
                    JOIN collections c ON b.collection_id = c.id 
                    WHERE c.user_id = ? AND b.is_active = TRUE 
                    ORDER BY b.created_at DESC 
                    LIMIT 5";
    $bookmarksStmt = $pdo->prepare($bookmarksSql);
    $bookmarksStmt->execute([$userId]);
    $bookmarks = $bookmarksStmt->fetchAll();

    // Get user's projects
    $projectsSql = "SELECT * FROM collections 
                   WHERE user_id = ? AND is_active = TRUE 
                   ORDER BY created_at DESC 
                   LIMIT 5";
    $projectsStmt = $pdo->prepare($projectsSql);
    $projectsStmt->execute([$userId]);
    $projects = $projectsStmt->fetchAll();

    // Prepare response
    $response = [
        'success' => true,
        'user' => $user,
        'posts' => $posts,
        'comments' => $comments,
        'bookmarks' => $bookmarks,
        'projects' => $projects
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