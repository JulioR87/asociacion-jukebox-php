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
## 5. Formulario de canción por POST (Pasos 3 y 4)

### Implementación realizada
- Archivo `src/10-formulario.php`: Recepción de datos vía `$_POST` con validación individual y vista previa sin alteración del catálogo en disco.
- Validación con `FILTER_VALIDATE_INT` para evitar conversiones implícitas erróneas y comprobación de rango (1 a 1800 segundos).
- Persistencia de campos en el formulario tras errores para mejorar la experiencia de usuario, escapando siempre los valores con `htmlspecialchars`.
- Selección cerrada de rutas de audio (`allowed_audios`) para impedir rutas arbitrarias.

### Casos de prueba del formulario
| Campo probado | Valor introducido | Resultado esperado | Resultado observado |
| :--- | :--- | :--- | :--- |
| **Título** | Solo espacios (`   `) | Rechazo por `trim()` | Error: *"El título es obligatorio"* |
| **Título con HTML**| `<b>Demo</b>` | Escape visual seguro | Muestra `<b>Demo</b>` en texto plano |
| **Duración** | `abc` | Rechazo por `FILTER_VALIDATE_INT` | Error: *"Debe ser un número entero válido"* |
| **Duración límite inferior** | `0` | Fuera de rango (< 1) | Error: *"Entre 1 y 1800 segundos"* |
| **Duración límite superior** | `1801` | Fuera de rango (> 1800) | Error: *"Entre 1 y 1800 segundos"* |
| **Ficha válida** | Datos correctos | HTTP 200 y recuadro verde | Muestra *"Vista previa, canción no guardada"* |