<?php
require_once __DIR__ . '/controllers/Level3controller.php';

$controller = new level3controller();
$controller->xss();
?>
