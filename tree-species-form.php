<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'config/db_connection.php';

// Ensure the database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set upload directory and ensure it exists
$uploads_dir = 'uploads/trees';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Fetch all tree records with additional images and uploader details
$query = "SELECT tr.*, ti.image_path AS additional_image, u.username AS uploader_name, u.profile_picture 
          FROM tree_planted tr
          LEFT JOIN tree_images ti ON tr.id = ti.tree_planted_id
          LEFT JOIN users u ON tr.user_id = u.id";
$stmt = mysqli_prepare($conn, $query);

if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($conn));
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$trees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tree_id = $row['id'];
    if (!isset($trees[$tree_id])) {
        $trees[$tree_id]['details'] = $row;
    }
    if ($row['additional_image']) {
        $trees[$tree_id]['images'][] = $row['additional_image'];
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Kalasan Mapping - Tree Species</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/paper-dashboard.css" rel="stylesheet">
    <link href="assets/css/custom-dashboard.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Add your custom styles here */
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
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
    .tree-image {
    width: 100%; /* Makes the image fill the card width */
    height: 200px; /* Standardized height for all images */
    object-fit: cover; /* Ensures the image maintains its aspect ratio while filling the space */
    border-radius: 8px; /* Adds a subtle rounded corner to the images */
}

/* Styling for profile pictures */
.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 10px;
}

}
    </style>
    <style>
/* Styling for the tree images */
.tree-image {
    width: 100%; /* Makes the image fill the card width */
    height: 200px; /* Standardized height for all images */
    object-fit: cover; /* Ensures the image maintains its aspect ratio while filling the space */
    border-radius: 8px; /* Adds a subtle rounded corner to the images */
}

/* Styling for profile pictures */
.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: 10px;
}
</style>
</head>

<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" data-color="white" data-active-color="white">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">
                <img src="assets/img/tree.png" alt="Logo">
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
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <span class="menu-toggle" id="menuToggle">
                        <i class="fa fa-bars"></i>
                    </span>
                    <a class="navbar-brand" href="javascript:;">Tree Species</a>
                </div>
                <div class="collapse navbar-collapse justify-content-end" id="navigation">
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
            </div>
        </nav>


    <div class="container-fluid">
        <h3>Tree Species Form</h3>
        <div class="row" id="treeContainer">
            <?php foreach ($trees as $tree): ?>
                <div class="col-md-4 tree-card">
                    <div class="card">
                        <a href="view-tree.php?id=<?php echo htmlspecialchars($tree['details']['id']); ?>">
                            <img 
                                class="tree-image" 
                                src="<?php echo htmlspecialchars($tree['details']['image_path']); ?>" 
                                alt="Tree Image" 
                            />
                        </a>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tree['details']['species_name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($tree['details']['address']); ?></p>
                            <img 
                                class="profile-pic" 
                                src="<?php echo htmlspecialchars($tree['details']['profile_picture']); ?>" 
                                alt="Profile Picture" 
                            />
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>




<!-- Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainPanel = document.querySelector('.main-panel');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
        mainPanel.classList.toggle('expanded');
    });
});
</script>

<script src="assets/js/core/jquery.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
</body>
</html>
