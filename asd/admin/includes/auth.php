<?php
// ========================================================
// AUTENTICACIÓN ADMIN
// ========================================================

session_start();

// Función para verificar si el admin está logueado
function checkLogin() {
    if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
        header("Location: index.php");
        exit;
    }
}

// Función para iniciar sesión
function login($usuario, $password) {
    global $conexion;

    // Escapar datos
    $usuario = $conexion->real_escape_string($usuario);
    $password = $conexion->real_escape_string($password);

    // Consultar admin
    $sql = "SELECT * FROM admin WHERE usuario='$usuario' LIMIT 1";
    $result = $conexion->query($sql);

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Verificar contraseña
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_user'] = $row['usuario'];
            return true;
        }
    }
    return false;
}

// Función para cerrar sesión
function logout() {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
