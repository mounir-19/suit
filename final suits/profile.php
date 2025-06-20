<?php
require_once 'auth_check.php';
require_once 'config.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - MSH-ISTANBOUL</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <div class="profile-container">
        <h1>My Profile</h1>
        <div class="user-info">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        
        <div class="orders">
            <h2>My Orders</h2>
            <?php if (empty($orders)): ?>
                <p>No orders yet</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="order">
                    <h3>Order #<?php echo $order['id']; ?></h3>
                    <p>Date: <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></p>
                    <p>Total: <?php echo number_format($order['total_amount'], 2); ?> DA</p>
                    <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>