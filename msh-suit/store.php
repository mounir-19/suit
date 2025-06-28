<?php
// store.php - Main store page with dynamic product loading

session_start();
require_once 'config.php';

// Get filter parameters
$priceSort = isset($_GET['sort']) ? $_GET['sort'] : 'asc';
$categories = isset($_GET['category']) ? $_GET['category'] : [];
$colors = isset($_GET['color']) ? $_GET['color'] : [];
$sizes = isset($_GET['size']) ? $_GET['size'] : [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query with filters
$sql = "SELECT p.*, 
        COALESCE(
            (SELECT op.offer_id 
             FROM offer_products op 
             JOIN offers o ON op.offer_id = o.id 
             WHERE op.product_id = p.id 
             AND o.is_active = 1 
             AND CURDATE() BETWEEN o.start_date AND o.end_date 
             ORDER BY o.discount_percentage DESC 
             LIMIT 1), 
            0
        ) as active_offer_id,
        COALESCE(
            (SELECT o.discount_percentage 
             FROM offer_products op 
             JOIN offers o ON op.offer_id = o.id 
             WHERE op.product_id = p.id 
             AND o.is_active = 1 
             AND CURDATE() BETWEEN o.start_date AND o.end_date 
             ORDER BY o.discount_percentage DESC 
             LIMIT 1), 
            0
        ) as discount_percentage
        FROM products p 
        WHERE p.is_active = 1";

$params = [];
$types = "";

// Add search filter
if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Add category filter
if (!empty($categories)) {
    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
    $sql .= " AND p.category IN ($placeholders)";
    foreach ($categories as $category) {
        $params[] = $category;
        $types .= "s";
    }
}

// Add color filter
if (!empty($colors)) {
    $placeholders = str_repeat('?,', count($colors) - 1) . '?';
    $sql .= " AND LOWER(p.color) IN ($placeholders)";
    foreach ($colors as $color) {
        $params[] = strtolower($color);
        $types .= "s";
    }
}

// Add size filter
if (!empty($sizes)) {
    $placeholders = str_repeat('?,', count($sizes) - 1) . '?';
    $sql .= " AND p.size IN ($placeholders)";
    foreach ($sizes as $size) {
        $params[] = $size;
        $types .= "s";
    }
}

// Add sorting
$sql .= " ORDER BY p.price " . ($priceSort === 'desc' ? 'DESC' : 'ASC');

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);

// Get cart item count for logged-in users
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartQuery = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $cartStmt = $conn->prepare($cartQuery);
    $cartStmt->bind_param("i", $_SESSION['user_id']);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    $cartData = $cartResult->fetch_assoc();
    $cartCount = $cartData['count'] ?? 0;
}

// Category mapping for display
$categoryMap = [
    'suits' => 'Costumes',
    'jackets' => 'Vestes',
    'shirts' => 'Chemises',
    'pants' => 'Pantalons',
    'accessories' => 'Accessoires'
];

// Color mapping
$colorMap = [
    'noir' => 'Noir',
    'bleu marine' => 'Bleu Marine',
    'gris' => 'Gris',
    'charcoal' => 'Charcoal',
    'blanc' => 'Blanc',
    'rouge' => 'Rouge',
    'bleu' => 'Bleu'
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store - MSH-ISTANBOUL</title>
    <link rel="stylesheet" href="store.css">
    <link href="https://fonts.googleapis.com/css2?family=Arial:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-icon {
            position: relative;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff0000;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff0000;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 1;
        }
        .card {
            position: relative;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 10px;
        }
        .discounted-price {
            color: #ff0000;
            font-weight: bold;
        }
        .out-of-stock {
            background-color: #ccc !important;
            cursor: not-allowed !important;
        }
        .no-products {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin: 50px 0;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="MSH-ISTANBOUL Logo" class="logo">
        
        <div class="menu-toggle" id="menuToggle">
            <span>☰</span>
        </div>
        
        <div class="nav" id="nav">
            <a href="homepage.php">Accueil</a>
            <a href="store.php">Catalogue</a>
            <a href="homepage.php#contact">Contact</a>
        </div>
        
        <p class="name">MSH-ISTANBOUL</p>
        
        <div class="user" id="user">
            <div class="language-selector">
                <a href="#" title="Langue" class="language-toggle"><i class="fas fa-globe"></i></a>
                <div class="language-dropdown">
                    <a href="#" data-lang="fr">Français</a>
                    <a href="#" data-lang="ar">العربية</a>
                </div>
            </div>
            <a href="cart.html" title="Panier" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="account.html" title="Compte"><i class="fas fa-user"></i></a>
        </div>
    </div>
    
    <div class="bar">
        <div class="Scroll" id="scroll">
            <div class="text">
                <p>Livraison rapide 1–4 jours</p>
                <p>Paiement à la livraison</p>
                <p>-25% Promo spéciale ce mois-ci</p>
                <p>Livraison dans 58 wilayas</p>
            </div>
        </div>
    </div>

    <button id="filterBtn">Filter</button>

    <div id="overlay"></div>
    <div class="sidebar" id="sidebar">
        <form id="filterForm" method="GET" action="store.php">
            <div class="accordion">
                <div class="accordion-header">Prix</div>
                <div class="accordion-content">
                    <label><input type="radio" name="sort" value="asc" <?php echo $priceSort === 'asc' ? 'checked' : ''; ?>> Croissant</label>
                    <label><input type="radio" name="sort" value="desc" <?php echo $priceSort === 'desc' ? 'checked' : ''; ?>> Décroissant</label>
                </div>

                <div class="accordion-header">Catégorie</div>
                <div class="accordion-content">
                    <label><input type="checkbox" name="category[]" value="suits" <?php echo in_array('suits', $categories) ? 'checked' : ''; ?>> Costumes</label>
                    <label><input type="checkbox" name="category[]" value="jackets" <?php echo in_array('jackets', $categories) ? 'checked' : ''; ?>> Vestes</label>
                    <label><input type="checkbox" name="category[]" value="pants" <?php echo in_array('pants', $categories) ? 'checked' : ''; ?>> Pantalons</label>
                    <label><input type="checkbox" name="category[]" value="accessories" <?php echo in_array('accessories', $categories) ? 'checked' : ''; ?>> Accessoires</label>
                    <label><input type="checkbox" name="category[]" value="shirts" <?php echo in_array('shirts', $categories) ? 'checked' : ''; ?>> Chemises</label>
                </div>

                <div class="accordion-header">Couleur</div>
                <div class="accordion-content">
                    <label><input type="checkbox" name="color[]" value="noir" <?php echo in_array('noir', $colors) ? 'checked' : ''; ?>> Noir</label>
                    <label><input type="checkbox" name="color[]" value="gris" <?php echo in_array('gris', $colors) ? 'checked' : ''; ?>> Gris</label>
                    <label><input type="checkbox" name="color[]" value="bleu" <?php echo in_array('bleu', $colors) ? 'checked' : ''; ?>> Bleu</label>
                    <label><input type="checkbox" name="color[]" value="marron" <?php echo in_array('marron', $colors) ? 'checked' : ''; ?>> Marron</label>
                    <label><input type="checkbox" name="color[]" value="blanc" <?php echo in_array('blanc', $colors) ? 'checked' : ''; ?>> Blanc</label>
                    <label><input type="checkbox" name="color[]" value="rouge" <?php echo in_array('rouge', $colors) ? 'checked' : ''; ?>> Rouge</label>
                </div>

                <div class="accordion-header">Taille</div>
                <div class="accordion-content">
                    <?php 
                    // Get available sizes from database
                    $sizeQuery = "SELECT DISTINCT size FROM products WHERE is_active = 1 ORDER BY size";
                    $sizeResult = $conn->query($sizeQuery);
                    while ($sizeRow = $sizeResult->fetch_assoc()): 
                    ?>
                        <label><input type="checkbox" name="size[]" value="<?php echo $sizeRow['size']; ?>" <?php echo in_array($sizeRow['size'], $sizes) ? 'checked' : ''; ?>> <?php echo $sizeRow['size']; ?></label>
                    <?php endwhile; ?>
                </div>
            </div>
            <button type="submit" id="applyBtn" class="apply-btn">Filtrer</button>
            <a href="store.php" id="resetBtn" class="reset-btn" style="text-decoration: none; display: inline-block; text-align: center;">Réinitialiser</a>
        </form>
    </div>

    <br>
    <div class="articles-container">
        <?php if (empty($products)): ?>
            <p class="no-products">Aucun produit trouvé.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="card" data-product-id="<?php echo $product['id']; ?>">
                    <?php if ($product['discount_percentage'] > 0): ?>
                        <div class="discount-badge">-<?php echo intval($product['discount_percentage']); ?>%</div>
                    <?php endif; ?>
                    
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'card.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='card.jpg'">
                    
                    <p class="article-name"><?php echo htmlspecialchars($product['name']); ?></p>
                    <p class="article-color"><?php echo htmlspecialchars($product['color']); ?></p>
                    
                    <?php if ($product['discount_percentage'] > 0): ?>
                        <p class="article-price">
                            <span class="original-price"><?php echo number_format($product['price'], 0, ',', ' '); ?> DA</span>
                            <span class="discounted-price">
                                <?php 
                                $discountedPrice = $product['price'] * (1 - $product['discount_percentage'] / 100);
                                echo number_format($discountedPrice, 0, ',', ' '); 
                                ?> DA
                            </span>
                        </p>
                    <?php else: ?>
                        <p class="article-price"><?php echo number_format($product['price'], 0, ',', ' '); ?> DA</p>
                    <?php endif; ?>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <button class="add-to-bag" onclick="addToCart(<?php echo $product['id']; ?>)">
                            Ajouter au panier
                        </button>
                    <?php else: ?>
                        <button class="add-to-bag out-of-stock" disabled>
                            Rupture de stock
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <div class="service-client">
            <p>Service client</p>
            <a href="#">Compte</a>
            <a href="#">Livraison & Retour</a>
            <a href="#">Contactez-Nous</a>
        </div>
        
        <div class="service">
            <p>Services</p>
            <a href="#">page1</a>
            <a href="#">page2</a>
            <a href="#">page3</a>
        </div>
        
        <div class="Contact3">
            <a href="#">location</a>
            <a href="#">phone</a>
            <a href="#">email</a>
            <a href="#">facebook</a>
        </div>
        
        <img src="logo.png" alt="MSH-ISTANBOUL">
        
        <div class="copyright">
            <hr>
            &copy; 2025 MSH Istanboul. Tous droits réservés.
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Add to cart functionality (simple alert for now)
        function addToCart(productId) {
            // For now, just show an alert
            // You'll need to implement add_to_cart.php separately
            alert('Produit ajouté au panier! (ID: ' + productId + ')');
            
            // In production, you would make an AJAX call to add_to_cart.php
            /*
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Produit ajouté au panier!');
                    updateCartBadge();
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout au panier');
                }
            });
            */
        }

        // Filter sidebar functionality
        document.getElementById("filterBtn").addEventListener("click", function() {
            document.getElementById("sidebar").classList.add("show");
            document.getElementById("overlay").style.display = "block";
        });

        document.getElementById("overlay").addEventListener("click", function() {
            document.getElementById("sidebar").classList.remove("show");
            document.getElementById("overlay").style.display = "none";
        });

        // Accordion functionality
        const headers = document.querySelectorAll('.accordion-header');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const isActive = header.classList.contains('active');
                
                // Close all accordion items
                headers.forEach(h => {
                    h.classList.remove('active');
                    h.nextElementSibling.style.display = 'none';
                });
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    header.classList.add('active');
                    content.style.display = 'block';
                }
            });
        });
    </script>
</body>
</html>