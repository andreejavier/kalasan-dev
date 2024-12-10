<?php
session_start();
include 'config/db_connection.php';  // Ensure this file contains your database connection details

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to prevent SQL injection
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Prepare SQL query to check admin's credentials
        $stmt = $conn->prepare("SELECT admin_id, password_hash FROM admin WHERE username = ? AND status = 'active'");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($admin_id, $password_hash);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $password_hash)) {
                    // Successful login
                    $_SESSION['admin_id'] = $admin_id;
                    $_SESSION['username'] = $username;

                    // Update last login time
                    //$updateStmt = $conn->prepare("UPDATE admin SET last_login = NOW() WHERE admin_id = ?");
                    if ($updateStmt) {
                        $updateStmt->bind_param("i", $admin_id);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }

                    // Redirect to admin dashboard
                    header("Location: home.php");
                    exit();
                } else {
                    $error = "Invalid password. Please try again.";
                }
            } else {
                $error = "Invalid username or account is inactive.";
            }
            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Kalasan Admin Login</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- CSS Files -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <!-- CSS Files -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Demo CSS (optional) -->
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

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            min-height: 50px;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            text-align: center;
        }

        .footer-nav ul {
            list-style: none;
            padding-left: 0;
        }

        .footer-nav ul li {
            display: inline;
            margin: 0 10px;
        }

        .footer-nav ul li a {
            color: #555;
            text-decoration: none;
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
                <h4 class="card-title text-center">Log In</h4>
                <form class="login-form" action="LogIn.php" method="POST">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>

                    <?php if (!empty($error)): ?> 
                        <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-block">Log In</button>
                </form>
                <div class="forgot text-center mt-3">
                    <a href="./register-page.php" class="btn btn-link">Register</a>
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
