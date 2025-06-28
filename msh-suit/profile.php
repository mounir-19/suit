<?php
require_once 'config.php';
require_once 'auth.php';


// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
    header('Location: login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user orders
$stmt = $conn->prepare("
    SELECT o.*, 
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    $errors = [];
    
    if (empty($first_name)) $errors[] = "Le prénom est requis";
    if (empty($last_name)) $errors[] = "Le nom est requis";
    if (empty($email)) $errors[] = "L'email est requis";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format d'email invalide";
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Cet email est déjà utilisé";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Profil mis à jour avec succès";
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Erreur lors de la mise à jour";
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $password_errors = [];
    
    if (empty($current_password)) $password_errors[] = "Le mot de passe actuel est requis";
    if (empty($new_password)) $password_errors[] = "Le nouveau mot de passe est requis";
    if (strlen($new_password) < 6) $password_errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    if ($new_password !== $confirm_password) $password_errors[] = "Les mots de passe ne correspondent pas";
    
    if (empty($password_errors)) {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $password_success = "Mot de passe modifié avec succès";
            } else {
                $password_errors[] = "Erreur lors de la modification";
            }
        } else {
            $password_errors[] = "Mot de passe actuel incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - MSH-ISTANBUL</title>
    <link rel="stylesheet" href="store.css">
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        .profile-sidebar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        .profile-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .profile-nav li {
            margin-bottom: 10px;
        }
        .profile-nav a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .profile-nav a:hover, .profile-nav a.active {
            background: #D4AE6A;
            color: white;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            background: #D4AE6A;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #B8954A;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .orders-table th, .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .orders-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
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
            <a href="cart.php">Cart</a>
            <a href="profile.php">Account</a>
            <a href="?logout=1">Logout</a>
        </div>
    </div>
    <hr />

    <div class="profile-container">
        <div class="profile-sidebar">
            <h3>Bonjour, <?php echo htmlspecialchars($user['first_name']); ?>!</h3>
            <ul class="profile-nav">
                <li><a href="#" onclick="showTab('profile')" class="tab-link active">Mon Profil</a></li>
                <li><a href="#" onclick="showTab('orders')" class="tab-link">Mes Commandes</a></li>
                <li><a href="#" onclick="showTab('password')" class="tab-link">Changer le mot de passe</a></li>
                <li><a href="?logout=1">Déconnexion</a></li>
            </ul>
        </div>

        <div class="profile-content">
            <!-- Profile Tab -->
            <div id="profile" class="tab-content active">
                <h2>Informations personnelles</h2>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Nom</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Téléphone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn">Mettre à jour</button>
                </form>
            </div>

            <!-- Orders Tab -->
            <div id="orders" class="tab-content">
                <h2>Mes Commandes</h2>
                
                <?php if (empty($orders)): ?>
                    <p>Vous n'avez pas encore passé de commande.</p>
                    <a href="store.php" class="btn">Découvrir nos produits</a>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Articles</th>
                                <th>Total</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($order['items'] ?: 'Aucun article'); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?> DA</td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php 
                                        switch($order['status']) {
                                            case 'pending': echo 'En attente'; break;
                                            case 'processing': echo 'En cours'; break;
                                            case 'shipped': echo 'Expédiée'; break;
                                            case 'delivered': echo 'Livrée'; break;
                                            default: echo ucfirst($order['status']);
                                        }
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Password Tab -->
            <div id="password" class="tab-content">
                <h2>Changer le mot de passe</h2>
                
                <?php if (isset($password_success)): ?>
                    <div class="alert alert-success"><?php echo $password_success; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($password_errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($password_errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn">Changer le mot de passe</button>
                </form>
            </div>
        </div>
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
    function showTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all tab links
        document.querySelectorAll('.tab-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById(tabName).classList.add('active');
        
        // Add active class to clicked link
        event.target.classList.add('active');
    }
    </script>
</body>
</html>