<?php
require_once __DIR__ . '/../config/Database.php';

class Level2 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function search2($server) {
        // Directly injecting $server value into the query (this is vulnerable)
        $query = "SELECT hostname, ip FROM level_2 WHERE os LIKE '%" . $server . "%'";

        try {
            // Query is executed without parameter binding, so it's vulnerable to UNION-based SQL injection
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return ["error" => $e->getMessage()]; // Return errors for debugging
        }

        //' ORDER BY 3--
        //' UNION SELECT hostname, port FROM level_2 --
    }






}