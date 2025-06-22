<?php
require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    
    if ($action === 'add' || $action === 'edit') {
        // Validate and sanitize input
        $productData = [
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'price' => filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT),
            'stock' => filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT),
            'category' => filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING),
            'color' => filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING),
            'size' => filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING)
        ];
        
        // Handle image upload
        $imageResult = handleImageUpload('image');
        if ($imageResult['success']) {
            $productData['image_url'] = $imageResult['path'];
        }
        
        if ($action === 'add') {
            // Insert new product
            $stmt = $mysqli->prepare(
                'INSERT INTO products (name, description, price, stock, category, color, size, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssdissss', 
                $productData['name'],
                $productData['description'],
                $productData['price'],
                $productData['stock'],
                $productData['category'],
                $productData['color'],
                $productData['size'],
                $productData['image_url'] ?? null
            );
            $stmt->execute();
            $stmt->close();
        } else {
            // Update existing product
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            
            if (isset($productData['image_url'])) {
                $stmt = $mysqli->prepare(
                    'UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, color = ?, size = ?, image_url = ? WHERE id = ?'
                );
                $stmt->bind_param('ssdissssi', 
                    $productData['name'],
                    $productData['description'],
                    $productData['price'],
                    $productData['stock'],
                    $productData['category'],
                    $productData['color'],
                    $productData['size'],
                    $productData['image_url'],
                    $productId
                );
            } else {
                $stmt = $mysqli->prepare(
                    'UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ?, color = ?, size = ? WHERE id = ?'
                );
                $stmt->bind_param('ssdisssi', 
                    $productData['name'],
                    $productData['description'],
                    $productData['price'],
                    $productData['stock'],
                    $productData['category'],
                    $productData['color'],
                    $productData['size'],
                    $productId
                );
            }
            $stmt->execute();
            $stmt->close();
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if ($action === 'delete') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId) {
            $stmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get products with pagination and filters
function getProducts($page = 1, $perPage = 12, $filters = []) {
    global $mysqli;
    
    $offset = ($page - 1) * $perPage;
    $where = [];
    $params = [];
    $types = '';
    
    if (!empty($filters['category'])) {
        $where[] = 'category = ?';
        $params[] = $filters['category'];
        $types .= 's';
    }
    
    if (!empty($filters['search'])) {
        $where[] = '(name LIKE ? OR description LIKE ?)';
        $searchTerm = "%{$filters['search']}%";
        $params = array_merge($params, [$searchTerm, $searchTerm]);
        $types .= 'ss';
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as count FROM products $whereClause";
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
    
    // Get products
    $query = "SELECT * FROM products $whereClause ORDER BY name LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return [
        'products' => $products,
        'total' => $total,
        'pages' => ceil($total / $perPage)
    ];
}

// Get categories for filter
$result = $mysqli->query('SELECT DISTINCT category FROM products ORDER BY category');
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Get current page and filters
$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$filters = [
    'category' => filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING),
    'search' => filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING)
];

$result = getProducts($currentPage, 12, $filters);
?>

<div class="products-management">
    <div class="page-header">
        <h1>Products Management</h1>
        <button class="btn btn-primary" onclick="openProductModal()">Add New Product</button>
    </div>
    
    <div class="filters">
        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search products..." 
                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <?php $selected = ($filters['category'] === $category) ? 'selected' : ''; ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($category); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if (!empty($filters['category']) || !empty($filters['search'])): ?>
                <a href="?" class="btn btn-secondary">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
    
    <div class="products-grid">
        <?php if (empty($result['products'])): ?>
            <div class="no-data">No products found</div>
        <?php else: ?>
            <?php foreach ($result['products'] as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                    <div class="product-details">
                        <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                        <span class="stock">Stock: <?php echo $product['stock']; ?></span>
                    </div>
                    <div class="product-meta">
                        <span class="color">Color: <?php echo htmlspecialchars($product['color']); ?></span>
                        <span class="size">Size: <?php echo htmlspecialchars($product['size']); ?></span>
                    </div>
                </div>
                <div class="product-actions">
                    <button class="btn btn-primary btn-sm" 
                            onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        Edit
                    </button>
                    <button class="btn btn-danger btn-sm" 
                            onclick="deleteProduct(<?php echo $product['id']; ?>)">
                        Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($result['pages'] > 1): ?>
    <div class="pagination">
        <?php 
        $queryParams = array_filter($filters);
        $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
        
        for ($i = 1; $i <= $result['pages']; $i++): 
            $activeClass = ($i === $currentPage) ? 'active' : '';
        ?>
            <a href="?page=<?php echo $i . $queryString; ?>" 
               class="page-link <?php echo $activeClass; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Add New Product</h2>
            <button type="button" class="close-button" onclick="closeProductModal()">&times;</button>
        </div>
        <form id="productForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="product_id" id="productId">
            
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" required>
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" required>
                </div>
                
                <div class="form-group">
                    <label for="size">Size</label>
                    <input type="text" id="size" name="size" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Delete Product</h2>
            <button type="button" class="close-button" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this product?</p>
        </div>
        <div class="modal-footer">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" id="deleteProductId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<style>
/* Products Management Styles */
.products-management {
    padding: 2rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.filter-form {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.product-card {
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 4px var(--shadow-color);
    transition: transform var(--transition-speed);
}

.product-card:hover {
    transform: translateY(-5px);
}

.product-image {
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    padding: 1rem;
}

.product-info h3 {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
}

.product-category {
    color: var(--text-light);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.product-details {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.price {
    font-weight: 600;
    color: var(--primary-color);
}

.stock {
    color: var(--text-light);
}

.product-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--text-light);
}

.product-actions {
    padding: 1rem;
    display: flex;
    gap: 0.5rem;
    border-top: 1px solid var(--border-color);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background: white;
    width: 90%;
    max-width: 600px;
    margin: 2rem auto;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px var(--shadow-color);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.close-button {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-light);
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.form-actions {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

@media (max-width: 768px) {
    .products-management {
        padding: 1rem;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        margin: 1rem;
        width: calc(100% - 2rem);
    }
}
</style>

<script>
function openProductModal(product = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    
    if (product) {
        title.textContent = 'Edit Product';
        form.action.value = 'edit';
        form.product_id.value = product.id;
        form.name.value = product.name;
        form.description.value = product.description;
        form.price.value = product.price;
        form.stock.value = product.stock;
        form.category.value = product.category;
        form.color.value = product.color;
        form.size.value = product.size;
    } else {
        title.textContent = 'Add New Product';
        form.action.value = 'add';
        form.reset();
    }
    
    modal.style.display = 'block';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

function deleteProduct(productId) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const productModal = document.getElementById('productModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target === productModal) {
        closeProductModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
}
</script>