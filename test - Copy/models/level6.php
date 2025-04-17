<?php
require_once __DIR__ . '/../config/Database.php';

class Level6 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getUser() {
        // Fetch a pre-existing user instead of creating a new one
        $stmt = $this->pdo->query("SELECT * FROM level6 ORDER BY id LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyLogin($password) {
        $stmt = $this->pdo->prepare("SELECT * FROM level6 WHERE user = 'admin' AND password = ?");
        $stmt->execute([$password]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
