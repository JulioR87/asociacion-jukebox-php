<?php
// docker/src/canciones-ajax.php
// Bloque 16: Vista privada con formulario de alta segura y meta CSRF
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/canciones.php';

// Reutilizamos las listas autorizadas de tu librería si existen, o valores por defecto del catálogo
$categoriasPermitidas = defined('CATEGORIAS_PERMITIDAS')
    ? CATEGORIAS_PERMITIDAS
    : ['Hip-Hop', 'Rock', 'Pop', 'Electrónica', 'Jazz', 'Clásica', 'Otro'];

$audiosDisponibles = [];
$rutaAudioDir = __DIR__ . '/audio';
if (is_dir($rutaAudioDir)) {
    foreach (scandir($rutaAudioDir) as $archivo) {
        if ($archivo !== '.' && $archivo !== '..' && is_file($rutaAudioDir . '/' . $archivo)) {
            $audiosDisponibles[] = 'audio/' . $archivo;
        }
    }
}
sort($audiosDisponibles);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <title>Catálogo AJAX - Jukebox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            color: #e5e5e5;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 2rem;
        }
        .contenedor {
            max-width: 1050px;
            margin: 0 auto;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .nav-links a {
            color: #f59e0b;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.4rem 0.8rem;
            border: 1px solid #f59e0b;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .nav-links a:hover {
            background-color: #f59e0b;
            color: #121212;
        }
        #estado-mensaje {
            padding: 0.85rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            background-color: #1e1e1e;
            border-left: 4px solid #3b82f6;
            min-height: 1.4rem;
        }
        #estado-mensaje.error {
            border-left-color: #ef4444;
            background-color: #2a1515;
            color: #fca5a5;
        }
        #estado-mensaje.ok {
            border-left-color: #10b981;
            background-color: #132a20;
            color: #6ee7b7;
        }
        .panel-formulario {
            background-color: #181818;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .panel-formulario h2 {
            margin-top: 0;
            color: #f59e0b;
            font-size: 1.15rem;
            margin-bottom: 1rem;
        }
        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .campo {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .campo label {
            font-size: 0.85rem;
            color: #a3a3a3;
        }
        .campo input, .campo select {
            background-color: #242424;
            border: 1px solid #3f3f46;
            color: #f4f4f5;
            padding: 0.6rem 0.75rem;
            border-radius: 5px;
            font-size: 0.95rem;
        }
        .campo input:focus, .campo select:focus {
            outline: none;
            border-color: #f59e0b;
        }
        .acciones-form {
            margin-top: 1.2rem;
            display: flex;
            gap: 0.75rem;
        }
        .btn-guardar {
            background-color: #f59e0b;
            color: #121212;
            border: none;
            padding: 0.65rem 1.25rem;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-guardar:hover {
            background-color: #d97706;
        }
        .btn-guardar:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #181818;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 0.85rem 1rem;
            text-align: left;
            border-bottom: 1px solid #2a2a2a;
        }
        th {
            background-color: #222;
            color: #f59e0b;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
        }
    </style>
    <script src="scripts/catalogo-ajax.js" defer></script>
</head>
<body>
    <div class="contenedor">
        <header>
            <h1><i class="fa-solid fa-bolt"></i> Catálogo AJAX</h1>
            <nav class="nav-links">
                <a href="home.php"><i class="fa-solid fa-headphones"></i> Reproductor</a>
                <a href="canciones.php"><i class="fa-solid fa-list-check"></i> CRUD Clásico</a>
            </nav>
        </header>

        <div id="estado-mensaje" role="status" aria-live="polite">Preparando consulta...</div>

        <section class="panel-formulario">
            <h2 id="titulo-formulario"><i class="fa-solid fa-plus-circle"></i> Añadir nueva canción</h2>
            <form id="form-cancion">
                <input type="hidden" id="cancion-id" name="id" value="">

                <div class="grid-form">
                    <div class="campo">
                        <label for="title">Título *</label>
                        <input type="text" id="title" name="title" required maxlength="80" placeholder="Ej. Still D.R.E.">
                    </div>

                    <div class="campo">
                        <label for="artist">Artista *</label>
                        <input type="text" id="artist" name="artist" required maxlength="80" placeholder="Ej. Dr. Dre">
                    </div>

                    <div class="campo">
                        <label for="category">Categoría *</label>
                        <select id="category" name="category" required>
                            <option value="">Selecciona categoría...</option>
                            <?php foreach ($categoriasPermitidas as $cat): ?>
                                <option value="<?= htmlspecialchars($cat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <?= htmlspecialchars($cat, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="duration">Duración (segundos) *</label>
                        <input type="number" id="duration" name="duration" required min="1" max="1800" step="1" placeholder="1 - 1800">
                    </div>

                    <div class="campo">
                        <label for="src">Archivo de audio *</label>
                        <select id="src" name="src" required>
                            <option value="">Selecciona audio...</option>
                            <?php foreach ($audiosDisponibles as $rutaAudio): ?>
                                <option value="<?= htmlspecialchars($rutaAudio, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <?= htmlspecialchars(basename($rutaAudio), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="acciones-form">
                    <button type="submit" id="btn-guardar" class="btn-guardar">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar canción
                    </button>
                    <button type="button" id="btn-cancelar" hidden style="background-color:#3f3f46;color:#f4f4f5;border:none;padding:0.65rem 1.25rem;border-radius:5px;font-weight:600;cursor:pointer;"><i class="fa-solid fa-xmark"></i> Cancelar</button>
                </div>
            </form>
        </section>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Artista</th>
                    <th>Categoría</th>
                    <th>Duración</th>
                    <th>Archivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-canciones-body">
                <tr>
                    <td colspan="7">Esperando datos del servidor...</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>