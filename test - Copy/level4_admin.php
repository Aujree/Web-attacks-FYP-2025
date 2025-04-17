<?php
require_once 'controllers/Level4Controller.php';

$controller = new Level4Controller();

// Check if the "Clear Messages" button was pressed
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['clear_messages'])) {
    $controller->clearMessages();
}

// Display the admin view
$controller->adminView();
?>
