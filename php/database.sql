-- Portfolio Database Setup
-- Run this SQL script to create the necessary database and table

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add indexes for better performance
CREATE INDEX idx_email ON contact_submissions(email);
CREATE INDEX idx_submitted_at ON contact_submissions(submitted_at);
CREATE INDEX idx_status ON contact_submissions(status);

-- Optional: Create a user for the application (replace with your preferred credentials)
-- CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE ON portfolio_db.* TO 'portfolio_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Sample query to view all submissions
-- SELECT id, name, email, LEFT(message, 50) as message_preview, submitted_at, status 
-- FROM contact_submissions 
-- ORDER BY submitted_at DESC;