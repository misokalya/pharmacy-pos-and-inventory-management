CREATE DATABASE IF NOT EXISTS pharmacy_db 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacy_db;

-- Users & roles
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','pharmacist','cashier') NOT NULL DEFAULT 'cashier',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Settings (key/value)
CREATE TABLE settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed settings
INSERT INTO settings (`key`, `value`) VALUES
  ('app_name', 'PharmaCare'),
  ('currency', 'USD'),
  ('tax_rate', '0'),
  ('expiry_alert_days', '90'),
  ('critical_expiry_days', '30');

-- Default admin (password: admin123 — change immediately)
INSERT INTO users (name, email, password_hash, role) VALUES
('Administrator', 'admin@pharmacy.com',
 '$2y$10$lMY9L3c5FS3/zu4NJ2dp0eXW6odI0xXzxxOshywfRDddjRQFe/Ynu', 'admin');

-- Login attempts (basic rate-limit log)
CREATE TABLE login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email_time (email, attempted_at)
) ENGINE=InnoDB;

USE pharmacy_db;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE suppliers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  contact_person VARCHAR(100) NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  address VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(50) NOT NULL UNIQUE,
  barcode VARCHAR(50) NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  generic_name VARCHAR(150) NULL,
  category_id INT UNSIGNED NULL,
  unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
  reorder_level INT UNSIGNED NOT NULL DEFAULT 10,
  cost_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  selling_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_name (name),
  INDEX idx_products_sku (sku)
) ENGINE=InnoDB;

-- Seed a couple categories
INSERT INTO categories (name, description) VALUES
  ('Analgesics', 'Pain relievers'),
  ('Antibiotics', 'Bacterial infection treatment'),
  ('Vitamins', 'Dietary supplements');

  USE pharmacy_db;

ALTER TABLE products
  ADD COLUMN image_path VARCHAR(255) NULL AFTER name;

USE pharmacy_db;

CREATE TABLE sales (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(30) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  customer_name VARCHAR(150) NULL,
  customer_phone VARCHAR(30) NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
  change_due DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method ENUM('cash','card','mobile','insurance') NOT NULL DEFAULT 'cash',
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sales_user FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_sales_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sale_id INT UNSIGNED NOT NULL,
  batch_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(150) NOT NULL,   -- snapshot
  batch_no VARCHAR(80) NOT NULL,        -- snapshot
  expiry_date DATE NOT NULL,            -- snapshot
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_items_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_batch FOREIGN KEY (batch_id) REFERENCES batches(id),
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- Invoice counter (per year, for human-friendly invoice numbers)
CREATE TABLE invoice_counters (
  `year` INT PRIMARY KEY,
  `counter` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

USE pharmacy_db;

-- Ensure required settings exist
INSERT INTO settings (`key`, `value`) VALUES
  ('app_name',            'PharmaCare'),
  ('currency',            'TZS'),
  ('currency_symbol',     'Tsh'),
  ('tax_rate',            '0'),
  ('expiry_alert_days',   '90'),
  ('critical_expiry_days','30'),
  ('alert_email',         'pharmacy@example.com'),
  ('expiry_digest_window','90'),
  ('low_stock_threshold', '10')
ON DUPLICATE KEY UPDATE `value` = `value`;

-- Add tracking columns to users
ALTER TABLE users
  ADD COLUMN created_by INT UNSIGNED NULL AFTER is_active,
  ADD COLUMN last_password_change DATETIME NULL AFTER last_login_at;