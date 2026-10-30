<?php
// docker/src/lib/almacen-json.php

/**
 * Lee el catálogo de canciones desde el archivo JSON privado.
 * Devuelve un array de canciones o lanza una excepción en caso de error.
 */
function leerCatalogoCanciones(): array
{
    // Ruta fija dentro del contenedor; nunca se toma de una entrada web
    $rutaArchivo = '/var/www/data/canciones.json';

    if (!file_exists($rutaArchivo)) {
        throw new RuntimeException("El archivo de catálogo no existe en {$rutaArchivo}.");
    }

    $text = @file_get_contents($rutaArchivo);
    if ($text === false) {
        throw new RuntimeException("No se pudo leer el archivo de almacenamiento.");
    }

    // Decodificamos a array asociativo usando JSON_THROW_ON_ERROR
    $songs = json_decode($text, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($songs)) {
        throw new RuntimeException("El formato del JSON no representa una lista válida.");
    }

    return $songs;
}