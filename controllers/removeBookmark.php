<?php
require_once __DIR__ . '/../config/databaseConnection.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!isset($_POST['bookmark_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bookmark ID is required']);
    exit();
}

$bookmarkId = $_POST['bookmark_id'];
$userId = $_SESSION['userID'];

try {
    // First verify that the bookmark belongs to a collection owned by the user
    $stmt = $pdo->prepare("
        SELECT b.id 
        FROM bookmarks b
        JOIN collections c ON b.collection_id = c.id
        WHERE b.id = :bookmark_id AND c.user_id = :user_id
    ");
    $stmt->bindParam(':bookmark_id', $bookmarkId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Bookmark not found or unauthorized']);
        exit();
    }

    // Delete the bookmark
    $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = :bookmark_id");
    $stmt->bindParam(':bookmark_id', $bookmarkId);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Bookmark removed successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 