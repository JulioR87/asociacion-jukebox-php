<?php
// Cargar las funciones y los datos comunes del bloque anterior
require_once __DIR__ . '/lib/canciones.php';
$all_songs = require __DIR__ . '/lib/catalogo-inicial.php';

// 1.Obtenemos el parámetro por GET o asignamos 'todas' por defecto
$category = $_GET['category'] ?? 'todas';

// Categorías admitidas 
$allowed = ['todas', 'electronica', 'rock', 'bandas-sonoras'];

// 2.Comprobamos que sea una cadena de texto y que esté en la lista permitida
if (!is_string($category) || !in_array($category, $allowed, true)) {
    http_response_code(400);
    echo 'Categoría no válida';
    exit; // Detenemos la ejecución para no mostrar el HTML ni las canciones
}

// 3.Filtramos las canciones según la categoría validada
$filtered_songs = [];

if ($category === 'todas') {
    $filtered_songs = $all_songs;
} else {
    foreach ($all_songs as $song) {
        if ($song['category'] === $category) {
            $filtered_songs[] = $song;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Filtrar Catálogo - Jukebox</title>
</head>
<body>
    <h1>Catálogo de Canciones</h1>
    <p>Categoría seleccionada: <strong><?php echo htmlspecialchars($category, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></p>

    <!-- Enlaces para filtrar fácilmente -->
    <nav>
        <a href="09-filtro.php">Todas</a> |
        <a href="09-filtro.php?category=rock">Rock</a> |
        <a href="09-filtro.php?category=electronica">Electrónica</a> |
        <a href="09-filtro.php?category=bandas-sonoras">Bandas Sonoras</a>
    </nav>
    <hr>

    <?php if (empty($filtered_songs)): ?>
        <p>No se encontraron canciones para esta categoría.</p>
    <?php else: ?>
        <div class="catalogo">
            <?php foreach ($filtered_songs as $song): ?>
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
                        <strong>Categoría:</strong> 
                        <?php echo htmlspecialchars($song['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                    </p>
                    <p>
                        <strong>Duración:</strong> 
                        <?php echo duracionLegible((int) $song['duration']); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>