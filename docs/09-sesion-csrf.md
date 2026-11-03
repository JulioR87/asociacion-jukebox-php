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