<?php
/**
 * Articles API Endpoint
 * 
 * This file handles all CRUD operations for articles.
 * To remove or modify this API:
 * 1. Comment out or remove the entire switch case for the method you want to disable
 * 2. Update the response format if needed
 * 3. Modify the validation rules in the respective cases
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../models/Article.php';

$article = new Article();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // ===== API ROUTING =====
    switch ($method) {
        // ===== GET REQUESTS =====
        case 'GET':
            // Get single article by ID
            if (isset($_GET['id'])) {
                $result = $article->getById($_GET['id']);
                if ($result) {
                    echo json_encode(['status' => 'success', 'data' => $result]);
                } else {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Article not found']);
                }
            } 
            // Get all articles
            else {
                $result = $article->getAll();
                echo json_encode(['status' => 'success', 'data' => $result]);
            }
            break;

        // ===== POST REQUESTS =====
        case 'POST':
            // Validate required fields
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['article_link']) || !isset($data['preview_image_link']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }

            // Create new article
            $result = $article->create($data['article_link'], $data['preview_image_link'], $data['description']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create article']);
            }
            break;

        // ===== PUT REQUESTS =====
        case 'PUT':
            // Validate required fields
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id']) || !isset($data['article_link']) || !isset($data['preview_image_link']) || !isset($data['description'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
                break;
            }

            // Update article
            $result = $article->update($data['id'], $data['article_link'], $data['preview_image_link'], $data['description']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to update article']);
            }
            break;

        // ===== DELETE REQUESTS =====
        case 'DELETE':
            // Validate ID parameter
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing article ID']);
                break;
            }

            // Delete article
            $result = $article->delete($_GET['id']);
            if ($result) {
                echo json_encode(['status' => 'success', 'message' => 'Article deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete article']);
            }
            break;

        // ===== INVALID METHODS =====
        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 