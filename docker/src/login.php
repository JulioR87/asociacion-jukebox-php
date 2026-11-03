<?php
// docker/src/login.php
require_once __DIR__ . '/lib/sesion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Jukebox</title>
</head>
<body>
    <h1>Acceso de usuarios</h1>

    <?php if (isset($_GET['registrado'])): ?>
        <p style="color: green; font-weight: bold;">Registro completado con éxito. Ya puedes identificarte.</p>
    <?php endif; ?>

    <p><a href="register.php">Volver al registro</a></p>
</body>
</html>