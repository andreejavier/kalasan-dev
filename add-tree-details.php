<?php 
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Database configuration

$servername = "sql211.infinityfree.com";
$username = "if0_38393967"; 
$password = "cqsYanZIbqY "; 
$dbname = "if0_38393967_kalasan_db";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tree data based on ID
$tree_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$tree = null;
$images = [];

if ($tree_id) {
    // Fetch main tree data and associated user
    $stmt = $conn->prepare("SELECT t.*, u.username, u.email 
                            FROM tree_planted t 
                            JOIN users u ON t.user_id = u.id 
                            WHERE t.id = ?");
    $stmt->bind_param("i", $tree_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tree = $result->fetch_assoc();
    $stmt->close();

    // Fetch associated images from tree_images table
    $stmt = $conn->prepare("SELECT image_path FROM tree_images WHERE tree_planted_id = ?");
    $stmt->bind_param("i", $tree_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row['image_path'];
    }
    $stmt->close();
}

// Update tree data if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_time = $_POST['date_time'];
    $species_name = $_POST['species_name'];
    $scientific_name = $_POST['scientific_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];

    // Handle image upload if a new image is uploaded
    if (isset($_FILES['tree_image']) && $_FILES['tree_image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB limit

        if (in_array($_FILES['tree_image']['type'], $allowed_types) && $_FILES['tree_image']['size'] <= $max_size) {
            $image_path = 'uploads/' . basename($_FILES['tree_image']['name']);
            if (move_uploaded_file($_FILES['tree_image']['tmp_name'], $image_path)) {
                // Insert the new image into the tree_images table
                $stmt = $conn->prepare("INSERT INTO tree_images (tree_planted_id, image_path) VALUES (?, ?)");
                $stmt->bind_param("is", $tree_id, $image_path);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "<p>Error uploading image. Please try again.</p>";
            }
        } else {
            echo "<p>Invalid file type or file too large.</p>";
        }
    }

    // Prepare the SQL update statement
    $stmt = $conn->prepare("UPDATE tree_planted 
                            SET date_time = ?, species_name = ?, scientific_name = ?, description = ?, category = ? 
                            WHERE id = ?");
    $stmt->bind_param("sssssi", $date_time, $species_name, $scientific_name, $description, $category, $tree_id);

    // Execute the update and handle success or failure
    if ($stmt->execute()) {
        // Redirect with an alert message after successful update
        header("Location: manage-record.php?alert=" . urlencode("Tree data updated successfully."));
        exit();
    } else {
        echo "<p>Error updating tree data: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Tree Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 400px; /* Ensure the map has enough height */
            width: 100%;
        }
        .tree-image {
            max-height: 400px;
            width: 100%;
            object-fit: cover;
        }
        /* Additional styling */
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <!-- Map Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4>Tree Location</h4>
                </div>
                <div class="card-body">
                    <div id="map"></div> <!-- Map container -->
                </div>
            </div>
        </div>

        <!-- Images and Form Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2>Update Tree Details</h2>
                </div>
                <div class="card-body">
 <!-- Display current images -->
<div class="form-group mb-4">
    <?php if (!empty($images)): ?>
        <div id="carousel<?php echo $tree_id; ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $image): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($image); ?>" class="d-block w-100 tree-image" alt="Tree Image" onclick="viewFullScreen(this)">
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="carousel-control-prev" href="#carousel<?php echo $tree_id; ?>" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carousel<?php echo $tree_id; ?>" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </a>
        </div>
    <?php else: ?>
        <p>No image available.</p>
    <?php endif; ?>
</div>

                    <!-- Tree form inputs -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="species_name">Species Name</label>
                            <input type="text" class="form-control" id="species_name" name="species_name" value="<?= htmlspecialchars($tree['species_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="scientific_name">Scientific Name</label>
                            <input type="text" class="form-control" id="scientific_name" name="scientific_name" value="<?= htmlspecialchars($tree['scientific_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required><?= htmlspecialchars($tree['description']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($tree['category']) ?>" required>
                        </div>

                        <!-- Input for new image -->
                        <div class="form-group">
                            <label for="tree_image">Upload New Image (optional)</label>
                            <input type="file" class="form-control" id="tree_image" name="tree_image">
                        </div>

                        <button type="submit" class="btn btn-primary">Update Tree</button>
                        <a href="./manage-record.php" class="btn btn-secondary">Back to List</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if the latitude and longitude are available
        const lat = <?= isset($tree['latitude']) ? htmlspecialchars($tree['latitude']) : 0 ?>;
        const lng = <?= isset($tree['longitude']) ? htmlspecialchars($tree['longitude']) : 0 ?>;

        if (lat && lng) {
            // Initialize the map
            var map = L.map('map').setView([lat, lng], 13);

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);

            // Add a marker at the tree location
            L.marker([lat, lng]).addTo(map)
                .bindPopup('Tree Location')
                .openPopup();
        } else {
            console.error('Invalid or missing coordinates');
        }
    });
</script>
<script>
    function viewFullScreen(imageElement) {
        if (imageElement.requestFullscreen) {
            imageElement.requestFullscreen();
        } else if (imageElement.mozRequestFullScreen) { // Firefox
            imageElement.mozRequestFullScreen();
        } else if (imageElement.webkitRequestFullscreen) { // Chrome, Safari, Opera
            imageElement.webkitRequestFullscreen();
        } else if (imageElement.msRequestFullscreen) { // IE/Edge
            imageElement.msRequestFullscreen();
        }
    }
</script>
</body>
</html>
