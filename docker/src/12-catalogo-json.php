<?php
// docker/src/12-catalogo-json.php
require_once __DIR__ . '/lib/canciones.php';
require_once __DIR__ . '/lib/almacen-json.php';
require_once __DIR__ . '/lib/acceso.php';

$songs = [];
$error_mensaje = null;

try {
    $songs = leerCatalogoCanciones();
} catch (JsonException $e) {
    http_response_code(500);
    $error_mensaje = "Error de formato en los datos JSON: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
} catch (RuntimeException $e) {
    http_response_code(500);
    $error_mensaje = "Error de almacenamiento: " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo desde JSON - Jukebox</title>
</head>
<body>
    <h1>Catálogo de Canciones (Almacenamiento JSON)</h1>

    <?php if ($error_mensaje !== null): ?>
        <p style="color: red; font-weight: bold;"><?php echo $error_mensaje; ?></p>
    <?php elseif (empty($songs)): ?>
        <p>Todavía no hay canciones</p>
    <?php else: ?>
        <div class="catalogo">
            <?php foreach ($songs as $song): ?>
                <article style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <h2>
                        #<?php echo (int) ($song['id'] ?? 0); ?> - 
                        <?php echo htmlspecialchars($song['title'] ?? 'Sin título', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                    </h2>
                    <p>
                        <strong>Artista:</strong> 
                        <?php echo htmlspecialchars($song['artist'] ?? 'Desconocido', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                    </p>
                    <p>
                        <strong>Categoría:</strong> 
                        <?php echo htmlspecialchars($song['category'] ?? 'General', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                    </p>
                    <p>
                        <strong>Duración:</strong> 
                        <?php echo duracionLegible((int) ($song['duration'] ?? 0)); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>