<?php
// docker/src/lib/sesion.php

// 1. Configuración de seguridad previa a la sesión
ini_set('session.use_strict_mode', '1');
session_name('JUKEBOXSESSID');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false, // false porque estamos en HTTP local
    'httponly' => true,  // Impide que JavaScript acceda a la cookie de sesión
    'samesite' => 'Lax'  // Mitiga ataques CSRF en navegaciones entre sitios
]);

// 2. Iniciar la sesión solo si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Generar el token CSRF si no existe todavía en la sesión
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Valida el token CSRF recibido por POST contra el token guardado en sesión.
 * Si falla, responde con HTTP 403 y detiene la ejecución.
 */
function validarTokenCsrf(): void
{
    $tokenRecibido = $_POST['csrf'] ?? null;

    if (!is_string($tokenRecibido) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo 'Acceso denegado: Token CSRF no válido o ausente.';
        exit;
    }
}