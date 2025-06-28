<?php
// Get all offers with product count
$offers_query = "
    SELECT o.*, COUNT(op.product_id) as product_count
    FROM offers o 
    LEFT JOIN offer_products op ON o.id = op.offer_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";
$offers_result = $mysqli->query($offers_query);
$offers = $offers_result->fetch_all(MYSQLI_ASSOC);

// Get all products for the form
$products_query = "SELECT id, name FROM products ORDER BY name";
$products_result = $mysqli->query($products_query);
$all_products = $products_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-tags"></i> Offers Management</h2>
        <p class="text-muted">Create and manage special offers for your products.</p>
    </div>
</div>

<!-- Add Offer Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Add New Offer</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="add_offer">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="offer_name" class="form-label">Offer Name</label>
                            <input type="text" class="form-control" id="offer_name" name="offer_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="discount" class="form-label">Discount Percentage</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="discount" name="discount" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Products for this Offer</label>
                        <div class="row">
                            <?php foreach ($all_products as $product): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>" id="product_<?= $product['id'] ?>">
                                        <label class="form-check-label" for="product_<?= $product['id'] ?>">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Offer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Offers List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> All Offers</h5>
                <span class="badge bg-primary"><?= count($offers) ?> Total Offers</span>
            </div>
            <div class="card-body">
                <?php if (empty($offers)): ?>
                    <p class="text-muted">No offers found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Offer Name</th>
                                    <th>Discount</th>
                                    <th>Products</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($offers as $offer): ?>
                                    <?php
                                    $today = date('Y-m-d');
                                    $status = 'expired';
                                    if ($today < $offer['start_date']) {
                                        $status = 'upcoming';
                                    } elseif ($today >= $offer['start_date'] && $today <= $offer['end_date']) {
                                        $status = 'active';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($offer['name']) ?></td>
                                        <td><?= $offer['discount_percentage'] ?>%</td>
                                        <td><?= $offer['product_count'] ?> products</td>
                                        <td><?= date('M j, Y', strtotime($offer['start_date'])) ?></td>
                                        <td><?= date('M j, Y', strtotime($offer['end_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $status === 'active' ? 'success' : ($status === 'upcoming' ? 'info' : 'secondary') ?>">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-sm btn-action" onclick="deleteItem('offer', <?= $offer['id'] ?>)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>