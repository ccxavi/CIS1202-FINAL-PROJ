<?php
require_once __DIR__ . '/../config/databaseConnection.php';

class Comment {
    public static function getComments($post_id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT c.*, u.username, u.profile_pic,
            (SELECT COUNT(*) FROM comments WHERE parent_comment_id = c.id) as reply_count,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? AND c.parent_comment_id IS NULL
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
    }

    public static function getReplies($comment_id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT c.*, u.username, u.profile_pic,
            (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) as likes_count
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.parent_comment_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$comment_id]);
        return $stmt->fetchAll();
    }

    public static function addComment($post_id, $user_id, $content, $parent_comment_id = null) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$post_id, $user_id, $content, $parent_comment_id]);
    }
    
    public static function getCommentCount($post_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$post_id]);
        return $stmt->fetchColumn();
    }
    
    public static function isCommentLikedByUser($comment_id, $user_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$comment_id, $user_id]);
        return $stmt->fetchColumn() > 0;
    }
    
    public static function toggleCommentLike($comment_id, $user_id) {
        global $pdo;
        // Check if already liked
        if (self::isCommentLikedByUser($comment_id, $user_id)) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
            $stmt->execute([$comment_id, $user_id]);
            return false; // Not liked anymore
        } else {
            // Like
            $stmt = $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
            $stmt->execute([$comment_id, $user_id]);
            return true; // Now liked
        }
    }
    
    public static function getCommentLikesCount($comment_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        return $stmt->fetchColumn();
    }
    
    public static function timeAgo($datetime) {
        try {
            $now = new DateTime();
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);
    
            if ($diff->y > 0) {
                return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
            }
            if ($diff->m > 0) {
                return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
            }
            if ($diff->d > 0) {
                return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
            }
            if ($diff->h > 0) {
                return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            }
            if ($diff->i > 0) {
                return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            }
            if ($diff->s > 0) {
                return $diff->s . ' second' . ($diff->s > 1 ? 's' : '') . ' ago';
            }
            return 'just now';
        } catch (Exception $e) {
            error_log("Error calculating time ago: " . $e->getMessage());
            return 'some time ago';
        }
    }
}
