-- ============================================================
-- RideMate Database Setup
-- Database: ridemate
-- ============================================================

CREATE DATABASE IF NOT EXISTS ridemate 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE ridemate;

-- ── Users Table ──
CREATE TABLE IF NOT EXISTS users (
  id           INT          NOT NULL AUTO_INCREMENT,
  name         VARCHAR(100) NOT NULL,
  phone        VARCHAR(20)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('driver','passenger','admin') NOT NULL DEFAULT 'passenger',
  profile_image VARCHAR(255) NULL DEFAULT NULL,
  otp_code     VARCHAR(6)   NULL DEFAULT NULL,
  otp_expires  DATETIME     NULL DEFAULT NULL,
  otp_attempts INT          NOT NULL DEFAULT 0,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pending_registrations (
  id           INT          NOT NULL AUTO_INCREMENT,
  name         VARCHAR(100) NOT NULL,
  phone        VARCHAR(20)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('driver','passenger') NOT NULL DEFAULT 'passenger',
  otp_code     VARCHAR(6)   NULL DEFAULT NULL,
  otp_expires  DATETIME     NULL DEFAULT NULL,
  otp_attempts INT          NOT NULL DEFAULT 0,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Rides Table ──
CREATE TABLE IF NOT EXISTS rides (
  id           INT            NOT NULL AUTO_INCREMENT,
  driver_id    INT            NOT NULL,
  origin       VARCHAR(150)   NOT NULL,
  destination  VARCHAR(150)   NOT NULL,
  date         DATETIME       NOT NULL,
  seats        INT            NOT NULL DEFAULT 1,
  price        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  vehicle_type ENUM('car','motorbike') NOT NULL,
  created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_rides_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Bookings Table ──
CREATE TABLE IF NOT EXISTS bookings (
  id           INT       NOT NULL AUTO_INCREMENT,
  ride_id      INT       NOT NULL,
  passenger_id INT       NOT NULL,
  status       ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending',
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_bookings_ride      FOREIGN KEY (ride_id)      REFERENCES rides(id)  ON DELETE CASCADE,
  CONSTRAINT fk_bookings_passenger FOREIGN KEY (passenger_id) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Notifications Table ──
CREATE TABLE IF NOT EXISTS notifications (
  id         INT          NOT NULL AUTO_INCREMENT,
  user_id    INT          NOT NULL,
  message    VARCHAR(255) NOT NULL,
  type       ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
  is_read    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Seed: Admin Account ──
-- Password: admin123 (bcrypt hashed)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@ridemate.com', '$2y$10$r4oBPDM3l9ZSq8Tyr3Sti.LoYm89HHd0.TndZUmzAiItxgOPTSG3e', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- ── Seed: Sample Driver ──
-- Password: password
INSERT INTO users (name, email, password, role) VALUES
('Ali Hassan', 'driver@ridemate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver')
ON DUPLICATE KEY UPDATE id=id;

-- ── Seed: Sample Passenger ──
INSERT INTO users (name, email, password, role) VALUES
('Sara Khan', 'passenger@ridemate.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger')
ON DUPLICATE KEY UPDATE id=id;

-- ── Seed: Sample Rides ──
INSERT INTO rides (driver_id, origin, destination, date, seats, price, vehicle_type) VALUES
(2, 'City Center', 'University Campus', DATE_ADD(NOW(), INTERVAL 2 DAY), 3, 150.00, 'car'),
(2, 'Mall Road', 'Engineering University', DATE_ADD(NOW(), INTERVAL 3 DAY), 2, 100.00, 'motorbike'),
(2, 'Airport Road', 'Medical College', DATE_ADD(NOW(), INTERVAL 1 DAY), 4, 200.00, 'car')
ON DUPLICATE KEY UPDATE id=id;
