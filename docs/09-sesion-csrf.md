# Bloque 9: Preparar sesiones y formularios privados

## 1. Descripción del encargo
Configuración del ciclo de vida de sesiones en PHP y protección de solicitudes POST mediante tokens CSRF (Cross-Site Request Forgery). Establecimiento de directivas estrictas para cookies y validación de tokens en el servidor mediante comparaciones inmunes a ataques de tiempo (`hash_equals`).

## 2. Archivos creados
- `docker/src/lib/sesion.php`: Configuración centralizada de sesión (`JUKEBOXSESSID`, `httponly=true`, `samesite=Lax`, `session.use_strict_mode=1`), generación del token CSRF en `$_SESSION['csrf']` y función de validación `validarTokenCsrf()`.
- `docker/src/13-prueba-sesion.php`: Formulario de comprobación con campo oculto `csrf`.
- `docs/09-sesion-csrf.md`: Documentación del bloque.

## 3. Conceptos del informe
- **Qué se conserva en el servidor:** Los datos de la sesión (como `$_SESSION['csrf']` y más adelante `usuario_id` y `alias`) se almacenan exclusivamente en los archivos temporales de sesión del servidor web, garantizando que datos sensibles o contraseñas no viajen por la red.
- **Qué envía la cookie:** El navegador únicamente envía una cookie pequeña con el nombre `JUKEBOXSESSID`, que contiene un identificador alfanumérico aleatorio. Con este identificador, PHP localiza la sesión correspondiente en el servidor. Al llevar el atributo `HttpOnly`, se impide su lectura desde scripts de JavaScript en el cliente.
- **Por qué se comprueba el token CSRF:** Para comprobar que la solicitud proviene de un formulario legítimo generado por nuestra propia aplicación y no de un sitio malicioso externo que intente enviar peticiones no autorizadas en nombre de un usuario conectado. Se utiliza `hash_equals()` para evitar ataques de temporización (timing attacks).

## 4. Pruebas y Comprobaciones
| Prueba | Acción realizada | Código HTTP esperado | Código HTTP observado | Comportamiento |
| :--- | :--- | :---: | :---: | :--- |
| **Envío legítimo** | Formulario enviado con el token hidden presente | `200 OK` | `200 OK` | Petición aceptada; muestra datos recibidos. |
| **Ataque CSRF simulado** | Eliminación del `<input type="hidden" name="csrf">` mediante el inspector del navegador antes del envío | `403 Forbidden` | `403 Forbidden` | Solicitud rechazada de inmediato; ejecución interrumpida sin alterar datos. |

## 5. Registro de cuentas de prueba (Pasos 3 y 4)

### Implementación realizada
- Archivo `src/register.php`: Recepción segura por POST, validación de alias (2-30 caracteres con `mb_strlen`), normalización y validación de correo (`strtolower` y `FILTER_VALIDATE_EMAIL`), y control de contraseña (12-64 caracteres y descarte ante > 72 bytes con `strlen` para cumplir con bcrypt).
- Almacenamiento protegido: Generación de identificador incremental y cifrado unidireccional de credenciales mediante `password_hash($password, PASSWORD_DEFAULT)`.
- Prevención de reenvío de formulario (Patrón Post/Redirect/Get): Redirección con código HTTP `303 See Other` hacia `login.php`.

### Casos de prueba documentados
| Caso probado | Entradas proporcionadas | Resultado esperado | Resultado observado |
| :--- | :--- | :--- | :--- |
| **Alta válida** | Alias correcto, correo nuevo y clave >= 12 chars | HTTP 303 hacia `login.php` y guardado | Redirección exitosa; usuario creado en `usuarios.json` con hash seguro. |
| **Correo con mayúsculas** | `USUARIO@PRUEBA.LOCAL` existente | Detección de duplicado por normalización | Rechazo con mensaje: *"Este correo electrónico ya está registrado"*. |
| **Claves no coincidentes** | Passwords distintas | Rechazo sin persistir passwords | Mensaje: *"Las contraseñas no coinciden"*; campos de clave limpios. |
| **Entrada tipo array** | `alias[]` | Detección por `is_string()` | Rechazo inmediato sin Warnings ni Fatal Errors. |
| **Acceso directo a datos** | `/data/usuarios.json` | HTTP 404 Not Found | Acceso web denegado por configuración de Apache. |
## 6. Procedimiento común de actualización atómica (Bloque 9)

### Razones técnicas del procedimiento
- **Limitación de `file_put_contents` con `LOCK_EX`:** Proteger solo el momento de la escritura no evita condiciones de carrera (race conditions). Dos peticiones paralelas podrían leer el archivo en el mismo instante, validar la inexistencia del correo simultáneamente y asignarse el mismo ID.
- **Flujo de 4 pasos implementado en `almacen-json.php`:**
  1. Apertura con `fopen('r+')` para no vaciar el archivo preventivamente.
  2. Adquisición de `flock(LOCK_EX)` antes de leer con `stream_get_contents` y parsear el JSON; la validación de duplicados y cálculo de ID ocurren dentro del bloqueo.
  3. Rebobinado con `rewind()`, escritura completa con `fwrite()`, recorte con `ftruncate()` y confirmación en disco con `fflush()`.
  4. Liberación del bloqueo con `flock(LOCK_UN)` y cierre del descriptor en bloque `finally`.
- **Lectura concurrente segura:** La función de lectura usa `flock(LOCK_SH)` (bloqueo compartido) para que varias consultas lean al mismo tiempo sin riesgo de capturar una escritura a medio procesar.
## 7. Flujo de autenticación y control de acceso (Pasos 5 y 6)

### Esquema del ciclo de vida de usuario y sesión
1. **Registro (`register.php`):**
   - El usuario envía datos vía POST protegido por CSRF.
   - Si los datos son válidos, se genera el hash unidireccional con `password_hash()`.
   - **Momento en que se guarda el usuario:** Se almacena en disco dentro de `/var/www/data/usuarios.json` bajo cerrojo `LOCK_EX`.
   - Redirige a `login.php` con cabecera `303`. *(Aún no hay sesión de usuario iniciada)*.

2. **Identificación (`login.php`):**
   - El usuario introduce credenciales por POST protegido por CSRF.
   - Se localiza el correo y se valida la contraseña usando `password_verify()`.
   - Ante fallos, se responde con HTTP 401 y el mensaje opaco *"Credenciales no válidas"*.
   - **Momento en que se abre la sesión de usuario:** Tras verificar la clave, se regenera el ID con `session_regenerate_id(true)` y se asignan `$_SESSION['usuario_id']` y `$_SESSION['alias']`.

3. **Acceso Privado (`home.php` y `lib/acceso.php`):**
   - En cada petición, el servidor ejecuta `acceso.php` antes de generar HTML.
   - Si falta `$_SESSION['usuario_id']`, se emite una redirección 303 hacia `login.php` y se termina con `exit`. La protección reside en el servidor y no en la interfaz del cliente.

4. **Cierre de sesión (`logout.php`):**
   - Exclusivamente mediante petición POST con token CSRF.
   - Se vacía `$_SESSION`, se invalida la cookie `JUKEBOXSESSID` en el navegador y se ejecuta `session_destroy()`.

### Tabla de pruebas de acceso
| Prueba | Acción realizada | Código HTTP esperado | Código HTTP observado |
| :--- | :--- | :---: | :---: |
| **Acceso directo anónimo** | Petición GET a `/home.php` sin cookie | `303 See Other` hacia `/login.php` | Redirección inmediata a login |
| **Login incorrecto** | Contraseña o correo erróneo | `401 Unauthorized` | Mensaje: *"Credenciales no válidas"* |
| **Login correcto** | Credenciales registradas válidas | `303 See Other` hacia `/home.php` | Carga de bienvenida con alias del usuario |
| **Logout GET no permitido**| Navegación directa a `/logout.php` | `405 Method Not Allowed` | Petición rechazada |
| **Logout POST legítimo** | Pulsar botón Cerrar sesión | `303 See Other` hacia `/login.php` | Sesión eliminada; retorno a login |