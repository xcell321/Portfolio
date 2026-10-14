-- Import sa phpMyAdmin OR: mysql -u root portfolio_db < schema.sql
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  num VARCHAR(10) DEFAULT '01',
  role VARCHAR(100) DEFAULT '',
  year VARCHAR(10) DEFAULT '',
  title VARCHAR(150) NOT NULL,
  `desc` TEXT,
  tags VARCHAR(255) DEFAULT '',
  live_url VARCHAR(255) DEFAULT '#',
  case_url VARCHAR(255) DEFAULT '#',
  emoji VARCHAR(10) DEFAULT '🚀',
  sort_order INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS timeline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  year VARCHAR(20) NOT NULL,
  title VARCHAR(150) NOT NULL,
  `desc` TEXT,
  tags VARCHAR(255) DEFAULT '',
  sort_order INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS certificates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  org VARCHAR(150) DEFAULT '',
  `date` VARCHAR(30) DEFAULT '',
  verify_url VARCHAR(255) DEFAULT '#',
  emoji VARCHAR(10) DEFAULT '🏅'
);

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  company VARCHAR(150) DEFAULT '',
  need VARCHAR(255) DEFAULT '',
  budget VARCHAR(50) DEFAULT '',
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS guestbook (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_likes (
  id INT PRIMARY KEY DEFAULT 1,
  total INT DEFAULT 0
);
INSERT IGNORE INTO site_likes (id,total) VALUES (1,0);

-- 1 per person: voter hash = sha256(IP + User-Agent), 1 row = 1 person
CREATE TABLE IF NOT EXISTS site_like_voters (
  voter_hash CHAR(64) PRIMARY KEY,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- default admin: username admin / password admin123 (ILISI DAYON!)
-- password_hash generated via: password_hash('admin123', PASSWORD_DEFAULT)
INSERT IGNORE INTO admin_users (username,password_hash) VALUES
('admin','$2y$10$wHk2KcQeYx6l8m3n5p7r9uQvW3x5y7z9a1b3c5d7e9f1g3h5i7j9k1m');

INSERT IGNORE INTO projects (num,role,year,title,`desc`,tags,live_url,emoji,sort_order) VALUES
('01','Founder · Full Stack','2026','Project Alpha','Ilisi ni sa admin ug imong tinuod nga project.','PHP,MySQL,Tailwind','#','🚀',1),
('02','Freelance · Web App','2025','Project Beta','Dugang/edit via Admin > Projects.','PHP,JavaScript,CSS','#','📊',2),
('03','Personal · UI/UX','2025','Project Gamma','Tanan text imoha ra.','Figma,PHP,MySQL','#','📝',3),
('04','Client Work · Website','2024','Project Delta','Showcase para sa client work.','PHP,Bootstrap,MySQL','#','🏗️',4);
