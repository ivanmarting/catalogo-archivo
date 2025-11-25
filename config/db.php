<?php
// Evita redefinir constantes si el archivo se incluye múltiples veces
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'aosch_bd');
}

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    die("Error crítico de conexión: " . $conexion->connect_error);
}

// Configurar charset a utf8mb4 para soportar tildes y emojis correctamente
$conexion->set_charset("utf8mb4");
?>