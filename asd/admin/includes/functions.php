<?php
// ========================================================
// FUNCIONES AUXILIARES DEL PANEL Y LANDING
// ========================================================

require_once 'db.php';

/**
 * Obtener todos los productos
 */
function getProductos($soloVisibles = true) {
    global $conexion;
    $sql = "SELECT * FROM productos";
    if ($soloVisibles) {
        $sql .= " WHERE visible = 1";
    }
    $sql .= " ORDER BY fecha_creacion DESC";
    $result = $conexion->query($sql);
    $productos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    return $productos;
}

/**
 * Obtener producto por ID
 */
function getProductoById($id) {
    global $conexion;
    $id = intval($id);
    $sql = "SELECT * FROM productos WHERE id=$id LIMIT 1";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Obtener promoción activa
 */
function getPromocionActiva() {
    global $conexion;
    $hoy = date('Y-m-d');
    $sql = "SELECT * FROM promocion WHERE activa = 1 AND fecha_inicio <= '$hoy' AND fecha_fin >= '$hoy' ORDER BY id DESC LIMIT 1";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Obtener todos los pedidos
 */
function getPedidos($estado = null) {
    global $conexion;
    $sql = "SELECT p.*, pr.nombre AS producto_nombre FROM pedidos p LEFT JOIN productos pr ON p.producto_id = pr.id";
    if ($estado) {
        $estado = $conexion->real_escape_string($estado);
        $sql .= " WHERE p.estado='$estado'";
    }
    $sql .= " ORDER BY p.fecha_pedido DESC";
    $result = $conexion->query($sql);
    $pedidos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
    }
    return $pedidos;
}

/**
 * Calcular precio final considerando promoción
 */
function calcularPrecioFinal($precio, $promocion) {
    if ($promocion && isset($promocion['descuento'])) {
        $descuento = floatval($promocion['descuento']);
        return round($precio * (1 - $descuento / 100), 2);
    }
    return $precio;
}

/**
 * Guardar nuevo pedido
 */
function crearPedido($nombre, $email, $usuario, $producto_id, $metodo_pago = null, $transaccion_id = null) {
    global $conexion;
    $nombre = $conexion->real_escape_string($nombre);
    $email = $conexion->real_escape_string($email);
    $usuario = $conexion->real_escape_string($usuario);
    $producto_id = intval($producto_id);
    $metodo_pago = $conexion->real_escape_string($metodo_pago);
    $transaccion_id = $conexion->real_escape_string($transaccion_id);

    $fecha_pedido = date('Y-m-d H:i:s');
    $sql = "INSERT INTO pedidos (nombre, email, usuario, producto_id, estado, metodo_pago, transaccion_id, fecha_pedido)
            VALUES ('$nombre', '$email', '$usuario', $producto_id, 'pendiente', '$metodo_pago', '$transaccion_id', '$fecha_pedido')";
    return $conexion->query($sql);
}
?>
