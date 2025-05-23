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
$users = [];
$error = null;
$success = null;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = $_POST['user_id'] ?? null;
    $action = $_POST['action'];
    
    if ($userId && in_array($action, ['ban', 'unban', 'promote'])) {
        try {
            $pdo->beginTransaction();
            
            switch ($action) {
                case 'ban':
                    $sql = "UPDATE users SET status = 'banned' WHERE id = ?";
                    break;
                case 'unban':
                    $sql = "UPDATE users SET status = 'active' WHERE id = ?";
                    break;
                case 'promote':
                    $sql = "UPDATE users SET role = 'admin' WHERE id = ?";
                    break;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
            
            $pdo->commit();
            $success = "User successfully " . ($action === 'promote' ? 'promoted to admin' : $action . 'ned');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error processing request: " . $e->getMessage();
        }
    }
}

// Get all users with their stats
try {
    $usersSql = "SELECT 
        u.*,
        (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
        (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count,
        (SELECT COUNT(*) FROM bookmarks b JOIN collections c ON b.collection_id = c.id WHERE c.user_id = u.id AND b.is_active = TRUE) as bookmark_count,
        (SELECT COUNT(*) FROM collections WHERE user_id = u.id AND is_active = TRUE) as project_count
    FROM users u
    ORDER BY u.created_at DESC";
    $usersStmt = $pdo->query($usersSql);
    $users = $usersStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
    $users = []; // Ensure $users is an empty array if query fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Vero Admin</title>
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
        <div class="users-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>User Management</h1>
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

            <div class="user-filters mb-4">
                <div class="input-group">
                    <input type="text" id="userSearch" class="form-control" placeholder="Search users...">
                    <select id="statusFilter" class="form-select" style="max-width: 150px;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="banned">Banned</option>
                    </select>
                    <select id="roleFilter" class="form-select" style="max-width: 150px;">
                        <option value="">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Stats</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row" 
                                data-status="<?php echo htmlspecialchars($user['status']); ?>"
                                data-role="<?php echo htmlspecialchars($user['role']); ?>"
                                data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                data-email="<?php echo htmlspecialchars($user['email']); ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($user['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'); ?>" 
                                             alt="Profile" 
                                             class="rounded-circle me-2"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="text-muted small">ID: <?php echo $user['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($user['status'] ?? 'active') === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-info" title="Posts">
                                            <i class="bi bi-chat-square-text"></i> <?php echo $user['post_count']; ?>
                                        </span>
                                        <span class="badge bg-info" title="Comments">
                                            <i class="bi bi-chat"></i> <?php echo $user['comment_count']; ?>
                                        </span>
                                        <span class="badge bg-info" title="Bookmarks">
                                            <i class="bi bi-bookmark"></i> <?php echo $user['bookmark_count']; ?>
                                        </span>
                                        <span class="badge bg-info" title="Projects">
                                            <i class="bi bi-folder"></i> <?php echo $user['project_count']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#userDetailsModal"
                                                data-user-id="<?php echo $user['id']; ?>"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="View User Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to promote this user to admin?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="promote">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Promote to Admin">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to ban this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="ban">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Ban User">
                                                    <i class="bi bi-ban"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to unban this user?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="unban">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-success"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Unban User">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="user-details-content">
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
        // Initialize all tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // User search and filter functionality
        document.getElementById('userSearch').addEventListener('input', filterUsers);
        document.getElementById('statusFilter').addEventListener('change', filterUsers);
        document.getElementById('roleFilter').addEventListener('change', filterUsers);

        function filterUsers() {
            const searchTerm = document.getElementById('userSearch').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const roleFilter = document.getElementById('roleFilter').value;

            document.querySelectorAll('.user-row').forEach(row => {
                const username = row.dataset.username.toLowerCase();
                const email = row.dataset.email.toLowerCase();
                const status = row.dataset.status;
                const role = row.dataset.role;

                const matchesSearch = username.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesRole = !roleFilter || role === roleFilter;

                row.style.display = matchesSearch && matchesStatus && matchesRole ? '' : 'none';
            });
        }

        // User details modal functionality
        const userDetailsModal = document.getElementById('userDetailsModal');
        userDetailsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.dataset.userId;
            const modalContent = this.querySelector('.user-details-content');

            // Show loading state
            modalContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

            // Fetch user details
            fetch(`controllers/getUserDetails.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modalContent.innerHTML = `
                            <div class="user-profile mb-4">
                                <div class="d-flex align-items-center">
                                    <img src="${data.user.profile_pic || '../assets/photo/Profile_Pictures/default.jpg'}" 
                                         alt="Profile" 
                                         class="rounded-circle me-3"
                                         style="width: 64px; height: 64px; object-fit: cover;">
                                    <div>
                                        <h4>${data.user.username}</h4>
                                        <p class="text-muted mb-0">${data.user.email}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Recent Posts</h5>
                                    ${data.posts.length ? data.posts.map(post => `
                                        <div class="card mb-2">
                                            <div class="card-body">
                                                <h6 class="card-title">${post.title}</h6>
                                                <p class="card-text small text-muted">
                                                    Posted on ${new Date(post.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                        </div>
                                    `).join('') : '<p class="text-muted">No posts yet</p>'}
                                </div>

                                <div class="col-md-6">
                                    <h5>Recent Comments</h5>
                                    ${data.comments.length ? data.comments.map(comment => `
                                        <div class="card mb-2">
                                            <div class="card-body">
                                                <p class="card-text">${comment.content}</p>
                                                <p class="card-text small text-muted">
                                                    On: ${comment.post_title}<br>
                                                    Posted on ${new Date(comment.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                        </div>
                                    `).join('') : '<p class="text-muted">No comments yet</p>'}
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5>Recent Bookmarks</h5>
                                    ${data.bookmarks.length ? data.bookmarks.map(bookmark => `
                                        <div class="card mb-2">
                                            <div class="card-body">
                                                <h6 class="card-title">${bookmark.title}</h6>
                                                <p class="card-text small text-muted">
                                                    Collection: ${bookmark.collection_name}<br>
                                                    Bookmarked on ${new Date(bookmark.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                        </div>
                                    `).join('') : '<p class="text-muted">No bookmarks yet</p>'}
                                </div>

                                <div class="col-md-6">
                                    <h5>Projects</h5>
                                    ${data.projects.length ? data.projects.map(project => `
                                        <div class="card mb-2">
                                            <div class="card-body">
                                                <h6 class="card-title">${project.name}</h6>
                                                <p class="card-text small text-muted">
                                                    Created on ${new Date(project.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                        </div>
                                    `).join('') : '<p class="text-muted">No projects yet</p>'}
                                </div>
                            </div>
                        `;
                    } else {
                        modalContent.innerHTML = '<div class="alert alert-danger">Error loading user details</div>';
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = '<div class="alert alert-danger">Error loading user details</div>';
                    console.error('Error:', error);
                });
        });
    </script>
</body>
</html> 