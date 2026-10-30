<?php
//Datos de la cancion (el titulo simula contener etiquetas HTML)
$title = '<b>Demo</b>';
$artist = 'Grupo del aula';
$duration = 185;
$volume = 0.5;
$available = true;
const APP_NAME = 'Jukebox de la asociacion';

//Escapamos el titulo para evitar problemas de seguridad
$title_escapado = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
</head>
<body>
    <h1>Canción destacada: <?php echo $title_escapado; ?></h1>

    <p>
        Estás escuchando la canción titulada <?php echo $title_escapado; ?>,
        interpretada por <?php echo htmlspecialchars($artist, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>.
    </p>

    <p>Duración: <?php echo $duration; ?> segundos.</p>
</body>
</html>