<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'auth.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php');
    exit();
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$section = $_GET['section'] ?? 'dashboard';
$success_message = '';
$error_message = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'delete_user':
            $id = intval($_POST['id']);
            $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            echo json_encode(['success' => $result, 'message' => $result ? 'User deleted successfully' : 'Failed to delete user']);
            break;
            
        case 'delete_product':
            $id = intval($_POST['id']);
            $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            echo json_encode(['success' => $result, 'message' => $result ? 'Product deleted successfully' : 'Failed to delete product']);
            break;
            
        case 'delete_order':
            $id = intval($_POST['id']);
            $mysqli->begin_transaction();
            try {
                $stmt1 = $mysqli->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmt1->bind_param("i", $id);
                $stmt1->execute();
                
                $stmt2 = $mysqli->prepare("DELETE FROM orders WHERE id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                
                $mysqli->commit();
                echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
            } catch (Exception $e) {
                $mysqli->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete order']);
            }
            break;
            
        case 'delete_offer':
            $id = intval($_POST['id']);
            $mysqli->begin_transaction();
            try {
                $stmt1 = $mysqli->prepare("DELETE FROM offer_products WHERE offer_id = ?");
                $stmt1->bind_param("i", $id);
                $stmt1->execute();
                
                $stmt2 = $mysqli->prepare("DELETE FROM offers WHERE id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                
                $mysqli->commit();
                echo json_encode(['success' => true, 'message' => 'Offer deleted successfully']);
            } catch (Exception $e) {
                $mysqli->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete offer']);
            }
            break;
            
        case 'delete_message':
            $id = intval($_POST['id']);
            $stmt = $mysqli->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            echo json_encode(['success' => $result, 'message' => $result ? 'Message deleted successfully' : 'Failed to delete message']);
            break;
    }
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'add_product':
                $name = trim($_POST['name']);
                $price = floatval($_POST['price']);
                $stock = intval($_POST['stock']);
                $category = trim($_POST['category']);
                $color = trim($_POST['color']);
                $size = trim($_POST['size']);
                $description = trim($_POST['description']);
                
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/products/';
                    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image_url = $upload_path;
                    }
                }
                
                $stmt = $mysqli->prepare("INSERT INTO products (name, price, stock, category, color, size, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdisssss", $name, $price, $stock, $category, $color, $size, $description, $image_url);
                
                if ($stmt->execute()) {
                    $success_message = 'Product added successfully';
                } else {
                    $error_message = 'Failed to add product';
                }
                break;
                
            case 'add_offer':
                $name = trim($_POST['offer_name']);
                $discount = floatval($_POST['discount']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $product_ids = $_POST['product_ids'] ?? [];
                
                $mysqli->begin_transaction();
                try {
                    $stmt = $mysqli->prepare("INSERT INTO offers (name, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sdss", $name, $discount, $start_date, $end_date);
                    $stmt->execute();
                    
                    $offer_id = $mysqli->insert_id;
                    
                    if (!empty($product_ids)) {
                        $stmt2 = $mysqli->prepare("INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)");
                        foreach ($product_ids as $product_id) {
                            $stmt2->bind_param("ii", $offer_id, $product_id);
                            $stmt2->execute();
                        }
                    }
                    
                    $mysqli->commit();
                    $success_message = 'Offer added successfully';
                } catch (Exception $e) {
                    $mysqli->rollback();
                    $error_message = 'Failed to add offer';
                }
                break;
                
            case 'update_order_status':
                $order_id = intval($_POST['order_id']);
                $status = trim($_POST['status']);
                
                $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $order_id);
                
                if ($stmt->execute()) {
                    $success_message = 'Order status updated successfully';
                } else {
                    $error_message = 'Failed to update order status';
                }
                break;
        }
    }
    
    // Regenerate CSRF token after form submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get statistics for dashboard
function getStats() {
    global $mysqli;
    
    $stats = [];
    
    // Total users
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    $stats['users'] = $result->fetch_assoc()['count'];
    
    // Total products
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products");
    $stats['products'] = $result->fetch_assoc()['count'];
    
    // Total orders
    $result = $mysqli->query("SELECT COUNT(*) as count FROM orders");
    $stats['orders'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $mysqli->query("SELECT SUM(total_amount) as total FROM orders");
    $stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Recent orders
    $result = $mysqli->query("SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
    $stats['recent_orders'] = $result->fetch_all(MYSQLI_ASSOC);
    
    return $stats;
}

$stats = getStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Suit Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-radius: 8px;
            margin: 5px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h4 class="text-white mb-4"><i class="fas fa-crown"></i> Admin Panel</h4>
                    <nav class="nav flex-column">
                        <a class="nav-link <?= $section === 'dashboard' ? 'active' : '' ?>" href="?section=dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link <?= $section === 'users' ? 'active' : '' ?>" href="?section=users">
                            <i class="fas fa-users me-2"></i> Users
                        </a>
                        <a class="nav-link <?= $section === 'products' ? 'active' : '' ?>" href="?section=products">
                            <i class="fas fa-box me-2"></i> Products
                        </a>
                        <a class="nav-link <?= $section === 'orders' ? 'active' : '' ?>" href="?section=orders">
                            <i class="fas fa-shopping-cart me-2"></i> Orders
                        </a>
                        <a class="nav-link <?= $section === 'offers' ? 'active' : '' ?>" href="?section=offers">
                            <i class="fas fa-tags me-2"></i> Offers
                        </a>
                        <a class="nav-link <?= $section === 'messages' ? 'active' : '' ?>" href="?section=messages">
                            <i class="fas fa-envelope me-2"></i> Messages
                        </a>
                        <hr class="text-white">
                        <a class="nav-link" href="homepage.php">
                            <i class="fas fa-home me-2"></i> Back to Site
                        </a>
                        <a class="nav-link" href="login.php?logout=1">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    switch ($section) {
                        case 'dashboard':
                            include 'admin_sections/dashboard.php';
                            break;
                        case 'users':
                            include 'admin_sections/users.php';
                            break;
                        case 'products':
                            include 'admin_sections/products.php';
                            break;
                        case 'orders':
                            include 'admin_sections/orders.php';
                            break;
                        case 'offers':
                            include 'admin_sections/offers.php';
                            break;
                        case 'messages':
                            include 'admin_sections/messages.php';
                            break;
                        default:
                            include 'admin_sections/dashboard.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteItem(type, id) {
            if (confirm('Are you sure you want to delete this item?')) {
                fetch('admin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ajax=1&action=delete_${type}&id=${id}&csrf_token=<?= $_SESSION['csrf_token'] ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }
    </script>
</body>
</html>