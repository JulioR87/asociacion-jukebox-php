<?php
// docker/src/home.php
// Protección del servidor: si no está logueado, acceso.php redirige a login.php
require_once __DIR__ . '/lib/acceso.php';

$aliasUsuario = $_SESSION['alias'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio - Jukebox</title>
</head>
<body>
    <header>
        <h1>Jukebox Comunitaria</h1>
        <p>Bienvenido/a, <strong><?php echo htmlspecialchars($aliasUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></p>
    </header>

    <nav>
        <ul>
            <li><a href="12-catalogo-json.php">Explorar Catálogo Musical</a></li>
        </ul>
    </nav>

    <hr>

    <!-- El cierre de sesión DEBE ser por POST y con token CSRF -->
    <form method="post" action="logout.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
        <button type="submit">Cerrar sesión</button>
    </form>
</body>
</html>