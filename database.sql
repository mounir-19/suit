-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS suit_store;
CREATE DATABASE suit_store;
USE suit_store;

-- Users table with role field
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_admin BOOLEAN DEFAULT FALSE,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products table with all required fields
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category VARCHAR(50) NOT NULL,
    color VARCHAR(50),
    size VARCHAR(20),
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_price (price),
    INDEX idx_stock (stock)
);

-- Orders table with order_date field
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Shipping addresses table
CREATE TABLE shipping_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'Morocco',
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- Offers table
CREATE TABLE offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_percentage DECIMAL(5,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_active (is_active)
);

-- Offer products table
CREATE TABLE offer_products (
    offer_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (offer_id, product_id),
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Cart table for persistent cart storage
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    session_id VARCHAR(255),
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_product_id (product_id)
);

-- Admin logs table for tracking admin actions
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, role, is_admin) VALUES 
('admin@msh-istanbul.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', TRUE);

-- Insert sample users
INSERT INTO users (email, password, first_name, last_name, role, phone) VALUES 
('user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'user', '+212600000001'),
('user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'user', '+212600000002');

-- Insert sample products
INSERT INTO products (name, description, price, stock, category, color, size, image_url) VALUES 
('Costume Mariage Classic', 'Costume élégant pour mariage avec finitions de qualité supérieure. Parfait pour les grandes occasions.', 25000.00, 12, 'suits', 'Noir', 'L', 'uploads/products/suit1.jpg'),
('Costume Bureau Élégant', 'Costume professionnel parfait pour le bureau. Coupe moderne et confortable.', 18500.00, 8, 'suits', 'Bleu Marine', 'M', 'uploads/products/suit2.jpg'),
('Smoking Noir Premium', 'Smoking de luxe pour les grandes occasions. Matériaux premium et finitions exceptionnelles.', 35000.00, 5, 'suits', 'Noir', 'L', 'uploads/products/suit3.jpg'),
('Veste Casual Moderne', 'Veste décontractée pour un look moderne et sophistiqué.', 12500.00, 15, 'jackets', 'Gris', 'M', 'uploads/products/jacket1.jpg'),
('Chemise Blanche Classic', 'Chemise blanche classique en coton premium. Indispensable dans toute garde-robe.', 4500.00, 20, 'shirts', 'Blanc', 'L', 'uploads/products/shirt1.jpg'),
('Pantalon Costume Noir', 'Pantalon de costume noir coupe droite. Parfait pour compléter votre tenue professionnelle.', 8500.00, 18, 'pants', 'Noir', 'L', 'uploads/products/pants1.jpg'),
('Cravate Soie Premium', 'Cravate en soie naturelle de haute qualité. Finition parfaite pour vos costumes.', 2500.00, 25, 'accessories', 'Rouge', 'One Size', 'uploads/products/tie1.jpg'),
('Chaussures Cuir Oxford', 'Chaussures Oxford en cuir véritable. Confort et élégance réunis.', 15000.00, 10, 'accessories', 'Noir', '42', 'uploads/products/shoes1.jpg'),
('Costume Trois Pièces', 'Ensemble complet trois pièces pour les occasions spéciales.', 32000.00, 6, 'suits', 'Charcoal', 'L', 'uploads/products/suit4.jpg'),
('Chemise Rayée Business', 'Chemise rayée élégante pour le business. Coton de qualité supérieure.', 5500.00, 14, 'shirts', 'Bleu', 'M', 'uploads/products/shirt2.jpg');

-- Insert sample orders
INSERT INTO orders (user_id, total_amount, status, order_date) VALUES 
(2, 25000.00, 'delivered', '2024-01-15 10:30:00'),
(3, 18500.00, 'shipped', '2024-01-20 14:45:00'),
(2, 12500.00, 'processing', '2024-01-22 09:15:00');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES 
(1, 1, 1, 25000.00),
(2, 2, 1, 18500.00),
(3, 4, 1, 12500.00);

-- Insert sample shipping addresses
INSERT INTO shipping_addresses (order_id, first_name, last_name, address_line_1, city, postal_code, country, phone) VALUES 
(1, 'John', 'Doe', '123 Rue Mohammed V', 'Casablanca', '20000', 'Morocco', '+212600000001'),
(2, 'Jane', 'Smith', '456 Avenue Hassan II', 'Rabat', '10000', 'Morocco', '+212600000002'),
(3, 'John', 'Doe', '123 Rue Mohammed V', 'Casablanca', '20000', 'Morocco', '+212600000001');

-- Insert sample offers
INSERT INTO offers (name, description, discount_percentage, start_date, end_date) VALUES 
('Collection Mariage -25%', 'Remise exceptionnelle sur notre collection mariage premium', 25.00, '2024-01-01', '2024-12-31'),
('Promo Spéciale Été', 'Offre limitée sur une sélection de produits été', 20.00, '2024-06-01', '2024-08-31'),
('Black Friday', 'Méga promotion Black Friday sur tous les costumes', 30.00, '2024-11-24', '2024-11-30');

-- Link offers to products
INSERT INTO offer_products (offer_id, product_id) VALUES 
(1, 1), (1, 3), (1, 9),
(2, 2), (2, 4), (2, 5),
(3, 1), (3, 2), (3, 3), (3, 9);

-- Insert sample contact messages
INSERT INTO contact_messages (name, email, phone, message) VALUES 
('Ahmed Benali', 'ahmed@example.com', '+212600000003', 'Bonjour, je souhaiterais avoir plus d\'informations sur vos costumes de mariage.'),
('Fatima Alami', 'fatima@example.com', '+212600000004', 'Avez-vous des costumes disponibles en taille XL?');