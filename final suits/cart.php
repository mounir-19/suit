<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = $_POST['product_id'] ?? 0;
    
    switch($action) {
        case 'add':
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = 0;
            }
            $_SESSION['cart'][$product_id]++;
            break;
            
        case 'remove':
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
            
        case 'update':
            $quantity = $_POST['quantity'] ?? 0;
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
    exit;
}

// Get cart items
$cart_items = [];
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $_SESSION['cart'][$product['id']],
            'total' => $product['price'] * $_SESSION['cart'][$product['id']]
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - MSH-ISTANBOUL</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <div class="cart-container">
        <h1>Your Cart</h1>
        <?php if (empty($cart_items)): ?>
            <p>Your cart is empty</p>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p>Price: <?php echo number_format($item['price'], 2); ?> DA</p>
                    <input type="number" min="1" value="<?php echo $item['quantity']; ?>"
                           data-product-id="<?php echo $item['id']; ?>" class="quantity-input">
                    <p>Total: <?php echo number_format($item['total'], 2); ?> DA</p>
                    <button class="remove-item" data-product-id="<?php echo $item['id']; ?>">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-summary">
                <h2>Total: <?php echo number_format(array_sum(array_column($cart_items, 'total')), 2); ?> DA</h2>
                <button class="checkout-btn">Proceed to Checkout</button>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>