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
        $sql = "SELECT * FROM articles WHERE 1=1";
        $params = [];
    
        if (!empty($filters['description'])) {
            $sql .= " AND description LIKE :description";
            $params[':description'] = '%' . $filters['description'] . '%';
        }
    
        if (!empty($filters['topic'])) {
            $sql .= " AND topic = :topic";
            $params[':topic'] = $filters['topic'];
        }
    
        if (!empty($filters['source_type'])) {
            $sql .= " AND source_type = :source_type";
            $params[':source_type'] = $filters['source_type'];
        }
    
        if (!empty($filters['credibility'])) {
            $sql .= " AND credibility = :credibility";
            $params[':credibility'] = $filters['credibility'];
        }
    
        if (!empty($filters['region'])) {
            $sql .= " AND region = :region";
            $params[':region'] = $filters['region'];
        }
    
        if (!empty($filters['date_range'])) {
            $range = explode(':', $filters['date_range']);
            if (count($range) === 2) {
                $sql .= " AND publish_date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $range[0];
                $params[':end_date'] = $range[1];
            }
        }
    
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
    
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
