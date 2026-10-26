CREATE DATABASE IF NOT EXISTS kasir_sembako CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasir_sembako;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','kasir') NOT NULL DEFAULT 'kasir',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barcode VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100) NOT NULL DEFAULT 'Umum',
  unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
  buy_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  sell_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  stock DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_stock DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_products_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sales (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(40) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  subtotal DECIMAL(14,2) NOT NULL,
  discount DECIMAL(14,2) NOT NULL DEFAULT 0,
  total DECIMAL(14,2) NOT NULL,
  paid DECIMAL(14,2) NOT NULL,
  change_amount DECIMAL(14,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sale_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sale_id BIGINT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  barcode VARCHAR(80) NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit VARCHAR(30) NOT NULL,
  qty DECIMAL(12,2) NOT NULL,
  price DECIMAL(14,2) NOT NULL,
  cost_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  line_total DECIMAL(14,2) NOT NULL,
  CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  CONSTRAINT fk_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stock_movements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  movement_type ENUM('masuk','keluar','penyesuaian') NOT NULL,
  qty DECIMAL(12,2) NOT NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_stock_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT fk_stock_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Produk contoh. Ganti barcode sesuai label barang di toko Anda.
INSERT IGNORE INTO products (barcode,name,category,unit,buy_price,sell_price,stock,min_stock) VALUES
('8991002100011','Beras Premium 5 kg','Beras','karung',68000,75000,20,5),
('8991002100012','Minyak Goreng 1 L','Minyak','botol',15500,18000,30,8),
('8991002100013','Gula Pasir 1 kg','Gula','kg',14500,17000,25,5),
('8991002100014','Telur Ayam 1 kg','Telur','kg',26000,30000,15,4),
('8991002100015','Mi Instan','Makanan','pcs',2500,3000,100,20),
('8991002100016','Air Mineral 600 ml','Minuman','botol',2500,3500,48,12);

-- Buat akun admin/kasir lewat halaman setup_admin.php agar password di-hash dengan aman.
