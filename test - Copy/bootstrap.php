<?php
// bootstrap.php
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 1 day
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']), // Auto-detect HTTPS
        'httponly' => true,
        'samesite' => 'Lax' // More compatible than Strict
    ]);

    // Start the session with a custom name
    session_name('SECURITYLABS_SESSID');
    session_start();

    // Regenerate session ID periodically for security
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}