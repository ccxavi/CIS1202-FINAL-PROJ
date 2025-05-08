<?php
/**
 * Article Model
 * 
 * This class handles all database operations for articles.
 * To modify or remove this functionality:
 * 1. Comment out or remove the methods you want to disable
 * 2. Update the SQL queries if needed
 * 3. Modify the return types or add new methods as required
 */

require_once __DIR__ . '/../config/databaseConnection.php';

class Article {
    private $pdo;

    /**
     * Constructor - Initializes database connection
     */
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Create a new article
     * @param string $articleLink URL of the article
     * @param string $previewImageLink URL of the preview image
     * @param string $description Article description
     * @return bool Success status
     */
    public function create($articleLink, $previewImageLink, $description) {
        $sql = "INSERT INTO articles (article_link, preview_image_link, description) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleLink, $previewImageLink, $description]);
    }

    /**
     * Get all articles
     * @return array List of all articles
     */
    public function getAll() {
        $sql = "SELECT * FROM articles ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a single article by ID
     * @param int $id Article ID
     * @return array|false Article data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT * FROM articles WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Update an existing article
     * @param int $id Article ID
     * @param string $articleLink New article URL
     * @param string $previewImageLink New preview image URL
     * @param string $description New description
     * @return bool Success status
     */
    public function update($id, $articleLink, $previewImageLink, $description) {
        $sql = "UPDATE articles SET article_link = ?, preview_image_link = ?, description = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$articleLink, $previewImageLink, $description, $id]);
    }

    /**
     * Delete an article
     * @param int $id Article ID
     * @return bool Success status
     */
    public function delete($id) {
        $sql = "DELETE FROM articles WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
} 