<?php
// Database connection
$host = 'localhost';
$dbname = 'div_kalasan_db';
$username = 'root';
$password = '';

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set response header to JSON
    header('Content-Type: application/json');

    // Query to fetch data for tree species grouped by address
    $speciesQuery = $pdo->prepare("
        SELECT address, COUNT(id) AS count 
        FROM tree_planted 
        GROUP BY address
    ");
    $speciesQuery->execute();
    $speciesData = $speciesQuery->fetchAll(PDO::FETCH_ASSOC);

    // Query to fetch upload dates grouped by date only
    $uploadsQuery = $pdo->prepare("
        SELECT DATE(created_at) AS created_at, COUNT(id) AS count 
        FROM tree_planted 
        GROUP BY DATE(created_at) 
        ORDER BY created_at ASC
    ");
    $uploadsQuery->execute();
    $uploadsData = $uploadsQuery->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    echo json_encode([
        'speciesData' => $speciesData,
        'uploadsData' => $uploadsData
    ]);
    
} catch (PDOException $e) {
    // Return a JSON error response
    echo json_encode([
        'error' => 'Connection failed: ' . $e->getMessage()
    ]);
}
?>
