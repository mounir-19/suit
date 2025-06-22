<?php
// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../auth.php';
require_once 'utils.php';

// Ensure user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Handle article actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    
    if ($action === 'add') {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $color = filter_input(INPUT_POST, 'color', FILTER_SANITIZE_STRING);
        $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_STRING);
        
        if ($name && $price && $stock !== false && $category) {
            try {
                // Insert product
                $stmt = $mysqli->prepare("INSERT INTO products (name, price, stock, category, description, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sdissss', $name, $price, $stock, $category, $description, $color, $size);
                $stmt->execute();
                $productId = $mysqli->insert_id;
                $stmt->close();
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imageResult = handleImageUpload('image', $productId);
                    if ($imageResult['success']) {
                        $updateStmt = $mysqli->prepare("UPDATE products SET image_url = ? WHERE id = ?");
                        $updateStmt->bind_param('si', $imageResult['path'], $productId);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $error = "Error adding product: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
    
    if ($action === 'delete') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        
        if ($productId) {
            try {
                $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $stmt->close();
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $error = "Error deleting product: " . $e->getMessage();
            }
        }
    }
}

// Function to get products with pagination
function getProducts($page = 1, $perPage = 10) {
    global $mysqli;
    
    $offset = ($page - 1) * $perPage;
    
    // Get total count
    $result = $mysqli->query('SELECT COUNT(*) as count FROM products');
    $total = $result->fetch_assoc()['count'];
    
    // Get products
    $stmt = $mysqli->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ii', $perPage, $offset);
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

$currentPage = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$result = getProducts($currentPage, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Articles - Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Manage Articles</h1>
        
        <?php displayMessage($success_message, 'success'); ?>
        <?php displayMessage($error_message, 'error'); ?>
        
        <!-- Add Product Form -->
        <div class="form-section">
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="suits">Suits</option>
                        <option value="shirts">Shirts</option>
                        <option value="accessories">Accessories</option>
                        <option value="shoes">Shoes</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color">
                </div>
                
                <div class="form-group">
                    <label for="size">Size</label>
                    <input type="text" id="size" name="size">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>
                
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
        
        <!-- Products List -->
        <div class="products-section">
            <h2>Products List</h2>
            
            <?php if (empty($result['products'])): ?>
                <p>No products found.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($result['products'] as $product): ?>
                        <div class="product-card">
                            <?php if (!empty($product['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                                <p class="stock">Stock: <?php echo $product['stock']; ?></p>
                                <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                                
                                <div class="product-actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($result['pages'] > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                            <?php $activeClass = ($i === $currentPage) ? 'active' : ''; ?>
                            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $activeClass; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>