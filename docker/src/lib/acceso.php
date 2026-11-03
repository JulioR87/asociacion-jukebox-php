<?php
// docker/src/lib/acceso.php
require_once __DIR__ . '/sesion.php';

// Si no existe usuario_id en la sesión, el usuario no ha iniciado sesión
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php', true, 303);
    exit;
}