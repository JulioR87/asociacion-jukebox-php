<?php
// docker/src/logout.php
require_once __DIR__ . '/lib/sesion.php';

// 1. Rechazar peticiones que no sean POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido. El cierre de sesión debe solicitarse por POST.';
    exit;
}

// 2. Validar token CSRF
validarTokenCsrf();

// 3. Vaciar el array de sesión en memoria
$_SESSION = [];

// 4. Destruir la cookie de sesión en el navegador con sus parámetros idénticos
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

// 5. Destruir la sesión en el servidor
session_destroy();

// 6. Redirigir a login
header('Location: login.php', true, 303);
exit;