<?php
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../models/userModel.php'; // Assuming findUserByID and other necessary functions are here
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in first.']);
    exit();
}

$userId = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Basic validation
    if (empty($username) || empty($email)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Username and email are required.'
        ]);
        exit();
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please enter a valid email address.'
        ]);
        exit();
    }
    
    // Check if username already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Username already taken. Please choose another.'
        ]);
        exit();
    }
    
    // Check if email already exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email already in use. Please use another email address.'
        ]);
        exit();
    }
    
    // Update user info
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Account information updated successfully.',
            'newUsername' => $username,
            'newEmail' => $email
        ]);
        exit();
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method. Only POST requests are accepted.'
    ]);
    exit();
}
?> 