-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 06, 2026 at 05:34 AM
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
(2, 15, '2026-02-06', '11:48:31', '11:48:35', 0, 'late', NULL, '2026-02-06 03:48:31', '2026-02-06 03:48:35');

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

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-17ba0791499db908433b80f37c5fbc89b870084b', 'i:1;', 1770351250),
('laravel-cache-17ba0791499db908433b80f37c5fbc89b870084b:timer', 'i:1770351250;', 1770351250),
('laravel-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b', 'i:3;', 1770348636),
('laravel-cache-424f74a6a7ed4d4ed4761507ebcd209a6ef0937b:timer', 'i:1770348636;', 1770348636),
('laravel-cache-user_token_7CZET1js0dDn7sSux5EU8TR3GGzVKc381XDvDJWkw72fg59SPeNA5zplhoDD', 'i:13;', 1772942985),
('laravel-cache-user_token_HgNgdx9iH14aXgA4RICkeVPtFuvlesVGFCSLNhhs6lhUOEHm9LlbC1E0RO0n', 'i:13;', 1772940576),
('laravel-cache-user_token_NuUAZqNz9uTWV9528Zm7BFhdGv3t24CHScMqzCTbnhzmfgeyeZlnsSxejZxy', 'i:13;', 1772940616),
('laravel-cache-user_token_yHbkJKNUAVR2f0aHcOcm6m2U6mNBlfn01CDsmIfIRnKhqCD5tOkqga83t1Pg', 'i:13;', 1772940562),
('laravel-cache-verification_code_umbal.christiancharles@ncst.edu.ph', 's:6:\"428210\";', 1770351652);

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
(29, '2026_02_06_230000_fix_add_parent_comment_id_to_product_comments', 2);

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
(1, 'Yangyeom', 'yangyeom', '2026-02-06 02:13:25', '2026-02-06 02:13:25'),
(2, 'Snow Cheese', 'snow-cheese', '2026-02-06 02:13:25', '2026-02-06 02:13:25'),
(3, 'Corndog', 'corndog', '2026-02-06 02:13:25', '2026-02-06 02:13:25'),
(4, 'Pastries', 'pastries', '2026-02-06 02:13:25', '2026-02-06 02:13:25'),
(5, 'Ramen', 'ramen', '2026-02-06 02:13:25', '2026-02-06 02:13:25'),
(6, 'Ice Cream', 'ice-cream', '2026-02-06 02:13:25', '2026-02-06 02:13:25');

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
(2, 1, NULL, NULL, 'umbal.christiancharles@ncst.edu.ph', 'mahal', 1, '::1', '2026-02-06 02:27:12', '2026-02-06 02:27:12'),
(3, 1, NULL, 2, 'xmusics77@gmail.com', 'weh ba', 5, '::1', '2026-02-06 02:42:09', '2026-02-06 02:42:09'),
(4, 6, NULL, NULL, 'xmusics77@gmail.com', 'Lasang Balot yung Icecream', 1, '::1', '2026-02-06 02:42:48', '2026-02-06 02:42:48'),
(5, 2, NULL, NULL, 'umbal.christiancharles@ncst.edu.ph', 'Masarap Siya kaso naaalala ko sya pag kinakain ko🙁🙁🙁🙁🙁🙁', 4, '::1', '2026-02-06 03:30:04', '2026-02-06 03:30:04'),
(6, 4, NULL, NULL, 'umbal.christiancharles@ncst.edu.ph', 'tinapaay lasang noodles', 1, '127.0.0.1', '2026-02-06 04:10:11', '2026-02-06 04:10:11');

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
(4, 11, 'staff-documents/11/resume.jpg', 'staff-documents/11/government_id.jpg', 'staff-documents/11/psa_birth_certificate.jpg', 'staff-documents/11/nbi_clearance.jpg', 'staff-documents/11/police_clearance.jpg', 'staff-documents/11/medical_certificate.jpg', 'staff-documents/11/drug_test_result.jpg', 'staff-documents/11/sss_id.jpg', 'staff-documents/11/philhealth_id.jpg', 'staff-documents/11/pagibig_mdf.jpg', 'staff-documents/11/tin_id.jpg', 'staff-documents/11/diploma_transcript.jpg', '2026-02-06 03:21:52', '2026-02-06 03:21:52'),
(5, 12, 'staff-documents/12/resume.jpg', 'staff-documents/12/government_id.jpg', 'staff-documents/12/psa_birth_certificate.jpg', 'staff-documents/12/nbi_clearance.jpg', 'staff-documents/12/police_clearance.jpg', 'staff-documents/12/medical_certificate.jpg', 'staff-documents/12/drug_test_result.jpg', 'staff-documents/12/sss_id.jpg', 'staff-documents/12/philhealth_id.jpg', 'staff-documents/12/pagibig_mdf.jpg', 'staff-documents/12/tin_id.jpg', 'staff-documents/12/diploma_transcript.jpg', '2026-02-06 03:23:29', '2026-02-06 03:23:29'),
(6, 14, 'staff-documents/14/resume.jpg', 'staff-documents/14/government_id.jpg', 'staff-documents/14/psa_birth_certificate.jpg', 'staff-documents/14/nbi_clearance.jpg', 'staff-documents/14/police_clearance.jpg', 'staff-documents/14/medical_certificate.jpg', 'staff-documents/14/drug_test_result.jpg', 'staff-documents/14/sss_id.jpg', 'staff-documents/14/philhealth_id.jpg', 'staff-documents/14/pagibig_mdf.jpg', 'staff-documents/14/tin_id.jpg', 'staff-documents/14/diploma_transcript.jpg', '2026-02-06 03:33:31', '2026-02-06 03:33:31'),
(7, 15, 'staff-documents/15/resume.jpg', 'staff-documents/15/government_id.jpg', 'staff-documents/15/psa_birth_certificate.jpg', 'staff-documents/15/nbi_clearance.jpg', 'staff-documents/15/police_clearance.jpg', 'staff-documents/15/medical_certificate.jpg', 'staff-documents/15/drug_test_result.jpg', 'staff-documents/15/sss_id.jpg', 'staff-documents/15/philhealth_id.jpg', 'staff-documents/15/pagibig_mdf.jpg', 'staff-documents/15/tin_id.jpg', 'staff-documents/15/diploma_transcript.jpg', '2026-02-06 03:47:16', '2026-02-06 03:47:16'),
(8, 16, NULL, 'staff-documents/16/government_id.jpg', 'staff-documents/16/psa_birth_certificate.jpg', 'staff-documents/16/nbi_clearance.jpg', 'staff-documents/16/police_clearance.jpg', 'staff-documents/16/medical_certificate.jpg', 'staff-documents/16/drug_test_result.jpg', 'staff-documents/16/sss_id.jpg', 'staff-documents/16/philhealth_id.jpg', 'staff-documents/16/pagibig_mdf.jpg', 'staff-documents/16/tin_id.jpg', 'staff-documents/16/diploma_transcript.jpg', '2026-02-06 03:56:48', '2026-02-06 03:56:59');

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

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `password`, `email_verified_at`, `role`, `branch_id`, `avatar_url`, `phone_number`, `address`, `is_active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`) VALUES
(2, 'admin@chikintayo.com', 'admin', 'Administrator', '$2y$12$BS586ELWL5YpVABOljTMQOzA0uXCeA1rY3sSlABZY9CItGgvrp3jy', NULL, 'OWNER', NULL, '/storage/avatars/avatar_2_1770343713.png', NULL, NULL, 1, NULL, '2026-02-06 02:07:33', '2026-02-06 02:07:33', NULL, 0),
(11, 'paul@gmail.com', 'Paul Berrer', 'Paul Berrer Batumbakals', '$2y$12$Cb9DUEB13lkBMR0us1WAc.2dnfSLIIT7h6E8vpUa9tfXQxQqutu8u', NULL, 'BRANCH_MANAGER', 1, '/storage/avatars/avatar_11_1770349526.jpg', '09123456799', 'asd', 1, NULL, '2026-02-06 03:21:52', '2026-02-06 03:59:50', NULL, 0),
(12, 'gabby@gmail.com', 'Gabby', 'Gabby D Minahal3', '$2y$12$FLmMvTOdw95Vhy/ynoilVOrzQ3V60nL8/a7nLLjCP215wjdhH57VO', NULL, 'STAFF', 1, '/storage/avatars/avatar_12_1770348642.jpg', '09156818851', 'asd', 1, NULL, '2026-02-06 03:23:29', '2026-02-06 03:24:08', NULL, 0),
(13, 'umbal.christiancharles@ncst.edu.ph', 'umbal', NULL, '$2y$12$O9zdvoAdRzrxUtHCUY8ah.ghXCdMqwfzT.6CeDwtxnWZre0Dt6C6u', '2026-02-06 03:29:22', 'customer', NULL, NULL, NULL, NULL, 1, NULL, '2026-02-06 03:29:22', '2026-02-06 03:29:22', NULL, 0),
(14, 'mark@gmail.com', 'mark', 'Mark Calaway', '$2y$12$gsm5n/dNa0OkPopStiES3uVEPXQHbytJYSRfwGr.NvXe75d.jOMHa', NULL, 'BRANCH_MANAGER', 2, '/storage/avatars/avatar_14_1770348856.jpg', '09156818851', 'asd', 1, NULL, '2026-02-06 03:33:31', '2026-02-06 03:34:05', NULL, 0),
(15, 'janne@gmail.com', 'janne', 'Janne De Guzman', '$2y$12$g5BMuBhoakS7IqHF5SQqne4dlsurJbbwcuSz24HajwVHbAaeiqOiG', NULL, 'HR', 1, NULL, '09099628117', 'asd', 1, NULL, '2026-02-06 03:47:16', '2026-02-06 03:48:23', NULL, 0),
(16, 'asd@gmail.com', 'asd', 'asds', '$2y$12$NYUBz3TQ9kjoNiECkhaCNu5Ug4md6d/dcpwOzm0SkkRrMPL7tiPBW', NULL, 'STAFF', 1, NULL, '09099628117', 'asd', 1, NULL, '2026-02-06 03:56:48', '2026-02-06 03:56:48', NULL, 1);

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
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
