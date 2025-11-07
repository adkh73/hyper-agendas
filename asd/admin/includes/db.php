<?php
// ========================================================
// CONEXIÓN A LA BASE DE DATOS
// ========================================================

require_once __DIR__ . '/../../config.php';

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error de conexión a la base de datos: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
