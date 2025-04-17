<?php
require_once __DIR__ . '/../models/UserModel.php';

class LoginController {
    private $userModel;
    private $maxLoginAttempts = 5;
    private $lockoutTime = 300; // 5 minutes in seconds

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function handleLogin($username, $password) {
        // Input validation
        if (empty($username) || empty($password)) {
            return ['status' => 'error', 'message' => 'Username and password are required'];
        }

        // Check for brute force attempts
        if ($this->userModel->isAccountLocked($username)) {
            $lockoutEnd = $this->userModel->getLockoutTime($username);
            $remainingTime = $lockoutEnd - time();
            return ['status' => 'error', 'message' => 'Account locked. Try again in '.ceil($remainingTime/60).' minutes'];
        }

        $user = $this->userModel->getUser($username, $password);
        if ($user) {
            // Reset failed attempts on successful login
            $this->userModel->resetFailedAttempts($username);
            return ['status' => 'success', 'user' => $user];
        } else {
            // Record failed attempt
            $this->userModel->recordFailedAttempt($username);
            $attemptsLeft = $this->maxLoginAttempts - $this->userModel->getFailedAttempts($username);

            if ($attemptsLeft <= 0) {
                $this->userModel->lockAccount($username, time() + $this->lockoutTime);
                return ['status' => 'error', 'message' => 'Too many failed attempts. Account locked for 5 minutes'];
            }

            return ['status' => 'error', 'message' => 'Invalid credentials. '.$attemptsLeft.' attempts remaining'];
        }
    }

    public function handleRegistration($username, $password) {
        // Stronger input validation
        if (empty($username) || empty($password)) {
            return ['status' => 'error', 'message' => 'Username and password are required'];
        }
        if (strlen($username) > 50) {
            return ['status' => 'error', 'message' => 'Username too long (max 50 characters)'];
        }
        if (strlen($password) < 8) {
            return ['status' => 'error', 'message' => 'Password must be at least 8 characters'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['status' => 'error', 'message' => 'Username can only contain letters, numbers and underscores'];
        }

        return $this->userModel->createUser($username, $password);
    }
}