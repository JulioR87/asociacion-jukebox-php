# Bloque 16 · Alta segura con CSRF y validación JSON en php://input

## Qué cambia
- Se añade el formulario de alta en `canciones-ajax.php` con validación nativa (`required`, `maxlength="80"`, `min="1"`, `max="1800"`, `step="1"`) y se publica el token en `<meta name="csrf-token">`.
- En `api/catalogo.php` se exige método `POST`, cabecera `X-CSRF-Token` verificada con `hash_equals()`, `Content-Type: application/json` y lectura estricta de `php://input` con `JSON_THROW_ON_ERROR`.
- En `catalogo-ajax.js` se captura `submit` con `preventDefault()`, se bloquean los controles durante la petición (`operacionEnCurso`) y solo se vacía el formulario tras recibir `201 Created`.

## Por qué
- La cookie de sesión identifica al usuario, pero el token CSRF garantiza que la petición nace legítimamente de nuestra interfaz y no de una web externa.
- Validar también en PHP es imprescindible porque un cliente puede saltarse las restricciones HTML/JS enviando peticiones directas.

## Qué probar y qué resultado esperar
1. Enviar `POST` sin cabecera `X-CSRF-Token`: responde HTTP `403` sin modificar `canciones.json`.
2. Enviar `Content-Type: text/plain`: responde HTTP `415`.
3. Enviar JSON mal formado: responde HTTP `400`.
4. Enviar duración negativa o categoría no admitida: responde HTTP `422`.
5. Enviar una canción válida desde el formulario: responde HTTP `201`, añade la fila sin recargar y limpia el formulario.