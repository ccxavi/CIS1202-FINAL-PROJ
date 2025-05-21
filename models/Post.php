<?php
require_once __DIR__ . '/../config/databaseConnection.php';

class Post {
    public static function getAllPosts() {
        global $pdo;
        $stmt = $pdo->query("SELECT posts.*, users.username, users.profile_pic FROM posts JOIN users ON posts.user_id = users.id ORDER BY created_at DESC");        
        return $stmt->fetchAll();
    }

    public static function addPost($user_id, $content) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        return $stmt->execute([$user_id, $content]);
    }
}
