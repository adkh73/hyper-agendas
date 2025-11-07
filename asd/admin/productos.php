<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Verificar sesión
checkLogin();

// Manejo de acciones (crear, actualizar, eliminar)
$accion = $_GET['accion'] ?? '';
$mensaje = '';

// Crear o editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $visible = isset($_POST['visible']) ? 1 : 0;
    $id = $_POST['id'] ?? 0;

    // Manejo de imagen
    $imagen_nombre = '';
    if(isset($_FILES['imagen']) && $_FILES['imagen']['tmp_name']) {
        $imagen_nombre = time() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], '../uploads/' . $imagen_nombre);
    }

    if($id){ // editar
        $sql = "UPDATE productos SET 
                    nombre='". $conexion->real_escape_string($nombre) ."',
                    descripcion='". $conexion->real_escape_string($descripcion) ."',
                    precio='". floatval($precio) ."',
                    visible=$visible";
        if($imagen_nombre) $sql .= ", imagen='$imagen_nombre'";
        $sql .= " WHERE id=". intval($id);
        if($conexion->query($sql)) $mensaje = "Producto actualizado correctamente";
    } else { // crear
        $sql = "INSERT INTO productos (nombre, descripcion, precio, visible, imagen, fecha_creacion) VALUES (
            '". $conexion->real_escape_string($nombre) ."',
            '". $conexion->real_escape_string($descripcion) ."',
            '". floatval($precio) ."',
            $visible,
            '". $imagen_nombre ."',
            NOW()
        )";
        if($conexion->query($sql)) $mensaje = "Producto creado correctamente";
    }
}

// Eliminar producto
if($accion === 'eliminar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conexion->query("DELETE FROM productos WHERE id=$id");
    $mensaje = "Producto eliminado correctamente";
}

// Obtener todos los productos
$productos = getProductos(false);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Productos - <?= SITE_NAME ?></title>
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

<h1 class="text-2xl font-bold mb-6">Gestión de Productos</h1>

<?php if($mensaje): ?>
<div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
    <?= $mensaje ?>
</div>
<?php endif; ?>

<!-- FORMULARIO CREAR / EDITAR -->
<div class="bg-white shadow rounded p-4 mb-6">
    <h2 class="text-xl font-bold mb-4">Crear / Editar Producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="producto_id">
        <label class="block mb-2 font-semibold">Nombre</label>
        <input type="text" name="nombre" id="nombre" required class="w-full border rounded px-3 py-2 mb-3">
        <label class="block mb-2 font-semibold">Descripción</label>
        <textarea name="descripcion" id="descripcion" class="w-full border rounded px-3 py-2 mb-3"></textarea>
        <label class="block mb-2 font-semibold">Precio</label>
        <input type="number" step="0.01" name="precio" id="precio" required class="w-full border rounded px-3 py-2 mb-3">
        <label class="block mb-2 font-semibold">Imagen</label>
        <input type="file" name="imagen" class="mb-3">
        <label class="inline-flex items-center mb-3">
            <input type="checkbox" name="visible" id="visible" class="mr-2"> Visible
        </label>
        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded">
            Guardar Producto
        </button>
    </form>
</div>

<!-- LISTADO DE PRODUCTOS -->
<div class="bg-white shadow rounded p-4">
    <h2 class="text-xl font-bold mb-4">Productos existentes</h2>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">ID</th>
                <th class="border p-2">Nombre</th>
                <th class="border p-2">Precio</th>
                <th class="border p-2">Visible</th>
                <th class="border p-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($productos as $prod): ?>
            <tr>
                <td class="border p-2"><?= $prod['id'] ?></td>
                <td class="border p-2"><?= htmlspecialchars($prod['nombre']) ?></td>
                <td class="border p-2"><?= number_format($prod['precio'],2) ?></td>
                <td class="border p-2"><?= $prod['visible'] ? 'Sí' : 'No' ?></td>
                <td class="border p-2">
                    <button onclick="editarProducto(<?= $prod['id'] ?>,'<?= htmlspecialchars($prod['nombre'],ENT_QUOTES) ?>','<?= htmlspecialchars($prod['descripcion'],ENT_QUOTES) ?>',<?= $prod['precio'] ?>,<?= $prod['visible'] ?>)"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded mr-1">Editar</button>
                    <a href="?accion=eliminar&id=<?= $prod['id'] ?>" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editarProducto(id, nombre, descripcion, precio, visible){
    document.getElementById('producto_id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('descripcion').value = descripcion;
    document.getElementById('precio').value = precio;
    document.getElementById('visible').checked = visible == 1;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

</main>
</body>
</html>
