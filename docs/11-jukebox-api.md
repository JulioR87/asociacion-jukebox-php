# Bloque 11: Conectar el jukebox a la home

## 1. Descripción del encargo
Integración del reproductor web y ecualizador sobre Canvas en `home.php`. Creación de un endpoint RESTful seguro en `api/canciones.php` que valida la sesión activa y entrega una lista JSON plana con las canciones y sus rutas de audio.

## 2. Archivos creados y adaptados
- `docker/src/api/canciones.php`: Endpoint con verificación de `usuario_id`; devuelve HTTP 401 estructurado en JSON si no hay sesión, o HTTP 200 con la lista plana `[...]`.
- `docker/src/scripts/app.js`: Script cliente adaptado para solicitar canciones a `api/canciones.php` mediante `fetch` asíncrono y mapear los campos hacia Howler.
- `docker/src/scripts/equalizer.js`: Script del ecualizador vinculado al elemento Canvas.
- `docker/src/home.php`: Interfaz protegida con `lib/acceso.php`, carga de dependencias en orden con `defer` y botón de cierre de sesión por POST con token CSRF.
- `docs/11-jukebox-api.md`: Documentación técnica del bloque.

## 3. Justificación de diseño de la API
- **¿Por qué la API no redirige al login con código 303?**  
  Porque las llamadas realizadas mediante `fetch()` en JavaScript esperan una respuesta estructurada en formato JSON. Si el servidor respondiera con una redirección HTTP 303 hacia `login.php`, el navegador descargaría el código HTML del formulario y el método `response.json()` lanzaría un error crítico de sintaxis (`SyntaxError: Unexpected token '<'`). Devolver un código `401 Unauthorized` con un JSON de error permite a la aplicación cliente capturar el fallo de forma controlada.
- **Protección de datos sensibles:**  
  La API entrega únicamente el catálogo musical (`id`, `title`, `artist`, `category`, `duration`, `src`). En ningún momento expone contraseñas, hashes de usuarios, tokens de sesión ni rutas físicas del sistema de archivos.

## 4. Tabla de pruebas de la API
| Petición | Estado de autenticación | Código HTTP | Content-Type | Formato de respuesta |
| :--- | :--- | :---: | :---: | :--- |
| `GET /api/canciones.php` | Sin sesión (anónimo) | `401 Unauthorized` | `application/json` | Objeto JSON con mensaje de error controlado. |
| `GET /api/canciones.php` | Con sesión activa | `200 OK` | `application/json` | Lista JSON plana `[...]` con las canciones. | 