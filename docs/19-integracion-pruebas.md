# Bloque 19 · Integración entre vistas, refactorización y pruebas de límites

## Qué cambia
- Se unifica la navegación enlazando `home.php`, `canciones.php` (CRUD clásico) y `canciones-ajax.php` (CRUD AJAX), todas protegidas por sesión.
- Se refactoriza `catalogo-ajax.js` extrayendo `enviarPeticionApi()` como función común de red y contrato, separando las responsabilidades en funciones pequeñas y gestionando el foco de teclado al editar o cancelar.
- Se mantiene intacto `api/canciones.php` devolviendo el array plano para el reproductor de `home.php`.

## Por qué
- Elimina duplicación de código en las comprobaciones HTTP y JSON del cliente y mejora la accesibilidad y claridad de mensajes (`Cargando...`, `Guardando...`, errores persistentes).
- Garantiza que el reproductor clásico y ambos CRUD compartan un único almacén JSON fuera del DocumentRoot sin romper compatibilidad.

## Qué probar y qué resultado esperar
1. Crear una canción en AJAX, editarla en el CRUD clásico (`canciones.php`) y reproducir su audio en `home.php`: las tres pantallas reflejan los mismos cambios.
2. Activar modo `Offline` en DevTools durante una consulta: muestra un error comprensible en pantalla sin reintentos automáticos en `POST` y se recupera al volver a `Online`.
3. Simular JSON corrupto en una copia de respaldo: la API devuelve HTTP `500` y conserva el archivo intacto sin sobrescribirlo con `[]`.