<?php
require_once __DIR__ . '/../config/databaseConnection.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if (!isset($_GET['collection_id'])) {
    echo json_encode(['success' => false, 'message' => 'Collection ID is required']);
    exit();
}

$collectionId = $_GET['collection_id'];
$userId = $_SESSION['userID'];

try {
    // First verify that the collection belongs to the user
    $stmt = $pdo->prepare("SELECT id FROM collections WHERE id = :collection_id AND user_id = :user_id");
    $stmt->bindParam(':collection_id', $collectionId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Collection not found or unauthorized']);
        exit();
    }

    // Get all bookmarks for this collection along with article details
    $stmt = $pdo->prepare("
        SELECT 
            b.id as bookmark_id,
            a.id as article_id,
            a.title,
            a.author,
            a.article_link,
            a.published_date,
            b.created_at as bookmarked_at
        FROM bookmarks b
        JOIN articles a ON b.article_id = a.id
        WHERE b.collection_id = :collection_id
        ORDER BY b.created_at DESC
    ");
    
    $stmt->bindParam(':collection_id', $collectionId);
    $stmt->execute();
    
    $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'bookmarks' => $bookmarks
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?> 