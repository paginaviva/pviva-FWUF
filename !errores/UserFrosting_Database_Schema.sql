-- ============================================================================
-- UserFrosting 5.x Database Schema
-- ============================================================================
-- Basado en las migraciones oficiales de UserFrosting:
-- vendor/userfrosting/sprinkle-account/app/src/Database/Migrations/v400/
--
-- IMPORTANTE: Estas tablas se crean automáticamente durante la instalación
-- de UserFrosting vía el wizard web o comando `php bakery migrate`.
-- Este archivo es solo para REFERENCIA y creación manual si es necesario.
-- ============================================================================

-- Configuración de charset para toda la BD
SET NAMES utf8;
SET character_set_client = utf8;

-- ============================================================================
-- 1. TABLA: groups
-- ============================================================================
-- "Group" representa el grupo primario de un usuario. Cada usuario pertenece
-- a exactamente un grupo.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'fas fa-user' COMMENT 'The icon representing users in this group.',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `groups_slug_unique` (`slug`),
    KEY `groups_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 2. TABLA: users
-- ============================================================================
-- Tabla principal de usuarios. Cada usuario tiene nombre, email, contraseña,
-- y pertenece a un grupo.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(254) NOT NULL,
    `first_name` VARCHAR(20) NOT NULL,
    `last_name` VARCHAR(30) NOT NULL,
    `locale` VARCHAR(10) NOT NULL DEFAULT 'en_US' COMMENT 'The language and locale to use for this user.',
    `theme` VARCHAR(100) DEFAULT NULL COMMENT 'The user theme.',
    `group_id` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'The id of the user group.',
    `flag_verified` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Set to 1 if the user has verified their account via email, 0 otherwise.',
    `flag_enabled` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Set to 1 if the user account is currently enabled, 0 otherwise. Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
    `last_activity_id` INT UNSIGNED DEFAULT NULL COMMENT 'The id of the last activity performed by this user.',
    `password` VARCHAR(255) NOT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `users_user_name_unique` (`user_name`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_user_name_index` (`user_name`),
    KEY `users_email_index` (`email`),
    KEY `users_group_id_index` (`group_id`),
    KEY `users_last_activity_id_index` (`last_activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 3. TABLA: roles
-- ============================================================================
-- Los roles reemplazan los "grupos" de UF 0.3.x. Los usuarios adquieren
-- permisos a través de roles. Relación muchos-a-muchos con usuarios.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `roles_slug_unique` (`slug`),
    KEY `roles_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 4. TABLA: permissions
-- ============================================================================
-- Los permisos reemplazan las tablas 'authorize_group' y 'authorize_user'.
-- Ahora mapean muchos-a-muchos con roles.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL COMMENT 'A code that references a specific action or URI that an assignee of this permission has access to.',
    `name` VARCHAR(255) NOT NULL,
    `conditions` TEXT NOT NULL COMMENT 'The conditions under which members of this group have access to this hook.',
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 5. TABLA: role_users
-- ============================================================================
-- Tabla pivote para la relación muchos-a-muchos entre usuarios y roles.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `role_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `role_users_user_id_index` (`user_id`),
    KEY `role_users_role_id_index` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 6. TABLA: permission_roles
-- ============================================================================
-- Tabla pivote para la relación muchos-a-muchos entre permisos y roles.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `permission_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `permission_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `permission_roles_permission_id_index` (`permission_id`),
    KEY `permission_roles_role_id_index` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 7. TABLA: activities
-- ============================================================================
-- Registro de actividades de usuarios (audit log).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `type` VARCHAR(255) NOT NULL COMMENT 'An identifier used to track the type of activity.',
    `occurred_at` TIMESTAMP NULL DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    KEY `activities_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 8. TABLA: password_resets
-- ============================================================================
-- Tabla para gestionar tokens de reseteo de contraseñas.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `hash` VARCHAR(255) NOT NULL COMMENT 'The hashed token for verification.',
    `completed` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `password_resets_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 9. TABLA: persistences
-- ============================================================================
-- Tabla para gestionar sesiones persistentes (remember me tokens).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `persistences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `persistences_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 10. TABLA: verifications
-- ============================================================================
-- Tabla para gestionar tokens de verificación de email.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `verifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `hash` VARCHAR(255) NOT NULL COMMENT 'The hashed token for verification.',
    `completed` TINYINT(1) NOT NULL DEFAULT 0,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    KEY `verifications_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- 11. TABLA: migrations (usada por UserFrosting)
-- ============================================================================
-- Tabla de control de migraciones de UserFrosting.
-- Se crea automáticamente por el sistema de migraciones.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sprinkle` VARCHAR(255) NOT NULL,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ============================================================================
-- NOTA IMPORTANTE SOBRE LA INSTALACIÓN
-- ============================================================================
-- 
-- NO SE RECOMIENDA crear estas tablas manualmente con este script SQL.
-- 
-- UserFrosting tiene un sistema de migraciones que:
-- 1. Crea estas tablas automáticamente
-- 2. Las llena con datos iniciales (seeds)
-- 3. Mantiene un registro de qué migraciones se han ejecutado
-- 
-- PROCESO RECOMENDADO:
-- 1. Crear la base de datos vacía:
--    CREATE DATABASE pvuf_staging CHARACTER SET utf8 COLLATE utf8_unicode_ci;
-- 
-- 2. Configurar .env con las credenciales
-- 
-- 3. Ejecutar el wizard de instalación web:
--    https://pvuf.plazza.xyz/
--    O comando CLI:
--    php bakery migrate
-- 
-- 4. El wizard creará todas las tablas automáticamente
-- 
-- USO DE ESTE SCRIPT:
-- Solo usar si necesitas crear la estructura manualmente por alguna razón
-- específica o para documentación/referencia.
-- 
-- ============================================================================

-- ============================================================================
-- DATOS INICIALES BÁSICOS (OPCIONAL - NO RECOMENDADO)
-- ============================================================================
-- El wizard de UserFrosting crea estos datos automáticamente.
-- Solo incluidos aquí para referencia.
-- ============================================================================

-- Grupo por defecto: Usuarios
-- INSERT INTO `groups` (`slug`, `name`, `description`, `icon`) 
-- VALUES ('users', 'Users', 'Default group for new users', 'fas fa-users');

-- ============================================================================
-- FIN DEL SCHEMA
-- ============================================================================
