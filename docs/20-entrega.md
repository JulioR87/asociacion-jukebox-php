# Bloque 20 · Entrega, arquitectura final y autoevaluación (RA2)

## Resumen de la ampliación RA2
- **Encargo:** Incorporar una interfaz de gestión asíncrona (`canciones-ajax.php`) apoyada en una API JSON (`api/catalogo.php`) que conviva con el reproductor (`home.php`) y el CRUD clásico (`canciones.php`) sobre el mismo almacén privado `/var/www/data/canciones.json`.
- **Estructura añadida:**
  - `docker/src/lib/api-json.php`: helpers estandarizados para cabeceras JSON y respuestas `responderExito()` / `responderError()`.
  - `docker/src/api/catalogo.php`: endpoint RESTful por acciones (`list`, `create`, `update`, `delete`) con control de sesión, CSRF, validación estricta y bloqueo `flock()`.
  - `docker/src/canciones-ajax.php` y `docker/src/scripts/catalogo-ajax.js`: interfaz SPA ligera con `fetch()`, `async/await` y renderizado DOM seguro (`textContent`).
- **Dificultad resuelta:** Al generar archivos PHP desde PowerShell con codificación UTF-8 por defecto se incluía marca BOM invisible, provocando errores `headers already sent` al enviar `Content-Type: application/json`. Se solucionó guardando todos los ficheros en UTF-8 sin BOM (`System.Text.UTF8Encoding($false)`) y validando con `php -l`.

## Autoevaluación
- **Qué funciona:** CRUD completo sin recarga de página, sincronización total con el CRUD clásico y el reproductor, protección frente a sesión caducada (`401`), CSRF (`403`), errores de contrato (`400`, `405`, `415`, `422`, `404`) y concurrencia mediante `flock()`.
- **Limitación que conserva el prototipo:** El almacenamiento sigue basándose en un único fichero JSON local y las categorías y audios disponibles se gestionan de forma estática desde el servidor sin subida directa de nuevos ficheros MP3 desde la interfaz.
- **Lo que puedo explicar sin leer código:** El ciclo completo de una petición `fetch()` desde el navegador hasta PHP, la diferencia entre autenticación por cookie de sesión y autorización por token CSRF, el contrato HTTP/JSON y por qué la interfaz solo actualiza el DOM tras recibir confirmación del servidor.