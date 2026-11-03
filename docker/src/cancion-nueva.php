<?php
// docker/src/cancion-nueva.php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$allowed_categories = ['electronica', 'rock', 'bandas-sonoras'];
$allowed_audios = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];

$title = '';
$artist = '';
$category = '';
$duration = '';
$src = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar Token CSRF
    validarTokenCsrf();

    // 2. Validar Título (máximo 80 caracteres)
    if (isset($_POST['title']) && is_string($_POST['title'])) {
        $title = trim($_POST['title']);
        $lenTitle = mb_strlen($title, 'UTF-8');
        if ($title === '') {
            $errors['title'] = 'El título es obligatorio.';
        } elseif ($lenTitle > 80) {
            $errors['title'] = 'El título no puede superar los 80 caracteres.';
        }
    } else {
        $errors['title'] = 'Título no válido.';
    }

    // 3. Validar Artista (máximo 80 caracteres)
    if (isset($_POST['artist']) && is_string($_POST['artist'])) {
        $artist = trim($_POST['artist']);
        $lenArtist = mb_strlen($artist, 'UTF-8');
        if ($artist === '') {
            $errors['artist'] = 'El artista es obligatorio.';
        } elseif ($lenArtist > 80) {
            $errors['artist'] = 'El artista no puede superar los 80 caracteres.';
        }
    } else {
        $errors['artist'] = 'Artista no válido.';
    }

    // 4. Validar Categoría
    if (isset($_POST['category']) && is_string($_POST['category']) && in_array($_POST['category'], $allowed_categories, true)) {
        $category = $_POST['category'];
    } else {
        $errors['category'] = 'Selecciona una categoría válida.';
    }

    // 5. Validar Duración (1 a 1800 segundos)
    if (isset($_POST['duration']) && is_string($_POST['duration'])) {
        $duration_input = trim($_POST['duration']);
        $duration_validada = filter_var($duration_input, FILTER_VALIDATE_INT);
        if ($duration_validada === false || $duration_validada < 1 || $duration_validada > 1800) {
            $errors['duration'] = 'La duración debe ser un número entero entre 1 y 1800 segundos.';
        } else {
            $duration = (int) $duration_validada;
        }
    } else {
        $errors['duration'] = 'Duración no válida.';
    }

    // 6. Validar Archivo de Audio
    if (isset($_POST['src']) && is_string($_POST['src']) && in_array($_POST['src'], $allowed_audios, true)) {
        $src = $_POST['src'];
    } else {
        $errors['src'] = 'Selecciona un archivo de audio permitido.';
    }

    // 7. Guardado atómico en disco si no hay errores
    if (empty($errors)) {
        $rutaCanciones = '/var/www/data/canciones.json';

        try {
            actualizarJsonConBloqueo($rutaCanciones, function (array $songs) use ($title, $artist, $category, $duration, $src) {
                // Cálculo de ID seguro: máximo existente + 1 (evita colisiones si se borraron elementos)
                $maxId = 0;
                foreach ($songs as $s) {
                    if (isset($s['id']) && $s['id'] > $maxId) {
                        $maxId = (int) $s['id'];
                    }
                }
                $newId = $maxId + 1;

                $songs[] = [
                    'id' => $newId,
                    'title' => $title,
                    'artist' => $artist,
                    'category' => $category,
                    'duration' => $duration,
                    'src' => $src
                ];

                return $songs;
            });

            // Redirección POST-Redirect-GET con código HTTP 303
            header('Location: canciones.php?creada=1', true, 303);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            $errors['general'] = 'Fallo al guardar la canción: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    } else {
        http_response_code(400);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Canción - Jukebox</title>
</head>
<body>
    <h1>Añadir Canción al Catálogo</h1>
    <nav>
        <a href="canciones.php">Volver al catálogo</a>
    </nav>
    <hr>

    <?php if (isset($errors['general'])): ?>
        <p style="color: red; font-weight: bold;"><?php echo $errors['general']; ?></p>
    <?php endif; ?>

    <form method="post" action="cancion-nueva.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">

        <p>
            <label for="title">Título (máx. 80 caracteres):</label><br>
            <input type="text" id="title" name="title" maxlength="80" value="<?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['title'])): ?>
                <span style="color: red;"><?php echo $errors['title']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="artist">Artista (máx. 80 caracteres):</label><br>
            <input type="text" id="artist" name="artist" maxlength="80" value="<?php echo htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['artist'])): ?>
                <span style="color: red;"><?php echo $errors['artist']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="category">Categoría:</label><br>
            <select id="category" name="category">
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach ($allowed_categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                        <?php echo ucfirst($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category'])): ?>
                <span style="color: red;"><?php echo $errors['category']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="duration">Duración en segundos (1 a 1800):</label><br>
            <input type="number" id="duration" name="duration" min="1" max="1800" value="<?php echo htmlspecialchars((string) $duration, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['duration'])): ?>
                <span style="color: red;"><?php echo $errors['duration']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="src">Archivo de audio:</label><br>
            <select id="src" name="src">
                <option value="">-- Selecciona un archivo --</option>
                <?php foreach ($allowed_audios as $audio): ?>
                    <option value="<?php echo $audio; ?>" <?php echo ($src === $audio) ? 'selected' : ''; ?>>
                        <?php echo $audio; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['src'])): ?>
                <span style="color: red;"><?php echo $errors['src']; ?></span>
            <?php endif; ?>
        </p>

        <button type="submit">Guardar Canción</button>
    </form>
</body>
</html>