<?php
/**
 * Articles API Endpoint
 * 
 * This file handles all CRUD operations for articles.
 */

header('Content-Type: application/json');

// ✅ Include DB Connection
require_once __DIR__ . '/../config/databaseConnection.php';

// ✅ Include Article model
require_once __DIR__ . '/../models/Article.php';

// ✅ Create Article instance with PDO
$article = new Article($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $filters = [];

            if (isset($_GET['topic'])) {
                $filters['topic'] = $_GET['topic'];
            }
            if (isset($_GET['source_type'])) {
                $filters['source_type'] = $_GET['source_type'];
            }
            if (isset($_GET['credibility'])) {
                $filters['credibility'] = $_GET['credibility'];
            }
            if (isset($_GET['region'])) {
                $filters['region'] = $_GET['region'];
            }
            if (isset($_GET['date_range'])) {
                $filters['date_range'] = $_GET['date_range'];
            }

            $result = $article->getFilteredArticles($filters);

            if ($result) {
                echo json_encode(['status' => 'success', 'data' => $result]);
            } else {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'No articles found']);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['article_link']) || !isset($data['preview_image_link']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }

            $result = $article->create($data['article_link'], $data['preview_image_link'], $data['description']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create article']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id']) || !isset($data['article_link']) || !isset($data['preview_image_link']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }

            $result = $article->update($data['id'], $data['article_link'], $data['preview_image_link'], $data['description']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update article']);
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing article ID']);
                break;
            }

            $result = $article->delete($_GET['id']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete article']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
