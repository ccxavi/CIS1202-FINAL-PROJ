<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

header('Content-Type: application/json'); // Ensure JSON response

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in first.']);
    exit();
} 

$userId = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../assets/photo/Profile_Pictures/';
    // Ensure this path is relative from the web root for client-side display
    $webAccessibleUploadPath = './assets/photo/Profile_Pictures/'; 
    $fileTmp = $_FILES['profilePic']['tmp_name'];
    $fileName = basename($_FILES['profilePic']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    // Generate a more unique filename to prevent browser caching issues and overwrites
    $newFileName = $userId . '_' . time() . '.' . $fileExtension;
    $targetFile = $uploadDir . $newFileName;
    $targetPathForClient = $webAccessibleUploadPath . $newFileName;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($fileTmp);

    if (in_array($fileType, $allowedTypes)) {
      if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
            exit();
        }
      }

      if (move_uploaded_file($fileTmp, $targetFile)) {
        try {
            // Get the current profile picture before updating
            $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $previousPicPath = $user['profile_pic'] ?? '';
            
            // Update the user's profile_pic in the database
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            if ($stmt->execute([$targetPathForClient, $userId])){
                // Delete previous profile picture if requested and if it's not the default picture
                if (isset($_POST['deletePrevious']) && $_POST['deletePrevious'] === 'true' && 
                    $previousPicPath && $previousPicPath !== './assets/photo/Profile_Pictures/default.jpg') {
                    
                    $previousPicFile = str_replace('./assets/photo/Profile_Pictures/', '../assets/photo/Profile_Pictures/', $previousPicPath);
                    
                    if (file_exists($previousPicFile) && is_file($previousPicFile)) {
                        unlink($previousPicFile);
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully.', 'newProfilePicUrl' => $targetPathForClient]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error: Failed to update profile picture path.']);
            }
            exit();
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
      } else {
        echo json_encode(['success' => false, 'message' => 'Error moving uploaded file. Check permissions for ' . $uploadDir]);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.']);
    }
  } else {
    $uploadError = $_FILES['profilePic']['error'] ?? 'Unknown error';
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error. Error code: ' . $uploadError]);
  }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>