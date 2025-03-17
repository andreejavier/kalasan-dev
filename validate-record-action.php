<?php
// Database connection settings

$servername = "sql211.infinityfree.com";
$username = "if0_38393967"; 
$password = "cqsYanZIbqY "; 
$dbname = "if0_38393967_kalasan_db";

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
