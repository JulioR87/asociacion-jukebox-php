# Bloque 2: Describir la primera canción

## 1. Descripción del encargo
Creación de una portada HTML estructurada con PHP incrustado y manipulación de datos de una canción mediante variables, tipos de datos, constantes y funciones de escape para salida HTML segura.

## 2. Archivos creados
- `src/02-portada.php`: Estructura HTML con `lang="es"`, `utf-8`, títulos y párrafos con `echo`, enlazando al saludo del bloque anterior.
- `src/03-cancion.php`: Definición de variables (`$title`, `$artist`, `$duration`, `$volume`, `$available`), constante `APP_NAME` y escape con `htmlspecialchars`.
- `docs/02-portada.md`: Documentación del bloque.

## 3. Tabla de tipos de datos explorados
| Variable / Valor | Tipo en PHP | Descripción |
| :--- | :--- | :--- |
| `$title` ('Sesión de prueba') | `string` | Cadena de texto ficticia |
| `$artist` ('Grupo del aula') | `string` | Cadena de texto |
| `$duration` (185) | `int` | Número entero (segundos) |
| `'185'` | `string` | Texto numérico (no operable directamente como entero) |
| `$volume` (0.5) | `float` | Número decimal |
| `$available` (true) | `bool` | Booleano verdadero/falso |
| `APP_NAME` | `const string` | Constante inmutable de la aplicación |

## 4. Pruebas y Comprobaciones

### Prueba 1: Ocultamiento del código PHP
- **Acción:** Acceder a `02-portada.php` y revisar "Ver código fuente".
- **Resultado observado:** El navegador solo recibe el código HTML resultante; las etiquetas `<?php echo ... ?>` se ejecutan en el servidor y nunca llegan al cliente.

### Prueba 2: Salida segura con `htmlspecialchars`
- **Acción:** Asignar `$title = '<b>Demo</b>'` y aplicar `htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
- **Resultado observado:** El navegador imprime literalmente `<b>Demo</b>` en lugar de interpretarlo como negrita, previniendo inyección de código. En el código fuente se observa transformado a `&lt;b&gt;Demo&lt;/b&gt;`.