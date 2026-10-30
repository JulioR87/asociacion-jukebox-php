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
## 4. Ampliación multiservicio: PostgreSQL y MariaDB

### Configuración de servicios
- Se orquestaron tres contenedores bajo el proyecto `asociacion-jukebox-multiservicio`:
  - `web`: Servidor Apache + PHP con extensiones PDO.
  - `postgres` (imagen `postgres:17-bookworm`): Base de datos `jukebox_pg`.
  - `mariadb` (imagen `mariadb:11.4`): Base de datos `jukebox_maria`.
- Se configuraron directivas `healthcheck` con `depends_on: condition: service_healthy` para garantizar que la web solo reciba tráfico una vez que los motores estén plenamente inicializados.
- Se mantuvo el aislamiento de red sin mapear los puertos 5432 ni 3306 al host de Windows.

### Diferencia entre tipos de almacenamiento
- **Bind Mounts (`./src` y `./data`):** Enlaces directos a carpetas del sistema anfitrión (Windows) que permiten editar código en vivo y persistir datos en formato JSON fuera de la raíz web de Apache.
- **Volúmenes con nombre de Docker (`pgdata` y `mariadata`):** Áreas de almacenamiento gestionadas directamente por el motor de Docker optimizadas para bases de datos relacionales, conservando los datos de los motores incluso si se eliminan los contenedores.
*(Nota: No se debe ejecutar `docker compose down -v` para evitar borrar estos volúmenes).*

### Prueba de conexiones PDO (`11-conexiones.php`)
- **Respuesta obtenida:** Estado HTTP `200 OK` con JSON confirmando conexión activa y bases de datos (`jukebox_pg` y `jukebox_maria`).
- **Seguridad:** En caso de fallo se devuelve un código de estado `503 Service Unavailable`, enviando el detalle del error al registro de errores de Apache (`error_log`) sin exponer credenciales en la respuesta pública.