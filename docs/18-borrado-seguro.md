# Bloque 18 · Eliminación controlada sin renumerar identificadores

## Qué cambia
- Se activa el botón **Eliminar** en cada fila solicitando confirmación explícita (`window.confirm`) con el título de la canción antes de enviar nada.
- Se programa `action=delete` en `api/catalogo.php`, que busca la ficha por `id` bajo `LOCK_EX`, la elimina con `unset()` y reordena las posiciones del array JSON con `array_values()` sin renumerar los `id` restantes ni usar `unlink()` sobre el archivo MP3.
- Si se elimina la misma canción que estaba cargada en el formulario de edición, la interfaz abandona automáticamente el modo edición.

## Por qué
- El identificador (`id`) es permanente y no depende de la posición en la tabla; mantener los `id` restantes evita confundir fichas en operaciones posteriores.
- El catálogo gestiona metadatos, mientras que los archivos de audio autorizados permanecen disponibles en `docker/src/audio/`.

## Qué probar y qué resultado esperar
1. Pulsar **Eliminar** y cancelar el diálogo: no se envía ninguna petición ni cambia el catálogo.
2. Eliminar una canción intermedia: desaparece de la tabla y las canciones posteriores conservan su `id` original intacto.
3. Intentar eliminar un `id` inexistente: responde HTTP `404`.