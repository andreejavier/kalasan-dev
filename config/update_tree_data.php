<?php
header('Content-Type: application/json');

require 'db_connect.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve data from the request
$data = json_decode(file_get_contents("php://input"), true);
$tree_id = $data['id'];
$validated = $data['validated'];

// Prepare and execute the update query
$sql = "UPDATE tree_planted SET validated = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $validated, $tree_id);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = "Tree data updated successfully.";
} else {
    $response['success'] = false;
    $response['message'] = "Error updating tree data: " . $stmt->error;
}

// Close the connection
$stmt->close();
$conn->close();

// Output JSON response
echo json_encode($response);
?>
