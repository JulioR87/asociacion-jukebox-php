# Bloque 7: Preparar la segunda etapa de Docker

## 1. Descripción del encargo
Evolución de la arquitectura del entorno Docker a una segunda etapa (puerto 8091), desacoplando los archivos públicos accesibles por web (`src/`) de los datos privados del sistema (`data/`). Compilación personalizada con `Dockerfile` incorporando controladores de base de datos (`pdo_mysql`, `pdo_pgsql`) y configuración de VirtualHost en Apache.

## 2. Estructura de archivos creada
- `docker/Dockerfile`: Definición de la imagen basada en `php:8.5-apache-bookworm`, instalación de extensiones PDO y habilitación del VirtualHost.
- `docker/apache/jukebox.conf`: Configuración del sitio virtual con `DocumentRoot /var/www/html`, `ServerName jukebox.test` y `ServerAlias localhost`.
- `docker/compose.yaml`: Orquestación del servicio en el puerto `8091:80` con doble volumen: `./src:/var/www/html` (público) y `./data:/var/www/data` (privado).
- `docker/.env.example` y `docker/.dockerignore`: Plantilla de variables de entorno y exclusión de datos privados del contexto de compilación.

## 3. Pruebas y Comprobaciones

### Prueba 1: Acceso público válido en el puerto 8091
- **Comando / URL:** `curl.exe -i http://localhost:8091/01-bienvenida.php`
- **Resultado observado:** Código de estado `200 OK`, verificando que el VirtualHost y el mapeo de puertos de la etapa 2 funcionan correctamente.

### Prueba 2: Comprobación de seguridad en `/data` (Caso límite)
- **Comando / URL:** `curl.exe -i http://localhost:8091/data/usuarios.json`
- **Resultado esperado:** `404 Not Found`.
- **Resultado observado:** El servidor web devuelve `404 Not Found`. Aunque el archivo existe en el sistema en `/var/www/data`, Apache restringe el acceso web exclusivamente a `/var/www/html`, impidiendo filtraciones de datos privados.

### Prueba 3: Verificación de módulos PHP
- **Comando:** `docker compose exec web php -m`
- **Resultado observado:** Módulos `pdo_mysql` y `pdo_pgsql` cargados con éxito en el motor PHP.