<?php
require 'config/database.php'; // Adjust to your actual database connection file

// Retrieve the species ID from the URL
$species_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($species_id) {
    // Query to fetch species details
    $query = "SELECT species_name, scientific_name, description, category, created_at 
              FROM tree_records 
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $species_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $species = $result->fetch_assoc();
    } else {
        echo "Species details not found.";
        exit;
    }
} else {
    echo "Invalid species ID.";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Species Details - Kalasan</title>
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Species Details</h1>
    <p><strong>Species Name:</strong> <?php echo htmlspecialchars($species['species_name']); ?></p>
    <p><strong>Scientific Name:</strong> <?php echo htmlspecialchars($species['scientific_name']); ?></p>
    <p><strong>Description:</strong> <?php echo htmlspecialchars($species['description']); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($species['category']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($species['created_at']); ?></p>

    <a href="tree_records.php" class="btn btn-primary">Back to Tree Records</a>
</div>
<script src="./assets/js/core/jquery.min.js"></script>
<script src="./assets/js/core/bootstrap.min.js"></script>
</body>
</html>
