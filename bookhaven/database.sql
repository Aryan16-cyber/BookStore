-- ============================================================
--  BookHaven Online Bookstore — Database Setup
--  Run this file in phpMyAdmin or MySQL CLI before starting
-- ============================================================

CREATE DATABASE IF NOT EXISTS bookhaven CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bookhaven;

-- ─── USERS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100),
    email       VARCHAR(191) UNIQUE NOT NULL,
    password    VARCHAR(255) NOT NULL,
    phone       VARCHAR(20),
    address     TEXT,
    user_type   ENUM('customer','admin') DEFAULT 'customer',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─── CATEGORIES ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    c_id    INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    icon    VARCHAR(10) DEFAULT '📚'
);

-- ─── BOOKS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS books (
    book_id         INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    author          VARCHAR(255) NOT NULL,
    c_id            INT,
    price           DECIMAL(10,2) NOT NULL,
    original_price  DECIMAL(10,2),
    stock           INT DEFAULT 0,
    rating          DECIMAL(2,1) DEFAULT 4.0,
    description     TEXT,
    publisher       VARCHAR(255),
    cover_icon      VARCHAR(10) DEFAULT '📚',
    cover_bg        VARCHAR(20) DEFAULT '#f5f5f5',
    badge           VARCHAR(50) DEFAULT '',
    sold            INT DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (c_id) REFERENCES categories(c_id) ON DELETE SET NULL
);

-- ─── CART ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cart (
    cart_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    book_id     INT NOT NULL,
    quantity    INT DEFAULT 1,
    added_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, book_id)
);

-- ─── ORDERS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    order_id        INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    status          ENUM('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    payment_method  VARCHAR(50) DEFAULT 'COD',
    shipping_addr   TEXT,
    order_date      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ─── ORDER ITEMS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id   INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    book_id         INT NOT NULL,
    quantity        INT NOT NULL,
    price           DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id)  REFERENCES books(book_id)
);

-- ─── PAYMENTS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payments (
    payment_id      INT AUTO_INCREMENT PRIMARY KEY,
    order_id        INT NOT NULL,
    user_id         INT NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    payment_method  VARCHAR(50),
    status          ENUM('Success','Failed','Pending') DEFAULT 'Pending',
    payment_date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (user_id)  REFERENCES users(user_id)
);

-- ─── SEED DATA ────────────────────────────────────────────
INSERT INTO categories (name, icon) VALUES
('Fiction','🔮'), ('Technology','💻'), ('Self-Help','🌱'),
('History','🏛️'), ('Science','🔬'), ('Biography','👤'), ('Mystery','🕵️');

INSERT INTO books (title, author, c_id, price, original_price, stock, rating, description, publisher, cover_icon, cover_bg, badge, sold) VALUES
('The Great Gatsby',       'F. Scott Fitzgerald', 1, 299,  399,  42, 4.7, 'A story of decadence and excess, Gatsby explores the American Dream through the eyes of narrator Nick Carraway.',               'Scribner',         '🌸', '#fdf0f5', 'Bestseller', 120),
('Clean Code',             'Robert C. Martin',    2, 499,  699,  28, 4.9, 'A handbook of agile software craftsmanship. Learn principles that help developers write better, cleaner code.',               'Prentice Hall',    '💻', '#f0f4ff', 'Hot',        98),
('Atomic Habits',          'James Clear',         3, 349,  450,  65, 4.8, 'An easy and proven way to build good habits and break bad ones. Reshape the way you think about progress.',                   'Avery',            '⚡', '#fffbf0', 'Bestseller', 215),
('Sapiens',                'Yuval Noah Harari',   4, 399,  499,  31, 4.6, 'A brief history of humankind. From the Stone Age to the 21st century, Harari charts humanity\'s extraordinary rise.',        'Harper',           '🌍', '#f0fdf4', '',           87),
('A Brief History of Time','Stephen Hawking',     5, 299,  350,  19, 4.5, 'From the Big Bang to Black Holes. Hawking brings complex cosmological concepts to the general reader.',                       'Bantam',           '🔭', '#f5f0ff', 'Classic',    64),
('Steve Jobs',             'Walter Isaacson',     6, 449,  550,  23, 4.7, 'The exclusive biography based on interviews with Jobs, family members, friends, and colleagues.',                              'Simon & Schuster', '🍎', '#fff0f0', '',           73),
('Gone Girl',              'Gillian Flynn',       7, 349,  399,  38, 4.4, 'On the morning of their fifth anniversary, Nick Dunne\'s wife Amy suddenly disappears.',                                     'Crown',            '🕵️','#f0f0f0', 'Thriller',   55),
('The Alchemist',          'Paulo Coelho',        1, 249,  299,  54, 4.8, 'A magical story about following your dreams. Santiago travels from Spain to Egypt in search of treasure.',                    'HarperOne',        '✨', '#fffaf0', 'Bestseller', 188),
('Deep Work',              'Cal Newport',         3, 379,  450,  44, 4.6, 'Rules for focused success in a distracted world. The ability to perform deep work is increasingly rare.',                     'Grand Central',    '🎯', '#f0f7ff', 'New',        42),
('The Da Vinci Code',      'Dan Brown',           7, 299,  349,  61, 4.3, 'Renowned curator Jacques Saunière is found murdered in the Louvre. Symbologist Robert Langdon investigates.',                'Doubleday',        '🔑', '#fdf5f0', '',           101),
('Cosmos',                 'Carl Sagan',          5, 349,  399,  16, 4.9, 'A personal voyage through our universe. Sagan celebrates the spirit of inquiry that led humanity to understand the cosmos.',  'Random House',     '🌌', '#f0f0ff', 'Classic',    79),
('Zero to One',            'Peter Thiel',         2, 399,  499,  33, 4.5, 'Notes on Startups, or How to Build the Future. Thiel shares contrarian thinking behind his most notable insights.',          'Crown Business',   '🚀', '#f0fff4', 'New',        58);

-- Admin account (password: admin123)
INSERT INTO users (first_name, last_name, email, password, user_type) VALUES
('Admin', 'BookHaven', 'admin@bookhaven.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Note: default password hash above = 'password'. For production use a proper hash.
-- To set admin123: UPDATE users SET password=PASSWORD_HASH WHERE email='admin@bookhaven.com';
