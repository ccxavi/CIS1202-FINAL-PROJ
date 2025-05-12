<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['article_id']) || !isset($_POST['collection_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$articleId = filter_var($_POST['article_id'], FILTER_SANITIZE_NUMBER_INT);
$collectionId = filter_var($_POST['collection_id'], FILTER_SANITIZE_NUMBER_INT);
$userId = $_SESSION['userID'];

try {
    // First verify that this collection belongs to the current user
    $checkStmt = $pdo->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$collectionId, $userId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Collection not found or access denied']);
        exit();
    }
    
    // Check if bookmark already exists
    $checkBookmarkStmt = $pdo->prepare("SELECT id FROM bookmarks WHERE article_id = ? AND collection_id = ?");
    $checkBookmarkStmt->execute([$articleId, $collectionId]);
    
    if ($checkBookmarkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Article already bookmarked in this collection']);
        exit();
    }
    
    // Add the bookmark
    $stmt = $pdo->prepare("INSERT INTO bookmarks (article_id, collection_id) VALUES (?, ?)");
    $success = $stmt->execute([$articleId, $collectionId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Bookmark added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add bookmark']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 