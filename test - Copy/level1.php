<?php
// index.php
require_once __DIR__ . '/controllers/Level1Controller.php';

$controller = new Level1Controller();
$controller->handleRequest();
