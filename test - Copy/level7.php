<?php
// index.php
require_once __DIR__ . '/controllers/Level7Controller.php';

$controller = new Level7Controller();
$controller->handleRequest();
