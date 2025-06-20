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
?>
<?php require_once 'admin_api.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Kadwa:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="admin.js"></script>
    <link rel="stylesheet" href="admin.css">
    <title>Admin Panel - MSH-ISTANBOUL</title>
</head>
<body>
    
  <!-- Header -->
    <div class="header">
        <div class="menu-toggle" id="menuToggle" style="display: none;">
            <i class="fas fa-bars"></i>
        </div>
        
    <img src="logo.png" alt="" class="logo">        
        <p class="name">MSH-ISTANBOUL ADMIN</p>

        <div class="user" id="user">
            <a href="#settings">
                <i class="fas fa-user"></i>
                Profil
            </a>
            <a href="?logout=1">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </div>
    </div>

    <div class="bar">
        <p>Tableau de bord administrateur - Gestion complète de votre boutique</p>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="nav" id="nav">
            <a href="#dashboard" class="nav-link active">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            <a href="#articles" class="nav-link">
                <i class="fas fa-shirt"></i>
                Articles
            </a>
            <a href="#orders" class="nav-link">
                <i class="fas fa-shopping-cart"></i>
                Commandes
            </a>
            <a href="#offers" class="nav-link">
                <i class="fas fa-tags"></i>
                Offres
            </a>
            <a href="#settings" class="nav-link">
                <i class="fas fa-cog"></i>
                Paramètres
            </a>
        </div>
    </div>

    <div class="admin-container">
        <!-- Dashboard Section -->
        <div id="dashboard-section" class="admin-section">
            <h2 class="section-title">Tableau de Bord</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="total-articles">24</div>
                    <div class="stat-label">Articles Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="pending-orders">8</div>
                    <div class="stat-label">Commandes en Attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="active-offers">3</div>
                    <div class="stat-label">Offres Actives</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="monthly-sales">15,420 DA</div>
                    <div class="stat-label">Ventes ce Mois</div>
                </div>
            </div>
        </div>

        <!-- Articles Section -->
        <div id="articles-section" class="admin-section" style="display: none;">
            <h2 class="section-title">Gestion des Articles</h2>
            <button class="btn" onclick="openModal('article-modal')">
                <i class="fas fa-plus"></i> Ajouter Article
            </button>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="articles-table">
                    <tr>
                        <td><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Crect width='50' height='50' fill='%23D4AE6A'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%23fff' font-size='12'%3EPhoto%3C/text%3E%3C/svg%3E" class="image-preview"></td>
                        <td>Costume Mariage Classic</td>
                        <td>25,000 DA</td>
                        <td>12</td>
                        <td>Mariage</td>
                        <td>
                            <button class="btn btn-info" onclick="editArticle(1)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteArticle(1)">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Crect width='50' height='50' fill='%23D4AE6A'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%23fff' font-size='12'%3EPhoto%3C/text%3E%3C/svg%3E" class="image-preview"></td>
                        <td>Costume Bureau Élégant</td>
                        <td>18,500 DA</td>
                        <td>8</td>
                        <td>Bureau</td>
                        <td>
                            <button class="btn btn-info" onclick="editArticle(2)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteArticle(2)">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Crect width='50' height='50' fill='%23D4AE6A'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%23fff' font-size='12'%3EPhoto%3C/text%3E%3C/svg%3E" class="image-preview"></td>
                        <td>Smoking Noir Premium</td>
                        <td>35,000 DA</td>
                        <td>5</td>
                        <td>Cérémonie</td>
                        <td>
                            <button class="btn btn-info" onclick="editArticle(3)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteArticle(3)">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td><img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Crect width='50' height='50' fill='%23D4AE6A'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%23fff' font-size='12'%3EPhoto%3C/text%3E%3C/svg%3E" class="image-preview"></td>
                        <td>Veste Casual Moderne</td>
                        <td>12,500 DA</td>
                        <td>15</td>
                        <td>Casual</td>
                        <td>
                            <button class="btn btn-info" onclick="editArticle(4)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteArticle(4)">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Orders Section -->
        <div id="orders-section" class="admin-section" style="display: none;">
            <h2 class="section-title">Gestion des Commandes</h2>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="orders-table">
                    <tr>
                        <td>#CMD001</td>
                        <td>Ahmed Benali</td>
                        <td>12/06/2025</td>
                        <td>25,000 DA</td>
                        <td><span class="order-status status-pending">En Attente</span></td>
                        <td>
                            <button class="btn btn-success" onclick="updateOrderStatus('CMD001', 'confirmed')">Confirmer</button>
                            <button class="btn btn-info" onclick="viewOrderDetails('CMD001')">Détails</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#CMD002</td>
                        <td>Fatima Khediri</td>
                        <td>11/06/2025</td>
                        <td>18,500 DA</td>
                        <td><span class="order-status status-shipped">Expédiée</span></td>
                        <td>
                            <button class="btn btn-success" onclick="updateOrderStatus('CMD002', 'delivered')">Livrer</button>
                            <button class="btn btn-info" onclick="viewOrderDetails('CMD002')">Détails</button>
                        </td>
                    </tr>
                    <tr>
                        <td>#CMD003</td>
                        <td>Mohamed Larbi</td>
                        <td>10/06/2025</td>
                        <td>32,000 DA</td>
                        <td><span class="order-status status-delivered">Livrée</span></td>
                        <td>
                            <button class="btn btn-info" onclick="viewOrderDetails('CMD003')">Détails</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Offers Section -->
        <div id="offers-section" class="admin-section" style="display: none;">
            <h2 class="section-title">Gestion des Offres</h2>
            <button class="btn" onclick="openModal('offer-modal')">
                <i class="fas fa-plus"></i> Ajouter Offre
            </button>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Réduction</th>
                        <th>Articles Inclus</th>
                        <th>Date Début</th>
                        <th>Date Fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="offers-table">
                    <tr>
                        <td>Collection Mariage -25%</td>
                        <td>25%</td>
                        <td>2 articles</td>
                        <td>01/06/2025</td>
                        <td>30/06/2025</td>
                        <td><span class="order-status status-confirmed">Active</span></td>
                        <td>
                            <button class="btn btn-info" onclick="editOffer(1)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteOffer(1)">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Promo Spéciale Été</td>
                        <td>20%</td>
                        <td>3 articles</td>
                        <td>15/06/2025</td>
                        <td>15/08/2025</td>
                        <td><span class="order-status status-pending">Programmée</span></td>
                        <td>
                            <button class="btn btn-info" onclick="editOffer(2)">Modifier</button>
                            <button class="btn btn-danger" onclick="deleteOffer(2)">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Settings Section -->
        <div id="settings-section" class="admin-section" style="display: none;">
            <h2 class="section-title">Paramètres</h2>
            <p style="text-align: center; color: #6c757d; margin-top: 40px;">Section des paramètres en développement...</p>
        </div>
    </div>

    <!-- Article Modal -->
    <div id="article-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('article-modal')">&times;</span>
            <h2>Ajouter/Modifier Article</h2>
            <form id="article-form" method="post" enctype="multipart/form-data">
                <input type="hidden" id="article-id" name="id">
                <div class="form-group">
                    <label>Nom de l'article</label>
                    <input type="text" id="article-name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Prix (DA)</label>
                    <input type="number" id="article-price" name="price" required>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" id="article-stock" name="stock" required>
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select id="article-category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <option value="mariage">Mariage</option>
                        <option value="bureau">Bureau</option>
                        <option value="ceremonie">Cérémonie</option>
                        <option value="casual">Casual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="article-description" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" id="article-image" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </form>
        </div>
    </div>

    <!-- Enhanced Offer Modal with Article Selection -->
    <div id="offer-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('offer-modal')">&times;</span>
            <h2 style="color: #2D2F3A; margin-bottom: 20px;">Ajouter/Modifier Offre</h2>
            <form id="offer-form">
                <div class="form-group">
                    <label>Titre de l'offre</label>
                    <input type="text" id="offer-title" required>
                </div>
                <div class="form-group">
                    <label>Pourcentage de réduction (%)</label>
                    <input type="number" id="offer-discount" min="1" max="100" required>
                </div>
                <div class="form-group">
                    <label>Date de début</label>
                    <input type="date" id="offer-start" required>
                </div>
                <div class="form-group">
                    <label>Date de fin</label>
                    <input type="date" id="offer-end" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="offer-description" rows="3"></textarea>
                </div>
                
                <!-- New Article Selection Section -->
                <div class="form-group">
                    <label>Sélectionner les articles pour cette offre</label>
                    <div class="articles-selection">
                        <h4>Articles disponibles :</h4>
                        <div id="articles-list">
                            <!-- Articles will be populated here -->
                        </div>
                        <div class="selected-articles" id="selected-articles" style="display: none;">
                            <h5>Articles sélectionnés :</h5>
                            <div id="selected-articles-list"></div>
                            <div class="selected-count" id="selected-count">0 article(s) sélectionné(s)</div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn">Enregistrer Offre</button>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="order-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('order-modal')">&times;</span>
            <h2>Détails de la Commande</h2>
            <div id="order-details"></div>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; }
        .nav-link { color: #fff; }
        .nav-link:hover { color: #f8f9fa; }
        .active { background: #495057; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#dashboard" data-toggle="tab">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#products" data-toggle="tab">
                                <i class="fas fa-box"></i> Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#orders" data-toggle="tab">
                                <i class="fas fa-shopping-cart"></i> Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?logout=1">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main role="main" class="col-md-10 ml-sm-auto px-4">
                <div class="tab-content" id="myTabContent">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1>Dashboard</h1>
                        </div>
                        <div class="row" id="dashboard-stats">
                            <!-- Dashboard stats will be loaded here -->
                        </div>
                    </div>

                    <!-- Products Tab -->
                    <div class="tab-pane fade" id="products">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1>Products</h1>
                            <button class="btn btn-primary" data-toggle="modal" data-target="#productModal">
                                Add New Product
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Category</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="products-table">
                                    <!-- Products will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Orders Tab -->
                    <div class="tab-pane fade" id="orders">
                        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                            <h1>Orders</h1>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table">
                                    <!-- Orders will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="productForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="productId">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" class="form-control" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select class="form-control" name="category" required>
                                <option value="suits">Suits</option>
                                <option value="shirts">Shirts</option>
                                <option value="pants">Pants</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Load dashboard stats
        function loadDashboardStats() {
            $.get('admin_api.php?action=get_dashboard_stats', function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#dashboard-stats').html(`
                        <div class="col-md-3 mb-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Total Products</h5>
                                    <h2>${stats.total_products}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5>Pending Orders</h5>
                                    <h2>${stats.pending_orders}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5>Active Offers</h5>
                                    <h2>${stats.active_offers}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Monthly Sales</h5>
                                    <h2>$${stats.monthly_sales}</h2>
                                </div>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // Load products
        function loadProducts() {
            $.get('admin_api.php?action=get_products', function(response) {
                if (response.success) {
                    $('#products-table').empty();
                    response.data.forEach(function(product) {
                        $('#products-table').append(`
                            <tr>
                                <td>${product.id}</td>
                                <td><img src="${product.image_url || 'placeholder.jpg'}" width="50"></td>
                                <td>${product.name}</td>
                                <td>$${product.price}</td>
                                <td>${product.stock}</td>
                                <td>${product.category}</td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-product" data-product='${JSON.stringify(product)}'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }

        // Load orders
        function loadOrders() {
            $.get('admin_api.php?action=get_orders', function(response) {
                if (response.success) {
                    $('#orders-table').empty();
                    response.data.forEach(function(order) {
                        const itemsList = order.items.map(item => 
                            `${item.name} (${item.quantity}x)`
                        ).join(', ');
                        
                        $('#orders-table').append(`
                            <tr>
                                <td>${order.id}</td>
                                <td>${order.first_name} ${order.last_name}<br>${order.email}</td>
                                <td>${itemsList}</td>
                                <td>$${order.total_amount}</td>
                                <td>
                                    <select class="form-control order-status" data-id="${order.id}">
                                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </td>
                                <td>${new Date(order.created_at).toLocaleDateString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-order" data-order='${JSON.stringify(order)}'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }

        // Handle product form submission
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const productId = $('#productId').val();
            const action = productId ? 'update_product' : 'add_product';
            
            $.ajax({
                url: `admin_api.php?action=${action}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#productModal').modal('hide');
                        loadProducts();
                        $('#productForm')[0].reset();
                        $('#productId').val('');
                    } else {
                        alert('Error saving product');
                    }
                }
            });
        });

        // Handle edit product
        $(document).on('click', '.edit-product', function() {
            const product = $(this).data('product');
            $('#productId').val(product.id);
            $('#productForm [name=name]').val(product.name);
            $('#productForm [name=price]').val(product.price);
            $('#productForm [name=stock]').val(product.stock);
            $('#productForm [name=category]').val(product.category);
            $('#productForm [name=description]').val(product.description);
            $('#productModal').modal('show');
        });

        // Handle delete product
        $(document).on('click', '.delete-product', function() {
            if (confirm('Are you sure you want to delete this product?')) {
                const productId = $(this).data('id');
                $.ajax({
                    url: 'admin_api.php?action=delete_product',
                    method: 'POST',
                    data: JSON.stringify({ id: productId }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            loadProducts();
                        } else {
                            alert('Error deleting product');
                        }
                    }
                });
            }
        });

        // Handle order status change
        $(document).on('change', '.order-status', function() {
            const orderId = $(this).data('id');
            const status = $(this).val();
            
            $.ajax({
                url: 'admin_api.php?action=update_order_status',
                method: 'POST',
                data: JSON.stringify({ id: orderId, status: status }),
                contentType: 'application/json',
                success: function(response) {
                    if (!response.success) {
                        alert('Error updating order status');
                    }
                }
            });
        });

        // Load initial data
        loadDashboardStats();
        loadProducts();
        loadOrders();

        // Handle tab changes
        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            if (target === '#dashboard') loadDashboardStats();
            else if (target === '#products') loadProducts();
            else if (target === '#orders') loadOrders();
        });

        // Reset form when modal is closed
        $('#productModal').on('hidden.bs.modal', function() {
            $('#productForm')[0].reset();
            $('#productId').val('');
        });
    </script>
</body>
</html>