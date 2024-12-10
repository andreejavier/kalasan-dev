<?php
class ValidationLog {
    private $conn;
    private $table_name = "`validation_log`";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readLogs() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY validated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
