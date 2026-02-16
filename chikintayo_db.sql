-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 12:00 PM
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
(1, 'DASMA', 'Dasmariñas Branch', '4606 Mangubat Ave. Zone 4', 1, '2026-02-06 02:45:41', '2026-02-06 02:45:41'),
(2, 'PAMPANGA', 'Pampanga Branch', 'Sm City Pampanga', 1, '2026-02-06 02:45:41', '2026-02-06 02:45:41'),
(3, 'QUEZON', 'Quezon City Branch', 'Robinsons Magnolia Residences', 1, '2026-02-06 02:45:41', '2026-02-06 02:45:41');

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

--
-- Dumping data for table `customer_accounts`
--

INSERT INTO `customer_accounts` (`id`, `user_id`, `email`, `full_name`, `phone_number`, `address`, `city`, `province`, `postal_code`, `total_comments`, `total_ratings`, `last_activity_at`, `status`, `created_at`, `updated_at`) VALUES
(3, 13, 'umbal.christiancharles@ncst.edu.ph', 'umbal', NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-02-06 03:29:22', 'active', '2026-02-06 03:29:22', '2026-02-06 03:29:22');

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
-- Table structure for table `feedback_complaints`
--

CREATE TABLE `feedback_complaints` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('FEEDBACK','COMPLAINT','BUG_REPORT','FEATURE_REQUEST') NOT NULL DEFAULT 'FEEDBACK',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('PENDING','IN_PROGRESS','RESOLVED','CLOSED') NOT NULL DEFAULT 'PENDING',
  `priority` enum('LOW','MEDIUM','HIGH','URGENT') NOT NULL DEFAULT 'MEDIUM',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_replies`
--

CREATE TABLE `feedback_replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `feedback_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_admin_reply` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(27, '2026_02_06_220000_add_missing_columns_to_users_table', 1),
(29, '2026_02_06_230000_fix_add_parent_comment_id_to_product_comments', 2),
(30, '2026_02_16_140000_add_department_to_users_table', 3),
(31, '2026_02_16_150000_create_user_departments_table', 4),
(32, '2026_02_16_150100_create_system_announcements_table', 4),
(33, '2026_02_16_150200_create_terms_and_conditions_table', 4),
(34, '2026_02_16_150300_create_feedback_complaints_table', 4),
(35, '2026_02_16_150109_create_staff_table', 5),
(36, '2026_02_16_000000_add_department_to_users_table', 6);

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
(1, 'CT-0015', 1, 1, 'Customer 15', 'completed', 1110.00, '2026-01-29 05:28:00', '2026-01-29 05:28:00', '2026-01-29 05:28:00'),
(2, 'CT-0016', 1, 1, 'Customer 16', 'completed', 628.00, '2026-01-20 01:55:00', '2026-01-20 01:55:00', '2026-01-20 01:55:00'),
(3, 'CT-0017', 1, 1, 'Customer 17', 'completed', 418.00, '2026-02-01 11:18:00', '2026-02-01 11:18:00', '2026-02-01 11:18:00'),
(4, 'CT-0018', 1, 1, 'Customer 18', 'completed', 702.00, '2026-01-22 11:56:00', '2026-01-22 11:56:00', '2026-01-22 11:56:00'),
(5, 'CT-0019', 1, 1, 'Customer 19', 'in_kitchen', 1297.00, '2026-01-31 04:00:00', '2026-01-31 04:00:00', '2026-01-31 04:00:00'),
(6, 'CT-0020', 1, 1, 'Customer 20', 'in_kitchen', 936.00, '2026-01-31 06:49:00', '2026-01-31 06:49:00', '2026-01-31 06:49:00'),
(7, 'CT-0021', 1, 1, 'Customer 21', 'completed', 565.00, '2026-02-04 05:10:00', '2026-02-04 05:10:00', '2026-02-04 05:10:00'),
(8, 'CT-0022', 1, 1, 'Customer 22', 'completed', 1034.00, '2026-01-16 07:54:00', '2026-01-16 07:54:00', '2026-01-16 07:54:00'),
(9, 'CT-0023', 1, 1, 'Customer 23', 'in_kitchen', 691.00, '2026-01-29 12:41:00', '2026-01-29 12:41:00', '2026-01-29 12:41:00'),
(10, 'CT-0024', 1, 1, 'Customer 24', 'completed', 688.00, '2026-01-29 09:26:00', '2026-01-29 09:26:00', '2026-01-29 09:26:00'),
(11, 'CT-0025', 1, 1, 'Customer 25', 'completed', 967.00, '2026-01-22 10:00:00', '2026-01-22 10:00:00', '2026-01-22 10:00:00'),
(12, 'CT-0026', 1, 1, 'Customer 26', 'completed', 315.00, '2026-01-25 05:08:00', '2026-01-25 05:08:00', '2026-01-25 05:08:00'),
(13, 'CT-0027', 1, 1, 'Customer 27', 'completed', 1585.00, '2026-01-23 04:00:00', '2026-01-23 04:00:00', '2026-01-23 04:00:00'),
(14, 'CT-0028', 1, 1, 'Customer 28', 'completed', 1510.00, '2026-01-28 12:37:00', '2026-01-28 12:37:00', '2026-01-28 12:37:00'),
(15, 'CT-0029', 1, 1, 'Customer 29', 'in_kitchen', 1300.00, '2026-01-22 04:34:00', '2026-01-22 04:34:00', '2026-01-22 04:34:00'),
(16, 'CT-0030', 1, 1, 'Customer 30', 'completed', 817.00, '2026-02-05 08:38:00', '2026-02-05 08:38:00', '2026-02-05 08:38:00'),
(17, 'CT-0031', 1, 1, 'Customer 31', 'pending', 1182.00, '2026-01-18 11:22:00', '2026-01-18 11:22:00', '2026-01-18 11:22:00'),
(18, 'CT-0032', 1, 1, 'Customer 32', 'completed', 1072.00, '2026-02-01 01:15:00', '2026-02-01 01:15:00', '2026-02-01 01:15:00'),
(19, 'CT-0033', 1, 1, 'Customer 33', 'completed', 1187.00, '2026-02-12 04:03:00', '2026-02-12 04:03:00', '2026-02-12 04:03:00'),
(20, 'CT-0034', 1, 1, 'Customer 34', 'completed', 635.00, '2026-01-30 04:01:00', '2026-01-30 04:01:00', '2026-01-30 04:01:00'),
(21, 'CT-0035', 1, 1, 'Customer 35', 'completed', 1409.00, '2026-01-30 03:05:00', '2026-01-30 03:05:00', '2026-01-30 03:05:00'),
(22, 'CT-0036', 1, 1, 'Customer 36', 'pending', 710.00, '2026-02-09 04:55:00', '2026-02-09 04:55:00', '2026-02-09 04:55:00'),
(23, 'CT-0037', 1, 1, 'Customer 37', 'completed', 1659.00, '2026-01-27 08:01:00', '2026-01-27 08:01:00', '2026-01-27 08:01:00'),
(24, 'CT-0038', 1, 1, 'Customer 38', 'completed', 1604.00, '2026-01-24 04:15:00', '2026-01-24 04:15:00', '2026-01-24 04:15:00'),
(25, 'CT-0039', 1, 1, 'Customer 39', 'completed', 1421.00, '2026-01-16 00:41:00', '2026-01-16 00:41:00', '2026-01-16 00:41:00'),
(26, 'CT-0040', 1, 1, 'Customer 40', 'completed', 1260.00, '2026-01-17 10:14:00', '2026-01-17 10:14:00', '2026-01-17 10:14:00'),
(27, 'CT-0041', 1, 1, 'Customer 41', 'completed', 1971.00, '2026-01-19 09:31:00', '2026-01-19 09:31:00', '2026-01-19 09:31:00'),
(28, 'CT-0042', 1, 1, 'Customer 42', 'completed', 642.00, '2026-02-04 03:27:00', '2026-02-04 03:27:00', '2026-02-04 03:27:00'),
(29, 'CT-0043', 1, 1, 'Customer 43', 'completed', 744.00, '2026-01-26 08:54:00', '2026-01-26 08:54:00', '2026-01-26 08:54:00'),
(30, 'CT-0044', 1, 1, 'Customer 44', 'completed', 1264.00, '2026-02-08 07:03:00', '2026-02-08 07:03:00', '2026-02-08 07:03:00'),
(31, 'CT-0045', 1, 1, 'Customer 45', 'completed', 1776.00, '2026-02-02 06:51:00', '2026-02-02 06:51:00', '2026-02-02 06:51:00'),
(32, 'CT-0046', 1, 1, 'Customer 46', 'completed', 1803.00, '2026-01-16 11:35:00', '2026-01-16 11:35:00', '2026-01-16 11:35:00'),
(33, 'CT-0047', 1, 1, 'Customer 47', 'completed', 1762.00, '2026-01-19 08:12:00', '2026-01-19 08:12:00', '2026-01-19 08:12:00'),
(34, 'CT-0048', 1, 1, 'Customer 48', 'completed', 837.00, '2026-02-07 05:12:00', '2026-02-07 05:12:00', '2026-02-07 05:12:00'),
(35, 'CT-0049', 1, 1, 'Customer 49', 'completed', 1438.00, '2026-01-31 10:52:00', '2026-01-31 10:52:00', '2026-01-31 10:52:00'),
(36, 'CT-0050', 1, 1, 'Customer 50', 'completed', 886.00, '2026-02-07 04:40:00', '2026-02-07 04:40:00', '2026-02-07 04:40:00'),
(37, 'CT-0051', 1, 1, 'Customer 51', 'completed', 1330.00, '2026-01-22 07:21:00', '2026-01-22 07:21:00', '2026-01-22 07:21:00'),
(38, 'CT-0052', 1, 1, 'Customer 52', 'completed', 1572.00, '2026-01-27 03:10:00', '2026-01-27 03:10:00', '2026-01-27 03:10:00'),
(39, 'CT-0053', 1, 1, 'Customer 53', 'in_kitchen', 461.00, '2026-01-18 03:38:00', '2026-01-18 03:38:00', '2026-01-18 03:38:00'),
(40, 'CT-0054', 1, 1, 'Customer 54', 'completed', 1745.00, '2026-02-09 11:41:00', '2026-02-09 11:41:00', '2026-02-09 11:41:00'),
(41, 'CT-0055', 1, 1, 'Customer 55', 'completed', 826.00, '2026-01-31 06:05:00', '2026-01-31 06:05:00', '2026-01-31 06:05:00'),
(42, 'CT-0056', 1, 1, 'Customer 56', 'in_kitchen', 596.00, '2026-02-05 09:32:00', '2026-02-05 09:32:00', '2026-02-05 09:32:00'),
(43, 'CT-0057', 1, 1, 'Customer 57', 'completed', 529.00, '2026-01-21 10:23:00', '2026-01-21 10:23:00', '2026-01-21 10:23:00'),
(44, 'CT-0058', 1, 1, 'Customer 58', 'completed', 1704.00, '2026-01-22 08:12:00', '2026-01-22 08:12:00', '2026-01-22 08:12:00'),
(45, 'CT-0059', 1, 1, 'Customer 59', 'pending', 1270.00, '2026-01-30 12:51:00', '2026-01-30 12:51:00', '2026-01-30 12:51:00'),
(46, 'CT-0060', 1, 1, 'Customer 60', 'completed', 1688.00, '2026-02-07 04:50:00', '2026-02-07 04:50:00', '2026-02-07 04:50:00'),
(47, 'CT-0061', 1, 1, 'Customer 61', 'completed', 1511.00, '2026-01-23 04:49:00', '2026-01-23 04:49:00', '2026-01-23 04:49:00'),
(48, 'CT-0062', 1, 1, 'Customer 62', 'completed', 1592.00, '2026-02-05 02:40:00', '2026-02-05 02:40:00', '2026-02-05 02:40:00'),
(49, 'CT-0063', 1, 1, 'Customer 63', 'in_kitchen', 357.00, '2026-02-02 10:39:00', '2026-02-02 10:39:00', '2026-02-02 10:39:00'),
(50, 'CT-0064', 1, 1, 'Customer 64', 'completed', 1245.00, '2026-01-23 09:31:00', '2026-01-23 09:31:00', '2026-01-23 09:31:00');

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
(1, 'App\\Models\\User', 19, 'auth_token', '22d1a2e29fa7cde2c276f4d9dc2f7ec688836ebfeb6f58fa56dd8505f8950a9c', '[\"*\"]', NULL, NULL, '2026-02-16 06:54:18', '2026-02-16 06:54:18'),
(2, 'App\\Models\\User', 19, 'auth_token', '52f581a401e2672a57a6342ce9e8ab83eb8df702968e3dea0e665fcc7fce7428', '[\"*\"]', NULL, NULL, '2026-02-16 06:58:08', '2026-02-16 06:58:08'),
(3, 'App\\Models\\User', 19, 'auth_token', 'a6b86bdeba01b6bc7496ea14bf392636c28992d46e65e5bd1d726fc8a99b8497', '[\"*\"]', NULL, NULL, '2026-02-16 07:02:47', '2026-02-16 07:02:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Yangyeom', 'yangyeom', '2026-02-13 13:21:44', '2026-02-13 13:21:44'),
(2, 'Snow Cheese', 'snowcheese', '2026-02-13 13:21:44', '2026-02-13 13:21:44'),
(3, 'Corndog', 'corndog', '2026-02-13 13:21:44', '2026-02-13 13:21:44'),
(4, 'Pastries', 'pastries', '2026-02-13 13:21:44', '2026-02-13 13:21:44'),
(5, 'Ramen', 'ramen', '2026-02-13 13:21:44', '2026-02-13 13:21:44'),
(6, 'Ice Cream', 'icecream', '2026-02-13 13:21:44', '2026-02-13 13:21:44');

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
  `rating` tinyint(3) UNSIGNED DEFAULT 5,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `parent_comment_id`, `author`, `text`, `rating`, `ip_address`, `created_at`, `updated_at`) VALUES
(7, 2, NULL, NULL, 'umbal.christiancharles@ncst.edu.ph', 'Lasang kalawang', 5, '127.0.0.1', '2026-02-16 05:16:49', '2026-02-16 05:16:49');

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
('4rB5eiJBY9SFf70fX0Nwe9Z1VXhy2wEXd7Wf1XLD', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.7705', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQVZFbWNJUm9HdFBkd09uZlM0NktySlNXOEZTQnhVdnBkSmFhRktQSSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771235009),
('DY59OlMzY5JkkVv06nZdTHDOvOmU4ZLXmNd9jC0Z', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVFZlaExnR21TUkx3THp0T3RYalVjRnlDTHZadXhBWXZJSVlBUmtBZCI7czo3OiJzdWNjZXNzIjtzOjI0OiJMb2dnZWQgb3V0IHN1Y2Nlc3NmdWxseS4iO3M6NjoiX2ZsYXNoIjthOjI6e3M6MzoibmV3IjthOjA6e31zOjM6Im9sZCI7YToxOntpOjA7czo3OiJzdWNjZXNzIjt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbG9nb3V0IjtzOjU6InJvdXRlIjtzOjY6ImxvZ291dCI7fX0=', 1771235841),
('NkIy2lK9gosPmvLC4IGkyPCHXs79kczoPxAmrnPs', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.7705', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUUVsbkZmRHpNSFJGUGJ5NzZqb2NiMzZxbEF0clI0VE51cmR4WjEwOSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771234948),
('OTH31yn0DrwJP6FyVa1jf0qB5Um0XMdqih0PxxIk', 21, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiRENEM1Z0V0F3TzNjeVBEZm1aRUllYnJnZ3I5Z2FMUk9YYVRNeEVzRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9vd25lci1wYW5lbCI7czo1OiJyb3V0ZSI7Tjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MjE7czo3OiJ1c2VyX2lkIjtpOjIxO3M6OToidXNlcl9yb2xlIjtzOjU6Ik9XTkVSIjtzOjk6InVzZXJfbmFtZSI7czo0OiJHQUJCIjt9', 1771239611),
('zczj0tvkTCIZFuc8FGKpewRmFVpbl2peMRapSe5F', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.7705', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTjNQUUdpSk5zZ3FsSXcxVnEwd0hOd1ZsTVo3Wm4wd0NQcXk2OXROQiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czoyMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1771234966);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `position` varchar(255) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(10, 21, 'staff-documents/21/resume.png', 'staff-documents/21/government_id.png', 'staff-documents/21/psa_birth_certificate.png', 'staff-documents/21/nbi_clearance.png', 'staff-documents/21/police_clearance.png', 'staff-documents/21/medical_certificate.png', 'staff-documents/21/drug_test_result.png', 'staff-documents/21/sss_id.png', 'staff-documents/21/philhealth_id.png', 'staff-documents/21/pagibig_mdf.png', 'staff-documents/21/tin_id.png', 'staff-documents/21/diploma_transcript.png', '2026-02-16 08:34:41', '2026-02-16 08:34:41');

-- --------------------------------------------------------

--
-- Table structure for table `system_announcements`
--

CREATE TABLE `system_announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('MAINTENANCE','INFO','WARNING','UPDATE') NOT NULL DEFAULT 'INFO',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms_and_conditions`
--

CREATE TABLE `terms_and_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(20) NOT NULL,
  `content` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `effective_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'STAFF',
  `department` varchar(50) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `password`, `email_verified_at`, `role`, `department`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`) VALUES
(13, 'umbal.christiancharles@ncst.edu.ph', 'umbal', NULL, '$2y$12$O9zdvoAdRzrxUtHCUY8ah.ghXCdMqwfzT.6CeDwtxnWZre0Dt6C6u', '2026-02-06 03:29:22', 'customer', NULL, NULL, NULL, NULL, NULL, 1, NULL, '2026-02-06 03:29:22', '2026-02-06 03:29:22', NULL, 0),
(18, 'admin@chikintayo.com', 'admin', 'System Administrator', '$2y$12$5MhrY4pfEnAbJwc7qYY2ru/lH3qCvuLY7Bo0HhuZQilp/FXxt.s1u', '2026-02-16 06:43:06', 'ADMIN', NULL, NULL, '/storage/avatars/avatar_18_1771228406.jpg', NULL, NULL, 1, NULL, '2026-02-16 06:43:06', '2026-02-16 06:43:06', NULL, 0),
(21, 'owner@gmail.com', 'owner', 'GABB', '$2y$12$IXz/PH6AhThR3A62ee.AfOYHtfrRh/GR5fL.md0s4rUpPy4yZK646', NULL, 'OWNER', NULL, NULL, NULL, '09295426078', 'asd', 1, NULL, '2026-02-16 08:34:41', '2026-02-16 09:57:11', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_departments`
--

CREATE TABLE `user_departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_terms_acceptance`
--

CREATE TABLE `user_terms_acceptance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `terms_id` bigint(20) UNSIGNED NOT NULL,
  `accepted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Indexes for table `feedback_complaints`
--
ALTER TABLE `feedback_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_complaints_assigned_to_foreign` (`assigned_to`),
  ADD KEY `feedback_complaints_user_id_index` (`user_id`),
  ADD KEY `feedback_complaints_status_index` (`status`),
  ADD KEY `feedback_complaints_priority_index` (`priority`),
  ADD KEY `feedback_complaints_type_index` (`type`);

--
-- Indexes for table `feedback_replies`
--
ALTER TABLE `feedback_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_replies_user_id_foreign` (`user_id`),
  ADD KEY `feedback_replies_feedback_id_index` (`feedback_id`);

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
  ADD UNIQUE KEY `products_slug_unique` (`slug`);

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
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_email_unique` (`email`),
  ADD KEY `staff_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_documents_user_id_index` (`user_id`);

--
-- Indexes for table `system_announcements`
--
ALTER TABLE `system_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_announcements_admin_id_foreign` (`admin_id`),
  ADD KEY `system_announcements_is_active_index` (`is_active`),
  ADD KEY `system_announcements_type_index` (`type`);

--
-- Indexes for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `terms_and_conditions_created_by_foreign` (`created_by`),
  ADD KEY `terms_and_conditions_is_active_index` (`is_active`),
  ADD KEY `terms_and_conditions_version_index` (`version`);

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
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_department_index` (`department`);

--
-- Indexes for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_departments_user_id_department_unique` (`user_id`,`department`),
  ADD KEY `user_departments_user_id_index` (`user_id`),
  ADD KEY `user_departments_department_index` (`department`);

--
-- Indexes for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_terms_acceptance_user_id_terms_id_unique` (`user_id`,`terms_id`),
  ADD KEY `user_terms_acceptance_terms_id_foreign` (`terms_id`),
  ADD KEY `user_terms_acceptance_user_id_index` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `feedback_complaints`
--
ALTER TABLE `feedback_complaints`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_replies`
--
ALTER TABLE `feedback_replies`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_announcements`
--
ALTER TABLE `system_announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `feedback_complaints`
--
ALTER TABLE `feedback_complaints`
  ADD CONSTRAINT `feedback_complaints_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_complaints_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_replies`
--
ALTER TABLE `feedback_replies`
  ADD CONSTRAINT `feedback_replies_feedback_id_foreign` FOREIGN KEY (`feedback_id`) REFERENCES `feedback_complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD CONSTRAINT `product_comments_parent_comment_id_foreign` FOREIGN KEY (`parent_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_announcements`
--
ALTER TABLE `system_announcements`
  ADD CONSTRAINT `system_announcements_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `terms_and_conditions`
--
ALTER TABLE `terms_and_conditions`
  ADD CONSTRAINT `terms_and_conditions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_departments`
--
ALTER TABLE `user_departments`
  ADD CONSTRAINT `user_departments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_terms_acceptance`
--
ALTER TABLE `user_terms_acceptance`
  ADD CONSTRAINT `user_terms_acceptance_terms_id_foreign` FOREIGN KEY (`terms_id`) REFERENCES `terms_and_conditions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_terms_acceptance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
