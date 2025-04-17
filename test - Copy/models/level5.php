<?php
require_once __DIR__ . '/../config/Database.php';
class level5 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getUser() {
        $stmt = $this->pdo->query("SELECT * FROM level5 ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addUser($username, $password) {
        $stmt = $this->pdo->prepare("INSERT INTO level5 (user, password) VALUES (?, ?)");
        return $stmt->execute([$username, $password]);
    }

    public function verifyLogin($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM level5 WHERE user = ? AND password = ?");
        $stmt->execute([$username, $password]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
