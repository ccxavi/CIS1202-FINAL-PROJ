<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/databaseConnection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    $userID = $_SESSION['userID'];

    // Handle file upload
    $uploadDir = __DIR__ . '/../assets/uploads/verification/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Check if ID document was uploaded
    if (!isset($_FILES['verificationId']) || $_FILES['verificationId']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = isset($_FILES['verificationId']) ? getUploadErrorMessage($_FILES['verificationId']['error']) : 'Please upload your ID for verification';
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }

    $documentFile = $_FILES['verificationId'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // Get file info
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $documentFile['tmp_name']);
    finfo_close($finfo);

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a valid image file (JPEG, PNG, or GIF)']);
        exit;
    }

    // Generate unique filename
    $extension = strtolower(pathinfo($documentFile['name'], PATHINFO_EXTENSION));
    $filename = uniqid('id_') . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($documentFile['tmp_name'], $targetPath)) {
        error_log("Failed to move uploaded file from {$documentFile['tmp_name']} to {$targetPath}");
        echo json_encode(['success' => false, 'message' => 'Failed to upload file. Please try again.']);
        exit;
    }

    // Update database
    $pdo = getDatabaseConnection();
    
    // First, check if a verification request already exists
    $stmt = $pdo->prepare("SELECT verification_status FROM users WHERE id = ?");
    $stmt->execute([$userID]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus === 'pending') {
        echo json_encode(['success' => false, 'message' => 'You already have a pending verification request']);
        exit;
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update user verification details
        $stmt = $pdo->prepare("
            UPDATE users 
            SET verification_document = ?, 
                verification_status = 'pending',
                verification_submitted_at = NOW()
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $filename,
            $userID
        ]);

        if (!$success) {
            throw new Exception('Failed to update database: ' . implode(', ', $stmt->errorInfo()));
        }

        // Verify the update was successful
        $stmt = $pdo->prepare("SELECT verification_status FROM users WHERE id = ?");
        $stmt->execute([$userID]);
        $updatedUser = $stmt->fetch();

        if ($updatedUser['verification_status'] !== 'pending') {
            throw new Exception('Database update verification failed');
        }

        // Commit transaction
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Your verification document has been submitted successfully. Our team will review it shortly.',
            'debug_info' => [
                'status' => $updatedUser['verification_status']
            ]
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Database Error in handleProfileAuth.php: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in handleProfileAuth.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while processing your request. Please try again.',
        'debug_message' => $e->getMessage()
    ]);
}

// Helper function to get upload error messages
function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
} 