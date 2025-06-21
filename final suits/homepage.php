<?php
require_once 'config.php';
session_start();

// Add CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $name = htmlspecialchars($_POST['name'] ?? '');
    $phone = htmlspecialchars($_POST['phone'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format d'email invalide";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (email, name, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $name, $phone, $message);
        
        if ($stmt->execute()) {
            $success_message = "Message envoyé avec succès!";
        } else {
            $error_message = "Échec de l'envoi du message. Veuillez réessayer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="homepage.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="description" content="MSH-ISTANBUL - Votre destination de choix pour des costumes élégants en Algérie. Trouvez le mélange parfait de style et de tradition.">
    <meta name="keywords" content="costumes, vêtements formels, costumes de mariage, MSH-ISTANBUL, mode algérienne">
    <title>MSH-ISTANBUL - Costumes Élégants & Vêtements Formels</title>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo-section">
                <img src="logo.png" alt="MSH-ISTANBUL Logo" class="logo">
                <span class="brand-name">MSH-ISTANBUL</span>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="menuToggle" class="menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <!-- Navigation -->
            <nav class="nav" id="nav">
                <a href="#hero" class="nav-link">Accueil</a>
                <a href="#about" class="nav-link">À Propos</a>
                <a href="store.php" class="nav-link">Catalogue</a>
                <a href="#contact" class="nav-link">Contact</a>
            </nav>

            <!-- User Actions -->
            <div class="user-actions">
                <a href="cart.php" class="action-btn cart-btn">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="action-btn"><i class="fas fa-user"></i></a>
                    <a href="?logout=1" class="action-btn"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" class="action-btn login-btn">Connexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Promotional Banner -->
    <div class="promo-banner">
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
                <span>-25% Promo spéciale ce mois-ci</span>
            </div>
            <div class="promo-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Livraison dans 58 wilayas</span>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="title-line">Bienvenue chez</span>
                    <span class="title-brand">MSH ISTANBUL</span>
                </h1>
                <p class="hero-subtitle">L'élégance, à l'algérienne.</p>
                <div class="hero-features">
                    <div class="feature">
                        <i class="fas fa-star"></i>
                        <span>Qualité Premium</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-scissors"></i>
                        <span>Coupe Sur Mesure</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-award"></i>
                        <span>Style Authentique</span>
                    </div>
                </div>
                <div class="hero-cta">
                    <a href="store.php" class="cta-primary">Découvrir la Collection</a>
                    <a href="#about" class="cta-secondary">En Savoir Plus</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="image-container">
                    <img src="img1.jpg" alt="Collection Élégante - Costumes" class="hero-img">
                    <div class="image-overlay">
                        <div class="deal-badge">
                            <span class="deal-percent">-20%</span>
                            <span class="deal-text">Collection Mariage</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Pourquoi nous choisir ?</h2>
                <p class="section-subtitle">
                    Chez MSH Istanbul, chaque costume est pensé pour révéler le meilleur de vous-même.
                    Découvrez ce qui nous distingue vraiment :
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="icon1.png" alt="Livraison rapide">
                    </div>
                    <h3 class="feature-title">Livraison rapide dans tout le pays</h3>
                    <p class="feature-description">Peu importe où vous êtes en Algérie, nous livrons chez vous en 1 à 4 jours ouvrés.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="icon2.png" alt="Paiement sécurisé">
                    </div>
                    <h3 class="feature-title">Paiement à la réception</h3>
                    <p class="feature-description">Vous ne payez qu'à la livraison, après avoir vu votre commande.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <img src="icon3.png" alt="Qualité supérieure">
                    </div>
                    <h3 class="feature-title">Qualité supérieure</h3>
                    <p class="feature-description">Nos costumes sont confectionnés avec des tissus haut de gamme soigneusement sélectionnés.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <div class="discount-badge">-20%</div>
                    </div>
                    <h3 class="feature-title">Promotions chaque mois</h3>
                    <p class="feature-description">Ne ratez pas nos offres exclusives, renouvelées régulièrement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="contact-content">
                <div class="contact-info">
                    <h2 class="contact-title">N'hésitez pas à nous contacter</h2>
                    <p class="contact-subtitle">Pour toute demande, nous répondons sous 24h.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h4>CALL CENTER</h4>
                                <a href="tel:+213789123456">+213 789 123 456</a>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>NOTRE LOCALISATION</h4>
                                <span>Alger, Algérie</span>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>EMAIL</h4>
                                <a href="mailto:mshistanbul@gmail.com">mshistanbul@gmail.com</a>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fab fa-facebook"></i>
                            </div>
                            <div class="contact-text">
                                <h4>RÉSEAUX SOCIAUX</h4>
                                <a href="#">Facebook</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3 class="form-title">Contactez-Nous</h3>
                    <form method="post" action="" class="contact-form-element">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="email">Adresse Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Nom Complet</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Numéro de Téléphone</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="4" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <span>Envoyer le Message</span>
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="logo.png" alt="MSH Istanbul Logo">
                        <span class="footer-brand">MSH ISTANBUL</span>
                    </div>
                    <p class="footer-description">
                        Votre destination de choix pour des costumes élégants qui allient tradition et modernité.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Service Client</h4>
                    <ul class="footer-links">
                        <li><a href="profile.php">Mon Compte</a></li>
                        <li><a href="#">Livraison & Retour</a></li>
                        <li><a href="#contact">Contactez-Nous</a></li>
                        <li><a href="#">Guide des Tailles</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Services</h4>
                    <ul class="footer-links">
                        <li><a href="homepage.php">Accueil</a></li>
                        <li><a href="store.php">Boutique</a></li>
                        <li><a href="cart.php">Panier</a></li>
                        <li><a href="#">Promotions</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Contact</h4>
                    <div class="footer-contact">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Alger, Algérie</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:+213789123456">+213 789 123 456</a>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:mshistanbul@gmail.com">mshistanbul@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; 2025 MSH Istanbul. Tous droits réservés.</p>
                </div>
                <div class="footer-social">
                    <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="homepage.js"></script>
</body>
</html>
