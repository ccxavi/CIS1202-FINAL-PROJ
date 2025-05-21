<?php
require_once __DIR__ . '/../config/databaseConnection.php';

class Reaction {
    public static function addLike($post_id, $user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM reactions WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id) VALUES (?, ?)");
            return $stmt->execute([$post_id, $user_id]);
        }
        return false;
    }

    public static function countLikes($post_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = ?");
        $stmt->execute([$post_id]);
        return $stmt->fetchColumn();
    }
}
