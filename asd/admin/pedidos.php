<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verificar sesión
checkLogin();

// Manejo de acciones: cambiar estado
if(isset($_GET['accion']) && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $accion = $_GET['accion'];

    if(in_array($accion,['pagado','entregado','pendiente','cancelado'])){
        $fecha_campo = ($accion=='pagado') ? 'fecha_pago' : (($accion=='entregado') ? 'fecha_entregado' : null);
        $sql = "UPDATE pedidos SET estado='$accion'";
        if($fecha_campo) $sql .= ", $fecha_campo=NOW()";
        $sql .= " WHERE id=$id";
        $conexion->query($sql);
    }
}

// Obtener pedidos (opcional filtro por estado)
$filtro_estado = $_GET['estado'] ?? null;
$pedidos = getPedidos($filtro_estado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pedidos - <?= SITE_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<!-- NAVBAR -->
<nav class="bg-indigo-500 text-white p-4 flex justify-between">
    <div class="font-bold text-xl"><?= SITE_NAME ?> - Admin</div>
    <div>
        <a href="dashboard.php" class="bg-gray-200 text-gray-800 px-3 py-1 rounded mr-2">Dashboard</a>
        <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Cerrar sesión</a>
    </div>
</nav>

<main class="p-6 max-w-6xl mx-auto">
<h1 class="text-2xl font-bold mb-6">Gestión de Pedidos</h1>

<!-- Filtro por estado -->
<div class="mb-4">
    <a href="pedidos.php" class="mr-2 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Todos</a>
    <a href="pedidos.php?estado=pendiente" class="mr-2 px-3 py-1 bg-yellow-200 rounded hover:bg-yellow-300">Pendientes</a>
    <a href="pedidos.php?estado=pagado" class="mr-2 px-3 py-1 bg-green-200 rounded hover:bg-green-300">Pagados</a>
    <a href="pedidos.php?estado=entregado" class="mr-2 px-3 py-1 bg-blue-200 rounded hover:bg-blue-300">Entregados</a>
    <a href="pedidos.php?estado=cancelado" class="mr-2 px-3 py-1 bg-red-200 rounded hover:bg-red-300">Cancelados</a>
</div>

<!-- Tabla de pedidos -->
<div class="bg-white shadow rounded p-4">
<table class="w-full border-collapse">
<thead>
<tr class="bg-gray-100">
    <th class="border p-2">ID</th>
    <th class="border p-2">Cliente</th>
    <th class="border p-2">Email</th>
    <th class="border p-2">Usuario</th>
    <th class="border p-2">Producto</th>
    <th class="border p-2">Estado</th>
    <th class="border p-2">Fecha pedido</th>
    <th class="border p-2">Fecha pago</th>
    <th class="border p-2">Fecha entrega</th>
    <th class="border p-2">Acciones</th>
</tr>
</thead>
<tbody>
<?php foreach($pedidos as $p): ?>
<tr>
    <td class="border p-2"><?= $p['id'] ?></td>
    <td class="border p-2"><?= htmlspecialchars($p['nombre']) ?></td>
    <td class="border p-2"><?= htmlspecialchars($p['email']) ?></td>
    <td class="border p-2"><?= htmlspecialchars($p['usuario']) ?></td>
    <td class="border p-2"><?= htmlspecialchars($p['producto_nombre']) ?></td>
    <td class="border p-2"><?= ucfirst($p['estado']) ?></td>
    <td class="border p-2"><?= $p['fecha_pedido'] ?></td>
    <td class="border p-2"><?= $p['fecha_pago'] ?? '-' ?></td>
    <td class="border p-2"><?= $p['fecha_entregado'] ?? '-' ?></td>
    <td class="border p-2 space-x-1">
        <?php if($p['estado'] != 'pagado'): ?>
            <a href="?accion=pagado&id=<?= $p['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">Marcar pagado</a>
        <?php endif; ?>
        <?php if($p['estado'] != 'entregado'): ?>
            <a href="?accion=entregado&id=<?= $p['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">Marcar entregado</a>
        <?php endif; ?>
        <?php if($p['estado'] != 'cancelado'): ?>
            <a href="?accion=cancelado&id=<?= $p['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">Cancelar</a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

</main>
</body>
</html>
