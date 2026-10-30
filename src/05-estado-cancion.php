<?php
// Cambiar este valor para probar: 0, 1, 239, 240, 241
$duration = 240;

if ($duration === 0) {
    $estado = 'Sin duración';
} elseif ($duration >= 1 && $duration <= 240) {
    $estado = 'Hasta cuatro minutos';
} else {
    $estado = 'Más de cuatro minutos';
}

echo "Duración: {$duration} segundos. Clasificación: {$estado}";

// Comparación adicional para mostrar el uso de var_dump
echo '<br><br><strong>Comparación de tipos:</strong><br>';
echo 'Resultado de (0 === "0"): ';
var_dump(0 === '0');