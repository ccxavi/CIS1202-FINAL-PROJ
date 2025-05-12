<?php
require_once __DIR__ . '/../config/databaseConnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure the user is authenticated
    session_start();
    if (!isset($_SESSION['userID'])) {
        header('Location: ../views/loginRegister.php');
        exit();
    }

    // Get the user ID from session
    $userID = $_SESSION['userID'];

    // Sanitize and validate input
    if (isset($_POST['collectionName']) && !empty($_POST['collectionName'])) {
        $collectionName = htmlspecialchars($_POST['collectionName']);

        // Insert the new collection into the database
        $stmt = $pdo->prepare("INSERT INTO collections (user_id, name) VALUES (:user_id, :name)");
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':name', $collectionName);

        if ($stmt->execute()) {
            header("Location: ../views/collection.php");
        } else {
            echo "Error creating collection.";
        }
    }
}
?>