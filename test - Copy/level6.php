<?php
session_start();
require_once __DIR__ . '/controllers/Level6Controller.php';

// Instantiate the controller and execute the login flow
$controller = new Level6Controller();
$controller->index();
?>
