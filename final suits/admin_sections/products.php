<?php
// Get all products
$products_query = "SELECT * FROM products ORDER BY created_at DESC";
$products_result = $mysqli->query($products_query);
$products = $products_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-box"></i> Products Management</h2>
        <p class="text-muted">Add, edit, and manage your store products.</p>
    </div>
</div>

<!-- Add Product Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Add New Product</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="action" value="add_product">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (DA)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="suits">Suits</option>
                                <option value="shirts">Shirts</option>
                                <option value="pants">Pants</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" name="color">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="size" class="form-label">Size</label>
                            <input type="text" class="form-control" id="size" name="size">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Products List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> All Products</h5>
                <span class="badge bg-primary"><?= count($products) ?> Total Products</span>
            </div>
            <div class="card-body">
                <?php if (empty($products)): ?>
                    <p class="text-muted">No products found.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image_url']): ?>
                                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= number_format($product['price'], 2) ?> DA</td>
                                        <td>
                                            <span class="badge bg-<?= $product['stock'] > 0 ? 'success' : 'danger' ?>">
                                                <?= $product['stock'] ?>
                                            </span>
                                        </td>
                                        <td><?= ucfirst($product['category']) ?></td>
                                        <td>
                                            <button class="btn btn-danger btn-sm btn-action" onclick="deleteItem('product', <?= $product['id'] ?>)">
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