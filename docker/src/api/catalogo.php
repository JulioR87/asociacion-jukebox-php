<?php
// docker/src/api/catalogo.php
// Bloque 18: CRUD completo (list, create, update, delete)

require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/api-json.php';
require_once __DIR__ . '/../lib/canciones.php';
require_once __DIR__ . '/../lib/almacen-json.php';

// 1. Comprobar sesión activa (401 + JSON)
if (empty($_SESSION['usuario_id'])) {
    responderError(401, 'Debes iniciar sesión para acceder al catálogo.');
}

// 2. Validar el parámetro action de $_GET
$rawAction = $_GET['action'] ?? 'list';
if (!is_string($rawAction)) {
    responderError(400, 'El parámetro action no es válido.');
}

$action = trim($rawAction);
$accionesPermitidas = ['list', 'create', 'update', 'delete'];

if (!in_array($action, $accionesPermitidas, true)) {
    responderError(400, 'Acción no reconocida.');
}

$rutaCanciones = '/var/www/data/canciones.json';

// 3. Acción 'list' (solo GET)
if ($action === 'list') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        responderError(405, 'Método no permitido para listar. Usa GET.', ['Allow' => 'GET']);
    }

    if (!is_readable($rutaCanciones)) {
        error_log("Error API catalogo: No se puede leer $rutaCanciones");
        responderError(500, 'No se puede leer el catálogo en este momento.');
    }

    $fp = fopen($rutaCanciones, 'r');
    if ($fp === false) {
        responderError(500, 'Error interno al abrir el catálogo.');
    }

    flock($fp, LOCK_SH);
    $contenido = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    if ($contenido === false || trim($contenido) === '') {
        responderError(500, 'El archivo del catálogo está ilegible o vacío.');
    }

    $canciones = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($canciones)) {
        responderError(500, 'El formato del catálogo está dañado.');
    }

    responderExito(array_values($canciones), 200);
}

// 4. Para create, update y delete: exigir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError(405, 'Método no permitido para esta acción. Usa POST.', ['Allow' => 'POST']);
}

// 5. Validar token CSRF en la cabecera X-CSRF-Token
$tokenSesion = $_SESSION['csrf'] ?? null;
$tokenCabecera = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

if (!is_string($tokenSesion) || $tokenSesion === '' || !is_string($tokenCabecera) || !hash_equals($tokenSesion, $tokenCabecera)) {
    responderError(403, 'Token CSRF ausente o no válido.');
}

// 6. Validar cabecera Content-Type: application/json
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$tipoBase = strtolower(trim(explode(';', $contentType)[0]));

if ($tipoBase !== 'application/json') {
    responderError(415, 'Tipo de contenido no soportado. Se requiere application/json.');
}

// 7. Leer y decodificar cuerpo JSON desde php://input
$cuerpo = file_get_contents('php://input');
if ($cuerpo === false || trim($cuerpo) === '') {
    responderError(400, 'El cuerpo de la petición JSON está vacío.');
}

try {
    $objeto = json_decode($cuerpo, false, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    responderError(400, 'El JSON enviado está mal formado.');
}

if (!is_object($objeto)) {
    responderError(400, 'Se esperaba un objeto JSON con los datos de la operación.');
}

$datos = get_object_vars($objeto);

function validarIdCancion(array $datos): int
{
    if (!isset($datos['id']) || !is_int($datos['id']) || $datos['id'] <= 0) {
        responderError(422, 'El identificador de la canción debe ser un entero positivo.');
    }
    return $datos['id'];
}

function validarDatosCancion(array $datos): array
{
    if (!isset($datos['title']) || !is_string($datos['title'])) {
        responderError(422, 'El título debe ser una cadena de texto.');
    }
    if (!isset($datos['artist']) || !is_string($datos['artist'])) {
        responderError(422, 'El artista debe ser una cadena de texto.');
    }
    if (!isset($datos['category']) || !is_string($datos['category'])) {
        responderError(422, 'La categoría debe ser una cadena de texto.');
    }
    if (!isset($datos['src']) || !is_string($datos['src'])) {
        responderError(422, 'La ruta de audio debe ser una cadena de texto.');
    }
    if (!isset($datos['duration']) || !is_int($datos['duration'])) {
        responderError(422, 'La duración debe ser un número entero.');
    }

    $title = trim($datos['title']);
    $artist = trim($datos['artist']);
    $category = trim($datos['category']);
    $src = trim($datos['src']);
    $duration = $datos['duration'];

    if ($title === '' || mb_strlen($title) > 80) {
        responderError(422, 'El título es obligatorio y no puede superar los 80 caracteres.');
    }
    if ($artist === '' || mb_strlen($artist) > 80) {
        responderError(422, 'El artista es obligatorio y no puede superar los 80 caracteres.');
    }

    $categoriasPermitidas = defined('CATEGORIAS_PERMITIDAS')
        ? CATEGORIAS_PERMITIDAS
        : ['Hip-Hop', 'Rock', 'Pop', 'Electrónica', 'Jazz', 'Clásica', 'Otro'];

    if (!in_array($category, $categoriasPermitidas, true)) {
        responderError(422, 'La categoría seleccionada no está admitida.');
    }

    if ($duration < 1 || $duration > 1800) {
        responderError(422, 'La duración debe ser un entero entre 1 y 1800 segundos.');
    }

    $audiosAutorizados = [];
    $rutaAudioDir = __DIR__ . '/../audio';
    if (is_dir($rutaAudioDir)) {
        foreach (scandir($rutaAudioDir) as $archivo) {
            if ($archivo !== '.' && $archivo !== '..' && is_file($rutaAudioDir . '/' . $archivo)) {
                $audiosAutorizados[] = 'audio/' . $archivo;
            }
        }
    }

    if (!in_array($src, $audiosAutorizados, true)) {
        responderError(422, 'El archivo de audio seleccionado no está autorizado en el catálogo local.');
    }

    return [
        'title'    => $title,
        'artist'   => $artist,
        'category' => $category,
        'duration' => $duration,
        'src'      => $src,
    ];
}

// 8. Acción 'create'
if ($action === 'create') {
    $fichaLimpia = validarDatosCancion($datos);

    $fp = fopen($rutaCanciones, 'c+');
    if ($fp === false) {
        responderError(500, 'Error interno al acceder al archivo del catálogo.');
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        responderError(500, 'No se pudo bloquear el catálogo para escritura.');
    }

    rewind($fp);
    $contenido = stream_get_contents($fp);

    if ($contenido === false || trim($contenido) === '') {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El catálogo está vacío o ilegible.');
    }

    $canciones = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($canciones)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El formato del catálogo está dañado.');
    }

    $maxId = 0;
    foreach ($canciones as $item) {
        if (isset($item['id']) && is_numeric($item['id']) && (int)$item['id'] > $maxId) {
            $maxId = (int)$item['id'];
        }
    }

    $nuevaCancion = array_merge(['id' => $maxId + 1], $fichaLimpia);
    $canciones[] = $nuevaCancion;

    $nuevoJson = json_encode(array_values($canciones), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($nuevoJson === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'Error al codificar el catálogo actualizado.');
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $nuevoJson . "\n");
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    responderExito(array_values($canciones), 201);
}

// 9. Acción 'update'
if ($action === 'update') {
    $idEditar = validarIdCancion($datos);
    $fichaLimpia = validarDatosCancion($datos);

    $fp = fopen($rutaCanciones, 'c+');
    if ($fp === false) {
        responderError(500, 'Error interno al acceder al archivo del catálogo.');
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        responderError(500, 'No se pudo bloquear el catálogo para escritura.');
    }

    rewind($fp);
    $contenido = stream_get_contents($fp);

    if ($contenido === false || trim($contenido) === '') {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El catálogo está vacío o ilegible.');
    }

    $canciones = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($canciones)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El formato del catálogo está dañado.');
    }

    $encontrada = false;
    foreach ($canciones as $indice => $item) {
        if (isset($item['id']) && (int)$item['id'] === $idEditar) {
            $canciones[$indice] = array_merge(['id' => $idEditar], $fichaLimpia);
            $encontrada = true;
            break;
        }
    }

    if (!$encontrada) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(404, 'La canción seleccionada ya no existe en el catálogo.');
    }

    $nuevoJson = json_encode(array_values($canciones), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($nuevoJson === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'Error al codificar el catálogo actualizado.');
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $nuevoJson . "\n");
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    responderExito(array_values($canciones), 200);
}

// 10. Acción 'delete' (Bloque 18)
if ($action === 'delete') {
    $idEliminar = validarIdCancion($datos);

    $fp = fopen($rutaCanciones, 'c+');
    if ($fp === false) {
        responderError(500, 'Error interno al acceder al archivo del catálogo.');
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        responderError(500, 'No se pudo bloquear el catálogo para escritura.');
    }

    rewind($fp);
    $contenido = stream_get_contents($fp);

    if ($contenido === false || trim($contenido) === '') {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El catálogo está vacío o ilegible.');
    }

    $canciones = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($canciones)) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'El formato del catálogo está dañado.');
    }

    $encontrada = false;
    foreach ($canciones as $indice => $item) {
        if (isset($item['id']) && (int)$item['id'] === $idEliminar) {
            // Eliminamos la ficha sin renumerar los id restantes ni borrar el archivo MP3
            unset($canciones[$indice]);
            $encontrada = true;
            break;
        }
    }

    if (!$encontrada) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(404, 'La canción que intentas eliminar ya no existe.');
    }

    // Reordenamos las posiciones del array JSON con array_values sin tocar los id
    $listaActualizada = array_values($canciones);
    $nuevoJson = json_encode($listaActualizada, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($nuevoJson === false) {
        flock($fp, LOCK_UN);
        fclose($fp);
        responderError(500, 'Error al codificar el catálogo actualizado.');
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $nuevoJson . "\n");
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    responderExito($listaActualizada, 200);
}