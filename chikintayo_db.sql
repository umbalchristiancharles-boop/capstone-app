-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 12:10 PM
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
-- Table structure for table `attendance_settings`
--

CREATE TABLE `attendance_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `early_clockout_override` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance_settings`
--

INSERT INTO `attendance_settings` (`id`, `branch_id`, `early_clockout_override`, `created_at`, `updated_at`) VALUES
(12, 11, 0, '2026-03-09 06:05:07', '2026-03-09 06:05:07');

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
(11, 'BR886318', 'Tagaytay City Branch', 'Tagaytay', 1, '2026-03-09 06:01:42', '2026-03-09 06:01:42');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-user_token_SSoopgAEr9zdbm3thOpogIc9Djxr9FI8xw0nm6tm7KZyW65UtzuZPJfdsaho', 'i:29;', 1775332852);

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
(2, 29, 'umbal.christiancharles@ncst.edu.ph', 'umballs', NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-03-05 20:00:52', 'active', '2026-03-05 20:00:52', '2026-03-05 20:00:52');

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
(35, '2026_02_17_215000_add_full_name_to_users_table', 1),
(36, '2026_02_20_000000_create_attendance_settings_table', 2),
(37, '2026_03_06_043915_add_payment_fields_to_orders_table', 3),
(38, '2026_03_06_043929_create_order_items_table', 3),
(39, '2026_03_10_000000_add_is_active_to_products_table', 4),
(40, '2026_03_15_000000_add_min_stock_to_products_table', 5);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_code` varchar(255) NOT NULL,
  `owner_id` bigint(20) UNSIGNED NOT NULL,
  `cashier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_kitchen','completed','cancelled') NOT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ordered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `owner_id`, `cashier_id`, `branch_id`, `customer_name`, `status`, `grand_total`, `amount_paid`, `change_amount`, `ordered_at`, `created_at`, `updated_at`) VALUES
(51, 'CT-0001', 28, 28, 1, 'Walk-in', 'completed', 200.00, 500.00, 300.00, '2026-03-05 20:49:08', '2026-03-05 20:49:08', '2026-03-05 20:49:08'),
(52, 'CT-0002', 28, 28, 1, 'Walk-in', 'completed', 2750.00, 3000.00, 250.00, '2026-03-05 20:59:43', '2026-03-05 20:59:43', '2026-03-05 20:59:43'),
(53, 'CT-0003', 28, 28, 1, 'Walk-in', 'completed', 250.00, 260.00, 10.00, '2026-03-05 21:05:23', '2026-03-05 21:05:23', '2026-03-05 21:05:23'),
(54, 'CT-0004', 28, 28, 2, 'Walk-in', 'completed', 4000.00, 5000.00, 1000.00, '2026-03-05 21:07:17', '2026-03-05 21:07:17', '2026-03-05 21:07:17'),
(55, 'CT-0005', 28, 28, 2, 'Walk-in', 'completed', 2000.00, 2000.00, 0.00, '2026-03-05 21:42:13', '2026-03-05 21:42:13', '2026-03-05 21:42:13'),
(56, 'CT-0006', 28, 28, 3, 'Walk-in', 'completed', 12000.00, 20000.00, 8000.00, '2026-03-05 21:42:53', '2026-03-05 21:42:53', '2026-03-05 21:42:53'),
(57, 'CT-0007', 28, 28, 1, 'Walk-in', 'completed', 1000.00, 1200.00, 200.00, '2026-03-06 11:43:59', '2026-03-06 11:43:59', '2026-03-06 11:43:59'),
(58, 'CT-0008', 28, 28, 3, 'Walk-in', 'completed', 3000.00, 3000.00, 0.00, '2026-03-06 11:45:16', '2026-03-06 11:45:16', '2026-03-06 11:45:16'),
(59, 'CT-0009', 28, 28, 9, 'Walk-in', 'completed', 300.00, 500.00, 200.00, '2026-03-07 08:05:06', '2026-03-07 08:05:06', '2026-03-07 08:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(28, 'App\\Models\\User', 3, 'auth-token', '45550a9150600dc170c9c29f4b5ee6e2e1eb47d7a8841cb64e03f031d15fe00d', '[\"*\"]', NULL, NULL, '2026-02-23 18:08:18', '2026-02-23 18:08:18'),
(29, 'App\\Models\\User', 3, 'auth-token', '39ad54315f0af3647167095c999959ab17c9777e63d18ed939f04bed5087fb0f', '[\"*\"]', NULL, NULL, '2026-02-24 04:16:18', '2026-02-24 04:16:18'),
(30, 'App\\Models\\User', 2, 'auth-token', '910c09c8704290c5928c5712875dc5ac0e9e57856ee758bd9b79d7c3e49ed6b1', '[\"*\"]', NULL, NULL, '2026-02-24 04:16:38', '2026-02-24 04:16:38'),
(31, 'App\\Models\\User', 2, 'auth-token', 'f9b6a41d560522eac668d52ef71ec19935a1f6b8e1d871ff3c85ba41046022f2', '[\"*\"]', NULL, NULL, '2026-02-24 04:23:40', '2026-02-24 04:23:40'),
(32, 'App\\Models\\User', 2, 'auth-token', 'bea0ef5d6a89908276ff7c1d218c6c16cf75f46d92bf2157bfd298d641f935a4', '[\"*\"]', NULL, NULL, '2026-02-24 04:57:59', '2026-02-24 04:57:59'),
(33, 'App\\Models\\User', 2, 'auth-token', '7cf48e1abf68dea522c2c19ca7a1dcb7066f3a7419fa00292b9be04f77776dd0', '[\"*\"]', NULL, NULL, '2026-02-24 05:02:31', '2026-02-24 05:02:31'),
(34, 'App\\Models\\User', 3, 'auth-token', '99deebd99d16f21e79f247c601fdee446b38018c1017f2989a6203b5a05dc64c', '[\"*\"]', NULL, NULL, '2026-02-24 05:03:29', '2026-02-24 05:03:29'),
(35, 'App\\Models\\User', 2, 'auth-token', '6741a0dec5d71aa54017dac971b7a8ee976cbc89274235d3f833241cf0fa30cd', '[\"*\"]', NULL, NULL, '2026-02-24 06:21:50', '2026-02-24 06:21:50'),
(36, 'App\\Models\\User', 2, 'auth-token', '6bcac87c1a669d4eb8f10cda85261a8dd349d7bff10afd1b784dbf2d4f83ecb9', '[\"*\"]', NULL, NULL, '2026-02-24 06:25:29', '2026-02-24 06:25:29'),
(37, 'App\\Models\\User', 2, 'auth-token', 'f1c4fc36f9633f88d12b9d0ae9a52eaaa8273664b065174912021b3d769a7655', '[\"*\"]', NULL, NULL, '2026-02-24 06:27:21', '2026-02-24 06:27:21'),
(38, 'App\\Models\\User', 2, 'auth-token', '0d81f5244adf091da5095de86a6d7980de0bf979bbf446f7c46313aa58ad1f61', '[\"*\"]', NULL, NULL, '2026-02-24 06:28:32', '2026-02-24 06:28:32'),
(39, 'App\\Models\\User', 2, 'auth-token', 'f5ecc9a432d9d74cdd783cef74a2532c0ad32f80cd0b84996a4f2e8f8cbad1aa', '[\"*\"]', NULL, NULL, '2026-02-24 07:10:43', '2026-02-24 07:10:43'),
(40, 'App\\Models\\User', 2, 'auth-token', '6ca0d4bed88e6b1d643feae18ae01eff01de6da879e48b9348de2286a8aa3db4', '[\"*\"]', NULL, NULL, '2026-02-24 07:13:06', '2026-02-24 07:13:06'),
(41, 'App\\Models\\User', 3, 'auth-token', '7d942adf2cc577ed3174ebe4cb0e5f97c783c3dc9bb579ad69a8bd897e9c64cf', '[\"*\"]', NULL, NULL, '2026-02-24 07:15:40', '2026-02-24 07:15:40'),
(42, 'App\\Models\\User', 3, 'auth-token', 'c8879e86994916acd428919ebe437f39bf8da48de756f3b0f75375892543c7f0', '[\"*\"]', NULL, NULL, '2026-02-24 07:19:13', '2026-02-24 07:19:13'),
(43, 'App\\Models\\User', 3, 'auth-token', 'a74322fbf88db7dd18b4095c96cfc5a20303f25f8ebb4cf4ce19fc144e24a057', '[\"*\"]', NULL, NULL, '2026-02-24 07:28:14', '2026-02-24 07:28:14'),
(44, 'App\\Models\\User', 3, 'auth-token', '8adc99e6ab9a680735a57c0d951033fa601eaba8d0b38546506c86f9215fc223', '[\"*\"]', NULL, NULL, '2026-02-24 07:33:44', '2026-02-24 07:33:44'),
(45, 'App\\Models\\User', 2, 'auth-token', '516840dcc34510ab3a5f97b497ce176c14b73b239330ba457841de0f479227bb', '[\"*\"]', NULL, NULL, '2026-02-24 08:44:53', '2026-02-24 08:44:53'),
(46, 'App\\Models\\User', 3, 'auth-token', 'bb8cc782f686ef59c54d89745eac79832e116dde1530cfdafbe9d760daf5ee62', '[\"*\"]', NULL, NULL, '2026-02-24 08:45:17', '2026-02-24 08:45:17'),
(47, 'App\\Models\\User', 2, 'auth-token', 'be855a7d2097455a9b9ee3f1b7a6ed104c12d23a72c8bccc14b6c703049f20b7', '[\"*\"]', NULL, NULL, '2026-02-24 08:46:43', '2026-02-24 08:46:43'),
(48, 'App\\Models\\User', 2, 'auth-token', 'bac5bec793b1f7f2d17d2ed2145cdd2ea9970f4898c779c7e791de97f99bd0d1', '[\"*\"]', NULL, NULL, '2026-02-24 08:48:29', '2026-02-24 08:48:29'),
(49, 'App\\Models\\User', 5, 'auth-token', '6a12310616bac9aa47899b26bdddb8623824acfaba655b1e6f4482ec690b5420', '[\"*\"]', NULL, NULL, '2026-02-24 08:49:13', '2026-02-24 08:49:13'),
(50, 'App\\Models\\User', 5, 'auth-token', '25af5850f6ff3a666e4690fbc7f443838c20d76b6f8009d7ef173740174fc5f0', '[\"*\"]', NULL, NULL, '2026-02-24 08:53:39', '2026-02-24 08:53:39'),
(51, 'App\\Models\\User', 2, 'auth-token', '66bef3da6210827c63f0c87f69d3e36e26343e21077e7542cb90af7ed47df6c4', '[\"*\"]', NULL, NULL, '2026-02-24 08:53:47', '2026-02-24 08:53:47'),
(52, 'App\\Models\\User', 2, 'auth-token', '47fe6c57da53e3f2f28c0d5fb1121be1673721b39d37f186e5303573b7163364', '[\"*\"]', NULL, NULL, '2026-02-24 08:57:42', '2026-02-24 08:57:42'),
(53, 'App\\Models\\User', 8, 'auth-token', '7143c095e75318a7138b5747e6ec6c5bbe0bd7a985530f18248c103bbe25b09f', '[\"*\"]', NULL, NULL, '2026-02-24 09:15:18', '2026-02-24 09:15:18'),
(54, 'App\\Models\\User', 8, 'auth-token', 'bb53838bc474639174c63a42fe47fedfb328766553d2577696ca0f11244ec4f7', '[\"*\"]', NULL, NULL, '2026-02-24 09:20:39', '2026-02-24 09:20:39'),
(55, 'App\\Models\\User', 8, 'auth-token', 'e1c1befe39ea76f9d2da2f15d931bb02aa9e6752a6a7069c7e10a946b7962ca2', '[\"*\"]', NULL, NULL, '2026-02-24 09:22:26', '2026-02-24 09:22:26'),
(56, 'App\\Models\\User', 8, 'auth-token', 'a951f8c91c27ae2d13002425de363ccb5e5b4bc7f765ee75f768920b174e9846', '[\"*\"]', NULL, NULL, '2026-02-24 09:23:50', '2026-02-24 09:23:50'),
(57, 'App\\Models\\User', 8, 'auth-token', 'c546a2840adf5eaf1cd77cbc82a30215047cc12cd28aaffe6a0275847b0c3134', '[\"*\"]', NULL, NULL, '2026-02-24 09:37:05', '2026-02-24 09:37:05'),
(58, 'App\\Models\\User', 8, 'auth-token', '56b400012e0c44f886fc2c959bfde3a3cc5c95086a2cc5529caeec406c537b99', '[\"*\"]', NULL, NULL, '2026-02-24 09:46:46', '2026-02-24 09:46:46'),
(59, 'App\\Models\\User', 8, 'auth-token', '808740884321258fcc0173abc35ec4798933e45cd2e3dd4806422a29ca07bd29', '[\"*\"]', NULL, NULL, '2026-02-24 09:48:26', '2026-02-24 09:48:26'),
(60, 'App\\Models\\User', 8, 'auth-token', '095ef3f4adbcfa7d47491b7f6db58458c3e52ccfff5f472e649e305e0a60876f', '[\"*\"]', NULL, NULL, '2026-02-24 09:51:04', '2026-02-24 09:51:04'),
(61, 'App\\Models\\User', 8, 'auth-token', '26e4192e530e78b7570da80b204686b09df2c9df31f287b2cc1295e0dee86ae9', '[\"*\"]', NULL, NULL, '2026-02-24 09:51:08', '2026-02-24 09:51:08'),
(62, 'App\\Models\\User', 8, 'auth-token', 'cae412628be67f3c891db88e5ec5efade34f675dd4ba36233ddb2da84e21fc4d', '[\"*\"]', NULL, NULL, '2026-02-24 09:53:10', '2026-02-24 09:53:10'),
(63, 'App\\Models\\User', 8, 'auth-token', 'cd1996c794b08f866b9af425bac162114f6af495ed5be30a3b21bd59daa2351a', '[\"*\"]', NULL, NULL, '2026-02-24 09:54:17', '2026-02-24 09:54:17'),
(64, 'App\\Models\\User', 8, 'auth-token', '3f12969f4b9af604e639b4a7392421146d7a841bc85048c7a3f739b3ce6a33a3', '[\"*\"]', NULL, NULL, '2026-02-24 09:55:31', '2026-02-24 09:55:31'),
(65, 'App\\Models\\User', 8, 'auth-token', 'f30ec991aba003403817333f7d5edb973d62b99fddb5e3a6a06b074c1984d310', '[\"*\"]', NULL, NULL, '2026-02-24 09:56:40', '2026-02-24 09:56:40'),
(66, 'App\\Models\\User', 8, 'auth-token', '370a9a4a3b33a4886c5eb57a593b1e6d3e72a1a2b4f5a6af29ce3cef76634a59', '[\"*\"]', NULL, NULL, '2026-02-24 10:02:16', '2026-02-24 10:02:16'),
(67, 'App\\Models\\User', 8, 'auth-token', '7baf4b221dcc81337e8b829b1b17cde9b6696e5ef531c9d14149f66e9ea99577', '[\"*\"]', NULL, NULL, '2026-02-24 10:13:57', '2026-02-24 10:13:57'),
(68, 'App\\Models\\User', 8, 'auth-token', 'f9c7445c65b158dd151fcefa1da498250cfef8e058178bfa262b603d621a8fe5', '[\"*\"]', NULL, NULL, '2026-02-24 10:20:54', '2026-02-24 10:20:54'),
(69, 'App\\Models\\User', 8, 'auth-token', 'c0f5dd34e0557f62ecbc249c64451ef8b54e31a2f583c27b3b1558010b35b0aa', '[\"*\"]', NULL, NULL, '2026-02-24 10:22:38', '2026-02-24 10:22:38'),
(70, 'App\\Models\\User', 2, 'auth-token', 'd8ed69a790cd9daab2471bb7fd60ee23ae797c2247f6c0cdfab6cb61450effed', '[\"*\"]', NULL, NULL, '2026-02-25 04:43:09', '2026-02-25 04:43:09'),
(71, 'App\\Models\\User', 9, 'auth-token', '52880a3fbc643ff1a5d54a8df1295b04fab0d6b9429f143b3920d0f872cb7092', '[\"*\"]', NULL, NULL, '2026-02-25 04:46:22', '2026-02-25 04:46:22'),
(72, 'App\\Models\\User', 9, 'auth-token', '1502703b5324a9873b330774bfa21128e9b8a7c5562ade7bd6bbe28252f300ee', '[\"*\"]', NULL, NULL, '2026-02-25 04:52:21', '2026-02-25 04:52:21'),
(73, 'App\\Models\\User', 9, 'auth-token', '0447a936447b51cab5c67360bc4be149367fd3eec986aca607bffbc31cf3af24', '[\"*\"]', NULL, NULL, '2026-02-25 04:55:24', '2026-02-25 04:55:24'),
(74, 'App\\Models\\User', 9, 'auth-token', 'a9ccf33ae7d65884e5cebaf397607701def03f2f77e43976abf2dfb02827d302', '[\"*\"]', NULL, NULL, '2026-02-25 05:00:27', '2026-02-25 05:00:27'),
(75, 'App\\Models\\User', 9, 'auth-token', '767756029f506c40b07cb11d300b06d877587ea0cf85f0c9f3a6083f0f3a252e', '[\"*\"]', NULL, NULL, '2026-02-25 05:03:51', '2026-02-25 05:03:51'),
(76, 'App\\Models\\User', 9, 'auth-token', '289935a52a9662d2619e97d92b76366c43e553bf2e9b63e935e1f9a940061471', '[\"*\"]', NULL, NULL, '2026-02-25 05:12:18', '2026-02-25 05:12:18'),
(77, 'App\\Models\\User', 2, 'auth-token', '14a41f21875e15d7d44e26405fbc0b4ca0a8ad4793ceb93b86adc7c203b6d341', '[\"*\"]', NULL, NULL, '2026-02-26 04:29:08', '2026-02-26 04:29:08'),
(78, 'App\\Models\\User', 9, 'auth-token', '2bf6e9039230dcf78f5323365412cf2e45be5668178c5007cd954086db8582be', '[\"*\"]', NULL, NULL, '2026-02-26 04:29:34', '2026-02-26 04:29:34'),
(79, 'App\\Models\\User', 2, 'auth-token', '8a0e572d9623b4dd112fb4029a3231d13e49103eb2be394ca4c4c48bf3a8751b', '[\"*\"]', NULL, NULL, '2026-02-26 04:37:33', '2026-02-26 04:37:33'),
(80, 'App\\Models\\User', 9, 'auth-token', '94bcadab4081de85761838013fcb271da73b10e6537f38938f5d0e18a145bb99', '[\"*\"]', NULL, NULL, '2026-02-26 04:39:50', '2026-02-26 04:39:50'),
(81, 'App\\Models\\User', 9, 'auth-token', '3d0bb6e0150e773261fa0ec44e54f2f8d5e9a6f35823fce88eed816b5343a942', '[\"*\"]', NULL, NULL, '2026-02-26 04:42:36', '2026-02-26 04:42:36'),
(82, 'App\\Models\\User', 9, 'auth-token', '92692f2b2bcc93d969831606d05a8f37da9c86611bf2e8d34ae7373fc67c4195', '[\"*\"]', NULL, NULL, '2026-02-26 04:44:30', '2026-02-26 04:44:30'),
(83, 'App\\Models\\User', 9, 'auth-token', '3e90eaf7a446007cf2474576174c658fbb1b7a2c7e63ad88ccff326bd24c088a', '[\"*\"]', NULL, NULL, '2026-02-26 04:46:13', '2026-02-26 04:46:13'),
(84, 'App\\Models\\User', 9, 'auth-token', '8d18de397047f9ccb218137c1827aa16bf4e18ce8eae22edd980ddd3599a7590', '[\"*\"]', NULL, NULL, '2026-02-26 04:46:52', '2026-02-26 04:46:52'),
(85, 'App\\Models\\User', 9, 'auth-token', '52bfa890897c8e637c8f4755e4fb8d243110902c25f0d265051b99cfb177ade7', '[\"*\"]', NULL, NULL, '2026-02-26 04:48:57', '2026-02-26 04:48:57'),
(86, 'App\\Models\\User', 9, 'auth-token', 'c5d82cd9c458fe3924f791da009d053438d7ac35545de53f5d43a8c7e03d2fda', '[\"*\"]', NULL, NULL, '2026-02-26 04:53:49', '2026-02-26 04:53:49'),
(87, 'App\\Models\\User', 9, 'auth-token', '888925825d2300f41cdf73c09803cf8b1da706d51354de1ebfb62bf6d0d375ea', '[\"*\"]', NULL, NULL, '2026-02-26 04:57:40', '2026-02-26 04:57:40'),
(88, 'App\\Models\\User', 9, 'auth-token', 'f6f18bc7180c2117609415824925c2170faaa715e6a1ec7e32eb46d659b73ec6', '[\"*\"]', NULL, NULL, '2026-02-26 10:04:33', '2026-02-26 10:04:33'),
(89, 'App\\Models\\User', 2, 'auth-token', 'c14ae603dfd414647dcb6624ba569a8b86a83d4eab768eee4d0ffdd27fadeafc', '[\"*\"]', NULL, NULL, '2026-02-26 14:33:17', '2026-02-26 14:33:17'),
(90, 'App\\Models\\User', 9, 'auth-token', 'bc1496359966d04fce33b1bd45a4770b9b118b46915dcf579bdd7ff3a3ab1655', '[\"*\"]', NULL, NULL, '2026-02-26 14:33:38', '2026-02-26 14:33:38'),
(91, 'App\\Models\\User', 9, 'auth-token', '90b0242b4788e71d7b8396006b9464d3a8de829d91d0e4b1fdcb05ecb0bd31c3', '[\"*\"]', NULL, NULL, '2026-02-26 14:56:59', '2026-02-26 14:56:59'),
(92, 'App\\Models\\User', 9, 'auth-token', 'ff6c43a51fdd7033025d8599bd3236889e0bd0ff36fcd93b17d2119b3d036584', '[\"*\"]', NULL, NULL, '2026-02-26 14:57:34', '2026-02-26 14:57:34'),
(93, 'App\\Models\\User', 2, 'auth-token', 'befc7620ac735e51e3dcb247c0bbe074d62390b1252ec22b29bfc159786d357a', '[\"*\"]', NULL, NULL, '2026-02-26 14:59:27', '2026-02-26 14:59:27'),
(94, 'App\\Models\\User', 10, 'auth-token', 'bc4870b73f53432d6b21bb18f1af5ace95ff24faaa64e5b96a62a86375778f1b', '[\"*\"]', NULL, NULL, '2026-02-26 15:01:02', '2026-02-26 15:01:02'),
(95, 'App\\Models\\User', 2, 'auth-token', 'a088ac117b2542160df8c408585c279a6ad7edfea8acf0d1abe5597d7828bb3b', '[\"*\"]', NULL, NULL, '2026-02-26 15:13:41', '2026-02-26 15:13:41'),
(96, 'App\\Models\\User', 2, 'auth-token', 'bed739531a22b38aee63a86d27efb6ebd978d909acc58bc6fbd80c2153869c00', '[\"*\"]', NULL, NULL, '2026-02-26 15:15:33', '2026-02-26 15:15:33'),
(97, 'App\\Models\\User', 11, 'auth-token', '484da9433088732faf13228add6ea6e56a3ae4c59ee51f00df669d775af5062a', '[\"*\"]', NULL, NULL, '2026-02-26 15:16:45', '2026-02-26 15:16:45'),
(98, 'App\\Models\\User', 11, 'auth-token', 'dddc41f95f08f83d63e1cc5a92b17efb512e8892aceec4cd9d4003f4af43803a', '[\"*\"]', NULL, NULL, '2026-02-26 15:17:27', '2026-02-26 15:17:27'),
(99, 'App\\Models\\User', 11, 'auth-token', 'f83f358a49f6a214512fee9420753784d076a18e9182e63a6cd45e866242578f', '[\"*\"]', NULL, NULL, '2026-02-26 15:29:00', '2026-02-26 15:29:00'),
(100, 'App\\Models\\User', 11, 'auth-token', '80ba55f21aff92071844c950cf2eb0a9c8d56eae68dbfb3b029f67951580d264', '[\"*\"]', NULL, NULL, '2026-02-26 15:35:27', '2026-02-26 15:35:27'),
(101, 'App\\Models\\User', 12, 'auth-token', 'b7e9c4aaf245ca3a12dc365d65a1bfa64b592a98391509045e1169aae80de16c', '[\"*\"]', NULL, NULL, '2026-02-26 15:39:57', '2026-02-26 15:39:57'),
(102, 'App\\Models\\User', 12, 'auth-token', '572febd246990d4fe824043bc1eae9a9c7225f6193ff582ae2e2184f37e80b26', '[\"*\"]', NULL, NULL, '2026-02-26 15:40:20', '2026-02-26 15:40:20'),
(103, 'App\\Models\\User', 12, 'auth-token', '52dcfd620c21360f2d3723510f598d1c2057231dac77c190d490470d4c8807b1', '[\"*\"]', NULL, NULL, '2026-02-26 15:40:29', '2026-02-26 15:40:29'),
(104, 'App\\Models\\User', 11, 'auth-token', 'b56d100ae56b4a9d795cb072e21867a6c040bfea07b90e290295241ca884d70c', '[\"*\"]', NULL, NULL, '2026-02-26 16:06:48', '2026-02-26 16:06:48'),
(105, 'App\\Models\\User', 2, 'auth-token', '8efc8c36f14713f8d77d0e53cfb8cb96e34a4392d917cdcf2a287d8e294b7d25', '[\"*\"]', NULL, NULL, '2026-02-26 16:45:45', '2026-02-26 16:45:45'),
(106, 'App\\Models\\User', 2, 'auth-token', '8360bf21afbf3d8ff331a7f17c764057cf146816b7ba6f5f51eae54539d3d777', '[\"*\"]', NULL, NULL, '2026-02-26 17:23:43', '2026-02-26 17:23:43'),
(107, 'App\\Models\\User', 11, 'auth-token', '6901794aed14e759aef5d4fca1f53dc5a9a19abf6071b70673c7ff9039c5496c', '[\"*\"]', NULL, NULL, '2026-02-26 17:24:42', '2026-02-26 17:24:42'),
(108, 'App\\Models\\User', 2, 'auth-token', 'e6e855233781a86129c1b79684ed2a53ed86292aaa2ff51a1f8f1c151877597b', '[\"*\"]', NULL, NULL, '2026-02-26 17:26:52', '2026-02-26 17:26:52'),
(109, 'App\\Models\\User', 13, 'auth-token', 'e83185ad8ee3548584af8bf01d474d069403d0f67135b48116178064dca1fb87', '[\"*\"]', NULL, NULL, '2026-02-26 17:49:20', '2026-02-26 17:49:20'),
(110, 'App\\Models\\User', 2, 'auth-token', '7d33da1a034d7c702de96d127c4685417cee279fb7774579bbf49c537dfec920', '[\"*\"]', NULL, NULL, '2026-02-26 17:49:41', '2026-02-26 17:49:41'),
(111, 'App\\Models\\User', 14, 'auth-token', '04e305f2787da4c41b5697e00bbb3166040478c82ca250026841f6e4e5804d43', '[\"*\"]', NULL, NULL, '2026-02-26 18:02:57', '2026-02-26 18:02:57'),
(112, 'App\\Models\\User', 2, 'auth-token', '40ad4761590fd59e59d0dd7c34f85dc1f521ba821f70f261f429f06940a42aab', '[\"*\"]', NULL, NULL, '2026-02-26 18:09:47', '2026-02-26 18:09:47'),
(113, 'App\\Models\\User', 15, 'auth-token', '5fcbabb540a4d8c18a4733dbded7b502ac4e088a76e507fa59db7e4141026b3f', '[\"*\"]', NULL, NULL, '2026-02-26 18:12:02', '2026-02-26 18:12:02'),
(114, 'App\\Models\\User', 15, 'auth-token', 'e9aa9eb5c46ae2b99fea4dd8491daf9db59e295738c58f80ae7ed62fb817636b', '[\"*\"]', NULL, NULL, '2026-02-26 19:01:06', '2026-02-26 19:01:06'),
(115, 'App\\Models\\User', 15, 'auth-token', '3d7174e697ed3f20340cc44587809db3ed9e726a27f37e8165b06dd7afab04e7', '[\"*\"]', NULL, NULL, '2026-02-26 19:16:05', '2026-02-26 19:16:05'),
(116, 'App\\Models\\User', 15, 'auth-token', 'f47287584e0f8556b84438477a5a0d599117ddad6d4d1d21b09f6eb74c66f396', '[\"*\"]', NULL, NULL, '2026-02-26 19:18:02', '2026-02-26 19:18:02'),
(117, 'App\\Models\\User', 15, 'auth-token', 'cba4f98357f35a1d58bb90ea02332b07de00896712abd57f6e3f3f7845d12096', '[\"*\"]', NULL, NULL, '2026-02-26 19:23:56', '2026-02-26 19:23:56'),
(118, 'App\\Models\\User', 15, 'auth-token', '77b086d379ccf0548c1508963e9c160b43593c054762a4bfd2ac3f7166e79455', '[\"*\"]', NULL, NULL, '2026-02-26 19:29:14', '2026-02-26 19:29:14'),
(119, 'App\\Models\\User', 15, 'auth-token', '839d253b1467fe4e236ed8064562cee45cd1277aad16b3cab8d6655aec9a4450', '[\"*\"]', NULL, NULL, '2026-02-26 19:47:55', '2026-02-26 19:47:55'),
(120, 'App\\Models\\User', 15, 'auth-token', 'e3ae35b8b6d433b26061bc8ddcde938760e164e073a07260961c55d4a13496f3', '[\"*\"]', NULL, NULL, '2026-02-26 19:52:21', '2026-02-26 19:52:21'),
(121, 'App\\Models\\User', 15, 'auth-token', '68c0f6a1f99f87668065e03d21cb33c7820d02bada0a3a9850719445d01bea51', '[\"*\"]', NULL, NULL, '2026-02-26 20:01:32', '2026-02-26 20:01:32'),
(122, 'App\\Models\\User', 15, 'auth-token', '54acc673f9ac9cf2d299945f1a6c170ffb6a6adc63ecc1ba8015c8139c587652', '[\"*\"]', NULL, NULL, '2026-02-26 20:07:30', '2026-02-26 20:07:30'),
(123, 'App\\Models\\User', 18, 'auth-token', 'e1d282757242b3529ca2dbd8544059219b1e168b50b6d3a500a568c3867a8d9b', '[\"*\"]', NULL, NULL, '2026-02-26 20:08:51', '2026-02-26 20:08:51'),
(124, 'App\\Models\\User', 15, 'auth-token', 'a09cf9865bacd0958a9fae2bff26e43ad9829c6c55ff2b5d1f2d9b93591dc08b', '[\"*\"]', NULL, NULL, '2026-02-26 20:11:42', '2026-02-26 20:11:42'),
(125, 'App\\Models\\User', 19, 'auth-token', '5e214c0153f6174ef8fcf5553fc0d349ad712c98241d6e8a1fdcfda062a7755f', '[\"*\"]', NULL, NULL, '2026-02-26 20:13:14', '2026-02-26 20:13:14'),
(126, 'App\\Models\\User', 18, 'auth-token', '5207b2df4f502d1f5b4c17a629c3fa76b1a494bfc71644a5119c2f0bd727dcc7', '[\"*\"]', NULL, NULL, '2026-02-27 06:48:17', '2026-02-27 06:48:17'),
(127, 'App\\Models\\User', 2, 'auth-token', '8d6134cff956899d162a69536847bf7024efe79c29976a881b063a809fd3abcd', '[\"*\"]', NULL, NULL, '2026-02-27 07:23:28', '2026-02-27 07:23:28'),
(128, 'App\\Models\\User', 20, 'auth-token', '9e7e06f0baf57a615f3ba07e59c2b0709efadbbd737bc074673f7a42bc6a5c23', '[\"*\"]', NULL, NULL, '2026-02-27 07:24:52', '2026-02-27 07:24:52'),
(129, 'App\\Models\\User', 20, 'auth-token', '8e2935353182f2aac89e15ac32ced53bee1af3da07f8047779c7c1338f14878f', '[\"*\"]', NULL, NULL, '2026-02-27 07:38:25', '2026-02-27 07:38:25'),
(130, 'App\\Models\\User', 20, 'auth-token', '614a9fc626d5daff984afb31091364bf7c9efa4c572bcb698fff1ff4047e4876', '[\"*\"]', NULL, NULL, '2026-02-27 07:48:08', '2026-02-27 07:48:08'),
(131, 'App\\Models\\User', 20, 'auth-token', '75e1955ddb77a9c07626cabd9dfd0196d58698dd4c0029bbe6f655fa53c2a171', '[\"*\"]', NULL, NULL, '2026-02-27 07:50:06', '2026-02-27 07:50:06'),
(132, 'App\\Models\\User', 20, 'auth-token', '484a0f83e7e2d1c06b3b40dbe883a617917f937b0ccd7e074b4ff701ace17871', '[\"*\"]', NULL, NULL, '2026-02-27 07:53:36', '2026-02-27 07:53:36'),
(133, 'App\\Models\\User', 20, 'auth-token', '1777d10e5e793abe139da62c022a974168d6cd22a1ca30db368c46b80cdb525d', '[\"*\"]', NULL, NULL, '2026-02-27 07:53:55', '2026-02-27 07:53:55'),
(134, 'App\\Models\\User', 20, 'auth-token', 'be1f176c29da737eb96a04eb2415befdd7ea5b3b892f597ed9c37826bb7f7a3e', '[\"*\"]', NULL, NULL, '2026-02-27 07:54:14', '2026-02-27 07:54:14'),
(135, 'App\\Models\\User', 20, 'auth-token', '83c1d8b894b2278393e301f84acaf9823cfe802b4b289e13caf7ec8be0be62a6', '[\"*\"]', NULL, NULL, '2026-02-27 07:59:01', '2026-02-27 07:59:01'),
(136, 'App\\Models\\User', 20, 'auth-token', '169f1298934c64e6cedba8bf8837f65a8caa4eb6bc4366b0d27dc7d31f11e574', '[\"*\"]', NULL, NULL, '2026-02-27 08:19:28', '2026-02-27 08:19:28'),
(137, 'App\\Models\\User', 20, 'auth-token', '339c81f5cc28bc692a02f86d0134fcc67c0d10f799b3d5de00398b2fb48ed32b', '[\"*\"]', NULL, NULL, '2026-02-27 08:19:37', '2026-02-27 08:19:37'),
(138, 'App\\Models\\User', 20, 'auth-token', 'cb9efbd325c3e643eb5e938445dfc57d62577619eb9353570754678fe89378de', '[\"*\"]', NULL, NULL, '2026-02-27 08:23:51', '2026-02-27 08:23:51'),
(139, 'App\\Models\\User', 20, 'auth-token', '7992e86589cac9f7efd1cd4da6ba3992c7c337a4771d107df3c3e7a60222e2f8', '[\"*\"]', NULL, NULL, '2026-02-27 08:25:34', '2026-02-27 08:25:34'),
(140, 'App\\Models\\User', 20, 'auth-token', 'cfe77fc06d3c041c1ef916d675b343a47a681bc90fa46ea34da40db1da97d356', '[\"*\"]', NULL, NULL, '2026-02-27 08:30:27', '2026-02-27 08:30:27'),
(141, 'App\\Models\\User', 20, 'auth-token', '41b3b2274325a5718d6d102c2b7dd2ec417ecb98012e323f89cb086c733e248e', '[\"*\"]', NULL, NULL, '2026-02-27 08:39:19', '2026-02-27 08:39:19'),
(142, 'App\\Models\\User', 20, 'auth-token', 'e4f5baf597fd90ebebe1e069b57816f588fa7b388424ea1fae45b4783d19960a', '[\"*\"]', NULL, NULL, '2026-02-27 08:39:29', '2026-02-27 08:39:29'),
(143, 'App\\Models\\User', 20, 'auth-token', 'e7ca91fb3635d4375c974a09a1852917a633abbf86e74f59859b5d25c333ae42', '[\"*\"]', NULL, NULL, '2026-02-27 08:40:29', '2026-02-27 08:40:29'),
(144, 'App\\Models\\User', 20, 'auth-token', '18b87e9fd54e4b32a9ab2c59a39c45d37ec796662f357562d151ae6c6c21e25d', '[\"*\"]', NULL, NULL, '2026-02-27 08:40:37', '2026-02-27 08:40:37'),
(145, 'App\\Models\\User', 20, 'auth-token', '895fe799a1c3856d023ece8cbea395e0d43e28b1b8adf724da6b8c2ed2ea04c2', '[\"*\"]', NULL, NULL, '2026-02-27 08:44:50', '2026-02-27 08:44:50'),
(146, 'App\\Models\\User', 20, 'auth-token', '50b6305e813b28c2387e8c75eb4ec95590d69cefe71a3c1047d4785183d49d32', '[\"*\"]', NULL, NULL, '2026-02-27 08:47:59', '2026-02-27 08:47:59'),
(147, 'App\\Models\\User', 20, 'auth-token', '2f108c979f92136512aabd200cc162c486200b52883dc9bdd4bcfabb9c7e8a44', '[\"*\"]', NULL, NULL, '2026-02-27 08:48:50', '2026-02-27 08:48:50'),
(148, 'App\\Models\\User', 20, 'auth-token', '8d4172d44e7ba2e83eae9373cca2f0d7ed736f94738b59d71d0b96007d9eb7ec', '[\"*\"]', NULL, NULL, '2026-02-27 08:50:34', '2026-02-27 08:50:34'),
(149, 'App\\Models\\User', 20, 'auth-token', '354dac92e53112ac70490a04b906aecdf4c431323488bdecb5eb0994180f381a', '[\"*\"]', NULL, NULL, '2026-02-27 08:51:13', '2026-02-27 08:51:13'),
(150, 'App\\Models\\User', 20, 'auth-token', '581a79beae80fcbfee7a10d6fe4e69554f889a62cab4102380ae77a5cece067a', '[\"*\"]', NULL, NULL, '2026-02-27 08:54:33', '2026-02-27 08:54:33'),
(151, 'App\\Models\\User', 20, 'auth-token', 'c3facea8a547b850e1a687fc5947cb0488a2624bf6e7a05b58b7b298f2e27009', '[\"*\"]', NULL, NULL, '2026-02-27 08:57:32', '2026-02-27 08:57:32'),
(152, 'App\\Models\\User', 20, 'auth-token', '8c43870f8f2fac3bcb5ea8cbeafac81b6fb7fbee9768c42f8295e2a6233a3943', '[\"*\"]', NULL, NULL, '2026-02-27 09:00:57', '2026-02-27 09:00:57'),
(153, 'App\\Models\\User', 20, 'auth-token', 'de7bd19e7b0404830c03d5add773a7e22f2694f514c709eb2698050de48d2e78', '[\"*\"]', NULL, NULL, '2026-02-27 09:02:03', '2026-02-27 09:02:03'),
(154, 'App\\Models\\User', 21, 'auth-token', 'db9b255f207fd6f860d50edaeadd8060424127874422441e6f812f6d026f5cd8', '[\"*\"]', NULL, NULL, '2026-02-27 09:03:58', '2026-02-27 09:03:58'),
(155, 'App\\Models\\User', 21, 'auth-token', '8eb3e887d04037f9aec723aeda10dd3606a21a03679f8c9a311e3fb5d32ce05f', '[\"*\"]', NULL, NULL, '2026-02-27 09:04:20', '2026-02-27 09:04:20'),
(156, 'App\\Models\\User', 20, 'auth-token', 'bc0f529e2bf47e7044930a9ec8196792b558a34fd0143695daa4d137c8f69951', '[\"*\"]', NULL, NULL, '2026-02-27 17:49:27', '2026-02-27 17:49:27'),
(157, 'App\\Models\\User', 21, 'auth-token', '014ef0df7e4cff6906e6c7f105a3c50c841f27e5e34762d0d73918891cbf7287', '[\"*\"]', NULL, NULL, '2026-02-27 17:50:51', '2026-02-27 17:50:51'),
(158, 'App\\Models\\User', 2, 'auth-token', 'e45243eaa227c82e90453ebec0643710d0ec486311ae5e6bff7227a218a8e57c', '[\"*\"]', NULL, NULL, '2026-02-27 17:52:22', '2026-02-27 17:52:22'),
(159, 'App\\Models\\User', 2, 'auth-token', 'fdb63427bc07a3970a1367bc11c0cb99b22b6ccabb9df113366fc88e294ce176', '[\"*\"]', NULL, NULL, '2026-02-27 17:54:10', '2026-02-27 17:54:10'),
(160, 'App\\Models\\User', 2, 'auth-token', '967322caaad635c3727f881fb41169a9ee771035d2b90f77b945cf742db8ce24', '[\"*\"]', NULL, NULL, '2026-02-27 18:15:53', '2026-02-27 18:15:53'),
(161, 'App\\Models\\User', 20, 'auth-token', '2142b4771aa3ac7521f8ccdde4c480ceb5b66edbcc7d1f1049f266a2c9cf418a', '[\"*\"]', NULL, NULL, '2026-02-27 18:18:14', '2026-02-27 18:18:14'),
(162, 'App\\Models\\User', 2, 'auth-token', 'b0e646ef216b6e113d5f714b05eadb3fe0de242a68e1901fc71942a29272c6ae', '[\"*\"]', NULL, NULL, '2026-02-27 18:20:10', '2026-02-27 18:20:10'),
(163, 'App\\Models\\User', 22, 'auth-token', 'c2435a396bd5db8320ec900793cd1274b5dd0d66635a1ba6fff7a1c49060d82a', '[\"*\"]', NULL, NULL, '2026-02-27 18:21:05', '2026-02-27 18:21:05'),
(164, 'App\\Models\\User', 22, 'auth-token', '9f1dcfffe248a4ca4be779f8243a7aeb1d1a5996fdccd17fe5e586825ebc96d5', '[\"*\"]', NULL, NULL, '2026-02-27 18:21:49', '2026-02-27 18:21:49'),
(165, 'App\\Models\\User', 20, 'auth-token', '3642e97503cd9528cf8664b5aa792b4c54747cff06112434cca64d1995243a03', '[\"*\"]', NULL, NULL, '2026-02-27 18:32:49', '2026-02-27 18:32:49'),
(166, 'App\\Models\\User', 2, 'auth-token', '602ed9ab3176d5410f37e27d768ebec242001d2dca38b6d2b75f16f8414ba679', '[\"*\"]', NULL, NULL, '2026-02-27 18:44:39', '2026-02-27 18:44:39'),
(167, 'App\\Models\\User', 2, 'auth-token', 'ca3de27d6a7d8ec6e666bff9aa92f89c766bc85d1c9b2299527be7d05236b411', '[\"*\"]', NULL, NULL, '2026-02-27 19:20:06', '2026-02-27 19:20:06'),
(168, 'App\\Models\\User', 20, 'auth-token', '9b4701d5c0e14115989a0cd8fc9e2d119cbd279cb8d6d5b9b03769dd420e7c73', '[\"*\"]', NULL, NULL, '2026-02-27 19:24:32', '2026-02-27 19:24:32'),
(169, 'App\\Models\\User', 20, 'auth-token', 'c81063f0d9bd3e14af37c80a29b1329e143044ee7c6ca1b05922b5b5f8fe35aa', '[\"*\"]', NULL, NULL, '2026-02-27 20:00:19', '2026-02-27 20:00:19'),
(170, 'App\\Models\\User', 22, 'auth-token', '3919b91991fa274abfcdfd095df932c633c05b9a40b0df502b8b4d7c7689f289', '[\"*\"]', NULL, NULL, '2026-02-27 20:02:39', '2026-02-27 20:02:39'),
(171, 'App\\Models\\User', 22, 'auth-token', '9c9210e6d9e9defdcdd44915d7c5e11dd822eaf918833e39a5d370acba7d873b', '[\"*\"]', NULL, NULL, '2026-02-27 20:03:59', '2026-02-27 20:03:59'),
(172, 'App\\Models\\User', 20, 'auth-token', '10cec00392f61f70c19aac4a4644914cb8147bd6c1aed92264d0c107b55b3bab', '[\"*\"]', NULL, NULL, '2026-02-27 20:22:57', '2026-02-27 20:22:57'),
(173, 'App\\Models\\User', 2, 'auth-token', '823d260b85f4ba9245e9ba9ae79f56e2731de263ff910a2f7ee0c12a2bedd4d7', '[\"*\"]', NULL, NULL, '2026-02-28 05:24:54', '2026-02-28 05:24:54'),
(174, 'App\\Models\\User', 22, 'auth-token', 'ccc03d15e8033572fe6bbf1c8b4762fe5699599e6eb8931c168d6d8c5c06db31', '[\"*\"]', NULL, NULL, '2026-02-28 05:25:33', '2026-02-28 05:25:33'),
(175, 'App\\Models\\User', 20, 'auth-token', 'e935c00a613426a04354c5f25d2727fc1d949c83ca415eb60a0faacc09b8cf73', '[\"*\"]', NULL, NULL, '2026-02-28 05:27:34', '2026-02-28 05:27:34'),
(176, 'App\\Models\\User', 22, 'auth-token', '8b1b2f065ead4bcc2459d3a68f4e945c5a28d58d3f35f63c6e35e42addb0ab19', '[\"*\"]', NULL, NULL, '2026-02-28 05:49:43', '2026-02-28 05:49:43'),
(177, 'App\\Models\\User', 23, 'auth-token', '001a6aab434c6ddba4624d93544d2f55d355a21ddf613e1e53ccdfa51e77fa6d', '[\"*\"]', NULL, NULL, '2026-02-28 05:54:23', '2026-02-28 05:54:23'),
(178, 'App\\Models\\User', 23, 'auth-token', 'e804222bb60bcad99ddb0d59f6917a982bd8edcfdde50a4aee20a83effda617f', '[\"*\"]', NULL, NULL, '2026-02-28 05:54:47', '2026-02-28 05:54:47'),
(179, 'App\\Models\\User', 22, 'auth-token', '8a6e9ee0568b6e60725d6c5f3ddee5e35f1801f402b92f66768baf44a91a3631', '[\"*\"]', NULL, NULL, '2026-02-28 05:55:15', '2026-02-28 05:55:15'),
(180, 'App\\Models\\User', 23, 'auth-token', 'b1fc4b3cae9f7183accd63f22c2ab8d7e3c3b4025c98d25c53b81ab00f072e72', '[\"*\"]', NULL, NULL, '2026-02-28 05:55:33', '2026-02-28 05:55:33'),
(181, 'App\\Models\\User', 23, 'auth-token', '24f3474accc7c3f771be3c204520d404dfc3e1289d1f959a47479045500ed6d4', '[\"*\"]', NULL, NULL, '2026-02-28 05:56:10', '2026-02-28 05:56:10'),
(182, 'App\\Models\\User', 23, 'auth-token', 'ee42b58d7c187b2523d2bb524c7ccf10331bf7afeeef39d9267d3a466a2c7cea', '[\"*\"]', NULL, NULL, '2026-02-28 06:09:35', '2026-02-28 06:09:35'),
(183, 'App\\Models\\User', 23, 'auth-token', 'bc68bb3c08c6414961b4a191d723c5fdc5bd05592332e2b0b3eba2ff8d9cb4cb', '[\"*\"]', NULL, NULL, '2026-02-28 09:15:08', '2026-02-28 09:15:08'),
(184, 'App\\Models\\User', 20, 'auth-token', 'cb02baaa07850794613c73a6bb4ba00b924db6cd917e42a3a5b4cd5d69dbb0b8', '[\"*\"]', NULL, NULL, '2026-02-28 09:54:11', '2026-02-28 09:54:11'),
(185, 'App\\Models\\User', 23, 'auth-token', '3c9b8f7f40115ba7ffb7af5fe502865123c188497fc5a72bb0e0726817487183', '[\"*\"]', NULL, NULL, '2026-02-28 10:36:45', '2026-02-28 10:36:45'),
(186, 'App\\Models\\User', 22, 'auth-token', '3dd1b8b2b1388775757e711c80b77df87ed6668dcf3d8d64496d8e1da481da00', '[\"*\"]', NULL, NULL, '2026-03-01 05:34:26', '2026-03-01 05:34:26'),
(187, 'App\\Models\\User', 2, 'auth-token', '903e7e5068729deea63c15dbb2b93a68e73cbef6ff8875f932d2b076683dfe47', '[\"*\"]', NULL, NULL, '2026-03-01 05:37:32', '2026-03-01 05:37:32'),
(188, 'App\\Models\\User', 22, 'auth-token', 'c57715ab01727f34a409ea110db065745b1e9abb01428dac79a3fff0238ffd95', '[\"*\"]', NULL, NULL, '2026-03-01 06:40:52', '2026-03-01 06:40:52'),
(189, 'App\\Models\\User', 20, 'auth-token', '4c60412dedfb934284d46d4fdd9aaa340d521fc901798c05ad88585d27612d7e', '[\"*\"]', NULL, NULL, '2026-03-01 06:42:01', '2026-03-01 06:42:01'),
(190, 'App\\Models\\User', 23, 'auth-token', '25266733d5587153cac8faadfcde9b5da2569b42675a150877ee53eb0a9895c0', '[\"*\"]', NULL, NULL, '2026-03-01 06:42:10', '2026-03-01 06:42:10'),
(191, 'App\\Models\\User', 2, 'auth-token', 'caf415917e1e67a0a63972856ee88cb6a1f33a5da88c2717d122f58d7966c78b', '[\"*\"]', NULL, NULL, '2026-03-01 06:44:15', '2026-03-01 06:44:15'),
(192, 'App\\Models\\User', 20, 'auth-token', '63a33a13cdddf96cbc9b040c01865c9f0f5e253cdbd97c57891402fafd8d5c9d', '[\"*\"]', NULL, NULL, '2026-03-01 06:45:12', '2026-03-01 06:45:12'),
(193, 'App\\Models\\User', 2, 'auth-token', '75a07ab88123ade4d33c1269734ddcce73bd8e6f0222affdb55fa87f07ba0b8e', '[\"*\"]', NULL, NULL, '2026-03-01 06:50:20', '2026-03-01 06:50:20'),
(194, 'App\\Models\\User', 22, 'auth-token', 'fe249065b63ef21d0c5f68dc722caba21eac098fc806de75b64f371090912104', '[\"*\"]', NULL, NULL, '2026-03-01 06:51:17', '2026-03-01 06:51:17'),
(195, 'App\\Models\\User', 20, 'auth-token', '0ef90e5fb0216e2902d65d172b094d1f6e7cbe9e5112edaf58696a47a7a4a029', '[\"*\"]', NULL, NULL, '2026-03-01 07:39:22', '2026-03-01 07:39:22'),
(196, 'App\\Models\\User', 2, 'auth-token', 'db743ba241ae238d45410b6e13960285bc27b072586fd02164789ebdb03b1d2e', '[\"*\"]', NULL, NULL, '2026-03-01 07:39:46', '2026-03-01 07:39:46'),
(197, 'App\\Models\\User', 22, 'auth-token', 'a3d600ea61b031acc9135454ad266fd46e907e07076bf7a9a5f3939f84e515ed', '[\"*\"]', NULL, NULL, '2026-03-01 07:52:33', '2026-03-01 07:52:33'),
(198, 'App\\Models\\User', 2, 'auth-token', 'f6fc5a4168acc94ba7236900959c6ebf7ccce3c418b27be6685b240aa3ac35b8', '[\"*\"]', NULL, NULL, '2026-03-01 07:53:29', '2026-03-01 07:53:29'),
(199, 'App\\Models\\User', 22, 'auth-token', '2ca0feb7460f16cafa42195036d0b7b7bb5003adfb2e30c577cfa1d1ff133056', '[\"*\"]', NULL, NULL, '2026-03-01 07:54:05', '2026-03-01 07:54:05'),
(200, 'App\\Models\\User', 20, 'auth-token', '08efd39fba7f7dff797daff1d0121534ea9c3f3ce216e2e019e916b01d2335bb', '[\"*\"]', NULL, NULL, '2026-03-01 08:01:28', '2026-03-01 08:01:28'),
(201, 'App\\Models\\User', 22, 'auth-token', 'fd9ed966875bb8d47232cdc1635a1de5fcabca75526f4863b0bf82494ed0b0cc', '[\"*\"]', NULL, NULL, '2026-03-01 08:01:43', '2026-03-01 08:01:43'),
(202, 'App\\Models\\User', 22, 'auth-token', 'eacfdc5cc3a3d1998798762a7faf1860813608b3b519a38934c372b6ce6693d6', '[\"*\"]', NULL, NULL, '2026-03-01 08:02:02', '2026-03-01 08:02:02'),
(203, 'App\\Models\\User', 22, 'auth-token', '89e6c979612ae3a4bff07cb31839d625bdc9737d70ca223324d00d2e6da9c3a7', '[\"*\"]', NULL, NULL, '2026-03-01 08:10:07', '2026-03-01 08:10:07'),
(204, 'App\\Models\\User', 22, 'auth-token', 'fa86ea631bb65e86fa7ce8314db863a8b538c365acec5a26a74f8aa427cee9ee', '[\"*\"]', NULL, NULL, '2026-03-01 08:32:39', '2026-03-01 08:32:39'),
(205, 'App\\Models\\User', 2, 'auth-token', '916b1a853515f0e8119cbfffe5ebcecc019badb18b5351866a058357a9de8f6d', '[\"*\"]', NULL, NULL, '2026-03-01 08:50:32', '2026-03-01 08:50:32'),
(206, 'App\\Models\\User', 22, 'auth-token', '84905b57373f2f167c16427315d026fc3292feb0ea9f4f5cdb84a13cc333df6b', '[\"*\"]', NULL, NULL, '2026-03-01 08:52:53', '2026-03-01 08:52:53'),
(207, 'App\\Models\\User', 2, 'auth-token', 'b9b62340d7fe2d2e537df25262fbf4a7aa8a40f1e6227a81d228755a4686431c', '[\"*\"]', NULL, NULL, '2026-03-01 09:34:16', '2026-03-01 09:34:16'),
(208, 'App\\Models\\User', 22, 'auth-token', '184ebde7454d0a1e477c751c44d6ba62620743ef16d61f5958942840f9e66737', '[\"*\"]', NULL, NULL, '2026-03-01 09:35:45', '2026-03-01 09:35:45'),
(209, 'App\\Models\\User', 2, 'auth-token', '40715da9c049d2e58dcde529486847fb40e159e430e0f79dba2778f94fb277ea', '[\"*\"]', NULL, NULL, '2026-03-01 09:36:24', '2026-03-01 09:36:24'),
(210, 'App\\Models\\User', 22, 'auth-token', 'a78f23678646660d036316c54b4d1535630b76a9561d0ceb1e92e9b13a7ca8c2', '[\"*\"]', NULL, NULL, '2026-03-01 09:54:55', '2026-03-01 09:54:55'),
(211, 'App\\Models\\User', 2, 'auth-token', 'ba950a9063a915b9820323a4fddd499cf146661243cdf548981f4f5109d25c27', '[\"*\"]', NULL, NULL, '2026-03-01 10:18:58', '2026-03-01 10:18:58'),
(212, 'App\\Models\\User', 20, 'auth-token', '2797711798a44c695253578d6804935ffd6640864bc96da5ffff13aa32234470', '[\"*\"]', NULL, NULL, '2026-03-01 10:45:33', '2026-03-01 10:45:33'),
(213, 'App\\Models\\User', 22, 'auth-token', '56c4de7d7e0a8e571147f121334aa2c1bff6052f7f94f3efe1cf555bff5a29a6', '[\"*\"]', NULL, NULL, '2026-03-01 12:13:35', '2026-03-01 12:13:35'),
(214, 'App\\Models\\User', 2, 'auth-token', '372a4140d5b2cc14946266d1e4f5c0cf60eefd14e6844a6a8ae986b564a72f92', '[\"*\"]', NULL, NULL, '2026-03-01 12:35:45', '2026-03-01 12:35:45'),
(215, 'App\\Models\\User', 22, 'auth-token', 'c626cb6db96e6faa86b4fbb6c4e2335dab06a180ae7ab245c3e600a354731a69', '[\"*\"]', NULL, NULL, '2026-03-01 13:11:34', '2026-03-01 13:11:34'),
(216, 'App\\Models\\User', 24, 'auth-token', '25e2a0e76697adc69616d4509dd1ce3c35a5e55590ced0b6b826fb204317c01b', '[\"*\"]', NULL, NULL, '2026-03-01 13:44:25', '2026-03-01 13:44:25'),
(217, 'App\\Models\\User', 24, 'auth-token', 'ebbe9d4f503309aa0d6a3e4295de389ba8762db37a9edd4ad098d22a882b63df', '[\"*\"]', NULL, NULL, '2026-03-01 13:45:06', '2026-03-01 13:45:06'),
(218, 'App\\Models\\User', 22, 'auth-token', 'de314b0f3a575de7e876215e14f50eda665574e9754843ea3ead9b48418b14ab', '[\"*\"]', NULL, NULL, '2026-03-01 14:16:38', '2026-03-01 14:16:38'),
(219, 'App\\Models\\User', 22, 'auth-token', 'f19474a116c20b3ca99fa150143d8ceabc4b4697fe27ac285b0e82191a2e6add', '[\"*\"]', NULL, NULL, '2026-03-01 14:45:09', '2026-03-01 14:45:09'),
(220, 'App\\Models\\User', 24, 'auth-token', 'c7d97d641482cdded3382beeaad75293e0cf4d8e54c5f80b07e86dfe8310a8fe', '[\"*\"]', NULL, NULL, '2026-03-01 14:45:45', '2026-03-01 14:45:45'),
(221, 'App\\Models\\User', 22, 'auth-token', '706184fd8cc6c38663cd0ee8a3ad9f74db73a829fc5dc778d6c692d7d38772c6', '[\"*\"]', NULL, NULL, '2026-03-01 14:46:45', '2026-03-01 14:46:45'),
(222, 'App\\Models\\User', 22, 'auth-token', '3c0bb0ca7677693ebb540b6c8787f06994b87db172989b569c9ce94df33d27a2', '[\"*\"]', NULL, NULL, '2026-03-01 15:06:33', '2026-03-01 15:06:33'),
(223, 'App\\Models\\User', 22, 'auth-token', 'd3f6081e1b1edccdb8a5d28cd5e944704358c4405315e1bf09fed6db98a639fb', '[\"*\"]', NULL, NULL, '2026-03-01 16:53:00', '2026-03-01 16:53:00'),
(224, 'App\\Models\\User', 22, 'auth-token', '47859cd3c05561263c9eac2ce67315a8861454b3f7131e0467f9e52644ee10dd', '[\"*\"]', NULL, NULL, '2026-03-01 17:12:15', '2026-03-01 17:12:15'),
(225, 'App\\Models\\User', 22, 'auth-token', 'aac66073f17709574b37cff69a26e9f31bb9dd1f6710af9e1820af5fb8322145', '[\"*\"]', NULL, NULL, '2026-03-01 17:21:20', '2026-03-01 17:21:20'),
(226, 'App\\Models\\User', 22, 'auth-token', 'ee5d33c54e434bf2a43e3568cbc81c1aeb5cfc4a1b966febe563319eed912911', '[\"*\"]', NULL, NULL, '2026-03-01 17:26:27', '2026-03-01 17:26:27'),
(227, 'App\\Models\\User', 20, 'auth-token', 'dec9c5299b0fae9e764f515657210796a8783dddb05541d8ce0f81319532871a', '[\"*\"]', NULL, NULL, '2026-03-01 17:34:23', '2026-03-01 17:34:23'),
(228, 'App\\Models\\User', 23, 'auth-token', '324c6ba409886a2e9ffa69c34960732a298ebc0dbbded9064528d07b3823710d', '[\"*\"]', NULL, NULL, '2026-03-01 17:42:32', '2026-03-01 17:42:32'),
(229, 'App\\Models\\User', 2, 'auth-token', '366d3943710d81ba78bc66825d26454a38a178f4d786214293889660975a1af2', '[\"*\"]', NULL, NULL, '2026-03-01 17:42:48', '2026-03-01 17:42:48'),
(230, 'App\\Models\\User', 22, 'auth-token', 'fbe69bee0934f3ff146697dd453bfc84527c40d4a2c69ae44446078a56e56eb9', '[\"*\"]', NULL, NULL, '2026-03-01 17:45:47', '2026-03-01 17:45:47'),
(231, 'App\\Models\\User', 22, 'auth-token', 'e85a129b5511d6adca19aa90177c0a08331ad6ed33f7f58f524fda2feceae3d9', '[\"*\"]', NULL, NULL, '2026-03-02 13:13:56', '2026-03-02 13:13:56'),
(232, 'App\\Models\\User', 22, 'auth-token', '02649f976bc567cbddf4c494dc7ddcecb7381754e9c60d122a1ac0fa6b0f4b8f', '[\"*\"]', NULL, NULL, '2026-03-02 15:07:02', '2026-03-02 15:07:02'),
(233, 'App\\Models\\User', 20, 'auth-token', '315ac2fe6bf5e102fb36a3d96255d30bc25d535dc8f72d8a55c381d8fdd59416', '[\"*\"]', NULL, NULL, '2026-03-02 15:15:14', '2026-03-02 15:15:14'),
(234, 'App\\Models\\User', 23, 'auth-token', '87b14d33e971d013d9a9b2de2b0443d0ba7bb1311b624d57abc85fb7c22a4f18', '[\"*\"]', NULL, NULL, '2026-03-02 15:17:54', '2026-03-02 15:17:54'),
(235, 'App\\Models\\User', 24, 'auth-token', '81605e192be4a2fe56b3e4c426654123296c7370a49487d1afaa5bd4d9339f5b', '[\"*\"]', NULL, NULL, '2026-03-02 15:25:45', '2026-03-02 15:25:45'),
(236, 'App\\Models\\User', 23, 'auth-token', 'eae1bed9392ac4673742c32de8b51d64e3b1f4d1c2b98c38d66abf485624f48a', '[\"*\"]', NULL, NULL, '2026-03-02 15:26:07', '2026-03-02 15:26:07'),
(237, 'App\\Models\\User', 24, 'auth-token', 'f1a6ce2f0ebd3651fe59eed67a163195594837db09ab16010106e651aa1d6181', '[\"*\"]', NULL, NULL, '2026-03-02 15:36:01', '2026-03-02 15:36:01'),
(238, 'App\\Models\\User', 20, 'auth-token', '83a784d0158cdabaf71fe2b3500090dbe1d3f7cbf812d45515a74820dcfbfb24', '[\"*\"]', NULL, NULL, '2026-03-02 15:36:27', '2026-03-02 15:36:27'),
(239, 'App\\Models\\User', 24, 'auth-token', '60e91533e8169235230df7f59993b20344322fe735aa33bb4177b5850658aa4d', '[\"*\"]', NULL, NULL, '2026-03-02 15:36:41', '2026-03-02 15:36:41'),
(240, 'App\\Models\\User', 23, 'auth-token', '20138bb08b7ece8eeab19a5b860f6083764023fd0e7d7049eeef681dfa61a3a7', '[\"*\"]', NULL, NULL, '2026-03-02 15:36:57', '2026-03-02 15:36:57'),
(241, 'App\\Models\\User', 24, 'auth-token', '4591fb5cc1e7a2f9fa783285161063ebb7770d5db0ec55f72dc390e609ac2a37', '[\"*\"]', NULL, NULL, '2026-03-02 15:37:16', '2026-03-02 15:37:16'),
(242, 'App\\Models\\User', 24, 'auth-token', '8fd3f762fedaf1b56c18efa13bcfd003d8bb6036104a34ca18437d57c8096c95', '[\"*\"]', NULL, NULL, '2026-03-02 15:48:45', '2026-03-02 15:48:45'),
(243, 'App\\Models\\User', 24, 'auth-token', '9eb044d588617a9204cd2d50f4c6ca04a8bbb7e3d41c6736c7291c96b7707490', '[\"*\"]', NULL, NULL, '2026-03-02 15:51:44', '2026-03-02 15:51:44'),
(244, 'App\\Models\\User', 24, 'auth-token', '0d7956065a9e6ead242e94ea7db1284f2e7db8dba057bfc0c4d1d36be1cc847c', '[\"*\"]', NULL, NULL, '2026-03-02 15:53:00', '2026-03-02 15:53:00'),
(245, 'App\\Models\\User', 24, 'auth-token', '976bfccad6c9948ce3b2ea1347b462ad3d5e37225cb4b041e6a7b5e15f8a9e60', '[\"*\"]', NULL, NULL, '2026-03-02 16:16:59', '2026-03-02 16:16:59'),
(246, 'App\\Models\\User', 20, 'auth-token', 'b8c0700481d59976ad468efae4fcaaa2a157beebc37e08596e96e19cc868f1d4', '[\"*\"]', NULL, NULL, '2026-03-02 16:24:22', '2026-03-02 16:24:22'),
(247, 'App\\Models\\User', 24, 'auth-token', '69ef885cc626ed506aaafd4ca10fcb436c6570ef9a8cc8151d390e21ded3bfbd', '[\"*\"]', NULL, NULL, '2026-03-02 16:30:15', '2026-03-02 16:30:15'),
(248, 'App\\Models\\User', 24, 'auth-token', '2a725992e363cd50a5a65629ceab8573e7a3b9cc0b53382164b8a366ef67e9cb', '[\"*\"]', NULL, NULL, '2026-03-02 16:46:22', '2026-03-02 16:46:22'),
(249, 'App\\Models\\User', 23, 'auth-token', '34da87d48ba7f7adf71351ad33afc10f24a35c42fdcfaaa33d8f70a2dd6097e6', '[\"*\"]', NULL, NULL, '2026-03-02 17:46:30', '2026-03-02 17:46:30'),
(250, 'App\\Models\\User', 22, 'auth-token', 'ad1f3de7526f78f80be4b0940fd3c7a1b15959852e9c12a3a3edc4df585790d3', '[\"*\"]', NULL, NULL, '2026-03-02 17:47:04', '2026-03-02 17:47:04'),
(251, 'App\\Models\\User', 23, 'auth-token', '39b6d488b12aaa97d1827182b0a60754e0011d47047ba6a570e261ccb013d928', '[\"*\"]', NULL, NULL, '2026-03-02 17:47:37', '2026-03-02 17:47:37'),
(252, 'App\\Models\\User', 20, 'auth-token', '42e30d116cc2c7a05061fd12176632738f4a65625d81a81ca18377f9047651ba', '[\"*\"]', NULL, NULL, '2026-03-02 18:18:38', '2026-03-02 18:18:38'),
(253, 'App\\Models\\User', 23, 'auth-token', '04c5d6f28937c1eebbd4c041b433c1e09d20d644125f1d2a4a86c4207f7f89db', '[\"*\"]', NULL, NULL, '2026-03-03 15:51:49', '2026-03-03 15:51:49'),
(254, 'App\\Models\\User', 23, 'auth-token', '3e87c8ca284a217cf40b9cdf2c1259af3e58b64c6db8b8e13d8227acc1001f0c', '[\"*\"]', NULL, NULL, '2026-03-03 15:57:07', '2026-03-03 15:57:07'),
(255, 'App\\Models\\User', 23, 'auth-token', 'a9f4bea4825388e0c3495488d8ac11c4b990daa3e1fb287ef7ff73ab0bb889dd', '[\"*\"]', NULL, NULL, '2026-03-03 17:35:40', '2026-03-03 17:35:40'),
(256, 'App\\Models\\User', 20, 'auth-token', '0ba4d04b343a999489b6037abc63e68bfe483740a1a5f08272e1442dcbe376d7', '[\"*\"]', NULL, NULL, '2026-03-03 18:15:37', '2026-03-03 18:15:37'),
(257, 'App\\Models\\User', 23, 'auth-token', '7bd27975338879fd7fec3c80c87f6e199ff1eb1f0f9779f8e6c460a09ad63d5b', '[\"*\"]', NULL, NULL, '2026-03-04 13:23:00', '2026-03-04 13:23:00'),
(258, 'App\\Models\\User', 20, 'auth-token', '02b9ee9e3ba5a9317300d18d26337728773e079b7b20b4649fd6d8965e393494', '[\"*\"]', NULL, NULL, '2026-03-04 13:23:40', '2026-03-04 13:23:40'),
(259, 'App\\Models\\User', 23, 'auth-token', '314d34c0a7e9041d65a851d0fc49433571f66c9d8bac2ef09eded72911880f5d', '[\"*\"]', NULL, NULL, '2026-03-04 13:28:40', '2026-03-04 13:28:40'),
(260, 'App\\Models\\User', 20, 'auth-token', '619ab30dda62e590d5a9c2aa7a9b7762212e06d1f9a51351210aad694ed189a0', '[\"*\"]', NULL, NULL, '2026-03-04 14:24:16', '2026-03-04 14:24:16'),
(261, 'App\\Models\\User', 23, 'auth-token', 'b1e1f352f99138a6ec24cf28e22f921ef2271d0fef1980c108ddcccadb94cd2d', '[\"*\"]', NULL, NULL, '2026-03-04 15:10:17', '2026-03-04 15:10:17'),
(262, 'App\\Models\\User', 2, 'auth-token', 'a1de92f320f385625163a01ee69544432cc236d5fda2089dc943a016c1004102', '[\"*\"]', NULL, NULL, '2026-03-04 15:10:26', '2026-03-04 15:10:26'),
(263, 'App\\Models\\User', 20, 'auth-token', '84a006a476e90a8ed6fddc4e2264fe388cce699db63d903484b02d8edb5452a1', '[\"*\"]', NULL, NULL, '2026-03-04 15:18:59', '2026-03-04 15:18:59'),
(264, 'App\\Models\\User', 26, 'auth-token', '15f01170f8372c03b2e164afe9bd2fb74fba7dfad96ee478781752e7c24e2418', '[\"*\"]', NULL, NULL, '2026-03-04 15:22:16', '2026-03-04 15:22:16'),
(265, 'App\\Models\\User', 26, 'auth-token', '24069cbab85fb10b1bd01964329fdd67df214849b10fcf28f30dcda8e503d99d', '[\"*\"]', NULL, NULL, '2026-03-04 15:22:59', '2026-03-04 15:22:59'),
(266, 'App\\Models\\User', 26, 'auth-token', '98806cc2a9ec9b62feba7e31425a7eb929d4e39b19fd7b9aeedca6ba41efbe8c', '[\"*\"]', NULL, NULL, '2026-03-04 15:23:25', '2026-03-04 15:23:25'),
(267, 'App\\Models\\User', 23, 'auth-token', '86f0ba6fb23713b7e89583d1fe37d6f1c2d3655dfd05cad82013d6ae96b947a3', '[\"*\"]', NULL, NULL, '2026-03-04 15:24:11', '2026-03-04 15:24:11'),
(268, 'App\\Models\\User', 26, 'auth-token', '183b519d99310ae7003a545deb66bc8c774bb98d08aed10b9713a96d0b333e35', '[\"*\"]', NULL, NULL, '2026-03-04 15:24:28', '2026-03-04 15:24:28'),
(269, 'App\\Models\\User', 26, 'auth-token', 'f80af96fc9f8f8363aae94ce3f220b664776244dbcf1114ddf68f4b70902a3b6', '[\"*\"]', NULL, NULL, '2026-03-04 15:32:06', '2026-03-04 15:32:06'),
(270, 'App\\Models\\User', 20, 'auth-token', '2b3db74e674eb5cb7afde5a9f80a542936e1f5eb168990796328ecb3ac334d29', '[\"*\"]', NULL, NULL, '2026-03-04 15:34:51', '2026-03-04 15:34:51'),
(271, 'App\\Models\\User', 26, 'auth-token', 'd0721805d9aca43701c6713e411455984811074d6f949d943bf0e8d3c7f68c49', '[\"*\"]', NULL, NULL, '2026-03-04 15:35:22', '2026-03-04 15:35:22'),
(272, 'App\\Models\\User', 23, 'auth-token', 'd6f59732a5b74cd7c2c134a53a58d2ddaa28b6840881fd1b3bb25bcb9660f347', '[\"*\"]', NULL, NULL, '2026-03-04 15:41:32', '2026-03-04 15:41:32'),
(273, 'App\\Models\\User', 2, 'auth-token', 'f44f9950e4428f71275ca640121c10834f44258affb0759f03431e09a3a44556', '[\"*\"]', NULL, NULL, '2026-03-04 15:44:33', '2026-03-04 15:44:33'),
(274, 'App\\Models\\User', 23, 'auth-token', '658242e1ac59291ada91e0496dbe0d6642b6ce7dc412a08d442035729f5f7635', '[\"*\"]', NULL, NULL, '2026-03-04 15:45:46', '2026-03-04 15:45:46'),
(275, 'App\\Models\\User', 23, 'auth-token', 'cbf72781289d6a3bf19aba993842d5aa6c3a0d57fe68f8983666833d874e4c38', '[\"*\"]', NULL, NULL, '2026-03-04 15:49:03', '2026-03-04 15:49:03'),
(276, 'App\\Models\\User', 20, 'auth-token', '61d275453a6fc93e1c0fa73c33165402e0527f3dd5c3ade24af03e8e1ee1ee61', '[\"*\"]', NULL, NULL, '2026-03-04 15:49:27', '2026-03-04 15:49:27');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(277, 'App\\Models\\User', 2, 'auth-token', '08f30e376f8538155110d9497bfeecdd7da0274379433cb0f50d704ac4d6e2f3', '[\"*\"]', NULL, NULL, '2026-03-04 16:43:18', '2026-03-04 16:43:18'),
(278, 'App\\Models\\User', 26, 'auth-token', 'b46d908f0be4aa8b73f3c63efe2c152e6f574d4e6d7f437be27fb633ad7719ea', '[\"*\"]', NULL, NULL, '2026-03-04 16:45:47', '2026-03-04 16:45:47'),
(279, 'App\\Models\\User', 20, 'auth-token', '70bed5abaea5ccdb28bd68aff3a1daf1b7bce3d5d3b0e1c10cedfe1b463e3aa7', '[\"*\"]', NULL, NULL, '2026-03-04 16:50:32', '2026-03-04 16:50:32'),
(280, 'App\\Models\\User', 2, 'auth-token', '2202e41f7032ff74f26b7df771321e44decf0a4121084c9b921b965f8d15cae4', '[\"*\"]', NULL, NULL, '2026-03-04 16:51:25', '2026-03-04 16:51:25'),
(281, 'App\\Models\\User', 26, 'auth-token', 'fda84b7377ae0b9a97a0ccf052f293ef6f9212d328d932dfefd3fdf953b57fb4', '[\"*\"]', NULL, NULL, '2026-03-04 16:57:13', '2026-03-04 16:57:13'),
(282, 'App\\Models\\User', 2, 'auth-token', '34ceebb1e6c67d891a5dc945794466f23b25b7d74bc6f6f523fb6afcf74d8e99', '[\"*\"]', NULL, NULL, '2026-03-04 16:57:41', '2026-03-04 16:57:41'),
(283, 'App\\Models\\User', 20, 'auth-token', '92e9d33bcab79d970bcf8abf9cdda43557bcca23e1f365c6c3576f23bbc058c3', '[\"*\"]', NULL, NULL, '2026-03-04 16:58:12', '2026-03-04 16:58:12'),
(284, 'App\\Models\\User', 26, 'auth-token', '713badc3b327eaf7d6e4db686bc9a9d17302c63880e3ca0ce836d2e0f0befa30', '[\"*\"]', NULL, NULL, '2026-03-04 17:55:30', '2026-03-04 17:55:30'),
(285, 'App\\Models\\User', 26, 'auth-token', '26a90aca25957ff52704b8e766d8c56ac3ba0fa0b7a11a1552316d72a1c5bb05', '[\"*\"]', NULL, NULL, '2026-03-04 17:56:08', '2026-03-04 17:56:08'),
(286, 'App\\Models\\User', 23, 'auth-token', 'a678ef3ba51a3d79546039d61fd4fecd670d222c3d89ce4cfc1f07b7facfafa3', '[\"*\"]', NULL, NULL, '2026-03-04 18:01:34', '2026-03-04 18:01:34'),
(287, 'App\\Models\\User', 27, 'auth-token', 'ccf49eccff1e35c4b70dea8d8b7ee973fd983f0a65f45a09aa68b3f4d986a5c8', '[\"*\"]', NULL, NULL, '2026-03-04 18:11:17', '2026-03-04 18:11:17'),
(288, 'App\\Models\\User', 27, 'auth-token', '0625566bba330d8850e6160a5ed6c8e3a56568173ce96219cddeaa41549f8130', '[\"*\"]', NULL, NULL, '2026-03-04 18:11:39', '2026-03-04 18:11:39'),
(289, 'App\\Models\\User', 27, 'auth-token', 'a22674517af03c7434ed8f5fd2d8ee6ac4f272cd2e268aa1a548b45a0b49df39', '[\"*\"]', NULL, NULL, '2026-03-04 18:16:11', '2026-03-04 18:16:11'),
(290, 'App\\Models\\User', 27, 'auth-token', '1917d4da660f170935b8d066b959da3c3ef450a1f5978c8d4ceb39ab6359401b', '[\"*\"]', NULL, NULL, '2026-03-04 18:16:37', '2026-03-04 18:16:37'),
(291, 'App\\Models\\User', 2, 'auth-token', 'd93faa80e8c651280b6a8a020fec0ff58cadba2312e21a41ea3124661c2cef3f', '[\"*\"]', NULL, NULL, '2026-03-04 18:37:04', '2026-03-04 18:37:04'),
(292, 'App\\Models\\User', 20, 'auth-token', 'd5c267d665d56b34809a92fecd8bff9708e6bd91b0aff686f82b43655c6c9efc', '[\"*\"]', NULL, NULL, '2026-03-04 18:38:00', '2026-03-04 18:38:00'),
(293, 'App\\Models\\User', 20, 'auth-token', 'd160951558e69e2d65451282179776a91ba60980af4dda4098865e3b3e872010', '[\"*\"]', NULL, NULL, '2026-03-05 06:15:13', '2026-03-05 06:15:13'),
(294, 'App\\Models\\User', 23, 'auth-token', '9192b403d6b1c0016997d10987a7338f98ad5c9bf3270a82b040d2851873873b', '[\"*\"]', NULL, NULL, '2026-03-05 06:16:45', '2026-03-05 06:16:45'),
(295, 'App\\Models\\User', 26, 'auth-token', '8eadebaa1eb12ea7915e4d38c456419d39537644d101f4e462ad59fc71a38b9c', '[\"*\"]', NULL, NULL, '2026-03-05 06:19:24', '2026-03-05 06:19:24'),
(296, 'App\\Models\\User', 22, 'auth-token', '27d86a1759f1df92ab4bb0d53a256a346acdbeb130a0f62ec3d1280cd2c9a7a9', '[\"*\"]', NULL, NULL, '2026-03-05 06:20:02', '2026-03-05 06:20:02'),
(297, 'App\\Models\\User', 2, 'auth-token', '96e9145acef69df8cd2b942dde94769137bcb124e41c10332d3e37e55e8de168', '[\"*\"]', NULL, NULL, '2026-03-05 06:22:01', '2026-03-05 06:22:01'),
(298, 'App\\Models\\User', 2, 'auth-token', '07628a22bd4ab890d4b7ee181fb9e8a37dbc1ca05efb3f092098560858d351a2', '[\"*\"]', NULL, NULL, '2026-03-05 07:57:55', '2026-03-05 07:57:55'),
(299, 'App\\Models\\User', 20, 'auth-token', 'c158ff213c237f0df4d14dc560fee408cc75611d9551b3c5d707da54469f9eb9', '[\"*\"]', NULL, NULL, '2026-03-05 07:58:52', '2026-03-05 07:58:52'),
(300, 'App\\Models\\User', 26, 'auth-token', '688e325bdd8d846d59d0e317b91631759d94d1e91e1852c4c59290b138e43957', '[\"*\"]', NULL, NULL, '2026-03-05 09:18:44', '2026-03-05 09:18:44'),
(301, 'App\\Models\\User', 26, 'auth-token', 'b81fe73d089e7338f80bc4182bee438e24d8dc8b50ad3817700883514ea50d5a', '[\"*\"]', NULL, NULL, '2026-03-05 09:23:17', '2026-03-05 09:23:17'),
(302, 'App\\Models\\User', 23, 'auth-token', 'e6df75b463fc62d59304aaa5860a2d9af3b789b68e9e1994a56d587a9fc5a48a', '[\"*\"]', NULL, NULL, '2026-03-05 09:23:30', '2026-03-05 09:23:30'),
(303, 'App\\Models\\User', 2, 'auth-token', 'a2c56b4f2503dd8f4c5c3803ef4509fce3f159d114e3a60e5de5ab0d16fbcea3', '[\"*\"]', NULL, NULL, '2026-03-05 14:44:20', '2026-03-05 14:44:20'),
(304, 'App\\Models\\User', 20, 'auth-token', 'e6d464362fa8c19929249ee4e1d75a919047a46810cea8d672445abee40402a3', '[\"*\"]', NULL, NULL, '2026-03-05 14:45:01', '2026-03-05 14:45:01'),
(305, 'App\\Models\\User', 28, 'auth-token', 'aff49acacacd431ad142cfb2d00aa821dfa3cb383cdf04bd7c43b57fedede504', '[\"*\"]', NULL, NULL, '2026-03-05 15:25:40', '2026-03-05 15:25:40'),
(306, 'App\\Models\\User', 28, 'auth-token', '686efc0199a83f7ad35c159287fafd3a1c02dc09681be16e057ecd51a1ed0598', '[\"*\"]', NULL, NULL, '2026-03-05 15:42:13', '2026-03-05 15:42:13'),
(307, 'App\\Models\\User', 28, 'auth-token', 'cd7e87e6e95f3153b444b269d4c665a5ab25b45073c929296c95d8aae7e4c220', '[\"*\"]', NULL, NULL, '2026-03-05 15:44:00', '2026-03-05 15:44:00'),
(308, 'App\\Models\\User', 28, 'auth-token', '410d4238b783922daa046f39625396c9841e1064ac9ed406755d84b5d8566e0a', '[\"*\"]', NULL, NULL, '2026-03-05 15:44:16', '2026-03-05 15:44:16'),
(309, 'App\\Models\\User', 28, 'auth-token', '6096a2131e27347ef069a6abaab95913797caa34279296a02c98fb393e4ddfa6', '[\"*\"]', NULL, NULL, '2026-03-05 15:46:42', '2026-03-05 15:46:42'),
(310, 'App\\Models\\User', 28, 'auth-token', '110f37a23176b51a5080dc060e3595b104c1c617902b8470e99e40fee48d1fd4', '[\"*\"]', NULL, NULL, '2026-03-05 15:46:57', '2026-03-05 15:46:57'),
(311, 'App\\Models\\User', 28, 'auth-token', 'ca4820257071b47b00515f69473a5dc0906df95aef2274aac3ad9a935a4cbdae', '[\"*\"]', NULL, NULL, '2026-03-05 15:59:15', '2026-03-05 15:59:15'),
(312, 'App\\Models\\User', 27, 'auth-token', '2a9e7890711ef15cfe329b0f72d5874aa6646468d1248fd35d8cb74197efb981', '[\"*\"]', NULL, NULL, '2026-03-05 15:59:59', '2026-03-05 15:59:59'),
(313, 'App\\Models\\User', 28, 'auth-token', 'fb92558f3d130084baa3aa3b56d6e19fdb498ffefe67471ec383b694c2a8d7fd', '[\"*\"]', NULL, NULL, '2026-03-05 16:00:15', '2026-03-05 16:00:15'),
(314, 'App\\Models\\User', 28, 'auth-token', 'f56492fd2da05b3f2f48f5935c03f7f750256a6461371c46491f96a3ee973668', '[\"*\"]', NULL, NULL, '2026-03-05 16:14:29', '2026-03-05 16:14:29'),
(315, 'App\\Models\\User', 28, 'auth-token', 'e35dc24f07c74bbca4ca880988e87cec9c3bc0aeac57e6adbd795e536ac3ef08', '[\"*\"]', NULL, NULL, '2026-03-05 17:06:06', '2026-03-05 17:06:06'),
(316, 'App\\Models\\User', 28, 'auth-token', '6fa03fdf1483f1d55c3ccc0cfa33a734dc311e2cbeb893f1ad60844edbce1e00', '[\"*\"]', NULL, NULL, '2026-03-05 17:06:15', '2026-03-05 17:06:15'),
(317, 'App\\Models\\User', 28, 'auth-token', '83223d8b0dd2a813d242de80bfc9e9a5108990c6dbe57778cbb522899df0bf4c', '[\"*\"]', NULL, NULL, '2026-03-05 17:10:00', '2026-03-05 17:10:00'),
(318, 'App\\Models\\User', 28, 'auth-token', '542125c4fdd2cb538578520400efcaaed57e558bff74a94969306a71d9664600', '[\"*\"]', NULL, NULL, '2026-03-05 17:29:16', '2026-03-05 17:29:16'),
(319, 'App\\Models\\User', 28, 'auth-token', 'a4a15d246c303064e851824a71f645f352048b1c9292f0f54be236a3546dcdd9', '[\"*\"]', NULL, NULL, '2026-03-05 17:44:28', '2026-03-05 17:44:28'),
(320, 'App\\Models\\User', 28, 'auth-token', 'f0f345a6cc37cad7ff5005e61ba707e6db1af570e0bd968a0fd73273d63f8de7', '[\"*\"]', NULL, NULL, '2026-03-05 18:16:48', '2026-03-05 18:16:48'),
(321, 'App\\Models\\User', 28, 'auth-token', 'c1c48cebb31048e8fd577fe12bb6a7b2bff6bb47a46bdb4cca1a30f7d55d11dc', '[\"*\"]', NULL, NULL, '2026-03-05 18:44:44', '2026-03-05 18:44:44'),
(322, 'App\\Models\\User', 28, 'auth-token', '0d5790cfbb75dacc4b1d8c6d29b500d63d1b33109616e99f23e7b4ceace1897c', '[\"*\"]', NULL, NULL, '2026-03-05 18:50:30', '2026-03-05 18:50:30'),
(323, 'App\\Models\\User', 28, 'auth-token', 'e4f97abd4a12785cd26dd4be2f6c3bcfc5e8e812fa412b09ce0ae4eaa7d5404f', '[\"*\"]', NULL, NULL, '2026-03-05 19:09:24', '2026-03-05 19:09:24'),
(324, 'App\\Models\\User', 28, 'auth-token', '5ff94c2eead62a9326fb3910260b5acb1f26c9bd8f62874680056baf918d1c89', '[\"*\"]', NULL, NULL, '2026-03-05 19:36:48', '2026-03-05 19:36:48'),
(325, 'App\\Models\\User', 22, 'auth-token', 'ade65caa3a04f6790592663b5781d5e9b23ff179ff6ae850a592bbdfc5a2df59', '[\"*\"]', NULL, NULL, '2026-03-05 19:49:42', '2026-03-05 19:49:42'),
(326, 'App\\Models\\User', 2, 'auth-token', 'b12a484c6c38a29b972555a02f842b658d5de1a3b20d5f3ea7e58ce5c62f6d1e', '[\"*\"]', NULL, NULL, '2026-03-05 19:50:28', '2026-03-05 19:50:28'),
(327, 'App\\Models\\User', 28, 'auth-token', 'f68bdba2e661d247a084d5e8f3b5deb6357bde09583d045299264293e4c8a05a', '[\"*\"]', NULL, NULL, '2026-03-05 19:50:39', '2026-03-05 19:50:39'),
(328, 'App\\Models\\User', 22, 'auth-token', '799686993aa092ad80d2a98a43a5c1be3a8db892d907a57cf93740b39de7e18c', '[\"*\"]', NULL, NULL, '2026-03-05 19:51:37', '2026-03-05 19:51:37'),
(329, 'App\\Models\\User', 28, 'auth-token', '24d8698232ef15abc8f3ea3e0bce6435b08406b52ee7682d1be1bff36c5cc90b', '[\"*\"]', NULL, NULL, '2026-03-05 20:07:17', '2026-03-05 20:07:17'),
(330, 'App\\Models\\User', 28, 'auth-token', '54b49a389d1f9bdc6b2df7550b69c6ac384f0c43bf5f056bcfcec9b71c5c39c0', '[\"*\"]', NULL, NULL, '2026-03-05 20:09:40', '2026-03-05 20:09:40'),
(331, 'App\\Models\\User', 22, 'auth-token', 'baf238881c4ef5a8d8cf04d50536e60cd54edaf2c9c68e5d0e144d54744ed1f9', '[\"*\"]', NULL, NULL, '2026-03-05 20:10:07', '2026-03-05 20:10:07'),
(332, 'App\\Models\\User', 28, 'auth-token', '7be4350c4264391e51be4415ca5c1e737cce5a563d85bef5e2fb725c6e5515af', '[\"*\"]', NULL, NULL, '2026-03-05 20:15:18', '2026-03-05 20:15:18'),
(333, 'App\\Models\\User', 28, 'auth-token', '9143c9776b5f6f4b888e04813429f39399f8047e7865851fee237bd1f36dba5a', '[\"*\"]', NULL, NULL, '2026-03-05 21:51:08', '2026-03-05 21:51:08'),
(334, 'App\\Models\\User', 28, 'auth-token', '7d3bd78ab2c41a28855fa16d29a4b027da5a8949903c53ad4e7d2ce5fb0ac919', '[\"*\"]', NULL, NULL, '2026-03-06 00:14:06', '2026-03-06 00:14:06'),
(335, 'App\\Models\\User', 28, 'auth-token', 'e09c376692c6d30f1cff0da30066a33abd3b863113ad9fceb26f5f3d5776390b', '[\"*\"]', NULL, NULL, '2026-03-06 02:55:38', '2026-03-06 02:55:38'),
(336, 'App\\Models\\User', 22, 'auth-token', '2ed8446244d75ecec2402a970f2e611e40f5954e6beaad4c0eda27ae7367b298', '[\"*\"]', NULL, NULL, '2026-03-06 02:56:02', '2026-03-06 02:56:02'),
(337, 'App\\Models\\User', 20, 'auth-token', 'a6beb50bc904d71814a686eef0d45db1a2161b2cc6c1d3f6bb5c72fa9cf552b8', '[\"*\"]', NULL, NULL, '2026-03-06 02:56:47', '2026-03-06 02:56:47'),
(338, 'App\\Models\\User', 28, 'auth-token', 'fde51c12f6d4c7cd20dba847eb545440c6fccf5f889a04015c69bca180a11510', '[\"*\"]', NULL, NULL, '2026-03-06 11:43:23', '2026-03-06 11:43:23'),
(339, 'App\\Models\\User', 28, 'auth-token', '17f619bbfbb5a586bb2be20f0280d6db883f95708b9b5c852f9c4922478520ea', '[\"*\"]', NULL, NULL, '2026-03-06 12:20:44', '2026-03-06 12:20:44'),
(340, 'App\\Models\\User', 28, 'auth-token', '7acb79d8dc72b4b43414bd1e12be7d9eaef5cd78b1d5ed06e1a79a9add8095c0', '[\"*\"]', NULL, NULL, '2026-03-07 04:29:25', '2026-03-07 04:29:25'),
(341, 'App\\Models\\User', 31, 'auth-token', '587c7191663a9ef088fc824125a94856b5f00c71f42987c4658de454f95d60f9', '[\"*\"]', NULL, NULL, '2026-03-07 04:32:33', '2026-03-07 04:32:33'),
(342, 'App\\Models\\User', 31, 'auth-token', 'dbf77da356a6f42ca436d2060133b7c30ccffd8681c7d80ae4f77710c679c082', '[\"*\"]', NULL, NULL, '2026-03-07 07:01:15', '2026-03-07 07:01:15'),
(343, 'App\\Models\\User', 28, 'auth-token', '0ff064d36d11e0d0d2d194de99193d07b7de405f5def56927b5ce924cb957a80', '[\"*\"]', NULL, NULL, '2026-03-07 07:16:34', '2026-03-07 07:16:34'),
(344, 'App\\Models\\User', 31, 'auth-token', '31470d59b00741fd8a044b8f02ad97ba45d52188cb9a8ecdd340647c2c82e6c0', '[\"*\"]', NULL, NULL, '2026-03-07 07:17:26', '2026-03-07 07:17:26'),
(345, 'App\\Models\\User', 31, 'auth-token', '7b882baa47e28f49a4b1bacbe58ebe45d19939d7ef2bec34b451d57be1f07436', '[\"*\"]', NULL, NULL, '2026-03-07 07:32:13', '2026-03-07 07:32:13'),
(346, 'App\\Models\\User', 31, 'auth-token', 'bf9acca8caf39ceb74e880c0939d7fa1c3bbf14bf0b418cff74fc0d89f3131f9', '[\"*\"]', NULL, NULL, '2026-03-07 07:38:52', '2026-03-07 07:38:52'),
(347, 'App\\Models\\User', 28, 'auth-token', '714e0b4147b3e080b62e0a6d57f968805e4959bbed7df735b9c84ebce95506c6', '[\"*\"]', NULL, NULL, '2026-03-07 07:54:34', '2026-03-07 07:54:34'),
(348, 'App\\Models\\User', 28, 'auth-token', 'cc209504b9c3782df058834863676614e489891a7a8701ea78b526422d4a7968', '[\"*\"]', NULL, NULL, '2026-03-07 07:54:45', '2026-03-07 07:54:45'),
(349, 'App\\Models\\User', 31, 'auth-token', '878c068e7f39067b4d75d3aacb0203d4949fa986d99e1a1f0f467facf6a5280b', '[\"*\"]', NULL, NULL, '2026-03-07 07:59:21', '2026-03-07 07:59:21'),
(350, 'App\\Models\\User', 28, 'auth-token', 'fbadf33425db92cfc05d04bc1c48e0edbd9d5cd572bafbf1ec5de398e5f68529', '[\"*\"]', NULL, NULL, '2026-03-07 08:02:52', '2026-03-07 08:02:52'),
(351, 'App\\Models\\User', 31, 'auth-token', 'c783132ee8a5d545214d73d7b90121bc39f0de080c386a9d19dc3a710e9cfe56', '[\"*\"]', NULL, NULL, '2026-03-07 08:23:46', '2026-03-07 08:23:46'),
(352, 'App\\Models\\User', 28, 'auth-token', '57c8c447bde7624644bc3067cab123eee34d1f53c28a9ab95dceaef766188c3f', '[\"*\"]', NULL, NULL, '2026-03-09 04:58:16', '2026-03-09 04:58:16'),
(353, 'App\\Models\\User', 28, 'auth-token', '8806446eec5b94a0c9350b7f0ffc618cd26fe752f691d59860f7dc9e73b881a7', '[\"*\"]', NULL, NULL, '2026-03-09 05:57:48', '2026-03-09 05:57:48'),
(354, 'App\\Models\\User', 42, 'auth-token', 'e3c69e8a6faf1518a6886091e10c702a9b358c6cf103fe9743be368db7568051', '[\"*\"]', NULL, NULL, '2026-03-09 06:02:45', '2026-03-09 06:02:45'),
(355, 'App\\Models\\User', 42, 'auth-token', 'd19f183bd6cf546eafa2c7e1e00bd7a829180a2f348eed098592ac0029351554', '[\"*\"]', NULL, NULL, '2026-03-09 06:05:05', '2026-03-09 06:05:05'),
(356, 'App\\Models\\User', 28, 'auth-token', '656cc1c707fe277394b013c671f54f9dd0ce69444278a083054ca77a637bb403', '[\"*\"]', NULL, NULL, '2026-03-09 07:04:16', '2026-03-09 07:04:16'),
(357, 'App\\Models\\User', 31, 'auth-token', 'e3a4d7d3a98aae07b08fe2f11cb4a4b35d2272cbf82ce2cb7fb37c909e9eece1', '[\"*\"]', NULL, NULL, '2026-03-09 07:04:35', '2026-03-09 07:04:35'),
(358, 'App\\Models\\User', 28, 'auth-token', '4e7ad5801e059085f2d42386a219d714ef14f6daae8118134720212aa80efc8a', '[\"*\"]', NULL, NULL, '2026-03-09 07:14:17', '2026-03-09 07:14:17'),
(359, 'App\\Models\\User', 31, 'auth-token', '4737f76593adb67a9bfcc5cc179c0156c18798fe2d7a52691311636ae2eb97fe', '[\"*\"]', NULL, NULL, '2026-03-09 07:15:04', '2026-03-09 07:15:04'),
(360, 'App\\Models\\User', 43, 'auth-token', '6898ab8a1706e50d5741f978769f9adf19bd336e1d17cde9c437d59de8711884', '[\"*\"]', NULL, NULL, '2026-03-09 07:15:41', '2026-03-09 07:15:41'),
(361, 'App\\Models\\User', 43, 'auth-token', 'f0592bc7383de5d2df2bf503b0199353358e4e04eb8b1a0ca03f57f7db654e51', '[\"*\"]', NULL, NULL, '2026-03-09 07:16:55', '2026-03-09 07:16:55'),
(362, 'App\\Models\\User', 28, 'auth-token', 'da777e0bc6611ebd44e56f2c8abc7820693c96b27040fe480859c0619155aca7', '[\"*\"]', NULL, NULL, '2026-03-09 07:17:32', '2026-03-09 07:17:32'),
(363, 'App\\Models\\User', 43, 'auth-token', '7c341743d19498255fa5c8ff7f6e46b13f378a37feed02b6546b0a27542a2142', '[\"*\"]', NULL, NULL, '2026-03-09 07:37:28', '2026-03-09 07:37:28');

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
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `sku` varchar(255) NOT NULL DEFAULT 'SKU-DEFAULT',
  `branch_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
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
('sMwLqasTVTmtyzfDYsK8ebCxkzrbdWwUFrXIQHNY', 43, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiaHBVUHdsMzlvZFJxVWJKallUNVFqeTZWQVpEd2w2T3h1c0JZSWhwUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDM7czo3OiJ1c2VyX2lkIjtpOjQzO3M6OToidXNlcl9yb2xlIjtzOjc6Ik1BTkFHRVIiO3M6OToidXNlcl9uYW1lIjtzOjMzOiJIUiBNYW5hZ2VyIC0gVGFnYXl0YXkgQ2l0eSBCcmFuY2giO3M6MTM6InJlZGlyZWN0X3BhdGgiO3M6MTE6Ii9tYW5hZ2VyL2hyIjt9', 1773041868);

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
(28, 'superadmin@example.com', 'superadmin', 'Super Administrators', NULL, '$2y$12$f67akYY/xm/H9KJoqytmXeblCxSgZ786slMmHkmqzlMozGUn3Ew7G', '2026-03-05 15:03:55', 'SUPER_ADMIN', NULL, NULL, NULL, '2026-03-05 15:03:55', '2026-03-05 16:01:06', NULL, 0, 1, NULL, '/storage/avatars/hkt6q9sq1yYpnTGkMyayeLp3bmIjf5hPNM2LnCBo.png', NULL),
(29, 'umbal.christiancharles@ncst.edu.ph', 'umballs', NULL, NULL, '$2y$12$05J2guUFR9PN1TuRwlF0WuRcRx2CyKeVChVKwIORgl.IMiK.fqsQC', '2026-03-05 20:00:52', 'customer', NULL, NULL, NULL, '2026-03-05 20:00:52', '2026-03-05 20:00:52', NULL, 1, 1, NULL, NULL, NULL),
(31, 'admin@chikintayo.com', 'Parks', 'Mr.parks', NULL, '$2y$12$/jjxezfu4JAW55dvduVkVu7hpmk5CBXg2GWKtlT17A8jEMfJFpY8y', NULL, 'OWNER', NULL, NULL, NULL, '2026-03-07 04:30:58', '2026-03-07 04:30:58', NULL, 0, 1, NULL, NULL, 'admin'),
(42, 'admin_br886318@chikintayo.com', 'admin_br886318', 'Admin - Tagaytay City Branch', NULL, '$2y$12$kFMyDbpWuvy7AC7vNHcBAuZxyhNLkAu6TiHcWxhGCfIPDyRfsSksS', NULL, 'ADMIN', 11, NULL, NULL, '2026-03-09 06:01:42', '2026-03-09 06:04:59', NULL, 0, 1, NULL, NULL, NULL),
(43, 'hr_br886318@chikintayo.com', 'hr_br886318', 'HR Manager - Tagaytay City Branch', NULL, '$2y$12$rC8ZEB9LSmRKgk/GXbtNluExst.AbNmebrAgKaPW6vg2LeeHlYli.', NULL, 'MANAGER', 11, 'HR', NULL, '2026-03-09 06:01:42', '2026-03-09 07:16:51', NULL, 0, 1, NULL, NULL, NULL);

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
-- Indexes for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_settings_branch_id_unique` (`branch_id`);

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
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  ADD CONSTRAINT `attendance_settings_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD CONSTRAINT `customer_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

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
