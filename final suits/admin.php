<?php
require_once 'auth.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    logout();
    header('Location: login.php');
    exit;
}

$current_section = $_GET['section'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - MSH-ISTANBUL</title>
    <link rel="stylesheet" href="styles/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" alt="MSH-ISTANBUL" class="logo">
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="sidebar-nav">
                <a href="?section=dashboard" class="nav-item <?php echo $current_section === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
                <a href="?section=products" class="nav-item <?php echo $current_section === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-shirt"></i>
                    <span>Produits</span>
                </a>
                <a href="?section=orders" class="nav-item <?php echo $current_section === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
                <a href="?section=offers" class="nav-item <?php echo $current_section === 'offers' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Offres</span>
                </a>
                <a href="?section=customers" class="nav-item <?php echo $current_section === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
                <a href="?section=messages" class="nav-item <?php echo $current_section === 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="?section=settings" class="nav-item <?php echo $current_section === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Retour au Site</span>
                </a>
                <a href="?logout=1" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="mobile-menu-btn" id="mobileMenuBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1><?php 
                        $titles = [
                            'dashboard' => 'Tableau de Bord',
                            'products' => 'Gestion des Produits',
                            'orders' => 'Gestion des Commandes',
                            'offers' => 'Gestion des Offres',
                            'customers' => 'Gestion des Clients',
                            'messages' => 'Messages de Contact',
                            'settings' => 'Paramètres'
                        ];
                        echo $titles[$current_section] ?? 'Admin Panel';
                    ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <i class="fas fa-user-shield"></i>
                        <span>Administrateur</span>
                    </div>
                </div>
            </header>
            
            <div class="content-body">
                <?php
                switch($current_section) {
                    case 'dashboard':
                        include 'admin/dashboard.php';
                        break;
                    case 'products':
                        include 'admin/products.php';
                        break;
                    case 'orders':
                        include 'admin/orders.php';
                        break;
                    case 'offers':
                        include 'admin/offers.php';
                        break;
                    case 'customers':
                        include 'admin/customers.php';
                        break;
                    case 'messages':
                        include 'admin/messages.php';
                        break;
                    case 'settings':
                        include 'admin/settings.php';
                        break;
                    default:
                        include 'admin/dashboard.php';
                }
                ?>
            </div>
        </main>
    </div>
    
    <script src="js/admin.js"></script>
</body>
</html>