# Bloque 14: Entender la nueva petición AJAX

## 1. Recorrido completo de una petición
1. **Acción del usuario (Navegador):** El usuario pulsa un botón (añadir, editar o eliminar) en `canciones-ajax.php`.
2. **Petición asíncrona (Navegador - JavaScript):** `catalogo-ajax.js` intercepta la acción y lanza una petición con `fetch()` hacia `api/catalogo.php` enviando los datos y el token CSRF sin recargar la página.
3. **Procesamiento y validación (Servidor - PHP en Docker):** `api/catalogo.php` verifica la sesión activa, valida los datos recibidos y llama a las funciones compartidas de `lib/`.
4. **Almacenamiento privado (Contenedor Docker):** PHP lee o actualiza `/var/www/data/canciones.json` (ubicado fuera del DocumentRoot público).
5. **Respuesta estructurada (Servidor → Navegador):** PHP devuelve exclusivamente JSON con el formato `{"ok": true, "data": [...]}` si todo fue bien, o `{"ok": false, "error": "Motivo"}` si falló.
6. **Actualización del DOM (Navegador):** JavaScript lee el JSON recibido y redibuja solo la tabla de canciones en pantalla.

## 2. Dos direcciones, dos responsabilidades
Ambas APIs leen el mismo archivo `/var/www/data/canciones.json` mediante las funciones compartidas de `lib/` (sin duplicar ficheros):

- **`api/canciones.php` (Lectura para el reproductor):**
  - **Qué espera recibir:** Petición `GET` desde `home.php`.
  - **Qué devuelve:** El array directo de canciones con campos como `id`, `title`, `artist`, `duration` y `src` (`songs[i].src`) para que Howler.js y el reproductor sigan funcionando sin cambios.

- **`api/catalogo.php` (Gestión CRUD para AJAX):**
  - **Qué espera recibir:** Peticiones `GET` (listar) y `POST` (crear, editar o eliminar canciones) con parámetros validados y token CSRF.
  - **Qué devuelve:** Un objeto JSON envoltorio con indicador booleano `ok`:
    - Éxito: `{"ok": true, "data": [ /* lista actualizada de canciones */ ]}`
    - Error: `{"ok": false, "error": "Mensaje descriptivo del fallo"}`

## 3. ¿Por qué la API y la página privada necesitan controles de acceso distintos?
- **En una página privada normal (`canciones-ajax.php`):** Si el usuario no tiene sesión, el servidor responde con una redirección HTTP (`302`/`303` hacia `login.php`) para que el navegador cargue el formulario de acceso HTML.
- **En una API (`api/catalogo.php`):** Si PHP devolviera una redirección o el HTML de `login.php`, JavaScript recibiría código HTML (`<!DOCTYPE html>...`) y al ejecutar `response.json()` lanzaría un error de sintaxis (`SyntaxError: Unexpected token '<'`), rompiendo la aplicación. Por eso la API debe responder siempre con código de estado HTTP adecuado (como `401 Unauthorized`) y una estructura JSON válida (`{"ok": false, "error": "Debes iniciar sesión."}`).

---

## 4. Contrato de Operaciones de `api/catalogo.php`
La acción viaja siempre en la URL (`?action=...`) y los datos de escritura viajan en el cuerpo en formato JSON (`Content-Type: application/json`):

- **`GET api/catalogo.php?action=list`**: Consulta el catálogo completo. No lleva cuerpo. Devuelve **`200 OK`** y `{"ok": true, "data": [...]}`[cite: 14, 15].
- **`POST api/catalogo.php?action=create`**: Crea una canción nueva[cite: 15]. Recibe `title`, `artist`, `category`, `duration` y `src`[cite: 15]. Devuelve **`201 Created`** y la lista actualizada[cite: 15].
- **`POST api/catalogo.php?action=update`**: Edita una canción existente[cite: 15]. Recibe `id` y los cinco campos[cite: 15]. Devuelve **`200 OK`** y la lista actualizada[cite: 15].
- **`POST api/catalogo.php?action=delete`**: Elimina una canción[cite: 15]. Recibe `id`[cite: 15]. Devuelve **`200 OK`** y la lista actualizada[cite: 15].

*(Ninguna petición `GET` podrá crear, editar ni eliminar fichas)[cite: 15].*

## 5. Catálogo de Errores HTTP y Mensajes Concretos
- **`400 Bad Request`**: JSON mal formado o acción desconocida ("La acción solicitada no es válida.")[cite: 15].
- **`401 Unauthorized`**: Falta sesión activa ("Debes iniciar sesión para realizar esta operación.")[cite: 14, 15].
- **`403 Forbidden`**: Token CSRF ausente o inválido ("Token de seguridad CSRF no válido.")[cite: 15].
- **`404 Not Found`**: La canción indicada no existe ("No se ha encontrado la canción solicitada.")[cite: 15].
- **`405 Method Not Allowed`**: Método HTTP incorrecto para esa acción (incluye cabecera `Allow`)[cite: 15].
- **`415 Unsupported Media Type`**: El cuerpo no llega con cabecera `application/json` ("La petición debe enviarse en formato JSON.")[cite: 15].
- **`422 Unprocessable Entity`**: Datos no válidos (ej: "La duración debe ser un entero entre 1 y 1800 segundos.")[cite: 15].
- **`500 Internal Server Error`**: Error interno al leer/guardar el fichero JSON ("Error interno al acceder al catálogo.")[cite: 15].

## 6. Casos de Comprobación (Para el Informe)
1. **Consultar sin sesión (`GET ?action=list` sin cookie):** Devuelve código **`401`** con `{"ok": false, "error": "Debes iniciar sesión para realizar esta operación."}`[cite: 14, 15].
2. **Editar un ID inexistente (`POST ?action=update` con `id: 999`):** Devuelve código **`404`** con `{"ok": false, "error": "No se ha encontrado la canción solicitada."}`[cite: 15].
3. **Enviar una duración negativa (`POST ?action=create` con `duration: -10`):** Devuelve código **`422`** con `{"ok": false, "error": "La duración debe ser un entero entre 1 y 1800 segundos."}`[cite: 15].
