<?php
require_once './config.php';

// Get statistics
$stats = [];

// Total products
$result = $conn->query("SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result->fetch_assoc()['total'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result->fetch_assoc()['total'];

// Pending orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result->fetch_assoc()['total'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
$stats['total_customers'] = $result->fetch_assoc()['total'];

// Monthly revenue
$result = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE) AND status != 'cancelled'");
$stats['monthly_revenue'] = $result->fetch_assoc()['total'];

// Active offers
$result = $conn->query("SELECT COUNT(*) as total FROM offers WHERE start_date <= CURRENT_DATE AND end_date >= CURRENT_DATE");
$stats['active_offers'] = $result->fetch_assoc()['total'];

// Recent orders
$recent_orders = $conn->query("
    SELECT o.*, u.first_name, u.last_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Low stock products
$low_stock = $conn->query("
    SELECT * FROM products 
    WHERE stock <= 5 
    ORDER BY stock ASC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Top selling products
$top_products = $conn->query("
    SELECT p.name, p.price, SUM(oi.quantity) as total_sold
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="dashboard">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon products">
                <i class="fas fa-shirt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_products']); ?></h3>
                <p>Produits Total</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p>Commandes Total</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['pending_orders']); ?></h3>
                <p>En Attente</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon customers">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_customers']); ?></h3>
                <p>Clients</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon revenue">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['monthly_revenue'], 2); ?> DA</h3>
                <p>Revenus ce Mois</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon offers">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['active_offers']); ?></h3>
                <p>Offres Actives</p>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Content Grid -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Commandes Récentes</h3>
                <a href="?section=orders" class="view-all">Voir tout</a>
            </div>
            <div class="card-content">
                <?php if (empty($recent_orders)): ?>
                    <p class="no-data">Aucune commande trouvée</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?> DA</td>
                                    <td>
                                        <span class="status status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'En Attente',
                                                'confirmed' => 'Confirmée',
                                                'processing' => 'En Cours',
                                                'shipped' => 'Expédiée',
                                                'delivered' => 'Livrée',
                                                'cancelled' => 'Annulée'
                                            ];
                                            echo $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Stock Faible</h3>
                <a href="?section=products" class="view-all">Gérer</a>
            </div>
            <div class="card-content">
                <?php if (empty($low_stock)): ?>
                    <p class="no-data">Tous les produits ont un stock suffisant</p>
                <?php else: ?>
                    <div class="stock-alerts">
                        <?php foreach ($low_stock as $product): ?>
                        <div class="stock-item <?php echo $product['stock'] == 0 ? 'out-of-stock' : 'low-stock'; ?>">
                            <div class="product-info">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p><?php echo htmlspecialchars($product['category']); ?></p>
                            </div>
                            <div class="stock-count">
                                <span class="stock-number"><?php echo $product['stock']; ?></span>
                                <span class="stock-label">en stock</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3>Produits Populaires</h3>
            </div>
            <div class="card-content">
                <?php if (empty($top_products)): ?>
                    <p class="no-data">Aucune donnée de vente disponible</p>
                <?php else: ?>
                    <div class="top-products">
                        <?php foreach ($top_products as $index => $product): ?>
                        <div class="product-rank">
                            <div class="rank-number"><?php echo $index + 1; ?></div>
                            <div class="product-details">
                                <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p><?php echo number_format($product['price'], 2); ?> DA</p>
                            </div>
                            <div class="sales-count">
                                <span><?php echo $product['total_sold']; ?> vendus</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>