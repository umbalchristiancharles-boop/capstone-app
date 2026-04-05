-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 04:50 PM
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
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target` varchar(255) NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `target`, `sender_id`, `created_at`, `updated_at`) VALUES
(2, 'UPDATE', 'ChikinTayo Website will be down Tomorrow at 12:30pm', 'all', 28, '2026-03-13 14:05:43', '2026-03-13 14:05:43'),
(4, 'GUYS', 'Buntis na asawa ko, kaso hindi sinkit', 'all', 31, '2026-03-13 14:07:42', '2026-03-13 14:07:42'),
(5, 'Update', 'Chikintayo 2.0', 'all', 28, '2026-03-13 14:29:55', '2026-03-13 14:29:55'),
(6, 'Update System 2.0', 'updates', 'all', 28, '2026-03-30 07:51:57', '2026-03-30 07:51:57');

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
(4, 154, '2026-03-29', '22:50:47', NULL, 0, 'late', NULL, '2026-03-29 14:50:47', '2026-03-29 14:50:47'),
(5, 154, '2026-04-04', '21:11:50', NULL, 0, 'late', NULL, '2026-04-04 13:11:50', '2026-04-04 13:11:50'),
(6, 151, '2026-04-04', '21:14:14', '22:24:14', -70, 'late', NULL, '2026-04-04 13:14:14', '2026-04-04 14:24:14'),
(7, 28, '2026-04-04', '22:44:25', '22:44:34', 0, 'late', NULL, '2026-04-04 14:44:25', '2026-04-04 14:44:34'),
(8, 157, '2026-04-05', '16:09:18', NULL, 0, 'late', NULL, '2026-04-05 08:09:18', '2026-04-05 08:09:18'),
(9, 149, '2026-04-05', '19:13:12', '19:14:42', -2, 'late', NULL, '2026-04-05 11:13:12', '2026-04-05 11:14:42');

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
(121, 31, 0, '2026-03-22 10:21:33', '2026-03-22 10:21:33'),
(136, 32, 0, '2026-03-26 06:45:12', '2026-03-26 06:45:12');

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
  `is_main_branch` tinyint(1) NOT NULL DEFAULT 0,
  `approval_status` varchar(20) NOT NULL DEFAULT 'approved',
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_confirmed_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `budget` bigint(20) NOT NULL DEFAULT 100000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `address`, `is_active`, `is_main_branch`, `approval_status`, `requested_by`, `finance_confirmed_by`, `finance_confirmed_at`, `approved_by`, `approved_at`, `rejected_at`, `budget`, `created_at`, `updated_at`) VALUES
(31, 'BR743957', 'Dasma Branch', 'Dasma', 1, 0, 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 97773, '2026-03-22 10:19:21', '2026-04-05 07:56:12'),
(32, 'MAIN', 'Main Branch', 'HQ', 1, 1, 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 1000000, '2026-03-25 06:56:11', '2026-04-05 12:39:10');

-- --------------------------------------------------------

--
-- Table structure for table `budget_requests`
--

CREATE TABLE `budget_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `purpose` varchar(500) NOT NULL,
  `requested_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Budget Given','Completed') NOT NULL DEFAULT 'Pending',
  `date_requested` date NOT NULL,
  `processed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `date_processed` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budget_requests`
--

INSERT INTO `budget_requests` (`id`, `branch_id`, `user_id`, `purpose`, `requested_amount`, `status`, `date_requested`, `processed_by`, `date_processed`, `created_at`, `updated_at`) VALUES
(80, 31, 150, 'Procurement Request #113: frozen hot dog x10', 1340.00, 'Completed', '2026-04-02', 150, '2026-04-02', '2026-04-02 14:52:26', '2026-04-02 14:56:34'),
(81, 31, 150, 'Procurement Request #112: ketchop x10', 230.00, 'Completed', '2026-04-02', 150, '2026-04-02', '2026-04-02 14:52:29', '2026-04-02 14:56:30'),
(82, 31, 150, 'Procurement Request #115: frozen hot dog x20', 2948.00, 'Pending', '2026-04-03', NULL, NULL, '2026-04-03 09:29:45', '2026-04-03 09:29:45'),
(83, 31, 150, 'Procurement Request #118: water x10', 150.00, 'Completed', '2026-04-04', 150, '2026-04-04', '2026-04-04 07:46:30', '2026-04-04 07:50:36'),
(84, 31, 150, 'Procurement Request #116: frozen hot dog x10', 1474.00, 'Completed', '2026-04-04', 150, '2026-04-04', '2026-04-04 14:25:33', '2026-04-04 14:33:18'),
(85, 31, 150, 'Procurement Request #114: frozen hot dog x20', 2948.00, 'Pending', '2026-04-04', NULL, NULL, '2026-04-04 14:27:35', '2026-04-04 14:27:35'),
(86, 31, 150, 'Procurement Request #119: water x10', 2500.00, 'Completed', '2026-04-04', 150, '2026-04-04', '2026-04-04 14:37:21', '2026-04-04 14:40:25'),
(87, 31, 150, 'Procurement Request #117: ketchop x10', 253.00, 'Completed', '2026-04-05', 150, '2026-04-05', '2026-04-05 07:54:28', '2026-04-05 07:59:22'),
(88, 31, 150, 'Procurement Request #121: Juice x10', 210.00, 'Pending', '2026-04-05', NULL, NULL, '2026-04-05 08:07:37', '2026-04-05 08:07:37');

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
('laravel-cache-user_token_ePGb1CbLonQhF0IKrz4sI3nZBqLAIuHuzV2sgAvvmF1RjpeTW0hSvK2JO84A', 'i:176;', 1777967186),
('laravel-cache-verification_code_xeyenil325@cosdas.com', 's:6:\"000675\";', 1775383220),
('laravel-cache-verification_rate_limit_kademel468@cosdas.com', 'i:1;', 1775375568),
('laravel-cache-verification_rate_limit_xeyenil325@cosdas.com', 'i:2;', 1775383220);

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
(6, 176, 'lejanis485@fun4k.com', 'Customer', NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-03-30 02:13:38', 'active', '2026-03-30 02:13:38', '2026-03-30 02:13:38'),
(7, 177, 'yoboko6989@fun4k.com', 'Customer_12', NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-03-30 07:46:53', 'active', '2026-03-30 07:46:53', '2026-03-30 07:46:53');

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `approval_status` varchar(255) NOT NULL DEFAULT 'pending_approval',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `name`, `created_by`, `branch_id`, `status`, `approval_status`, `approved_by`, `approved_at`, `approval_notes`, `created_at`, `updated_at`) VALUES
(35, 'hot dog', 157, 31, 'active', 'approved', 31, '2026-04-02 14:49:15', NULL, '2026-04-02 14:48:12', '2026-04-02 14:49:15'),
(36, 'test', 157, 31, 'active', 'approved', 31, '2026-04-05 08:10:58', NULL, '2026-04-05 08:10:13', '2026-04-05 08:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `dish_ingredients`
--

CREATE TABLE `dish_ingredients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `per_serving` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dish_ingredients`
--

INSERT INTO `dish_ingredients` (`id`, `dish_id`, `product_id`, `name`, `unit`, `per_serving`, `created_at`, `updated_at`) VALUES
(48, 34, 143, 'frozen hordog', 'pcs', 1.0000, '2026-04-02 14:15:22', '2026-04-02 14:15:54'),
(49, 34, 144, 'kethcop', 'pcs', 0.0000, '2026-04-02 14:15:22', '2026-04-02 14:15:54'),
(50, 35, 148, 'frozen hot dog', 'pcs', 1.0000, '2026-04-02 14:48:12', '2026-04-02 14:49:15'),
(51, 35, 149, 'ketchop', 'pcs', 0.0000, '2026-04-02 14:48:12', '2026-04-02 14:49:15'),
(52, 36, 158, 'Chicken Frozen', 'pcs', 1.0000, '2026-04-05 08:10:13', '2026-04-05 08:10:58');

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
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
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

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"7d8db3b6-8b59-4879-9115-9f0dbec5a9b1\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:117;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775375668,\"delay\":null}', 0, NULL, 1775375668, 1775375668),
(2, 'default', '{\"uuid\":\"27fc5fe6-2140-40f4-a626-99bc4987a095\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:117;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775375821,\"delay\":null}', 0, NULL, 1775375821, 1775375821),
(3, 'default', '{\"uuid\":\"f0adbe88-2b66-4705-8845-18604470d524\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:121;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775376457,\"delay\":null}', 0, NULL, 1775376457, 1775376457);

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
-- Table structure for table `logistics_transactions`
--

CREATE TABLE `logistics_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `procurement_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `destination_branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `quantity_verified` decimal(10,2) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'unit',
  `reference_number` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `initiated_at` timestamp NULL DEFAULT NULL,
  `in_transit_at` timestamp NULL DEFAULT NULL,
  `at_destination_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `expected_quantity` int(11) NOT NULL DEFAULT 0,
  `actual_quantity` int(11) NOT NULL DEFAULT 0,
  `variance_reason` text DEFAULT NULL,
  `source_location` varchar(255) DEFAULT NULL,
  `destination_location` varchar(255) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `receipt_path` text DEFAULT NULL,
  `proof_of_delivery_path` text DEFAULT NULL,
  `documentation_files` text DEFAULT NULL,
  `cost_price` decimal(12,2) DEFAULT NULL,
  `cost_reference` varchar(255) DEFAULT NULL,
  `is_duplicate` tinyint(1) NOT NULL DEFAULT 0,
  `duplicate_of_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `audit_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `logistics_transactions`
--

INSERT INTO `logistics_transactions` (`id`, `procurement_request_id`, `supplier_order_id`, `product_id`, `source_branch_id`, `destination_branch_id`, `branch_id`, `type`, `status`, `quantity`, `quantity_verified`, `unit`, `reference_number`, `description`, `notes`, `created_by_user_id`, `updated_by_user_id`, `verified_by_user_id`, `initiated_at`, `in_transit_at`, `at_destination_at`, `verified_at`, `confirmed_at`, `completed_at`, `cancelled_at`, `expected_quantity`, `actual_quantity`, `variance_reason`, `source_location`, `destination_location`, `delivery_address`, `receipt_path`, `proof_of_delivery_path`, `documentation_files`, `cost_price`, `cost_reference`, `is_duplicate`, `duplicate_of_transaction_id`, `audit_notes`, `created_at`, `updated_at`) VALUES
(1, 119, NULL, 153, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-119', 'water', NULL, 154, NULL, NULL, '2026-04-04 14:34:27', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-04-04 14:34:27', '2026-04-04 14:34:27'),
(2, 120, NULL, 152, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-120', 'frozen hot dog', NULL, 154, NULL, NULL, '2026-04-05 07:44:11', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 07:44:11', '2026-04-05 07:44:11'),
(3, 121, NULL, 155, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-121', 'Juice', NULL, 154, NULL, NULL, '2026-04-05 08:04:51', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 08:04:51', '2026-04-05 08:04:51'),
(4, 122, NULL, 158, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-122', 'Chicken Frozen', NULL, 154, NULL, NULL, '2026-04-05 08:11:35', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 08:11:35', '2026-04-05 08:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `from_user_id` bigint(20) UNSIGNED NOT NULL,
  `to_user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `branch_id`, `from_user_id`, `to_user_id`, `body`, `read_at`, `created_at`, `updated_at`) VALUES
(12, 31, 149, 148, 'Workplace is too hot', '2026-03-22 11:46:35', '2026-03-22 11:46:11', '2026-03-22 11:46:35'),
(13, 31, 148, 149, 'Inom ka tubig pre', '2026-03-22 11:47:01', '2026-03-22 11:46:44', '2026-03-22 11:47:01'),
(14, 31, 149, 148, 'nang yan amp, barat', '2026-03-24 09:19:54', '2026-03-22 11:47:15', '2026-03-24 09:19:54'),
(15, 31, 150, 148, 'hiii hehe', '2026-03-24 09:20:06', '2026-03-24 04:52:00', '2026-03-24 09:20:06'),
(16, 31, 153, 148, 'Test', '2026-03-24 09:19:54', '2026-03-24 09:19:34', '2026-03-24 09:19:54'),
(17, 31, 148, 153, 'test', '2026-03-25 06:34:12', '2026-03-24 09:20:05', '2026-03-25 06:34:12'),
(18, 31, 154, 148, 'May chismiss ako', NULL, '2026-03-25 06:34:59', '2026-03-25 06:34:59'),
(19, 31, 150, 147, 'helloo', '2026-03-30 03:34:12', '2026-03-30 03:28:50', '2026-03-30 03:34:12'),
(20, 31, 151, 150, 'hoy request ko', '2026-03-30 03:30:49', '2026-03-30 03:30:20', '2026-03-30 03:30:49'),
(21, 31, 150, 151, 'bahaala ka dyan', '2026-03-30 08:50:27', '2026-03-30 03:30:58', '2026-03-30 08:50:27'),
(22, 31, 147, 150, 'sinu kaa', '2026-03-30 05:42:42', '2026-03-30 03:34:20', '2026-03-30 05:42:42'),
(23, 31, 149, 151, 'Hello', '2026-03-30 08:50:05', '2026-03-30 08:49:44', '2026-03-30 08:50:05'),
(24, 31, 151, 149, 'hi', NULL, '2026-03-30 08:50:19', '2026-03-30 08:50:19'),
(25, NULL, 28, 147, 'hi Gab', NULL, '2026-04-05 01:53:08', '2026-04-05 01:53:08');

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
(40, '2026_03_15_000000_add_min_stock_to_products_table', 5),
(41, '2026_03_20_000000_create_budget_requests_table', 6),
(42, '2026_03_12_235307_make_email_nullable_in_users_table', 7),
(43, '2026_03_25_000000_create_expenses_table', 7),
(44, '2026_03_25_000001_create_settlements_table', 7),
(45, '2026_04_01_000000_create_announcements_table', 7),
(46, '2026_03_14_000001_add_vat_discount_to_orders_table', 8),
(47, '2026_12_01_120000_create_purchase_requests_table', 9),
(48, '2026_03_15_000001_add_budget_to_branches_table', 10),
(49, '2026_10_20_000000_add_supplier_id_to_products_table', 11),
(50, '2026_03_17_000001_add_supplier_name_to_products_table', 12),
(51, '2026_03_18_000001_add_is_published_to_products', 13),
(53, '2024_11_10_000000_add_order_pending_cancel_fields', 14),
(54, '2026_03_18_143629_add_procurement_fields_to_products_table', 15),
(55, '2026_03_18_143800_create_procurement_requests_table', 16),
(56, '2026_03_18_170000_update_procurement_requests_schema', 17),
(57, '2026_03_18_164510_add_logistics_request_available_to_products_table', 18),
(58, '2026_03_18_171700_add_logistics_request_available_if_missing', 19),
(59, '2026_03_18_200000_add_procurement_statuses', 20),
(60, '2026_03_19_000000_add_pending_order_status_procurement_requests', 21),
(61, '2026_03_19_100000_create_supplier_orders_table', 22),
(62, '2026_03_19_210000_add_ongoing_delivery_to_procurement_requests', 23),
(63, '2026_03_20_000000_create_messages_table', 24),
(64, '2026_03_21_000001_add_budget_given_status_to_budget_requests', 25),
(65, '2026_03_21_000002_add_completed_status_to_budget_requests', 26),
(66, '2026_03_21_000003_add_cost_price_to_products_table', 27),
(67, '2026_03_21_000004_create_price_audits_table', 27),
(68, '2026_03_21_000005_backfill_product_cost_price', 27),
(69, '2026_03_21_000000_add_refund_fields_to_orders_table', 28),
(70, '2026_03_23_000000_add_kitchen_to_department', 29),
(71, '2026_03_23_000001_create_dishes_table', 30),
(72, '2026_03_23_000002_create_dish_ingredients_table', 30),
(73, '2026_03_23_000003_add_per_serving_to_dish_ingredients', 31),
(74, '2026_03_23_000004_drop_quantity_from_dish_ingredients', 32),
(75, '2026_03_23_000001_add_is_kitchen_dish_to_products', 33),
(76, '2026_03_23_000002_mark_existing_kitchen_dishes', 34),
(77, '2026_03_23_000003_mark_products_matching_dishes', 35),
(78, '2026_03_24_000000_add_is_broadcast_to_supplier_orders', 36),
(79, '2026_03_24_010000_add_supplier_confirmed_to_procurement_requests', 37),
(80, '2026_03_24_150001_add_price_to_supplier_orders', 38),
(81, '2026_03_25_000100_add_is_main_branch_to_branches_table', 39),
(82, '2026_03_26_000000_add_receipt_fields_to_procurement_requests', 40),
(83, '2026_03_27_041700_add_awaiting_inventory_confirmation_to_procurement_requests', 41),
(84, '2026_03_27_000100_add_permissions_to_users_table', 42),
(85, '2026_03_28_100000_add_completed_fields_to_orders_table', 43),
(86, '2026_03_28_162429_fix_order_status_enum', 44),
(87, '2026_03_28_000001_add_category_and_expiration_to_products', 45),
(88, '2026_03_28_000001_add_per_pack_or_individual_to_products', 46),
(89, '2026_03_29_000000_add_approval_workflow_to_dishes_table', 47),
(90, '2026_04_01_000001_create_price_markup_percentages_table', 48),
(91, '2026_04_01_000002_create_price_markup_requests_table', 48),
(92, '2026_04_02_000000_create_product_requests_table', 49),
(93, '2026_03_30_000000_add_supplier_id_to_procurement_requests', 50),
(94, '2026_03_30_000000_add_real_stock_to_products_table', 51),
(95, '2026_04_02_000001_add_pack_fields_to_products', 52),
(96, '2026_04_02_000001_set_default_is_published_false', 53),
(97, '2026_04_02_000002_add_open_pack_used_to_products', 54),
(98, '2026_04_02_000003_unpublish_kitchen_placeholder_products', 55),
(99, '2026_04_02_000004_unpublish_unexpected_published_products', 56),
(100, '2026_04_02_000005_create_missing_dish_products', 57),
(101, '2026_04_02_000006_add_dish_and_publish_fields_to_products', 58),
(102, '2026_04_02_000007_add_is_dish_product_to_products', 59),
(103, '2026_04_02_000008_mark_existing_dish_products', 60),
(104, '2026_04_02_000010_link_hotdog_ingredient_fix', 61),
(106, '2026_04_03_000001_add_approval_workflow_to_products', 62),
(107, '2026_04_03_000002_add_approval_workflow_to_product_requests', 63),
(108, '2026_04_04_000001_create_supplier_audit_logs_table', 64),
(109, '2026_04_04_000001_create_logistics_transactions_table', 65),
(110, '2026_04_05_000001_add_branch_approval_fields_to_branches_table', 65),
(111, '2026_04_05_000002_add_finance_confirmation_fields_to_branches_table', 66),
(112, '2026_04_05_120000_add_delivery_confirmation_fields_to_procurement_requests_table', 67);

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
  `status` enum('pending','in_kitchen','approved','cancelled','completed') NOT NULL,
  `is_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `refund_reason` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `grand_total` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_type` varchar(20) NOT NULL DEFAULT 'none',
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vat_percent` decimal(5,2) NOT NULL DEFAULT 12.00,
  `vat_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ordered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `owner_id`, `cashier_id`, `branch_id`, `customer_name`, `status`, `is_cancelled`, `cancelled_at`, `cancelled_by`, `refund_reason`, `approved_at`, `grand_total`, `amount_paid`, `change_amount`, `discount_type`, `discount_percent`, `discount_amount`, `vat_percent`, `vat_amount`, `subtotal`, `ordered_at`, `created_at`, `updated_at`, `approved_by`, `completed_at`, `completed_by`) VALUES
(97, 'CT-0001', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:28:39', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:28:39', '2026-04-02 15:28:39', '2026-04-02 15:28:39', 153, NULL, NULL),
(98, 'CT-0002', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:31:16', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:31:16', '2026-04-02 15:31:16', '2026-04-02 15:31:16', 153, NULL, NULL),
(99, 'CT-0003', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:31:20', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:31:20', '2026-04-02 15:31:20', '2026-04-02 15:31:20', 153, NULL, NULL),
(100, 'CT-0004', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:31:48', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:31:48', '2026-04-02 15:31:48', '2026-04-02 15:31:48', 153, NULL, NULL),
(101, 'CT-0005', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:31:53', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:31:53', '2026-04-02 15:31:53', '2026-04-02 15:31:53', 153, NULL, NULL),
(102, 'CT-0006', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:32:01', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:32:01', '2026-04-02 15:32:01', '2026-04-02 15:32:01', 153, NULL, NULL),
(103, 'CT-0007', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:32:05', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:32:05', '2026-04-02 15:32:05', '2026-04-02 15:32:05', 153, NULL, NULL),
(104, 'CT-0008', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:35:58', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:35:58', '2026-04-02 15:35:58', '2026-04-02 15:35:58', 153, NULL, NULL),
(105, 'CT-0009', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:44:51', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:44:51', '2026-04-02 15:44:51', '2026-04-02 15:44:51', 153, NULL, NULL),
(106, 'CT-0010', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:44:57', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:44:57', '2026-04-02 15:44:57', '2026-04-02 15:44:57', 153, NULL, NULL),
(107, 'CT-0011', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:01', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:01', '2026-04-02 15:45:01', '2026-04-02 15:45:01', 153, NULL, NULL),
(108, 'CT-0012', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:05', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:05', '2026-04-02 15:45:05', '2026-04-02 15:45:05', 153, NULL, NULL),
(109, 'CT-0013', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:09', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:09', '2026-04-02 15:45:09', '2026-04-02 15:45:09', 153, NULL, NULL),
(110, 'CT-0014', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:12', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:12', '2026-04-02 15:45:12', '2026-04-02 15:45:12', 153, NULL, NULL),
(111, 'CT-0015', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:25', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:25', '2026-04-02 15:45:25', '2026-04-02 15:45:25', 153, NULL, NULL),
(112, 'CT-0016', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:29', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:29', '2026-04-02 15:45:29', '2026-04-02 15:45:29', 153, NULL, NULL),
(113, 'CT-0017', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:33', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:33', '2026-04-02 15:45:33', '2026-04-02 15:45:33', 153, NULL, NULL),
(114, 'CT-0018', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-02 15:45:37', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-02 15:45:37', '2026-04-02 15:45:37', '2026-04-02 15:45:37', 153, NULL, NULL),
(115, 'CT-0019', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:20:47', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:20:47', '2026-04-04 07:20:47', '2026-04-04 07:20:47', 153, NULL, NULL),
(116, 'CT-0020', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:20:52', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:20:52', '2026-04-04 07:20:52', '2026-04-04 07:20:52', 153, NULL, NULL),
(117, 'CT-0021', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:20:54', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:20:54', '2026-04-04 07:20:54', '2026-04-04 07:20:54', 153, NULL, NULL),
(118, 'CT-0022', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:20:58', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:20:58', '2026-04-04 07:20:58', '2026-04-04 07:20:58', 153, NULL, NULL),
(119, 'CT-0023', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:02', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:02', '2026-04-04 07:21:02', '2026-04-04 07:21:02', 153, NULL, NULL),
(120, 'CT-0024', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:06', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:06', '2026-04-04 07:21:06', '2026-04-04 07:21:06', 153, NULL, NULL),
(121, 'CT-0025', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:10', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:10', '2026-04-04 07:21:10', '2026-04-04 07:21:10', 153, NULL, NULL),
(122, 'CT-0026', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:13', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:13', '2026-04-04 07:21:13', '2026-04-04 07:21:13', 153, NULL, NULL),
(123, 'CT-0027', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:17', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:17', '2026-04-04 07:21:17', '2026-04-04 07:21:17', 153, NULL, NULL),
(124, 'CT-0028', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:21:20', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:21:20', '2026-04-04 07:21:20', '2026-04-04 07:21:20', 153, NULL, NULL),
(125, 'CT-0029', 153, 153, 31, 'Walk-in', 'cancelled', 1, '2026-04-05 07:53:41', 153, 'expired', '2026-04-04 07:51:42', 73.92, 100.00, 26.08, 'none', 0.00, 0.00, 12.00, 7.92, 66.00, '2026-04-04 07:51:42', '2026-04-04 07:51:42', '2026-04-05 07:53:41', 153, NULL, NULL),
(126, 'CT-0030', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-04 07:51:51', 237.38, 300.00, 62.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-04 07:51:51', '2026-04-04 07:51:51', '2026-04-04 07:51:51', 153, NULL, NULL),
(127, 'CT-0031', 153, 153, 31, 'Walk-in', 'in_kitchen', 0, NULL, NULL, NULL, '2026-04-05 07:53:18', 237.38, 5000.00, 4762.62, 'none', 0.00, 0.00, 12.00, 25.43, 211.95, '2026-04-05 07:53:18', '2026-04-05 07:53:18', '2026-04-05 07:53:18', 153, NULL, NULL);

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `subtotal`, `created_at`, `updated_at`) VALUES
(48, 97, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:28:39', '2026-04-02 15:28:39'),
(49, 98, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:31:16', '2026-04-02 15:31:16'),
(50, 99, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:31:20', '2026-04-02 15:31:20'),
(51, 100, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:31:48', '2026-04-02 15:31:48'),
(52, 101, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:31:53', '2026-04-02 15:31:53'),
(53, 102, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:32:01', '2026-04-02 15:32:01'),
(54, 103, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:32:05', '2026-04-02 15:32:05'),
(55, 104, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:35:58', '2026-04-02 15:35:58'),
(56, 105, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:44:51', '2026-04-02 15:44:51'),
(57, 106, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:44:57', '2026-04-02 15:44:57'),
(58, 107, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:01', '2026-04-02 15:45:01'),
(59, 108, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:05', '2026-04-02 15:45:05'),
(60, 109, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:09', '2026-04-02 15:45:09'),
(61, 110, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:12', '2026-04-02 15:45:12'),
(62, 111, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:25', '2026-04-02 15:45:25'),
(63, 112, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:29', '2026-04-02 15:45:29'),
(64, 113, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:33', '2026-04-02 15:45:33'),
(65, 114, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-02 15:45:37', '2026-04-02 15:45:37'),
(66, 115, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:20:47', '2026-04-04 07:20:47'),
(67, 116, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:20:52', '2026-04-04 07:20:52'),
(68, 117, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:20:54', '2026-04-04 07:20:54'),
(69, 118, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:20:58', '2026-04-04 07:20:58'),
(70, 119, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:02', '2026-04-04 07:21:02'),
(71, 120, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:06', '2026-04-04 07:21:06'),
(72, 121, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:10', '2026-04-04 07:21:10'),
(73, 122, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:13', '2026-04-04 07:21:13'),
(74, 123, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:17', '2026-04-04 07:21:17'),
(75, 124, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:21:20', '2026-04-04 07:21:20'),
(76, 125, 154, 'water', 16.50, 4, 66.00, '2026-04-04 07:51:42', '2026-04-04 07:51:42'),
(77, 126, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-04 07:51:51', '2026-04-04 07:51:51'),
(78, 127, 150, 'hot dog', 211.95, 1, 211.95, '2026-04-05 07:53:18', '2026-04-05 07:53:18');

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
(363, 'App\\Models\\User', 43, 'auth-token', '7c341743d19498255fa5c8ff7f6e46b13f378a37feed02b6546b0a27542a2142', '[\"*\"]', NULL, NULL, '2026-03-09 07:37:28', '2026-03-09 07:37:28'),
(364, 'App\\Models\\User', 28, 'auth-token', '70315203d0482ee6783acb47ea946f572fa3c458417843799e4832e7382b2feb', '[\"*\"]', NULL, NULL, '2026-03-10 04:57:25', '2026-03-10 04:57:25'),
(365, 'App\\Models\\User', 42, 'auth-token', '24daad8a59210641293ca3b17d8d23f48c66c4e4a874f795e8ba14c47718f420', '[\"*\"]', NULL, NULL, '2026-03-10 06:46:13', '2026-03-10 06:46:13'),
(366, 'App\\Models\\User', 42, 'auth-token', '978fac011694e6a7b7e38de522e99d861c6a0f76897bb5f7185712b664651806', '[\"*\"]', NULL, NULL, '2026-03-10 06:46:39', '2026-03-10 06:46:39'),
(367, 'App\\Models\\User', 42, 'auth-token', '5453822caaa5e5a4c9f98826ad726250d10347d9c8027982760ec32c1e359292', '[\"*\"]', NULL, NULL, '2026-03-10 07:13:37', '2026-03-10 07:13:37'),
(368, 'App\\Models\\User', 43, 'auth-token', 'f97627407e4fb0668287cafc7906f63c46f0b3a826b8b8df9471aaa02987fb23', '[\"*\"]', NULL, NULL, '2026-03-10 07:13:48', '2026-03-10 07:13:48'),
(369, 'App\\Models\\User', 43, 'auth-token', '448d661ca0f97426dcdfc361607b8b67b35454dc2f5ee3e08807c7343bfb2521', '[\"*\"]', NULL, NULL, '2026-03-10 07:34:36', '2026-03-10 07:34:36'),
(370, 'App\\Models\\User', 28, 'auth-token', 'c92730ce6d4f4b503afd22929d116cabd5a1ff5551b2243bd740490a5993e919', '[\"*\"]', NULL, NULL, '2026-03-10 07:36:03', '2026-03-10 07:36:03'),
(371, 'App\\Models\\User', 28, 'auth-token', 'd8a5f565b02d8f0f0feee8db1d839183c819d1f39a198e91850880ce3e720dba', '[\"*\"]', NULL, NULL, '2026-03-10 07:39:14', '2026-03-10 07:39:14'),
(372, 'App\\Models\\User', 43, 'auth-token', '53decc4980381ea525642daa97ac34ed3d74529049b3651ebd6f2f922e6809bb', '[\"*\"]', NULL, NULL, '2026-03-10 07:39:32', '2026-03-10 07:39:32'),
(373, 'App\\Models\\User', 28, 'auth-token', 'ec8509e358fe6e1bce4ad2e2df8820fbcf9545307072d3d359ed0b7c476d6e61', '[\"*\"]', NULL, NULL, '2026-03-10 07:53:11', '2026-03-10 07:53:11'),
(374, 'App\\Models\\User', 31, 'auth-token', '73efccbae60a830c91aa3642a50f86f03afe715028e6421aedfdc5ea67cb889c', '[\"*\"]', NULL, NULL, '2026-03-10 07:59:29', '2026-03-10 07:59:29'),
(375, 'App\\Models\\User', 31, 'auth-token', 'dc29d11c60ad3c3965aa60d83d25d9457b35bd975619b65ac5c3d85d2f5700e3', '[\"*\"]', NULL, NULL, '2026-03-10 07:59:38', '2026-03-10 07:59:38'),
(376, 'App\\Models\\User', 31, 'auth-token', '234ae705389c0f1fe8977351a7e0ff23471e2f81890578301db528f372370d20', '[\"*\"]', NULL, NULL, '2026-03-10 08:00:41', '2026-03-10 08:00:41'),
(377, 'App\\Models\\User', 31, 'auth-token', '786f3440dd08e7c641e5d661eea8d0ac5853c17626d697d344bbe9b8cc62e18e', '[\"*\"]', NULL, NULL, '2026-03-10 08:14:10', '2026-03-10 08:14:10'),
(378, 'App\\Models\\User', 49, 'auth-token', '808690c6aaf62d10cfd85b80ace15c8c41742cc72c7ea197eea4c321d9405a30', '[\"*\"]', NULL, NULL, '2026-03-10 08:15:55', '2026-03-10 08:15:55'),
(379, 'App\\Models\\User', 49, 'auth-token', 'a2083d8b3779241bf9160195701a55c9a90bbddf04c83bc9ec77381f0155c7ca', '[\"*\"]', NULL, NULL, '2026-03-10 08:17:18', '2026-03-10 08:17:18'),
(380, 'App\\Models\\User', 49, 'auth-token', '2e7e5fb0ac75313a71a3fc5d6a1b3cfbeae05c7dd199bfb29a425427e910066c', '[\"*\"]', NULL, NULL, '2026-03-10 08:22:12', '2026-03-10 08:22:12'),
(381, 'App\\Models\\User', 53, 'auth-token', 'c4543f670ed8dd78d9cd4eda677b69b59d70b2061bc2057cde0ca09356ff2146', '[\"*\"]', NULL, NULL, '2026-03-10 08:23:42', '2026-03-10 08:23:42'),
(382, 'App\\Models\\User', 53, 'auth-token', '8f3617c4b51a4ae87264494ea845120e2ee755894a867d7af1488bd70666098d', '[\"*\"]', NULL, NULL, '2026-03-10 08:24:11', '2026-03-10 08:24:11'),
(383, 'App\\Models\\User', 28, 'auth-token', '73d2e7ea8cec3536f9492f70e25062cdf593be210b10d047d0b8796cb184cc17', '[\"*\"]', NULL, NULL, '2026-03-10 08:25:02', '2026-03-10 08:25:02'),
(384, 'App\\Models\\User', 53, 'auth-token', '40d710bc68dea6a4e91e9f61276aadebe6b6618c079d71ac7460821942f3ad9b', '[\"*\"]', NULL, NULL, '2026-03-10 08:35:40', '2026-03-10 08:35:40'),
(385, 'App\\Models\\User', 28, 'auth-token', '52feaf6771050d6d12180ce373cff3000e7b49ba67d9b09232bb3980678435a5', '[\"*\"]', NULL, NULL, '2026-03-10 08:36:14', '2026-03-10 08:36:14'),
(386, 'App\\Models\\User', 53, 'auth-token', '72bc080608562e8ae867bb29b2a7824106be72e2b3c9cb03e6a101d834b2db53', '[\"*\"]', NULL, NULL, '2026-03-10 08:36:55', '2026-03-10 08:36:55'),
(387, 'App\\Models\\User', 53, 'auth-token', '952ef4e22697bfa3cc2d3dbb6745b064c17c58c49b138ec2ec9a7ca0a5a3d4b3', '[\"*\"]', NULL, NULL, '2026-03-10 08:37:29', '2026-03-10 08:37:29'),
(388, 'App\\Models\\User', 49, 'auth-token', 'cbbef19ab393c6b92a45e49e729e651c6c82d1d87dd2f4ed4cff4fbdf6c72a28', '[\"*\"]', NULL, NULL, '2026-03-10 08:37:47', '2026-03-10 08:37:47'),
(389, 'App\\Models\\User', 49, 'auth-token', 'a57293ef1c520af101e6ae063e6ff590fa5afe0f4eee82638d8f77b5e1f6e62b', '[\"*\"]', NULL, NULL, '2026-03-10 09:24:10', '2026-03-10 09:24:10'),
(390, 'App\\Models\\User', 53, 'auth-token', 'a711883e2c5c2bb4298060f889ff2f85fcbce95b8059d549d6d346a0a03156cc', '[\"*\"]', NULL, NULL, '2026-03-10 09:36:47', '2026-03-10 09:36:47'),
(391, 'App\\Models\\User', 28, 'auth-token', '640f356e88515ce05d5cb2d4c759cea28e5f5ea371cf45fee45f266d5d5a7c02', '[\"*\"]', NULL, NULL, '2026-03-10 09:37:10', '2026-03-10 09:37:10'),
(392, 'App\\Models\\User', 28, 'auth-token', '2ba4dbc04012a9cbb48be27b66b2540ece8fcf7593e9e6b95cd4897f632c0383', '[\"*\"]', NULL, NULL, '2026-03-10 14:08:14', '2026-03-10 14:08:14'),
(393, 'App\\Models\\User', 49, 'auth-token', 'd788cad5b3bd4bd563b51f8a6715f4d4d3776e8b13a4eaae14a2fa2fb2805274', '[\"*\"]', NULL, NULL, '2026-03-10 15:38:54', '2026-03-10 15:38:54'),
(394, 'App\\Models\\User', 51, 'auth-token', '7962409eeef4cc6fa9954475018193d95b1b8146b5f08b8dd741c5701bbcb734', '[\"*\"]', NULL, NULL, '2026-03-10 15:39:55', '2026-03-10 15:39:55'),
(395, 'App\\Models\\User', 51, 'auth-token', '562ec0108d16f6f7b418b2877efa422984163a1d6b626786b620eb23fe9e98bd', '[\"*\"]', NULL, NULL, '2026-03-10 15:40:41', '2026-03-10 15:40:41'),
(396, 'App\\Models\\User', 49, 'auth-token', '1499901edf84477d6454a1d1f3e1503b1a409b1ab9295894a99434f7f7339df7', '[\"*\"]', NULL, NULL, '2026-03-10 15:45:38', '2026-03-10 15:45:38'),
(397, 'App\\Models\\User', 50, 'auth-token', 'c222cd5753f1dbfd197d24bd53f90b67a2b589600f29147afb8914721f10a53e', '[\"*\"]', NULL, NULL, '2026-03-10 15:47:26', '2026-03-10 15:47:26'),
(398, 'App\\Models\\User', 50, 'auth-token', '06e0d0d241c222267275ae3f5bf4a71b786ef29c88efd7ebdbdc8218d0079675', '[\"*\"]', NULL, NULL, '2026-03-10 15:48:12', '2026-03-10 15:48:12'),
(399, 'App\\Models\\User', 49, 'auth-token', '53a1f29a186a2d902379a84ad8743f8a71b0a0642ffe7e1daaa47c28b3b9677f', '[\"*\"]', NULL, NULL, '2026-03-10 16:26:59', '2026-03-10 16:26:59'),
(400, 'App\\Models\\User', 51, 'auth-token', '14a9e72f2f2aae8efd0df6a603d459a20dbde00800c8728d03ba5036a712f2b0', '[\"*\"]', NULL, NULL, '2026-03-10 16:27:27', '2026-03-10 16:27:27'),
(401, 'App\\Models\\User', 28, 'auth-token', 'da126eae284f865678542db4dbdd6b8a69ff9ecf9d81ee4c5f2e7b9f2dbee051', '[\"*\"]', NULL, NULL, '2026-03-10 17:56:13', '2026-03-10 17:56:13'),
(402, 'App\\Models\\User', 50, 'auth-token', '6a1868f09a0242ca71a40d800824d141615d112e48d360dca845b100b0f79b8d', '[\"*\"]', NULL, NULL, '2026-03-10 17:57:10', '2026-03-10 17:57:10'),
(403, 'App\\Models\\User', 51, 'auth-token', '40757b26a8db333b717d3de897a6d938d60d1e724499200a2b8d16fbe6971764', '[\"*\"]', NULL, NULL, '2026-03-10 17:57:56', '2026-03-10 17:57:56'),
(404, 'App\\Models\\User', 50, 'auth-token', 'df5c684c9632426b97431201c0676bfb6b0bb8dac7d5a85de01f34bdcd457ed9', '[\"*\"]', NULL, NULL, '2026-03-10 18:05:24', '2026-03-10 18:05:24'),
(405, 'App\\Models\\User', 50, 'auth-token', 'afa93f1a82b3f6e9fe7a4c7a491a5c9be7363209db5ee2d046b76aff2743472f', '[\"*\"]', NULL, NULL, '2026-03-10 18:42:40', '2026-03-10 18:42:40'),
(406, 'App\\Models\\User', 31, 'auth-token', '6100c171bbacc9e0b3ed85cd254e6a07072254cec3d0f6aef591580a9c5d0438', '[\"*\"]', NULL, NULL, '2026-03-10 19:15:10', '2026-03-10 19:15:10'),
(407, 'App\\Models\\User', 53, 'auth-token', '5c04ce54288524228379e1384153640211fc0bcbd8955bc7067e12e2a799d5a8', '[\"*\"]', NULL, NULL, '2026-03-10 19:20:36', '2026-03-10 19:20:36'),
(408, 'App\\Models\\User', 28, 'auth-token', 'eb572a1d37f9fc251b35bd8a5b317ecc416c959025304125844ea248062037ac', '[\"*\"]', NULL, NULL, '2026-03-11 10:26:07', '2026-03-11 10:26:07'),
(409, 'App\\Models\\User', 28, 'auth-token', '2f1cddee2de623b7964c5e0950d2274faf9764665522ce36b20e93007c6515f0', '[\"*\"]', NULL, NULL, '2026-03-12 14:01:07', '2026-03-12 14:01:07'),
(410, 'App\\Models\\User', 51, 'auth-token', '018079a67555b5b99093d31ce19e01f5696fcc2355100daae298f43c5922fdf1', '[\"*\"]', NULL, NULL, '2026-03-12 14:01:37', '2026-03-12 14:01:37'),
(411, 'App\\Models\\User', 51, 'auth-token', 'b2b47e2bb581a21205a38857aa8eba17275cbc0e632f23a79bb8da4c498aea26', '[\"*\"]', NULL, NULL, '2026-03-12 14:04:41', '2026-03-12 14:04:41'),
(412, 'App\\Models\\User', 50, 'auth-token', '2e149af0ab9a8d8e15e052fc21bc5d71a1ee57c4a594c2c807974bfc7d79b9e1', '[\"*\"]', NULL, NULL, '2026-03-12 14:06:41', '2026-03-12 14:06:41'),
(413, 'App\\Models\\User', 49, 'auth-token', '9d0d350a78088cd920a6614ca151dea6acf5ce8e31464919bd21a7dbfb0d545e', '[\"*\"]', NULL, NULL, '2026-03-12 14:08:54', '2026-03-12 14:08:54'),
(414, 'App\\Models\\User', 60, 'auth-token', 'b19068f8697be0b2abf20c689fbbc5a4fcd11095bd2b92b88612d3032d905adc', '[\"*\"]', NULL, NULL, '2026-03-12 14:11:11', '2026-03-12 14:11:11'),
(415, 'App\\Models\\User', 60, 'auth-token', 'ed8bc861cdfdd243d7979cccf9299b3cc7f50625118a451e893ee6d1d6e4d338', '[\"*\"]', NULL, NULL, '2026-03-12 14:11:51', '2026-03-12 14:11:51'),
(416, 'App\\Models\\User', 28, 'auth-token', '7969b0bba060fc41a16f7947431768119b839b22edc9ef95c87717e04fa52b99', '[\"*\"]', NULL, NULL, '2026-03-12 14:12:36', '2026-03-12 14:12:36'),
(417, 'App\\Models\\User', 28, 'auth-token', 'da2c748baa9a1f3da1bee5995adeac56b45bf79774efc5f9653109ec04729150', '[\"*\"]', NULL, NULL, '2026-03-12 14:13:32', '2026-03-12 14:13:32'),
(418, 'App\\Models\\User', 53, 'auth-token', '9537d4f600f4c502cfb6fa5080413a072b9fafe56dc1c2fbd79c75181fea71c5', '[\"*\"]', NULL, NULL, '2026-03-12 14:22:05', '2026-03-12 14:22:05'),
(419, 'App\\Models\\User', 28, 'auth-token', 'e665f0e456431cb9aa70a60152bef70a6f38a713daf339e0879521fefcfd0489', '[\"*\"]', NULL, NULL, '2026-03-12 14:22:50', '2026-03-12 14:22:50'),
(420, 'App\\Models\\User', 28, 'auth-token', '31d94a33eb315784e02d9f6066a17cb8bd7d8e2abbef369b0e110b35d73885e0', '[\"*\"]', NULL, NULL, '2026-03-12 14:23:22', '2026-03-12 14:23:22'),
(421, 'App\\Models\\User', 28, 'auth-token', '0f6b96714c494c8f1607acf0580759a714fc5d9758f86543ffdcf9d515e145be', '[\"*\"]', NULL, NULL, '2026-03-12 14:23:36', '2026-03-12 14:23:36'),
(422, 'App\\Models\\User', 28, 'auth-token', '51fb24ef532322ff1d14611d6887ac00d1fb89f4b89c1b55e29e99dccca3cb6f', '[\"*\"]', NULL, NULL, '2026-03-12 14:27:08', '2026-03-12 14:27:08'),
(423, 'App\\Models\\User', 28, 'auth-token', '1e3011d10eb6e6de8dde47db6c6bb36bbdfbad7d0e024898192418695cdf8c6c', '[\"*\"]', NULL, NULL, '2026-03-12 14:32:44', '2026-03-12 14:32:44'),
(424, 'App\\Models\\User', 28, 'auth-token', '9c82d1d600cd9d3389799d0a31a2f8c64bd14955a518094749bfc851c02361d8', '[\"*\"]', NULL, NULL, '2026-03-12 14:38:39', '2026-03-12 14:38:39'),
(425, 'App\\Models\\User', 31, 'auth-token', 'd8eb9928c112f035eafdd56a8e270ff1aa8ba86be38aa5108d0cc69bafb11686', '[\"*\"]', NULL, NULL, '2026-03-12 14:39:20', '2026-03-12 14:39:20'),
(426, 'App\\Models\\User', 70, 'auth-token', 'f86d9b7793d0471196576e9c0cfc85d710623c982dde8a9f8d9cdafedee66e33', '[\"*\"]', NULL, NULL, '2026-03-12 14:40:22', '2026-03-12 14:40:22'),
(427, 'App\\Models\\User', 70, 'auth-token', '8cb3f35bde43ba35cddaa05fd133b15b45f88c8e9c171c2dd86fb86d91aaecb4', '[\"*\"]', NULL, NULL, '2026-03-12 14:41:03', '2026-03-12 14:41:03'),
(428, 'App\\Models\\User', 72, 'auth-token', 'b1af012307c5fc5a28769ca5170f1a583fcc7ef0a390af905aef2a9061310f7b', '[\"*\"]', NULL, NULL, '2026-03-12 14:44:17', '2026-03-12 14:44:17'),
(429, 'App\\Models\\User', 70, 'auth-token', 'b9883a99e4666fc6e20d84ba3adda1da68b7ca58b877fafefa432bdc84dd6df6', '[\"*\"]', NULL, NULL, '2026-03-12 14:48:41', '2026-03-12 14:48:41'),
(430, 'App\\Models\\User', 72, 'auth-token', '6c170dd4680bf3046932e0d607aa04a72f23c66ba2c6a317e9a5d9849a3eaba5', '[\"*\"]', NULL, NULL, '2026-03-12 14:50:52', '2026-03-12 14:50:52'),
(431, 'App\\Models\\User', 72, 'auth-token', '04129ee9724bdd65e25db65420212774ae1938bc721a3d1f77f96a10efca4ce8', '[\"*\"]', NULL, NULL, '2026-03-12 14:51:35', '2026-03-12 14:51:35'),
(432, 'App\\Models\\User', 72, 'auth-token', '56c78dd06d302c84e0e1226c92909dee18fb0f9061bcb5785ddd8d9cbf72d94a', '[\"*\"]', NULL, NULL, '2026-03-12 14:52:20', '2026-03-12 14:52:20'),
(433, 'App\\Models\\User', 72, 'auth-token', '34989a3d7961ffbe5e181ee5d3c21b66ad12d7d3f1433c05c47c06d8c64a40e1', '[\"*\"]', NULL, NULL, '2026-03-12 14:52:40', '2026-03-12 14:52:40'),
(434, 'App\\Models\\User', 70, 'auth-token', 'c1d67e8b64ddf57065984487323c0c95654179b9ce2aefb99267211e7553b645', '[\"*\"]', NULL, NULL, '2026-03-12 15:00:21', '2026-03-12 15:00:21'),
(435, 'App\\Models\\User', 28, 'auth-token', 'df6ee87f0122f21bbbd46ab1b0a894e40e60092eaafcf36c634349f27d05800a', '[\"*\"]', NULL, NULL, '2026-03-12 15:27:50', '2026-03-12 15:27:50'),
(436, 'App\\Models\\User', 70, 'auth-token', '30cf097c18738f91f368591ad71bac7b20a43dc3defcdf5301f62ea01bb36433', '[\"*\"]', NULL, NULL, '2026-03-12 15:42:11', '2026-03-12 15:42:11'),
(437, 'App\\Models\\User', 73, 'auth-token', '6e215cae6eb95167b7e6dc7d98d01fa72656ec357e7ba91949e86b00406505a0', '[\"*\"]', NULL, NULL, '2026-03-12 15:58:06', '2026-03-12 15:58:06'),
(438, 'App\\Models\\User', 73, 'auth-token', 'ca18ca0d07d6ee05de515005e60862305d9738504652b8473c32646b1d1217f8', '[\"*\"]', NULL, NULL, '2026-03-12 15:58:38', '2026-03-12 15:58:38'),
(439, 'App\\Models\\User', 73, 'auth-token', '2b384c350b51f67db5f47ceeb101f9e1695c0caa8b75c47ff63f7896711e818b', '[\"*\"]', NULL, NULL, '2026-03-12 16:12:35', '2026-03-12 16:12:35'),
(440, 'App\\Models\\User', 28, 'auth-token', '2aef618bba3f0d416188141bc1e8318dd30f5a19d0ae7bcb21c066b350f4e2d9', '[\"*\"]', NULL, NULL, '2026-03-12 16:12:49', '2026-03-12 16:12:49'),
(441, 'App\\Models\\User', 74, 'auth-token', '7da4b3cb58a11d1207a70bc7a6d29139ee1721d9ccd447e363aec90c8e500f3f', '[\"*\"]', NULL, NULL, '2026-03-12 16:16:47', '2026-03-12 16:16:47'),
(442, 'App\\Models\\User', 28, 'auth-token', '8d734cb3100e8a5dcf2969398cd86d5a1cf329afbfc170bdd18aebd5faa0d463', '[\"*\"]', NULL, NULL, '2026-03-12 16:19:13', '2026-03-12 16:19:13'),
(443, 'App\\Models\\User', 76, 'auth-token', '33054fff2c1f3920cf0c8acfceefffc90b80ef92ff37ee80ecbee6244f4d977b', '[\"*\"]', NULL, NULL, '2026-03-12 16:22:54', '2026-03-12 16:22:54'),
(444, 'App\\Models\\User', 76, 'auth-token', '0438d9a5e8d3b4f4aa530b8afad43c583064c6d2acf2fc2b72bc9ba7795f0735', '[\"*\"]', NULL, NULL, '2026-03-12 16:23:18', '2026-03-12 16:23:18'),
(445, 'App\\Models\\User', 79, 'auth-token', '8ec6c6bb7fa735a7687f7ec674de0872dcfdb3b3710c7dac02e9429bd8547a06', '[\"*\"]', NULL, NULL, '2026-03-12 16:26:14', '2026-03-12 16:26:14'),
(446, 'App\\Models\\User', 79, 'auth-token', '909c30dac2b715e78b7548f602bd77c495df0df2f4f4c0270ad5f5165dcaf1f1', '[\"*\"]', NULL, NULL, '2026-03-12 16:27:50', '2026-03-12 16:27:50'),
(447, 'App\\Models\\User', 76, 'auth-token', 'df0668922984ceed9ff10134738f50bb78e6f877122cad166f5d7bae594d2f77', '[\"*\"]', NULL, NULL, '2026-03-12 16:39:42', '2026-03-12 16:39:42'),
(448, 'App\\Models\\User', 80, 'auth-token', '3ea1a27851aa04c354716948b7fdde20994b40716e6519fc2d0140eba20ff08b', '[\"*\"]', NULL, NULL, '2026-03-12 16:41:24', '2026-03-12 16:41:24'),
(449, 'App\\Models\\User', 80, 'auth-token', '59b720cdb760b542ac6151a4ca471e82dd1604432b1aa84a5cd75cfaa074fa80', '[\"*\"]', NULL, NULL, '2026-03-12 16:42:50', '2026-03-12 16:42:50'),
(450, 'App\\Models\\User', 28, 'auth-token', '29d26042a85063afc1ed33ea88182cb63b3ed80f764d5d7f756cc1af89ffe2d3', '[\"*\"]', NULL, NULL, '2026-03-12 16:47:43', '2026-03-12 16:47:43'),
(451, 'App\\Models\\User', 28, 'auth-token', 'be4106e3a52163200dbb4ac1ce721368c5a4e9022a324faf4029d3beef97fa2a', '[\"*\"]', NULL, NULL, '2026-03-12 16:48:26', '2026-03-12 16:48:26'),
(452, 'App\\Models\\User', 82, 'auth-token', '2b315576a5adb2f7ec88de76e07fe337dc9fb11fe46e4aa018b29f9a8fbc5cc8', '[\"*\"]', NULL, NULL, '2026-03-12 16:49:44', '2026-03-12 16:49:44'),
(453, 'App\\Models\\User', 28, 'auth-token', 'b364f7a011706ae17b1c918251b49a93ffc5cdbcc2e21c641ef31eb9c6b6dadf', '[\"*\"]', NULL, NULL, '2026-03-12 16:52:05', '2026-03-12 16:52:05'),
(454, 'App\\Models\\User', 86, 'auth-token', '46628c20c77a1d3156f240ba2e4f62eccb99286634d9b334e9c1a14c57c3533c', '[\"*\"]', NULL, NULL, '2026-03-12 16:58:04', '2026-03-12 16:58:04'),
(455, 'App\\Models\\User', 86, 'auth-token', 'fa449e48248c76d4a51382cc9b30e78e939b0f105daa96b7f340bea18013d841', '[\"*\"]', NULL, NULL, '2026-03-12 16:58:53', '2026-03-12 16:58:53'),
(456, 'App\\Models\\User', 28, 'auth-token', 'f03c7909790346b8a20c6b454980bd7af08d72b626589d0a3cba08b0ea091f9b', '[\"*\"]', NULL, NULL, '2026-03-13 03:44:41', '2026-03-13 03:44:41'),
(457, 'App\\Models\\User', 89, 'auth-token', '05bb3dfac407866c5081d3f72ec93c019da4909e651e4da5cd6b1fce372a5cd0', '[\"*\"]', NULL, NULL, '2026-03-13 03:46:19', '2026-03-13 03:46:19'),
(458, 'App\\Models\\User', 89, 'auth-token', '7ff05611a3d1ad6f6536696bc4b29943c1196492d7b7569740bdd96b05ab919a', '[\"*\"]', NULL, NULL, '2026-03-13 03:48:46', '2026-03-13 03:48:46'),
(459, 'App\\Models\\User', 86, 'auth-token', '037d355909de51b4aec1ac88a528ace4aef4d53f34fc79f30a0967555e9c1de3', '[\"*\"]', NULL, NULL, '2026-03-13 03:49:06', '2026-03-13 03:49:06'),
(460, 'App\\Models\\User', 88, 'auth-token', '4aa403adfdcaf1bc02ca36fbf5eace5148334608b14c993725a3d9826aaaabac', '[\"*\"]', NULL, NULL, '2026-03-13 03:49:55', '2026-03-13 03:49:55'),
(461, 'App\\Models\\User', 31, 'auth-token', '5df28614b9011a0810ca79c87231036409796d283e4a51a979d5bdee2409cde0', '[\"*\"]', NULL, NULL, '2026-03-13 03:50:05', '2026-03-13 03:50:05'),
(462, 'App\\Models\\User', 31, 'auth-token', 'e5265b0d9374210b500abcee6edc659c63e414eb5d29275612dfb46524b70819', '[\"*\"]', NULL, NULL, '2026-03-13 03:51:03', '2026-03-13 03:51:03'),
(463, 'App\\Models\\User', 90, 'auth-token', '5f63bac7924a20e3f008709cbf27c0d986f3d7b0d72fe3f57454c4c8e062f74e', '[\"*\"]', NULL, NULL, '2026-03-13 03:52:03', '2026-03-13 03:52:03'),
(464, 'App\\Models\\User', 90, 'auth-token', '138188ba572eb9bf52f50f0e3e7d13d8978961956afbad7457b231f420fbe3ec', '[\"*\"]', NULL, NULL, '2026-03-13 03:53:16', '2026-03-13 03:53:16'),
(465, 'App\\Models\\User', 31, 'auth-token', 'eefb9cffd1c1957795d2006c69110bef836f281253454b582f79edfa7987679c', '[\"*\"]', NULL, NULL, '2026-03-13 03:54:05', '2026-03-13 03:54:05'),
(466, 'App\\Models\\User', 91, 'auth-token', 'f9d97ac8dc5ee432e2abfb92b93ef9dc01b4e8f30db2663dd27b8c8b4d92e971', '[\"*\"]', NULL, NULL, '2026-03-13 03:54:34', '2026-03-13 03:54:34'),
(467, 'App\\Models\\User', 31, 'auth-token', 'd2ae4ccaaae76b33d19157406bf7f37e76c05197b587b5314ba28f6716ea85c7', '[\"*\"]', NULL, NULL, '2026-03-13 03:56:20', '2026-03-13 03:56:20'),
(468, 'App\\Models\\User', 91, 'auth-token', '09fc43441f9b30594e80e0df9df00ece1a66d07a2f9616b10871758517adc1a2', '[\"*\"]', NULL, NULL, '2026-03-13 03:56:42', '2026-03-13 03:56:42'),
(469, 'App\\Models\\User', 91, 'auth-token', '7206b0e5ad5ea45551a5d123c4b3f54a384dae201b2cd25ce1b8ae8ef8d5945c', '[\"*\"]', NULL, NULL, '2026-03-13 03:57:10', '2026-03-13 03:57:10'),
(470, 'App\\Models\\User', 92, 'auth-token', '5d8fd40f86a3b107d6a75b919f9386fe7da776882845341c5488bbe888e3fb08', '[\"*\"]', NULL, NULL, '2026-03-13 03:57:34', '2026-03-13 03:57:34'),
(471, 'App\\Models\\User', 92, 'auth-token', '7ed16f7fe07cbf3fde4a55f877261ad6a29df0b529b1d446a69dec18c22885bc', '[\"*\"]', NULL, NULL, '2026-03-13 03:58:38', '2026-03-13 03:58:38'),
(472, 'App\\Models\\User', 91, 'auth-token', '70d6814f73e524037832eb291f5467af2d0c2780841d2aa3287fcdf56ab87736', '[\"*\"]', NULL, NULL, '2026-03-13 03:58:54', '2026-03-13 03:58:54'),
(473, 'App\\Models\\User', 93, 'auth-token', 'b4a9607f883741c7abf4000c172d411718240985f4111c78acc704b09306500a', '[\"*\"]', NULL, NULL, '2026-03-13 03:59:17', '2026-03-13 03:59:17'),
(474, 'App\\Models\\User', 91, 'auth-token', '4d6e143de006ba57e920ddcf2641926f9500ec69880f219a7a08ab4ea8bacb34', '[\"*\"]', NULL, NULL, '2026-03-13 04:00:43', '2026-03-13 04:00:43'),
(475, 'App\\Models\\User', 90, 'auth-token', '004d9fdb1e04657f8d96deba9a53a794cf9ef7c0e42dba5133663124a67deddd', '[\"*\"]', NULL, NULL, '2026-03-13 04:01:05', '2026-03-13 04:01:05'),
(476, 'App\\Models\\User', 31, 'auth-token', 'f805b22b004fed32feee6193537408cdc849056b5c9605e952510028888201c1', '[\"*\"]', NULL, NULL, '2026-03-13 04:15:15', '2026-03-13 04:15:15'),
(477, 'App\\Models\\User', 90, 'auth-token', 'd945739cada7e23827b491fc76314b01b984cefa4f6f8e7ebf63fb1c06fea2ab', '[\"*\"]', NULL, NULL, '2026-03-13 04:15:39', '2026-03-13 04:15:39'),
(478, 'App\\Models\\User', 31, 'auth-token', '6f079e3c47c66ea3427339ae46b67ef20565a91721825e0053218229c557ba41', '[\"*\"]', NULL, NULL, '2026-03-13 04:15:56', '2026-03-13 04:15:56'),
(479, 'App\\Models\\User', 93, 'auth-token', '66fbc5fb05efff388a1b0903b53e8c1e54b4ba2af59348857432e76683331b00', '[\"*\"]', NULL, NULL, '2026-03-13 04:16:25', '2026-03-13 04:16:25'),
(480, 'App\\Models\\User', 90, 'auth-token', '7078c45fa759f26e35d2dbdd2d07b55171231ac567714323b4d2dbefc0b8fae9', '[\"*\"]', NULL, NULL, '2026-03-13 04:16:46', '2026-03-13 04:16:46'),
(481, 'App\\Models\\User', 93, 'auth-token', '94683806f6ba63a36972443f0393e014ca95723c458441dac7cdc67814200d07', '[\"*\"]', NULL, NULL, '2026-03-13 04:17:35', '2026-03-13 04:17:35'),
(482, 'App\\Models\\User', 93, 'auth-token', 'beef2b050bb7355ac6a86fe6e9202552f87850e4122e9588233a9dc6255306ed', '[\"*\"]', NULL, NULL, '2026-03-13 04:17:56', '2026-03-13 04:17:56'),
(483, 'App\\Models\\User', 31, 'auth-token', '914066d36a043157a537ab0299770b6f335ade0a566ed6898006718d5f0c33c2', '[\"*\"]', NULL, NULL, '2026-03-13 04:18:09', '2026-03-13 04:18:09'),
(484, 'App\\Models\\User', 93, 'auth-token', '012afc59b2da9accdc51bd2de086b7a49ca1cb5469e1829b931fa8d005157fc4', '[\"*\"]', NULL, NULL, '2026-03-13 04:18:25', '2026-03-13 04:18:25'),
(485, 'App\\Models\\User', 91, 'auth-token', '4e515832da9064cb053f38c88b929996f9697a0db20bf8fe119dcaaac10fd168', '[\"*\"]', NULL, NULL, '2026-03-13 04:18:43', '2026-03-13 04:18:43'),
(486, 'App\\Models\\User', 98, 'auth-token', '560dc96de4038ab1c9d3469dfcea5329896a205dab9fe371f80beaf3f02b5f8b', '[\"*\"]', NULL, NULL, '2026-03-13 04:21:04', '2026-03-13 04:21:04'),
(487, 'App\\Models\\User', 98, 'auth-token', 'c926687483868a3b786f683112783804ec5cf1f015751a38276439e3e53846b7', '[\"*\"]', NULL, NULL, '2026-03-13 04:21:49', '2026-03-13 04:21:49'),
(488, 'App\\Models\\User', 90, 'auth-token', 'ea177706288a10b9bddc2c35dd34b455e13363c5e03c2bc62d54767dd8c48f9a', '[\"*\"]', NULL, NULL, '2026-03-13 04:22:06', '2026-03-13 04:22:06'),
(489, 'App\\Models\\User', 31, 'auth-token', 'ee1c951e50dc415bf8b2d31a342a5dc44c11c4e11f4fbf2df217d1da39947e8c', '[\"*\"]', NULL, NULL, '2026-03-13 04:22:52', '2026-03-13 04:22:52'),
(490, 'App\\Models\\User', 98, 'auth-token', '2a02165b2150633d618bb8242382a67c870da053cefda2faac360a5e0ec504b8', '[\"*\"]', NULL, NULL, '2026-03-13 04:27:50', '2026-03-13 04:27:50'),
(491, 'App\\Models\\User', 90, 'auth-token', '6c0e7261333f65faa4cddd1263e864e23885e2b6655c94a1d11645d7add71159', '[\"*\"]', NULL, NULL, '2026-03-13 04:32:21', '2026-03-13 04:32:21'),
(492, 'App\\Models\\User', 28, 'auth-token', 'c049476f459dc29fad1a632e53398075ff7680feace396d3b349da675ce6a282', '[\"*\"]', NULL, NULL, '2026-03-13 04:32:40', '2026-03-13 04:32:40'),
(493, 'App\\Models\\User', 90, 'auth-token', '97b92d35bf0f3c6d877ef33c8440fb8e94715f7ff44fc1a01527fcacb6b7d885', '[\"*\"]', NULL, NULL, '2026-03-13 04:33:06', '2026-03-13 04:33:06'),
(494, 'App\\Models\\User', 92, 'auth-token', 'f567a3a122a36b640b170a1e968f04a286aba98930588f6dc9ef6e71eb811dac', '[\"*\"]', NULL, NULL, '2026-03-13 04:33:22', '2026-03-13 04:33:22'),
(495, 'App\\Models\\User', 98, 'auth-token', 'e9e47936e4b08663a095d47f1f7d2b2f43d378b5fa1d23248f3d8e34353fe4e6', '[\"*\"]', NULL, NULL, '2026-03-13 04:33:43', '2026-03-13 04:33:43'),
(496, 'App\\Models\\User', 91, 'auth-token', 'be27aa67d14da422253962e5c6e08a09c314e9fc2cc6c91e4225657dc8c3d4bd', '[\"*\"]', NULL, NULL, '2026-03-13 04:34:36', '2026-03-13 04:34:36'),
(497, 'App\\Models\\User', 98, 'auth-token', '41680709434eeb76ec32e11a9517f99fb3c0b0e135b3629322edb62e13a67019', '[\"*\"]', NULL, NULL, '2026-03-13 04:39:52', '2026-03-13 04:39:52'),
(498, 'App\\Models\\User', 91, 'auth-token', '04220d35121b0a769409f4304f579abc63378a832a8f94d73c07dd65385007d3', '[\"*\"]', NULL, NULL, '2026-03-13 04:40:17', '2026-03-13 04:40:17'),
(499, 'App\\Models\\User', 99, 'auth-token', '5f6b3f2b37dd3fa4ccbc46190eb88ea08d8c5aac32562824538f350ff2231800', '[\"*\"]', NULL, NULL, '2026-03-13 04:41:37', '2026-03-13 04:41:37'),
(500, 'App\\Models\\User', 99, 'auth-token', 'fb94ade67832d5670733c8f3f91957caa6c401f515771f1b74823530135461eb', '[\"*\"]', NULL, NULL, '2026-03-13 04:42:20', '2026-03-13 04:42:20'),
(501, 'App\\Models\\User', 93, 'auth-token', '1a64ee2a06248ed4345c247f35cbd9ca3acd2adcdcabfa6d9331b37dba8935cb', '[\"*\"]', NULL, NULL, '2026-03-13 04:43:18', '2026-03-13 04:43:18'),
(502, 'App\\Models\\User', 98, 'auth-token', '702ac1bcbf3948fc376602ec7c3e8fa98c9994f79717fc1bafb34880088a1ec6', '[\"*\"]', NULL, NULL, '2026-03-13 04:47:17', '2026-03-13 04:47:17'),
(503, 'App\\Models\\User', 98, 'auth-token', '855124b4e4d9b206baac988f203ed7fb118a2685eaa5e0319542eb68b7af9313', '[\"*\"]', NULL, NULL, '2026-03-13 05:24:07', '2026-03-13 05:24:07'),
(504, 'App\\Models\\User', 99, 'auth-token', '1c75e34655a1a593ea1b8233f9e9a4a5aca9ae12312aacaf7f4d4f57e7276eb4', '[\"*\"]', NULL, NULL, '2026-03-13 05:24:18', '2026-03-13 05:24:18'),
(505, 'App\\Models\\User', 98, 'auth-token', '6a662b3ce9fc53f5f455cd2cfffb3d1885b09c7eefc9c1fdf9ff75f6b421582e', '[\"*\"]', NULL, NULL, '2026-03-13 05:24:31', '2026-03-13 05:24:31'),
(506, 'App\\Models\\User', 99, 'auth-token', '3d4ad3323552d3c62a81672f93a3e7e7df5dd7b0e52b3cf0ea26c2fb3da76365', '[\"*\"]', NULL, NULL, '2026-03-13 05:24:43', '2026-03-13 05:24:43'),
(507, 'App\\Models\\User', 91, 'auth-token', '8a8c4c2fd2e4d91d5adf4d7ad36d3a6f7574af9d683ed7d096c7f70824fb72ac', '[\"*\"]', NULL, NULL, '2026-03-13 05:25:07', '2026-03-13 05:25:07'),
(508, 'App\\Models\\User', 100, 'auth-token', 'b3c86bc1a875da7b5f9dbff59af1726ad5856f85f5fd8a63089c4d37a0329740', '[\"*\"]', NULL, NULL, '2026-03-13 05:26:47', '2026-03-13 05:26:47'),
(509, 'App\\Models\\User', 100, 'auth-token', 'eb02dfe09300351f9558aff6c05b84d33f138d8141f2e64ae85da7850c52df63', '[\"*\"]', NULL, NULL, '2026-03-13 05:28:00', '2026-03-13 05:28:00'),
(510, 'App\\Models\\User', 31, 'auth-token', '40fa3fd134c3e4f62e5318ca6d7a0ee00a3d6918a5bca8414c0396eb756a3455', '[\"*\"]', NULL, NULL, '2026-03-13 05:49:02', '2026-03-13 05:49:02'),
(511, 'App\\Models\\User', 31, 'auth-token', '36ec471ce48b8341992bb59dfcccd66dca82466fb8d1f45430ee7c59c3e86155', '[\"*\"]', NULL, NULL, '2026-03-13 05:50:08', '2026-03-13 05:50:08'),
(512, 'App\\Models\\User', 31, 'auth-token', '4bdd7566ed5eb93b94d4097c35e9e0e963366960ee65d674124d87e30b99c5a3', '[\"*\"]', NULL, NULL, '2026-03-13 05:55:12', '2026-03-13 05:55:12'),
(513, 'App\\Models\\User', 31, 'auth-token', 'da24c3048bfe858e9cab5a55c44435c1d2bf3574d3f6f155a29b36575a753819', '[\"*\"]', NULL, NULL, '2026-03-13 05:59:17', '2026-03-13 05:59:17'),
(514, 'App\\Models\\User', 31, 'auth-token', '2ee113a03c033a0764d0562f001ea151aaabf6827e31e40bd96625134ededcb9', '[\"*\"]', NULL, NULL, '2026-03-13 06:01:03', '2026-03-13 06:01:03'),
(515, 'App\\Models\\User', 31, 'auth-token', '10797ff4f80899778c35e99fbe998e0abd30856c16ff649aa8fc2d5e8a863e15', '[\"*\"]', NULL, NULL, '2026-03-13 06:02:33', '2026-03-13 06:02:33'),
(516, 'App\\Models\\User', 31, 'auth-token', 'bbec249f3d66fa99a1ad858d39ee72b8673e610085e3cf9ed08fbaf8f0e2b91a', '[\"*\"]', NULL, NULL, '2026-03-13 06:47:29', '2026-03-13 06:47:29'),
(517, 'App\\Models\\User', 31, 'auth-token', '1a3efc497f776ab55baeb71ad12a22f9986082b5b09f4e63d530679ca312c72d', '[\"*\"]', NULL, NULL, '2026-03-13 13:42:13', '2026-03-13 13:42:13'),
(518, 'App\\Models\\User', 90, 'auth-token', '20fef40e4d6f3d61bf44667670613ad18aa2842f730ec08f42d2540749252b82', '[\"*\"]', NULL, NULL, '2026-03-13 13:42:58', '2026-03-13 13:42:58'),
(519, 'App\\Models\\User', 31, 'auth-token', '7a55493f1b7ef41eddb1dbec9b013acf15b17cccac700026d5eed951b73b4cbc', '[\"*\"]', NULL, NULL, '2026-03-13 13:43:44', '2026-03-13 13:43:44'),
(520, 'App\\Models\\User', 28, 'auth-token', '281180f7ffe87f3f237e46b9b024ff4f2548696d58f561a2dee9b4a0c792ec3c', '[\"*\"]', NULL, NULL, '2026-03-13 13:45:15', '2026-03-13 13:45:15'),
(521, 'App\\Models\\User', 31, 'auth-token', '5e4adb7df9f0fd9e2a9bf3ac673adeb24bffc5f4049fdd762e903d330f858a5f', '[\"*\"]', NULL, NULL, '2026-03-13 13:45:58', '2026-03-13 13:45:58'),
(522, 'App\\Models\\User', 31, 'auth-token', 'e667a87c316f1ba567c76c8f05936179d42f5f2749823b1d0fdf01868ce8a349', '[\"*\"]', NULL, NULL, '2026-03-13 13:48:38', '2026-03-13 13:48:38'),
(523, 'App\\Models\\User', 31, 'auth-token', '1c821039fa0dc11934e4c0130f62c388ceb0edf4bf944389c018933174f93d74', '[\"*\"]', NULL, NULL, '2026-03-13 13:50:04', '2026-03-13 13:50:04'),
(524, 'App\\Models\\User', 28, 'auth-token', 'bab799bbdcc814e19b8691aea8419ca10efabea9b64a274b81b84aa67d52e7a4', '[\"*\"]', NULL, NULL, '2026-03-13 14:04:58', '2026-03-13 14:04:58'),
(525, 'App\\Models\\User', 100, 'auth-token', '5a4d75b5d1d60629632fb7cc0a1a799e26bd666a232e6b675ce6c590afd54e38', '[\"*\"]', NULL, NULL, '2026-03-13 14:05:56', '2026-03-13 14:05:56'),
(526, 'App\\Models\\User', 31, 'auth-token', '3edfee1bc259120d64f051a8c7a0d130cb7bafcda7bcbcde76068b27e2306b3b', '[\"*\"]', NULL, NULL, '2026-03-13 14:06:18', '2026-03-13 14:06:18'),
(527, 'App\\Models\\User', 99, 'auth-token', '5dc894ebf8a25662182e00e39a8fcf62e077158fdfaa06f27806dfc386fdbd85', '[\"*\"]', NULL, NULL, '2026-03-13 14:08:01', '2026-03-13 14:08:01'),
(528, 'App\\Models\\User', 93, 'auth-token', 'a8d34ff33ea3330c7cc9df57491b8cd0723e9994f803c1f91bc4dcab52effeeb', '[\"*\"]', NULL, NULL, '2026-03-13 14:09:00', '2026-03-13 14:09:00'),
(529, 'App\\Models\\User', 92, 'auth-token', 'b151adbc600e0f6fb8935bf3b495eed5376e94db18ffa4a5625aa3ebe7e0b277', '[\"*\"]', NULL, NULL, '2026-03-13 14:09:45', '2026-03-13 14:09:45'),
(530, 'App\\Models\\User', 99, 'auth-token', '3788be323aec8e44b9337688547f8b4f9aa11d25da16c5ea7677dd4e77de974f', '[\"*\"]', NULL, NULL, '2026-03-13 14:10:13', '2026-03-13 14:10:13'),
(531, 'App\\Models\\User', 28, 'auth-token', '4bba903c4a45e5226170a548528a20cb06549c04b97249170ffa1ea27c1119b4', '[\"*\"]', NULL, NULL, '2026-03-13 14:10:53', '2026-03-13 14:10:53'),
(532, 'App\\Models\\User', 28, 'auth-token', '6ccee3e4bbf71e9e29be40c7b6a98feb6383dfe748a9c83357d4e246e306dbd6', '[\"*\"]', NULL, NULL, '2026-03-13 14:11:39', '2026-03-13 14:11:39'),
(533, 'App\\Models\\User', 93, 'auth-token', '512f004b1a10e2dbc5c0945b38fbb523b2c1eec0b046b5e6d29fc21977facb41', '[\"*\"]', NULL, NULL, '2026-03-13 14:14:20', '2026-03-13 14:14:20'),
(534, 'App\\Models\\User', 99, 'auth-token', '7fcbb1def5f0d6a8b039723a8c509869a8f285e5aa8bc8fe5d26a45854748ba6', '[\"*\"]', NULL, NULL, '2026-03-13 14:14:46', '2026-03-13 14:14:46'),
(535, 'App\\Models\\User', 28, 'auth-token', 'cef21c2e724b176e32417b3333f430370e9390f8438dd8682a6412cae7f82feb', '[\"*\"]', NULL, NULL, '2026-03-13 14:16:16', '2026-03-13 14:16:16'),
(536, 'App\\Models\\User', 99, 'auth-token', '9970e9c5e8397e074cd08c5827ffc467c3c40193c6a56f28dbd4ff111db33c7b', '[\"*\"]', NULL, NULL, '2026-03-13 14:17:00', '2026-03-13 14:17:00'),
(537, 'App\\Models\\User', 28, 'auth-token', 'fb0f12bb0ebc60349e4d730fa53de7fab4a2b44b24bf10a7d3270ffc3cb26e46', '[\"*\"]', NULL, NULL, '2026-03-13 14:18:43', '2026-03-13 14:18:43'),
(538, 'App\\Models\\User', 28, 'auth-token', '8640fb9ec8f0a8475510cb565c3f4897b2990d306babbc3ee00197aa50ae509c', '[\"*\"]', NULL, NULL, '2026-03-13 14:23:23', '2026-03-13 14:23:23'),
(539, 'App\\Models\\User', 31, 'auth-token', '5dac6e00d7eee18e938ce2f4c875901ce7140d4e2e6b8ab2e053e52bba8b756f', '[\"*\"]', NULL, NULL, '2026-03-13 14:25:03', '2026-03-13 14:25:03'),
(540, 'App\\Models\\User', 110, 'auth-token', '976eca1b1cae55955fdfa1de9a22212741b805de62f87d091b791283e22f5ee4', '[\"*\"]', NULL, NULL, '2026-03-13 14:27:16', '2026-03-13 14:27:16'),
(541, 'App\\Models\\User', 110, 'auth-token', '34a26143b1a7dcf3f06ad662372b6d44df367fd945150a93fade75223c5fe864', '[\"*\"]', NULL, NULL, '2026-03-13 14:29:04', '2026-03-13 14:29:04'),
(542, 'App\\Models\\User', 28, 'auth-token', '8ce65fe7833888beb6c3c889a9821613d671bbe3601edda06dbca4478bbf72c0', '[\"*\"]', NULL, NULL, '2026-03-13 14:29:20', '2026-03-13 14:29:20'),
(543, 'App\\Models\\User', 92, 'auth-token', '9d99f0a96d6151d53485985fd05a8f53114a0905b6ee2badeea695b8b7d48d8a', '[\"*\"]', NULL, NULL, '2026-03-13 14:30:07', '2026-03-13 14:30:07'),
(544, 'App\\Models\\User', 91, 'auth-token', '1d5a05d4ec855d42177fb683ca881bb7527d3a954818c2b623ba5d5983388799', '[\"*\"]', NULL, NULL, '2026-03-13 14:30:56', '2026-03-13 14:30:56'),
(545, 'App\\Models\\User', 110, 'auth-token', 'db45961eb544957eb97872b9f0a0c35f21b24643ca28ccf9a4034b10905014c9', '[\"*\"]', NULL, NULL, '2026-03-13 14:31:21', '2026-03-13 14:31:21'),
(546, 'App\\Models\\User', 110, 'auth-token', '9c041433af8e7a6468d631bcd301c47b5385651a082a7f6e1e08bfd8daa31a23', '[\"*\"]', NULL, NULL, '2026-03-13 14:41:21', '2026-03-13 14:41:21'),
(547, 'App\\Models\\User', 93, 'auth-token', '2b951ce9a6b52e872e6c6fc25d3768f7ac56b6ffcf1a0aa6c0712c8b3b17e065', '[\"*\"]', NULL, NULL, '2026-03-13 14:42:46', '2026-03-13 14:42:46'),
(548, 'App\\Models\\User', 100, 'auth-token', 'e819685c59851218ee99ffe18c04453bfc8ecc81967ea684be8d149c8fe8927f', '[\"*\"]', NULL, NULL, '2026-03-13 14:45:08', '2026-03-13 14:45:08'),
(549, 'App\\Models\\User', 28, 'auth-token', 'e4d16a890976739e8f9298f6d91e9b18ad968d54f92c69ab6d8dcaf932617847', '[\"*\"]', NULL, NULL, '2026-03-13 14:48:26', '2026-03-13 14:48:26'),
(550, 'App\\Models\\User', 100, 'auth-token', '50752d3fbbe37e2a36209538af577015193e94fb39d4b22bcdf28ba46e2cfbf1', '[\"*\"]', NULL, NULL, '2026-03-13 15:00:34', '2026-03-13 15:00:34'),
(551, 'App\\Models\\User', 92, 'auth-token', '2d8592b4c29a6995ccd336ebcbbcc3916111e176c3af321db948d93b7911bec5', '[\"*\"]', NULL, NULL, '2026-03-13 15:04:31', '2026-03-13 15:04:31');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(552, 'App\\Models\\User', 90, 'auth-token', '2ae0f8e616555446a10767776fe9204d000af931c25dfe5a85f4ce2716c15d1b', '[\"*\"]', NULL, NULL, '2026-03-13 15:06:21', '2026-03-13 15:06:21'),
(553, 'App\\Models\\User', 90, 'auth-token', '39ce005f2634327769339dda1c37083c99f9bfac33190e2c64b03e1d12ae18d2', '[\"*\"]', NULL, NULL, '2026-03-13 15:09:22', '2026-03-13 15:09:22'),
(554, 'App\\Models\\User', 28, 'auth-token', 'affe17bae5816d9715721d679ef06d3cd9ea8e9d78ab296b4b17ffc5e0ea023e', '[\"*\"]', NULL, NULL, '2026-03-13 15:12:41', '2026-03-13 15:12:41'),
(555, 'App\\Models\\User', 28, 'auth-token', 'b7ba97534208fd28bef3d1960c1260c3ce13a71dea88e030a993320da157cb74', '[\"*\"]', NULL, NULL, '2026-03-14 03:59:19', '2026-03-14 03:59:19'),
(556, 'App\\Models\\User', 31, 'auth-token', '79a1c513e1d65de9e30b90a24d07569cb391d2570d6fa70c29e5efe25ed23b9d', '[\"*\"]', NULL, NULL, '2026-03-14 04:06:23', '2026-03-14 04:06:23'),
(557, 'App\\Models\\User', 91, 'auth-token', 'f6343d910ab48e7abb3c990e65d736cd373026e9f2179c2772ed4c7577f5f9cf', '[\"*\"]', NULL, NULL, '2026-03-14 04:15:07', '2026-03-14 04:15:07'),
(558, 'App\\Models\\User', 92, 'auth-token', '14a459a73444d38f6d5a28d892b1ccf18969f6050af00efd90368162d0babd4f', '[\"*\"]', NULL, NULL, '2026-03-14 04:16:13', '2026-03-14 04:16:13'),
(559, 'App\\Models\\User', 31, 'auth-token', '4065f83fe2e4ba12f51cf70e207cd5df29dd054c66fd7a6fc0f6694dda29ea5e', '[\"*\"]', NULL, NULL, '2026-03-14 04:30:05', '2026-03-14 04:30:05'),
(560, 'App\\Models\\User', 126, 'auth-token', '30faa75e3f102d3e9a1b82080c7d42074ba051dd69eeb8f315508fe9aa24f78f', '[\"*\"]', NULL, NULL, '2026-03-14 04:38:21', '2026-03-14 04:38:21'),
(561, 'App\\Models\\User', 126, 'auth-token', '16e6f8930f67ca3310cfb04df354a063a6a18f78b8680d7f2dfff047844fcb84', '[\"*\"]', NULL, NULL, '2026-03-14 04:40:04', '2026-03-14 04:40:04'),
(562, 'App\\Models\\User', 126, 'auth-token', '5e3210810957de8be989c0e95076171d7789b9ee977edb4da183ee2e88941837', '[\"*\"]', NULL, NULL, '2026-03-14 04:48:22', '2026-03-14 04:48:22'),
(563, 'App\\Models\\User', 126, 'auth-token', '43ca8690715c7d575794c4fd5023661636c58d7df7ab0107168984a9a8f7b017', '[\"*\"]', NULL, NULL, '2026-03-14 04:48:53', '2026-03-14 04:48:53'),
(564, 'App\\Models\\User', 126, 'auth-token', '9495e9918f957a844a93d6e247d84f799276e1b9b512eb35502e99ee7b47b3a3', '[\"*\"]', NULL, NULL, '2026-03-14 04:57:40', '2026-03-14 04:57:40'),
(565, 'App\\Models\\User', 28, 'auth-token', '41e7a4c5298c24a2b06d6d7d15aaa65e3fe673d3b477551cbb45a30cff8bf6b2', '[\"*\"]', NULL, NULL, '2026-03-14 04:58:20', '2026-03-14 04:58:20'),
(566, 'App\\Models\\User', 31, 'auth-token', '65008446c2c554513838b0f47b601c2088e33610416ada0d4803e28ed25a32fd', '[\"*\"]', NULL, NULL, '2026-03-14 05:10:43', '2026-03-14 05:10:43'),
(567, 'App\\Models\\User', 127, 'auth-token', '0c8696f670f3aaf8c835906849b946e080f91a678d5b0846301da262133aa81a', '[\"*\"]', NULL, NULL, '2026-03-14 05:11:27', '2026-03-14 05:11:27'),
(568, 'App\\Models\\User', 127, 'auth-token', '0587757ae64425ccc7097cf92de1907b67a58bc7752a1a187c5bdc049fe52894', '[\"*\"]', NULL, NULL, '2026-03-14 05:12:42', '2026-03-14 05:12:42'),
(569, 'App\\Models\\User', 28, 'auth-token', 'a05f8b285607ef09119ace16764872848bca8586ce13c4d094f6a53da38a47e5', '[\"*\"]', NULL, NULL, '2026-03-14 05:14:04', '2026-03-14 05:14:04'),
(570, 'App\\Models\\User', 126, 'auth-token', 'dcc93fd359307a02c583fa8344f14377fd9ac50e137e062b6bbf0a92792f33e5', '[\"*\"]', NULL, NULL, '2026-03-14 05:25:15', '2026-03-14 05:25:15'),
(571, 'App\\Models\\User', 31, 'auth-token', '8b7b2ddbab7b102533afed5ef9c982dd2e4c4814884e316a903e838a83a3acc8', '[\"*\"]', NULL, NULL, '2026-03-14 06:49:52', '2026-03-14 06:49:52'),
(572, 'App\\Models\\User', 124, 'auth-token', '6804aeae8d329635f8130317fe9a07b21f2d31372db572c104bce1cb4a6fdc71', '[\"*\"]', NULL, NULL, '2026-03-14 06:51:41', '2026-03-14 06:51:41'),
(573, 'App\\Models\\User', 31, 'auth-token', '9fecc758e10308a53bf49ee9fe8e6bd82995fea00dd8e0f184deddd288a2ff0d', '[\"*\"]', NULL, NULL, '2026-03-14 06:52:51', '2026-03-14 06:52:51'),
(574, 'App\\Models\\User', 123, 'auth-token', '0e6be25ff6031cddf5e9951ddddfae5459054363c5d2ed15fb4f2789b1231822', '[\"*\"]', NULL, NULL, '2026-03-14 06:53:18', '2026-03-14 06:53:18'),
(575, 'App\\Models\\User', 123, 'auth-token', '1aac79a1a36b0019628cb578e7f9a91db3a0c5452d0077a7c6a4dfad378723fe', '[\"*\"]', NULL, NULL, '2026-03-14 06:54:23', '2026-03-14 06:54:23'),
(576, 'App\\Models\\User', 31, 'auth-token', '44756ab7e54af83aca10ea11ac1467e2c78c2da4e3a3cdd01d3b925e2293a6fb', '[\"*\"]', NULL, NULL, '2026-03-14 06:55:12', '2026-03-14 06:55:12'),
(577, 'App\\Models\\User', 124, 'auth-token', 'fd147c7009bbda6825cd6b9290ad43f5311d7dd96576b059da07458f7b8b535e', '[\"*\"]', NULL, NULL, '2026-03-14 06:55:31', '2026-03-14 06:55:31'),
(578, 'App\\Models\\User', 124, 'auth-token', '4be8d6dd0f5146b42f4e479ce2b1465f7396ae066e7fd97de2a6c69de2015d5f', '[\"*\"]', NULL, NULL, '2026-03-14 06:56:34', '2026-03-14 06:56:34'),
(579, 'App\\Models\\User', 129, 'auth-token', '3232a0d35165f370d98d2f14f6fa3011deea765f69d246ead7f3254e6460edb3', '[\"*\"]', NULL, NULL, '2026-03-14 06:59:45', '2026-03-14 06:59:45'),
(580, 'App\\Models\\User', 128, 'auth-token', '80d082e4dff7e4253f5f5554cf72e1019372fea0edb4250e49040c376073c3ec', '[\"*\"]', NULL, NULL, '2026-03-14 07:01:01', '2026-03-14 07:01:01'),
(581, 'App\\Models\\User', 129, 'auth-token', '56888be584705efc9d342092e3b11d9899b6be3afa8b0b95fd9c4e4a0f5fadf9', '[\"*\"]', NULL, NULL, '2026-03-14 07:02:24', '2026-03-14 07:02:24'),
(582, 'App\\Models\\User', 128, 'auth-token', '9338d17599f3685e0d5238074bf5ea6416754c6d79dc62bff755ebf9d0787d6b', '[\"*\"]', NULL, NULL, '2026-03-14 07:03:48', '2026-03-14 07:03:48'),
(583, 'App\\Models\\User', 28, 'auth-token', 'c348518911a3db986389cd226aa020adb2449371cda213cb9dbf626a2eb52e55', '[\"*\"]', NULL, NULL, '2026-03-14 13:23:01', '2026-03-14 13:23:01'),
(584, 'App\\Models\\User', 28, 'auth-token', 'ef997a59a432763faf889ca79dbc24ab16c21e602c17ba4f50f687eb70bfe937', '[\"*\"]', NULL, NULL, '2026-03-14 13:50:55', '2026-03-14 13:50:55'),
(585, 'App\\Models\\User', 28, 'auth-token', '95053e33d4867a24ee22e83a8aca7f487862f70e6a093092b44385befb8d7cb9', '[\"*\"]', NULL, NULL, '2026-03-14 14:07:01', '2026-03-14 14:07:01'),
(586, 'App\\Models\\User', 28, 'auth-token', '2631ba0706c50e0fd6f1ae0f22c47cbd03c4c8c118579c6920861b3d94b3ffcd', '[\"*\"]', NULL, NULL, '2026-03-14 14:07:13', '2026-03-14 14:07:13'),
(587, 'App\\Models\\User', 28, 'auth-token', '45e3f9df64d7f6fd96215d4fa7dda3f65ee396be3a7789f668e5e3393320fa77', '[\"*\"]', NULL, NULL, '2026-03-14 14:11:09', '2026-03-14 14:11:09'),
(588, 'App\\Models\\User', 28, 'auth-token', '0351a502c865d6dc26018ceaeb10dfffad7c8ffc978117f894e293fddfbf02b2', '[\"*\"]', NULL, NULL, '2026-03-14 14:16:26', '2026-03-14 14:16:26'),
(589, 'App\\Models\\User', 28, 'auth-token', '7789487a550a72042d86621d14855a3fbf0950d1d547e5ba47ca5732db107598', '[\"*\"]', NULL, NULL, '2026-03-14 14:20:39', '2026-03-14 14:20:39'),
(590, 'App\\Models\\User', 28, 'auth-token', 'b52b6076315fa8e4503a363553c24b3abcfc04986f3aba9382d9d7f7d9d5ea60', '[\"*\"]', NULL, NULL, '2026-03-14 14:35:26', '2026-03-14 14:35:26'),
(591, 'App\\Models\\User', 28, 'auth-token', '770866bb4f6f3b6abf0d79bf6e3e3d2a2fec0f5b5f36bb03ee89b8a203c8ab63', '[\"*\"]', NULL, NULL, '2026-03-14 14:49:24', '2026-03-14 14:49:24'),
(592, 'App\\Models\\User', 28, 'auth-token', '3ac2243b66ba48712383e4caabe1b87d20263a2ec5d5c57247e5fb3017c715a7', '[\"*\"]', NULL, NULL, '2026-03-14 14:54:36', '2026-03-14 14:54:36'),
(593, 'App\\Models\\User', 28, 'auth-token', 'f841f8fadfeab850554dadb77ea021fc89b80f73f2b5b4b0891b9c224c340df0', '[\"*\"]', NULL, NULL, '2026-03-14 15:29:41', '2026-03-14 15:29:41'),
(594, 'App\\Models\\User', 28, 'auth-token', '650c16017096acc9a9925c9176a493a7c018c6ec65fe5652e3c954c7aa053dfe', '[\"*\"]', NULL, NULL, '2026-03-14 15:37:46', '2026-03-14 15:37:46'),
(595, 'App\\Models\\User', 28, 'auth-token', 'b2fb89d698f43f5804e629002e83795f96c3508fc250cf6047e4fc6b162850a9', '[\"*\"]', NULL, NULL, '2026-03-14 15:48:55', '2026-03-14 15:48:55'),
(596, 'App\\Models\\User', 28, 'auth-token', '36f486b45300fb445b98aacd41d4b7a8c922043669d2cd8921a33397c5bd07bb', '[\"*\"]', NULL, NULL, '2026-03-15 09:19:25', '2026-03-15 09:19:25'),
(597, 'App\\Models\\User', 28, 'auth-token', '46741d95b9a158294b875b3b395ee418f43b24a7244745807374ecebad0f26c7', '[\"*\"]', NULL, NULL, '2026-03-15 09:31:36', '2026-03-15 09:31:36'),
(598, 'App\\Models\\User', 31, 'auth-token', 'a9245d4b42418b4ccce68a60c6739ceaa152880f5ea62b62d560f57e79072fe0', '[\"*\"]', NULL, NULL, '2026-03-15 09:33:22', '2026-03-15 09:33:22'),
(599, 'App\\Models\\User', 126, 'auth-token', '0b648f4c97da572ba087c4d150a18fec81cdac6ef437dab3c41a33cc0bf2ccc5', '[\"*\"]', NULL, NULL, '2026-03-15 09:56:08', '2026-03-15 09:56:08'),
(600, 'App\\Models\\User', 31, 'auth-token', 'e184a8b60d571cf7488895bc6214992f53eb56c3f68f01236eda8c8b3322dd55', '[\"*\"]', NULL, NULL, '2026-03-15 10:25:07', '2026-03-15 10:25:07'),
(601, 'App\\Models\\User', 28, 'auth-token', 'e74ea01a823aea88c2c7e2b3e3b9a747870961d48b8079c147cb770e8b7094d0', '[\"*\"]', NULL, NULL, '2026-03-15 10:33:20', '2026-03-15 10:33:20'),
(602, 'App\\Models\\User', 126, 'auth-token', '1c72832953cf1f9a0837fa4d95a65ef32afcb9d5a379261edc35c9b1e1e3aa10', '[\"*\"]', NULL, NULL, '2026-03-16 12:17:14', '2026-03-16 12:17:14'),
(603, 'App\\Models\\User', 127, 'auth-token', 'ea75f326271f8a70b0613d832ac9a4496d5297d6ab89bf5cd2d10af478554c1a', '[\"*\"]', NULL, NULL, '2026-03-16 12:24:15', '2026-03-16 12:24:15'),
(604, 'App\\Models\\User', 126, 'auth-token', 'd35520d1ad4c43f9b9ba1b614ee8bba76f59d5ef45dbc8395499465c24115604', '[\"*\"]', NULL, NULL, '2026-03-16 12:38:09', '2026-03-16 12:38:09'),
(605, 'App\\Models\\User', 126, 'auth-token', '517910485b67ea9ad32e52b00f8de158b1956622cd0355946dee31ca4ebd8d85', '[\"*\"]', NULL, NULL, '2026-03-16 13:10:26', '2026-03-16 13:10:26'),
(606, 'App\\Models\\User', 136, 'auth-token', '61881bd61c92cefe4ac1e86ecf1572a5c46f753a37fe40edf95035307fc10c9d', '[\"*\"]', NULL, NULL, '2026-03-16 13:30:10', '2026-03-16 13:30:10'),
(607, 'App\\Models\\User', 126, 'auth-token', '1665b3ef87ceb32076b9e7db9c73aec533f8858e7fc59fccdd9f1fd33cf81149', '[\"*\"]', NULL, NULL, '2026-03-16 13:31:40', '2026-03-16 13:31:40'),
(608, 'App\\Models\\User', 137, 'auth-token', '9e85761e6599ee7b6b5f2b55844d93baa487988a41ee910c6343f9566b3cdf53', '[\"*\"]', NULL, NULL, '2026-03-16 13:33:16', '2026-03-16 13:33:16'),
(609, 'App\\Models\\User', 137, 'auth-token', '5dea321bb65a37eb2ee81707b0c26996efe7cdbe547bedb54fe174e8a511c175', '[\"*\"]', NULL, NULL, '2026-03-16 13:33:41', '2026-03-16 13:33:41'),
(610, 'App\\Models\\User', 126, 'auth-token', '45131c1ab9ca3eb298c4e909d7d75faef80fdf7757f020fd755d0ca967f189ec', '[\"*\"]', NULL, NULL, '2026-03-16 14:46:02', '2026-03-16 14:46:02'),
(611, 'App\\Models\\User', 137, 'auth-token', '0e3fda99a1cd7a567e8f1dedaa68ab076774789be663886344f07f5bde828346', '[\"*\"]', NULL, NULL, '2026-03-16 15:09:32', '2026-03-16 15:09:32'),
(612, 'App\\Models\\User', 127, 'auth-token', '5d1cebf733cb922acaa610078914d73c0deef23568ad738511b84afd85465db1', '[\"*\"]', NULL, NULL, '2026-03-16 15:09:49', '2026-03-16 15:09:49'),
(613, 'App\\Models\\User', 137, 'auth-token', 'f13a96fe6f10519f243a36b5e0bb0b268f7a59e4ee9189174c0d0870e01a2c60', '[\"*\"]', NULL, NULL, '2026-03-16 15:11:01', '2026-03-16 15:11:01'),
(614, 'App\\Models\\User', 137, 'auth-token', 'f78ee8bdbab6f1fd74d5ea64bfe6786e6f8c3df70aecb07c6887748cdfc71727', '[\"*\"]', NULL, NULL, '2026-03-16 15:31:40', '2026-03-16 15:31:40'),
(615, 'App\\Models\\User', 137, 'auth-token', '2e6a568f540c1d515b00b0dac5211d95563a9f4a8a4a99ec17688cd144fa6dfa', '[\"*\"]', NULL, NULL, '2026-03-16 15:33:41', '2026-03-16 15:33:41'),
(616, 'App\\Models\\User', 137, 'auth-token', '1feae6a16da534da03af70f5fdc6596569d6f065bf4775a6ec5cc024462b82f5', '[\"*\"]', NULL, NULL, '2026-03-16 15:43:08', '2026-03-16 15:43:08'),
(617, 'App\\Models\\User', 28, 'auth-token', 'b00c68a8b34c6c7160918399253d2e1be12396f24995628173eb19494261a4a3', '[\"*\"]', NULL, NULL, '2026-03-17 04:47:46', '2026-03-17 04:47:46'),
(618, 'App\\Models\\User', 28, 'auth-token', 'a058c3eee0a360f6eee1a72d8372cea7debc3dbaec82a99129ecfaa543eb1b1d', '[\"*\"]', NULL, NULL, '2026-03-17 05:11:19', '2026-03-17 05:11:19'),
(619, 'App\\Models\\User', 123, 'auth-token', '57d2bf2914ea677321491c21dcb38989ee70043b87bbde738bd5af459f9b2608', '[\"*\"]', NULL, NULL, '2026-03-17 05:11:32', '2026-03-17 05:11:32'),
(620, 'App\\Models\\User', 137, 'auth-token', '649222e2f036cafed6ac854f4c7a54b1fc1efcd99ad114141ac2f1aec289a94c', '[\"*\"]', NULL, NULL, '2026-03-17 05:12:08', '2026-03-17 05:12:08'),
(621, 'App\\Models\\User', 127, 'auth-token', '67d78afee28a43c8e362ce60437074a60fcc56bae50f9021db5725e8fe9d330c', '[\"*\"]', NULL, NULL, '2026-03-17 05:23:41', '2026-03-17 05:23:41'),
(622, 'App\\Models\\User', 137, 'auth-token', '5fe61e6cb86b8edf8ee5ea896464d36e35dea5d48b885760a0bf64ef905ce81c', '[\"*\"]', NULL, NULL, '2026-03-17 05:24:53', '2026-03-17 05:24:53'),
(623, 'App\\Models\\User', 137, 'auth-token', 'ba08a8cc50bb0373277ad0b05551391e75f81a1c9edfcc500b90acbd8ceb1418', '[\"*\"]', NULL, NULL, '2026-03-17 05:29:01', '2026-03-17 05:29:01'),
(624, 'App\\Models\\User', 137, 'auth-token', '345b7c24bef11598a89443b2b58f1938da707e7ec9c4a8d48ef5e3a3244b0df6', '[\"*\"]', NULL, NULL, '2026-03-17 05:32:26', '2026-03-17 05:32:26'),
(625, 'App\\Models\\User', 126, 'auth-token', 'e7ad627ae6eb4dfefd2546b087ac7202c600b8b4542cf3af1484233599692f33', '[\"*\"]', NULL, NULL, '2026-03-17 05:42:54', '2026-03-17 05:42:54'),
(626, 'App\\Models\\User', 137, 'auth-token', 'c87de8f30bb8bf086cef9e4ff46512aeb4a02c1a056b53a5cf310223611b9e35', '[\"*\"]', NULL, NULL, '2026-03-17 05:45:08', '2026-03-17 05:45:08'),
(627, 'App\\Models\\User', 127, 'auth-token', 'e582984e071801dfe2acad73d301e403a93aa86eb134584da286d2f5d3210e3b', '[\"*\"]', NULL, NULL, '2026-03-17 05:52:37', '2026-03-17 05:52:37'),
(628, 'App\\Models\\User', 137, 'auth-token', 'a28f3f1dbff2ec9b97845ef210d9d2c60a3c37c5b96d5747fcbbd9ff2f579105', '[\"*\"]', NULL, NULL, '2026-03-17 05:57:08', '2026-03-17 05:57:08'),
(629, 'App\\Models\\User', 129, 'auth-token', '7091fbb6725ebff76f4c9c70c027f7d9a6c99c38481c67750d91cce5a2ce6d15', '[\"*\"]', NULL, NULL, '2026-03-17 06:08:25', '2026-03-17 06:08:25'),
(630, 'App\\Models\\User', 137, 'auth-token', 'ad9b867e2bcf1fb6587f365bc693a6c724a351eae5d8dee6e2771804e44e3a09', '[\"*\"]', NULL, NULL, '2026-03-17 06:11:58', '2026-03-17 06:11:58'),
(631, 'App\\Models\\User', 126, 'auth-token', '343e50bd8d81e042a7c65c33bee05ece91c636bf27b1021c55407bd4d685e1c3', '[\"*\"]', NULL, NULL, '2026-03-17 07:53:04', '2026-03-17 07:53:04'),
(632, 'App\\Models\\User', 137, 'auth-token', 'baaaa39f164c149389eef4022f7dfbbba1a927a3687907e4073a776933db26d8', '[\"*\"]', NULL, NULL, '2026-03-17 08:03:01', '2026-03-17 08:03:01'),
(633, 'App\\Models\\User', 126, 'auth-token', 'bd7415c39d19c84c3fb6b3efdc97f048c1c914482282dd30a52b118be8cfe8a9', '[\"*\"]', NULL, NULL, '2026-03-17 08:03:27', '2026-03-17 08:03:27'),
(634, 'App\\Models\\User', 129, 'auth-token', '9d839f8af1dab6dae7f68d3e8297d444e151a5ad58290f3ad6be9480232bb67b', '[\"*\"]', NULL, NULL, '2026-03-17 08:06:14', '2026-03-17 08:06:14'),
(635, 'App\\Models\\User', 137, 'auth-token', 'af76525e0c6e2d38123b99a352ef1b72b41ff2b5fab054b87ca3eb16e0eb7a69', '[\"*\"]', NULL, NULL, '2026-03-17 08:06:36', '2026-03-17 08:06:36'),
(636, 'App\\Models\\User', 28, 'auth-token', 'f32d65625c672b8a8db898b69702d832d581f55b5d987d415f2968fc40be4491', '[\"*\"]', NULL, NULL, '2026-03-18 05:13:53', '2026-03-18 05:13:53'),
(637, 'App\\Models\\User', 127, 'auth-token', 'f6dc38c488d6724516127f43e27f0679a13773dea4f75090007afae5b273404b', '[\"*\"]', NULL, NULL, '2026-03-18 05:14:54', '2026-03-18 05:14:54'),
(638, 'App\\Models\\User', 126, 'auth-token', 'e9bb5665ab4125d1811f78bf4c5c896cce3e72ae8ac50aa083623bbc46c66e4b', '[\"*\"]', NULL, NULL, '2026-03-18 05:15:13', '2026-03-18 05:15:13'),
(639, 'App\\Models\\User', 137, 'auth-token', 'fb58eaa720a000a4074d9af036a79d7ef4c6aaa128209ed67790c1aca277eb6d', '[\"*\"]', NULL, NULL, '2026-03-18 05:21:41', '2026-03-18 05:21:41'),
(640, 'App\\Models\\User', 126, 'auth-token', '4b983c3acf8eb98d165e6e72ee0cc8f3e137ff17ac91458c44ee7472621fd0e8', '[\"*\"]', NULL, NULL, '2026-03-18 05:21:51', '2026-03-18 05:21:51'),
(641, 'App\\Models\\User', 137, 'auth-token', '0c19364e2f418376aa1e995505a009301ca9c95e651a6002688ffaea1b3fde4a', '[\"*\"]', NULL, NULL, '2026-03-18 05:22:06', '2026-03-18 05:22:06'),
(642, 'App\\Models\\User', 126, 'auth-token', '21796fdaa65226f0d43a973a94fbf03d151829c41ec051d2dde2f7e7326e0d29', '[\"*\"]', NULL, NULL, '2026-03-18 05:26:57', '2026-03-18 05:26:57'),
(643, 'App\\Models\\User', 127, 'auth-token', '7b0a000927d72e645da21309c8173bfd954a172b1429275fac39804946279608', '[\"*\"]', NULL, NULL, '2026-03-18 05:27:11', '2026-03-18 05:27:11'),
(644, 'App\\Models\\User', 126, 'auth-token', 'c2e37c6b7ca54d199e6202674cb3b77a9b5e7e9f604e1f4029370b589f9c6120', '[\"*\"]', NULL, NULL, '2026-03-18 05:33:29', '2026-03-18 05:33:29'),
(645, 'App\\Models\\User', 127, 'auth-token', '4221115edbe8bdc3bdf751167aabab7474d37be4fb1b36b7e5248a3d6cbcfa75', '[\"*\"]', NULL, NULL, '2026-03-18 05:34:04', '2026-03-18 05:34:04'),
(646, 'App\\Models\\User', 127, 'auth-token', '91983e06529b8493f13ffadd68e48c58a5b9e40255468ea8243704cf79d3c7bd', '[\"*\"]', NULL, NULL, '2026-03-18 05:37:24', '2026-03-18 05:37:24'),
(647, 'App\\Models\\User', 129, 'auth-token', 'd8e3cb011ca582707c961efd4e43f7af9530386acef3a77a7517019660bc723d', '[\"*\"]', NULL, NULL, '2026-03-18 05:37:46', '2026-03-18 05:37:46'),
(648, 'App\\Models\\User', 126, 'auth-token', '3be471ad5e4a1e25f7b04b5c9b29115863e2b495a6779f7f18a0814babf2eed9', '[\"*\"]', NULL, NULL, '2026-03-18 05:38:03', '2026-03-18 05:38:03'),
(649, 'App\\Models\\User', 137, 'auth-token', '711f32c3988dfbb0e6cadb75cf2a5b01899ad76779eaeb5f3236c8c5ceb2100c', '[\"*\"]', NULL, NULL, '2026-03-18 05:41:55', '2026-03-18 05:41:55'),
(650, 'App\\Models\\User', 127, 'auth-token', 'ce865b01149e76a35cb65df088e42e1dd3d1b5e37f364c8286f1570fad147998', '[\"*\"]', NULL, NULL, '2026-03-18 05:42:07', '2026-03-18 05:42:07'),
(651, 'App\\Models\\User', 127, 'auth-token', '999bb70751de404fd515d8d56a4b8fdc290b412d38eaaae719fd680d1732a050', '[\"*\"]', NULL, NULL, '2026-03-18 05:46:25', '2026-03-18 05:46:25'),
(652, 'App\\Models\\User', 137, 'auth-token', '7174d23463e75c80906fe9485015c0f6152486247c9b1fc87561fbcfd5ba7074', '[\"*\"]', NULL, NULL, '2026-03-18 05:47:09', '2026-03-18 05:47:09'),
(653, 'App\\Models\\User', 127, 'auth-token', 'd0032754a6ce3483e7450536f07bb9abdebcdbc0ee21a44eb48073a71ab838ff', '[\"*\"]', NULL, NULL, '2026-03-18 05:47:39', '2026-03-18 05:47:39'),
(654, 'App\\Models\\User', 126, 'auth-token', '9eb59ad8a7a04346631b266fbc6e877190f828fb2090c9cb0e75974297877803', '[\"*\"]', NULL, NULL, '2026-03-18 05:52:30', '2026-03-18 05:52:30'),
(655, 'App\\Models\\User', 137, 'auth-token', 'f7d8ece6225d252661f51bb88df10ad6fe6f0d9817c3ecc903147fe5263ac8f6', '[\"*\"]', NULL, NULL, '2026-03-18 06:12:08', '2026-03-18 06:12:08'),
(656, 'App\\Models\\User', 126, 'auth-token', 'bc5075c4209e1a4f0d915c39afa09c2e261a6cd88397bd2e6485f2313320a96f', '[\"*\"]', NULL, NULL, '2026-03-18 06:12:32', '2026-03-18 06:12:32'),
(657, 'App\\Models\\User', 127, 'auth-token', '005d7055f108ef80aa941095af51487b2d0d6676c572716c4345097102868f4e', '[\"*\"]', NULL, NULL, '2026-03-18 06:47:02', '2026-03-18 06:47:02'),
(658, 'App\\Models\\User', 137, 'auth-token', 'cabe1c660c2383b0bc0d8cc63b2125ef24b75cc5d1f7704e6e1ce25665842f2e', '[\"*\"]', NULL, NULL, '2026-03-18 06:50:45', '2026-03-18 06:50:45'),
(659, 'App\\Models\\User', 126, 'auth-token', '0a96b4bc61abdc6a32cfe4a9486c779398573b4803d183cfb0f89e563b19597a', '[\"*\"]', NULL, NULL, '2026-03-18 06:51:23', '2026-03-18 06:51:23'),
(660, 'App\\Models\\User', 127, 'auth-token', 'feb48c8152a9a617e7e17a61a7a234af60e2fc0ca9e51fbcbf64cb8ba575bace', '[\"*\"]', NULL, NULL, '2026-03-18 07:08:26', '2026-03-18 07:08:26'),
(661, 'App\\Models\\User', 137, 'auth-token', 'c49836dac530f945a79b13db85ccb588a3817df866c0fb14a98b13e9b49fe6b3', '[\"*\"]', NULL, NULL, '2026-03-18 07:11:04', '2026-03-18 07:11:04'),
(662, 'App\\Models\\User', 137, 'auth-token', '871a55c29b746b1c3f8ad32004aebca737777a2b03ab0546371ea5200bfa75ff', '[\"*\"]', NULL, NULL, '2026-03-18 07:12:53', '2026-03-18 07:12:53'),
(663, 'App\\Models\\User', 137, 'auth-token', '7ba50b616a2a0ed8ca3a7e840dfabcc692347ffa67144bb3904741f01249804c', '[\"*\"]', NULL, NULL, '2026-03-18 07:13:17', '2026-03-18 07:13:17'),
(664, 'App\\Models\\User', 127, 'auth-token', '1f4856614af09ecfe046e7d5a67d18c2dff4e45d37b3380e6c2e4204ca309653', '[\"*\"]', NULL, NULL, '2026-03-18 07:14:53', '2026-03-18 07:14:53'),
(665, 'App\\Models\\User', 126, 'auth-token', 'c7a6db0cdd60df599ab14d7028abcbfe9af4ef24dfd20bc857cbac7b8bef92e5', '[\"*\"]', NULL, NULL, '2026-03-18 07:15:06', '2026-03-18 07:15:06'),
(666, 'App\\Models\\User', 127, 'auth-token', '56b158e4b707eb463b8367c29047bf0a8f35a205096e2a036ae06dfb93c4e30a', '[\"*\"]', NULL, NULL, '2026-03-18 07:20:58', '2026-03-18 07:20:58'),
(667, 'App\\Models\\User', 128, 'auth-token', 'ca05c581117d92ef0940346f3d47c41d551f9e0d68a457f108c054a133bff9cb', '[\"*\"]', NULL, NULL, '2026-03-18 07:21:25', '2026-03-18 07:21:25'),
(668, 'App\\Models\\User', 127, 'auth-token', '2628c19c468fde86ffca2ba7de171155422490254e4a76946e4fd981344c3cd1', '[\"*\"]', NULL, NULL, '2026-03-18 07:22:15', '2026-03-18 07:22:15'),
(669, 'App\\Models\\User', 128, 'auth-token', '69c24b76d0a05b2ee9d2d727c753c6c2570ee70d85d32561e1ae682042846da9', '[\"*\"]', NULL, NULL, '2026-03-18 07:22:39', '2026-03-18 07:22:39'),
(670, 'App\\Models\\User', 127, 'auth-token', 'c3802b1aa347b597998ab5731cbf2086757feb52aa98ee50f97a9f2b9ef08af7', '[\"*\"]', NULL, NULL, '2026-03-18 07:28:29', '2026-03-18 07:28:29'),
(671, 'App\\Models\\User', 128, 'auth-token', '8edb50e85bade89ea44e0c34f341d2d98806289987f44acdcab21ce2c4cc8157', '[\"*\"]', NULL, NULL, '2026-03-18 07:28:55', '2026-03-18 07:28:55'),
(672, 'App\\Models\\User', 137, 'auth-token', 'b4ab2638d4037f15b0c9d0f238a40549fce3bdff58d2f97bcd83254d98b10e5c', '[\"*\"]', NULL, NULL, '2026-03-18 07:29:46', '2026-03-18 07:29:46'),
(673, 'App\\Models\\User', 127, 'auth-token', '1baa7a849d763142dded219bb770b91bca2a70ab980d5bc94f71809d90e58448', '[\"*\"]', NULL, NULL, '2026-03-18 07:30:00', '2026-03-18 07:30:00'),
(674, 'App\\Models\\User', 127, 'auth-token', 'fcf3bcad0e12fbe2d32f09d28f2938fe681add300ea1ac02cfab426f90788ab1', '[\"*\"]', NULL, NULL, '2026-03-18 07:43:38', '2026-03-18 07:43:38'),
(675, 'App\\Models\\User', 127, 'auth-token', '6f3a7fa1f1316152a40415229167db260f24227bfefb7adb8318138938066a7e', '[\"*\"]', NULL, NULL, '2026-03-18 07:51:36', '2026-03-18 07:51:36'),
(676, 'App\\Models\\User', 137, 'auth-token', 'b07b33bb60325305d81dc733cc1fc1db9c70ec0ac2bae971bc7227e267aaf487', '[\"*\"]', NULL, NULL, '2026-03-18 07:54:03', '2026-03-18 07:54:03'),
(677, 'App\\Models\\User', 127, 'auth-token', '56369ab77268fa3d611097315d9466dc550b15199abacba94f1c3d49d92b12d3', '[\"*\"]', NULL, NULL, '2026-03-18 07:54:20', '2026-03-18 07:54:20'),
(678, 'App\\Models\\User', 127, 'auth-token', '556e3537eb1bdd75cc2b8e2180eb337d982700ee6d3a4523118c80c23522b92f', '[\"*\"]', NULL, NULL, '2026-03-18 07:54:29', '2026-03-18 07:54:29'),
(679, 'App\\Models\\User', 127, 'auth-token', '3ecc3af9a1166cc58728518edaf3cb426e6cc75e500aa3b3fa38fba0709d7efa', '[\"*\"]', NULL, NULL, '2026-03-18 07:54:50', '2026-03-18 07:54:50'),
(680, 'App\\Models\\User', 127, 'auth-token', '743392c9abf13fc69313fe11cb7d7e587cb9a60854832c730e33e4cb8fab6c57', '[\"*\"]', NULL, NULL, '2026-03-18 07:58:11', '2026-03-18 07:58:11'),
(681, 'App\\Models\\User', 126, 'auth-token', '0c7758f5527778658352a013a6fc4b113b4c2f00ecf98b597d0fdc3f1b62163d', '[\"*\"]', NULL, NULL, '2026-03-18 07:58:59', '2026-03-18 07:58:59'),
(682, 'App\\Models\\User', 126, 'auth-token', 'cb59f3a9dfae2354025be2e00e6187160e4af70dded9d36e36a2d69e966b680d', '[\"*\"]', NULL, NULL, '2026-03-18 08:01:04', '2026-03-18 08:01:04'),
(683, 'App\\Models\\User', 127, 'auth-token', '1dffedbf77e1b73013b97aa07963555775c70b73780dc19642c20341aae8ecc2', '[\"*\"]', NULL, NULL, '2026-03-18 08:01:17', '2026-03-18 08:01:17'),
(684, 'App\\Models\\User', 127, 'auth-token', '3530d155f1e52c10de45c00bb2c98bf674e8ddc31fd2e06484d8313f0d97857e', '[\"*\"]', NULL, NULL, '2026-03-18 08:05:25', '2026-03-18 08:05:25'),
(685, 'App\\Models\\User', 126, 'auth-token', '5123d538c3e8f3455fcfb91a1732e61eac0a9dbf78240a41ba4f9d24a0d55712', '[\"*\"]', NULL, NULL, '2026-03-18 08:18:25', '2026-03-18 08:18:25'),
(686, 'App\\Models\\User', 127, 'auth-token', '55dd167ba5c8c64e4b2a38b2654c51ebbea489b3aabaa18a9325065be80a93b8', '[\"*\"]', NULL, NULL, '2026-03-18 08:18:42', '2026-03-18 08:18:42'),
(687, 'App\\Models\\User', 127, 'auth-token', '49875d2688d061b9e5313c4eb60804c6ac163794b5af4fb95e1f460b505ab751', '[\"*\"]', NULL, NULL, '2026-03-18 08:20:20', '2026-03-18 08:20:20'),
(688, 'App\\Models\\User', 127, 'auth-token', '66d6eb44c4bdc0c99b572749bf049df90a0686263cfa0bc1ef5d1c293238c22f', '[\"*\"]', NULL, NULL, '2026-03-18 08:26:52', '2026-03-18 08:26:52'),
(689, 'App\\Models\\User', 126, 'auth-token', '50f71975a8f7badb728c930b82e6b31f4b51d6d8ebef8566020f5c9f09af8fef', '[\"*\"]', NULL, NULL, '2026-03-18 08:34:44', '2026-03-18 08:34:44'),
(690, 'App\\Models\\User', 127, 'auth-token', '36802d8f07e56d4da2b1e140e4d9c1b1d109379b85b476cb5f155237fd9399ac', '[\"*\"]', NULL, NULL, '2026-03-18 08:35:47', '2026-03-18 08:35:47'),
(691, 'App\\Models\\User', 127, 'auth-token', '2e9800549201ca9683198ae01cd69f5478dbf9d6405b2781c9286eb5af9f461b', '[\"*\"]', NULL, NULL, '2026-03-18 08:38:27', '2026-03-18 08:38:27'),
(692, 'App\\Models\\User', 127, 'auth-token', '21d99236134065c0f8b6c308dd50c4240f24342179a769cb58ecae529e2d6b3e', '[\"*\"]', NULL, NULL, '2026-03-18 08:38:42', '2026-03-18 08:38:42'),
(693, 'App\\Models\\User', 127, 'auth-token', 'db46e3d1eed2f25c36a396b92c1783f44a187d10e3337dcc8c6f321be7cee7ea', '[\"*\"]', NULL, NULL, '2026-03-18 08:39:19', '2026-03-18 08:39:19'),
(694, 'App\\Models\\User', 137, 'auth-token', 'a43ded6c1665ccd803b5c583d863bc1f1dc97360a0144a6e6fd67190a94c12eb', '[\"*\"]', NULL, NULL, '2026-03-18 08:40:09', '2026-03-18 08:40:09'),
(695, 'App\\Models\\User', 127, 'auth-token', 'dd3ebdb0875aa5185f183b1e2873be5d1029a36a50a341d020f23e4422421859', '[\"*\"]', NULL, NULL, '2026-03-18 08:41:07', '2026-03-18 08:41:07'),
(696, 'App\\Models\\User', 126, 'auth-token', '4aed0226aedf399b74f98647ad6da273926f6a3a593544a80c5869ac7de9be67', '[\"*\"]', NULL, NULL, '2026-03-18 08:41:23', '2026-03-18 08:41:23'),
(697, 'App\\Models\\User', 127, 'auth-token', 'fe96903910a457cfa7fa888bf773ddb973739723f85bc897628b83ec2cea7e65', '[\"*\"]', NULL, NULL, '2026-03-18 08:41:40', '2026-03-18 08:41:40'),
(698, 'App\\Models\\User', 126, 'auth-token', '9d848f949fed9bfaf72082863c9fcac6ace137fe029b197c894851d6c57ff4ee', '[\"*\"]', NULL, NULL, '2026-03-18 08:52:51', '2026-03-18 08:52:51'),
(699, 'App\\Models\\User', 126, 'auth-token', 'e75791427b7a7c89f27cb9fc122a55974a9b15b73c4de6fb97c136295ef7c614', '[\"*\"]', NULL, NULL, '2026-03-18 09:08:26', '2026-03-18 09:08:26'),
(700, 'App\\Models\\User', 126, 'auth-token', 'aa87f47e62e72baa97e36c9a393f9ac656a7c7c3cc9cf409f15ccd62378f175c', '[\"*\"]', NULL, NULL, '2026-03-18 09:10:06', '2026-03-18 09:10:06'),
(701, 'App\\Models\\User', 126, 'auth-token', 'b6998838bc5c82cf501796c912e787cddd58b15116c9a3b6cb965ca50b21620b', '[\"*\"]', NULL, NULL, '2026-03-18 09:22:23', '2026-03-18 09:22:23'),
(702, 'App\\Models\\User', 137, 'auth-token', 'ded420a63533e6b43dae5bec2f18843bc8d69308db34315e4b852fe997597a5f', '[\"*\"]', NULL, NULL, '2026-03-18 09:25:18', '2026-03-18 09:25:18'),
(703, 'App\\Models\\User', 126, 'auth-token', '72a519eb0e13b0cf4d00bc1dd1ee8503241e9004facf0380e2fc4ded7caf41e1', '[\"*\"]', NULL, NULL, '2026-03-18 09:25:53', '2026-03-18 09:25:53'),
(704, 'App\\Models\\User', 127, 'auth-token', '521b9ee2c7fb6ee995c21d3ad00f249d0a85c728354863e9e92c90f3bf5d9142', '[\"*\"]', NULL, NULL, '2026-03-18 09:27:00', '2026-03-18 09:27:00'),
(705, 'App\\Models\\User', 126, 'auth-token', 'c41999cf3630743db6bc4daa56ebc9b2548c1994841e37674356b9ffd9c602ed', '[\"*\"]', NULL, NULL, '2026-03-18 09:27:17', '2026-03-18 09:27:17'),
(706, 'App\\Models\\User', 126, 'auth-token', '00db979e3ef3763faa7216659df6c2348981cd00fb48ff3531fd90947509a27a', '[\"*\"]', NULL, NULL, '2026-03-18 09:35:44', '2026-03-18 09:35:44'),
(707, 'App\\Models\\User', 137, 'auth-token', 'ea1619d90f2e68f498a4eb5d6abe08bb143cc2eadbac63e482ee0b386a5a7eec', '[\"*\"]', NULL, NULL, '2026-03-18 09:40:21', '2026-03-18 09:40:21'),
(708, 'App\\Models\\User', 126, 'auth-token', '0c1edad44b781fb2541e5afce6c87d6e211fa6f0fae9356e1f7a39875e72a280', '[\"*\"]', NULL, NULL, '2026-03-18 09:40:48', '2026-03-18 09:40:48'),
(709, 'App\\Models\\User', 126, 'auth-token', 'd59653e4393f067013538554b53f15c31a2de14cd79fdb86144ee5a41a1c7c3f', '[\"*\"]', NULL, NULL, '2026-03-18 09:46:58', '2026-03-18 09:46:58'),
(710, 'App\\Models\\User', 126, 'auth-token', '0538914b64a06a53d348905258018c239cae53d14849c6b117a04692a8bf0441', '[\"*\"]', NULL, NULL, '2026-03-18 14:26:05', '2026-03-18 14:26:05'),
(711, 'App\\Models\\User', 127, 'auth-token', 'b49e1f56d694ec5a073f49d768e66cc752f545e134acb533c2a38bc201c7a9bd', '[\"*\"]', NULL, NULL, '2026-03-18 14:26:24', '2026-03-18 14:26:24'),
(712, 'App\\Models\\User', 126, 'auth-token', 'a6b0d525ebae8202122790d475e6da1f5ccd19d9c7cfb0c435179b3987708bc1', '[\"*\"]', NULL, NULL, '2026-03-18 14:26:52', '2026-03-18 14:26:52'),
(713, 'App\\Models\\User', 127, 'auth-token', 'a1b18fb0746ec01908ee42a8ee2cc8bf32d2cdbfc64429418ec29d3b2cca511c', '[\"*\"]', NULL, NULL, '2026-03-18 14:28:30', '2026-03-18 14:28:30'),
(714, 'App\\Models\\User', 127, 'auth-token', 'dcd66f8e9e623e5c2f69792ff936f669d26fce221d1152e3efff29f8dfd60dec', '[\"*\"]', NULL, NULL, '2026-03-18 14:46:30', '2026-03-18 14:46:30'),
(715, 'App\\Models\\User', 126, 'auth-token', '002441989cf98172edeeb6b2d2ce02fcf96e142d0fb7cd0c18fe0e88e25a701d', '[\"*\"]', NULL, NULL, '2026-03-18 14:46:56', '2026-03-18 14:46:56'),
(716, 'App\\Models\\User', 31, 'auth-token', '8df162ea39fe1908fcf38472476c6dc9aa36f4a749fe610e0cd68f94f0dd8f71', '[\"*\"]', NULL, NULL, '2026-03-18 15:07:17', '2026-03-18 15:07:17'),
(717, 'App\\Models\\User', 125, 'auth-token', 'ef0c966e1bde852fcb95b91b2d7d8f21263d79cf114297b49cfde7061cf98a54', '[\"*\"]', NULL, NULL, '2026-03-18 15:08:40', '2026-03-18 15:08:40'),
(718, 'App\\Models\\User', 125, 'auth-token', '755012b41c9cce769e274ca703548f89ddae92a7508b8bea0404a57a81895ff2', '[\"*\"]', NULL, NULL, '2026-03-18 15:09:04', '2026-03-18 15:09:04'),
(719, 'App\\Models\\User', 127, 'auth-token', 'b8e13f4526bcd8832240b99e36dc67c71c2bd99d4359d14d96f5406454937d71', '[\"*\"]', NULL, NULL, '2026-03-18 15:09:56', '2026-03-18 15:09:56'),
(720, 'App\\Models\\User', 126, 'auth-token', '30604462a97c0eedf475ee075168ccd22f88460f55a93c9e5eac09a58d827dbd', '[\"*\"]', NULL, NULL, '2026-03-18 15:10:08', '2026-03-18 15:10:08'),
(721, 'App\\Models\\User', 126, 'auth-token', 'af11bfe7c01bea6bb546cd95f715a481695f7fd963c81220ea9db1b89244d5e6', '[\"*\"]', NULL, NULL, '2026-03-18 15:27:18', '2026-03-18 15:27:18'),
(722, 'App\\Models\\User', 127, 'auth-token', 'd297b053607d5d7137527dadc038209c8c7b6e567e12366896ee7c4f442cc238', '[\"*\"]', NULL, NULL, '2026-03-18 15:33:21', '2026-03-18 15:33:21'),
(723, 'App\\Models\\User', 125, 'auth-token', '7b5bd47e607aeda9f3c82b82c076583bfe8df5f44dbc99e8f7577eb69aff3cb5', '[\"*\"]', NULL, NULL, '2026-03-18 15:33:47', '2026-03-18 15:33:47'),
(724, 'App\\Models\\User', 126, 'auth-token', '35c165ea958f460b2622a2c9dd75b5f000f0627630732c540bdb73d5ebe5e4fd', '[\"*\"]', NULL, NULL, '2026-03-18 16:05:14', '2026-03-18 16:05:14'),
(725, 'App\\Models\\User', 125, 'auth-token', 'e907a3051e1d6235ec6f3c59ac9e636ab0ca03bc16062f27bc06fb155b010bb1', '[\"*\"]', NULL, NULL, '2026-03-18 16:12:11', '2026-03-18 16:12:11'),
(726, 'App\\Models\\User', 127, 'auth-token', '25aaba45769a939010bdfd2c58fef528b14b0282a9f280b7cf995c80e595402a', '[\"*\"]', NULL, NULL, '2026-03-18 16:13:11', '2026-03-18 16:13:11'),
(727, 'App\\Models\\User', 126, 'auth-token', '3d280e92c58e26f23d279c767e0401e4f850d1cc0f0c265e89fcc1a174ed22c6', '[\"*\"]', NULL, NULL, '2026-03-18 16:13:49', '2026-03-18 16:13:49'),
(728, 'App\\Models\\User', 125, 'auth-token', '086f582ddbba79a0938d6b6c75f5cc5f4c0c75a9f275bca8de80235686ddc0a1', '[\"*\"]', NULL, NULL, '2026-03-18 16:15:59', '2026-03-18 16:15:59'),
(729, 'App\\Models\\User', 126, 'auth-token', 'c4e7d48d9735776be6d3c465bd5d7af13fa26890d466eea40027420a4d9e673e', '[\"*\"]', NULL, NULL, '2026-03-18 16:39:14', '2026-03-18 16:39:14'),
(730, 'App\\Models\\User', 31, 'auth-token', '3c16548c3e19d3af1ca81455f52ae06485d93676e7d89c57b9c66953b91c3744', '[\"*\"]', NULL, NULL, '2026-03-19 06:03:53', '2026-03-19 06:03:53'),
(731, 'App\\Models\\User', 126, 'auth-token', '44c16e0d44a24fe58b144712d19c37696a21aafbbadd0237cfdcbde2eb79857f', '[\"*\"]', NULL, NULL, '2026-03-19 06:04:07', '2026-03-19 06:04:07'),
(732, 'App\\Models\\User', 125, 'auth-token', '43ff473b0eef9008bb060e62a91f04b7af5e66888ae2a0e9a22d003e82f9494a', '[\"*\"]', NULL, NULL, '2026-03-19 06:56:23', '2026-03-19 06:56:23'),
(733, 'App\\Models\\User', 126, 'auth-token', '62e2fda367b78c0c43410a65b5ac960449a2c5eb320ad4f2f75a2555e4135181', '[\"*\"]', NULL, NULL, '2026-03-19 06:56:44', '2026-03-19 06:56:44'),
(734, 'App\\Models\\User', 126, 'auth-token', '1f3b03ce7592997030e7985547bf9c91f2a564c8e378603bdf1584201d0f972d', '[\"*\"]', NULL, NULL, '2026-03-19 07:59:25', '2026-03-19 07:59:25'),
(735, 'App\\Models\\User', 127, 'auth-token', '66bfd58d110b57c856ecb19a189d7244d3d942087d4caa139fbbf700cc277ccd', '[\"*\"]', NULL, NULL, '2026-03-19 07:59:56', '2026-03-19 07:59:56'),
(736, 'App\\Models\\User', 126, 'auth-token', 'a99f6fd4a1433ba5565e02910b8862538246de67fa05a1c146c8915b263103ce', '[\"*\"]', NULL, NULL, '2026-03-19 08:00:18', '2026-03-19 08:00:18'),
(737, 'App\\Models\\User', 125, 'auth-token', '9c65e747c49f001ccf03ebb4c4fdd5df935c681c87458d2ba563cf90b4a7e7f0', '[\"*\"]', NULL, NULL, '2026-03-19 08:00:53', '2026-03-19 08:00:53'),
(738, 'App\\Models\\User', 126, 'auth-token', '8f6ef897717e988f19bd9a2217b72aea8dd7d048f108fe86482036102e6c1f98', '[\"*\"]', NULL, NULL, '2026-03-19 08:01:08', '2026-03-19 08:01:08'),
(739, 'App\\Models\\User', 127, 'auth-token', 'f05e2fbf01f59b05da6a864ed4116999ed1dd087920364e44c517aa6610d3836', '[\"*\"]', NULL, NULL, '2026-03-19 08:10:23', '2026-03-19 08:10:23'),
(740, 'App\\Models\\User', 126, 'auth-token', '1bd970a6d8dd60b1ec79e622db70e5684ea9840b867e7ca45a7d855124182b68', '[\"*\"]', NULL, NULL, '2026-03-19 08:10:58', '2026-03-19 08:10:58'),
(741, 'App\\Models\\User', 125, 'auth-token', 'f4d0ce237a819fd5d6eac343ea89879ead12361edab864b85aafe0c27f149a62', '[\"*\"]', NULL, NULL, '2026-03-19 08:11:18', '2026-03-19 08:11:18'),
(742, 'App\\Models\\User', 126, 'auth-token', '36bc3441a99996ee4f884413ec4317434be40626d6a5d7a343bf5c3f714ad6e8', '[\"*\"]', NULL, NULL, '2026-03-19 08:11:36', '2026-03-19 08:11:36'),
(743, 'App\\Models\\User', 127, 'auth-token', '6ae638312b8aee6df3ac400eb0151b9695586bc008c8f266f97105133af4c2d8', '[\"*\"]', NULL, NULL, '2026-03-19 08:19:51', '2026-03-19 08:19:51'),
(744, 'App\\Models\\User', 126, 'auth-token', '60f8698ec49193dbaf7d07c8aa1fcaa3c7da0997b2b284a7cc1f54a242414f91', '[\"*\"]', NULL, NULL, '2026-03-19 08:20:12', '2026-03-19 08:20:12'),
(745, 'App\\Models\\User', 127, 'auth-token', '39a2f420e9ce270983cc1c86f81e86c77f95e47757e124092418af601e966607', '[\"*\"]', NULL, NULL, '2026-03-19 08:25:48', '2026-03-19 08:25:48'),
(746, 'App\\Models\\User', 126, 'auth-token', '30d5537323cc171f9eaf10d59ff4d7311cb803e554fd51b236b0d5fdc6ebd4b3', '[\"*\"]', NULL, NULL, '2026-03-19 08:26:05', '2026-03-19 08:26:05'),
(747, 'App\\Models\\User', 125, 'auth-token', '050e8489c8c3d3771453e7af51b1f6be8325f28ab448c8e6009b81cefbeaac5e', '[\"*\"]', NULL, NULL, '2026-03-19 08:26:34', '2026-03-19 08:26:34'),
(748, 'App\\Models\\User', 126, 'auth-token', '5d8628a8b9105d655a4025a944d2a54aa47f002dc064ff8ab1979ca295c5342f', '[\"*\"]', NULL, NULL, '2026-03-19 08:31:15', '2026-03-19 08:31:15'),
(749, 'App\\Models\\User', 125, 'auth-token', '7c22956fe0a7507f35d671affce976dcbddb0305759c2a50e91534283549fc10', '[\"*\"]', NULL, NULL, '2026-03-19 08:31:56', '2026-03-19 08:31:56'),
(750, 'App\\Models\\User', 126, 'auth-token', '9b6244575fb3df4c82cec2d5a8eaf529b9bf90e1001e602053f5618d338135cc', '[\"*\"]', NULL, NULL, '2026-03-19 08:33:24', '2026-03-19 08:33:24'),
(751, 'App\\Models\\User', 127, 'auth-token', '65accd1dd073eeea3e4f88fd3f890c2bc41a467c54a19708af28a3532d947dc4', '[\"*\"]', NULL, NULL, '2026-03-19 08:40:43', '2026-03-19 08:40:43'),
(752, 'App\\Models\\User', 125, 'auth-token', '2140f1d4e78b7634c9bd954da6eeee51923dc6ea587e182f1dc533536729798b', '[\"*\"]', NULL, NULL, '2026-03-19 08:40:59', '2026-03-19 08:40:59'),
(753, 'App\\Models\\User', 126, 'auth-token', 'af3c7cabfd2286a165b531ff2d8f4466384f94bec07d040a5738c49922aa642e', '[\"*\"]', NULL, NULL, '2026-03-19 08:41:15', '2026-03-19 08:41:15'),
(754, 'App\\Models\\User', 125, 'auth-token', '8f2fb2e35f93f495153c76c92f326a5dc70dc8699e6351c8d45e1eade02c0ae7', '[\"*\"]', NULL, NULL, '2026-03-19 08:41:30', '2026-03-19 08:41:30'),
(755, 'App\\Models\\User', 126, 'auth-token', '6640071c203aa6f6b8c859e96e024b4f80c4f4e4e5f7bde86326eff9e50a55f1', '[\"*\"]', NULL, NULL, '2026-03-19 08:44:29', '2026-03-19 08:44:29'),
(756, 'App\\Models\\User', 127, 'auth-token', '7edea646aff2f18b1db6261a583836aa8b2665e8bdc6f79600f771ef790f8aa5', '[\"*\"]', NULL, NULL, '2026-03-19 08:46:33', '2026-03-19 08:46:33'),
(757, 'App\\Models\\User', 126, 'auth-token', '34ae8fe3c34e7bc40f33beb9a2282d374b5605f078771d9a302266dd8c05b560', '[\"*\"]', NULL, NULL, '2026-03-19 08:47:16', '2026-03-19 08:47:16'),
(758, 'App\\Models\\User', 125, 'auth-token', '45c55d6996223f6c957d763aa9bd1ecd2e2412a8c6a628615acd05bbb11c3c9a', '[\"*\"]', NULL, NULL, '2026-03-19 08:47:36', '2026-03-19 08:47:36'),
(759, 'App\\Models\\User', 126, 'auth-token', '721638958d9f74aa23c697a65090984d156f4969537fde251a74501e277c8a29', '[\"*\"]', NULL, NULL, '2026-03-19 08:48:41', '2026-03-19 08:48:41'),
(760, 'App\\Models\\User', 127, 'auth-token', '4ee8e6da6b685a07d27f070c974d967bf8abd195f72d33c110970b669d700cbc', '[\"*\"]', NULL, NULL, '2026-03-19 08:49:42', '2026-03-19 08:49:42'),
(761, 'App\\Models\\User', 126, 'auth-token', '174289c01ea352b6bc74472b3183cdb8cf959a9ade3be1bb531cedad6b9e34e4', '[\"*\"]', NULL, NULL, '2026-03-19 08:50:30', '2026-03-19 08:50:30'),
(762, 'App\\Models\\User', 126, 'auth-token', '061a6a27712a74de3e145af1cfaddfac2ebb390cd4dfd03cd105bf67f6bfc0bd', '[\"*\"]', NULL, NULL, '2026-03-19 08:55:43', '2026-03-19 08:55:43'),
(763, 'App\\Models\\User', 125, 'auth-token', '514116ac3a95e1710f17f257da5522a57d2428924d063782e96cb9c9f76f1554', '[\"*\"]', NULL, NULL, '2026-03-19 09:01:24', '2026-03-19 09:01:24'),
(764, 'App\\Models\\User', 126, 'auth-token', 'd96514206215239444263e64a0a6a21875c8971e81bfed9c7fd2f60de1f6a317', '[\"*\"]', NULL, NULL, '2026-03-19 09:01:41', '2026-03-19 09:01:41'),
(765, 'App\\Models\\User', 126, 'auth-token', '64e0189691fa548045bd845c2499c364e806b3754b7e1a5e7b7f0b2ab916e639', '[\"*\"]', NULL, NULL, '2026-03-19 09:05:24', '2026-03-19 09:05:24'),
(766, 'App\\Models\\User', 125, 'auth-token', '439f182e221cbc6a2a45bf5c431e196bde01dce85547d76f1791477d3c249ed3', '[\"*\"]', NULL, NULL, '2026-03-19 09:05:38', '2026-03-19 09:05:38'),
(767, 'App\\Models\\User', 126, 'auth-token', 'd7305b15b0e4c08a8b514fd628889389e47b53008513ef1ea346e68c8cb7eedd', '[\"*\"]', NULL, NULL, '2026-03-19 09:06:40', '2026-03-19 09:06:40'),
(768, 'App\\Models\\User', 127, 'auth-token', '45a8d9ecd401729d7439b053787d4d6236aedd72be6ea33b1100582b00e4a839', '[\"*\"]', NULL, NULL, '2026-03-19 09:22:30', '2026-03-19 09:22:30'),
(769, 'App\\Models\\User', 126, 'auth-token', 'cc4d32ed7b1073bdee5fe547ee3fdbc6b81886ad573d717e9dc72e5c7faff4f1', '[\"*\"]', NULL, NULL, '2026-03-19 09:23:13', '2026-03-19 09:23:13'),
(770, 'App\\Models\\User', 125, 'auth-token', '263815fce1f419f60cd2fe70551b9873e3a44e6b258fbec1fa08c42a14259df8', '[\"*\"]', NULL, NULL, '2026-03-19 09:23:35', '2026-03-19 09:23:35'),
(771, 'App\\Models\\User', 126, 'auth-token', 'ab15b80c2476485dc6c9bf436e7519d46405fa3e5ed48764c19764b60612e420', '[\"*\"]', NULL, NULL, '2026-03-19 09:24:21', '2026-03-19 09:24:21'),
(772, 'App\\Models\\User', 125, 'auth-token', '0e9d8833e1bcdbcdd919c73f243850710f373986fd25024ae1add3deb9d85ee7', '[\"*\"]', NULL, NULL, '2026-03-19 09:24:40', '2026-03-19 09:24:40'),
(773, 'App\\Models\\User', 126, 'auth-token', 'e9ccda28bdff285f3cd6ef3ecd4b63cdcc327e83245761119ea6ac95d58566d8', '[\"*\"]', NULL, NULL, '2026-03-19 09:25:12', '2026-03-19 09:25:12'),
(774, 'App\\Models\\User', 137, 'auth-token', 'fb852a865574c15bb59246c6282746cc1f215c6d9d1fc80137666136d9b6fda6', '[\"*\"]', NULL, NULL, '2026-03-19 09:34:57', '2026-03-19 09:34:57'),
(775, 'App\\Models\\User', 126, 'auth-token', 'd5a7d3d0385b8204aedebf62b57b1a1f75d231cc594171921b8448a9a1ae7f4e', '[\"*\"]', NULL, NULL, '2026-03-19 09:38:24', '2026-03-19 09:38:24'),
(776, 'App\\Models\\User', 127, 'auth-token', 'e8810971850cdb5465041f9d08057f1676ed9b37c1bee7754b3613d68f75a654', '[\"*\"]', NULL, NULL, '2026-03-19 09:42:29', '2026-03-19 09:42:29'),
(777, 'App\\Models\\User', 126, 'auth-token', '22de5d90fdccfbe4d7becb5081392487998cfead72b0363c40d782bd4b133096', '[\"*\"]', NULL, NULL, '2026-03-19 09:42:52', '2026-03-19 09:42:52'),
(778, 'App\\Models\\User', 125, 'auth-token', '90be857af9aa44ed1a4b720f3983446d1ea57530d64537a591f106491d94d38d', '[\"*\"]', NULL, NULL, '2026-03-19 09:43:11', '2026-03-19 09:43:11'),
(779, 'App\\Models\\User', 127, 'auth-token', 'd1aa7d92bcc20df67e7d675c55f04b58543a9769ca7ecc960de6afa372106a38', '[\"*\"]', NULL, NULL, '2026-03-19 09:48:51', '2026-03-19 09:48:51'),
(780, 'App\\Models\\User', 126, 'auth-token', 'd363ea0eb98ad4ef48fba54dc8b6483aad11df00e937b834e83942441b932ce2', '[\"*\"]', NULL, NULL, '2026-03-19 09:49:03', '2026-03-19 09:49:03'),
(781, 'App\\Models\\User', 137, 'auth-token', '3022763fcf996dd5f809e32a347b39f681dcb22f72d0d3be234ac33127339a52', '[\"*\"]', NULL, NULL, '2026-03-19 09:49:26', '2026-03-19 09:49:26'),
(782, 'App\\Models\\User', 126, 'auth-token', 'ce0d1a28f9f63d300ddf9092e7d7c4e3b40e97bf0d129c73108fc85ac8ba517f', '[\"*\"]', NULL, NULL, '2026-03-19 10:21:07', '2026-03-19 10:21:07'),
(783, 'App\\Models\\User', 137, 'auth-token', '15fec853a648baa8f87a440bb319486b8fcae9add01e0cb96258fee28bd4afe9', '[\"*\"]', NULL, NULL, '2026-03-19 10:38:09', '2026-03-19 10:38:09'),
(784, 'App\\Models\\User', 127, 'auth-token', '26defc9978e60afaf83281b28bf8aee070e4af59a6d479ade5b1fec13219a349', '[\"*\"]', NULL, NULL, '2026-03-19 10:39:46', '2026-03-19 10:39:46'),
(785, 'App\\Models\\User', 126, 'auth-token', '8e3dcc90c12ad14b7f64a6f3652b934720c2a0f63b281a17bec826073d0e189b', '[\"*\"]', NULL, NULL, '2026-03-19 10:40:07', '2026-03-19 10:40:07'),
(786, 'App\\Models\\User', 127, 'auth-token', 'fd72e32ce685a4c2d3a419e76c4d077b0a86753735f308e1b96a58214936650e', '[\"*\"]', NULL, NULL, '2026-03-19 10:47:09', '2026-03-19 10:47:09'),
(787, 'App\\Models\\User', 126, 'auth-token', 'f0e6907e504af26c7baa39f547351caac82119de6acbef7e11e7b49090e8469d', '[\"*\"]', NULL, NULL, '2026-03-19 10:47:44', '2026-03-19 10:47:44'),
(788, 'App\\Models\\User', 125, 'auth-token', '7032660cea32a2e7a27f4f5b6b67e5b4cb61d5d608d458271a792c49da37dfc1', '[\"*\"]', NULL, NULL, '2026-03-19 10:48:21', '2026-03-19 10:48:21'),
(789, 'App\\Models\\User', 126, 'auth-token', 'fcb2823b4ebe5da4f534e0d2bc983cc4c9b90499ee31ae3722680abbd7325250', '[\"*\"]', NULL, NULL, '2026-03-19 10:49:49', '2026-03-19 10:49:49'),
(790, 'App\\Models\\User', 126, 'auth-token', '70c7d4a9fe7253e8c0fcec13329c7e7212d7b4b00d5764a797dc055c92edf23d', '[\"*\"]', NULL, NULL, '2026-03-19 10:56:19', '2026-03-19 10:56:19'),
(791, 'App\\Models\\User', 127, 'auth-token', '26f50dd86cea1abade1835a034255faa4e7ac2cee379928a621a436e0c4b91a9', '[\"*\"]', NULL, NULL, '2026-03-19 11:04:54', '2026-03-19 11:04:54'),
(792, 'App\\Models\\User', 126, 'auth-token', 'e8fe9fa8916f3b36ce3b4772ba08ee9c4ab3e48e54fddec16a6f07a596ae1ecc', '[\"*\"]', NULL, NULL, '2026-03-19 11:05:15', '2026-03-19 11:05:15'),
(793, 'App\\Models\\User', 125, 'auth-token', '351d4c38054bb358221584aea868bb5f28abc2ccc1d90a834d61ca662875ea0d', '[\"*\"]', NULL, NULL, '2026-03-19 11:05:47', '2026-03-19 11:05:47'),
(794, 'App\\Models\\User', 126, 'auth-token', '054528e4b16a650f1d9be1c9da782cde52bb4945bf58165628b9f921d08eb482', '[\"*\"]', NULL, NULL, '2026-03-19 11:06:06', '2026-03-19 11:06:06'),
(795, 'App\\Models\\User', 125, 'auth-token', 'c531929cde6d837c49e985bd3dcdc58f63b4691df17b5b61dc93eac01863a85f', '[\"*\"]', NULL, NULL, '2026-03-19 11:06:34', '2026-03-19 11:06:34'),
(796, 'App\\Models\\User', 126, 'auth-token', '066d5c4ee2e672a6c420274fcedbdfc06f1d2dbc72d52b5799a937b7c94a1f68', '[\"*\"]', NULL, NULL, '2026-03-19 11:07:20', '2026-03-19 11:07:20'),
(797, 'App\\Models\\User', 137, 'auth-token', 'f46dd2d4d9a645c29b09b74ed4037a2c6c45954261dc1d24f71cc9fd2575ffc5', '[\"*\"]', NULL, NULL, '2026-03-19 11:14:15', '2026-03-19 11:14:15'),
(798, 'App\\Models\\User', 126, 'auth-token', 'e9b279ad3ad17873c8597719cf640a34559fed44b488bbbf0ae1dee8ca123344', '[\"*\"]', NULL, NULL, '2026-03-19 11:25:29', '2026-03-19 11:25:29'),
(799, 'App\\Models\\User', 127, 'auth-token', '2c3a56c9b9511c0eff69790a37b50c0732de8decb5c79550db379ec506e624ec', '[\"*\"]', NULL, NULL, '2026-03-19 11:31:13', '2026-03-19 11:31:13'),
(800, 'App\\Models\\User', 126, 'auth-token', 'ef3198363573001bab35bd80c21ac4dfef911ae31aad71f42f3952649f963ab3', '[\"*\"]', NULL, NULL, '2026-03-19 11:33:43', '2026-03-19 11:33:43'),
(801, 'App\\Models\\User', 125, 'auth-token', '7542d12a0ac8e851253ffd6aa1c70973fc448f2741d660f665642fefdfc24036', '[\"*\"]', NULL, NULL, '2026-03-19 11:34:16', '2026-03-19 11:34:16'),
(802, 'App\\Models\\User', 126, 'auth-token', '6f2fbbabd583f47514d7e955e159c0142b1b0373c1884215b8731395cf2ca327', '[\"*\"]', NULL, NULL, '2026-03-19 11:34:46', '2026-03-19 11:34:46'),
(803, 'App\\Models\\User', 137, 'auth-token', '58cfa9e0c47e80e07d5cf83b019fb7465b13b1e5068e014b9f91a3babb2a3f86', '[\"*\"]', NULL, NULL, '2026-03-19 11:35:17', '2026-03-19 11:35:17'),
(804, 'App\\Models\\User', 126, 'auth-token', '50b07cd08ae2afc033642c171ba0f201a8d4a090576a3f87ec294e5b484f1122', '[\"*\"]', NULL, NULL, '2026-03-19 11:35:42', '2026-03-19 11:35:42'),
(805, 'App\\Models\\User', 137, 'auth-token', '3973434e4fbb67839f60ab4c25d5b8d266ed3907acd534678c3d654338724257', '[\"*\"]', NULL, NULL, '2026-03-19 11:36:57', '2026-03-19 11:36:57'),
(806, 'App\\Models\\User', 126, 'auth-token', '642952acfbd2e7ebfcafa49e2c137e14cb8bec6a81392c7cfd9ae9cba4d33946', '[\"*\"]', NULL, NULL, '2026-03-19 11:38:39', '2026-03-19 11:38:39'),
(807, 'App\\Models\\User', 127, 'auth-token', 'cf4603896af6dc508e211e8e9203922abac514e5964e01fc710175c2d02b4dfc', '[\"*\"]', NULL, NULL, '2026-03-19 11:56:41', '2026-03-19 11:56:41'),
(808, 'App\\Models\\User', 126, 'auth-token', '30337802024a7dc2d5268e7b961efe6e6e985759f2661c24e333654437f6bf2e', '[\"*\"]', NULL, NULL, '2026-03-19 11:56:59', '2026-03-19 11:56:59'),
(809, 'App\\Models\\User', 126, 'auth-token', 'f4d07bb8427f971b71f223c29ea56e86af57e0de7bbd27b424955770c53d4b59', '[\"*\"]', NULL, NULL, '2026-03-19 11:57:49', '2026-03-19 11:57:49'),
(810, 'App\\Models\\User', 125, 'auth-token', '3cb36fe47f09423ea3e1b232ddf2e3c0c72af30994f290f54cf52ffe5050efce', '[\"*\"]', NULL, NULL, '2026-03-19 11:58:22', '2026-03-19 11:58:22'),
(811, 'App\\Models\\User', 126, 'auth-token', '344516cb376a93869747061334e98759b9f8e81967308013e64e72b624a1f9da', '[\"*\"]', NULL, NULL, '2026-03-19 11:58:43', '2026-03-19 11:58:43'),
(812, 'App\\Models\\User', 137, 'auth-token', 'a66db2072833c330177e2922340870f77d47005225e034519be43139125357cc', '[\"*\"]', NULL, NULL, '2026-03-19 11:59:07', '2026-03-19 11:59:07'),
(813, 'App\\Models\\User', 126, 'auth-token', 'c35da8ec33b8a7fec26907455e9d9f6285c283305b7fe4c42c7b961142a2c522', '[\"*\"]', NULL, NULL, '2026-03-19 12:11:37', '2026-03-19 12:11:37'),
(814, 'App\\Models\\User', 127, 'auth-token', '4494820d0f0e4e82c39fffd142f78c3ee49276a5186ed1242a59c7887730c90c', '[\"*\"]', NULL, NULL, '2026-03-19 12:17:34', '2026-03-19 12:17:34'),
(815, 'App\\Models\\User', 128, 'auth-token', '66d435a772dcaafab953d75068c7d35bd85ea5e2dfdd3d37edb594404b7f1fde', '[\"*\"]', NULL, NULL, '2026-03-19 12:17:50', '2026-03-19 12:17:50'),
(816, 'App\\Models\\User', 125, 'auth-token', '89cf188d3e222c7b9878ade463d1f72e004d22567e992668e564c97d3f94afcd', '[\"*\"]', NULL, NULL, '2026-03-19 12:18:23', '2026-03-19 12:18:23'),
(817, 'App\\Models\\User', 128, 'auth-token', '418b23c98e83772df3db7b73acbba878823e8327d0134cd8ae63fc1e38e75046', '[\"*\"]', NULL, NULL, '2026-03-19 12:18:35', '2026-03-19 12:18:35'),
(818, 'App\\Models\\User', 127, 'auth-token', '2dca66a62af8b4749cf683acef224972275811e0da876ad6b28503fd8e90bfbb', '[\"*\"]', NULL, NULL, '2026-03-19 12:22:19', '2026-03-19 12:22:19'),
(819, 'App\\Models\\User', 126, 'auth-token', '4c48433fb98eb854b0c7054438087c31055749bbca3b8b05509bb7b07a42899b', '[\"*\"]', NULL, NULL, '2026-03-19 12:22:33', '2026-03-19 12:22:33'),
(820, 'App\\Models\\User', 125, 'auth-token', 'cc432c12b3b89afa230e2798e693d2412ff02ce41a5b3e6c148dd2222a5506cc', '[\"*\"]', NULL, NULL, '2026-03-19 12:22:50', '2026-03-19 12:22:50'),
(821, 'App\\Models\\User', 126, 'auth-token', 'dfc6a988d9e5b59f13ae2627b4ee30baa17f8c586e9676a3a6594a52c035b59f', '[\"*\"]', NULL, NULL, '2026-03-19 12:23:21', '2026-03-19 12:23:21'),
(822, 'App\\Models\\User', 126, 'auth-token', 'a5b13036a3a799863230fa19a826f245578c6ffd14dd31f20aa93c1fe85474d4', '[\"*\"]', NULL, NULL, '2026-03-19 12:24:18', '2026-03-19 12:24:18'),
(823, 'App\\Models\\User', 125, 'auth-token', 'ffa4bd0440e2fd80784e14ef95c06a61158a4a3b359fafc3b765a3c01498add5', '[\"*\"]', NULL, NULL, '2026-03-19 12:24:45', '2026-03-19 12:24:45'),
(824, 'App\\Models\\User', 126, 'auth-token', '32b6055ecb577be06eb2b5ff9f0babd038d067da80a55695659447ea84062214', '[\"*\"]', NULL, NULL, '2026-03-19 12:25:01', '2026-03-19 12:25:01');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(825, 'App\\Models\\User', 137, 'auth-token', '118d1be90a8ea6a5f04d111ea5c2ed8a015f7bfd297e50cd0e4a9817f4d70b92', '[\"*\"]', NULL, NULL, '2026-03-19 12:25:30', '2026-03-19 12:25:30'),
(826, 'App\\Models\\User', 126, 'auth-token', 'd668f9afd41b70c907852cc3128d77bf357e5d6348123314f26faebd7ed7c202', '[\"*\"]', NULL, NULL, '2026-03-19 12:36:38', '2026-03-19 12:36:38'),
(827, 'App\\Models\\User', 127, 'auth-token', '424a72c9c359e7ea3fbb4876b35e235e292d44cc1c0770dc77b82509e93aad3b', '[\"*\"]', NULL, NULL, '2026-03-19 12:37:48', '2026-03-19 12:37:48'),
(828, 'App\\Models\\User', 126, 'auth-token', '4e47bd5acb25fb34a6fa33cccb9523c8cd219164312021310c3589ea02246066', '[\"*\"]', NULL, NULL, '2026-03-19 12:38:03', '2026-03-19 12:38:03'),
(829, 'App\\Models\\User', 125, 'auth-token', 'ea936e6e0dfe70f444ac060e98628eef8ab8727e69f77d7bb74593c65ef482ee', '[\"*\"]', NULL, NULL, '2026-03-19 12:38:34', '2026-03-19 12:38:34'),
(830, 'App\\Models\\User', 137, 'auth-token', '8220f23abe94938b7188d8e69ea3ae6b717c48cc5e9c90945faef8932d3512a3', '[\"*\"]', NULL, NULL, '2026-03-19 12:39:09', '2026-03-19 12:39:09'),
(831, 'App\\Models\\User', 126, 'auth-token', '453bac0c29db837786d397090cc408155aa285aa391df972abd9157645baffe3', '[\"*\"]', NULL, NULL, '2026-03-19 12:39:26', '2026-03-19 12:39:26'),
(832, 'App\\Models\\User', 137, 'auth-token', 'f54965603df8146ab0ad0181942a319e4df6c82f1302d958e92daa359a78a5ec', '[\"*\"]', NULL, NULL, '2026-03-19 12:39:52', '2026-03-19 12:39:52'),
(833, 'App\\Models\\User', 127, 'auth-token', 'f53f388f283f9a3cb945983f9739dbe5b7b73ca58a839d91cf05670debda6960', '[\"*\"]', NULL, NULL, '2026-03-19 12:46:15', '2026-03-19 12:46:15'),
(834, 'App\\Models\\User', 126, 'auth-token', '59cce932bbd597ef7b5595a43a2ab7f5c5fb28560031d4fbe221c32d659e7d25', '[\"*\"]', NULL, NULL, '2026-03-19 12:46:30', '2026-03-19 12:46:30'),
(835, 'App\\Models\\User', 125, 'auth-token', 'd01952e3a80b05b5672c46fdf9aa326c83fbeca5940ddcfcc9a8cf5653261332', '[\"*\"]', NULL, NULL, '2026-03-19 12:46:46', '2026-03-19 12:46:46'),
(836, 'App\\Models\\User', 126, 'auth-token', 'e132060b6b315ab5f24305de05177c171bf0d01cb555ea505c71813f5614ea08', '[\"*\"]', NULL, NULL, '2026-03-19 12:47:11', '2026-03-19 12:47:11'),
(837, 'App\\Models\\User', 137, 'auth-token', '165b3c9ab3291962d0dcc9fc1d7cca94939f805f1abf0ec869cef7a7d2df3fe5', '[\"*\"]', NULL, NULL, '2026-03-19 12:47:31', '2026-03-19 12:47:31'),
(838, 'App\\Models\\User', 126, 'auth-token', 'ff1b822f406f55bc2b3d7a10f669ab5017984671fa72a8f198d9877aa087146d', '[\"*\"]', NULL, NULL, '2026-03-19 12:48:00', '2026-03-19 12:48:00'),
(839, 'App\\Models\\User', 126, 'auth-token', '9ef0efa017707abb07e0fcac7b0408fe73efd9989e64c6f8c82798c15a6f7fd0', '[\"*\"]', NULL, NULL, '2026-03-19 13:12:37', '2026-03-19 13:12:37'),
(840, 'App\\Models\\User', 127, 'auth-token', 'bcbe335ed83653c4fdc4037a5628768b42fafa2ac0c2a0746a2d9a405dd3e8a7', '[\"*\"]', NULL, NULL, '2026-03-19 13:15:20', '2026-03-19 13:15:20'),
(841, 'App\\Models\\User', 126, 'auth-token', '4239c786a79a51482401133e827f52bc669d6a0b9ad14c951d93269fcaac5a9a', '[\"*\"]', NULL, NULL, '2026-03-19 13:16:07', '2026-03-19 13:16:07'),
(842, 'App\\Models\\User', 125, 'auth-token', 'c4400a6f6ba84697b006f8352493aa440dc615c22edaadf8c3e28118abafd593', '[\"*\"]', NULL, NULL, '2026-03-19 13:16:24', '2026-03-19 13:16:24'),
(843, 'App\\Models\\User', 126, 'auth-token', 'c3280d2549bface89d34567292010c11ac9888f18573ea57287432c8aaa0709d', '[\"*\"]', NULL, NULL, '2026-03-19 13:17:02', '2026-03-19 13:17:02'),
(844, 'App\\Models\\User', 137, 'auth-token', '3dc732202cb9275c4c6b2f1e7c1ff4103c5447bb6706072676fe64f4ae50bb39', '[\"*\"]', NULL, NULL, '2026-03-19 13:17:22', '2026-03-19 13:17:22'),
(845, 'App\\Models\\User', 126, 'auth-token', '37a5bddd7673e7b8887d1c34dde13a20b235d618611189bdbe593a5fb6a6ff55', '[\"*\"]', NULL, NULL, '2026-03-19 13:17:56', '2026-03-19 13:17:56'),
(846, 'App\\Models\\User', 127, 'auth-token', '1e7d3ee93778c5938a2279a7752fca73bb8072faa5e5d37a3f6cb02a4dcad83a', '[\"*\"]', NULL, NULL, '2026-03-19 13:18:20', '2026-03-19 13:18:20'),
(847, 'App\\Models\\User', 28, 'auth-token', 'b2556b81ecb025aea1943f90d620e4ff466d529f0a61e8ff43a9785f6d1ee738', '[\"*\"]', NULL, NULL, '2026-03-19 18:48:52', '2026-03-19 18:48:52'),
(848, 'App\\Models\\User', 128, 'auth-token', 'e5b303ccac682117e59034d795d23a9f9ddd23ddbb2f9a4bfe2d3a402d90e9de', '[\"*\"]', NULL, NULL, '2026-03-19 18:49:28', '2026-03-19 18:49:28'),
(849, 'App\\Models\\User', 125, 'auth-token', '2440bfadd7baf5203daf5fdaa195094369ab47052727cb51547ecfecc0cfd259', '[\"*\"]', NULL, NULL, '2026-03-19 19:11:06', '2026-03-19 19:11:06'),
(850, 'App\\Models\\User', 126, 'auth-token', '1a7d7c7d82b9dddd74d0f551a1fe0bbf9798ad1e706296816e746aa14382caba', '[\"*\"]', NULL, NULL, '2026-03-19 19:11:45', '2026-03-19 19:11:45'),
(851, 'App\\Models\\User', 125, 'auth-token', '3e27297cc138156373f7bf6f14937ade4be9c077a94c03d09bb4cd1369fa7713', '[\"*\"]', NULL, NULL, '2026-03-19 19:12:31', '2026-03-19 19:12:31'),
(852, 'App\\Models\\User', 137, 'auth-token', 'a20be4fd9f56c8e9f98a4f6190f97df15ee652ec5895505390b87c286cd1be02', '[\"*\"]', NULL, NULL, '2026-03-19 19:43:02', '2026-03-19 19:43:02'),
(853, 'App\\Models\\User', 126, 'auth-token', 'dcc4f9a3f644ae87b40f483f82ae603708757a1423560dc3b201675984489971', '[\"*\"]', NULL, NULL, '2026-03-19 19:49:48', '2026-03-19 19:49:48'),
(854, 'App\\Models\\User', 127, 'auth-token', '6499a5a92dc60bb50ac46b4b7bdaaa3a06ad18da2bbec3e09525136ca40678b3', '[\"*\"]', NULL, NULL, '2026-03-19 19:50:05', '2026-03-19 19:50:05'),
(855, 'App\\Models\\User', 126, 'auth-token', '150fbe2efdeac42c6eab585604fd7ab89b8d214a983895f9f04957769eec6241', '[\"*\"]', NULL, NULL, '2026-03-19 19:53:18', '2026-03-19 19:53:18'),
(856, 'App\\Models\\User', 125, 'auth-token', '3f41a4affb883eb88dbd2e51afb107dd17b69f7603a2bac100d26cc87b96c9d5', '[\"*\"]', NULL, NULL, '2026-03-19 19:54:08', '2026-03-19 19:54:08'),
(857, 'App\\Models\\User', 126, 'auth-token', '2f56efe71d280626ff0a832bd370788bcb764de7beae6eeb31f221cb9a62f33a', '[\"*\"]', NULL, NULL, '2026-03-19 19:55:01', '2026-03-19 19:55:01'),
(858, 'App\\Models\\User', 137, 'auth-token', '1f82945a670af02a44ac6d67c1c679768f37a668098517815c1e027a2862ee3b', '[\"*\"]', NULL, NULL, '2026-03-19 19:55:19', '2026-03-19 19:55:19'),
(859, 'App\\Models\\User', 126, 'auth-token', '4a291b569856864fcf60f18f80bddd058bf59204b935e8e541a58430bbf8f78d', '[\"*\"]', NULL, NULL, '2026-03-19 19:56:20', '2026-03-19 19:56:20'),
(860, 'App\\Models\\User', 126, 'auth-token', '738d0e6f051f0791e001ae4a053cf91053a2bec64e9f256f2cbf210d8c2f6e34', '[\"*\"]', NULL, NULL, '2026-03-19 19:57:01', '2026-03-19 19:57:01'),
(861, 'App\\Models\\User', 31, 'auth-token', 'b11017e2681612147aa7b4852c5334c4435a25af88da85e042015ab0f640c31b', '[\"*\"]', NULL, NULL, '2026-03-20 03:56:02', '2026-03-20 03:56:02'),
(862, 'App\\Models\\User', 124, 'auth-token', '490793d5694a7c3b5c565a207f313ca0ffb4a1142c786c79bce21f55aa464f03', '[\"*\"]', NULL, NULL, '2026-03-20 03:56:29', '2026-03-20 03:56:29'),
(863, 'App\\Models\\User', 31, 'auth-token', '580e000974dd1ac9163e9a5be04760385e961df14741a4109a81ee71ec1c5a92', '[\"*\"]', NULL, NULL, '2026-03-20 03:57:14', '2026-03-20 03:57:14'),
(864, 'App\\Models\\User', 28, 'auth-token', 'cfb67c6f8c6dceafca12bcae338c5406acc63785734441c816895cbcacc6f9ed', '[\"*\"]', NULL, NULL, '2026-03-20 03:57:37', '2026-03-20 03:57:37'),
(865, 'App\\Models\\User', 126, 'auth-token', '98b886ba4bfa27d7b439e72f3e7b7df3240c9dd4536099f4df443f45cce13ea1', '[\"*\"]', NULL, NULL, '2026-03-20 04:11:18', '2026-03-20 04:11:18'),
(866, 'App\\Models\\User', 28, 'auth-token', '7d3307268a2181ad577c63d38221048be384afcb41cd9e2e38649c75abfe9098', '[\"*\"]', NULL, NULL, '2026-03-20 04:11:38', '2026-03-20 04:11:38'),
(867, 'App\\Models\\User', 126, 'auth-token', '000cd5d12ba9263a55aa3e7190b6f0a0c7d7b35f6463be20d815526d90b6e627', '[\"*\"]', NULL, NULL, '2026-03-20 04:25:32', '2026-03-20 04:25:32'),
(868, 'App\\Models\\User', 126, 'auth-token', 'b8065f0a928bb781f6d0493ee9d91ffa91c63c5dc3fb3255f49bbff15fa56a65', '[\"*\"]', NULL, NULL, '2026-03-20 04:26:43', '2026-03-20 04:26:43'),
(869, 'App\\Models\\User', 28, 'auth-token', 'bd393060ad59f1c7454eb6538fbc71f4660b4677b0c66f7c8a87b6cfaf7353f5', '[\"*\"]', NULL, NULL, '2026-03-20 04:27:11', '2026-03-20 04:27:11'),
(870, 'App\\Models\\User', 28, 'auth-token', '011e510d3f0d84a7d3e8c6889445fd606c6e6a7cadaee19cfb7c9ed675f69850', '[\"*\"]', NULL, NULL, '2026-03-20 04:27:44', '2026-03-20 04:27:44'),
(871, 'App\\Models\\User', 28, 'auth-token', 'f15af6adda02113560c61ae74b628a1980653208b93709eaf0cef4dee3081d23', '[\"*\"]', NULL, NULL, '2026-03-20 04:49:48', '2026-03-20 04:49:48'),
(872, 'App\\Models\\User', 28, 'auth-token', 'f6d54ced553b17ede1170db5212ac327ccf14a3451fd7a2bef33dba22a843c56', '[\"*\"]', NULL, NULL, '2026-03-20 04:58:45', '2026-03-20 04:58:45'),
(873, 'App\\Models\\User', 126, 'auth-token', 'cf4caf01fa23b3dad61cd9575af75296d735cf6ac470d3b216a7b6d22666b1d8', '[\"*\"]', NULL, NULL, '2026-03-20 05:15:47', '2026-03-20 05:15:47'),
(874, 'App\\Models\\User', 125, 'auth-token', '520a448e803fe5a20fd5087f109fed2b6bae2978a6bc9fc67fffd196a4fcb43f', '[\"*\"]', NULL, NULL, '2026-03-20 05:16:05', '2026-03-20 05:16:05'),
(875, 'App\\Models\\User', 28, 'auth-token', '90d9c1d31905495d99aff7e1ee0dac55401c0c9c235b586af525c27f50e8ba5a', '[\"*\"]', NULL, NULL, '2026-03-20 05:16:36', '2026-03-20 05:16:36'),
(876, 'App\\Models\\User', 28, 'auth-token', '05a25df5d43e0d3e3776a880c48dbbddcb9b5d1f6cfa85ebe469c87d7f51ce84', '[\"*\"]', NULL, NULL, '2026-03-20 05:16:45', '2026-03-20 05:16:45'),
(877, 'App\\Models\\User', 28, 'auth-token', '6363e1ec517b01a2dbbc820f58c435a5636b0b59ce9f7a64787d17c023b24c39', '[\"*\"]', NULL, NULL, '2026-03-20 05:16:52', '2026-03-20 05:16:52'),
(878, 'App\\Models\\User', 28, 'auth-token', 'b734ed981748f490f4d9ec4729bcc263a786f1abdcaaafbccc8f3e3b091677ea', '[\"*\"]', NULL, NULL, '2026-03-20 05:22:11', '2026-03-20 05:22:11'),
(879, 'App\\Models\\User', 28, 'auth-token', '0c87eef5c286228ce05cf22e64ef11dc8a47087076f913935ea74e1eecd8fb23', '[\"*\"]', NULL, NULL, '2026-03-20 05:24:14', '2026-03-20 05:24:14'),
(880, 'App\\Models\\User', 126, 'auth-token', '3c7ec517d2d0a28478903ac17664cd6e55e9407947c117876452cf9bb8f85dca', '[\"*\"]', NULL, NULL, '2026-03-20 05:53:28', '2026-03-20 05:53:28'),
(881, 'App\\Models\\User', 123, 'auth-token', '86c53edf77e3014715eb922e2a6c1762567d3bbe212c8505c23fbc341dd19b95', '[\"*\"]', NULL, NULL, '2026-03-20 05:54:15', '2026-03-20 05:54:15'),
(882, 'App\\Models\\User', 124, 'auth-token', 'c5d542721971bbcd5dde3964065a979b8ccea77245b89b17ba70b8569c4bbf75', '[\"*\"]', NULL, NULL, '2026-03-20 06:08:59', '2026-03-20 06:08:59'),
(883, 'App\\Models\\User', 124, 'auth-token', 'e3580d30f1e50c4350ef87e65796d4cec98e4ae08bc925ee2a91b4a68d39a38d', '[\"*\"]', NULL, NULL, '2026-03-20 06:18:37', '2026-03-20 06:18:37'),
(884, 'App\\Models\\User', 129, 'auth-token', '2b6e85e870c3539dc7e4f3bd7ef5b286973158983d7bc07b553990aa0da9affa', '[\"*\"]', NULL, NULL, '2026-03-20 06:19:03', '2026-03-20 06:19:03'),
(885, 'App\\Models\\User', 129, 'auth-token', '6fc5adb9ac283feb82560beb7c4aba19e493eeb648c7c0485504ed43af54560a', '[\"*\"]', NULL, NULL, '2026-03-20 06:19:17', '2026-03-20 06:19:17'),
(886, 'App\\Models\\User', 126, 'auth-token', '9be0f49f08c3cd0b4d25b12f2615dda46712b843c0abbc1d0aa97e037eff8a16', '[\"*\"]', NULL, NULL, '2026-03-20 06:20:33', '2026-03-20 06:20:33'),
(887, 'App\\Models\\User', 126, 'auth-token', '58bbf9850b6a1aad1dd5cee27efecdb7b4a9aa06569a2fec89e84bd00e2e7749', '[\"*\"]', NULL, NULL, '2026-03-20 06:24:41', '2026-03-20 06:24:41'),
(888, 'App\\Models\\User', 126, 'auth-token', '10f0468247ba4f9900e17c6e7125b59bae22105308ec7a32592a4d1858287edf', '[\"*\"]', NULL, NULL, '2026-03-20 06:37:51', '2026-03-20 06:37:51'),
(889, 'App\\Models\\User', 123, 'auth-token', 'c9e9a03b6deaad8678d86c53f0996bd8749c8a4e8a0ce0feb57cfafa94686740', '[\"*\"]', NULL, NULL, '2026-03-20 06:38:03', '2026-03-20 06:38:03'),
(890, 'App\\Models\\User', 129, 'auth-token', '3ad6c38105d8d2909c36191fda3519bb273623a62976237d2e13167ba17744a4', '[\"*\"]', NULL, NULL, '2026-03-20 06:44:20', '2026-03-20 06:44:20'),
(891, 'App\\Models\\User', 129, 'auth-token', 'f15043b4facc9314dc919c737b41c30c62a30f60c0df23e34c4563431d722bb5', '[\"*\"]', NULL, NULL, '2026-03-20 06:48:12', '2026-03-20 06:48:12'),
(892, 'App\\Models\\User', 125, 'auth-token', '82c1b7fc30eeceb5103f74603aaaca1c716e8cfd7d229d4681a316f3e3465931', '[\"*\"]', NULL, NULL, '2026-03-20 06:48:43', '2026-03-20 06:48:43'),
(893, 'App\\Models\\User', 124, 'auth-token', '2ccc893bc8b2816f148a7e41c2a2610526e7f18a15f27692b8c7421e8f1240ec', '[\"*\"]', NULL, NULL, '2026-03-20 06:51:40', '2026-03-20 06:51:40'),
(894, 'App\\Models\\User', 137, 'auth-token', '4fd95f5986c5d8e2d340123e647e13197d0f2fc077d4e83ef1421bbcc01e41b0', '[\"*\"]', NULL, NULL, '2026-03-20 06:52:04', '2026-03-20 06:52:04'),
(895, 'App\\Models\\User', 128, 'auth-token', 'd6f35619bbce82e1d3f1ca80cdf1c5d2e0d1c8161535f41e732d9e54acb94da7', '[\"*\"]', NULL, NULL, '2026-03-20 06:52:17', '2026-03-20 06:52:17'),
(896, 'App\\Models\\User', 127, 'auth-token', '488dd520bb569719dd06c297ccf264db5de9c5456b79fff5f68c8c88afe28933', '[\"*\"]', NULL, NULL, '2026-03-20 06:57:01', '2026-03-20 06:57:01'),
(897, 'App\\Models\\User', 127, 'auth-token', '86a30b6e5e45b96a7afa1cb269a6983e686057fa6b18f6f9e415ba8547a69f7c', '[\"*\"]', NULL, NULL, '2026-03-20 06:59:50', '2026-03-20 06:59:50'),
(898, 'App\\Models\\User', 124, 'auth-token', 'ab6492e676e2f675cdeacbe1f03ffcf85d292286bcf0fd43b5ebda070508d066', '[\"*\"]', NULL, NULL, '2026-03-20 07:02:10', '2026-03-20 07:02:10'),
(899, 'App\\Models\\User', 124, 'auth-token', '3cdcc4a1503f9390f743b048fe54c4a4c0e90d27de85efaa49b9df6f57150f25', '[\"*\"]', NULL, NULL, '2026-03-20 07:05:21', '2026-03-20 07:05:21'),
(900, 'App\\Models\\User', 127, 'auth-token', '314aad2e9aaf0733d31154fa52bba23cfdaf85253ce8ad9c9f957bac03875560', '[\"*\"]', NULL, NULL, '2026-03-20 07:15:59', '2026-03-20 07:15:59'),
(901, 'App\\Models\\User', 129, 'auth-token', 'e73ebae06917355b562d956cb5bdd6907bce0289144c046106e38c487e72d1dd', '[\"*\"]', NULL, NULL, '2026-03-20 07:16:11', '2026-03-20 07:16:11'),
(902, 'App\\Models\\User', 129, 'auth-token', 'c84437cfc650f95032a2f5dda78224af455444f53d6282b88628edd7a9df5ee4', '[\"*\"]', NULL, NULL, '2026-03-20 07:33:43', '2026-03-20 07:33:43'),
(903, 'App\\Models\\User', 124, 'auth-token', 'd6d3821ddd063694453a152b32a4fb822baef664c1362e2365ba7d70c8b08472', '[\"*\"]', NULL, NULL, '2026-03-20 07:38:20', '2026-03-20 07:38:20'),
(904, 'App\\Models\\User', 124, 'auth-token', '06e6d0440dfbd74f48a1687a0dea8aa233fabe04c8e37d02b71c7d9ce209a214', '[\"*\"]', NULL, NULL, '2026-03-20 07:56:23', '2026-03-20 07:56:23'),
(905, 'App\\Models\\User', 125, 'auth-token', '4f595bbff711c14b5cbd36fa2a5a17051f4d2d21a04c691b2f70e743b6169412', '[\"*\"]', NULL, NULL, '2026-03-20 08:05:43', '2026-03-20 08:05:43'),
(906, 'App\\Models\\User', 125, 'auth-token', '0dbdc6ddd5bf7b1d340f97d82293758eb6e1a12515a4c53e3f587f7c88215cef', '[\"*\"]', NULL, NULL, '2026-03-20 08:07:10', '2026-03-20 08:07:10'),
(907, 'App\\Models\\User', 124, 'auth-token', '01de16b71d62f1e9d8d8d1b75e851d77ab5d503244a011f19bd9f6e2c362cc68', '[\"*\"]', NULL, NULL, '2026-03-20 12:50:47', '2026-03-20 12:50:47'),
(908, 'App\\Models\\User', 125, 'auth-token', '9572768ad9eb4490645a2f4006ac42a3cfd8ad9b603e9b7c734703dbf42232f6', '[\"*\"]', NULL, NULL, '2026-03-20 12:51:13', '2026-03-20 12:51:13'),
(909, 'App\\Models\\User', 125, 'auth-token', '947751008562a726500fabf835c7491eede54a324605f1a0138281c2d12eb87c', '[\"*\"]', NULL, NULL, '2026-03-20 12:55:21', '2026-03-20 12:55:21'),
(910, 'App\\Models\\User', 127, 'auth-token', '7d5f1d9431476d3d650dd16aefe7560b5dcaad03d27f2c34c78f64c35066e29f', '[\"*\"]', NULL, NULL, '2026-03-20 12:56:17', '2026-03-20 12:56:17'),
(911, 'App\\Models\\User', 127, 'auth-token', '076f0fe0d9c77fe46803cdcefe4d5fc778ae3d193d34b8500dd3c1252098c4f8', '[\"*\"]', NULL, NULL, '2026-03-20 12:57:38', '2026-03-20 12:57:38'),
(912, 'App\\Models\\User', 123, 'auth-token', 'ba7f0f0c393317d60085a47c2466899d3934b5f1e0f2981004b2d35db6ebccea', '[\"*\"]', NULL, NULL, '2026-03-20 12:58:30', '2026-03-20 12:58:30'),
(913, 'App\\Models\\User', 123, 'auth-token', '89d6d979cb42442cd4e2557bd4d87c2581e260a32383461031a74c8adde0f35f', '[\"*\"]', NULL, NULL, '2026-03-20 13:00:08', '2026-03-20 13:00:08'),
(914, 'App\\Models\\User', 124, 'auth-token', '9290c25326686a33071027a40812b2ab6b180f826fef264ab075b5dd97237c5a', '[\"*\"]', NULL, NULL, '2026-03-20 13:03:25', '2026-03-20 13:03:25'),
(915, 'App\\Models\\User', 127, 'auth-token', '2106ba07d22185a3be9cc996a4f3b43cf6c991c6503f658758531e74cdc150cc', '[\"*\"]', NULL, NULL, '2026-03-20 13:03:37', '2026-03-20 13:03:37'),
(916, 'App\\Models\\User', 126, 'auth-token', 'a296c833bf5d1c3e32c721aa3453280289926b9c447980ebf515b11233358d62', '[\"*\"]', NULL, NULL, '2026-03-20 13:04:29', '2026-03-20 13:04:29'),
(917, 'App\\Models\\User', 129, 'auth-token', '1eb2a2ed2e0ff711a97fbdf83eb3ccce7f8c7bbc00650c2d118b6763007ad42d', '[\"*\"]', NULL, NULL, '2026-03-20 13:05:10', '2026-03-20 13:05:10'),
(918, 'App\\Models\\User', 125, 'auth-token', '0576ee3d833900906f850f74a7ac39ac188d70eee146a77bbf995a0267eef14a', '[\"*\"]', NULL, NULL, '2026-03-20 13:05:38', '2026-03-20 13:05:38'),
(919, 'App\\Models\\User', 124, 'auth-token', 'da315e6f90c11362fb8b3ff30164fc8a7f2ab224117e88d614a6a5c60883113c', '[\"*\"]', NULL, NULL, '2026-03-20 13:06:37', '2026-03-20 13:06:37'),
(920, 'App\\Models\\User', 124, 'auth-token', 'fedee4fac5e6b8b3473e992c21db06cd6efbf1847cd7b5f67f816ccaba500743', '[\"*\"]', NULL, NULL, '2026-03-20 13:12:59', '2026-03-20 13:12:59'),
(921, 'App\\Models\\User', 127, 'auth-token', '41dbfa6bb4badcda1cec651b3237dd8de30a65dc0ad9136e36b7572da2cdc238', '[\"*\"]', NULL, NULL, '2026-03-20 13:15:16', '2026-03-20 13:15:16'),
(922, 'App\\Models\\User', 28, 'auth-token', '73389f106b1cc4899f7f0fe9148971deaa79f50c84197d1c2f5a776a55d162d8', '[\"*\"]', NULL, NULL, '2026-03-20 13:16:35', '2026-03-20 13:16:35'),
(923, 'App\\Models\\User', 127, 'auth-token', '1abab792b9fdda33ad053dda5d500627528e4f2836e6f1fea6bcf6aeaae9e157', '[\"*\"]', NULL, NULL, '2026-03-20 13:17:11', '2026-03-20 13:17:11'),
(924, 'App\\Models\\User', 126, 'auth-token', '262ca7a574be4aecbd6f3999f9c695182b682dff9c61c9d61c928b0209995224', '[\"*\"]', NULL, NULL, '2026-03-20 13:17:29', '2026-03-20 13:17:29'),
(925, 'App\\Models\\User', 140, 'auth-token', 'ae706b0bdf3043bd5a4b5a43c126c5f5698b4c63b5cece9d5ec3c5d2dded62f3', '[\"*\"]', NULL, NULL, '2026-03-20 13:19:27', '2026-03-20 13:19:27'),
(926, 'App\\Models\\User', 140, 'auth-token', '4f4abcd690eb5818614169a5126ceed8032725cd06b3b093fe1f457ad1cf1ea6', '[\"*\"]', NULL, NULL, '2026-03-20 13:19:56', '2026-03-20 13:19:56'),
(927, 'App\\Models\\User', 140, 'auth-token', 'cbbd12682da494a771bfb88e25933c87aa88d004ec0c720b851ee8423f188d26', '[\"*\"]', NULL, NULL, '2026-03-20 13:20:37', '2026-03-20 13:20:37'),
(928, 'App\\Models\\User', 137, 'auth-token', 'f8ff6b09e32ac50d9bc0ead31afbd9772d39c7a44385af3ee3f9e7a7b4886247', '[\"*\"]', NULL, NULL, '2026-03-20 13:21:10', '2026-03-20 13:21:10'),
(929, 'App\\Models\\User', 126, 'auth-token', 'c113eeeb8b0d869a13e923fd2f60c8842758344496e3482f7a3cff116386f814', '[\"*\"]', NULL, NULL, '2026-03-20 13:21:22', '2026-03-20 13:21:22'),
(930, 'App\\Models\\User', 125, 'auth-token', '674975796ee9d16995c04f7725ea045828fbd33058baf4c8b1cb1ecb73fbe708', '[\"*\"]', NULL, NULL, '2026-03-20 13:21:46', '2026-03-20 13:21:46'),
(931, 'App\\Models\\User', 126, 'auth-token', '22f71df0da1e0c2d0c5e51af4e9781bd2864f1758089435ef7b25bf0e414e935', '[\"*\"]', NULL, NULL, '2026-03-20 13:22:13', '2026-03-20 13:22:13'),
(932, 'App\\Models\\User', 125, 'auth-token', 'd5cc32fa2da737e9c297dcc657f0813865ab5963dd796b9bb9e6de1e249f2e74', '[\"*\"]', NULL, NULL, '2026-03-20 13:22:29', '2026-03-20 13:22:29'),
(933, 'App\\Models\\User', 126, 'auth-token', '166bd058c9d7875a38d0e5e0448c3ec81dbdaa38950d53acd3a61ca96a5ae04e', '[\"*\"]', NULL, NULL, '2026-03-20 13:22:49', '2026-03-20 13:22:49'),
(934, 'App\\Models\\User', 137, 'auth-token', '99eb1014d1f063b1100a0ade54bfb3e979e5bac69eea06387cb061e16da4f396', '[\"*\"]', NULL, NULL, '2026-03-20 13:23:42', '2026-03-20 13:23:42'),
(935, 'App\\Models\\User', 126, 'auth-token', '9ae0f9fb7c4ae5e60519867a26a204e300826037ae9babd7f4a8cfa2be26a98c', '[\"*\"]', NULL, NULL, '2026-03-20 13:24:35', '2026-03-20 13:24:35'),
(936, 'App\\Models\\User', 127, 'auth-token', '743a1fa785e21d54499671303a20b359e17111178c141d1f9afed1cbb504c3c8', '[\"*\"]', NULL, NULL, '2026-03-20 13:24:54', '2026-03-20 13:24:54'),
(937, 'App\\Models\\User', 128, 'auth-token', 'a5a237c8deb453dac7a902c8cf9f6fd5af1d9220bced2fe7e419e646d73c1c29', '[\"*\"]', NULL, NULL, '2026-03-20 13:25:36', '2026-03-20 13:25:36'),
(938, 'App\\Models\\User', 28, 'auth-token', 'd06d5c926d2df169795f15d8b2c197305d8ae09d7f5da01a994e357c3256102f', '[\"*\"]', NULL, NULL, '2026-03-20 13:27:17', '2026-03-20 13:27:17'),
(939, 'App\\Models\\User', 124, 'auth-token', '323fb226e0f82e08166c932b54428f440e83367f0b077d1de2eb8f1be81e392b', '[\"*\"]', NULL, NULL, '2026-03-20 13:28:13', '2026-03-20 13:28:13'),
(940, 'App\\Models\\User', 129, 'auth-token', 'fa3885818277e98eb43dc7067e548d7280b383dcf0c4ba4cfd33d7a162d4cb34', '[\"*\"]', NULL, NULL, '2026-03-20 13:28:46', '2026-03-20 13:28:46'),
(941, 'App\\Models\\User', 127, 'auth-token', 'e8c9f58f483af73bf98c62c0bc1c157d6179b632f8e3ec5430113beaad8f609f', '[\"*\"]', NULL, NULL, '2026-03-20 13:29:37', '2026-03-20 13:29:37'),
(942, 'App\\Models\\User', 124, 'auth-token', '739c5717dd66054caece06305951f9a51d9ac54b6748d7895ab7e2cb524c08a9', '[\"*\"]', NULL, NULL, '2026-03-20 13:40:02', '2026-03-20 13:40:02'),
(943, 'App\\Models\\User', 128, 'auth-token', '07719f5a096e1083ad8e415531bc0427c21b36bbc898d28901f051a492cc35ba', '[\"*\"]', NULL, NULL, '2026-03-20 13:45:14', '2026-03-20 13:45:14'),
(944, 'App\\Models\\User', 124, 'auth-token', '4e1e95cae377b40f1bd020217eb021db9d85f83026e6fc3d416fdc05763f2a5a', '[\"*\"]', NULL, NULL, '2026-03-20 13:54:43', '2026-03-20 13:54:43'),
(945, 'App\\Models\\User', 124, 'auth-token', '3ddf52ad813e323a2ad12401e2126baeb5a476a6da3c0405662c4188bde73105', '[\"*\"]', NULL, NULL, '2026-03-20 13:58:48', '2026-03-20 13:58:48'),
(946, 'App\\Models\\User', 129, 'auth-token', '4eaa2c81eef2dbb23d24b83102e2cfc385fdd6e2a8486339b2dca061987212c1', '[\"*\"]', NULL, NULL, '2026-03-20 14:05:43', '2026-03-20 14:05:43'),
(947, 'App\\Models\\User', 124, 'auth-token', 'fc26e9aa34c00f672d241a589cdbcf1910d284c374057f73a7888be4c3e45fcd', '[\"*\"]', NULL, NULL, '2026-03-20 14:06:29', '2026-03-20 14:06:29'),
(948, 'App\\Models\\User', 129, 'auth-token', '386d9ac8bc5c15617130049d3867796446afb7f05ea6d0b8f3b14f79816f4691', '[\"*\"]', NULL, NULL, '2026-03-20 14:13:42', '2026-03-20 14:13:42'),
(949, 'App\\Models\\User', 124, 'auth-token', '409631a05d80f02a41b76111191f36ff5b7808c8a0fe0f233638e5e15d8b03d3', '[\"*\"]', NULL, NULL, '2026-03-20 14:14:07', '2026-03-20 14:14:07'),
(950, 'App\\Models\\User', 129, 'auth-token', '21421bcc5481c4677905a1de527a503801f65efaf983434d92284b8a127f60bf', '[\"*\"]', NULL, NULL, '2026-03-20 14:14:44', '2026-03-20 14:14:44'),
(951, 'App\\Models\\User', 125, 'auth-token', '6a5a81751d135bf24f16419e139d055ea266d03725c2b81615ec511a4dc5e49c', '[\"*\"]', NULL, NULL, '2026-03-20 14:16:56', '2026-03-20 14:16:56'),
(952, 'App\\Models\\User', 124, 'auth-token', '1944e2428bd5caba4ae54d0632ef43398424eed15ca1c419cb6c38b1c4b66d84', '[\"*\"]', NULL, NULL, '2026-03-20 14:17:20', '2026-03-20 14:17:20'),
(953, 'App\\Models\\User', 28, 'auth-token', '42fb470b1670d12ad68d7f30bc15a53c30e6ffee2be4631f6cc033721c0d12f4', '[\"*\"]', NULL, NULL, '2026-03-20 14:27:38', '2026-03-20 14:27:38'),
(954, 'App\\Models\\User', 125, 'auth-token', '26273d6de8f635105a66953d7617e1eda9c8b4c12fc213844a490c012f0501c2', '[\"*\"]', NULL, NULL, '2026-03-20 14:41:23', '2026-03-20 14:41:23'),
(955, 'App\\Models\\User', 124, 'auth-token', 'f9936a3f37cb1ce27ffe24073cca471fa2e6dbb8a47833ea864139568d33193e', '[\"*\"]', NULL, NULL, '2026-03-20 14:53:36', '2026-03-20 14:53:36'),
(956, 'App\\Models\\User', 127, 'auth-token', '4708535927fffd1d06cc20a2e514ae16afe406d46e77637802034091fddec1fe', '[\"*\"]', NULL, NULL, '2026-03-20 14:56:22', '2026-03-20 14:56:22'),
(957, 'App\\Models\\User', 28, 'auth-token', '3d1e441f64448c5f6502704a1656a96c5295aacb361630e53a026bf149d69d8f', '[\"*\"]', NULL, NULL, '2026-03-20 14:57:05', '2026-03-20 14:57:05'),
(958, 'App\\Models\\User', 28, 'auth-token', '4b01ba89916975a8dc2a362c3c90284b3a3663ff45514b5b24ee1869427425ca', '[\"*\"]', NULL, NULL, '2026-03-20 15:02:34', '2026-03-20 15:02:34'),
(959, 'App\\Models\\User', 137, 'auth-token', 'f4cf0348271b6e85b5db439eb329fd0415fa5de3ee1fee23f172738caf9233f5', '[\"*\"]', NULL, NULL, '2026-03-21 05:10:28', '2026-03-21 05:10:28'),
(960, 'App\\Models\\User', 125, 'auth-token', '0e5ee6c87bf6a4c03125c201290f097a3eddabcba8b09df18fd9feecdc117f9b', '[\"*\"]', NULL, NULL, '2026-03-21 05:10:43', '2026-03-21 05:10:43'),
(961, 'App\\Models\\User', 28, 'auth-token', '67c8fac55ed383b12345c1529a4a8143bd92d3ddd63202e45752b75ffc9b8f31', '[\"*\"]', NULL, NULL, '2026-03-21 05:19:03', '2026-03-21 05:19:03'),
(962, 'App\\Models\\User', 28, 'auth-token', '59b578a47ac052bc3833aff1d8f97f81908dde209829b476a68683b421c51ffe', '[\"*\"]', NULL, NULL, '2026-03-21 05:21:10', '2026-03-21 05:21:10'),
(963, 'App\\Models\\User', 125, 'auth-token', '91123f4c94c3e3375006e2b0699a2395a890d21c1883579fac11e78680ff1619', '[\"*\"]', NULL, NULL, '2026-03-21 05:30:41', '2026-03-21 05:30:41'),
(964, 'App\\Models\\User', 28, 'auth-token', '5759465c8f60ab5ea2b01dd53f8baeac19c5d8105a60e6aba0ec07e877d4b4be', '[\"*\"]', NULL, NULL, '2026-03-21 05:31:56', '2026-03-21 05:31:56'),
(965, 'App\\Models\\User', 125, 'auth-token', '4a254fb20b0ef23e65d438d6510765e5562514f63b82d5216d67bbcbdc7bcdf4', '[\"*\"]', NULL, NULL, '2026-03-21 05:39:32', '2026-03-21 05:39:32'),
(966, 'App\\Models\\User', 127, 'auth-token', 'b2ce5014aac563517043d34a2593985e3044ae013e0e29c616a1b530f6cc0c14', '[\"*\"]', NULL, NULL, '2026-03-21 05:54:01', '2026-03-21 05:54:01'),
(967, 'App\\Models\\User', 126, 'auth-token', '2c50c83511760f9c5d87c02874f17b8238f55ef2fe840dc7793b93cfa79e9175', '[\"*\"]', NULL, NULL, '2026-03-21 05:55:01', '2026-03-21 05:55:01'),
(968, 'App\\Models\\User', 125, 'auth-token', '172455b66a4a4642caae32ac1627921271a641f0d60c1e188528893577643d32', '[\"*\"]', NULL, NULL, '2026-03-21 05:55:20', '2026-03-21 05:55:20'),
(969, 'App\\Models\\User', 126, 'auth-token', '77e172e5ed3cfe5a978f012ac32bcea8fb275bf79adf71c78906b0c0c5249ad5', '[\"*\"]', NULL, NULL, '2026-03-21 05:55:59', '2026-03-21 05:55:59'),
(970, 'App\\Models\\User', 137, 'auth-token', '51448f241459733917ea1bfa19619d04ae479a32704e55cd73869d699c2394a1', '[\"*\"]', NULL, NULL, '2026-03-21 05:56:26', '2026-03-21 05:56:26'),
(971, 'App\\Models\\User', 126, 'auth-token', '70b4fb97a6a86688dd7e9c40e67186a6b7656d1a022640bf65624f6022fa9af7', '[\"*\"]', NULL, NULL, '2026-03-21 05:56:42', '2026-03-21 05:56:42'),
(972, 'App\\Models\\User', 125, 'auth-token', '37aa45db2bdc84c3123dca5d86b0bb680aeafb7588b4694214e7285d145bc74d', '[\"*\"]', NULL, NULL, '2026-03-21 05:57:30', '2026-03-21 05:57:30'),
(973, 'App\\Models\\User', 128, 'auth-token', '8841f0562e45e92e022573a8d98897c888eff9a8ca7a52e6accc17e0c39b4a7e', '[\"*\"]', NULL, NULL, '2026-03-21 06:00:43', '2026-03-21 06:00:43'),
(974, 'App\\Models\\User', 125, 'auth-token', 'e7fd231be95eb643ae74f0c9661753a6716563799b1ff00f9c69696ef1451a2c', '[\"*\"]', NULL, NULL, '2026-03-21 06:07:03', '2026-03-21 06:07:03'),
(975, 'App\\Models\\User', 128, 'auth-token', '9271be28d2ddf73efac7f6ca71a87dba878516d7d9dbe888c98258cb83aa2adc', '[\"*\"]', NULL, NULL, '2026-03-21 06:08:00', '2026-03-21 06:08:00'),
(976, 'App\\Models\\User', 125, 'auth-token', '045d725b1944d2ab877da2df341c0da637cefb0b571c4ae5647eac20da312b4a', '[\"*\"]', NULL, NULL, '2026-03-21 06:13:33', '2026-03-21 06:13:33'),
(977, 'App\\Models\\User', 123, 'auth-token', '75c39665fd3e2e1845ba61753ac4a18232e3e0cbb0df916e50a8d9e9afc24723', '[\"*\"]', NULL, NULL, '2026-03-21 06:14:55', '2026-03-21 06:14:55'),
(978, 'App\\Models\\User', 128, 'auth-token', 'ca701bb9d79e91e053115611c2db9345d9529edeec5d74ceebd11350129a16bc', '[\"*\"]', NULL, NULL, '2026-03-21 06:15:25', '2026-03-21 06:15:25'),
(979, 'App\\Models\\User', 125, 'auth-token', '8f9e5a0e0314b844bb34213c9cc55962f3cc86bdfcd5803df28c6b76c285887c', '[\"*\"]', NULL, NULL, '2026-03-21 06:23:00', '2026-03-21 06:23:00'),
(980, 'App\\Models\\User', 125, 'auth-token', '311f8dd86e3c315a5624912285fb3499eb3d91f5dc4226c875a15483df1dc3b8', '[\"*\"]', NULL, NULL, '2026-03-21 13:07:54', '2026-03-21 13:07:54'),
(981, 'App\\Models\\User', 125, 'auth-token', '6e4f69aba83845d5160c09e70d356f14c6782831449c892f41d059a4f36574ad', '[\"*\"]', NULL, NULL, '2026-03-21 13:22:28', '2026-03-21 13:22:28'),
(982, 'App\\Models\\User', 28, 'auth-token', '0c79df140047523f12fb4381c4d065c0991ad5b68e61806d1b59de8c924c965a', '[\"*\"]', NULL, NULL, '2026-03-21 13:27:28', '2026-03-21 13:27:28'),
(983, 'App\\Models\\User', 125, 'auth-token', '07d1113e1c0f42d1dd37f4a6d69c250d0361eb1b815b5f0804f25aed0bc9531c', '[\"*\"]', NULL, NULL, '2026-03-21 13:30:35', '2026-03-21 13:30:35'),
(984, 'App\\Models\\User', 28, 'auth-token', '706ecd645cb47fe911ef4c08ca65d094db82df806aa510794250a97fac44449d', '[\"*\"]', NULL, NULL, '2026-03-21 13:36:49', '2026-03-21 13:36:49'),
(985, 'App\\Models\\User', 28, 'auth-token', 'd5f34e3ffb6c3ea4f12d62af14aebffc6f436d499c9aa085f633ee8cdd1e44af', '[\"*\"]', NULL, NULL, '2026-03-21 13:39:12', '2026-03-21 13:39:12'),
(986, 'App\\Models\\User', 125, 'auth-token', '79c5bbe78c771fdf827621ad5bd5c726e631b52e56c4f3a7bc45db55a5b1ddf8', '[\"*\"]', NULL, NULL, '2026-03-21 13:39:46', '2026-03-21 13:39:46'),
(987, 'App\\Models\\User', 28, 'auth-token', '6b633ed02fd8d3a561abd98674891b00da606ff3c1d91b1c5a8226832fed66d3', '[\"*\"]', NULL, NULL, '2026-03-21 13:40:06', '2026-03-21 13:40:06'),
(988, 'App\\Models\\User', 125, 'auth-token', 'fca284b676f227c86d36b270235173b370b76afafa48b1c2685cf96b355bc04e', '[\"*\"]', NULL, NULL, '2026-03-21 13:49:12', '2026-03-21 13:49:12'),
(989, 'App\\Models\\User', 128, 'auth-token', '75089368eb302ae65111be1776f244adc4319cd0bcef8eb76227dbc1bf89ac59', '[\"*\"]', NULL, NULL, '2026-03-21 13:49:33', '2026-03-21 13:49:33'),
(990, 'App\\Models\\User', 125, 'auth-token', '59207751e39588921b5c49ca37d57f796dbc0181db119b0894ccb310f63ea54d', '[\"*\"]', NULL, NULL, '2026-03-21 13:49:55', '2026-03-21 13:49:55'),
(991, 'App\\Models\\User', 125, 'auth-token', 'cf5e3e8be82e3cdb3c87ba3e9d3b3f7f0ccc6714ff9e690f7cc1353d391144ca', '[\"*\"]', NULL, NULL, '2026-03-21 14:00:10', '2026-03-21 14:00:10'),
(992, 'App\\Models\\User', 128, 'auth-token', 'f2a58ee45139bc2538617f5c7a275394bc3dd1b677d86d047fd6becbb5280f18', '[\"*\"]', NULL, NULL, '2026-03-21 14:00:42', '2026-03-21 14:00:42'),
(993, 'App\\Models\\User', 125, 'auth-token', '9179139574b5c8ad910c1681b4d1dae52e260dbae2ad782c743c0248fad29344', '[\"*\"]', NULL, NULL, '2026-03-21 14:01:07', '2026-03-21 14:01:07'),
(994, 'App\\Models\\User', 128, 'auth-token', '57526092019769fd1c53828913906ca3c05d0028bf3c17321868787fc734655f', '[\"*\"]', NULL, NULL, '2026-03-21 14:03:26', '2026-03-21 14:03:26'),
(995, 'App\\Models\\User', 125, 'auth-token', '7e5fe05ac1d36b7b6ae32c3db88f998ff7a74e5e99cc9b513875b02a383549a8', '[\"*\"]', NULL, NULL, '2026-03-21 14:03:54', '2026-03-21 14:03:54'),
(996, 'App\\Models\\User', 127, 'auth-token', 'bc897a83470978810c8ca3e09bf5b54bfbe62b6d93eb05f427822d1179bc54d9', '[\"*\"]', NULL, NULL, '2026-03-21 14:20:31', '2026-03-21 14:20:31'),
(997, 'App\\Models\\User', 126, 'auth-token', 'bc19a55c73c749980dc09ba26d767abb394dfbec4078391109b9323a8300184d', '[\"*\"]', NULL, NULL, '2026-03-21 14:21:31', '2026-03-21 14:21:31'),
(998, 'App\\Models\\User', 125, 'auth-token', 'ea6bd8a7822a5cc54e4874fa140e83752e0d9f86ab29027320e9f10f6289aa33', '[\"*\"]', NULL, NULL, '2026-03-21 14:22:02', '2026-03-21 14:22:02'),
(999, 'App\\Models\\User', 126, 'auth-token', '35313abad5072d97a2d6316add84862c09185548d7f0cfd1605777edb8bb5a1d', '[\"*\"]', NULL, NULL, '2026-03-21 14:22:34', '2026-03-21 14:22:34'),
(1000, 'App\\Models\\User', 137, 'auth-token', '24b67b7cdc3181f03b14861ca91511829e62f422332ef232c941993a7e8a3321', '[\"*\"]', NULL, NULL, '2026-03-21 14:22:55', '2026-03-21 14:22:55'),
(1001, 'App\\Models\\User', 126, 'auth-token', '4ebe0334d12cfe84bfff1b3ee7edf1a1497b6a6cd3ad588136525995a111b7e1', '[\"*\"]', NULL, NULL, '2026-03-21 14:23:24', '2026-03-21 14:23:24'),
(1002, 'App\\Models\\User', 125, 'auth-token', '6f460c561983a70f6fb21ee0f67e62f7736dc1adedbb986570ba481b0af98fba', '[\"*\"]', NULL, NULL, '2026-03-21 14:23:50', '2026-03-21 14:23:50'),
(1003, 'App\\Models\\User', 128, 'auth-token', 'a2b2d1dffe00b868881f3f5a9185583e9c3ed2bb7e53c452608d244f4c9a78d1', '[\"*\"]', NULL, NULL, '2026-03-21 14:24:10', '2026-03-21 14:24:10'),
(1004, 'App\\Models\\User', 128, 'auth-token', 'cf5dbff59a4976c0c4e01d0a77a990f5e57f980de158fd541a50fe13b2bc360f', '[\"*\"]', NULL, NULL, '2026-03-21 14:49:00', '2026-03-21 14:49:00'),
(1005, 'App\\Models\\User', 128, 'auth-token', 'b3ce1024073a5da36dad4b9d6f9de3b63fdafa15ce9696b720cb17d70dd7e6e7', '[\"*\"]', NULL, NULL, '2026-03-21 14:53:31', '2026-03-21 14:53:31'),
(1006, 'App\\Models\\User', 140, 'auth-token', 'dfa96abe373a75452d527577b883457d7549079e2d4fa90279090afab23c4c93', '[\"*\"]', NULL, NULL, '2026-03-22 09:34:56', '2026-03-22 09:34:56'),
(1007, 'App\\Models\\User', 124, 'auth-token', 'b34a09023ce1691df977eb5931ba9fa962d4dd6cdccc269a156fa261e35d1f0b', '[\"*\"]', NULL, NULL, '2026-03-22 09:40:02', '2026-03-22 09:40:02'),
(1008, 'App\\Models\\User', 141, 'auth-token', 'a37340f5e10557b7186a0fa3e4aaa3c71d806f6112b3154b5c43d05ab5d4326c', '[\"*\"]', NULL, NULL, '2026-03-22 09:43:18', '2026-03-22 09:43:18'),
(1009, 'App\\Models\\User', 31, 'auth-token', '6aac15edec297c0b333b536dbcaca0e36320564673dfce4b20c034b0add3d787', '[\"*\"]', NULL, NULL, '2026-03-22 09:44:25', '2026-03-22 09:44:25'),
(1010, 'App\\Models\\User', 28, 'auth-token', 'dd9f786acf29b7ab2ebfd626bba9e240e772a45b1353c9756c8c077d7baad95a', '[\"*\"]', NULL, NULL, '2026-03-22 10:03:45', '2026-03-22 10:03:45'),
(1011, 'App\\Models\\User', 31, 'auth-token', 'f9c027f962c55fc706ba233ec98d5d55d76a30ff35428193d87ec0d086d318d0', '[\"*\"]', NULL, NULL, '2026-03-22 10:07:12', '2026-03-22 10:07:12'),
(1012, 'App\\Models\\User', 143, 'auth-token', 'b7b24b79516608e1948bb6c5d38fecf48457c5fc7e75a8de0e419eaf89bfcb10', '[\"*\"]', NULL, NULL, '2026-03-22 10:09:19', '2026-03-22 10:09:19'),
(1013, 'App\\Models\\User', 31, 'auth-token', '48f1452e9c39b0920e419f2c2c9cb34ba42ce41a2eabc8a5b8758a09ec3a36f5', '[\"*\"]', NULL, NULL, '2026-03-22 10:13:23', '2026-03-22 10:13:23'),
(1014, 'App\\Models\\User', 142, 'auth-token', '4d517581316a60259d993ae009196b91f2724db55075e24d62c491612d438ac1', '[\"*\"]', NULL, NULL, '2026-03-22 10:13:59', '2026-03-22 10:13:59'),
(1015, 'App\\Models\\User', 31, 'auth-token', '6c35a7d464ee624067ec452adb399c00570db84ebf920613ce7a0e83cbb583f1', '[\"*\"]', NULL, NULL, '2026-03-22 10:18:53', '2026-03-22 10:18:53'),
(1016, 'App\\Models\\User', 148, 'auth-token', 'ff6ff574cc601ce711f5584869e9794cac98b6f880cfe21dca3071709e784fdb', '[\"*\"]', NULL, NULL, '2026-03-22 10:19:54', '2026-03-22 10:19:54'),
(1017, 'App\\Models\\User', 148, 'auth-token', 'b35a906e845c6cc95505eaa0ef27c3c175ba6b207eb03d60fa81e1b213e29f6d', '[\"*\"]', NULL, NULL, '2026-03-22 10:20:20', '2026-03-22 10:20:20'),
(1018, 'App\\Models\\User', 148, 'auth-token', '7b280e6b9893a37a085c8fdf7e60b805ec070dc2a2b070473d863d19f2adff08', '[\"*\"]', NULL, NULL, '2026-03-22 10:21:29', '2026-03-22 10:21:29'),
(1019, 'App\\Models\\User', 31, 'auth-token', '4eb881635d70c31c4bfb1fb082e9e462a0df1c95acdfd04ce87de3de6f93e4ec', '[\"*\"]', NULL, NULL, '2026-03-22 10:22:57', '2026-03-22 10:22:57'),
(1020, 'App\\Models\\User', 149, 'auth-token', 'dbaf665e2474b8277820faab5aa529a4063c9bed7d588e62a2c69570b27d4dc2', '[\"*\"]', NULL, NULL, '2026-03-22 10:23:55', '2026-03-22 10:23:55'),
(1021, 'App\\Models\\User', 149, 'auth-token', '823072f008a9de8a618ec9411c79d404ef16265be8376a89aa68cc3794341899', '[\"*\"]', NULL, NULL, '2026-03-22 10:24:34', '2026-03-22 10:24:34'),
(1022, 'App\\Models\\User', 149, 'auth-token', 'c78606799345c527c420f6ced024c46c57069ed8d31cad2fcb31b934e4eb4076', '[\"*\"]', NULL, NULL, '2026-03-22 10:27:06', '2026-03-22 10:27:06'),
(1023, 'App\\Models\\User', 31, 'auth-token', 'f724d5cccf352935e7881e8d60f25576a6adf1d51b8c5dae18adcaaf02c86095', '[\"*\"]', NULL, NULL, '2026-03-22 10:27:38', '2026-03-22 10:27:38'),
(1024, 'App\\Models\\User', 150, 'auth-token', '6f3415cfbb8c5e2434d6e6cc6eb6283fd0d38953b08baf306cac3b0d98e2c088', '[\"*\"]', NULL, NULL, '2026-03-22 10:28:46', '2026-03-22 10:28:46'),
(1025, 'App\\Models\\User', 150, 'auth-token', 'f176195b2e736dbbbf80422474ba57969b7c29f637e710b35ec12b1d09d0e513', '[\"*\"]', NULL, NULL, '2026-03-22 10:31:58', '2026-03-22 10:31:58'),
(1026, 'App\\Models\\User', 31, 'auth-token', '3659a107ce653011d28d3d1fa1f27def826fcc340144ce8b3603c7a256ff6bce', '[\"*\"]', NULL, NULL, '2026-03-22 10:33:37', '2026-03-22 10:33:37'),
(1027, 'App\\Models\\User', 150, 'auth-token', '90e7964eaf104554773297eb4831e197a913b2f18618c504ab7237ca3b85915f', '[\"*\"]', NULL, NULL, '2026-03-22 10:36:39', '2026-03-22 10:36:39'),
(1028, 'App\\Models\\User', 152, 'auth-token', '427364de6558d801fd094d371be9ac55e587709cd1f01f70f364c3bcfd15bdec', '[\"*\"]', NULL, NULL, '2026-03-22 10:38:43', '2026-03-22 10:38:43'),
(1029, 'App\\Models\\User', 152, 'auth-token', 'bd1a031444c88b0380201c79509d161a17d15b9350c127d417d8c826dbaaa59c', '[\"*\"]', NULL, NULL, '2026-03-22 10:43:09', '2026-03-22 10:43:09'),
(1030, 'App\\Models\\User', 148, 'auth-token', '5e3f58d76a0f4087339563b8b9b1a4f5798a4aa9af443e9753cfd22fb12ed990', '[\"*\"]', NULL, NULL, '2026-03-22 10:47:48', '2026-03-22 10:47:48'),
(1031, 'App\\Models\\User', 154, 'auth-token', 'd92268c338566f609436fa03fb243d3445f7ae8bf759023e80f84c6f7d729cc9', '[\"*\"]', NULL, NULL, '2026-03-22 10:50:34', '2026-03-22 10:50:34'),
(1032, 'App\\Models\\User', 154, 'auth-token', '3fd7d5bdd409e5c08d3d3ba140c47144a52a906f345b3a796187a758222362d4', '[\"*\"]', NULL, NULL, '2026-03-22 10:53:36', '2026-03-22 10:53:36'),
(1033, 'App\\Models\\User', 153, 'auth-token', 'a8737cbd9ca266466984966f2f769d9dd8302603465ccbbe74f299e3f79c4697', '[\"*\"]', NULL, NULL, '2026-03-22 10:55:32', '2026-03-22 10:55:32'),
(1034, 'App\\Models\\User', 153, 'auth-token', '338378cc057ad72d9ab044837014ddae0ff6ddcba46fc785aeeaf051e02a0e43', '[\"*\"]', NULL, NULL, '2026-03-22 10:55:53', '2026-03-22 10:55:53'),
(1035, 'App\\Models\\User', 153, 'auth-token', '45bf0b701485c11eef0d3cf5ac068e29d4461caa28410cfd41a63e48de737423', '[\"*\"]', NULL, NULL, '2026-03-22 10:58:49', '2026-03-22 10:58:49'),
(1036, 'App\\Models\\User', 31, 'auth-token', '27ea60168f4cf413189b2ba98eff7c79feec0877b78d8e0830693582949b4f27', '[\"*\"]', NULL, NULL, '2026-03-22 11:31:54', '2026-03-22 11:31:54'),
(1037, 'App\\Models\\User', 31, 'auth-token', 'f1525ce622ce4de1e9bea4ca98ee2bf8f0b0fd098f2fc8fc62c7e1f8c4d60a31', '[\"*\"]', NULL, NULL, '2026-03-22 11:33:11', '2026-03-22 11:33:11'),
(1038, 'App\\Models\\User', 147, 'auth-token', '4cca3f5121896ed5d9ac95eb174546359d8cb849cda0c96c72186a99db6c606d', '[\"*\"]', NULL, NULL, '2026-03-22 11:33:45', '2026-03-22 11:33:45'),
(1039, 'App\\Models\\User', 147, 'auth-token', '85daf5cbcceda38cd3a3aaa856029568858038c96dab6fb060a7aa3d2df65ef1', '[\"*\"]', NULL, NULL, '2026-03-22 11:34:32', '2026-03-22 11:34:32'),
(1040, 'App\\Models\\User', 147, 'auth-token', '8612ef914c6b8e13fd6936c8b654e55ce286e285ddf0d8058cb5165d7564ba82', '[\"*\"]', NULL, NULL, '2026-03-22 11:35:07', '2026-03-22 11:35:07'),
(1041, 'App\\Models\\User', 151, 'auth-token', 'e8b0cdaedcaea44dbec4b3c876585d05fd3915eb926519c8a4d5bbe6b4def0f7', '[\"*\"]', NULL, NULL, '2026-03-22 11:36:41', '2026-03-22 11:36:41'),
(1042, 'App\\Models\\User', 151, 'auth-token', '9d0ea71f4d6a77075453e5c2cfeb9ed13ae6941ac0ad0bbd5099a50b2237338c', '[\"*\"]', NULL, NULL, '2026-03-22 11:37:18', '2026-03-22 11:37:18'),
(1043, 'App\\Models\\User', 151, 'auth-token', '97e95329436762a11734baaf5d2f2e935aef4bc6795156024da6c80ba76246f5', '[\"*\"]', NULL, NULL, '2026-03-22 11:37:48', '2026-03-22 11:37:48'),
(1044, 'App\\Models\\User', 150, 'auth-token', 'ab18a179418b08095d81eb24082a10ffee53e7f5b7ca7fb771c93b632d682e17', '[\"*\"]', NULL, NULL, '2026-03-22 11:39:04', '2026-03-22 11:39:04'),
(1045, 'App\\Models\\User', 149, 'auth-token', '83497411b4b1ae90ae255716f2bc83d035995bd5817b284876648d1f956e7c3a', '[\"*\"]', NULL, NULL, '2026-03-22 11:39:39', '2026-03-22 11:39:39'),
(1046, 'App\\Models\\User', 150, 'auth-token', '2f2dbaedcb9f489255dd9ef069f256e40098939e232eab58340babb1c55412a5', '[\"*\"]', NULL, NULL, '2026-03-22 11:40:14', '2026-03-22 11:40:14'),
(1047, 'App\\Models\\User', 149, 'auth-token', 'fe57ffb95f8eba21c074c1d083bbc35bb51bf5144d4a1c604abe1d50a964acda', '[\"*\"]', NULL, NULL, '2026-03-22 11:40:31', '2026-03-22 11:40:31'),
(1048, 'App\\Models\\User', 150, 'auth-token', 'ad5da45448511ff3d528319e0e22ad456977ee6e55f721d81999c39f305df258', '[\"*\"]', NULL, NULL, '2026-03-22 11:41:01', '2026-03-22 11:41:01'),
(1049, 'App\\Models\\User', 152, 'auth-token', '77cd65d5c5e773739d4911e425b9e90b142b9e9b3b3f572f4847f6cbb3c5a210', '[\"*\"]', NULL, NULL, '2026-03-22 11:41:50', '2026-03-22 11:41:50'),
(1050, 'App\\Models\\User', 150, 'auth-token', 'c0efb9309c20ed59bff691ecdc03eaba9e5ca15b60713d9db83498abccf25555', '[\"*\"]', NULL, NULL, '2026-03-22 11:42:24', '2026-03-22 11:42:24'),
(1051, 'App\\Models\\User', 149, 'auth-token', 'ce3234503e2d7350256ab606f0d219308958e9e8e1a662c1715cca653be7b14b', '[\"*\"]', NULL, NULL, '2026-03-22 11:43:25', '2026-03-22 11:43:25'),
(1052, 'App\\Models\\User', 153, 'auth-token', '493d1a5fc8a2603963e688b057b38c14980809207ef878108e251ce4ec45c265', '[\"*\"]', NULL, NULL, '2026-03-22 11:43:53', '2026-03-22 11:43:53'),
(1053, 'App\\Models\\User', 149, 'auth-token', 'fc80424698d2b05f9571a1e3995b81a64e09c7e8b9d38f9e73db10db197e5124', '[\"*\"]', NULL, NULL, '2026-03-22 11:45:16', '2026-03-22 11:45:16'),
(1054, 'App\\Models\\User', 148, 'auth-token', '0ffb85c1aece1ab71b2d2961fa265a9df726db17c36d66db92e4a3142083ad91', '[\"*\"]', NULL, NULL, '2026-03-22 11:46:25', '2026-03-22 11:46:25'),
(1055, 'App\\Models\\User', 149, 'auth-token', 'df04bdccbbfe497e3750893884fedf3231abc9ff47f7f2c449a1b5d91eb33b91', '[\"*\"]', NULL, NULL, '2026-03-22 11:46:58', '2026-03-22 11:46:58'),
(1056, 'App\\Models\\User', 148, 'auth-token', 'f7ae85bc1dbce34da0fc5be8c3d0874acc90913ebfa4e709f3b0382dec4f211a', '[\"*\"]', NULL, NULL, '2026-03-23 04:42:09', '2026-03-23 04:42:09'),
(1057, 'App\\Models\\User', 157, 'auth-token', 'a7d9ce67edd5f59adef42e44d98de2b3287ebcb051ead85d415cfccda5710033', '[\"*\"]', NULL, NULL, '2026-03-23 05:08:17', '2026-03-23 05:08:17'),
(1058, 'App\\Models\\User', 157, 'auth-token', 'ba454d56cf2bc6b2b1500e69f4d37e44ca249fcd0e5d1a239f0ae683d95ca21a', '[\"*\"]', NULL, NULL, '2026-03-23 05:09:16', '2026-03-23 05:09:16'),
(1059, 'App\\Models\\User', 157, 'auth-token', 'b3a0c4259ccc2a5b6d67272464aff42bb29dceb51fc101649b2d45fb73a07527', '[\"*\"]', NULL, NULL, '2026-03-23 05:10:28', '2026-03-23 05:10:28'),
(1060, 'App\\Models\\User', 157, 'auth-token', '72fe7e0da1087feacde893066e4d60425769c7e3a8a891273cef87208ba37886', '[\"*\"]', NULL, NULL, '2026-03-23 05:28:54', '2026-03-23 05:28:54'),
(1061, 'App\\Models\\User', 157, 'auth-token', '0cb2e22a8c9ca4fd19ae81fb6fb41bd7ee0b41603c9bba36393f859a2eec3afc', '[\"*\"]', NULL, NULL, '2026-03-23 05:29:50', '2026-03-23 05:29:50'),
(1062, 'App\\Models\\User', 157, 'auth-token', '71656b82f5d090d3639ede1b580f6f850c447d8f23bd6138ae69f407248e9e6f', '[\"*\"]', NULL, NULL, '2026-03-23 05:52:37', '2026-03-23 05:52:37'),
(1063, 'App\\Models\\User', 157, 'auth-token', 'd4975647515b841a7335c5fd466f663647321f101c33f0ca92f437900144c637', '[\"*\"]', NULL, NULL, '2026-03-23 12:56:34', '2026-03-23 12:56:34'),
(1064, 'App\\Models\\User', 157, 'auth-token', 'ae52cf9a1262dc5bb8ade3d8a425fb5a84c3511c5f935e2de4441b7b5dc27449', '[\"*\"]', NULL, NULL, '2026-03-23 13:59:00', '2026-03-23 13:59:00'),
(1065, 'App\\Models\\User', 151, 'auth-token', 'f857491e35d59c8792ea1de43a32b3f3344812dd4784180f254bd4cc945eb05e', '[\"*\"]', NULL, NULL, '2026-03-23 14:00:30', '2026-03-23 14:00:30'),
(1066, 'App\\Models\\User', 157, 'auth-token', '4393a3002d3675866b8ebc0812bfaf7395de427fa293b926bea914cc93bbc0bd', '[\"*\"]', NULL, NULL, '2026-03-23 14:05:26', '2026-03-23 14:05:26'),
(1067, 'App\\Models\\User', 157, 'auth-token', '65eb54cfc55c9616b7c710f2f46ee1698e59e7300d8783a88b79af8857b49481', '[\"*\"]', NULL, NULL, '2026-03-23 14:10:33', '2026-03-23 14:10:33'),
(1068, 'App\\Models\\User', 157, 'auth-token', '209d1c9736ca5d6a7c52888a8bb10f68bdbf790cbd6ac35aa9354249901088bd', '[\"*\"]', NULL, NULL, '2026-03-23 14:10:57', '2026-03-23 14:10:57'),
(1069, 'App\\Models\\User', 151, 'auth-token', 'e1589767895a2055cc0bab9cb736b29f2261243e3e7f3a70e2862fb7368d4451', '[\"*\"]', NULL, NULL, '2026-03-23 14:20:42', '2026-03-23 14:20:42'),
(1070, 'App\\Models\\User', 157, 'auth-token', '329ebc45eec51546a28ca8b825ae048df74d74d6e9f6f950ae084f97276b2bd8', '[\"*\"]', NULL, NULL, '2026-03-23 14:23:12', '2026-03-23 14:23:12'),
(1071, 'App\\Models\\User', 151, 'auth-token', '1da86333a972476cb5ec2ca30dd044ed7dc49c465715afefbc4ef0a2733be1e6', '[\"*\"]', NULL, NULL, '2026-03-23 14:27:41', '2026-03-23 14:27:41'),
(1072, 'App\\Models\\User', 157, 'auth-token', 'a2bf2801bc88a78da31799984a7083209c6beac14a4d18e4605f34dd7d1cf6fa', '[\"*\"]', NULL, NULL, '2026-03-23 14:32:08', '2026-03-23 14:32:08'),
(1073, 'App\\Models\\User', 151, 'auth-token', 'f91396ef7e548238a2a4a518ebca85aac8683f3a8a60726faf812abe3a5d7c20', '[\"*\"]', NULL, NULL, '2026-03-23 14:33:04', '2026-03-23 14:33:04'),
(1074, 'App\\Models\\User', 150, 'auth-token', 'dceeacdc2c6f943baaad8bc651e606f605a00835d622a4280a926cc9ed96afeb', '[\"*\"]', NULL, NULL, '2026-03-23 14:33:21', '2026-03-23 14:33:21'),
(1075, 'App\\Models\\User', 157, 'auth-token', '7be3ace6aa68f4ff0302c346c5b6507ab46eb017f2faf95de37ec6e9eac51bcd', '[\"*\"]', NULL, NULL, '2026-03-23 14:45:05', '2026-03-23 14:45:05'),
(1076, 'App\\Models\\User', 150, 'auth-token', 'adaa1261faf916587195a5d2d05d9762aea50546fbce0d29ab21e13a18ab9944', '[\"*\"]', NULL, NULL, '2026-03-23 14:45:40', '2026-03-23 14:45:40'),
(1077, 'App\\Models\\User', 151, 'auth-token', '868f0e8cf6a75f88e0c6b3a870f8253c8494f5f0aab20d8af39f0ebf8fbbb3ce', '[\"*\"]', NULL, NULL, '2026-03-23 15:00:00', '2026-03-23 15:00:00'),
(1078, 'App\\Models\\User', 151, 'auth-token', '544141349da3a58f7fe6864b0a687097f0887a64a230c9d20900828821951254', '[\"*\"]', NULL, NULL, '2026-03-23 15:03:37', '2026-03-23 15:03:37'),
(1079, 'App\\Models\\User', 157, 'auth-token', '1fca0912bec1a55d65d51c5668c2843c11b7185f7798ed6f8d8d9f840ff15271', '[\"*\"]', NULL, NULL, '2026-03-23 15:26:41', '2026-03-23 15:26:41'),
(1080, 'App\\Models\\User', 151, 'auth-token', 'fe51f0bf0fdcaec5d6359c77e975213a9fc3ded6ccdec5864bcc2c947eb32b65', '[\"*\"]', NULL, NULL, '2026-03-23 15:27:17', '2026-03-23 15:27:17'),
(1081, 'App\\Models\\User', 150, 'auth-token', 'e989465f29f99a71881fb4bdcebc6ecf1c8d4c7b8686b9fa347278c9c302742c', '[\"*\"]', NULL, NULL, '2026-03-23 15:27:53', '2026-03-23 15:27:53'),
(1082, 'App\\Models\\User', 157, 'auth-token', '0259aba2653d924766165de11a9d4eeb3ae623cdb7a45355a3cdb4adaa9d105a', '[\"*\"]', NULL, NULL, '2026-03-23 15:45:41', '2026-03-23 15:45:41'),
(1083, 'App\\Models\\User', 151, 'auth-token', '1928eead997b5393e6455c843feb5e3b62060dfc824a653f92f09a090e3024f1', '[\"*\"]', NULL, NULL, '2026-03-23 15:46:37', '2026-03-23 15:46:37'),
(1084, 'App\\Models\\User', 150, 'auth-token', '5fc9fe71f50302407b9d9a20af1eece8d2e3e3912ee3da4fe8d9c1bee1830869', '[\"*\"]', NULL, NULL, '2026-03-23 15:47:01', '2026-03-23 15:47:01'),
(1085, 'App\\Models\\User', 151, 'auth-token', '0909936a02f7e9f50cc19e8d5502e795823bb43be4d495b0db0f9ee5c3121d26', '[\"*\"]', NULL, NULL, '2026-03-23 15:53:32', '2026-03-23 15:53:32'),
(1086, 'App\\Models\\User', 150, 'auth-token', 'b2b7a33719179c38fcdf734bf0c4f772818819a51bf9aa5cd7274bdcd279318a', '[\"*\"]', NULL, NULL, '2026-03-23 15:54:24', '2026-03-23 15:54:24'),
(1087, 'App\\Models\\User', 150, 'auth-token', '5317f8703f032831a7dc52b86a57df896795757af57c0112c760ecd31f8a7ff7', '[\"*\"]', NULL, NULL, '2026-03-24 03:43:09', '2026-03-24 03:43:09'),
(1088, 'App\\Models\\User', 150, 'auth-token', '6028429ff833746dce36daf5a7cf666d6681da32f038d136dd992e5606607f33', '[\"*\"]', NULL, NULL, '2026-03-24 03:45:09', '2026-03-24 03:45:09'),
(1089, 'App\\Models\\User', 152, 'auth-token', '120cb63513be916eba7857af9aa4f3a22fdf48f256fc4f357c17a33cb5e1f409', '[\"*\"]', NULL, NULL, '2026-03-24 03:45:30', '2026-03-24 03:45:30'),
(1090, 'App\\Models\\User', 150, 'auth-token', 'd939156369b214e2fc6158b18aa3de1f74b2d573c8a8398f31da1c8e6a012d3b', '[\"*\"]', NULL, NULL, '2026-03-24 04:41:41', '2026-03-24 04:41:41'),
(1091, 'App\\Models\\User', 151, 'auth-token', '76d7917fd5fb142848aa3cfc23563f16b6aa14f531040277b4663ecba4bc5a90', '[\"*\"]', NULL, NULL, '2026-03-24 04:42:11', '2026-03-24 04:42:11'),
(1092, 'App\\Models\\User', 152, 'auth-token', '7b5c7cabf61e12ca2d49aa91659a65cc6bd2decce1617e119954e56d4ce0d278', '[\"*\"]', NULL, NULL, '2026-03-24 04:42:36', '2026-03-24 04:42:36'),
(1093, 'App\\Models\\User', 150, 'auth-token', 'ee9aa975b0c717f594a9f792362ab2de310d9488fd8e800a5535b4c1b1a17131', '[\"*\"]', NULL, NULL, '2026-03-24 04:43:02', '2026-03-24 04:43:02'),
(1094, 'App\\Models\\User', 152, 'auth-token', 'daa5d52bfb7da8bb0eb6d57bb4aab6b0a2a2f5bd8b27f2cbe810d3c42ab922b5', '[\"*\"]', NULL, NULL, '2026-03-24 04:53:58', '2026-03-24 04:53:58'),
(1095, 'App\\Models\\User', 150, 'auth-token', '3c328c3c5adee9cc791adadef95198f63ea0cdab51ec20d9c75d91d73f6d4d55', '[\"*\"]', NULL, NULL, '2026-03-24 04:55:59', '2026-03-24 04:55:59'),
(1096, 'App\\Models\\User', 152, 'auth-token', '1d643b8b28b159c4719f904104048076a02934496f83d8413848cb8c634970b6', '[\"*\"]', NULL, NULL, '2026-03-24 04:56:46', '2026-03-24 04:56:46'),
(1097, 'App\\Models\\User', 150, 'auth-token', '9d64552af430f183658c5fc20e960a1099744bd883ea4c9bc62b035508109713', '[\"*\"]', NULL, NULL, '2026-03-24 05:00:02', '2026-03-24 05:00:02');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1098, 'App\\Models\\User', 152, 'auth-token', '55b45df3888779cf30496780b4b4c0ea144be3c0684b8b9a37da2500c61584e8', '[\"*\"]', NULL, NULL, '2026-03-24 05:00:21', '2026-03-24 05:00:21'),
(1099, 'App\\Models\\User', 152, 'auth-token', 'ff49120db4d599365ac215e20890e113c73aa7b157ca5d8421e4693c6ac0dd99', '[\"*\"]', NULL, NULL, '2026-03-24 05:14:59', '2026-03-24 05:14:59'),
(1100, 'App\\Models\\User', 150, 'auth-token', '9f6e71877c29b2d5ed4aa494671a09ffd86e3e2fe56bca0760e359c31726b585', '[\"*\"]', NULL, NULL, '2026-03-24 05:31:38', '2026-03-24 05:31:38'),
(1101, 'App\\Models\\User', 152, 'auth-token', '5649a9b458bbf5bd09d1dfeceb51488928a0c13d1fc0f6a2a1980c692137fbf5', '[\"*\"]', NULL, NULL, '2026-03-24 05:39:43', '2026-03-24 05:39:43'),
(1102, 'App\\Models\\User', 150, 'auth-token', '2d77d448a4c8d967b62975fb7fa98fac0d72b832cf15fe9857eb6ba3de8df08c', '[\"*\"]', NULL, NULL, '2026-03-24 05:45:15', '2026-03-24 05:45:15'),
(1103, 'App\\Models\\User', 149, 'auth-token', 'c2f6297a6312cad1e82b53970652591a47702239a86bd415d2efe26558542559', '[\"*\"]', NULL, NULL, '2026-03-24 05:45:34', '2026-03-24 05:45:34'),
(1104, 'App\\Models\\User', 150, 'auth-token', 'de5e0d331b381585163494949cd92c4c48901b11252d017d25efc7c7263acf53', '[\"*\"]', NULL, NULL, '2026-03-24 05:46:10', '2026-03-24 05:46:10'),
(1105, 'App\\Models\\User', 152, 'auth-token', '4a1e31817e087c5efdbfca56d8daa65c2f208488518d5bf28a9e52a18ff6e7aa', '[\"*\"]', NULL, NULL, '2026-03-24 05:46:27', '2026-03-24 05:46:27'),
(1106, 'App\\Models\\User', 150, 'auth-token', 'c6b5c94f2c1a9a48d8dd21f15ec4346a17dfc084bb2edce8959369787d40b3bf', '[\"*\"]', NULL, NULL, '2026-03-24 05:47:32', '2026-03-24 05:47:32'),
(1107, 'App\\Models\\User', 152, 'auth-token', '1c165fc3ba78bc404474a976bed362fb3600bce61675d5af6c64675d7e466d64', '[\"*\"]', NULL, NULL, '2026-03-24 05:47:57', '2026-03-24 05:47:57'),
(1108, 'App\\Models\\User', 150, 'auth-token', '746ed4cf72f3428e752a67bd5a3f6dd92b980185224695ce90db7a7050b9bc81', '[\"*\"]', NULL, NULL, '2026-03-24 05:48:36', '2026-03-24 05:48:36'),
(1109, 'App\\Models\\User', 152, 'auth-token', 'd1f1848c925d2633ec5c5a018266d18228cb1f5d85d793def0df0d4d09813723', '[\"*\"]', NULL, NULL, '2026-03-24 06:03:02', '2026-03-24 06:03:02'),
(1110, 'App\\Models\\User', 157, 'auth-token', 'd6b143a32af8231faed72b343493365b18d7db9495e20f4a37472cd7c3117a0d', '[\"*\"]', NULL, NULL, '2026-03-24 06:24:18', '2026-03-24 06:24:18'),
(1111, 'App\\Models\\User', 151, 'auth-token', '13564897ebe70af0f57df1d6633c544fdb03c66f8be80876f901b9aff8df2713', '[\"*\"]', NULL, NULL, '2026-03-24 06:24:49', '2026-03-24 06:24:49'),
(1112, 'App\\Models\\User', 150, 'auth-token', 'd34eec453dec0bd5d4da24ae3d818496a577c13c5763f5faf1ad9fd90031f246', '[\"*\"]', NULL, NULL, '2026-03-24 06:25:10', '2026-03-24 06:25:10'),
(1113, 'App\\Models\\User', 152, 'auth-token', '807acf857cf79618702551954f51c83399f0bb1247cb8816523517614d267d2c', '[\"*\"]', NULL, NULL, '2026-03-24 06:25:27', '2026-03-24 06:25:27'),
(1114, 'App\\Models\\User', 150, 'auth-token', 'cf448b83ba354905c962fa5574b3d4603add952e3d04f9a8f1e4289a52182e3e', '[\"*\"]', NULL, NULL, '2026-03-24 06:26:22', '2026-03-24 06:26:22'),
(1115, 'App\\Models\\User', 149, 'auth-token', 'e830969b10f3d3a1b9785d5a990b30b95a7c757d92c4efa0f3454f67be42bed1', '[\"*\"]', NULL, NULL, '2026-03-24 06:26:55', '2026-03-24 06:26:55'),
(1116, 'App\\Models\\User', 150, 'auth-token', '5c0bc92d557a7ebd7c92715669ce013835f4d9e477e73f25e203c789b3cecdb9', '[\"*\"]', NULL, NULL, '2026-03-24 06:27:20', '2026-03-24 06:27:20'),
(1117, 'App\\Models\\User', 152, 'auth-token', '29b6c250801d36a05b8d4e40061f94b1e37ea61dcdcd5d3e4890633b0487c743', '[\"*\"]', NULL, NULL, '2026-03-24 06:33:29', '2026-03-24 06:33:29'),
(1118, 'App\\Models\\User', 157, 'auth-token', 'b24da2ec3bdfca83c7228fe434f27e4878fec05a8cb8bfa2cb7cdb0e5649065e', '[\"*\"]', NULL, NULL, '2026-03-24 07:07:19', '2026-03-24 07:07:19'),
(1119, 'App\\Models\\User', 157, 'auth-token', '33e589983774edd8841966fa95b2fd649ef20e87e9166c649e7fa02e62282157', '[\"*\"]', NULL, NULL, '2026-03-24 07:14:07', '2026-03-24 07:14:07'),
(1120, 'App\\Models\\User', 151, 'auth-token', '91b8791df4708e7c1978f8a6e872dbd2615bc3bb8b5fdf293c08effcf272c079', '[\"*\"]', NULL, NULL, '2026-03-24 07:15:57', '2026-03-24 07:15:57'),
(1121, 'App\\Models\\User', 157, 'auth-token', 'dd22168a9a244d47d0386836996b681c49ed46df1fac257d348aa585bde42654', '[\"*\"]', NULL, NULL, '2026-03-24 07:17:11', '2026-03-24 07:17:11'),
(1122, 'App\\Models\\User', 151, 'auth-token', 'c1b966c810b21e04383a623ae8be5e005845bbf7a0e1cd8c15876efc94ea85ef', '[\"*\"]', NULL, NULL, '2026-03-24 07:17:50', '2026-03-24 07:17:50'),
(1123, 'App\\Models\\User', 150, 'auth-token', 'e6de871b15c0ce3cad2f9bfcfddfdec06dd922e4b33f69ef4507fabc9a0ed5dd', '[\"*\"]', NULL, NULL, '2026-03-24 07:18:18', '2026-03-24 07:18:18'),
(1124, 'App\\Models\\User', 157, 'auth-token', '497520bfb94a011e5cc2f99fe332df5d7321f392e6273814083bcd0b39a267cf', '[\"*\"]', NULL, NULL, '2026-03-24 07:18:43', '2026-03-24 07:18:43'),
(1125, 'App\\Models\\User', 152, 'auth-token', '722e58ddee2f2527a03517c6057bf4b9491313222474bbfe0e5ad4127ee7895f', '[\"*\"]', NULL, NULL, '2026-03-24 07:19:11', '2026-03-24 07:19:11'),
(1126, 'App\\Models\\User', 150, 'auth-token', '8a05fc8444ce396c828f2b05d2736ef4e78a1708e0eefe7d549f8275bac24532', '[\"*\"]', NULL, NULL, '2026-03-24 07:19:44', '2026-03-24 07:19:44'),
(1127, 'App\\Models\\User', 152, 'auth-token', '9a6ef877ce9f93e4c81b6c4bef13d0eab13370d02265a890f3ff03e0e37eb624', '[\"*\"]', NULL, NULL, '2026-03-24 07:20:29', '2026-03-24 07:20:29'),
(1128, 'App\\Models\\User', 157, 'auth-token', '745c729f43eae0aed10a6d39a4f4ea1f12e66e6c821b8d339084e09104d3fccc', '[\"*\"]', NULL, NULL, '2026-03-24 07:29:02', '2026-03-24 07:29:02'),
(1129, 'App\\Models\\User', 151, 'auth-token', '09efb7a57b09ea134df9a0f927f553a3cbb678d2c7385e4097d43504cca09089', '[\"*\"]', NULL, NULL, '2026-03-24 07:29:33', '2026-03-24 07:29:33'),
(1130, 'App\\Models\\User', 150, 'auth-token', '3c71711d730a680da3b8e1b9978f0e745757d74d8402cc93ff6bb061ee7afef2', '[\"*\"]', NULL, NULL, '2026-03-24 07:29:50', '2026-03-24 07:29:50'),
(1131, 'App\\Models\\User', 152, 'auth-token', '110854d90631b3845fb2d8a3de2280d92373244d96c31bd161bd7b13805b0a58', '[\"*\"]', NULL, NULL, '2026-03-24 07:30:08', '2026-03-24 07:30:08'),
(1132, 'App\\Models\\User', 150, 'auth-token', '2370b160955d0b79fe42f80571172c0fed0cb8496cf4513cb3557fc4f0b15863', '[\"*\"]', NULL, NULL, '2026-03-24 07:30:39', '2026-03-24 07:30:39'),
(1133, 'App\\Models\\User', 149, 'auth-token', 'b0bfe98637dc328c8a7be96ee0bbeff4b8cbe819daa934623dda6f65d8a8c5bc', '[\"*\"]', NULL, NULL, '2026-03-24 07:31:02', '2026-03-24 07:31:02'),
(1134, 'App\\Models\\User', 150, 'auth-token', 'e5917b49c8b926772a8821f8998f4acd3d7a6c235ffca3456f57931f01d98c45', '[\"*\"]', NULL, NULL, '2026-03-24 07:31:35', '2026-03-24 07:31:35'),
(1135, 'App\\Models\\User', 152, 'auth-token', '1727025c2426c31bf7047e09c4c77b35cdc4e45c77eb3c343af09fb8bb89f17c', '[\"*\"]', NULL, NULL, '2026-03-24 07:31:52', '2026-03-24 07:31:52'),
(1136, 'App\\Models\\User', 151, 'auth-token', 'cf6a3b25f7a8fdceabc4301b3abc70388cb1315ff9fa6dd4c0c9c97cfec0fc08', '[\"*\"]', NULL, NULL, '2026-03-24 07:34:51', '2026-03-24 07:34:51'),
(1137, 'App\\Models\\User', 157, 'auth-token', 'a6d8bd7a3778b44cabe1bff5531e81de5c4d7a3e6c0aa5634a99e2a9b4650bde', '[\"*\"]', NULL, NULL, '2026-03-24 07:35:43', '2026-03-24 07:35:43'),
(1138, 'App\\Models\\User', 151, 'auth-token', 'd4f28f49cce81137857097580eac0361a6e4d361c3d5ce4749f7db7287037bea', '[\"*\"]', NULL, NULL, '2026-03-24 07:35:55', '2026-03-24 07:35:55'),
(1139, 'App\\Models\\User', 150, 'auth-token', '6644297467ce740c12cd9a825017e75396416fff3a1679dad2b0fa25b444b85c', '[\"*\"]', NULL, NULL, '2026-03-24 07:36:12', '2026-03-24 07:36:12'),
(1140, 'App\\Models\\User', 151, 'auth-token', 'e18760cbd57857e338cac9ffa3f5f6ae644f8a950d7901d9ce05043be43c77b0', '[\"*\"]', NULL, NULL, '2026-03-24 07:36:27', '2026-03-24 07:36:27'),
(1141, 'App\\Models\\User', 157, 'auth-token', '9276d009d44047c74f8ad0f4612d3f095f76310b81ae4a40d264d85fbc84948f', '[\"*\"]', NULL, NULL, '2026-03-24 07:37:01', '2026-03-24 07:37:01'),
(1142, 'App\\Models\\User', 151, 'auth-token', '4c4fb663848aff859977543612573b5b032430ed7d17080b27f1bf466571e7a7', '[\"*\"]', NULL, NULL, '2026-03-24 07:37:53', '2026-03-24 07:37:53'),
(1143, 'App\\Models\\User', 150, 'auth-token', 'fe4dd9835eb0ee575989a938a386b51d9e202afad29f2e8c8602a34de6267a13', '[\"*\"]', NULL, NULL, '2026-03-24 07:38:10', '2026-03-24 07:38:10'),
(1144, 'App\\Models\\User', 152, 'auth-token', '47da7f36cec738ab2a0e1d8af1aae73f11489878ac472ba68e5fb7bb48ca1ec1', '[\"*\"]', NULL, NULL, '2026-03-24 07:38:26', '2026-03-24 07:38:26'),
(1145, 'App\\Models\\User', 150, 'auth-token', 'b73166b9951503de00db22e78b8a53ccc0204f2803cfd9f3714cb4e9723dde91', '[\"*\"]', NULL, NULL, '2026-03-24 07:38:53', '2026-03-24 07:38:53'),
(1146, 'App\\Models\\User', 149, 'auth-token', '443c5b80e85d6c2938a5acdd3d754f7dabc8d8211d2713671f05ba7d06717208', '[\"*\"]', NULL, NULL, '2026-03-24 07:39:09', '2026-03-24 07:39:09'),
(1147, 'App\\Models\\User', 150, 'auth-token', 'b51e1054b7bc96ba26598615df45b0f0aaa3f5905939f09fb06040bd3eb837da', '[\"*\"]', NULL, NULL, '2026-03-24 07:39:53', '2026-03-24 07:39:53'),
(1148, 'App\\Models\\User', 152, 'auth-token', 'c097b91e15750d479aa93cb0938779a8f8d1dfbe01428f6bdadb7e36e32415ef', '[\"*\"]', NULL, NULL, '2026-03-24 07:40:43', '2026-03-24 07:40:43'),
(1149, 'App\\Models\\User', 150, 'auth-token', 'ffa1b5539f7f725da046b6e36fd541305ba98c8155e55cd56ca6bf5b04572f36', '[\"*\"]', NULL, NULL, '2026-03-24 07:42:53', '2026-03-24 07:42:53'),
(1150, 'App\\Models\\User', 151, 'auth-token', '77de65bc2791ce4ef6e8d71103909a1eb1f23978bab00edce2237a32b438fb47', '[\"*\"]', NULL, NULL, '2026-03-24 07:43:16', '2026-03-24 07:43:16'),
(1151, 'App\\Models\\User', 157, 'auth-token', '580283782d7b03061ed47272f86eeb9592978521ee0de510f11e0eaa3de33f67', '[\"*\"]', NULL, NULL, '2026-03-24 07:45:27', '2026-03-24 07:45:27'),
(1152, 'App\\Models\\User', 157, 'auth-token', 'e5d0918767744fd33809f31f5fba9eee06d5d7575a90acb9f2c0c3eeaf1f8ed2', '[\"*\"]', NULL, NULL, '2026-03-24 07:53:09', '2026-03-24 07:53:09'),
(1153, 'App\\Models\\User', 151, 'auth-token', '80d75b320b1d7110c79a1f81bfd2c1057cb24d7792096b00ee362eeffd9cefef', '[\"*\"]', NULL, NULL, '2026-03-24 07:56:01', '2026-03-24 07:56:01'),
(1154, 'App\\Models\\User', 152, 'auth-token', '999c1c8039eaeb89070950a9c55d8154fb9a22c149ca83fa2d55237482d9fee3', '[\"*\"]', NULL, NULL, '2026-03-24 07:56:21', '2026-03-24 07:56:21'),
(1155, 'App\\Models\\User', 150, 'auth-token', 'ec37e55a396b1cd9c9fb46b19ed4522c24cf296772f6a131d473ccb67286af18', '[\"*\"]', NULL, NULL, '2026-03-24 07:56:36', '2026-03-24 07:56:36'),
(1156, 'App\\Models\\User', 152, 'auth-token', '449f3d8769d95caf6fa0b9cf0b2d73d8381da8969c18489a7acdcd7eec7d80c3', '[\"*\"]', NULL, NULL, '2026-03-24 07:56:58', '2026-03-24 07:56:58'),
(1157, 'App\\Models\\User', 150, 'auth-token', 'acf9dc211814edf531d936b3d26ba78608defe5f38c46a652be91d35aca7db06', '[\"*\"]', NULL, NULL, '2026-03-24 07:57:19', '2026-03-24 07:57:19'),
(1158, 'App\\Models\\User', 149, 'auth-token', 'd937995e8ed084efe0fad9c40e385d19f86d41b22b6879fb215bee3eb253665f', '[\"*\"]', NULL, NULL, '2026-03-24 07:57:37', '2026-03-24 07:57:37'),
(1159, 'App\\Models\\User', 150, 'auth-token', 'b0baa190173b06c0543cf40daaced882e7136090ad6f82e96cee84add603da88', '[\"*\"]', NULL, NULL, '2026-03-24 07:58:03', '2026-03-24 07:58:03'),
(1160, 'App\\Models\\User', 152, 'auth-token', 'e4b38c9ea817d8cc24a46b2bd0a1596c98a542d32aaee91d0365cdbc15b5c49a', '[\"*\"]', NULL, NULL, '2026-03-24 07:58:23', '2026-03-24 07:58:23'),
(1161, 'App\\Models\\User', 150, 'auth-token', 'd8b07f2d362b7dbbac85c0fa24fc8e0f56a926709348b8fcf05ea725ace31d41', '[\"*\"]', NULL, NULL, '2026-03-24 07:58:39', '2026-03-24 07:58:39'),
(1162, 'App\\Models\\User', 151, 'auth-token', 'f814c20deab20a832aaa6ba860b106f85c66ec727fb00b0c83bfac8176debc80', '[\"*\"]', NULL, NULL, '2026-03-24 08:07:18', '2026-03-24 08:07:18'),
(1163, 'App\\Models\\User', 150, 'auth-token', 'adee6342e5cdcf6b047ebac7ec0ba75fd05c2528e91e26388b8de417c6442006', '[\"*\"]', NULL, NULL, '2026-03-24 08:11:38', '2026-03-24 08:11:38'),
(1164, 'App\\Models\\User', 153, 'auth-token', '3aef83f3720ff6cec61e9fc53edde1aedcd6d0b70b0e309374f3bb71081a81b4', '[\"*\"]', NULL, NULL, '2026-03-24 08:35:21', '2026-03-24 08:35:21'),
(1165, 'App\\Models\\User', 153, 'auth-token', '63296b8363f4db1e53a94adb10a1ed7b70e3af45377ca6b930516acac7e197eb', '[\"*\"]', NULL, NULL, '2026-03-24 08:43:39', '2026-03-24 08:43:39'),
(1166, 'App\\Models\\User', 152, 'auth-token', 'e0cd7580a20775c3bd8fc268086960fa1499de6412831901aeef3e1550003990', '[\"*\"]', NULL, NULL, '2026-03-24 08:55:22', '2026-03-24 08:55:22'),
(1167, 'App\\Models\\User', 151, 'auth-token', '71845dc28704ac516e0fdd8a23109cfbe155513da92d88ae3467d9e178c27378', '[\"*\"]', NULL, NULL, '2026-03-24 08:55:36', '2026-03-24 08:55:36'),
(1168, 'App\\Models\\User', 150, 'auth-token', 'dd0f4d9adb651daa4b3217643f571bcc77ce603deff9d36654c43744ffa7c784', '[\"*\"]', NULL, NULL, '2026-03-24 08:55:53', '2026-03-24 08:55:53'),
(1169, 'App\\Models\\User', 158, 'auth-token', '613d0968931fcec84c8ed8eb337e286b91a270a7a5e40462c09b71d68a00c217', '[\"*\"]', NULL, NULL, '2026-03-24 08:58:23', '2026-03-24 08:58:23'),
(1170, 'App\\Models\\User', 158, 'auth-token', '6f8cc7519bf57d2f07e9fb1fb606a7ad781bd8c1a93da81460c152adeb65393f', '[\"*\"]', NULL, NULL, '2026-03-24 08:58:41', '2026-03-24 08:58:41'),
(1171, 'App\\Models\\User', 151, 'auth-token', '739e8109150e8a26b6c82ab297fda3e337843a4f0252b64a42c8601a2c32c865', '[\"*\"]', NULL, NULL, '2026-03-24 08:59:40', '2026-03-24 08:59:40'),
(1172, 'App\\Models\\User', 150, 'auth-token', '29de466a76290b830de4d1e8eb05b15b50ed5248f0e56a663ae48696b31677ed', '[\"*\"]', NULL, NULL, '2026-03-24 09:00:02', '2026-03-24 09:00:02'),
(1173, 'App\\Models\\User', 149, 'auth-token', 'f4d4f71d7c119104dd0ca9ee40da826fae10eda58b85fcb300668a38b3297a1d', '[\"*\"]', NULL, NULL, '2026-03-24 09:00:23', '2026-03-24 09:00:23'),
(1174, 'App\\Models\\User', 149, 'auth-token', '77c21e7f8581827a54191ef513d7c001737107b942492bc6b528703bb2f335ad', '[\"*\"]', NULL, NULL, '2026-03-24 09:00:49', '2026-03-24 09:00:49'),
(1175, 'App\\Models\\User', 150, 'auth-token', '2916f45a9ea07e2c36e00b75f80651cf98496434b2c9e74f7e5ff26200269c92', '[\"*\"]', NULL, NULL, '2026-03-24 09:02:00', '2026-03-24 09:02:00'),
(1176, 'App\\Models\\User', 149, 'auth-token', '9039e50ec794d356f6929c75850e5cc35263bd2eba9b33db241b123c6451ed8c', '[\"*\"]', NULL, NULL, '2026-03-24 09:02:17', '2026-03-24 09:02:17'),
(1177, 'App\\Models\\User', 150, 'auth-token', '6a2a71f499f3b82585e047d9c3407c9d64a3ebf26f994c874bdcfc6d532b9873', '[\"*\"]', NULL, NULL, '2026-03-24 09:02:49', '2026-03-24 09:02:49'),
(1178, 'App\\Models\\User', 152, 'auth-token', '481a92b303882debda5e32bfc1fafd4066ea0c54b685884908091de5eb79312d', '[\"*\"]', NULL, NULL, '2026-03-24 09:05:09', '2026-03-24 09:05:09'),
(1179, 'App\\Models\\User', 150, 'auth-token', 'd8124381eb3ad627ce905cc346d894afc551ec4dccbbd0256385dafaa5136d90', '[\"*\"]', NULL, NULL, '2026-03-24 09:06:31', '2026-03-24 09:06:31'),
(1180, 'App\\Models\\User', 154, 'auth-token', '87e6d2565b65f74d1402e799ee90c5a10333fca93dcfbe64b8adae77c1446fbb', '[\"*\"]', NULL, NULL, '2026-03-24 09:07:32', '2026-03-24 09:07:32'),
(1181, 'App\\Models\\User', 157, 'auth-token', '90f293288aaea4f1ccc8d0d8fa96fdeb74d0ba8e808d3cbfbfe44cb782e1623d', '[\"*\"]', NULL, NULL, '2026-03-24 09:08:48', '2026-03-24 09:08:48'),
(1182, 'App\\Models\\User', 151, 'auth-token', '2c9ef913342392c5100278c587c9386cc465bdf56ed5bee3b8b506918e00d633', '[\"*\"]', NULL, NULL, '2026-03-24 09:10:37', '2026-03-24 09:10:37'),
(1183, 'App\\Models\\User', 150, 'auth-token', '07865c2bf723762f75e40413659d44beb4c7b530964a1794f7a538177c1922d5', '[\"*\"]', NULL, NULL, '2026-03-24 09:11:00', '2026-03-24 09:11:00'),
(1184, 'App\\Models\\User', 152, 'auth-token', '63b2ab991a2c73ca74a10d338c73fce02ce3ec1f60de1b4eb903f947cbfb0904', '[\"*\"]', NULL, NULL, '2026-03-24 09:12:52', '2026-03-24 09:12:52'),
(1185, 'App\\Models\\User', 150, 'auth-token', '047d27a4af2fb4e11aa37cfc600a6e44130bc71a5bc691b39f40436949c366ea', '[\"*\"]', NULL, NULL, '2026-03-24 09:13:29', '2026-03-24 09:13:29'),
(1186, 'App\\Models\\User', 149, 'auth-token', '819c19b34b2bff2aaa4bceb946c61821566fec07ebf9a022ab07549593e90a82', '[\"*\"]', NULL, NULL, '2026-03-24 09:13:50', '2026-03-24 09:13:50'),
(1187, 'App\\Models\\User', 150, 'auth-token', 'e7a04d29d2100ffa025916c4f5dfeffc63e8b7834e4b5514ef8ec8d807443f9b', '[\"*\"]', NULL, NULL, '2026-03-24 09:14:24', '2026-03-24 09:14:24'),
(1188, 'App\\Models\\User', 152, 'auth-token', '0f06e59b92104a356ce1ed1ff2101632204e5eb370f2272b0cfd380ebb90a602', '[\"*\"]', NULL, NULL, '2026-03-24 09:15:03', '2026-03-24 09:15:03'),
(1189, 'App\\Models\\User', 150, 'auth-token', '128cfb92ebb79c951e456add5e8c28663b040e8e37f0dfc809fea01d40bd6815', '[\"*\"]', NULL, NULL, '2026-03-24 09:15:43', '2026-03-24 09:15:43'),
(1190, 'App\\Models\\User', 151, 'auth-token', '001dc372bd3624f36cb501e585f717fa4935b8803b452384be81abae53b05904', '[\"*\"]', NULL, NULL, '2026-03-24 09:16:20', '2026-03-24 09:16:20'),
(1191, 'App\\Models\\User', 153, 'auth-token', '8bd3e10c29edf6ddac040defe02e5f7854c6574d417a63ced8ecc98d6b4866d5', '[\"*\"]', NULL, NULL, '2026-03-24 09:16:51', '2026-03-24 09:16:51'),
(1192, 'App\\Models\\User', 148, 'auth-token', '12382a70334a06f616b47c8f28b482aaadb1107c70b12838b00c5c1142d6690c', '[\"*\"]', NULL, NULL, '2026-03-24 09:19:47', '2026-03-24 09:19:47'),
(1193, 'App\\Models\\User', 31, 'auth-token', 'eca212b0328823efe7f0687d8dcab970e0445c66b8729e587819aac6681326f9', '[\"*\"]', NULL, NULL, '2026-03-24 09:21:32', '2026-03-24 09:21:32'),
(1194, 'App\\Models\\User', 153, 'auth-token', 'd0ea2d0fbb3c395449cb84d05032187c4f236389f5a32fe72ad903671c1e5841', '[\"*\"]', NULL, NULL, '2026-03-25 06:34:09', '2026-03-25 06:34:09'),
(1195, 'App\\Models\\User', 154, 'auth-token', 'f36cd2eca672e4e381ffc2677da4206a48526a8cbea2f35ae27681ca5b424cc7', '[\"*\"]', NULL, NULL, '2026-03-25 06:34:35', '2026-03-25 06:34:35'),
(1196, 'App\\Models\\User', 147, 'auth-token', '0cecc38af248026a33e3b32fb6ac0698e5a583618b2ef7f8dd7b824f4ff69092', '[\"*\"]', NULL, NULL, '2026-03-25 06:35:11', '2026-03-25 06:35:11'),
(1197, 'App\\Models\\User', 31, 'auth-token', '7d1d016b728784d896fad2a27e8435230e602cc65d8912e478527108b29c4073', '[\"*\"]', NULL, NULL, '2026-03-25 06:35:29', '2026-03-25 06:35:29'),
(1198, 'App\\Models\\User', 159, 'auth-token', 'e8dbc316ecdd53acb5e584769f197eaac462db047918cfe62e2d67f983f0e93c', '[\"*\"]', NULL, NULL, '2026-03-25 07:06:47', '2026-03-25 07:06:47'),
(1199, 'App\\Models\\User', 159, 'auth-token', '5d9fc57191a1a66ed711cdf95a5331298734576dab21977ba9b69f297f48857b', '[\"*\"]', NULL, NULL, '2026-03-25 07:07:06', '2026-03-25 07:07:06'),
(1200, 'App\\Models\\User', 159, 'auth-token', 'f645a237b771581a42001785665b15f49aa053b4cc2477ef5524bd214052e8ce', '[\"*\"]', NULL, NULL, '2026-03-25 07:08:01', '2026-03-25 07:08:01'),
(1201, 'App\\Models\\User', 160, 'auth-token', '6aec93daa5086e293ecd2746f7a753b776a2cf5790dd408133ed2fd94ff312c9', '[\"*\"]', NULL, NULL, '2026-03-25 07:16:24', '2026-03-25 07:16:24'),
(1202, 'App\\Models\\User', 160, 'auth-token', '7f89efdc67e279b82522d6058b7e0384aacb168b8e904b587e6794daf27d3a29', '[\"*\"]', NULL, NULL, '2026-03-25 07:16:49', '2026-03-25 07:16:49'),
(1203, 'App\\Models\\User', 160, 'auth-token', '4636165b0b2804971ebc702d657992d99b4ec9e4c9198c1cd334d9ebe4fdc701', '[\"*\"]', NULL, NULL, '2026-03-25 07:17:29', '2026-03-25 07:17:29'),
(1204, 'App\\Models\\User', 161, 'auth-token', 'f459d2a077eeb9c6090b3d6ba9551f0701bc1c06d14e2ad0adff1d55f44f2fd4', '[\"*\"]', NULL, NULL, '2026-03-25 07:18:39', '2026-03-25 07:18:39'),
(1205, 'App\\Models\\User', 149, 'auth-token', '1003bcd819e422b19f6753cd98c496b7941e228b4a4656b4757d006b51734bfd', '[\"*\"]', NULL, NULL, '2026-03-25 07:19:24', '2026-03-25 07:19:24'),
(1206, 'App\\Models\\User', 161, 'auth-token', '035f5da8590d6b017a6e60615790734abd27d2677a2411b200dd80d93775ab2c', '[\"*\"]', NULL, NULL, '2026-03-25 07:19:40', '2026-03-25 07:19:40'),
(1207, 'App\\Models\\User', 162, 'auth-token', 'cae08c04dbb3a9a9825a536782aef3b7789c041b7a61b20f0137d36726d65b5e', '[\"*\"]', NULL, NULL, '2026-03-25 07:20:38', '2026-03-25 07:20:38'),
(1208, 'App\\Models\\User', 162, 'auth-token', '0adcd2c444e899b0b33a9753001efd2522627b3e5031ff2b9a119188537acb5b', '[\"*\"]', NULL, NULL, '2026-03-25 07:21:32', '2026-03-25 07:21:32'),
(1209, 'App\\Models\\User', 162, 'auth-token', '1b025f71eceec374cfabf2fb56dce63dbb73ed08292def132c0dd7f344812faf', '[\"*\"]', NULL, NULL, '2026-03-25 07:22:19', '2026-03-25 07:22:19'),
(1210, 'App\\Models\\User', 162, 'auth-token', 'f3b2bcc92061a180119a19b6207e0ff9ed42328aa0265d4e8ba2e2184edb70a7', '[\"*\"]', NULL, NULL, '2026-03-26 04:40:17', '2026-03-26 04:40:17'),
(1211, 'App\\Models\\User', 162, 'auth-token', '95c627c395a75112754b150172050bbd6732e5e2a9baf8bdfaedc714b274ff64', '[\"*\"]', NULL, NULL, '2026-03-26 04:40:31', '2026-03-26 04:40:31'),
(1212, 'App\\Models\\User', 162, 'auth-token', '70e9d7176c09daba9ab8fe7572bca8e9c75563eedb994b779a953b87b1f8c6d7', '[\"*\"]', NULL, NULL, '2026-03-26 04:40:54', '2026-03-26 04:40:54'),
(1213, 'App\\Models\\User', 28, 'auth-token', '3184e75da7bfa608dc834eff5c1f69011e1a55b7f0e363d3b7e2e320a654fef6', '[\"*\"]', NULL, NULL, '2026-03-26 04:47:42', '2026-03-26 04:47:42'),
(1214, 'App\\Models\\User', 151, 'auth-token', '02090bf96f08e89dcdd6dd6b95cce6b0f93478ee8b9089e3b4e9930db93c148b', '[\"*\"]', NULL, NULL, '2026-03-26 04:48:03', '2026-03-26 04:48:03'),
(1215, 'App\\Models\\User', 162, 'auth-token', '13c2ac87f267b331138dfd624ba9a4cd23794aec0386c678a716df1f0c2b79b2', '[\"*\"]', NULL, NULL, '2026-03-26 05:34:02', '2026-03-26 05:34:02'),
(1216, 'App\\Models\\User', 151, 'auth-token', '7b2ec9262600221e9a7ca358162d212dc7c976e3ac6a8e394381429579dd8cdd', '[\"*\"]', NULL, NULL, '2026-03-26 05:34:18', '2026-03-26 05:34:18'),
(1217, 'App\\Models\\User', 162, 'auth-token', '0978aa3aa288fa517b982a9c9d2ba7caef29d0ab6afccb35a47e80ca960d893a', '[\"*\"]', NULL, NULL, '2026-03-26 05:37:53', '2026-03-26 05:37:53'),
(1218, 'App\\Models\\User', 159, 'auth-token', 'b620cd187876a575326785cf967a66ebc0ceb9269252b55b21467399cd4ec65d', '[\"*\"]', NULL, NULL, '2026-03-26 06:43:36', '2026-03-26 06:43:36'),
(1219, 'App\\Models\\User', 162, 'auth-token', '461c6ab94b79bf8b1d6f7b5e4980eda8f25283f0bc42b3d94704c6f5bec2805e', '[\"*\"]', NULL, NULL, '2026-03-26 07:01:40', '2026-03-26 07:01:40'),
(1220, 'App\\Models\\User', 151, 'auth-token', 'dcf6cad68598a18d7c56c0bcbc2ff67541361fdf4883086d6d62e32c0da5ceb3', '[\"*\"]', NULL, NULL, '2026-03-26 07:45:39', '2026-03-26 07:45:39'),
(1221, 'App\\Models\\User', 150, 'auth-token', 'cd017af77f279902aeee43261dbc5dd7b2f1f091813f7895aa78ef7273e77c74', '[\"*\"]', NULL, NULL, '2026-03-26 07:49:27', '2026-03-26 07:49:27'),
(1222, 'App\\Models\\User', 149, 'auth-token', '8481720c6a50316a7d2cc0dc5b2e67f659f035d096e5b2e568932e15739f6fb1', '[\"*\"]', NULL, NULL, '2026-03-26 07:50:12', '2026-03-26 07:50:12'),
(1223, 'App\\Models\\User', 152, 'auth-token', '91d3e44c83b7715633dd0192b1201c43d86769718ef555c741cec173011c40e3', '[\"*\"]', NULL, NULL, '2026-03-26 07:50:37', '2026-03-26 07:50:37'),
(1224, 'App\\Models\\User', 152, 'auth-token', '7fc8b1206062ce83c25622aced799643b753406732ac44ab01ad2c59ea1ee29e', '[\"*\"]', NULL, NULL, '2026-03-26 07:50:51', '2026-03-26 07:50:51'),
(1225, 'App\\Models\\User', 150, 'auth-token', '988452e4aed0dd556ca69dde989f60075a735dcdff691f3a7c010f1157e0c643', '[\"*\"]', NULL, NULL, '2026-03-26 07:51:07', '2026-03-26 07:51:07'),
(1226, 'App\\Models\\User', 162, 'auth-token', '53e3eb8a9ba220598f27a75c7c9383f7f8f96e7b8839c47352b13d677632d5d0', '[\"*\"]', NULL, NULL, '2026-03-26 07:59:00', '2026-03-26 07:59:00'),
(1227, 'App\\Models\\User', 151, 'auth-token', '5e5fd0049344248b143bc5781ebdea2422d89e8a85af05505a9c78cdebe5b09a', '[\"*\"]', NULL, NULL, '2026-03-26 07:59:14', '2026-03-26 07:59:14'),
(1228, 'App\\Models\\User', 150, 'auth-token', '743e7936fabf1508734b1d5e7f50cdf09fce442df6def60713e96d413b0f92f3', '[\"*\"]', NULL, NULL, '2026-03-26 07:59:30', '2026-03-26 07:59:30'),
(1229, 'App\\Models\\User', 149, 'auth-token', 'ee39b5839f9ea5395f2b8dd094516eaf5b2300791ca92d6cbc27f8a5f0886388', '[\"*\"]', NULL, NULL, '2026-03-26 08:00:09', '2026-03-26 08:00:09'),
(1230, 'App\\Models\\User', 150, 'auth-token', 'd07ceb3a2911e5db54ecc5dc0ef0dad86b99131f5727b35d3f4feb823f816072', '[\"*\"]', NULL, NULL, '2026-03-26 08:00:33', '2026-03-26 08:00:33'),
(1231, 'App\\Models\\User', 152, 'auth-token', '9f228236759d4e8ecd99f33ebb7fcb5920a65e9bffa7d916ec1c700b8574abdd', '[\"*\"]', NULL, NULL, '2026-03-26 08:08:15', '2026-03-26 08:08:15'),
(1232, 'App\\Models\\User', 150, 'auth-token', 'a6e1e9f2214c547d49491faaf60bdc080360a7e60223e4bdf5028fb991e8bed8', '[\"*\"]', NULL, NULL, '2026-03-26 08:08:47', '2026-03-26 08:08:47'),
(1233, 'App\\Models\\User', 149, 'auth-token', '7d3041e5c469c0b38bd072591c4260345441192a28801768e2f239de834cad28', '[\"*\"]', NULL, NULL, '2026-03-26 08:10:33', '2026-03-26 08:10:33'),
(1234, 'App\\Models\\User', 150, 'auth-token', 'a7c49597b8996395d0f8c992df9bfac3849756573c8dd9f5883e71deb43975d5', '[\"*\"]', NULL, NULL, '2026-03-26 08:11:07', '2026-03-26 08:11:07'),
(1235, 'App\\Models\\User', 150, 'auth-token', 'c8942597f2581c054ae1e5f05ee2be640974962fdfc6fb7023e80efb6ceecd21', '[\"*\"]', NULL, NULL, '2026-03-26 08:16:39', '2026-03-26 08:16:39'),
(1236, 'App\\Models\\User', 152, 'auth-token', 'c9b8b6cc4ff422b2a23e4d6555c5010ca39fd4b5b516dc2e124cbcfab9e67dae', '[\"*\"]', NULL, NULL, '2026-03-26 08:19:35', '2026-03-26 08:19:35'),
(1237, 'App\\Models\\User', 150, 'auth-token', 'a37ecc1cea98790172d6d01f1224360e81313eda5f0ae296f4eedac116b2e423', '[\"*\"]', NULL, NULL, '2026-03-26 08:19:57', '2026-03-26 08:19:57'),
(1238, 'App\\Models\\User', 162, 'auth-token', '41440d9e891d75a8508d1a234a51fad7c03f4c200dd0bb5f6d8979d41ba52969', '[\"*\"]', NULL, NULL, '2026-03-26 08:21:39', '2026-03-26 08:21:39'),
(1239, 'App\\Models\\User', 151, 'auth-token', '6de647d6f6e284d5cc72ec3b26694edb24881adc4e4a46a65215bff93f7e00b0', '[\"*\"]', NULL, NULL, '2026-03-26 08:21:54', '2026-03-26 08:21:54'),
(1240, 'App\\Models\\User', 150, 'auth-token', 'a5718f7e4799c6183b90df90f5cf98fd14b979d760deb50ce8868cdf241141f5', '[\"*\"]', NULL, NULL, '2026-03-26 08:22:21', '2026-03-26 08:22:21'),
(1241, 'App\\Models\\User', 149, 'auth-token', 'ee491eb872d4bb6dadea3250b490ce6cc7270f858be5230e09cef3416505475a', '[\"*\"]', NULL, NULL, '2026-03-26 08:22:56', '2026-03-26 08:22:56'),
(1242, 'App\\Models\\User', 150, 'auth-token', '84da0ae15e03e305f06d46b1515dd3ccc69c5f5cca39c758acb310ab8f286293', '[\"*\"]', NULL, NULL, '2026-03-26 08:23:32', '2026-03-26 08:23:32'),
(1243, 'App\\Models\\User', 152, 'auth-token', 'a23a0e9e9562a7c7c5deeb8f009034b7aa7e987600e43118eaa9ff7dc74a9a30', '[\"*\"]', NULL, NULL, '2026-03-26 08:25:27', '2026-03-26 08:25:27'),
(1244, 'App\\Models\\User', 150, 'auth-token', 'd1a9237776537953297b8b66aada3225999c69ae883e6c4eb2e4c7ece5befb8d', '[\"*\"]', NULL, NULL, '2026-03-26 08:25:40', '2026-03-26 08:25:40'),
(1245, 'App\\Models\\User', 150, 'auth-token', 'e6d41ea023f606649e02d3bd7232c75b6e7bc4f4349677ac09ee5aba993b180f', '[\"*\"]', NULL, NULL, '2026-03-26 08:34:04', '2026-03-26 08:34:04'),
(1246, 'App\\Models\\User', 152, 'auth-token', '81416e04b2807e6557e95fde05b83cfe122fa6f65cd748fd80360ae230929ed1', '[\"*\"]', NULL, NULL, '2026-03-26 08:34:17', '2026-03-26 08:34:17'),
(1247, 'App\\Models\\User', 152, 'auth-token', '1f025f44d551df68916edd8574a51217d9552bd48c47ac6f4b0ae6eb62e5d435', '[\"*\"]', NULL, NULL, '2026-03-26 08:36:17', '2026-03-26 08:36:17'),
(1248, 'App\\Models\\User', 150, 'auth-token', '84b82f267dc1dfe7e13dddf6137096383f175a4db55ec29c4e1b40ba1415f145', '[\"*\"]', NULL, NULL, '2026-03-26 08:36:25', '2026-03-26 08:36:25'),
(1249, 'App\\Models\\User', 151, 'auth-token', 'd27bf8ab52e4eb0164baf2567ce2d0e76d8e9fc81f90cb371e8a4afced7f08fe', '[\"*\"]', NULL, NULL, '2026-03-26 08:37:30', '2026-03-26 08:37:30'),
(1250, 'App\\Models\\User', 151, 'auth-token', 'b8edadba2a2abee6351a88e6e824f3d027606a8db2cbe90ebea8101e45f63575', '[\"*\"]', NULL, NULL, '2026-03-26 08:53:01', '2026-03-26 08:53:01'),
(1251, 'App\\Models\\User', 150, 'auth-token', '24714465d57ccec1d1259abdc819cce0d0b359c6a7ab3d3b1ce538f6614f03a6', '[\"*\"]', NULL, NULL, '2026-03-26 08:56:25', '2026-03-26 08:56:25'),
(1252, 'App\\Models\\User', 149, 'auth-token', '0233857b51700a104aaf14a05a2fd72614a3d1f3bd315d326c00b852a607e535', '[\"*\"]', NULL, NULL, '2026-03-26 08:56:49', '2026-03-26 08:56:49'),
(1253, 'App\\Models\\User', 149, 'auth-token', '9c23d06ed699247474ac8700e2c75c548684e5699e7ea914afd338eaae8248d1', '[\"*\"]', NULL, NULL, '2026-03-26 09:02:25', '2026-03-26 09:02:25'),
(1254, 'App\\Models\\User', 150, 'auth-token', 'c871f83236f88e498a81fb79326c89186475b958e6f15390e42d854360372617', '[\"*\"]', NULL, NULL, '2026-03-26 09:02:39', '2026-03-26 09:02:39'),
(1255, 'App\\Models\\User', 158, 'auth-token', '4cdc0f207a4acea512ee12a881d5371587c4b3ca56ae27e4425675b98cfce4d0', '[\"*\"]', NULL, NULL, '2026-03-26 09:03:03', '2026-03-26 09:03:03'),
(1256, 'App\\Models\\User', 150, 'auth-token', '677991ed4e188fee8edcab27e94cd3fb8fcff3965138a8314dd13d327102489e', '[\"*\"]', NULL, NULL, '2026-03-26 09:03:54', '2026-03-26 09:03:54'),
(1257, 'App\\Models\\User', 149, 'auth-token', '9d2a69d28d90d3521c3741f990a1a2ccb53f1b9e5db9a79cc185d8877a0ddabd', '[\"*\"]', NULL, NULL, '2026-03-26 09:04:53', '2026-03-26 09:04:53'),
(1258, 'App\\Models\\User', 151, 'auth-token', '8db632f73c1e72f092c53aec8ec41ae7fecd614b1b21678ae05b53bb6b23d16d', '[\"*\"]', NULL, NULL, '2026-03-26 09:12:31', '2026-03-26 09:12:31'),
(1259, 'App\\Models\\User', 150, 'auth-token', '17fd5961db479c2adb6d041d9cb719318577c36bcc832085d45012f62842cd3b', '[\"*\"]', NULL, NULL, '2026-03-26 09:13:04', '2026-03-26 09:13:04'),
(1260, 'App\\Models\\User', 151, 'auth-token', 'b7e9a6f29e7b21ee02fb74a84f06a168d1e9a0eaf7585a1aaa9094f762fb2b01', '[\"*\"]', NULL, NULL, '2026-03-26 09:13:37', '2026-03-26 09:13:37'),
(1261, 'App\\Models\\User', 149, 'auth-token', 'e56d778b2d737245d71c6797f576a7f916eafad4813f15e50528f0f65be54d85', '[\"*\"]', NULL, NULL, '2026-03-26 09:13:54', '2026-03-26 09:13:54'),
(1262, 'App\\Models\\User', 150, 'auth-token', 'd9255472b275ae84fc4c86d1a368da811a64e2089c5b6a3f72d5c5af78ddb464', '[\"*\"]', NULL, NULL, '2026-03-26 09:42:33', '2026-03-26 09:42:33'),
(1263, 'App\\Models\\User', 149, 'auth-token', '640e479c5bab74071f0e89296a9f7dd088e2dc5363defef71c7ca861018112f6', '[\"*\"]', NULL, NULL, '2026-03-26 09:46:48', '2026-03-26 09:46:48'),
(1264, 'App\\Models\\User', 150, 'auth-token', 'b5049b67e8efd1d48ee988365c91cbeb658704424a4193d006235b399abc432b', '[\"*\"]', NULL, NULL, '2026-03-26 09:47:13', '2026-03-26 09:47:13'),
(1265, 'App\\Models\\User', 149, 'auth-token', '7e6282369d9986ff53874d3c144a9e6f6b20257f087096ccd9d6c3d37f24ea65', '[\"*\"]', NULL, NULL, '2026-03-26 09:59:34', '2026-03-26 09:59:34'),
(1266, 'App\\Models\\User', 150, 'auth-token', 'eee7ad0b10395a545eb68075dabbefbad5ebb4aa85abef47cbbd465b548657cf', '[\"*\"]', NULL, NULL, '2026-03-26 10:00:13', '2026-03-26 10:00:13'),
(1267, 'App\\Models\\User', 149, 'auth-token', '074c79713b1f9f920ac625c25fc71f4a2fa3c25d7a2ba0e8ada6f2a80489a414', '[\"*\"]', NULL, NULL, '2026-03-26 10:02:19', '2026-03-26 10:02:19'),
(1268, 'App\\Models\\User', 150, 'auth-token', 'fe6f414a58d216046ea43660843ee19761aeae9fe4c37937f80e3381f3e283ef', '[\"*\"]', NULL, NULL, '2026-03-26 10:03:11', '2026-03-26 10:03:11'),
(1269, 'App\\Models\\User', 149, 'auth-token', '62026d7b441a894a61a5beca36c02655980965448c04399c988f91a163802222', '[\"*\"]', NULL, NULL, '2026-03-26 10:08:15', '2026-03-26 10:08:15'),
(1270, 'App\\Models\\User', 150, 'auth-token', '49f8b51999f9c30ffe2e9d0f05341ca18a3c27740f7b2842e08f5c338959a8a9', '[\"*\"]', NULL, NULL, '2026-03-26 10:09:10', '2026-03-26 10:09:10'),
(1271, 'App\\Models\\User', 152, 'auth-token', '3e479e37472ca55dd8b3ac4cd7cb8e9e75ba0595af9d371ceeb5e91999ae6dd5', '[\"*\"]', NULL, NULL, '2026-03-26 10:16:37', '2026-03-26 10:16:37'),
(1272, 'App\\Models\\User', 150, 'auth-token', '49ab8c5c28bc116361464d7cea0c38e7aaeb7e7d79df891184252017d1cdf44f', '[\"*\"]', NULL, NULL, '2026-03-26 10:17:02', '2026-03-26 10:17:02'),
(1273, 'App\\Models\\User', 151, 'auth-token', '824cecab3c4df29ab7430a19669d1e784ef888a4092a333ab45ec7d6c3bc5e5b', '[\"*\"]', NULL, NULL, '2026-03-26 18:09:51', '2026-03-26 18:09:51'),
(1274, 'App\\Models\\User', 152, 'auth-token', 'a629aff5d2e7dbb6f002c5d5d3326e1ef46d060bb62320dc5e7678433f1b69c1', '[\"*\"]', NULL, NULL, '2026-03-26 18:10:11', '2026-03-26 18:10:11'),
(1275, 'App\\Models\\User', 151, 'auth-token', '5131e01f9c06a70d3a2f82e6af50c11546155706aa13c68f76fa397c035316a6', '[\"*\"]', NULL, NULL, '2026-03-26 18:10:26', '2026-03-26 18:10:26'),
(1276, 'App\\Models\\User', 162, 'auth-token', '5e24f3b76e0f8d0881757409717bf193bf1dcddc1a554be122273082def1409c', '[\"*\"]', NULL, NULL, '2026-03-26 18:10:43', '2026-03-26 18:10:43'),
(1277, 'App\\Models\\User', 162, 'auth-token', '4b5fd8c92911eb4dd85d96c6b0adf7ba0c6ad25f166297890300cf8983b772bb', '[\"*\"]', NULL, NULL, '2026-03-26 18:23:54', '2026-03-26 18:23:54'),
(1278, 'App\\Models\\User', 151, 'auth-token', '1724ad43662332d505c536a78ead001862634c903353b892ff1bef1fe6d1f1f7', '[\"*\"]', NULL, NULL, '2026-03-26 18:24:10', '2026-03-26 18:24:10'),
(1279, 'App\\Models\\User', 150, 'auth-token', '87c49965710c281075f9caf392c82a638dd695cefd213537b4609defdc56d59e', '[\"*\"]', NULL, NULL, '2026-03-26 18:40:02', '2026-03-26 18:40:02'),
(1280, 'App\\Models\\User', 149, 'auth-token', '1bb2d88fc93bf4c5d7272adc846994e9a678d20aa75672517f35c488b1ac3260', '[\"*\"]', NULL, NULL, '2026-03-26 18:40:25', '2026-03-26 18:40:25'),
(1281, 'App\\Models\\User', 151, 'auth-token', 'fb2ea362aa4c49196f5b4a02de5594c9fe3a7113d34fd9bf8a34550c37f2a7b7', '[\"*\"]', NULL, NULL, '2026-03-26 18:41:13', '2026-03-26 18:41:13'),
(1282, 'App\\Models\\User', 28, 'auth-token', 'd83cf0d24bc2669f7f600d78311d7b47923dfdd7f7e9af2b152e9fb637011655', '[\"*\"]', NULL, NULL, '2026-03-26 18:53:53', '2026-03-26 18:53:53'),
(1283, 'App\\Models\\User', 28, 'auth-token', '26c69331a5ae432a3a4d6856a9b4b84468f341bc35564618a588ef6e22d7f8c1', '[\"*\"]', NULL, NULL, '2026-03-26 19:11:08', '2026-03-26 19:11:08'),
(1284, 'App\\Models\\User', 157, 'auth-token', 'b8ac66660fec68ffe13ae6a414373da6031c2c74c533dc2c2b8a79541264db56', '[\"*\"]', NULL, NULL, '2026-03-26 19:14:02', '2026-03-26 19:14:02'),
(1285, 'App\\Models\\User', 28, 'auth-token', '8327a707fdb2526b119fc7e1e4565e7397b5817ecef6debcc0026db17c6f5d10', '[\"*\"]', NULL, NULL, '2026-03-26 19:14:16', '2026-03-26 19:14:16'),
(1286, 'App\\Models\\User', 28, 'auth-token', '46c6e25ceee81e3dc3c603cd6c5faaa89102244d52c0e74b24e19c475758fdf8', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:18', '2026-03-26 19:16:18'),
(1287, 'App\\Models\\User', 28, 'auth-token', 'd6a45388c48921e43c20a15776110178bcc562fbba0a142b7d9eb98cd99c86ed', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:27', '2026-03-26 19:16:27'),
(1288, 'App\\Models\\User', 28, 'auth-token', 'ff477ee3bb9499165221797d3209bf11870073ce9e83638198210816d8395855', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:35', '2026-03-26 19:16:35'),
(1289, 'App\\Models\\User', 28, 'auth-token', 'bebd77bf7ea91089949343d1023dadeacf3a5a1969e7810835b53ff490ffa393', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:47', '2026-03-26 19:16:47'),
(1290, 'App\\Models\\User', 28, 'auth-token', '3c8997547b545bcbbe3ef3398043c4b5e27be810c5f63e99da010f8615c9fc7a', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:01', '2026-03-26 19:17:01'),
(1291, 'App\\Models\\User', 28, 'auth-token', 'eae332d5b54554d8562bc6b0dd3653bca657190a3c59495323e48985abb1a37e', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:13', '2026-03-26 19:17:13'),
(1292, 'App\\Models\\User', 28, 'auth-token', 'add188c9755bf60cb03467deb72348a68a31a432594913ce78565a6c62ee22cd', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:25', '2026-03-26 19:17:25'),
(1293, 'App\\Models\\User', 28, 'auth-token', 'c17d7b6c2f56cc61adcc51e9d7bea79dea943374d4959b094892932ae4403ed2', '[\"*\"]', NULL, NULL, '2026-03-26 19:30:15', '2026-03-26 19:30:15'),
(1294, 'App\\Models\\User', 154, 'auth-token', 'd77ac8c5981909bcd55ca240ab8e53bc658cef0b29ab78e05e28958f4775efc2', '[\"*\"]', NULL, NULL, '2026-03-26 19:31:58', '2026-03-26 19:31:58'),
(1295, 'App\\Models\\User', 154, 'auth-token', 'd27e34cf1190775a0d7698a29f825f9faf0e39e52492216244e98cc457cb5fde', '[\"*\"]', NULL, NULL, '2026-03-26 19:32:16', '2026-03-26 19:32:16'),
(1296, 'App\\Models\\User', 150, 'auth-token', '7a772e71761d4454c0909dd317efe98cb688367c7149fac03a5efd3d5ffc8795', '[\"*\"]', NULL, NULL, '2026-03-26 19:38:32', '2026-03-26 19:38:32'),
(1297, 'App\\Models\\User', 149, 'auth-token', 'd5517cc5567c01bff203c24687741a284ee07a8ff5dfd6a1fed1feaa1558d5af', '[\"*\"]', NULL, NULL, '2026-03-26 19:39:47', '2026-03-26 19:39:47'),
(1298, 'App\\Models\\User', 150, 'auth-token', '3e3aa03d84366793acf2066ce13f0da55f938f80b69108175f160c58d56c3cf4', '[\"*\"]', NULL, NULL, '2026-03-26 19:41:00', '2026-03-26 19:41:00'),
(1299, 'App\\Models\\User', 152, 'auth-token', 'cac5eb0c6a308557761d233639747d6887f60113c8bcf508e6f1fefef8756ae2', '[\"*\"]', NULL, NULL, '2026-03-26 19:41:33', '2026-03-26 19:41:33'),
(1300, 'App\\Models\\User', 150, 'auth-token', '74304a5f31d209f83691a4ac80767cf0cd7963ba7690c89a34bca4d85aa828cc', '[\"*\"]', NULL, NULL, '2026-03-26 19:42:36', '2026-03-26 19:42:36'),
(1301, 'App\\Models\\User', 149, 'auth-token', '99ec3406b729fd9e6a19a97e44df19bd3f14b84b425e9421428462685ae135cb', '[\"*\"]', NULL, NULL, '2026-03-26 19:43:37', '2026-03-26 19:43:37'),
(1302, 'App\\Models\\User', 150, 'auth-token', 'd185c36728d671f732e31051528bca0437c9a067c9dec41a5aab36263a7137d0', '[\"*\"]', NULL, NULL, '2026-03-26 19:45:37', '2026-03-26 19:45:37'),
(1303, 'App\\Models\\User', 149, 'auth-token', 'eb708822979774db691943ae5b0ad419d48fa4a09a767fade1d252f53725e7a2', '[\"*\"]', NULL, NULL, '2026-03-26 20:05:30', '2026-03-26 20:05:30'),
(1304, 'App\\Models\\User', 154, 'auth-token', 'd0835022bb7828c375efa41e1d3d40aeec500b592d4f4b648cd008d189b259ee', '[\"*\"]', NULL, NULL, '2026-03-26 20:05:56', '2026-03-26 20:05:56'),
(1305, 'App\\Models\\User', 150, 'auth-token', 'cef1d5c202b7943801ee63aa59376b98d000ffc83de43e05a898cee0172b40a2', '[\"*\"]', NULL, NULL, '2026-03-26 20:06:24', '2026-03-26 20:06:24'),
(1306, 'App\\Models\\User', 152, 'auth-token', 'd164b2cf42a5f00f1f0bcda4a9e3d89ff17551d77db7b481c20ecde799db66c4', '[\"*\"]', NULL, NULL, '2026-03-26 20:06:51', '2026-03-26 20:06:51'),
(1307, 'App\\Models\\User', 150, 'auth-token', '831ebca8461a2e295b4c66d70cca5fd2c6a8bc711506f0daf2a8416d1c9f6574', '[\"*\"]', NULL, NULL, '2026-03-26 20:07:11', '2026-03-26 20:07:11'),
(1308, 'App\\Models\\User', 149, 'auth-token', 'd6e36e010f8b7183c5cdead61b51690de03706cb83ca88d486d6a65a62089faa', '[\"*\"]', NULL, NULL, '2026-03-26 20:07:48', '2026-03-26 20:07:48'),
(1309, 'App\\Models\\User', 154, 'auth-token', 'e042a0d0f0aef14963c6c49e6849e63a975bcb65a40f619125f6f5e42cbf9a33', '[\"*\"]', NULL, NULL, '2026-03-26 20:08:25', '2026-03-26 20:08:25'),
(1310, 'App\\Models\\User', 154, 'auth-token', '7c743103eb2f8eaffceb623f3d901bef7c2c6800bd6366236022bb4ce50b19c9', '[\"*\"]', NULL, NULL, '2026-03-26 20:34:37', '2026-03-26 20:34:37'),
(1311, 'App\\Models\\User', 151, 'auth-token', '74f033ce72a6cb456dd45742f8c2370785162e40d6df4840516fedafcfdaa7a9', '[\"*\"]', NULL, NULL, '2026-03-26 20:37:34', '2026-03-26 20:37:34'),
(1312, 'App\\Models\\User', 150, 'auth-token', 'f67d79095f6b3c13df8a3928542f5aee0586f2f5f16692daeb9688687303f1b0', '[\"*\"]', NULL, NULL, '2026-03-26 20:39:04', '2026-03-26 20:39:04'),
(1313, 'App\\Models\\User', 149, 'auth-token', '0a86cf6bf7a7929ed56df6e054c94e94ddf21bcbdf7e2e43a28ca6cb377326e0', '[\"*\"]', NULL, NULL, '2026-03-26 20:39:38', '2026-03-26 20:39:38'),
(1314, 'App\\Models\\User', 150, 'auth-token', '16910908b6e8e9f1686962499dd9c131c190f36aa125436189da8a3bc832bcfc', '[\"*\"]', NULL, NULL, '2026-03-26 20:40:07', '2026-03-26 20:40:07'),
(1315, 'App\\Models\\User', 152, 'auth-token', '6d8fec6bc103ab1327bf162ff9d086592479363b981906a9e1bbdd2a8e9cffb3', '[\"*\"]', NULL, NULL, '2026-03-26 20:41:01', '2026-03-26 20:41:01'),
(1316, 'App\\Models\\User', 150, 'auth-token', '0c443c51e1fbedd523cd903695036589ec8c9f24a5ec98bff347f91ec59891e6', '[\"*\"]', NULL, NULL, '2026-03-26 20:41:28', '2026-03-26 20:41:28'),
(1317, 'App\\Models\\User', 149, 'auth-token', '6e4a2d3a83aa6f1ff366d6adcaf436ed493b2d4ddb3271f1f6176a94e6df1b55', '[\"*\"]', NULL, NULL, '2026-03-26 20:42:05', '2026-03-26 20:42:05'),
(1318, 'App\\Models\\User', 150, 'auth-token', '9a5564697f94680472a784ff67cfed4d2c8c91abc1f3bd72c165999b80c25e88', '[\"*\"]', NULL, NULL, '2026-03-26 20:42:30', '2026-03-26 20:42:30'),
(1319, 'App\\Models\\User', 154, 'auth-token', '228a02028bde55cce77df400963992d89fc4d9afb9119a16b414646d2243d5b2', '[\"*\"]', NULL, NULL, '2026-03-26 20:43:25', '2026-03-26 20:43:25'),
(1320, 'App\\Models\\User', 151, 'auth-token', 'd443c2824119f4f38e8a28be5a81d7a13191ba79b8235db61ab13d0ee216a7b9', '[\"*\"]', NULL, NULL, '2026-03-26 20:44:03', '2026-03-26 20:44:03'),
(1321, 'App\\Models\\User', 150, 'auth-token', '4a0818e933e1b4e004c4a97fbfdbd39cea263a193b54bf12f1d93627fd0a4e78', '[\"*\"]', NULL, NULL, '2026-03-26 20:44:47', '2026-03-26 20:44:47'),
(1322, 'App\\Models\\User', 149, 'auth-token', 'c192bd76384916d96f5f17f0472dc0a3986db44011fd18f73ae73818066bda11', '[\"*\"]', NULL, NULL, '2026-03-26 20:45:09', '2026-03-26 20:45:09'),
(1323, 'App\\Models\\User', 150, 'auth-token', '26fc2e088ac70a5c5e0e8a9fe87501e2fa75d4a73f0688c97c6c82e451e0d334', '[\"*\"]', NULL, NULL, '2026-03-26 20:45:46', '2026-03-26 20:45:46'),
(1324, 'App\\Models\\User', 158, 'auth-token', 'd2e55c8523770b29bd0c74a7c59f00b9373db203991deca5d89ca7f0aad65793', '[\"*\"]', NULL, NULL, '2026-03-26 20:46:22', '2026-03-26 20:46:22'),
(1325, 'App\\Models\\User', 150, 'auth-token', 'b8d016a27adc5e3cd550d0cfec3fea928235daf5bd4fd6e2aa03c82b7586c45e', '[\"*\"]', NULL, NULL, '2026-03-26 20:46:45', '2026-03-26 20:46:45'),
(1326, 'App\\Models\\User', 151, 'auth-token', '4b4747e8e440355436ca75b123d3639cd2ef8b0dfe3fd311f6dd5fb7997824e1', '[\"*\"]', NULL, NULL, '2026-03-26 20:54:10', '2026-03-26 20:54:10'),
(1327, 'App\\Models\\User', 150, 'auth-token', '68378df0c28ddb39381e22abf78c3282e87b9ba14941e5a67eb7437727e36db7', '[\"*\"]', NULL, NULL, '2026-03-26 20:54:37', '2026-03-26 20:54:37'),
(1328, 'App\\Models\\User', 149, 'auth-token', 'a8d2f05d532906361bbf9ce8cb8e2f4cb98779f89a33e28d9285e0480cfc696a', '[\"*\"]', NULL, NULL, '2026-03-26 20:55:00', '2026-03-26 20:55:00'),
(1329, 'App\\Models\\User', 150, 'auth-token', '4babc9075173e5c32f8013c331c78fee3a8c7e7f455dd7f33fea2bb7cb7de96d', '[\"*\"]', NULL, NULL, '2026-03-26 20:55:20', '2026-03-26 20:55:20'),
(1330, 'App\\Models\\User', 28, 'auth-token', 'a7da4dee85ac1bc26e3911028eeac528196fea0525a2c9b824fb09944f844d85', '[\"*\"]', NULL, NULL, '2026-03-26 21:13:16', '2026-03-26 21:13:16'),
(1331, 'App\\Models\\User', 150, 'auth-token', '253bc79573261415de0523495a477c9c2b22eb86e6ecb130bafd7a5ac34a1c78', '[\"*\"]', NULL, NULL, '2026-03-26 21:15:13', '2026-03-26 21:15:13'),
(1332, 'App\\Models\\User', 31, 'auth-token', 'e7cc81396746c52d96ddede79ebda4fe3f841c7509d4dc80bf138debea0bdc73', '[\"*\"]', NULL, NULL, '2026-03-26 21:19:46', '2026-03-26 21:19:46'),
(1333, 'App\\Models\\User', 152, 'auth-token', '455a645f2d8cddce16633fb85f2df57c7f31c5bff1fc691500dd915d73af5e53', '[\"*\"]', NULL, NULL, '2026-03-26 21:20:01', '2026-03-26 21:20:01'),
(1334, 'App\\Models\\User', 150, 'auth-token', 'b1550a1de97ecd7737e0b983fc07a8d3c39982160acabc3456cd56cf2c08f7b5', '[\"*\"]', NULL, NULL, '2026-03-26 21:20:22', '2026-03-26 21:20:22'),
(1335, 'App\\Models\\User', 149, 'auth-token', '51b503957e86050af9eaf3fae407e3002a8e02a9f911e75ddf3237760c2de923', '[\"*\"]', NULL, NULL, '2026-03-26 21:22:00', '2026-03-26 21:22:00'),
(1336, 'App\\Models\\User', 150, 'auth-token', '65f2644bc5412c022acdc3c567e48631285f0ab6cfcf56ac7ac245c6aa2fac20', '[\"*\"]', NULL, NULL, '2026-03-26 21:22:31', '2026-03-26 21:22:31'),
(1337, 'App\\Models\\User', 149, 'auth-token', '056459526d1f96eb0981214d110bcf6872472fa73eec6acdab201bd81957e202', '[\"*\"]', NULL, NULL, '2026-03-26 21:32:10', '2026-03-26 21:32:10'),
(1338, 'App\\Models\\User', 150, 'auth-token', 'e2d29253549b6e0065482bb13a89c73e872e56c396595c86087246e91dcafbef', '[\"*\"]', NULL, NULL, '2026-03-26 21:32:38', '2026-03-26 21:32:38'),
(1339, 'App\\Models\\User', 154, 'auth-token', '2a847a9709e6ce55d2d8a0c6c82b2d5053c62fd2814ce7ac97f00e9d18d2863f', '[\"*\"]', NULL, NULL, '2026-03-26 21:33:01', '2026-03-26 21:33:01'),
(1340, 'App\\Models\\User', 159, 'auth-token', '021266f29c1bbc9f7665734c7930380a05367834fe76f1cd5168417284f7ab33', '[\"*\"]', NULL, NULL, '2026-03-27 05:02:25', '2026-03-27 05:02:25'),
(1341, 'App\\Models\\User', 147, 'auth-token', 'adacb950669f08bfe6d747fa91e0d243d5b747db399157cee436759114998487', '[\"*\"]', NULL, NULL, '2026-03-27 05:11:33', '2026-03-27 05:11:33'),
(1342, 'App\\Models\\User', 159, 'auth-token', 'ef5539807c979789d078fbd685b459c25a5dfa60830e25ccc24fc2dfa38936f6', '[\"*\"]', NULL, NULL, '2026-03-27 05:23:39', '2026-03-27 05:23:39'),
(1343, 'App\\Models\\User', 159, 'auth-token', '4af7f77beec04e16e4b36d35ff48ec2016fc07e632a159b272ff45ed117117b3', '[\"*\"]', NULL, NULL, '2026-03-27 05:46:58', '2026-03-27 05:46:58'),
(1344, 'App\\Models\\User', 159, 'auth-token', 'c79b2c18a7e186065bf60502374caf72e625afa6092d216c66836b036f17b91a', '[\"*\"]', NULL, NULL, '2026-03-27 05:52:45', '2026-03-27 05:52:45'),
(1345, 'App\\Models\\User', 159, 'auth-token', '364c8b95b289c390c4f9ed9347c89e59b3b9b3b40bf59dea975ac8816cdf1124', '[\"*\"]', NULL, NULL, '2026-03-27 05:55:32', '2026-03-27 05:55:32'),
(1346, 'App\\Models\\User', 159, 'auth-token', '27d7cbb70a9f7be319f612ee7cb9e90831608b4a73ec1e0c3a4aa01f37caf684', '[\"*\"]', NULL, NULL, '2026-03-27 06:01:54', '2026-03-27 06:01:54'),
(1347, 'App\\Models\\User', 151, 'auth-token', '7f6eef13c026658667b6af7b07eff11684a699a66113968bd4432aab24ae85f6', '[\"*\"]', NULL, NULL, '2026-03-27 06:13:25', '2026-03-27 06:13:25'),
(1348, 'App\\Models\\User', 159, 'auth-token', '1da95a3120b407a8c17123fd4a8f1aba6a406528908d91d9e7f97a908827eff5', '[\"*\"]', NULL, NULL, '2026-03-27 06:14:51', '2026-03-27 06:14:51'),
(1349, 'App\\Models\\User', 159, 'auth-token', 'c23cf915ecb2e9981abbf1efc013c156abc04cd8f9ae3c74d2a18d7385be0e32', '[\"*\"]', NULL, NULL, '2026-03-27 06:36:15', '2026-03-27 06:36:15'),
(1350, 'App\\Models\\User', 159, 'auth-token', 'ac05c14c878f9f056ad601d263421af384488729ac0ac71189b343e7c5d9f472', '[\"*\"]', NULL, NULL, '2026-03-27 06:40:12', '2026-03-27 06:40:12'),
(1351, 'App\\Models\\User', 167, 'auth-token', '1c7e89a38f31ceb081a9064bda1c61fa6a6d9b6dc23330322afe74cd67936335', '[\"*\"]', NULL, NULL, '2026-03-27 06:45:46', '2026-03-27 06:45:46'),
(1352, 'App\\Models\\User', 167, 'auth-token', '6df635684ed33d8dad8dfc94b8a067b9952e76330bfcc28f01bd468a87306297', '[\"*\"]', NULL, NULL, '2026-03-27 06:46:28', '2026-03-27 06:46:28'),
(1353, 'App\\Models\\User', 167, 'auth-token', 'eba222f2734a0022b4abbcd6c30fb92d0b53fc8ae4ea5c64a966ade911ab051d', '[\"*\"]', NULL, NULL, '2026-03-27 06:47:33', '2026-03-27 06:47:33'),
(1354, 'App\\Models\\User', 167, 'auth-token', '0c413dba4d55364e501538a13a0a4f6ac5c7080b6a5af89d3b13bdd9a0f84259', '[\"*\"]', NULL, NULL, '2026-03-27 08:19:28', '2026-03-27 08:19:28'),
(1355, 'App\\Models\\User', 167, 'auth-token', 'c486df1249b85f316235a39e8591f649c59e3a9648eeddd26fb6008cfabe72c2', '[\"*\"]', NULL, NULL, '2026-03-27 08:24:00', '2026-03-27 08:24:00'),
(1356, 'App\\Models\\User', 167, 'auth-token', 'd5415b398b88a49f9ebfac7ee8c00c112d33d01283f983f9483175baa76a9e13', '[\"*\"]', NULL, NULL, '2026-03-27 08:29:13', '2026-03-27 08:29:13'),
(1357, 'App\\Models\\User', 167, 'auth-token', 'c97d168fa15bf98f91614a54c9277fae5d3b31273e28a56403d30c564f7f876a', '[\"*\"]', NULL, NULL, '2026-03-27 08:33:18', '2026-03-27 08:33:18'),
(1358, 'App\\Models\\User', 167, 'auth-token', '65874e939d0749beb850acec844327077d5ac9aad7a209e9c2f7aac39bcd6d06', '[\"*\"]', NULL, NULL, '2026-03-27 08:35:54', '2026-03-27 08:35:54'),
(1359, 'App\\Models\\User', 167, 'auth-token', 'eddc5c025c342d3cec9d4e3276765d48e2509a336d8c04741c36b1c4acf3a463', '[\"*\"]', NULL, NULL, '2026-03-27 08:50:29', '2026-03-27 08:50:29'),
(1360, 'App\\Models\\User', 167, 'auth-token', '67e145290bb35b85d428676e867562e8d58e6415751235da52d4888d037ce595', '[\"*\"]', NULL, NULL, '2026-03-27 09:02:21', '2026-03-27 09:02:21'),
(1361, 'App\\Models\\User', 167, 'auth-token', '6caad882cde152b55ec62d9b474f5b7f285f9f15669968ef30aa80a080e06e47', '[\"*\"]', NULL, NULL, '2026-03-27 09:04:36', '2026-03-27 09:04:36'),
(1362, 'App\\Models\\User', 159, 'auth-token', 'd2b58964c44526b1f7473affb57faa320620f78dd77e97a38e05dc6acebcc411', '[\"*\"]', NULL, NULL, '2026-03-27 09:05:34', '2026-03-27 09:05:34'),
(1363, 'App\\Models\\User', 169, 'auth-token', '9ac892b4d8d8bd0e057b5301e6ae2a52bdc5de2d64d3fb6290b349450149a9bc', '[\"*\"]', NULL, NULL, '2026-03-27 09:08:42', '2026-03-27 09:08:42'),
(1364, 'App\\Models\\User', 169, 'auth-token', '290c523e03debc1d3e8552dd222d9caeb9f247dfaa685fd4691663e5051696dc', '[\"*\"]', NULL, NULL, '2026-03-27 09:09:20', '2026-03-27 09:09:20'),
(1365, 'App\\Models\\User', 169, 'auth-token', 'e13f664e418b3055b532f4b2d2f6dab6cbcd4ed50257b8d75b015c388a60bf55', '[\"*\"]', NULL, NULL, '2026-03-27 09:10:17', '2026-03-27 09:10:17'),
(1366, 'App\\Models\\User', 169, 'auth-token', '28bedc20e4a8c232d1b8fa4675976161ade85442eefe4d7aee6fc4fd3bc56b41', '[\"*\"]', NULL, NULL, '2026-03-27 09:16:49', '2026-03-27 09:16:49'),
(1367, 'App\\Models\\User', 169, 'auth-token', '80e75aecf09bb8c2c06885d49816070f9812330da72cca3cd79520e5e47e8174', '[\"*\"]', NULL, NULL, '2026-03-27 09:17:12', '2026-03-27 09:17:12'),
(1368, 'App\\Models\\User', 169, 'auth-token', '1e6567ca7806dff4409b0988cf32dea47a14046c8b1e4dbf32792f6aa1f80a32', '[\"*\"]', NULL, NULL, '2026-03-27 09:38:18', '2026-03-27 09:38:18'),
(1369, 'App\\Models\\User', 169, 'auth-token', '0ef3e9fdb0247723e9b9fd4b08f4d5a8061d4d1512b71430ca1f5aaec3a91526', '[\"*\"]', NULL, NULL, '2026-03-27 09:47:59', '2026-03-27 09:47:59');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1370, 'App\\Models\\User', 169, 'auth-token', '3811bebb2422ca5191a75423546f16c2fc0eb54ea3cf7447eb684198d606fbed', '[\"*\"]', NULL, NULL, '2026-03-27 09:48:30', '2026-03-27 09:48:30'),
(1371, 'App\\Models\\User', 150, 'auth-token', '6ac9a107fae7afcc01eb975793af1c075580e13cac43f123b700e9332ad1a7d3', '[\"*\"]', NULL, NULL, '2026-03-28 03:56:11', '2026-03-28 03:56:11'),
(1372, 'App\\Models\\User', 28, 'auth-token', '9458f9fde790a00947e7a8ffdea71403163afda55d7ad75be3453995918a66ee', '[\"*\"]', NULL, NULL, '2026-03-28 03:56:36', '2026-03-28 03:56:36'),
(1373, 'App\\Models\\User', 169, 'auth-token', '2d312bbd8b235bdee492b9476755bf35f457e2345e8bbd90642e945867361f8a', '[\"*\"]', NULL, NULL, '2026-03-28 03:57:55', '2026-03-28 03:57:55'),
(1374, 'App\\Models\\User', 151, 'auth-token', '41b936d36f2a815badd04a6f0a1970ef4d341f6cd4445fd7c5eeb863f80dd272', '[\"*\"]', NULL, NULL, '2026-03-28 04:05:54', '2026-03-28 04:05:54'),
(1375, 'App\\Models\\User', 169, 'auth-token', '5e3633b572f58609ec4175a4c9666137bb7f7808dd2f5330013bff98ca1e6567', '[\"*\"]', NULL, NULL, '2026-03-28 04:47:23', '2026-03-28 04:47:23'),
(1376, 'App\\Models\\User', 28, 'auth-token', '2b2ebf90db403ee81509bee872f15b0e198f5a54ae2e739d5761c465806741dd', '[\"*\"]', NULL, NULL, '2026-03-28 04:48:59', '2026-03-28 04:48:59'),
(1377, 'App\\Models\\User', 169, 'auth-token', 'aeecfd7337a744c8f4e12c2222d8568a9f8f4e6d5f92be63b737ec84a4e1d64c', '[\"*\"]', NULL, NULL, '2026-03-28 04:50:29', '2026-03-28 04:50:29'),
(1378, 'App\\Models\\User', 169, 'auth-token', 'c6e5b5a3924940ba0f134fb8bc02c39ee0bbded20f0b7d51ed1e84e4908c6614', '[\"*\"]', NULL, NULL, '2026-03-28 04:52:04', '2026-03-28 04:52:04'),
(1379, 'App\\Models\\User', 159, 'auth-token', '8a6e973a009f913ecf6ab717a399cfb81ce0dbd0f6c2aa1d7e795aee6340a90b', '[\"*\"]', NULL, NULL, '2026-03-28 05:04:50', '2026-03-28 05:04:50'),
(1380, 'App\\Models\\User', 173, 'auth-token', 'c66131eed66c322fc75bb1d36b2dc165849cff03bae58e6bccbad0677ea8fc9c', '[\"*\"]', NULL, NULL, '2026-03-28 05:19:04', '2026-03-28 05:19:04'),
(1381, 'App\\Models\\User', 173, 'auth-token', '48e47388d95d2cfc66e442729a64e6ea3d449b4b10ecb3bcd672a66398958f5c', '[\"*\"]', NULL, NULL, '2026-03-28 05:19:33', '2026-03-28 05:19:33'),
(1382, 'App\\Models\\User', 173, 'auth-token', 'f0d35825f9a2ad02ba6adfe5333e10bc1695484426bdc9e6eb02a75cec26095d', '[\"*\"]', NULL, NULL, '2026-03-28 05:20:01', '2026-03-28 05:20:01'),
(1383, 'App\\Models\\User', 173, 'auth-token', '7ce6e6227248b79b6062526e8fc4921657dee5e49aecb4c996418ffbd6f09456', '[\"*\"]', NULL, NULL, '2026-03-28 05:30:28', '2026-03-28 05:30:28'),
(1384, 'App\\Models\\User', 173, 'auth-token', 'f5f79bcc1997877d451e9c479a79e0f51972b913208506f5d0c8c918ea84cd43', '[\"*\"]', NULL, NULL, '2026-03-28 05:34:19', '2026-03-28 05:34:19'),
(1385, 'App\\Models\\User', 173, 'auth-token', '32d7c022a6638b5c68f792ac0cfb50bcb6fd871e0d7dd17020c5e3a59286b710', '[\"*\"]', NULL, NULL, '2026-03-28 05:37:59', '2026-03-28 05:37:59'),
(1386, 'App\\Models\\User', 173, 'auth-token', 'bf18ac9ebe12bdfbe44dd5a9a86cf27a96a7f2970c0104f7f96ea0a68e5fd931', '[\"*\"]', NULL, NULL, '2026-03-28 05:38:25', '2026-03-28 05:38:25'),
(1387, 'App\\Models\\User', 173, 'auth-token', '83f80dd9cee0a35fab32086bc7b9a1bc7ba360cbb98ba15b2908c22dbec6a524', '[\"*\"]', NULL, NULL, '2026-03-28 05:52:35', '2026-03-28 05:52:35'),
(1388, 'App\\Models\\User', 173, 'auth-token', 'aa719c9ddac823e6401c049055a3779d9609c2b9f7c3b8f538556d70aabeb524', '[\"*\"]', NULL, NULL, '2026-03-28 05:56:08', '2026-03-28 05:56:08'),
(1389, 'App\\Models\\User', 173, 'auth-token', '2d7daef3e743b63d54fac3bfb62acca2d81a851f34a97a663eac9c65daaf53c8', '[\"*\"]', NULL, NULL, '2026-03-28 06:06:32', '2026-03-28 06:06:32'),
(1390, 'App\\Models\\User', 173, 'auth-token', 'df02ca4bbf8e3661a2253734f9b98890b070b76c56648397cf902ea614b62a33', '[\"*\"]', NULL, NULL, '2026-03-28 06:06:53', '2026-03-28 06:06:53'),
(1391, 'App\\Models\\User', 173, 'auth-token', 'e3fab4288e2be1891a291773338f6ede0c25e21b9bd682438d93e7657fc3ea2f', '[\"*\"]', NULL, NULL, '2026-03-28 06:13:59', '2026-03-28 06:13:59'),
(1392, 'App\\Models\\User', 173, 'auth-token', '0348da8cded8744817df7f98a7fddf171f8711152ebb5c796e54b279baee9d04', '[\"*\"]', NULL, NULL, '2026-03-28 06:19:02', '2026-03-28 06:19:02'),
(1393, 'App\\Models\\User', 173, 'auth-token', 'f70957862a81fc145f008fb66f08be93d7c1b2569347a8b4313901b0d6373965', '[\"*\"]', NULL, NULL, '2026-03-28 06:23:37', '2026-03-28 06:23:37'),
(1394, 'App\\Models\\User', 151, 'auth-token', 'f42866808a9cd9def07bd9f09d414622c00e7a3aa58602ee0edf57b3b836e49c', '[\"*\"]', NULL, NULL, '2026-03-28 06:35:59', '2026-03-28 06:35:59'),
(1395, 'App\\Models\\User', 173, 'auth-token', '2a9920f531dc644b7dd35fe25ad822a3617203683df50a08b0b8bfcfcd2efcf0', '[\"*\"]', NULL, NULL, '2026-03-28 06:48:14', '2026-03-28 06:48:14'),
(1396, 'App\\Models\\User', 154, 'auth-token', '0a28b7b57bf158fa45e001b6c7f40210017cc8277ce703b9164f5c4fe66e1956', '[\"*\"]', NULL, NULL, '2026-03-28 06:53:37', '2026-03-28 06:53:37'),
(1397, 'App\\Models\\User', 173, 'auth-token', '622ac05c50f9c06b39412a3c5064414a7575469f58cdf734a02b78959c1ef2fe', '[\"*\"]', NULL, NULL, '2026-03-28 06:57:41', '2026-03-28 06:57:41'),
(1398, 'App\\Models\\User', 174, 'auth-token', 'd846828c717f53591818375b98cecc06e64b48d04f7a0b30482e2206e0f142e6', '[\"*\"]', NULL, NULL, '2026-03-28 06:59:33', '2026-03-28 06:59:33'),
(1399, 'App\\Models\\User', 174, 'auth-token', '1933856364a5222728c3910bf75230562a1fb46f6e85df55dd2627c1d8d20157', '[\"*\"]', NULL, NULL, '2026-03-28 06:59:53', '2026-03-28 06:59:53'),
(1400, 'App\\Models\\User', 173, 'auth-token', 'aef6b6f2aa6d92a8e1644488b97c36987ccb701566018906227bb177c82a672c', '[\"*\"]', NULL, NULL, '2026-03-28 07:00:45', '2026-03-28 07:00:45'),
(1401, 'App\\Models\\User', 174, 'auth-token', '3deffa66c12724e1e4ca7abe3fb7fe73bf12b2d0ea423b0e41f9df926eb29e55', '[\"*\"]', NULL, NULL, '2026-03-28 07:29:15', '2026-03-28 07:29:15'),
(1402, 'App\\Models\\User', 173, 'auth-token', '4c819d307cd09e0f63dae79ddc4f1548fceb6709cbfa497d746fe784946c46dd', '[\"*\"]', NULL, NULL, '2026-03-28 07:29:36', '2026-03-28 07:29:36'),
(1403, 'App\\Models\\User', 174, 'auth-token', '9ccea425990c2c04a5f4dde61dce330a70923d4ea3e8fafa1e8c9c22eded7ff4', '[\"*\"]', NULL, NULL, '2026-03-28 07:30:09', '2026-03-28 07:30:09'),
(1404, 'App\\Models\\User', 173, 'auth-token', 'caf9dd8b093dfdeab7b43c298fcf359b1bc6b089e59c54ecd7f867f3a2ac2c6a', '[\"*\"]', NULL, NULL, '2026-03-28 07:30:27', '2026-03-28 07:30:27'),
(1405, 'App\\Models\\User', 151, 'auth-token', '41435ce454e2308ebb4e64bc069d56195c354978ac6094229f97b46a0a4877d2', '[\"*\"]', NULL, NULL, '2026-03-28 07:36:51', '2026-03-28 07:36:51'),
(1406, 'App\\Models\\User', 154, 'auth-token', 'c979cb55b72090063d81a16420d79c59847e9214f1355faa73bbb0e067c372c4', '[\"*\"]', NULL, NULL, '2026-03-28 07:37:10', '2026-03-28 07:37:10'),
(1407, 'App\\Models\\User', 173, 'auth-token', 'da31c18885801d41211bf773316135851a206c40e89a66501bfb8d0d182b41e3', '[\"*\"]', NULL, NULL, '2026-03-28 07:42:05', '2026-03-28 07:42:05'),
(1408, 'App\\Models\\User', 154, 'auth-token', 'd4d1df58ac325588eb6e4dd3d948abace076f09db696b6d6cf36c6a143a9b981', '[\"*\"]', NULL, NULL, '2026-03-28 07:52:20', '2026-03-28 07:52:20'),
(1409, 'App\\Models\\User', 154, 'auth-token', 'cb4612834d65b8ca902fe0c5deb9be82ddd9b3b6066a37d7ea3ae1d4bfa1b1e0', '[\"*\"]', NULL, NULL, '2026-03-28 07:54:25', '2026-03-28 07:54:25'),
(1410, 'App\\Models\\User', 154, 'auth-token', '1e1e39fd757516319e9e038dd626ff4fb1a697e05cd95566d2710abd953b846c', '[\"*\"]', NULL, NULL, '2026-03-28 07:54:27', '2026-03-28 07:54:27'),
(1411, 'App\\Models\\User', 154, 'auth-token', '016938a8b68003c09ad461febfd71f72762ace8232b6233f29f5f12dc7052ebd', '[\"*\"]', NULL, NULL, '2026-03-28 07:57:46', '2026-03-28 07:57:46'),
(1412, 'App\\Models\\User', 173, 'auth-token', '17e56c8c9b7b8fa31fc7751c4fd9d57cb9871c8ebbee786930f7f54bee9782c2', '[\"*\"]', NULL, NULL, '2026-03-28 07:58:07', '2026-03-28 07:58:07'),
(1413, 'App\\Models\\User', 173, 'auth-token', 'b62f9b30238dc6f7685f6828d4243ba30384e629a60de9f4509c37b99c725d6d', '[\"*\"]', NULL, NULL, '2026-03-28 08:07:05', '2026-03-28 08:07:05'),
(1414, 'App\\Models\\User', 153, 'auth-token', '0a370e3070f6f0cb0fa58f98d3f2b5d632b987f4e62a7673c1678e722a89bf0e', '[\"*\"]', NULL, NULL, '2026-03-28 08:07:57', '2026-03-28 08:07:57'),
(1415, 'App\\Models\\User', 154, 'auth-token', '4a9e2a3a341f7bab5681a645b52fc1ca9781161acb65f84044af232b97158e10', '[\"*\"]', NULL, NULL, '2026-03-28 08:08:29', '2026-03-28 08:08:29'),
(1416, 'App\\Models\\User', 157, 'auth-token', '75e7c535ae168bfe615f16c87046581a71654f6c63c6d6288845bdd07b0ebd58', '[\"*\"]', NULL, NULL, '2026-03-28 08:08:50', '2026-03-28 08:08:50'),
(1417, 'App\\Models\\User', 153, 'auth-token', '23824975c4cbc613673a9136374b4869c53670137f895bcadea706af1b748b0d', '[\"*\"]', NULL, NULL, '2026-03-28 08:16:39', '2026-03-28 08:16:39'),
(1418, 'App\\Models\\User', 157, 'auth-token', 'e36a895eb1b7169f6d257f675cbd56ec8e3baca795ddb41865c991b93d3a3602', '[\"*\"]', NULL, NULL, '2026-03-28 08:32:23', '2026-03-28 08:32:23'),
(1419, 'App\\Models\\User', 151, 'auth-token', '3fda03bdecabbc9a2af1ff0ebbc701f2e7edf829e6f131069b81108af91c2a56', '[\"*\"]', NULL, NULL, '2026-03-28 08:43:07', '2026-03-28 08:43:07'),
(1420, 'App\\Models\\User', 151, 'auth-token', '572df3d013ddead9c27db253ce9b1a3bfeebacf224f6fc1b7bf3241d16c24182', '[\"*\"]', NULL, NULL, '2026-03-28 08:45:55', '2026-03-28 08:45:55'),
(1421, 'App\\Models\\User', 153, 'auth-token', '44f5a4304c5a3a4ad2d5a571b91f0000728993afe9e859a3e81330661a29d2ae', '[\"*\"]', NULL, NULL, '2026-03-28 08:46:13', '2026-03-28 08:46:13'),
(1422, 'App\\Models\\User', 152, 'auth-token', 'c3078ae155fa6770d6e3613ba063daad3339c146b2d584fdde9cb1826ac94844', '[\"*\"]', NULL, NULL, '2026-03-28 08:46:42', '2026-03-28 08:46:42'),
(1423, 'App\\Models\\User', 154, 'auth-token', '7af232411b5a39cc822770dc173e34e6b6f46a62654aa8c3dbef095ec9c12bdc', '[\"*\"]', NULL, NULL, '2026-03-28 08:47:47', '2026-03-28 08:47:47'),
(1424, 'App\\Models\\User', 152, 'auth-token', 'dc5776b936577367006e41607b614169abc01ab96ab403b02ece5ef1aef913dd', '[\"*\"]', NULL, NULL, '2026-03-28 08:51:02', '2026-03-28 08:51:02'),
(1425, 'App\\Models\\User', 151, 'auth-token', 'f892d654c2609fe30e90c95d8601a0becc6bd924cd77ceef9b6311f9fc340baf', '[\"*\"]', NULL, NULL, '2026-03-28 08:52:28', '2026-03-28 08:52:28'),
(1426, 'App\\Models\\User', 151, 'auth-token', '0e36658b134307e11dc260928c3108d429bdfa493b4a36775258c3047fc88dc8', '[\"*\"]', NULL, NULL, '2026-03-28 08:56:31', '2026-03-28 08:56:31'),
(1427, 'App\\Models\\User', 152, 'auth-token', 'fd08270dac9c418fb47c79125da78f4a883ce94a3602b542f03e8b3bffd687a0', '[\"*\"]', NULL, NULL, '2026-03-28 08:57:00', '2026-03-28 08:57:00'),
(1428, 'App\\Models\\User', 151, 'auth-token', 'e42c5f7ef7320c31afa737d9526efaad09916850ccf920f575d37f1549cbd179', '[\"*\"]', NULL, NULL, '2026-03-28 09:09:57', '2026-03-28 09:09:57'),
(1429, 'App\\Models\\User', 153, 'auth-token', '9e646a2cd5299fb992ba9b9600431a426585b257abd6ec989aafc3c02c953c7e', '[\"*\"]', NULL, NULL, '2026-03-28 09:10:23', '2026-03-28 09:10:23'),
(1430, 'App\\Models\\User', 152, 'auth-token', 'a7bab84ce8afc1f90f845e76e9e1b713f23730894315962465a062af08c4e6b9', '[\"*\"]', NULL, NULL, '2026-03-28 09:13:08', '2026-03-28 09:13:08'),
(1431, 'App\\Models\\User', 152, 'auth-token', '04c1990010415234f784de1a141f20675ccfba1d62cda658e8f15024c693f6e5', '[\"*\"]', NULL, NULL, '2026-03-28 09:18:03', '2026-03-28 09:18:03'),
(1432, 'App\\Models\\User', 152, 'auth-token', '503c97613627d3540b5df668d9d803b6b61991b2f66fc2cf4035bcb85a59f86c', '[\"*\"]', NULL, NULL, '2026-03-28 09:21:34', '2026-03-28 09:21:34'),
(1433, 'App\\Models\\User', 151, 'auth-token', 'd9616ef439a4d6ab7103f41cb28bd0456d0d9ea43d4b03b9812caa8eae8845f8', '[\"*\"]', NULL, NULL, '2026-03-28 09:26:07', '2026-03-28 09:26:07'),
(1434, 'App\\Models\\User', 173, 'auth-token', '7cf31f61a170aa1935009e064374f2edbd69733de0549a329ad1a36c146d19a2', '[\"*\"]', NULL, NULL, '2026-03-28 09:29:40', '2026-03-28 09:29:40'),
(1435, 'App\\Models\\User', 152, 'auth-token', '70eebe1529252214bbbca617f58935dd2a42a12cbfaf10e92fec137bd7b5da78', '[\"*\"]', NULL, NULL, '2026-03-28 09:30:42', '2026-03-28 09:30:42'),
(1436, 'App\\Models\\User', 151, 'auth-token', '521485e0924b869af891fc330674f42162e840141cd17c6e8b491c5eaa23c1f6', '[\"*\"]', NULL, NULL, '2026-03-28 09:37:43', '2026-03-28 09:37:43'),
(1437, 'App\\Models\\User', 150, 'auth-token', 'c6f9663877da74506b02379aa6cf06ccb8cd74364bd330b5953254a8872a73d0', '[\"*\"]', NULL, NULL, '2026-03-28 09:38:10', '2026-03-28 09:38:10'),
(1438, 'App\\Models\\User', 149, 'auth-token', '6bee408d0eccf6ad9b1eb74bcd2f86a3b10aee3bb7807727efee1e0f37d02365', '[\"*\"]', NULL, NULL, '2026-03-28 09:38:56', '2026-03-28 09:38:56'),
(1439, 'App\\Models\\User', 150, 'auth-token', '016f618a2d2c5c32ac703ea1d098f46fd52e13f876e33f6726f1ce4fa364da90', '[\"*\"]', NULL, NULL, '2026-03-28 09:39:15', '2026-03-28 09:39:15'),
(1440, 'App\\Models\\User', 149, 'auth-token', 'cc78df72a40fb513a85984d600b72739fb8e79931953bfd1022086c3c444e6fb', '[\"*\"]', NULL, NULL, '2026-03-28 09:39:36', '2026-03-28 09:39:36'),
(1441, 'App\\Models\\User', 150, 'auth-token', 'f90163ee7de569fbc00157248063a07181790470542312750dd4efe94a40ade5', '[\"*\"]', NULL, NULL, '2026-03-28 09:39:57', '2026-03-28 09:39:57'),
(1442, 'App\\Models\\User', 152, 'auth-token', '78485bb0c383914d6f4ccda262ab4c9348f786952e16fadbf09030be40c5ccbf', '[\"*\"]', NULL, NULL, '2026-03-28 09:40:26', '2026-03-28 09:40:26'),
(1443, 'App\\Models\\User', 150, 'auth-token', 'fdce0e158fe52df68ae9d8ff0bd05a4e10e4a55e2e76d449aec48f14b1f94af9', '[\"*\"]', NULL, NULL, '2026-03-28 09:41:12', '2026-03-28 09:41:12'),
(1444, 'App\\Models\\User', 149, 'auth-token', '7add07de0139851b8d4357c4869af1f5f8ee6bf1539a6426b331aca7a65feeba', '[\"*\"]', NULL, NULL, '2026-03-28 09:41:41', '2026-03-28 09:41:41'),
(1445, 'App\\Models\\User', 150, 'auth-token', 'b2c7f6dea5b540029dedd744470bc3c21b7b7d8d50809bfeee77191a64c37f51', '[\"*\"]', NULL, NULL, '2026-03-28 09:42:22', '2026-03-28 09:42:22'),
(1446, 'App\\Models\\User', 154, 'auth-token', 'e5ef7225c1e5b33782a9dce0afd1b8de8dda9b97f6bcdbb5f276f34a1b7958b3', '[\"*\"]', NULL, NULL, '2026-03-28 09:42:44', '2026-03-28 09:42:44'),
(1447, 'App\\Models\\User', 151, 'auth-token', '866d1598e8b4a6a4a3b6a35395eaec987d97093ac510fa0ed5372b4c22381332', '[\"*\"]', NULL, NULL, '2026-03-28 09:43:06', '2026-03-28 09:43:06'),
(1448, 'App\\Models\\User', 150, 'auth-token', '09dc30f9301f5a814e7655e1628887ae4b04433792c72c65693c9c3d744fd081', '[\"*\"]', NULL, NULL, '2026-03-28 09:43:30', '2026-03-28 09:43:30'),
(1449, 'App\\Models\\User', 149, 'auth-token', '9bdd7d8ee7560dcb439f9cb465ddffc0517c4e4e86e3c34cf2bfbff5740465ce', '[\"*\"]', NULL, NULL, '2026-03-28 09:44:13', '2026-03-28 09:44:13'),
(1450, 'App\\Models\\User', 150, 'auth-token', '1a4d33619a0e657c030b5a4de49158c3f66d5d063b51e8c44c8c971c094a433b', '[\"*\"]', NULL, NULL, '2026-03-28 09:44:41', '2026-03-28 09:44:41'),
(1451, 'App\\Models\\User', 151, 'auth-token', '34f7d173af9c62c4df3172778c96364d062cc220a3a32f41a22f94771225cf32', '[\"*\"]', NULL, NULL, '2026-03-28 09:45:38', '2026-03-28 09:45:38'),
(1452, 'App\\Models\\User', 150, 'auth-token', '8c94bdb0d62d3569307dd2e74e0375936e54e4de7892202ba237cae17310cc6d', '[\"*\"]', NULL, NULL, '2026-03-28 09:47:07', '2026-03-28 09:47:07'),
(1453, 'App\\Models\\User', 157, 'auth-token', '0dca1d7321d4a46dc4e4436b97a8a578c09af35a63fbce24df954595b23132e8', '[\"*\"]', NULL, NULL, '2026-03-29 09:53:59', '2026-03-29 09:53:59'),
(1454, 'App\\Models\\User', 150, 'auth-token', 'b5e153eabdfd747d913bc19106472048d09bb0815687e27efa6aefba17a3d530', '[\"*\"]', NULL, NULL, '2026-03-29 09:57:54', '2026-03-29 09:57:54'),
(1455, 'App\\Models\\User', 31, 'auth-token', '149d7597d76064a308ad16d31604b49c1537196ca26d2caa08b8f5cfedd8ff78', '[\"*\"]', NULL, NULL, '2026-03-29 10:00:15', '2026-03-29 10:00:15'),
(1456, 'App\\Models\\User', 31, 'auth-token', 'b3d9439bc58bb4238419337b1f18270e296323247a912864b7bb6879653216ef', '[\"*\"]', NULL, NULL, '2026-03-29 10:07:09', '2026-03-29 10:07:09'),
(1457, 'App\\Models\\User', 31, 'auth-token', '4cfb3352abcde0f52123ffa3cf03db2f5e1c88e90c521b19ff951a5e8619d490', '[\"*\"]', NULL, NULL, '2026-03-29 10:12:56', '2026-03-29 10:12:56'),
(1458, 'App\\Models\\User', 153, 'auth-token', '3a2063306717131c157b63079e1522cae66591caa14419657c49a3e3fc10fa73', '[\"*\"]', NULL, NULL, '2026-03-29 10:17:03', '2026-03-29 10:17:03'),
(1459, 'App\\Models\\User', 157, 'auth-token', '1fcd6cb10ef9876348e363eb5c3696ba38827cd0549da2a919d4d2c52a69d19b', '[\"*\"]', NULL, NULL, '2026-03-29 10:17:39', '2026-03-29 10:17:39'),
(1460, 'App\\Models\\User', 151, 'auth-token', '83f58f5074c50952cd0214b8c9ca983616b5a2765a917cc74e76760173b7f768', '[\"*\"]', NULL, NULL, '2026-03-29 10:18:23', '2026-03-29 10:18:23'),
(1461, 'App\\Models\\User', 157, 'auth-token', 'd82a60f6d8d4585298ae034fefaccf8f0f9169a67d9b994fedba765747e24cd0', '[\"*\"]', NULL, NULL, '2026-03-29 10:18:52', '2026-03-29 10:18:52'),
(1462, 'App\\Models\\User', 151, 'auth-token', '0a3f72dd90f2ac3154a0264426978e99c23f064918320bdc14b712360c832f5c', '[\"*\"]', NULL, NULL, '2026-03-29 10:20:19', '2026-03-29 10:20:19'),
(1463, 'App\\Models\\User', 157, 'auth-token', '1010bff0b2fed89ce39877ddbbe27a6513b56027a23b4e0f2ae7b1d0b60c867b', '[\"*\"]', NULL, NULL, '2026-03-29 10:24:27', '2026-03-29 10:24:27'),
(1464, 'App\\Models\\User', 151, 'auth-token', '43af180af8aa477b3e9d3c610b636998c43eca4bcd80f094a1d80484bc62d034', '[\"*\"]', NULL, NULL, '2026-03-29 10:25:14', '2026-03-29 10:25:14'),
(1465, 'App\\Models\\User', 31, 'auth-token', '76f7ede59abdbef0a79d54df495dfe85d5d09276acb658e5ebf6af17c58b7d29', '[\"*\"]', NULL, NULL, '2026-03-29 10:25:48', '2026-03-29 10:25:48'),
(1466, 'App\\Models\\User', 151, 'auth-token', 'f7e6670444adc997cf393a806013e54491c50964236f61dabb164966411d8cf4', '[\"*\"]', NULL, NULL, '2026-03-29 10:26:58', '2026-03-29 10:26:58'),
(1467, 'App\\Models\\User', 153, 'auth-token', 'c13289002c8bfb6b3d9434752fd48753f8d9c14f06ae4b168b640d9bf55aabdd', '[\"*\"]', NULL, NULL, '2026-03-29 10:27:33', '2026-03-29 10:27:33'),
(1468, 'App\\Models\\User', 150, 'auth-token', '884f3dd11552eaa89e07d691bd2f71f6e34b1983bc37f00ae3850feaa6bdb145', '[\"*\"]', NULL, NULL, '2026-03-29 10:31:03', '2026-03-29 10:31:03'),
(1469, 'App\\Models\\User', 149, 'auth-token', 'ebc58d280f7907704736590c16e6396a38d73154f26fe1c2410cb21477a9c475', '[\"*\"]', NULL, NULL, '2026-03-29 11:31:51', '2026-03-29 11:31:51'),
(1470, 'App\\Models\\User', 149, 'auth-token', 'f51cafa27d22d0f56077f5162d3ca819bdf3053d9c2bff3550e102bbf0d9cf26', '[\"*\"]', NULL, NULL, '2026-03-29 11:57:43', '2026-03-29 11:57:43'),
(1471, 'App\\Models\\User', 161, 'auth-token', '057f61d3898205e0b1365628648b1c85761d65906d87eb5faab561c1f63fc6f6', '[\"*\"]', NULL, NULL, '2026-03-29 11:59:38', '2026-03-29 11:59:38'),
(1472, 'App\\Models\\User', 31, 'auth-token', '59b29d505dad36d7dde0ae486c20d189fb5d70764a7dca1244a6b7256179a9df', '[\"*\"]', NULL, NULL, '2026-03-29 12:12:12', '2026-03-29 12:12:12'),
(1473, 'App\\Models\\User', 149, 'auth-token', 'c49b14beb1ee81e8aa660c6782719741dbfd53fc99cb4088516b796413d14c5b', '[\"*\"]', NULL, NULL, '2026-03-29 13:00:55', '2026-03-29 13:00:55'),
(1474, 'App\\Models\\User', 161, 'auth-token', '90af6d5350e969c8cfad045df989c610e7b55d82787f87b8725d50d6ce265ca0', '[\"*\"]', NULL, NULL, '2026-03-29 13:04:25', '2026-03-29 13:04:25'),
(1475, 'App\\Models\\User', 31, 'auth-token', '85e76ad7da74e29658b7aa9dbb13acd4d842bc1ffb90ed7f072fe762bf5c5a7b', '[\"*\"]', NULL, NULL, '2026-03-29 13:04:49', '2026-03-29 13:04:49'),
(1476, 'App\\Models\\User', 173, 'auth-token', '7efc31c02a5081b634367c509447679515c9579b6486e6f0db7905d62acbc3da', '[\"*\"]', NULL, NULL, '2026-03-29 13:05:43', '2026-03-29 13:05:43'),
(1477, 'App\\Models\\User', 148, 'auth-token', '2c5bc035eb8f72210e45e63ae46d67345d208dce1b48c719ca6903c957656dd9', '[\"*\"]', NULL, NULL, '2026-03-29 13:06:20', '2026-03-29 13:06:20'),
(1478, 'App\\Models\\User', 31, 'auth-token', 'e9002a4ca33eaacb0104805f7acee4cf5077137c1b9de5f69c8252b266d81cfb', '[\"*\"]', NULL, NULL, '2026-03-29 13:06:41', '2026-03-29 13:06:41'),
(1479, 'App\\Models\\User', 154, 'auth-token', 'e700194ed6cf978059a88a13c0735537495eeca41358b777448b18c129147ad3', '[\"*\"]', NULL, NULL, '2026-03-29 13:08:06', '2026-03-29 13:08:06'),
(1480, 'App\\Models\\User', 150, 'auth-token', '12812d46ead0dfe23587343c0af232399c85a7718933df695ef587edb0036ba4', '[\"*\"]', NULL, NULL, '2026-03-29 13:10:13', '2026-03-29 13:10:13'),
(1481, 'App\\Models\\User', 151, 'auth-token', '1f668dc9e4aba133d2682149ca46238ac5595cd4c26517cb16daf71233cc7283', '[\"*\"]', NULL, NULL, '2026-03-29 13:10:35', '2026-03-29 13:10:35'),
(1482, 'App\\Models\\User', 150, 'auth-token', '6830fd91f42a04afbe90a48fb7a9d4f4f6f2875ac7106cf124040af313584252', '[\"*\"]', NULL, NULL, '2026-03-29 13:11:40', '2026-03-29 13:11:40'),
(1483, 'App\\Models\\User', 152, 'auth-token', 'e295bf52a22c3c05abc935189faf5562a145e17186afb9753f1e2b25100e3d59', '[\"*\"]', NULL, NULL, '2026-03-29 13:12:36', '2026-03-29 13:12:36'),
(1484, 'App\\Models\\User', 150, 'auth-token', '485f0dbb41833a4b9af3a3c51065ff2c095d49a42fa7f3db0359410d2b286620', '[\"*\"]', NULL, NULL, '2026-03-29 13:17:34', '2026-03-29 13:17:34'),
(1485, 'App\\Models\\User', 149, 'auth-token', '3688faa153219db4f3abf8437f8e602facf0cc848da7715d0ef7db59502da445', '[\"*\"]', NULL, NULL, '2026-03-29 13:18:20', '2026-03-29 13:18:20'),
(1486, 'App\\Models\\User', 149, 'auth-token', '6b431c53b70ffbe77d98946528d94934641ab0a4840746ec8148c60ddbfa5102', '[\"*\"]', NULL, NULL, '2026-03-29 13:27:16', '2026-03-29 13:27:16'),
(1487, 'App\\Models\\User', 153, 'auth-token', '8b4dd310cac551a7f45f61d9c4028a0e2735fbad02b3a40d64239e476bc88c2e', '[\"*\"]', NULL, NULL, '2026-03-29 13:42:14', '2026-03-29 13:42:14'),
(1488, 'App\\Models\\User', 149, 'auth-token', '0b94f17f328028643330d2d73c98f37841294f611b50896b63fdae4004eb2cb9', '[\"*\"]', NULL, NULL, '2026-03-29 13:43:01', '2026-03-29 13:43:01'),
(1489, 'App\\Models\\User', 149, 'auth-token', '55083682b5b7698da26b6c5355dbd4bb0000c4d4ccd4af2f607298662d6c3262', '[\"*\"]', NULL, NULL, '2026-03-29 14:01:02', '2026-03-29 14:01:02'),
(1490, 'App\\Models\\User', 153, 'auth-token', '57227d76d9d38bc16959ef15d9a944b3c03873f63d2ef6ff1abfb06502301d33', '[\"*\"]', NULL, NULL, '2026-03-29 14:33:58', '2026-03-29 14:33:58'),
(1491, 'App\\Models\\User', 151, 'auth-token', 'b0e388846412f3d28de49d927ac26af2f546d7fe297b5afe980a35ed472239d4', '[\"*\"]', NULL, NULL, '2026-03-29 14:34:44', '2026-03-29 14:34:44'),
(1492, 'App\\Models\\User', 151, 'auth-token', 'd919eca47471631108b0d2dd159028c81735b2f83f094f119b6e48322a2d5827', '[\"*\"]', NULL, NULL, '2026-03-29 14:36:41', '2026-03-29 14:36:41'),
(1493, 'App\\Models\\User', 150, 'auth-token', 'd0182e0869ea88aa9232065dd5a68c201feaefd06dc0f3cf1d5d18b8ba2c3f11', '[\"*\"]', NULL, NULL, '2026-03-29 14:36:59', '2026-03-29 14:36:59'),
(1494, 'App\\Models\\User', 152, 'auth-token', '5414686299256726247ff061b2095a3592ed7da48026c5d250deb28ccb3b24d4', '[\"*\"]', NULL, NULL, '2026-03-29 14:40:33', '2026-03-29 14:40:33'),
(1495, 'App\\Models\\User', 150, 'auth-token', '31480ad6340ac3924ee7c21ae945ce925658cb11e7d8c29530513ee1962ccdc5', '[\"*\"]', NULL, NULL, '2026-03-29 14:41:38', '2026-03-29 14:41:38'),
(1496, 'App\\Models\\User', 149, 'auth-token', '763a5fee065dc3daabc3d6255f37d08c61118d8bf0ee85b030f157fdfb7d0dfe', '[\"*\"]', NULL, NULL, '2026-03-29 14:42:11', '2026-03-29 14:42:11'),
(1497, 'App\\Models\\User', 150, 'auth-token', '34dc935214df9efa4ab53798b25bbd1e6a43024f2e6b24a440b891f8d6aee382', '[\"*\"]', NULL, NULL, '2026-03-29 14:43:28', '2026-03-29 14:43:28'),
(1498, 'App\\Models\\User', 157, 'auth-token', 'e6c1cc2565e1b3ccc9cbd5cb9e5ecd3f0ebc2b25d88b83fdbe16fb77c3fe1a68', '[\"*\"]', NULL, NULL, '2026-03-29 14:44:20', '2026-03-29 14:44:20'),
(1499, 'App\\Models\\User', 152, 'auth-token', 'bd86e7334f047973a21da0e4c9dd9a799e37b983c115319cc01ec7f750d08460', '[\"*\"]', NULL, NULL, '2026-03-29 14:44:46', '2026-03-29 14:44:46'),
(1500, 'App\\Models\\User', 150, 'auth-token', 'e91ceab19571fbf576bd9bebde50944dd0ec97c6a21790c9405194c0b8129a26', '[\"*\"]', NULL, NULL, '2026-03-29 14:48:21', '2026-03-29 14:48:21'),
(1501, 'App\\Models\\User', 149, 'auth-token', '6430def61590c4b40d537db35db8ee69d231625d51eebe8ee7a2604803dfb1b0', '[\"*\"]', NULL, NULL, '2026-03-29 14:49:05', '2026-03-29 14:49:05'),
(1502, 'App\\Models\\User', 150, 'auth-token', '7e2d74910a4a018e52416968894618d68e5af10b84270fa29b411b24e2f5d899', '[\"*\"]', NULL, NULL, '2026-03-29 14:49:41', '2026-03-29 14:49:41'),
(1503, 'App\\Models\\User', 154, 'auth-token', '943fb024e1542a20956283fba5829da7b3e9594338aeb7ff9162963cd4a850f8', '[\"*\"]', NULL, NULL, '2026-03-29 14:50:11', '2026-03-29 14:50:11'),
(1504, 'App\\Models\\User', 151, 'auth-token', 'b09201e91fca8cd5bb35024d8bb2e8d89500a2c45ad4150d8edf2314c6b428dc', '[\"*\"]', NULL, NULL, '2026-03-29 14:51:58', '2026-03-29 14:51:58'),
(1505, 'App\\Models\\User', 150, 'auth-token', '5164f50432e581317273c8ca569b1dc0d52a7d37c69d169137756d72896ebf89', '[\"*\"]', NULL, NULL, '2026-03-29 14:55:44', '2026-03-29 14:55:44'),
(1506, 'App\\Models\\User', 151, 'auth-token', '8d63378dbf3299589c862539e398d25bc39a85d6ba66eb0946ae370f1caa2f7a', '[\"*\"]', NULL, NULL, '2026-03-29 14:56:09', '2026-03-29 14:56:09'),
(1507, 'App\\Models\\User', 162, 'auth-token', 'cd77efb9b21cd120087cd7eaaea8154737edecad57ff2355c5d324d4b42fd393', '[\"*\"]', NULL, NULL, '2026-03-29 15:11:32', '2026-03-29 15:11:32'),
(1508, 'App\\Models\\User', 31, 'auth-token', 'f57a8026ff2273be106effdee7a7ec8c0852e9ddaa8e569282b95ebce5e718a5', '[\"*\"]', NULL, NULL, '2026-03-29 15:13:44', '2026-03-29 15:13:44'),
(1509, 'App\\Models\\User', 150, 'auth-token', 'a92831261a422c3a7fa4936dcdb5a116b71c5d7dd33161026bb95b383a9c8a9b', '[\"*\"]', NULL, NULL, '2026-03-29 15:14:36', '2026-03-29 15:14:36'),
(1510, 'App\\Models\\User', 151, 'auth-token', '534a5dfc9b76ba5526238d56ef05c070ac1b012f591bc5c14c84b334e74ed240', '[\"*\"]', NULL, NULL, '2026-03-29 15:15:03', '2026-03-29 15:15:03'),
(1511, 'App\\Models\\User', 150, 'auth-token', '4dc127f1773fa91d8eeaab989476bec0ba356e66f96a598bc7ed8b37c2c38294', '[\"*\"]', NULL, NULL, '2026-03-29 15:15:33', '2026-03-29 15:15:33'),
(1512, 'App\\Models\\User', 174, 'auth-token', '218423ee9f62739146f8fd53956a74ee45ac98caad7aff3d04a435f770cbd50b', '[\"*\"]', NULL, NULL, '2026-03-29 15:16:23', '2026-03-29 15:16:23'),
(1513, 'App\\Models\\User', 158, 'auth-token', '66f71387feac1bc75c3d073e313cb8839cc0be39a51e598b00150ae97784e39e', '[\"*\"]', NULL, NULL, '2026-03-29 15:16:42', '2026-03-29 15:16:42'),
(1514, 'App\\Models\\User', 158, 'auth-token', '56cfc17cd977f77af8b31444bd0c4eaaaec73c850199df651a227352dbfcde9f', '[\"*\"]', NULL, NULL, '2026-03-29 15:25:55', '2026-03-29 15:25:55'),
(1515, 'App\\Models\\User', 152, 'auth-token', 'd768138c3e7b04c20b0bc5741297427a9d9224c6b627d9a44018599b5f18a8f6', '[\"*\"]', NULL, NULL, '2026-03-29 15:28:44', '2026-03-29 15:28:44'),
(1516, 'App\\Models\\User', 150, 'auth-token', '4e8db5d4faa7f56fdb379392f5f6302cfe870c48c285a5b04123698cdb2fcadf', '[\"*\"]', NULL, NULL, '2026-03-29 15:29:33', '2026-03-29 15:29:33'),
(1517, 'App\\Models\\User', 149, 'auth-token', '687fd4a46e285ae65a9b34d838ba9a65a1c39b8802b70b3315331cc3d5b23bb3', '[\"*\"]', NULL, NULL, '2026-03-29 15:31:00', '2026-03-29 15:31:00'),
(1518, 'App\\Models\\User', 151, 'auth-token', '1b98b355022ee403523a17789aca25122afe1961742685cbe9dc2e5c1aad2eab', '[\"*\"]', NULL, NULL, '2026-03-29 15:34:27', '2026-03-29 15:34:27'),
(1519, 'App\\Models\\User', 150, 'auth-token', 'f97353a575790f425575db1d29f1cc37de435d735e0a78662b838541558cae9d', '[\"*\"]', NULL, NULL, '2026-03-29 15:35:03', '2026-03-29 15:35:03'),
(1520, 'App\\Models\\User', 151, 'auth-token', '3f82f3fcf4af3408277faad7f4f38522e45085b246a1c52b9a264261a2897a05', '[\"*\"]', NULL, NULL, '2026-03-29 15:42:14', '2026-03-29 15:42:14'),
(1521, 'App\\Models\\User', 31, 'auth-token', 'd07dc2fdab3d265bbd022456c7fd680b9b85c2242f16d3d5559c2e72aa55822f', '[\"*\"]', NULL, NULL, '2026-03-29 15:42:58', '2026-03-29 15:42:58'),
(1522, 'App\\Models\\User', 151, 'auth-token', 'df83d66ff11da56c85a069b14dd5b4400d1fc43a0877e7b855baa635d260b824', '[\"*\"]', NULL, NULL, '2026-03-29 15:44:03', '2026-03-29 15:44:03'),
(1523, 'App\\Models\\User', 150, 'auth-token', '1d8410ddfc49d601f86ff8fafd9fa2f5665870116458b9e168e09e7d550b8e6a', '[\"*\"]', NULL, NULL, '2026-03-29 15:44:50', '2026-03-29 15:44:50'),
(1524, 'App\\Models\\User', 152, 'auth-token', '316b8e4e241f6250ec762f5b81f61a357ae15fde2d6043454e438c5d24103081', '[\"*\"]', NULL, NULL, '2026-03-29 15:45:47', '2026-03-29 15:45:47'),
(1525, 'App\\Models\\User', 158, 'auth-token', '55680a14b3d7827242de3e54e936a14c3d01713f5b5a41a3a357c089e110cdd4', '[\"*\"]', NULL, NULL, '2026-03-29 15:47:31', '2026-03-29 15:47:31'),
(1526, 'App\\Models\\User', 150, 'auth-token', '29975700bd5c5f39be8831cf990fae0d6538eac96352d0d12b63f38db38983a3', '[\"*\"]', NULL, NULL, '2026-03-29 15:48:25', '2026-03-29 15:48:25'),
(1527, 'App\\Models\\User', 149, 'auth-token', '419a9d776ae43a9807e6078a36c0168f82b1b7e21337d62e0c7c34c4e8b8a030', '[\"*\"]', NULL, NULL, '2026-03-29 15:50:19', '2026-03-29 15:50:19'),
(1528, 'App\\Models\\User', 150, 'auth-token', 'ec0f8d8713196167f5705d2a5884c2754c1c4e5f4142e395835f86fa475c3661', '[\"*\"]', NULL, NULL, '2026-03-29 15:51:22', '2026-03-29 15:51:22'),
(1529, 'App\\Models\\User', 149, 'auth-token', 'c5e1aba4fcb7f0d160adbf7cc10cf2c2f69cb5ebe7dca317ccd64fa89ff27659', '[\"*\"]', NULL, NULL, '2026-03-29 15:55:45', '2026-03-29 15:55:45'),
(1530, 'App\\Models\\User', 150, 'auth-token', '95b0872462cb94d88fa835709428b522b381275296f0caf806014cb51599a2c7', '[\"*\"]', NULL, NULL, '2026-03-29 15:56:13', '2026-03-29 15:56:13'),
(1531, 'App\\Models\\User', 151, 'auth-token', '77172daf236866afec5348de24e2d7f0f8ea3ae30e7bcbf688f307c062e53f04', '[\"*\"]', NULL, NULL, '2026-03-29 16:00:06', '2026-03-29 16:00:06'),
(1532, 'App\\Models\\User', 31, 'auth-token', '971da8fa08659f02dab8212195f3fb67049a1210a7f3f75c8e2fe43d735f9369', '[\"*\"]', NULL, NULL, '2026-03-29 16:02:25', '2026-03-29 16:02:25'),
(1533, 'App\\Models\\User', 151, 'auth-token', 'd42a4ea26ec8272905755e65f1f20b2a04dc0baec1db43569fe92829fef54d5d', '[\"*\"]', NULL, NULL, '2026-03-29 16:03:02', '2026-03-29 16:03:02'),
(1534, 'App\\Models\\User', 150, 'auth-token', 'bbd8183ffe744f2063000be5353a1fa260a991cba757c5c709db2a7d5ada2874', '[\"*\"]', NULL, NULL, '2026-03-29 16:03:44', '2026-03-29 16:03:44'),
(1535, 'App\\Models\\User', 152, 'auth-token', '5b6f10a9e71ce1e580d9677d3766c569a578f3889dadb04073235c878ba4b30d', '[\"*\"]', NULL, NULL, '2026-03-29 16:04:13', '2026-03-29 16:04:13'),
(1536, 'App\\Models\\User', 158, 'auth-token', '100805c988814e8909adca0f4e1a0f97dfb6c95c897171d058cfdb62c78308b5', '[\"*\"]', NULL, NULL, '2026-03-29 16:05:16', '2026-03-29 16:05:16'),
(1537, 'App\\Models\\User', 150, 'auth-token', '1023d89140956c91aec68f1dd244a69d9fe6ae1265256fa9a2531d667806fd2f', '[\"*\"]', NULL, NULL, '2026-03-29 16:05:59', '2026-03-29 16:05:59'),
(1538, 'App\\Models\\User', 152, 'auth-token', '45379b873d47f6d215ac4b50c157e09a7cfa1df563094bafa97ffd93f886b96c', '[\"*\"]', NULL, NULL, '2026-03-29 16:10:28', '2026-03-29 16:10:28'),
(1539, 'App\\Models\\User', 152, 'auth-token', 'c90017654b68e05a5c37ec329a495550c78348c2c5428a0bd2b8768df24e629c', '[\"*\"]', NULL, NULL, '2026-03-29 16:11:41', '2026-03-29 16:11:41'),
(1540, 'App\\Models\\User', 151, 'auth-token', 'ac9edaed66fba7eb4e30e9252b9386dd03897d82536fc977b43e21fb2d06b260', '[\"*\"]', NULL, NULL, '2026-03-29 16:12:33', '2026-03-29 16:12:33'),
(1541, 'App\\Models\\User', 31, 'auth-token', 'b6f7b7ac83c50a10976ed377e1cacc14d8c938c50eb902d7dbd5e1f5ce29603b', '[\"*\"]', NULL, NULL, '2026-03-29 16:13:10', '2026-03-29 16:13:10'),
(1542, 'App\\Models\\User', 150, 'auth-token', '4d1d81ce5832d67e7b600c1429db0d53bdf034a01c4a1e908975bdb6189958ef', '[\"*\"]', NULL, NULL, '2026-03-29 16:13:36', '2026-03-29 16:13:36'),
(1543, 'App\\Models\\User', 151, 'auth-token', '12911b852306255b5db4f62dabd979491c50b531c09d3761216ffdc14d2d6164', '[\"*\"]', NULL, NULL, '2026-03-29 16:14:04', '2026-03-29 16:14:04'),
(1544, 'App\\Models\\User', 150, 'auth-token', '820c2996222afd2e1f04b9e6caf202d9be138a6ee1860359d8a3196847545beb', '[\"*\"]', NULL, NULL, '2026-03-29 16:14:57', '2026-03-29 16:14:57'),
(1545, 'App\\Models\\User', 158, 'auth-token', '3e5cf0a6b1e776f3163612f816e6b7d3bd42fa8e8289b7427ae9e0c4d6ffdfa4', '[\"*\"]', NULL, NULL, '2026-03-29 16:15:23', '2026-03-29 16:15:23'),
(1546, 'App\\Models\\User', 152, 'auth-token', '150d3245aeeadad76959b446e144a70c6de4c4b8399727e1c6e3bf4b44769fc7', '[\"*\"]', NULL, NULL, '2026-03-29 16:16:08', '2026-03-29 16:16:08'),
(1547, 'App\\Models\\User', 150, 'auth-token', '5384ff3291865891be063682650dfa1a8b5e048b12401493382f6eba6d99f366', '[\"*\"]', NULL, NULL, '2026-03-29 16:17:08', '2026-03-29 16:17:08'),
(1548, 'App\\Models\\User', 151, 'auth-token', '32935e0dab16a3c00990d4547346ce04ae0dd1cb0c4dc393d03c67c450b9376b', '[\"*\"]', NULL, NULL, '2026-03-29 16:19:13', '2026-03-29 16:19:13'),
(1549, 'App\\Models\\User', 31, 'auth-token', 'dd9e40621544e6e88579257bf4ebf4934dacfa8bec05f6af18183c727d18ba58', '[\"*\"]', NULL, NULL, '2026-03-29 16:19:56', '2026-03-29 16:19:56'),
(1550, 'App\\Models\\User', 151, 'auth-token', '8eb9a6d58c4065879e3f8063c68cb939c8ca18d4d8981f55d18994ba7b7b0565', '[\"*\"]', NULL, NULL, '2026-03-29 16:20:27', '2026-03-29 16:20:27'),
(1551, 'App\\Models\\User', 150, 'auth-token', '268bff493ca2c3048df245e53b4bbec0d1e4f64ae4bcde49dfbe337f847a8244', '[\"*\"]', NULL, NULL, '2026-03-29 16:20:57', '2026-03-29 16:20:57'),
(1552, 'App\\Models\\User', 152, 'auth-token', 'af384885483dd000a5246a0fa0acab45cc4b37314a692ecb46017785c7a2ffc9', '[\"*\"]', NULL, NULL, '2026-03-29 16:21:23', '2026-03-29 16:21:23'),
(1553, 'App\\Models\\User', 158, 'auth-token', 'b38c1aab53a9bff571f880a331c0d65b593843940734311422c9ec13b8ea5d1b', '[\"*\"]', NULL, NULL, '2026-03-29 16:22:06', '2026-03-29 16:22:06'),
(1554, 'App\\Models\\User', 150, 'auth-token', '35a496e7848eb27472d5d6697f01a8b0bbdeedbe97fef4ddd816d447ccab0e21', '[\"*\"]', NULL, NULL, '2026-03-29 16:22:49', '2026-03-29 16:22:49'),
(1555, 'App\\Models\\User', 152, 'auth-token', '12a89bf2b104b35e0fedf2031150f5b37ce7b5530de313457712ae9a3c3eeb8c', '[\"*\"]', NULL, NULL, '2026-03-29 16:24:10', '2026-03-29 16:24:10'),
(1556, 'App\\Models\\User', 158, 'auth-token', '196caf16c28f819a82317f4bf57cc8194a8a22d13d378662fdbcc5db5908cd1c', '[\"*\"]', NULL, NULL, '2026-03-29 16:24:35', '2026-03-29 16:24:35'),
(1557, 'App\\Models\\User', 151, 'auth-token', 'f34a576174fc1bb9e1904867eb0616065c4b59c7509fb3dbf8e8e0e6e938a9d7', '[\"*\"]', NULL, NULL, '2026-03-29 16:28:00', '2026-03-29 16:28:00'),
(1558, 'App\\Models\\User', 150, 'auth-token', '4484a99699fbbdc94cabd29f6965346c636e53406f5fa7275e89e65e19c6410f', '[\"*\"]', NULL, NULL, '2026-03-29 16:30:51', '2026-03-29 16:30:51'),
(1559, 'App\\Models\\User', 150, 'auth-token', '8ad51f0aa96b6cddbfd125caf47e64cedc87a4ed091c1b80ef638dcd36921e7f', '[\"*\"]', NULL, NULL, '2026-03-29 16:35:43', '2026-03-29 16:35:43'),
(1560, 'App\\Models\\User', 159, 'auth-token', '8e7bb63ea834f527fc7b38011e9973d263903bbe6ed4125d45a81dd2bfda8227', '[\"*\"]', NULL, NULL, '2026-03-29 16:38:18', '2026-03-29 16:38:18'),
(1561, 'App\\Models\\User', 151, 'auth-token', 'fb65e502336b01129f6030122e9b487f80716e22de0cd3c90ab35c284b16bf20', '[\"*\"]', NULL, NULL, '2026-03-29 16:42:17', '2026-03-29 16:42:17'),
(1562, 'App\\Models\\User', 159, 'auth-token', '944a7805fa6532948df0fe3cdb8bc0b81f1186d2ab4c2450ff61cbfd9f0ff666', '[\"*\"]', NULL, NULL, '2026-03-29 16:43:18', '2026-03-29 16:43:18'),
(1563, 'App\\Models\\User', 151, 'auth-token', '0dca5284fcb519bd1515ec308226ff4c941ec594ee206e1fcfca97a3a680e0b1', '[\"*\"]', NULL, NULL, '2026-03-29 16:44:42', '2026-03-29 16:44:42'),
(1564, 'App\\Models\\User', 31, 'auth-token', '732fa252a16db00a38eeaebe1839fb1db1cece0091225b6d27ca8d6815f94bc2', '[\"*\"]', NULL, NULL, '2026-03-29 16:47:39', '2026-03-29 16:47:39'),
(1565, 'App\\Models\\User', 151, 'auth-token', '59e6ca1e4e896dd0b15b8546cb8912379d4fbf9f1286d9d45e70b3a50020e56a', '[\"*\"]', NULL, NULL, '2026-03-29 16:48:05', '2026-03-29 16:48:05'),
(1566, 'App\\Models\\User', 150, 'auth-token', '2b976053cc19dda8286dd860d223c1e29fdcdbd83f93a9abca98fabf6248b34f', '[\"*\"]', NULL, NULL, '2026-03-29 16:48:28', '2026-03-29 16:48:28'),
(1567, 'App\\Models\\User', 152, 'auth-token', 'e2a87c8c2eeed41e0812b2b57fa37e58ad981fba26641affc154227626e9d6c4', '[\"*\"]', NULL, NULL, '2026-03-29 16:48:51', '2026-03-29 16:48:51'),
(1568, 'App\\Models\\User', 158, 'auth-token', 'd2be7e0e323a42930572014fe74ade8d0727dd473a4027bf15fe7e3946fd9898', '[\"*\"]', NULL, NULL, '2026-03-29 16:49:27', '2026-03-29 16:49:27'),
(1569, 'App\\Models\\User', 150, 'auth-token', '060d8f49fb5c6b375a3e29536d7071ffad5080a3febcb6f0701c78f5a573ec65', '[\"*\"]', NULL, NULL, '2026-03-29 16:50:11', '2026-03-29 16:50:11'),
(1570, 'App\\Models\\User', 152, 'auth-token', 'a2003062575f3787aa68c2f0c0d63c5096ce3dcb2d63698f0268db72e57e5064', '[\"*\"]', NULL, NULL, '2026-03-29 16:51:13', '2026-03-29 16:51:13'),
(1571, 'App\\Models\\User', 151, 'auth-token', '132e9495c4f35e551c325624aa0ed5a5c0a67b9440a643b053035db6a05371e1', '[\"*\"]', NULL, NULL, '2026-03-29 16:53:06', '2026-03-29 16:53:06'),
(1572, 'App\\Models\\User', 150, 'auth-token', '6d7a481abd20b3e7a69f3b780e4450c98526d1af36efde72a141d03d466a36ac', '[\"*\"]', NULL, NULL, '2026-03-29 16:53:16', '2026-03-29 16:53:16'),
(1573, 'App\\Models\\User', 149, 'auth-token', 'a86e460fa099856a7e9b1d05315a8f3131f75c7978eccdec1097e1ab7ce50fde', '[\"*\"]', NULL, NULL, '2026-03-29 16:53:40', '2026-03-29 16:53:40'),
(1574, 'App\\Models\\User', 151, 'auth-token', 'ed428ddbfb5053bfb48189d38c9a415353e0869e9c71046e824a7427ac23b684', '[\"*\"]', NULL, NULL, '2026-03-29 16:57:39', '2026-03-29 16:57:39'),
(1575, 'App\\Models\\User', 31, 'auth-token', 'd0e7d2b43455637ebcdcd9de9685737ba3e7ecd2f50dbddb355c7d46aaade330', '[\"*\"]', NULL, NULL, '2026-03-29 16:58:30', '2026-03-29 16:58:30'),
(1576, 'App\\Models\\User', 151, 'auth-token', '47c20a8e04cfc7221df391a47b6f88526097c0ddb2ca73348bd07276063070d1', '[\"*\"]', NULL, NULL, '2026-03-29 16:59:01', '2026-03-29 16:59:01'),
(1577, 'App\\Models\\User', 150, 'auth-token', 'cdb10a92f0eca9676de6c7cf6c2cdb39df21ff8e6a65e18ad795594f0a6aa138', '[\"*\"]', NULL, NULL, '2026-03-29 16:59:30', '2026-03-29 16:59:30'),
(1578, 'App\\Models\\User', 158, 'auth-token', '1ddd308aa22f65203d496112f0dc2d80577ca0a19f6197f8ce120fbccc9f696d', '[\"*\"]', NULL, NULL, '2026-03-29 16:59:56', '2026-03-29 16:59:56'),
(1579, 'App\\Models\\User', 158, 'auth-token', '4ce76f8c7a0def088e8f3a1e9a3446f2761b22d09f9a810b80781cb4e1a1c628', '[\"*\"]', NULL, NULL, '2026-03-29 17:00:31', '2026-03-29 17:00:31'),
(1580, 'App\\Models\\User', 152, 'auth-token', '4d7d97c4dba8b79d70f73ece157d934de220305ac0610a41d58e78d5da84a3ad', '[\"*\"]', NULL, NULL, '2026-03-29 17:00:52', '2026-03-29 17:00:52'),
(1581, 'App\\Models\\User', 150, 'auth-token', 'f67c4c3e42ee775f962a328a9abc783c2324a8b1b24ce4425eae6b28673bec6f', '[\"*\"]', NULL, NULL, '2026-03-29 17:02:37', '2026-03-29 17:02:37'),
(1582, 'App\\Models\\User', 149, 'auth-token', '5bdffdb9ee60ccdfc243a094c45271d08812deb2bf9f3d8cc5cb2482c373965e', '[\"*\"]', NULL, NULL, '2026-03-29 17:03:20', '2026-03-29 17:03:20'),
(1583, 'App\\Models\\User', 150, 'auth-token', '769568c91a0ac0b594f329b82bb7d6acc0950095053691dc6b221171ddfafb7c', '[\"*\"]', NULL, NULL, '2026-03-29 17:07:05', '2026-03-29 17:07:05'),
(1584, 'App\\Models\\User', 150, 'auth-token', '605165c9e192a56b500f195b190cd3595f26881781f8f38bc55de411468a8d30', '[\"*\"]', NULL, NULL, '2026-03-29 17:16:59', '2026-03-29 17:16:59'),
(1585, 'App\\Models\\User', 150, 'auth-token', '721094f75289fbb1e23ba59398fb73996fad82a2d261de4156b88047358d0e77', '[\"*\"]', NULL, NULL, '2026-03-29 17:21:19', '2026-03-29 17:21:19'),
(1586, 'App\\Models\\User', 149, 'auth-token', 'ee5777f1f7c9f20e48b9f23c11dd8b19dbfc1dd313dfacd5ce23d0abd9bdfd43', '[\"*\"]', NULL, NULL, '2026-03-29 17:22:11', '2026-03-29 17:22:11'),
(1587, 'App\\Models\\User', 150, 'auth-token', 'f14b3a5d168fedd2b27eef71b7117440a10b696e8be0ffdeb81ae5c15b98f334', '[\"*\"]', NULL, NULL, '2026-03-29 17:22:45', '2026-03-29 17:22:45'),
(1588, 'App\\Models\\User', 158, 'auth-token', '5602be0d9b3d5612f90e536b1a51475e3bd180d52e102a7e2b8b8c3ca3ecd69a', '[\"*\"]', NULL, NULL, '2026-03-29 17:23:13', '2026-03-29 17:23:13'),
(1589, 'App\\Models\\User', 152, 'auth-token', '25d323d8247b10057330a5c1abc624e7c50b0682b1d297d593287aa338a8353a', '[\"*\"]', NULL, NULL, '2026-03-29 17:23:32', '2026-03-29 17:23:32'),
(1590, 'App\\Models\\User', 151, 'auth-token', '595ab59a05d38e00b762d56ccd75d7a572bb4c7dc22740e38ebcd833e298152d', '[\"*\"]', NULL, NULL, '2026-03-29 17:27:13', '2026-03-29 17:27:13'),
(1591, 'App\\Models\\User', 31, 'auth-token', '03f82ad9e49910624858e55e8af6e6e9eb66b1eb4d58383653c1e03ed18ae2cc', '[\"*\"]', NULL, NULL, '2026-03-29 17:27:54', '2026-03-29 17:27:54'),
(1592, 'App\\Models\\User', 151, 'auth-token', 'fd6f4c20be2e102eebfc5e32a7d2497c75c6ea019ff0f80bdb62157ab86667dc', '[\"*\"]', NULL, NULL, '2026-03-29 17:28:27', '2026-03-29 17:28:27'),
(1593, 'App\\Models\\User', 150, 'auth-token', '719a0ab70f067b8361755d7caf0bc214d70cfc43c10ea5af58aaff96a5050ad6', '[\"*\"]', NULL, NULL, '2026-03-29 17:28:57', '2026-03-29 17:28:57'),
(1594, 'App\\Models\\User', 152, 'auth-token', 'db16605a1ab00e90e7ab53b045f413f3bc1dcbe1fddc71013af6fbbe59c9fa45', '[\"*\"]', NULL, NULL, '2026-03-29 17:29:25', '2026-03-29 17:29:25'),
(1595, 'App\\Models\\User', 158, 'auth-token', 'f92068a25047ce5b7d103e8e3f30b7aa8c9d772dc417eeb25f28ea7da4ad4361', '[\"*\"]', NULL, NULL, '2026-03-29 17:29:58', '2026-03-29 17:29:58'),
(1596, 'App\\Models\\User', 150, 'auth-token', '0431001b420eca653f94989c77dacd8ea7c9707e1d143b530fcfcf68c1026d95', '[\"*\"]', NULL, NULL, '2026-03-29 17:30:30', '2026-03-29 17:30:30'),
(1597, 'App\\Models\\User', 149, 'auth-token', 'a26f83289c30cf32aabc6940d8b1329f63f2b26e2aee6c6604a8bbd22cb9639c', '[\"*\"]', NULL, NULL, '2026-03-29 17:31:19', '2026-03-29 17:31:19'),
(1598, 'App\\Models\\User', 150, 'auth-token', '2f9ad102d09ed73643dff05eb887636374f1202789146830dd1073ffea97093d', '[\"*\"]', NULL, NULL, '2026-03-29 17:32:17', '2026-03-29 17:32:17'),
(1599, 'App\\Models\\User', 152, 'auth-token', '200abfc4c04d23c21a7bb961b401a9494ec0b961836812404f20784d1e4f409e', '[\"*\"]', NULL, NULL, '2026-03-29 17:35:27', '2026-03-29 17:35:27'),
(1600, 'App\\Models\\User', 150, 'auth-token', '747fa03fcc88d909e392031d5afcd8eb71fcc2b1f49d5099dc70117660f720ef', '[\"*\"]', NULL, NULL, '2026-03-29 17:36:25', '2026-03-29 17:36:25'),
(1601, 'App\\Models\\User', 149, 'auth-token', '6910cb9c2b4c09961a7ec8d0911da7d6aeabd61f7092112a0509424e61b2d601', '[\"*\"]', NULL, NULL, '2026-03-29 17:39:53', '2026-03-29 17:39:53'),
(1602, 'App\\Models\\User', 150, 'auth-token', 'e484ec4211eefd0c732eb6df25d4f79edda2365dd33d824398b0ec4333da7261', '[\"*\"]', NULL, NULL, '2026-03-29 17:40:45', '2026-03-29 17:40:45'),
(1603, 'App\\Models\\User', 152, 'auth-token', '13d17768e69bc74ec6ed17a69c6098971fc9f071feef9d6d822f0a3a73087bc5', '[\"*\"]', NULL, NULL, '2026-03-29 17:41:43', '2026-03-29 17:41:43'),
(1604, 'App\\Models\\User', 158, 'auth-token', 'e7609ed04ccd1b0d54fd7464953e6daf4fd45e927e6d40166a0eee61ace7ae79', '[\"*\"]', NULL, NULL, '2026-03-29 17:42:03', '2026-03-29 17:42:03'),
(1605, 'App\\Models\\User', 150, 'auth-token', 'de107368ec18db1b428b3ea724ffca3e533f17f327d42d06794244e0dfd92e73', '[\"*\"]', NULL, NULL, '2026-03-29 17:42:34', '2026-03-29 17:42:34'),
(1606, 'App\\Models\\User', 149, 'auth-token', 'ad7f7ef14f8bb3a10bdd54f224004a31c70d5e757b5196972d1cd9b129f54978', '[\"*\"]', NULL, NULL, '2026-03-29 17:43:09', '2026-03-29 17:43:09'),
(1607, 'App\\Models\\User', 150, 'auth-token', '05c55a77e90d44292f654675a43845912b99c95de533abfa77a0844944b49abc', '[\"*\"]', NULL, NULL, '2026-03-29 17:43:43', '2026-03-29 17:43:43'),
(1608, 'App\\Models\\User', 154, 'auth-token', '48deb1dd63d82f177537a70df5ea801249cabfb42315b7804c8fbfead78bfc24', '[\"*\"]', NULL, NULL, '2026-03-29 17:44:13', '2026-03-29 17:44:13'),
(1609, 'App\\Models\\User', 151, 'auth-token', '353309ed465276f00bd463968ba58b7eb247da4f593d72f48f107b68a2b8fbe9', '[\"*\"]', NULL, NULL, '2026-03-29 17:51:43', '2026-03-29 17:51:43'),
(1610, 'App\\Models\\User', 151, 'auth-token', 'd15720b5044d7711b683b66bf03ada20898dbf76b502fb5068dfa23a23a17fed', '[\"*\"]', NULL, NULL, '2026-03-29 17:52:00', '2026-03-29 17:52:00'),
(1611, 'App\\Models\\User', 31, 'auth-token', 'cab1421107067d82ab62efd1b788d311f48b3b5628fa82b93589f71e682c2fec', '[\"*\"]', NULL, NULL, '2026-03-29 17:53:25', '2026-03-29 17:53:25'),
(1612, 'App\\Models\\User', 151, 'auth-token', '2e0d48819874d032b1faf50dd5e9fa808e918182e2a96679747ac4f64b89c2e6', '[\"*\"]', NULL, NULL, '2026-03-29 17:53:58', '2026-03-29 17:53:58'),
(1613, 'App\\Models\\User', 150, 'auth-token', '3ff5f596ccf34c099f5210ee6bd0dc38cdeae33a280819032e65ef1dccb62939', '[\"*\"]', NULL, NULL, '2026-03-29 17:54:33', '2026-03-29 17:54:33'),
(1614, 'App\\Models\\User', 152, 'auth-token', 'd5a4979e431d263d4c64e61005492bc808b6d17cccdb20b030dadcac78738bf8', '[\"*\"]', NULL, NULL, '2026-03-29 17:55:43', '2026-03-29 17:55:43'),
(1615, 'App\\Models\\User', 158, 'auth-token', 'f126484a6ea4b5cb385379008f6652d5497a20731ca860f86b5c413352291c13', '[\"*\"]', NULL, NULL, '2026-03-29 17:56:26', '2026-03-29 17:56:26'),
(1616, 'App\\Models\\User', 150, 'auth-token', 'f2ed170212622a9eed3f9cf4b3fc64bbe2f527a381ac34a0a028f9babfc5e46e', '[\"*\"]', NULL, NULL, '2026-03-29 17:57:06', '2026-03-29 17:57:06'),
(1617, 'App\\Models\\User', 149, 'auth-token', '8b67f988cb6b70d95412feae720d7e5036db1e5d32ba0700d1897803353797b1', '[\"*\"]', NULL, NULL, '2026-03-29 17:58:15', '2026-03-29 17:58:15'),
(1618, 'App\\Models\\User', 150, 'auth-token', 'd5f45fa052ea950053b942c4af8ced1d2d2459ad665e298a2695a0be664db782', '[\"*\"]', NULL, NULL, '2026-03-29 17:59:01', '2026-03-29 17:59:01'),
(1619, 'App\\Models\\User', 152, 'auth-token', '91f43993cc261b4987268e910deacbd9de0efb2bf4dc4fb0badd82d8320f7431', '[\"*\"]', NULL, NULL, '2026-03-29 17:59:31', '2026-03-29 17:59:31'),
(1620, 'App\\Models\\User', 150, 'auth-token', '8e884c3851595c5a5293b09003b5ed64d66693accfab218894537bd4a6c4f465', '[\"*\"]', NULL, NULL, '2026-03-29 17:59:56', '2026-03-29 17:59:56'),
(1621, 'App\\Models\\User', 154, 'auth-token', '772dca15a503c4631cdcc8c9aa907f77885415e5c577a4f3b6f6fbb84cc41c92', '[\"*\"]', NULL, NULL, '2026-03-29 18:00:39', '2026-03-29 18:00:39'),
(1622, 'App\\Models\\User', 149, 'auth-token', 'ede76efec1712bb5e3fd49b577c2a4d2f15fed85fb3a1ed0869ad7282fee8c5f', '[\"*\"]', NULL, NULL, '2026-03-29 18:01:00', '2026-03-29 18:01:00'),
(1623, 'App\\Models\\User', 150, 'auth-token', '88c4a12c86bab427db7163bc845d1609be737f1967f5f7cee77dfc4e0e034a9b', '[\"*\"]', NULL, NULL, '2026-03-29 18:01:50', '2026-03-29 18:01:50'),
(1624, 'App\\Models\\User', 154, 'auth-token', 'e0acffa662aa1042c6f811eee8df76a844486aaabb11267ebfbd083665271dc8', '[\"*\"]', NULL, NULL, '2026-03-29 18:02:38', '2026-03-29 18:02:38'),
(1625, 'App\\Models\\User', 159, 'auth-token', 'd1f59e451a70a1a0176bd34fb2fd25f5c9665b1cc50b1369f32690f7d434f4e7', '[\"*\"]', NULL, NULL, '2026-03-29 18:17:40', '2026-03-29 18:17:40'),
(1626, 'App\\Models\\User', 159, 'auth-token', '7fb49b24343075f914f0877538739020fadb440a77eee4019a40680a98c73f6b', '[\"*\"]', NULL, NULL, '2026-03-29 18:18:36', '2026-03-29 18:18:36'),
(1627, 'App\\Models\\User', 159, 'auth-token', 'd4c22e1fa4135d5f88e0755a476492fdf5b912fbacfdf3c3d8ccff5adc24a122', '[\"*\"]', NULL, NULL, '2026-03-29 18:40:03', '2026-03-29 18:40:03'),
(1628, 'App\\Models\\User', 159, 'auth-token', '5ef0ecc473f172709ea69a7dd75518b26de88a67ab0229cbf6ee28abd28cff1a', '[\"*\"]', NULL, NULL, '2026-03-29 18:42:22', '2026-03-29 18:42:22'),
(1629, 'App\\Models\\User', 159, 'auth-token', '624f038dbc6536128a6c81f72a2d233adcfa229222bf5a35ded59b7cd687be0f', '[\"*\"]', NULL, NULL, '2026-03-29 19:01:46', '2026-03-29 19:01:46'),
(1630, 'App\\Models\\User', 147, 'auth-token', '36e61dbd7f0d406475d8519e2dc32331910e7379c4fcdd2e741ac5bc1c9b6ce3', '[\"*\"]', NULL, NULL, '2026-03-29 19:02:17', '2026-03-29 19:02:17'),
(1631, 'App\\Models\\User', 147, 'auth-token', '66fa004543a2daf4a2e531ee88def00c525452ec07eca12681bd7b5244e46a39', '[\"*\"]', NULL, NULL, '2026-03-30 01:49:11', '2026-03-30 01:49:11'),
(1632, 'App\\Models\\User', 149, 'auth-token', '66f5df03dceafaee071410c5ba6c55e5961e83e69490ccc89a1c3ac16d82f8aa', '[\"*\"]', NULL, NULL, '2026-03-30 02:06:58', '2026-03-30 02:06:58'),
(1633, 'App\\Models\\User', 147, 'auth-token', 'bd88b5be1862d8949a9ec98a83e3bc6d33464bd15beabb6e4c2ca8422178c50b', '[\"*\"]', NULL, NULL, '2026-03-30 02:14:15', '2026-03-30 02:14:15'),
(1634, 'App\\Models\\User', 159, 'auth-token', 'cde6e86e8ce24c202e2c7d6972a08519629cac37b7dcc5f8437a728fd9e20f23', '[\"*\"]', NULL, NULL, '2026-03-30 02:14:34', '2026-03-30 02:14:34'),
(1635, 'App\\Models\\User', 149, 'auth-token', '1e76967402bcdb4f3f35f0e6f1060a0964d45226f3ebfbba518f133031cacaee', '[\"*\"]', NULL, NULL, '2026-03-30 02:19:38', '2026-03-30 02:19:38'),
(1636, 'App\\Models\\User', 149, 'auth-token', '04cd7d5d02532b7a8b99abd8ac9aa0d5b9b04adb160018e450b108706841382c', '[\"*\"]', NULL, NULL, '2026-03-30 03:07:32', '2026-03-30 03:07:32'),
(1637, 'App\\Models\\User', 150, 'auth-token', '87ff2af47603a4924b19abc52854933ff47d303f89aa0a123fffc20650229f12', '[\"*\"]', NULL, NULL, '2026-03-30 03:13:26', '2026-03-30 03:13:26'),
(1638, 'App\\Models\\User', 150, 'auth-token', '64bd9895893d3bcf3068a8bb1c98d06e304177aed8933a37f66377d964a82fce', '[\"*\"]', NULL, NULL, '2026-03-30 03:24:03', '2026-03-30 03:24:03'),
(1639, 'App\\Models\\User', 147, 'auth-token', '6b60ff635f243ac2d1dd912a0a3e703549997633d4bd8f428b043dc221dd71b0', '[\"*\"]', NULL, NULL, '2026-03-30 03:29:10', '2026-03-30 03:29:10'),
(1640, 'App\\Models\\User', 151, 'auth-token', 'f9169ee31ed63b5199d240050d71160fc035e32ec1bb4c41e489ef91d616c6e1', '[\"*\"]', NULL, NULL, '2026-03-30 03:30:00', '2026-03-30 03:30:00'),
(1641, 'App\\Models\\User', 150, 'auth-token', '7c8c3f66f9d8fc4e4351fe63fd7cc462f1365204b7469890c5b6f9ef5839c6a5', '[\"*\"]', NULL, NULL, '2026-03-30 03:30:39', '2026-03-30 03:30:39');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1642, 'App\\Models\\User', 147, 'auth-token', '320b60dd50d5d5644cc79d87f96ec2f1d29155832f8e8704bc44c6a9c604c674', '[\"*\"]', NULL, NULL, '2026-03-30 03:31:28', '2026-03-30 03:31:28'),
(1643, 'App\\Models\\User', 159, 'auth-token', 'b31174ca966d0b9325fb8f959d5599df06552711d55266a06caaa980ffe5e7ef', '[\"*\"]', NULL, NULL, '2026-03-30 03:36:09', '2026-03-30 03:36:09'),
(1644, 'App\\Models\\User', 162, 'auth-token', 'ec32664b70c9da71f596e84aac132028f831e0b8ca3b596890c4de91df335351', '[\"*\"]', NULL, NULL, '2026-03-30 03:37:36', '2026-03-30 03:37:36'),
(1645, 'App\\Models\\User', 159, 'auth-token', 'b31bb6940571be164ee430e979495f4fb789a4a5812d720dafd061f7e474e649', '[\"*\"]', NULL, NULL, '2026-03-30 03:38:00', '2026-03-30 03:38:00'),
(1646, 'App\\Models\\User', 160, 'auth-token', '997f158ccbe35b8825934a6f770b77f74c29919b7a892588f9386c317287335b', '[\"*\"]', NULL, NULL, '2026-03-30 03:38:20', '2026-03-30 03:38:20'),
(1647, 'App\\Models\\User', 160, 'auth-token', '9040eb61e80b7d8554ca9eb9bf61c51bde531c21d1ff9dc40f283e0f790cf72d', '[\"*\"]', NULL, NULL, '2026-03-30 03:47:10', '2026-03-30 03:47:10'),
(1648, 'App\\Models\\User', 159, 'auth-token', 'ce0c2e95bc4340a47e13b153f69880b46bf32fb0e6116619b64340fd02452068', '[\"*\"]', NULL, NULL, '2026-03-30 03:47:31', '2026-03-30 03:47:31'),
(1649, 'App\\Models\\User', 161, 'auth-token', 'f0e921012d0b8d0794f1b015dea96109065c4d6ad634b8df6649a6b2d3a44eaf', '[\"*\"]', NULL, NULL, '2026-03-30 03:54:46', '2026-03-30 03:54:46'),
(1650, 'App\\Models\\User', 161, 'auth-token', 'dc994b846d41a12e91196b3908640c3454810b214f6e933e159b4192dd91a3ea', '[\"*\"]', NULL, NULL, '2026-03-30 04:09:07', '2026-03-30 04:09:07'),
(1651, 'App\\Models\\User', 149, 'auth-token', '687cd245fbe5baa114f729b207418a639da707eebcccad038dcfd5d8e84bfc70', '[\"*\"]', NULL, NULL, '2026-03-30 04:13:33', '2026-03-30 04:13:33'),
(1652, 'App\\Models\\User', 149, 'auth-token', '0843051cf05b419b568ea86ae7ad824ca4ef89895f8d77babeab94a3fbcd705d', '[\"*\"]', NULL, NULL, '2026-03-30 04:16:33', '2026-03-30 04:16:33'),
(1653, 'App\\Models\\User', 161, 'auth-token', '638281813796a0d4be048fb83df1575c94283e628605365ee8e8545173fc3b41', '[\"*\"]', NULL, NULL, '2026-03-30 04:16:45', '2026-03-30 04:16:45'),
(1654, 'App\\Models\\User', 161, 'auth-token', '03263bf6e9c8810604b1c24e5545a31f477a29795d61d82b0d7ff4f5e46bde5b', '[\"*\"]', NULL, NULL, '2026-03-30 04:18:55', '2026-03-30 04:18:55'),
(1655, 'App\\Models\\User', 161, 'auth-token', 'd124b4f2aab001b524f1267704859bf9b8bdf11f9038fac4f04a448a532ba1d4', '[\"*\"]', NULL, NULL, '2026-03-30 04:26:13', '2026-03-30 04:26:13'),
(1656, 'App\\Models\\User', 161, 'auth-token', 'a8963db33728f47dc63839619d53c11bb0c0579eab8228ea7a2332f852ab5bfa', '[\"*\"]', NULL, NULL, '2026-03-30 04:29:10', '2026-03-30 04:29:10'),
(1657, 'App\\Models\\User', 161, 'auth-token', '2706ee9e3fc283cf82d1832d02f1b034e56a43dd349953543819aabc460ded26', '[\"*\"]', NULL, NULL, '2026-03-30 04:30:17', '2026-03-30 04:30:17'),
(1658, 'App\\Models\\User', 161, 'auth-token', 'ea560fec0ff35bd2cb19e842425fab08cdd68ded80f41f3a25811a18f86557a1', '[\"*\"]', NULL, NULL, '2026-03-30 04:36:29', '2026-03-30 04:36:29'),
(1659, 'App\\Models\\User', 149, 'auth-token', 'c20fed37134b35b60103dff8284e54ce123131c42696cb6920588a50fee8c8f1', '[\"*\"]', NULL, NULL, '2026-03-30 04:37:01', '2026-03-30 04:37:01'),
(1660, 'App\\Models\\User', 161, 'auth-token', '69ba31f373c26fbc502e7f391f34fdb4be6888358747c16e513c24c2b55b1858', '[\"*\"]', NULL, NULL, '2026-03-30 04:37:19', '2026-03-30 04:37:19'),
(1661, 'App\\Models\\User', 149, 'auth-token', 'c491abfa6062732272dbfea1f246d8ac6459282c6830a111451464c2c43abe74', '[\"*\"]', NULL, NULL, '2026-03-30 04:37:41', '2026-03-30 04:37:41'),
(1662, 'App\\Models\\User', 31, 'auth-token', 'e31e75e7d106fd18cd3391c82ac1d158d12391d3ecf93f0d8d7ede0ee9227967', '[\"*\"]', NULL, NULL, '2026-03-30 04:38:08', '2026-03-30 04:38:08'),
(1663, 'App\\Models\\User', 149, 'auth-token', 'b75c42f086052bedfd2eb988f57b69c56a426c81ef70178d77466cf6acbdd6d2', '[\"*\"]', NULL, NULL, '2026-03-30 04:41:35', '2026-03-30 04:41:35'),
(1664, 'App\\Models\\User', 161, 'auth-token', '56e817946eaafc7ad5fabd25a0e5202f33fc6668312a7ee8e090b23bfd5f9724', '[\"*\"]', NULL, NULL, '2026-03-30 04:42:17', '2026-03-30 04:42:17'),
(1665, 'App\\Models\\User', 161, 'auth-token', '76adb3f493dc5ffec74a43a6550f8ae9650659d71dd85f5eb4c5c34de73aa7cf', '[\"*\"]', NULL, NULL, '2026-03-30 04:44:40', '2026-03-30 04:44:40'),
(1666, 'App\\Models\\User', 31, 'auth-token', 'c9b7664a8e89f227082dcbafbe857ea20176a8717b03b8d47820f572d56f2522', '[\"*\"]', NULL, NULL, '2026-03-30 04:54:55', '2026-03-30 04:54:55'),
(1667, 'App\\Models\\User', 161, 'auth-token', 'f536e246ddb39fc83222f9701f8a29d5623ff39f37b88b6b2b0158aadc5165aa', '[\"*\"]', NULL, NULL, '2026-03-30 04:55:53', '2026-03-30 04:55:53'),
(1668, 'App\\Models\\User', 149, 'auth-token', 'ba4e92ae7545157b9d75562db04f70cf3bdc4e6be9e8e4f45716569ea8845c2e', '[\"*\"]', NULL, NULL, '2026-03-30 05:03:01', '2026-03-30 05:03:01'),
(1669, 'App\\Models\\User', 161, 'auth-token', 'c06fe667f2c0cd5237d52d1885c859b043125bbc918679338acfd2fcf30124f1', '[\"*\"]', NULL, NULL, '2026-03-30 05:05:26', '2026-03-30 05:05:26'),
(1670, 'App\\Models\\User', 31, 'auth-token', 'e52c838f90c730e455cf8c6e2dd50a8d46b1cd2325cfaea7e84ed6cd4a83ad09', '[\"*\"]', NULL, NULL, '2026-03-30 05:06:08', '2026-03-30 05:06:08'),
(1671, 'App\\Models\\User', 31, 'auth-token', 'c43c3391f879d0684cfefa78b04adf42330d1ba2e2ab421301ee20db121a9ab7', '[\"*\"]', NULL, NULL, '2026-03-30 05:16:07', '2026-03-30 05:16:07'),
(1672, 'App\\Models\\User', 151, 'auth-token', 'fb429732e8e0a43407c3062058b7ea1e4baaa91f593a6a75b3ddbddd24544d01', '[\"*\"]', NULL, NULL, '2026-03-30 05:17:37', '2026-03-30 05:17:37'),
(1673, 'App\\Models\\User', 153, 'auth-token', '19ac97f0b76935403acc462536886e59595675ba37b7202a0ce5146cc4c4d8b4', '[\"*\"]', NULL, NULL, '2026-03-30 05:18:20', '2026-03-30 05:18:20'),
(1674, 'App\\Models\\User', 152, 'auth-token', '52e8213d895617e8ab9ea45a624912334f94e7add321c08161558508cac64e9a', '[\"*\"]', NULL, NULL, '2026-03-30 05:20:36', '2026-03-30 05:20:36'),
(1675, 'App\\Models\\User', 152, 'auth-token', 'eb440a649428c49f0fde32680dd76d3fc3f11b1aee2ec7f87cdb369fae9f2124', '[\"*\"]', NULL, NULL, '2026-03-30 05:25:34', '2026-03-30 05:25:34'),
(1676, 'App\\Models\\User', 160, 'auth-token', '07aa8364eada9c0c2d9fa7d4710ea18537c560e5c148bf247de7a005605b4023', '[\"*\"]', NULL, NULL, '2026-03-30 05:25:49', '2026-03-30 05:25:49'),
(1677, 'App\\Models\\User', 31, 'auth-token', 'b4458043513a596b8aada1b42422c2bf3d7269f8014c6e134feb4933d364dbfb', '[\"*\"]', NULL, NULL, '2026-03-30 05:27:53', '2026-03-30 05:27:53'),
(1678, 'App\\Models\\User', 160, 'auth-token', 'e778b5eab3a9f338be13144bf419bcd95551438d4925d8c23abd0e5ea99b0177', '[\"*\"]', NULL, NULL, '2026-03-30 05:28:48', '2026-03-30 05:28:48'),
(1679, 'App\\Models\\User', 157, 'auth-token', 'cf420759f9e8747f3f01fd90983b89bc4bb24afe73b62c43565ace89ceb44e7f', '[\"*\"]', NULL, NULL, '2026-03-30 05:37:31', '2026-03-30 05:37:31'),
(1680, 'App\\Models\\User', 31, 'auth-token', '0c77d0528d540133b8b31b05d8ae7d8ba990649d0fe23724ef37f4b5f5de5b9a', '[\"*\"]', NULL, NULL, '2026-03-30 05:38:53', '2026-03-30 05:38:53'),
(1681, 'App\\Models\\User', 151, 'auth-token', 'de70391421ed6aac07f5dc0590c592fea7aa1411aec0bce8e7e15a5ba9a3dd8f', '[\"*\"]', NULL, NULL, '2026-03-30 05:39:33', '2026-03-30 05:39:33'),
(1682, 'App\\Models\\User', 153, 'auth-token', '55e963ee24ff4b7b9699d68799c1e2d3e461718e034cbe98b4946ec04af6238a', '[\"*\"]', NULL, NULL, '2026-03-30 05:40:11', '2026-03-30 05:40:11'),
(1683, 'App\\Models\\User', 162, 'auth-token', '9817bef7f9a940970aef8d2907e867613fde3554ab54b5745e35c3aa2cec51e9', '[\"*\"]', NULL, NULL, '2026-03-30 05:41:41', '2026-03-30 05:41:41'),
(1684, 'App\\Models\\User', 150, 'auth-token', '3d1279eaa452c9d0cf8d9b85bd2a647a333daa355bc1f011e48881118dea8641', '[\"*\"]', NULL, NULL, '2026-03-30 05:42:38', '2026-03-30 05:42:38'),
(1685, 'App\\Models\\User', 152, 'auth-token', '41caa416fece666b4c0d65ab0e5072291eddd89ffca1c713e15d2971f8e8d307', '[\"*\"]', NULL, NULL, '2026-03-30 05:43:14', '2026-03-30 05:43:14'),
(1686, 'App\\Models\\User', 158, 'auth-token', '008805667ffa82f6d28670b680b1ddb13f2a5ddc97dada5664ef19a11e5d0cb9', '[\"*\"]', NULL, NULL, '2026-03-30 05:44:13', '2026-03-30 05:44:13'),
(1687, 'App\\Models\\User', 150, 'auth-token', 'ac4d1d7e6883725121ba5b888df0b8fa7d27f096e9446ec0439f9f14c347a873', '[\"*\"]', NULL, NULL, '2026-03-30 05:45:21', '2026-03-30 05:45:21'),
(1688, 'App\\Models\\User', 158, 'auth-token', '29458699013fc51b9ab637375a6fa69b72405ae87c3182a92bd5f9e362a85130', '[\"*\"]', NULL, NULL, '2026-03-30 05:47:22', '2026-03-30 05:47:22'),
(1689, 'App\\Models\\User', 150, 'auth-token', 'fd601bdc0ea1d2d28120026d4dbb8570f081ddf87ce8df0c6b939f97b593b105', '[\"*\"]', NULL, NULL, '2026-03-30 05:50:14', '2026-03-30 05:50:14'),
(1690, 'App\\Models\\User', 152, 'auth-token', '5b8385d12761a5ed21a7f5db53b37e5371980d40ce87f61f0f94939bafea7a02', '[\"*\"]', NULL, NULL, '2026-03-30 05:53:36', '2026-03-30 05:53:36'),
(1691, 'App\\Models\\User', 158, 'auth-token', '05883a1efd801f243b5bfd5820f741aad3cc19be62051f23d9d55a7486b23ccb', '[\"*\"]', NULL, NULL, '2026-03-30 05:53:52', '2026-03-30 05:53:52'),
(1692, 'App\\Models\\User', 149, 'auth-token', '63e84441c069bb41ec6207a239959d2596a03c88ae7eefff6b2719c66f6fa89a', '[\"*\"]', NULL, NULL, '2026-03-30 05:54:16', '2026-03-30 05:54:16'),
(1693, 'App\\Models\\User', 150, 'auth-token', '1a90102b1dfcd08ccb5a7e91731cf3264d821a24b67a7f66af07a693678b628c', '[\"*\"]', NULL, NULL, '2026-03-30 05:55:30', '2026-03-30 05:55:30'),
(1694, 'App\\Models\\User', 152, 'auth-token', '086e9747f0ecb3947e360860da6bda04819da0e272942fb6a4aa974080fa85fc', '[\"*\"]', NULL, NULL, '2026-03-30 05:56:02', '2026-03-30 05:56:02'),
(1695, 'App\\Models\\User', 158, 'auth-token', 'b33c41ebb4cdbc3a3612a0936fe80f694172e1eb228d3fbeb36cdda0abe071f0', '[\"*\"]', NULL, NULL, '2026-03-30 05:56:17', '2026-03-30 05:56:17'),
(1696, 'App\\Models\\User', 152, 'auth-token', 'a2aa7904586db8b7cccf10a02f61f213ec976aeef19ad89ec77072b8cf87437f', '[\"*\"]', NULL, NULL, '2026-03-30 05:56:51', '2026-03-30 05:56:51'),
(1697, 'App\\Models\\User', 150, 'auth-token', '09458e335df872c8688613a69830611838278b40581da1e8d6aae675eeb2b784', '[\"*\"]', NULL, NULL, '2026-03-30 05:57:18', '2026-03-30 05:57:18'),
(1698, 'App\\Models\\User', 149, 'auth-token', '30284089b5e0fa2d7a0822b8d53ac23f12f39d3c6d5aef256727e602923e5852', '[\"*\"]', NULL, NULL, '2026-03-30 05:59:28', '2026-03-30 05:59:28'),
(1699, 'App\\Models\\User', 150, 'auth-token', 'c4177699a23418f5038e4a37888359b16eb69b89e839661bd8a81e0640deadfe', '[\"*\"]', NULL, NULL, '2026-03-30 06:01:35', '2026-03-30 06:01:35'),
(1700, 'App\\Models\\User', 154, 'auth-token', 'c0d1dcc9deec3ecf6d5562819b879e53c09d7124951f3b73a16f1dc56ce679af', '[\"*\"]', NULL, NULL, '2026-03-30 06:02:07', '2026-03-30 06:02:07'),
(1701, 'App\\Models\\User', 159, 'auth-token', '10e3cd778b87c36ffe20c12b67ea6a60b8a15c015e9ba01f877311c0240f5030', '[\"*\"]', NULL, NULL, '2026-03-30 06:02:50', '2026-03-30 06:02:50'),
(1702, 'App\\Models\\User', 159, 'auth-token', 'eda4adadf7effa410f9e47904fb5823528baa53da222a1208cc276e928fe9261', '[\"*\"]', NULL, NULL, '2026-03-30 06:07:54', '2026-03-30 06:07:54'),
(1703, 'App\\Models\\User', 147, 'auth-token', 'aa14709bdf0a057752d056b891f49f720a87a373a7822d4ec34e5ee183c0c8da', '[\"*\"]', NULL, NULL, '2026-03-30 06:08:26', '2026-03-30 06:08:26'),
(1704, 'App\\Models\\User', 159, 'auth-token', '0d32aed54836d44fd81488daa62d82e48044b46cc2514d0ca6e0e9d255a458bd', '[\"*\"]', NULL, NULL, '2026-03-30 06:10:56', '2026-03-30 06:10:56'),
(1705, 'App\\Models\\User', 159, 'auth-token', 'd9a7aa8d904dab2a4f4299a525b481cb74aeea42842f035774cf25c3eaa69a0a', '[\"*\"]', NULL, NULL, '2026-03-30 06:14:09', '2026-03-30 06:14:09'),
(1706, 'App\\Models\\User', 159, 'auth-token', 'fb5a298632bcc47b7d3df1b1c0e50c2aadb0bb54c7ddd10e396aac68ee3fbaaa', '[\"*\"]', NULL, NULL, '2026-03-30 06:14:42', '2026-03-30 06:14:42'),
(1707, 'App\\Models\\User', 159, 'auth-token', '5a2fe7501592c72301d9025d0835be4b6b10dcaeb23a081fae1f8b320b495394', '[\"*\"]', NULL, NULL, '2026-03-30 06:18:00', '2026-03-30 06:18:00'),
(1708, 'App\\Models\\User', 149, 'auth-token', 'c049b59d11e7de2b0437ae5731df566339e9a26e328b35f1b4d855bc09ecf722', '[\"*\"]', NULL, NULL, '2026-03-30 06:31:34', '2026-03-30 06:31:34'),
(1709, 'App\\Models\\User', 159, 'auth-token', '33b4875deeecfce3d23fd589cf0eb145a744d3f548e45761078a6e7c9b69d51e', '[\"*\"]', NULL, NULL, '2026-03-30 06:32:05', '2026-03-30 06:32:05'),
(1710, 'App\\Models\\User', 159, 'auth-token', 'c2d1dd236ee4f643f8c41b7ca8afd71cbecd015530b63259aac8fc979a38ad55', '[\"*\"]', NULL, NULL, '2026-03-30 07:47:38', '2026-03-30 07:47:38'),
(1711, 'App\\Models\\User', 161, 'auth-token', '9d8817ddf696e0feccf99266f934dfbed653f3756a5294de244fe99e79cc4214', '[\"*\"]', NULL, NULL, '2026-03-30 07:48:29', '2026-03-30 07:48:29'),
(1712, 'App\\Models\\User', 162, 'auth-token', '5b53390b10f97eb841899ea9309623d5f21eaf6c835a10dd909051cbfe38facb', '[\"*\"]', NULL, NULL, '2026-03-30 07:49:14', '2026-03-30 07:49:14'),
(1713, 'App\\Models\\User', 28, 'auth-token', 'd6a31fe372dc7cf9768d5684e94179911feab0e477c41fb6bff252006163973d', '[\"*\"]', NULL, NULL, '2026-03-30 07:50:07', '2026-03-30 07:50:07'),
(1714, 'App\\Models\\User', 31, 'auth-token', '94544377e65c14abb440ef58ffef368f0fcb9cb296face4b191741fa8ef5f5af', '[\"*\"]', NULL, NULL, '2026-03-30 07:52:13', '2026-03-30 07:52:13'),
(1715, 'App\\Models\\User', 159, 'auth-token', '7f995d3c5e051f14dff7684aabdad60e90702d5dbae5f8a83d8870959bdd6464', '[\"*\"]', NULL, NULL, '2026-03-30 07:53:32', '2026-03-30 07:53:32'),
(1716, 'App\\Models\\User', 28, 'auth-token', 'fc5d7acf4311340d74829d533dbd12ad866cd01c5d45024cbab470fcece00703', '[\"*\"]', NULL, NULL, '2026-03-30 07:53:55', '2026-03-30 07:53:55'),
(1717, 'App\\Models\\User', 179, 'auth-token', '06f81091c46d48cf4f20e5226dbca85b79246264a11ae8063de092f1c9e3eb5c', '[\"*\"]', NULL, NULL, '2026-03-30 07:56:38', '2026-03-30 07:56:38'),
(1718, 'App\\Models\\User', 151, 'auth-token', '3480266d57aef18f72a57683c0c5816dd4e3e1a20a6be315afdeef5740c16933', '[\"*\"]', NULL, NULL, '2026-03-30 07:58:24', '2026-03-30 07:58:24'),
(1719, 'App\\Models\\User', 150, 'auth-token', '16f81563cfd90571a6fbc472ef143b76ad174079162de3d3ed415e809f43be55', '[\"*\"]', NULL, NULL, '2026-03-30 07:59:20', '2026-03-30 07:59:20'),
(1720, 'App\\Models\\User', 153, 'auth-token', '089a2608ebbce77638802aa407a9e2103dcfcfb73e993aa3673a3370321d3876', '[\"*\"]', NULL, NULL, '2026-03-30 08:00:48', '2026-03-30 08:00:48'),
(1721, 'App\\Models\\User', 150, 'auth-token', 'de5a86aa4e8f6e4a8737ebd372abcad87dade6c5726cd3394b4e3e852b74ae85', '[\"*\"]', NULL, NULL, '2026-03-30 08:02:03', '2026-03-30 08:02:03'),
(1722, 'App\\Models\\User', 151, 'auth-token', '020c1c89a3c9dd7ad8915e2a5cdccc41ba83ecebc77deeaa0d8bb30eadeeddaf', '[\"*\"]', NULL, NULL, '2026-03-30 08:04:10', '2026-03-30 08:04:10'),
(1723, 'App\\Models\\User', 150, 'auth-token', '89952eab0f65cc74890e9591f56694ff36e58b6fa18263517b388a232c9c1ce6', '[\"*\"]', NULL, NULL, '2026-03-30 08:04:30', '2026-03-30 08:04:30'),
(1724, 'App\\Models\\User', 151, 'auth-token', '4854f0296aeffdf6eaff8fd9249668e0601089bd1e5c035f6df1668e7b493d00', '[\"*\"]', NULL, NULL, '2026-03-30 08:05:19', '2026-03-30 08:05:19'),
(1725, 'App\\Models\\User', 154, 'auth-token', '1ee98cd81ee1ad06cc3bb2d380d0d016818da10126a145410aa99a68402c12cc', '[\"*\"]', NULL, NULL, '2026-03-30 08:07:13', '2026-03-30 08:07:13'),
(1726, 'App\\Models\\User', 154, 'auth-token', 'b32f46885002329ea7a5405143a575a8b9220812671fd67a8405e759814b7d4e', '[\"*\"]', NULL, NULL, '2026-03-30 08:08:15', '2026-03-30 08:08:15'),
(1727, 'App\\Models\\User', 151, 'auth-token', 'dde31784102f5e6cb82dc284830533e16df6deb0332ea12d90efb364b287b4be', '[\"*\"]', NULL, NULL, '2026-03-30 08:09:33', '2026-03-30 08:09:33'),
(1728, 'App\\Models\\User', 150, 'auth-token', '94bc19ac02560f156e019b80ed5fecfd305e917b962eef3e03555fdc704a9f83', '[\"*\"]', NULL, NULL, '2026-03-30 08:11:49', '2026-03-30 08:11:49'),
(1729, 'App\\Models\\User', 31, 'auth-token', '46e173e104d6806815dbd9d848a34972c699ede7fdbab8ec830aebf014cfdc7d', '[\"*\"]', NULL, NULL, '2026-03-30 08:12:12', '2026-03-30 08:12:12'),
(1730, 'App\\Models\\User', 151, 'auth-token', '6212ae72884cea13e5a347a2980873e22d92cdc18170188e692763c0c0d862c0', '[\"*\"]', NULL, NULL, '2026-03-30 08:12:45', '2026-03-30 08:12:45'),
(1731, 'App\\Models\\User', 150, 'auth-token', 'a5073f686876e63f756eff2782a62c5b59421e15daf5ee92816444678ce668a2', '[\"*\"]', NULL, NULL, '2026-03-30 08:13:09', '2026-03-30 08:13:09'),
(1732, 'App\\Models\\User', 158, 'auth-token', '9505feb960d0c97361a2ae693377df1318a669fbc86216bb2a9d9b54b36cebfc', '[\"*\"]', NULL, NULL, '2026-03-30 08:13:42', '2026-03-30 08:13:42'),
(1733, 'App\\Models\\User', 152, 'auth-token', 'efc6862936f5f1708ef922ef09a2d814e2055845962fdad7d411764db500d40a', '[\"*\"]', NULL, NULL, '2026-03-30 08:14:49', '2026-03-30 08:14:49'),
(1734, 'App\\Models\\User', 150, 'auth-token', '6c147cde9dca5857fdf3a2a671f9f42d3ba268f8bc2c79716b6ecdbd4e015d54', '[\"*\"]', NULL, NULL, '2026-03-30 08:15:20', '2026-03-30 08:15:20'),
(1735, 'App\\Models\\User', 149, 'auth-token', 'be39b7af309943763980e5db3e8c8bd3f526b91a995e6e3ae91a69f5c78d01bd', '[\"*\"]', NULL, NULL, '2026-03-30 08:15:58', '2026-03-30 08:15:58'),
(1736, 'App\\Models\\User', 150, 'auth-token', '3c5f7f2427dda093d79e19ff63080e053e931435f0238456db7c4b507bec79b0', '[\"*\"]', NULL, NULL, '2026-03-30 08:21:14', '2026-03-30 08:21:14'),
(1737, 'App\\Models\\User', 152, 'auth-token', 'c5369d88ad615ae8a777d88fcdf69713a99d63434a88f7485ca34457611e5849', '[\"*\"]', NULL, NULL, '2026-03-30 08:21:34', '2026-03-30 08:21:34'),
(1738, 'App\\Models\\User', 158, 'auth-token', '2ea8e8d86fbe73774c859ad56d38f8343122403c7b6adde8675ba2c83b2fe320', '[\"*\"]', NULL, NULL, '2026-03-30 08:21:47', '2026-03-30 08:21:47'),
(1739, 'App\\Models\\User', 150, 'auth-token', '5a17c83a99e269808e8a165afe81ace0f9b2ff6fa4478c84a21aa7d3712de247', '[\"*\"]', NULL, NULL, '2026-03-30 08:22:30', '2026-03-30 08:22:30'),
(1740, 'App\\Models\\User', 173, 'auth-token', '00e2f2fcc6c84f6d32347e28beadaad3c53dc17d72da1e482cf76b75985a4bd9', '[\"*\"]', NULL, NULL, '2026-03-30 08:26:45', '2026-03-30 08:26:45'),
(1741, 'App\\Models\\User', 173, 'auth-token', '8f327bfce03b97584d1b9ddfe0a835fc139b80b8f9267f144be72b918c196f77', '[\"*\"]', NULL, NULL, '2026-03-30 08:28:54', '2026-03-30 08:28:54'),
(1742, 'App\\Models\\User', 28, 'auth-token', '07726974441f4396a293b4edb50d0bdb95497a803d0afc5d37d60dec95c4cb5d', '[\"*\"]', NULL, NULL, '2026-03-30 08:29:15', '2026-03-30 08:29:15'),
(1743, 'App\\Models\\User', 181, 'auth-token', '88f3ba9a54ac1873adf26b3d2b913d82a065ba1cfd52a46709234a827039b4eb', '[\"*\"]', NULL, NULL, '2026-03-30 08:33:21', '2026-03-30 08:33:21'),
(1744, 'App\\Models\\User', 150, 'auth-token', 'b48e132e66b1b66c0b1a57594cc7f267b2b258f26118d603c659c332fcf6bd3d', '[\"*\"]', NULL, NULL, '2026-03-30 08:35:15', '2026-03-30 08:35:15'),
(1745, 'App\\Models\\User', 149, 'auth-token', '9a09ea0d3e5a6b17e73146f8f0e45461261f28c179a270f3bac7313d64840403', '[\"*\"]', NULL, NULL, '2026-03-30 08:35:59', '2026-03-30 08:35:59'),
(1746, 'App\\Models\\User', 150, 'auth-token', '76931c932e4c63b093897dac4ebee4a3e5d32ec301c13e64fd02554b8aa2aee6', '[\"*\"]', NULL, NULL, '2026-03-30 08:39:28', '2026-03-30 08:39:28'),
(1747, 'App\\Models\\User', 154, 'auth-token', 'a42c912f1a52570e4a3706f66cb5c7d4b4123da2c9259a9f64e168d875621b35', '[\"*\"]', NULL, NULL, '2026-03-30 08:39:54', '2026-03-30 08:39:54'),
(1748, 'App\\Models\\User', 149, 'auth-token', '0bc5086f6b9c06a33f0a37acb9ac58ea0073a4a5a1fb69f775aed87296420198', '[\"*\"]', NULL, NULL, '2026-03-30 08:40:25', '2026-03-30 08:40:25'),
(1749, 'App\\Models\\User', 161, 'auth-token', 'b16928f828780fed4cb014251d6916864da73a30473e6078355070e53729683b', '[\"*\"]', NULL, NULL, '2026-03-30 08:41:19', '2026-03-30 08:41:19'),
(1750, 'App\\Models\\User', 31, 'auth-token', '91651c7be79a5410c1533a3859d6dd9ddc10f82d5333150db3c55aa0d979c14f', '[\"*\"]', NULL, NULL, '2026-03-30 08:41:42', '2026-03-30 08:41:42'),
(1751, 'App\\Models\\User', 157, 'auth-token', '7c1eedea741bf83b293ba2f6f08934bf9778adbcd3c6d0b57f913f7fc23baf35', '[\"*\"]', NULL, NULL, '2026-03-30 08:42:28', '2026-03-30 08:42:28'),
(1752, 'App\\Models\\User', 31, 'auth-token', 'd9b232c66a563ec454bbddbfc3e106bbee5e4416860e7fe92459ff18099e608a', '[\"*\"]', NULL, NULL, '2026-03-30 08:45:25', '2026-03-30 08:45:25'),
(1753, 'App\\Models\\User', 151, 'auth-token', 'bc9d56b209d6e35686a90ada09105b5abb8a9a87dc7bd230628e36e0ae9cb7df', '[\"*\"]', NULL, NULL, '2026-03-30 08:46:04', '2026-03-30 08:46:04'),
(1754, 'App\\Models\\User', 153, 'auth-token', '7176a038ec2a77e80a71d8cbbcd7a81b941f0cf58e9d02c794fc6d7582417a02', '[\"*\"]', NULL, NULL, '2026-03-30 08:46:38', '2026-03-30 08:46:38'),
(1755, 'App\\Models\\User', 149, 'auth-token', '206dcda16810bcd100b004286cff6d29b513a21e6f7c2b8a1f582d61e45836e1', '[\"*\"]', NULL, NULL, '2026-03-30 08:48:57', '2026-03-30 08:48:57'),
(1756, 'App\\Models\\User', 151, 'auth-token', 'a6cb8a1daabfa08479c456981240a7d7dc0c0bbe54ef5cd83e71d9855ddb25a6', '[\"*\"]', NULL, NULL, '2026-03-30 08:50:00', '2026-03-30 08:50:00'),
(1757, 'App\\Models\\User', 159, 'auth-token', 'ad17cba2deec405c283c9bc68bc3b6320b0199c779d35f2dc36a3f910025e895', '[\"*\"]', NULL, NULL, '2026-03-30 09:05:04', '2026-03-30 09:05:04'),
(1758, 'App\\Models\\User', 159, 'auth-token', '02190024efadae49d2c55f4f6dfbad8bcc3e62a061106fc6ed845198ed90eab2', '[\"*\"]', NULL, NULL, '2026-03-30 09:05:28', '2026-03-30 09:05:28'),
(1759, 'App\\Models\\User', 159, 'auth-token', '1f91013ad892c87efaa9d0a723c385d5ba4dd34a58280fceac8faf0dbd0c86ee', '[\"*\"]', NULL, NULL, '2026-03-30 09:07:02', '2026-03-30 09:07:02'),
(1760, 'App\\Models\\User', 159, 'auth-token', 'ccfc81e2a39816f96e54cf3b9f7d745e7f3f137a115fd84868d515e438bdcd1c', '[\"*\"]', NULL, NULL, '2026-03-30 09:07:15', '2026-03-30 09:07:15'),
(1761, 'App\\Models\\User', 159, 'auth-token', '5d2576b8703d7ba26ba3bbab2a49982dc4b04b9b8a24d8a95d56fcd71f18a275', '[\"*\"]', NULL, NULL, '2026-03-30 09:07:26', '2026-03-30 09:07:26'),
(1762, 'App\\Models\\User', 31, 'auth-token', '504cc61b1f7743c107d470685929337af30b94b528a3646074563cb08dee42c3', '[\"*\"]', NULL, NULL, '2026-03-30 09:09:29', '2026-03-30 09:09:29'),
(1763, 'App\\Models\\User', 149, 'auth-token', 'fba1b51275f82059f7963eb30c862e85ec003b8039ad00a3cf1f077660b9281f', '[\"*\"]', NULL, NULL, '2026-03-30 09:10:10', '2026-03-30 09:10:10'),
(1764, 'App\\Models\\User', 174, 'auth-token', 'cbaeb03770c6b07dfdd8ca1ea6670d907f305a42381fa88917871487a09ab487', '[\"*\"]', NULL, NULL, '2026-03-30 09:10:35', '2026-03-30 09:10:35'),
(1765, 'App\\Models\\User', 150, 'auth-token', '5cb1a0d7d448f4c114f9159ac5d059fed04a17878e0783302726d1d64e49703e', '[\"*\"]', NULL, NULL, '2026-03-30 09:10:52', '2026-03-30 09:10:52'),
(1766, 'App\\Models\\User', 147, 'auth-token', '14564da9bb55acdc7ac2bcb13eec29e3a9098552b4c94b4c54f5983fea887c7c', '[\"*\"]', NULL, NULL, '2026-03-30 09:11:21', '2026-03-30 09:11:21'),
(1767, 'App\\Models\\User', 151, 'auth-token', 'c66579161edc061d51d7a028f9cd2e0c5fa7b2e649ba5b377fb7fb3e017d906b', '[\"*\"]', NULL, NULL, '2026-03-30 09:11:47', '2026-03-30 09:11:47'),
(1768, 'App\\Models\\User', 154, 'auth-token', '09297c518f113351b0281ad84452cf1488f3be2639adf8d5927fb632660a79fc', '[\"*\"]', NULL, NULL, '2026-03-30 09:12:24', '2026-03-30 09:12:24'),
(1769, 'App\\Models\\User', 154, 'auth-token', 'f188c7fece670946f601e5d3aab98a472ee31eb9a771a843b96ccab650152ce6', '[\"*\"]', NULL, NULL, '2026-03-30 09:12:35', '2026-03-30 09:12:35'),
(1770, 'App\\Models\\User', 154, 'auth-token', '11c13405e1a5d4f374192f04f210615b2c3f2018324aa6e0ea4a5c95fd3a66be', '[\"*\"]', NULL, NULL, '2026-03-30 09:12:53', '2026-03-30 09:12:53'),
(1771, 'App\\Models\\User', 153, 'auth-token', 'a08fba05794d361d5622cddfe1467521fd53032bba014775304c36f0590ba1c5', '[\"*\"]', NULL, NULL, '2026-03-30 09:13:10', '2026-03-30 09:13:10'),
(1772, 'App\\Models\\User', 153, 'auth-token', 'fd5d5c79996bd4da4f6c330fb5659884e2b49ebd3142a5eddf01a5ab5cd75b71', '[\"*\"]', NULL, NULL, '2026-03-30 09:38:17', '2026-03-30 09:38:17'),
(1773, 'App\\Models\\User', 153, 'auth-token', '29053890efe0574e4de282eb0713ce8878cbf0aa3ad7ba5cc481b9edc52d9d7a', '[\"*\"]', NULL, NULL, '2026-03-30 10:43:43', '2026-03-30 10:43:43'),
(1774, 'App\\Models\\User', 148, 'auth-token', '0cb8aa67138a6d7a9814dc37e42c4af9622c3d4328167538613e58868bbae3cd', '[\"*\"]', NULL, NULL, '2026-03-30 10:51:37', '2026-03-30 10:51:37'),
(1775, 'App\\Models\\User', 148, 'auth-token', '4e7365bf7f72267a2c6da2a468656176b5be2a49dbf81b5c306696fa39ee687d', '[\"*\"]', NULL, NULL, '2026-03-30 10:52:06', '2026-03-30 10:52:06'),
(1776, 'App\\Models\\User', 28, 'auth-token', '6c0a7a909a1f7bb2d7a2bf82056c9080ac525f12c698f11f62bf4ae388cb49a5', '[\"*\"]', NULL, NULL, '2026-03-30 10:52:50', '2026-03-30 10:52:50'),
(1777, 'App\\Models\\User', 147, 'auth-token', '0f4b2508ff198e5785438ac08b670c64eed572cf64f74549956d675789f954e4', '[\"*\"]', NULL, NULL, '2026-03-30 10:53:22', '2026-03-30 10:53:22'),
(1778, 'App\\Models\\User', 148, 'auth-token', '2720c8075f75d0d17607424fcf879e3cc1905626c2bd40956fe2a4fc9b6d4993', '[\"*\"]', NULL, NULL, '2026-03-30 10:53:55', '2026-03-30 10:53:55'),
(1779, 'App\\Models\\User', 28, 'auth-token', 'a9e615b1df90ca9f441a01a412a2458815128df245db52bb5ae0cc665db4b9e2', '[\"*\"]', NULL, NULL, '2026-03-30 10:59:20', '2026-03-30 10:59:20'),
(1780, 'App\\Models\\User', 148, 'auth-token', 'b1516b93cf0e8467c72139f877937a90741f53d994dab99d25016c91c5559cba', '[\"*\"]', NULL, NULL, '2026-03-30 11:03:42', '2026-03-30 11:03:42'),
(1781, 'App\\Models\\User', 173, 'auth-token', '544550b32f996b94da5fcde52e320dbd69c810b1064f672aec58a85172d2deb6', '[\"*\"]', NULL, NULL, '2026-03-30 11:04:44', '2026-03-30 11:04:44'),
(1782, 'App\\Models\\User', 181, 'auth-token', '4103a461cd6d33d9853b4d4922e48998aacf7085f37d52da17295d66d1c7f03d', '[\"*\"]', NULL, NULL, '2026-03-30 11:22:12', '2026-03-30 11:22:12'),
(1783, 'App\\Models\\User', 173, 'auth-token', '11f12be56c0a69cbe4ce3ac00d1333e483b131689d8b05052096f5c28500e7a7', '[\"*\"]', NULL, NULL, '2026-03-30 12:14:12', '2026-03-30 12:14:12'),
(1784, 'App\\Models\\User', 151, 'auth-token', 'be38ca04f4963b93de3320a260d8102ba0671f28cb7d5501584ef1fabf83ad3e', '[\"*\"]', NULL, NULL, '2026-03-30 12:25:53', '2026-03-30 12:25:53'),
(1785, 'App\\Models\\User', 154, 'auth-token', '44edf0292d091ab14c2e4d051ff58533782a8798cef9a0712df57e678c4811f1', '[\"*\"]', NULL, NULL, '2026-03-30 12:26:26', '2026-03-30 12:26:26'),
(1786, 'App\\Models\\User', 151, 'auth-token', 'b25e274d6da4ae610c451b71c915ef0e222e99bcf486fce76f51edb1927f1ed0', '[\"*\"]', NULL, NULL, '2026-03-30 12:33:17', '2026-03-30 12:33:17'),
(1787, 'App\\Models\\User', 154, 'auth-token', '44fab673e44c1b2f8068dd03c1fc87cf73e8e7f4054336670728826056f07dd1', '[\"*\"]', NULL, NULL, '2026-03-30 12:41:58', '2026-03-30 12:41:58'),
(1788, 'App\\Models\\User', 150, 'auth-token', 'b1392928e3f30d137195a27f09532ac0e882bcc3de95a3c28980dcdb0a649d29', '[\"*\"]', NULL, NULL, '2026-03-30 13:16:02', '2026-03-30 13:16:02'),
(1789, 'App\\Models\\User', 151, 'auth-token', 'a3846c9f6547ac889d122a307f17587b80de9217548e6f6c086a02e9430ef24f', '[\"*\"]', NULL, NULL, '2026-03-30 13:17:07', '2026-03-30 13:17:07'),
(1790, 'App\\Models\\User', 154, 'auth-token', 'f9eb852cc2173273e911b3602a07c5df5e9a88901be2a95e240dba66dfce12da', '[\"*\"]', NULL, NULL, '2026-03-30 13:17:23', '2026-03-30 13:17:23'),
(1791, 'App\\Models\\User', 151, 'auth-token', 'cec0238b0eda814ef9d0371391812aa16d31a2a03e496a539da97737bffed65f', '[\"*\"]', NULL, NULL, '2026-03-30 13:34:35', '2026-03-30 13:34:35'),
(1792, 'App\\Models\\User', 151, 'auth-token', '31f7b6ccef7a87047796f76fbb2ceb31abb2b9e511bb30ffdbb4c3acf34684b4', '[\"*\"]', NULL, NULL, '2026-03-30 13:35:02', '2026-03-30 13:35:02'),
(1793, 'App\\Models\\User', 154, 'auth-token', 'ddffea1370da280b32ddb7eca62860e02dc5ce7180c4d02bbe5c060d0ae2a72f', '[\"*\"]', NULL, NULL, '2026-03-30 13:35:17', '2026-03-30 13:35:17'),
(1794, 'App\\Models\\User', 150, 'auth-token', 'e7fdbfef5debeff270dc089d5e69cefb0636d1cacf9d8e9c4ef7ee417c630bba', '[\"*\"]', NULL, NULL, '2026-03-30 13:35:50', '2026-03-30 13:35:50'),
(1795, 'App\\Models\\User', 154, 'auth-token', 'fd0a753ee0614b1b0ad8343c29e5d69447e6ef11f551930ace21600d045fc0ad', '[\"*\"]', NULL, NULL, '2026-03-30 13:43:45', '2026-03-30 13:43:45'),
(1796, 'App\\Models\\User', 151, 'auth-token', '86be7308e3e41c13f038fafc483c06c924948778dde5fa5e99a7072cbacb16d4', '[\"*\"]', NULL, NULL, '2026-03-30 13:44:24', '2026-03-30 13:44:24'),
(1797, 'App\\Models\\User', 151, 'auth-token', 'bbf8746180aeb596493d776b81d26f939d7568796b04a8a1d2dd9ac0dfc9811b', '[\"*\"]', NULL, NULL, '2026-03-30 13:45:48', '2026-03-30 13:45:48'),
(1798, 'App\\Models\\User', 154, 'auth-token', '44cc35ed98a2f7a1f440d7eaec50499a19c3b90c81e6acc4ff6713587f976b58', '[\"*\"]', NULL, NULL, '2026-03-30 13:51:00', '2026-03-30 13:51:00'),
(1799, 'App\\Models\\User', 154, 'auth-token', '8f3514fdf68f1982f17ffad76d3d568d8b934f0b5c9a70f68e95c5643db8c80b', '[\"*\"]', NULL, NULL, '2026-03-30 13:54:42', '2026-03-30 13:54:42'),
(1800, 'App\\Models\\User', 31, 'auth-token', '88131738a9adb2066206f13291b2cc1eefb34a8ef35f3175834c25b587c4bf30', '[\"*\"]', NULL, NULL, '2026-03-30 13:56:02', '2026-03-30 13:56:02'),
(1801, 'App\\Models\\User', 154, 'auth-token', 'b538ab675e9e59275ee369c322e477997d22efc39d230fdfe3a06bfabc85621e', '[\"*\"]', NULL, NULL, '2026-03-30 13:57:05', '2026-03-30 13:57:05'),
(1802, 'App\\Models\\User', 150, 'auth-token', '67fcac0643e6eeecd88d10d8fe48c1d13650c19b039721ed6838af1a26496aff', '[\"*\"]', NULL, NULL, '2026-03-30 13:57:59', '2026-03-30 13:57:59'),
(1803, 'App\\Models\\User', 152, 'auth-token', '3e938b1d1a127b554ff246ead6b9526086e8ca653677a976931721cae8be4578', '[\"*\"]', NULL, NULL, '2026-03-30 13:59:03', '2026-03-30 13:59:03'),
(1804, 'App\\Models\\User', 152, 'auth-token', '300fa1cb6674bc5399c54e5145f13a6fada850b2cb7256f3847d76909ff8d52a', '[\"*\"]', NULL, NULL, '2026-03-30 14:00:02', '2026-03-30 14:00:02'),
(1805, 'App\\Models\\User', 150, 'auth-token', '8f95e908b10c1b9080b3987acaaad2a50da0935e3227efee1711c62dbbf89301', '[\"*\"]', NULL, NULL, '2026-03-30 14:00:18', '2026-03-30 14:00:18'),
(1806, 'App\\Models\\User', 149, 'auth-token', '7e60b9edae7333b01333e0a34e03456be837b018fc407c611c5c44ba76249c12', '[\"*\"]', NULL, NULL, '2026-03-30 14:03:08', '2026-03-30 14:03:08'),
(1807, 'App\\Models\\User', 150, 'auth-token', '871c938eb9f000850b1ffe10b26af55cb27d1b287f38988a20e809cca7e9fb9b', '[\"*\"]', NULL, NULL, '2026-03-30 14:04:38', '2026-03-30 14:04:38'),
(1808, 'App\\Models\\User', 152, 'auth-token', 'd99d62bef9483c3c456654f0edba7d05ddc9903d8f82b597d1dfd8a165164afa', '[\"*\"]', NULL, NULL, '2026-03-30 14:05:28', '2026-03-30 14:05:28'),
(1809, 'App\\Models\\User', 150, 'auth-token', '08dadd1600eb714b698b552617379f04fe9d953c0e1f3998c916ba8b23360760', '[\"*\"]', NULL, NULL, '2026-03-30 14:05:55', '2026-03-30 14:05:55'),
(1810, 'App\\Models\\User', 154, 'auth-token', '75b5a2953dd77d2f029f14a8586d38ea510d03877d64f8828abc77f2a2c53a7f', '[\"*\"]', NULL, NULL, '2026-03-30 14:06:28', '2026-03-30 14:06:28'),
(1811, 'App\\Models\\User', 149, 'auth-token', 'b318924ce49a82441d853ef168994ca8519b7e1f9c8ad9e7cba5aaa3adfe2a39', '[\"*\"]', NULL, NULL, '2026-03-30 14:07:38', '2026-03-30 14:07:38'),
(1812, 'App\\Models\\User', 150, 'auth-token', 'bbd8900cbfb71325788345d8c7016a348dd1070ca92ad2c0e2fd3eeac141bbeb', '[\"*\"]', NULL, NULL, '2026-03-30 14:08:09', '2026-03-30 14:08:09'),
(1813, 'App\\Models\\User', 154, 'auth-token', 'ec0c49c3dd20b82aae6fc14db1e97a229ed2b70abefd677a867f399b501d527b', '[\"*\"]', NULL, NULL, '2026-03-30 14:08:33', '2026-03-30 14:08:33'),
(1814, 'App\\Models\\User', 151, 'auth-token', '9d5967d7ce5061adb7bea593dfe305719f984cf14eb7ed594502a047cd3c8621', '[\"*\"]', NULL, NULL, '2026-03-30 14:08:52', '2026-03-30 14:08:52'),
(1815, 'App\\Models\\User', 154, 'auth-token', 'b46949ac85a6d5757884abf900890a69de84f2a772e519d859ef4d9d114fa9cd', '[\"*\"]', NULL, NULL, '2026-03-30 14:10:19', '2026-03-30 14:10:19'),
(1816, 'App\\Models\\User', 154, 'auth-token', 'c444fb8d059338801e1f6f948c4ee9f99de811acf854736cf69af41999e1d280', '[\"*\"]', NULL, NULL, '2026-03-30 14:12:43', '2026-03-30 14:12:43'),
(1817, 'App\\Models\\User', 153, 'auth-token', 'dade9d72122c3da15e8eb617f7e3def73aedfd054a0a4952f9a35787b373d8ac', '[\"*\"]', NULL, NULL, '2026-03-30 14:15:02', '2026-03-30 14:15:02'),
(1818, 'App\\Models\\User', 154, 'auth-token', 'b6d6b969b528014eddd1f6be23f2fabff1090be7f5f6da2f27ee244a570463a3', '[\"*\"]', NULL, NULL, '2026-03-30 14:15:55', '2026-03-30 14:15:55'),
(1819, 'App\\Models\\User', 150, 'auth-token', 'd2e06a3132bb3ec290d6a34e9f01a7f98a0c9959491ae289736f6b2e7c8a8587', '[\"*\"]', NULL, NULL, '2026-03-30 14:21:32', '2026-03-30 14:21:32'),
(1820, 'App\\Models\\User', 154, 'auth-token', '02750512b3d5dd94b567b3ce10478043058a701cca3b81ed56367e3f90cc0d64', '[\"*\"]', NULL, NULL, '2026-03-30 14:53:44', '2026-03-30 14:53:44'),
(1821, 'App\\Models\\User', 150, 'auth-token', 'dadf948fe279aa6ca4735a2953f6d6889a47fa7061e7208b1ca13743b1aed042', '[\"*\"]', NULL, NULL, '2026-03-30 14:56:38', '2026-03-30 14:56:38'),
(1822, 'App\\Models\\User', 149, 'auth-token', 'b6e130fad1424f796981af44542131690bfaefce236b90914ff7ab6b74a53217', '[\"*\"]', NULL, NULL, '2026-03-30 15:00:36', '2026-03-30 15:00:36'),
(1823, 'App\\Models\\User', 150, 'auth-token', 'acad950705f4bf66f95017a6eae127a19dc7af0a6af4d3175745a58dc304a202', '[\"*\"]', NULL, NULL, '2026-03-30 15:01:49', '2026-03-30 15:01:49'),
(1824, 'App\\Models\\User', 152, 'auth-token', 'c921dfdec0e2d2eafca9496591e205c5693ab316a890288df03121ba4a4ba410', '[\"*\"]', NULL, NULL, '2026-03-30 15:02:45', '2026-03-30 15:02:45'),
(1825, 'App\\Models\\User', 150, 'auth-token', '50c8227e4e94d7bc2da7757268fa5da6014a0802eb6bfd7aeb06f75a7338421c', '[\"*\"]', NULL, NULL, '2026-03-30 15:03:22', '2026-03-30 15:03:22'),
(1826, 'App\\Models\\User', 149, 'auth-token', 'd605c1a4464aed032b69d90d34d06fd37af5b3506de29522b0a0458accd8f46e', '[\"*\"]', NULL, NULL, '2026-03-30 15:03:55', '2026-03-30 15:03:55'),
(1827, 'App\\Models\\User', 150, 'auth-token', 'e64900f16a5f47dd169d4c8e4790afc5e675c62dd8687e5019415206a475282f', '[\"*\"]', NULL, NULL, '2026-03-30 15:04:52', '2026-03-30 15:04:52'),
(1828, 'App\\Models\\User', 151, 'auth-token', 'd1fe9357f159b17d4cd9aa66c98d7e1002299a936487248e0aa05ce96cd43578', '[\"*\"]', NULL, NULL, '2026-03-30 15:05:21', '2026-03-30 15:05:21'),
(1829, 'App\\Models\\User', 154, 'auth-token', 'f863a838dc1a26e9fc3def4ac3d17a3e2b1f61c149f2fbfabbff39f97bc7ca9e', '[\"*\"]', NULL, NULL, '2026-03-30 15:06:11', '2026-03-30 15:06:11'),
(1830, 'App\\Models\\User', 31, 'auth-token', '1e07f0fd89e36ec6251d0606481d49e4705d8ccd00aba6bd0d8654c1718aa935', '[\"*\"]', NULL, NULL, '2026-03-30 15:13:53', '2026-03-30 15:13:53'),
(1831, 'App\\Models\\User', 154, 'auth-token', '70cef9f02d98001f33f6683c0c4e8d89a6a77a9c175b591b30b594d0b2cd4815', '[\"*\"]', NULL, NULL, '2026-03-30 15:15:44', '2026-03-30 15:15:44'),
(1832, 'App\\Models\\User', 150, 'auth-token', 'd1b1946439ef3648ec9dd1048010dad0efadf459f50a6f7ea1e93805ea4018d9', '[\"*\"]', NULL, NULL, '2026-03-30 15:16:07', '2026-03-30 15:16:07'),
(1833, 'App\\Models\\User', 158, 'auth-token', 'e2dfcf0cc8d5601ecbb3d49b4b31de17b3ed3921ac8e0cdd13dd84cfe9d56e7a', '[\"*\"]', NULL, NULL, '2026-03-30 15:16:34', '2026-03-30 15:16:34'),
(1834, 'App\\Models\\User', 150, 'auth-token', '3b09b153eebf17cd9f1ffd53e9ea75dcaea10254b714b27880dd1108342bd72e', '[\"*\"]', NULL, NULL, '2026-03-30 15:17:39', '2026-03-30 15:17:39'),
(1835, 'App\\Models\\User', 152, 'auth-token', 'f91b7d40d78eab9f81c83df363a84d79c83d546d8e241b7664ebe0638721969f', '[\"*\"]', NULL, NULL, '2026-03-30 15:18:51', '2026-03-30 15:18:51'),
(1836, 'App\\Models\\User', 158, 'auth-token', '254d0415eb4ee1cde80dbcecf201bc3eab7d5af03ec05039308cd88f1cb1efd7', '[\"*\"]', NULL, NULL, '2026-03-30 15:19:07', '2026-03-30 15:19:07'),
(1837, 'App\\Models\\User', 152, 'auth-token', 'f1a82f4625b4ba3fca3a45ff8b51b6ec407c3ed6fddb23db743b83126fe9b588', '[\"*\"]', NULL, NULL, '2026-03-30 15:19:38', '2026-03-30 15:19:38'),
(1838, 'App\\Models\\User', 150, 'auth-token', '2c0f2bdc77c9f2d19c54d3cb8bb4c68fd1c4fce04eb6644ece9f415e70d2331f', '[\"*\"]', NULL, NULL, '2026-03-30 15:20:25', '2026-03-30 15:20:25'),
(1839, 'App\\Models\\User', 149, 'auth-token', '0bf97b83aa4f3d7e87a9bb187c4b3e6eb46eef261730d15803bf61a9f2144d84', '[\"*\"]', NULL, NULL, '2026-03-30 15:21:10', '2026-03-30 15:21:10'),
(1840, 'App\\Models\\User', 150, 'auth-token', '22da42205359765bd81d0b4dc285c55a880d1f37cb769979ed35645dd258b3a1', '[\"*\"]', NULL, NULL, '2026-03-30 15:21:42', '2026-03-30 15:21:42'),
(1841, 'App\\Models\\User', 158, 'auth-token', '85bb1248a39ae7661894252f0467aa58d8408f6f172ef1c3f5c85074e29b8f01', '[\"*\"]', NULL, NULL, '2026-03-30 15:22:04', '2026-03-30 15:22:04'),
(1842, 'App\\Models\\User', 152, 'auth-token', 'a351cea1271ebb5d409f4824685e905094b9a7290690316bbc05c702f9ce184f', '[\"*\"]', NULL, NULL, '2026-03-30 15:22:21', '2026-03-30 15:22:21'),
(1843, 'App\\Models\\User', 150, 'auth-token', '31e4dbc6be14d83c07a1a4b041b63e9f6ef7f13ce8c79e1229cc0457ed1c4522', '[\"*\"]', NULL, NULL, '2026-03-30 15:22:47', '2026-03-30 15:22:47'),
(1844, 'App\\Models\\User', 158, 'auth-token', '0e23440b55b48e4c8c680b640922e30464942cd80d9be849cf91b22a48e7d787', '[\"*\"]', NULL, NULL, '2026-03-30 15:23:38', '2026-03-30 15:23:38'),
(1845, 'App\\Models\\User', 153, 'auth-token', '769564c4b64c3124e4b6a0f659b7704a558dcbf5d675378b8aeeba972cc08fe6', '[\"*\"]', NULL, NULL, '2026-03-30 15:24:57', '2026-03-30 15:24:57'),
(1846, 'App\\Models\\User', 150, 'auth-token', '60e3dc7d411a64070554c6cf24d2262d46ae0b570888f6ddc0143f84dcae1e53', '[\"*\"]', NULL, NULL, '2026-03-30 15:25:21', '2026-03-30 15:25:21'),
(1847, 'App\\Models\\User', 149, 'auth-token', '8ee07fa7dd108aff6c7a190e03b2d4362de13c9b2c30013164c9eccc7b1fb939', '[\"*\"]', NULL, NULL, '2026-03-30 15:25:51', '2026-03-30 15:25:51'),
(1848, 'App\\Models\\User', 150, 'auth-token', '4366d61e553ec0e371773a42cb4371090b781cbc1b2445bd6e2f74b0e290ae26', '[\"*\"]', NULL, NULL, '2026-03-30 15:26:28', '2026-03-30 15:26:28'),
(1849, 'App\\Models\\User', 154, 'auth-token', 'f98710cb92c37fa8fa430c4a1072698a01a00f0e12af28323ceb449b66551509', '[\"*\"]', NULL, NULL, '2026-03-30 15:27:06', '2026-03-30 15:27:06'),
(1850, 'App\\Models\\User', 151, 'auth-token', '50f32decdda99fb34473ecca5615decdcddb8fe56f3a395b37aadbff59dac521', '[\"*\"]', NULL, NULL, '2026-03-30 15:27:35', '2026-03-30 15:27:35'),
(1851, 'App\\Models\\User', 154, 'auth-token', 'f56ca5f73bc4996844e7967ec223aee7d530a3e73713c1276eff2d47a62ec4ee', '[\"*\"]', NULL, NULL, '2026-03-30 15:28:19', '2026-03-30 15:28:19'),
(1852, 'App\\Models\\User', 154, 'auth-token', '41c4f7bcd02a312d06862ac75c80037edae2d41600138279cb9f56792c8d346b', '[\"*\"]', NULL, NULL, '2026-03-30 15:29:04', '2026-03-30 15:29:04'),
(1853, 'App\\Models\\User', 154, 'auth-token', 'ad4880ea32632929b268e6258759d2d3e6f20f3fc32e1a7ca23c524b4f1f4064', '[\"*\"]', NULL, NULL, '2026-03-30 15:53:27', '2026-03-30 15:53:27'),
(1854, 'App\\Models\\User', 31, 'auth-token', '2a439c147223ff9d0423d29780983abddbfa9281c580d5c69b8d212a2ed9873f', '[\"*\"]', NULL, NULL, '2026-03-30 15:54:14', '2026-03-30 15:54:14'),
(1855, 'App\\Models\\User', 154, 'auth-token', '660c6c905763bf684a987d13ae04df8dff740760b4f7b2c69b78d2ad82e06232', '[\"*\"]', NULL, NULL, '2026-03-30 15:54:58', '2026-03-30 15:54:58'),
(1856, 'App\\Models\\User', 154, 'auth-token', '25ecf63690dfe316417da7b037320e05a084da01c93968764676613f76cc1948', '[\"*\"]', NULL, NULL, '2026-03-30 15:56:59', '2026-03-30 15:56:59'),
(1857, 'App\\Models\\User', 150, 'auth-token', 'f95b3fef5cb890fff0c6c1a7c083e3f695e584de3dda0526507e6d6df23a1543', '[\"*\"]', NULL, NULL, '2026-03-30 15:57:28', '2026-03-30 15:57:28'),
(1858, 'App\\Models\\User', 152, 'auth-token', '0341a7a07df0698f83b461356aa8e81afb4cbe0787951d384d7e38543505d642', '[\"*\"]', NULL, NULL, '2026-03-30 15:57:58', '2026-03-30 15:57:58'),
(1859, 'App\\Models\\User', 150, 'auth-token', 'f9070c9009a98dd9411792e116961eca62810be6465ecd5902634bb991c8d50d', '[\"*\"]', NULL, NULL, '2026-03-30 16:00:39', '2026-03-30 16:00:39'),
(1860, 'App\\Models\\User', 31, 'auth-token', '8cc60e9d7b3c6a168dbb2f34906e889ad6ec44bb407c63f23e6fc1487c271092', '[\"*\"]', NULL, NULL, '2026-03-30 16:03:04', '2026-03-30 16:03:04'),
(1861, 'App\\Models\\User', 159, 'auth-token', 'fa0b35d3f427f40dc21a7eac709dc097e4fe3a46fa12d325b50f05dedbe55f1d', '[\"*\"]', NULL, NULL, '2026-03-30 16:03:21', '2026-03-30 16:03:21'),
(1862, 'App\\Models\\User', 28, 'auth-token', '3f06efcf5dba0230126e0b67ad1fa331b70c9e46825a2a38c635bbad52965ceb', '[\"*\"]', NULL, NULL, '2026-03-30 16:03:45', '2026-03-30 16:03:45'),
(1863, 'App\\Models\\User', 150, 'auth-token', '708fc35681f4012f1df511554877ed0f923521a005e49533c2671007e8cda290', '[\"*\"]', NULL, NULL, '2026-03-30 16:10:26', '2026-03-30 16:10:26'),
(1864, 'App\\Models\\User', 154, 'auth-token', 'f6f2ff03a8cadd3922840437f813870f24a534ca30c60f7319595e1773ad4b83', '[\"*\"]', NULL, NULL, '2026-03-30 16:11:47', '2026-03-30 16:11:47'),
(1865, 'App\\Models\\User', 149, 'auth-token', 'ad80b253bf754fa8fd1534f4cfb144977cce8ac2e1049489354c94b0c6cd859a', '[\"*\"]', NULL, NULL, '2026-03-30 16:12:33', '2026-03-30 16:12:33'),
(1866, 'App\\Models\\User', 150, 'auth-token', '386ca5c0d0fff83f5a9ddf7ca53cf1beb1dcd24c5c61b856e42a39e202babb2a', '[\"*\"]', NULL, NULL, '2026-03-30 16:13:01', '2026-03-30 16:13:01'),
(1867, 'App\\Models\\User', 152, 'auth-token', '46f790e798775ff42571c6ceef10da388aee44a7cd3979d70166f34449b78b84', '[\"*\"]', NULL, NULL, '2026-03-30 16:14:07', '2026-03-30 16:14:07'),
(1868, 'App\\Models\\User', 150, 'auth-token', 'f274f536bb09f5e641c599d78439776b969680c2162fc4949f7643b021466495', '[\"*\"]', NULL, NULL, '2026-03-30 16:14:27', '2026-03-30 16:14:27'),
(1869, 'App\\Models\\User', 152, 'auth-token', 'cea00bd081f4855794f55d5193404730eed6a8311d2cd099dbdfd664b883bc58', '[\"*\"]', NULL, NULL, '2026-03-30 16:15:02', '2026-03-30 16:15:02'),
(1870, 'App\\Models\\User', 150, 'auth-token', '34c1eeea066ec738502ffc787a2dc811a3b55e0e7d28d65feb5a27b3b3ce9744', '[\"*\"]', NULL, NULL, '2026-03-30 16:15:29', '2026-03-30 16:15:29'),
(1871, 'App\\Models\\User', 152, 'auth-token', '684983ff66e5254e2fe189521d0caf45db18128c891b7df2369c52095985eba0', '[\"*\"]', NULL, NULL, '2026-03-30 16:16:01', '2026-03-30 16:16:01'),
(1872, 'App\\Models\\User', 150, 'auth-token', '0d7cf56f77e24d3f4faee3f328aa220271029f2bce4d0c24db0d6b6dfc2fad7a', '[\"*\"]', NULL, NULL, '2026-03-30 16:20:50', '2026-03-30 16:20:50'),
(1873, 'App\\Models\\User', 152, 'auth-token', '201e3c72fedd60b470f123941113c04076399b07f15c735d47c2c238d72002c3', '[\"*\"]', NULL, NULL, '2026-03-30 16:21:21', '2026-03-30 16:21:21'),
(1874, 'App\\Models\\User', 150, 'auth-token', '2354f0dd96715e5d68b60384b502cb961eef038331dbfeb2cef0c5f7e2cf13c5', '[\"*\"]', NULL, NULL, '2026-03-30 16:21:40', '2026-03-30 16:21:40'),
(1875, 'App\\Models\\User', 152, 'auth-token', '425e7f8256ab1ec4fd695dd27c00e39e858f854db2d970570737e9caf947ce3f', '[\"*\"]', NULL, NULL, '2026-03-30 16:28:18', '2026-03-30 16:28:18'),
(1876, 'App\\Models\\User', 154, 'auth-token', '696f056678829846f9623bfde9b3dfa8043d8c48f059e8480d4e2c00f99dcdd4', '[\"*\"]', NULL, NULL, '2026-03-30 16:32:00', '2026-03-30 16:32:00'),
(1877, 'App\\Models\\User', 150, 'auth-token', 'ac19a2975324e5a80fa43167dcaca42c85a068c40337791ae9405239c9d07bfb', '[\"*\"]', NULL, NULL, '2026-03-30 16:32:41', '2026-03-30 16:32:41'),
(1878, 'App\\Models\\User', 149, 'auth-token', 'f6f5282701a546c5b0dcb5e875fba57f87612580b3b531c309a05f1a2d18cf50', '[\"*\"]', NULL, NULL, '2026-03-30 16:33:06', '2026-03-30 16:33:06'),
(1879, 'App\\Models\\User', 150, 'auth-token', '74e462f1d6dacee736a7abad4b8cd81814399619ecd9503663c8a892392c4f77', '[\"*\"]', NULL, NULL, '2026-03-30 16:33:32', '2026-03-30 16:33:32'),
(1880, 'App\\Models\\User', 152, 'auth-token', 'fe7b2ab7c7439195ce098f80996d15445368608c9c9eeb57d9771d21d71c538d', '[\"*\"]', NULL, NULL, '2026-03-30 16:34:23', '2026-03-30 16:34:23'),
(1881, 'App\\Models\\User', 150, 'auth-token', '3ab95f4983183c35a5dd31f3332f75c1c844b45384af681d836f82ae2362c310', '[\"*\"]', NULL, NULL, '2026-03-30 16:35:23', '2026-03-30 16:35:23'),
(1882, 'App\\Models\\User', 149, 'auth-token', '9fd69fc57b001eb5df452664a6d7e1d38affbc34fc40be3b0e29b47d70dc4cf8', '[\"*\"]', NULL, NULL, '2026-03-30 16:36:57', '2026-03-30 16:36:57'),
(1883, 'App\\Models\\User', 154, 'auth-token', '5b59742e95505be567ecd9ba1b81bc9b1656ebccfaf025ce28a4d0afefd62ced', '[\"*\"]', NULL, NULL, '2026-03-30 16:37:23', '2026-03-30 16:37:23'),
(1884, 'App\\Models\\User', 154, 'auth-token', '44106b2fc3aa48f1469d6f30834b2e5f5136eafdd3368ca9107f89698f441ce6', '[\"*\"]', NULL, NULL, '2026-03-30 16:37:40', '2026-03-30 16:37:40'),
(1885, 'App\\Models\\User', 150, 'auth-token', '9209875d168cfa3c268305f79b5df0026ce00cca0daf9643cc1d50f843015321', '[\"*\"]', NULL, NULL, '2026-03-30 16:37:56', '2026-03-30 16:37:56'),
(1886, 'App\\Models\\User', 151, 'auth-token', 'e6f5856fa74e165651a9015d6e8dd26e4b2d3eac59dd2dedc61a5e791a2c4c01', '[\"*\"]', NULL, NULL, '2026-03-30 16:38:31', '2026-03-30 16:38:31'),
(1887, 'App\\Models\\User', 154, 'auth-token', '87a4fc2cc06cc7a7dbe51becd4e691ffe4bcc6c7a98d8add958a4157f8dd8d87', '[\"*\"]', NULL, NULL, '2026-03-30 16:39:10', '2026-03-30 16:39:10'),
(1888, 'App\\Models\\User', 179, 'auth-token', '885cc5a3973af89676c3e6da00aff05c5329180fdd3dca6e302f2f84e911a51c', '[\"*\"]', NULL, NULL, '2026-04-02 05:04:27', '2026-04-02 05:04:27'),
(1889, 'App\\Models\\User', 154, 'auth-token', 'fcce8a2973604257ea8d3e7c980c1f163bc741c9f5b40565b643458b0c717446', '[\"*\"]', NULL, NULL, '2026-04-02 05:07:02', '2026-04-02 05:07:02'),
(1890, 'App\\Models\\User', 162, 'auth-token', '663eeedf37f06ad39d881de2e24f7658b32cec23085460396c3b41cfcc5085b0', '[\"*\"]', NULL, NULL, '2026-04-02 05:11:01', '2026-04-02 05:11:01'),
(1891, 'App\\Models\\User', 152, 'auth-token', 'c8460b49d7470523dadba6d989b88c87381278ba017d5716cf508126783a21bc', '[\"*\"]', NULL, NULL, '2026-04-02 05:11:46', '2026-04-02 05:11:46'),
(1892, 'App\\Models\\User', 154, 'auth-token', '206337f41df25eb9e142589279510715236fd8234e48f4f4fcb91d0faa47df64', '[\"*\"]', NULL, NULL, '2026-04-02 05:12:54', '2026-04-02 05:12:54'),
(1893, 'App\\Models\\User', 162, 'auth-token', '5d75aad12666aed058cdbc77dccd5a1fa59f262390379b10eaffb4e710e6f804', '[\"*\"]', NULL, NULL, '2026-04-02 05:20:54', '2026-04-02 05:20:54'),
(1894, 'App\\Models\\User', 154, 'auth-token', 'a9ecad6fe0826d5919a1d81bf0d96021d03814fad956be3f9dd3d326077a323d', '[\"*\"]', NULL, NULL, '2026-04-02 05:21:31', '2026-04-02 05:21:31'),
(1895, 'App\\Models\\User', 157, 'auth-token', '9d29806b93492234b8c6cc7194a3d158dff604ae7bbc26da0042ed43c191f0f5', '[\"*\"]', NULL, NULL, '2026-04-02 05:22:13', '2026-04-02 05:22:13'),
(1896, 'App\\Models\\User', 31, 'auth-token', '293647cac8dc8241ebc17d1333ba0d9b8f64a25a32fafad22c01e6fc4e4c8080', '[\"*\"]', NULL, NULL, '2026-04-02 05:29:34', '2026-04-02 05:29:34'),
(1897, 'App\\Models\\User', 151, 'auth-token', '29b46124c1ccf3c81f4ab2eda5da5d91724132aab2896ff451323dc8c924a1eb', '[\"*\"]', NULL, NULL, '2026-04-02 05:31:23', '2026-04-02 05:31:23'),
(1898, 'App\\Models\\User', 154, 'auth-token', '4a0ea838a028bff34b67663c3e2e7d93917c02dd41a5eeca272f34b19483cd75', '[\"*\"]', NULL, NULL, '2026-04-02 05:32:00', '2026-04-02 05:32:00'),
(1899, 'App\\Models\\User', 150, 'auth-token', '80d8bfb42c7d18dbf415abf2a86d157028e6ebcd43f7e24557dfb56565fe887a', '[\"*\"]', NULL, NULL, '2026-04-02 05:33:04', '2026-04-02 05:33:04'),
(1900, 'App\\Models\\User', 152, 'auth-token', '553adfc5b0716b1ae063f1b1627e18bdb978fe309ec3bd7b93646c86d4962bcb', '[\"*\"]', NULL, NULL, '2026-04-02 05:33:26', '2026-04-02 05:33:26'),
(1901, 'App\\Models\\User', 158, 'auth-token', 'b16924c048323e0d45f4a1d038df6309c037711a8999820ca4fbfc0e7751c81a', '[\"*\"]', NULL, NULL, '2026-04-02 05:34:15', '2026-04-02 05:34:15'),
(1902, 'App\\Models\\User', 150, 'auth-token', 'c716163e7060f93859acb6ebd999486de2025975823f21b699edc27e9a1f4b2e', '[\"*\"]', NULL, NULL, '2026-04-02 05:34:56', '2026-04-02 05:34:56'),
(1903, 'App\\Models\\User', 149, 'auth-token', '8827ea8ae4ba89dfbe7bb2f9b9413ac044e0774b6bed76a7c2152dbd6690d304', '[\"*\"]', NULL, NULL, '2026-04-02 05:35:35', '2026-04-02 05:35:35'),
(1904, 'App\\Models\\User', 150, 'auth-token', 'f4e80e4156bf62f0796042982ae5b4fb63a85039ce9bdc6d8964fda8a3e5a988', '[\"*\"]', NULL, NULL, '2026-04-02 05:36:16', '2026-04-02 05:36:16'),
(1905, 'App\\Models\\User', 158, 'auth-token', '5a6123270d0900f7572eae1270eb4fc7f5195bff16356a2bb256ddc5d42a1e95', '[\"*\"]', NULL, NULL, '2026-04-02 05:36:37', '2026-04-02 05:36:37'),
(1906, 'App\\Models\\User', 152, 'auth-token', 'b022001037994cd8e1eaf17ed3ae78288d92e3ecdd602bad6d86824c078a58e1', '[\"*\"]', NULL, NULL, '2026-04-02 05:36:49', '2026-04-02 05:36:49'),
(1907, 'App\\Models\\User', 158, 'auth-token', 'f8d98e8b18d8cb0cb05d211425ba474adc83f70962050546e5de0316dbc0c605', '[\"*\"]', NULL, NULL, '2026-04-02 05:37:04', '2026-04-02 05:37:04'),
(1908, 'App\\Models\\User', 150, 'auth-token', '7527de373c2c82199663e920b49566743446b1ef36a8ea75aa60340de851d395', '[\"*\"]', NULL, NULL, '2026-04-02 05:37:26', '2026-04-02 05:37:26'),
(1909, 'App\\Models\\User', 149, 'auth-token', 'dfad48df2f2dd2a82fe293135fcd24d250b44f36e0214b4c0a92e3f1d023a7ea', '[\"*\"]', NULL, NULL, '2026-04-02 05:38:28', '2026-04-02 05:38:28'),
(1910, 'App\\Models\\User', 153, 'auth-token', 'eca20bdedf8e857cb1b56b1dc515b47efd0f9b9691a8b498f5ae9f1a50f6eeda', '[\"*\"]', NULL, NULL, '2026-04-02 05:38:59', '2026-04-02 05:38:59'),
(1911, 'App\\Models\\User', 150, 'auth-token', 'b9ab42b6f1a17cb01633524c65c7955c697aae6f9db2679bb3567d3f8ac1e5ac', '[\"*\"]', NULL, NULL, '2026-04-02 05:39:55', '2026-04-02 05:39:55'),
(1912, 'App\\Models\\User', 162, 'auth-token', '308fe3b5292a57fd61259758e2ff438c98894df15573afbdc930d716825ba6af', '[\"*\"]', NULL, NULL, '2026-04-02 05:40:27', '2026-04-02 05:40:27'),
(1913, 'App\\Models\\User', 151, 'auth-token', '157e44ebebedadd6a20cc4c428568061d8d4c4af0ed3a9fb5828a3c4291f9556', '[\"*\"]', NULL, NULL, '2026-04-02 05:41:26', '2026-04-02 05:41:26');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1914, 'App\\Models\\User', 153, 'auth-token', '0bf43d3c60fb349b954875b37b20c795cfcdcf8fccb7d93373c43f58a35cfa00', '[\"*\"]', NULL, NULL, '2026-04-02 05:42:19', '2026-04-02 05:42:19'),
(1915, 'App\\Models\\User', 154, 'auth-token', '03de7f992237edc10f48364e440f607a9b6d1c535d67ba136cd8e6f01a4615a7', '[\"*\"]', NULL, NULL, '2026-04-02 05:42:57', '2026-04-02 05:42:57'),
(1916, 'App\\Models\\User', 150, 'auth-token', 'e39d55ea581f0433d662f76f1cb9217df288a296ce554a01965c76f2a4ce8cd5', '[\"*\"]', NULL, NULL, '2026-04-02 05:50:22', '2026-04-02 05:50:22'),
(1917, 'App\\Models\\User', 152, 'auth-token', '2343fbe4705d8d6ef4eb7b923c8d0fb0406e4c085470ae694f33fd955527c348', '[\"*\"]', NULL, NULL, '2026-04-02 05:53:17', '2026-04-02 05:53:17'),
(1918, 'App\\Models\\User', 158, 'auth-token', 'aa7784b7cd4f98bc446c6b85045cbd6b9caf2db6ce5a5c03de66ccd14790f0f8', '[\"*\"]', NULL, NULL, '2026-04-02 05:55:06', '2026-04-02 05:55:06'),
(1919, 'App\\Models\\User', 150, 'auth-token', '473b2a34757619f42ab0bd8470c2509ad4450455e0ed8f9e16048ac8ce2e781c', '[\"*\"]', NULL, NULL, '2026-04-02 05:56:07', '2026-04-02 05:56:07'),
(1920, 'App\\Models\\User', 149, 'auth-token', '211e86a4039c3a3b3d3a5e824e8ad988bf1034614fa8ce99e1a069fb995c6498', '[\"*\"]', NULL, NULL, '2026-04-02 05:57:06', '2026-04-02 05:57:06'),
(1921, 'App\\Models\\User', 150, 'auth-token', '875637cdfba0c52061493dd72634453970c7d88bd61b2628132987533f8a591e', '[\"*\"]', NULL, NULL, '2026-04-02 05:57:59', '2026-04-02 05:57:59'),
(1922, 'App\\Models\\User', 152, 'auth-token', '413776652a67d98b69c62c51f2f578d29ab242a85b37720fcd1e057a47064295', '[\"*\"]', NULL, NULL, '2026-04-02 05:58:36', '2026-04-02 05:58:36'),
(1923, 'App\\Models\\User', 150, 'auth-token', 'd842cdab816fb679000b174b56db534ec02f8fc1bcc7ad5dbe0dd90e00ed446d', '[\"*\"]', NULL, NULL, '2026-04-02 06:01:56', '2026-04-02 06:01:56'),
(1924, 'App\\Models\\User', 149, 'auth-token', 'b4e68c2c4d3ff30d247629a1ede8d29a568d6dfe7921cb1c3da0dd29f4256fa3', '[\"*\"]', NULL, NULL, '2026-04-02 06:03:28', '2026-04-02 06:03:28'),
(1925, 'App\\Models\\User', 150, 'auth-token', 'd7455069f89983dba34591ec09dc614b2e0f338abd7a4ea3871d319441ef3e8d', '[\"*\"]', NULL, NULL, '2026-04-02 06:04:32', '2026-04-02 06:04:32'),
(1926, 'App\\Models\\User', 154, 'auth-token', '7deaccca8dd008236a6e4a1398e249eb98035f5c6e101cd56568ae68a0275c33', '[\"*\"]', NULL, NULL, '2026-04-02 06:05:22', '2026-04-02 06:05:22'),
(1927, 'App\\Models\\User', 151, 'auth-token', 'f57c2220f0c6b21b51f7577343bd5731521e557d4634f656b9908742e4c61f82', '[\"*\"]', NULL, NULL, '2026-04-02 06:05:49', '2026-04-02 06:05:49'),
(1928, 'App\\Models\\User', 154, 'auth-token', '82a1dd1268d7815820f814a14a1ca39396007e24f896b4eebfd40ad42d2034ae', '[\"*\"]', NULL, NULL, '2026-04-02 06:06:22', '2026-04-02 06:06:22'),
(1929, 'App\\Models\\User', 147, 'auth-token', '64471572bf5304a79742a8549dca35c6531d2418412063eca4676d8dde7cac82', '[\"*\"]', NULL, NULL, '2026-04-02 06:10:04', '2026-04-02 06:10:04'),
(1930, 'App\\Models\\User', 154, 'auth-token', 'e14d0ad6913f122f82623a4b272a10517fb44dad6e79bd1a258d68ce1bac771e', '[\"*\"]', NULL, NULL, '2026-04-02 06:25:55', '2026-04-02 06:25:55'),
(1931, 'App\\Models\\User', 31, 'auth-token', '6490f04fd7496319cfb84ac87e6c00203664867b2ea9171ff3d7ee1a93b82c65', '[\"*\"]', NULL, NULL, '2026-04-02 07:51:18', '2026-04-02 07:51:18'),
(1932, 'App\\Models\\User', 147, 'auth-token', '23d6dd83a0697f38dd71b9e9a880901fb7c4a3477af29698b7a1addfe54ac705', '[\"*\"]', NULL, NULL, '2026-04-02 08:08:05', '2026-04-02 08:08:05'),
(1933, 'App\\Models\\User', 154, 'auth-token', 'c41a5901e12754eedb9e041df0be9ff67e054f0f6dc09889e2c7dbd81d3b11f9', '[\"*\"]', NULL, NULL, '2026-04-02 08:26:50', '2026-04-02 08:26:50'),
(1934, 'App\\Models\\User', 31, 'auth-token', '1e7e356a7cbeab9a0af9ef9f61f40182e5a1f083cb80db9061370d1ddd656d55', '[\"*\"]', NULL, NULL, '2026-04-02 08:30:02', '2026-04-02 08:30:02'),
(1935, 'App\\Models\\User', 150, 'auth-token', 'ba40241da8ac26fc6041510fb2699c10054151df9a18845813ed5ca93064fb16', '[\"*\"]', NULL, NULL, '2026-04-02 08:30:34', '2026-04-02 08:30:34'),
(1936, 'App\\Models\\User', 154, 'auth-token', '550f4c449095ace6cc128915793d08066fbf970f43ef933bee460b9cef636ef9', '[\"*\"]', NULL, NULL, '2026-04-02 08:30:52', '2026-04-02 08:30:52'),
(1937, 'App\\Models\\User', 150, 'auth-token', 'f72388fc306d6f7823c6eff3fded1a10116a927cfcdd266324fd13eedd2a45a8', '[\"*\"]', NULL, NULL, '2026-04-02 08:31:56', '2026-04-02 08:31:56'),
(1938, 'App\\Models\\User', 158, 'auth-token', '9d210b3e9b8c3eac717b2ac0939ff0bf70652b1b3e4411ef47cc8cf52c3d8956', '[\"*\"]', NULL, NULL, '2026-04-02 08:32:59', '2026-04-02 08:32:59'),
(1939, 'App\\Models\\User', 150, 'auth-token', '1b49446cc32fcf3a5b6b1651dd1be9aaf5d09601b0c05a25711b9c2d5556ed62', '[\"*\"]', NULL, NULL, '2026-04-02 08:37:47', '2026-04-02 08:37:47'),
(1940, 'App\\Models\\User', 149, 'auth-token', 'c3afb7d817067c0964b8fe993a4106df18e70a775fe05f0256057db923dd33c1', '[\"*\"]', NULL, NULL, '2026-04-02 08:38:23', '2026-04-02 08:38:23'),
(1941, 'App\\Models\\User', 150, 'auth-token', 'c1ce125109d8b10c99bb54a47beca92668b5998cf75f5356954cf881feb77b0f', '[\"*\"]', NULL, NULL, '2026-04-02 08:39:45', '2026-04-02 08:39:45'),
(1942, 'App\\Models\\User', 152, 'auth-token', 'db81220ef64452f71042e3d71181017cdd7fa7ff16cbef4781fc144edae6908e', '[\"*\"]', NULL, NULL, '2026-04-02 08:40:03', '2026-04-02 08:40:03'),
(1943, 'App\\Models\\User', 158, 'auth-token', '396b467c3a74f288b61cca28885e6da30a38812764a432a006145485bfd8414d', '[\"*\"]', NULL, NULL, '2026-04-02 08:40:34', '2026-04-02 08:40:34'),
(1944, 'App\\Models\\User', 28, 'auth-token', '633fc8561f88ea9e72510fd4a8266f31106214044a8061d944f7499ff4f4ec6f', '[\"*\"]', NULL, NULL, '2026-04-02 08:41:04', '2026-04-02 08:41:04'),
(1945, 'App\\Models\\User', 150, 'auth-token', '16c3042bc2a86dff515210f096d4ead958282ca7fb7f889486d7eaff775ec4ba', '[\"*\"]', NULL, NULL, '2026-04-02 08:41:31', '2026-04-02 08:41:31'),
(1946, 'App\\Models\\User', 149, 'auth-token', '304fa7bfe32dffbbeef4eae41ee47a236f11193f4b8875e5c366d0e799455023', '[\"*\"]', NULL, NULL, '2026-04-02 08:42:10', '2026-04-02 08:42:10'),
(1947, 'App\\Models\\User', 174, 'auth-token', '40ebfd2d918c2e278a0e13dd6176e5e8eb71cfa9bd27d14faf8d0b9440b5f6b7', '[\"*\"]', NULL, NULL, '2026-04-02 08:42:45', '2026-04-02 08:42:45'),
(1948, 'App\\Models\\User', 150, 'auth-token', '7556fedced44efc8a0bf3f17f256a7483c76d5d0b3ea3dbe593db8fe223f3fd7', '[\"*\"]', NULL, NULL, '2026-04-02 08:43:08', '2026-04-02 08:43:08'),
(1949, 'App\\Models\\User', 151, 'auth-token', '5aaa45632eceed5409862b1ece4a083707b2b6aaa308e8c54d16ba7eb56d98ea', '[\"*\"]', NULL, NULL, '2026-04-02 08:43:39', '2026-04-02 08:43:39'),
(1950, 'App\\Models\\User', 154, 'auth-token', '67886f9518085ab7941ee37d8ed91fcb1e6ff0f3e26cbd10a92f2189795e71fe', '[\"*\"]', NULL, NULL, '2026-04-02 08:48:15', '2026-04-02 08:48:15'),
(1951, 'App\\Models\\User', 31, 'auth-token', 'b04eaae5eab3ad63c5f391bf6df79d01c005c4c9490940b9c582bba67cb34300', '[\"*\"]', NULL, NULL, '2026-04-02 08:49:57', '2026-04-02 08:49:57'),
(1952, 'App\\Models\\User', 147, 'auth-token', 'd00b3d3056a8c442a3a415347b434e0bbecef492d068ebe2fba9849aff0b54f2', '[\"*\"]', NULL, NULL, '2026-04-02 08:50:18', '2026-04-02 08:50:18'),
(1953, 'App\\Models\\User', 153, 'auth-token', 'd5ebbd214e90a0a3d8cfdc3c8dd2b24739e0004a8858d6dac8a315d5a3979a9b', '[\"*\"]', NULL, NULL, '2026-04-02 08:50:55', '2026-04-02 08:50:55'),
(1954, 'App\\Models\\User', 147, 'auth-token', '9855d5b959f61c875b080ca4cdcca01aae5baefae4c421cef3ba693da92c5e48', '[\"*\"]', NULL, NULL, '2026-04-02 08:56:57', '2026-04-02 08:56:57'),
(1955, 'App\\Models\\User', 159, 'auth-token', '0d17d00318081a31b66349592eee03242804d139aa611cdda12a056ff2c75831', '[\"*\"]', NULL, NULL, '2026-04-02 09:06:15', '2026-04-02 09:06:15'),
(1956, 'App\\Models\\User', 147, 'auth-token', '1f7c5ce48270f42a777d20cdd014f00a713fa6dc52fb72a5d144658ce5836ec1', '[\"*\"]', NULL, NULL, '2026-04-02 09:06:47', '2026-04-02 09:06:47'),
(1957, 'App\\Models\\User', 147, 'auth-token', 'ad8fa6c62022029d84d200223f8c8ac9861e082c80c54aac004ba39b9ee02609', '[\"*\"]', NULL, NULL, '2026-04-02 09:14:33', '2026-04-02 09:14:33'),
(1958, 'App\\Models\\User', 153, 'auth-token', 'b11ed3bbad441dd77f45a02e7d02e8f7b1fdee62a042d144286472d7d1c97c30', '[\"*\"]', NULL, NULL, '2026-04-02 09:30:57', '2026-04-02 09:30:57'),
(1959, 'App\\Models\\User', 154, 'auth-token', '977bbb62a10a8581081267eac4fac92e1c741943d5188be4acf7952f189c04d4', '[\"*\"]', NULL, NULL, '2026-04-02 09:33:56', '2026-04-02 09:33:56'),
(1960, 'App\\Models\\User', 147, 'auth-token', '5b45c4e7791dcfb9f4b45179f04d9a725dec77357abad4df07b91839c0f71b8d', '[\"*\"]', NULL, NULL, '2026-04-02 09:37:34', '2026-04-02 09:37:34'),
(1961, 'App\\Models\\User', 150, 'auth-token', '052267e98a266dfaaca5ddf722de360ad8137a66871f459135d7ded273bd2e0b', '[\"*\"]', NULL, NULL, '2026-04-02 09:43:22', '2026-04-02 09:43:22'),
(1962, 'App\\Models\\User', 149, 'auth-token', '6e108093daeaa05f918a17b04f9d0289edc2497b12d6646f072a54e68a4175ce', '[\"*\"]', NULL, NULL, '2026-04-02 09:44:33', '2026-04-02 09:44:33'),
(1963, 'App\\Models\\User', 150, 'auth-token', '562b564ffe2aa5812ebd0d17eface4c64f1dddfa458d7f98f052426fd0db3d3b', '[\"*\"]', NULL, NULL, '2026-04-02 09:45:34', '2026-04-02 09:45:34'),
(1964, 'App\\Models\\User', 158, 'auth-token', 'e2bcd454c7464dff2f7b653e60f29b5969b17974abb90069092f3b92944bf44d', '[\"*\"]', NULL, NULL, '2026-04-02 09:45:59', '2026-04-02 09:45:59'),
(1965, 'App\\Models\\User', 160, 'auth-token', 'e62d94bb21b20fb4f4e38114e092e1263bdda1849c9fd1db53b4829798f7e567', '[\"*\"]', NULL, NULL, '2026-04-02 09:46:22', '2026-04-02 09:46:22'),
(1966, 'App\\Models\\User', 150, 'auth-token', 'c52f32b727d98ca16106913283e36baf25b358aef38eaa5e7c2efa6045085d1e', '[\"*\"]', NULL, NULL, '2026-04-02 09:47:00', '2026-04-02 09:47:00'),
(1967, 'App\\Models\\User', 150, 'auth-token', '5b715a7c7b64ce905f891433584d362e587e1f1d10e4ab1ca1a949dade2794c2', '[\"*\"]', NULL, NULL, '2026-04-02 09:47:53', '2026-04-02 09:47:53'),
(1968, 'App\\Models\\User', 158, 'auth-token', 'bee4b1850b1eb309f65d5dd94aaac6c715f2f3ff58adb4fb3a164b0ba60bbed4', '[\"*\"]', NULL, NULL, '2026-04-02 09:48:30', '2026-04-02 09:48:30'),
(1969, 'App\\Models\\User', 150, 'auth-token', 'a85d0f3a331772a43d574b4b41ce86c9957770e6b20815a13075faf79e5db432', '[\"*\"]', NULL, NULL, '2026-04-02 09:48:53', '2026-04-02 09:48:53'),
(1970, 'App\\Models\\User', 149, 'auth-token', '49181fe2f0b40d8ad027e5944fc64b787d0d9dc4ac8d56d36bdff74de51383f2', '[\"*\"]', NULL, NULL, '2026-04-02 09:49:31', '2026-04-02 09:49:31'),
(1971, 'App\\Models\\User', 150, 'auth-token', 'b2870c3a841bf06bf70cda4f4e74627add49f2046eb0dee0276c2b308cccee05', '[\"*\"]', NULL, NULL, '2026-04-02 09:50:43', '2026-04-02 09:50:43'),
(1972, 'App\\Models\\User', 150, 'auth-token', '73c2c8b0f05c54f83d89eaeaee2537407a6b5a63e18efcd0150ee89846dc54b0', '[\"*\"]', NULL, NULL, '2026-04-02 09:53:07', '2026-04-02 09:53:07'),
(1973, 'App\\Models\\User', 149, 'auth-token', '9de29d2f3462cf934e5c0a5f0b66c729d7845b908a269627a0bba9e499748a0a', '[\"*\"]', NULL, NULL, '2026-04-02 09:53:54', '2026-04-02 09:53:54'),
(1974, 'App\\Models\\User', 150, 'auth-token', '9175b6f7c3b080615b40ec658854fcc50619632379f7ae50d8e2398c1c43c535', '[\"*\"]', NULL, NULL, '2026-04-02 09:54:32', '2026-04-02 09:54:32'),
(1975, 'App\\Models\\User', 151, 'auth-token', '661bd0e06f9f6841d99f5bdfe59f729c4b60a5015bf0fc4a4a832d9462d4b6fa', '[\"*\"]', NULL, NULL, '2026-04-02 09:54:51', '2026-04-02 09:54:51'),
(1976, 'App\\Models\\User', 154, 'auth-token', '34f258bd14f0c4d004b230bb58be4a2181e101786f8de5f5e4a93c296afa60c2', '[\"*\"]', NULL, NULL, '2026-04-02 09:55:34', '2026-04-02 09:55:34'),
(1977, 'App\\Models\\User', 150, 'auth-token', '0fadefcb9a9c46dcc9728407828ee1c181490835ab2e4bb757ba11a874ccf300', '[\"*\"]', NULL, NULL, '2026-04-02 09:57:09', '2026-04-02 09:57:09'),
(1978, 'App\\Models\\User', 152, 'auth-token', 'aa45a56fc18cd2565f438e33bf4738c0231c7273b1c3087698c13e7b2fd3c27d', '[\"*\"]', NULL, NULL, '2026-04-02 09:57:53', '2026-04-02 09:57:53'),
(1979, 'App\\Models\\User', 158, 'auth-token', 'c99dd1db9ebdde885f0db682ad7cb7b13323a2976c6edd083e026679efa12b23', '[\"*\"]', NULL, NULL, '2026-04-02 10:00:17', '2026-04-02 10:00:17'),
(1980, 'App\\Models\\User', 150, 'auth-token', 'e3d0243f92c5a992d622b29cf9cd7750b762c9b1d7f4d484fdded61c0b93141b', '[\"*\"]', NULL, NULL, '2026-04-02 10:00:46', '2026-04-02 10:00:46'),
(1981, 'App\\Models\\User', 149, 'auth-token', '25135f4ac4380eb765ebbd1a6034149647c482bca96ea44868548f679be42971', '[\"*\"]', NULL, NULL, '2026-04-02 10:01:37', '2026-04-02 10:01:37'),
(1982, 'App\\Models\\User', 149, 'auth-token', '172187d0bcef8ea96d6d16bc1a2ec1cc94b6e7fbf0b5845793320ab4648d642f', '[\"*\"]', NULL, NULL, '2026-04-02 10:02:22', '2026-04-02 10:02:22'),
(1983, 'App\\Models\\User', 152, 'auth-token', 'c32d989a0d84adadb19c77c066feebd5d9194f37eda7a21d3f3adf0eb76ae092', '[\"*\"]', NULL, NULL, '2026-04-02 10:02:55', '2026-04-02 10:02:55'),
(1984, 'App\\Models\\User', 158, 'auth-token', '15d689e2986795d95909ba995348720086ff7479d2882be21954e3daa649ca32', '[\"*\"]', NULL, NULL, '2026-04-02 10:03:09', '2026-04-02 10:03:09'),
(1985, 'App\\Models\\User', 150, 'auth-token', 'a58d3be0823bdbc3a21f02080b3657d18ed9aabe1882f2ca295f606393c6a5c9', '[\"*\"]', NULL, NULL, '2026-04-02 10:03:27', '2026-04-02 10:03:27'),
(1986, 'App\\Models\\User', 152, 'auth-token', 'd6e75cd4cd5e4e1485c2386cd1124e68ad0c493a239e1d019f5f7fce18b4b5fd', '[\"*\"]', NULL, NULL, '2026-04-02 10:03:50', '2026-04-02 10:03:50'),
(1987, 'App\\Models\\User', 150, 'auth-token', '5c6a0c926e67bddd0dbf1e4cb3e25f626741364ca2ccc90da1c3ac2fb7d6518b', '[\"*\"]', NULL, NULL, '2026-04-02 10:04:12', '2026-04-02 10:04:12'),
(1988, 'App\\Models\\User', 150, 'auth-token', '0e8bfd9877bb3c85661c8c200c4a647b0dad330e61d573f85864a5ab0ea24974', '[\"*\"]', NULL, NULL, '2026-04-02 10:04:54', '2026-04-02 10:04:54'),
(1989, 'App\\Models\\User', 149, 'auth-token', 'b43293e7496d79d209971e82dbe1a0f18b5b925e93a5be3749e89c1d219f77e8', '[\"*\"]', NULL, NULL, '2026-04-02 10:05:16', '2026-04-02 10:05:16'),
(1990, 'App\\Models\\User', 149, 'auth-token', '80bfa624f030087f6347b513f2b690fd4a9e854ccc188b3fb97898a439eebfc5', '[\"*\"]', NULL, NULL, '2026-04-02 10:05:36', '2026-04-02 10:05:36'),
(1991, 'App\\Models\\User', 150, 'auth-token', '9dd109ee73ac3e2f33439a0a624bba5543559a6c54af309f93caa10b62e4e138', '[\"*\"]', NULL, NULL, '2026-04-02 10:05:55', '2026-04-02 10:05:55'),
(1992, 'App\\Models\\User', 154, 'auth-token', 'ac113fc838a2b9dc941f9c32d1984b00be43631ce0ac4a5b7232cd56bb653307', '[\"*\"]', NULL, NULL, '2026-04-02 10:06:22', '2026-04-02 10:06:22'),
(1993, 'App\\Models\\User', 151, 'auth-token', 'b973f884cdbc9adf7ce2429206184116d36b87f393e97b94af949aa712243bb6', '[\"*\"]', NULL, NULL, '2026-04-02 10:06:51', '2026-04-02 10:06:51'),
(1994, 'App\\Models\\User', 154, 'auth-token', '7c3d3bc66f4069c8046b6094ab581009d556050896d732c34ffd9996425749b5', '[\"*\"]', NULL, NULL, '2026-04-02 10:07:58', '2026-04-02 10:07:58'),
(1995, 'App\\Models\\User', 153, 'auth-token', 'c1f5f61b9b53aaf846d996728fd75e7c976ea8b6b417022f5936858734413e55', '[\"*\"]', NULL, NULL, '2026-04-02 10:08:19', '2026-04-02 10:08:19'),
(1996, 'App\\Models\\User', 157, 'auth-token', '7f606500b22a44fceee9280d7c9687120f1a52a12e53c1e5b9545c6d4f7de13a', '[\"*\"]', NULL, NULL, '2026-04-02 10:19:31', '2026-04-02 10:19:31'),
(1997, 'App\\Models\\User', 157, 'auth-token', '616cba52b89ef6e24bef10e32662079392cf26832d3ec5f3b7d4820c87060df0', '[\"*\"]', NULL, NULL, '2026-04-02 10:20:08', '2026-04-02 10:20:08'),
(1998, 'App\\Models\\User', 157, 'auth-token', 'f78e1a961a22df42cddd5230ceae1a8c684abef6e36d0eefe37c368a9c81ba05', '[\"*\"]', NULL, NULL, '2026-04-02 10:52:20', '2026-04-02 10:52:20'),
(1999, 'App\\Models\\User', 154, 'auth-token', '954c9a799d9736f72892b7064b2e1f844b157e376db020ba8d693c6005832d52', '[\"*\"]', NULL, NULL, '2026-04-02 10:54:27', '2026-04-02 10:54:27'),
(2000, 'App\\Models\\User', 31, 'auth-token', 'e301d626f2661e0c318f1a10285dc84863353d35ca0bae5dd4a0e4054d95e836', '[\"*\"]', NULL, NULL, '2026-04-02 10:54:52', '2026-04-02 10:54:52'),
(2001, 'App\\Models\\User', 154, 'auth-token', '95f03fbe8970a86798e381ef68aacf59a81bada04cc8d11483c90c92d84e8c03', '[\"*\"]', NULL, NULL, '2026-04-02 11:04:10', '2026-04-02 11:04:10'),
(2002, 'App\\Models\\User', 150, 'auth-token', 'e76799b6a59e1b1c1df725b53a034d16f9a94c4e21fd64c38f2f8ceab233c910', '[\"*\"]', NULL, NULL, '2026-04-02 11:04:37', '2026-04-02 11:04:37'),
(2003, 'App\\Models\\User', 157, 'auth-token', '5e6ac8d534e2a41e72ed4579e9edd5839e4f9ea3b8e081b68ab6c412453ebb8b', '[\"*\"]', NULL, NULL, '2026-04-02 11:15:01', '2026-04-02 11:15:01'),
(2004, 'App\\Models\\User', 31, 'auth-token', '24d19d4226e7da159df12c4e9c5ba452da579902281addc0f503639fc73cc6f4', '[\"*\"]', NULL, NULL, '2026-04-02 11:19:48', '2026-04-02 11:19:48'),
(2005, 'App\\Models\\User', 154, 'auth-token', '947038e3c91db2cfc63ef2ea5ad3b99905a2afb2cace3372580fc7c991b3ca66', '[\"*\"]', NULL, NULL, '2026-04-02 11:20:21', '2026-04-02 11:20:21'),
(2006, 'App\\Models\\User', 150, 'auth-token', '7e1dd05e511625b705e952438c3c4e31aa027cb96573e584599b4aabf8615b88', '[\"*\"]', NULL, NULL, '2026-04-02 11:21:02', '2026-04-02 11:21:02'),
(2007, 'App\\Models\\User', 152, 'auth-token', '11882dd230ed8474939061a38717e36063e232a9fda9be921021e29826e7f63f', '[\"*\"]', NULL, NULL, '2026-04-02 11:21:29', '2026-04-02 11:21:29'),
(2008, 'App\\Models\\User', 158, 'auth-token', 'a151c2649ef10df583a639ce789905db735731ffe3e1be34db763c8c7344cc39', '[\"*\"]', NULL, NULL, '2026-04-02 11:23:29', '2026-04-02 11:23:29'),
(2009, 'App\\Models\\User', 150, 'auth-token', 'b69966eabd3b017045c5e4c598931f87d26afd0d91130f5ffb9d8ba3080842bb', '[\"*\"]', NULL, NULL, '2026-04-02 11:24:18', '2026-04-02 11:24:18'),
(2010, 'App\\Models\\User', 147, 'auth-token', '2437fefc0d2305ea69cd02a6b230baacdcf00513f71bd41cb4fedced5269105f', '[\"*\"]', NULL, NULL, '2026-04-02 11:25:18', '2026-04-02 11:25:18'),
(2011, 'App\\Models\\User', 157, 'auth-token', '4e7095faa47ede7dd86dee17170dd5a2bd77718c78c73e88b2b34e15de5eaae7', '[\"*\"]', NULL, NULL, '2026-04-02 11:29:51', '2026-04-02 11:29:51'),
(2012, 'App\\Models\\User', 157, 'auth-token', '9d1ca58b428d40ca4fdb82c89b0836acb6224ef033ea6b1d0ccbf73aabba198b', '[\"*\"]', NULL, NULL, '2026-04-02 11:31:36', '2026-04-02 11:31:36'),
(2013, 'App\\Models\\User', 31, 'auth-token', '7efc1cab26a77b956916d431279088e165cf77fd100113182a40eaf86c85ca83', '[\"*\"]', NULL, NULL, '2026-04-02 11:31:54', '2026-04-02 11:31:54'),
(2014, 'App\\Models\\User', 154, 'auth-token', '7a556dd44700f46bea2af06e7945dfff75543dfa0c976349d1c8e52bedacb1c3', '[\"*\"]', NULL, NULL, '2026-04-02 11:32:48', '2026-04-02 11:32:48'),
(2015, 'App\\Models\\User', 150, 'auth-token', '89712df1f9cff828bd5ddbde579fef07fc67a5a030d50e99fcf2a77e69b43cae', '[\"*\"]', NULL, NULL, '2026-04-02 11:33:17', '2026-04-02 11:33:17'),
(2016, 'App\\Models\\User', 152, 'auth-token', 'b9478da0a1e9e21140df9b5372346675e53b61c557f351cc7ba4d632ec2bb73b', '[\"*\"]', NULL, NULL, '2026-04-02 11:33:51', '2026-04-02 11:33:51'),
(2017, 'App\\Models\\User', 150, 'auth-token', '18ca71c1542ec8ee59a4c4e978640fc601502ea695e3f39362af40f8e2239496', '[\"*\"]', NULL, NULL, '2026-04-02 11:35:10', '2026-04-02 11:35:10'),
(2018, 'App\\Models\\User', 158, 'auth-token', '8edcf9969e82f91cca3a5d8fe03c7a49faeeda1c6d821962a072079d5f92204a', '[\"*\"]', NULL, NULL, '2026-04-02 11:35:23', '2026-04-02 11:35:23'),
(2019, 'App\\Models\\User', 150, 'auth-token', 'ff68480bdc388497ac0f5adb88abd1ff341a2ee861344a2fd8d1e352943daaba', '[\"*\"]', NULL, NULL, '2026-04-02 11:35:54', '2026-04-02 11:35:54'),
(2020, 'App\\Models\\User', 153, 'auth-token', '940df8146ed2d03533c5a66c7ca34c1357fe44fa7602fcbca9dd8b02097afed9', '[\"*\"]', NULL, NULL, '2026-04-02 11:37:09', '2026-04-02 11:37:09'),
(2021, 'App\\Models\\User', 147, 'auth-token', 'f508c0d0197780d1ea55a67cab35ed6abcda4e9e13e04c3cb884880537a3e974', '[\"*\"]', NULL, NULL, '2026-04-02 11:37:46', '2026-04-02 11:37:46'),
(2022, 'App\\Models\\User', 157, 'auth-token', 'ae6f9b546b776538ebd988af5a41fcec887dcb7aa4593fddabb486fc9e442ea9', '[\"*\"]', NULL, NULL, '2026-04-02 11:42:37', '2026-04-02 11:42:37'),
(2023, 'App\\Models\\User', 31, 'auth-token', '221d2f83195755e1fddbf08a3e5c8340d66ce45aaf1bffe728348a7a7e629aaa', '[\"*\"]', NULL, NULL, '2026-04-02 11:43:20', '2026-04-02 11:43:20'),
(2024, 'App\\Models\\User', 147, 'auth-token', 'fac9707a7dba0eca59d5d8ced7e6691bc394c3cfe372e86eb0080fcda24cea3c', '[\"*\"]', NULL, NULL, '2026-04-02 11:43:43', '2026-04-02 11:43:43'),
(2025, 'App\\Models\\User', 31, 'auth-token', '9ee526567c4bcf723023c3e4968e31e559ce49e972c752969839c14e05cceb65', '[\"*\"]', NULL, NULL, '2026-04-02 11:44:10', '2026-04-02 11:44:10'),
(2026, 'App\\Models\\User', 154, 'auth-token', 'f3f984b9c8a8fa2257ceee225bfdb5083ed282601d61b7dfcb785050f50a470e', '[\"*\"]', NULL, NULL, '2026-04-02 11:45:24', '2026-04-02 11:45:24'),
(2027, 'App\\Models\\User', 150, 'auth-token', 'f59649d355af6c083ee8d3941c25a0c906175e84e86876585808da41eb952eb8', '[\"*\"]', NULL, NULL, '2026-04-02 11:46:13', '2026-04-02 11:46:13'),
(2028, 'App\\Models\\User', 147, 'auth-token', 'c2c74f0cfa9d006d04f9b8e1c63fdef5083626c84bbe6486dacf38a5e0ed5c7d', '[\"*\"]', NULL, NULL, '2026-04-02 11:46:35', '2026-04-02 11:46:35'),
(2029, 'App\\Models\\User', 152, 'auth-token', '18e06c7033af7e561dc3c0c3bf8e84130247b3e76e79665c6ba147c33b2cc92d', '[\"*\"]', NULL, NULL, '2026-04-02 11:47:44', '2026-04-02 11:47:44'),
(2030, 'App\\Models\\User', 158, 'auth-token', 'f6f92b23b35bbb6c60d22dc649a4ca0d83aca555df1a48609a9cb2159c50a2a7', '[\"*\"]', NULL, NULL, '2026-04-02 11:49:13', '2026-04-02 11:49:13'),
(2031, 'App\\Models\\User', 150, 'auth-token', '88d416d4e6be9777f2f9cd857ec426263a4ffd0ff21163ff3b1a2ffa1427d2a0', '[\"*\"]', NULL, NULL, '2026-04-02 11:49:57', '2026-04-02 11:49:57'),
(2032, 'App\\Models\\User', 153, 'auth-token', 'cf48a27f640d52dccf1c8db3be15b5c54358ff9bab4706c276aefc882794b81a', '[\"*\"]', NULL, NULL, '2026-04-02 11:52:57', '2026-04-02 11:52:57'),
(2033, 'App\\Models\\User', 147, 'auth-token', 'dd892773ad9b13ae1ea2d49bbc050cc7c1f371075ca61c34754b09bbe00edc46', '[\"*\"]', NULL, NULL, '2026-04-02 11:53:09', '2026-04-02 11:53:09'),
(2034, 'App\\Models\\User', 157, 'auth-token', 'd4cfed902d0b9c4b041ce6e0bcc6b1a326c6b7c559f752320646ff87aaaed4f7', '[\"*\"]', NULL, NULL, '2026-04-02 12:07:12', '2026-04-02 12:07:12'),
(2035, 'App\\Models\\User', 157, 'auth-token', 'b40d40634964cbcebdda3e5466930f1944b3c56a8bcbb55bbef8bcfc4379b520', '[\"*\"]', NULL, NULL, '2026-04-02 12:08:28', '2026-04-02 12:08:28'),
(2036, 'App\\Models\\User', 31, 'auth-token', 'd37347d8e297105eb9b4194a7198f114e2eabe12d780e40fab0d44be905b120f', '[\"*\"]', NULL, NULL, '2026-04-02 12:09:02', '2026-04-02 12:09:02'),
(2037, 'App\\Models\\User', 154, 'auth-token', '08bc972b41be65dbc71847aaac70269d188f9b6b5ee204af661bdcf4afaecbcd', '[\"*\"]', NULL, NULL, '2026-04-02 12:09:27', '2026-04-02 12:09:27'),
(2038, 'App\\Models\\User', 147, 'auth-token', 'ccf74309dfc7084045119e4a7b7da9008d6f307ecb6d4871e672f5ddf5902121', '[\"*\"]', NULL, NULL, '2026-04-02 12:17:49', '2026-04-02 12:17:49'),
(2039, 'App\\Models\\User', 157, 'auth-token', 'd47778e2a5459ba77077765453b59ffffa0187e85b0f2a71ccf1828c230a1622', '[\"*\"]', NULL, NULL, '2026-04-02 13:22:43', '2026-04-02 13:22:43'),
(2040, 'App\\Models\\User', 31, 'auth-token', 'e53cbcf66eeb6ac8f77c5f49d11b5a2e14aaaf96780f4202a42239a629e86346', '[\"*\"]', NULL, NULL, '2026-04-02 13:24:14', '2026-04-02 13:24:14'),
(2041, 'App\\Models\\User', 154, 'auth-token', '60463b56f050b0259614df1fcf1dd63ae3ba2e6f6563d89ff96a2914ad0839bd', '[\"*\"]', NULL, NULL, '2026-04-02 13:25:03', '2026-04-02 13:25:03'),
(2042, 'App\\Models\\User', 150, 'auth-token', 'bddc036b6fd5926de8582f5562b23d417cac2ebbcdc3d6250f60f63ecebbee85', '[\"*\"]', NULL, NULL, '2026-04-02 13:26:30', '2026-04-02 13:26:30'),
(2043, 'App\\Models\\User', 147, 'auth-token', '1296713e001a1ac6a0a2edeb542dcc3991a64b712b493142530b909eeb2c1007', '[\"*\"]', NULL, NULL, '2026-04-02 13:27:34', '2026-04-02 13:27:34'),
(2044, 'App\\Models\\User', 157, 'auth-token', 'd26aaf36698fdcc42cbfcf989e2ba761a14f70cffed5a1be775c5a8491d690af', '[\"*\"]', NULL, NULL, '2026-04-02 13:34:37', '2026-04-02 13:34:37'),
(2045, 'App\\Models\\User', 31, 'auth-token', '777e8b59d48c70bb68df3b0fcf285f89713af6c4ad3ce61f9a8bfc30dfbbd701', '[\"*\"]', NULL, NULL, '2026-04-02 13:36:33', '2026-04-02 13:36:33'),
(2046, 'App\\Models\\User', 154, 'auth-token', 'd89b7c49af10b3bfab102f2f102363b6bb8f34235b67dced9bcfe4d0e81b1920', '[\"*\"]', NULL, NULL, '2026-04-02 13:38:02', '2026-04-02 13:38:02'),
(2047, 'App\\Models\\User', 157, 'auth-token', '2a82f6a9f895df01b64bffaf0ca2caffb0452144c8eea2086845edc8072a0486', '[\"*\"]', NULL, NULL, '2026-04-02 13:39:35', '2026-04-02 13:39:35'),
(2048, 'App\\Models\\User', 31, 'auth-token', 'eb797ee2c559699421516ad3f8e796c59dceea02ba211ef366fef9896c20a631', '[\"*\"]', NULL, NULL, '2026-04-02 13:40:57', '2026-04-02 13:40:57'),
(2049, 'App\\Models\\User', 154, 'auth-token', 'fa0ad3b47de5dd8dcfd98a98b1123004538a9e06e5a16bb0d0d7bc9c9d7c4d38', '[\"*\"]', NULL, NULL, '2026-04-02 13:44:05', '2026-04-02 13:44:05'),
(2050, 'App\\Models\\User', 150, 'auth-token', 'f674497f77b086d680c5d378471e273e733db57f4f6b4382ae582436478dc98b', '[\"*\"]', NULL, NULL, '2026-04-02 13:44:46', '2026-04-02 13:44:46'),
(2051, 'App\\Models\\User', 152, 'auth-token', 'e6e0c534d5355209239fee6144a1480dd6ace2f5098df7aca0e75544d41f0032', '[\"*\"]', NULL, NULL, '2026-04-02 13:46:21', '2026-04-02 13:46:21'),
(2052, 'App\\Models\\User', 158, 'auth-token', '273d94ece14eeef87d04a3999670df813659b4ba1172152ef4523e36210b093b', '[\"*\"]', NULL, NULL, '2026-04-02 13:48:30', '2026-04-02 13:48:30'),
(2053, 'App\\Models\\User', 150, 'auth-token', '20b4be493e1b3aecfe7b9f349046a2c3c09ec970904dde10d7c5300f09f0d103', '[\"*\"]', NULL, NULL, '2026-04-02 13:50:22', '2026-04-02 13:50:22'),
(2054, 'App\\Models\\User', 149, 'auth-token', 'c4ac7ab1655f1e2ff0e496384e5e815b39e6874ebcc6b27eeeaf236ef7c955e9', '[\"*\"]', NULL, NULL, '2026-04-02 13:51:49', '2026-04-02 13:51:49'),
(2055, 'App\\Models\\User', 154, 'auth-token', 'd92c6b58bd7fdd908f2ddcb2b9cac91d0cc530dd16882f030f0e485fa295d899', '[\"*\"]', NULL, NULL, '2026-04-02 13:52:36', '2026-04-02 13:52:36'),
(2056, 'App\\Models\\User', 150, 'auth-token', '0220e045a8dc0bad3892f4c81caea5733ee4ccd2a1eb2225e017505984b4bd4d', '[\"*\"]', NULL, NULL, '2026-04-02 13:53:37', '2026-04-02 13:53:37'),
(2057, 'App\\Models\\User', 150, 'auth-token', '7d7b42db1bbe8161424095010227ea0294d908532440b3b3027d044b383f561a', '[\"*\"]', NULL, NULL, '2026-04-02 13:54:18', '2026-04-02 13:54:18'),
(2058, 'App\\Models\\User', 152, 'auth-token', '5fdffa65163e636a98c0fb83d1666529a35c6bf363009ad3492c9243f2b72c9d', '[\"*\"]', NULL, NULL, '2026-04-02 13:54:43', '2026-04-02 13:54:43'),
(2059, 'App\\Models\\User', 150, 'auth-token', '2300fd636d5e345c3730cb2b8b5d96f71e6c1739271e1c10cf3ede9ab609f51a', '[\"*\"]', NULL, NULL, '2026-04-02 13:55:13', '2026-04-02 13:55:13'),
(2060, 'App\\Models\\User', 149, 'auth-token', 'b11e2ba6fb7516a9078ca2b2c8eb650bab3d5c27750437091e15768f0bdbb1f9', '[\"*\"]', NULL, NULL, '2026-04-02 13:55:52', '2026-04-02 13:55:52'),
(2061, 'App\\Models\\User', 150, 'auth-token', 'f6bf4dc2a6df6fde1a014618dfd61579522041aefcf65f0b893ac484d4afbc6d', '[\"*\"]', NULL, NULL, '2026-04-02 13:56:26', '2026-04-02 13:56:26'),
(2062, 'App\\Models\\User', 151, 'auth-token', 'eaf3093ee6de3a3062b5791f8d80252bfc1099cf5eb57104335eec12ef52b77d', '[\"*\"]', NULL, NULL, '2026-04-02 13:57:13', '2026-04-02 13:57:13'),
(2063, 'App\\Models\\User', 154, 'auth-token', 'a170441c4afdda11771f8c20804f6966e32f32a4babb7fdfdf3756da476ec300', '[\"*\"]', NULL, NULL, '2026-04-02 13:57:52', '2026-04-02 13:57:52'),
(2064, 'App\\Models\\User', 157, 'auth-token', 'b28a346bb97bd04929edb76927290d774de67d16d81cdff2cdfc575a5b7f9444', '[\"*\"]', NULL, NULL, '2026-04-02 13:58:19', '2026-04-02 13:58:19'),
(2065, 'App\\Models\\User', 147, 'auth-token', '88cf752e4dde392703f716b5bc545c1ec07ffe6abb82e0a0f842fd42cb15324e', '[\"*\"]', NULL, NULL, '2026-04-02 13:58:36', '2026-04-02 13:58:36'),
(2066, 'App\\Models\\User', 153, 'auth-token', '92346ab3f3499e8105119fccc7022964741ef9e28c18a2736d2d266465e75886', '[\"*\"]', NULL, NULL, '2026-04-02 13:59:34', '2026-04-02 13:59:34'),
(2067, 'App\\Models\\User', 153, 'auth-token', 'b22e70e52ad7a7a6e83063e499de53c2f7382de70fe9485f65d831ab0a3794c8', '[\"*\"]', NULL, NULL, '2026-04-02 14:05:33', '2026-04-02 14:05:33'),
(2068, 'App\\Models\\User', 157, 'auth-token', '65240d53967cf47ed3e00a48aef1cd23e608e85e7ae76f0e6708102449a72c5c', '[\"*\"]', NULL, NULL, '2026-04-02 14:11:25', '2026-04-02 14:11:25'),
(2069, 'App\\Models\\User', 31, 'auth-token', '19f2377e9f258717998b66a3ad1e845d05ef2c6cdfb8379978ec690d85ba79f6', '[\"*\"]', NULL, NULL, '2026-04-02 14:12:42', '2026-04-02 14:12:42'),
(2070, 'App\\Models\\User', 154, 'auth-token', 'b52f25c76008da0166eb34bd3a94b90316353be138a47a446eef004090ff3934', '[\"*\"]', NULL, NULL, '2026-04-02 14:13:17', '2026-04-02 14:13:17'),
(2071, 'App\\Models\\User', 157, 'auth-token', '112d4fbfa0fbb8e59b8f71cf53602b1a5139843296202f7bdb95007940822376', '[\"*\"]', NULL, NULL, '2026-04-02 14:14:42', '2026-04-02 14:14:42'),
(2072, 'App\\Models\\User', 31, 'auth-token', '8f87e6a5dc5408934840c19ee82e9543af0d16e9de9bdda92445c56f03370961', '[\"*\"]', NULL, NULL, '2026-04-02 14:15:42', '2026-04-02 14:15:42'),
(2073, 'App\\Models\\User', 154, 'auth-token', '05f6eab53b3165920459743669c72d3147a85067fcb00f839b140e455a8fb39a', '[\"*\"]', NULL, NULL, '2026-04-02 14:16:27', '2026-04-02 14:16:27'),
(2074, 'App\\Models\\User', 150, 'auth-token', '8931f289bbd0a0c0b6301ff771f9f5eed2de69d269e885fd37b936863de803e3', '[\"*\"]', NULL, NULL, '2026-04-02 14:17:05', '2026-04-02 14:17:05'),
(2075, 'App\\Models\\User', 152, 'auth-token', 'b492fa5b5cb88007d55f61473db39fbeadfaffe6da30e69ee8b1c00bd7c9da13', '[\"*\"]', NULL, NULL, '2026-04-02 14:17:42', '2026-04-02 14:17:42'),
(2076, 'App\\Models\\User', 150, 'auth-token', '117fa5089ee46c625cfa2efa0f654f40ee5ebc9e0cd40737c08dcfe9bc34a63d', '[\"*\"]', NULL, NULL, '2026-04-02 14:18:47', '2026-04-02 14:18:47'),
(2077, 'App\\Models\\User', 149, 'auth-token', '98f461ee27c74f16455f748d23f28bbe7a92e7e6711b5404ce49fbe9799d9982', '[\"*\"]', NULL, NULL, '2026-04-02 14:19:32', '2026-04-02 14:19:32'),
(2078, 'App\\Models\\User', 149, 'auth-token', '0c6b22448ab028f7d9128193e1801bf0696abe1f622d7c8d059e397e6db7d139', '[\"*\"]', NULL, NULL, '2026-04-02 14:26:56', '2026-04-02 14:26:56'),
(2079, 'App\\Models\\User', 150, 'auth-token', 'd15f9d50ade0e4cff1a88ca4ac57643ad1313e1e25a982299fd050a76f3c36b2', '[\"*\"]', NULL, NULL, '2026-04-02 14:27:29', '2026-04-02 14:27:29'),
(2080, 'App\\Models\\User', 158, 'auth-token', '7b025c24a14c0dfc4533879c8b4422816054d7a07373667b8226cc440f3826a6', '[\"*\"]', NULL, NULL, '2026-04-02 14:27:57', '2026-04-02 14:27:57'),
(2081, 'App\\Models\\User', 152, 'auth-token', '6ac07b6cf7077af926c228277069c8590ec32d1f7e67f419d106a54da031acc1', '[\"*\"]', NULL, NULL, '2026-04-02 14:28:17', '2026-04-02 14:28:17'),
(2082, 'App\\Models\\User', 150, 'auth-token', '24807817b8735207759ab1500def7bba457d296321881d8289112683db8b9617', '[\"*\"]', NULL, NULL, '2026-04-02 14:28:43', '2026-04-02 14:28:43'),
(2083, 'App\\Models\\User', 149, 'auth-token', '26d214b96c082c4dc275a1d50f6d6dcc6db44a0d38b1776bf4cb2ecc230068e0', '[\"*\"]', NULL, NULL, '2026-04-02 14:30:28', '2026-04-02 14:30:28'),
(2084, 'App\\Models\\User', 150, 'auth-token', '50e76773f9c87392619f8d17ed60c8e8ef02a2eb9d5f951facee6a14c8a956c9', '[\"*\"]', NULL, NULL, '2026-04-02 14:33:15', '2026-04-02 14:33:15'),
(2085, 'App\\Models\\User', 151, 'auth-token', '03916aa51edf3cd5ac6ad93b8a8b1326190ede466d9c7d8a9985ddf0c8b35f67', '[\"*\"]', NULL, NULL, '2026-04-02 14:34:11', '2026-04-02 14:34:11'),
(2086, 'App\\Models\\User', 153, 'auth-token', '061a0ba4b2f2da8f90e6a131481804fc3bfb1bfc72f1a601d2daa5cbfe3b3124', '[\"*\"]', NULL, NULL, '2026-04-02 14:34:45', '2026-04-02 14:34:45'),
(2087, 'App\\Models\\User', 157, 'auth-token', '66f42b2e03f9acc1a775538f41969c6f24eec51c578e036fc7f0845112838049', '[\"*\"]', NULL, NULL, '2026-04-02 14:47:38', '2026-04-02 14:47:38'),
(2088, 'App\\Models\\User', 31, 'auth-token', 'e3643418ae9e5ae4da36921f36b483eca41b80d734430af881f98a281f258bc4', '[\"*\"]', NULL, NULL, '2026-04-02 14:49:01', '2026-04-02 14:49:01'),
(2089, 'App\\Models\\User', 154, 'auth-token', 'de80ad450061acf0c4e4fd051ad88f8df1b8897f6e2ac33ee4a2d32ab955c25d', '[\"*\"]', NULL, NULL, '2026-04-02 14:49:42', '2026-04-02 14:49:42'),
(2090, 'App\\Models\\User', 150, 'auth-token', '66ea4eaa5f0d5c2ca927e1bdfc9503cb085a120eac17d4874b1f57cec1eb56ff', '[\"*\"]', NULL, NULL, '2026-04-02 14:50:24', '2026-04-02 14:50:24'),
(2091, 'App\\Models\\User', 152, 'auth-token', 'a8d805bb41b68b4e22bee8e53b881f58c899cd244247976bf9ddd389a885ee93', '[\"*\"]', NULL, NULL, '2026-04-02 14:51:25', '2026-04-02 14:51:25'),
(2092, 'App\\Models\\User', 150, 'auth-token', '7190bcb5ccb1a6f8190a98a3cf170bc260598347adc4b437e60aea071f59fe64', '[\"*\"]', NULL, NULL, '2026-04-02 14:52:16', '2026-04-02 14:52:16'),
(2093, 'App\\Models\\User', 152, 'auth-token', '8ac8a9ca67f9f37647bb622aeb0ccf64edb55f30bdb279c0878f9caf86004f30', '[\"*\"]', NULL, NULL, '2026-04-02 14:52:50', '2026-04-02 14:52:50'),
(2094, 'App\\Models\\User', 149, 'auth-token', '2dc78a5c79d12b74775ff7615053946f05218926a7babf105c55c2bcf920e089', '[\"*\"]', NULL, NULL, '2026-04-02 14:53:11', '2026-04-02 14:53:11'),
(2095, 'App\\Models\\User', 150, 'auth-token', 'a200d9c855a9a8435e276fa8dc6b31677e1be97a45563806c01487e09b140c26', '[\"*\"]', NULL, NULL, '2026-04-02 14:53:55', '2026-04-02 14:53:55'),
(2096, 'App\\Models\\User', 152, 'auth-token', '0b62b52c0ad272eef35558b580def2236dc26e618f56ae467abcc2952e9bc99d', '[\"*\"]', NULL, NULL, '2026-04-02 14:54:29', '2026-04-02 14:54:29'),
(2097, 'App\\Models\\User', 150, 'auth-token', '086c44a117f5553505a988eb00b5eb058ed61c13bcefa6564d7f3510ca5c684b', '[\"*\"]', NULL, NULL, '2026-04-02 14:55:02', '2026-04-02 14:55:02'),
(2098, 'App\\Models\\User', 149, 'auth-token', '92bac4a3bb0806365dec95e789710099df3e90ecaf3b71804eff4a4787a30860', '[\"*\"]', NULL, NULL, '2026-04-02 14:55:48', '2026-04-02 14:55:48'),
(2099, 'App\\Models\\User', 150, 'auth-token', '82dfecc644dbaa33c40c1f8d6a0fb9719e597fc104688edb7177175311871f88', '[\"*\"]', NULL, NULL, '2026-04-02 14:56:21', '2026-04-02 14:56:21'),
(2100, 'App\\Models\\User', 154, 'auth-token', '6d52d28b7915808c281ac266aff6ac130639f17b513adc92c79f8fe40e58497d', '[\"*\"]', NULL, NULL, '2026-04-02 14:57:38', '2026-04-02 14:57:38'),
(2101, 'App\\Models\\User', 151, 'auth-token', '5b56ef6b4004a38f2d82f2f8dd9608690ebe96c6e0652e2434834666c3bbfd6e', '[\"*\"]', NULL, NULL, '2026-04-02 14:57:57', '2026-04-02 14:57:57'),
(2102, 'App\\Models\\User', 153, 'auth-token', '59053bf0a873cf48037fce4ec9fe9cb17adc4d101d642d11b1e12f6e30e739d7', '[\"*\"]', NULL, NULL, '2026-04-02 14:58:23', '2026-04-02 14:58:23'),
(2103, 'App\\Models\\User', 151, 'auth-token', 'd8a60c171c4bae96465dc49478cae7867ebb5502ce3a1d2421669d71b2a9a523', '[\"*\"]', NULL, NULL, '2026-04-03 08:09:34', '2026-04-03 08:09:34'),
(2104, 'App\\Models\\User', 150, 'auth-token', 'c31ca20cc6d61352dbd65f8281bc22a663e8d9f5f7e6fc8c3df88acc57190dde', '[\"*\"]', NULL, NULL, '2026-04-03 08:10:11', '2026-04-03 08:10:11'),
(2105, 'App\\Models\\User', 154, 'auth-token', '8852b634fcbeb477ee164d9232c3310662fbf0a1fde06e612cccc97c78b205d2', '[\"*\"]', NULL, NULL, '2026-04-03 08:35:26', '2026-04-03 08:35:26'),
(2106, 'App\\Models\\User', 151, 'auth-token', 'bc547788ef23005080d412a8c70f49442db0a4396ca0f9d9fd91cb9e582e8cf4', '[\"*\"]', NULL, NULL, '2026-04-03 08:37:27', '2026-04-03 08:37:27'),
(2107, 'App\\Models\\User', 162, 'auth-token', '539a02f6459b00950bbb4aaa5f03e0213ebf244a610e42ebc6c03e7addf45bfa', '[\"*\"]', NULL, NULL, '2026-04-03 08:38:10', '2026-04-03 08:38:10'),
(2108, 'App\\Models\\User', 151, 'auth-token', '4eb2b3568469e95dbcbaeaf1026e95c90555768d2440f4801208fc084401d6f9', '[\"*\"]', NULL, NULL, '2026-04-03 08:46:20', '2026-04-03 08:46:20'),
(2109, 'App\\Models\\User', 28, 'auth-token', '58317aa1de3954656fdc44258ebac6a547c311bc179131298e03c2116ea96885', '[\"*\"]', NULL, NULL, '2026-04-03 08:46:55', '2026-04-03 08:46:55'),
(2110, 'App\\Models\\User', 154, 'auth-token', 'fb9f7dbea436368b86d4c06feafb58ad553dc702354fd086b5045ec9e1ffbeff', '[\"*\"]', NULL, NULL, '2026-04-03 08:48:01', '2026-04-03 08:48:01'),
(2111, 'App\\Models\\User', 31, 'auth-token', '277ac613ee0286ffbefcde3fb241644dc6b5c362073e65c7dbe6def1c7b66212', '[\"*\"]', NULL, NULL, '2026-04-03 08:55:56', '2026-04-03 08:55:56'),
(2112, 'App\\Models\\User', 162, 'auth-token', 'a716bf63dc512edd3fd09d821976d4687eb3e9d95b09182278c68d6a063e11e1', '[\"*\"]', NULL, NULL, '2026-04-03 08:59:00', '2026-04-03 08:59:00'),
(2113, 'App\\Models\\User', 154, 'auth-token', '4f7a445c6f083560d65386bd511bf5c29d4967450e91149f0c079f06390c2918', '[\"*\"]', NULL, NULL, '2026-04-03 09:09:39', '2026-04-03 09:09:39'),
(2114, 'App\\Models\\User', 162, 'auth-token', 'c9805b7fa7ed708be54e9626c6e8ed7331e6bb900a99988a400c0a3cf2d134eb', '[\"*\"]', NULL, NULL, '2026-04-03 09:14:35', '2026-04-03 09:14:35'),
(2115, 'App\\Models\\User', 150, 'auth-token', '096c74ca0d6045d49f0a082bab4d913bc47b6a4e4b9a1ba9512fe463f3113b9d', '[\"*\"]', NULL, NULL, '2026-04-03 09:28:15', '2026-04-03 09:28:15'),
(2116, 'App\\Models\\User', 162, 'auth-token', '4d4639a30cc8a2a684c5400f05ecf356859109b6adc8636b0d850709f828ca3f', '[\"*\"]', NULL, NULL, '2026-04-03 09:33:50', '2026-04-03 09:33:50'),
(2117, 'App\\Models\\User', 154, 'auth-token', '19325337a978e098421c6305885e544166f0bae092c8e9f7caed300495bb4db6', '[\"*\"]', NULL, NULL, '2026-04-03 12:52:44', '2026-04-03 12:52:44'),
(2118, 'App\\Models\\User', 149, 'auth-token', '9a99ccd0ea6ca78d6da4f7aea00779c13dcc4d4266515699db4ba964e251df31', '[\"*\"]', NULL, NULL, '2026-04-03 12:59:34', '2026-04-03 12:59:34'),
(2119, 'App\\Models\\User', 28, 'auth-token', '7bae80bc9a58213b9e35b341637521a38e244d21fe13f1bde3fff2bd727c9ea6', '[\"*\"]', NULL, NULL, '2026-04-03 13:00:48', '2026-04-03 13:00:48'),
(2120, 'App\\Models\\User', 149, 'auth-token', 'a3b463917a2e8972d4922ac19d1cb1c15277311ff6c4785f1b652664aae03a4d', '[\"*\"]', NULL, NULL, '2026-04-03 13:01:07', '2026-04-03 13:01:07'),
(2121, 'App\\Models\\User', 153, 'auth-token', '26453beeec686c5a03aae7a2438a14162651425b9504d7ed152f1d1de0f7b36d', '[\"*\"]', NULL, NULL, '2026-04-03 13:38:27', '2026-04-03 13:38:27'),
(2122, 'App\\Models\\User', 151, 'auth-token', 'a95ecac22e6b926012075bac858e4c7909f69f8d4cab8fd67093962c9da22864', '[\"*\"]', NULL, NULL, '2026-04-03 14:10:16', '2026-04-03 14:10:16'),
(2123, 'App\\Models\\User', 154, 'auth-token', '1a9b21134106efaa26118aff111ad20153167c35d1b9b5b61b95fcdb7ec956d7', '[\"*\"]', NULL, NULL, '2026-04-03 14:10:55', '2026-04-03 14:10:55'),
(2124, 'App\\Models\\User', 147, 'auth-token', '395f28b8f8074a82b8e884fa9588a4defc5570ed6049a57351adac368ea41459', '[\"*\"]', NULL, NULL, '2026-04-03 14:50:54', '2026-04-03 14:50:54'),
(2125, 'App\\Models\\User', 28, 'auth-token', '8a8e83d1e00a00ffc79bc3dfc20b44063ee50e0bb5bd6fe83ec21dd28e3fdfb8', '[\"*\"]', NULL, NULL, '2026-04-03 14:51:38', '2026-04-03 14:51:38'),
(2126, 'App\\Models\\User', 182, 'auth-token', '4a7fe8cbfd39caa9b67fef0bbd6bb1daed51a129a682738ed8541a026f6ec0b5', '[\"*\"]', NULL, NULL, '2026-04-03 14:55:10', '2026-04-03 14:55:10'),
(2127, 'App\\Models\\User', 182, 'auth-token', 'cc6d56d3faad5c75a9dfac5a8a20023b23e16fff4318483427aa0eb3dfa0e49b', '[\"*\"]', NULL, NULL, '2026-04-03 15:02:17', '2026-04-03 15:02:17'),
(2128, 'App\\Models\\User', 182, 'auth-token', '13c191305644a322c3456120c5af05b300364ef4d7368268c443f99aab821f87', '[\"*\"]', NULL, NULL, '2026-04-03 15:02:57', '2026-04-03 15:02:57'),
(2129, 'App\\Models\\User', 147, 'auth-token', 'a23012575adc5de4a9116d988af542e01521dc286d91727a12b8216ae3a2ee22', '[\"*\"]', NULL, NULL, '2026-04-03 15:09:38', '2026-04-03 15:09:38'),
(2130, 'App\\Models\\User', 148, 'auth-token', '89e81c22e7a5b243f1bdfea2f3e900d68b432cf6770eefff40f422cac8495784', '[\"*\"]', '2026-04-03 15:18:31', NULL, '2026-04-03 15:17:11', '2026-04-03 15:18:31'),
(2131, 'App\\Models\\User', 182, 'auth-token', 'afad6d27cadf8392aacf093bc9fb68844bcf4c7a2ccc8765960e186006ec724f', '[\"*\"]', NULL, NULL, '2026-04-03 15:18:45', '2026-04-03 15:18:45'),
(2132, 'App\\Models\\User', 31, 'auth-token', '4c6602c287164938613e6bcb1faa80e1171ff900c5b70fa76f7243098d4f0556', '[\"*\"]', NULL, NULL, '2026-04-03 15:27:57', '2026-04-03 15:27:57'),
(2133, 'App\\Models\\User', 148, 'auth-token', '9b24a84234bd39365a6053d69d0bff563a61919fe0d7326da2a542c3bcf978fd', '[\"*\"]', NULL, NULL, '2026-04-03 15:44:49', '2026-04-03 15:44:49'),
(2134, 'App\\Models\\User', 159, 'auth-token', '6f719cbe50a1fdf253628542460cc3cf2a2cb76a42f89318dbf2b2c55c5e5d0c', '[\"*\"]', NULL, NULL, '2026-04-03 15:57:02', '2026-04-03 15:57:02'),
(2135, 'App\\Models\\User', 28, 'auth-token', '60818938e9305198de533fe35bc388fdf0d0f6045adf438c3f4013af3be6d0d1', '[\"*\"]', NULL, NULL, '2026-04-03 16:08:15', '2026-04-03 16:08:15'),
(2136, 'App\\Models\\User', 147, 'auth-token', 'ff284a9a60389df57706342b08ee444738fdbe7f5febaecf1cd07cb9f34d3fc3', '[\"*\"]', NULL, NULL, '2026-04-03 16:12:57', '2026-04-03 16:12:57'),
(2137, 'App\\Models\\User', 148, 'auth-token', 'e4b992bc8ee2359f7b1441479900ba3b4158b3685278c2b64ae1c0c1072cdeb3', '[\"*\"]', NULL, NULL, '2026-04-03 16:37:07', '2026-04-03 16:37:07'),
(2138, 'App\\Models\\User', 28, 'auth-token', '186277d54bff5f766cc8551ec38b9c5d732e20c011a1e6439ff9919993558036', '[\"*\"]', NULL, NULL, '2026-04-03 16:55:05', '2026-04-03 16:55:05'),
(2139, 'App\\Models\\User', 153, 'auth-token', 'fbedae61d0b00bdc0f706057028b0d0a7389c8e36933045ffea7fc9ebf300d92', '[\"*\"]', NULL, NULL, '2026-04-03 17:11:44', '2026-04-03 17:11:44'),
(2140, 'App\\Models\\User', 148, 'auth-token', 'e8e62918977f2b86fdc3dd67fee3ca50f8fdc7cf12a5f9f5537949c1cfc62fb5', '[\"*\"]', NULL, NULL, '2026-04-03 17:13:06', '2026-04-03 17:13:06'),
(2141, 'App\\Models\\User', 159, 'auth-token', 'e3de021fdb6d7cbd9058cdd43be57afecfc8a490afedd32b6ca8244c72c34b60', '[\"*\"]', NULL, NULL, '2026-04-03 17:23:32', '2026-04-03 17:23:32'),
(2142, 'App\\Models\\User', 28, 'auth-token', '5a873f5399fbec2a638587ca2de4a22bef3056f9c3b5bdc1a1275050cdfa7d17', '[\"*\"]', NULL, NULL, '2026-04-03 17:25:22', '2026-04-03 17:25:22'),
(2143, 'App\\Models\\User', 148, 'auth-token', '1b8485acbfab0e1a4fc5fa526b1fcd196b04335dca1be592706c187065e37c6e', '[\"*\"]', NULL, NULL, '2026-04-03 17:35:18', '2026-04-03 17:35:18'),
(2144, 'App\\Models\\User', 159, 'auth-token', '85923952807eca771cccb29fccefab63c741c56d9ccdf546a645c452c986b265', '[\"*\"]', NULL, NULL, '2026-04-03 17:59:06', '2026-04-03 17:59:06'),
(2145, 'App\\Models\\User', 159, 'auth-token', 'f54419970dde9bb01fd7084b46c3a53e166631cf74a16dfb8b031595e2fcc885', '[\"*\"]', NULL, NULL, '2026-04-03 18:08:17', '2026-04-03 18:08:17'),
(2146, 'App\\Models\\User', 28, 'auth-token', '5363a04d1db310e9a69f745dc2085502e35aa594ef52d03a4d6731ce9f3627fc', '[\"*\"]', NULL, NULL, '2026-04-03 18:11:51', '2026-04-03 18:11:51'),
(2147, 'App\\Models\\User', 31, 'auth-token', '09c9f3734c832e116aee623dc32d1ee8f5f201d8c9b7a50c2a1017c87b1b1c5c', '[\"*\"]', NULL, NULL, '2026-04-03 18:15:17', '2026-04-03 18:15:17'),
(2148, 'App\\Models\\User', 31, 'auth-token', 'cf17ceaac8864062c5f30984a4ae456aa31846dadedd7a00e001c19b2b15c59f', '[\"*\"]', NULL, NULL, '2026-04-03 18:21:12', '2026-04-03 18:21:12'),
(2149, 'App\\Models\\User', 147, 'auth-token', '9175527f7dcacab394827c8a8af856ad77e4633c87e68aee22ccc483135728f0', '[\"*\"]', NULL, NULL, '2026-04-03 18:22:32', '2026-04-03 18:22:32'),
(2150, 'App\\Models\\User', 31, 'auth-token', 'd86a3c95e0e0a274b2ecaf8b12a2895dc80ada8fafc6628c97ddd1b6522be3c9', '[\"*\"]', NULL, NULL, '2026-04-03 18:23:11', '2026-04-03 18:23:11'),
(2151, 'App\\Models\\User', 28, 'auth-token', '44caf5f2727c65500e1ed6d3087c81188429dc4e6639d8e4061b5697bb32a91e', '[\"*\"]', NULL, NULL, '2026-04-03 18:23:35', '2026-04-03 18:23:35'),
(2152, 'App\\Models\\User', 31, 'auth-token', '50e1af7c6ca90b30353de34e9fafa969ef460b61fba6df78b9e91a31b02c7492', '[\"*\"]', NULL, NULL, '2026-04-03 18:27:19', '2026-04-03 18:27:19'),
(2153, 'App\\Models\\User', 31, 'auth-token', '8c4ac3e0ceff67f0e0711b29ba8ec63e741541fb24b317f01b450fe664a94183', '[\"*\"]', NULL, NULL, '2026-04-03 18:30:05', '2026-04-03 18:30:05'),
(2154, 'App\\Models\\User', 148, 'auth-token', 'c656ea7f64111b41d6fc6f2f593ffc1a7a509b098cb78e178a9955372a7476b2', '[\"*\"]', '2026-04-03 18:44:36', NULL, '2026-04-03 18:33:52', '2026-04-03 18:44:36'),
(2155, 'App\\Models\\User', 148, 'auth-token', '5f46fccaf7c09a7eb3fd90b0264bba7a587e2cfbd7005799c49fa9eb1df0b7bb', '[\"*\"]', NULL, NULL, '2026-04-03 18:44:51', '2026-04-03 18:44:51'),
(2156, 'App\\Models\\User', 149, 'auth-token', '9d6a470482c6f333ee5e21fd6efca1d2543a2c1f1277493454c6e53fea1b9412', '[\"*\"]', NULL, NULL, '2026-04-04 07:13:49', '2026-04-04 07:13:49'),
(2157, 'App\\Models\\User', 153, 'auth-token', '65acb0c4fd7a90197711302f2d2b30a6822a4aa423748ffb3371439781c76ed3', '[\"*\"]', NULL, NULL, '2026-04-04 07:15:07', '2026-04-04 07:15:07'),
(2158, 'App\\Models\\User', 149, 'auth-token', '8b77c171b41a232af89cd07217cccba3ffd6833ff761bc879045407ebc5666b6', '[\"*\"]', NULL, NULL, '2026-04-04 07:15:54', '2026-04-04 07:15:54'),
(2159, 'App\\Models\\User', 153, 'auth-token', '98961ccf2813268cd354f423817f60ae94ef7a295c6865b978627bc8cafb4ffe', '[\"*\"]', NULL, NULL, '2026-04-04 07:16:28', '2026-04-04 07:16:28'),
(2160, 'App\\Models\\User', 149, 'auth-token', '4cd26bb0a7dad8392cfd42aec9cb97b982a5615ff07e55f8fbdd58df69b29e15', '[\"*\"]', NULL, NULL, '2026-04-04 07:17:35', '2026-04-04 07:17:35'),
(2161, 'App\\Models\\User', 153, 'auth-token', '6083c618b3810f54bd69bec22592355faf11d5dce1b9101e4844c26e54e51e2d', '[\"*\"]', NULL, NULL, '2026-04-04 07:20:35', '2026-04-04 07:20:35'),
(2162, 'App\\Models\\User', 149, 'auth-token', 'f41b22f2f7ee8c1753a7a817ed0075d8b1f098d55cfe5a9218da40af6184649a', '[\"*\"]', NULL, NULL, '2026-04-04 07:21:36', '2026-04-04 07:21:36'),
(2163, 'App\\Models\\User', 154, 'auth-token', 'a113d5a408e552e015cd06d5e68e66579f82d346ac5e66e34ef6ef0e2b2d650d', '[\"*\"]', NULL, NULL, '2026-04-04 07:22:54', '2026-04-04 07:22:54'),
(2164, 'App\\Models\\User', 31, 'auth-token', '1a843b4dda6986b26f102f7e52d235a44516347a3246e9cdf5b17f973a3284de', '[\"*\"]', NULL, NULL, '2026-04-04 07:23:22', '2026-04-04 07:23:22'),
(2165, 'App\\Models\\User', 147, 'auth-token', 'c0973bd4ca1883272bb7a6cb35ffcaa60acdcab150de147ee2773a71d23d73d8', '[\"*\"]', NULL, NULL, '2026-04-04 07:23:57', '2026-04-04 07:23:57'),
(2166, 'App\\Models\\User', 31, 'auth-token', '758c8d0a9fc1bcbca5dbee76513e5faf5e1cda69945c6559965b968efa78dcd2', '[\"*\"]', NULL, NULL, '2026-04-04 07:24:16', '2026-04-04 07:24:16'),
(2167, 'App\\Models\\User', 31, 'auth-token', '01d8ecda3a25032b35aac264912ecdf06937b6f80206354aace3b008a8dda32a', '[\"*\"]', NULL, NULL, '2026-04-04 07:27:24', '2026-04-04 07:27:24'),
(2168, 'App\\Models\\User', 162, 'auth-token', '8eb8e75280bf85daeb53ef4baa837b1158c3eb54cf9cbc2ba56a01c10a878df0', '[\"*\"]', NULL, NULL, '2026-04-04 07:29:42', '2026-04-04 07:29:42'),
(2169, 'App\\Models\\User', 162, 'auth-token', '93310dfe3164c9e1866deeb6797116ef7a09d3349e94513f1989c2280cdc9904', '[\"*\"]', NULL, NULL, '2026-04-04 07:33:57', '2026-04-04 07:33:57'),
(2170, 'App\\Models\\User', 162, 'auth-token', 'd20ed5d22d3717c16a49eb173c1b9505731e0a25aebeec01b0cf8248cd6417fe', '[\"*\"]', NULL, NULL, '2026-04-04 07:36:20', '2026-04-04 07:36:20'),
(2171, 'App\\Models\\User', 31, 'auth-token', '05787a8024ad415f3e20e8b9206af53f0cf5d48edf801024cc04949a8a7c4502', '[\"*\"]', NULL, NULL, '2026-04-04 07:37:42', '2026-04-04 07:37:42'),
(2172, 'App\\Models\\User', 31, 'auth-token', '73b32025d9b0e807a984029ba93995a0fc0f176bd924466df953ebb523961f16', '[\"*\"]', NULL, NULL, '2026-04-04 07:41:25', '2026-04-04 07:41:25'),
(2173, 'App\\Models\\User', 154, 'auth-token', 'f443afc96b2c1d19e4e45aa73ecf640ba67f5999c9b02a798e9a96d03f59dfb1', '[\"*\"]', NULL, NULL, '2026-04-04 07:42:18', '2026-04-04 07:42:18'),
(2174, 'App\\Models\\User', 147, 'auth-token', '4e0f9a66a67baec5d0e2ca4a89ff5dc2849dba8c12b2280e460349e7a87b7c60', '[\"*\"]', NULL, NULL, '2026-04-04 07:42:43', '2026-04-04 07:42:43'),
(2175, 'App\\Models\\User', 154, 'auth-token', '15c71c63cb92be0fcb734332ff6732189e083bb8703c8509302107797976116c', '[\"*\"]', NULL, NULL, '2026-04-04 07:43:51', '2026-04-04 07:43:51'),
(2176, 'App\\Models\\User', 150, 'auth-token', '6528ef96934178c4cfbc8d59795c1cfcaee28a87f49d5e7f48e874fbc4f595d2', '[\"*\"]', NULL, NULL, '2026-04-04 07:44:20', '2026-04-04 07:44:20'),
(2177, 'App\\Models\\User', 152, 'auth-token', 'a35b4f8a03fae76d54de61fc752940976eb919ea5159a521910ae5599c1a830b', '[\"*\"]', NULL, NULL, '2026-04-04 07:44:55', '2026-04-04 07:44:55'),
(2178, 'App\\Models\\User', 150, 'auth-token', '8120f727f601783bd183445f400fb41d2d1585d23f3306f68495528070bffaba', '[\"*\"]', NULL, NULL, '2026-04-04 07:46:20', '2026-04-04 07:46:20'),
(2179, 'App\\Models\\User', 149, 'auth-token', '26e83848f1a9dea78307a87c93bd81ae7362b8ccf58febdf7cda2eeaeaad1302', '[\"*\"]', NULL, NULL, '2026-04-04 07:46:42', '2026-04-04 07:46:42'),
(2180, 'App\\Models\\User', 150, 'auth-token', '269d98abfc1e85c8621c33ed3a9b10d75ee63c8f0abfb0d049b210d1f6f5c22e', '[\"*\"]', NULL, NULL, '2026-04-04 07:47:24', '2026-04-04 07:47:24'),
(2181, 'App\\Models\\User', 152, 'auth-token', '065c45b524cfe71f7fcdc6f4d8ea1b1df99d7f21419bb23915b805126d4af974', '[\"*\"]', NULL, NULL, '2026-04-04 07:48:03', '2026-04-04 07:48:03'),
(2182, 'App\\Models\\User', 150, 'auth-token', 'fd71720befedf7b09e8d80c8506a4cfd0718bf29d0b89b8f76da28a633b4fbd2', '[\"*\"]', NULL, NULL, '2026-04-04 07:48:23', '2026-04-04 07:48:23'),
(2183, 'App\\Models\\User', 149, 'auth-token', '41f2a060bdc3741a19f14c7fa5e6ed2a2f9824fa4f5d07b2de0d8f114d7d8dc6', '[\"*\"]', NULL, NULL, '2026-04-04 07:49:16', '2026-04-04 07:49:16'),
(2184, 'App\\Models\\User', 150, 'auth-token', 'df61146ee14aae99fe41fd6e69627c833cac3770a065b1a801c9eccf17ed1904', '[\"*\"]', NULL, NULL, '2026-04-04 07:50:28', '2026-04-04 07:50:28'),
(2185, 'App\\Models\\User', 151, 'auth-token', '3d525dd70aa39ca689315c4c7b011cf734b2e85e4361a3119b73afd5ad284808', '[\"*\"]', NULL, NULL, '2026-04-04 07:50:58', '2026-04-04 07:50:58');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2186, 'App\\Models\\User', 153, 'auth-token', '73d276c3772ba00a0d44f288db5e2de82ebf7e4a833bbee5ff8cacb1b580abb9', '[\"*\"]', NULL, NULL, '2026-04-04 07:51:22', '2026-04-04 07:51:22'),
(2187, 'App\\Models\\User', 149, 'auth-token', '5d69a754c41f174dccce4e85b69e33985d14752dbdb78c09c18a1e06d19dc94e', '[\"*\"]', NULL, NULL, '2026-04-04 07:52:10', '2026-04-04 07:52:10'),
(2188, 'App\\Models\\User', 148, 'auth-token', '0c5aac029ff868894ed23bf3667c7c7424dccb77cc752cc2b7dd1436036bdd53', '[\"*\"]', NULL, NULL, '2026-04-04 07:56:43', '2026-04-04 07:56:43'),
(2189, 'App\\Models\\User', 148, 'auth-token', 'cf55bc5ad378ac6998a246d5b87517ce44c883ff6b4cba08dc4acde47e9ed9e5', '[\"*\"]', NULL, NULL, '2026-04-04 09:12:21', '2026-04-04 09:12:21'),
(2190, 'App\\Models\\User', 184, 'auth-token', '42dda13b7076163b4adc9d502a04a5d46633aa3f2fd3d4361a737eb6dedc82d5', '[\"*\"]', NULL, NULL, '2026-04-04 09:20:55', '2026-04-04 09:20:55'),
(2191, 'App\\Models\\User', 148, 'auth-token', 'a18bbf23e175825187424195c170cce6068c625b21434a1faa1e20293871226c', '[\"*\"]', NULL, NULL, '2026-04-04 09:37:35', '2026-04-04 09:37:35'),
(2192, 'App\\Models\\User', 185, 'auth-token', 'b7f35ea7158c5edde81a0ad3068f6bcfbc7944bd60b6a858f30593d7b57a46fd', '[\"*\"]', NULL, NULL, '2026-04-04 10:21:42', '2026-04-04 10:21:42'),
(2193, 'App\\Models\\User', 148, 'auth-token', '7facd34486d32ab64a22e8c2a55d624be3202755ef34fa888eb96d4480d0d2c5', '[\"*\"]', NULL, NULL, '2026-04-04 10:29:16', '2026-04-04 10:29:16'),
(2194, 'App\\Models\\User', 187, 'auth-token', 'e15baa675caf5fb0db6ffa968e8d91c48141febd71cc98fc09aea2e119a80d96', '[\"*\"]', NULL, NULL, '2026-04-04 10:51:23', '2026-04-04 10:51:23'),
(2195, 'App\\Models\\User', 148, 'auth-token', '95b20251682ca406c955df5e5f85dc2746cd560aeccc4fc233f534058d923db6', '[\"*\"]', NULL, NULL, '2026-04-04 10:55:46', '2026-04-04 10:55:46'),
(2196, 'App\\Models\\User', 28, 'auth-token', '4102ad4eb56b5c1b2733a757e4d17200d9cbfa5014e5a9686b3f60fe641536a1', '[\"*\"]', NULL, NULL, '2026-04-04 10:59:14', '2026-04-04 10:59:14'),
(2197, 'App\\Models\\User', 187, 'auth-token', 'df2a3429f2d1c4b6cd71ae975b4062c44b9235bb1ab5ee7e107fdb593c9b92e9', '[\"*\"]', NULL, NULL, '2026-04-04 11:01:06', '2026-04-04 11:01:06'),
(2198, 'App\\Models\\User', 157, 'auth-token', '52b8b6841924fe139c97072296fe6a1f1d20ce3e1698c5984dc2f4660b6eb63d', '[\"*\"]', NULL, NULL, '2026-04-04 11:31:02', '2026-04-04 11:31:02'),
(2199, 'App\\Models\\User', 187, 'auth-token', 'e99386c9b1a14ba1696e452ba263e30938b887cf030f9672b3a458a6e5f8cc35', '[\"*\"]', NULL, NULL, '2026-04-04 12:17:56', '2026-04-04 12:17:56'),
(2200, 'App\\Models\\User', 147, 'auth-token', '501d92ae5644ecb17816982d1cb5b7a285844ad9cf32d066cd82d3bfd449cc89', '[\"*\"]', NULL, NULL, '2026-04-04 12:19:13', '2026-04-04 12:19:13'),
(2201, 'App\\Models\\User', 28, 'auth-token', 'ec3c237170a3caa47c9ecf8a49fb4f2f86eb9d39baa713c2cf6b4f7668dfdfb6', '[\"*\"]', NULL, NULL, '2026-04-04 12:24:59', '2026-04-04 12:24:59'),
(2202, 'App\\Models\\User', 148, 'auth-token', '72d05c30cbb4030764653ac694fbb57950d7d6fe06aa3982d947fee0d816dd42', '[\"*\"]', NULL, NULL, '2026-04-04 12:25:57', '2026-04-04 12:25:57'),
(2203, 'App\\Models\\User', 147, 'auth-token', '126df05472c1c26cc069198aed5dd41f7472ad4f64ce9c262bfb5c47fad143d3', '[\"*\"]', NULL, NULL, '2026-04-04 13:02:10', '2026-04-04 13:02:10'),
(2204, 'App\\Models\\User', 150, 'auth-token', 'c2defb657d3c155d9e27021195e8b03f0bba1ad2f97e3edd54e61d96ea75f893', '[\"*\"]', NULL, NULL, '2026-04-04 13:03:32', '2026-04-04 13:03:32'),
(2205, 'App\\Models\\User', 154, 'auth-token', '7baf2ff659f4abb1e3edebb697fe0b0ee337bc067e1bb2923f4b3148c6405c26', '[\"*\"]', NULL, NULL, '2026-04-04 13:09:57', '2026-04-04 13:09:57'),
(2206, 'App\\Models\\User', 151, 'auth-token', '58e3fff9722428427c3cc416f156766010e72b9b4fed9b776a03cac04eb59444', '[\"*\"]', NULL, NULL, '2026-04-04 13:12:06', '2026-04-04 13:12:06'),
(2207, 'App\\Models\\User', 148, 'auth-token', '345c8de0916211e60b133bd08dede8e5ec4ffde054102bc15e9539e9bad24dcc', '[\"*\"]', NULL, NULL, '2026-04-04 13:24:09', '2026-04-04 13:24:09'),
(2208, 'App\\Models\\User', 187, 'auth-token', '57877470d09ae4574dc788ef617c18144056134f52fc21dcf393c253b20ab663', '[\"*\"]', NULL, NULL, '2026-04-04 13:24:37', '2026-04-04 13:24:37'),
(2209, 'App\\Models\\User', 28, 'auth-token', '5b4d423194b6df53ace28f4f7117d792877c8d1f850d6198116dc38969c5c22f', '[\"*\"]', NULL, NULL, '2026-04-04 13:24:58', '2026-04-04 13:24:58'),
(2210, 'App\\Models\\User', 28, 'auth-token', 'c6bfe9922359db5b586a3e55a4fc3cbe61ed25464bc22daac14373adee48d86a', '[\"*\"]', NULL, NULL, '2026-04-04 13:37:14', '2026-04-04 13:37:14'),
(2211, 'App\\Models\\User', 149, 'auth-token', 'fbc38fcf197ef0fad3579848ab8b6f91fb51fa78c68f9b7cb2c3314ff5b9e9e6', '[\"*\"]', '2026-04-04 14:36:18', NULL, '2026-04-04 13:42:06', '2026-04-04 14:36:18'),
(2212, 'App\\Models\\User', 151, 'auth-token', 'ad299730b6276ecaeea2b1455179e7ac667f0544ce9e96b87ffdc4ff24c71575', '[\"*\"]', NULL, NULL, '2026-04-04 14:23:51', '2026-04-04 14:23:51'),
(2213, 'App\\Models\\User', 150, 'auth-token', '529b6cc20f407823aa733dce99537e4b511b890075792ba4651c00e3a9eec8b1', '[\"*\"]', NULL, NULL, '2026-04-04 14:25:15', '2026-04-04 14:25:15'),
(2214, 'App\\Models\\User', 149, 'auth-token', 'd5a6e02a0ba894af03786fb9cddc16d25a227c3e7aaa9b5b721b4be2fe8efc4f', '[\"*\"]', NULL, NULL, '2026-04-04 14:25:50', '2026-04-04 14:25:50'),
(2215, 'App\\Models\\User', 150, 'auth-token', '91aaf6789064496dc02ba2e3d680ed57496826432b392d3138e07554fdedb411', '[\"*\"]', NULL, NULL, '2026-04-04 14:26:31', '2026-04-04 14:26:31'),
(2216, 'App\\Models\\User', 149, 'auth-token', '78de3a25bf7581c5ce9e2d860b90a6e8dfec3142d0e19fe082139d5b79a8a761', '[\"*\"]', NULL, NULL, '2026-04-04 14:26:55', '2026-04-04 14:26:55'),
(2217, 'App\\Models\\User', 150, 'auth-token', 'd8ebfe90d31ec61af0c14a38a9aa13b5c53869115b6afa2ceb11badfe3427862', '[\"*\"]', NULL, NULL, '2026-04-04 14:27:24', '2026-04-04 14:27:24'),
(2218, 'App\\Models\\User', 152, 'auth-token', 'cf6847089ff8a7834e03953124894fb8d058dc1839393409f054c084cbc05cbe', '[\"*\"]', NULL, NULL, '2026-04-04 14:29:23', '2026-04-04 14:29:23'),
(2219, 'App\\Models\\User', 158, 'auth-token', 'a4f339936c16b9ef48ae269517c7789e8db5c11e7e14dce1aff2a9219535b8da', '[\"*\"]', NULL, NULL, '2026-04-04 14:29:41', '2026-04-04 14:29:41'),
(2220, 'App\\Models\\User', 150, 'auth-token', 'bc4274844c3d8a10c9480b6c44719a2f2d0b467c05ef8ec9526cfbf9cbee8d4a', '[\"*\"]', NULL, NULL, '2026-04-04 14:30:08', '2026-04-04 14:30:08'),
(2221, 'App\\Models\\User', 152, 'auth-token', '87c39ef36c2e9a80ee98f04610bfda04aa1129df1a0f385c749cab19790b1cc9', '[\"*\"]', NULL, NULL, '2026-04-04 14:30:37', '2026-04-04 14:30:37'),
(2222, 'App\\Models\\User', 150, 'auth-token', 'cf869230b96a444299e612b4b385c7fc7e42a33b0e72740f5744517368cbc192', '[\"*\"]', NULL, NULL, '2026-04-04 14:31:13', '2026-04-04 14:31:13'),
(2223, 'App\\Models\\User', 149, 'auth-token', '61aa3013ea881e64ad24819ac61ad97dbd48d4316f427af288e5f413fd084f37', '[\"*\"]', NULL, NULL, '2026-04-04 14:32:27', '2026-04-04 14:32:27'),
(2224, 'App\\Models\\User', 150, 'auth-token', '7d8ba63fe2c014f8b55e889c1993b735e6e51db034e5aca44e4b861e101fc3cf', '[\"*\"]', NULL, NULL, '2026-04-04 14:33:02', '2026-04-04 14:33:02'),
(2225, 'App\\Models\\User', 154, 'auth-token', '3ec0a7bab484804c2a0045f1f37d3eb1855444d6764255290d0ac46316d1c6b0', '[\"*\"]', NULL, NULL, '2026-04-04 14:34:09', '2026-04-04 14:34:09'),
(2226, 'App\\Models\\User', 150, 'auth-token', '1f2090fdf0aa856cf12512d2d9a09d219b680471004256d267dfc6da4dfdddac', '[\"*\"]', NULL, NULL, '2026-04-04 14:34:42', '2026-04-04 14:34:42'),
(2227, 'App\\Models\\User', 152, 'auth-token', '020bb31a7a794d822289ce48f62213ea3bbe419e09ed293a1030279ee6b8f52b', '[\"*\"]', NULL, NULL, '2026-04-04 14:35:19', '2026-04-04 14:35:19'),
(2228, 'App\\Models\\User', 187, 'auth-token', 'a50a44a4e49a1ae8d315043844842eb84a7c5a4943e12f3a6c14311b867edb4a', '[\"*\"]', NULL, NULL, '2026-04-04 14:36:02', '2026-04-04 14:36:02'),
(2229, 'App\\Models\\User', 152, 'auth-token', 'e78c97ecb49490b24e74b0cc0fa3abc385a369f2e51a66d6c816c1b926b3fbf3', '[\"*\"]', NULL, NULL, '2026-04-04 14:36:20', '2026-04-04 14:36:20'),
(2230, 'App\\Models\\User', 150, 'auth-token', 'ebb1dfb61b2fc5d6ae0721df6236bffc71fa5571cef86c7f9a20a7aa4fb96ffd', '[\"*\"]', NULL, NULL, '2026-04-04 14:37:05', '2026-04-04 14:37:05'),
(2231, 'App\\Models\\User', 149, 'auth-token', '5257421e8d68f00ae4cbd2fe21e56d35a28e5ab4691b68cc006dd2fa9ac2de5f', '[\"*\"]', NULL, NULL, '2026-04-04 14:37:38', '2026-04-04 14:37:38'),
(2232, 'App\\Models\\User', 150, 'auth-token', '7cfd6519a050b54d6431c34bfd9f0250f29c3bc328c4d230899edf748fa2a404', '[\"*\"]', NULL, NULL, '2026-04-04 14:38:09', '2026-04-04 14:38:09'),
(2233, 'App\\Models\\User', 152, 'auth-token', '1e2aaca2cccb1cdc81dd6aca0532c9a7c99b80d4ab1e7e8d716545e76f01e507', '[\"*\"]', NULL, NULL, '2026-04-04 14:38:33', '2026-04-04 14:38:33'),
(2234, 'App\\Models\\User', 150, 'auth-token', '89a4b9305436d2490bdbd873057e3d5acb8625e3c1769cb538c03930609d539d', '[\"*\"]', NULL, NULL, '2026-04-04 14:39:03', '2026-04-04 14:39:03'),
(2235, 'App\\Models\\User', 149, 'auth-token', 'e485d4d2979e052faf4d73950f2088461a2f32ca6aefd40048a03218e4ad4f99', '[\"*\"]', NULL, NULL, '2026-04-04 14:39:35', '2026-04-04 14:39:35'),
(2236, 'App\\Models\\User', 150, 'auth-token', 'a14cccbf817046b01ed58ecd9794bdd3c2d847302bf8379a282223f2e34cd8e2', '[\"*\"]', NULL, NULL, '2026-04-04 14:40:10', '2026-04-04 14:40:10'),
(2237, 'App\\Models\\User', 151, 'auth-token', 'b59ebcb5b7041d7cc71f4ae9f6e57867caeefdf1fb27471eab7c50f75a12d5e7', '[\"*\"]', NULL, NULL, '2026-04-04 14:40:41', '2026-04-04 14:40:41'),
(2238, 'App\\Models\\User', 157, 'auth-token', 'fff4c7c4ee625de8751151ef29e9d066c501777b0ba60221b988b1be3e41e831', '[\"*\"]', NULL, NULL, '2026-04-04 16:33:47', '2026-04-04 16:33:47'),
(2239, 'App\\Models\\User', 147, 'auth-token', '12ddb9bb1881f5e49b303449e45ca395dd9355ee35005e2573799f490b6444c6', '[\"*\"]', NULL, NULL, '2026-04-04 17:03:43', '2026-04-04 17:03:43'),
(2240, 'App\\Models\\User', 28, 'auth-token', 'facd8647123ce4866efc7164d06c0bca78b9f5f48fa0501ae554f01f9b37216e', '[\"*\"]', NULL, NULL, '2026-04-04 17:04:59', '2026-04-04 17:04:59'),
(2241, 'App\\Models\\User', 28, 'auth-token', '451cc4bede3b348880b53faa37e1d8ec6dbb5613b96bd8dfe267ba825a5992bc', '[\"*\"]', NULL, NULL, '2026-04-04 17:10:17', '2026-04-04 17:10:17'),
(2242, 'App\\Models\\User', 28, 'auth-token', '43f0ba365173e756e2c07d8639f8ff99722a6392fbc61e0d93d6b86db19ee779', '[\"*\"]', NULL, NULL, '2026-04-04 17:12:45', '2026-04-04 17:12:45'),
(2243, 'App\\Models\\User', 28, 'auth-token', '3080a86f6bca270065ea89a471ee17ce8c1843ee677eb20c9fd1f42e5bead3a6', '[\"*\"]', NULL, NULL, '2026-04-04 17:13:17', '2026-04-04 17:13:17'),
(2244, 'App\\Models\\User', 28, 'auth-token', 'df2985c879531565a43197bd01f7fe9f1e2b347d4190d470c16d1a0c9138dc61', '[\"*\"]', NULL, NULL, '2026-04-04 19:02:30', '2026-04-04 19:02:30'),
(2245, 'App\\Models\\User', 147, 'auth-token', '53f13c694b268aaab85cad9db126d13ae6484cbb63b60097362d3eaa43e5a6ae', '[\"*\"]', NULL, NULL, '2026-04-04 19:12:47', '2026-04-04 19:12:47'),
(2246, 'App\\Models\\User', 151, 'auth-token', '95f448aa0119fdd8315b31ea0c273b2c484334b899adcb1fec833958baa553b6', '[\"*\"]', NULL, NULL, '2026-04-04 19:20:58', '2026-04-04 19:20:58'),
(2247, 'App\\Models\\User', 31, 'auth-token', 'fd633e950a4e5f1f51f4dada5d282e74b5c679fca70d70adf3c5aa3295f17f50', '[\"*\"]', NULL, NULL, '2026-04-04 19:32:59', '2026-04-04 19:32:59'),
(2248, 'App\\Models\\User', 151, 'auth-token', 'd874099dfc18e2084ca4b085804646743f20391b379dd9a598087486490aafb1', '[\"*\"]', NULL, NULL, '2026-04-04 19:41:11', '2026-04-04 19:41:11'),
(2249, 'App\\Models\\User', 147, 'auth-token', '128182a035913ce58d3486a18319d9e25504ecdab2711d6c94cc2fd003efb06d', '[\"*\"]', NULL, NULL, '2026-04-04 19:41:27', '2026-04-04 19:41:27'),
(2250, 'App\\Models\\User', 147, 'auth-token', '9724222835f222c285c9548f7b921f7834975b44e5ceba0f468ce6ecbfc86f25', '[\"*\"]', NULL, NULL, '2026-04-04 20:06:59', '2026-04-04 20:06:59'),
(2251, 'App\\Models\\User', 147, 'auth-token', 'fcb71683038be733df2af4044b354d4938dbd11ddc43e78feca348b4ab125ba6', '[\"*\"]', NULL, NULL, '2026-04-04 20:21:16', '2026-04-04 20:21:16'),
(2252, 'App\\Models\\User', 31, 'auth-token', 'b609be2fc2ad3f47fc0ee3401a84f95c64ec80fe8611d993b7a0812b64fb1a4d', '[\"*\"]', NULL, NULL, '2026-04-04 20:24:18', '2026-04-04 20:24:18'),
(2253, 'App\\Models\\User', 28, 'auth-token', '60efbdcefc1116a94e6dc927b54f604d2c05154c56c20ea96510c1e6a53a3e19', '[\"*\"]', NULL, NULL, '2026-04-04 20:25:01', '2026-04-04 20:25:01'),
(2254, 'App\\Models\\User', 31, 'auth-token', 'ec6396e6db3f13664b47dad13e7f8d70365b4355f4dcb0753019058c0fd68bfb', '[\"*\"]', NULL, NULL, '2026-04-04 20:25:57', '2026-04-04 20:25:57'),
(2255, 'App\\Models\\User', 31, 'auth-token', '32539127042982972e4cbe65c809f847625530c0e6e9c732d4c29e682696d23f', '[\"*\"]', NULL, NULL, '2026-04-04 20:26:09', '2026-04-04 20:26:09'),
(2256, 'App\\Models\\User', 147, 'auth-token', 'b14980c206cfd137a17d7a018cf6a4a4e96406185faac5ce5febfb89fcae0cb9', '[\"*\"]', NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(2257, 'App\\Models\\User', 147, 'auth-token', '2db0acbc4185ebebc307fdd4129c4844006270d32e5e74e56b4f6af8023b4062', '[\"*\"]', NULL, NULL, '2026-04-04 20:45:31', '2026-04-04 20:45:31'),
(2258, 'App\\Models\\User', 147, 'auth-token', '2965ce593474dee721a8ae702f829d4dd0383d77e7d55510204f5245e3b7149c', '[\"*\"]', NULL, NULL, '2026-04-04 20:49:18', '2026-04-04 20:49:18'),
(2259, 'App\\Models\\User', 147, 'auth-token', 'aed4b76570a3a217424d27cfd693a5a529c4371c33a90a2a3c1e3aac0da493d3', '[\"*\"]', NULL, NULL, '2026-04-04 21:14:31', '2026-04-04 21:14:31'),
(2260, 'App\\Models\\User', 28, 'auth-token', 'b3fc8226a670453a1b491e6027f276d875b57d9d5922a7501746136a27d56ff0', '[\"*\"]', NULL, NULL, '2026-04-04 21:18:34', '2026-04-04 21:18:34'),
(2261, 'App\\Models\\User', 151, 'auth-token', '08601b91897eada5be98874ce406bdeb4da04ac582e6d0b0238a7771449047c5', '[\"*\"]', NULL, NULL, '2026-04-04 21:20:20', '2026-04-04 21:20:20'),
(2262, 'App\\Models\\User', 28, 'auth-token', 'fd32f5477781d11eb69979adddd7f563fa60f7960a462080f2d3bbc74b1f7a9b', '[\"*\"]', NULL, NULL, '2026-04-04 21:52:33', '2026-04-04 21:52:33'),
(2263, 'App\\Models\\User', 147, 'auth-token', 'acf3aac1c7caae92a3486c1241a8f6fe0d79429da70d0ec5e90a26b10f8ac576', '[\"*\"]', NULL, NULL, '2026-04-04 21:53:47', '2026-04-04 21:53:47'),
(2264, 'App\\Models\\User', 147, 'auth-token', '4431b5e9b8d1a5327c2ae7548f0d4f7310482a29b4e2f04f714ec72018779d88', '[\"*\"]', NULL, NULL, '2026-04-04 21:54:26', '2026-04-04 21:54:26'),
(2265, 'App\\Models\\User', 28, 'auth-token', '5e551f43b45e8523489f5a6fb2e2e18967d7b9dd229cb51910039c252d33bba9', '[\"*\"]', NULL, NULL, '2026-04-04 22:38:14', '2026-04-04 22:38:14'),
(2266, 'App\\Models\\User', 151, 'auth-token', 'b58aa626d95e080fcfcb7bf99a594a0209f13a5b7d1f8e3235eea44a2688a90f', '[\"*\"]', NULL, NULL, '2026-04-04 22:45:48', '2026-04-04 22:45:48'),
(2267, 'App\\Models\\User', 148, 'auth-token', '18cfdaea5fb6b977b32793436155f72e26603105fe8b9ccf618ea67a5683dbb2', '[\"*\"]', NULL, NULL, '2026-04-04 23:24:32', '2026-04-04 23:24:32'),
(2268, 'App\\Models\\User', 28, 'auth-token', 'cac795c42e23650fcb7953db741a1b608d841b2b5ace488970c8939900e4fe14', '[\"*\"]', NULL, NULL, '2026-04-04 23:40:47', '2026-04-04 23:40:47'),
(2269, 'App\\Models\\User', 153, 'auth-token', 'c9c586cde622d28afd38911c1c65b12e5a077a392e2e13637f1d613b632b0805', '[\"*\"]', NULL, NULL, '2026-04-05 01:15:38', '2026-04-05 01:15:38'),
(2270, 'App\\Models\\User', 148, 'auth-token', 'e7507a820dc8fa947420b4f6cff4a8648d3acd9723c5ea3bb63c3ae505666b12', '[\"*\"]', NULL, NULL, '2026-04-05 01:20:06', '2026-04-05 01:20:06'),
(2271, 'App\\Models\\User', 148, 'auth-token', 'f10867a5fa4c4dc640f05c6b8ff28b49deeda361b61297b9ca08c6255c46b393', '[\"*\"]', NULL, NULL, '2026-04-05 01:21:10', '2026-04-05 01:21:10'),
(2272, 'App\\Models\\User', 157, 'auth-token', '7253825a59b66a8464337d54cbe32da2417003cd3de1f8fbbd9fa8af9dd204ba', '[\"*\"]', NULL, NULL, '2026-04-05 01:21:57', '2026-04-05 01:21:57'),
(2273, 'App\\Models\\User', 28, 'auth-token', '99f6bd6491769a5607610791d8fe98fca0345196e00523f16d49a1333878866e', '[\"*\"]', NULL, NULL, '2026-04-05 01:24:16', '2026-04-05 01:24:16'),
(2274, 'App\\Models\\User', 157, 'auth-token', '29a5a5762ef036654af688a34180b3716553ebfff21a8701b51746731a0bf886', '[\"*\"]', NULL, NULL, '2026-04-05 01:31:08', '2026-04-05 01:31:08'),
(2275, 'App\\Models\\User', 157, 'auth-token', '6a82fe6ad28f8e74b73610160216dc4f0adc807b92e4f7b90e4f254f7f297614', '[\"*\"]', NULL, NULL, '2026-04-05 01:36:24', '2026-04-05 01:36:24'),
(2276, 'App\\Models\\User', 28, 'auth-token', 'e4fe787caee6a057f6a29c29e10ee92c98bacdce48f26b266e6f825d01c1054d', '[\"*\"]', NULL, NULL, '2026-04-05 01:37:22', '2026-04-05 01:37:22'),
(2277, 'App\\Models\\User', 148, 'auth-token', 'a5b6cef7e17439a6d0c90ee55a08f5f32ea6fcba0df674dec274e1a770d97d2e', '[\"*\"]', NULL, NULL, '2026-04-05 01:42:35', '2026-04-05 01:42:35'),
(2278, 'App\\Models\\User', 28, 'auth-token', '075853b999f0ce903e72d1482a543f9de925ed1ef25de31416e5cdc0361af088', '[\"*\"]', NULL, NULL, '2026-04-05 01:43:04', '2026-04-05 01:43:04'),
(2279, 'App\\Models\\User', 151, 'auth-token', '922604947bb586c0f16af000ebd40ca30aff4ffb329090c97c12021a9e85a5e0', '[\"*\"]', NULL, NULL, '2026-04-05 01:44:54', '2026-04-05 01:44:54'),
(2280, 'App\\Models\\User', 28, 'auth-token', '4339a1d139f472535b820102a1844ed5fe1a6a133dad0129a4978a575fbda257', '[\"*\"]', NULL, NULL, '2026-04-05 01:51:45', '2026-04-05 01:51:45'),
(2281, 'App\\Models\\User', 159, 'auth-token', '2824041f316d8621484c73010d15ac5d3aa82a7c2a5d4419cfbf1ec9eed20281', '[\"*\"]', NULL, NULL, '2026-04-05 07:08:23', '2026-04-05 07:08:23'),
(2282, 'App\\Models\\User', 159, 'auth-token', 'ed8cf3275993ac679466fedbd4dff67da8ec335f6310e2b798d644ff760a4d2b', '[\"*\"]', NULL, NULL, '2026-04-05 07:17:17', '2026-04-05 07:17:17'),
(2283, 'App\\Models\\User', 28, 'auth-token', 'c34252d8115ac1530142a7bcdb5f8b9dde71457a3cd77e3913e0338f92bb3912', '[\"*\"]', NULL, NULL, '2026-04-05 07:19:00', '2026-04-05 07:19:00'),
(2284, 'App\\Models\\User', 187, 'auth-token', 'cca87e8c35d66289ab41146ac4d055429e54c1215190987d6c4252b0817bab48', '[\"*\"]', NULL, NULL, '2026-04-05 07:23:48', '2026-04-05 07:23:48'),
(2285, 'App\\Models\\User', 162, 'auth-token', '42ac4177c157ac3ca1d42a0a5791df16ae972abf25a3b76189a1ce99c6cd771a', '[\"*\"]', NULL, NULL, '2026-04-05 07:26:41', '2026-04-05 07:26:41'),
(2286, 'App\\Models\\User', 161, 'auth-token', '529a03b9aa7642b0276ea95a5afe9e129adae7d267798299b8d0cc3856e43b5b', '[\"*\"]', NULL, NULL, '2026-04-05 07:27:48', '2026-04-05 07:27:48'),
(2287, 'App\\Models\\User', 160, 'auth-token', '4bccb2a59ad5b2d66e9d2bb0a4e395a1c925fe074ae4a88b2a0d3451ef695450', '[\"*\"]', NULL, NULL, '2026-04-05 07:29:47', '2026-04-05 07:29:47'),
(2288, 'App\\Models\\User', 159, 'auth-token', '9af949f20f522eb7af1dd94f148d635dc9b64c256085fa6d18053a037e1192fc', '[\"*\"]', NULL, NULL, '2026-04-05 07:33:03', '2026-04-05 07:33:03'),
(2289, 'App\\Models\\User', 28, 'auth-token', '2125bd7784a81a79120cb71ecba67d6913b3999375222b6e8b4c29b7e5f7191b', '[\"*\"]', NULL, NULL, '2026-04-05 07:33:34', '2026-04-05 07:33:34'),
(2290, 'App\\Models\\User', 28, 'auth-token', '2cd54ffba4655d3ff73e892e74a39f098c824edb54bc8118ac0ec807a247dc55', '[\"*\"]', NULL, NULL, '2026-04-05 07:41:10', '2026-04-05 07:41:10'),
(2291, 'App\\Models\\User', 193, 'auth-token', 'dde12743ce798e239bf7d1dd83ac1ffe430df82df6659038e96813c6d1280043', '[\"*\"]', NULL, NULL, '2026-04-05 07:41:59', '2026-04-05 07:41:59'),
(2292, 'App\\Models\\User', 193, 'auth-token', '88162a55693ccaf4bd9765cb4c564c7ac8198df735ad148d191b6d3f0a85fb61', '[\"*\"]', NULL, NULL, '2026-04-05 07:42:27', '2026-04-05 07:42:27'),
(2293, 'App\\Models\\User', 154, 'auth-token', '8c0dad0958b502461ab97ac877efb23b091f06313ff827b24f5f64f13def0e9c', '[\"*\"]', NULL, NULL, '2026-04-05 07:43:57', '2026-04-05 07:43:57'),
(2294, 'App\\Models\\User', 150, 'auth-token', '33b9bb956cb495c1b4bf19aedcb28edb3ce1190bca364626e86757bd8a71ac7d', '[\"*\"]', NULL, NULL, '2026-04-05 07:44:24', '2026-04-05 07:44:24'),
(2295, 'App\\Models\\User', 152, 'auth-token', 'b6e13f0bfbde982d6967cac6ec9d8c05cdeb402aad1e37604588842799f722c8', '[\"*\"]', NULL, NULL, '2026-04-05 07:44:42', '2026-04-05 07:44:42'),
(2296, 'App\\Models\\User', 159, 'auth-token', '54beddffb8a9f1b0e25327da9f23fc6186cf37eaa5f50733b15104c18f5124f9', '[\"*\"]', NULL, NULL, '2026-04-05 07:49:38', '2026-04-05 07:49:38'),
(2297, 'App\\Models\\User', 153, 'auth-token', '42b515a81e38886f8355548656fe8f0e435013be22d323b6f5f61ec49604f712', '[\"*\"]', NULL, NULL, '2026-04-05 07:51:27', '2026-04-05 07:51:27'),
(2298, 'App\\Models\\User', 150, 'auth-token', '47c84c3a076da366176e188d3e342f3a375257fc9ac34de9a756692f86bc32a1', '[\"*\"]', NULL, NULL, '2026-04-05 07:54:07', '2026-04-05 07:54:07'),
(2299, 'App\\Models\\User', 149, 'auth-token', '208272c16f6fb34a0cc6ee9b4d753ce89dd49a7c409496e223856b078d8a189d', '[\"*\"]', NULL, NULL, '2026-04-05 07:54:39', '2026-04-05 07:54:39'),
(2300, 'App\\Models\\User', 150, 'auth-token', '7d38b174d4285c80036c8937ef4652a88176501229bed85f947db4a40154d925', '[\"*\"]', NULL, NULL, '2026-04-05 07:56:02', '2026-04-05 07:56:02'),
(2301, 'App\\Models\\User', 152, 'auth-token', 'b1d7fd068814daff8fa60a06e0d3f417cbbd20a10b3184d192b19e59a8156023', '[\"*\"]', NULL, NULL, '2026-04-05 07:56:39', '2026-04-05 07:56:39'),
(2302, 'App\\Models\\User', 150, 'auth-token', '89c3ebd24938eea8b532b8db2c89d7dd27b8020f80ae9d0a4526896a60fee313', '[\"*\"]', NULL, NULL, '2026-04-05 07:57:31', '2026-04-05 07:57:31'),
(2303, 'App\\Models\\User', 149, 'auth-token', '8c54fa3cdd3907749364ba1a569687138f5d09111a6e8af39b21aee8103acebf', '[\"*\"]', NULL, NULL, '2026-04-05 07:58:15', '2026-04-05 07:58:15'),
(2304, 'App\\Models\\User', 150, 'auth-token', '4c47ef8a5f06dacf8c0541ba029ba8325784af5eb2bcabef5047f134863494bf', '[\"*\"]', NULL, NULL, '2026-04-05 07:59:10', '2026-04-05 07:59:10'),
(2305, 'App\\Models\\User', 151, 'auth-token', '433b5807cf41e4927b035b4d60def29d1780a8d1d38190101881d20860333821', '[\"*\"]', NULL, NULL, '2026-04-05 07:59:40', '2026-04-05 07:59:40'),
(2306, 'App\\Models\\User', 154, 'auth-token', 'a8d849066e57af76a842cc2b38afef54ba928e014b105b1036950e0d1e127723', '[\"*\"]', NULL, NULL, '2026-04-05 08:00:59', '2026-04-05 08:00:59'),
(2307, 'App\\Models\\User', 162, 'auth-token', 'e268e6890fe161173c2bf9ab7502acbc9512a20275175a17218bc9509fad7d65', '[\"*\"]', NULL, NULL, '2026-04-05 08:03:01', '2026-04-05 08:03:01'),
(2308, 'App\\Models\\User', 31, 'auth-token', '51186fc2f4b17c131ac6b7ed2a60ab6ac549797ea6f2c2e90ba41e67497017cd', '[\"*\"]', NULL, NULL, '2026-04-05 08:03:57', '2026-04-05 08:03:57'),
(2309, 'App\\Models\\User', 154, 'auth-token', '353c561c4c4f9962ba923fa080fd64e5270caa1e9bf42313a136a95510be6157', '[\"*\"]', NULL, NULL, '2026-04-05 08:04:40', '2026-04-05 08:04:40'),
(2310, 'App\\Models\\User', 150, 'auth-token', '6020e737b0b2b9aed7f39a73f93be67d4258bf9d955ec716b6a5f33b5bd9620c', '[\"*\"]', NULL, NULL, '2026-04-05 08:05:06', '2026-04-05 08:05:06'),
(2311, 'App\\Models\\User', 158, 'auth-token', '5a52e9bf6c0db58d03660b501f2a3aecfecb68de1911c48b64e2f1869a4cd066', '[\"*\"]', NULL, NULL, '2026-04-05 08:05:28', '2026-04-05 08:05:28'),
(2312, 'App\\Models\\User', 152, 'auth-token', 'b2667bfd83f295249147f1832ebc5331ccdc429873f98918414b98f7e1b1b704', '[\"*\"]', NULL, NULL, '2026-04-05 08:06:38', '2026-04-05 08:06:38'),
(2313, 'App\\Models\\User', 150, 'auth-token', 'bb9994827b0a0f61dd60e1982bd26151b46f6902412ad77b2f4702e9fed6f28c', '[\"*\"]', NULL, NULL, '2026-04-05 08:07:05', '2026-04-05 08:07:05'),
(2314, 'App\\Models\\User', 157, 'auth-token', '05ff912650c48429a96210f4f36a148d0e564b31d2177d005327ae85b34ebc6d', '[\"*\"]', NULL, NULL, '2026-04-05 08:09:05', '2026-04-05 08:09:05'),
(2315, 'App\\Models\\User', 31, 'auth-token', '6bf412d430dc551f9468af98e861840a7988ac5768fddb82e5f5995014080fa4', '[\"*\"]', NULL, NULL, '2026-04-05 08:10:37', '2026-04-05 08:10:37'),
(2316, 'App\\Models\\User', 154, 'auth-token', 'ab6946b9d382f03d5099d7702e5c8e603861a7151e41cd33c1c95668f7c1496d', '[\"*\"]', NULL, NULL, '2026-04-05 08:11:09', '2026-04-05 08:11:09'),
(2317, 'App\\Models\\User', 148, 'auth-token', 'd530e142da0a76906724e8e848bf642c36c7adecd8ef32ce747a4551333013ea', '[\"*\"]', NULL, NULL, '2026-04-05 08:12:06', '2026-04-05 08:12:06'),
(2318, 'App\\Models\\User', 159, 'auth-token', '93e2604dd7adb078c6bd934e60e0ed95d855805637f99000d35baa0321ca9ad8', '[\"*\"]', NULL, NULL, '2026-04-05 09:43:42', '2026-04-05 09:43:42'),
(2319, 'App\\Models\\User', 148, 'auth-token', '4acd5d77d57a0e1e74546e85501b3b45b988dec401c3b9657663744301a47167', '[\"*\"]', NULL, NULL, '2026-04-05 10:03:45', '2026-04-05 10:03:45'),
(2320, 'App\\Models\\User', 148, 'auth-token', '4a49c6afd0650e138cf652caa1c45126ffb9fdc5b9b5cd38c0aef86114247eeb', '[\"*\"]', NULL, NULL, '2026-04-05 10:10:49', '2026-04-05 10:10:49'),
(2321, 'App\\Models\\User', 153, 'auth-token', '895098d631f55a5da5e505f58041eb6270bfb35c3987402d2a5a2698f74ec9c1', '[\"*\"]', NULL, NULL, '2026-04-05 10:29:44', '2026-04-05 10:29:44'),
(2322, 'App\\Models\\User', 151, 'auth-token', '43f0569744ac2141185d52dd681cd691842986f73dc7b5f632ee0393df0533ac', '[\"*\"]', NULL, NULL, '2026-04-05 10:36:05', '2026-04-05 10:36:05'),
(2323, 'App\\Models\\User', 154, 'auth-token', '34e2665178d6b1f743924c5f96907e6a5aae804e0834527690e03f28fc7e95e3', '[\"*\"]', NULL, NULL, '2026-04-05 10:36:18', '2026-04-05 10:36:18'),
(2324, 'App\\Models\\User', 187, 'auth-token', '0638c67b85b4ff05f7a68c8f9ad2ca91a692c00d995f0d086e523a44ee668929', '[\"*\"]', NULL, NULL, '2026-04-05 10:36:36', '2026-04-05 10:36:36'),
(2325, 'App\\Models\\User', 149, 'auth-token', 'e7db12d684c01a7afb13c8412045b2021c3a6d5236621b6f9dc72aeae9398990', '[\"*\"]', NULL, NULL, '2026-04-05 11:05:02', '2026-04-05 11:05:02'),
(2326, 'App\\Models\\User', 153, 'auth-token', '33caf024f2b4cb10f3dececda2ed1b7856b5d1503a1594cc29124b3ee9e6d290', '[\"*\"]', NULL, NULL, '2026-04-05 11:05:24', '2026-04-05 11:05:24'),
(2327, 'App\\Models\\User', 151, 'auth-token', 'fc69babf537b327929a51bb1eabdc199c887d19fc7ccb7fa0e2c7c884bb6acd0', '[\"*\"]', NULL, NULL, '2026-04-05 11:06:45', '2026-04-05 11:06:45'),
(2328, 'App\\Models\\User', 149, 'auth-token', '0e20d6cf5ed6a3af6df703c1d13b2923d9a2182a0b99d35945731d5ef68b18fd', '[\"*\"]', NULL, NULL, '2026-04-05 11:07:01', '2026-04-05 11:07:01'),
(2329, 'App\\Models\\User', 31, 'auth-token', 'cb65ce91f82b496b2ff32ee0c41f89a06d22b525f881cc63344cb84d8c5a4eda', '[\"*\"]', NULL, NULL, '2026-04-05 11:25:07', '2026-04-05 11:25:07'),
(2330, 'App\\Models\\User', 159, 'auth-token', '3a4bfbd3eaff01c987858d28838d8134de499b05cf6b75d18121dc5538ef772e', '[\"*\"]', NULL, NULL, '2026-04-05 11:32:27', '2026-04-05 11:32:27'),
(2331, 'App\\Models\\User', 31, 'auth-token', 'aeb18ff556f1a58e83737c370d13dbcc1513d1c145e8ae967f578f7e24c2b267', '[\"*\"]', NULL, NULL, '2026-04-05 11:34:04', '2026-04-05 11:34:04'),
(2332, 'App\\Models\\User', 159, 'auth-token', 'f8de43a4c6fa8d197e3afd54cbb7f5e15dcf62904a88bed12a7e7caefa37a98e', '[\"*\"]', NULL, NULL, '2026-04-05 11:43:09', '2026-04-05 11:43:09'),
(2333, 'App\\Models\\User', 31, 'auth-token', '0f241e8613b5eae6238a3621fc54dc3b4e8f07b83124c058adc048103baeb316', '[\"*\"]', NULL, NULL, '2026-04-05 12:00:16', '2026-04-05 12:00:16'),
(2334, 'App\\Models\\User', 161, 'auth-token', 'bf7f2e857a74a2621efd77860208b0315df7a94d9c96f4bb315210a837ee3328', '[\"*\"]', NULL, NULL, '2026-04-05 12:01:17', '2026-04-05 12:01:17'),
(2335, 'App\\Models\\User', 31, 'auth-token', '47df8fed30aa50faa7ca21e3a782a065a306ccf9f432dc48fd0317bb0b1e263d', '[\"*\"]', NULL, NULL, '2026-04-05 12:03:06', '2026-04-05 12:03:06'),
(2336, 'App\\Models\\User', 147, 'auth-token', 'c80befcc21dad0cc4465d2d2b3103fc366c56bb04613f2cea249603f994c4303', '[\"*\"]', NULL, NULL, '2026-04-05 12:04:00', '2026-04-05 12:04:00'),
(2337, 'App\\Models\\User', 159, 'auth-token', 'ab90ee7d57be01eec310c1a560fb4a648dbaad025a418bf98da2f2f7a76a97f2', '[\"*\"]', NULL, NULL, '2026-04-05 12:04:16', '2026-04-05 12:04:16'),
(2338, 'App\\Models\\User', 161, 'auth-token', 'f5fb0b5f084534ee9a5456c5d0b42556080ac7bd621d77f247a05e1513efe403', '[\"*\"]', NULL, NULL, '2026-04-05 12:06:45', '2026-04-05 12:06:45'),
(2339, 'App\\Models\\User', 31, 'auth-token', '763de1a7565f74a95e423d0b08dc8a1ac4039576c8b5986dcde63fcbd9eddebe', '[\"*\"]', NULL, NULL, '2026-04-05 12:07:09', '2026-04-05 12:07:09'),
(2340, 'App\\Models\\User', 159, 'auth-token', 'dd0b0611a427faffb2b095e2dad48c30dabde2fcd15ac051fc582c08ebde5cad', '[\"*\"]', NULL, NULL, '2026-04-05 12:09:23', '2026-04-05 12:09:23'),
(2341, 'App\\Models\\User', 161, 'auth-token', '2d3ee22ff07d3bb13def8cb72b51abd7de6a921d85e067a7542bb958db289653', '[\"*\"]', NULL, NULL, '2026-04-05 12:37:55', '2026-04-05 12:37:55'),
(2342, 'App\\Models\\User', 31, 'auth-token', '6c286aefe84f506d28ae278ea1c6bd381f482c1ec7bd2406c25bbfbd93ebd4a2', '[\"*\"]', NULL, NULL, '2026-04-05 12:38:42', '2026-04-05 12:38:42'),
(2343, 'App\\Models\\User', 159, 'auth-token', '16780fbe5e743e35c56d9d1b123e69d90c9c54328bf62930e0149b00a1bf76f8', '[\"*\"]', NULL, NULL, '2026-04-05 12:40:27', '2026-04-05 12:40:27'),
(2344, 'App\\Models\\User', 151, 'auth-token', '3b5ec6fec601a1ecdfe85d96e38c38fb0d2203bc0a458825bba4bb3b9bbc5490', '[\"*\"]', NULL, NULL, '2026-04-05 12:41:25', '2026-04-05 12:41:25'),
(2345, 'App\\Models\\User', 150, 'auth-token', 'b1a14c580859861113f5c8dbfa80be80b6e8acba8a846dff2cef22b8e87292b9', '[\"*\"]', NULL, NULL, '2026-04-05 12:51:55', '2026-04-05 12:51:55'),
(2346, 'App\\Models\\User', 152, 'auth-token', 'c0038badad80be6ddd5fe2cdc2fc5cf17a59e4fe9b64c4a4a13df81665cc047b', '[\"*\"]', NULL, NULL, '2026-04-05 12:52:41', '2026-04-05 12:52:41'),
(2347, 'App\\Models\\User', 152, 'auth-token', '17be47277fbfe1b0eb9174e7b3c72836cc15be58ab27b28abb8ba8c31635d591', '[\"*\"]', NULL, NULL, '2026-04-05 12:55:35', '2026-04-05 12:55:35'),
(2348, 'App\\Models\\User', 149, 'auth-token', '17d21d3d51464e4aa0ba05c9186f5ad95073ecf38f09b7ce2b4a4ee3bf6d041b', '[\"*\"]', NULL, NULL, '2026-04-05 13:14:03', '2026-04-05 13:14:03'),
(2349, 'App\\Models\\User', 161, 'auth-token', '281adbd5fa2f7157a16a362a518baf6fe16bb74f6dde52adfbf80af38b900cb3', '[\"*\"]', NULL, NULL, '2026-04-05 13:19:11', '2026-04-05 13:19:11'),
(2350, 'App\\Models\\User', 154, 'auth-token', '7a9e2ee7fd07619731b9d6099c39d479cc50af080ab9577397971b14cb6b439a', '[\"*\"]', NULL, NULL, '2026-04-05 13:19:40', '2026-04-05 13:19:40'),
(2351, 'App\\Models\\User', 149, 'auth-token', '7e59f65694c23e8f7ec363cb312dff5cda4a9513aaf88a0b0139de760fc1daef', '[\"*\"]', NULL, NULL, '2026-04-05 13:23:22', '2026-04-05 13:23:22'),
(2352, 'App\\Models\\User', 151, 'auth-token', '5121011dba89bd98cd04405f2f1ca6baa69cad4a8e0627df612730ca8054c287', '[\"*\"]', NULL, NULL, '2026-04-05 13:23:38', '2026-04-05 13:23:38'),
(2353, 'App\\Models\\User', 28, 'auth-token', '839a05274a11190edf585ea0585637dd00983f8fecf68d57cf9837186edbe4eb', '[\"*\"]', NULL, NULL, '2026-04-05 13:27:05', '2026-04-05 13:27:05'),
(2354, 'App\\Models\\User', 157, 'auth-token', '4e8052848f7e07364eb4648c1788874a8af20841fe1d435a52871f403a91a589', '[\"*\"]', NULL, NULL, '2026-04-05 13:29:05', '2026-04-05 13:29:05'),
(2355, 'App\\Models\\User', 149, 'auth-token', '0c63fab6c6be399ed73f847b190e57b95f34508b14331c6bf2da52b6f0a4d4b1', '[\"*\"]', NULL, NULL, '2026-04-05 13:29:23', '2026-04-05 13:29:23'),
(2356, 'App\\Models\\User', 147, 'auth-token', 'd11b822992fcc937d7a9eb27ec75d2187aafad2debf93ebcdc660a7cade6df2e', '[\"*\"]', NULL, NULL, '2026-04-05 13:30:07', '2026-04-05 13:30:07'),
(2357, 'App\\Models\\User', 151, 'auth-token', 'ed86811542b151787cd747f9483b43e53074d0862b78864c29c1e549be1afe1d', '[\"*\"]', NULL, NULL, '2026-04-05 13:30:50', '2026-04-05 13:30:50'),
(2358, 'App\\Models\\User', 150, 'auth-token', '8cea6dc87c78c40b180b20e29ca133927ae568fa04bd9b9c18301af2fa72efe0', '[\"*\"]', NULL, NULL, '2026-04-05 13:31:11', '2026-04-05 13:31:11'),
(2359, 'App\\Models\\User', 187, 'auth-token', 'cb7b082a273216abfc09dc9143afe123e9b1480cb76a925a63679b413418789b', '[\"*\"]', NULL, NULL, '2026-04-05 13:31:43', '2026-04-05 13:31:43'),
(2360, 'App\\Models\\User', 187, 'auth-token', 'd7fe4f401b5bfde603203ab4d907070cfd0e12ab244ab11211cbbb354459d646', '[\"*\"]', NULL, NULL, '2026-04-05 13:40:30', '2026-04-05 13:40:30'),
(2361, 'App\\Models\\User', 149, 'auth-token', '8d42b29c1fa896a4eb0178e61001d2fa3a1a90ea5ab3625cc3dc340c66b9331d', '[\"*\"]', NULL, NULL, '2026-04-05 13:40:53', '2026-04-05 13:40:53'),
(2362, 'App\\Models\\User', 150, 'auth-token', 'a60b360b984495325fdfb6cb0d5eba1e7ca98b6da728095369db402d0c20a771', '[\"*\"]', NULL, NULL, '2026-04-05 13:41:18', '2026-04-05 13:41:18'),
(2363, 'App\\Models\\User', 148, 'auth-token', 'ca8124c0cc5dc788bad9f941694d6eb81631b755d198693887df387e0a10060d', '[\"*\"]', NULL, NULL, '2026-04-05 13:42:18', '2026-04-05 13:42:18'),
(2364, 'App\\Models\\User', 159, 'auth-token', 'b329ce747ebef26823f1c41a61ec44ae2a5040f0191c8a126f2e1e52ac67003f', '[\"*\"]', NULL, NULL, '2026-04-05 14:37:43', '2026-04-05 14:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `price_audits`
--

CREATE TABLE `price_audits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `old_cost_price` decimal(10,2) DEFAULT NULL,
  `new_cost_price` decimal(10,2) DEFAULT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `new_price` decimal(10,2) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_markup_percentages`
--

CREATE TABLE `price_markup_percentages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT 20.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `set_by` bigint(20) UNSIGNED DEFAULT NULL,
  `set_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `price_markup_percentages`
--

INSERT INTO `price_markup_percentages` (`id`, `branch_id`, `percentage`, `is_active`, `set_by`, `set_at`, `notes`, `created_at`, `updated_at`) VALUES
(4, 31, 30.00, 0, 31, '2026-03-29 13:00:36', 'Approved via price markup request #2', '2026-03-29 13:00:36', '2026-03-30 08:42:03'),
(5, 32, 20.00, 1, NULL, NULL, NULL, '2026-03-30 04:26:15', '2026-03-30 04:26:15'),
(6, 31, 31.00, 0, 31, '2026-03-30 04:38:28', 'Approved via price markup request #3', '2026-03-30 04:38:28', '2026-03-30 08:42:03'),
(7, 31, 32.00, 0, 31, '2026-03-30 05:28:32', 'Approved via price markup request #4', '2026-03-30 05:28:32', '2026-03-30 08:42:03'),
(10, 31, 35.00, 1, 31, '2026-03-30 08:42:03', 'Approved via price markup request #5', '2026-03-30 08:42:03', '2026-03-30 08:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `price_markup_requests`
--

CREATE TABLE `price_markup_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED NOT NULL,
  `current_percentage` decimal(5,2) NOT NULL,
  `requested_percentage` decimal(5,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `main_finance_approval` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `main_finance_approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `main_finance_approved_at` timestamp NULL DEFAULT NULL,
  `main_finance_notes` text DEFAULT NULL,
  `owner_approval` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `owner_approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `owner_approved_at` timestamp NULL DEFAULT NULL,
  `owner_notes` text DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `price_markup_requests`
--

INSERT INTO `price_markup_requests` (`id`, `branch_id`, `requested_by`, `current_percentage`, `requested_percentage`, `reason`, `status`, `main_finance_approval`, `main_finance_approved_by`, `main_finance_approved_at`, `main_finance_notes`, `owner_approval`, `owner_approved_by`, `owner_approved_at`, `owner_notes`, `activated_at`, `created_at`, `updated_at`) VALUES
(2, 31, 149, 20.00, 30.00, 'low profit', 'approved', 'approved', 161, '2026-03-29 12:11:50', NULL, 'approved', 31, '2026-03-29 13:00:36', NULL, '2026-03-29 13:00:36', '2026-03-29 11:58:01', '2026-03-29 13:00:36'),
(3, 31, 149, 30.00, 31.00, 'basta', 'approved', 'approved', 161, '2026-03-29 13:04:32', NULL, 'approved', 31, '2026-03-30 04:38:28', NULL, '2026-03-30 04:38:28', '2026-03-29 13:04:07', '2026-03-30 04:38:28'),
(4, 31, 149, 31.00, 32.00, NULL, 'approved', 'approved', 161, '2026-03-30 05:05:39', NULL, 'approved', 31, '2026-03-30 05:28:32', NULL, '2026-03-30 05:28:32', '2026-03-30 04:42:00', '2026-03-30 05:28:32'),
(5, 31, 149, 32.00, 35.00, 'Low Profit', 'approved', 'approved', 161, '2026-03-30 08:41:30', NULL, 'approved', 31, '2026-03-30 08:42:03', NULL, '2026-03-30 08:42:03', '2026-03-30 08:41:00', '2026-03-30 08:42:03');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_requests`
--

CREATE TABLE `procurement_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `logistics_user_id` bigint(20) UNSIGNED NOT NULL,
  `procurement_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finance_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('pending','budget_pending','approved','completed','cancelled','cash_in_transit','delivery_pending','pending_order_to_supplier','ongoing_delivery','awaiting_inventory_confirmation') DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receipt_uploaded_at` timestamp NULL DEFAULT NULL,
  `receipt_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `receipt_confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receipt_confirmed_at` timestamp NULL DEFAULT NULL,
  `confirmed_quantity` int(10) UNSIGNED DEFAULT NULL,
  `variance_quantity` int(11) DEFAULT NULL,
  `variance_reason` varchar(255) DEFAULT NULL,
  `variance_reported_at` timestamp NULL DEFAULT NULL,
  `delivery_proof_path` varchar(255) DEFAULT NULL,
  `budget_approved` tinyint(1) NOT NULL DEFAULT 0,
  `supplier_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `budget_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `procurement_requests`
--

INSERT INTO `procurement_requests` (`id`, `product_id`, `supplier_id`, `logistics_user_id`, `procurement_user_id`, `finance_user_id`, `quantity`, `price`, `total_amount`, `status`, `receipt_path`, `receipt_uploaded_by`, `receipt_uploaded_at`, `receipt_confirmed`, `receipt_confirmed_by`, `receipt_confirmed_at`, `confirmed_quantity`, `variance_quantity`, `variance_reason`, `variance_reported_at`, `delivery_proof_path`, `budget_approved`, `supplier_confirmed`, `budget_amount`, `created_at`, `updated_at`, `branch_id`) VALUES
(112, 151, 152, 154, 151, 149, 10, 23.00, 230.00, 'completed', '/receipts/receipt_112_1775141716.jpg', 150, '2026-04-02 14:55:16', 1, 149, '2026-04-02 14:56:00', NULL, NULL, NULL, NULL, NULL, 1, 0, 230.00, '2026-04-02 14:49:51', '2026-04-02 14:58:09', 31),
(113, 152, 152, 154, 151, 149, 10, 134.00, 1340.00, 'completed', '/receipts/receipt_113_1775141725.png', 150, '2026-04-02 14:55:25', 1, 149, '2026-04-02 14:55:57', NULL, NULL, NULL, NULL, NULL, 1, 0, 1340.00, '2026-04-02 14:49:54', '2026-04-02 14:58:07', 31),
(114, 152, NULL, 154, 150, NULL, 20, 147.40, 2948.00, 'budget_pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2026-04-03 09:22:10', '2026-04-04 14:27:35', 31),
(115, 152, 152, 154, 150, NULL, 20, 147.40, 2948.00, 'budget_pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, '2026-04-03 09:26:43', '2026-04-03 09:29:45', 31),
(116, 152, 152, 154, 151, 149, 10, 147.40, 1474.00, 'completed', '/receipts/receipt_116_1775313129.png', 150, '2026-04-04 14:32:09', 1, 149, '2026-04-04 14:32:48', 11, 1, 'Variance: 1 units', '2026-04-05 12:51:41', '/storage/delivery-proofs/delivery_proof_116_1775393501.jpg', 1, 0, 1474.00, '2026-04-03 09:30:39', '2026-04-05 12:51:41', 31),
(117, 151, 152, 154, 151, 149, 10, 25.30, 253.00, 'completed', '/receipts/receipt_117_1775375873.jpg', 150, '2026-04-05 07:57:53', 1, 149, '2026-04-05 07:58:57', NULL, NULL, NULL, NULL, NULL, 1, 0, 253.00, '2026-04-03 09:30:50', '2026-04-05 08:00:44', 31),
(118, 154, 152, 154, 151, 149, 10, 15.00, 150.00, 'completed', '/receipts/receipt_118_1775288927.jpg', 150, '2026-04-04 07:48:47', 1, 149, '2026-04-04 07:49:26', NULL, NULL, NULL, NULL, NULL, 1, 0, 150.00, '2026-04-04 07:44:06', '2026-04-04 07:51:08', 31),
(119, 154, 152, 154, 151, 149, 10, 250.00, 2500.00, 'completed', '/receipts/receipt_119_1775313561.png', 150, '2026-04-04 14:39:21', 1, 149, '2026-04-04 14:39:50', NULL, NULL, NULL, NULL, NULL, 1, 0, 2500.00, '2026-04-04 14:34:27', '2026-04-04 14:41:04', 31),
(120, 152, NULL, 154, NULL, NULL, 10, 147.40, 1474.00, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2026-04-05 07:44:11', '2026-04-05 07:44:11', 31),
(121, 156, 158, 154, 150, NULL, 10, 21.00, 210.00, 'budget_pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, NULL, '2026-04-05 08:04:51', '2026-04-05 08:07:37', 31),
(122, 158, NULL, 154, NULL, NULL, 10, 0.00, 0.00, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, '2026-04-05 08:11:35', '2026-04-05 08:11:35', 31);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `per_pack_or_individual` varchar(255) NOT NULL DEFAULT 'individual',
  `pack_quantity` decimal(10,2) DEFAULT NULL,
  `pack_unit` varchar(50) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `real_stock` int(11) NOT NULL DEFAULT 0,
  `open_pack_used` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `expires_at` datetime DEFAULT NULL,
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `sku` varchar(255) NOT NULL DEFAULT 'SKU-DEFAULT',
  `branch_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `published_by` bigint(20) UNSIGNED DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `has_been_ordered` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_kitchen_dish` tinyint(1) NOT NULL DEFAULT 0,
  `is_dish_product` tinyint(1) NOT NULL DEFAULT 0,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `logistics_request_available` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending_owner' COMMENT 'pending_logistics_main, pending_owner, approved, rejected',
  `requires_logistics` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether product requires logistics approval',
  `approved_by_logistics_main` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'User ID of logistics main branch approver',
  `approved_by_owner` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'User ID of owner approver',
  `rejection_reason` text DEFAULT NULL COMMENT 'Reason for product rejection',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'Final approval timestamp'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `dish_id`, `name`, `category`, `per_pack_or_individual`, `pack_quantity`, `pack_unit`, `slug`, `created_at`, `updated_at`, `price`, `cost_price`, `stock`, `real_stock`, `open_pack_used`, `expires_at`, `min_stock`, `sku`, `branch_id`, `published_by`, `published_at`, `is_published`, `has_been_ordered`, `is_active`, `is_kitchen_dish`, `is_dish_product`, `supplier_name`, `supplier_id`, `logistics_request_available`, `status`, `requires_logistics`, `approved_by_logistics_main`, `approved_by_owner`, `rejection_reason`, `approved_at`) VALUES
(148, NULL, 'frozen hot dog', NULL, 'individual', NULL, NULL, 'frozen-hot-dog-35-1775141355', '2026-04-02 14:49:15', '2026-04-02 14:49:54', 0.00, 0.00, 0, 0, 0.0000, NULL, 0, 'KITCHEN-DISH-35-1898', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(149, NULL, 'ketchop', NULL, 'individual', NULL, NULL, 'ketchop-35-1775141355', '2026-04-02 14:49:15', '2026-04-05 07:53:18', 0.00, 0.00, 0, 0, 0.0000, NULL, 0, 'KITCHEN-DISH-35-6411', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(150, 35, 'hot dog', NULL, 'individual', NULL, NULL, 'hot-dog', '2026-04-02 14:49:15', '2026-04-05 10:29:47', 215.06, 159.30, 20, 0, 0.0000, NULL, 0, 'HOTDOG-UTG2', 31, 31, '2026-04-02 14:49:15', 1, 0, 1, 1, 1, NULL, NULL, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(151, NULL, 'ketchop', 'Condiment', 'individual', NULL, NULL, 'ketchop', '2026-04-02 14:51:41', '2026-04-05 08:00:44', 27.83, 25.30, 20, 20, 0.0000, '2026-05-14 22:51:00', 10, 'sku-1775141501-7091', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(152, NULL, 'frozen hot dog', 'Meat', 'per_pack', 6.00, 'pcs', 'frozen-hot-dog', '2026-04-02 14:52:00', '2026-04-05 12:51:41', 162.14, 147.40, 16, 16, 0.0000, '2026-04-24 22:51:00', 10, 'sku-1775141520-2382', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(153, NULL, 'water', NULL, 'individual', NULL, NULL, 'water-25-1775288463', '2026-04-04 07:41:03', '2026-04-04 12:24:02', 0.00, 0.00, 0, 0, 0.0000, NULL, 0, 'PRODUCT-REQ-25-3907', 31, 147, '2026-04-04 12:24:02', 1, 1, 1, 0, 0, 'TO BE ASSIGNED', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(154, NULL, 'water', 'Beverage', 'per_pack', 30.00, 'pcs', 'water-1', '2026-04-04 07:45:44', '2026-04-04 14:41:04', 275.00, 250.00, 17, 17, 0.0000, '2026-05-01 22:36:00', 10, 'sku-1775288744-7168', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(155, NULL, 'Juice', NULL, 'individual', NULL, NULL, 'juice-26-1775376256', '2026-04-05 08:04:16', '2026-04-05 08:04:51', 0.00, 0.00, 0, 0, 0.0000, NULL, 0, 'PRODUCT-REQ-26-8848', 31, NULL, NULL, 1, 1, 1, 0, 0, 'TO BE ASSIGNED', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(156, NULL, 'Juice', 'Beverage', 'individual', 100.00, 'pcs', 'juice', '2026-04-05 08:06:16', '2026-04-05 08:06:16', 21.00, 21.00, 0, 0, 0.0000, '2026-06-11 16:06:00', 10, 'sku-1775376376-8246', 31, NULL, NULL, 1, 0, 1, 0, 0, 'John Stalone', 158, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(157, NULL, 'Juice', 'Beverage', 'individual', NULL, NULL, 'juice-1', '2026-04-05 08:06:55', '2026-04-05 08:06:55', 25.00, 25.00, 0, 0, 0.0000, '2026-05-14 16:06:00', 10, 'sku-1775376415-6093', 31, NULL, NULL, 1, 0, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(158, NULL, 'Chicken Frozen', NULL, 'individual', NULL, NULL, 'chicken-frozen-36-1775376658', '2026-04-05 08:10:58', '2026-04-05 08:11:35', 0.00, 0.00, 0, 0, 0.0000, NULL, 0, 'KITCHEN-DISH-36-8060', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(159, 36, 'test', NULL, 'individual', NULL, NULL, 'test', '2026-04-05 08:10:58', '2026-04-05 08:10:58', 0.00, NULL, 0, 0, 0.0000, NULL, 0, 'TEST-OTUD', 31, 31, '2026-04-05 08:10:58', 1, 0, 1, 1, 1, NULL, NULL, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `parent_comment_id`, `author`, `text`, `rating`, `ip_address`, `created_at`, `updated_at`) VALUES
(17, 150, NULL, NULL, 'Customer', 'Hello', 3, '127.0.0.1', '2026-04-05 07:46:36', '2026-04-05 07:46:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_requests`
--

CREATE TABLE `product_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` text DEFAULT NULL COMMENT 'pcs, g, kg, ml, l, pack, etc',
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `approval_status` enum('pending_approval','approved','rejected') NOT NULL DEFAULT 'pending_approval',
  `status` varchar(255) NOT NULL DEFAULT 'pending_logistics' COMMENT 'pending_logistics, pending_owner, approved, rejected',
  `approved_by_logistics` bigint(20) UNSIGNED DEFAULT NULL,
  `logistics_approval_notes` text DEFAULT NULL,
  `approved_by_owner` bigint(20) UNSIGNED DEFAULT NULL,
  `owner_approval_notes` text DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Product created after approval',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_requests`
--

INSERT INTO `product_requests` (`id`, `name`, `description`, `unit`, `requested_by`, `branch_id`, `approval_status`, `status`, `approved_by_logistics`, `logistics_approval_notes`, `approved_by_owner`, `owner_approval_notes`, `rejected_at`, `approved_by`, `approved_at`, `approval_notes`, `product_id`, `created_at`, `updated_at`) VALUES
(17, 'Adobong Manok', 'Masarap to', 'pcs', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 08:36:36', '2026-04-03 08:36:36'),
(18, 'Masarap na Manok', 'Gawa ni Jullius', 'pcs', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 08:39:30', '2026-04-03 08:39:30'),
(19, 'Manok ni Mang Julius', 'Masarap ito visaya mode', 'pcs', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 08:44:54', '2026-04-03 08:44:54'),
(20, 'Manok', 'Masarap', 'kg', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 08:48:57', '2026-04-03 08:48:57'),
(21, 'Manok', 'Masarap to', 'kg', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 08:50:47', '2026-04-03 08:50:47'),
(22, 'Manok ni Juls', 'Masarap eto', 'kg', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 09:00:13', '2026-04-03 09:00:13'),
(23, 'Chiken', 'Masarap to', 'kg', 154, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 09:02:20', '2026-04-03 09:02:20'),
(24, 'Chiken', 'Masarap to', 'kg', 154, 31, 'pending_approval', 'pending_owner', 162, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 09:32:49', '2026-04-05 08:03:44'),
(25, 'water', NULL, NULL, 154, 31, 'approved', 'approved', 162, NULL, 31, NULL, NULL, 31, '2026-04-04 07:41:03', NULL, 153, '2026-04-04 07:23:04', '2026-04-04 07:41:03'),
(26, 'Juice', NULL, NULL, 154, 31, 'approved', 'approved', 162, NULL, 31, NULL, NULL, 31, '2026-04-05 08:04:16', NULL, 155, '2026-04-05 08:02:49', '2026-04-05 08:04:16');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_request_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','sent','delivered','completed','cancelled') NOT NULL DEFAULT 'pending',
  `supplier_details` text DEFAULT NULL,
  `expected_delivery` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `requester_id` bigint(20) UNSIGNED NOT NULL,
  `pr_number` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `estimated_cost` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','hold') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
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
('FuT6P91Krap0m6fQtIdJlMchsbjy7vXq22AHJC3L', 159, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiN1pLZW1JYmR2d1lPNG9TUjRydlVGaG1lUTRJaFFaSEdXMXNBQWUwYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9tYWluLWJyYW5jaC9icmFuY2hlcyI7czo1OiJyb3V0ZSI7czoxOToibWFpbmJyYW5jaC5icmFuY2hlcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE1OTtzOjc6InVzZXJfaWQiO2k6MTU5O3M6OToidXNlcl9yb2xlIjtzOjU6IkFETUlOIjtzOjk6InVzZXJfbmFtZSI7czoxNzoiQWRtaW4gTWFpbiBCcmFuY2giO3M6MTM6InJlZGlyZWN0X3BhdGgiO3M6MTI6Ii9hZG1pbi1wYW5lbCI7fQ==', 1775400597),
('hiWBgtjpULsy4c3nBCViIxxzrGmoN1IRSGBTCXc2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-PH) WindowsPowerShell/5.1.26100.7920', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSURYSmJ3aGE1NTd5QzRDN3dkZGMxZ2hjOG1TcVR1emxYejVtdTlZSiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo5MjoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FwaS9sb2NhdGlvbnMvcHJvdmluY2VzP3JlZ2lvbj1OYXRpb25hbCUyMENhcGl0YWwlMjBSZWdpb24lMjAlMjhOQ1IlMjkiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1775399638);

-- --------------------------------------------------------

--
-- Table structure for table `settlements`
--

CREATE TABLE `settlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `processed_by` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
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
(35, 153, NULL, 'staff-documents/153/government_id.jpg', 'staff-documents/153/psa_birth_certificate.jpg', 'staff-documents/153/nbi_clearance.png', 'staff-documents/153/police_clearance.jpg', 'staff-documents/153/medical_certificate.jpg', 'staff-documents/153/drug_test_result.jpg', 'staff-documents/153/sss_id.png', 'staff-documents/153/philhealth_id.jpg', 'staff-documents/153/pagibig_mdf.png', 'staff-documents/153/tin_id.jpg', 'staff-documents/153/diploma_transcript.jpg', '2026-03-22 10:49:07', '2026-03-22 10:49:07'),
(36, 154, NULL, 'staff-documents/154/government_id.jpg', 'staff-documents/154/psa_birth_certificate.jpg', 'staff-documents/154/nbi_clearance.jpg', 'staff-documents/154/police_clearance.png', 'staff-documents/154/medical_certificate.png', 'staff-documents/154/drug_test_result.jpg', 'staff-documents/154/sss_id.png', 'staff-documents/154/philhealth_id.jpg', 'staff-documents/154/pagibig_mdf.jpg', 'staff-documents/154/tin_id.jpg', 'staff-documents/154/diploma_transcript.jpg', '2026-03-22 10:50:09', '2026-03-22 10:50:09'),
(39, 157, NULL, 'staff-documents/157/government_id.jpg', 'staff-documents/157/psa_birth_certificate.jpg', 'staff-documents/157/nbi_clearance.jpg', 'staff-documents/157/police_clearance.jpg', 'staff-documents/157/medical_certificate.jpg', 'staff-documents/157/drug_test_result.png', 'staff-documents/157/sss_id.jpg', 'staff-documents/157/philhealth_id.png', 'staff-documents/157/pagibig_mdf.png', 'staff-documents/157/tin_id.jpg', 'staff-documents/157/diploma_transcript.jpg', '2026-03-23 05:07:32', '2026-03-23 05:07:32'),
(45, 187, NULL, 'staff-documents/187/government_id.jpg', 'staff-documents/187/psa_birth_certificate.jpg', 'staff-documents/187/nbi_clearance.png', 'staff-documents/187/police_clearance.jpg', 'staff-documents/187/medical_certificate.png', 'staff-documents/187/drug_test_result.jpg', 'staff-documents/187/sss_id.jpg', 'staff-documents/187/philhealth_id.jpg', 'staff-documents/187/pagibig_mdf.jpg', 'staff-documents/187/tin_id.jpg', 'staff-documents/187/diploma_transcript.jpg', '2026-04-04 10:50:09', '2026-04-04 10:50:09');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_audit_logs`
--

CREATE TABLE `supplier_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `triggered_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `affected_records` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_records`)),
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_audit_logs`
--

INSERT INTO `supplier_audit_logs` (`id`, `supplier_id`, `action`, `description`, `triggered_by_user_id`, `old_values`, `new_values`, `affected_records`, `severity`, `ip_address`, `user_agent`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 152, 'delivery_variance_reported', 'Delivery variance reported for procurement #116', 151, NULL, NULL, NULL, 'warning', NULL, NULL, '{\"procurement_request_id\":116,\"expected_quantity\":10,\"actual_quantity\":11,\"variance\":1}', '2026-04-05 12:51:41', '2026-04-05 12:51:41');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_orders`
--

CREATE TABLE `supplier_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `procurement_request_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `is_broadcast` tinyint(1) NOT NULL DEFAULT 0,
  `fulfilled_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_orders`
--

INSERT INTO `supplier_orders` (`id`, `procurement_request_id`, `product_id`, `supplier_id`, `quantity`, `price`, `status`, `is_broadcast`, `fulfilled_at`, `branch_id`, `created_at`, `updated_at`) VALUES
(130, 113, 152, 152, 10, NULL, 'fulfilled', 0, '2026-04-02 14:58:07', 31, '2026-04-02 14:50:33', '2026-04-02 14:58:07'),
(131, 113, 148, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-02 14:50:33', '2026-04-02 14:50:33'),
(132, 112, 151, 152, 10, NULL, 'fulfilled', 0, '2026-04-02 14:58:09', 31, '2026-04-02 14:50:37', '2026-04-02 14:58:09'),
(133, 112, 149, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-02 14:50:37', '2026-04-02 14:50:37'),
(134, 118, 154, 152, 10, NULL, 'fulfilled', 0, '2026-04-04 07:51:08', 31, '2026-04-04 07:44:32', '2026-04-04 07:51:08'),
(135, 118, 153, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-04 07:44:32', '2026-04-04 07:44:32'),
(136, 116, 152, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 12:51:41', 31, '2026-04-04 14:30:23', '2026-04-05 12:51:41'),
(137, 119, 154, 152, 10, NULL, 'fulfilled', 0, '2026-04-04 14:41:04', 31, '2026-04-04 14:34:55', '2026-04-04 14:41:04'),
(138, 119, 153, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-04 14:34:55', '2026-04-04 14:34:55'),
(139, 117, 151, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 08:00:44', 31, '2026-04-05 07:56:12', '2026-04-05 08:00:44'),
(140, 121, 157, 152, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 08:05:16', '2026-04-05 08:06:55'),
(141, 121, 156, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 08:05:16', '2026-04-05 08:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'STAFF',
  `department` enum('HR','FINANCE','INVENTORY','LOGISTICS','CASHIER','KITCHEN','PROCUREMENT') DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
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

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `name`, `password`, `email_verified_at`, `role`, `department`, `permissions`, `branch_id`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`, `is_active`, `address`, `avatar_url`, `phone_number`) VALUES
(28, 'superadmin@example.com', 'superadmin', 'Super Administrators', NULL, '$2y$12$f67akYY/xm/H9KJoqytmXeblCxSgZ786slMmHkmqzlMozGUn3Ew7G', '2026-03-05 15:03:55', 'SUPER_ADMIN', NULL, NULL, NULL, NULL, '2026-03-05 15:03:55', '2026-03-05 16:01:06', NULL, 0, 1, NULL, '/storage/avatars/avatar_28_1775232518.jpg', NULL),
(31, 'admin@chikintayo.com', 'Parks', 'Mr.parks', NULL, '$2y$12$/jjxezfu4JAW55dvduVkVu7hpmk5CBXg2GWKtlT17A8jEMfJFpY8y', NULL, 'OWNER', NULL, NULL, NULL, NULL, '2026-03-07 04:30:58', '2026-03-07 04:30:58', NULL, 0, 1, NULL, NULL, 'admin'),
(147, 'biteyag645@onbap.com', 'admin_br743957', 'Admin - Dasma Branch', NULL, '$2y$12$8RJHYsL5dHsXJc9.XiEUSuHL8F2DAPRGug1ED5uYU6tZylZwpcjGa', '2026-03-22 11:35:00', 'ADMIN', NULL, NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 11:35:00', NULL, 0, 1, NULL, NULL, NULL),
(148, 'yawilog234@lxbeta.com', 'hr_br743957', 'HR Manager - Dasma Branch', NULL, '$2y$12$1AUNIAaO6IsU.NgPHyVqfuZmrI7EbeUTnHrku.w9douNkI7STbjfW', '2026-03-22 10:21:24', 'MANAGER', 'HR', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:21:24', NULL, 0, 1, NULL, NULL, NULL),
(149, 'jiveda9771@lxbeta.com', 'finance_br743957', 'Finance Manager - Dasma Branch', NULL, '$2y$12$9tGgivqfWOXFzZjHh./TNeQnKFXsrvyMHitvfpjtnRH9ctUwYKA5S', '2026-03-22 10:27:01', 'MANAGER', 'FINANCE', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:27:01', NULL, 0, 1, NULL, NULL, NULL),
(150, 'yenater279@onbap.com', 'procurement_br743957', 'Procurement Manager - Dasma Branch', NULL, '$2y$12$kF..QIb.DvV18ylucsDIHuPPnLwxXpZe4R5/VcwiI5CCZ2XpkCYx.', '2026-03-22 10:33:29', 'MANAGER', 'PROCUREMENT', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:33:29', NULL, 0, 1, NULL, NULL, NULL),
(151, 'rogogaj619@onbap.com', 'logistics_br743957', 'Logistics Manager - Dasma Branch', NULL, '$2y$12$OGJ.SPNyGXorcgKCa/EAaOmnaeqm4sLmZtx6dpGJiijay1uXA6LXq', '2026-03-22 11:37:41', 'MANAGER', 'LOGISTICS', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 11:37:41', NULL, 0, 1, NULL, NULL, NULL),
(152, 'jadol43025@onbap.com', 'Umberto', 'Umberto Batumbakal', NULL, '$2y$12$eym3kh1XoBRy0Ibjr3qrR.fLvvYUL9bimjdYe.f867drHAYjMURWa', NULL, 'SUPPLIER', NULL, NULL, 31, NULL, '2026-03-22 10:37:58', '2026-03-22 10:38:57', NULL, 0, 1, NULL, NULL, ''),
(153, 'mecepey855@lxbeta.com', 'janne', 'Janne De Guzman', NULL, '$2y$12$1SY4y/G62PeI69w/RGa3bu13s/bCYLFpBPEiT8kZD7K.Ebg3UV/ly', '2026-03-22 10:58:41', 'STAFF', 'CASHIER', NULL, 31, NULL, '2026-03-22 10:49:07', '2026-03-22 10:58:41', NULL, 0, 1, '213', NULL, '09156818822'),
(154, 'lalaher611@lxbeta.com', 'vince', 'Vince Hannibal Bido', NULL, '$2y$12$.9o2LKOIrQQWcrtHTKCtKOxdGGgLVs41Xr.eaY9OqHDe/iNtJwnUi', '2026-03-22 10:55:12', 'STAFF', 'INVENTORY', NULL, 31, NULL, '2026-03-22 10:50:09', '2026-03-22 10:55:12', NULL, 0, 1, '213', NULL, '09156818811'),
(157, 'matika8515@onbap.com', 'charles', 'christian Umbal', NULL, '$2y$12$DUh2zXlesutpPfCM6bBeHuKfbmkGWR083ZLYQ9Ul4RT9QV/HB.XMC', '2026-03-23 05:10:23', 'STAFF', 'KITCHEN', NULL, 31, NULL, '2026-03-23 05:07:32', '2026-03-23 05:10:23', NULL, 0, 1, '123', NULL, '09156818831'),
(158, 'xiralih141@lxbeta.com', 'John', 'John Stalone', NULL, '$2y$12$LOUp7zlefTxO05AmE6OCAuQtq3vFSVkgFjMUSa2LAyD57UwtF85uC', '2026-03-24 08:58:36', 'SUPPLIER', NULL, NULL, 31, NULL, '2026-03-24 08:57:40', '2026-03-24 08:58:36', NULL, 0, 1, NULL, NULL, ''),
(159, 'xecof21486@fun4k.com', 'admin_main_branch', 'Admin Main Branch', NULL, '$2y$12$0mEgYuYslnBkJs79fi7H0OjiH3uTrkDbe2Kvas21ltIJ9BExNMm0C', '2026-03-25 07:07:57', 'ADMIN', NULL, NULL, 32, NULL, '2026-03-25 06:56:11', '2026-03-25 07:07:57', NULL, 0, 1, NULL, NULL, NULL),
(160, 'radalol730@fun4k.com', 'hr_main_branch', 'HR Main Branch', NULL, '$2y$12$7XE7ggeRv7J3xUcTOgf6qOvZEPs4A8ppDdHk8zb3VxzTggE3EUI2i', '2026-03-25 07:17:25', 'MANAGER', 'HR', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:17:25', NULL, 0, 1, NULL, NULL, NULL),
(161, 'tavej98512@fabaos.com', 'finance_main_branch', 'Finance Main Branch', NULL, '$2y$12$Hb8m1H0Iau3u0jFJ0Spr5uqsg2Fl0kk.2QbHUAKQ.iMtZ9zKuBpra', '2026-03-25 07:20:21', 'MANAGER', 'FINANCE', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:20:21', NULL, 0, 1, NULL, NULL, NULL),
(162, 'lefet30141@fun4k.com', 'logistics_main_branch', 'Logistics Main Branch', NULL, '$2y$12$5jgXzzPw90ASb/4TuQnmreNk6jVKcPGX6ELJy6C9yfDFNv67u2j6u', '2026-03-25 07:22:05', 'MANAGER', 'LOGISTICS', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:22:05', NULL, 0, 1, NULL, NULL, NULL),
(176, 'lejanis485@fun4k.com', 'Customer', NULL, NULL, '$2y$12$HaDuG9eTBOzfLI2QJBBEJunc9e6yPAlAfdTYcGQwObeikCBEUfR4C', '2026-03-30 02:13:38', 'customer', NULL, NULL, NULL, NULL, '2026-03-30 02:13:38', '2026-03-30 02:13:38', NULL, 1, 1, NULL, NULL, NULL),
(177, 'yoboko6989@fun4k.com', 'Customer_12', NULL, NULL, '$2y$12$mDEoShJoia8vzT45WUs3wOfrTISRPuFIb2UZ6lMD3CtbuDcQzul3.', '2026-03-30 07:46:53', 'customer', NULL, NULL, NULL, NULL, '2026-03-30 07:46:53', '2026-03-30 07:46:53', NULL, 1, 1, NULL, NULL, NULL),
(187, 'yopakes567@availors.com', 'JOHNSON184', 'Johnson', NULL, '$2y$12$.CtC7HZZ3jfBHWsVM6FRheKnV5HAwzSi2ExUyhU04ROwJoOH1EUlW', '2026-04-04 11:01:40', 'CUSTOM', NULL, '{\"modules\":[\"finance\",\"logistics\",\"inventory\",\"procurement\",\"kitchen\",\"hr\",\"cashier\"],\"functions\":[]}', 31, NULL, '2026-04-04 10:50:09', '2026-04-04 11:01:40', NULL, 0, 1, '213', NULL, '0915681822');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_sender_id_foreign` (`sender_id`),
  ADD KEY `announcements_target_index` (`target`),
  ADD KEY `announcements_created_at_index` (`created_at`);

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
-- Indexes for table `budget_requests`
--
ALTER TABLE `budget_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_requests_branch_id_foreign` (`branch_id`),
  ADD KEY `budget_requests_user_id_foreign` (`user_id`),
  ADD KEY `budget_requests_processed_by_foreign` (`processed_by`);

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
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dishes_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `dish_ingredients`
--
ALTER TABLE `dish_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dish_ingredients_dish_id_index` (`dish_id`),
  ADD KEY `dish_ingredients_product_id_index` (`product_id`);

--
-- Indexes for table `employee_timesheets`
--
ALTER TABLE `employee_timesheets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_branch_id_foreign` (`branch_id`),
  ADD KEY `expenses_created_by_foreign` (`created_by`);

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
-- Indexes for table `logistics_transactions`
--
ALTER TABLE `logistics_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logistics_transactions_procurement_request_id_foreign` (`procurement_request_id`),
  ADD KEY `logistics_transactions_supplier_order_id_foreign` (`supplier_order_id`),
  ADD KEY `logistics_transactions_product_id_foreign` (`product_id`),
  ADD KEY `logistics_transactions_source_branch_id_foreign` (`source_branch_id`),
  ADD KEY `logistics_transactions_destination_branch_id_foreign` (`destination_branch_id`),
  ADD KEY `logistics_transactions_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_branch_id_index` (`branch_id`),
  ADD KEY `messages_from_user_id_index` (`from_user_id`),
  ADD KEY `messages_to_user_id_index` (`to_user_id`),
  ADD KEY `messages_read_at_index` (`read_at`);

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
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_approved_by_foreign` (`approved_by`);

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
-- Indexes for table `price_audits`
--
ALTER TABLE `price_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `price_audits_product_id_index` (`product_id`),
  ADD KEY `price_audits_user_id_index` (`user_id`);

--
-- Indexes for table `price_markup_percentages`
--
ALTER TABLE `price_markup_percentages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `price_markup_percentages_set_by_foreign` (`set_by`),
  ADD KEY `price_markup_percentages_branch_id_index` (`branch_id`);

--
-- Indexes for table `price_markup_requests`
--
ALTER TABLE `price_markup_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `price_markup_requests_main_finance_approved_by_foreign` (`main_finance_approved_by`),
  ADD KEY `price_markup_requests_owner_approved_by_foreign` (`owner_approved_by`),
  ADD KEY `price_markup_requests_branch_id_index` (`branch_id`),
  ADD KEY `price_markup_requests_requested_by_index` (`requested_by`);

--
-- Indexes for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `procurement_requests_product_id_foreign` (`product_id`),
  ADD KEY `procurement_requests_logistics_user_id_foreign` (`logistics_user_id`),
  ADD KEY `procurement_requests_procurement_user_id_foreign` (`procurement_user_id`),
  ADD KEY `procurement_requests_finance_user_id_foreign` (`finance_user_id`),
  ADD KEY `procurement_requests_branch_id_foreign` (`branch_id`),
  ADD KEY `procurement_requests_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_branch_id_foreign` (`branch_id`),
  ADD KEY `1` (`supplier_id`);

--
-- Indexes for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_comments_product_id_created_at_index` (`product_id`,`created_at`),
  ADD KEY `product_comments_user_id_foreign` (`user_id`),
  ADD KEY `product_comments_parent_comment_id_foreign` (`parent_comment_id`);

--
-- Indexes for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_requests_requested_by_foreign` (`requested_by`),
  ADD KEY `product_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `product_requests_branch_id_foreign` (`branch_id`),
  ADD KEY `product_requests_product_id_foreign` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_orders_purchase_request_id_foreign` (`purchase_request_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_requests_pr_number_unique` (`pr_number`),
  ADD KEY `purchase_requests_branch_id_foreign` (`branch_id`),
  ADD KEY `purchase_requests_requester_id_foreign` (`requester_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settlements`
--
ALTER TABLE `settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settlements_branch_id_foreign` (`branch_id`),
  ADD KEY `settlements_processed_by_foreign` (`processed_by`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_documents_user_id_index` (`user_id`);

--
-- Indexes for table `supplier_audit_logs`
--
ALTER TABLE `supplier_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_audit_logs_supplier_id_index` (`supplier_id`),
  ADD KEY `supplier_audit_logs_triggered_by_user_id_index` (`triggered_by_user_id`),
  ADD KEY `supplier_audit_logs_action_index` (`action`),
  ADD KEY `supplier_audit_logs_created_at_index` (`created_at`),
  ADD KEY `supplier_audit_logs_supplier_id_created_at_index` (`supplier_id`,`created_at`);

--
-- Indexes for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_orders_supplier_id_index` (`supplier_id`),
  ADD KEY `supplier_orders_procurement_request_id_index` (`procurement_request_id`),
  ADD KEY `supplier_orders_product_id_index` (`product_id`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `budget_requests`
--
ALTER TABLE `budget_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `dish_ingredients`
--
ALTER TABLE `dish_ingredients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `employee_timesheets`
--
ALTER TABLE `employee_timesheets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logistics_transactions`
--
ALTER TABLE `logistics_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2365;

--
-- AUTO_INCREMENT for table `price_audits`
--
ALTER TABLE `price_audits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `price_markup_percentages`
--
ALTER TABLE `price_markup_percentages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `price_markup_requests`
--
ALTER TABLE `price_markup_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settlements`
--
ALTER TABLE `settlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `supplier_audit_logs`
--
ALTER TABLE `supplier_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `budget_requests`
--
ALTER TABLE `budget_requests`
  ADD CONSTRAINT `budget_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budget_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `budget_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD CONSTRAINT `customer_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logistics_transactions`
--
ALTER TABLE `logistics_transactions`
  ADD CONSTRAINT `logistics_transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_transactions_destination_branch_id_foreign` FOREIGN KEY (`destination_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_transactions_procurement_request_id_foreign` FOREIGN KEY (`procurement_request_id`) REFERENCES `procurement_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_transactions_source_branch_id_foreign` FOREIGN KEY (`source_branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logistics_transactions_supplier_order_id_foreign` FOREIGN KEY (`supplier_order_id`) REFERENCES `supplier_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_to_user_id_foreign` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `price_markup_percentages`
--
ALTER TABLE `price_markup_percentages`
  ADD CONSTRAINT `price_markup_percentages_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `price_markup_percentages_set_by_foreign` FOREIGN KEY (`set_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `price_markup_requests`
--
ALTER TABLE `price_markup_requests`
  ADD CONSTRAINT `price_markup_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `price_markup_requests_main_finance_approved_by_foreign` FOREIGN KEY (`main_finance_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `price_markup_requests_owner_approved_by_foreign` FOREIGN KEY (`owner_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `price_markup_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procurement_requests`
--
ALTER TABLE `procurement_requests`
  ADD CONSTRAINT `procurement_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `procurement_requests_finance_user_id_foreign` FOREIGN KEY (`finance_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `procurement_requests_logistics_user_id_foreign` FOREIGN KEY (`logistics_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `procurement_requests_procurement_user_id_foreign` FOREIGN KEY (`procurement_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `procurement_requests_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `procurement_requests_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD CONSTRAINT `product_comments_parent_comment_id_foreign` FOREIGN KEY (`parent_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_requests`
--
ALTER TABLE `product_requests`
  ADD CONSTRAINT `product_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_requests_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settlements`
--
ALTER TABLE `settlements`
  ADD CONSTRAINT `settlements_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `settlements_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_audit_logs`
--
ALTER TABLE `supplier_audit_logs`
  ADD CONSTRAINT `supplier_audit_logs_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_audit_logs_triggered_by_user_id_foreign` FOREIGN KEY (`triggered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
