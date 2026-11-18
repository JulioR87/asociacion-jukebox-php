// docker/src/scripts/catalogo-ajax.js
// Bloque 19: Arquitectura modular, envío común validado y accesibilidad por teclado

const estadoMensaje = document.getElementById('estado-mensaje');
const tbodyCanciones = document.getElementById('tabla-canciones-body');
const formCancion = document.getElementById('form-cancion');
const inputCancionId = document.getElementById('cancion-id');
const tituloFormulario = document.getElementById('titulo-formulario');
const btnGuardar = document.getElementById('btn-guardar');
const btnCancelar = document.getElementById('btn-cancelar');

let cancionesActuales = [];
let operacionEnCurso = false;

/**
 * Lee el token CSRF publicado por PHP en la cabecera <meta>.
 * Se envía en cada POST para demostrar que la petición nace de esta vista.
 */
function obtenerTokenCsrf() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : '';
}

/**
 * Actualiza el área de estado visible (role="status") con estilos diferenciados.
 * Los errores permanecen fijos hasta que el usuario inicia una nueva acción.
 */
function mostrarMensaje(texto, tipo = 'info') {
    if (!estadoMensaje) return;
    estadoMensaje.textContent = texto;
    estadoMensaje.className = '';
    if (tipo === 'error') estadoMensaje.classList.add('error');
    if (tipo === 'ok') estadoMensaje.classList.add('ok');
}

/**
 * Bloquea o desbloquea los controles de escritura mientras hay una petición en curso,
 * evitando envíos duplicados por doble clic o pulsaciones repetidas de Enter.
 */
function alternarEstadoControles(deshabilitar) {
    if (btnGuardar) btnGuardar.disabled = deshabilitar;
    if (btnCancelar) btnCancelar.disabled = deshabilitar;
    const botonesTabla = document.querySelectorAll('#tabla-canciones-body button[data-accion]');
    botonesTabla.forEach((btn) => {
        btn.disabled = deshabilitar;
    });
}

/**
 * Función común de red: centraliza fetch, cabeceras CSRF/JSON, lectura única del cuerpo
 * y verificación estricta del contrato { ok: true, data: [...] }.
 */
async function enviarPeticionApi(url, opciones = {}) {
    const esPost = (opciones.method || 'GET').toUpperCase() === 'POST';
    const cabeceras = {
        'Accept': 'application/json',
        ...(opciones.headers || {})
    };

    if (esPost) {
        cabeceras['Content-Type'] = 'application/json';
        cabeceras['X-CSRF-Token'] = obtenerTokenCsrf();
    }

    const respuesta = await fetch(url, {
        ...opciones,
        headers: cabeceras
    });

    let contenido;
    try {
        contenido = await respuesta.json();
    } catch (errorParseo) {
        throw new Error('La respuesta del servidor no tiene formato JSON válido.');
    }

    if (!respuesta.ok || contenido.ok !== true) {
        throw new Error(contenido.error || `Error inesperado del servidor (HTTP ${respuesta.status}).`);
    }

    if (!Array.isArray(contenido.data)) {
        throw new Error('El servidor no devolvió una lista válida de canciones.');
    }

    return contenido.data;
}

/**
 * Restaura el formulario al estado inicial de alta y devuelve el foco al primer campo
 * para permitir seguir operando con teclado sin perder orientación en la pantalla.
 */
function salirModoEdicion(enfocarInicio = false) {
    if (formCancion) formCancion.reset();
    if (inputCancionId) inputCancionId.value = '';
    if (tituloFormulario) {
        tituloFormulario.innerHTML = '<i class="fa-solid fa-plus-circle"></i> Añadir nueva canción';
    }
    if (btnGuardar) {
        btnGuardar.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar canción';
    }
    if (btnCancelar) {
        btnCancelar.hidden = true;
    }
    if (enfocarInicio && formCancion && formCancion.elements.title) {
        formCancion.elements.title.focus();
    }
}

/**
 * Carga los datos de una canción identificada por su ID único en el formulario
 * y desplaza el foco al primer campo editable.
 */
function iniciarEdicion(id) {
    if (!Number.isInteger(id) || id <= 0) return;

    const elegida = cancionesActuales.find((cancion) => Number(cancion.id) === id);
    if (!elegida) {
        mostrarMensaje('No se encontró la canción seleccionada en la lista local.', 'error');
        return;
    }

    inputCancionId.value = String(elegida.id);
    formCancion.elements.title.value = elegida.title ?? '';
    formCancion.elements.artist.value = elegida.artist ?? '';
    formCancion.elements.category.value = elegida.category ?? '';
    formCancion.elements.duration.value = elegida.duration ?? '';
    formCancion.elements.src.value = elegida.src ?? '';

    tituloFormulario.textContent = `Editando: ${elegida.title}`;
    btnGuardar.innerHTML = '<i class="fa-solid fa-check"></i> Guardar cambios';
    btnCancelar.hidden = false;

    mostrarMensaje(`Editando "${elegida.title}" (ID #${elegida.id}).`, 'info');
    formCancion.elements.title.focus();
}

/**
 * Construye las filas de la tabla usando nodos DOM y textContent para impedir
 * la inyección de HTML/XSS y asociar cada botón al ID real de la ficha.
 */
function renderizarCanciones(canciones) {
    if (!tbodyCanciones) return;

    tbodyCanciones.replaceChildren();

    if (!Array.isArray(canciones) || canciones.length === 0) {
        const filaVacia = document.createElement('tr');
        const celdaVacia = document.createElement('td');
        celdaVacia.colSpan = 7;
        celdaVacia.textContent = 'Todavía no hay canciones';
        celdaVacia.style.textAlign = 'center';
        celdaVacia.style.color = '#9ca3af';
        filaVacia.append(celdaVacia);
        tbodyCanciones.append(filaVacia);
        return;
    }

    for (const cancion of canciones) {
        const fila = document.createElement('tr');

        const tdId = document.createElement('td');
        tdId.textContent = String(cancion.id ?? '');

        const tdTitulo = document.createElement('td');
        tdTitulo.textContent = String(cancion.title ?? '');

        const tdArtista = document.createElement('td');
        tdArtista.textContent = String(cancion.artist ?? '');

        const tdCategoria = document.createElement('td');
        tdCategoria.textContent = String(cancion.category ?? '');

        const tdDuracion = document.createElement('td');
        tdDuracion.textContent = `${cancion.duration ?? 0} s`;

        const tdArchivo = document.createElement('td');
        tdArchivo.textContent = String(cancion.src ?? '');

        const tdAcciones = document.createElement('td');

        const btnEditar = document.createElement('button');
        btnEditar.type = 'button';
        btnEditar.textContent = 'Editar';
        btnEditar.dataset.id = String(cancion.id ?? '');
        btnEditar.dataset.accion = 'editar';
        btnEditar.setAttribute('aria-label', `Editar ${cancion.title}`);
        btnEditar.style.marginRight = '0.5rem';
        btnEditar.style.cursor = 'pointer';
        btnEditar.addEventListener('click', () => {
            if (!operacionEnCurso) iniciarEdicion(Number(btnEditar.dataset.id));
        });

        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.dataset.id = String(cancion.id ?? '');
        btnEliminar.dataset.accion = 'eliminar';
        btnEliminar.setAttribute('aria-label', `Eliminar ${cancion.title}`);
        btnEliminar.style.cursor = 'pointer';
        btnEliminar.addEventListener('click', () => {
            if (!operacionEnCurso) eliminarCancion(Number(btnEliminar.dataset.id));
        });

        tdAcciones.append(btnEditar, btnEliminar);

        fila.append(
            tdId,
            tdTitulo,
            tdArtista,
            tdCategoria,
            tdDuracion,
            tdArchivo,
            tdAcciones
        );

        tbodyCanciones.append(fila);
    }
}

/**
 * Consulta inicial del catálogo completo mediante GET.
 */
async function cargarCatalogo() {
    mostrarMensaje('Cargando catálogo...', 'info');
    operacionEnCurso = true;
    alternarEstadoControles(true);

    try {
        cancionesActuales = await enviarPeticionApi('api/catalogo.php?action=list');
        renderizarCanciones(cancionesActuales);
        mostrarMensaje(`Catálogo cargado (${cancionesActuales.length} canciones).`, 'ok');
    } catch (error) {
        console.error('Error al cargar catálogo:', error);
        mostrarMensaje(error.message || 'No se pudo cargar el catálogo.', 'error');
    } finally {
        // En finally solo liberamos controles; nunca mostramos mensajes de éxito aquí
        operacionEnCurso = false;
        alternarEstadoControles(false);
    }
}

/**
 * Envía una nueva ficha al servidor (action=create) y actualiza la tabla tras confirmar 201.
 */
async function crearCancion(datosNueva) {
    cancionesActuales = await enviarPeticionApi('api/catalogo.php?action=create', {
        method: 'POST',
        body: JSON.stringify(datosNueva)
    });
    renderizarCanciones(cancionesActuales);
    salirModoEdicion(true);
    mostrarMensaje('Canción añadida correctamente.', 'ok');
}

/**
 * Envía los cambios de una ficha existente (action=update) manteniendo su ID original.
 */
async function actualizarCancion(datosEditados) {
    cancionesActuales = await enviarPeticionApi('api/catalogo.php?action=update', {
        method: 'POST',
        body: JSON.stringify(datosEditados)
    });
    renderizarCanciones(cancionesActuales);
    salirModoEdicion(true);
    mostrarMensaje('Cambios guardados.', 'ok');
}

/**
 * Solicita confirmación explícita y elimina la ficha por su ID único (action=delete).
 */
async function eliminarCancion(id) {
    if (operacionEnCurso || !Number.isInteger(id) || id <= 0) return;

    const elegida = cancionesActuales.find((cancion) => Number(cancion.id) === id);
    if (!elegida) {
        mostrarMensaje('No se encontró la canción seleccionada.', 'error');
        return;
    }

    if (!window.confirm(`¿Seguro que quieres eliminar "${elegida.title}" (ID #${elegida.id})?`)) {
        return;
    }

    operacionEnCurso = true;
    alternarEstadoControles(true);
    mostrarMensaje(`Eliminando "${elegida.title}"...`, 'info');

    try {
        cancionesActuales = await enviarPeticionApi('api/catalogo.php?action=delete', {
            method: 'POST',
            body: JSON.stringify({ id })
        });

        if (Number(inputCancionId.value) === id) {
            salirModoEdicion(false);
        }

        renderizarCanciones(cancionesActuales);
        mostrarMensaje(`Canción "${elegida.title}" eliminada.`, 'ok');
    } catch (error) {
        console.error('Error al eliminar canción:', error);
        if (error instanceof TypeError) {
            mostrarMensaje('No se pudo confirmar la eliminación por un fallo de red. Recarga la lista antes de reintentar.', 'error');
        } else {
            mostrarMensaje(error.message || 'Error al eliminar la canción.', 'error');
        }
    } finally {
        operacionEnCurso = false;
        alternarEstadoControles(false);
    }
}

/**
 * Gestiona el envío del formulario derivando a creación o edición según exista ID activo.
 */
async function manejarEnvioFormulario(evento) {
    evento.preventDefault();
    if (operacionEnCurso) return;

    if (formCancion && !formCancion.reportValidity()) {
        return;
    }

    const idActual = Number(inputCancionId.value);
    const esEdicion = Number.isInteger(idActual) && idActual > 0;

    const payload = {
        title: formCancion.elements.title.value.trim(),
        artist: formCancion.elements.artist.value.trim(),
        category: formCancion.elements.category.value,
        duration: Number(formCancion.elements.duration.value),
        src: formCancion.elements.src.value
    };

    operacionEnCurso = true;
    alternarEstadoControles(true);
    mostrarMensaje('Guardando...', 'info');

    try {
        if (esEdicion) {
            await actualizarCancion({ id: idActual, ...payload });
        } else {
            await crearCancion(payload);
        }
    } catch (error) {
        console.error('Error al guardar:', error);
        if (error instanceof TypeError) {
            mostrarMensaje('No se pudo confirmar si el servidor guardó los datos por un fallo de red. Consulta la lista antes de reintentar.', 'error');
        } else {
            mostrarMensaje(error.message || 'Error al guardar los datos.', 'error');
        }
    } finally {
        operacionEnCurso = false;
        alternarEstadoControles(false);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarCatalogo();

    if (formCancion) {
        formCancion.addEventListener('submit', manejarEnvioFormulario);
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', () => {
            if (operacionEnCurso) return;
            salirModoEdicion(true);
            mostrarMensaje('Edición cancelada.', 'info');
        });
    }
});