<?php
// get_plant_data.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u593341949_devKalasan2024"; 
$password = "DEV-Kalasan2024"; 
$dbname = "u593341949_dev_kalasan";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch plant data
$sql = "SELECT * FROM `tree_planted`"; 
$result = $conn->query($sql);

$plants = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $plants[] = $row; // Append each row to the plants array
    }
}

$conn->close();

// Output JSON data
echo json_encode($plants);
?>
