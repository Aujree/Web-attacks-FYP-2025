<?php
require_once __DIR__ . '/../config/Database.php';

class Level4 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function saveMessage($username, $message) {
        $stmt = $this->pdo->prepare("INSERT INTO level4 (username, message) VALUES (:username, :message)");
        $stmt->execute(['username' => $username, 'message' => $message]);
    }

    public function getMessages() {
        $stmt = $this->pdo->query("SELECT * FROM level4 ORDER BY username DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearMessages() {
        $this->pdo->query("DELETE FROM level4"); // Deletes all rows in the table
    }






}