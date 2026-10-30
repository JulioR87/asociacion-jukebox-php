<?php
// Cargamos la biblioteca de funciones mediante require_once
require_once __DIR__ . '/lib/canciones.php';

// Llamada con 60 y 185 segundos mostrando ambos resultados
echo '<p>Prueba con 60 segundos: ' . duracionLegible(60) . '</p>';
echo '<p>Prueba con 185 segundos: ' . duracionLegible(185) . '</p>';