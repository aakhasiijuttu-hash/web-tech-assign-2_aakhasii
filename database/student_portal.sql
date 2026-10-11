-- ============================================================
-- Student Admission Portal
-- Database setup for Q2 and Q3
-- Safe version: preserves existing student records
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_portal
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE student_portal;

-- ============================================================
-- Table 1: Students
-- ============================================================

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL,
    course VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 2: Uploaded student files
-- ============================================================

CREATE TABLE IF NOT EXISTS student_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_student_files_student_id (student_id),

    CONSTRAINT fk_student_files_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table 3: Administrators for Q3
-- ============================================================

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- No sample records are inserted automatically.
-- Existing records remain unchanged.
-- ============================================================