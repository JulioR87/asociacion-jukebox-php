# Bloque 17 · Edición de la canción elegida manteniendo su identidad

## Qué cambia
- Se activa el botón **Editar** en cada fila de la tabla, cargando los datos de la canción buscada por su `id` numérico en `cancionesActuales`.
- Se añade el botón **Cancelar** (oculto por defecto) y un campo oculto `#cancion-id` para distinguir el modo alta (`create`) del modo edición (`update`).
- En `api/catalogo.php` se programa `action=update`, que localiza la ficha por `id` bajo bloqueo exclusivo (`LOCK_EX`), actualiza sus campos conservando su `id` original y responde `404` si la ficha ya no existe.

## Por qué
- Permite modificar cualquier canción sin duplicar registros ni alterar su identificador único.
- Comprobar la existencia dentro del bloqueo evita recrear accidentalmente una ficha que otra pestaña haya eliminado instantes antes.

## Qué probar y qué resultado esperar
1. Editar título o duración de una canción y guardar: responde HTTP `200`, mantiene el mismo `id` y conserva el total de filas.
2. Pulsar **Cancelar** durante una edición: limpia el formulario y vuelve al modo alta sin lanzar peticiones de red.
3. Borrar una canción desde otra pestaña mientras está abierta en edición e intentar guardar: responde HTTP `404` sin volver a crearla.