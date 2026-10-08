CREATE DATABASE IF NOT EXISTS fixmate;

USE fixmate;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS repair_requests;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;


-- =========================================
-- USERS TABLE
-- =========================================

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer','technician') DEFAULT 'customer',
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- SERVICES TABLE
-- =========================================

CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_days INT DEFAULT 1,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================
-- REPAIR REQUESTS TABLE
-- =========================================

CREATE TABLE repair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,

    customer_id INT NOT NULL,
    technician_id INT DEFAULT NULL,
    service_id INT NOT NULL,

    device_type VARCHAR(100) NOT NULL,
    device_brand VARCHAR(100),
    device_model VARCHAR(100),

    problem_description TEXT NOT NULL,

    estimated_cost DECIMAL(10,2) DEFAULT 0,
    final_cost DECIMAL(10,2) DEFAULT 0,

    status ENUM(
        'Pending',
        'Assigned',
        'In Progress',
        'Completed',
        'Cancelled'
    ) DEFAULT 'Pending',

    priority ENUM('Low','Medium','High') DEFAULT 'Medium',

    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date DATETIME DEFAULT NULL,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (technician_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    FOREIGN KEY (service_id)
        REFERENCES services(id)
        ON DELETE CASCADE
);


-- =========================================
-- PAYMENTS TABLE
-- =========================================

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,

    request_id INT NOT NULL,
    customer_id INT NOT NULL,

    amount DECIMAL(10,2) NOT NULL,

    payment_method ENUM(
        'Cash',
        'UPI',
        'Card',
        'Net Banking'
    ) DEFAULT 'Cash',

    payment_status ENUM(
        'Pending',
        'Paid',
        'Failed'
    ) DEFAULT 'Pending',

    transaction_id VARCHAR(100),

    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (request_id)
        REFERENCES repair_requests(id)
        ON DELETE CASCADE,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


-- =========================================
-- DEFAULT ADMIN
-- Password = password
-- =========================================

INSERT INTO users
(name,email,phone,password,role,address)
VALUES
(
    'FixMate Admin',
    'admin@fixmate.com',
    '9999999999',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9xR6gZ4N5Y7xF7QqW',
    'admin',
    'Raipur, Chhattisgarh'
);


-- =========================================
-- SAMPLE TECHNICIANS
-- Password = password
-- =========================================

INSERT INTO users
(name,email,phone,password,role,address)
VALUES

(
    'Rahul Sharma',
    'rahul@fixmate.com',
    '9876543210',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9xR6gZ4N5Y7xF7QqW',
    'technician',
    'Raipur'
),

(
    'Amit Verma',
    'amit@fixmate.com',
    '9876543211',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC9xR6gZ4N5Y7xF7QqW',
    'technician',
    'Bhilai'
);


-- =========================================
-- SAMPLE SERVICES
-- =========================================

INSERT INTO services
(service_name,description,price,estimated_days,status)
VALUES

(
    'Screen Replacement',
    'Broken or damaged mobile screen replacement.',
    2499,
    1,
    'active'
),

(
    'Battery Replacement',
    'Mobile battery replacement service.',
    1299,
    1,
    'active'
),

(
    'Charging Port Repair',
    'Charging port cleaning and replacement.',
    799,
    1,
    'active'
),

(
    'Software Issue',
    'Operating system and software troubleshooting.',
    499,
    1,
    'active'
),

(
    'Water Damage Repair',
    'Inspection and repair of water damaged devices.',
    1499,
    2,
    'active'
),

(
    'Speaker Repair',
    'Mobile speaker troubleshooting and replacement.',
    899,
    1,
    'active'
),

(
    'Camera Repair',
    'Front and rear camera repair/replacement.',
    1599,
    1,
    'active'
),

(
    'General Diagnosis',
    'Complete mobile device diagnosis.',
    299,
    1,
    'active'
);