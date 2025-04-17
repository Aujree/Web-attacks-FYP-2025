<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../models/Level5.php';
require_once __DIR__ . '/../models/Log.php';

class Level5Controller {
    private $model;
    private $log;

    public function __construct() {
        $this->model = new Level5();
        $this->log = new Log();

        // Initialize session tracking for fresh starts
        if (empty($_SESSION['level5_initialized'])) {
            $this->resetChallengeSession();
            $_SESSION['level5_initialized'] = true;
        }
    }

    public function index() {
        // Generate or retrieve user credentials
        list($username, $passwords) = $this->generateNewUser();

        // Handle login attempts
        $message = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $message = $this->handleLogin($_POST['username'], $_POST['password']);
        }

        // Load the view and pass data
        require __DIR__ . '/../views/level5.phtml';
    }

    private function resetChallengeSession() {
        // Only reset our challenge-specific session vars
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['lockout_time']);
        unset($_SESSION['challenge_user']);
    }

    private function generateNewUser() {
        if (!isset($_SESSION['challenge_user'])) {
            $username = 'user' . rand(1000, 9999);
            $_SESSION['challenge_user'] = $username;
        }

        // Always generate a fresh list of passwords
        $passwords = array_map(function() {
            return bin2hex(random_bytes(3)); // 6-char passwords
        }, range(1, 10));

        // Set one correct password
        $correctPassword = $passwords[array_rand($passwords)];
        $this->model->addUser($_SESSION['challenge_user'], $correctPassword);

        return [$_SESSION['challenge_user'], $passwords];
    }

    private function handleLogin($username, $password) {
        // Check lockout status first
        if (isset($_SESSION['lockout_time'])) {
            if (time() < $_SESSION['lockout_time']) {
                $remaining = $_SESSION['lockout_time'] - time();
                return "Too many failed attempts. Try again in $remaining seconds.";
            }
            $this->resetChallengeSession();
        }

        // Verify credentials
        if ($this->model->verifyLogin($username, $password)) {
            $this->resetChallengeSession();
            return "Login successful!";
        }

        // Handle failed attempt
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;

        // Log the attempt
        $this->log->logFailedAttempt(
            'Level5',
            $password,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );

        // Check for lockout
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['lockout_time'] = time() + 30;
            return "Too many failed attempts. Wait 30 seconds.";
        }

        return "Invalid credentials. Attempts: {$_SESSION['login_attempts']}/5";
    }
}