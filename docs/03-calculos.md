# Bloque 3: Calcular y decidir sobre una canción

## 1. Descripción del encargo
Cálculo aritmético de duraciones (minutos y segundos) usando división entera y módulo, y aplicación de estructuras de control condicionales (`if`, `elseif`, `else`) para clasificar canciones según su longitud.

## 2. Archivos creados
- `src/04-duracion.php`: Conversión de segundos a minutos y segundos con `intdiv()` y `%`.
- `src/05-estado-cancion.php`: Clasificación por tramos de duración y comparación estricta.
- `docs/03-calculos.md`: Documentación del bloque.

## 3. Pruebas de duración (minutos y segundos)
| Duración ($duration) | intdiv($duration, 60) | $duration % 60 | Resultado final |
| :--- | :--- | :--- | :--- |
| **0 s** | 0 | 0 | 0 minutos y 0 segundos |
| **59 s** | 0 | 59 | 0 minutos y 59 segundos |
| **60 s** | 1 | 0 | 1 minutos y 0 segundos |
| **185 s** | 3 | 5 | 3 minutos y 5 segundos |

## 4. Pruebas de límites de estado
| Valor probado | Rama condicional | Estado asignado |
| :--- | :--- | :--- |
| **0** | `if ($duration === 0)` | Sin duración |
| **1** | `elseif ($duration >= 1 && $duration <= 240)` | Hasta cuatro minutos |
| **239** | `elseif ($duration >= 1 && $duration <= 240)` | Hasta cuatro minutos |
| **240** | `elseif ($duration >= 1 && $duration <= 240)` | Hasta cuatro minutos (límite) |
| **241** | `else` | Más de cuatro minutos |

## 5. Conceptos clave requeridos
- **Diferencia entre `=` y `===`:**
  - `=`: Es un operador de **asignación**. Guarda un dato dentro de una variable (ej. `$duration = 185`).
  - `===`: Es un operador de **comparación estricta**. Comprueba si dos elementos tienen el mismo valor y además el mismo tipo de dato. Por eso, `0 === '0'` da `false`, ya que el primero es de tipo `int` y el segundo es `string`.