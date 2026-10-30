<?php
// Límite de las listas
$limite = 4;

echo "<h2>Lista con bucle FOR:</h2>";
echo "<ul>";
for ($i = 1; $i <= $limite; $i++) {
    echo "<li>Posición: {$i}</li>";
}
echo "</ul>";

echo "<h2>Lista con bucle WHILE:</h2>";
echo "<ul>";
$j = 1;
while ($j <= $limite) {
    echo "<li>Posición: {$j}</li>";
    $j++;
}
echo "</ul>";

echo "<h2>Array de títulos con FOREACH:</h2>";
$titulos = [
    'Canción Electrónica de Fondo',
    'Solo de Guitarra Rock',
    'Tema Principal Orquestal'
];

echo "<ol>";
// $indice empieza en 0 internamente, pero al usuario le mostramos $indice + 1
foreach ($titulos as $indice => $titulo) {
    $numero_visible = $indice + 1;
    echo "<li>Pista {$numero_visible} (Índice interno {$indice}): " . htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
}
echo "</ol>";