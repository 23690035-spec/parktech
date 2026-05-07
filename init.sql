-- ============================================================
-- SCRIPT DE CREACIÓN DE BASE DE DATOS — ParkTech CV
-- ============================================================

CREATE DATABASE IF NOT EXISTS parking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parking;

-- ============================================================
-- TABLA: users
-- Columnas requeridas por login.php, register.php, admin_getUsers.php:
--   whatsapp, is_admin
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    whatsapp   VARCHAR(20)  NULL,
    is_admin   TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email    (email),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: spots
-- ============================================================
CREATE TABLE IF NOT EXISTS spots (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    status     ENUM('disponible','reservado','ocupado') NOT NULL DEFAULT 'disponible',
    user_id    INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status  (status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: reservations
-- Columna requerida por reserve.php y admin_setSpot.php: whatsapp
-- SIN UNIQUE en spot_id para permitir historial de reservas
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT         NOT NULL,
    spot_id    INT         NOT NULL,
    plate      VARCHAR(20) NOT NULL,
    whatsapp   VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES spots(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_spot_id (spot_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATOS DE PRUEBA
-- ============================================================

INSERT INTO users (name, email, password, whatsapp, is_admin) VALUES
('Admin ParkTech', 'admin@parktech.com', 'admin123',  '4811000000', 1),
('Juan Pérez',     'juan@example.com',   '123456',    '4811111111', 0),
('María González', 'maria@example.com',  '123456',    '4812222222', 0),
('Carlos López',   'carlos@example.com', '123456',    NULL,         0);

INSERT INTO spots (status) VALUES
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible'),
('disponible');
