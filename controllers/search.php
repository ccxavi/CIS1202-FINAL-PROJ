<?php
// Forward all search/filter inputs
session_start();

// Get the search query from POST data
$searchQuery = $_POST['query'] ?? '';

// Store the search query in the URL parameters
header("Location: ../views/searchResults.php?query=" . urlencode($searchQuery));
exit;

?>
