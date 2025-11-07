<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verificar sesión
checkLogin();

$mensaje = '';

// Guardar promoción
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = $_POST['texto'] ?? '';
    $descuento = floatval($_POST['descuento'] ?? 0);
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    // Desactivar todas las promociones actuales
    $conexion->query("UPDATE promocion SET activa=0");

    // Insertar nueva promoción
    $sql = "INSERT INTO promocion (texto, descuento, fecha_inicio, fecha_fin, activa)
            VALUES (
                '". $conexion->real_escape_string($texto) ."',
                $descuento,
                '". $conexion->real_escape_string($fecha_inicio) ."',
                '". $conexion->real_escape_string($fecha_fin) ."',
                1
            )";
    if($conexion->query($sql)) $mensaje = "Promoción guardada correctamente";
}

// Obtener promoción activa actual
$promocion = getPromocionActiva();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Promoción - <?= SITE_NAME ?></title>
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

<main class="p-6 max-w-3xl mx-auto">
<h1 class="text-2xl font-bold mb-6">Gestión de Promoción</h1>

<?php if($mensaje): ?>
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    <?= $mensaje ?>
</div>
<?php endif; ?>

<form method="POST" class="bg-white shadow rounded p-6">
    <label class="block mb-2 font-semibold">Texto de promoción</label>
    <input type="text" name="texto" required class="w-full border rounded px-3 py-2 mb-4" 
           value="<?= htmlspecialchars($promocion['texto'] ?? '') ?>">

    <label class="block mb-2 font-semibold">Descuento (%)</label>
    <input type="number" step="0.01" name="descuento" required class="w-full border rounded px-3 py-2 mb-4"
           value="<?= htmlspecialchars($promocion['descuento'] ?? 0) ?>">

    <label class="block mb-2 font-semibold">Fecha de inicio</label>
    <input type="date" name="fecha_inicio" required class="w-full border rounded px-3 py-2 mb-4"
           value="<?= htmlspecialchars($promocion['fecha_inicio'] ?? '') ?>">

    <label class="block mb-2 font-semibold">Fecha de fin</label>
    <input type="date" name="fecha_fin" required class="w-full border rounded px-3 py-2 mb-6"
           value="<?= htmlspecialchars($promocion['fecha_fin'] ?? '') ?>">

    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
        Guardar Promoción
    </button>
</form>

</main>
</body>
</html>
