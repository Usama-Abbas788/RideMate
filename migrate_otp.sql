-- ============================================================
-- Migrate existing email-auth DB → phone OTP + notification email
-- ============================================================

USE ridemate;

-- 1) Add phone (nullable first)
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='phone');
SET @sql := IF(@col=0, 'ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER name', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) Backfill phones for existing rows (format XXXX-XXXXXXX)
UPDATE users
SET phone = CONCAT('0300-', LPAD(id, 7, '0'))
WHERE phone IS NULL OR phone = '';

-- 3) Enforce unique phone
ALTER TABLE users
  MODIFY phone VARCHAR(20) NOT NULL,
  ADD UNIQUE KEY uniq_users_phone (phone);

-- 4) Keep email for notifications only (drop login uniqueness requirement)
ALTER TABLE users
  MODIFY email VARCHAR(150) NULL;

-- Drop old unique email index if present (name may vary)
SET @idx := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
  WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND INDEX_NAME='email'
);
SET @sql := IF(@idx>0, 'ALTER TABLE users DROP INDEX email', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 5) OTP columns
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='otp_code');
SET @sql := IF(@col=0, 'ALTER TABLE users ADD COLUMN otp_code VARCHAR(6) NULL, ADD COLUMN otp_expires DATETIME NULL, ADD COLUMN otp_attempts INT NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 6) Remove legacy email-verification / reset-token columns
SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='email_verified');
SET @sql := IF(@col>0, 'ALTER TABLE users DROP COLUMN email_verified', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='verification_token');
SET @sql := IF(@col>0, 'ALTER TABLE users DROP COLUMN verification_token', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='verification_expires');
SET @sql := IF(@col>0, 'ALTER TABLE users DROP COLUMN verification_expires', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='reset_token');
SET @sql := IF(@col>0, 'ALTER TABLE users DROP COLUMN reset_token', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

SET @col := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='ridemate' AND TABLE_NAME='users' AND COLUMN_NAME='reset_expires');
SET @sql := IF(@col>0, 'ALTER TABLE users DROP COLUMN reset_expires', 'SELECT 1');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 7) Pending registrations for OTP signup
CREATE TABLE IF NOT EXISTS pending_registrations (
  id           INT           NOT NULL AUTO_INCREMENT,
  name         VARCHAR(100)  NOT NULL,
  phone        VARCHAR(20)   NOT NULL UNIQUE,
  email        VARCHAR(150)  NULL DEFAULT NULL,
  password     VARCHAR(255)  NOT NULL,
  role         ENUM('driver','passenger') NOT NULL DEFAULT 'passenger',
  otp_code     VARCHAR(6)    NULL DEFAULT NULL,
  otp_expires  DATETIME      NULL DEFAULT NULL,
  otp_attempts INT           NOT NULL DEFAULT 0,
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
