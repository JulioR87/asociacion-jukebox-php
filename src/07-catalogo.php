<?php
// Catálogo de canciones en arrays asociativos
$songs = [
    [
        'id' => 1,
        'title' => 'Sesión de prueba',
        'artist' => 'Grupo del aula',
        'category' => 'electronica',
        'duration' => 185,
        'src' => 'audio/demo-01.mp3',
    ],
    [
        'id' => 2,
        'title' => 'Riff Callejero',
        'artist' => 'Banda Local',
        'category' => 'rock',
        'duration' => 210,
        'src' => 'audio/demo-02.mp3',
    ],
    [
        'id' => 3,
        'title' => 'Sinfonía del Barrio',
        'artist' => 'Orquesta Juvenil',
        'category' => 'bandas-sonoras',
        'duration' => 320,
        'src' => 'audio/demo-03.mp3',
    ],
    [
        'id' => 4,
        'title' => 'Ritmo Nocturno',
        'artist' => 'DJ Residente',
        'category' => 'electronica',
        'duration' => 145,
        'src' => 'audio/demo-04.mp3',
    ],
];
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