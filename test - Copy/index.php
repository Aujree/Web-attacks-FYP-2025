<?php
require_once __DIR__ . '/controllers/LoginController.php';

$controller = new LoginController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic input sanitization for XSS protection (output encoding)
    $action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

    if ($action === 'register') {
        $result = $controller->handleRegistration($username, $password);
        if ($result['status'] === 'success') {
            $message = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            include __DIR__ . '/views/success_view.phtml';
            exit;
        } else {
            $error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            // Show register form again with error
            include __DIR__ . '/views/login_view.phtml';
            exit;
        }
    } else {
        $result = $controller->handleLogin($username, $password);
        if ($result['status'] === 'success') {
            $username = htmlspecialchars($result['user']['user'], ENT_QUOTES, 'UTF-8');
            include __DIR__ . '/views/success_view.phtml';
            exit;
        } else {
            $error = htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8');
            // Show login form again with error
            include __DIR__ . '/views/login_view.phtml';
            exit;
        }
    }
} else {
    // Show appropriate form based on action parameter
    include __DIR__ . '/views/login_view.phtml';
}