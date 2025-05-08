<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    die("Access denied. Please log in first.");
} 

$userId = $_SESSION['userID'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../assets/photo/Profile_Pictures/';
    $uploadPath = './assets/photo/Profile_Pictures/';
    $fileTmp = $_FILES['profilePic']['tmp_name'];
    $fileName = basename($_FILES['profilePic']['name']);
    $targetFile = $uploadDir . time() . '_' . $fileName;
    $targetPath = $uploadPath . time() . '_' . $fileName;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($fileTmp);

    if (in_array($fileType, $allowedTypes)) {
      if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
      }

      if (move_uploaded_file($fileTmp, $targetFile)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute([$targetPath, $userId]);

            header("Location: ../index.php?upload=success");
            exit();
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }

      } else {
        echo "Error moving uploaded file.";
      }
    } else {
      echo "Invalid file type. Only JPG, PNG, and GIF allowed.";
    }
  } else {
    echo "No file uploaded or upload error.";
  }
}
?>