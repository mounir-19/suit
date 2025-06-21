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
                $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
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
    }
}

// Handle GET request for cart count
if (isset($_GET['get_count'])) {
    echo json_encode(['cart' => $_SESSION['cart']]);
    exit;
}

// Get cart items with product details
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MSH-ISTANBUL</title>
    <link rel="stylesheet" href="store.css">
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .item-details h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .item-price {
            font-size: 18px;
            font-weight: bold;
            color: #D4AE6A;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            background: #D4AE6A;
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
            border-radius: 4px;
            padding: 5px;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .cart-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .checkout-btn {
            background: #D4AE6A;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            margin-top: 15px;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .continue-shopping {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="" class="logo" />
        <div id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav" id="nav">
            <a href="homepage.php">Acceuil</a>
            <a href="store.php">Catalogue</a>
            <a href="homepage.php#contact">Contact</a>
        </div>
        <p class="name">MSH-ISTANBUL</p>
        <div class="user" id="user">
            <a href="#">Langue</a>
            <a href="cart.php">Cart (<span id="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>)</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Account</a>
                <a href="?logout=1">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <hr />

    <div class="cart-container">
        <h1>Votre Panier</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits et ajoutez-les à votre panier</p>
                <a href="store.php" class="continue-shopping">Continuer vos achats</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-product-id="<?php echo $item['product']['id']; ?>">
                    <img src="<?php echo htmlspecialchars($item['product']['image_url'] ?: 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['product']['name']); ?></h3>
                        <p><?php echo htmlspecialchars($item['product']['description']); ?></p>
                        <?php if ($item['product']['color']): ?>
                            <p><strong>Couleur:</strong> <?php echo htmlspecialchars($item['product']['color']); ?></p>
                        <?php endif; ?>
                        <?php if ($item['product']['size']): ?>
                            <p><strong>Taille:</strong> <?php echo htmlspecialchars($item['product']['size']); ?></p>
                        <?php endif; ?>
                        <div class="item-price"><?php echo number_format($item['product']['price'], 2); ?> DA</div>
                    </div>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product']['id']; ?>, -1)">-</button>
                        <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" onchange="setQuantity(<?php echo $item['product']['id']; ?>, this.value)">
                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product']['id']; ?>, 1)">+</button>
                    </div>
                    <div class="item-subtotal">
                        <strong><?php echo number_format($item['subtotal'], 2); ?> DA</strong>
                    </div>
                    <button class="remove-btn" onclick="removeItem(<?php echo $item['product']['id']; ?>)">Supprimer</button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Résumé de la commande</h3>
                <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                    <span>Sous-total:</span>
                    <span id="cart-total"><?php echo number_format($total, 2); ?> DA</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                    <span>Livraison:</span>
                    <span>Gratuite</span>
                </div>
                <hr>
                <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">
                    <span>Total:</span>
                    <span id="final-total"><?php echo number_format($total, 2); ?> DA</span>
                </div>
                <button class="checkout-btn" onclick="checkout()">Passer la commande</button>
                <a href="store.php" class="continue-shopping">Continuer vos achats</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="service-client">
            <p>Service client</p>
            <a href="profile.php">Compte</a>
            <a href="#">Livraison &amp; Retour</a>
            <a href="homepage.php#contact">Contactez-Nous</a>
        </div>
        <div class="service">
            <p>Services</p>
            <a href="homepage.php">Accueil</a>
            <a href="store.php">Boutique</a>
            <a href="cart.php">Panier</a>
        </div>
        <div class="Contact3">
            <a href="#">Alger, Algérie</a>
            <a href="tel:+213123456789">+213 123 456 789</a>
            <a href="mailto:contact@msh-istanbul.com">contact@msh-istanbul.com</a>
            <a href="#">Facebook</a>
        </div>
        <img src="logo.png" alt="Logo of MSH Istanbul" />
        <div class="copyright">
            <hr />
            &copy; 2025 MSH Istanbul. Tous droits réservés.
        </div>
    </footer>

    <script>
    function updateQuantity(productId, change) {
        const input = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
        const newQuantity = Math.max(1, parseInt(input.value) + change);
        setQuantity(productId, newQuantity);
    }

    function setQuantity(productId, quantity) {
        quantity = Math.max(1, parseInt(quantity));
        
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
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function removeItem(productId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet article?')) {
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
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    function checkout() {
        <?php if (isset($_SESSION['user_id'])): ?>
            // User is logged in, proceed to checkout
            alert('Fonctionnalité de commande à implémenter');
        <?php else: ?>
            // User needs to login
            if (confirm('Vous devez vous connecter pour passer une commande. Voulez-vous vous connecter maintenant?')) {
                window.location.href = 'login.php';
            }
        <?php endif; ?>
    }
    </script>
</body>
</html>