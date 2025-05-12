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

if (!isset($_POST['collection_id']) || !isset($_POST['new_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$collectionId = filter_var($_POST['collection_id'], FILTER_SANITIZE_NUMBER_INT);
$newName = htmlspecialchars(trim($_POST['new_name']));
$userId = $_SESSION['userID'];

try {
    // First verify that this collection belongs to the current user
    $checkStmt = $pdo->prepare("SELECT id FROM collections WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$collectionId, $userId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Collection not found or access denied']);
        exit();
    }
    
    // Update the collection name
    $updateStmt = $pdo->prepare("UPDATE collections SET name = ? WHERE id = ? AND user_id = ?");
    $success = $updateStmt->execute([$newName, $collectionId, $userId]);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Collection name updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update collection name']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 