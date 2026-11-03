// ==========================================================================
// MODULO ECUALIZADOR PARAMETRICO (Web Audio API)
// ==========================================================================

const Ecualizador = (() => {
  let lowFilter = null;
  let midFilter = null;
  let highFilter = null;
  let inicializado = false;

  // Funcion para conectar los filtros a la salida de Howler
  function inicializar() {
    if (inicializado || !Howler.ctx) return;

    const ctx = Howler.ctx;

    // 1. Crear filtro de Bajos (Low Shelf a 100 Hz)
    lowFilter = ctx.createBiquadFilter();
    lowFilter.type = 'lowshelf';
    lowFilter.frequency.value = 100;
    lowFilter.gain.value = 0;

    // 2. Crear filtro de Medios (Peaking a 1000 Hz)
    midFilter = ctx.createBiquadFilter();
    midFilter.type = 'peaking';
    midFilter.frequency.value = 1000;
    midFilter.Q.value = 1;
    midFilter.gain.value = 0;

    // 3. Crear filtro de Agudos (High Shelf a 8000 Hz)
    highFilter = ctx.createBiquadFilter();
    highFilter.type = 'highshelf';
    highFilter.frequency.value = 8000;
    highFilter.gain.value = 0;

    // 4. Encadenar los nodos de audio
    // Howler.masterGain -> lowFilter -> midFilter -> highFilter -> destino
    Howler.masterGain.disconnect();
    Howler.masterGain.connect(lowFilter);
    lowFilter.connect(midFilter);
    midFilter.connect(highFilter);
    highFilter.connect(ctx.destination);

    inicializado = true;
  }

  // Metodos publicos para ajustar la ganancia de cada banda
  function setBajos(valorDb) {
    if (lowFilter) lowFilter.gain.value = valorDb;
  }

  function setMedios(valorDb) {
    if (midFilter) midFilter.gain.value = valorDb;
  }

  function setAgudos(valorDb) {
    if (highFilter) highFilter.gain.value = valorDb;
  }

  return {
    inicializar,
    setBajos,
    setMedios,
    setAgudos
  };
})();