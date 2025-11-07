<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'agendas_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// Obtener el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_SERVER['PATH_INFO']) ? explode('/', trim($_SERVER['PATH_INFO'],'/')) : [];
$table = isset($request[0]) ? preg_replace('/[^a-z0-9_]+/i','', $request[0]) : '';
$key = isset($request[1]) ? $request[1] : null;

// Directorio para subir imágenes
$uploadDir = 'images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Función para subir archivos
function uploadFile($file, $uploadDir) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath;
    }
    
    return null;
}

switch ($method) {
    case 'GET':
        if ($table == 'products') {
            $stmt = $pdo->query("SELECT * FROM products WHERE is_active = TRUE");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table == 'promotions') {
            $stmt = $pdo->query("SELECT * FROM promotions WHERE is_active = TRUE AND expiry_date >= CURDATE()");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table == 'orders') {
            $stmt = $pdo->query("SELECT o.*, 
                                GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as products
                                FROM orders o
                                LEFT JOIN order_items oi ON o.id = oi.order_id
                                LEFT JOIN products p ON oi.product_id = p.id
                                GROUP BY o.id
                                ORDER BY o.created_at DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($table == 'site_content') {
            $stmt = $pdo->query("SELECT * FROM site_content");
            $content = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $content[$row['section_name']] = $row;
            }
            echo json_encode($content);
        } elseif ($table == 'samples') {
            $stmt = $pdo->query("SELECT ps.*, p.name as product_name 
                               FROM product_samples ps 
                               LEFT JOIN products p ON ps.product_id = p.id 
                               WHERE ps.is_active = TRUE 
                               ORDER BY ps.display_order ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;
        
    case 'POST':
        // Manejar FormData (subida de archivos)
        if (!empty($_FILES)) {
            $input = $_POST;
            
            if ($table == 'products') {
                $image_url = null;
                if (isset($_FILES['image'])) {
                    $image_url = uploadFile($_FILES['image'], $uploadDir);
                }
                
                $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image_url, stock, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['name'], 
                    $input['price'], 
                    $input['description'], 
                    $image_url,
                    $input['stock'] ?? 0,
                    $input['is_featured'] ?? 0
                ]);
                echo json_encode(['id' => $pdo->lastInsertId()]);
                
            } elseif ($table == 'samples') {
                $image_url = null;
                if (isset($_FILES['image'])) {
                    $image_url = uploadFile($_FILES['image'], $uploadDir);
                }
                
                $stmt = $pdo->prepare("INSERT INTO product_samples (product_id, title, description, image_url, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['product_id'], 
                    $input['title'], 
                    $input['description'] ?? '', 
                    $image_url,
                    $input['display_order'] ?? 0
                ]);
                echo json_encode(['id' => $pdo->lastInsertId()]);
            }
        } else {
            // Manejar JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($table == 'products') {
                $stmt = $pdo->prepare("INSERT INTO products (name, price, description, stock, is_featured) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['name'], 
                    $input['price'], 
                    $input['description'], 
                    $input['stock'] ?? 0,
                    $input['is_featured'] ?? 0
                ]);
                echo json_encode(['id' => $pdo->lastInsertId()]);
            } elseif ($table == 'promotions') {
                $stmt = $pdo->prepare("INSERT INTO promotions (name, discount, code, expiry_date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$input['name'], $input['discount'], $input['code'], $input['expiry_date']]);
                echo json_encode(['id' => $pdo->lastInsertId()]);
            } elseif ($table == 'orders') {
                $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, total_amount, customer_notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['customer_name'], 
                    $input['customer_email'], 
                    $input['customer_phone'] ?? null, 
                    $input['total_amount'],
                    $input['customer_notes'] ?? null
                ]);
                $orderId = $pdo->lastInsertId();
                
                // Insertar items del pedido
                if (isset($input['items'])) {
                    foreach ($input['items'] as $item) {
                        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['unit_price']]);
                    }
                }
                
                echo json_encode(['id' => $orderId]);
            } elseif ($table == 'samples') {
                $stmt = $pdo->prepare("INSERT INTO product_samples (product_id, title, description, display_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $input['product_id'], 
                    $input['title'], 
                    $input['description'] ?? '', 
                    $input['display_order'] ?? 0
                ]);
                echo json_encode(['id' => $pdo->lastInsertId()]);
            }
        }
        break;
        
    case 'PUT':
        // Manejar FormData (subida de archivos)
        if (!empty($_FILES)) {
            $input = $_POST;
            
            if ($table == 'products' && $key) {
                // Obtener producto actual para mantener la imagen si no se sube una nueva
                $currentStmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                $currentStmt->execute([$key]);
                $currentProduct = $currentStmt->fetch(PDO::FETCH_ASSOC);
                
                $image_url = $currentProduct['image_url'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_url = uploadFile($_FILES['image'], $uploadDir);
                    // Eliminar imagen anterior si existe
                    if ($currentProduct['image_url'] && file_exists($currentProduct['image_url'])) {
                        unlink($currentProduct['image_url']);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, image_url=?, stock=?, is_featured=? WHERE id=?");
                $stmt->execute([
                    $input['name'], 
                    $input['price'], 
                    $input['description'], 
                    $image_url,
                    $input['stock'] ?? 0,
                    $input['is_featured'] ?? 0,
                    $key
                ]);
                echo json_encode(['success' => true]);
                
            } elseif ($table == 'samples' && $key) {
                // Obtener muestra actual para mantener la imagen si no se sube una nueva
                $currentStmt = $pdo->prepare("SELECT image_url FROM product_samples WHERE id = ?");
                $currentStmt->execute([$key]);
                $currentSample = $currentStmt->fetch(PDO::FETCH_ASSOC);
                
                $image_url = $currentSample['image_url'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image_url = uploadFile($_FILES['image'], $uploadDir);
                    // Eliminar imagen anterior si existe
                    if ($currentSample['image_url'] && file_exists($currentSample['image_url'])) {
                        unlink($currentSample['image_url']);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE product_samples SET product_id=?, title=?, description=?, image_url=?, display_order=? WHERE id=?");
                $stmt->execute([
                    $input['product_id'], 
                    $input['title'], 
                    $input['description'] ?? '', 
                    $image_url,
                    $input['display_order'] ?? 0,
                    $key
                ]);
                echo json_encode(['success' => true]);
            }
        } else {
            // Manejar JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($table == 'products' && $key) {
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, stock=?, is_featured=? WHERE id=?");
                $stmt->execute([
                    $input['name'], 
                    $input['price'], 
                    $input['description'], 
                    $input['stock'] ?? 0,
                    $input['is_featured'] ?? 0,
                    $key
                ]);
                echo json_encode(['success' => true]);
            } elseif ($table == 'site_content' && $key) {
                $stmt = $pdo->prepare("UPDATE site_content SET title=?, content=? WHERE section_name=?");
                $stmt->execute([$input['title'], $input['content'], $key]);
                echo json_encode(['success' => true]);
            } elseif ($table == 'samples' && $key) {
                $stmt = $pdo->prepare("UPDATE product_samples SET product_id=?, title=?, description=?, display_order=? WHERE id=?");
                $stmt->execute([
                    $input['product_id'], 
                    $input['title'], 
                    $input['description'] ?? '', 
                    $input['display_order'] ?? 0,
                    $key
                ]);
                echo json_encode(['success' => true]);
            }
        }
        break;
        
    case 'DELETE':
        if ($table == 'products' && $key) {
            $stmt = $pdo->prepare("UPDATE products SET is_active = FALSE WHERE id = ?");
            $stmt->execute([$key]);
            echo json_encode(['success' => true]);
        } elseif ($table == 'promotions' && $key) {
            $stmt = $pdo->prepare("UPDATE promotions SET is_active = FALSE WHERE id = ?");
            $stmt->execute([$key]);
            echo json_encode(['success' => true]);
        } elseif ($table == 'samples' && $key) {
            // Obtener información de la muestra para eliminar la imagen
            $currentStmt = $pdo->prepare("SELECT image_url FROM product_samples WHERE id = ?");
            $currentStmt->execute([$key]);
            $currentSample = $currentStmt->fetch(PDO::FETCH_ASSOC);
            
            // Eliminar imagen si existe
            if ($currentSample['image_url'] && file_exists($currentSample['image_url'])) {
                unlink($currentSample['image_url']);
            }
            
            $stmt = $pdo->prepare("UPDATE product_samples SET is_active = FALSE WHERE id = ?");
            $stmt->execute([$key]);
            echo json_encode(['success' => true]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Método no soportado']);
        break;
}
?>