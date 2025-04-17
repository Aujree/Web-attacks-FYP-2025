<?php
// index.php
require_once __DIR__ . '/controllers/Level2controller.php';

$controller = new Level2Controller();
$controller->search();
