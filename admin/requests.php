<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated and is admin
if (!isAuthenticated() || !isAdmin()) {
    header('Location: ../views/loginRegister.php');
    exit();
}

// Handle request actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = $_POST['request_id'] ?? null;
    $action = $_POST['action'];
    
    if ($requestId && in_array($action, ['approve', 'reject'])) {
        try {
            $pdo->beginTransaction();
            
            if ($action === 'approve') {
                // Get the user_id from the request
                $getUserSql = "SELECT user_id FROM admin_requests WHERE id = ?";
                $getUserStmt = $pdo->prepare($getUserSql);
                $getUserStmt->execute([$requestId]);
                $userId = $getUserStmt->fetch()['user_id'];
                
                // Update user role to admin
                $updateUserSql = "UPDATE users SET role = 'admin' WHERE id = ?";
                $updateUserStmt = $pdo->prepare($updateUserSql);
                $updateUserStmt->execute([$userId]);
            }
            
            // Update request status
            $updateRequestSql = "UPDATE admin_requests SET status = ? WHERE id = ?";
            $updateRequestStmt = $pdo->prepare($updateRequestSql);
            $updateRequestStmt->execute([$action === 'approve' ? 'approved' : 'rejected', $requestId]);
            
            $pdo->commit();
            $success = "Request " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Error processing request: " . $e->getMessage();
        }
    }
}

// Get all pending requests
try {
    $requestsSql = "SELECT ar.*, u.username, u.email as user_email 
                   FROM admin_requests ar 
                   JOIN users u ON ar.user_id = u.id 
                   WHERE ar.status = 'pending' 
                   ORDER BY ar.created_at DESC";
    $requestsStmt = $pdo->query($requestsSql);
    $pendingRequests = $requestsStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching requests: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Requests - Vero Admin</title>
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
        <div class="requests-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Admin Requests</h1>
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

            <?php if (empty($pendingRequests)): ?>
                <div class="alert alert-info">No pending admin requests.</div>
            <?php else: ?>
                <?php foreach ($pendingRequests as $request): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-user">
                                <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($request['name']); ?></h5>
                                    <div class="request-meta">
                                        Username: <?php echo htmlspecialchars($request['username']); ?><br>
                                        Email: <?php echo htmlspecialchars($request['email']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="request-actions">
                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this request?');">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="request-content">
                            <h6>Reason for Request:</h6>
                            <p><?php echo nl2br(htmlspecialchars($request['reason'])); ?></p>
                        </div>
                        <div class="request-meta">
                            Requested on: <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>© 2025 <strong><em>Vero.</em></strong> All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 