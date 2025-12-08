-- BASE DE DONNÉES aide_co
-- Pour ton projet de consultation médicale

CREATE DATABASE IF NOT EXISTS aide_co_db;
USE aide_co_db;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    role ENUM('patient', 'doctor', 'admin') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des rendez-vous
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Données d'exemple
INSERT INTO users (email, password, full_name, role) VALUES
('patient@example.com', '$2y$10$VexampleHashedPassword', 'Ali Patient', 'patient'),
('docteur@example.com', '$2y$10$VexampleHashedPassword', 'Dr. Sarah Smith', 'doctor'),
('admin@example.com', '$2y$10$VexampleHashedPassword', 'Admin System', 'admin');

INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES
(1, 2, '2024-12-20', '14:30:00', 'confirmed');