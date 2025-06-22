<?php
// Check if session is already started before calling session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../auth.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Handle API requests
try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_dashboard':
            handleGetDashboard();
            break;
            
        case 'get_articles':
            handleGetArticles();
            break;
            
        case 'get_article':
            handleGetArticle();
            break;
            
        case 'add_article':
            handleAddArticle();
            break;
            
        case 'update_article':
            handleUpdateArticle();
            break;
            
        case 'delete_article':
            handleDeleteArticle();
            break;
            
        case 'get_orders':
            handleGetOrders();
            break;
            
        case 'get_order_details':
            handleGetOrderDetails();
            break;
            
        case 'update_order_status':
            handleUpdateOrderStatus();
            break;
            
        case 'get_offers':
            handleGetOffers();
            break;
            
        case 'get_customers':
            handleGetCustomers();
            break;
            
        case 'get_messages':
            handleGetMessages();
            break;
            
        case 'get_settings':
            handleGetSettings();
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
} catch (Exception $e) {
    error_log("Admin API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Dashboard functions
function handleGetDashboard() {
    global $mysqli;
    
    try {
        // Get total products
        $result = $mysqli->query('SELECT COUNT(*) as count FROM products');
        $totalProducts = $result->fetch_assoc()['count'] ?? 0;
        
        // Get pending orders
        $stmt = $mysqli->prepare('SELECT COUNT(*) as count FROM orders WHERE status = ?');
        $status = 'pending';
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingOrders = $result->fetch_assoc()['count'] ?? 0;
        $stmt->close();
        
        // Get active offers (if offers table exists)
        try {
            $result = $mysqli->query('SELECT COUNT(*) as count FROM offers WHERE end_date >= CURRENT_DATE');
            $activeOffers = $result ? $result->fetch_assoc()['count'] ?? 0 : 0;
        } catch (Exception $e) {
            $activeOffers = 0; // Table might not exist
        }
        
        // Get monthly sales
        $stmt = $mysqli->prepare(
            'SELECT COALESCE(SUM(total_amount), 0) as sales 
            FROM orders 
            WHERE MONTH(order_date) = MONTH(CURRENT_DATE) 
            AND YEAR(order_date) = YEAR(CURRENT_DATE)
            AND status != "cancelled"'
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $monthlySales = $result->fetch_assoc()['sales'] ?? 0;
        $stmt->close();
        
        echo json_encode([
            'total_products' => $totalProducts,
            'pending_orders' => $pendingOrders,
            'active_offers' => $activeOffers,
            'monthly_sales' => $monthlySales
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to get dashboard data: ' . $e->getMessage());
    }
}

// Article management functions
function handleGetArticles() {
    global $mysqli;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    try {
        // Get total count
        $result = $mysqli->query('SELECT COUNT(*) as count FROM products');
        $total = $result->fetch_assoc()['count'];
        
        // Get articles
        $stmt = $mysqli->prepare('SELECT * FROM products ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bind_param('ii', $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Generate pagination
        $totalPages = ceil($total / $perPage);
        $pagination = generatePagination($page, $totalPages);
        
        echo json_encode([
            'articles' => $articles,
            'pagination' => $pagination,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to get articles: ' . $e->getMessage());
    }
}

function handleGetArticle() {
    global $mysqli;
    
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid article ID');
    }
    
    try {
        $stmt = $mysqli->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $article = $result->fetch_assoc();
        $stmt->close();
        
        if (!$article) {
            http_response_code(404);
            echo json_encode(['error' => 'Article not found']);
            return;
        }
        
        echo json_encode($article);
    } catch (Exception $e) {
        throw new Exception('Failed to get article: ' . $e->getMessage());
    }
}

function handleAddArticle() {
    global $pdo;
    
    try {
        // Validate and sanitize input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        
        if (empty($name) || empty($description) || $price <= 0) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = handleImageUpload($_FILES['image']);
        }
        
        // Insert article
        $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock, category, image_url) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $description, $price, $stock, $category, $imagePath]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Article added successfully',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to add article: ' . $e->getMessage());
    }
}

function handleUpdateArticle() {
    global $pdo;
    
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid article ID');
    }
    
    try {
        // Validate and sanitize input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        
        if (empty($name) || empty($description) || $price <= 0) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Handle image upload if new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = handleImageUpload($_FILES['image']);
            
            // Update with new image
            $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, image_url = ? WHERE id = ?');
            $stmt->execute([$name, $description, $price, $stock, $category, $imagePath, $id]);
        } else {
            // Update without changing image
            $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?');
            $stmt->execute([$name, $description, $price, $stock, $category, $id]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Article updated successfully'
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to update article: ' . $e->getMessage());
    }
}

function handleDeleteArticle() {
    global $pdo;
    
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid article ID');
    }
    
    try {
        // Get article image to delete file
        $stmt = $pdo->prepare('SELECT image_url FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete article
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($article && $article['image_url'] && file_exists($article['image_url'])) {
            unlink($article['image_url']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Article deleted successfully'
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to delete article: ' . $e->getMessage());
    }
}

// Order management functions
function handleGetOrders() {
    global $pdo;
    
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    try {
        // Get total count
        $stmt = $pdo->query('SELECT COUNT(*) FROM orders');
        $total = $stmt->fetchColumn();
        
        // Get orders with user information
        $stmt = $pdo->prepare(
            'SELECT o.*, u.first_name, u.last_name, u.email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.order_date DESC 
            LIMIT ? OFFSET ?'
        );
        $stmt->execute([$perPage, $offset]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate pagination
        $totalPages = ceil($total / $perPage);
        $pagination = generatePagination($page, $totalPages);
        
        echo json_encode([
            'orders' => $orders,
            'pagination' => $pagination,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to get orders: ' . $e->getMessage());
    }
}

function handleGetOrderDetails() {
    global $pdo;
    
    $orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
    if (!$orderId) {
        throw new Exception('Invalid order ID');
    }
    
    try {
        // Get order details
        $stmt = $pdo->prepare(
            'SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?'
        );
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            return;
        }
        
        // Get order items
        $stmt = $pdo->prepare(
            'SELECT oi.*, p.name, p.image_url 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?'
        );
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $order['items'] = $items;
        $order['subtotal'] = $order['total_amount'] - ($order['shipping_cost'] ?? 0);
        
        echo json_encode($order);
    } catch (Exception $e) {
        throw new Exception('Failed to get order details: ' . $e->getMessage());
    }
}

function handleUpdateOrderStatus() {
    global $pdo;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['order_id'] ?? null;
    $status = $input['status'] ?? null;
    
    if (!$orderId || !in_array($status, ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])) {
        throw new Exception('Invalid order ID or status');
    }
    
    try {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $orderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to update order status: ' . $e->getMessage());
    }
}

// Other section handlers
function handleGetOffers() {
    echo json_encode([
        'offers' => [],
        'message' => 'Offers section - coming soon'
    ]);
}

function handleGetCustomers() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT id, first_name, last_name, email, created_at FROM users WHERE role != "admin" ORDER BY created_at DESC LIMIT 50');
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'customers' => $customers
        ]);
    } catch (Exception $e) {
        throw new Exception('Failed to get customers: ' . $e->getMessage());
    }
}

function handleGetMessages() {
    echo json_encode([
        'messages' => [],
        'message' => 'Messages section - coming soon'
    ]);
}

function handleGetSettings() {
    echo json_encode([
        'settings' => [],
        'message' => 'Settings section - coming soon'
    ]);
}

// Helper functions
function handleImageUpload($file) {
    $uploadDir = '../uploads/products/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }
    
    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file.');
    }
    
    return 'uploads/products/' . $filename;
}

function generatePagination($currentPage, $totalPages) {
    $pagination = '';
    
    if ($totalPages <= 1) {
        return $pagination;
    }
    
    $pagination .= '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<a href="#" data-page="' . ($currentPage - 1) . '">Previous</a>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = ($i == $currentPage) ? 'class="active"' : '';
        $pagination .= '<a href="#" data-page="' . $i . '" ' . $active . '>' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<a href="#" data-page="' . ($currentPage + 1) . '">Next</a>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}
?>