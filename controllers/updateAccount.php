<?php
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../models/userModel.php'; // Assuming findUserByID and other necessary functions are here
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

$userID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Validate input
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username and email are required.']);
        exit();
    }

    if (strlen($username) < 2) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 2 characters.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }

    // Check if email is already in use by another user
    $currentUser = findUserByID($userID); // Fetch current user details
    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit();
    }

    if (strtolower($email) !== strtolower($currentUser['email'])) {
        $existingUserByEmail = findUserByEmail($email);
        if ($existingUserByEmail) {
            echo json_encode(['success' => false, 'message' => 'Email already in use by another account.']);
            exit();
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$username, $email, $userID])) {
            // Optionally, update session data if username/email is stored there directly for display
            // For now, client-side will refresh relevant parts or user can be prompted to re-login for some changes.
            echo json_encode(['success' => true, 'message' => 'Account details updated successfully.', 'newUsername' => $username, 'newEmail' => $email]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update account details.']);
        }
    } catch (PDOException $e) {
        // Log error $e->getMessage() for server-side debugging
        echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?> 