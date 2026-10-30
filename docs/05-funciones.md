# Bloque 5: Separar funciones y datos

## 1. Descripción del encargo
Modularización de la aplicación separando responsabilidades: definición de funciones reutilizables con tipado estricto en parámetros y retorno, desacoplamiento de la estructura de datos del catálogo inicial en archivos dedicados, y gestión de dependencias mediante `require_once` y `require`.

## 2. Archivos creados y modificados
- `src/lib/canciones.php`: Biblioteca común con la función `duracionLegible(int $total): string`.
- `src/lib/catalogo-inicial.php`: Archivo de datos que retorna la colección de canciones mediante `return [...]`.
- `src/08-funciones.php`: Script de prueba que incluye la librería y ejecuta llamadas a la función.
- `src/07-catalogo.php`: Actualizado para importar funciones y datos usando `__DIR__`, aplicando `duracionLegible()` a cada ficha.
- `docs/05-funciones.md`: Documentación del bloque.

## 3. Estructura de la función (Definición, Llamada y Retorno)
- **Definición:** `function duracionLegible(int $total): string` declara que espera un parámetro entero obligatorio y garantiza que devolverá una cadena de texto.
- **Retorno:** Mediante la sentencia `return`, entrega el valor procesado al punto donde se invoque, evitando imprimir HTML directamente (`echo`).
- **Llamada:** Se invoca como `duracionLegible(60)` o dentro del bucle como `duracionLegible((int) $song['duration'])`.

## 4. Pruebas y Comprobaciones

### Prueba 1: Ejecución válida y reutilización
- **URL:** `http://localhost:8090/08-funciones.php` y `http://localhost:8090/07-catalogo.php`
- **Resultado observado:** Para 60 devuelve *"1 minutos y 0 segundos"* y para 185 devuelve *"3 minutos y 5 segundos"*. En el catálogo, todas las canciones muestran su duración formateada correctamente.

### Prueba 2: Comprobación de dependencia inexistente (Fallo con require)
- **Acción:** Apuntar temporalmente a una ruta inexistente (`/lib/canciones-inventado.php`).
- **Comando:** `docker compose logs --tail=30 web`
- **Resultado observado:** Salida de error fatal: `Fatal error: Failed opening required ... No such file or directory`. `require` detiene la ejecución inmediatamente al faltar una dependencia crítica.

## 5. Pregunta del informe: ¿Por qué `php -l` puede validar la sintaxis aunque el archivo requerido no exista?
El comando `php -l` realiza un análisis puramente estático de la sintaxis (parsing de tokens gramaticales, llaves, puntos y coma). Durante este análisis el código **no se ejecuta en tiempo de ejecución**, por lo que PHP no intenta resolver las rutas de los ficheros ni abrirlos en disco. Por eso la sintaxis puede ser válida (`No syntax errors detected`) aunque al cargarse en el navegador falle por un archivo faltante.