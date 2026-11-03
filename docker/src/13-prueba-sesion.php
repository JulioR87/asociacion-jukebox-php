<?php
// docker/src/13-prueba-sesion.php
// Cargamos la sesión antes de generar cualquier salida HTML
require_once __DIR__ . '/lib/sesion.php';

$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Comprobamos el token CSRF con la función común
    validarTokenCsrf();

    // Si pasa la validación CSRF:
    $datoPrueba = trim($_POST['dato_prueba'] ?? '');
    $mensaje = "Envío aceptado correctamente. Dato recibido: " . htmlspecialchars($datoPrueba, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Sesión y CSRF - Jukebox</title>
</head>
<body>
    <h1>Prueba de Sesión y Token CSRF</h1>

    <p>Estado de autenticación: 
        <strong>
            <?php echo isset($_SESSION['usuario_id']) ? 'Sesión activa' : 'Usuario no autenticado (Sesión de invitado)'; ?>
        </strong>
    </p>

    <?php if ($mensaje !== null): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="post" action="13-prueba-sesion.php">
        <!-- Campo oculto con el token CSRF escapado -->
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

        <p>
            <label for="dato_prueba">Texto de prueba:</label><br>
            <input type="text" id="dato_prueba" name="dato_prueba" value="Probando token seguro">
        </p>

        <button type="submit">Enviar con CSRF</button>
    </form>
</body>
</html>