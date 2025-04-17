<?php
// controllers/Level4Controller.php
require_once __DIR__ . '/../models/Level4.php';
require_once __DIR__ . '/../models/Log.php'; // Use the Log class

class Level4Controller {
    private $model;
    private $log;

    public function __construct() {
        $this->model = new Level4();
        $this->log = new Log(); // Initialize the Log class
    }

    public function xss2() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? 'Anonymous';
            $message = $_POST['message'] ?? '';

            // Log the user input
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $is_suspicious = $this->isSuspicious($message); // Check if input is suspicious

            // Log the input
            $this->log->logInput('Level4', $message, $ip_address, $user_agent, $is_suspicious);

            // Store input without sanitization (introducing Stored XSS vulnerability)
            $this->model->saveMessage($username, $message);

            // Redirect user to simulate a real attack
            header("Location: level4_admin.php");
            exit;
        }

        include 'views/level4.phtml';
    }

    public function adminView() {
        $messages = $this->model->getMessages();
        include 'views/level4_admin.phtml';
    }

    public function clearMessages() {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear_messages'])) {
            $this->model->clearMessages();
            header("Location: level4_admin.php");
            exit;
        }
    }

    /**
     * Check if the input is suspicious.
     */
    private function isSuspicious($input) {
        $known_attacks = [
            // XSS patterns
            "/<script>/i", // Matches <script>, <SCRIPT>, etc.
            "/onerror=/i", // Matches onerror=, onError=, etc.
            "/onload=/i", // Matches onload=, onLoad=, etc.
            "/javascript:/i", // Matches javascript:, JavaScript:, etc.
            "/alert\(/i", // Matches alert(, Alert(, etc.
        ];

        foreach ($known_attacks as $pattern) {
            if (preg_match($pattern, $input)) {
                return true; // Input matches a known attack pattern
            }
        }
        return false; // Input is safe
    }
}
?>