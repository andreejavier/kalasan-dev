<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/db_connection.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = trim($_POST['admin_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $date_assigned = $_POST['date_assigned'];
    $status = 'active'; // Default status is active

    // Validate that passwords match
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT admin_id FROM `admin` WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username or email already taken.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare to insert the new admin record
            $stmt = $conn->prepare("INSERT INTO `admin` (admin_name, username, email, password_hash, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $admin_name, $username, $email, $hashedPassword, $status);

            if ($stmt->execute()) {
                $success = "Admin account created successfully. <a href='LogIn.php'>Login here</a>";
            } else {
                $error = "Error creating admin account: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Kalasan Admin Registration</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />

    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <!-- CSS Files -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <!-- Demo CSS -->
    <link href="assets/demo/demo.css" rel="stylesheet" />

    <style>
        body {
            background-image: url('assets/img/background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }

        .card h4 {
            text-align: center;
            color: #4caf50;
        }

        .btn-block {
            margin-top: 20px;
            background-color: #4caf50;
            color: white;
            border: none;
        }

        .btn-block:hover {
            background-color: #388e3c;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            min-height: 50px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            text-align: center;
        }

        .login-form label {
            font-weight: bold;
            margin-top: 10px;
        }

        .btn-block {
            margin-top: 20px;
        }

        .forgot {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <div class="content">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-center">Admin Registration</h4>
                <!-- Display error or success messages -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success mt-3"><?php echo $success; ?></div>
                <?php endif; ?>

                <form class="login-form" action="register-page.php" method="POST">
                    <label>Full Name</label>
                    <input type="text" name="admin_name" class="form-control" placeholder="Full Name" required>

                    <label>Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Username" required>

                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>

                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>

                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>

                    <label>Date Assigned</label>
                    <input type="date" name="date_assigned" class="form-control" required>

                    <button type="submit" class="btn btn-danger btn-block">Register</button>
                </form>
                <div class="forgot text-center mt-3">
                    <a href="LogIn.php" class="btn btn-link">Log In</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <nav class="footer-nav">
            <ul>
                <li><a href="#">Kalasan Team</a></li>
                <li><a href="#">About Us</a></li>
            </ul>
        </nav>
    </footer>

    <!-- Core JS Files -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>

</body>

</html>
