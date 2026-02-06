-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2026 at 01:48 AM
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

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `time_in`, `time_out`, `hours_worked`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(7, 26, '2026-02-05', '14:16:04', '16:03:56', -108, 'late', NULL, '2026-02-05 06:16:04', '2026-02-05 08:03:56'),
(8, 28, '2026-02-05', '14:51:05', '15:57:49', -67, 'late', NULL, '2026-02-05 06:51:05', '2026-02-05 07:57:49'),
(9, 30, '2026-02-05', '16:58:08', '16:58:29', 0, 'late', NULL, '2026-02-05 08:58:08', '2026-02-05 08:58:29'),
(10, 35, '2026-02-05', '21:49:35', '21:49:39', 0, 'late', NULL, '2026-02-05 13:49:35', '2026-02-05 13:49:39'),
(11, 30, '2026-02-06', '03:57:58', '04:16:24', -18, 'present', NULL, '2026-02-05 19:57:58', '2026-02-05 20:16:24'),
(12, 26, '2026-02-06', '04:16:45', '04:17:20', -1, 'present', NULL, '2026-02-05 20:16:45', '2026-02-05 20:17:20'),
(13, 28, '2026-02-06', '08:24:47', NULL, 0, 'present', NULL, '2026-02-06 00:24:47', '2026-02-06 00:24:47');

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

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1770338277),
('laravel-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1770338277;', 1770338277),
('laravel-cache-887309d048beef83ad3eabf2a79a64a389ab1c9f', 'i:1;', 1770334572),
('laravel-cache-887309d048beef83ad3eabf2a79a64a389ab1c9f:timer', 'i:1770334572;', 1770334572);

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
(7, '2026_01_13_165546_add_soft_deletes_to_users_table\r\n', 5),
(8, '2019_12_14_000001_create_personal_access_tokens_table', 6),
(9, '2026_01_19_141306_create_password_resets_table', 7),
(10, '2026_01_20_162500_add_remember_token_to_users_table', 8),
(11, '2026_01_20_170100_add_password_column_and_copy_hash', 9),
(12, '2026_01_30_130000_change_users_role_to_string', 10),
(13, '2026_02_02_120000_add_must_change_password_to_users_table', 11),
(14, '2026_02_04_000000_create_attendance_table', 12),
(15, '2026_02_05_210000_create_staff_documents_table', 13),
(16, '2026_02_05_230000_make_staff_documents_nullable', 14),
(17, '2026_02_06_120000_create_products_table', 15),
(18, '2026_02_06_120100_create_product_comments_table', 16),
(19, '2026_02_06_130000_add_rating_to_product_comments_table', 17),
(20, '2026_02_06_073143_add_parent_comment_id_to_product_comments_table', 18),
(21, '2026_02_06_073404_make_rating_nullable_in_product_comments_table', 19);

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

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('calicamarkjulius@gmail.com', '$2y$12$uEC/xyBKkxp3OzKAVCISw.0DfXu/G.pdMKpKmCk9BpFQi/DCM7sGG', '2026-01-22 00:08:05'),
('onggab54@gmail.com', '$2y$12$i08mBn/xtVnqlEhy7osAr.gyA8VIhfT8c5ap2zWLpFZhzXCxwvDpG', '2026-01-22 01:11:44'),
('umbal.christiancharles@ncst.edu.ph', '$2y$12$3tlbaHkeiGMusc7TTDIF2OP40RLhZcbdeGvXek.gOhU/dneP5sTqS', '2026-01-19 21:31:35');

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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Yangyeom', 'yangyeom', '2026-02-05 20:21:04', '2026-02-05 20:21:04'),
(2, 'Snow Cheese', 'snowcheese', '2026-02-05 20:21:04', '2026-02-05 20:21:04'),
(3, 'Corndog', 'corndog', '2026-02-05 20:21:04', '2026-02-05 20:21:04'),
(4, 'Pastries', 'pastries', '2026-02-05 20:21:04', '2026-02-05 20:21:04'),
(5, 'Ramen', 'ramen', '2026-02-05 20:21:04', '2026-02-05 20:21:04'),
(6, 'Ice Cream', 'icecream', '2026-02-05 20:21:04', '2026-02-05 20:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `product_comments`
--

CREATE TABLE `product_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `parent_comment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `author` varchar(60) NOT NULL,
  `text` text NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `parent_comment_id`, `author`, `text`, `rating`, `ip_address`, `created_at`, `updated_at`) VALUES
(5, 2, NULL, 'Kristel Joy Cabugayan', 'Grabe! Ang sarap netong Snow Cheese nilaa. Babalikan talaga mga dzai!', 5, '127.0.0.1', '2026-02-05 20:32:02', '2026-02-05 20:32:02'),
(6, 1, NULL, 'Vince Bido', 'Goods na goods to mga man, eto lagi order ko. HAHAHAHA', 5, '127.0.0.1', '2026-02-05 20:37:35', '2026-02-05 20:37:35'),
(7, 3, NULL, 'Christian Charles Umbal', 'Okay naman, medyo mahirap lang kainin kase malaki HAHAH pero goods na goods yung lasa nya.', 4, '127.0.0.1', '2026-02-05 20:38:16', '2026-02-05 20:38:16'),
(8, 4, NULL, 'Gabriel Ongsip', 'Okay mga pastriess nila so far, pero lungkowts madalang nalang may available na pastry. Madalas wala talaga. Sana mabalik tooo :(((', 5, '127.0.0.1', '2026-02-05 20:39:44', '2026-02-05 20:39:44'),
(9, 5, NULL, 'Mari Janne', 'Fav ko dito CHEESE RAMEN!!! HAHAHA balik ako after class hehe.', 5, '127.0.0.1', '2026-02-05 20:41:21', '2026-02-05 20:41:21'),
(10, 6, NULL, 'Vince Bido', 'Kakatikim ko lang neto kanina, okay dinnn sya kainin ket walang kain HAHAHAHAHAH', 5, '127.0.0.1', '2026-02-05 20:47:42', '2026-02-05 20:47:42'),
(11, 1, NULL, 'Mark Julius Calica', 'Goods pare, maluto nga \'to sa bahay. HA HA HA HA!', 5, '127.0.0.1', '2026-02-05 20:48:32', '2026-02-05 20:48:32'),
(12, 5, NULL, 'Paul Solancho', 'Eto talaga dabes, pag walang budget man.', 5, '127.0.0.1', '2026-02-05 21:10:13', '2026-02-05 21:10:13'),
(13, 2, NULL, 'Maxine Andrales', 'Fav ko to.', 5, '127.0.0.1', '2026-02-05 21:10:50', '2026-02-05 21:10:50'),
(14, 4, NULL, 'Andra Villanueva', 'Namimiss ko natooo! :((', 5, '127.0.0.1', '2026-02-05 21:11:48', '2026-02-05 21:11:48'),
(15, 1, NULL, 'Gabriel Ongsip', 'May favvv!!!😋', 5, '127.0.0.1', '2026-02-05 21:24:43', '2026-02-05 21:24:43'),
(16, 3, NULL, 'Paul Solancho', 'Natikman ko nato, eto yung pinaka sakto lang sa menu nila HAHAHA.😇❣️', 5, '127.0.0.1', '2026-02-05 22:45:43', '2026-02-05 22:45:43'),
(17, 2, 5, 'Vince Bido', 'Sige kain tayo ulet jan.', NULL, '127.0.0.1', '2026-02-05 23:35:12', '2026-02-05 23:35:12'),
(18, 5, 12, 'Vince Bido', 'Arat na pol.', NULL, '127.0.0.1', '2026-02-05 23:57:45', '2026-02-05 23:57:45'),
(19, 6, NULL, 'Gab Ongsip', 'Ice Cream Chilleen!😍', 5, '127.0.0.1', '2026-02-06 00:36:57', '2026-02-06 00:36:57'),
(20, 6, 19, 'Vince Bido', 'Ang ganda ko!🥰', NULL, '127.0.0.1', '2026-02-06 00:37:48', '2026-02-06 00:37:48');

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
('0lRdebjUDbjkqc7PiI2L5ttt1tZZ88PvHphsHZZX', 26, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSGJmWEE5V2F1bkl6NlFtNE05TE5XZmNVV3FwcTJzblJSM0ZxbjU2aSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NzA6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC8ud2VsbC1rbm93bi9hcHBzcGVjaWZpYy9jb20uY2hyb21lLmRldnRvb2xzLmpzb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI2O30=', 1770266234);

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
(1, 36, 'staff-documents/36/resume.png', 'staff-documents/36/government_id.png', 'staff-documents/36/psa_birth_certificate.png', 'staff-documents/36/nbi_clearance.png', 'staff-documents/36/police_clearance.png', 'staff-documents/36/medical_certificate.png', 'staff-documents/36/drug_test_result.png', 'staff-documents/36/sss_id.png', 'staff-documents/36/philhealth_id.png', 'staff-documents/36/pagibig_mdf.png', 'staff-documents/36/tin_id.png', 'staff-documents/36/diploma_transcript.jpg', '2026-02-05 14:14:53', '2026-02-05 14:33:16'),
(2, 30, 'staff-documents/30/resume.jpg', 'staff-documents/30/government_id.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 14:34:09', '2026-02-05 23:41:34'),
(3, 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 14:40:46', '2026-02-05 14:40:49'),
(4, 26, NULL, NULL, NULL, NULL, 'staff-documents/26/police_clearance.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-05 14:41:14', '2026-02-05 14:41:14');

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
  `role` varchar(50) NOT NULL DEFAULT 'STAFF',
  `avatarUrl` varchar(255) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `avatarUrl`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `created_at`, `updated_at`, `deleted_at`, `remember_token`, `password`, `must_change_password`) VALUES
(11, 'owner_admin', 'calicamarkjulius@gmail.com', '$2y$12$0UbstBBBR2nY9p9ZANRp1OxlBwWoZeZ/9Zx7WuDERrtMbrq1V/auy', 'System Administrato', 'OWNER', NULL, NULL, '/storage/avatars/avatar_11_1770286787.jpg', '09123456789', 'Main Office', 1, '2026-01-13 16:05:28', '2026-02-05 10:19:47', NULL, 'lPthjWkCI5hI8qrTo4E5T4KwKp5h71FQPVylSOXwf3DBa1Os86WFnEgfNw7I', '$2y$12$sFbzg7dPLe0R/gYGAzVSjew8oDu8YcxgJ1O5f6x2dC7GgmUmlJNca', 0),
(26, 'Paul Berrer', 'paul@gmail.com', '$2y$12$oF3pFH7PntX8XLGkAxwPJunUq7EHP4Gb7Fo6xZqXvXM6cUNkNdlfS', 'GABRIEL ONGSIP', 'BRANCH_MANAGER', NULL, 1, '/storage/avatars/avatar_26_1770278620.jpg', '09099628117', 'Dito lang din sa TEXAS magkabilang bahay lang kami ni undertaker.', 1, '2026-01-29 11:21:45', '2026-02-05 20:17:10', NULL, NULL, NULL, 0),
(28, 'Gabby', 'gabby@gmail.com', '$2y$12$660MZKQoGT/IGQzw8BNDd.jB3pxsGcrY09qhKFOV1Yf7TuL05KOLW', 'Gabriel Louis Ongsips', 'STAFF', NULL, 1, '/storage/avatars/avatar_28_1770272964.jpg', '09295426078', 'Summer Wind, Villa Isabel', 1, '2026-01-29 13:56:44', '2026-02-05 10:16:45', NULL, NULL, NULL, 0),
(30, 'vincetae', 'vincetae@gmail.com', '$2y$12$kRQezyFsgxmx4i5NHdynw.n1ak1ZyotH4t.TGyZhTILw.spgChdCa', 'I\'m the HR', 'HR', NULL, 1, '/storage/avatars/avatar_30_1770286745.jpg', '09123456783', NULL, 1, '2026-01-29 22:20:38', '2026-02-05 10:19:05', NULL, NULL, NULL, 0),
(33, 'charles_bm', 'christianumbal12@gmail.com', '$2y$12$EeJiLxJphtI/EhI6ZZZMROYlcPYc.Unu9IYTjZobJYLRLoOPUcx42', 'Christian Charles Umbal', 'BRANCH_MANAGER', NULL, 2, NULL, '+63 908 171 8908', 'Alfonso Cavite', 1, '2026-02-02 07:58:19', '2026-02-02 08:19:03', NULL, NULL, NULL, 0),
(35, 'bogart', 'bogart@gmail.com', '$2y$12$0oxkuIK1e9ZUu06s2EgLjOtgGqGkFQ1/X5A/6u1H2B1n1bpHZnIFu', 'bogart D Explorer', 'BRANCH_MANAGER', NULL, 3, '/storage/avatars/avatar_35_1770299365.jpg', '09123456777', 'Pelepens', 1, '2026-02-05 13:46:55', '2026-02-05 14:13:02', '2026-02-05 14:13:02', NULL, NULL, 0),
(36, 'bogart2', 'bogart2@gmail.com', '$2y$12$OQQCbHO68vyJR/ryKqWk9uSTe8eFaU.w.o5wt/WGhiXI.1dxZNqUO', 'bogart D second', 'BRANCH_MANAGER', NULL, 3, NULL, '09156818880', NULL, 1, '2026-02-05 14:14:53', '2026-02-05 14:14:53', NULL, NULL, NULL, 1);

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
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD CONSTRAINT `product_comments_parent_comment_id_foreign` FOREIGN KEY (`parent_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
