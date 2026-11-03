// docker/src/scripts/app.js
document.addEventListener('DOMContentLoaded', () => {
  // RELOJ DIGITAL
  const digitalClok = document.getElementById('digital-clock');

  function actualizarReloj() {
    const ahora = new Date();
    if (digitalClok) {
      digitalClok.textContent = ahora.toLocaleTimeString();
    }
  }

  actualizarReloj();
  setInterval(actualizarReloj, 1000);

  //================================================================================
  // VARIABLES DE ESTADO Y ELEMENTOS DOM
  let canciones = [];
  let indiceActual = 0;
  let reproduccionActual = null;

  // Botones
  const btnPlay = document.getElementById('btn-play');
  const btnIcon = document.getElementById('play-icon');
  const btnStop = document.getElementById('btn-stop');
  const btnPrev = document.getElementById('btn-prev');
  const btnNext = document.getElementById('btn-next');

  // Progreso y tiempos
  const currentTimeText = document.getElementById('current-time');
  const totalDurationText = document.getElementById('total-duration');
  const progressContainer = document.getElementById('progress-container');
  const progressFill = document.getElementById('progress-fill');

  // Metadatos
  const currentTitle = document.getElementById('current-title');
  const currentArtist = document.getElementById('current-artist');
  const currentCover = document.getElementById('current-cover');

  // Vinilo
  const vinylDisc = document.getElementById('vinyl-disc');
  const vinylCover = document.getElementById('vinyl-cover');

  // Canvas visualizador / ecualizador
  const canvas = document.getElementById('visualizer-canvas') || document.getElementById('equalizer');
  const canvasCtx = canvas ? canvas.getContext('2d') : null;
  let audioContext = null;
  let analizador = null;
  let fuenteConectada = false;
  let idAnimacion = null;

  // Rack lateral derecho
  const rackTitle = document.getElementById('rack-title');
  const rackArtist = document.getElementById('rack-artist');
  const rackGenre = document.getElementById('rack-genre');
  const rackCover = document.getElementById('rack-cover');

  const eqLow = document.getElementById('eq-low');
  const eqMid = document.getElementById('eq-mid');
  const eqHigh = document.getElementById('eq-high');
  const gainLowText = document.getElementById('gain-low');
  const gainMidText = document.getElementById('gain-mid');
  const gainHighText = document.getElementById('gain-high');
  const btnResetEq = document.getElementById('btn-reset-eq');

  const selectOrdenar = document.getElementById('select-ordenar');
  let temporizadorProgreso = null;

  const volumeSlider = document.getElementById('volume-slider');
  const btnMute = document.getElementById('btn-mute');
  const volumeIcon = document.getElementById('volume-icon');

  let estaSilenciado = false;
  if (typeof Howler !== 'undefined') {
    Howler.volume(0.8);
  }

  //================================================================================
  // PINTAR LISTA CON textContent Y CONTROL DE ESTADO VACÍO (Paso 3)
  function mostrarPlaylist() {
    const contenedor = document.getElementById('playlist-container');
    if (!contenedor) return;

    contenedor.innerHTML = '';

    // Si la colección está vacía, desactivar reproducción y explicar motivo
    if (!Array.isArray(canciones) || canciones.length === 0) {
      const msgVacio = document.createElement('p');
      msgVacio.style.color = '#888';
      msgVacio.textContent = 'No hay canciones en el catálogo. Añade una desde el administrador.';
      contenedor.appendChild(msgVacio);

      if (btnPlay) btnPlay.disabled = true;
      if (currentTitle) currentTitle.textContent = 'Sin pistas disponibles';
      if (currentArtist) currentArtist.textContent = '-';
      return;
    }

    if (btnPlay) btnPlay.disabled = false;

    canciones.forEach((cancion, index) => {
      const tarjeta = document.createElement('div');
      tarjeta.className = 'track-card';

      const thumb = document.createElement('img');
      thumb.className = 'card-thumb';
      thumb.src = cancion.caratula || 'img/default.jpg';
      thumb.alt = cancion.titulo;

      const cardText = document.createElement('div');
      cardText.className = 'card-text';

      // Uso estricto de textContent para textos recibidos
      const titleEl = document.createElement('div');
      titleEl.className = 'card-title';
      titleEl.textContent = cancion.titulo;

      const subEl = document.createElement('div');
      subEl.className = 'card-sub';
      subEl.textContent = `${cancion.artista} - ${cancion.genero}`;

      cardText.appendChild(titleEl);
      cardText.appendChild(subEl);
      tarjeta.appendChild(thumb);
      tarjeta.appendChild(cardText);

      tarjeta.addEventListener('click', () => {
        reproducirCancion(index);
      });

      contenedor.appendChild(tarjeta);
    });
  }

  //================================================================================
  // CARGAR CANCIONES DESDE LA API PRIVADA (Paso 3)
  async function cargarCanciones() {
    try {
      // Petición a ./api/canciones.php desde la misma web y puerto
      const response = await fetch('./api/canciones.php');
      if (!response.ok) {
        throw new Error(`Error en respuesta API: ${response.status}`);
      }

      const songs = await response.json();

      // Validación de lista válida
      if (!Array.isArray(songs)) {
        throw new Error('La respuesta recibida no es una lista válida.');
      }

      // Adaptación de propiedades: title, artist, category, src -> variables internas
      canciones = songs.map(item => ({
        id: item.id,
        titulo: item.title || 'Sin título',
        artista: item.artist || 'Artista desconocido',
        genero: item.category || 'General',
        archivo: item.src || '',
        caratula: item.cover || 'img/default.jpg'
      }));

      mostrarPlaylist();

      // Inicializar índice en 0 si hay datos
      if (canciones.length > 0) {
        indiceActual = 0;
      }
    } catch (error) {
      console.error('Error controlado al cargar catálogo:', error);
      const contenedor = document.getElementById('playlist-container');
      if (contenedor) {
        contenedor.innerHTML = '';
        const msgError = document.createElement('p');
        msgError.style.color = '#c00';
        msgError.textContent = 'Error al cargar las canciones desde el servidor.';
        contenedor.appendChild(msgError);
      }
    }
  }

  cargarCanciones();

  //================================================================================
  // ORDENAR PLAYLIST
  function ordenarPlaylist(criterio) {
    if (canciones.length === 0) return;

    const cancionSonando = canciones[indiceActual];

    if (criterio === 'titulo') {
      canciones.sort((a, b) => a.titulo.localeCompare(b.titulo));
    } else if (criterio === 'artista') {
      canciones.sort((a, b) => a.artista.localeCompare(b.artista));
    } else if (criterio === 'id') {
      canciones.sort((a, b) => a.id - b.id);
    }

    if (cancionSonando) {
      indiceActual = canciones.findIndex(c => c.id === cancionSonando.id);
    }

    mostrarPlaylist();
    if (reproduccionActual && reproduccionActual.playing()) {
      resaltarTarjetaActiva(indiceActual);
    }
  }

  //================================================================================
  // ANALIZADOR Y CANVAS
  function inicializarAnalizador() {
    if (analizador || typeof Howler === 'undefined') return;

    audioContext = Howler.ctx;
    analizador = audioContext.createAnalyser();
    analizador.fftSize = 128;

    if (Howler.masterGain && !fuenteConectada) {
      Howler.masterGain.connect(analizador);
      fuenteConectada = true;
    }
  }

  function dibujarVisualizador() {
    idAnimacion = requestAnimationFrame(dibujarVisualizador);
    if (!canvas || !canvasCtx || !analizador) return;

    if (canvas.width !== canvas.clientWidth || canvas.height !== canvas.clientHeight) {
      canvas.width = canvas.clientWidth;
      canvas.height = canvas.clientHeight;
    }

    const totalBarras = analizador.frequencyBinCount;
    const datosFrecuencia = new Uint8Array(totalBarras);
    analizador.getByteFrequencyData(datosFrecuencia);

    canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
    const anchoBarra = (canvas.width / totalBarras) * 1.5;
    let posicionX = 0;

    for (let i = 0; i < totalBarras; i++) {
      const alturaBarra = (datosFrecuencia[i] / 255) * (canvas.height * 0.85);
      const gradiente = canvasCtx.createLinearGradient(0, canvas.height, 0, canvas.height - alturaBarra);
      gradiente.addColorStop(0, 'rgba(197, 160, 89, 0.2)');
      gradiente.addColorStop(1, 'rgba(223, 190, 126, 0.9)');

      canvasCtx.fillStyle = gradiente;
      const y = canvas.height - alturaBarra;
      canvasCtx.fillRect(posicionX, y, anchoBarra - 2, alturaBarra);
      posicionX += anchoBarra + 1;
    }
  }

  //================================================================================
  // REPRODUCCIÓN (No crear Howl si la lista está vacía)
  function reproducirCancion(indice) {
    if (canciones.length === 0) return;

    if (reproduccionActual) {
      reproduccionActual.stop();
      reproduccionActual.unload();
    }

    indiceActual = indice;
    const cancion = canciones[indiceActual];
    if (!cancion) return;

    if (currentTitle) currentTitle.textContent = cancion.titulo;
    if (currentArtist) currentArtist.textContent = cancion.artista;
    if (currentCover) {
      currentCover.src = cancion.caratula;
      currentCover.alt = cancion.titulo;
    }

    if (vinylCover) {
      vinylCover.src = cancion.caratula;
      vinylCover.alt = cancion.titulo;
    }

    if (rackTitle) rackTitle.textContent = cancion.titulo;
    if (rackArtist) rackArtist.textContent = cancion.artista;
    if (rackGenre) rackGenre.textContent = cancion.genero;
    if (rackCover) {
      rackCover.src = cancion.caratula;
      rackCover.alt = cancion.titulo;
    }

    resaltarTarjetaActiva(indiceActual);

    // Creación de Howl con comprobación de src
    reproduccionActual = new Howl({
      src: [cancion.archivo],
      html5: false,
      onloaderror: (id, error) => {
        console.error('Error al cargar la pista de audio:', error, cancion.archivo);
      },
      onplay: () => {
        if (btnIcon) {
          btnIcon.classList.remove('fa-play');
          btnIcon.classList.add('fa-pause');
        }
        if (vinylDisc) vinylDisc.classList.add('spinning');
        if (typeof Ecualizador !== 'undefined') Ecualizador.inicializar();
        inicializarAnalizador();
        cancelAnimationFrame(idAnimacion);
        dibujarVisualizador();

        if (totalDurationText) {
          totalDurationText.textContent = formatearTiempo(reproduccionActual.duration());
        }

        clearInterval(temporizadorProgreso);
        temporizadorProgreso = setInterval(actualizarProgreso, 500);
      },
      onpause: () => {
        if (btnIcon) {
          btnIcon.classList.remove('fa-pause');
          btnIcon.classList.add('fa-play');
        }
        if (vinylDisc) vinylDisc.classList.remove('spinning');
        clearInterval(temporizadorProgreso);
      },
      onstop: () => {
        if (btnIcon) {
          btnIcon.classList.remove('fa-pause');
          btnIcon.classList.add('fa-play');
        }
        if (vinylDisc) vinylDisc.classList.remove('spinning');
        cancelAnimationFrame(idAnimacion);
        if (canvasCtx && canvas) {
          canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
        }
        clearInterval(temporizadorProgreso);
        if (progressFill) progressFill.style.width = '0%';
        if (currentTimeText) currentTimeText.textContent = '00:00';
      },
      onend: () => {
        if (vinylDisc) vinylDisc.classList.remove('spinning');
        cancelAnimationFrame(idAnimacion);
        if (canvasCtx && canvas) {
          canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
        }
        clearInterval(temporizadorProgreso);
        if (progressFill) progressFill.style.width = '0%';
        if (currentTimeText) currentTimeText.textContent = '00:00';
        siguienteCancion();
      }
    });

    reproduccionActual.play();
  }

  function alternarPlayPause() {
    if (canciones.length === 0) return;

    if (!reproduccionActual) {
      reproducirCancion(indiceActual);
      return;
    }
    if (reproduccionActual.playing()) {
      reproduccionActual.pause();
    } else {
      reproduccionActual.play();
    }
  }

  function detenerCancion() {
    if (reproduccionActual) reproduccionActual.stop();
  }

  function siguienteCancion() {
    if (canciones.length === 0) return;
    indiceActual = (indiceActual + 1 >= canciones.length) ? 0 : indiceActual + 1;
    reproducirCancion(indiceActual);
  }

  function anteriorCancion() {
    if (canciones.length === 0) return;
    indiceActual = (indiceActual - 1 < 0) ? canciones.length - 1 : indiceActual - 1;
    reproducirCancion(indiceActual);
  }

  function cambiarVolumen(evento) {
    const nivel = parseFloat(evento.target.value);
    if (typeof Howler !== 'undefined') Howler.volume(nivel);
    if (estaSilenciado && nivel > 0) {
      Howler.mute(false);
      estaSilenciado = false;
    }
    actualizarIconoVolumen(nivel);
  }

  function alternarMute() {
    estaSilenciado = !estaSilenciado;
    if (typeof Howler !== 'undefined') Howler.mute(estaSilenciado);
    if (volumeIcon) {
      if (estaSilenciado) {
        volumeIcon.className = 'fa-solid fa-volume-xmark';
      } else if (volumeSlider) {
        actualizarIconoVolumen(parseFloat(volumeSlider.value));
      }
    }
  }

  function actualizarIconoVolumen(nivel) {
    if (!volumeIcon) return;
    if (nivel === 0) {
      volumeIcon.className = 'fa-solid fa-volume-xmark';
    } else if (nivel < 0.5) {
      volumeIcon.className = 'fa-solid fa-volume-low';
    } else {
      volumeIcon.className = 'fa-solid fa-volume-high';
    }
  }

  function formatearTiempo(segundos) {
    if (isNaN(segundos) || segundos === 0) return '00:00';
    const minutos = Math.floor(segundos / 60);
    const segRestantes = Math.floor(segundos % 60);
    return `${String(minutos).padStart(2, '0')}:${String(segRestantes).padStart(2, '0')}`;
  }

  function actualizarProgreso() {
    if (reproduccionActual && reproduccionActual.playing()) {
      const tiempoActual = reproduccionActual.seek() || 0;
      const duracionTotal = reproduccionActual.duration() || 0;

      if (currentTimeText) currentTimeText.textContent = formatearTiempo(tiempoActual);
      if (totalDurationText) totalDurationText.textContent = formatearTiempo(duracionTotal);

      if (duracionTotal > 0 && progressFill) {
        const porcentaje = (tiempoActual / duracionTotal) * 100;
        progressFill.style.width = `${porcentaje}%`;
      }
    }
  }

  function saltarA(evento) {
    if (!reproduccionActual || !progressContainer) return;
    const rectangulo = progressContainer.getBoundingClientRect();
    const porcentajeClic = (evento.clientX - rectangulo.left) / rectangulo.width;
    const duracionTotal = reproduccionActual.duration() || 0;
    reproduccionActual.seek(duracionTotal * porcentajeClic);
    actualizarProgreso();
  }

  function resaltarTarjetaActiva(indice) {
    const todasLasTarjetas = document.querySelectorAll('.track-card');
    todasLasTarjetas.forEach((tarjeta, i) => {
      if (i === indice) {
        tarjeta.classList.add('active');
      } else {
        tarjeta.classList.remove('active');
      }
    });
  }

  // Eventos de botones
  if (btnPlay) btnPlay.addEventListener('click', alternarPlayPause);
  if (btnStop) btnStop.addEventListener('click', detenerCancion);
  if (btnNext) btnNext.addEventListener('click', siguienteCancion);
  if (btnPrev) btnPrev.addEventListener('click', anteriorCancion);

  if (volumeSlider) volumeSlider.addEventListener('input', cambiarVolumen);
  if (btnMute) btnMute.addEventListener('click', alternarMute);
  if (progressContainer) progressContainer.addEventListener('click', saltarA);

  if (selectOrdenar) {
    selectOrdenar.addEventListener('change', (e) => {
      ordenarPlaylist(e.target.value);
    });
  }

  if (eqLow) {
    eqLow.addEventListener('input', (e) => {
      const val = parseFloat(e.target.value);
      if (gainLowText) gainLowText.textContent = `${val > 0 ? '+' : ''}${val} dB`;
      if (typeof Ecualizador !== 'undefined') Ecualizador.setBajos(val);
    });
  }

  if (eqMid) {
    eqMid.addEventListener('input', (e) => {
      const val = parseFloat(e.target.value);
      if (gainMidText) gainMidText.textContent = `${val > 0 ? '+' : ''}${val} dB`;
      if (typeof Ecualizador !== 'undefined') Ecualizador.setMedios(val);
    });
  }

  if (eqHigh) {
    eqHigh.addEventListener('input', (e) => {
      const val = parseFloat(e.target.value);
      if (gainHighText) gainHighText.textContent = `${val > 0 ? '+' : ''}${val} dB`;
      if (typeof Ecualizador !== 'undefined') Ecualizador.setAgudos(val);
    });
  }

  if (btnResetEq) {
    btnResetEq.addEventListener('click', () => {
      if (eqLow) { eqLow.value = 0; if (gainLowText) gainLowText.textContent = '0 dB'; if (typeof Ecualizador !== 'undefined') Ecualizador.setBajos(0); }
      if (eqMid) { eqMid.value = 0; if (gainMidText) gainMidText.textContent = '0 dB'; if (typeof Ecualizador !== 'undefined') Ecualizador.setMedios(0); }
      if (eqHigh) { eqHigh.value = 0; if (gainHighText) gainHighText.textContent = '0 dB'; if (typeof Ecualizador !== 'undefined') Ecualizador.setAgudos(0); }
    });
  }
});