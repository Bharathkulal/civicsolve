CREATE DATABASE IF NOT EXISTS civicsolve;
USE civicsolve;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin','super_admin') DEFAULT 'user',
    department VARCHAR(50) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(500) DEFAULT NULL,
    profile_image VARCHAR(500) DEFAULT NULL
);

-- COMPLAINTS TABLE
CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(200),
    department VARCHAR(100),
    description TEXT,
    image_path VARCHAR(500) DEFAULT NULL,
    latitude DECIMAL(10,6) DEFAULT NULL,
    longitude DECIMAL(10,6) DEFAULT NULL,
    address VARCHAR(500) DEFAULT NULL,
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Run these if the table already exists and you need to add columns:
-- ALTER TABLE complaints ADD COLUMN image_path VARCHAR(500) DEFAULT NULL AFTER description;
-- ALTER TABLE complaints ADD COLUMN latitude DECIMAL(10,6) DEFAULT NULL AFTER image_path;
-- ALTER TABLE complaints ADD COLUMN longitude DECIMAL(10,6) DEFAULT NULL AFTER latitude;
-- ALTER TABLE complaints ADD COLUMN address VARCHAR(500) DEFAULT NULL AFTER longitude;