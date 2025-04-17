<?php

require_once __DIR__ . '/../config/Database.php';

class Level3 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function isxss($user_input) {
        // The query expects ':pattern', so we should bind the input to ':pattern'
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM xss WHERE :pattern LIKE '%' || pattern || '%'");
        // Bind the parameter to ':pattern' (matching the placeholder in the query)
        $stmt->bindParam(':pattern', $user_input, PDO::PARAM_STR);

        // Execute the query and return true if the pattern exists in the table
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }


}
?>
