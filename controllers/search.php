<?php
// Forward all search/filter inputs
session_start();
$_SESSION['search_post_data'] = $_POST;
header("Location: ../views/searchResults.php");
exit;

?>
