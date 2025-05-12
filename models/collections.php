<?php
require_once __DIR__ . '/../config/databaseConnection.php';

function getCollectionsByUserID($userID) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $userID);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
