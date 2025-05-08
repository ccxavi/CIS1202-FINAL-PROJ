<?php
// Test data
$testArticle = [
    'article_link' => 'https://example.com/test-article',
    'preview_image_link' => 'https://example.com/test-image.jpg',
    'description' => 'This is a test article description'
];

// Function to make API calls
function makeRequest($method, $url, $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Test 1: Create Article
echo "Test 1: Creating article...\n";
$createResult = makeRequest('POST', 'http://localhost/CIS1202-FINAL-PROJ/api/articles.php', $testArticle);
echo "Status Code: " . $createResult['code'] . "\n";
echo "Response: " . json_encode($createResult['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Get All Articles
echo "Test 2: Getting all articles...\n";
$getAllResult = makeRequest('GET', 'http://localhost/CIS1202-FINAL-PROJ/api/articles.php');
echo "Status Code: " . $getAllResult['code'] . "\n";
echo "Response: " . json_encode($getAllResult['response'], JSON_PRETTY_PRINT) . "\n\n";

// Get the ID of the first article for update and delete tests
$articleId = null;
if (isset($getAllResult['response']['data']) && !empty($getAllResult['response']['data'])) {
    $articleId = $getAllResult['response']['data'][0]['id'];
}

if ($articleId) {
    // Test 3: Get Single Article
    echo "Test 3: Getting single article...\n";
    $getSingleResult = makeRequest('GET', "http://localhost/CIS1202-FINAL-PROJ/api/articles.php?id=$articleId");
    echo "Status Code: " . $getSingleResult['code'] . "\n";
    echo "Response: " . json_encode($getSingleResult['response'], JSON_PRETTY_PRINT) . "\n\n";

    // Test 4: Update Article
    echo "Test 4: Updating article...\n";
    $updateData = array_merge($testArticle, [
        'id' => $articleId,
        'description' => 'Updated test description'
    ]);
    $updateResult = makeRequest('PUT', 'http://localhost/CIS1202-FINAL-PROJ/api/articles.php', $updateData);
    echo "Status Code: " . $updateResult['code'] . "\n";
    echo "Response: " . json_encode($updateResult['response'], JSON_PRETTY_PRINT) . "\n\n";

    // Test 5: Delete Article
    echo "Test 5: Deleting article...\n";
    $deleteResult = makeRequest('DELETE', "http://localhost/CIS1202-FINAL-PROJ/api/articles.php?id=$articleId");
    echo "Status Code: " . $deleteResult['code'] . "\n";
    echo "Response: " . json_encode($deleteResult['response'], JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No articles found to test single article operations.\n";
} 