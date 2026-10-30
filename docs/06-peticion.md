# Bloque 6: Recibir una petición del navegador

## 1. Descripción del encargo
Recepción de parámetros por la URL mediante la superglobal `$_GET` para el filtrado de canciones, validación estricta de tipos de datos (`is_string`) y listas blancas (`in_array`), y control de respuestas HTTP con códigos de estado (`200` y `400`).

## 2. Archivos creados
- `src/09-filtro.php`: Script con la lógica de recepción, validación, filtrado y renderizado condicional.
- `docs/06-peticion.md`: Documentación del bloque.

## 3. Puntos clave de la implementación
- **Dónde se lee:** En la línea `$_GET['category'] ?? 'todas'`, extrayendo el valor de la superglobal o asignando `'todas'` si no existe el parámetro.
- **Dónde se valida:** En la condición `if (!is_string($category) || !in_array($category, $allowed, true))`. Se verifica primero el tipo primitivo y luego su pertenencia a la lista permitida.
- **Dónde se usa:** En el bucle de filtrado `$song['category'] === $category` para seleccionar solo los registros coincidentes antes de generar el HTML.

## 4. Tabla de pruebas y casos analizados
| Caso probado | Comando / URL | Código HTTP Esperado | Código HTTP Observado | Comportamiento |
| :--- | :--- | :---: | :---: | :--- |
| **Sin parámetros** | `/09-filtro.php` | `200 OK` | `200 OK` | Muestra la totalidad de canciones del catálogo. |
| **Categoría admitida** | `/09-filtro.php?category=rock` | `200 OK` | `200 OK` | Filtra y muestra únicamente canciones con categoría rock. |
| **Categoría desconocida** | `/09-filtro.php?category=desconocida` | `400 Bad Request` | `400 Bad Request` | Retorna mensaje *"Categoría no válida"* sin generar HTML. |
| **Inyección de array** | `/09-filtro.php?category[]=rock` | `400 Bad Request` | `400 Bad Request` | `is_string()` rechaza el array, retornando estado 400 sin warnings ni fallos de ejecución. |