<?php
require_once './config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $price = $_POST['price'] ?? 0;
                $stock = $_POST['stock'] ?? 0;
                $category = $_POST['category'] ?? '';
                $description = $_POST['description'] ?? '';
                $color = $_POST['color'] ?? '';
                $size = $_POST['size'] ?? '';
                
                $stmt = $conn->prepare("INSERT INTO products (name, price, stock, category, description, color, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdissss", $name, $price, $stock, $category, $description, $color, $size);
                
                if ($stmt->execute()) {
                    $productId = $conn->insert_id;
                    if (isset($_FILES['image'])) {
                        handleImageUpload($productId);
                    }
                    $success_message = "Article ajouté avec succès!";
                } else {
                    $error_message = "Erreur lors de l'ajout de l'article.";
                }
                break;

            case 'edit':
                $id = $_POST['id'] ?? 0;
                $name = $_POST['name'] ?? '';
                $price = $_POST['price'] ?? 0;
                $stock = $_POST['stock'] ?? 0;
                $category = $_POST['category'] ?? '';
                $description = $_POST['description'] ?? '';
                $color = $_POST['color'] ?? '';
                $size = $_POST['size'] ?? '';
                
                $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=?, color=?, size=? WHERE id=?");
                $stmt->bind_param("sdissssi", $name, $price, $stock, $category, $description, $color, $size, $id);
                
                if ($stmt->execute()) {
                    if (isset($_FILES['image'])) {
                        handleImageUpload($id);
                    }
                    $success_message = "Article modifié avec succès!";
                } else {
                    $error_message = "Erreur lors de la modification de l'article.";
                }
                break;

            case 'delete':
                $id = $_POST['id'] ?? 0;
                $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success_message = "Article supprimé avec succès!";
                } else {
                    $error_message = "Erreur lors de la suppression de l'article.";
                }
                break;
        }
    }
}

// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<div id="articles-section" class="admin-section">
    <h2 class="section-title">Gestion des Articles</h2>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="post" action="?section=articles" enctype="multipart/form-data" class="article-form">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="" id="edit-id">
        
        <div class="form-group">
            <label for="name">Nom de l'article</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="price">Prix</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" required>
        </div>
        
        <div class="form-group">
            <label for="category">Catégorie</label>
            <select id="category" name="category" required>
                <option value="suits">Costumes</option>
                <option value="shirts">Chemises</option>
                <option value="pants">Pantalons</option>
                <option value="accessories">Accessoires</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="color">Couleur</label>
            <input type="text" id="color" name="color">
        </div>
        
        <div class="form-group">
            <label for="size">Taille</label>
            <input type="text" id="size" name="size">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="image">Image</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
    </form>

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
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="image-preview" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Crect width='50' height='50' fill='%23D4AE6A'/%3E%3Ctext x='25' y='30' text-anchor='middle' fill='%23fff' font-size='12'%3EPhoto%3C/text%3E%3C/svg%3E" class="image-preview">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo number_format($product['price'], 2); ?> DA</td>
                <td><?php echo $product['stock']; ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td>
                    <form method="post" action="?section=articles" style="display: inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <button type="button" class="btn btn-info" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Modifier</button>
                    </form>
                    <form method="post" action="?section=articles" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function editProduct(product) {
    document.querySelector('input[name="action"]').value = 'edit';
    document.querySelector('#edit-id').value = product.id;
    document.querySelector('#name').value = product.name;
    document.querySelector('#price').value = product.price;
    document.querySelector('#stock').value = product.stock;
    document.querySelector('#category').value = product.category;
    document.querySelector('#color').value = product.color || '';
    document.querySelector('#size').value = product.size || '';
    document.querySelector('#description').value = product.description || '';
    
    document.querySelector('.article-form').scrollIntoView({ behavior: 'smooth' });
}
</script>