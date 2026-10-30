<?php
//Variable para la duracion inicial en segundos
$duration = 185;

//Variables para el calculo de minutos y segundos
$minutes = intdiv($duration, 60);
$seconds = $duration % 60;

//Imprimir la salida con la frase construida
echo "La duración de {$duration} segundos equivale a {$minutes} minutos y {$seconds} segundos.";