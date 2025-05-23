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

try {
    // Total Users
    $usersSql = "SELECT COUNT(*) as total FROM users";
    $usersStmt = $pdo->query($usersSql);
    $totalUsers = $usersStmt->fetch()['total'];

    // Total Posts
    $postsSql = "SELECT COUNT(*) as total FROM posts";
    $postsStmt = $pdo->query($postsSql);
    $totalPosts = $postsStmt->fetch()['total'];

    // Total Comments
    $commentsSql = "SELECT COUNT(*) as total FROM comments";
    $commentsStmt = $pdo->query($commentsSql);
    $totalComments = $commentsStmt->fetch()['total'];

    // Total Bookmarks
    $bookmarksSql = "SELECT COUNT(*) as total FROM bookmarks WHERE is_active = TRUE";
    $bookmarksStmt = $pdo->query($bookmarksSql);
    $totalBookmarks = $bookmarksStmt->fetch()['total'];

    // Total Projects
    $projectsSql = "SELECT COUNT(*) as total FROM collections WHERE is_active = TRUE";
    $projectsStmt = $pdo->query($projectsSql);
    $totalProjects = $projectsStmt->fetch()['total'];

    // Weekly Active Users
    $weeklyUsersSql = "SELECT COUNT(DISTINCT user_id) as total FROM (
        SELECT user_id, created_at FROM posts 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT user_id, created_at FROM comments 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT user_id, created_at FROM bookmarks 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        SELECT user_id, created_at FROM collections 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ) as active_users";
    $weeklyUsersStmt = $pdo->query($weeklyUsersSql);
    $result = $weeklyUsersStmt->fetch();
    $weeklyActiveUsers = $result ? $result['total'] : 0;

    // Prepare response
    $response = [
        'success' => true,
        'totalUsers' => $totalUsers,
        'totalPosts' => $totalPosts,
        'totalComments' => $totalComments,
        'totalBookmarks' => $totalBookmarks,
        'totalProjects' => $totalProjects,
        'weeklyActiveUsers' => $weeklyActiveUsers
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