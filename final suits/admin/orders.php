<?php
require_once './config.php';
require_once './auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $order_id = filter_var($_POST['order_id'] ?? 0, FILTER_VALIDATE_INT);
        $new_status = htmlspecialchars($_POST['status'] ?? '');
        
        $valid_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if ($order_id && in_array($new_status, $valid_statuses)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $order_id);
            
            if ($stmt->execute()) {
                $success_message = "Statut de la commande mis à jour avec succès!";
            } else {
                $error_message = "Erreur lors de la mise à jour du statut.";
            }
        } else {
            $error_message = "Données invalides.";
        }
    }
}

// Fetch all orders with customer details
$query = "SELECT o.*, u.email, u.first_name, u.last_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC";
$result = $conn->query($query);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<div id="orders-section" class="admin-section">
    <h2 class="section-title">Gestion des Commandes</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Email</th>
                <th>Date</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#CMD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                    <td><?php echo number_format($order['total_amount'], 2); ?> DA</td>
                    <td>
                        <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php 
                            $status_labels = [
                                'pending' => 'En Attente',
                                'confirmed' => 'Confirmée',
                                'shipped' => 'Expédiée',
                                'delivered' => 'Livrée',
                                'cancelled' => 'Annulée'
                            ];
                            echo $status_labels[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" action="?section=orders" style="display: inline;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <?php foreach ($status_labels as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $order['status'] === $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        
                        <button type="button" class="btn btn-info" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">Détails</button>
                    </td>
                </tr>
                
                <!-- Order Details Row (hidden by default) -->
                <tr id="order-details-<?php echo $order['id']; ?>" style="display: none;">
                    <td colspan="7">
                        <div class="order-details">
                            <h4>Détails de la Commande #CMD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></h4>
                            <table class="inner-table">
                                <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th>Prix Unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $items_query = "SELECT oi.*, p.name 
                                                   FROM order_items oi 
                                                   JOIN products p ON oi.product_id = p.id 
                                                   WHERE oi.order_id = ?";
                                    $stmt = $conn->prepare($items_query);
                                    $stmt->bind_param("i", $order['id']);
                                    $stmt->execute();
                                    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                    
                                    foreach ($items as $item):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo number_format($item['price'], 2); ?> DA</td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> DA</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function viewOrderDetails(orderId) {
    const detailsRow = document.getElementById(`order-details-${orderId}`);
    if (detailsRow) {
        detailsRow.style.display = detailsRow.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>