CREATE DATABASE IF NOT EXISTS hospital_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hospital_system;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (email, password_hash)
SELECT 'admin@hospital.com', '$2y$10$bN51v/tw4Rtr9I6HDK9p6e4pr8vOE3M7NmgpTfRRKprcTvggYyJFa'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE email = 'admin@hospital.com');

CREATE TABLE IF NOT EXISTS patients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE NULL,
    address VARCHAR(255) NULL,
    password_hash VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE patients ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL;

CREATE TABLE IF NOT EXISTS doctors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id INT UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(500) NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_doctor_slot (doctor_id, appointment_date, appointment_time)
);

INSERT INTO doctors (name, specialty, phone, email)
SELECT 'Dr. Ahmed Khan', 'Cardiology', '03011234567', 'ahmed@hospital.com'
WHERE NOT EXISTS (SELECT 1 FROM doctors WHERE email = 'ahmed@hospital.com');

INSERT INTO doctors (name, specialty, phone, email)
SELECT 'Dr. Fatima Riaz', 'Dermatology', '03021234567', 'fatima@hospital.com'
WHERE NOT EXISTS (SELECT 1 FROM doctors WHERE email = 'fatima@hospital.com');

INSERT INTO doctors (name, specialty, phone, email)
SELECT 'Dr. Hassan Ali', 'Neurology', '03031234567', 'hassan@hospital.com'
WHERE NOT EXISTS (SELECT 1 FROM doctors WHERE email = 'hassan@hospital.com');

INSERT INTO doctors (name, specialty, phone, email)
SELECT 'Dr. Usman Malik', 'Orthopedics', '03041234567', 'usman@hospital.com'
WHERE NOT EXISTS (SELECT 1 FROM doctors WHERE email = 'usman@hospital.com');
