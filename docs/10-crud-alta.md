# Bloque 10: Consultar y añadir canciones

## 1. Descripción del encargo
Implementación de las operaciones Read y Create sobre el catálogo de canciones persistido en `/var/www/data/canciones.json`. Control estricto de acceso en servidor mediante `acceso.php`, validación de campos bajo listas blancas y tipado en `cancion-nueva.php`, cálculo de identificadores con `$maxId + 1` y concurrencia gestionada con cerrojos `LOCK_EX`.

## 2. Archivos creados y modificados
- `docker/src/canciones.php`: Vista de consulta protegida (Read) con enlaces de administración y formulario de borrado preparado.
- `docker/src/cancion-nueva.php`: Formulario de alta (Create) con validaciones completas, límite de 80 caracteres y patrón Post/Redirect/Get (HTTP 303).
- `docker/src/12-catalogo-json.php`: Protegido con `acceso.php` para restringir el acceso directo al JSON.
- `docker/src/home.php`: Enlace de navegación actualizado al catálogo administrativo.
- `docs/10-crud-alta.md`: Documentación técnica del bloque.

## 3. Justificación del cálculo del identificador
Se descartó el uso de `count($songs) + 1` porque si en el futuro se eliminan registros intermedios (por ejemplo, eliminando el id 2 de un total de 4 canciones), un recuento devolvería 3 + 1 = 4, produciendo una colisión de claves primarias. La búsqueda del identificador más alto (`$maxId + 1`) garantiza unicidad monótona sin importar eliminaciones previas.

## 4. Tabla de pruebas realizadas
| Caso de prueba | Entrada suministrada | Código HTTP | Resultado observado |
| :--- | :--- | :---: | :--- |
| **Acceso sin sesión** | GET a `/canciones.php` sin cookie | `303 See Other` | Redirección inmediata a `login.php`. |
| **Consulta del catálogo** | Sesión activa en `/canciones.php` | `200 OK` | Muestra listado de 4 canciones iniciales. |
| **Alta válida** | Datos correctos (duración 230 s) | `303 See Other` | Redirige a `canciones.php`; se crea registro con ID #5 en disco. |
| **Recarga tras alta** | `F5` sobre `canciones.php?creada=1` | `200 OK` | Petición GET limpia; no duplica el registro en `canciones.json`. |
| **Rechazo por validación** | Duración 2500 s | `400 Bad Request` | Muestra error en formulario; el JSON permanece inalterado. |
| **Rechazo por CSRF** | Token ausente o modificado | `403 Forbidden` | Deniega el guardado; no altera la colección. |

## 5. Edición y Eliminación por Identificador (Pasos 3 y 4)

### Implementación realizada
- `cancion-editar.php`: Validación estricta de ID por GET, carga de valores actuales y modificación atómica de campos conservando el ID inalterado.
- `cancion-eliminar.php`: Confirmación visual previa por GET y ejecución del borrado exclusivamente por POST bajo token CSRF.
- Uso de `array_values()` tras `unset()`: Evita huecos en los índices del array que provocarían que `json_encode()` serializara un objeto asociativo `{}` en lugar de una lista indexada `[]`.
- Conservación de activos estáticos: La operación de borrado solo afecta a los metadatos en el JSON, sin invocar `unlink()` sobre los ficheros de audio en `src/audio/`.

### Explicación técnica: ¿Por qué ID y posición no son lo mismo?
- **Posición (Índice del array):** Representa el índice numérico secuencial en memoria (0, 1, 2...) que ocupa un elemento en ese momento exacto. Si se elimina el elemento en el índice 1, el elemento que estaba en el índice 2 pasa a ocupar el índice 1.
- **ID (Identificador único):** Clave primaria estable del registro que no varía a lo largo de su ciclo de vida, independientemente de que se reordenen, borren o filtren otros elementos. Buscar por posición (`$songs[$id]`) causaría la edición o eliminación de una canción equivocada.

### Evolución de IDs tras las pruebas
| Acción realizada | Canción afectada | ID | Posición en array tras la acción | Estado en `canciones.json` |
| :--- | :--- | :---: | :---: | :--- |
| **Estado inicial** | Pistas 1, 2, 3, 4 | 1, 2, 3, 4 | Índices 0 a 3 | Lista continua |
| **Borrado** | Riff Callejero | 2 | Eliminado | Reindexado con `array_values` (pistas 1, 3, 4) |
| **Edición** | Sinfonía del Barrio | 3 | Conserva índice | Título modificado a "Remix" |
| **Nueva alta** | Pista posterior | Siguiente $maxId + 1 | Último índice | ID asignado monótonamente sin colisión |