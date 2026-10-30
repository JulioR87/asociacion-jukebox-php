<?php
require_once __DIR__ . '/lib/canciones.php';

// Listas de valores permitidos
$allowed_categories = ['electronica', 'rock', 'bandas-sonoras'];
$allowed_audios = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];

// Variables para los campos y errores
$title = '';
$artist = '';
$category = '';
$duration = '';
$src = '';

$errors = [];
$preview = null;

// Comprobamos si la petición llega por POST (al pulsar Enviar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Validar Título (debe ser texto y no estar vacío)
    if (isset($_POST['title']) && is_string($_POST['title'])) {
        $title = trim($_POST['title']);
        if ($title === '') {
            $errors['title'] = 'El título es obligatorio y no puede contener solo espacios.';
        }
    } else {
        $errors['title'] = 'El título no es válido.';
    }

    // 2. Validar Artista (debe ser texto y no estar vacío)
    if (isset($_POST['artist']) && is_string($_POST['artist'])) {
        $artist = trim($_POST['artist']);
        if ($artist === '') {
            $errors['artist'] = 'El artista es obligatorio y no puede contener solo espacios.';
        }
    } else {
        $errors['artist'] = 'El artista no es válido.';
    }

    // 3. Validar Categoría (debe estar en la lista permitida)
    if (isset($_POST['category']) && is_string($_POST['category']) && in_array($_POST['category'], $allowed_categories, true)) {
        $category = $_POST['category'];
    } else {
        $errors['category'] = 'Selecciona una categoría válida.';
    }

    // 4. Validar Duración (entero entre 1 y 1800 segundos)
    if (isset($_POST['duration']) && is_string($_POST['duration'])) {
        $duration_input = trim($_POST['duration']);
        $duration_validada = filter_var($duration_input, FILTER_VALIDATE_INT);

        if ($duration_validada === false) {
            $errors['duration'] = 'La duración debe ser un número entero válido.';
        } elseif ($duration_validada < 1 || $duration_validada > 1800) {
            $errors['duration'] = 'La duración debe estar entre 1 y 1800 segundos.';
        } else {
            $duration = (int) $duration_validada;
        }
    } else {
        $errors['duration'] = 'Duración no válida.';
    }

    // 5. Validar Archivo de Audio / src (lista blanca)
    if (isset($_POST['src']) && is_string($_POST['src']) && in_array($_POST['src'], $allowed_audios, true)) {
        $src = $_POST['src'];
    } else {
        $errors['src'] = 'Selecciona un archivo de audio permitido.';
    }

    // Si hay errores, respondemos con código HTTP 400
    if (!empty($errors)) {
        http_response_code(400);
    } else {
        // Si no hay errores, construimos la ficha de vista previa
        $preview = [
            'title' => $title,
            'artist' => $artist,
            'category' => $category,
            'duration' => $duration,
            'src' => $src
        ];
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
    <h1>Añadir nueva canción (Vista previa)</h1>

    <!-- Bloque de vista previa cuando el formulario es válido -->
    <?php if ($preview !== null): ?>
        <section style="border: 2px solid green; padding: 15px; margin-bottom: 20px; background-color: #eefbee;">
            <h2>Vista previa, canción no guardada</h2>
            <p><strong>Título:</strong> <?php echo htmlspecialchars($preview['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Artista:</strong> <?php echo htmlspecialchars($preview['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Categoría:</strong> <?php echo htmlspecialchars($preview['category'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
            <p><strong>Duración:</strong> <?php echo duracionLegible($preview['duration']); ?> (<?php echo $preview['duration']; ?> s)</p>
            <p><strong>Archivo:</strong> <?php echo htmlspecialchars($preview['src'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
        </section>
    <?php endif; ?>

    <!-- Formulario POST que conserva los datos al fallar -->
    <form method="post" action="10-formulario.php">
        <p>
            <label for="title">Título:</label><br>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['title'])): ?>
                <span style="color: red;"><?php echo $errors['title']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="artist">Artista:</label><br>
            <input type="text" id="artist" name="artist" value="<?php echo htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['artist'])): ?>
                <span style="color: red;"><?php echo $errors['artist']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="category">Categoría:</label><br>
            <select id="category" name="category">
                <option value="">-- Selecciona --</option>
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
            <label for="duration">Duración (segundos, entre 1 y 1800):</label><br>
            <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars((string)$duration, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['duration'])): ?>
                <span style="color: red;"><?php echo $errors['duration']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="src">Audio disponible:</label><br>
            <select id="src" name="src">
                <option value="">-- Selecciona archivo --</option>
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

        <button type="submit">Generar vista previa</button>
    </form>
</body>
</html>