<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';

// Check if user is authenticated
if (!isset($_SESSION['userID'])) {
    header('Location: ../views/loginRegister.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/community.php');
    exit();
}

// Get user input
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$userID = $_SESSION['userID'];

// Validate input
if (empty($title) || empty($content)) {
    $_SESSION['error'] = "Both title and content are required.";
    header('Location: ../views/community.php');
    exit();
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    // Execute with parameters
    $stmt->execute([$userID, $title, $content]);
    
    // Set success message
    $_SESSION['success'] = "Post created successfully!";
    
} catch (PDOException $e) {
    // Log error (in a production environment, use proper logging)
    error_log("Error creating post: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while creating your post. Please try again.";
}

// Redirect back to community page
header('Location: ../views/community.php');
exit(); 