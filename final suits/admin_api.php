<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_order_details':
        $order_id = intval($_GET['id']);
        
        // Get order details
        $order_query = "
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ";
        $stmt = $mysqli->prepare($order_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit();
        }
        
        // Get order items
        $items_query = "
            SELECT oi.*, p.name, p.image_url
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ";
        $stmt = $mysqli->prepare($items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Generate HTML
        $html = '<div class="order-details">';
        $html .= '<h6>Order Information</h6>';
        $html .= '<p><strong>Order ID:</strong> #' . $order['id'] . '</p>';
        $html .= '<p><strong>Customer:</strong> ' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '</p>';
        $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>';
        $html .= '<p><strong>Status:</strong> <span class="badge bg-info">' . ucfirst($order['status']) . '</span></p>';
        $html .= '<p><strong>Total Amount:</strong> ' . number_format($order['total_amount'], 2) . ' DA</p>';
        $html .= '<p><strong>Order Date:</strong> ' . date('M j, Y g:i A', strtotime($order['created_at'])) . '</p>';
        
        $html .= '<hr><h6>Order Items</h6>';
        $html .= '<div class="table-responsive">';
        $html .= '<table class="table table-sm">';
        $html .= '<thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['name']) . '</td>';
            $html .= '<td>' . $item['quantity'] . '</td>';
            $html .= '<td>' . number_format($item['price'], 2) . ' DA</td>';
            $html .= '<td>' . number_format($item['price'] * $item['quantity'], 2) . ' DA</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></div></div>';
        
        echo json_encode(['success' => true, 'html' => $html]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>