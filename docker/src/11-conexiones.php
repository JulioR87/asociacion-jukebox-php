<?php
// docker/src/11-conexiones.php
header('Content-Type: application/json; charset=utf-8');

$resultado = [
    'postgres' => [
        'status' => 'error',
        'database' => null
    ],
    'mariadb' => [
        'status' => 'error',
        'database' => null
    ]
];

$hay_error = false;

// 1. Conexión PDO a PostgreSQL
try {
    $pghost = getenv('PGHOST') ?: 'postgres';
    $pgport = getenv('PGPORT') ?: '5432';
    $pgdb   = getenv('PGDATABASE');
    $pguser = getenv('PGUSER');
    $pgpass = getenv('PGPASSWORD');

    $dsn_pg = "pgsql:host={$pghost};port={$pgport};dbname={$pgdb}";
    $db_pg = new PDO($dsn_pg, $pguser, $pgpass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Comprobamos la base de datos activa
    $db_name_pg = $db_pg->query('SELECT current_database()')->fetchColumn();
    $resultado['postgres']['status'] = 'ok';
    $resultado['postgres']['database'] = $db_name_pg;
} catch (PDOException $e) {
    $hay_error = true;
    error_log('Error de conexión a PostgreSQL: ' . $e->getMessage());
}

// 2. Conexión PDO a MariaDB
try {
    $myhost = getenv('MYSQL_HOST') ?: 'mariadb';
    $myport = getenv('MYSQL_PORT') ?: '3306';
    $mydb   = getenv('MYSQL_DATABASE');
    $myuser = getenv('MYSQL_USER');
    $mypass = getenv('MYSQL_PASSWORD');

    $dsn_my = "mysql:host={$myhost};port={$myport};dbname={$mydb};charset=utf8mb4";
    $db_my = new PDO($dsn_my, $myuser, $mypass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Comprobamos la base de datos activa
    $db_name_my = $db_my->query('SELECT DATABASE()')->fetchColumn();
    $resultado['mariadb']['status'] = 'ok';
    $resultado['mariadb']['database'] = $db_name_my;
} catch (PDOException $e) {
    $hay_error = true;
    error_log('Error de conexión a MariaDB: ' . $e->getMessage());
}

// Si falla alguno de los motores, respondemos con código HTTP 503
if ($hay_error) {
    http_response_code(503);
} else {
    http_response_code(200);
}

// Imprimimos el JSON resultante
echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);