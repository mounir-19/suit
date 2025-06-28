<?php
// Navbar component - requires session to be started and config to be included
if (!isset($_SESSION)) {
    session_start();
}
?>
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Header -->
<link rel="stylesheet" href="nav.css">
<header class="header">
    <div class="header-container">
        <!-- Mobile Menu Toggle -->
        <button id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="logo-section">
            <img src="logo.png" alt="MSH-ISTANBUL Logo" class="logo">
            <span class="brand-name">MSH-ISTANBUL</span>
        </div>
        
        <!-- Navigation -->
        <nav class="nav" id="nav">
            <a href="#hero" class="nav-link">Accueil</a>
            <a href="#about" class="nav-link">À Propos</a>
            <a href="store.php" class="nav-link">Catalogue</a>
            <a href="#contact" class="nav-link">Contact</a>
        </nav>

        <!-- User Actions -->
        <div class="user-actions">
            <a href="cart.php" class="action-btn cart-btn" aria-label="Panier">
                <i class="fas fa-shopping-bag"></i>
                <span class="cart-count" id="cart-count">0</span>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="action-btn" aria-label="Profil"><i class="fas fa-user"></i></a>
                <a href="?logout=1" class="action-btn" aria-label="Déconnexion"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="login.php" class="action-btn login-btn" aria-label="Connexion">
                    <i class="fas fa-user"></i>
                    <span class="login-text">Connexion</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Promotional Banner -->
<div class="promo-banner">
    <div class="promo-scroll">
        <div class="promo-content">
            <div class="promo-item">
                <i class="fas fa-shipping-fast"></i>
                <span>Livraison rapide 1–4 jours</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-credit-card"></i>
                <span>Paiement à la livraison</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-percentage"></i>
                <span>-25% Promo spéciale</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Livraison 58 wilayas</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-shield-alt"></i>
                <span>Garantie qualité</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-undo"></i>
                <span>Retour facile</span>
            </div>
        </div>
        <!-- Duplicate for seamless scroll -->
        <div class="promo-content">
            <div class="promo-item">
                <i class="fas fa-shipping-fast"></i>
                <span>Livraison rapide 1–4 jours</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-credit-card"></i>
                <span>Paiement à la livraison</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-percentage"></i>
                <span>-25% Promo spéciale</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Livraison 58 wilayas</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-shield-alt"></i>
                <span>Garantie qualité</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-undo"></i>
                <span>Retour facile</span>
            </div>
        </div>
    </div>
</div>

<script>
// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const nav = document.getElementById('nav');
    
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            nav.classList.toggle('active');
        });
        
        // Close menu when clicking on nav links
        const navLinks = nav.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                menuToggle.classList.remove('active');
                nav.classList.remove('active');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!menuToggle.contains(event.target) && !nav.contains(event.target)) {
                menuToggle.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    }
});
</script>