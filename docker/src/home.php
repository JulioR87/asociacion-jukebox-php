<?php
// docker/src/home.php
require_once __DIR__ . '/lib/acceso.php';

$aliasUsuario = $_SESSION['alias'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JUKEBOX</title>

    <!-- Tipografía Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Iconografía Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Hoja de estilos -->
    <link rel="stylesheet" href="assets/styles.css">

    <!-- Howler y scripts con defer -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js" defer></script>
    <script src="scripts/equalizer.js" defer></script>
    <script src="scripts/app.js" defer></script>
</head>

<body>

    <!-- Contenedor general que ocupa la pantalla -->
    <div class="jukebox-viewport">

        <!-- Barra superior con sesión PHP y nombre de la app -->
        <header class="top-status-bar">
            <div class="system-badge">
                <span class="status-dot"></span>
                <span class="status-text">HOWLER-MUSIC</span>
                <span class="php-user-badge">Conectado como: <strong><?php echo htmlspecialchars($aliasUsuario, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong></span>
            </div>

            <!-- RELOJ DIGITAL Y ACCIONES PHP -->
            <div class="status-icons-cluster">
                <a href="canciones.php" class="btn-php-nav"><i class="fa-solid fa-list-check"></i> CATÁLOGO</a>
                
                <form method="post" action="logout.php" style="display: inline; margin: 0;">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                    <button type="submit" class="btn-php-logout"><i class="fa-solid fa-power-off"></i> SALIR</button>
                </form>

                <div class="clock-pill" id="digital-clock">00:00:00</div>
            </div>
        </header>

        <main class="console-grid">

            <!-- ======================================================== -->
            <!-- 1. COLUMNA IZQUIERDA: VINILO & TRACKLIST                 -->
            <!-- ======================================================== -->
            <section class="asym-card panel-left-stage">
                <!-- Cabecera con título y selector de ordenación -->
                <div class="panel-header-badge"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; z-index: 10;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="tag-accent">TRACKLIST</span>
                        <h2 class="gold-title"><i class="fa-solid fa-record-vinyl"></i> LISTA</h2>
                    </div>

                    <!-- Selector de ordenación -->
                    <select id="select-ordenar"
                        style="background: rgba(18, 20, 26, 0.85); color: var(--gold-primary); border: 1px solid var(--gold-border); border-radius: 6px; padding: 4px 8px; font-size: 0.68rem; font-family: inherit; outline: none; cursor: pointer;">
                        <option value="id">Por Defecto</option>
                        <option value="titulo">Título (A-Z)</option>
                        <option value="artista">Artista (A-Z)</option>
                    </select>
                </div>
                <div class="deck-workspace">
                    <!-- Módulo del Vinilo Giratorio -->
                    <div class="vinyl-dock">
                        <div class="vinyl-shadow-envelope">
                            <!-- El disco con id para manipularlo desde JS -->
                            <div class="massive-vinyl" id="vinyl-disc">
                                <!-- Surcos decorativos -->
                                <div class="groove-band band-outer"></div>
                                <div class="groove-band band-mid"></div>
                                <div class="groove-band band-inner"></div>
                                <div class="sheen-reflection"></div>

                                <!-- Etiqueta central con la carátula -->
                                <div class="vinyl-center-label">
                                    <img id="vinyl-cover" src="img/ArcangelFelizNavidad8.jpg" alt="Vinilo">
                                    <div class="center-spindle-hole"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Contenedor para las canciones -->
                    <div class="tracks-floating-stack" id="playlist-container"></div>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 2. COLUMNA CENTRAL: VISUALIZADOR (SPECTRUM)              -->
            <!-- ======================================================== -->
            <section class="asym-card panel-center-visualizer">
                <div class="vis-header-block">
                    <div class="urban-dual-title">
                        <span class="title-gold">STUDIO</span>
                        <span class="title-crimson">SPECTRUM</span>
                    </div>
                    <div class="audio-metrics-badge">
                        <span class="pulse-indicator"></span>
                        <span>REAL-TIME FFT</span>
                    </div>
                </div>

                <div class="canvas-3d-wrapper">
                    <!-- Cuadrícula sutil de fondo -->
                    <div class="urban-perspective-grid"></div>
                    <!-- El lienzo donde dibujará JavaScript -->
                    <canvas id="visualizer-canvas"></canvas>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- 3. COLUMNA DERECHA: NOW PLAYING DETALLADO & ECUALIZADOR -->
            <!-- ======================================================== -->
            <section class="asym-card panel-right-rack">

                <!-- Módulo superior: Info destacada -->
                <div class="now-playing-module">
                    <div class="card-edge-label">REPRODUCIENDO</div>
                    <div class="np-content-flex">
                        <div class="np-meta-block">
                            <div class="np-song-title" id="rack-title">Selecciona una pista</div>
                            <div class="np-artist-genre">
                                <span class="artist-gold" id="rack-artist">---</span>
                                <span class="divider-bullet">•</span>
                                <span class="genre-tag" id="rack-genre">Audio System</span>
                            </div>
                        </div>
                        <div class="np-artwork-frame">
                            <img id="rack-cover" src="img/ArcangelFelizNavidad8.jpg" alt="Cover">
                        </div>
                    </div>
                </div>

                <!-- Módulo inferior: Rack del Ecualizador -->
                <div class="equalizer-console-module">
                    <div class="eq-top-bar">
                        <span class="gold-title-sm">PARAMETRIC EQ</span>
                        <span class="eq-subtext">3-BAND ANALOG</span>
                    </div>

                    <!-- Faders verticales (Bajos, Medios, Agudos) -->
                    <div class="faders-rack-container">

                        <!-- Canal Bajos -->
                        <div class="analog-channel">
                            <span class="gain-readout" id="gain-low">0 dB</span>
                            <div class="fader-track-unit">
                                <div class="tick-marks ticks-left">
                                    <span>+12</span><span>0</span><span>-12</span>
                                </div>
                                <div class="slider-rail">
                                    <input type="range" class="vertical-fader" id="eq-low" min="-12" max="12" step="1"
                                        value="0">
                                </div>
                            </div>
                            <div class="channel-label">
                                <span>LOW</span>
                                <span class="freq-tag">100 Hz</span>
                            </div>
                        </div>

                        <!-- Canal Medios -->
                        <div class="analog-channel">
                            <span class="gain-readout" id="gain-mid">0 dB</span>
                            <div class="fader-track-unit">
                                <div class="tick-marks ticks-left">
                                    <span>+12</span><span>0</span><span>-12</span>
                                </div>
                                <div class="slider-rail">
                                    <input type="range" class="vertical-fader" id="eq-mid" min="-12" max="12" step="1"
                                        value="0">
                                </div>
                            </div>
                            <div class="channel-label">
                                <span>MID</span>
                                <span class="freq-tag">1 kHz</span>
                            </div>
                        </div>

                        <!-- Canal Agudos -->
                        <div class="analog-channel">
                            <span class="gain-readout" id="gain-high">0 dB</span>
                            <div class="fader-track-unit">
                                <div class="tick-marks ticks-left">
                                    <span>+12</span><span>0</span><span>-12</span>
                                </div>
                                <div class="slider-rail">
                                    <input type="range" class="vertical-fader" id="eq-high" min="-12" max="12" step="1"
                                        value="0">
                                </div>
                            </div>
                            <div class="channel-label">
                                <span>HIGH</span>
                                <span class="freq-tag">8 kHz</span>
                            </div>
                        </div>

                    </div>

                    <!-- Botón para reiniciar todos los deslizadores a 0 dB -->
                    <button class="btn-oval-reset" id="btn-reset-eq">
                        <i class="fa-solid fa-rotate-left"></i> RESET EQ
                    </button>
                </div>

            </section>

        </main>

        <!-- Barra inferior de control del reproductor -->
        <footer class="continuous-player-deck">
            <div class="deck-controls-row">
                <!-- Información de la canción actual en el reproductor inferior -->
                <div class="deck-track-info">
                    <img id="current-cover" class="deck-mini-thumb" src="img/ArcangelFelizNavidad8.jpg" alt="Carátula actual">
                    <div class="deck-text-group">
                        <span id="current-title" class="deck-track-title">Seleccione una pista</span>
                        <span id="current-artist" class="deck-track-artist">---</span>
                    </div>
                </div>
                <!-- Contenedor central de los botones -->
                <div class="deck-main-buttons">
                    <!-- Botón Anterior -->
                    <button id="btn-prev" class="btn-deck-gold" title="Anterior">
                        <i class="fa-solid fa-backward-step"></i>
                    </button>
                    <!-- Botón Play / Pausa -->
                    <button id="btn-play" class="btn-deck-crimson-master" title="Reproducir">
                        <i class="fa-solid fa-play" id="play-icon"></i>
                    </button>
                    <!-- Botón Stop -->
                    <button id="btn-stop" class="btn-deck-gold" title="Detener">
                        <i class="fa-solid fa-stop"></i>
                    </button>
                    <!-- Botón Siguiente -->
                    <button id="btn-next" class="btn-deck-gold" title="Siguiente">
                        <i class="fa-solid fa-forward-step"></i>
                    </button>
                </div>
                <!-- Barra de tiempo y progreso -->
                <div class="deck-progress-cluster">
                    <span class="time-readout" id="current-time">00:00</span>
                    <div class="progress-track" id="progress-container">
                        <div class="progress-bar-fill" id="progress-fill"></div>
                    </div>
                    <span class="time-readout" id="total-duration">00:00</span>
                </div>
                <!-- Control de volumen y silencio -->
                <div class="deck-volume-cluster">
                    <button id="btn-mute" class="btn-deck-gold" title="Silenciar / Activar sonido">
                        <i class="fa-solid fa-volume-high" id="volume-icon"></i>
                    </button>
                    <input type="range" id="volume-slider" class="gold-range-track" min="0" max="1" step="0.01"
                        value="0.8">
                </div>
            </div>
        </footer>
    </div>

</body>
</html>