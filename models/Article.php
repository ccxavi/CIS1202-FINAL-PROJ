<?php
require_once __DIR__ . '/../config/databaseConnection.php';

class Article {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create($articleLink, $previewImageLink, $description) {
        $sql = "INSERT INTO articles (article_link, preview_image_link, description) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleLink, $previewImageLink, $description]);
    }

    public function getAll() {
        $sql = "SELECT * FROM articles ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM articles WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update($id, $articleLink, $previewImageLink, $description) {
        $sql = "UPDATE articles SET article_link = ?, preview_image_link = ?, description = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleLink, $previewImageLink, $description, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM articles WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
} 