<?php
// Log.php (main entry point)
require_once __DIR__ . '/controllers/LogController.php';

$action = $_GET['action'] ?? 'index';

$controller = new LogController();

if ($action === 'export') {
    $controller->exportCSV();
} elseif ($action === 'graphs') {
    // Modified this part to only handle graphs
    $controller->showGraphs();
} else {
    $controller->index();
}