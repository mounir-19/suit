<?php
// Check if session is already started before calling session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/admin.php');
    } else {
        header('Location: homepage.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        if (login($email, $password)) {
            // Successful login - redirect based on role
            if (isAdmin()) {
                header('Location: admin/admin.php');
            } else {
                header('Location: homepage.php');
            }
            exit();
        } else {
            $error = "Email ou mot de passe incorrect";
        }
    } else {
        $error = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Connexion - MSH-ISTANBUL</title>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Logo MSH Istanbul" class="logo">

        <!-- Hamburger Menu Toggle -->
        <div id="menuToggle" class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        
        <!-- Navigation Links -->
        <div class="nav" id="nav">
            <a href="homepage.php">Accueil</a>
            <a href="store.php">Catalogue</a>
            <a href="homepage.php#contact">Contact</a>
        </div>
        
        <p class="name">MSH-ISTANBUL</p>

        <!-- User Actions -->
        <div class="user" id="user">
            <a href="#">Langue</a>
            <a href="cart.php">Panier</a>
            <a href="profile.php">Compte</a>
        </div>
    </div>
    <hr>

    <div class="login">
        <div class="form">
            <p class="p1">Connectez-Vous</p>
            <p class="p2">Connectez-vous pour consulter l'historique <br>de vos commandes et mettre à jour vos coordonnées.</p>
            
            <form action="" method="post">
                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; padding: 10px; background-color: #ffe6e6; border: 1px solid #ff9999; border-radius: 4px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <label for="email">Email</label><br>
                <input type="email" name="email" id="email" placeholder="Exemple@gmail.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required><br>
                
                <label for="password">Mot de passe</label><br>
                <input type="password" name="password" id="password" placeholder="Entrer votre mot de passe" required><br>
                
                <button type="submit">Connexion</button><br>
                
                <a href="signup.php" class="signup">Créer un compte</a>
                <a href="forgot-password.php" class="forgot-password" style="display: block; margin-top: 10px; text-align: center;">Mot de passe oublié ?</a>
            </form>
        </div>
    </div>
    
    <hr>
    
    <footer>
        <div class="service-client">
            <p>Service client</p>
            <a href="profile.php">Compte</a>
            <a href="#">Livraison & Retour</a>
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
        <img src="logo.png" alt="Logo of MSH Istanbul">
        <div class="copyright">&copy; 2025 MSH Istanbul. Tous droits réservés.</div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            const nav = document.getElementById('nav');
            nav.classList.toggle('active');
        });
    </script>
</body>
</html>