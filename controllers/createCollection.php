<?php
require_once __DIR__ . '/../config/databaseConnection.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$userID = $_SESSION['userID'];
$defaultName = "untitled";

try {
    // Insert the new collection into the database
    $stmt = $pdo->prepare("INSERT INTO collections (user_id, name) VALUES (:user_id, :name)");
    $stmt->bindParam(':user_id', $userID);
    $stmt->bindParam(':name', $defaultName);

    if ($stmt->execute()) {
        $newId = $pdo->lastInsertId();
        echo json_encode([
            'success' => true, 
            'message' => 'Collection created successfully',
            'collection' => [
                'id' => $newId,
                'name' => $defaultName
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating collection']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>