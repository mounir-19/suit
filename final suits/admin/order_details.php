<?php
require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Get order details
function getOrderDetails($orderId) {
    global $mysqli;
    
    // Get order information
    $stmt = $mysqli->prepare(
        'SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?'
    );
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        return null;
    }
    
    // Get order items
    $stmt = $mysqli->prepare(
        'SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?'
    );
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get shipping address
    $stmt = $mysqli->prepare('SELECT * FROM shipping_addresses WHERE order_id = ?');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $shipping = $result->fetch_assoc();
    $stmt->close();
    
    return [
        'order' => $order,
        'items' => $items,
        'shipping' => $shipping
    ];
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    
    if ($orderId && $newStatus) {
        $stmt = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();
        
        // Redirect to refresh the page
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $orderId);
        exit();
    }
}

// Get order ID from URL
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: admin.php?section=orders');
    exit();
}

$orderDetails = getOrderDetails($orderId);
if (!$orderDetails) {
    header('Location: admin.php?section=orders');
    exit();
}
?>

<div class="order-details">
    <div class="order-header">
        <h1>Order #<?php echo $orderId; ?></h1>
        <div class="order-status">
            <form method="POST" class="status-form">
                <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
                <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo $orderDetails['order']['status']; ?>">
                    <?php
                    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
                    foreach ($statuses as $status) {
                        $selected = $status === $orderDetails['order']['status'] ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>" . ucfirst($status) . "</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="update_status" value="1">
            </form>
        </div>
    </div>
    
    <div class="order-grid">
        <!-- Customer Information -->
        <div class="order-section">
            <h2>Customer Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Name:</label>
                    <span><?php echo htmlspecialchars($orderDetails['order']['first_name'] . ' ' . $orderDetails['order']['last_name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($orderDetails['order']['email']); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($orderDetails['order']['phone']); ?></span>
                </div>
                <div class="info-item">
                    <label>Order Date:</label>
                    <span><?php echo date('M d, Y H:i', strtotime($orderDetails['order']['order_date'])); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="order-section">
            <h2>Shipping Address</h2>
            <div class="address-details">
                <p><?php echo htmlspecialchars($orderDetails['shipping']['address_line1']); ?></p>
                <?php if (!empty($orderDetails['shipping']['address_line2'])): ?>
                    <p><?php echo htmlspecialchars($orderDetails['shipping']['address_line2']); ?></p>
                <?php endif; ?>
                <p>
                    <?php echo htmlspecialchars($orderDetails['shipping']['city']); ?>,
                    <?php echo htmlspecialchars($orderDetails['shipping']['state']); ?>
                    <?php echo htmlspecialchars($orderDetails['shipping']['postal_code']); ?>
                </p>
                <p><?php echo htmlspecialchars($orderDetails['shipping']['country']); ?></p>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="order-section full-width">
            <h2>Order Items</h2>
            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails['items'] as $item): ?>
                        <tr>
                            <td class="product-cell">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Subtotal</td>
                            <td>$<?php echo number_format($orderDetails['order']['subtotal'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3">Shipping</td>
                            <td>$<?php echo number_format($orderDetails['order']['shipping_cost'], 2); ?></td>
                        </tr>
                        <?php if ($orderDetails['order']['discount'] > 0): ?>
                        <tr>
                            <td colspan="3">Discount</td>
                            <td>-$<?php echo number_format($orderDetails['order']['discount'], 2); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td colspan="3">Total</td>
                            <td>$<?php echo number_format($orderDetails['order']['total_amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Order Details Styles */
.order-details {
    padding: 2rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.order-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
}

.order-section {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 2px 4px var(--shadow-color);
}

.order-section.full-width {
    grid-column: 1 / -1;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-weight: 600;
    color: var(--text-light);
    margin-bottom: 0.25rem;
}

.address-details p {
    margin: 0.5rem 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.product-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.product-cell img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 0.25rem;
}

.total-row {
    font-weight: 600;
    font-size: 1.1rem;
}

.status-select {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    border: 1px solid var(--border-color);
    font-size: 0.875rem;
    cursor: pointer;
}

.status-select.status-pending { color: var(--warning-color); }
.status-select.status-confirmed { color: var(--info-color); }
.status-select.status-processing { color: var(--primary-color); }
.status-select.status-shipped { color: var(--success-color); }
.status-select.status-delivered { color: var(--success-dark-color); }
.status-select.status-cancelled { color: var(--danger-color); }

@media (max-width: 768px) {
    .order-details {
        padding: 1rem;
    }
    
    .order-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .items-table {
        font-size: 0.875rem;
    }
    
    .product-cell img {
        width: 40px;
        height: 40px;
    }
}
</style>