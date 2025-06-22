<?php
require_once 'config.php';
session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $product_id = (int)$_POST['product_id'];
            if ($product_id > 0) {
                // Check if product exists and has stock
                $stmt = $mysqli->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($product = $result->fetch_assoc()) {
                    if ($product['stock'] > 0) {
                        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
                        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Product out of stock']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                }
                $stmt->close();
            }
            exit;
            
        case 'update':
            $product_id = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            if ($product_id > 0) {
                if ($quantity <= 0) {
                    unset($_SESSION['cart'][$product_id]);
                } else {
                    $_SESSION['cart'][$product_id] = $quantity;
                }
                echo json_encode(['success' => true]);
            }
            exit;
            
        case 'remove':
            $product_id = (int)$_POST['product_id'];
            if ($product_id > 0) {
                unset($_SESSION['cart'][$product_id]);
                echo json_encode(['success' => true]);
            }
            exit;
            
        case 'clear':
            $_SESSION['cart'] = [];
            echo json_encode(['success' => true]);
            exit;
    }
}

// Get cart items with product details
function getCartItems() {
    global $mysqli;
    
    if (empty($_SESSION['cart'])) {
        return [];
    }
    
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($productIds)), ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $cartItems = [];
    foreach ($products as $product) {
        $cartItems[] = [
            'product' => $product,
            'quantity' => $_SESSION['cart'][$product['id']]
        ];
    }
    
    return $cartItems;
}

$cartItems = getCartItems();
$cartTotal = 0;
foreach ($cartItems as $item) {
    $cartTotal += $item['product']['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Suit Store</title>
    <link rel="stylesheet" href="homepage.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            gap: 20px;
        }
        
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #666;
            font-size: 16px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            background: #007bff;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 4px;
        }
        
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
        }
        
        .checkout-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h1>Shopping Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="store.php" class="checkout-btn" style="display: inline-block; text-decoration: none;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                    <img src="<?php echo htmlspecialchars($item['product']['image_url'] ?? 'placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                    
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['product']['name']); ?></div>
                        <div class="item-price">$<?php echo number_format($item['product']['price'], 2); ?></div>
                    </div>
                    
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product']['id']; ?>, -1)">-</button>
                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                               onchange="setQuantity(<?php echo $item['product']['id']; ?>, this.value)" min="1">
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product']['id']; ?>, 1)">+</button>
                    </div>
                    
                    <button class="remove-btn" onclick="removeItem(<?php echo $item['product']['id']; ?>)">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="total">Total: $<?php echo number_format($cartTotal, 2); ?></div>
                <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateQuantity(productId, change) {
            const input = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
            const newQuantity = Math.max(1, parseInt(input.value) + change);
            setQuantity(productId, newQuantity);
        }
        
        function setQuantity(productId, quantity) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
        
        function removeItem(productId) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
        
        function checkout() {
            // Implement checkout logic
            alert('Checkout functionality to be implemented');
        }
    </script>
</body>
</html>