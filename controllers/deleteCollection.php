<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if collection_id is provided
if (!isset($_POST['collection_id'])) {
    echo json_encode(['success' => false, 'message' => 'Collection ID is required']);
    exit();
}

$collectionId = $_POST['collection_id'];
$userId = $_SESSION['userID'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // First verify that the collection belongs to the user
    $checkSql = "SELECT id FROM collections WHERE id = :id AND user_id = :user_id AND is_active = TRUE";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute(['id' => $collectionId, 'user_id' => $userId]);
    
    if (!$checkStmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Collection not found or unauthorized']);
        exit();
    }

    // Soft delete all bookmarks in the collection
    $bookmarkSql = "UPDATE bookmarks SET is_active = FALSE WHERE collection_id = :collection_id";
    $bookmarkStmt = $pdo->prepare($bookmarkSql);
    $bookmarkStmt->execute(['collection_id' => $collectionId]);

    // Soft delete the collection
    $collectionSql = "UPDATE collections SET is_active = FALSE WHERE id = :id AND user_id = :user_id";
    $collectionStmt = $pdo->prepare($collectionSql);
    $result = $collectionStmt->execute([
        'id' => $collectionId,
        'user_id' => $userId
    ]);

    if ($result) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Collection and its bookmarks deleted successfully']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to delete collection']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Error deleting collection: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?> 