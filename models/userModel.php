<?php
require_once __DIR__ . '/../config/databaseConnection.php';

function register($userName, $email, $hashedPassword) // a function for signing up a user
{
    global $pdo;
    try{
        $sql = "INSERT INTO users (userName, email, password) 
                VALUES (:userName, :email, :password)";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error signing up user: " . $e->getMessage());
        return false; // can change this to throw a custom exception if needed
    }
    
}

function findUserByEmail($email) // a function to find a user by email
{
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error finding user by email: " . $e->getMessage());
        return false;
    }
    
}

function findUserByID($userID) // a function to find a user by ID
{
    global $pdo;
    try {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $userID);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error finding user by ID: " . $e->getMessage());
        return false;
    }
    
}