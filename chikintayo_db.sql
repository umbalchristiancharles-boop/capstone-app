-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 03:12 PM
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
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `hours_worked` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'absent',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'QC_MAIN', 'Quezon City Main Branch', 'Quezon City, Metro Manila', 1, '2026-02-19 07:29:08', '2026-02-19 07:29:08'),
(2, 'MAKATI', 'Makati Branch', 'Makati City, Metro Manila', 1, '2026-02-19 07:29:08', '2026-02-19 07:29:08'),
(3, 'BGC', 'BGC Branch', 'Bonifacio Global City, Taguig', 1, '2026-02-19 07:29:08', '2026-02-19 07:29:08'),
(4, 'PASIG', 'Pasig Branch', 'Pasig City, Metro Manila', 1, '2026-02-19 07:29:08', '2026-02-19 07:29:08'),
(5, 'MANILA', 'Manila Branch', 'Manila City', 1, '2026-02-19 07:29:08', '2026-02-19 07:29:08');

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
-- Table structure for table `customer_accounts`
--

CREATE TABLE `customer_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `total_comments` int(11) NOT NULL DEFAULT 0,
  `total_ratings` int(11) NOT NULL DEFAULT 0,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_timesheets`
--

CREATE TABLE `employee_timesheets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(4, '0001_01_01_000003_create_sessions_table', 1),
(5, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2026_01_08_203224_create_orders_table', 1),
(7, '2026_01_11_130224_create_branches_table', 1),
(8, '2026_01_13_165546_add_soft_deletes_to_users_table', 1),
(9, '2026_01_19_141306_create_password_resets_table', 1),
(10, '2026_01_20_162500_add_remember_token_to_users_table', 1),
(11, '2026_01_20_170000_copy_password_hash_to_password', 1),
(12, '2026_01_20_170100_add_password_column_and_copy_hash', 1),
(13, '2026_01_30_130000_change_users_role_to_string', 1),
(14, '2026_02_02_120000_add_must_change_password_to_users_table', 1),
(15, '2026_02_04_000000_create_attendance_table', 1),
(16, '2026_02_05_182716_create_employee_timesheets_table', 1),
(17, '2026_02_05_210000_create_staff_documents_table', 1),
(18, '2026_02_05_230000_make_staff_documents_nullable', 1),
(19, '2026_02_06_073143_add_parent_comment_id_to_product_comments_table', 1),
(20, '2026_02_06_073404_make_rating_nullable_in_product_comments_table', 1),
(21, '2026_02_06_120000_create_products_table', 1),
(22, '2026_02_06_120100_create_product_comments_table', 1),
(23, '2026_02_06_130000_add_rating_to_product_comments_table', 1),
(24, '2026_02_06_200000_add_authentication_to_users_table', 1),
(25, '2026_02_06_200100_create_customer_accounts_table', 1),
(26, '2026_02_06_200200_enhance_product_comments_table', 1),
(27, '2026_02_06_230000_fix_add_parent_comment_id_to_product_comments', 1),
(28, '2026_02_10_000000_add_department_to_users_table', 1),
(29, '2026_02_17_205226_add_fields_to_products_table', 1),
(30, '2026_02_17_214000_add_branch_id_to_users_table', 2),
(31, '2026_02_17_213000_add_avatar_url_to_users_table', 3),
(32, '2026_02_17_212000_add_phone_number_to_users_table', 4),
(33, '2026_02_17_211000_add_address_to_users_table', 5),
(34, '2026_02_17_210000_add_is_active_to_users_table', 6),
(35, '2026_02_17_215000_add_full_name_to_users_table', 7);

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
(1, 'CT-0015', 1, 1, 'Customer 15', 'completed', 445.00, '2026-02-17 07:49:00', '2026-02-17 07:49:00', '2026-02-17 07:49:00'),
(2, 'CT-0016', 1, 1, 'Customer 16', 'completed', 493.00, '2026-02-05 05:54:00', '2026-02-05 05:54:00', '2026-02-05 05:54:00'),
(3, 'CT-0017', 1, 1, 'Customer 17', 'in_kitchen', 857.00, '2026-02-14 07:53:00', '2026-02-14 07:53:00', '2026-02-14 07:53:00'),
(4, 'CT-0018', 1, 1, 'Customer 18', 'completed', 1901.00, '2026-02-05 11:59:00', '2026-02-05 11:59:00', '2026-02-05 11:59:00'),
(5, 'CT-0019', 1, 1, 'Customer 19', 'completed', 440.00, '2026-02-05 07:03:00', '2026-02-05 07:03:00', '2026-02-05 07:03:00'),
(6, 'CT-0020', 1, 1, 'Customer 20', 'completed', 1234.00, '2026-02-07 02:53:00', '2026-02-07 02:53:00', '2026-02-07 02:53:00'),
(7, 'CT-0021', 1, 1, 'Customer 21', 'completed', 1078.00, '2026-01-31 07:02:00', '2026-01-31 07:02:00', '2026-01-31 07:02:00'),
(8, 'CT-0022', 1, 1, 'Customer 22', 'in_kitchen', 1742.00, '2026-02-09 12:22:00', '2026-02-09 12:22:00', '2026-02-09 12:22:00'),
(9, 'CT-0023', 1, 1, 'Customer 23', 'completed', 318.00, '2026-02-16 08:49:00', '2026-02-16 08:49:00', '2026-02-16 08:49:00'),
(10, 'CT-0024', 1, 1, 'Customer 24', 'pending', 1970.00, '2026-01-20 03:57:00', '2026-01-20 03:57:00', '2026-01-20 03:57:00'),
(11, 'CT-0025', 1, 1, 'Customer 25', 'in_kitchen', 1597.00, '2026-02-16 05:21:00', '2026-02-16 05:21:00', '2026-02-16 05:21:00'),
(12, 'CT-0026', 1, 1, 'Customer 26', 'completed', 1206.00, '2026-02-12 09:18:00', '2026-02-12 09:18:00', '2026-02-12 09:18:00'),
(13, 'CT-0027', 1, 1, 'Customer 27', 'pending', 1911.00, '2026-02-10 07:19:00', '2026-02-10 07:19:00', '2026-02-10 07:19:00'),
(14, 'CT-0028', 1, 1, 'Customer 28', 'completed', 1925.00, '2026-01-26 12:46:00', '2026-01-26 12:46:00', '2026-01-26 12:46:00'),
(15, 'CT-0029', 1, 1, 'Customer 29', 'completed', 838.00, '2026-02-05 01:35:00', '2026-02-05 01:35:00', '2026-02-05 01:35:00'),
(16, 'CT-0030', 1, 1, 'Customer 30', 'completed', 617.00, '2026-02-04 00:17:00', '2026-02-04 00:17:00', '2026-02-04 00:17:00'),
(17, 'CT-0031', 1, 1, 'Customer 31', 'completed', 1278.00, '2026-01-26 10:19:00', '2026-01-26 10:19:00', '2026-01-26 10:19:00'),
(18, 'CT-0032', 1, 1, 'Customer 32', 'pending', 601.00, '2026-02-01 06:29:00', '2026-02-01 06:29:00', '2026-02-01 06:29:00'),
(19, 'CT-0033', 1, 1, 'Customer 33', 'completed', 1588.00, '2026-02-04 01:24:00', '2026-02-04 01:24:00', '2026-02-04 01:24:00'),
(20, 'CT-0034', 1, 1, 'Customer 34', 'pending', 966.00, '2026-01-27 06:30:00', '2026-01-27 06:30:00', '2026-01-27 06:30:00'),
(21, 'CT-0035', 1, 1, 'Customer 35', 'completed', 1121.00, '2026-02-14 11:59:00', '2026-02-14 11:59:00', '2026-02-14 11:59:00'),
(22, 'CT-0036', 1, 1, 'Customer 36', 'completed', 1531.00, '2026-02-04 07:15:00', '2026-02-04 07:15:00', '2026-02-04 07:15:00'),
(23, 'CT-0037', 1, 1, 'Customer 37', 'completed', 1174.00, '2026-01-25 12:10:00', '2026-01-25 12:10:00', '2026-01-25 12:10:00'),
(24, 'CT-0038', 1, 1, 'Customer 38', 'pending', 1700.00, '2026-02-13 06:42:00', '2026-02-13 06:42:00', '2026-02-13 06:42:00'),
(25, 'CT-0039', 1, 1, 'Customer 39', 'completed', 611.00, '2026-02-09 07:19:00', '2026-02-09 07:19:00', '2026-02-09 07:19:00'),
(26, 'CT-0040', 1, 1, 'Customer 40', 'completed', 1768.00, '2026-01-30 12:08:00', '2026-01-30 12:08:00', '2026-01-30 12:08:00'),
(27, 'CT-0041', 1, 1, 'Customer 41', 'in_kitchen', 1377.00, '2026-01-22 00:15:00', '2026-01-22 00:15:00', '2026-01-22 00:15:00'),
(28, 'CT-0042', 1, 1, 'Customer 42', 'pending', 1895.00, '2026-01-26 00:56:00', '2026-01-26 00:56:00', '2026-01-26 00:56:00'),
(29, 'CT-0043', 1, 1, 'Customer 43', 'completed', 1085.00, '2026-02-07 10:02:00', '2026-02-07 10:02:00', '2026-02-07 10:02:00'),
(30, 'CT-0044', 1, 1, 'Customer 44', 'pending', 888.00, '2026-01-27 05:50:00', '2026-01-27 05:50:00', '2026-01-27 05:50:00'),
(31, 'CT-0045', 1, 1, 'Customer 45', 'pending', 580.00, '2026-01-27 01:13:00', '2026-01-27 01:13:00', '2026-01-27 01:13:00'),
(32, 'CT-0046', 1, 1, 'Customer 46', 'completed', 326.00, '2026-01-29 03:13:00', '2026-01-29 03:13:00', '2026-01-29 03:13:00'),
(33, 'CT-0047', 1, 1, 'Customer 47', 'completed', 683.00, '2026-02-08 06:08:00', '2026-02-08 06:08:00', '2026-02-08 06:08:00'),
(34, 'CT-0048', 1, 1, 'Customer 48', 'pending', 1362.00, '2026-02-16 07:12:00', '2026-02-16 07:12:00', '2026-02-16 07:12:00'),
(35, 'CT-0049', 1, 1, 'Customer 49', 'in_kitchen', 930.00, '2026-02-12 00:34:00', '2026-02-12 00:34:00', '2026-02-12 00:34:00'),
(36, 'CT-0050', 1, 1, 'Customer 50', 'in_kitchen', 1741.00, '2026-01-19 11:19:00', '2026-01-19 11:19:00', '2026-01-19 11:19:00'),
(37, 'CT-0051', 1, 1, 'Customer 51', 'completed', 1847.00, '2026-02-11 06:26:00', '2026-02-11 06:26:00', '2026-02-11 06:26:00'),
(38, 'CT-0052', 1, 1, 'Customer 52', 'pending', 1870.00, '2026-02-15 03:03:00', '2026-02-15 03:03:00', '2026-02-15 03:03:00'),
(39, 'CT-0053', 1, 1, 'Customer 53', 'pending', 728.00, '2026-01-29 02:02:00', '2026-01-29 02:02:00', '2026-01-29 02:02:00'),
(40, 'CT-0054', 1, 1, 'Customer 54', 'pending', 1273.00, '2026-01-26 02:28:00', '2026-01-26 02:28:00', '2026-01-26 02:28:00'),
(41, 'CT-0055', 1, 1, 'Customer 55', 'in_kitchen', 923.00, '2026-01-19 04:46:00', '2026-01-19 04:46:00', '2026-01-19 04:46:00'),
(42, 'CT-0056', 1, 1, 'Customer 56', 'completed', 1197.00, '2026-02-12 02:28:00', '2026-02-12 02:28:00', '2026-02-12 02:28:00'),
(43, 'CT-0057', 1, 1, 'Customer 57', 'completed', 561.00, '2026-02-03 09:12:00', '2026-02-03 09:12:00', '2026-02-03 09:12:00'),
(44, 'CT-0058', 1, 1, 'Customer 58', 'completed', 808.00, '2026-01-28 07:27:00', '2026-01-28 07:27:00', '2026-01-28 07:27:00'),
(45, 'CT-0059', 1, 1, 'Customer 59', 'in_kitchen', 343.00, '2026-01-19 12:19:00', '2026-01-19 12:19:00', '2026-01-19 12:19:00'),
(46, 'CT-0060', 1, 1, 'Customer 60', 'in_kitchen', 1318.00, '2026-02-11 10:04:00', '2026-02-11 10:04:00', '2026-02-11 10:04:00'),
(47, 'CT-0061', 1, 1, 'Customer 61', 'completed', 585.00, '2026-01-26 11:02:00', '2026-01-26 11:02:00', '2026-01-26 11:02:00'),
(48, 'CT-0062', 1, 1, 'Customer 62', 'completed', 1446.00, '2026-02-02 00:56:00', '2026-02-02 00:56:00', '2026-02-02 00:56:00'),
(49, 'CT-0063', 1, 1, 'Customer 63', 'completed', 418.00, '2026-01-24 07:25:00', '2026-01-24 07:25:00', '2026-01-24 07:25:00'),
(50, 'CT-0064', 1, 1, 'Customer 64', 'completed', 1754.00, '2026-02-15 11:38:00', '2026-02-15 11:38:00', '2026-02-15 11:38:00');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `sku` varchar(255) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_comments`
--

CREATE TABLE `product_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `author` varchar(60) NOT NULL,
  `text` text NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('OQuOX9IGxlstGae55o4oFrcBSLr0xB2FJasyRDCj', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoicFhBa0Y4dXI5aHVuRVA0VWtia0lLTGZkcGM4MFRUbk9HT0Z1M0xudyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC8ud2VsbC1rbm93bi9hcHBzcGVjaWZpYy9jb20uY2hyb21lLmRldnRvb2xzLmpzb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7czo3OiJ1c2VyX2lkIjtpOjI7czo5OiJ1c2VyX3JvbGUiO3M6NToiQURNSU4iO3M6OToidXNlcl9uYW1lIjtzOjEwOiJNYWluIEFkbWluIjtzOjEzOiJyZWRpcmVjdF9wYXRoIjtzOjEyOiIvYWRtaW4tcGFuZWwiO30=', 1771769349);

-- --------------------------------------------------------

--
-- Table structure for table `staff_documents`
--

CREATE TABLE `staff_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `government_id_path` varchar(255) DEFAULT NULL,
  `psa_birth_certificate_path` varchar(255) DEFAULT NULL,
  `nbi_clearance_path` varchar(255) DEFAULT NULL,
  `police_clearance_path` varchar(255) DEFAULT NULL,
  `medical_certificate_path` varchar(255) DEFAULT NULL,
  `drug_test_result_path` varchar(255) DEFAULT NULL,
  `sss_id_path` varchar(255) DEFAULT NULL,
  `philhealth_id_path` varchar(255) DEFAULT NULL,
  `pagibig_mdf_path` varchar(255) DEFAULT NULL,
  `tin_id_path` varchar(255) DEFAULT NULL,
  `diploma_transcript_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_documents`
--

INSERT INTO `staff_documents` (`id`, `user_id`, `resume_path`, `government_id_path`, `psa_birth_certificate_path`, `nbi_clearance_path`, `police_clearance_path`, `medical_certificate_path`, `drug_test_result_path`, `sss_id_path`, `philhealth_id_path`, `pagibig_mdf_path`, `tin_id_path`, `diploma_transcript_path`, `created_at`, `updated_at`) VALUES
(1, 4, 'staff-documents/4/resume.png', 'staff-documents/4/government_id.png', 'staff-documents/4/psa_birth_certificate.png', 'staff-documents/4/nbi_clearance.png', 'staff-documents/4/police_clearance.png', 'staff-documents/4/medical_certificate.png', 'staff-documents/4/drug_test_result.png', 'staff-documents/4/sss_id.png', 'staff-documents/4/philhealth_id.png', 'staff-documents/4/pagibig_mdf.png', 'staff-documents/4/tin_id.png', 'staff-documents/4/diploma_transcript.png', '2026-02-19 07:39:55', '2026-02-19 07:39:55'),
(2, 6, NULL, 'staff-documents/6/government_id.png', 'staff-documents/6/psa_birth_certificate.png', 'staff-documents/6/nbi_clearance.png', 'staff-documents/6/police_clearance.png', 'staff-documents/6/medical_certificate.png', 'staff-documents/6/drug_test_result.png', 'staff-documents/6/sss_id.png', 'staff-documents/6/philhealth_id.png', 'staff-documents/6/pagibig_mdf.png', 'staff-documents/6/tin_id.png', 'staff-documents/6/diploma_transcript.png', '2026-02-19 17:49:31', '2026-02-19 17:49:31'),
(3, 8, NULL, 'staff-documents/8/government_id.png', 'staff-documents/8/psa_birth_certificate.png', 'staff-documents/8/nbi_clearance.png', 'staff-documents/8/police_clearance.png', 'staff-documents/8/medical_certificate.png', 'staff-documents/8/drug_test_result.png', 'staff-documents/8/sss_id.png', 'staff-documents/8/philhealth_id.png', 'staff-documents/8/pagibig_mdf.png', 'staff-documents/8/tin_id.png', 'staff-documents/8/diploma_transcript.png', '2026-02-21 07:28:01', '2026-02-21 07:28:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'STAFF',
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `department` enum('HR','FINANCE','INVENTORY','LOGISTICS','CASHIER') DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `name`, `password`, `email_verified_at`, `role`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `department`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`) VALUES
(1, 'owner12@example.com', 'owner', 'Owner CT', NULL, '$2y$12$VJUll4ZOxJiysf4O7zLXc.LCL78XSzpGNcX9Oh4nUGkMjOIsbudEq', '2026-02-17 14:41:30', 'OWNER', NULL, NULL, '09081717813', '123 Main St', 1, 'HR', NULL, '2026-02-17 14:41:30', '2026-02-17 16:21:23', NULL, 0),
(2, 'admin_main@example.com', 'admin_main', 'Main Admin', NULL, '$2y$12$YkAwoJ2uovKAo1v5f4ZOLOAa0zyTekL2j5J8ZegQAsMkYbVLM1X2C', '2026-02-17 14:46:48', 'ADMIN', NULL, '/storage/avatars/avatar_2_1771572511.png', '09171234567', 'Admin HQ', 1, 'HR', NULL, '2026-02-17 14:46:48', '2026-02-17 14:46:48', NULL, 0),
(4, 'gab@gmail.com', 'gab', 'gab ongs', NULL, '$2y$12$tlCShykOjGeYR9hW7QW0meBrTXw.J.HEVH4bPOxFzXv2FTpicYuQW', NULL, 'BRANCH_MANAGER', 3, NULL, '09156818857', NULL, 1, 'HR', NULL, '2026-02-19 07:39:55', '2026-02-20 03:32:49', NULL, 0),
(5, 'John@gmail.com', 'John', 'Johnys', NULL, '$2y$12$tOzQICKP7Ga4dVNbh9Bmae16hRHm3tur4t8uFtAILKD90O8M.AF.q', NULL, 'STAFF', 2, NULL, '09156818858', 's', 1, 'INVENTORY', NULL, '2026-02-19 10:59:04', '2026-02-20 03:33:51', NULL, 0),
(6, 'janne@gmail.com', 'Janne', 'Janne De Guzman', NULL, '$2y$12$5xIqDK8xH4WU9MpjjyBZA.hRGqiaZrTkn30TjgzKENTeHDxrS8k3O', NULL, 'STAFF', 5, NULL, '09156818859', '2312455', 1, 'INVENTORY', NULL, '2026-02-19 17:49:30', '2026-02-20 03:32:24', NULL, 1),
(7, 'park@gmail.com', 'Park', 'mr.Parks', NULL, '$2y$12$GOJjIOufxuFrp5o3Ho.UMOsyIrF4vE1.qVL1WF8yV0f/pOujDfjA.', NULL, 'OWNER', NULL, NULL, '09156818853', '', 1, NULL, NULL, '2026-02-20 07:23:35', '2026-02-21 06:55:23', NULL, 0),
(8, 'robert@gmail.com', 'Robert', 'Robert Downie', NULL, '$2y$12$DwUYoOcKVyt.maIktVGaNe6vcuY0iYIKcvaA2XjoUwP/UpDuDYl4S', NULL, 'BRANCH_MANAGER', 2, NULL, '0915681881', '2314', 1, 'INVENTORY', NULL, '2026-02-21 07:28:00', '2026-02-21 07:28:39', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_user_id_date_unique` (`user_id`,`date`),
  ADD KEY `attendance_date_index` (`date`),
  ADD KEY `attendance_status_index` (`status`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_code_unique` (`code`);

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
-- Indexes for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_accounts_user_id_unique` (`user_id`),
  ADD KEY `customer_accounts_email_index` (`email`),
  ADD KEY `customer_accounts_user_id_index` (`user_id`),
  ADD KEY `customer_accounts_status_index` (`status`),
  ADD KEY `customer_accounts_created_at_index` (`created_at`);

--
-- Indexes for table `employee_timesheets`
--
ALTER TABLE `employee_timesheets`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_comments_product_id_created_at_index` (`product_id`,`created_at`),
  ADD KEY `product_comments_user_id_foreign` (`user_id`),
  ADD KEY `product_comments_parent_comment_id_foreign` (`parent_comment_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_documents_user_id_index` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD KEY `users_email_index` (`email`),
  ADD KEY `users_username_index` (`username`),
  ADD KEY `users_role_index` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_timesheets`
--
ALTER TABLE `employee_timesheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD CONSTRAINT `customer_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD CONSTRAINT `product_comments_parent_comment_id_foreign` FOREIGN KEY (`parent_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
