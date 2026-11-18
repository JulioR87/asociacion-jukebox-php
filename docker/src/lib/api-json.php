<?php
// docker/src/lib/api-json.php
// Utilidades comunes para responder siempre en formato JSON limpio

function responderJson(int $codigoHttp, array $payload, array $cabecerasExtra = []): never
{
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');

    foreach ($cabecerasExtra as $nombre => $valor) {
        header("$nombre: $valor");
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function responderExito(array $data, int $codigoHttp = 200): never
{
    responderJson($codigoHttp, [
        'ok'   => true,
        'data' => $data,
    ]);
}

function responderError(int $codigoHttp, string $mensaje, array $cabecerasExtra = []): never
{
    responderJson($codigoHttp, [
        'ok'    => false,
        'error' => $mensaje,
    ], $cabecerasExtra);
}