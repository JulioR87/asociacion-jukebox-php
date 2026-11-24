# Jukebox Comunitaria - Práctica de Servidor PHP

Aplicación web modular en PHP para la gestión de catálogo musical y reproducción interactiva mediante contenedores Docker, sesiones de usuario y persistencia JSON.

---

## 1. Requisitos previos
- Docker Desktop en ejecución.
- Docker Compose v2+.
- Puerto de red `8091` disponible en el equipo.

---

## 2. Estructura del proyecto
- `data-ejemplo/`: Archivos base limpios (`canciones.json` y `usuarios.json`) para arranques nuevos.
- `docker/`: Configuración del entorno (`compose.yaml`, `Dockerfile` y configuración de Apache).
- `docker/data/`: Directorio privado de almacenamiento persistente montado fuera del DocumentRoot.
- `docker/src/`: Directorio público servido por Apache (`home.php`, `login.php`, `canciones.php`, etc.).
- `docs/`: Documentación técnica de los bloques de desarrollo.

---

## 3. Despliegue inicial

1. Copiar las plantillas limpias a la carpeta de datos privada:
   ```bash
   # En Windows PowerShell:
   Copy-Item .\data-ejemplo\* .\docker\data\
## Ampliación AJAX y API RESTful (RA2 - Bloques 13 a 20)

- **Nueva pantalla de gestión asíncrona:** `http://localhost:8091/canciones-ajax.php` (requiere sesión iniciada).
- **Endpoint JSON:** `api/catalogo.php` soporta las acciones `list` (`GET`), `create`, `update` y `delete` (`POST` con cabecera `X-CSRF-Token` y cuerpo `application/json`).
- **Compatibilidad:** Convive con el reproductor (`home.php` + `api/canciones.php`) y con el CRUD clásico (`canciones.php`), compartiendo el almacén privado `/var/www/data/canciones.json` protegido con bloqueos `flock()`.
- **Documentación técnica:** Disponible en la carpeta `docs/` (`13` hasta `20-entrega.md`).