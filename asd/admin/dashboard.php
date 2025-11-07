<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verificar sesión
checkLogin();

// Obtener estadísticas
$total_productos = count(getProductos(false));
$total_pedidos = count(getPedidos());
$total_pedidos_pendientes = count(getPedidos('pendiente'));
$total_pedidos_pagados = count(getPedidos('pagado'));
$total_pedidos_entregados = count(getPedidos('entregado'));

$promocion = getPromocionActiva();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- NAVBAR -->
<nav class="bg-indigo-500 text-white p-4 flex justify-between">
    <div class="font-bold text-xl"><?= SITE_NAME ?> - Admin</div>
    <div>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Cerrar sesión</a>
    </div>
</nav>

<!-- DASHBOARD -->
<main class="p-6 max-w-6xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">Panel de control</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600">Productos</h2>
            <p class="text-3xl font-bold mt-2"><?= $total_productos ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600">Pedidos totales</h2>
            <p class="text-3xl font-bold mt-2"><?= $total_pedidos ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600">Pendientes</h2>
            <p class="text-3xl font-bold mt-2"><?= $total_pedidos_pendientes ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600">Pagados</h2>
            <p class="text-3xl font-bold mt-2"><?= $total_pedidos_pagados ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600 mb-2">Entregados</h2>
            <p class="text-2xl font-bold"><?= $total_pedidos_entregados ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <h2 class="font-bold text-gray-600 mb-2">Promoción activa</h2>
            <?php if($promocion): ?>
                <p class="text-lg"><?= htmlspecialchars($promocion['texto']) ?> (<?= $promocion['descuento'] ?>%)</p>
            <?php else: ?>
                <p class="text-gray-500">No hay promoción activa</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-6">
        <a href="productos.php" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded mr-2">Gestionar Productos</a>
        <a href="pedidos.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded mr-2">Ver Pedidos</a>
        <a href="promocion.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Editar Promoción</a>
    </div>

</main>

</body>
</html>
