-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2026 at 10:35 AM
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
(1, 'QC_MAIN', 'Quezon City Main Branch', 'Quezon City, Metro Manila', 1, '2026-01-08 16:09:31', '2026-01-08 16:09:31'),
(10, 'MAKATI', 'Makati Branch', 'Makati City, Metro Manila', 1, '2026-01-11 05:03:43', '2026-01-11 05:03:43'),
(11, 'BGC', 'BGC Branch', 'Bonifacio Global City, Taguig', 1, '2026-01-11 05:03:43', '2026-01-11 05:03:43'),
(12, 'PASIG', 'Pasig Branch', 'Pasig City, Metro Manila', 1, '2026-01-11 05:03:43', '2026-01-11 05:03:43'),
(13, 'MANILA', 'Manila Branch', 'Manila City', 1, '2026-01-11 05:03:43', '2026-01-11 05:03:43');

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
(5, '0001_01_01_000003_create_sessions_table', 3);

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
('15AiRkZZ3P9Ngu6iRg9ivfmnsRLjKqLnQVcRpT4l', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMzRNR3hyZW9nSmFERzJCSTVPTDdja3lyS0liUE9WQ1NhUzNhWlJLdSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768290209),
('3DWIItRYtgPxPpqOd1yYF0bkT1YzD6FTDCY3q1vF', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRnhEaEhkbU9KQ3RSQkxoOGpMancza25tQktXZGNPMzFFWmpDWEp2MyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768291204),
('5cpZuoh59wVPGyP1KFkoPMgDyUp2VwGVmzebaDWl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXpyTlRqQWRISmlXVkI4THVweHBwWnRSM1BRRGtMeEtiUWxUTXpvcSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768295003),
('68VhN7g3DKcCbmKg9mUKMtXbosJbCKqz6kH9idf5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNlZpaWVPU0ZUZTM5MmV6NnVhYnZzV2g4cDU2VWFMV01KR21KZGpoZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288897),
('8tcbXXga3ywSsz5qIfFhscfHvLOSJ62TJDZQKQaI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRWtnekdRUHlqSnRDcGM4MDVsbUJyTW5qcmV1MmFubEdqMVBVSnZKTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768293507),
('8YxUsqOjt6WWerxSLoD5DgHR9RgM7fNC3O6pDcr9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYXIwZlVkamRvUEtrd1pUazE5RXZWZVR0RlFmVzdjc3QyTWdFRHBoeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294018),
('9AUTqDuNers9teCdBH5SZrLtt2JF4LCbpGk9xD8w', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOWJjVUFXWjBGUW5tVUpsYllZRzJiNzNlcjhNS29qUFloMWVaSnVRSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768293663),
('9WqNChPG5H3FeptHq5EbvW1MWShA6MdOqaQmLzG3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU2tHbHQ4cEtzR3BXbUtEZUNzdlV5QW92V2lXMW90dEFuVjVKbTZFZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768290410),
('bcTRwxla5mW9eb63sG0u9X27uCLDRZxhf3xWZkVg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQ25vMEVyRGZ2ZE1IV3hSaVU4a2Roek9Tc3hCN3ZLamJtcGQ5TzhvTSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxMTc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZi1tYW5hZ2VtZW50P2lkPWNmMDc1YmRjLWMwOGEtNDIxZi04YjQ3LWQ4ZTJmZjU0MjE0YSZ2c2NvZGVCcm93c2VyUmVxSWQ9MTc2ODI4ODIzMTA2NCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjExNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3N0YWZmLW1hbmFnZW1lbnQ/aWQ9Y2YwNzViZGMtYzA4YS00MjFmLThiNDctZDhlMmZmNTQyMTRhJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4Mjg4MjMxMDY0IjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5zdGFmZi1tYW5hZ2VtZW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768288231),
('BFjt6xBGVnxoGXoxpCnpi9v4nBoelg6FfUOFbVBr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUmJ1eGF2V0hkQTRCalNiZ3phcFkxV1lZY2dKUHUzMnhRc1dXanRXdCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294211),
('btU7hv2UGZ01r3k35F8obudQ2PolgMwZMhgmN5dn', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUJKMUd4MTdzakpDQ0EwTkRNakNnZEJlOGNCNXFueU53TTVoQ3JWeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4MjkwNjk5NDkzIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768290877),
('chYBTUqijov2qrI0dAwEEToqCE18PuT4TzAjs9pf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidU82ZVNvRVJrbkxiNlZYaVhWSXd0enltdnJJVEFvdlJ0bXl2SjBZMCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288631),
('DbtAQ1oDSH0oJgMY807LdntQOeuzhYVyb4qw2Yvr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTmhXWGhQY0k2TTI2d3BlZFJuTEtjc0tzaDZzd0RXZlhLa3U3eTRSUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768291349),
('dP6JpoAm3a5BE09HUzPMrIIHlQYi5tU0EuFyFy8T', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYlU3ZTdWb2lVbkNhV1NtVzJmVFNxRkMwTFQwN0l6UUFYcG5SazVJeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288232),
('DXiOQaZdJM9vHuwJNMDrDcTMZ5ijg17VphZ9zJnE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWmt0eWdUeEFWNUY3eDBQeGs3R1huQXU1ZFlsemQ3ZWRpN0FLVWlYeCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768291211),
('e2GVNBR8PTwtMoOABgTIcTjSsSmh3O7OFh7cVbjT', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaG9jRzgyT1FLMUdIVko4UFdLdGVOamh6a2padHFpdk9tY0lwTDAwSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768290087),
('e5FgKtlnF20wrslLiHU9zXsnrFyefVtPRnaCbTgA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWVBrenVVNVZLaHRpNU9XM3YyYzg1ZnNZWGNaZTllTzYyZDdnakVubSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768292376),
('EWXzTdtOxDmS5pAbXqG34PlrGS8pMBCUeFXKV7gB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibVc1OHdleFBGbmY1dmdYVEZ1d2RreUJFM3BJdW5WdzNHU1AyaWpkOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288982),
('fsa3jgK6so7e2PSvfdnKmcmbjWXzLRW7jWrUVzKT', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN1pUd2Y0cFcxa2F0ZTFlMkQ5dVhZTWVZVHBHSzRHS0JOVWRkZktDeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4MjkwNjk5NDkzIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768290843),
('g1E9wjB2ER63rBws43a1TCmc3zCi9ck22pqJYfZP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNXlBbzRYeXlONzlVUEhJSW4wUzVRZDdNM1Z4eVF6YWRvZHZIVFBDNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768289750),
('H0AjEhPAztgNVSUnqqjzSLyoIkgPYfFJE8If0gA6', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSlVmVTVCWDVnT01wTmNsb2U5NEZvREtEZVBLaEF3eTczQUVxVWxLbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294720),
('iaxryfTnuJKu93MUZw8T5XAABC3WI9wIqqQVDnBM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic1l5S1RrbmV4VFlZZUUyU2RJNUc0aHFGRVA3S0NrTEhwbWRqdDB5RSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288364),
('la812XzBkZJnPEmVWD2tV2Nri0lvZex7BYPukMcg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieVVaZlVlM2Nqc1hIWHczTHQ3MzdJSnRNTElrSlY0NHJtVEk1UU5xNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4MjkwODgyNTEwIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768290882),
('lVHlsukJCfvbvwL49OgL5oblA4iarb6Cw23bukOd', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNXBzV2FWdGlVN1RYMHVmN0xBbnZOYnlyMUoxNHMzUW95QjNNT1RiayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768289731),
('MacOPc0xKFJ4IrLfAcvv4nHVqZ9NKGx2JmpfNsAh', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNkh4eVZGaHA0eG9UWTZXVW1LNEx2WU93RGJ5dkxwa1BSam80bHlnaiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768296461),
('mq6OtprmFDJE1hGUX6Xoz3JASpC4GEB5UBlri5w1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1ZmMzc0cU5jQ1pxbkxRSjVYNEpBb1pQNG8yM2FHY3NDSTRrTWt2VSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4MjkwNjk5NDkzIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768290857),
('MQnKQUQidYP7gx0Z2waHnQwqnp25qQARomcojmnJ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicG5udzl1UkNCVlVxeTdibUszeHlRTldkUGlvTHpvcDRhNlo2d2lWSyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxMTc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZi1tYW5hZ2VtZW50P2lkPWNmMDc1YmRjLWMwOGEtNDIxZi04YjQ3LWQ4ZTJmZjU0MjE0YSZ2c2NvZGVCcm93c2VyUmVxSWQ9MTc2ODI4ODM2MzA5NiI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjExNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3N0YWZmLW1hbmFnZW1lbnQ/aWQ9Y2YwNzViZGMtYzA4YS00MjFmLThiNDctZDhlMmZmNTQyMTRhJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4Mjg4MzYzMDk2IjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5zdGFmZi1tYW5hZ2VtZW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768288363),
('oMZYoQnIvgqUzYr59NLWvj74VJKcGWHSKP96rmUF', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoia3BzUzJyVlgwOW1UNnRBdmtKbVdLMFB5akgzZk41RjJpZUl2UDBsUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294226),
('Py37P9iWsvZwZUpHbsgvmAZHwdVC0av5QUGV8hCB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicHNYQ1JpT1l6czBCQ1lsUG1BQ1l6emswaG1uSTlZUG1vMUZLYkRYVyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoxMTc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9zdGFmZi1tYW5hZ2VtZW50P2lkPTAyZjYxODc4LWNlNzYtNGZlMi04NzM2LWU5YmFmMTJlYzE2MCZ2c2NvZGVCcm93c2VyUmVxSWQ9MTc2ODI4ODQ2MjAyNSI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjExNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL3N0YWZmLW1hbmFnZW1lbnQ/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4Mjg4NDYyMDI1IjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5zdGFmZi1tYW5hZ2VtZW50Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768288462),
('qiJ7reC3ZusB29x9q5xzW2K4G72LdGyzaEzfonjI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTHZBNk5FejNDWVdXbGQwZHRCRlhaQThtdEtLNzdxSUk4NFF6aGh4aiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768290307),
('QjJLfaJgUwyC3vIq9zyHgaQEpNYfoTjKbBLPbYq9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUVh4MlU3eXZuZ21xaFFreTFvcHVWYTJhaEJLMXk4MVMyTmFOemZ5ZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768292285),
('s6p2oZcyGDS1eVae8TR9AsQWnTOl8mSQH9HhHg61', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicFVnVGJGbWI1YUo1b0JBbHdycHZkNDQyRFNPRngxa0M4YjEwVndCZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288742),
('sPjGjlvMPtA9mkyAwk5WAxB1fI8jIrvUccHLnF3H', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTWxkU0RMWFNSSFFUdGhFYTl2ZlJ4QjVRNWFDWkN0MTN5N3g3cnBNYiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTAwOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9naW4/aWQ9MDJmNjE4NzgtY2U3Ni00ZmUyLTg3MzYtZTliYWYxMmVjMTYwJnZzY29kZUJyb3dzZXJSZXFJZD0xNzY4MjkwNjk5NDkzIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768290699),
('sy59RZhtiPamJO5qee46Uy372XMXdi3hLXoSyc0R', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoib1hxZ1hLS2VqQU00VnVrUE9GTk1wbGF4a1luOG1GSzlvMzQwM0hyNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294008),
('TCn9hPeaWSk9vIMWK4dKkgwjdHa5hEAbnKZq9lIA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFdRa3U0ZTFsWGFkV2hsekRQbGRQaFRUbUpzWGVmUllFc0xkaFNnTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768293494),
('tHIkCL53b4H1NTMv0l5M7ebwDaCgvzkaokiKJ05p', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUm9kT3hxNmwwbWE2SE1MckhqQlVISGY2Z1gyV092YmVWbHhZMWxoayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768289934),
('UYyLPDprFeklyaZkBGHk5NLA7P8anYyhxlog90Gw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNnZwdjUzV1FTa0R5RzRKQjFOSTd2SlJIa0ZjaU9TRmNrZEtWR3huRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288463),
('VyQjICsWhwhf2pKqkdQcmHFfC2PUJVQDuLqxKNjq', 8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZlYxMXUwazNjODlCOUhjSVlENkNacTdjdnFyMUlXbTY2Q3lVbWhVcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjg7czo3OiJ1c2VyX2lkIjtpOjg7czo5OiJ1c2VyX3JvbGUiO3M6MTQ6IkJSQU5DSF9NQU5BR0VSIjtzOjk6InVzZXJfbmFtZSI7czo2OiJnYWJnYWIiO30=', 1768290238),
('wSyIZv9FUdAJtWPbkv62RQJ28yEt9FCiNjekyNex', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ25VcG05YmJmcThFUFM1cjVZdHQ3ZVhCdjJ6M1JPZnIwb1JoMUQ1NCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768288742),
('WztnGFHCTt3zjIF1xCGnFo2w4nsaRpNUIvKg2Y7P', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3JaSGtJT01GRXN4ZDgzallhelNIVlNQdjhHdnB0ZVQ0ZHZ0ZFJuYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1768289988),
('YBKdSAZN6XOdA0zkha4TUHA8LHRqSySmqQdmQmgY', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.108.0 Chrome/142.0.7444.235 Electron/39.2.7 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZVdYakw3RGFGN3lrQTJwYXUxVVpPVTdOamNOck9jSkQ5Q0gwdkI5biI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1768294223);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `avatarUrl`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 'Umbal', 'Umbal@gmail.com', '$2y$10$C1d9gWlr38feyfl0hwQnkOeYllKFnNSJ3p7R1A3pYAJge3P.WElIu', 'TambolUmbal', 'BRANCH_MANAGER', NULL, 10, NULL, '09156818857', 'Sa pelepens', 1, '2026-01-11 23:33:14', '2026-01-11 23:33:14'),
(8, 'gab', 'branch.mnl@example.com', '$2y$10$AfZn9wX829eVKYDu5vnhmunkwVJFwNMilSBbO/Rq62XUQntH0ar0i', 'gabgab', 'BRANCH_MANAGER', NULL, 10, NULL, '09156818856', NULL, 1, '2026-01-12 23:25:11', '2026-01-12 23:25:11'),
(9, 'gab_owner', 'gab@gmail.com', '$2y$12$T.eqMXV/byHbt9HYm6EQSuScSP3SSx8.nWzvMxf3Xvk/BwYqJwCI.', 'Gabriel', 'OWNER', NULL, NULL, NULL, '09156818852', 'cavite dasma', 1, '2026-01-13 08:23:27', '2026-01-13 08:25:10'),
(10, 'staff_jane', 'staff.jane@example.com', '$2y$12$SbqKs37js30eIGWaeGz8lOZ322Efi9D/lil3t9MigxiqqZzftujke', 'Jane Dela Cruz', 'STAFF', NULL, NULL, '/storage/avatars/avatar_10_1768293625.png', '09170000000', 'Quezon City, Metro Manila', 1, '2026-01-13 08:34:51', '2026-01-13 08:40:26');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
