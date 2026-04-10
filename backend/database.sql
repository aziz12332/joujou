-- ═══════════════════════════════════════════════
--  JouJou Accessoires — Database Schema
--  Run this once in phpMyAdmin or MySQL CLI
-- ═══════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS joujou2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE joujou2_db;

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(200)   NOT NULL,
  cat       ENUM('bagues','colliers','bracelets','boucles') NOT NULL,
  price     DECIMAL(10,2)  NOT NULL,
  old_price DECIMAL(10,2)  DEFAULT NULL,
  img       VARCHAR(500)   NOT NULL,
  is_new    TINYINT(1)     DEFAULT 0,
  created_at DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  order_ref    VARCHAR(20)   NOT NULL UNIQUE,
  first_name   VARCHAR(100)  NOT NULL,
  last_name    VARCHAR(100)  DEFAULT '',
  phone        VARCHAR(30)   NOT NULL,
  address      VARCHAR(300)  NOT NULL,
  city         VARCHAR(100)  DEFAULT '',
  zip          VARCHAR(20)   DEFAULT '',
  note         TEXT          DEFAULT NULL,
  items        JSON          NOT NULL,
  subtotal     DECIMAL(10,2) NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  status       ENUM('new','confirmed','delivered','cancelled') DEFAULT 'new',
  created_at   DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100)  NOT NULL,
  city       VARCHAR(100)  DEFAULT NULL,
  rating     TINYINT(1)    NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment    TEXT          NOT NULL,
  approved   TINYINT(1)    DEFAULT 1,
  created_at DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(200) NOT NULL UNIQUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin account (password stored as bcrypt hash)
-- Default password: JouJou@2025  — CHANGE IT after first login!
CREATE TABLE IF NOT EXISTS admin (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Insert default admin (password: JouJou@2025)
INSERT IGNORE INTO admin (username, password_hash)
VALUES ('admin', '$2y$12$5Kv8Qz9mXwT3rNpLdVcJeOeY6hUsFgBiAkZs7WqCxMn4DjE1RtPuK');
-- ^ This hash is a placeholder. Run setup.php after upload to set your real password.

-- Default products
INSERT IGNORE INTO products (id,name,cat,price,old_price,img,is_new) VALUES
(1,'Bague Perle Goutte','bagues',24.00,NULL,'https://images.unsplash.com/photo-1611652022419-a9419f74343d?w=400&q=80',1),
(2,'Bague Torsadee Or','bagues',18.00,22.00,'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?w=400&q=80',0),
(3,'Bague Empilable Rose','bagues',15.00,NULL,'https://images.unsplash.com/photo-1602752250015-52934bc45613?w=400&q=80',0),
(4,'Collier Chaine Fine','colliers',32.00,NULL,'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&q=80',1),
(5,'Pendentif Coeur','colliers',28.00,35.00,'https://images.unsplash.com/photo-1589128777073-263566ae5e4d?w=400&q=80',0),
(6,'Ras du Cou Etoile','colliers',22.00,NULL,'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=400&q=80',1),
(7,'Bracelet Tennis Cristal','bracelets',38.00,NULL,'https://images.unsplash.com/photo-1573408301185-9519f94f5a37?w=400&q=80',0),
(8,'Jonc Dore','bracelets',20.00,25.00,'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=400&q=80',0),
(9,'Creoles Perle','boucles',26.00,NULL,'https://images.unsplash.com/photo-1588444837495-c6cfeb53f32d?w=400&q=80',1),
(10,'Puces Papillon','boucles',16.00,NULL,'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?w=400&q=80',0),
(11,'Pendants Lune','boucles',22.00,28.00,'https://images.unsplash.com/photo-1630019852942-f89202989a59?w=400&q=80',0),
(12,'Bracelet Charmes','bracelets',30.00,NULL,'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=400&q=80',1);
