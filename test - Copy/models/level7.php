<?php
// models/Level7.php
require_once __DIR__ . '/../config/Database.php';

class Level7 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Function to authenticate a user based on username and password.
     * Deliberately vulnerable to SQL injection for testing purposes.
     *
     * @param string $username - The username input.
     * @param string $password - The password input.
     * @return array|null - User data if login succeeds, null otherwise.
     */
    public function login($username, $password) {
        //Vulnerable SQL query (unsafe, direct concatenation)
        $query = "SELECT * FROM level7 WHERE username = '$username' AND password = '$password'";
        $stmt = $this->pdo->query($query); // Execute the query
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return [
                'username' => $row['username'],
                'flag' => 'FLAG{sql_injection_worked}' // For SQLMap to detect
            ];
        }
        return null;
    }
}