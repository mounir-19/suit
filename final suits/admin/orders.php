<?php
require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Function to get orders with pagination and filters
function getOrders($page = 1, $perPage = 10, $filters = []) {
    global $mysqli;
    
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($filters['status'])) {
        $where[] = 'o.status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }
    
    if (!empty($filters['search'])) {
        $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)';
        $searchTerm = "%{$filters['search']}%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $types .= 'sss';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as count FROM orders o JOIN users u ON o.user_id = u.id $whereClause";
    if (!empty($params)) {
        $stmt = $mysqli->prepare($countQuery);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['count'];
        $stmt->close();
    } else {
        $result = $mysqli->query($countQuery);
        $total = $result->fetch_assoc()['count'];
    }
    
    // Get orders
    $query = "SELECT o.*, u.first_name, u.last_name, u.email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              $whereClause 
              ORDER BY o.order_date DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return [
        'orders' => $orders,
        'total' => $total,
        'pages' => ceil($total / $perPage)
    ];
}

// Get current page and filters
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$filters = [
    'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING),
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING)
];

$result = getOrders($currentPage, 10, $filters);
?>

<div class="orders-management">
    <div class="page-header">
        <h1>Orders Management</h1>
    </div>
    
    <div class="filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="section" value="orders">
            <input type="text" name="search" placeholder="Search by customer name or email..." 
                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            
            <select name="status">
                <option value="">All Statuses</option>
                <?php
                $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
                foreach ($statuses as $status) {
                    $selected = ($filters['status'] === $status) ? 'selected' : '';
                    echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
                }
                ?>
            </select>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if (!empty($filters['status']) || !empty($filters['search'])): ?>
                <a href="?section=orders" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="orders-table-container">
        <?php if (empty($result['orders'])): ?>
            <div class="no-data">No orders found</div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['orders'] as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <form method="POST" class="status-form" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo $order['status']; ?>">
                                    <?php
                                    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
                                    foreach ($statuses as $status) {
                                        $selected = $status === $order['status'] ? 'selected' : '';
                                        echo "<option value='$status' $selected>" . ucfirst($status) . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="update_order_status" value="1">
                            </form>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                        <td>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php if ($result['pages'] > 1): ?>
    <div class="pagination">
        <?php
        $baseUrl = '?section=orders';
        if (!empty($filters['status'])) $baseUrl .= '&status=' . urlencode($filters['status']);
        if (!empty($filters['search'])) $baseUrl .= '&search=' . urlencode($filters['search']);
        
        if ($currentPage > 1): ?>
            <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage - 1; ?>" class="btn btn-secondary">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $currentPage - 2); $i <= min($result['pages'], $currentPage + 2); $i++): ?>
            <?php if ($i == $currentPage): ?>
                <span class="btn btn-primary current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo $baseUrl; ?>&page=<?php echo $i; ?>" class="btn btn-secondary"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($currentPage < $result['pages']): ?>
            <a href="<?php echo $baseUrl; ?>&page=<?php echo $currentPage + 1; ?>" class="btn btn-secondary">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<style>
/* Orders Management Styles */
.orders-management {
    padding: 2rem;
}

.page-header {
    margin-bottom: 2rem;
}

.filter-form {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.filter-form input,
.filter-form select {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px var(--shadow-color);
}

.orders-table th,
.orders-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.orders-table th {
    background-color: var(--background-color);
    font-weight: 600;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-email {
    font-size: 0.875rem;
    color: var(--text-light);
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.status-pending { background: var(--warning-light); color: var(--warning-color); }
.status-badge.status-confirmed { background: var(--info-light); color: var(--info-color); }
.status-badge.status-processing { background: var(--primary-light); color: var(--primary-color); }
.status-badge.status-shipped { background: var(--success-light); color: var(--success-color); }
.status-badge.status-delivered { background: var(--success-dark-light); color: var(--success-dark-color); }
.status-badge.status-cancelled { background: var(--danger-light); color: var(--danger-color); }

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.25rem;
    background: white;
    color: var(--text-color);
    text-decoration: none;
    transition: all var(--transition-speed);
}

.page-link:hover {
    background: var(--primary-light);
    color: var(--primary-color);
}

.page-link.active {
    background: var(--primary-color);
    color: white;
}

.no-data {
    text-align: center;
    padding: 2rem;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .orders-management {
        padding: 1rem;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .orders-table {
        font-size: 0.875rem;
    }
    
    .customer-info {
        min-width: 200px;
    }
    
    .actions {
        flex-direction: column;
    }
}
</style>