<?php
require_once './config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $discount = $_POST['discount'] ?? 0;
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                $products = $_POST['products'] ?? [];
                
                $stmt = $conn->prepare("INSERT INTO offers (name, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdss", $name, $discount, $start_date, $end_date);
                
                if ($stmt->execute()) {
                    $offer_id = $conn->insert_id;
                    
                    // Link products to the offer
                    if (!empty($products)) {
                        $stmt = $conn->prepare("INSERT INTO offer_products (offer_id, product_id) VALUES (?, ?)");
                        foreach ($products as $product_id) {
                            $stmt->bind_param("ii", $offer_id, $product_id);
                            $stmt->execute();
                        }
                    }
                    
                    $success_message = "Offre ajoutée avec succès!";
                } else {
                    $error_message = "Erreur lors de l'ajout de l'offre.";
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                
                // First delete from offer_products
                $stmt = $conn->prepare("DELETE FROM offer_products WHERE offer_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Then delete the offer
                $stmt = $conn->prepare("DELETE FROM offers WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success_message = "Offre supprimée avec succès!";
                } else {
                    $error_message = "Erreur lors de la suppression de l'offre.";
                }
                break;
        }
    }
}

// Fetch all offers
$offers = $conn->query("SELECT * FROM offers ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch all products for the form
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<div id="offers-section" class="admin-section">
    <h2 class="section-title">Gestion des Offres</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="post" action="?section=offers" class="offer-form">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label for="name">Nom de l'offre</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="discount">Pourcentage de réduction</label>
            <input type="number" id="discount" name="discount" min="0" max="100" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="start_date">Date de début</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>
        
        <div class="form-group">
            <label for="end_date">Date de fin</label>
            <input type="date" id="end_date" name="end_date" required>
        </div>
        
        <div class="form-group">
            <label>Articles en promotion</label>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-checkbox">
                        <input type="checkbox" name="products[]" value="<?php echo $product['id']; ?>" id="product-<?php echo $product['id']; ?>">
                        <label for="product-<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['name']); ?>
                            <span class="price"><?php echo number_format($product['price'], 2); ?> DA</span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Créer l'offre</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Réduction</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>Statut</th>
                <th>Articles</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($offers as $offer): 
                $now = new DateTime();
                $start_date = new DateTime($offer['start_date']);
                $end_date = new DateTime($offer['end_date']);
                
                if ($now < $start_date) {
                    $status = 'À venir';
                    $status_class = 'pending';
                } elseif ($now > $end_date) {
                    $status = 'Terminée';
                    $status_class = 'expired';
                } else {
                    $status = 'Active';
                    $status_class = 'active';
                }
                
                // Get products in this offer
                $stmt = $conn->prepare("SELECT p.name 
                                       FROM offer_products op 
                                       JOIN products p ON op.product_id = p.id 
                                       WHERE op.offer_id = ?");
                $stmt->bind_param("i", $offer['id']);
                $stmt->execute();
                $offer_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($offer['name']); ?></td>
                <td><?php echo $offer['discount_percentage']; ?>%</td>
                <td><?php echo date('d/m/Y', strtotime($offer['start_date'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($offer['end_date'])); ?></td>
                <td><span class="offer-status status-<?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                <td>
                    <?php 
                    $product_names = array_column($offer_products, 'name');
                    echo count($product_names) > 0 ? htmlspecialchars(implode(', ', $product_names)) : 'Aucun article';
                    ?>
                </td>
                <td>
                    <form method="post" action="?section=offers" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>