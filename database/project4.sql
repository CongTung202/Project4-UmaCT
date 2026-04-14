CREATE DATABASE IF NOT EXISTS umact_db;
USE umact_db;

-- ==========================================
-- 1. NHÓM TÀI KHOẢN & PHÂN QUYỀN
-- ==========================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE
);
INSERT INTO roles (role_name) VALUES ('ROLE_ADMIN'), ('ROLE_USER'), ('ROLE_STAFF');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    address VARCHAR(255),
    avatar_url VARCHAR(255),
    role_id INT,
    status ENUM('ACTIVE', 'BANNED') DEFAULT 'ACTIVE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
INSERT INTO users (username, password, full_name, email, role_id) VALUES 
('admin_umact', 'hashed_pwd_admin', 'Quản trị viên', 'admin@umact.com', 1),
('bokachan', 'hashed_pwd_boka', 'Boka Chan', 'bokachan@gmail.com', 2),
('customer01', 'hashed_pwd_cust', 'Khách hàng 1', 'khach1@gmail.com', 2);

-- ==========================================
-- 2. NHÓM SẢN PHẨM & DANH MỤC
-- ==========================================
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_info VARCHAR(100),
    address VARCHAR(255)
);
INSERT INTO suppliers (name, contact_info, address) VALUES 
('Good Smile Company', 'contact@goodsmile.jp', 'Tokyo, Japan'),
('Cygames Store', 'support@cygames.co.jp', 'Shibuya, Japan');

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE
);
INSERT INTO categories (name, slug) VALUES 
('Mô hình / Figure', 'mo-hinh-figure'),
('Trang phục / Cosplay', 'trang-phuc-cosplay'),
('Phụ kiện', 'phu-kien');

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    supplier_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2) DEFAULT 0,
    stock_quantity INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);
INSERT INTO products (category_id, supplier_id, name, description, price, stock_quantity) VALUES 
(1, 1, 'Nendoroid Special Week', 'Mô hình Nendoroid Special Week chính hãng kèm phụ kiện', 1200000, 50),
(2, 2, 'Áo khoác Tokai Teio', 'Áo khoác dạo phố form chuẩn nguyên bản', 850000, 20),
(3, 2, 'Móc khóa Silence Suzuka', 'Móc khóa acrylic trong suốt', 50000, 200);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_url VARCHAR(255) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ==========================================
-- 3. NHÓM TƯƠNG TÁC NGƯỜI DÙNG
-- ==========================================
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, product_id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ==========================================
-- 4. NHÓM KHUYẾN MÃI & GIAO DỊCH
-- ==========================================
CREATE TABLE vouchers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_amount DECIMAL(10, 2) NOT NULL,
    min_order_value DECIMAL(10, 2),
    usage_limit INT,
    expiration_date DATETIME
);
INSERT INTO vouchers (code, discount_amount, min_order_value, usage_limit, expiration_date) VALUES 
('UMA100K', 100000, 500000, 100, '2026-12-31 23:59:59'),
('FREESHIP', 30000, 200000, 500, '2026-06-30 23:59:59');

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    voucher_id INT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    payment_method TINYINT DEFAULT 1 COMMENT '1: COD, 2: MOMO, 3: PAYOS, 4: VNPAY',
    status ENUM('PENDING', 'PAID', 'SHIPPING', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id)
);
INSERT INTO orders (user_id, voucher_id, total_price, shipping_address, payment_method, status) VALUES 
(2, 1, 1100000, '123 Đường A, Quận B', 4, 'PAID'),
(3, NULL, 50000, '456 Đường C, Quận D', 1, 'PENDING');

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES 
(1, 1, 1, 1200000),
(2, 3, 1, 50000);

CREATE TABLE user_voucher_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    order_id INT NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    UNIQUE KEY unique_user_voucher (user_id, voucher_id) 
);
-- Ghi nhận lịch sử đã sử dụng voucher UMA100K của Boka Chan
INSERT INTO user_voucher_usage (user_id, voucher_id, order_id) VALUES (2, 1, 1);

-- ==========================================
-- 5. NHÓM TIN TỨC & TIỆN ÍCH
-- ==========================================
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);
INSERT INTO articles (title, content, author_id) VALUES 
('Thông báo ra mắt Nendoroid mới', 'Nội dung chi tiết bài viết...', 1);

CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    position VARCHAR(50)
);