<?php
session_start();
include 'config/db_connection.php'; // Include your database connection

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tree_planted_id = $_POST['tree_planted_id'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $review_by = $_SESSION['admin_id']; // Admin's ID from session

    // Insert the review into the database
    $sql = "INSERT INTO reviews (tree_planted_id, review_by, status, comments) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiss", $tree_planted_id, $review_by, $status, $comments);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Review submitted successfully!";
        } else {
            $_SESSION['error'] = "Failed to submit review: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing statement: " . $conn->error;
    }

    $conn->close();

    // Redirect back to the tree details page
    header("Location: view-tree.php?id=" . $tree_planted_id);
    exit();
}
?>
