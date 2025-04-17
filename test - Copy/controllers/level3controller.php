<?php
// controllers/Level3Controller.php
require_once __DIR__ . '/../models/Level3.php';
require_once __DIR__ . '/../models/Log.php'; // Use the Log class

class Level3Controller {
    private $model;
    private $log;

    public function __construct() {
        $this->model = new Level3();
        $this->log = new Log(); // Initialize the Log class
    }

    public function xss() {
        $token = bin2hex(random_bytes(16));
        setcookie("XSS_Token", $token, time() + 3600, "/");

        $user_input = isset($_POST['user_input']) ? $_POST['user_input'] : '';
        $is_attack = $this->model->isxss($user_input);

        // Log the user input
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $is_suspicious = $this->isSuspicious($user_input); // Check if input is suspicious

        // Log the input
        $this->log->logInput('Level3', $user_input, $ip_address, $user_agent, $is_suspicious);

        // Pass variables to the view
        extract(['user_input' => $user_input, 'is_attack' => $is_attack]);
        require_once 'views/level3.phtml';
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