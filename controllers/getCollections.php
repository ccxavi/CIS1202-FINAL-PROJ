<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userId = $_SESSION['userID'];

try {
    // Get all collections for the user
    $collectionsSql = "SELECT * FROM collections WHERE user_id = :user_id AND is_active = TRUE ORDER BY created_at DESC";
    $collectionsStmt = $pdo->prepare($collectionsSql);
    $collectionsStmt->execute(['user_id' => $userId]);
    $collections = $collectionsStmt->fetchAll(PDO::FETCH_ASSOC);

    // For each collection, get its bookmarks
    foreach ($collections as &$collection) {
        $bookmarksSql = "SELECT * FROM bookmarks WHERE collection_id = :collection_id AND is_active = TRUE ORDER BY created_at DESC";
        $bookmarksStmt = $pdo->prepare($bookmarksSql);
        $bookmarksStmt->execute(['collection_id' => $collection['id']]);
        $collection['bookmarks'] = $bookmarksStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'success' => true,
        'collections' => $collections
    ]);
} catch (PDOException $e) {
    error_log("Error fetching collections: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 