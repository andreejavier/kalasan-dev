<?php
session_start();
include 'db_connection.php'; // Ensure this points to your DB connection file

// Check if id is set
if (!isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$tree_id = $_GET['id'];

// Fetch plant data and uploader's info
$sql = "SELECT t.*, u.username, u.profile_picture, COUNT(t2.id) AS observations_count
        FROM `tree_planted` t
        JOIN `users` u ON t.user_id = u.id
        LEFT JOIN `tree_planted` t2 ON t2.user_id = u.id
        WHERE t.id = ? 
        GROUP BY u.id";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Query preparation failed: ' . $conn->error);
}
$stmt->bind_param("i", $tree_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No tree found.";
    exit();
}

$tree = $result->fetch_assoc();

// Fetch other plants by the same user
$other_plants_sql = "SELECT t.id, t.species_name, t.image_path 
                     FROM `tree_planted` t
                     WHERE t.user_id = ? AND t.id != ?";
$stmt = $conn->prepare($other_plants_sql);
$stmt->bind_param("ii", $tree['user_id'], $tree_id);
$stmt->execute();
$other_plants_result = $stmt->get_result();

// Fetch reviews related to the tree
$reviews_sql = "SELECT r.*, u.username 
                FROM `reviews` r 
                JOIN `users` u ON r.review_by = u.id 
                WHERE r.tree_planted_id = ? ORDER BY r.review_date DESC";
$stmt = $conn->prepare($reviews_sql);
$stmt->bind_param("i", $tree_id);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Submit new review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $comments = $_POST['comments'];
    $user_id = $_SESSION['user_id']; // Assuming user is logged in
    $status = 'pending'; // Initial status

    $insert_review_sql = "INSERT INTO reviews (tree_planted_id, review_by, comments, status) 
                          VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_review_sql);
    $stmt->bind_param("iiss", $tree_id, $user_id, $comments, $status);
    if ($stmt->execute()) {
        echo "Review submitted successfully.";
        header("Location: view-tree.php?id=$tree_id"); // Redirect to refresh the page
        exit();
    } else {
        echo "Failed to submit the review.";
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>