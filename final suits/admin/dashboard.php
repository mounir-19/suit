<?php
// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

function getDashboardStats() {
    global $mysqli;
    
    $stats = [];
    
    try {
        // Total products
        $result = $mysqli->query('SELECT COUNT(*) as count FROM products');
        $stats['total_products'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Pending orders
        $stmt = $mysqli->prepare('SELECT COUNT(*) as count FROM orders WHERE status = ?');
        $status = 'pending';
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['pending_orders'] = $result->fetch_assoc()['count'] ?? 0;
        $stmt->close();
        
        // Active offers
        $result = $mysqli->query('SELECT COUNT(*) as count FROM offers WHERE end_date >= CURRENT_DATE');
        $stats['active_offers'] = $result ? $result->fetch_assoc()['count'] ?? 0 : 0;
        
        // Monthly sales
        $stmt = $mysqli->prepare(
            'SELECT COALESCE(SUM(total_amount), 0) as sales 
            FROM orders 
            WHERE MONTH(order_date) = MONTH(CURRENT_DATE) 
            AND YEAR(order_date) = YEAR(CURRENT_DATE)
            AND status != "cancelled"'
        );
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['monthly_sales'] = $result->fetch_assoc()['sales'] ?? 0;
        $stmt->close();
        
        // Recent orders
        $result = $mysqli->query(
            'SELECT o.id, o.total_amount, o.status, o.order_date, u.first_name, u.last_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.order_date DESC 
            LIMIT 5'
        );
        $stats['recent_orders'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
        // Top selling products
        $result = $mysqli->query(
            'SELECT p.name, SUM(oi.quantity) as total_sold 
            FROM products p 
            JOIN order_items oi ON p.id = oi.product_id 
            JOIN orders o ON oi.order_id = o.id 
            WHERE o.status != "cancelled" 
            GROUP BY p.id, p.name 
            ORDER BY total_sold DESC 
            LIMIT 5'
        );
        $stats['top_products'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        
    } catch (Exception $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
        // Return default values on error
        $stats = [
            'total_products' => 0,
            'pending_orders' => 0,
            'active_offers' => 0,
            'monthly_sales' => 0,
            'recent_orders' => [],
            'top_products' => []
        ];
    }
    
    return $stats;
}


// Dashboard content is included in admin.php
if (!isset($stats)) {
    $stats = getDashboardStats();
}
?>

<div class="admin-section">
    <h1 class="section-title">Dashboard Overview</h1>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="?section=articles" class="quick-action">
            <div class="quick-action-icon"><i class="fas fa-plus"></i></div>
            <div class="quick-action-title">Add Product</div>
            <div class="quick-action-desc">Create new product</div>
        </a>
        <a href="?section=orders" class="quick-action">
            <div class="quick-action-icon"><i class="fas fa-eye"></i></div>
            <div class="quick-action-title">View Orders</div>
            <div class="quick-action-desc">Manage orders</div>
        </a>
        <a href="?section=offers" class="quick-action">
            <div class="quick-action-icon"><i class="fas fa-percentage"></i></div>
            <div class="quick-action-title">Create Offer</div>
            <div class="quick-action-desc">Add new promotion</div>
        </a>
        <a href="?section=customers" class="quick-action">
            <div class="quick-action-icon"><i class="fas fa-users"></i></div>
            <div class="quick-action-title">Customers</div>
            <div class="quick-action-desc">View customer list</div>
        </a>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <div class="stat-number"><?php echo number_format($stats['total_products']); ?></div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +12% from last month
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Pending Orders</h3>
            <div class="stat-number"><?php echo number_format($stats['pending_orders']); ?></div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i> -5% from last week
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Active Offers</h3>
            <div class="stat-number"><?php echo number_format($stats['active_offers']); ?></div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +2 new offers
            </div>
        </div>
        
        <div class="stat-card">
            <h3>Monthly Sales</h3>
            <div class="stat-number">$<?php echo number_format($stats['monthly_sales'], 2); ?></div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> +18% from last month
            </div>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Recent Orders</h3>
            <?php if (empty($stats['recent_orders'])): ?>
                <p class="text-muted">No recent orders found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_orders'] as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-card">
            <h3>Top Selling Products</h3>
            <?php if (empty($stats['top_products'])): ?>
                <p class="text-muted">No sales data available.</p>
            <?php else: ?>
                <div class="top-products-list">
                    <?php foreach ($stats['top_products'] as $index => $product): ?>
                    <div class="top-product-item">
                        <div class="product-rank"><?php echo $index + 1; ?></div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-sales"><?php echo $product['total_sold']; ?> sold</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.top-products-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.top-product-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--background-color);
    border-radius: var(--border-radius);
}

.product-rank {
    width: 2rem;
    height: 2rem;
    background: var(--primary-color);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 500;
    color: var(--text-color);
    margin-bottom: 0.25rem;
}

.product-sales {
    font-size: 0.75rem;
    color: var(--text-light);
}

.text-muted {
    color: var(--text-light);
    font-style: italic;
}
</style>