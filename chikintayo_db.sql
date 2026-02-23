-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 07:17 PM
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
(1, 'QC_MAIN', 'Quezon City Main Branch', 'Quezon City, Metro Manila', 1, '2026-02-23 14:44:43', '2026-02-23 14:44:43'),
(2, 'MAKATI', 'Makati Branch', 'Makati City, Metro Manila', 1, '2026-02-23 14:44:43', '2026-02-23 14:44:43'),
(3, 'BGC', 'BGC Branch', 'Bonifacio Global City, Taguig', 1, '2026-02-23 14:44:43', '2026-02-23 14:44:43'),
(4, 'PASIG', 'Pasig Branch', 'Pasig City, Metro Manila', 1, '2026-02-23 14:44:43', '2026-02-23 14:44:43'),
(5, 'MANILA', 'Manila Branch', 'Manila City', 1, '2026-02-23 14:44:43', '2026-02-23 14:44:43');

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
(30, '2026_02_17_210000_add_is_active_to_users_table', 1),
(31, '2026_02_17_211000_add_address_to_users_table', 1),
(32, '2026_02_17_212000_add_phone_number_to_users_table', 1),
(33, '2026_02_17_213000_add_avatar_url_to_users_table', 1),
(34, '2026_02_17_214000_add_branch_id_to_users_table', 1),
(35, '2026_02_17_215000_add_full_name_to_users_table', 1);

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
(1, 'CT-0015', 1, 1, 'Customer 15', 'pending', 1811.00, '2026-01-26 02:17:00', '2026-01-26 02:17:00', '2026-01-26 02:17:00'),
(2, 'CT-0016', 1, 1, 'Customer 16', 'in_kitchen', 776.00, '2026-02-11 00:05:00', '2026-02-11 00:05:00', '2026-02-11 00:05:00'),
(3, 'CT-0017', 1, 1, 'Customer 17', 'completed', 684.00, '2026-01-25 07:51:00', '2026-01-25 07:51:00', '2026-01-25 07:51:00'),
(4, 'CT-0018', 1, 1, 'Customer 18', 'completed', 1516.00, '2026-02-03 04:02:00', '2026-02-03 04:02:00', '2026-02-03 04:02:00'),
(5, 'CT-0019', 1, 1, 'Customer 19', 'pending', 1633.00, '2026-02-16 01:00:00', '2026-02-16 01:00:00', '2026-02-16 01:00:00'),
(6, 'CT-0020', 1, 1, 'Customer 20', 'in_kitchen', 1763.00, '2026-02-17 12:54:00', '2026-02-17 12:54:00', '2026-02-17 12:54:00'),
(7, 'CT-0021', 1, 1, 'Customer 21', 'in_kitchen', 823.00, '2026-02-18 03:20:00', '2026-02-18 03:20:00', '2026-02-18 03:20:00'),
(8, 'CT-0022', 1, 1, 'Customer 22', 'in_kitchen', 690.00, '2026-02-06 12:09:00', '2026-02-06 12:09:00', '2026-02-06 12:09:00'),
(9, 'CT-0023', 1, 1, 'Customer 23', 'in_kitchen', 1719.00, '2026-02-19 07:14:00', '2026-02-19 07:14:00', '2026-02-19 07:14:00'),
(10, 'CT-0024', 1, 1, 'Customer 24', 'completed', 935.00, '2026-02-06 01:08:00', '2026-02-06 01:08:00', '2026-02-06 01:08:00'),
(11, 'CT-0025', 1, 1, 'Customer 25', 'completed', 639.00, '2026-02-01 06:49:00', '2026-02-01 06:49:00', '2026-02-01 06:49:00'),
(12, 'CT-0026', 1, 1, 'Customer 26', 'pending', 673.00, '2026-02-20 11:57:00', '2026-02-20 11:57:00', '2026-02-20 11:57:00'),
(13, 'CT-0027', 1, 1, 'Customer 27', 'in_kitchen', 1524.00, '2026-01-31 07:09:00', '2026-01-31 07:09:00', '2026-01-31 07:09:00'),
(14, 'CT-0028', 1, 1, 'Customer 28', 'completed', 1220.00, '2026-01-31 01:12:00', '2026-01-31 01:12:00', '2026-01-31 01:12:00'),
(15, 'CT-0029', 1, 1, 'Customer 29', 'in_kitchen', 745.00, '2026-01-28 05:13:00', '2026-01-28 05:13:00', '2026-01-28 05:13:00'),
(16, 'CT-0030', 1, 1, 'Customer 30', 'completed', 1832.00, '2026-01-28 02:42:00', '2026-01-28 02:42:00', '2026-01-28 02:42:00'),
(17, 'CT-0031', 1, 1, 'Customer 31', 'completed', 1424.00, '2026-02-13 11:25:00', '2026-02-13 11:25:00', '2026-02-13 11:25:00'),
(18, 'CT-0032', 1, 1, 'Customer 32', 'completed', 1464.00, '2026-02-20 02:37:00', '2026-02-20 02:37:00', '2026-02-20 02:37:00'),
(19, 'CT-0033', 1, 1, 'Customer 33', 'completed', 1187.00, '2026-01-25 09:36:00', '2026-01-25 09:36:00', '2026-01-25 09:36:00'),
(20, 'CT-0034', 1, 1, 'Customer 34', 'pending', 476.00, '2026-02-10 12:13:00', '2026-02-10 12:13:00', '2026-02-10 12:13:00'),
(21, 'CT-0035', 1, 1, 'Customer 35', 'completed', 1420.00, '2026-02-11 01:43:00', '2026-02-11 01:43:00', '2026-02-11 01:43:00'),
(22, 'CT-0036', 1, 1, 'Customer 36', 'in_kitchen', 1012.00, '2026-01-25 06:29:00', '2026-01-25 06:29:00', '2026-01-25 06:29:00'),
(23, 'CT-0037', 1, 1, 'Customer 37', 'completed', 1582.00, '2026-02-10 06:46:00', '2026-02-10 06:46:00', '2026-02-10 06:46:00'),
(24, 'CT-0038', 1, 1, 'Customer 38', 'pending', 448.00, '2026-02-23 05:29:00', '2026-02-23 05:29:00', '2026-02-23 05:29:00'),
(25, 'CT-0039', 1, 1, 'Customer 39', 'completed', 376.00, '2026-02-22 07:41:00', '2026-02-22 07:41:00', '2026-02-22 07:41:00'),
(26, 'CT-0040', 1, 1, 'Customer 40', 'completed', 1562.00, '2026-01-26 12:39:00', '2026-01-26 12:39:00', '2026-01-26 12:39:00'),
(27, 'CT-0041', 1, 1, 'Customer 41', 'completed', 1663.00, '2026-02-20 09:26:00', '2026-02-20 09:26:00', '2026-02-20 09:26:00'),
(28, 'CT-0042', 1, 1, 'Customer 42', 'pending', 1909.00, '2026-02-19 04:57:00', '2026-02-19 04:57:00', '2026-02-19 04:57:00'),
(29, 'CT-0043', 1, 1, 'Customer 43', 'completed', 445.00, '2026-02-06 09:04:00', '2026-02-06 09:04:00', '2026-02-06 09:04:00'),
(30, 'CT-0044', 1, 1, 'Customer 44', 'completed', 842.00, '2026-02-20 11:28:00', '2026-02-20 11:28:00', '2026-02-20 11:28:00'),
(31, 'CT-0045', 1, 1, 'Customer 45', 'completed', 1976.00, '2026-02-16 03:20:00', '2026-02-16 03:20:00', '2026-02-16 03:20:00'),
(32, 'CT-0046', 1, 1, 'Customer 46', 'completed', 514.00, '2026-02-13 12:07:00', '2026-02-13 12:07:00', '2026-02-13 12:07:00'),
(33, 'CT-0047', 1, 1, 'Customer 47', 'completed', 388.00, '2026-02-13 02:30:00', '2026-02-13 02:30:00', '2026-02-13 02:30:00'),
(34, 'CT-0048', 1, 1, 'Customer 48', 'completed', 1499.00, '2026-02-14 02:54:00', '2026-02-14 02:54:00', '2026-02-14 02:54:00'),
(35, 'CT-0049', 1, 1, 'Customer 49', 'in_kitchen', 1149.00, '2026-02-07 04:08:00', '2026-02-07 04:08:00', '2026-02-07 04:08:00'),
(36, 'CT-0050', 1, 1, 'Customer 50', 'completed', 1495.00, '2026-02-09 04:01:00', '2026-02-09 04:01:00', '2026-02-09 04:01:00'),
(37, 'CT-0051', 1, 1, 'Customer 51', 'in_kitchen', 1828.00, '2026-02-02 06:47:00', '2026-02-02 06:47:00', '2026-02-02 06:47:00'),
(38, 'CT-0052', 1, 1, 'Customer 52', 'in_kitchen', 1751.00, '2026-02-16 11:13:00', '2026-02-16 11:13:00', '2026-02-16 11:13:00'),
(39, 'CT-0053', 1, 1, 'Customer 53', 'completed', 1281.00, '2026-01-25 11:24:00', '2026-01-25 11:24:00', '2026-01-25 11:24:00'),
(40, 'CT-0054', 1, 1, 'Customer 54', 'in_kitchen', 1370.00, '2026-02-02 07:25:00', '2026-02-02 07:25:00', '2026-02-02 07:25:00'),
(41, 'CT-0055', 1, 1, 'Customer 55', 'pending', 1830.00, '2026-02-07 06:57:00', '2026-02-07 06:57:00', '2026-02-07 06:57:00'),
(42, 'CT-0056', 1, 1, 'Customer 56', 'pending', 1924.00, '2026-01-28 11:16:00', '2026-01-28 11:16:00', '2026-01-28 11:16:00'),
(43, 'CT-0057', 1, 1, 'Customer 57', 'in_kitchen', 927.00, '2026-01-28 07:25:00', '2026-01-28 07:25:00', '2026-01-28 07:25:00'),
(44, 'CT-0058', 1, 1, 'Customer 58', 'pending', 1851.00, '2026-02-12 08:46:00', '2026-02-12 08:46:00', '2026-02-12 08:46:00'),
(45, 'CT-0059', 1, 1, 'Customer 59', 'completed', 318.00, '2026-01-28 12:29:00', '2026-01-28 12:29:00', '2026-01-28 12:29:00'),
(46, 'CT-0060', 1, 1, 'Customer 60', 'pending', 705.00, '2026-02-06 06:09:00', '2026-02-06 06:09:00', '2026-02-06 06:09:00'),
(47, 'CT-0061', 1, 1, 'Customer 61', 'completed', 1606.00, '2026-02-16 11:49:00', '2026-02-16 11:49:00', '2026-02-16 11:49:00'),
(48, 'CT-0062', 1, 1, 'Customer 62', 'completed', 671.00, '2026-02-07 06:32:00', '2026-02-07 06:32:00', '2026-02-07 06:32:00'),
(49, 'CT-0063', 1, 1, 'Customer 63', 'completed', 1741.00, '2026-02-02 06:09:00', '2026-02-02 06:09:00', '2026-02-02 06:09:00'),
(50, 'CT-0064', 1, 1, 'Customer 64', 'completed', 1293.00, '2026-02-22 06:02:00', '2026-02-22 06:02:00', '2026-02-22 06:02:00');

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

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 3, 'auth-token', '9f3fc8018ea8cac112a8f9bf3745a93e4eabf99502f326df6d97e5f6fa21a084', '[\"*\"]', NULL, NULL, '2026-02-23 16:45:30', '2026-02-23 16:45:30'),
(2, 'App\\Models\\User', 2, 'auth-token', 'd221a94cd3c945b06d94a5040922aecedcfa4a053d545195355850483c0bf606', '[\"*\"]', NULL, NULL, '2026-02-23 16:45:47', '2026-02-23 16:45:47'),
(3, 'App\\Models\\User', 3, 'auth-token', 'c51397dcb634bbec4e83b4d860937406b21944a55b52c13553dababf45512d4b', '[\"*\"]', NULL, NULL, '2026-02-23 16:48:45', '2026-02-23 16:48:45'),
(4, 'App\\Models\\User', 5, 'auth-token', '29d886ba5a547b88fa747c90800f960a4790c2e0fcc9203f09e878fdcd29aa1e', '[\"*\"]', NULL, NULL, '2026-02-23 16:52:11', '2026-02-23 16:52:11'),
(5, 'App\\Models\\User', 5, 'auth-token', '1639309393b85fba404d0341c4baf2dac96f386501e395e04740666a9c929c0e', '[\"*\"]', NULL, NULL, '2026-02-23 16:52:55', '2026-02-23 16:52:55'),
(6, 'App\\Models\\User', 5, 'auth-token', '79dc95deb51767af49f96f01313078ee73a3e95db1287ee79adbdadebfe1e6d5', '[\"*\"]', NULL, NULL, '2026-02-23 17:02:29', '2026-02-23 17:02:29'),
(7, 'App\\Models\\User', 5, 'auth-token', '7707389e6a3166578c608eaf37ee7fc0cd227551220f5b4777ae01d4dff5fc5e', '[\"*\"]', NULL, NULL, '2026-02-23 17:06:59', '2026-02-23 17:06:59'),
(8, 'App\\Models\\User', 5, 'auth-token', 'ce710e94fb9883a5d1532c546145986b5e306194408e6b404a2323bcdbfbb505', '[\"*\"]', NULL, NULL, '2026-02-23 17:07:18', '2026-02-23 17:07:18'),
(9, 'App\\Models\\User', 5, 'auth-token', '83b82a7b87d711d74d1dbfd4750d014e513f96a2984478cca20f52cd671cc133', '[\"*\"]', NULL, NULL, '2026-02-23 17:10:04', '2026-02-23 17:10:04'),
(10, 'App\\Models\\User', 5, 'auth-token', '20f9325d50aa2716ce78a2e5a02fc27e874c42ad9affd502924d3036a71ee887', '[\"*\"]', NULL, NULL, '2026-02-23 17:12:29', '2026-02-23 17:12:29'),
(11, 'App\\Models\\User', 5, 'auth-token', '456e6857ff50d2fc26eb899c565426acdea502fe166f5dc0071c7fc6f895250e', '[\"*\"]', NULL, NULL, '2026-02-23 17:12:47', '2026-02-23 17:12:47'),
(12, 'App\\Models\\User', 5, 'auth-token', '136203a5fdb98dbf0628888f304f39d3a5a2dd4d4a97ed59b62e3727ac8835b8', '[\"*\"]', NULL, NULL, '2026-02-23 17:12:55', '2026-02-23 17:12:55'),
(13, 'App\\Models\\User', 3, 'auth-token', 'cd1351b24d53f66cbb6f1e36871ef248cd8d7b9060bb557caf82bc0cb5deb3b1', '[\"*\"]', NULL, NULL, '2026-02-23 17:14:39', '2026-02-23 17:14:39'),
(14, 'App\\Models\\User', 5, 'auth-token', '1b1a0d6b9a1babbe418edc2ff29056ad67e64fc586d3ceef6461005fee1e83d8', '[\"*\"]', NULL, NULL, '2026-02-23 17:17:35', '2026-02-23 17:17:35'),
(15, 'App\\Models\\User', 2, 'auth-token', '29238fefd63bf3eca7f9cdd6059931d4fa4bd55ed46918b817a1be2ff8bc41f5', '[\"*\"]', NULL, NULL, '2026-02-23 17:18:40', '2026-02-23 17:18:40'),
(16, 'App\\Models\\User', 2, 'auth-token', 'ab377e007dd5808a778f32046f55130a49d7e000f5098d15f0bc15af1c01db57', '[\"*\"]', NULL, NULL, '2026-02-23 17:27:48', '2026-02-23 17:27:48'),
(17, 'App\\Models\\User', 5, 'auth-token', '3fe614f4afe3c63781afd8e4b0dfbf20daa9e2815459c515382a054a1958ced3', '[\"*\"]', NULL, NULL, '2026-02-23 17:28:06', '2026-02-23 17:28:06'),
(18, 'App\\Models\\User', 3, 'auth-token', '66cce52259ac447d9afba5cea1a4280bb019949b9513dbac9c1ec5aed0b2a5cb', '[\"*\"]', NULL, NULL, '2026-02-23 17:28:42', '2026-02-23 17:28:42'),
(19, 'App\\Models\\User', 6, 'auth-token', 'd600475313869477832d6a8455227ea8822dbb5bdacae941264f981bf16ba5f3', '[\"*\"]', NULL, NULL, '2026-02-23 17:32:50', '2026-02-23 17:32:50'),
(20, 'App\\Models\\User', 7, 'auth-token', 'f96df3f3bc2670081f6c05fea143f7e0d3893f6ddcef5abe8ee1415696a8b383', '[\"*\"]', NULL, NULL, '2026-02-23 17:35:54', '2026-02-23 17:35:54'),
(21, 'App\\Models\\User', 2, 'auth-token', '4f71d65cbfc8bc66ea8c34df3e812565426e57a3780f81b9a67d9a632bf080ec', '[\"*\"]', NULL, NULL, '2026-02-23 17:43:19', '2026-02-23 17:43:19'),
(22, 'App\\Models\\User', 2, 'auth-token', '6e9f459b419ad46187758a67304309f8fdc609845822db0a0800ba6b2bd471dd', '[\"*\"]', NULL, NULL, '2026-02-23 17:44:40', '2026-02-23 17:44:40'),
(23, 'App\\Models\\User', 2, 'auth-token', '7650d05134dda4d099480e64e36e0e8357ee880563be0f0da45e7172056ffe6a', '[\"*\"]', NULL, NULL, '2026-02-23 17:53:24', '2026-02-23 17:53:24'),
(24, 'App\\Models\\User', 2, 'auth-token', '9da37cfe32bc3ff87945c1e32264338174b49f243731956b81a054889c6b0a8b', '[\"*\"]', NULL, NULL, '2026-02-23 17:54:23', '2026-02-23 17:54:23'),
(25, 'App\\Models\\User', 2, 'auth-token', '20ab6976558553bf9038c322a14480408784983b89bcaf88b8f201f9c83dcf3d', '[\"*\"]', NULL, NULL, '2026-02-23 17:57:53', '2026-02-23 17:57:53'),
(26, 'App\\Models\\User', 2, 'auth-token', '4ee65a0359d8450aada8004080fe2302dbabb444339b34b3ec8df83a1094d099', '[\"*\"]', NULL, NULL, '2026-02-23 18:07:01', '2026-02-23 18:07:01'),
(27, 'App\\Models\\User', 5, 'auth-token', 'c07075c35098266aa88f1852f05f12e8aed700c91a57de78d708c92cc9a8c393', '[\"*\"]', NULL, NULL, '2026-02-23 18:07:24', '2026-02-23 18:07:24'),
(28, 'App\\Models\\User', 3, 'auth-token', '45550a9150600dc170c9c29f4b5ee6e2e1eb47d7a8841cb64e03f031d15fe00d', '[\"*\"]', NULL, NULL, '2026-02-23 18:08:18', '2026-02-23 18:08:18');

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
  `sku` varchar(255) NOT NULL DEFAULT 'SKU-DEFAULT',
  `branch_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `created_at`, `updated_at`, `price`, `stock`, `sku`, `branch_id`) VALUES
(1, 'Yangyeom', 'yangyeom', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-YANGYEOM', 1),
(2, 'Snow Cheese', 'snowcheese', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-SNOWCHEESE', 1),
(3, 'Corndog', 'corndog', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-CORNDOG', 1),
(4, 'Pastries', 'pastries', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-PASTRIES', 1),
(5, 'Ramen', 'ramen', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-RAMEN', 1),
(6, 'Ice Cream', 'icecream', '2026-02-23 14:44:43', '2026-02-23 14:44:43', 0.00, 0, 'SKU-ICECREAM', 1);

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
('FWrFSNAuBwzaoiypyVHf9LgbQ20EsO5NdedKA2R6', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiclZQWWRLNWFyY21XbE04TE1hV1paR2xLWGY2SW1NaUg3RFlwTEtoTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi1sb2dpbiI7czo1OiJyb3V0ZSI7Tjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjtzOjc6InVzZXJfaWQiO2k6MjtzOjk6InVzZXJfcm9sZSI7czo1OiJBRE1JTiI7czo5OiJ1c2VyX25hbWUiO3M6MTA6Ik1haW4gQWRtaW4iO3M6MTM6InJlZGlyZWN0X3BhdGgiO3M6MTI6Ii9hZG1pbi1wYW5lbCI7fQ==', 1771868687),
('QKJ929SxnoksJBApK5jNOGNGH6WoCky2sOccQIBc', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaGtaSEl1NXhpMUh3U1B3MGxmMGxEaUR3aHVKVElGUkk5d1pZd3AxWCI7czo3OiJzdWNjZXNzIjtzOjI0OiJMb2dnZWQgb3V0IHN1Y2Nlc3NmdWxseS4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9nb3V0IjtzOjU6InJvdXRlIjtzOjY6ImxvZ291dCI7fX0=', 1771870112),
('SvwULiLrdJ9TKgh1cm4woCoT1MQSYBx4s8v8aGeI', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiRTQ0bDhncGppM1BWQnNPZU5nVDlLSmtjc2h5T2ZXbmlUYlRMSmNlNiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3N0YWZmLW1hbmFnZW1lbnQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozMzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluLWxvZ2luIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO3M6NzoidXNlcl9pZCI7aTozO3M6OToidXNlcl9yb2xlIjtzOjU6Ik9XTkVSIjtzOjk6InVzZXJfbmFtZSI7czoxNDoiR2FicmllbCBPbmdzaXAiO3M6MTM6InJlZGlyZWN0X3BhdGgiO3M6MTI6Ii9vd25lci1wYW5lbCI7fQ==', 1771870103),
('XIbAMIG2PbzXq9SW9MJbSWQLJQsXJWeRPces1IJp', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZXBYUXFJdFpKQ3d5bWdnVHZ5QzA1dVgwWTlnMnZiTk5CZFk5NUJnWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC8ud2VsbC1rbm93bi9hcHBzcGVjaWZpYy9jb20uY2hyb21lLmRldnRvb2xzLmpzb24iO3M6NToicm91dGUiO047fX0=', 1771870305),
('y2I9yxljPwEitiR7ZdZAnY8y1vr1aGVNoLJwraMh', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiUFpXUEZ0ekdia2pvcE5sTVdsRzhrZWNMbzR6ZFRhSGZMWDlGeUxSeSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3N0YWZmLW1hbmFnZW1lbnQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NTtzOjc6InVzZXJfaWQiO2k6NTtzOjk6InVzZXJfcm9sZSI7czo3OiJNQU5BR0VSIjtzOjk6InVzZXJfbmFtZSI7czoxMzoiQ2hhcmxlcyBVbWJhbCI7czoxMzoicmVkaXJlY3RfcGF0aCI7czoxMToiL21hbmFnZXIvaHIiO30=', 1771870048),
('zeAaiIwA3w2fdKeR1MXlJrjBW0x3RuNnnSJdKfCq', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT1V3M0E4czRmMjlBVjl1aG9nVktxdkV2cWZhM2NreTJRUzdwdEZIeiI7czo3OiJzdWNjZXNzIjtzOjI0OiJMb2dnZWQgb3V0IHN1Y2Nlc3NmdWxseS4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9nb3V0IjtzOjU6InJvdXRlIjtzOjY6ImxvZ291dCI7fX0=', 1771869281);

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
(2, 5, NULL, 'staff-documents/5/government_id.png', 'staff-documents/5/psa_birth_certificate.png', 'staff-documents/5/nbi_clearance.png', 'staff-documents/5/police_clearance.png', 'staff-documents/5/medical_certificate.png', 'staff-documents/5/drug_test_result.png', 'staff-documents/5/sss_id.png', 'staff-documents/5/philhealth_id.png', 'staff-documents/5/pagibig_mdf.png', 'staff-documents/5/tin_id.png', 'staff-documents/5/diploma_transcript.png', '2026-02-23 16:51:07', '2026-02-23 16:51:07'),
(3, 6, NULL, 'staff-documents/6/government_id.png', 'staff-documents/6/psa_birth_certificate.png', 'staff-documents/6/nbi_clearance.png', 'staff-documents/6/police_clearance.png', 'staff-documents/6/medical_certificate.png', 'staff-documents/6/drug_test_result.png', 'staff-documents/6/sss_id.png', 'staff-documents/6/philhealth_id.png', 'staff-documents/6/pagibig_mdf.png', 'staff-documents/6/tin_id.png', 'staff-documents/6/diploma_transcript.png', '2026-02-23 17:32:33', '2026-02-23 17:32:33'),
(4, 7, NULL, 'staff-documents/7/government_id.png', 'staff-documents/7/psa_birth_certificate.png', 'staff-documents/7/nbi_clearance.png', 'staff-documents/7/police_clearance.png', 'staff-documents/7/medical_certificate.png', 'staff-documents/7/drug_test_result.png', 'staff-documents/7/sss_id.png', 'staff-documents/7/philhealth_id.png', 'staff-documents/7/pagibig_mdf.png', 'staff-documents/7/tin_id.png', 'staff-documents/7/diploma_transcript.png', '2026-02-23 17:35:39', '2026-02-23 17:35:39');

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
  `department` enum('HR','FINANCE','INVENTORY','LOGISTICS','CASHIER') DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `address` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `name`, `password`, `email_verified_at`, `role`, `branch_id`, `department`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`, `is_active`, `address`, `avatar_url`, `phone_number`) VALUES
(2, 'admin_main@example.com', 'admin_main', 'Main Admin', NULL, '$2y$12$1o6PwCQnVSntMJaAbnpKQ.V4i2a8pyrZvSv71F1ex//OerR3EbJu.', '2026-02-23 14:44:43', 'ADMIN', NULL, 'HR', NULL, '2026-02-23 14:44:43', '2026-02-23 14:44:43', NULL, 0, 1, 'Admin HQ', NULL, '09171234567'),
(3, 'gab@test.com', 'gab', 'Gabriel Ongsip', NULL, '$2y$12$/i8o7LgwP/YZKZekIB/sgOlOx6vl1lJrb0K1Zx.k4qZcMko8gCFXK', NULL, 'OWNER', NULL, NULL, NULL, '2026-02-23 16:06:22', '2026-02-23 16:07:56', NULL, 0, 1, '', NULL, '09081717616'),
(5, 'charles@test.com', 'charles', 'Charles Umbal', NULL, '$2y$12$Xrrcu0yQq5SlBcL6UaLGdeSerNCio2P8L1g9rVQuWomMS3Nbrb4Cm', NULL, 'MANAGER', 3, 'HR', NULL, '2026-02-23 16:51:07', '2026-02-23 16:52:48', NULL, 0, 1, '14A', NULL, '09081717717'),
(6, 'raf@test.com', 'rafa', 'Rafael Cuneta', NULL, '$2y$12$LuMd.W4tV3dRVq3TIxN.SelRyDCaarfmWnwXF9OJ8cYU8cLKAv0F2', NULL, 'STAFF', 3, 'INVENTORY', NULL, '2026-02-23 17:32:33', '2026-02-23 17:33:18', NULL, 0, 1, '123Av', NULL, '09917758052'),
(7, 'vince@test.com', 'vince', 'Vince Bido', NULL, '$2y$12$CfWXOnueNcZNZYOpEDWORO9gn3tGBIxmmbaZhE794zrxB/DT49fp2', NULL, 'MANAGER', 5, 'FINANCE', NULL, '2026-02-23 17:35:39', '2026-02-23 17:36:05', NULL, 0, 1, '123', NULL, '09871513425');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
