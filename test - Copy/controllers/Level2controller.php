<?php
// controllers/Level2controller.php
require_once __DIR__ . '/../models/Level2.php';
require_once __DIR__ . '/../models/Log.php';

class Level2controller {
    private $model;
    private $logModel;

    public function __construct() {
        $this->model = new Level2();
        $this->logModel = new Log(); // Initialize the LogModel
    }

    public function search() {
        $results = [];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['os'])) {
            $server = trim($_POST['os']);
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $is_suspicious = $this->isSuspicious($server);
            $this->logModel->logInput('Level2', $server, $ip_address, $user_agent, $is_suspicious);

            try {
                $results = $this->model->search2($server);
                if (empty($results)) {
                    $error = "No results found.";
                } elseif (isset($results['error'])) {
                    $error = $results['error'];
                    $results = [];
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }

        $servers = $results;
        include __DIR__ . '/../views/level2.phtml';
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