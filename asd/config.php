<?php
// ========================================================
// CONFIGURACIÓN GENERAL DEL SITIO - Agendas 2026
// ========================================================

// --- CONFIGURACIÓN DE BASE DE DATOS ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Cambiar según tu hosting
define('DB_PASS', '');           // Cambiar según tu hosting
define('DB_NAME', 'agendas');    // Nombre de la base de datos

// --- NOMBRE DEL SITIO ---
define('SITE_NAME', 'Agendas 2026');

// --- URL BASE (ajustar según dominio real) ---
define('BASE_URL', 'http://localhost/venta-agendas/');

// --- CONFIGURACIÓN DE ZONA HORARIA ---
date_default_timezone_set('America/Argentina/Buenos_Aires');

// --- ERRORES (en producción desactivar) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
