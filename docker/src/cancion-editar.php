<?php
// docker/src/cancion-editar.php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$allowed_categories = ['electronica', 'rock', 'bandas-sonoras'];
$allowed_audios = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];
$rutaCanciones = '/var/www/data/canciones.json';

// 1. Obtener y validar el ID recibido por GET o POST
$id_param = $_REQUEST['id'] ?? null;
$id_validado = filter_var($id_param, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id_validado === false) {
    http_response_code(400);
    echo 'Identificador de canción inválido.';
    exit;
}
$id = (int) $id_validado;

// 2. Cargar el catálogo actual para comprobar existencia
try {
    $songs = leerCatalogoCanciones();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error al leer el catálogo.';
    exit;
}

$cancionActual = null;
foreach ($songs as $s) {
    if (isset($s['id']) && (int) $s['id'] === $id) {
        $cancionActual = $s;
        break;
    }
}

if ($cancionActual === null) {
    http_response_code(404);
    echo 'La canción solicitada no existe.';
    exit;
}

// Variables iniciales precargadas con los datos actuales
$title = $cancionActual['title'] ?? '';
$artist = $cancionActual['artist'] ?? '';
$category = $cancionActual['category'] ?? '';
$duration = $cancionActual['duration'] ?? '';
$src = $cancionActual['src'] ?? '';
$errors = [];

// 3. Procesar formulario por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarTokenCsrf();

    // Validar título (máximo 80 caracteres)
    if (isset($_POST['title']) && is_string($_POST['title'])) {
        $title = trim($_POST['title']);
        $lenTitle = mb_strlen($title, 'UTF-8');
        if ($title === '') {
            $errors['title'] = 'El título es obligatorio.';
        } elseif ($lenTitle > 80) {
            $errors['title'] = 'El título no puede superar los 80 caracteres.';
        }
    } else {
        $errors['title'] = 'Título inválido.';
    }

    // Validar artista (máximo 80 caracteres)
    if (isset($_POST['artist']) && is_string($_POST['artist'])) {
        $artist = trim($_POST['artist']);
        $lenArtist = mb_strlen($artist, 'UTF-8');
        if ($artist === '') {
            $errors['artist'] = 'El artista es obligatorio.';
        } elseif ($lenArtist > 80) {
            $errors['artist'] = 'El artista no puede superar los 80 caracteres.';
        }
    } else {
        $errors['artist'] = 'Artista inválido.';
    }

    // Validar categoría
    if (isset($_POST['category']) && is_string($_POST['category']) && in_array($_POST['category'], $allowed_categories, true)) {
        $category = $_POST['category'];
    } else {
        $errors['category'] = 'Selecciona una categoría válida.';
    }

    // Validar duración (1 a 1800)
    if (isset($_POST['duration']) && is_string($_POST['duration'])) {
        $dur_val = filter_var(trim($_POST['duration']), FILTER_VALIDATE_INT);
        if ($dur_val === false || $dur_val < 1 || $dur_val > 1800) {
            $errors['duration'] = 'La duración debe estar entre 1 y 1800 segundos.';
        } else {
            $duration = (int) $dur_val;
        }
    } else {
        $errors['duration'] = 'Duración inválida.';
    }

    // Validar archivo de audio
    if (isset($_POST['src']) && is_string($_POST['src']) && in_array($_POST['src'], $allowed_audios, true)) {
        $src = $_POST['src'];
    } else {
        $errors['src'] = 'Selecciona un archivo de audio permitido.';
    }

    // Guardado bajo bloqueo si no hay errores
    if (empty($errors)) {
        try {
            actualizarJsonConBloqueo($rutaCanciones, function (array $coleccion) use ($id, $title, $artist, $category, $duration, $src) {
                $encontrado = false;
                foreach ($coleccion as $posicion => $item) {
                    if (isset($item['id']) && (int) $item['id'] === $id) {
                        // Conservamos el id estable y reemplazamos los campos validados
                        $coleccion[$posicion] = [
                            'id' => $id,
                            'title' => $title,
                            'artist' => $artist,
                            'category' => $category,
                            'duration' => $duration,
                            'src' => $src
                        ];
                        $encontrado = true;
                        break;
                    }
                }

                if (!$encontrado) {
                    throw new RuntimeException('La canción ya no existe al intentar guardar.');
                }

                return $coleccion;
            });

            header('Location: canciones.php?editada=1', true, 303);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            $errors['general'] = 'Error al actualizar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    <title>Editar Canción #<?php echo $id; ?> - Jukebox</title>
</head>
<body>
    <h1>Editar Canción #<?php echo $id; ?></h1>
    <nav>
        <a href="canciones.php">Volver al catálogo</a>
    </nav>
    <hr>

    <?php if (isset($errors['general'])): ?>
        <p style="color: red; font-weight: bold;"><?php echo $errors['general']; ?></p>
    <?php endif; ?>

    <form method="post" action="cancion-editar.php?id=<?php echo $id; ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <p>
            <label for="title">Título:</label><br>
            <input type="text" id="title" name="title" maxlength="80" value="<?php echo htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['title'])): ?>
                <span style="color: red;"><?php echo $errors['title']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="artist">Artista:</label><br>
            <input type="text" id="artist" name="artist" maxlength="80" value="<?php echo htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['artist'])): ?>
                <span style="color: red;"><?php echo $errors['artist']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="category">Categoría:</label><br>
            <select id="category" name="category">
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
            <label for="duration">Duración (segundos):</label><br>
            <input type="number" id="duration" name="duration" min="1" max="1800" value="<?php echo htmlspecialchars((string) $duration, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
            <?php if (isset($errors['duration'])): ?>
                <span style="color: red;"><?php echo $errors['duration']; ?></span>
            <?php endif; ?>
        </p>

        <p>
            <label for="src">Archivo de audio:</label><br>
            <select id="src" name="src">
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

        <button type="submit">Actualizar Canción</button>
    </form>
</body>
</html>