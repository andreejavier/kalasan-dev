<?php
session_start();
include 'config/db_connection.php'; // Ensure this points to your DB connection file

// Check if id is set
if (!isset($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$tree_id = $_GET['id'];
$admin_id = $_SESSION['admin_id']; // Assuming admin is logged in and admin_id is stored in the session

// Fetch tree planting details
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
$tree = $result->fetch_assoc();
$stmt->close();

// Fetch all reviews for this tree planting
$reviews_sql = "SELECT r.*, a.admin_name FROM `reviews` r JOIN `admin` a ON r.review_by = a.admin_id WHERE r.tree_planted_id = ?";
$stmt = $conn->prepare($reviews_sql);
$stmt->bind_param("i", $tree_id);
$stmt->execute();
$reviews_result = $stmt->get_result();
$stmt->close();

// Handle review submission by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    
    $insert_review_sql = "INSERT INTO `reviews` (tree_planted_id, review_by, status, comments) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_review_sql);
    $stmt->bind_param("iiss", $tree_id, $admin_id, $status, $comments);
    
    if ($stmt->execute()) {
        header("Location: view-tree.php?id=$tree_id");
        exit();
    } else {
        echo "Error submitting review: " . $conn->error;
    }
    
    $stmt->close();
}
// Fetch other trees planted by the same user (excluding the current tree)
$other_trees_sql = "SELECT id, species_name, scientific_name, date_time, latitude, longitude, image_path
                    FROM `tree_planted`
                    WHERE user_id = ? AND id != ?";
$stmt = $conn->prepare($other_trees_sql);
$stmt->bind_param("ii", $tree['user_id'], $tree_id);
$stmt->execute();
$other_trees_result = $stmt->get_result();
$stmt->close();
?>


$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Tree Details</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!-- CSS Files -->
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <!-- CSS for Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/css/lightgallery.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.3.0/lightgallery.min.js"></script>

    
    <style>
        #map { height: 250px; width: 100%; margin-top: 20px; border: 1px solid #ddd; }
         /* Adjust styles to support the LightGallery */
  .tree-image {
    width: 100%;
    height: auto;
    cursor: pointer; /* Make it clear the image is clickable */
  }
        .species-details { font-style: italic; color: #555; }
        .user-info { display: flex; align-items: center; gap: 10px; margin-top: 20px; }
        .user-info img { border-radius: 50%; width: 40px; height: 40px; }
        .review-form { margin-top: 30px; }
        .review-item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .review-item:last-child { border-bottom: none; }

        .custom-close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background-color: #000;
  color: #fff;
  border: none;
  padding: 10px;
  font-size: 18px;
  cursor: pointer;
  z-index: 1001; /* Ensure the button appears above the image */
  border-radius: 5px;
  opacity: 0.7;
}

.custom-close-btn:hover {
  opacity: 1;
}
.sidebar {
    width: 260px;
    transition: transform 0.3s ease-in-out;
    position: fixed;
    top: 0;
    left: 0;
    height: 100%;
    z-index: 1000;
    background-color: #2a513b; /* Forest green */
    color: #e0f5e5; /* Light green text */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); /* Subtle shadow */
}

.sidebar .nav li a {
    font-size: 16px;
    font-weight: 500;
}

.sidebar-wrapper .nav li.active a {
    background-color: #81C784; /* Active background color */
    color: #fff; /* Text color for the active item */
    border-radius: 8px; /* Optional: Rounded corners */
}

.sidebar-wrapper .nav li.active a i {
    color: #fff; /* Icon color for the active item */
}

/* Sidebar logo styles */
.sidebar .logo {
    background-color: #254d36; /* Slightly darker green for logo area */
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #3b6e4d; /* Divider below the logo */
}

.sidebar .logo .simple-text.logo-normal {
    color: #e0f5e5; 
    font-family: 'WoodFont', serif;
    font-size: 22px; 
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Sidebar toggle button */
.menu-toggle {
    font-size: 24px;
    cursor: pointer;
    color: #2a513b;
    margin-right: 15px;
    display: none;
}

/* Main panel adjustment for sidebar */
.main-panel {
    padding-top: 80px;
    transition: margin-left 0.3s ease-in-out;
    margin-left: 260px;
}

.main-panel.expanded {
    margin-left: 0;
}

/* Responsive styles */
@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }
    .sidebar {
        transform: translateX(-260px);
    }
    .sidebar.expanded {
        transform: translateX(0);
    }
    .main-panel {
        margin-left: 0;
    }
    .main-panel.expanded {
        margin-left: 260px;
    }
  }


        
    </style>
</head>
<body class="">
<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" data-color="white" data-active-color="white" id="sidebar">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">
                <div class="logo-image-small">
                    <img src="./assets/img/tree.png" alt="Logo">
                </div>
            </a>
            <a href="#" class="simple-text logo-normal">Kalasan</a>
        </div>
      <div class="sidebar-wrapper">
        <ul class="nav">
          <li>
            <a href="./home.php">
              <i class="nc-icon nc-bank"></i>
              <p>Home</p>
            </a>
          </li>
          <li>
            <a href="./map.php">
              <i class="nc-icon nc-pin-3"></i>
              <p>Maps</p>
            </a>
          </li>
          <li>
            <a href="./manage-record.php">
              <i class="fa fa-tasks"></i>
              <p>Manage Records</p>
            </a>
          </li>
          <li class="active">
            <a href="./tree-species-form.php">
              <i class="fa fa-tree"></i>
              <p>Tree Species</p>
            </a>
          </li>
          <li>
            <a href="./contributors-datatable.php">
              <i class="fa fa-users"></i>
              <p>Manage User</p>
            </a>
          </li>
          <li>
            <a href="./validate-records.php">
              <i class="fa fa-clipboard"></i>
              <p>Tree Records</p>
            </a>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Main Panel -->
    <div class="main-panel" id="mainPanel">
        <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <span class="menu-toggle" id="menuToggle">
                        <i class="fa fa-bars"></i>
                    </span>
                    <a class="navbar-brand" href="javascript:;">Dashboard & Analytics</a>
                </div>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="nc-icon nc-single-02"></i>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="profile.php">View Profile</a>
                            <a class="dropdown-item" href="settings.php">Settings</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
      <!-- End Navbar -->

      <!-- Main Content -->
        <div class="container">
          <div class="header-section">
            <div>
              <h5><?php echo htmlspecialchars($tree['species_name']); ?></h5>
              <p class="species-details">(<?php echo htmlspecialchars($tree['scientific_name']); ?>)</p>
            </div>
          </div>

<!-- Display Tree and User Information -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
      <?php if (!empty($tree['image_path'])): ?>
      <img src="<?php echo htmlspecialchars($tree['image_path']); ?>" alt="Tree Image" class="tree-image" id="treeImage" onclick="openFullscreen();">
  <?php else: ?>
      <p>No image available.</p>
  <?php endif; ?>
      </div>
                <div class="col-md-6">
                  <div class="user-info mt-4">
                    <img src="<?php echo !empty($tree['profile_picture']) ? htmlspecialchars($tree['profile_picture']) : 'assets/img/user-avatar.png'; ?>" alt="User Avatar">
                    <div>
                      <p><strong>Uploaded by:</strong> <a href="#"><?php echo htmlspecialchars($tree['username']); ?></a></p>
                    </div>
                  </div>
                  <div id="map"></div>
                  <div class="map-details">
                    <p><strong>Location Map:</strong> <?php echo htmlspecialchars($tree['address']); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($tree['description']); ?></p>
                  </div>
                </div>
              </div>
              <div class="observation-details mt-3">
                <p><strong>Location:</strong> <?php echo htmlspecialchars($tree['address']); ?></p>
                <p><strong>Date Observed:</strong> <?php echo htmlspecialchars($tree['date_time']); ?></p>
              </div>
            </div>
          </div>

          <!-- Admin Review Form -->
          <div class="review-form">
            <h3>Leave a Review</h3>
            <form action="view-tree.php?id=<?php echo $tree_id; ?>" method="POST">
              <div class="form-group">
                <label for="status">Status</label>
                <select name="status" class="form-control" id="status">
                  <option value="agree">Agree</option>
                  <option value="disagree">Disagree</option>
                </select>
              </div>
              <div class="form-group">
                <label for="comments">Comments</label>
                <textarea name="comments" id="comments" class="form-control" rows="4" placeholder="Leave your comments here..."></textarea>
              </div>
              <button type="submit" name="submit_review" class="btn btn-success">Submit Review</button>
            </form>
          </div>

          <!-- Display Existing Reviews -->
          <div class="reviews mt-5">
            <h3>Reviews</h3>
            <?php if ($reviews_result->num_rows > 0): ?>
                <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <div class="review-item">
                        <h4>Review by <?php echo htmlspecialchars($review['admin_name']); ?> - <small><?php echo htmlspecialchars($review['status']); ?></small></h4>
                        <p><?php echo htmlspecialchars($review['comments']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
          </div>
        </div>
        <div class="other-trees mt-5">
    <h3>Other Trees Planted by <?php echo htmlspecialchars($tree['username']); ?></h3>
    <?php if ($other_trees_result->num_rows > 0): ?>
        <div class="row">
            <?php while ($other_tree = $other_trees_result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <?php if (!empty($other_tree['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($other_tree['image_path']); ?>" alt="Other Tree Image" class="card-img-top tree-image">
                        <?php else: ?>
                            <img src="assets/img/no-image.png" alt="No Image Available" class="card-img-top tree-image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($other_tree['species_name']); ?></h5>
                            <p class="species-details">(<?php echo htmlspecialchars($other_tree['scientific_name']); ?>)</p>
                            <p><strong>Date Observed:</strong> <?php echo htmlspecialchars($other_tree['date_time']); ?></p>
                            <p><strong>Location:</strong> Lat: <?php echo htmlspecialchars($other_tree['latitude']); ?>, Lng: <?php echo htmlspecialchars($other_tree['longitude']); ?></p>
                            <a href="view-tree.php?id=<?php echo $other_tree['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No other trees planted by this user.</p>
    <?php endif; ?>
</div>


      </div>
      </div>
      </div>
    </div>
</div>

<!-- Display Tree and User Information -->
<div class="card mb-4">
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <?php if (!empty($tree['image_path'])): ?>
          <!-- Add lightGallery container and anchor tag for full-screen viewing -->
          <div id="lightgallery">
            <a href="<?php echo htmlspecialchars($tree['image_path']); ?>">
              <img src="<?php echo htmlspecialchars($tree['image_path']); ?>" alt="Tree Image" class="tree-image">
            </a>
          </div>
        <?php else: ?>
          <p>No image available.</p>
        <?php endif; ?>
      </div>

      <script>
  // Get the image element
  var elem = document.getElementById("treeImage");

  // Function to open the image in full-screen mode
  function openFullscreen() {
    if (elem.requestFullscreen) {
      elem.requestFullscreen();
    } else if (elem.mozRequestFullScreen) { // Firefox
      elem.mozRequestFullScreen();
    } else if (elem.webkitRequestFullscreen) { // Chrome, Safari, Opera
      elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) { // IE/Edge
      elem.msRequestFullscreen();
    }
  }
</script>
      

<!-- JS Scripts -->
<script src="./assets/js/core/jquery.min.js"></script>
<script src="./assets/js/core/popper.min.js"></script>
<script src="./assets/js/core/bootstrap.min.js"></script>


<!-- Leaflet Map JS -->
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<script>
  var map = L.map('map').setView([<?php echo htmlspecialchars($tree['latitude']); ?>, <?php echo htmlspecialchars($tree['longitude']); ?>], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
  }).addTo(map);
  L.marker([<?php echo htmlspecialchars($tree['latitude']); ?>, <?php echo htmlspecialchars($tree['longitude']); ?>]).addTo(map);
</script>
</body>
</html>
