# Bloque 8: Leer el catálogo desde JSON

## 1. Descripción del encargo
Migración de la capa de datos en memoria PHP a persistencia en formato JSON ubicado fuera de la raíz web (`/var/www/data/canciones.json`). Lectura segura con `file_get_contents` y parseo con control de excepciones mediante `json_decode(..., true, 512, JSON_THROW_ON_ERROR)`.

## 2. Archivos creados
- `docker/data/canciones.json`: Almacén de canciones con formato JSON estricto (4 registros iniciales).
- `docker/src/lib/almacen-json.php`: Función desacoplada `leerCatalogoCanciones()` con captura de fallos de lectura y sintaxis.
- `docker/src/12-catalogo-json.php`: Renderizado del catálogo y captura controlada de excepciones (`JsonException`, `RuntimeException`).
- `docs/08-json.md`: Documentación técnica del bloque.

## 3. Relación de transformación de datos
- **Archivo JSON (Texto plano serializado):** Estructura en disco compuesta por tipos primitivos compatibles entre lenguajes (cadenas, números, booleanos, listas y objetos) con codificación UTF-8.
- **Array PHP (Estructura en memoria):** Obtenido al invocar `json_decode($text, true)`. Convierte las listas y objetos en arrays asociativos manipulables por sentencias iterativas (`foreach`).
- **HTML resultante (Vista en cliente):** Generación de elementos del DOM (`<article>`, `<h2>`, `<p>`) tras procesar los datos y aplicarles filtrado de seguridad con `htmlspecialchars()`.

## 4. Pruebas y Comprobaciones
| Caso probado | Condición en disco | Código HTTP | Resultado en navegador |
| :--- | :--- | :---: | :--- |
| **Lectura válida** | 4 registros en `canciones.json` | `200 OK` | Renderiza 4 fichas completas. |
| **Lista vacía** | Archivo con `[]` | `200 OK` | Mensaje: *"Todavía no hay canciones"*. |
| **JSON mal formado** | Coma final o sintaxis inválida | `500 Internal Server Error` | Mensaje controlado de error sintáctico sin romper la ejecución. |
| **Descarga directa** | Petición a `/data/canciones.json` | `404 Not Found` | Archivo protegido fuera del DocumentRoot de Apache. |