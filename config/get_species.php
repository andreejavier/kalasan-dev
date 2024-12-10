<?php
// Database connection
require 'db_connection.php';

// Fetch all species
$query = "SELECT id, species_name, scientific_name FROM tree_planted";
$result = mysqli_query($conn, $query);

$species = array();

while ($row = mysqli_fetch_assoc($result)) {
    $species[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($species);
?>
