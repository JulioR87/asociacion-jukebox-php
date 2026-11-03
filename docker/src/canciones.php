<?php
// docker/src/canciones.php
// 1. Control de acceso estricto: exige sesión iniciada
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/canciones.php';
require_once __DIR__ . '/lib/almacen-json.php';

$songs = [];
$error_mensaje = null;

try {
    $songs = leerCatalogoCanciones();
} catch (Throwable $e) {
    http_response_code(500);
    $error_mensaje = 'Error al leer el catálogo: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Catálogo - Jukebox</title>
</head>
<body>
    <header>
        <h1>Catálogo de Canciones</h1>
        <p>Usuario: <strong><?php echo htmlspecialchars($_SESSION['alias'] ?? 'Usuario', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></p>
        <nav>
            <a href="home.php">Volver al Inicio</a> | 
            <a href="cancion-nueva.php"><strong>+ Añadir nueva canción</strong></a>
        </nav>
    </header>
    <hr>

    <?php if (isset($_GET['creada'])): ?>
        <p style="color: green; font-weight: bold;">Canción añadida correctamente al catálogo.</p>
    <?php endif; ?>

    <?php if ($error_mensaje !== null): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error_mensaje; ?></p>
    <?php elseif (empty($songs)): ?>
        <p>Todavía no hay canciones</p>
    <?php else: ?>
        <div class="catalogo">
            <?php foreach ($songs as $song): ?>
                <article style="border: 1px solid #ccc; padding: 12px; margin-bottom: 12px;">
                    <h2>
                        #<?php echo (int) ($song['id'] ?? 0); ?> - 
                        <?php echo htmlspecialchars($song['title'] ?? 'Sin título', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                    </h2>
                    <p><strong>Artista:</strong> <?php echo htmlspecialchars($song['artist'] ?? 'Desconocido', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($song['category'] ?? 'General', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
                    <p><strong>Duración:</strong> <?php echo duracionLegible((int) ($song['duration'] ?? 0)); ?> (<?php echo (int) ($song['duration'] ?? 0); ?> s)</p>
                    <p><strong>Ruta audio:</strong> <code><?php echo htmlspecialchars($song['src'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></code></p>
                    
                    <div style="margin-top: 10px;">
                        <!-- Enlace provisional para editar -->
                        <a href="cancion-editar.php?id=<?php echo (int) $song['id']; ?>">Editar</a>
                        
                        <!-- Formulario de borrado por POST con token (desactivado provisionalmente) -->
                        <form method="post" action="cancion-eliminar.php" style="display:inline; margin-left: 10px;">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                            <input type="hidden" name="id" value="<?php echo (int) $song['id']; ?>">
                            <button type="submit" disabled title="Se activará en el paso correspondiente">Eliminar</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>