# Articles API Documentation

This document provides comprehensive documentation for the Articles API endpoints.

## Base URL
```
http://localhost/CIS1202-FINAL-PROJ/api/articles.php
```

## Authentication
Currently, the API does not require authentication.

## JSON Structure

### Article Object
```json
{
    "id": "integer (auto-increment)",
    "article_link": "string (URL)",
    "preview_image_link": "string (URL)",
    "description": "string (text)",
    "created_at": "timestamp (YYYY-MM-DD HH:MM:SS)",
    "updated_at": "timestamp (YYYY-MM-DD HH:MM:SS)"
}
```

### Field Descriptions
| Field | Type | Description | Required | Example |
|-------|------|-------------|----------|---------|
| id | Integer | Unique identifier | No (auto-generated) | 1 |
| article_link | String | URL to the full article | Yes | "https://example.com/article" |
| preview_image_link | String | URL to the article's preview image | Yes | "https://example.com/image.jpg" |
| description | String | Short description of the article | Yes | "This is an article about..." |
| created_at | Timestamp | When the article was created | No (auto-generated) | "2024-05-08 23:53:17" |
| updated_at | Timestamp | When the article was last updated | No (auto-generated) | "2024-05-08 23:53:17" |

### Response Structure
All API responses follow this format:
```json
{
    "status": "string (success/error)",
    "data": "object/array (for GET requests)",
    "message": "string (for POST/PUT/DELETE requests)"
}
```

### Error Response Structure
```json
{
    "status": "error",
    "message": "string (error description)"
}
```

## Endpoints

### 1. Get All Articles
Retrieves a list of all articles.

**Request:**
```http
GET /api/articles.php
```

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "article_link": "https://example.com/article",
            "preview_image_link": "https://example.com/image.jpg",
            "description": "Article description",
            "created_at": "2024-05-08 23:53:17",
            "updated_at": "2024-05-08 23:53:17"
        }
    ]
}
```

### 2. Get Single Article
Retrieves a specific article by ID.

**Request:**
```http
GET /api/articles.php?id=1
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "article_link": "https://example.com/article",
        "preview_image_link": "https://example.com/image.jpg",
        "description": "Article description",
        "created_at": "2024-05-08 23:53:17",
        "updated_at": "2024-05-08 23:53:17"
    }
}
```

### 3. Create Article
Creates a new article.

**Request:**
```http
POST /api/articles.php
Content-Type: application/json

{
    "article_link": "https://example.com/article",
    "preview_image_link": "https://example.com/image.jpg",
    "description": "Article description"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Article created successfully"
}
```

### 4. Update Article
Updates an existing article.

**Request:**
```http
PUT /api/articles.php
Content-Type: application/json

{
    "id": 1,
    "article_link": "https://example.com/updated-article",
    "preview_image_link": "https://example.com/updated-image.jpg",
    "description": "Updated description"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Article updated successfully"
}
```

### 5. Delete Article
Deletes an article.

**Request:**
```http
DELETE /api/articles.php?id=1
```

**Response:**
```json
{
    "status": "success",
    "message": "Article deleted successfully"
}
```

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
    "status": "error",
    "message": "Missing required fields"
}
```

### 404 Not Found
```json
{
    "status": "error",
    "message": "Article not found"
}
```

### 500 Internal Server Error
```json
{
    "status": "error",
    "message": "Error message details"
}
```

## Example Usage

### JavaScript/Fetch API
```javascript
// Get all articles
async function getAllArticles() {
    const response = await fetch('http://localhost/CIS1202-FINAL-PROJ/api/articles.php');
    const data = await response.json();
    return data;
}

// Get single article
async function getArticle(id) {
    const response = await fetch(`http://localhost/CIS1202-FINAL-PROJ/api/articles.php?id=${id}`);
    const data = await response.json();
    return data;
}

// Create article
async function createArticle(articleData) {
    const response = await fetch('http://localhost/CIS1202-FINAL-PROJ/api/articles.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(articleData)
    });
    const data = await response.json();
    return data;
}

// Update article
async function updateArticle(articleData) {
    const response = await fetch('http://localhost/CIS1202-FINAL-PROJ/api/articles.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(articleData)
    });
    const data = await response.json();
    return data;
}

// Delete article
async function deleteArticle(id) {
    const response = await fetch(`http://localhost/CIS1202-FINAL-PROJ/api/articles.php?id=${id}`, {
        method: 'DELETE'
    });
    const data = await response.json();
    return data;
}
```

### Axios Example
```javascript
import axios from 'axios';

const API_URL = 'http://localhost/CIS1202-FINAL-PROJ/api/articles.php';

// Get all articles
const getAllArticles = async () => {
    const response = await axios.get(API_URL);
    return response.data;
};

// Get single article
const getArticle = async (id) => {
    const response = await axios.get(`${API_URL}?id=${id}`);
    return response.data;
};

// Create article
const createArticle = async (articleData) => {
    const response = await axios.post(API_URL, articleData);
    return response.data;
};

// Update article
const updateArticle = async (articleData) => {
    const response = await axios.put(API_URL, articleData);
    return response.data;
};

// Delete article
const deleteArticle = async (id) => {
    const response = await axios.delete(`${API_URL}?id=${id}`);
    return response.data;
};
```

## Best Practices

1. **Error Handling**
   - Always check the response status and handle errors appropriately
   - Implement proper error messages for users
   - Log errors for debugging purposes

2. **Data Validation**
   - Validate all input data before sending to the API
   - Ensure URLs are properly formatted
   - Check for required fields

3. **Loading States**
   - Implement loading states while waiting for API responses
   - Show appropriate loading indicators to users

4. **Caching**
   - Consider implementing caching for GET requests
   - Cache article data when appropriate to reduce API calls

5. **Security**
   - Sanitize all user input
   - Validate URLs before sending to the API
   - Implement proper CORS headers if needed

## Support

For any issues or questions regarding the API, please contact the development team. 