-- Slopara Database Schema
CREATE DATABASE IF NOT EXISTS slopara_chat;
USE slopara_chat;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff', 'Finance') NOT NULL,
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    encrypted_payload TEXT NOT NULL,
    message_type ENUM('text', 'file_snippet') DEFAULT 'text',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Default Users (Passwords should be hashed in production)
-- Assuming 'password123' for demonstration
INSERT INTO users (username, password_hash, role) VALUES 
('AdminUser', '$2y$10$YourHashedPasswordHere', 'Admin'),
('StaffUser', '$2y$10$YourHashedPasswordHere', 'Staff'),
('FinanceUser', '$2y$10$YourHashedPasswordHere', 'Finance');