<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if required parameters are present
if (!isset($_POST['collection_id']) || !isset($_POST['new_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$collectionId = $_POST['collection_id'];
$newName = trim($_POST['new_name']);
$userId = $_SESSION['userID'];

// Validate input
if (empty($newName)) {
    echo json_encode(['success' => false, 'message' => 'Collection name cannot be empty']);
    exit();
}

try {
    // First verify that the collection belongs to the user
    $checkSql = "SELECT id FROM collections WHERE id = :id AND user_id = :user_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['id' => $collectionId, 'user_id' => $userId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Collection not found or unauthorized']);
        exit();
    }

    // Update the collection name
    $sql = "UPDATE collections SET name = :name WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'name' => $newName,
        'id' => $collectionId,
        'user_id' => $userId
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Collection renamed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to rename collection']);
    }
} catch (PDOException $e) {
    error_log("Error renaming collection: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?> 