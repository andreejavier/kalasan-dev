<?php
// Start session and check if admin is logged in
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: LogIn.php");
    exit();
}

// Database connection

$servername = "sql211.infinityfree.com";
$username = "if0_38393967"; 
$password = "cqsYanZIbqY "; 
$dbname = "if0_38393967_kalasan_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid contributor ID.";
    exit();
}

$contributor_id = intval($_GET['id']);

// Fetch contributor details
$sql = "SELECT id, username, email, created_at, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contributor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Contributor not found.";
    exit();
}

$contributor = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>View Profile - Contributor</title>
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet" />
    <link href="./assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="./assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
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
          <li>
            <a href="./tree-species-form.php">
              <i class="fa fa-tree"></i>
              <p>Tree Species</p>
            </a>
          </li>
          <li class="active">
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
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="javascript:;">Contributor Profile</a>
                </div>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php"><i class="nc-icon nc-single-02"></i><p>Profile</p></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="nc-icon nc-button-power"></i><p>Logout</p></a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <div class="content">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Contributor Details</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <img src="<?php echo htmlspecialchars($contributor['profile_picture']); ?>" 
                                         alt="Profile Picture" 
                                         class="img-fluid rounded-circle" 
                                         style="max-width: 150px;">
                                </div>
                                <div class="col-md-8">
                                    <h5>Username: <?php echo htmlspecialchars($contributor['username']); ?></h5>
                                    <p>Email: <?php echo htmlspecialchars($contributor['email']); ?></p>
                                    <p>Date Joined: <?php echo htmlspecialchars($contributor['created_at']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="contributors-datatable.php" class="btn btn-primary">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer" style="position: absolute; bottom: 0; width: 100%;">
            <div class="container-fluid">
                <div class="row">
                    <div class="credits ml-auto">
                        <span>&copy; 2024 Kalasan Project</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- JS Files -->
<script src="./assets/js/core/jquery.min.js"></script>
<script src="./assets/js/core/bootstrap.min.js"></script>
</body>
</html>
