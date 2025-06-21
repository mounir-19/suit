<?php
require_once 'config.php';
session_start();

// Get filter parameters
$priceSort = $_GET['price_sort'] ?? 'croissant';
$categories = $_GET['categories'] ?? [];
$colors = $_GET['colors'] ?? [];
$sizes = $_GET['sizes'] ?? [];

// Build query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($categories)) {
    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
    $query .= " AND category IN ($placeholders)";
    $params = array_merge($params, $categories);
    $types .= str_repeat('s', count($categories));
}

if (!empty($colors)) {
    $placeholders = str_repeat('?,', count($colors) - 1) . '?';
    $query .= " AND color IN ($placeholders)";
    $params = array_merge($params, $colors);
    $types .= str_repeat('s', count($colors));
}

if (!empty($sizes)) {
    $placeholders = str_repeat('?,', count($sizes) - 1) . '?';
    $query .= " AND size IN ($placeholders)";
    $params = array_merge($params, $sizes);
    $types .= str_repeat('s', count($sizes));
}

$query .= " ORDER BY price " . ($priceSort === 'croissant' ? 'ASC' : 'DESC');

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="store.css" />
    <script src="homepage.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <title>Store - MSH-ISTANBUL</title>
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            margin-top: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
        }
        .product-card h3 {
            margin: 10px 0;
            color: #333;
        }
        .product-card .price {
            font-size: 18px;
            font-weight: bold;
            color: #D4AE6A;
            margin: 10px 0;
        }
        .product-card .details {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        .add-to-cart {
            background: #D4AE6A;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .add-to-cart:hover {
            background: #B8954A;
        }
        .cart-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="cart-notification" id="cartNotification">
        Product added to cart!
    </div>

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
            <a href="cart.php">Cart (<span id="cart-count">0</span>)</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Account</a>
                <a href="?logout=1">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
    <hr />

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

    <div class="main" id="main">
        <div class="title">
            <p class="welcome">Notre Collection</p>
            <p class="quote">Découvrez nos produits de qualité.</p>
        </div>
    </div>

    <button id="filterBtn">Filter</button>

    <div id="overlay"></div>
    <div class="sidebar" id="sidebar">
        <div class="accordion">
            <div class="accordion-header">Prix</div>
            <div class="accordion-content">
                <label><input type="radio" name="price-sort" value="croissant" checked> Croissant</label>
                <label><input type="radio" name="price-sort" value="decroissant"> Décroissant</label>
            </div>

            <div class="accordion-header">Catégorie</div>
            <div class="accordion-content">
                <label><input type="checkbox" value="suits"> Costumes</label>
                <label><input type="checkbox" value="shirts"> Chemises</label>
                <label><input type="checkbox" value="pants"> Pantalons</label>
                <label><input type="checkbox" value="accessories"> Accessoires</label>
            </div>

            <div class="accordion-header">Couleur</div>
            <div class="accordion-content">
                <label><input type="checkbox" value="Noir"> Noir</label>
                <label><input type="checkbox" value="Gris"> Gris</label>
                <label><input type="checkbox" value="Bleu Marine"> Bleu Marine</label>
                <label><input type="checkbox" value="Blanc"> Blanc</label>
                <label><input type="checkbox" value="Rouge"> Rouge</label>
            </div>

            <div class="accordion-header">Taille</div>
            <div class="accordion-content">
                <label><input type="checkbox" value="XS"> XS</label>
                <label><input type="checkbox" value="S"> S</label>
                <label><input type="checkbox" value="M"> M</label>
                <label><input type="checkbox" value="L"> L</label>
                <label><input type="checkbox" value="XL"> XL</label>
                <label><input type="checkbox" value="XXL"> XXL</label>
            </div>
        </div>
        <button id="applyBtn" class="apply-btn">Filtrer</button>
        <button id="resetBtn" class="reset-btn">Réinitialiser</button>
    </div>

    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                <h3>Aucun produit trouvé</h3>
                <p>Essayez de modifier vos filtres ou <a href="store.php">voir tous les produits</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="details">
                    <?php if ($product['color']): ?>
                        <span>Couleur: <?php echo htmlspecialchars($product['color']); ?></span><br>
                    <?php endif; ?>
                    <?php if ($product['size']): ?>
                        <span>Taille: <?php echo htmlspecialchars($product['size']); ?></span><br>
                    <?php endif; ?>
                    <span>Stock: <?php echo $product['stock']; ?></span>
                </div>
                <p class="price"><?php echo number_format($product['price'], 2); ?> DA</p>
                <p style="font-size: 14px; color: #666; margin: 10px 0;"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                <button class="add-to-cart" data-product-id="<?php echo $product['id']; ?>" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] <= 0 ? 'Rupture de stock' : 'Ajouter au panier'; ?>
                </button>
            </div>
            <?php endforeach; ?>
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
    document.addEventListener("DOMContentLoaded", function() {
        // Update cart count on page load
        updateCartCount();

        // Filter functionality
        const filterBtn = document.getElementById("filterBtn");
        const sidebar = document.getElementById("sidebar");
        const overlay = document.getElementById("overlay");
        const applyBtn = document.getElementById("applyBtn");
        const resetBtn = document.getElementById("resetBtn");

        filterBtn.addEventListener("click", () => {
            sidebar.classList.add("show");
            overlay.style.display = "block";
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("show");
            overlay.style.display = "none";
        });

        // Reset filters
        resetBtn.addEventListener("click", () => {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.querySelector('input[name="price-sort"][value="croissant"]').checked = true;
        });

        // Apply filters
        applyBtn.addEventListener("click", () => {
            const priceSort = document.querySelector('input[name="price-sort"]:checked').value;
            const categories = Array.from(document.querySelectorAll('input[type="checkbox"][value="suits"], input[type="checkbox"][value="shirts"], input[type="checkbox"][value="pants"], input[type="checkbox"][value="accessories"]'))
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            const colors = Array.from(document.querySelectorAll('input[type="checkbox"][value="Noir"], input[type="checkbox"][value="Gris"], input[type="checkbox"][value="Bleu Marine"], input[type="checkbox"][value="Blanc"], input[type="checkbox"][value="Rouge"]'))
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            const sizes = Array.from(document.querySelectorAll('input[type="checkbox"][value="XS"], input[type="checkbox"][value="S"], input[type="checkbox"][value="M"], input[type="checkbox"][value="L"], input[type="checkbox"][value="XL"], input[type="checkbox"][value="XXL"]'))
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            // Build URL with filters
            const params = new URLSearchParams();
            params.set('price_sort', priceSort);
            categories.forEach(cat => params.append('categories[]', cat));
            colors.forEach(color => params.append('colors[]', color));
            sizes.forEach(size => params.append('sizes[]', size));

            window.location.href = 'store.php?' + params.toString();
        });

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showCartNotification();
                        updateCartCount();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        function showCartNotification() {
            const notification = document.getElementById('cartNotification');
            notification.style.display = 'block';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        function updateCartCount() {
            fetch('cart.php?get_count=1')
                .then(response => response.json())
                .then(data => {
                    const count = Object.values(data.cart || {}).reduce((sum, qty) => sum + qty, 0);
                    document.getElementById('cart-count').textContent = count;
                })
                .catch(error => {
                    console.error('Error updating cart count:', error);
                });
        }
    });
    </script>
</body>
</html>

