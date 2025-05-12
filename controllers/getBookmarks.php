<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!isset($_GET['collection_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing collection ID']);
    exit();
}

$collectionId = filter_var($_GET['collection_id'], FILTER_SANITIZE_NUMBER_INT);
$userId = $_SESSION['userID'];

try {
    // First verify that this collection belongs to the current user
    $checkStmt = $pdo->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$collectionId, $userId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Collection not found or access denied']);
        exit();
    }
    
    // Get bookmarks for this collection
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.url, b.created_at 
        FROM bookmarks b
        WHERE b.collection_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$collectionId]);
    $bookmarks = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'bookmarks' => $bookmarks
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 