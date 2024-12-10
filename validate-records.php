<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: LogIn.php");
    exit();
}

// Database connection settings
$host = 'localhost';
$db = 'dev_kalasan_db';
$user = 'root';
$pass = '';

try {
    // Establish the connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to fetch tree records
    $sql = "SELECT 
                tree_planted.id AS planted_id,
                tree_planted.latitude,
                tree_planted.longitude,
                tree_planted.date_time,
                tree_planted.address,
                tree_planted.image_path,
                tree_planted.validated,
                tree_planted.scientific_name,
                tree_planted.species_name,
                tree_planted.description,
                tree_planted.category
            FROM tree_planted";
    
    // Execute the query
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tree Record</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/paper-dashboard.css" rel="stylesheet">
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
        img { width: 100px; height: auto; }
        button { padding: 5px 10px; }
         /* Sidebar base styles with forest theme */
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
    <script>
        function reviewRecord(recordId) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "Validate-record-action.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById("status_" + recordId).innerText = "Reviewed";
                    document.getElementById("button_" + recordId).disabled = true;
                }
            };

            xhr.send("planted_id=" + recordId);
        }
    </script>
</head>
<body>
    <div class="main-panel">
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
          <li>
            <a href="./contributors-datatable.php">
              <i class="fa fa-users"></i>
              <p>Manage User</p>
            </a>
          </li>
          <li class="active">
            <a href="./validate-records.php">
              <i class="fa fa-clipboard"></i>
              <p>Tree Records</p>
            </a>
          </li>
        </ul>
      </div>
    </div>
        <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="javascript:;">Tree Records</a>
                </div>
                <div class="collapse navbar-collapse justify-content-end">
                <div class="searchwrapper">
                    <input id="speciesSearch" class="form-control" placeholder="Search Species" type="text" onkeyup="filterTrees()" />
                </div>
                <div class="searchwrapper">
                    <input id="locationSearch" class="form-control" placeholder="Search Location" type="text" onkeyup="filterTrees()" />
                </div>
                <button class="btn btn-default btn-inat btn-focus show-btn" type="button" onclick="filterTrees()">
                    <i class="fa fa-search"></i>
                </button>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="color: black;">
                                <i class="nc-icon nc-single-02"></i>
                                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
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


        <div class="export-buttons">
    <button class="btn btn-success" onclick="exportToPDF()">Export to PDF</button>
    <button class="btn btn-primary" onclick="exportToExcel()">Export to Excel</button>
</div>

    <h2>Tree Records and Species Information</h2>
    <table>
        <tr>
            <th>No.</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Date & Time</th>
            <th>Address</th>
            <th>Image</th>
            <th>Species Name</th>
            <th>Description</th>
            <th>Category</th>
        </tr>
        <?php 
        $counter = 1; // Initialize counter
        foreach ($records as $record): ?>
            <tr>
                <td><?= $counter++ ?></td> <!-- Increment counter for each row -->
                <td><?= htmlspecialchars($record['latitude']) ?></td>
                <td><?= htmlspecialchars($record['longitude']) ?></td>
                <td><?= htmlspecialchars($record['date_time']) ?></td>
                <td><?= htmlspecialchars($record['address']) ?></td>
                <td>
                    <?php if (!empty($record['image_path'])): ?>
                        <img src="<?= htmlspecialchars($record['image_path']) ?>" alt="Tree Image">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($record['species_name']) ?></td>
                <td><?= htmlspecialchars($record['description']) ?></td>
                <td><?= htmlspecialchars($record['category']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>


    <script>
    function filterTrees() {
        // Get search inputs
        const speciesInput = document.getElementById('speciesSearch').value.toLowerCase();
        const locationInput = document.getElementById('locationSearch').value.toLowerCase();

        // Get table rows
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tr');

        // Iterate over rows to filter based on inputs
        rows.forEach((row, index) => {
            if (index === 0) return; // Skip the header row

            const species = row.cells[6]?.textContent.toLowerCase() || '';
            const address = row.cells[4]?.textContent.toLowerCase() || '';

            // Show/hide rows based on search inputs
            if (species.includes(speciesInput) && address.includes(locationInput)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
<script>
    // Function to export table data to PDF
    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Set title and header
        doc.setFontSize(18);
        doc.text('Tree Records', 14, 16);
        doc.setFontSize(12);

        // Capture table data
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tr');

        const tableData = [];
        rows.forEach((row, index) => {
            const rowData = [];
            const cells = row.querySelectorAll('td, th');
            cells.forEach(cell => {
                rowData.push(cell.textContent.trim());
            });
            if (index > 0) { // Skip the header row
                tableData.push(rowData);
            }
        });

        // Add table data to the PDF
        doc.autoTable({
            head: [['No.', 'Latitude', 'Longitude', 'Date & Time', 'Address', 'Image', 'Species Name', 'Description', 'Category']],
            body: tableData,
            startY: 30,
        });

        // Save the PDF
        doc.save('tree_records.pdf');
    }

    // Function to export table data to Excel
    function exportToExcel() {
        const table = document.querySelector('table');
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Tree Records' });

        // Export the table as an Excel file
        XLSX.writeFile(wb, 'tree_records.xlsx');
    }
</script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

  <script src="assets/js/core/jquery.min.js"></script>
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
  <script src="assets/js/paper-dashboard.min.js?v=2.0.1" type="text/javascript"></script>
</body>
</html>
