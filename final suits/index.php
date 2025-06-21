<?php
require_once 'config.php';
session_start();

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $message);
        
        if ($stmt->execute()) {
            $contact_success = "Votre message a été envoyé avec succès!";
        } else {
            $contact_error = "Erreur lors de l'envoi du message.";
        }
    } else {
        $contact_error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSH-ISTANBUL - Costumes de Luxe</title>
    <link rel="stylesheet" href="styles/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="assets/images/logo.png" alt="MSH-ISTANBUL">
                    <span class="brand-name">MSH-ISTANBUL</span>
                </div>
                
                <nav class="nav" id="nav">
                    <a href="#home">Accueil</a>
                    <a href="store.php">Catalogue</a>
                    <a href="#about">À Propos</a>
                    <a href="#contact">Contact</a>
                </nav>
                
                <div class="user-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="cart-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count" id="cartCount">0</span>
                        </a>
                        <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="admin.php"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                        <a href="signup.php"><i class="fas fa-user-plus"></i> Inscription</a>
                    <?php endif; ?>
                </div>
                
                <div class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Promotional Banner -->
    <div class="promo-banner">
        <div class="promo-content">
            <span>🎉 Nouvelle Collection Automne-Hiver 2024 - Jusqu'à 30% de réduction!</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>L'Élégance à l'État Pur</h1>
            <p>Découvrez notre collection exclusive de costumes sur mesure, alliant tradition turque et modernité européenne.</p>
            <div class="hero-buttons">
                <a href="store.php" class="btn btn-primary">Découvrir la Collection</a>
                <a href="#about" class="btn btn-secondary">En Savoir Plus</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="assets/images/hero-suit.jpg" alt="Costume élégant">
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>Pourquoi Choisir MSH-ISTANBUL?</h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-cut"></i>
                    </div>
                    <h3>Sur Mesure</h3>
                    <p>Chaque costume est confectionné selon vos mesures exactes pour un ajustement parfait.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3>Qualité Premium</h3>
                    <p>Nous utilisons uniquement les meilleurs tissus et matériaux pour garantir durabilité et élégance.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Livraison Rapide</h3>
                    <p>Livraison gratuite en Algérie sous 48h pour toute commande supérieure à 20 000 DA.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3>Expertise Turque</h3>
                    <p>Plus de 20 ans d'expérience dans la confection de costumes de luxe à Istanbul.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Contactez-Nous</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Informations de Contact</h3>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Alger, Algérie</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+213 123 456 789</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@msh-istanbul.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Lun-Sam: 9h-18h</span>
                    </div>
                </div>
                
                <form class="contact-form" method="POST">
                    <?php if (isset($contact_success)): ?>
                        <div class="alert alert-success"><?php echo $contact_success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($contact_error)): ?>
                        <div class="alert alert-error"><?php echo $contact_error; ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Votre email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Votre téléphone">
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Votre message" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="contact_submit" class="btn btn-primary">Envoyer le Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>MSH-ISTANBUL</h3>
                    <p>Votre destination pour des costumes de luxe alliant tradition et modernité.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="#home">Accueil</a></li>
                        <li><a href="store.php">Catalogue</a></li>
                        <li><a href="#about">À Propos</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="profile.php">Mon Compte</a></li>
                        <li><a href="cart.php">Panier</a></li>
                        <li><a href="#">Livraison</a></li>
                        <li><a href="#">Retours</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li>Alger, Algérie</li>
                        <li>+213 123 456 789</li>
                        <li>contact@msh-istanbul.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 MSH-ISTANBUL. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
