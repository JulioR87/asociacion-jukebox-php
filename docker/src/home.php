<?php
// docker/src/home.php
require_once __DIR__ . '/lib/acceso.php';

$aliasUsuario =$_SESSION['alias'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Jukebox - Reproductor</title>
    <!-- Estilos de tu interfaz -->
    <!--<link rel="stylesheet" href="assets/styles.css">-->
    <!-- Iconos FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Librerías y Scripts con defer en orden -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js" defer></script>
    <script src="scripts/equalizer.js" defer></script>
    <script src="scripts/app.js" defer></script>
</head>
<body style="background-color: #0d0d0d; color: #ffffff; font-family: sans-serif; margin: 0; padding: 0;">

    <!-- Barra de navegación superior -->
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 12px 24px; background: #111; color: #dfbe7e; border-bottom: 2px solid #333;">
        <div>
            <h1 style="margin: 0; font-size: 1.3rem;">Jukebox Comunitaria</h1>
            <small style="color: #bbb;">Conectado como: <strong><?php echo htmlspecialchars($aliasUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></small>
        </div>
        <div>
            <a href="canciones.php" style="color: #dfbe7e; margin-right: 20px; text-decoration: none; font-weight: bold;">Administrar Catálogo</a>
            <!-- Formulario de cierre de sesión por POST con token CSRF -->
            <form method="post" action="logout.php" style="display: inline;">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                <button type="submit" style="background: #900; color: #fff; border: 1px solid #c00; padding: 6px 14px; cursor: pointer; border-radius: 4px;">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <main style="padding: 20px;">
        <!-- Reloj digital -->
        <div id="digital-clock" style="text-align: center; font-size: 1.5rem; margin-bottom: 15px; color: #dfbe7e; font-family: monospace;"></div>

        <!-- Contenedor general del Jukebox -->
        <div class="jukebox-container" style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            
            <!-- Columna izquierda: Lista de reproducción -->
            <section style="flex: 1; min-width: 280px; max-width: 380px; background: #161616; padding: 15px; border-radius: 6px; border: 1px solid #333;">
                <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <label for="select-ordenar" style="color: #dfbe7e;">Ordenar por:</label>
                    <select id="select-ordenar" style="background: #222; color: #fff; border: 1px solid #444; padding: 4px;">
                        <option value="id">Original</option>
                        <option value="titulo">Título</option>
                        <option value="artista">Artista</option>
                    </select>
                </div>
                <div id="playlist-container" class="playlist-cards" style="max-height: 480px; overflow-y: auto;">
                    <p style="color: #888;">Cargando catálogo...</p>
                </div>
            </section>

            <!-- Columna central: Vinilo, Visualizador y Mandos -->
            <section style="flex: 1; min-width: 320px; max-width: 440px; text-align: center; background: #161616; padding: 15px; border-radius: 6px; border: 1px solid #333;">
                
                <!-- Vinilo giratorio -->
                <div id="vinyl-disc" class="vinyl-disc" style="margin: 0 auto 15px auto;">
                    <img id="vinyl-cover" class="vinyl-cover" src="img/SnoopDoglitIceCube.jpg" alt="Carátula Vinilo" style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 2px solid #dfbe7e;">
                </div>

                <!-- Canvas para el visualizador/ecualizador -->
                <canvas id="visualizer-canvas" width="320" height="70" style="background: #000; border: 1px solid #444; display: block; margin: 0 auto 15px auto;"></canvas>

                <!-- Barra de progreso interactiva -->
                <div id="progress-container" style="background: #333; height: 8px; width: 100%; cursor: pointer; position: relative; margin-bottom: 5px; border-radius: 4px;">
                    <div id="progress-fill" style="background: #dfbe7e; height: 100%; width: 0%; border-radius: 4px;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #aaa; margin-bottom: 15px;">
                    <span id="current-time">00:00</span>
                    <span id="total-duration">00:00</span>
                </div>

                <!-- Metadatos de la canción sonando -->
                <div style="margin-bottom: 15px;">
                    <div id="current-title" style="font-weight: bold; font-size: 1.1rem; color: #fff;">Selecciona una pista</div>
                    <div id="current-artist" style="color: #aaa; font-size: 0.9rem;">-</div>
                </div>

                <!-- Botonera -->
                <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 15px;">
                    <button type="button" id="btn-prev" style="background: #222; color: #dfbe7e; border: 1px solid #444; padding: 8px 14px; cursor: pointer;">⏮</button>
                    <button type="button" id="btn-play" style="background: #dfbe7e; color: #111; border: none; padding: 8px 18px; cursor: pointer; font-size: 1rem;"><i id="play-icon" class="fa-solid fa-play"></i></button>
                    <button type="button" id="btn-stop" style="background: #222; color: #dfbe7e; border: 1px solid #444; padding: 8px 14px; cursor: pointer;">⏹</button>
                    <button type="button" id="btn-next" style="