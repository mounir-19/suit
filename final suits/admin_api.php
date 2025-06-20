<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

function handleImageUpload($productId) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $uploadDir = 'uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        return false;
    }

    $fileName = 'product_' . $productId . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
        global $conn;
        $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $stmt->bind_param("si", $filePath, $productId);
        return $stmt->execute();
    }

    return false;
}

switch($action) {
    case 'get_products':
        $result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
        $products = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $products]);
        break;
        
    case 'add_product':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $category = $_POST['category'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO products (name, price, stock, category, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiss", $name, $price, $stock, $category, $description);
            
            if ($stmt->execute()) {
                $productId = $conn->insert_id;
                $imageUploaded = handleImageUpload($productId);
                
                echo json_encode([
                    'success' => true, 
                    'id' => $productId,
                    'image_uploaded' => $imageUploaded
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        }
        break;
        
    case 'update_product':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $price = $_POST['price'] ?? 0;
            $stock = $_POST['stock'] ?? 0;
            $category = $_POST['category'] ?? '';
            $description = $_POST['description'] ?? '';
            
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=? WHERE id=?");
            $stmt->bind_param("sdissi", $name, $price, $stock, $category, $description, $id);
            
            $success = $stmt->execute();
            $imageUploaded = false;
            
            if ($success && isset($_FILES['image'])) {
                $imageUploaded = handleImageUpload($id);
            }
            
            echo json_encode(['success' => $success, 'image_uploaded' => $imageUploaded]);
        }
        break;
        
    case 'delete_product':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            
            // First, get the image path
            $stmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            
            // Delete the product
            $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            
            // If successful and image exists, delete the image file
            if ($success && $product && $product['image_url'] && file_exists($product['image_url'])) {
                unlink($product['image_url']);
            }
            
            echo json_encode(['success' => $success]);
        }
        break;
        
    case 'get_orders':
        $result = $conn->query("SELECT o.*, u.email, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        
        // Get order items for each order
        foreach ($orders as &$order) {
            $stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt->bind_param("i", $order['id']);
            $stmt->execute();
            $order['items'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        
        echo json_encode(['success' => true, 'data' => $orders]);
        break;
        
    case 'update_order_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $status = $data['status'] ?? '';
            
            $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
            $stmt->bind_param("si", $status, $id);
            
            echo json_encode(['success' => $stmt->execute()]);
        }
        break;
        
    case 'get_dashboard_stats':
        // Get total products
        $result = $conn->query("SELECT COUNT(*) as total FROM products");
        $totalProducts = $result->fetch_assoc()['total'];
        
        // Get pending orders
        $result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
        $pendingOrders = $result->fetch_assoc()['total'];
        
        // Get active offers
        $result = $conn->query("SELECT COUNT(*) as total FROM offers WHERE end_date >= CURRENT_DATE");
        $activeOffers = $result->fetch_assoc()['total'];
        
        // Get monthly sales
        $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)");
        $monthlySales = $result->fetch_assoc()['total'] ?? 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'pending_orders' => $pendingOrders,
                'active_offers' => $activeOffers,
                'monthly_sales' => $monthlySales
            ]
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>