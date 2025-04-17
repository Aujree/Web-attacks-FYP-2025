<?php
// models/Log.php
require_once __DIR__ . '/../config/Database.php';
class Log {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function logInput($level, $input, $ip_address, $user_agent, $is_suspicious) {
        $stmt = $this->pdo->prepare("INSERT INTO logs (level, input, ip_address, user_agent, suspicious) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$level, $input, $ip_address, $user_agent, $is_suspicious]);
    }

    public function logFailedAttempt($level, $input, $ip_address, $user_agent) {
        // Check if the IP address has multiple failed attempts in the last 5 minutes
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as attempts FROM logs WHERE 
                                          level = ? AND ip_address = ? AND timestamp > 
                                          datetime('now', '-5 minutes')");
        $stmt->execute([$level, $ip_address]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $failed_attempts = $result['attempts'] + 1;

        // Flag as suspicious if there are too many failed attempts
        $is_suspicious = ($failed_attempts > 5);

        // Log the failed attempt
        $this->logInput($level, $input, $ip_address, $user_agent, $is_suspicious);

        // Update the failed_attempts count in the logs table
        $stmt = $this->pdo->prepare("UPDATE logs SET failed_attempts = ? WHERE level = ? 
                                    AND ip_address = ? AND timestamp > datetime('now', '-5 minutes')");
        $stmt->execute([$failed_attempts, $level, $ip_address]);
    }

    /**
     * Fetch all logs from the database.
     */
    public function getLogs($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare("SELECT * FROM logs ORDER BY timestamp DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalLogs() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM logs");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllLogsForExport() {
        $stmt = $this->pdo->query("SELECT * FROM logs ORDER BY timestamp DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIpAddressDistribution() {
        $stmt = $this->pdo->prepare("
        SELECT ip_address, COUNT(*) as count 
        FROM logs 
        GROUP BY ip_address 
        ORDER BY count DESC
        LIMIT 10
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLevelDistribution() {
        $stmt = $this->pdo->prepare("
        SELECT level, COUNT(*) as count 
        FROM logs 
        GROUP BY level
        ORDER BY count DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSuspiciousDistribution() {
        $stmt = $this->pdo->prepare("
        SELECT suspicious, COUNT(*) as count 
        FROM logs 
        GROUP BY suspicious
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}