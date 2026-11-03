<?php
// docker/src/lib/almacen-json.php

/**
 * Lee un archivo JSON con bloqueo compartido (LOCK_SH) para evitar lecturas inconsistentes.
 */
function leerJsonConBloqueo(string $ruta): array
{
    if (!file_exists($ruta)) {
        throw new RuntimeException("El archivo {$ruta} no existe.");
    }

    $fp = @fopen($ruta, 'rb');
    if ($fp === false) {
        throw new RuntimeException("No se pudo abrir el archivo para lectura.");
    }

    try {
        if (!flock($fp, LOCK_SH)) {
            throw new RuntimeException("No se pudo adquirir el bloqueo de lectura.");
        }

        $contenido = stream_get_contents($fp);
        if ($contenido === false) {
            throw new RuntimeException("Fallo al leer el contenido del archivo.");
        }

        if (trim($contenido) === '') {
            return [];
        }

        return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * Procedimiento de 4 pasos para actualizar un JSON bajo bloqueo exclusivo (LOCK_EX).
 * El $callback recibe el array actual, valida y devuelve el array modificado.
 */
function actualizarJsonConBloqueo(string $ruta, callable $callback): array
{
    if (!file_exists($ruta)) {
        throw new RuntimeException("El archivo {$ruta} no existe. Prepáralo antes con [].");
    }

    // 1. Abrir con r+: permite leer y escribir sin truncar al inicio
    $fp = @fopen($ruta, 'r+b');
    if ($fp === false) {
        throw new RuntimeException("Error al abrir {$ruta} en modo r+.");
    }

    try {
        // 2. Adquirir bloqueo exclusivo LOCK_EX
        if (!flock($fp, LOCK_EX)) {
            throw new RuntimeException("No se pudo adquirir el bloqueo exclusivo.");
        }

        $contenido = stream_get_contents($fp);
        if ($contenido === false) {
            throw new RuntimeException("Error al leer el archivo durante el bloqueo.");
        }

        $records = [];
        if (trim($contenido) !== '') {
            $records = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($records)) {
                throw new RuntimeException("El contenido del JSON no es una lista válida.");
            }
        }

        // El callback procesa la modificación (comprueba duplicados o añade elementos)
        $nuevosRecords = $callback($records);

        // Convertir a texto JSON con banderas estrictas antes de tocar el disco
        $json = json_encode(
            $nuevosRecords,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        // 3. Modificar en disco bajo el mismo bloqueo
        if (rewind($fp) === false) {
            throw new RuntimeException("Error al rebobinar el archivo (rewind).");
        }

        $bytesEscritos = fwrite($fp, $json);
        if ($bytesEscritos === false || $bytesEscritos < strlen($json)) {
            throw new RuntimeException("Error: no se completó la escritura de todos los bytes.");
        }

        if (ftruncate($fp, $bytesEscritos) === false) {
            throw new RuntimeException("Error al recortar el archivo sobrante (ftruncate).");
        }

        if (fflush($fp) === false) {
            throw new RuntimeException("Error al forzar el volcado a disco (fflush).");
        }

        return $nuevosRecords;
    } finally {
        // 4. Liberar el bloqueo y cerrar el descriptor siempre
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/**
 * Función compatible para el catálogo de canciones
 */
function leerCatalogoCanciones(): array
{
    return leerJsonConBloqueo('/var/www/data/canciones.json');
}