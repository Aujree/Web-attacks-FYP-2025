<?php
require_once __DIR__ . '/../models/Level6.php';
require_once __DIR__ . '/../models/Log.php';

class Level6Controller {
    private $model;
    private $log;

    public function __construct() {
        $this->model = new Level6();
        $this->log = new Log();
    }

    public function index() {
        $user = $this->model->getUser();

        if (!$user) {
            die("No users found in the database!");
        }

        $username = $user['user'];
        $passwordList = $this->loadPasswordList();

        $message = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['password'])) {
                $loginSuccess = $this->handleLogin($_POST['password']);
                $message = $loginSuccess ? "Login successful!" : "Login failed! Wrong password.";
            } elseif (isset($_FILES['attack_script'])) {
                $message = $this->handleFileUpload($_FILES['attack_script'], $username);
            }
        }

        require __DIR__ . '/../views/level6.phtml';
    }

    private function loadPasswordList() {
        $wordlistFile = __DIR__ . '/../wordlists/common_passwords.txt';
        return file($wordlistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    private function handleLogin($password) {
        $isSuspicious = $this->isSuspiciousInput($password, 'manual_attempt');
        $this->log->logInput('Level6', $password, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $isSuspicious);

        if ($isSuspicious) {
            $this->log->logFailedAttempt('Level6', $password, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        }

        return $this->model->verifyLogin($password);
    }

    private function handleFileUpload($file, $username) {
        // First check for suspicious files
        $fileCheck = $this->checkUploadedFile($file);
        if ($fileCheck['suspicious']) {
            $this->log->logInput(
                'Level6',
                'Suspicious file upload attempt: ' . $file['name'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'],
                true
            );
            $this->log->logFailedAttempt(
                'Level6',
                'Suspicious file: ' . $fileCheck['reason'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );
            return "File upload rejected: " . $fileCheck['reason'];
        }

        $allowed_extensions = ['php', 'py'];
        $upload_dir = __DIR__ . '/../uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array($file_ext, $allowed_extensions)) {
            $this->log->logInput(
                'Level6',
                'Invalid file type attempt: ' . $file['name'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'],
                true
            );
            return "Invalid file type! Only PHP and Python scripts are allowed.";
        }

        $file_path = $upload_dir . uniqid('attack_', true) . '.' . $file_ext;
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            $this->log->logInput(
                'Level6',
                'File upload failed: ' . $file['name'],
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'],
                false
            );
            return "File upload failed!";
        }

        // Log successful upload
        $this->log->logInput(
            'Level6',
            'Script uploaded: ' . $file['name'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            false
        );

        return $this->executeAttackScript($file_path, $username);
    }

    private function checkUploadedFile($file) {
        $result = ['suspicious' => false, 'reason' => ''];

        // Check for double extensions
        if (substr_count($file['name'], '.') > 1) {
            $result['suspicious'] = true;
            $result['reason'] = 'Double extension attempt';
            return $result;
        }

        // Check file size (limit to 100KB)
        if ($file['size'] > 100000) {
            $result['suspicious'] = true;
            $result['reason'] = 'File too large (max 100KB allowed)';
            return $result;
        }

        // Check for potentially dangerous file names
        $dangerousExtensions = ['php3', 'php4', 'php5', 'phtml', 'phar', 'htaccess'];
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($fileExt), $dangerousExtensions)) {
            $result['suspicious'] = true;
            $result['reason'] = 'Dangerous file extension';
            return $result;
        }

        // Check first 256 bytes for dangerous content
        $content = file_get_contents($file['tmp_name'], false, null, 0, 256);
        $dangerousFunctions = ['system(', 'shell_exec(', 'exec(', 'passthru(', 'eval('];
        foreach ($dangerousFunctions as $func) {
            if (stripos($content, $func) !== false) {
                $result['suspicious'] = true;
                $result['reason'] = 'Dangerous function detected: ' . $func;
                return $result;
            }
        }

        return $result;
    }

    private function executeAttackScript($scriptPath, $username) {
        $output = '';
        $isSuspicious = false;

        if (str_ends_with($scriptPath, '.php')) {
            ob_start();
            include $scriptPath;
            $output = ob_get_clean();

            // Check for suspicious PHP output
            if (strpos($output, 'Warning:') !== false ||
                strpos($output, 'Error:') !== false ||
                strpos($output, 'Exception:') !== false) {
                $isSuspicious = true;
            }
        }
        elseif (str_ends_with($scriptPath, '.py')) {
            $command = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($username);
            $output = shell_exec($command . " 2>&1");

            // Check for suspicious Python output
            if (strpos($output, 'Traceback') !== false ||
                strpos($output, 'ImportError') !== false) {
                $isSuspicious = true;
            }
        }
        else {
            return "Unsupported file format!";
        }

        // Log script execution
        $this->log->logInput(
            'Level6',
            'Script executed: ' . basename($scriptPath),
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $isSuspicious
        );

        if ($isSuspicious) {
            $this->log->logFailedAttempt(
                'Level6',
                'Suspicious script output',
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );
        }

        if (strpos($output, "Login successful!") !== false) {
            return "Login successful! Script executed correctly.";
        }

        return "Script execution output:\n" . htmlspecialchars($output);
    }

    private function isSuspiciousInput($input, $type) {
        // Common attack patterns
        $attackPatterns = [
            '/etc/passwd',
            '../',
            'union select',
            '<?php',
            '<?=',
            'hydra',
            'dictionary',
            'brute'
        ];

        foreach ($attackPatterns as $pattern) {
            if (stripos($input, $pattern) !== false) {
                return true;
            }
        }

        // Type-specific checks
        switch ($type) {
            case 'manual_attempt':
                return strlen($input) > 50; // Unusually long password attempts
            default:
                return false;
        }
    }
}