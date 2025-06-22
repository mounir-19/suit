<?php
require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle offer actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    
    if ($action === 'add') {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $discount = filter_input(INPUT_POST, 'discount_percentage', FILTER_VALIDATE_FLOAT);
        $startDate = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
        $productIds = $_POST['product_ids'] ?? [];
        
        if ($name && $discount && $startDate && $endDate) {
            try {
                $mysqli->autocommit(FALSE);
                
                // Insert offer
                $stmt = $mysqli->prepare("INSERT INTO offers (name, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sdss', $name, $discount, $startDate, $endDate);
                $stmt->execute();
                $offer_id = $mysqli->insert_id;
                $stmt->close();
                
                // Link products to offer
                if (!empty($productIds)) {
                    $stmt = $mysqli->prepare("INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)");
                    foreach ($productIds as $productId) {
                        $productId = (int)$productId;
                        if ($productId > 0) {
                            $stmt->bind_param('ii', $offer_id, $productId);
                            $stmt->execute();
                        }
                    }
                    $stmt->close();
                }
                
                $mysqli->commit();
                $mysqli->autocommit(TRUE);
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $mysqli->rollback();
                $mysqli->autocommit(TRUE);
                $error = "Error creating offer: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'delete') {
        $offerId = filter_input(INPUT_POST, 'offer_id', FILTER_VALIDATE_INT);
        
        if ($offerId) {
            try {
                $mysqli->autocommit(FALSE);
                
                // Delete offer products first
                $stmt = $mysqli->prepare("DELETE FROM offer_products WHERE offer_id = ?");
                $stmt->bind_param('i', $offerId);
                $stmt->execute();
                $stmt->close();
                
                // Delete offer
                $stmt = $mysqli->prepare("DELETE FROM offers WHERE id = ?");
                $stmt->bind_param('i', $offerId);
                $stmt->execute();
                $stmt->close();
                
                $mysqli->commit();
                $mysqli->autocommit(TRUE);
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $mysqli->rollback();
                $mysqli->autocommit(TRUE);
                $error = "Error deleting offer: " . $e->getMessage();
            }
        }
    }
}

// Get all offers
$result = $mysqli->query("SELECT * FROM offers ORDER BY created_at DESC");
$offers = $result->fetch_all(MYSQLI_ASSOC);

// Get all products for the form
$result = $mysqli->query("SELECT id, name, price FROM products ORDER BY name");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="offers-management">
    <div class="page-header">
        <h1>Offers Management</h1>
        <button class="btn btn-primary" onclick="openOfferModal()">Add New Offer</button>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="offers-grid">
        <?php if (empty($offers)): ?>
            <div class="no-data">No offers found</div>
        <?php else: ?>
            <?php foreach ($offers as $offer): ?>
            <div class="offer-card">
                <div class="offer-header">
                    <h3><?php echo htmlspecialchars($offer['name']); ?></h3>
                    <span class="discount"><?php echo $offer['discount_percentage']; ?>% OFF</span>
                </div>
                
                <div class="offer-dates">
                    <div>Start: <?php echo date('M j, Y', strtotime($offer['start_date'])); ?></div>
                    <div>End: <?php echo date('M j, Y', strtotime($offer['end_date'])); ?></div>
                </div>
                
                <div class="offer-status">
                    <?php
                    $now = date('Y-m-d');
                    if ($now < $offer['start_date']) {
                        echo '<span class="status upcoming">Upcoming</span>';
                    } elseif ($now > $offer['end_date']) {
                        echo '<span class="status expired">Expired</span>';
                    } else {
                        echo '<span class="status active">Active</span>';
                    }
                    ?>
                </div>
                
                <div class="offer-products">
                    <strong>Products:</strong>
                    <?php
                    $stmt = $mysqli->prepare("SELECT p.name FROM products p JOIN offer_products op ON p.id = op.product_id WHERE op.offer_id = ?");
                    $stmt->bind_param('i', $offer['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $offer_products = [];
                    while ($row = $result->fetch_assoc()) {
                        $offer_products[] = $row['name'];
                    }
                    $stmt->close();
                    echo htmlspecialchars(implode(', ', $offer_products));
                    ?>
                </div>
                
                <div class="offer-actions">
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this offer?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="offer_id" value="<?php echo $offer['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Offer Modal -->
<div id="offerModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeOfferModal()">&times;</span>
        <h2>Add New Offer</h2>
        
        <form method="POST" class="offer-form">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="name">Offer Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="discount_percentage">Discount Percentage:</label>
                <input type="number" id="discount_percentage" name="discount_percentage" min="1" max="100" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required>
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" required>
            </div>
            
            <div class="form-group">
                <label>Select Products:</label>
                <div class="products-checkbox-list">
                    <?php foreach ($products as $product): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>">
                        <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['price'], 2); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Offer</button>
                <button type="button" class="btn btn-secondary" onclick="closeOfferModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openOfferModal() {
    document.getElementById('offerModal').style.display = 'block';
}

function closeOfferModal() {
    document.getElementById('offerModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('offerModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>