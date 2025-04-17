<?php
// models/Level1Model.php
require_once __DIR__ . '/../config/Database.php';

class Level1 {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Function to search for names and their related information in the database
     *
     * @param string $name_query - The search term used to find names.
     * @return array - Array of results containing 'name' and 'information'
     */
    public function searchName($name_query) {
        // Vulnerable SQL query (Unsafe and susceptible to SQL Injection)
        $query = "SELECT name, information FROM level_1 WHERE name = '$name_query'";  // No sanitization, user input is directly inserted
        $stmt = $this->pdo->query($query);  // Execute the query
        //' UNION SELECT name, information, bank_details FROM level_2--
        // ' OR '1='1'
        // ORDER BY 1,2,3--
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Return results as an associative array
    }
}
