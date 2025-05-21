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
    private $db;

    // Constructor to receive PDO instance
    public function __construct($db) {
        $this->db = $db;
    }

    public function getFilteredArticles($filters = []) {
        $sql = "SELECT * FROM articles WHERE 1";

        if (isset($filters['topic'])) {
            $sql .= " AND topic = :topic";
        }
        if (isset($filters['source_type'])) {
            $sql .= " AND source_type = :source_type";
        }
        if (isset($filters['credibility'])) {
            $sql .= " AND credibility = :credibility";
        }
        if (isset($filters['region'])) {
            $sql .= " AND region = :region";
        }
        if (isset($filters['date_range'])) {
            $dateRange = explode(":", $filters['date_range']);
            $sql .= " AND publish_date BETWEEN :start_date AND :end_date";
        }

        $stmt = $this->db->prepare($sql);

        if (isset($filters['topic'])) {
            $stmt->bindParam(':topic', $filters['topic']);
        }
        if (isset($filters['source_type'])) {
            $stmt->bindParam(':source_type', $filters['source_type']);
        }
        if (isset($filters['credibility'])) {
            $stmt->bindParam(':credibility', $filters['credibility']);
        }
        if (isset($filters['region'])) {
            $stmt->bindParam(':region', $filters['region']);
        }
        if (isset($filters['date_range'])) {
            $stmt->bindParam(':start_date', $dateRange[0]);
            $stmt->bindParam(':end_date', $dateRange[1]);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
