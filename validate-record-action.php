<?php
// Database connection settings
$host = 'localhost';
$db = 'dev_kalasan_db';
$user = 'root';
$pass = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['planted_id'])) {
    $recordId = intval($_POST['planted_id']);

    try {
        // Establish the connection
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Update the review status
        $sql = "UPDATE tree_planted SET validated = 1 WHERE id = :planted_id"; // Change 'validated' to reflect 'reviewed' if necessary.
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':planted_id', $recordId, PDO::PARAM_INT);
        $stmt->execute();

        echo "Record reviewed successfully.";

    } catch (PDOException $e) {
        echo "Failed to review record: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
