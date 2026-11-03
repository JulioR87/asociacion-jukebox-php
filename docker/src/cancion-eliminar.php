<?php
// docker/src/cancion-eliminar.php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-json.php';

$rutaCanciones = '/var/www/data/canciones.json';

// Validar que el método HTTP sea exclusivamente GET o POST
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método HTTP no permitido.';
    exit;
}

// Validar el identificador numérico
$id_param = $_REQUEST['id'] ?? null;
$id_validado = filter_var($id_param, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id_validado === false) {
    http_response_code(400);
    echo 'Identificador inválido.';
    exit;
}
$id = (int) $id_validado;

// Comprobar existencia en el catálogo
try {
    $songs = leerCatalogoCanciones();
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Error al leer el catálogo.';
    exit;
}

$cancionEncontrada = null;
foreach ($songs as $s) {
    if (isset($s['id']) && (int) $s['id'] === $id) {
        $cancionEncontrada = $s;
        break;
    }
}

if ($cancionEncontrada === null) {
    http_response_code(404);
    echo 'La canción solicitada no existe o ya fue eliminada.';
    exit;
}

// 1. Si es POST: ejecutar el borrado definitivo bajo bloqueo exclusivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarTokenCsrf();

    try {
        actualizarJsonConBloqueo($rutaCanciones, function (array $coleccion) use ($id) {
            $posicionBorrar = null;
            foreach ($coleccion as $pos => $item) {
                if (isset($item['id']) && (int) $item['id'] === $id) {
                    $posicionBorrar = $pos;
                    break;
                }
            }

            if ($posicionBorrar === null) {
                throw new RuntimeException('La canción no fue encontrada para eliminar.');
            }

            // Quitar el elemento de la colección
            unset($coleccion[$posicionBorrar]);

            // Reconstruir índices consecutivos para garantizar que se guarde como lista JSON ([])
            return array_values($coleccion);
        });

        header('Location: canciones.php?eliminada=1', true, 303);
        exit;

    } catch (Throwable $e) {
        http_response_code(500);
        echo 'Error al eliminar la canción: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        exit;
    }
}

// 2. Si es GET: mostrar pantalla de confirmación previa
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Eliminación - Jukebox</title>
</head>
<body>
    <h1>¿Eliminar canción?</h1>
    <p>Estás a punto de eliminar la siguiente pista del catálogo:</p>
    
    <div style="border: 1px solid #c00; padding: 15px; margin-bottom: 20px; background-color: #fff5f5;">
        <p><strong>ID:</strong> #<?php echo $id; ?></p>
        <p><strong>Título:</strong> <?php echo htmlspecialchars($cancionEncontrada['title'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
        <p><strong>Artista:</strong> <?php echo htmlspecialchars($cancionEncontrada['artist'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
    </div>

    <form method="post" action="cancion-eliminar.php">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <button type="submit" style="background-color: #c00; color: white; padding: 8px 16px;">Sí, eliminar definitivamente</button>
        <a href="canciones.php" style="margin-left: 15px;">Cancelar</a>
    </form>
</body>
</html>