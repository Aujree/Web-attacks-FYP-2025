<?php
require_once __DIR__ . '/../config/Database.php';

class UserModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getUser($username, $password) {
        // Get user with password verification
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::PARAM_STR);

        if ($user && password_verify($password, $user['passwords'])) {
            return $user;
        }
        return false;
    }

    public function createUser($username, $password) {
        // Check if username exists
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Username already exists'];
        }

        // Create user with hashed password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO user (user, passwords) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'User created successfully'];
        }
        return ['status' => 'error', 'message' => 'Failed to create user'];
    }

    // Brute force protection methods
    public function recordFailedAttempt($username) {
        $stmt = $this->pdo->prepare("UPDATE user SET failed_attempts = failed_attempts + 1 WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function getFailedAttempts($username) {
        $stmt = $this->pdo->prepare("SELECT failed_attempts FROM user WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['failed_attempts'] : 0;
    }

    public function resetFailedAttempts($username) {
        $stmt = $this->pdo->prepare("UPDATE user SET failed_attempts = 0, lockout_time = NULL WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function lockAccount($username, $lockoutEnd) {
        $stmt = $this->pdo->prepare("UPDATE user SET lockout_time = :lockout_time WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':lockout_time', $lockoutEnd, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function isAccountLocked($username) {
        $stmt = $this->pdo->prepare("SELECT lockout_time FROM user WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['lockout_time'] && $result['lockout_time'] > time();
    }

    public function getLockoutTime($username) {
        $stmt = $this->pdo->prepare("SELECT lockout_time FROM user WHERE user = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['lockout_time'] : 0;
    }
}