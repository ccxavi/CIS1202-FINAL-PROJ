<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

header('Content-Type: application/json'); // Ensure JSON response

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in first.']);
    exit();
}

$userId = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Basic validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode([
            'success' => false, 
            'message' => 'All password fields are required.'
        ]);
        exit();
    }
    
    // Confirm passwords match
    if ($newPassword !== $confirmPassword) {
        echo json_encode([
            'success' => false, 
            'message' => 'New passwords do not match.'
        ]);
        exit();
    }
    
    // Check password length
    if (strlen($newPassword) < 6) {
        echo json_encode([
            'success' => false, 
            'message' => 'Password must be at least 6 characters long.'
        ]);
        exit();
    }
    
    // Verify current password
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $storedHash = $stmt->fetchColumn();
        
        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Current password is incorrect.'
            ]);
            exit();
        }
        
        // Make sure new password is different
        if (password_verify($newPassword, $storedHash)) {
            echo json_encode([
                'success' => false, 
                'message' => 'New password must be different from your current password.'
            ]);
            exit();
        }
        
        // Update password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password updated successfully. Please use your new password next time you login.'
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