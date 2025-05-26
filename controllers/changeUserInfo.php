<?php
require_once __DIR__ . '/../config/databaseConnection.php';
session_start();

if (!isset($_SESSION['userID'])) {
    die("Access denied. Please log in first.");
}

$userId = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ===== Profile Picture Update =====
    if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/photo/Profile_Pictures/';
        $uploadPath = './assets/photo/Profile_Pictures/';
        $fileTmp = $_FILES['profilePic']['tmp_name'];
        $fileName = basename($_FILES['profilePic']['name']);
        $timestamp = time();
        $newFileName = $timestamp . '_' . $fileName;
        $targetFile = $uploadDir . $newFileName;
        $targetPath = $uploadPath . $newFileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($fileTmp);

        if (in_array($fileType, $allowedTypes)) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $oldPic = $stmt->fetchColumn();

            if ($oldPic && $oldPic !== './assets/photo/Profile_Pictures/default.jpg') {
                $oldFilePath = str_replace('./', '../', $oldPic);
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            if (move_uploaded_file($fileTmp, $targetFile)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$targetPath, $userId]);
                header("Location: ../index.php?upload=success");
                exit();
            } else {
                echo "Error moving uploaded file.";
            }
        } else {
            echo "Invalid file type.";
        }
    }

    // ===== Account Info Update =====
    if (isset($_POST['username']) && isset($_POST['email'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);

        if (!empty($username) && !empty($email)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $userId]);
            header("Location: ../index.php?update=success");
            exit();
        }
    }

    // ===== Password Change =====
    if (isset($_POST['currentPassword'], $_POST['newPassword'], $_POST['confirmPassword'])) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentHashed = $stmt->fetchColumn();

        if (!password_verify($_POST['currentPassword'], $currentHashed)) {
            die("Current password does not match.");
        }

        if ($_POST['newPassword'] !== $_POST['confirmPassword']) {
            die("New passwords do not match.");
        }

        $newHashed = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHashed, $userId]);
        header("Location: ../index.php?password=changed");
        exit();
    }
}
?>
