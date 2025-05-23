<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/userAuthHandler.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if it's an admin request
if ($_POST['action'] !== 'request_admin') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Validate input
$name = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$reason = trim($_POST['reason'] ?? '');

if (empty($name) || empty($email) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

try {
    // Create admin_requests table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS admin_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Check if user already has a pending request
    $checkSql = "SELECT id FROM admin_requests WHERE user_id = ? AND status = 'pending'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$_SESSION['userID']]);
    
    if ($checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You already have a pending admin request']);
        exit();
    }

    // Insert the request
    $insertSql = "INSERT INTO admin_requests (user_id, name, email, reason) VALUES (?, ?, ?, ?)";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->execute([$_SESSION['userID'], $name, $email, $reason]);

    echo json_encode(['success' => true, 'message' => 'Admin request submitted successfully']);
} catch (PDOException $e) {
    error_log("Error submitting admin request: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?> 