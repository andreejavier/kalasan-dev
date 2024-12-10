<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: LogIn.php");
    exit();
}
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dev_kalasan_db";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kalasan - List of Trees</title>
  <!-- External CSS files -->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
  <link href="assets/css/custom-dashboard.css" rel="stylesheet" />
  <style>
   
    #plantsContainer {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-around;
      gap: 1rem; 
    }

   
    .plant-card {
      flex: 1 1 300px; 
      max-width: 300px;
      margin-bottom: 2rem; 
    }

    .plant-card img {
      max-width: 110%;
      height: auto;
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
          <li class="active">
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

      
          <!-- Display plant statistics -->
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card card-stats">
              <div class="card-body">
                <div class="row">
                  <div class="col-5 col-md-4">
                    <div class="icon-big text-center icon-warning">
                      <i class="nc-icon nc-money-coins text-success"></i>
                    </div>
                  </div>
                  <div class="col-7 col-md-8">
                    <div class="numbers">
                      <p class="card-category">Plants Uploaded</p>
                      <p class="card-title" id="plantsPlantedCount">0</p> <!-- Updated via JS -->
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Plant cards container (flexbox) -->
          <div id="plantsContainer"></div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="footer">
        <div class="container-fluid">
          <div class="copyright">
            &copy; 2024 <a href="">Kalasan Team</a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- JavaScript to fetch and display plant data -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      fetch('config/get_plant_data.php')
        .then(response => response.json())
        .then(plants => {
          document.getElementById('plantsPlantedCount').textContent = plants.length;

          const plantsContainer = document.getElementById('plantsContainer');
          const fragment = document.createDocumentFragment();

          plants.forEach(plant => {
            const plantCard = document.createElement('div');
            plantCard.className = 'plant-card'; // Flexbox responsive plant card

            const locationAddress = plant.address ? plant.address : 'Address not available';

            plantCard.innerHTML = `
  <div class="card stats-box8-sub p-8">
    <a href="add-tree-details.php?id=${plant.id}" style="text-decoration: none; width: 150%; font-size:12px; color:#212121; margin-top:1rem;">
      <div class="card-header">
        <h5 class="card-title"> ${plant.species_name}</h5>
        <h6 class="card-subtitle mb-2 text-muted">${plant.scientific_name}</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-7">
            <img src="${plant.image_path}" alt="Plant Image" 
                 class="img-fluid" 
                 style="width: 250%; height: 150px; object-fit: cover;" 
                 onerror="this.onerror=null; this.src='assets/img/default-plant.jpg';">
          </div>
          <div class="col-10">
  
            <p class="card-text"><b>Latitude:</b> ${plant.latitude}</p>
            <p class="card-text"><b>Longitude:</b> ${plant.longitude}</p>
            <p class="card-text" style="margin-top:5px"><b>Date Observed:</b> ${plant.date_time}</p>
          </div>
        </div>
      </div>
    </a>
  </div>
`;

            fragment.appendChild(plantCard);
          });

          plantsContainer.appendChild(fragment);
        })
        .catch(error => console.error('Error fetching plant data:', error));
    });
  </script>
  <script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainPanel = document.getElementById('mainPanel');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('expanded');
        mainPanel.classList.toggle('expanded');
    });
</script>

  <!-- External JS files -->
  <script src="assets/js/core/jquery.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
</body>
</html>
