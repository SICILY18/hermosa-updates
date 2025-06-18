-- MySQL Database Setup Script for Hermosa Water District
-- Run this in phpMyAdmin or MySQL command line after creating the database

-- Set MySQL settings for compatibility
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET foreign_key_checks = 0;

-- Create admin table
CREATE TABLE IF NOT EXISTS `admin` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `admin_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `remember_token` varchar(100) DEFAULT NULL,
    `username` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customers_tb table
CREATE TABLE IF NOT EXISTS `customers_tb` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `customer_type` enum('residential','commercial','government') NOT NULL,
    `address` varchar(255) NOT NULL,
    `contact_number` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `account_number` varchar(255) NOT NULL,
    `meter_number` varchar(9) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `customers_tb_username_unique` (`username`),
    UNIQUE KEY `customers_tb_email_unique` (`email`),
    UNIQUE KEY `customers_tb_account_number_unique` (`account_number`),
    UNIQUE KEY `customers_tb_meter_number_unique` (`meter_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create staff_tb table
CREATE TABLE IF NOT EXISTS `staff_tb` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL DEFAULT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('superadmin','admin','billing','customer_service') NOT NULL DEFAULT 'billing',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `remember_token` varchar(100) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `staff_tb_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rates_tb table
CREATE TABLE IF NOT EXISTS `rates_tb` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `customer_type` enum('residential','commercial','government') NOT NULL,
    `min_consumption` int NOT NULL,
    `max_consumption` int DEFAULT NULL,
    `rate_per_cubic_meter` decimal(8,2) NOT NULL,
    `base_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create announcements_tb table
CREATE TABLE IF NOT EXISTS `announcements_tb` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `duration` int DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create bills table
CREATE TABLE IF NOT EXISTS `bills` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `customer_id` bigint unsigned NOT NULL,
    `bill_number` varchar(255) NOT NULL,
    `account_number` varchar(255) NOT NULL,
    `meter_number` varchar(255) NOT NULL,
    `billing_period_start` date NOT NULL,
    `billing_period_end` date NOT NULL,
    `due_date` date NOT NULL,
    `previous_reading` decimal(10,2) NOT NULL,
    `current_reading` decimal(10,2) NOT NULL,
    `consumption` decimal(10,2) NOT NULL,
    `rate_per_cubic_meter` decimal(8,2) NOT NULL,
    `amount_due` decimal(10,2) NOT NULL,
    `status` enum('Pending','Paid','Overdue','Partially_Paid') NOT NULL DEFAULT 'Pending',
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `bills_bill_number_unique` (`bill_number`),
    KEY `bills_customer_id_foreign` (`customer_id`),
    KEY `bills_customer_id_status_index` (`customer_id`,`status`),
    KEY `bills_account_number_meter_number_index` (`account_number`,`meter_number`),
    KEY `bills_due_date_index` (`due_date`),
    CONSTRAINT `bills_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers_tb` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create payments table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `customer_id` bigint unsigned NOT NULL,
    `bill_id` bigint unsigned NOT NULL,
    `payment_number` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `payment_type` enum('Full','Partial') NOT NULL,
    `payment_method` enum('Cash','GCash','Bank_Transfer','Credit_Card','Other') NOT NULL,
    `proof_of_payment` varchar(255) DEFAULT NULL,
    `account_number` varchar(255) NOT NULL,
    `meter_number` varchar(255) NOT NULL,
    `remarks` text,
    `status` enum('Pending','Approved','Rejected','Verification_Failed') NOT NULL DEFAULT 'Pending',
    `remaining_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
    `approved_by` bigint unsigned DEFAULT NULL,
    `approved_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
    KEY `payments_customer_id_foreign` (`customer_id`),
    KEY `payments_bill_id_foreign` (`bill_id`),
    KEY `payments_approved_by_foreign` (`approved_by`),
    KEY `payments_customer_id_status_index` (`customer_id`,`status`),
    KEY `payments_account_number_meter_number_index` (`account_number`,`meter_number`),
    KEY `payments_payment_number_index` (`payment_number`),
    KEY `payments_created_at_index` (`created_at`),
    CONSTRAINT `payments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
    CONSTRAINT `payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
    CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers_tb` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create standard Laravel tables
CREATE TABLE IF NOT EXISTS `password_resets` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `tokenable_type` varchar(255) NOT NULL,
    `tokenable_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `token` varchar(64) NOT NULL,
    `abilities` text,
    `last_used_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `uuid` varchar(255) NOT NULL,
    `connection` text NOT NULL,
    `queue` text NOT NULL,
    `payload` longtext NOT NULL,
    `exception` longtext NOT NULL,
    `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create migrations table
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `migration` varchar(255) NOT NULL,
    `batch` int NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enable foreign key checks
SET foreign_key_checks = 1;

-- Insert initial data (optional)
INSERT INTO `customers_tb` (`name`, `username`, `password`, `customer_type`, `address`, `contact_number`, `email`, `account_number`, `meter_number`, `created_at`, `updated_at`) 
VALUES ('Gian Carlo S. Victorino', 'giancarlo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'residential', '10 Harvard Street', '09089896733', 'giancarlosvictorino@gmail.com', '11-111111', '111111111', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Success message
SELECT 'MySQL database structure created successfully!' as status; 