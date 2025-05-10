<?php
// Load your JSON file
$jsonData = file_get_contents("./articles.json");
$articles = json_decode($jsonData, true);

foreach ($articles as $article) {
    // Escape strings for SQL
    $link = addslashes($article['article_link']);
    $title = addslashes($article['title']);
    $desc = addslashes($article['description']);
    $topic = addslashes($article['topic']);
    $source = addslashes($article['source_type']);
    $cred = addslashes($article['credibility']);
    $region = addslashes($article['region']);
    $pubDate = $article['published_date'];
    $created = $article['created_at'];
    $author = addslashes($article['author']);

    echo "INSERT INTO articles (article_link, title, description, topic, source_type, credibility, region, published_date, created_at, author)
    VALUES ('$link', '$title', '$desc', '$topic', '$source', '$cred', '$region', '$pubDate', '$created', '$author');\n";
}
?>