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
    $uploadDir = __DIR__ . '/../assets/photo/Profile_Pictures/';
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
      // Ensure upload directory exists with proper permissions
      if (!is_dir($uploadDir)) {
        // Create directory recursively with permissive permissions
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Failed to create directory: " . $uploadDir);
            echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
            exit();
        }
        // Set permissive permissions
        chmod($uploadDir, 0777);
      } else {
        // If directory exists, make sure it has proper permissions
        chmod($uploadDir, 0777);
      }

      // Log the permissions for debugging
      error_log("Directory permissions check: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
      
      // Try to move the uploaded file
      if (move_uploaded_file($fileTmp, $targetFile)) {
        // Make the uploaded file readable
        chmod($targetFile, 0644);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            if ($stmt->execute([$targetPathForClient, $userId])){
                // Log success and URL for debugging
                error_log("Profile picture uploaded successfully. Path: " . $targetPathForClient);
                
                echo json_encode(['success' => true, 
                                  'message' => 'Profile picture updated successfully.', 
                                  'newProfilePicUrl' => $targetPathForClient]);
            } else {
                error_log("Database error when updating profile_pic for user $userId");
                echo json_encode(['success' => false, 'message' => 'Database error: Failed to update profile picture path.']);
            }
            exit();
        } catch (PDOException $e) {
            error_log("PDO Exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit();
        }
      } else {
        // Log upload error details
        error_log("Failed to move uploaded file from $fileTmp to $targetFile");
        error_log("Upload directory permissions: " . substr(sprintf('%o', fileperms($uploadDir)), -4));
        error_log("PHP running as user: " . exec('whoami'));
        
        echo json_encode(['success' => false, 'message' => 'Error moving uploaded file. Server permission error.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.']);
    }
  } else {
    $uploadError = isset($_FILES['profilePic']) ? $_FILES['profilePic']['error'] : 'No file data received';
    $errorMessage = 'Upload error: ';
    
    // Translate error codes to human-readable messages
    switch($uploadError) {
        case UPLOAD_ERR_INI_SIZE:
            $errorMessage .= 'File exceeds the upload_max_filesize directive in php.ini';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage .= 'File exceeds the MAX_FILE_SIZE directive in the HTML form';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage .= 'File was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage .= 'No file was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage .= 'Missing a temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage .= 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMessage .= 'File upload stopped by extension';
            break;
        default:
            $errorMessage .= 'Unknown upload error';
    }
    
    error_log($errorMessage);
    echo json_encode(['success' => false, 'message' => $errorMessage]);
  }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>