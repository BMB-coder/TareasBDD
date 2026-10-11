<?php
require_once __DIR__ . '/includes/autenticacion.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido()) {
    http_response_code(403);
    exit('Solicitud no válida. Cierra sesión mediante el botón de tu panel.');
}
$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600, 'path' => $cookie['path'],
    'secure' => $cookie['secure'], 'httponly' => true, 'samesite' => 'Lax'
]);
session_destroy();
header('Location: ' . BASE_ACCESO . '/login.php');
exit;
