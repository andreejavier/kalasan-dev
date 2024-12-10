<?php
require 'db_connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$treeId = $data['tree_id'];
$speciesId = $data['species_id'];

// Update the tree record with the species ID
$query = "UPDATE tree_records SET tree_species_id = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $speciesId, $treeId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
