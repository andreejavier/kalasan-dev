<?php
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
?>
