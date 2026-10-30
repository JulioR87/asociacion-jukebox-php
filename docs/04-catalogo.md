# Bloque 4: Construir el catálogo musical

## 1. Descripción del encargo
Paso de la gestión de una única canción a colecciones de canciones mediante estructuras iterativas (`for`, `while`, `foreach`) y arrays multidimensionales asociativos con claves estandarizadas (`id`, `title`, `artist`, `category`, `duration`, `src`).

## 2. Archivos creados
- `src/06-listas.php`: Implementación de bucles `for`, `while` con límite en 4 y recorrido de array indexado con `foreach`.
- `src/07-catalogo.php`: Generación dinámica de fichas a partir de un array asociativo con control de catálogo vacío mediante `empty()`.
- `docs/04-catalogo.md`: Documentación técnica del bloque.

## 3. Traza del contador y condiciones (06-listas.php con límite 4)
| Iteración | Valor de $i (Inicio) | Condición ($i <= 4) | Salida producida | Valor final ($i++) |
| :---: | :---: | :---: | :---: | :---: |
| 1 | 1 | `true` | Posición: 1 | 2 |
| 2 | 2 | `true` | Posición: 2 | 3 |
| 3 | 3 | `true` | Posición: 3 | 4 |
| 4 | 4 | `true` | Posición: 4 | 5 |
| Fin | 5 | `false` (5 <= 4 es falso) | Se detiene el bucle | - |

## 4. Pruebas y Comprobaciones

### Prueba 1: Catálogo con 4 registros
- **URL:** `http://localhost:8090/07-catalogo.php`
- **Resultado observado:** Se muestran 4 tarjetas diferenciadas con sus títulos, artistas y duraciones correspondientes. Cada ficha corresponde a un elemento del array recorrido en cada vuelta del `foreach`.

### Prueba 2: Catálogo vacío (Caso límite)
- **Acción:** Definir temporalmente `$songs = [];`.
- **Resultado observado:** La estructura condicional detecta la ausencia de registros e imprime en pantalla: *"Todavía no hay canciones"*, evitando errores de renderizado.