<?php
// Check if session is already started before calling session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    if ($orderId && $newStatus) {
        $stmt = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();
        
        header('Location: ' . $_SERVER['PHP_SELF'] . '?section=' . ($_GET['section'] ?? 'dashboard'));
        exit();
    }
}

// Get current section
$section = $_GET['section'] ?? 'dashboard';
$validSections = ['dashboard', 'articles', 'orders', 'offers', 'customers', 'messages', 'settings'];
if (!in_array($section, $validSections)) {
    $section = 'dashboard';
}

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $mysqli->prepare('SELECT first_name, last_name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Suit Store</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Enhanced Header -->
    <header class="header">
        <div class="header-left">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <img src="../logo.png" alt="Logo" class="logo">
            <h1 class="header-title">Admin Panel</h1>
        </div>
        
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <div class="user-role">Administrator</div>
            </div>
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
            </div>
        </div>
    </header>

    <!-- Enhanced Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Navigation</h2>
            <div class="user-info">
                <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                <small><?php echo htmlspecialchars($user['email']); ?></small>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li><a href="?section=dashboard" class="<?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="?section=articles" class="<?php echo $section === 'articles' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Products
            </a></li>
            <li><a href="?section=orders" class="<?php echo $section === 'orders' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a></li>
            <li><a href="?section=offers" class="<?php echo $section === 'offers' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Offers
            </a></li>
            <li><a href="?section=customers" class="<?php echo $section === 'customers' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Customers
            </a></li>
            <li><a href="?section=messages" class="<?php echo $section === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Messages
            </a></li>
            <li><a href="?section=settings" class="<?php echo $section === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a></li>
            <li><a href="../auth.php?action=logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>
    </nav>
    
    <!-- Enhanced Main Content -->
    <div class="admin-container">
        <main class="main-content">
            <?php
            switch ($section) {
                case 'dashboard':
                    include 'dashboard.php';
                    break;
                case 'articles':
                    include 'articles.php';
                    break;
                case 'orders':
                    include 'orders.php';
                    break;
                case 'offers':
                    include 'offers.php';
                    break;
                case 'customers':
                    echo '<div class="admin-section">';
                    echo '<h1 class="section-title">Customer Management</h1>';
                    echo '<p>Customer management functionality coming soon...</p>';
                    echo '</div>';
                    break;
                case 'messages':
                    echo '<div class="admin-section">';
                    echo '<h1 class="section-title">Messages</h1>';
                    echo '<p>Message management functionality coming soon...</p>';
                    echo '</div>';
                    break;
                case 'settings':
                    echo '<div class="admin-section">';
                    echo '<h1 class="section-title">Settings</h1>';
                    echo '<p>Settings management functionality coming soon...</p>';
                    echo '</div>';
                    break;
                default:
                    include 'dashboard.php';
            }
            ?>
        </main>
    </div>

    <script src="admin.js"></script>
</body>
</html>