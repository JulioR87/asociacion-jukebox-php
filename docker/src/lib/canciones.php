<?php
// src/lib/canciones.php

function duracionLegible(int $total): string
{
    $minutos = intdiv($total, 60);
    $segundos = $total % 60;
    return "{$minutos} minutos y {$segundos} segundos";
}