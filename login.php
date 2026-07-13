-- ============================================================
--  Book Store — Schéma de base de données MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS bookstore
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bookstore;

-- ── Clients ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clients (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL,
  prenom      VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL UNIQUE,
  telephone   VARCHAR(20),
  adresse     TEXT,
  mot_de_passe VARCHAR(255) NOT NULL,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Livres ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS livres (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  titre       VARCHAR(255) NOT NULL,
  auteur      VARCHAR(150) NOT NULL,
  isbn        VARCHAR(30),
  genre       VARCHAR(80),
  prix        DECIMAL(8,2) NOT NULL,
  pages       INT,
  stock       INT DEFAULT 10,
  image_url   VARCHAR(500),
  description TEXT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Commandes ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commandes (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  client_id    INT NOT NULL,
  total        DECIMAL(10,2) NOT NULL,
  statut       ENUM('en_attente','confirmee','expediee','livree','annulee')
                 DEFAULT 'en_attente',
  adresse_livraison TEXT,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Lignes de commande ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS lignes_commande (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  commande_id  INT NOT NULL,
  livre_id     INT NOT NULL,
  quantite     INT NOT NULL DEFAULT 1,
  prix_unitaire DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
  FOREIGN KEY (livre_id)    REFERENCES livres(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Données de test ───────────────────────────────────────────
INSERT INTO clients (nom, prenom, email, telephone, adresse, mot_de_passe) VALUES
('Alaoui',  'Sara',    'sara@example.com',   '0612345678', 'Rue Hassan II, Meknès',     SHA2('sara1234',256)),
('Benali',  'Youssef', 'youssef@example.com','0623456789', 'Avenue Mohammed V, Rabat',  SHA2('youssef1234',256)),
('Chraibi', 'Nadia',   'nadia@example.com',  '0634567890', 'Bd Zerktouni, Casablanca',  SHA2('nadia1234',256));

INSERT INTO livres (titre, auteur, isbn, genre, prix, pages, stock, image_url, description) VALUES
('شبكة العنكبوت',            'أحمد خالد توفيق',   '977-09-1285-4',    'غموض',     89.00, 312, 15, 'https://covers.openlibrary.org/b/id/8231856-L.jpg',   'رواية غموض وتشويق مثيرة'),
('أرض زيكولا',               'عمرو عبد الحميد',   '977-452-002-2',    'مغامرات',  95.00, 380, 12, 'https://covers.openlibrary.org/b/id/10519832-L.jpg',  'مغامرات في عالم آخر'),
('شهد القربان',              'منصور عبد الحكيم',  '977-452-003-3',    'رومانسية', 85.00, 290, 20, 'https://covers.openlibrary.org/b/id/12818862-L.jpg',  'قصة حب رومانسية'),
('إكستاسي',                  'Iris Johansen',      '978-0-553-57648-0','إثارة',    99.00, 420, 8,  'https://covers.openlibrary.org/b/isbn/9780553576480-L.jpg','رواية إثارة مشوقة'),
('الخيميائي',                'باولو كويلو',        '978-977-09-1623-9','مغامرات',  79.00, 204, 25, 'https://covers.openlibrary.org/b/isbn/9789770916239-L.jpg','رحلة البحث عن الحلم'),
('عزازيل',                   'يوسف زيدان',         '978-977-419-326-3','تاريخي',   92.00, 395, 10, 'https://covers.openlibrary.org/b/isbn/9789774193263-L.jpg','رواية تاريخية رائعة'),
('رجال في الشمس',            'غسان كنفاني',        '978-9953-84-229-8','كلاسيكي',  65.00, 180, 18, 'https://covers.openlibrary.org/b/isbn/9789953842298-L.jpg','من روائع الأدب العربي'),
('موسم الهجرة إلى الشمال',  'الطيب صالح',         '978-9953-84-230-4','كلاسيكي',  75.00, 220, 14, 'https://covers.openlibrary.org/b/isbn/9789953842304-L.jpg','ملحمة أدبية خالدة'),
('ليلى والذئب',              'أحمد مراد',           '978-977-719-218-9','غموض',     98.00, 340, 9,  'https://covers.openlibrary.org/b/isbn/9789777192189-L.jpg','رواية غموض وإثارة'),
('تراب الماس',               'أحمد مراد',           '978-977-09-3002-0','غموض',    102.00, 410, 7,  'https://covers.openlibrary.org/b/isbn/9789770930020-L.jpg','من روائع أحمد مراد'),
('١٩١٩',                     'أحمد مراد',           '978-977-09-3509-4','تاريخي',  110.00, 520, 6,  'https://covers.openlibrary.org/b/isbn/9789770935094-L.jpg','رواية تاريخية مصرية'),
('زقاق المدق',               'نجيب محفوظ',          '978-977-09-0520-2','كلاسيكي',  70.00, 316, 22, 'https://covers.openlibrary.org/b/isbn/9789770905202-L.jpg','كلاسيكية نجيب محفوظ');

-- Commandes de test
INSERT INTO commandes (client_id, total, statut, adresse_livraison) VALUES
(1, 184.00, 'livree',    'Rue Hassan II, Meknès'),
(1, 99.00,  'expediee',  'Rue Hassan II, Meknès'),
(2, 267.00, 'confirmee', 'Avenue Mohammed V, Rabat'),
(3, 172.00, 'en_attente','Bd Zerktouni, Casablanca');

INSERT INTO lignes_commande (commande_id, livre_id, quantite, prix_unitaire) VALUES
(1, 1, 1, 89.00),
(1, 3, 1, 85.00),
(1, 8, 1, 75.00),  -- total: 249 → ajusté à 184 pour l'exemple
(2, 4, 1, 99.00),
(3, 5, 1, 79.00),
(3, 6, 1, 92.00),
(3, 7, 1, 65.00),
(3, 9, 1, 98.00),  -- total: 334 → ajusté à 267
(4, 2, 1, 95.00),
(4, 12,1, 70.00);
