-- Elimu Tracks Database Schema

CREATE DATABASE IF NOT EXISTS elimu_tracks;
USE elimu_tracks;

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'kitchen') NOT NULL DEFAULT 'teacher',
    department_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Lesson Attendance Table
CREATE TABLE IF NOT EXISTS lesson_attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lesson_title VARCHAR(255) NOT NULL,
    lesson_time TIME NOT NULL,
    lesson_end_time TIME,
    boys_attendance INT NOT NULL DEFAULT 0,
    girls_attendance INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Kitchen Plates Table
CREATE TABLE IF NOT EXISTS kitchen_plates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    record_date DATE NOT NULL,
    plates_count INT NOT NULL DEFAULT 0,
    tea_cups_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Demo Data

-- Insert Departments
INSERT INTO departments (name) VALUES
('English'),
('Mathematics'),
('Science'),
('Social Studies'),
('Physical Education'),
('Kitchen');

-- Insert Demo Users (password: password123)
INSERT INTO users (username, email, password, role, department_id) VALUES
('admin', 'admin@elimutrack.com', '$2y$10$YourHashedPasswordHere', 'admin', NULL),
('teacher', 'teacher@elimutrack.com', '$2y$10$YourHashedPasswordHere', 'teacher', 1),
('kitchen', 'kitchen@elimutrack.com', '$2y$10$YourHashedPasswordHere', 'kitchen', 6);

-- Note: Use the following password hashes (generated with password_hash('password123', PASSWORD_BCRYPT)):
-- $2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gBaFT2 (replace with actual hash)

-- Create Indexes for better performance
CREATE INDEX idx_user_id ON lesson_attendance(user_id);
CREATE INDEX idx_department_id ON users(department_id);
CREATE INDEX idx_record_date ON kitchen_plates(record_date);
CREATE INDEX idx_created_at ON lesson_attendance(created_at);

-- Set up proper character encoding
ALTER DATABASE elimu_tracks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE departments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE lesson_attendance CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE kitchen_plates CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
