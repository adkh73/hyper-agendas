<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($usuario, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center"><?= SITE_NAME ?> - Admin</h1>

    <?php if ($error): ?>
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
        <label class="block mb-2 font-semibold">Usuario</label>
        <input type="text" name="usuario" required class="w-full border rounded px-3 py-2 mb-4">

        <label class="block mb-2 font-semibold">Contraseña</label>
        <input type="password" name="password" required class="w-full border rounded px-3 py-2 mb-6">

        <button type="submit" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 rounded">
            Ingresar
        </button>
    </form>
</div>

</body>
</html>
