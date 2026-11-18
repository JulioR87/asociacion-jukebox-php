# Bloque 15 · Consulta asíncrona y renderizado seguro de la tabla

## Qué cambia
- Se implementa la acción `action=list` en `docker/src/api/catalogo.php` devolviendo `{"ok": true, "data": [...]}` tras validar la sesión activa (`$_SESSION['usuario_id']`).
- Se crea la vista privada `docker/src/canciones-ajax.php` protegida por `lib/acceso.php` y se enlaza desde `home.php`.
- Se programa `docker/src/scripts/catalogo-ajax.js` con `fetch()` y `renderizarCanciones()` usando `document.createElement()` y `textContent`.

## Por qué
- Evita recargar la página completa para consultar el catálogo.
- Usar `textContent` en lugar de `innerHTML` impide la ejecución de código HTML/JavaScript malicioso (XSS) si un título contiene caracteres como `<` o `>`.
- Guardar el `id` real en `dataset.id` desacopla cada ficha de su posición visual en la tabla.

## Qué probar y qué resultado esperar
1. Acceder a `canciones-ajax.php` con sesión iniciada: en Red/Network aparece `api/catalogo.php?action=list` con estado `200` y se dibujan todas las canciones.
2. Consultar `api/catalogo.php?action=list` sin sesión (incógnito): responde HTTP `401` con JSON `{"ok": false, ...}`.
3. Renderizar una lista vacía `[]`: muestra la fila centrada `"Todavía no hay canciones"`.