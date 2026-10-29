# Bloque 1: Levantar el primer servicio y distinguir errores de acceso

## 1. Descripción del encargo
Puesta en marcha del entorno de desarrollo local con Docker Compose para ejecutar PHP sobre un servidor Apache. Comprobación del enrutamiento de puertos, montaje de carpetas y diagnóstico básico de fallos de acceso y sintaxis.

## 2. Archivos modificados y creados
- `compose.yaml`: Configuración del servicio web (`php:8.5-apache-bookworm`), mapeo de puertos (`127.0.0.1:8090:80`) y montaje del volumen (`./src:/var/www/html`).
- `src/01-bienvenida.php`: Script inicial en PHP que imprime el saludo de bienvenida.
- `docs/01-entorno.md`: Documentación técnica del bloque.

## 3. Pruebas realizadas

### Prueba 1: Ejecución válida (200 OK)
- **Comando / URL:** `curl.exe -i http://localhost:8090/01-bienvenida.php`
- **Resultado esperado:** Código de estado HTTP 200 OK y visualización del mensaje: *"Bienvenido al jukebox de la asociación"*.
- **Resultado observado:** El servidor Apache responde correctamente con cabecera `HTTP/1.1 200 OK` y el texto en pantalla.

### Prueba 2: Comprobación de errores y límites

#### A. Archivo inexistente (Error 404)
- **Comando / URL:** `curl.exe -i http://localhost:8090/no-existe.php`
- **Resultado esperado:** Respuesta HTTP 404 Not Found.
- **Resultado observado:** El servidor Apache responde que el recurso no existe, confirmando que el servicio web está activo pero la ruta no es válida.

#### B. Servicio detenido (Fallo de conexión)
- **Comando:** `docker compose stop` seguido de `curl.exe -i http://localhost:8090/01-bienvenida.php`
- **Resultado esperado:** Error de conexión a nivel de red/transporte.
- **Resultado observado:** Mensaje de error (*Failed to connect*), lo que confirma que no hay un proceso escuchando en el puerto 8090.

#### C. Error de sintaxis en PHP
- **Acción:** Eliminación deliberada de un punto y coma (`;`) en `01-bienvenida.php`.
- **Comando de comprobación:** `docker compose exec web php -l /var/www/html/01-bienvenida.php`
- **Resultado observado:** `PHP Parse error: syntax error, unexpected token "echo"...` indicando línea y archivo.
- **Corrección:** Se restaura el `;` y la salida confirma `No syntax errors detected`.

## 4. Diferencias clave de diagnóstico
| Tipo de fallo | Causa | Detección |
| :--- | :--- | :--- |
| **Fallo de conexión** | El contenedor/servidor está apagado. | *Failed to connect* (no hay cabeceras ni código HTTP). |
| **Error 404 Not Found** | El servidor está encendido pero el archivo no existe. | Cabecera `HTTP/1.1 404 Not Found`. |
| **Error de sintaxis** | El archivo existe pero el motor de PHP no puede interpretar el código. | `Parse error` mediante el linter `php -l` o error 500 en pantalla. |