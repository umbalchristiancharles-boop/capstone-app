-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 06:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chikintayo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'DASMA_MAIN', 'Dasmariñas, Cavite Main Branch', 'Dasmariñas, Cavite', 1, '2026-01-13 16:24:09', '2026-01-13 16:24:09'),
(2, 'GENTRI', 'General Trias, Cavite', 'General Trias, Cavite', 1, '2026-01-13 16:24:09', '2026-01-13 16:24:09'),
(3, 'MANILA', 'Manila Branch', 'Manila', 1, '2026-01-13 16:24:09', '2026-01-13 16:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_08_203224_create_orders_table', 2),
(5, '0001_01_01_000003_create_sessions_table', 3),
(6, '2026_01_11_130224_create_branches_table\r\n', 4),
(7, '2026_01_13_165546_add_soft_deletes_to_users_table\r\n', 5);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_code` varchar(255) NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_kitchen','completed','cancelled') NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `ordered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `owner_id`, `branch_id`, `customer_name`, `status`, `grand_total`, `ordered_at`, `created_at`, `updated_at`) VALUES
(1, 'CT-0015', 1, 1, 'Customer 15', 'pending', 714.00, '2026-01-04 02:27:00', '2026-01-04 02:27:00', '2026-01-04 02:27:00'),
(2, 'CT-0016', 1, 1, 'Customer 16', 'completed', 303.00, '2025-12-14 03:12:00', '2025-12-14 03:12:00', '2025-12-14 03:12:00'),
(3, 'CT-0017', 1, 1, 'Customer 17', 'in_kitchen', 1887.00, '2025-12-29 07:09:00', '2025-12-29 07:09:00', '2025-12-29 07:09:00'),
(4, 'CT-0018', 1, 1, 'Customer 18', 'pending', 558.00, '2025-12-10 11:31:00', '2025-12-10 11:31:00', '2025-12-10 11:31:00'),
(5, 'CT-0019', 1, 1, 'Customer 19', 'completed', 472.00, '2026-01-02 10:57:00', '2026-01-02 10:57:00', '2026-01-02 10:57:00'),
(6, 'CT-0020', 1, 1, 'Customer 20', 'in_kitchen', 654.00, '2025-12-26 02:52:00', '2025-12-26 02:52:00', '2025-12-26 02:52:00'),
(7, 'CT-0021', 1, 1, 'Customer 21', 'completed', 1884.00, '2025-12-30 02:02:00', '2025-12-30 02:02:00', '2025-12-30 02:02:00'),
(8, 'CT-0022', 1, 1, 'Customer 22', 'in_kitchen', 874.00, '2025-12-16 00:29:00', '2025-12-16 00:29:00', '2025-12-16 00:29:00'),
(9, 'CT-0023', 1, 1, 'Customer 23', 'pending', 1221.00, '2025-12-24 10:15:00', '2025-12-24 10:15:00', '2025-12-24 10:15:00'),
(10, 'CT-0024', 1, 1, 'Customer 24', 'completed', 1930.00, '2026-01-04 12:57:00', '2026-01-04 12:57:00', '2026-01-04 12:57:00'),
(11, 'CT-0025', 1, 1, 'Customer 25', 'completed', 624.00, '2025-12-25 03:03:00', '2025-12-25 03:03:00', '2025-12-25 03:03:00'),
(12, 'CT-0026', 1, 1, 'Customer 26', 'completed', 380.00, '2025-12-29 05:43:00', '2025-12-29 05:43:00', '2025-12-29 05:43:00'),
(13, 'CT-0027', 1, 1, 'Customer 27', 'completed', 519.00, '2025-12-24 07:54:00', '2025-12-24 07:54:00', '2025-12-24 07:54:00'),
(14, 'CT-0028', 1, 1, 'Customer 28', 'in_kitchen', 1878.00, '2025-12-17 04:14:00', '2025-12-17 04:14:00', '2025-12-17 04:14:00'),
(15, 'CT-0029', 1, 1, 'Customer 29', 'completed', 1508.00, '2025-12-14 00:37:00', '2025-12-14 00:37:00', '2025-12-14 00:37:00'),
(16, 'CT-0030', 1, 1, 'Customer 30', 'pending', 1295.00, '2026-01-02 09:16:00', '2026-01-02 09:16:00', '2026-01-02 09:16:00'),
(17, 'CT-0031', 1, 1, 'Customer 31', 'pending', 969.00, '2025-12-27 04:26:00', '2025-12-27 04:26:00', '2025-12-27 04:26:00'),
(18, 'CT-0032', 1, 1, 'Customer 32', 'in_kitchen', 1125.00, '2026-01-03 08:45:00', '2026-01-03 08:45:00', '2026-01-03 08:45:00'),
(19, 'CT-0033', 1, 1, 'Customer 33', 'pending', 1278.00, '2025-12-31 09:40:00', '2025-12-31 09:40:00', '2025-12-31 09:40:00'),
(20, 'CT-0034', 1, 1, 'Customer 34', 'pending', 662.00, '2025-12-10 01:55:00', '2025-12-10 01:55:00', '2025-12-10 01:55:00'),
(21, 'CT-0035', 1, 1, 'Customer 35', 'completed', 1977.00, '2025-12-17 07:04:00', '2025-12-17 07:04:00', '2025-12-17 07:04:00'),
(22, 'CT-0036', 1, 1, 'Customer 36', 'pending', 1984.00, '2025-12-22 02:56:00', '2025-12-22 02:56:00', '2025-12-22 02:56:00'),
(23, 'CT-0037', 1, 1, 'Customer 37', 'completed', 893.00, '2026-01-01 08:55:00', '2026-01-01 08:55:00', '2026-01-01 08:55:00'),
(24, 'CT-0038', 1, 1, 'Customer 38', 'completed', 1235.00, '2026-01-06 07:57:00', '2026-01-06 07:57:00', '2026-01-06 07:57:00'),
(25, 'CT-0039', 1, 1, 'Customer 39', 'in_kitchen', 588.00, '2025-12-14 06:26:00', '2025-12-14 06:26:00', '2025-12-14 06:26:00'),
(26, 'CT-0040', 1, 1, 'Customer 40', 'pending', 1573.00, '2025-12-11 00:31:00', '2025-12-11 00:31:00', '2025-12-11 00:31:00'),
(27, 'CT-0041', 1, 1, 'Customer 41', 'completed', 1973.00, '2025-12-25 11:57:00', '2025-12-25 11:57:00', '2025-12-25 11:57:00'),
(28, 'CT-0042', 1, 1, 'Customer 42', 'pending', 811.00, '2025-12-20 01:28:00', '2025-12-20 01:28:00', '2025-12-20 01:28:00'),
(29, 'CT-0043', 1, 1, 'Customer 43', 'completed', 1950.00, '2025-12-17 07:07:00', '2025-12-17 07:07:00', '2025-12-17 07:07:00'),
(30, 'CT-0044', 1, 1, 'Customer 44', 'in_kitchen', 663.00, '2025-12-22 08:43:00', '2025-12-22 08:43:00', '2025-12-22 08:43:00'),
(31, 'CT-0045', 1, 1, 'Customer 45', 'pending', 469.00, '2025-12-20 11:46:00', '2025-12-20 11:46:00', '2025-12-20 11:46:00'),
(32, 'CT-0046', 1, 1, 'Customer 46', 'completed', 704.00, '2026-01-04 05:02:00', '2026-01-04 05:02:00', '2026-01-04 05:02:00'),
(33, 'CT-0047', 1, 1, 'Customer 47', 'in_kitchen', 1040.00, '2025-12-14 03:09:00', '2025-12-14 03:09:00', '2025-12-14 03:09:00'),
(34, 'CT-0048', 1, 1, 'Customer 48', 'completed', 707.00, '2026-01-05 05:16:00', '2026-01-05 05:16:00', '2026-01-05 05:16:00'),
(35, 'CT-0049', 1, 1, 'Customer 49', 'completed', 1796.00, '2025-12-29 03:25:00', '2025-12-29 03:25:00', '2025-12-29 03:25:00'),
(36, 'CT-0050', 1, 1, 'Customer 50', 'completed', 412.00, '2025-12-23 00:00:00', '2025-12-23 00:00:00', '2025-12-23 00:00:00'),
(37, 'CT-0051', 1, 1, 'Customer 51', 'completed', 1783.00, '2025-12-29 10:35:00', '2025-12-29 10:35:00', '2025-12-29 10:35:00'),
(38, 'CT-0052', 1, 1, 'Customer 52', 'completed', 776.00, '2026-01-05 09:18:00', '2026-01-05 09:18:00', '2026-01-05 09:18:00'),
(39, 'CT-0053', 1, 1, 'Customer 53', 'completed', 889.00, '2026-01-01 05:06:00', '2026-01-01 05:06:00', '2026-01-01 05:06:00'),
(40, 'CT-0054', 1, 1, 'Customer 54', 'in_kitchen', 1280.00, '2025-12-19 09:47:00', '2025-12-19 09:47:00', '2025-12-19 09:47:00'),
(41, 'CT-0055', 1, 1, 'Customer 55', 'pending', 711.00, '2025-12-24 07:59:00', '2025-12-24 07:59:00', '2025-12-24 07:59:00'),
(42, 'CT-0056', 1, 1, 'Customer 56', 'completed', 1740.00, '2025-12-26 10:37:00', '2025-12-26 10:37:00', '2025-12-26 10:37:00'),
(43, 'CT-0057', 1, 1, 'Customer 57', 'in_kitchen', 481.00, '2025-12-30 00:23:00', '2025-12-30 00:23:00', '2025-12-30 00:23:00'),
(44, 'CT-0058', 1, 1, 'Customer 58', 'completed', 942.00, '2026-01-08 06:35:00', '2026-01-08 06:35:00', '2026-01-08 06:35:00'),
(45, 'CT-0059', 1, 1, 'Customer 59', 'completed', 1767.00, '2025-12-17 05:39:00', '2025-12-17 05:39:00', '2025-12-17 05:39:00'),
(46, 'CT-0060', 1, 1, 'Customer 60', 'completed', 1961.00, '2025-12-17 08:43:00', '2025-12-17 08:43:00', '2025-12-17 08:43:00'),
(47, 'CT-0061', 1, 1, 'Customer 61', 'completed', 1087.00, '2026-01-06 02:51:00', '2026-01-06 02:51:00', '2026-01-06 02:51:00'),
(48, 'CT-0062', 1, 1, 'Customer 62', 'completed', 1179.00, '2026-01-01 11:29:00', '2026-01-01 11:29:00', '2026-01-01 11:29:00'),
(49, 'CT-0063', 1, 1, 'Customer 63', 'completed', 489.00, '2026-01-08 10:14:00', '2026-01-08 10:14:00', '2026-01-08 10:14:00'),
(50, 'CT-0064', 1, 1, 'Customer 64', 'pending', 1959.00, '2025-12-15 02:03:00', '2025-12-15 02:03:00', '2025-12-15 02:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('iwd35NsDToYepWqDco992K5IUp3mT2XAqHclW1eD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWWJ1UUMwMWJQQjg0d0Nqc2t0MTA4RjFVTm52ajJsN1pjTG0wTWtwaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768328887),
('LWBZKNEy2xCjz0sU6Kh1xOV6LXJcRZF3U2E1CbwO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSXY1cGJUTm42eXVQeTU4WjdHallqMHV2NmZsTU5ycG1TNERtcFNYZSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3N0YWZmLW1hbmFnZW1lbnQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768367665),
('lYB8Pm2wpXpnffNQMEBebMm9FMuglH52R3OzFSIs', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il9mbGFzaCI7YToyOntzOjM6Im5ldyI7YTowOnt9czozOiJvbGQiO2E6MDp7fX1zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0NDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3N0YWZmLW1hbmFnZW1lbnQiO3M6NToicm91dGUiO3M6MjI6ImFkbWluLnN0YWZmLW1hbmFnZW1lbnQiO31zOjY6Il90b2tlbiI7czo0MDoiWEcxUk9LTnp2OFZaZHlnblNXb1p3SzR5UlY4dmo1RkZnT3ZDYTBLNiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluLXBhbmVsIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7fQ==', 1768329733),
('nEMK8nNRgOS3eRRYZxqTdrIkEriSvdYJXlbzbkPV', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQTdZWTNyTnNJb0w3bXoxdlVWMUVWVnRTU2h3MmxVcjN3RXRpa0hkUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZi1tYW5hZ2VtZW50IjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5zdGFmZi1tYW5hZ2VtZW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7fQ==', 1768369834);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('OWNER','BRANCH_MANAGER','STAFF') NOT NULL,
  `avatarUrl` varchar(255) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `avatarUrl`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(11, 'owner_admin', 'owner@chikintayo.com', '$2y$12$MYf/AzoU4mwCKXOJU/sJI./iboBZyo6AntC02heZjc5hqorr9fw.e', 'System Administrator', 'OWNER', NULL, NULL, NULL, '09123456789', 'Main Office', 1, '2026-01-13 16:05:28', '2026-01-13 16:09:05', NULL),
(12, 'mark_01', 'markjulius@gmail.com', '$2y$10$1kFz4h4Nrj7OUIz4oAjdiuBjjdRpufS65t7lsj6nRVd2XM1lVTc1a', 'Mark Test', 'BRANCH_MANAGER', NULL, 1, NULL, '+63 908 171 8919', 'Dasmariñas Cavite', 1, '2026-01-13 08:37:04', '2026-01-13 08:37:04', NULL),
(13, 'gab_02', 'gab@test.com', '$2y$10$DvAjtNhK/KSMbmxessPJ8eDANatAhoTd0HANS.liJTL1aBCeP70iy', 'Gab Test', 'BRANCH_MANAGER', NULL, 2, NULL, '+63 908 171 8997', 'Taga Mars', 1, '2026-01-13 08:40:04', '2026-01-13 08:40:04', NULL),
(14, 'Janne.who', 'jeremy@test.com', '$2y$10$ILwjlAYaTeDFzWUaRg9TiOpdQgNQUgb5GG1777izaTXzutTQ0.6/W', 'Jeremy Anne', 'BRANCH_MANAGER', NULL, 3, NULL, '+63 908 171 0897', 'Taga Elliston', 1, '2026-01-13 08:41:59', '2026-01-13 10:18:03', '2026-01-13 10:18:03'),
(15, 'vince_bading', 'vie@test.com', '$2y$10$JN.UqE5Kzu2.U5k/spHHv.6XPdtDIC0VWeeg9e.vUPFy9yLadu69u', 'Vince Bading', 'STAFF', NULL, 1, NULL, '+63 908 171 0000', 'Taga Gay', 1, '2026-01-13 08:43:09', '2026-01-13 10:17:52', '2026-01-13 10:17:52');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `position_title` varchar(120) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `branch_name_label` varchar(120) DEFAULT NULL,
  `emergency_contact` varchar(120) DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_branch` (`branch_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_profiles_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
