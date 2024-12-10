<?php
require_once 'models/ValidationLog.php';

class ValidationLogController {
    private $db;
    private $validationLog;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->validationLog = new ValidationLog($this->db);
    }

    public function getLogs() {
        $logs = $this->validationLog->readLogs();
        $logRecords = [];
        while ($row = $logs->fetch(PDO::FETCH_ASSOC)) {
            $logRecords[] = [
                'admin_id' => $row['admin_id'],
                'tree_id' => $row['tree_id'],
                'status' => $row['status'],
                'validated_at' => $row['validated_at'],
                'remarks' => $row['remarks'],
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($logRecords);
    }
}
?>
