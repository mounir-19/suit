-- Create database
CREATE DATABASE IF NOT EXISTS suit_store;
USE suit_store;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    color VARCHAR(50),
    size VARCHAR(20),
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Offers table
CREATE TABLE offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Offer products table
CREATE TABLE offer_products (
    offer_id INT NOT NULL,
    product_id INT NOT NULL,
    PRIMARY KEY (offer_id, product_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, is_admin) VALUES 
('admin@msh-istanbul.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', TRUE);

-- Insert sample products
INSERT INTO products (name, price, stock, category, color, size, description, image_url) VALUES 
('Costume Mariage Classic', 25000.00, 12, 'suits', 'Noir', 'L', 'Costume élégant pour mariage avec finitions de qualité supérieure', 'uploads/products/suit1.jpg'),
('Costume Bureau Élégant', 18500.00, 8, 'suits', 'Bleu Marine', 'M', 'Costume professionnel parfait pour le bureau', 'uploads/products/suit2.jpg'),
('Smoking Noir Premium', 35000.00, 5, 'suits', 'Noir', 'L', 'Smoking de luxe pour les grandes occasions', 'uploads/products/suit3.jpg'),
('Veste Casual Moderne', 12500.00, 15, 'shirts', 'Gris', 'M', 'Veste décontractée pour un look moderne', 'uploads/products/jacket1.jpg'),
('Chemise Blanche Classic', 4500.00, 20, 'shirts', 'Blanc', 'L', 'Chemise blanche classique en coton premium', 'uploads/products/shirt1.jpg'),
('Pantalon Costume Noir', 8500.00, 18, 'pants', 'Noir', 'L', 'Pantalon de costume noir coupe droite', 'uploads/products/pants1.jpg'),
('Cravate Soie Premium', 2500.00, 25, 'accessories', 'Rouge', 'One Size', 'Cravate en soie naturelle', 'uploads/products/tie1.jpg'),
('Chaussures Cuir Oxford', 15000.00, 10, 'accessories', 'Noir', '42', 'Chaussures Oxford en cuir véritable', 'uploads/products/shoes1.jpg');

-- Insert sample offers
INSERT INTO offers (name, discount_percentage, start_date, end_date) VALUES 
('Collection Mariage -25%', 25.00, '2024-01-01', '2024-12-31'),
('Promo Spéciale Été', 20.00, '2024-06-01', '2024-08-31');

-- Link offers to products
INSERT INTO offer_products (offer_id, product_id) VALUES 
(1, 1), (1, 3),
(2, 2), (2, 4), (2, 5);