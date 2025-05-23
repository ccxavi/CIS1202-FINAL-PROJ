<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated and is admin
if (!isAuthenticated() || !isAdmin()) {
    header('Location: ../views/loginRegister.php');
    exit();
}

// Initialize variables
$error = null;
$success = null;

// Handle content actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $contentId = $_POST['content_id'] ?? null;
    $contentType = $_POST['content_type'] ?? null;
    $action = $_POST['action'];
    
    if ($contentId && $contentType && in_array($action, ['flag', 'hide', 'unhide'])) {
        try {
            $pdo->beginTransaction();
            
            switch ($contentType) {
                case 'post':
                    $table = 'posts';
                    break;
                case 'comment':
                    $table = 'comments';
                    break;
                default:
                    throw new Exception('Invalid content type');
            }
            
            switch ($action) {
                case 'flag':
                    $sql = "UPDATE $table SET is_flagged = TRUE WHERE id = ?";
                    break;
                case 'hide':
                    $sql = "UPDATE $table SET is_hidden = TRUE WHERE id = ?";
                    break;
                case 'unhide':
                    $sql = "UPDATE $table SET is_hidden = FALSE WHERE id = ?";
                    break;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contentId]);
            
            $pdo->commit();
            $success = ucfirst($contentType) . " successfully " . ($action === 'unhide' ? 'unhidden' : $action . 'ged');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error processing request: " . $e->getMessage();
        }
    }
}

// Get trending posts
try {
    $trendingPostsSql = "SELECT p.*, u.username, u.profile_pic,
                         (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as like_count,
                         COALESCE(p.is_hidden, FALSE) as is_hidden
                         FROM posts p 
                         JOIN users u ON p.user_id = u.id 
                         WHERE COALESCE(p.is_hidden, FALSE) = FALSE
                         ORDER BY like_count DESC 
                         LIMIT 5";
    $trendingPostsStmt = $pdo->query($trendingPostsSql);
    $trendingPosts = $trendingPostsStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching trending posts: " . $e->getMessage();
    $trendingPosts = [];
}

// Get all posts with user info and stats
try {
    $postsSql = "SELECT p.*, u.username, u.profile_pic,
                 (SELECT COUNT(*) FROM reactions WHERE post_id = p.id) as like_count,
                 (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                 COALESCE(p.is_flagged, FALSE) as is_flagged,
                 COALESCE(p.is_hidden, FALSE) as is_hidden
                 FROM posts p 
                 JOIN users u ON p.user_id = u.id 
                 ORDER BY p.created_at DESC";
    $postsStmt = $pdo->query($postsSql);
    $posts = $postsStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching posts: " . $e->getMessage();
    $posts = [];
}

// Get all comments with user and post info
try {
    $commentsSql = "SELECT c.*, u.username, u.profile_pic, p.title as post_title,
                   (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as like_count,
                   COALESCE(c.is_flagged, FALSE) as is_flagged,
                   COALESCE(c.is_hidden, FALSE) as is_hidden
                   FROM comments c 
                   JOIN users u ON c.user_id = u.id 
                   JOIN posts p ON c.post_id = p.id 
                   ORDER BY c.created_at DESC";
    $commentsStmt = $pdo->query($commentsSql);
    $comments = $commentsStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching comments: " . $e->getMessage();
    $comments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - Vero Admin</title>
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
        <div class="content-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Content Management</h1>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Trending Posts Section -->
            <div class="trending-section mb-4">
                <h3>Trending Posts</h3>
                <div class="row">
                    <?php foreach ($trendingPosts as $post): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?php echo htmlspecialchars($post['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" 
                                             alt="Profile" 
                                             class="rounded-circle me-2"
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($post['username']); ?></div>
                                            <div class="text-muted small">
                                                <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                    <p class="card-text small text-muted">
                                        <i class="bi bi-heart-fill text-danger"></i> <?php echo $post['like_count']; ?> likes
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row">
                <!-- Posts Section -->
                <div class="col-md-6">
                    <div class="content-section">
                        <h3>Posts</h3>
                        <div class="content-filters mb-3">
                            <input type="text" id="postSearch" class="form-control" placeholder="Search posts...">
                        </div>
                        <div class="content-list">
                            <?php foreach ($posts as $post): ?>
                                <div class="content-item post-item" 
                                     data-title="<?php echo htmlspecialchars($post['title']); ?>"
                                     data-username="<?php echo htmlspecialchars($post['username']); ?>">
                                    <div class="content-header">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($post['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" 
                                                 alt="Profile" 
                                                 class="rounded-circle me-2"
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($post['username']); ?></div>
                                                <div class="text-muted small">
                                                    <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content-actions">
                                            <?php if ($post['is_flagged']): ?>
                                                <span class="badge bg-warning">Flagged</span>
                                            <?php endif; ?>
                                            <?php if ($post['is_hidden']): ?>
                                                <span class="badge bg-danger">Hidden</span>
                                            <?php endif; ?>
                                            <div class="btn-group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="View Post"
                                                        onclick="viewPost(<?php echo $post['id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php if (!$post['is_flagged']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $post['id']; ?>">
                                                        <input type="hidden" name="content_type" value="post">
                                                        <input type="hidden" name="action" value="flag">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Flag Post">
                                                            <i class="bi bi-flag"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (!$post['is_hidden']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $post['id']; ?>">
                                                        <input type="hidden" name="content_type" value="post">
                                                        <input type="hidden" name="action" value="hide">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Hide Post">
                                                            <i class="bi bi-eye-slash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $post['id']; ?>">
                                                        <input type="hidden" name="content_type" value="post">
                                                        <input type="hidden" name="action" value="unhide">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Unhide Post">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="content-body">
                                        <h5><?php echo htmlspecialchars($post['title']); ?></h5>
                                        <p class="text-muted small">
                                            <i class="bi bi-heart-fill text-danger"></i> <?php echo $post['like_count']; ?> likes
                                            <i class="bi bi-chat-fill ms-2"></i> <?php echo $post['comment_count']; ?> comments
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="col-md-6">
                    <div class="content-section">
                        <h3>Comments</h3>
                        <div class="content-filters mb-3">
                            <input type="text" id="commentSearch" class="form-control" placeholder="Search comments...">
                        </div>
                        <div class="content-list">
                            <?php foreach ($comments as $comment): ?>
                                <div class="content-item comment-item"
                                     data-content="<?php echo htmlspecialchars($comment['content']); ?>"
                                     data-username="<?php echo htmlspecialchars($comment['username']); ?>">
                                    <div class="content-header">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($comment['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" 
                                                 alt="Profile" 
                                                 class="rounded-circle me-2"
                                                 style="width: 32px; height: 32px; object-fit: cover;">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($comment['username']); ?></div>
                                                <div class="text-muted small">
                                                    On: <?php echo htmlspecialchars($comment['post_title']); ?><br>
                                                    <?php echo date('M j, Y g:i A', strtotime($comment['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content-actions">
                                            <?php if ($comment['is_flagged']): ?>
                                                <span class="badge bg-warning">Flagged</span>
                                            <?php endif; ?>
                                            <?php if ($comment['is_hidden']): ?>
                                                <span class="badge bg-danger">Hidden</span>
                                            <?php endif; ?>
                                            <div class="btn-group">
                                                <?php if (!$comment['is_flagged']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $comment['id']; ?>">
                                                        <input type="hidden" name="content_type" value="comment">
                                                        <input type="hidden" name="action" value="flag">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-warning"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Flag Comment">
                                                            <i class="bi bi-flag"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (!$comment['is_hidden']): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $comment['id']; ?>">
                                                        <input type="hidden" name="content_type" value="comment">
                                                        <input type="hidden" name="action" value="hide">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Hide Comment">
                                                            <i class="bi bi-eye-slash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="content_id" value="<?php echo $comment['id']; ?>">
                                                        <input type="hidden" name="content_type" value="comment">
                                                        <input type="hidden" name="action" value="unhide">
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-success"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="Unhide Comment">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="content-body">
                                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        <p class="text-muted small">
                                            <i class="bi bi-heart-fill text-danger"></i> <?php echo $comment['like_count']; ?> likes
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Post View Modal -->
    <div class="modal fade" id="postViewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="post-details-content">
                        <!-- Content will be loaded dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <p>© 2025 <strong><em>Vero.</em></strong> All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Search functionality
        document.getElementById('postSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.post-item').forEach(item => {
                const title = item.dataset.title.toLowerCase();
                const username = item.dataset.username.toLowerCase();
                item.style.display = title.includes(searchTerm) || username.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('commentSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.comment-item').forEach(item => {
                const content = item.dataset.content.toLowerCase();
                const username = item.dataset.username.toLowerCase();
                item.style.display = content.includes(searchTerm) || username.includes(searchTerm) ? '' : 'none';
            });
        });

        // Post view functionality
        function viewPost(postId) {
            const modalContent = document.querySelector('.post-details-content');
            modalContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

            fetch(`controllers/getPostDetails.php?post_id=${postId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modalContent.innerHTML = `
                            <div class="post-header mb-4">
                                <div class="d-flex align-items-center">
                                    <img src="${data.post.profile_pic || '../assets/photo/Profile_Pictures/default.jpg'}" 
                                         alt="Profile" 
                                         class="rounded-circle me-3"
                                         style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <h4>${data.post.title}</h4>
                                        <p class="text-muted mb-0">Posted by ${data.post.username}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="post-content mb-4">
                                ${data.post.content}
                            </div>

                            <div class="post-stats mb-4">
                                <span class="badge bg-danger">
                                    <i class="bi bi-heart-fill"></i> ${data.post.like_count} likes
                                </span>
                                <span class="badge bg-primary ms-2">
                                    <i class="bi bi-chat-fill"></i> ${data.post.comment_count} comments
                                </span>
                            </div>

                            <div class="post-comments">
                                <h5>Comments</h5>
                                ${data.comments.length ? data.comments.map(comment => `
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="${comment.profile_pic || '../assets/photo/Profile_Pictures/default.jpg'}" 
                                                     alt="Profile" 
                                                     class="rounded-circle me-2"
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                                <div>
                                                    <div class="fw-bold">${comment.username}</div>
                                                    <div class="text-muted small">
                                                        ${new Date(comment.created_at).toLocaleString()}
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="card-text">${comment.content}</p>
                                        </div>
                                    </div>
                                `).join('') : '<p class="text-muted">No comments yet</p>'}
                            </div>
                        `;
                    } else {
                        modalContent.innerHTML = '<div class="alert alert-danger">Error loading post details</div>';
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = '<div class="alert alert-danger">Error loading post details</div>';
                    console.error('Error:', error);
                });

            new bootstrap.Modal(document.getElementById('postViewModal')).show();
        }
    </script>
</body>
</html> 