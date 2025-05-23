<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "veroDB";

try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Set timezone
    date_default_timezone_set('Asia/Manila');

    // Create posts table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Update existing posts to ensure created_at is set
    $sql = "UPDATE posts SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL";
    $pdo->exec($sql);

    // Create reactions table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS reactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        reaction_type ENUM('like') DEFAULT 'like',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_reaction (post_id, user_id)
    )";
    $pdo->exec($sql);
    
    // Create comments table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        parent_comment_id INT DEFAULT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Create comment_likes table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS comment_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        comment_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_comment_like (comment_id, user_id)
    )";
    $pdo->exec($sql);

    // Create collections table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS collections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Create bookmarks table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        collection_id INT NOT NULL,
        title VARCHAR(255),
        article_link TEXT NOT NULL,
        author VARCHAR(255),
        published_date DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);

    // Update existing tables to add is_active column if it doesn't exist
    $sql = "ALTER TABLE collections ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE";
    $pdo->exec($sql);

    $sql = "ALTER TABLE bookmarks ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE";
    $pdo->exec($sql);

    // Add role column to users table if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user'";
    $pdo->exec($sql);

    // Add status column to users table if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS status ENUM('active', 'banned') DEFAULT 'active'";
    $pdo->exec($sql);

    // Add is_hidden and is_flagged columns to posts table if they don't exist
    $pdo->exec("ALTER TABLE posts 
        ADD COLUMN IF NOT EXISTS is_hidden BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS is_flagged BOOLEAN DEFAULT FALSE");

    // Add is_hidden and is_flagged columns to comments table if they don't exist
    $pdo->exec("ALTER TABLE comments 
        ADD COLUMN IF NOT EXISTS is_hidden BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS is_flagged BOOLEAN DEFAULT FALSE");

    // Connection successful
    // echo "Connected successfully";
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}