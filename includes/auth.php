<?php

if (!defined('APP_BASE')) {
    define('APP_BASE', '/security-project');
}

$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
];
session_set_cookie_params($cookieParams);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/headers.php';


$timeout_seconds = 15 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_seconds) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . APP_BASE . '/login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . APP_BASE . '/login.php');
        exit;
    }
}

function clear_session_cookie() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
