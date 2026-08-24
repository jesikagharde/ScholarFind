<?php
require_once 'db.php';

$stmt = $pdo->query("SELECT scholarship_id, title, application_url FROM scholarships");
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $url = $row['application_url'];
    if (empty($url) || $url === '#') continue;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "ID: {$row['scholarship_id']} | Code: {$http_code} | URL: {$url} | Title: {$row['title']}\n";
}
