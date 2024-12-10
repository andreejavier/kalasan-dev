<?php
session_start();
include 'db_connection.php';

// Check if user is logged in and get the user_id from session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Query to fetch tree records along with additional images
$query = "
    SELECT tr.id, tr.date_time, tr.address, tr.latitude, tr.longitude, tr.species_name, tr.created_at, 
           ti.image_path AS additional_image
    FROM tree_planted tr
    LEFT JOIN tree_images ti ON tr.id = ti.tree_record_id
    WHERE tr.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize an empty array to hold tree records
$trees = [];

while ($row = $result->fetch_assoc()) {
    // Check if the tree record already exists in the array
    if (!isset($trees[$row['id']])) {
        $trees[$row['id']] = [
            'id' => $row['id'],
            'date_time' => $row['date_time'],
            'address' => $row['address'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'species_name' => $row['species_name'],
            'created_at' => $row['created_at'],
            'images' => [] // Initialize an empty array for images
        ];
    }

    // Add the image path if available
    if ($row['additional_image']) {
        $trees[$row['id']]['images'][] = $row['additional_image'];
    }
}

// Convert the associative array into a numerically indexed array
$trees = array_values($trees);

echo json_encode($trees);

mysqli_close($conn);
?>
