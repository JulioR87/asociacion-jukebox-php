<?php
// Cargamos la biblioteca de funciones y los datos externos
require_once __DIR__ . '/lib/canciones.php';
$songs = require __DIR__ . '/lib/catalogo-inicial.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Musical - Jukebox</title>
</head>
<body>
    <h1>Catálogo de Canciones</h1>
    <?php if (empty($songs)): ?>
        <p>Todavía no hay canciones en el catálogo.</p>
    <?php else: ?>
        <div class="catalogo">
            <?php foreach ($songs as $song): ?>
                <article style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                  <h2>
                    #<?php echo (int) $song['id']; ?> - 
                    <?php echo htmlspecialchars($song['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                  </h2>
                  <p>
                    <strong>Artista:</strong>
                    <?php echo htmlspecialchars($song['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                  </p>
                  <p>
                    <strong>Categoria:</strong>
                    <?php echo htmlspecialchars($song['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                  </p>
                  <p>
                    <strong>Duración:</strong>
                    <?php echo (int) $song['duration']; ?> segundos
                  </p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>