<?php
// controllers/Level7Controller.php
require_once __DIR__ . '/../models/Level7.php';
require_once __DIR__ . '/../models/Log.php';

class Level7Controller {
    private $model;
    private $logModel;

    public function __construct() {
        $this->model = new Level7();
        $this->logModel = new Log(); // Initialize the LogModel
    }

    public function handleRequest() {
        $result = null;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            // Log the user input
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $is_suspicious = $this->isSuspicious($username) || $this->isSuspicious($password); // Check if input is suspicious
            $input_data = "username: $username, password: [hidden]"; // Avoid logging password directly
            $this->logModel->logInput('Level7', $input_data, $ip_address, $user_agent, $is_suspicious);

            try {
                $result = $this->model->login($username, $password);
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }

        include __DIR__ . '/../views/level7.phtml';
    }

    /**
     * Check if the input is suspicious.
     */
    private function isSuspicious($input) {
        $known_attacks = [
            // SQL Injection patterns
            "/'.*OR.*=.*--/i", // Matches ' OR 1=1 --, ' OR 'a'='a, etc.
            "/UNION.*SELECT/i", // Matches UNION SELECT, UNION ALL SELECT, etc.
            "/DROP.*TABLE/i", // Matches DROP TABLE, DROP TABLE IF EXISTS, etc.
            "/--\s+/i", // Matches SQL comments (--)
            "/;.*--/i", // Matches SQL comments after a semicolon (;--)
        ];

        foreach ($known_attacks as $pattern) {
            if (preg_match($pattern, $input)) {
                return true; // Input matches a known attack pattern
            }
        }
        return false; // Input is safe
    }
}