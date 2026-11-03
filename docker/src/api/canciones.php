<?php
// docker/src/api/canciones.php
require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/almacen-json.php';

// Cabecera obligatoria JSON
header('Content-Type: application/json; charset=utf-8');

// 1. Control de sesión: si falta usuario_id, responder 401 en JSON (sin HTML)
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'No autorizado. Se requiere inicio de sesión.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Leer canciones privadas y devolver lista plana
try {
    $songs = leerCatalogoCanciones();
    http_response_code(200);
    // Devuelve directamente la lista sin envolver en claves extras
    echo json_encode($songs, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Error al obtener el catálogo de canciones.'
    ], JSON_UNESCAPED_UNICODE);
}