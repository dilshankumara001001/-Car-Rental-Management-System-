-- ============================================================
-- 🚗 Car Rental Management System - Full Database (FIXED)
-- Database: car_rental_db
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. CREATE DATABASE
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `car_rental_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `car_rental_db`;

-- ------------------------------------------------------------
-- 2. DROP OLD TABLES
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `rentals`;
DROP TABLE IF EXISTS `cars`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------
-- 3. USERS TABLE
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `user_id`    INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. CUSTOMERS TABLE
-- ------------------------------------------------------------
CREATE TABLE `customers` (
  `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(100) NOT NULL,
  `address`     VARCHAR(255) DEFAULT NULL,
  `phone`       VARCHAR(20)  DEFAULT NULL,
  `email`       VARCHAR(100) DEFAULT NULL,
  `license_no`  VARCHAR(50)  UNIQUE,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. CARS TABLE
-- ------------------------------------------------------------
CREATE TABLE `cars` (
  `car_id`          INT AUTO_INCREMENT PRIMARY KEY,
  `car_name`        VARCHAR(100) NOT NULL,
  `brand`           VARCHAR(50)  DEFAULT NULL,
  `model`           VARCHAR(50)  DEFAULT NULL,
  `year`            INT          DEFAULT NULL,
  `registration_no` VARCHAR(30)  UNIQUE NOT NULL,
  `rental_price`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`          ENUM('Available','Rented','Maintenance') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. RENTALS TABLE
-- ------------------------------------------------------------
CREATE TABLE `rentals` (
  `rental_id`     INT AUTO_INCREMENT PRIMARY KEY,
  `car_id`        INT NOT NULL,
  `customer_id`   INT NOT NULL,
  `rental_date`   DATE NOT NULL,
  `return_date`   DATE DEFAULT NULL,
  `total_amount`  DECIMAL(10,2) DEFAULT 0.00,
  `rental_status` ENUM('Rented','Returned') DEFAULT 'Rented',
  CONSTRAINT `fk_rentals_car`
    FOREIGN KEY (`car_id`) REFERENCES `cars`(`car_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rentals_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 7. USERS — created by setup.php (REAL password hashes)
-- ============================================================
-- ⚠️ Fake hash දාන්නේ නෑ. setup.php file එකෙන් real hash හදනවා.
-- ============================================================

-- ============================================================
-- 8. SAMPLE DATA — CARS
-- ============================================================
INSERT INTO `cars` (`car_name`, `brand`, `model`, `year`, `registration_no`, `rental_price`, `status`) VALUES
('Toyota Corolla',  'Toyota', 'Corolla',  2020, 'CAR-001', 5000.00, 'Available'),
('Honda Civic',     'Honda',  'Civic',    2021, 'CAR-002', 6500.00, 'Available'),
('Suzuki Alto',     'Suzuki', 'Alto',     2019, 'CAR-003', 3500.00, 'Available'),
('Nissan Leaf',     'Nissan', 'Leaf',     2022, 'CAR-004', 8000.00, 'Available'),
('Toyota Prius',    'Toyota', 'Prius',    2020, 'CAR-005', 7000.00, 'Available'),
('Mitsubishi Lancer','Mitsubishi','Lancer',2018,'CAR-006',4500.00, 'Maintenance'),
('BMW 320i',        'BMW',    '320i',     2021, 'CAR-007', 15000.00,'Available'),
('Mercedes C200',   'Mercedes','C200',    2020, 'CAR-008', 18000.00,'Available'),
('Kia Sportage',    'Kia',    'Sportage', 2022, 'CAR-009', 9500.00, 'Available'),
('Hyundai Tucson',  'Hyundai','Tucson',   2021, 'CAR-010', 9000.00, 'Available');

-- ============================================================
-- 9. SAMPLE DATA — CUSTOMERS
-- ============================================================
INSERT INTO `customers` (`name`, `address`, `phone`, `email`, `license_no`) VALUES
('Kamal Perera',    'No 12, Galle Road, Colombo 03',  '0771234567', 'kamal@example.com',   'B1234567'),
('Nimal Silva',     'No 45, Kandy Road, Kandy',       '0712345678', 'nimal@example.com',   'B7654321'),
('Sunil Fernando',  'No 78, Main Street, Galle',      '0765551234', 'sunil@example.com',   'B2468135'),
('Anjali Wijesinghe','No 23, Temple Road, Negombo',   '0778889999', 'anjali@example.com',  'B9876543'),
('Ruwan Jayasuriya','No 56, Beach Road, Matara',      '0713334444', 'ruwan@example.com',   'B1357924');

-- ============================================================
-- 10. SAMPLE DATA — RENTALS
-- ============================================================
INSERT INTO `rentals` (`car_id`, `customer_id`, `rental_date`, `return_date`, `total_amount`, `rental_status`) VALUES
(1, 1, '2025-09-01', '2025-09-05', 20000.00, 'Returned'),
(2, 2, '2025-09-10', '2025-09-12', 13000.00, 'Returned'),
(3, 3, '2025-09-15', NULL,         0.00,    'Rented'),
(4, 4, '2025-09-18', NULL,         0.00,    'Rented');

UPDATE `cars` SET `status` = 'Rented' WHERE `car_id` IN (3, 4);

-- ============================================================
-- 11. VERIFY
-- ============================================================
SELECT '✅ Database installation complete!' AS Status;
SELECT COUNT(*) AS total_cars      FROM cars;
SELECT COUNT(*) AS total_customers FROM customers;
SELECT COUNT(*) AS total_rentals   FROM rentals;

-- ============================================================
-- END OF FILE
-- ============================================================