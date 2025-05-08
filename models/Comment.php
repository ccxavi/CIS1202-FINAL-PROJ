<?php
require_once __DIR__ . '/../config/databaseConnection.php';

class Comment {
    public static function getComments($post_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    public static function addComment($post_id, $user_id, $content) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        return $stmt->execute([$post_id, $user_id, $content]);
    }
}
