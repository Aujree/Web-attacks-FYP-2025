<?php
session_start();
//require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/controllers/Level5controller.php';

// Instantiate the controller and execute the login flow
$controller = new level5controller();
$controller->index();
?>
