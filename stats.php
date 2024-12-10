<?php
// Database connection settings
$host = 'localhost';
$db = 'dev_kalasan_db';
$user = 'root';
$pass = ''; // Update with your password if needed

try {
    // Establish the connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the SQL query
    $sql = "SELECT * FROM `tree_planted`"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Fetch the results
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
    <title>Tree Records with Species Information</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h1>Tree Records and Species Information</h1>
    <table>
        <tr>
            <th>Record ID</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Date & Time</th>
            <th>Address</th>
            <th>Image Path</th>
            <th>Validated</th>
            <th>Species Name</th>
            <th>Scientific Name</th>
            <th>Description</th>
        </tr>
        <?php if (!empty($records)): ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= htmlspecialchars($record['id']) ?></td>
                    <td><?= htmlspecialchars($record['latitude']) ?></td>
                    <td><?= htmlspecialchars($record['longitude']) ?></td>
                    <td><?= htmlspecialchars($record['date_time']) ?></td>
                    <td><?= htmlspecialchars($record['address']) ?></td>
                    <td><?= htmlspecialchars($record['image_path']) ?></td>
                    <td><?= htmlspecialchars($record['validated'] ? 'Yes' : 'No') ?></td>
                    <td><?= htmlspecialchars($record['species_name']) ?></td>
                    <td><?= htmlspecialchars($record['scientific_name']) ?></td>
                    <td><?= htmlspecialchars($record['description']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No records found.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>
