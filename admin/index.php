<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated and is admin
if (!isAuthenticated() || !isAdmin()) {
    header('Location: ../views/loginRegister.php');
    exit();
}

// Initialize metrics with default values
$totalUsers = 0;
$totalPosts = 0;
$totalComments = 0;
$totalBookmarks = 0;
$totalProjects = 0;
$weeklyActiveUsers = 0;
$latestPosts = [];
$latestComments = [];

// Get metrics
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

    // Weekly Active Users (fixed query)
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

    // Latest Posts with user info
    $latestPostsSql = "SELECT p.*, u.username, u.profile_pic 
                       FROM posts p 
                       JOIN users u ON p.user_id = u.id 
                       ORDER BY p.created_at DESC 
                       LIMIT 5";
    $latestPostsStmt = $pdo->query($latestPostsSql);
    $latestPosts = $latestPostsStmt->fetchAll();

    // Latest Comments with user and post info
    $latestCommentsSql = "SELECT c.*, u.username, u.profile_pic, p.title as post_title 
                         FROM comments c 
                         JOIN users u ON c.user_id = u.id 
                         JOIN posts p ON c.post_id = p.id 
                         ORDER BY c.created_at DESC 
                         LIMIT 5";
    $latestCommentsStmt = $pdo->query($latestCommentsSql);
    $latestComments = $latestCommentsStmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error fetching admin metrics: " . $e->getMessage());
    $error = "Error loading dashboard data";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vero Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./assets/css/admin.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="logo">
                <h2>Vero Admin</h2>
            </div>
            <div class="links">
                <a href="../index.php"><div class="home">Main Site</div></a>
                <a href="index.php"><div class="explore">Dashboard</div></a>
                <a href="requests.php"><div class="guide">Admin Requests</div></a>
                <a href="users.php"><div class="guide">User Management</div></a>
                <a href="content.php"><div class="guide">Content Management</div></a>
            </div>
        </div>
    </header>

    <main>
        <div class="admin-dashboard">
            <h1 class="mb-4">Admin Dashboard</h1>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="metric-title">Total Users</div>
                    <div class="metric-value"><?php echo number_format($totalUsers); ?></div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="bi bi-chat-square-text-fill"></i>
                    </div>
                    <div class="metric-title">Total Posts & Comments</div>
                    <div class="metric-value"><?php echo number_format($totalPosts + $totalComments); ?></div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="bi bi-bookmark-fill"></i>
                    </div>
                    <div class="metric-title">Total Bookmarks & Projects</div>
                    <div class="metric-value"><?php echo number_format($totalBookmarks + $totalProjects); ?></div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="metric-title">Weekly Active Users</div>
                    <div class="metric-value"><?php echo number_format($weeklyActiveUsers); ?></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="latest-activity">
                        <div class="activity-title">Latest Posts</div>
                        <?php if (empty($latestPosts)): ?>
                            <div class="text-muted">No posts yet</div>
                        <?php else: ?>
                            <?php foreach ($latestPosts as $post): ?>
                                <div class="activity-item">
                                    <div class="activity-user">
                                        <img src="<?php echo htmlspecialchars($post['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" alt="User">
                                        <span><?php echo htmlspecialchars($post['username']); ?></span>
                                    </div>
                                    <div class="activity-content">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="latest-activity">
                        <div class="activity-title">Latest Comments</div>
                        <?php if (empty($latestComments)): ?>
                            <div class="text-muted">No comments yet</div>
                        <?php else: ?>
                            <?php foreach ($latestComments as $comment): ?>
                                <div class="activity-item">
                                    <div class="activity-user">
                                        <img src="<?php echo htmlspecialchars($comment['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" alt="User">
                                        <span><?php echo htmlspecialchars($comment['username']); ?></span>
                                    </div>
                                    <div class="activity-content">
                                        On: <?php echo htmlspecialchars($comment['post_title']); ?>
                                        <br>
                                        <?php echo htmlspecialchars(substr($comment['content'], 0, 100)) . '...'; ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>© 2025 <strong><em>Vero.</em></strong> All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Refresh metrics every 30 seconds
        setInterval(() => {
            fetch('controllers/getMetrics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update metric values
                        document.querySelectorAll('.metric-value').forEach((el, index) => {
                            const values = [
                                data.totalUsers,
                                data.totalPosts + data.totalComments,
                                data.totalBookmarks + data.totalProjects,
                                data.weeklyActiveUsers
                            ];
                            el.textContent = values[index].toLocaleString();
                        });
                    }
                })
                .catch(error => console.error('Error updating metrics:', error));
        }, 30000);
    </script>
</body>
</html>
