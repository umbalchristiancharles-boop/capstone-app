-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 02:51 PM
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
(10, 149, '2026-04-06', '03:16:44', NULL, 0, 'present', NULL, '2026-04-05 19:16:44', '2026-04-05 19:16:44');

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
(146, 31, 0, '2026-04-05 18:23:33', '2026-04-05 18:23:33'),
(147, 32, 0, '2026-04-05 19:26:07', '2026-04-05 19:26:07'),
(149, 48, 0, '2026-04-15 04:50:19', '2026-04-15 04:50:19');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
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
  `default_password` varchar(255) DEFAULT NULL COMMENT 'Current default password for branch staff accounts',
  `default_password_updated_at` timestamp NULL DEFAULT NULL COMMENT 'Last time the default password was updated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `code`, `name`, `address`, `latitude`, `longitude`, `is_active`, `is_main_branch`, `approval_status`, `requested_by`, `finance_confirmed_by`, `finance_confirmed_at`, `approved_by`, `approved_at`, `rejected_at`, `budget`, `default_password`, `default_password_updated_at`, `created_at`, `updated_at`) VALUES
(31, 'BR743957', 'Dasma Branch', 'Dasma', NULL, NULL, 1, 0, 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 95808, 'BDP2026052647004F', '2026-05-26 08:23:16', '2026-03-22 10:19:21', '2026-05-26 08:23:16'),
(32, 'MAIN', 'Main Branch', 'HQ', NULL, NULL, 1, 1, 'approved', NULL, NULL, NULL, NULL, NULL, NULL, 800000, 'BDP2026052608570E', '2026-05-26 08:23:16', '2026-03-25 06:56:11', '2026-05-26 08:23:16'),
(48, 'BR426492', 'Manila Branch', NULL, 14.61391060, 120.99431992, 1, 0, 'approved', 159, 161, '2026-04-15 04:18:32', 31, '2026-04-15 04:22:52', NULL, 100000, 'BDP202605262D9C15', '2026-05-26 08:23:16', '2026-04-15 04:14:07', '2026-05-26 08:23:16');

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
(89, 31, 150, 'Procurement Request #124: Flour x10', 400.00, 'Completed', '2026-04-06', 150, '2026-04-06', '2026-04-05 18:48:14', '2026-04-05 19:18:10'),
(90, 31, 150, 'Procurement Request #127: Yang Yeom Sauce x10', 450.00, 'Completed', '2026-04-06', 150, '2026-04-06', '2026-04-05 18:48:22', '2026-04-05 19:18:07'),
(91, 31, 150, 'Procurement Request #126: Salt x10', 200.00, 'Completed', '2026-04-06', 150, '2026-04-06', '2026-04-05 18:48:27', '2026-04-05 19:18:03'),
(92, 31, 150, 'Procurement Request #125: Pepper x10', 100.00, 'Completed', '2026-04-06', 150, '2026-04-06', '2026-04-05 18:48:31', '2026-04-05 19:17:58'),
(93, 31, 150, 'Procurement Request #129: Boneless Chiken x10', 2000.00, 'Completed', '2026-04-06', 150, '2026-04-06', '2026-04-05 19:00:44', '2026-04-05 19:18:15'),
(94, 31, 150, 'Procurement Request #131: Flour x5', 250.00, 'Budget Given', '2026-05-23', 149, '2026-05-23', '2026-05-23 12:09:28', '2026-05-23 12:10:52'),
(95, 31, 150, 'Procurement Request #132: Salt x50', 500.00, 'Budget Given', '2026-05-23', 149, '2026-05-23', '2026-05-23 12:19:01', '2026-05-23 12:20:18'),
(96, 31, 150, 'Procurement Request #133: Salt x50', 500.00, 'Completed', '2026-05-23', 150, '2026-05-23', '2026-05-23 12:28:19', '2026-05-23 12:34:21'),
(97, 31, 150, 'Procurement Request #134: Yang Yeom Sauce x10', 600.00, 'Completed', '2026-05-23', 150, '2026-05-23', '2026-05-23 12:32:41', '2026-05-23 12:34:28');

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
('laravel-cache-user_token_AK9tUHsIMLIAa4Xgei4DMgWYysXYvwMtoJjsVkuUrhTcGoTh0zkSVC3druLC', 'i:240;', 1782380049),
('laravel-cache-user_token_lqdGhGtGeQGn6ud1XQRRGUA6KMAbvIEPHInhfLipqvFW3LupuxCC84w0GA6A', 'i:240;', 1782380470),
('laravel-cache-user_token_tzVhkeqrF2ijxBJhSElzMLUnV1lfPrTCZKhSgaXtDKO0pN6LKVxtN5c825tC', 'i:240;', 1783481731),
('laravel-cache-user_token_VB570zzpQuzq4p23mqQ9N60mcGEQolBFg7nrCgD7R6xNS6rsTDf5NNRzHE53', 'i:240;', 1783482581),
('laravel-cache-verification_rate_limit_xmusics77@gmail.com', 'i:1;', 1779788589);

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
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
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

INSERT INTO `customer_accounts` (`id`, `user_id`, `email`, `full_name`, `phone_number`, `address`, `city`, `province`, `postal_code`, `latitude`, `longitude`, `total_comments`, `total_ratings`, `last_activity_at`, `status`, `created_at`, `updated_at`) VALUES
(8, 240, 'xmusics77@gmail.com', 'gab', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2026-05-26 09:34:09', 'active', '2026-05-26 09:34:09', '2026-05-26 09:34:09');

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
(37, 'Yang Yeom Chiken', 157, 31, 'active', 'approved', 31, '2026-04-05 18:35:11', 'Kfine', '2026-04-05 18:32:04', '2026-04-05 18:35:11');

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
(53, 37, 160, 'Boneless Chiken', 'pcs', 1.0000, '2026-04-05 18:32:04', '2026-04-05 18:35:11'),
(54, 37, 161, 'Flour', 'g', 300.0000, '2026-04-05 18:32:05', '2026-04-05 18:35:11'),
(55, 37, 162, 'Yang Yeom Sauce', 'pcs', 1.0000, '2026-04-05 18:32:05', '2026-04-05 18:35:11'),
(56, 37, 163, 'Salt', 'pcs', 0.0000, '2026-04-05 18:32:05', '2026-04-05 18:35:11'),
(57, 37, 164, 'Pepper', 'pcs', 0.0000, '2026-04-05 18:32:05', '2026-04-05 18:35:11');

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
(3, 'default', '{\"uuid\":\"f0adbe88-2b66-4705-8845-18604470d524\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:121;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775376457,\"delay\":null}', 0, NULL, 1775376457, 1775376457),
(4, 'default', '{\"uuid\":\"bcf77ff8-c33d-4be4-9ad5-7213422cdf5f\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:124;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775414894,\"delay\":null}', 0, NULL, 1775414894, 1775414894),
(5, 'default', '{\"uuid\":\"f93935f7-570e-4208-9362-7e2cae177ff0\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:127;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775414902,\"delay\":null}', 0, NULL, 1775414902, 1775414902),
(6, 'default', '{\"uuid\":\"47c2bda6-0d19-4fbd-b637-d6a407c17c34\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:126;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775414907,\"delay\":null}', 0, NULL, 1775414907, 1775414907),
(7, 'default', '{\"uuid\":\"0cc308eb-a6b1-4ca3-86d4-48fcefe6bbc8\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:125;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775414911,\"delay\":null}', 0, NULL, 1775414911, 1775414911),
(8, 'default', '{\"uuid\":\"8a66b805-5220-49f8-a8ee-4e267c246192\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:129;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"product\\\";i:1;s:13:\\\"logisticsUser\\\";i:2;s:15:\\\"procurementUser\\\";i:3;s:11:\\\"financeUser\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775415644,\"delay\":null}', 0, NULL, 1775415644, 1775415644),
(9, 'default', '{\"uuid\":\"04b03d84-9e26-41fa-9e15-2a3ce62d83bf\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:129;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775416440,\"delay\":null}', 0, NULL, 1775416440, 1775416440),
(10, 'default', '{\"uuid\":\"ccbcd2db-44b0-496b-9033-8b8c0b6c1ae0\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:124;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775416459,\"delay\":null}', 0, NULL, 1775416459, 1775416459),
(11, 'default', '{\"uuid\":\"87387a07-1d09-4509-8ea4-be4beeb7a044\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:127;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775416462,\"delay\":null}', 0, NULL, 1775416462, 1775416462),
(12, 'default', '{\"uuid\":\"e2053500-9cec-4284-9c80-165d7dbf78e1\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:126;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775416464,\"delay\":null}', 0, NULL, 1775416464, 1775416464),
(13, 'default', '{\"uuid\":\"5184a79f-fdf6-4b60-8d97-e2cb1cde918e\",\"displayName\":\"App\\\\Events\\\\ProcurementRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:36:\\\"App\\\\Events\\\\ProcurementRequestUpdated\\\":1:{s:18:\\\"procurementRequest\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:29:\\\"App\\\\Models\\\\ProcurementRequest\\\";s:2:\\\"id\\\";i:125;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1775416467,\"delay\":null}', 0, NULL, 1775416467, 1775416467);

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
(6, 124, NULL, 161, 31, 31, 31, 'procurement', 'pending', 10, 10.00, 'unit', 'PR-124', 'Flour', NULL, 154, NULL, NULL, '2026-04-05 18:36:51', NULL, NULL, NULL, NULL, NULL, NULL, 10, 10, NULL, NULL, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_124_1775416848.png', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 18:36:51', '2026-04-05 19:20:48'),
(7, 125, NULL, 164, 31, 31, 31, 'procurement', 'pending', 10, 9.00, 'unit', 'PR-125', 'Pepper', NULL, 154, NULL, NULL, '2026-04-05 18:36:55', NULL, NULL, NULL, NULL, NULL, NULL, 10, 9, 'Variance: -1 units', NULL, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_125_1775416839.png', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 18:36:55', '2026-04-05 19:20:39'),
(8, 126, NULL, 163, 31, 31, 31, 'procurement', 'pending', 10, 10.00, 'unit', 'PR-126', 'Salt', NULL, 154, NULL, NULL, '2026-04-05 18:37:07', NULL, NULL, NULL, NULL, NULL, NULL, 10, 10, NULL, NULL, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_126_1775416825.png', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 18:37:07', '2026-04-05 19:20:25'),
(9, 127, NULL, 162, 31, 31, 31, 'procurement', 'pending', 10, 10.00, 'unit', 'PR-127', 'Yang Yeom Sauce', NULL, 154, NULL, NULL, '2026-04-05 18:37:12', NULL, NULL, NULL, NULL, NULL, NULL, 10, 10, NULL, NULL, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_127_1775416817.png', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 18:37:12', '2026-04-05 19:20:17'),
(11, 129, NULL, 160, 31, 31, 31, 'procurement', 'pending', 10, 12.00, 'unit', 'PR-129', 'Boneless Chiken', NULL, 154, NULL, NULL, '2026-04-05 18:54:15', NULL, NULL, NULL, NULL, NULL, NULL, 10, 12, 'Variance: 2 units', NULL, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_129_1775416799.png', NULL, NULL, NULL, 0, NULL, NULL, '2026-04-05 18:54:15', '2026-04-05 19:19:59'),
(12, 130, NULL, 169, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-130', 'Flour', NULL, 154, NULL, NULL, '2026-05-23 09:32:48', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-05-23 09:32:48', '2026-05-23 09:32:48'),
(13, 135, NULL, 174, 31, 31, 31, 'procurement', 'pending', 10, NULL, 'unit', 'PR-135', 'Water', NULL, 154, NULL, NULL, '2026-06-08 05:07:51', NULL, NULL, NULL, NULL, NULL, NULL, 10, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-06-08 05:07:51', '2026-06-08 05:07:51');

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
(112, '2026_04_05_120000_add_delivery_confirmation_fields_to_procurement_requests_table', 67),
(113, '2026_04_14_000000_add_geolocation_to_tables', 68),
(114, '2026_04_15_000000_add_requires_setup_to_users_table', 69),
(115, '2026_04_15_000001_add_required_setup_type_to_users_table', 70),
(116, '2026_04_15_000002_add_default_password_to_branches_table', 71),
(117, '2026_05_23_000000_add_is_manual_to_procurement_requests', 72),
(118, '2026_05_26_000001_add_flags_to_product_comments_table', 73),
(119, '2026_05_26_000002_create_product_comment_flags_table', 73),
(120, '2026_06_01_000001_add_is_hidden_to_product_comments_table', 74),
(121, '2026_06_08_000001_add_date_made_to_products_table', 75);

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
(343, 'App\\Models\\User', 28, 'auth-token', '0ff064d36d11e0d0d2d194de99193d07b7de405f5def56927b5ce924cb957a80', '[\"*\"]', NULL, NULL, '2026-03-07 07:16:34', '2026-03-07 07:16:34'),
(347, 'App\\Models\\User', 28, 'auth-token', '714e0b4147b3e080b62e0a6d57f968805e4959bbed7df735b9c84ebce95506c6', '[\"*\"]', NULL, NULL, '2026-03-07 07:54:34', '2026-03-07 07:54:34'),
(348, 'App\\Models\\User', 28, 'auth-token', 'cc209504b9c3782df058834863676614e489891a7a8701ea78b526422d4a7968', '[\"*\"]', NULL, NULL, '2026-03-07 07:54:45', '2026-03-07 07:54:45'),
(350, 'App\\Models\\User', 28, 'auth-token', 'fbadf33425db92cfc05d04bc1c48e0edbd9d5cd572bafbf1ec5de398e5f68529', '[\"*\"]', NULL, NULL, '2026-03-07 08:02:52', '2026-03-07 08:02:52'),
(352, 'App\\Models\\User', 28, 'auth-token', '57c8c447bde7624644bc3067cab123eee34d1f53c28a9ab95dceaef766188c3f', '[\"*\"]', NULL, NULL, '2026-03-09 04:58:16', '2026-03-09 04:58:16'),
(353, 'App\\Models\\User', 28, 'auth-token', '8806446eec5b94a0c9350b7f0ffc618cd26fe752f691d59860f7dc9e73b881a7', '[\"*\"]', NULL, NULL, '2026-03-09 05:57:48', '2026-03-09 05:57:48'),
(354, 'App\\Models\\User', 42, 'auth-token', 'e3c69e8a6faf1518a6886091e10c702a9b358c6cf103fe9743be368db7568051', '[\"*\"]', NULL, NULL, '2026-03-09 06:02:45', '2026-03-09 06:02:45'),
(355, 'App\\Models\\User', 42, 'auth-token', 'd19f183bd6cf546eafa2c7e1e00bd7a829180a2f348eed098592ac0029351554', '[\"*\"]', NULL, NULL, '2026-03-09 06:05:05', '2026-03-09 06:05:05'),
(356, 'App\\Models\\User', 28, 'auth-token', '656cc1c707fe277394b013c671f54f9dd0ce69444278a083054ca77a637bb403', '[\"*\"]', NULL, NULL, '2026-03-09 07:04:16', '2026-03-09 07:04:16'),
(358, 'App\\Models\\User', 28, 'auth-token', '4e7ad5801e059085f2d42386a219d714ef14f6daae8118134720212aa80efc8a', '[\"*\"]', NULL, NULL, '2026-03-09 07:14:17', '2026-03-09 07:14:17'),
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
(463, 'App\\Models\\User', 90, 'auth-token', '5f63bac7924a20e3f008709cbf27c0d986f3d7b0d72fe3f57454c4c8e062f74e', '[\"*\"]', NULL, NULL, '2026-03-13 03:52:03', '2026-03-13 03:52:03'),
(464, 'App\\Models\\User', 90, 'auth-token', '138188ba572eb9bf52f50f0e3e7d13d8978961956afbad7457b231f420fbe3ec', '[\"*\"]', NULL, NULL, '2026-03-13 03:53:16', '2026-03-13 03:53:16'),
(466, 'App\\Models\\User', 91, 'auth-token', 'f9d97ac8dc5ee432e2abfb92b93ef9dc01b4e8f30db2663dd27b8c8b4d92e971', '[\"*\"]', NULL, NULL, '2026-03-13 03:54:34', '2026-03-13 03:54:34'),
(468, 'App\\Models\\User', 91, 'auth-token', '09fc43441f9b30594e80e0df9df00ece1a66d07a2f9616b10871758517adc1a2', '[\"*\"]', NULL, NULL, '2026-03-13 03:56:42', '2026-03-13 03:56:42'),
(469, 'App\\Models\\User', 91, 'auth-token', '7206b0e5ad5ea45551a5d123c4b3f54a384dae201b2cd25ce1b8ae8ef8d5945c', '[\"*\"]', NULL, NULL, '2026-03-13 03:57:10', '2026-03-13 03:57:10'),
(470, 'App\\Models\\User', 92, 'auth-token', '5d8fd40f86a3b107d6a75b919f9386fe7da776882845341c5488bbe888e3fb08', '[\"*\"]', NULL, NULL, '2026-03-13 03:57:34', '2026-03-13 03:57:34'),
(471, 'App\\Models\\User', 92, 'auth-token', '7ed16f7fe07cbf3fde4a55f877261ad6a29df0b529b1d446a69dec18c22885bc', '[\"*\"]', NULL, NULL, '2026-03-13 03:58:38', '2026-03-13 03:58:38'),
(472, 'App\\Models\\User', 91, 'auth-token', '70d6814f73e524037832eb291f5467af2d0c2780841d2aa3287fcdf56ab87736', '[\"*\"]', NULL, NULL, '2026-03-13 03:58:54', '2026-03-13 03:58:54'),
(473, 'App\\Models\\User', 93, 'auth-token', 'b4a9607f883741c7abf4000c172d411718240985f4111c78acc704b09306500a', '[\"*\"]', NULL, NULL, '2026-03-13 03:59:17', '2026-03-13 03:59:17'),
(474, 'App\\Models\\User', 91, 'auth-token', '4d6e143de006ba57e920ddcf2641926f9500ec69880f219a7a08ab4ea8bacb34', '[\"*\"]', NULL, NULL, '2026-03-13 04:00:43', '2026-03-13 04:00:43'),
(475, 'App\\Models\\User', 90, 'auth-token', '004d9fdb1e04657f8d96deba9a53a794cf9ef7c0e42dba5133663124a67deddd', '[\"*\"]', NULL, NULL, '2026-03-13 04:01:05', '2026-03-13 04:01:05'),
(477, 'App\\Models\\User', 90, 'auth-token', 'd945739cada7e23827b491fc76314b01b984cefa4f6f8e7ebf63fb1c06fea2ab', '[\"*\"]', NULL, NULL, '2026-03-13 04:15:39', '2026-03-13 04:15:39'),
(479, 'App\\Models\\User', 93, 'auth-token', '66fbc5fb05efff388a1b0903b53e8c1e54b4ba2af59348857432e76683331b00', '[\"*\"]', NULL, NULL, '2026-03-13 04:16:25', '2026-03-13 04:16:25'),
(480, 'App\\Models\\User', 90, 'auth-token', '7078c45fa759f26e35d2dbdd2d07b55171231ac567714323b4d2dbefc0b8fae9', '[\"*\"]', NULL, NULL, '2026-03-13 04:16:46', '2026-03-13 04:16:46'),
(481, 'App\\Models\\User', 93, 'auth-token', '94683806f6ba63a36972443f0393e014ca95723c458441dac7cdc67814200d07', '[\"*\"]', NULL, NULL, '2026-03-13 04:17:35', '2026-03-13 04:17:35'),
(482, 'App\\Models\\User', 93, 'auth-token', 'beef2b050bb7355ac6a86fe6e9202552f87850e4122e9588233a9dc6255306ed', '[\"*\"]', NULL, NULL, '2026-03-13 04:17:56', '2026-03-13 04:17:56'),
(484, 'App\\Models\\User', 93, 'auth-token', '012afc59b2da9accdc51bd2de086b7a49ca1cb5469e1829b931fa8d005157fc4', '[\"*\"]', NULL, NULL, '2026-03-13 04:18:25', '2026-03-13 04:18:25'),
(485, 'App\\Models\\User', 91, 'auth-token', '4e515832da9064cb053f38c88b929996f9697a0db20bf8fe119dcaaac10fd168', '[\"*\"]', NULL, NULL, '2026-03-13 04:18:43', '2026-03-13 04:18:43'),
(486, 'App\\Models\\User', 98, 'auth-token', '560dc96de4038ab1c9d3469dfcea5329896a205dab9fe371f80beaf3f02b5f8b', '[\"*\"]', NULL, NULL, '2026-03-13 04:21:04', '2026-03-13 04:21:04'),
(487, 'App\\Models\\User', 98, 'auth-token', 'c926687483868a3b786f683112783804ec5cf1f015751a38276439e3e53846b7', '[\"*\"]', NULL, NULL, '2026-03-13 04:21:49', '2026-03-13 04:21:49'),
(488, 'App\\Models\\User', 90, 'auth-token', 'ea177706288a10b9bddc2c35dd34b455e13363c5e03c2bc62d54767dd8c48f9a', '[\"*\"]', NULL, NULL, '2026-03-13 04:22:06', '2026-03-13 04:22:06'),
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
(518, 'App\\Models\\User', 90, 'auth-token', '20fef40e4d6f3d61bf44667670613ad18aa2842f730ec08f42d2540749252b82', '[\"*\"]', NULL, NULL, '2026-03-13 13:42:58', '2026-03-13 13:42:58'),
(520, 'App\\Models\\User', 28, 'auth-token', '281180f7ffe87f3f237e46b9b024ff4f2548696d58f561a2dee9b4a0c792ec3c', '[\"*\"]', NULL, NULL, '2026-03-13 13:45:15', '2026-03-13 13:45:15'),
(524, 'App\\Models\\User', 28, 'auth-token', 'bab799bbdcc814e19b8691aea8419ca10efabea9b64a274b81b84aa67d52e7a4', '[\"*\"]', NULL, NULL, '2026-03-13 14:04:58', '2026-03-13 14:04:58'),
(525, 'App\\Models\\User', 100, 'auth-token', '5a4d75b5d1d60629632fb7cc0a1a799e26bd666a232e6b675ce6c590afd54e38', '[\"*\"]', NULL, NULL, '2026-03-13 14:05:56', '2026-03-13 14:05:56'),
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
(551, 'App\\Models\\User', 92, 'auth-token', '2d8592b4c29a6995ccd336ebcbbcc3916111e176c3af321db948d93b7911bec5', '[\"*\"]', NULL, NULL, '2026-03-13 15:04:31', '2026-03-13 15:04:31'),
(552, 'App\\Models\\User', 90, 'auth-token', '2ae0f8e616555446a10767776fe9204d000af931c25dfe5a85f4ce2716c15d1b', '[\"*\"]', NULL, NULL, '2026-03-13 15:06:21', '2026-03-13 15:06:21'),
(553, 'App\\Models\\User', 90, 'auth-token', '39ce005f2634327769339dda1c37083c99f9bfac33190e2c64b03e1d12ae18d2', '[\"*\"]', NULL, NULL, '2026-03-13 15:09:22', '2026-03-13 15:09:22'),
(554, 'App\\Models\\User', 28, 'auth-token', 'affe17bae5816d9715721d679ef06d3cd9ea8e9d78ab296b4b17ffc5e0ea023e', '[\"*\"]', NULL, NULL, '2026-03-13 15:12:41', '2026-03-13 15:12:41'),
(555, 'App\\Models\\User', 28, 'auth-token', 'b7ba97534208fd28bef3d1960c1260c3ce13a71dea88e030a993320da157cb74', '[\"*\"]', NULL, NULL, '2026-03-14 03:59:19', '2026-03-14 03:59:19'),
(557, 'App\\Models\\User', 91, 'auth-token', 'f6343d910ab48e7abb3c990e65d736cd373026e9f2179c2772ed4c7577f5f9cf', '[\"*\"]', NULL, NULL, '2026-03-14 04:15:07', '2026-03-14 04:15:07'),
(558, 'App\\Models\\User', 92, 'auth-token', '14a459a73444d38f6d5a28d892b1ccf18969f6050af00efd90368162d0babd4f', '[\"*\"]', NULL, NULL, '2026-03-14 04:16:13', '2026-03-14 04:16:13'),
(560, 'App\\Models\\User', 126, 'auth-token', '30faa75e3f102d3e9a1b82080c7d42074ba051dd69eeb8f315508fe9aa24f78f', '[\"*\"]', NULL, NULL, '2026-03-14 04:38:21', '2026-03-14 04:38:21'),
(561, 'App\\Models\\User', 126, 'auth-token', '16e6f8930f67ca3310cfb04df354a063a6a18f78b8680d7f2dfff047844fcb84', '[\"*\"]', NULL, NULL, '2026-03-14 04:40:04', '2026-03-14 04:40:04'),
(562, 'App\\Models\\User', 126, 'auth-token', '5e3210810957de8be989c0e95076171d7789b9ee977edb4da183ee2e88941837', '[\"*\"]', NULL, NULL, '2026-03-14 04:48:22', '2026-03-14 04:48:22'),
(563, 'App\\Models\\User', 126, 'auth-token', '43ca8690715c7d575794c4fd5023661636c58d7df7ab0107168984a9a8f7b017', '[\"*\"]', NULL, NULL, '2026-03-14 04:48:53', '2026-03-14 04:48:53'),
(564, 'App\\Models\\User', 126, 'auth-token', '9495e9918f957a844a93d6e247d84f799276e1b9b512eb35502e99ee7b47b3a3', '[\"*\"]', NULL, NULL, '2026-03-14 04:57:40', '2026-03-14 04:57:40'),
(565, 'App\\Models\\User', 28, 'auth-token', '41e7a4c5298c24a2b06d6d7d15aaa65e3fe673d3b477551cbb45a30cff8bf6b2', '[\"*\"]', NULL, NULL, '2026-03-14 04:58:20', '2026-03-14 04:58:20'),
(567, 'App\\Models\\User', 127, 'auth-token', '0c8696f670f3aaf8c835906849b946e080f91a678d5b0846301da262133aa81a', '[\"*\"]', NULL, NULL, '2026-03-14 05:11:27', '2026-03-14 05:11:27'),
(568, 'App\\Models\\User', 127, 'auth-token', '0587757ae64425ccc7097cf92de1907b67a58bc7752a1a187c5bdc049fe52894', '[\"*\"]', NULL, NULL, '2026-03-14 05:12:42', '2026-03-14 05:12:42'),
(569, 'App\\Models\\User', 28, 'auth-token', 'a05f8b285607ef09119ace16764872848bca8586ce13c4d094f6a53da38a47e5', '[\"*\"]', NULL, NULL, '2026-03-14 05:14:04', '2026-03-14 05:14:04'),
(570, 'App\\Models\\User', 126, 'auth-token', 'dcc93fd359307a02c583fa8344f14377fd9ac50e137e062b6bbf0a92792f33e5', '[\"*\"]', NULL, NULL, '2026-03-14 05:25:15', '2026-03-14 05:25:15'),
(572, 'App\\Models\\User', 124, 'auth-token', '6804aeae8d329635f8130317fe9a07b21f2d31372db572c104bce1cb4a6fdc71', '[\"*\"]', NULL, NULL, '2026-03-14 06:51:41', '2026-03-14 06:51:41'),
(574, 'App\\Models\\User', 123, 'auth-token', '0e6be25ff6031cddf5e9951ddddfae5459054363c5d2ed15fb4f2789b1231822', '[\"*\"]', NULL, NULL, '2026-03-14 06:53:18', '2026-03-14 06:53:18'),
(575, 'App\\Models\\User', 123, 'auth-token', '1aac79a1a36b0019628cb578e7f9a91db3a0c5452d0077a7c6a4dfad378723fe', '[\"*\"]', NULL, NULL, '2026-03-14 06:54:23', '2026-03-14 06:54:23'),
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
(594, 'App\\Models\\User', 28, 'auth-token', '650c16017096acc9a9925c9176a493a7c018c6ec65fe5652e3c954c7aa053dfe', '[\"*\"]', NULL, NULL, '2026-03-14 15:37:46', '2026-03-14 15:37:46');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(595, 'App\\Models\\User', 28, 'auth-token', 'b2fb89d698f43f5804e629002e83795f96c3508fc250cf6047e4fc6b162850a9', '[\"*\"]', NULL, NULL, '2026-03-14 15:48:55', '2026-03-14 15:48:55'),
(596, 'App\\Models\\User', 28, 'auth-token', '36f486b45300fb445b98aacd41d4b7a8c922043669d2cd8921a33397c5bd07bb', '[\"*\"]', NULL, NULL, '2026-03-15 09:19:25', '2026-03-15 09:19:25'),
(597, 'App\\Models\\User', 28, 'auth-token', '46741d95b9a158294b875b3b395ee418f43b24a7244745807374ecebad0f26c7', '[\"*\"]', NULL, NULL, '2026-03-15 09:31:36', '2026-03-15 09:31:36'),
(599, 'App\\Models\\User', 126, 'auth-token', '0b648f4c97da572ba087c4d150a18fec81cdac6ef437dab3c41a33cc0bf2ccc5', '[\"*\"]', NULL, NULL, '2026-03-15 09:56:08', '2026-03-15 09:56:08'),
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
(824, 'App\\Models\\User', 126, 'auth-token', '32b6055ecb577be06eb2b5ff9f0babd038d067da80a55695659447ea84062214', '[\"*\"]', NULL, NULL, '2026-03-19 12:25:01', '2026-03-19 12:25:01'),
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
(862, 'App\\Models\\User', 124, 'auth-token', '490793d5694a7c3b5c565a207f313ca0ffb4a1142c786c79bce21f55aa464f03', '[\"*\"]', NULL, NULL, '2026-03-20 03:56:29', '2026-03-20 03:56:29'),
(864, 'App\\Models\\User', 28, 'auth-token', 'cfb67c6f8c6dceafca12bcae338c5406acc63785734441c816895cbcacc6f9ed', '[\"*\"]', NULL, NULL, '2026-03-20 03:57:37', '2026-03-20 03:57:37'),
(865, 'App\\Models\\User', 126, 'auth-token', '98b886ba4bfa27d7b439e72f3e7b7df3240c9dd4536099f4df443f45cce13ea1', '[\"*\"]', NULL, NULL, '2026-03-20 04:11:18', '2026-03-20 04:11:18'),
(866, 'App\\Models\\User', 28, 'auth-token', '7d3307268a2181ad577c63d38221048be384afcb41cd9e2e38649c75abfe9098', '[\"*\"]', NULL, NULL, '2026-03-20 04:11:38', '2026-03-20 04:11:38'),
(867, 'App\\Models\\User', 126, 'auth-token', '000cd5d12ba9263a55aa3e7190b6f0a0c7d7b35f6463be20d815526d90b6e627', '[\"*\"]', NULL, NULL, '2026-03-20 04:25:32', '2026-03-20 04:25:32'),
(868, 'App\\Models\\User', 126, 'auth-token', 'b8065f0a928bb781f6d0493ee9d91ffa91c63c5dc3fb3255f49bbff15fa56a65', '[\"*\"]', NULL, NULL, '2026-03-20 04:26:43', '2026-03-20 04:26:43'),
(869, 'App\\Models\\User', 28, 'auth-token', 'bd393060ad59f1c7454eb6538fbc71f4660b4677b0c66f7c8a87b6cfaf7353f5', '[\"*\"]', NULL, NULL, '2026-03-20 04:27:11', '2026-03-20 04:27:11'),
(870, 'App\\Models\\User', 28, 'auth-token', '011e510d3f0d84a7d3e8c6889445fd606c6e6a7cadaee19cfb7c9ed675f69850', '[\"*\"]', NULL, NULL, '2026-03-20 04:27:44', '2026-03-20 04:27:44'),
(871, 'App\\Models\\User', 28, 'auth-token', 'f15af6adda02113560c61ae74b628a1980653208b93709eaf0cef4dee3081d23', '[\"*\"]', NULL, NULL, '2026-03-20 04:49:48', '2026-03-20 04:49:48'),
(872, 'App\\Models\\User', 28, 'auth-token', 'f6d54ced553b17ede1170db5212ac327ccf14a3451fd7a2bef33dba22a843c56', '[\"*\"]', NULL, NULL, '2026-03-20 04:58:45', '2026-03-20 04:58:45'),
(873, 'App\\Models\\User', 126, 'auth-token', 'cf4caf01fa23b3dad61cd9575af75296d735cf6ac470d3b216a7b6d22666b1d8', '[\"*\"]', NULL, NULL, '2026-03-20 05:15:47', '2026-03-20 05:15:47');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
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
(1010, 'App\\Models\\User', 28, 'auth-token', 'dd9f786acf29b7ab2ebfd626bba9e240e772a45b1353c9756c8c077d7baad95a', '[\"*\"]', NULL, NULL, '2026-03-22 10:03:45', '2026-03-22 10:03:45'),
(1012, 'App\\Models\\User', 143, 'auth-token', 'b7b24b79516608e1948bb6c5d38fecf48457c5fc7e75a8de0e419eaf89bfcb10', '[\"*\"]', NULL, NULL, '2026-03-22 10:09:19', '2026-03-22 10:09:19'),
(1014, 'App\\Models\\User', 142, 'auth-token', '4d517581316a60259d993ae009196b91f2724db55075e24d62c491612d438ac1', '[\"*\"]', NULL, NULL, '2026-03-22 10:13:59', '2026-03-22 10:13:59'),
(1033, 'App\\Models\\User', 153, 'auth-token', 'a8737cbd9ca266466984966f2f769d9dd8302603465ccbbe74f299e3f79c4697', '[\"*\"]', NULL, NULL, '2026-03-22 10:55:32', '2026-03-22 10:55:32'),
(1034, 'App\\Models\\User', 153, 'auth-token', '338378cc057ad72d9ab044837014ddae0ff6ddcba46fc785aeeaf051e02a0e43', '[\"*\"]', NULL, NULL, '2026-03-22 10:55:53', '2026-03-22 10:55:53'),
(1035, 'App\\Models\\User', 153, 'auth-token', '45bf0b701485c11eef0d3cf5ac068e29d4461caa28410cfd41a63e48de737423', '[\"*\"]', NULL, NULL, '2026-03-22 10:58:49', '2026-03-22 10:58:49'),
(1038, 'App\\Models\\User', 147, 'auth-token', '4cca3f5121896ed5d9ac95eb174546359d8cb849cda0c96c72186a99db6c606d', '[\"*\"]', NULL, NULL, '2026-03-22 11:33:45', '2026-03-22 11:33:45'),
(1039, 'App\\Models\\User', 147, 'auth-token', '85daf5cbcceda38cd3a3aaa856029568858038c96dab6fb060a7aa3d2df65ef1', '[\"*\"]', NULL, NULL, '2026-03-22 11:34:32', '2026-03-22 11:34:32'),
(1040, 'App\\Models\\User', 147, 'auth-token', '8612ef914c6b8e13fd6936c8b654e55ce286e285ddf0d8058cb5165d7564ba82', '[\"*\"]', NULL, NULL, '2026-03-22 11:35:07', '2026-03-22 11:35:07'),
(1052, 'App\\Models\\User', 153, 'auth-token', '493d1a5fc8a2603963e688b057b38c14980809207ef878108e251ce4ec45c265', '[\"*\"]', NULL, NULL, '2026-03-22 11:43:53', '2026-03-22 11:43:53'),
(1164, 'App\\Models\\User', 153, 'auth-token', '3aef83f3720ff6cec61e9fc53edde1aedcd6d0b70b0e309374f3bb71081a81b4', '[\"*\"]', NULL, NULL, '2026-03-24 08:35:21', '2026-03-24 08:35:21'),
(1165, 'App\\Models\\User', 153, 'auth-token', '63296b8363f4db1e53a94adb10a1ed7b70e3af45377ca6b930516acac7e197eb', '[\"*\"]', NULL, NULL, '2026-03-24 08:43:39', '2026-03-24 08:43:39'),
(1191, 'App\\Models\\User', 153, 'auth-token', '8bd3e10c29edf6ddac040defe02e5f7854c6574d417a63ced8ecc98d6b4866d5', '[\"*\"]', NULL, NULL, '2026-03-24 09:16:51', '2026-03-24 09:16:51'),
(1194, 'App\\Models\\User', 153, 'auth-token', 'd0ea2d0fbb3c395449cb84d05032187c4f236389f5a32fe72ad903671c1e5841', '[\"*\"]', NULL, NULL, '2026-03-25 06:34:09', '2026-03-25 06:34:09'),
(1196, 'App\\Models\\User', 147, 'auth-token', '0cecc38af248026a33e3b32fb6ac0698e5a583618b2ef7f8dd7b824f4ff69092', '[\"*\"]', NULL, NULL, '2026-03-25 06:35:11', '2026-03-25 06:35:11'),
(1201, 'App\\Models\\User', 160, 'auth-token', '6aec93daa5086e293ecd2746f7a753b776a2cf5790dd408133ed2fd94ff312c9', '[\"*\"]', NULL, NULL, '2026-03-25 07:16:24', '2026-03-25 07:16:24'),
(1202, 'App\\Models\\User', 160, 'auth-token', '7f89efdc67e279b82522d6058b7e0384aacb168b8e904b587e6794daf27d3a29', '[\"*\"]', NULL, NULL, '2026-03-25 07:16:49', '2026-03-25 07:16:49'),
(1203, 'App\\Models\\User', 160, 'auth-token', '4636165b0b2804971ebc702d657992d99b4ec9e4c9198c1cd334d9ebe4fdc701', '[\"*\"]', NULL, NULL, '2026-03-25 07:17:29', '2026-03-25 07:17:29'),
(1213, 'App\\Models\\User', 28, 'auth-token', '3184e75da7bfa608dc834eff5c1f69011e1a55b7f0e363d3b7e2e320a654fef6', '[\"*\"]', NULL, NULL, '2026-03-26 04:47:42', '2026-03-26 04:47:42'),
(1282, 'App\\Models\\User', 28, 'auth-token', 'd83cf0d24bc2669f7f600d78311d7b47923dfdd7f7e9af2b152e9fb637011655', '[\"*\"]', NULL, NULL, '2026-03-26 18:53:53', '2026-03-26 18:53:53'),
(1283, 'App\\Models\\User', 28, 'auth-token', '26c69331a5ae432a3a4d6856a9b4b84468f341bc35564618a588ef6e22d7f8c1', '[\"*\"]', NULL, NULL, '2026-03-26 19:11:08', '2026-03-26 19:11:08'),
(1285, 'App\\Models\\User', 28, 'auth-token', '8327a707fdb2526b119fc7e1e4565e7397b5817ecef6debcc0026db17c6f5d10', '[\"*\"]', NULL, NULL, '2026-03-26 19:14:16', '2026-03-26 19:14:16'),
(1286, 'App\\Models\\User', 28, 'auth-token', '46c6e25ceee81e3dc3c603cd6c5faaa89102244d52c0e74b24e19c475758fdf8', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:18', '2026-03-26 19:16:18'),
(1287, 'App\\Models\\User', 28, 'auth-token', 'd6a45388c48921e43c20a15776110178bcc562fbba0a142b7d9eb98cd99c86ed', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:27', '2026-03-26 19:16:27'),
(1288, 'App\\Models\\User', 28, 'auth-token', 'ff477ee3bb9499165221797d3209bf11870073ce9e83638198210816d8395855', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:35', '2026-03-26 19:16:35'),
(1289, 'App\\Models\\User', 28, 'auth-token', 'bebd77bf7ea91089949343d1023dadeacf3a5a1969e7810835b53ff490ffa393', '[\"*\"]', NULL, NULL, '2026-03-26 19:16:47', '2026-03-26 19:16:47'),
(1290, 'App\\Models\\User', 28, 'auth-token', '3c8997547b545bcbbe3ef3398043c4b5e27be810c5f63e99da010f8615c9fc7a', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:01', '2026-03-26 19:17:01'),
(1291, 'App\\Models\\User', 28, 'auth-token', 'eae332d5b54554d8562bc6b0dd3653bca657190a3c59495323e48985abb1a37e', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:13', '2026-03-26 19:17:13'),
(1292, 'App\\Models\\User', 28, 'auth-token', 'add188c9755bf60cb03467deb72348a68a31a432594913ce78565a6c62ee22cd', '[\"*\"]', NULL, NULL, '2026-03-26 19:17:25', '2026-03-26 19:17:25'),
(1293, 'App\\Models\\User', 28, 'auth-token', 'c17d7b6c2f56cc61adcc51e9d7bea79dea943374d4959b094892932ae4403ed2', '[\"*\"]', NULL, NULL, '2026-03-26 19:30:15', '2026-03-26 19:30:15'),
(1330, 'App\\Models\\User', 28, 'auth-token', 'a7da4dee85ac1bc26e3911028eeac528196fea0525a2c9b824fb09944f844d85', '[\"*\"]', NULL, NULL, '2026-03-26 21:13:16', '2026-03-26 21:13:16'),
(1341, 'App\\Models\\User', 147, 'auth-token', 'adacb950669f08bfe6d747fa91e0d243d5b747db399157cee436759114998487', '[\"*\"]', NULL, NULL, '2026-03-27 05:11:33', '2026-03-27 05:11:33'),
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
(1363, 'App\\Models\\User', 169, 'auth-token', '9ac892b4d8d8bd0e057b5301e6ae2a52bdc5de2d64d3fb6290b349450149a9bc', '[\"*\"]', NULL, NULL, '2026-03-27 09:08:42', '2026-03-27 09:08:42'),
(1364, 'App\\Models\\User', 169, 'auth-token', '290c523e03debc1d3e8552dd222d9caeb9f247dfaa685fd4691663e5051696dc', '[\"*\"]', NULL, NULL, '2026-03-27 09:09:20', '2026-03-27 09:09:20'),
(1365, 'App\\Models\\User', 169, 'auth-token', 'e13f664e418b3055b532f4b2d2f6dab6cbcd4ed50257b8d75b015c388a60bf55', '[\"*\"]', NULL, NULL, '2026-03-27 09:10:17', '2026-03-27 09:10:17'),
(1366, 'App\\Models\\User', 169, 'auth-token', '28bedc20e4a8c232d1b8fa4675976161ade85442eefe4d7aee6fc4fd3bc56b41', '[\"*\"]', NULL, NULL, '2026-03-27 09:16:49', '2026-03-27 09:16:49'),
(1367, 'App\\Models\\User', 169, 'auth-token', '80e75aecf09bb8c2c06885d49816070f9812330da72cca3cd79520e5e47e8174', '[\"*\"]', NULL, NULL, '2026-03-27 09:17:12', '2026-03-27 09:17:12'),
(1368, 'App\\Models\\User', 169, 'auth-token', '1e6567ca7806dff4409b0988cf32dea47a14046c8b1e4dbf32792f6aa1f80a32', '[\"*\"]', NULL, NULL, '2026-03-27 09:38:18', '2026-03-27 09:38:18'),
(1369, 'App\\Models\\User', 169, 'auth-token', '0ef3e9fdb0247723e9b9fd4b08f4d5a8061d4d1512b71430ca1f5aaec3a91526', '[\"*\"]', NULL, NULL, '2026-03-27 09:47:59', '2026-03-27 09:47:59'),
(1370, 'App\\Models\\User', 169, 'auth-token', '3811bebb2422ca5191a75423546f16c2fc0eb54ea3cf7447eb684198d606fbed', '[\"*\"]', NULL, NULL, '2026-03-27 09:48:30', '2026-03-27 09:48:30'),
(1372, 'App\\Models\\User', 28, 'auth-token', '9458f9fde790a00947e7a8ffdea71403163afda55d7ad75be3453995918a66ee', '[\"*\"]', NULL, NULL, '2026-03-28 03:56:36', '2026-03-28 03:56:36'),
(1373, 'App\\Models\\User', 169, 'auth-token', '2d312bbd8b235bdee492b9476755bf35f457e2345e8bbd90642e945867361f8a', '[\"*\"]', NULL, NULL, '2026-03-28 03:57:55', '2026-03-28 03:57:55'),
(1375, 'App\\Models\\User', 169, 'auth-token', '5e3633b572f58609ec4175a4c9666137bb7f7808dd2f5330013bff98ca1e6567', '[\"*\"]', NULL, NULL, '2026-03-28 04:47:23', '2026-03-28 04:47:23'),
(1376, 'App\\Models\\User', 28, 'auth-token', '2b2ebf90db403ee81509bee872f15b0e198f5a54ae2e739d5761c465806741dd', '[\"*\"]', NULL, NULL, '2026-03-28 04:48:59', '2026-03-28 04:48:59'),
(1377, 'App\\Models\\User', 169, 'auth-token', 'aeecfd7337a744c8f4e12c2222d8568a9f8f4e6d5f92be63b737ec84a4e1d64c', '[\"*\"]', NULL, NULL, '2026-03-28 04:50:29', '2026-03-28 04:50:29'),
(1378, 'App\\Models\\User', 169, 'auth-token', 'c6e5b5a3924940ba0f134fb8bc02c39ee0bbded20f0b7d51ed1e84e4908c6614', '[\"*\"]', NULL, NULL, '2026-03-28 04:52:04', '2026-03-28 04:52:04'),
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
(1395, 'App\\Models\\User', 173, 'auth-token', '2a9920f531dc644b7dd35fe25ad822a3617203683df50a08b0b8bfcfcd2efcf0', '[\"*\"]', NULL, NULL, '2026-03-28 06:48:14', '2026-03-28 06:48:14'),
(1397, 'App\\Models\\User', 173, 'auth-token', '622ac05c50f9c06b39412a3c5064414a7575469f58cdf734a02b78959c1ef2fe', '[\"*\"]', NULL, NULL, '2026-03-28 06:57:41', '2026-03-28 06:57:41'),
(1398, 'App\\Models\\User', 174, 'auth-token', 'd846828c717f53591818375b98cecc06e64b48d04f7a0b30482e2206e0f142e6', '[\"*\"]', NULL, NULL, '2026-03-28 06:59:33', '2026-03-28 06:59:33'),
(1399, 'App\\Models\\User', 174, 'auth-token', '1933856364a5222728c3910bf75230562a1fb46f6e85df55dd2627c1d8d20157', '[\"*\"]', NULL, NULL, '2026-03-28 06:59:53', '2026-03-28 06:59:53'),
(1400, 'App\\Models\\User', 173, 'auth-token', 'aef6b6f2aa6d92a8e1644488b97c36987ccb701566018906227bb177c82a672c', '[\"*\"]', NULL, NULL, '2026-03-28 07:00:45', '2026-03-28 07:00:45'),
(1401, 'App\\Models\\User', 174, 'auth-token', '3deffa66c12724e1e4ca7abe3fb7fe73bf12b2d0ea423b0e41f9df926eb29e55', '[\"*\"]', NULL, NULL, '2026-03-28 07:29:15', '2026-03-28 07:29:15'),
(1402, 'App\\Models\\User', 173, 'auth-token', '4c819d307cd09e0f63dae79ddc4f1548fceb6709cbfa497d746fe784946c46dd', '[\"*\"]', NULL, NULL, '2026-03-28 07:29:36', '2026-03-28 07:29:36'),
(1403, 'App\\Models\\User', 174, 'auth-token', '9ccea425990c2c04a5f4dde61dce330a70923d4ea3e8fafa1e8c9c22eded7ff4', '[\"*\"]', NULL, NULL, '2026-03-28 07:30:09', '2026-03-28 07:30:09'),
(1404, 'App\\Models\\User', 173, 'auth-token', 'caf9dd8b093dfdeab7b43c298fcf359b1bc6b089e59c54ecd7f867f3a2ac2c6a', '[\"*\"]', NULL, NULL, '2026-03-28 07:30:27', '2026-03-28 07:30:27'),
(1407, 'App\\Models\\User', 173, 'auth-token', 'da31c18885801d41211bf773316135851a206c40e89a66501bfb8d0d182b41e3', '[\"*\"]', NULL, NULL, '2026-03-28 07:42:05', '2026-03-28 07:42:05'),
(1412, 'App\\Models\\User', 173, 'auth-token', '17e56c8c9b7b8fa31fc7751c4fd9d57cb9871c8ebbee786930f7f54bee9782c2', '[\"*\"]', NULL, NULL, '2026-03-28 07:58:07', '2026-03-28 07:58:07'),
(1413, 'App\\Models\\User', 173, 'auth-token', 'b62f9b30238dc6f7685f6828d4243ba30384e629a60de9f4509c37b99c725d6d', '[\"*\"]', NULL, NULL, '2026-03-28 08:07:05', '2026-03-28 08:07:05'),
(1414, 'App\\Models\\User', 153, 'auth-token', '0a370e3070f6f0cb0fa58f98d3f2b5d632b987f4e62a7673c1678e722a89bf0e', '[\"*\"]', NULL, NULL, '2026-03-28 08:07:57', '2026-03-28 08:07:57'),
(1417, 'App\\Models\\User', 153, 'auth-token', '23824975c4cbc613673a9136374b4869c53670137f895bcadea706af1b748b0d', '[\"*\"]', NULL, NULL, '2026-03-28 08:16:39', '2026-03-28 08:16:39'),
(1421, 'App\\Models\\User', 153, 'auth-token', '44f5a4304c5a3a4ad2d5a571b91f0000728993afe9e859a3e81330661a29d2ae', '[\"*\"]', NULL, NULL, '2026-03-28 08:46:13', '2026-03-28 08:46:13'),
(1429, 'App\\Models\\User', 153, 'auth-token', '9e646a2cd5299fb992ba9b9600431a426585b257abd6ec989aafc3c02c953c7e', '[\"*\"]', NULL, NULL, '2026-03-28 09:10:23', '2026-03-28 09:10:23'),
(1434, 'App\\Models\\User', 173, 'auth-token', '7cf31f61a170aa1935009e064374f2edbd69733de0549a329ad1a36c146d19a2', '[\"*\"]', NULL, NULL, '2026-03-28 09:29:40', '2026-03-28 09:29:40'),
(1458, 'App\\Models\\User', 153, 'auth-token', '3a2063306717131c157b63079e1522cae66591caa14419657c49a3e3fc10fa73', '[\"*\"]', NULL, NULL, '2026-03-29 10:17:03', '2026-03-29 10:17:03'),
(1467, 'App\\Models\\User', 153, 'auth-token', 'c13289002c8bfb6b3d9434752fd48753f8d9c14f06ae4b168b640d9bf55aabdd', '[\"*\"]', NULL, NULL, '2026-03-29 10:27:33', '2026-03-29 10:27:33'),
(1476, 'App\\Models\\User', 173, 'auth-token', '7efc31c02a5081b634367c509447679515c9579b6486e6f0db7905d62acbc3da', '[\"*\"]', NULL, NULL, '2026-03-29 13:05:43', '2026-03-29 13:05:43'),
(1487, 'App\\Models\\User', 153, 'auth-token', '8b4dd310cac551a7f45f61d9c4028a0e2735fbad02b3a40d64239e476bc88c2e', '[\"*\"]', NULL, NULL, '2026-03-29 13:42:14', '2026-03-29 13:42:14'),
(1490, 'App\\Models\\User', 153, 'auth-token', '57227d76d9d38bc16959ef15d9a944b3c03873f63d2ef6ff1abfb06502301d33', '[\"*\"]', NULL, NULL, '2026-03-29 14:33:58', '2026-03-29 14:33:58'),
(1512, 'App\\Models\\User', 174, 'auth-token', '218423ee9f62739146f8fd53956a74ee45ac98caad7aff3d04a435f770cbd50b', '[\"*\"]', NULL, NULL, '2026-03-29 15:16:23', '2026-03-29 15:16:23'),
(1630, 'App\\Models\\User', 147, 'auth-token', '36e61dbd7f0d406475d8519e2dc32331910e7379c4fcdd2e741ac5bc1c9b6ce3', '[\"*\"]', NULL, NULL, '2026-03-29 19:02:17', '2026-03-29 19:02:17'),
(1631, 'App\\Models\\User', 147, 'auth-token', '66fa004543a2daf4a2e531ee88def00c525452ec07eca12681bd7b5244e46a39', '[\"*\"]', NULL, NULL, '2026-03-30 01:49:11', '2026-03-30 01:49:11'),
(1633, 'App\\Models\\User', 147, 'auth-token', 'bd88b5be1862d8949a9ec98a83e3bc6d33464bd15beabb6e4c2ca8422178c50b', '[\"*\"]', NULL, NULL, '2026-03-30 02:14:15', '2026-03-30 02:14:15'),
(1639, 'App\\Models\\User', 147, 'auth-token', '6b60ff635f243ac2d1dd912a0a3e703549997633d4bd8f428b043dc221dd71b0', '[\"*\"]', NULL, NULL, '2026-03-30 03:29:10', '2026-03-30 03:29:10'),
(1642, 'App\\Models\\User', 147, 'auth-token', '320b60dd50d5d5644cc79d87f96ec2f1d29155832f8e8704bc44c6a9c604c674', '[\"*\"]', NULL, NULL, '2026-03-30 03:31:28', '2026-03-30 03:31:28'),
(1646, 'App\\Models\\User', 160, 'auth-token', '997f158ccbe35b8825934a6f770b77f74c29919b7a892588f9386c317287335b', '[\"*\"]', NULL, NULL, '2026-03-30 03:38:20', '2026-03-30 03:38:20'),
(1647, 'App\\Models\\User', 160, 'auth-token', '9040eb61e80b7d8554ca9eb9bf61c51bde531c21d1ff9dc40f283e0f790cf72d', '[\"*\"]', NULL, NULL, '2026-03-30 03:47:10', '2026-03-30 03:47:10'),
(1673, 'App\\Models\\User', 153, 'auth-token', '19ac97f0b76935403acc462536886e59595675ba37b7202a0ce5146cc4c4d8b4', '[\"*\"]', NULL, NULL, '2026-03-30 05:18:20', '2026-03-30 05:18:20'),
(1676, 'App\\Models\\User', 160, 'auth-token', '07aa8364eada9c0c2d9fa7d4710ea18537c560e5c148bf247de7a005605b4023', '[\"*\"]', NULL, NULL, '2026-03-30 05:25:49', '2026-03-30 05:25:49'),
(1678, 'App\\Models\\User', 160, 'auth-token', 'e778b5eab3a9f338be13144bf419bcd95551438d4925d8c23abd0e5ea99b0177', '[\"*\"]', NULL, NULL, '2026-03-30 05:28:48', '2026-03-30 05:28:48'),
(1682, 'App\\Models\\User', 153, 'auth-token', '55e963ee24ff4b7b9699d68799c1e2d3e461718e034cbe98b4946ec04af6238a', '[\"*\"]', NULL, NULL, '2026-03-30 05:40:11', '2026-03-30 05:40:11'),
(1703, 'App\\Models\\User', 147, 'auth-token', 'aa14709bdf0a057752d056b891f49f720a87a373a7822d4ec34e5ee183c0c8da', '[\"*\"]', NULL, NULL, '2026-03-30 06:08:26', '2026-03-30 06:08:26'),
(1713, 'App\\Models\\User', 28, 'auth-token', 'd6a31fe372dc7cf9768d5684e94179911feab0e477c41fb6bff252006163973d', '[\"*\"]', NULL, NULL, '2026-03-30 07:50:07', '2026-03-30 07:50:07'),
(1716, 'App\\Models\\User', 28, 'auth-token', 'fc5d7acf4311340d74829d533dbd12ad866cd01c5d45024cbab470fcece00703', '[\"*\"]', NULL, NULL, '2026-03-30 07:53:55', '2026-03-30 07:53:55'),
(1717, 'App\\Models\\User', 179, 'auth-token', '06f81091c46d48cf4f20e5226dbca85b79246264a11ae8063de092f1c9e3eb5c', '[\"*\"]', NULL, NULL, '2026-03-30 07:56:38', '2026-03-30 07:56:38'),
(1720, 'App\\Models\\User', 153, 'auth-token', '089a2608ebbce77638802aa407a9e2103dcfcfb73e993aa3673a3370321d3876', '[\"*\"]', NULL, NULL, '2026-03-30 08:00:48', '2026-03-30 08:00:48'),
(1740, 'App\\Models\\User', 173, 'auth-token', '00e2f2fcc6c84f6d32347e28beadaad3c53dc17d72da1e482cf76b75985a4bd9', '[\"*\"]', NULL, NULL, '2026-03-30 08:26:45', '2026-03-30 08:26:45'),
(1741, 'App\\Models\\User', 173, 'auth-token', '8f327bfce03b97584d1b9ddfe0a835fc139b80b8f9267f144be72b918c196f77', '[\"*\"]', NULL, NULL, '2026-03-30 08:28:54', '2026-03-30 08:28:54'),
(1742, 'App\\Models\\User', 28, 'auth-token', '07726974441f4396a293b4edb50d0bdb95497a803d0afc5d37d60dec95c4cb5d', '[\"*\"]', NULL, NULL, '2026-03-30 08:29:15', '2026-03-30 08:29:15'),
(1743, 'App\\Models\\User', 181, 'auth-token', '88f3ba9a54ac1873adf26b3d2b913d82a065ba1cfd52a46709234a827039b4eb', '[\"*\"]', NULL, NULL, '2026-03-30 08:33:21', '2026-03-30 08:33:21'),
(1754, 'App\\Models\\User', 153, 'auth-token', '7176a038ec2a77e80a71d8cbbcd7a81b941f0cf58e9d02c794fc6d7582417a02', '[\"*\"]', NULL, NULL, '2026-03-30 08:46:38', '2026-03-30 08:46:38'),
(1764, 'App\\Models\\User', 174, 'auth-token', 'cbaeb03770c6b07dfdd8ca1ea6670d907f305a42381fa88917871487a09ab487', '[\"*\"]', NULL, NULL, '2026-03-30 09:10:35', '2026-03-30 09:10:35'),
(1766, 'App\\Models\\User', 147, 'auth-token', '14564da9bb55acdc7ac2bcb13eec29e3a9098552b4c94b4c54f5983fea887c7c', '[\"*\"]', NULL, NULL, '2026-03-30 09:11:21', '2026-03-30 09:11:21'),
(1771, 'App\\Models\\User', 153, 'auth-token', 'a08fba05794d361d5622cddfe1467521fd53032bba014775304c36f0590ba1c5', '[\"*\"]', NULL, NULL, '2026-03-30 09:13:10', '2026-03-30 09:13:10'),
(1772, 'App\\Models\\User', 153, 'auth-token', 'fd5d5c79996bd4da4f6c330fb5659884e2b49ebd3142a5eddf01a5ab5cd75b71', '[\"*\"]', NULL, NULL, '2026-03-30 09:38:17', '2026-03-30 09:38:17'),
(1773, 'App\\Models\\User', 153, 'auth-token', '29053890efe0574e4de282eb0713ce8878cbf0aa3ad7ba5cc481b9edc52d9d7a', '[\"*\"]', NULL, NULL, '2026-03-30 10:43:43', '2026-03-30 10:43:43'),
(1776, 'App\\Models\\User', 28, 'auth-token', '6c0a7a909a1f7bb2d7a2bf82056c9080ac525f12c698f11f62bf4ae388cb49a5', '[\"*\"]', NULL, NULL, '2026-03-30 10:52:50', '2026-03-30 10:52:50'),
(1777, 'App\\Models\\User', 147, 'auth-token', '0f4b2508ff198e5785438ac08b670c64eed572cf64f74549956d675789f954e4', '[\"*\"]', NULL, NULL, '2026-03-30 10:53:22', '2026-03-30 10:53:22'),
(1779, 'App\\Models\\User', 28, 'auth-token', 'a9e615b1df90ca9f441a01a412a2458815128df245db52bb5ae0cc665db4b9e2', '[\"*\"]', NULL, NULL, '2026-03-30 10:59:20', '2026-03-30 10:59:20'),
(1781, 'App\\Models\\User', 173, 'auth-token', '544550b32f996b94da5fcde52e320dbd69c810b1064f672aec58a85172d2deb6', '[\"*\"]', NULL, NULL, '2026-03-30 11:04:44', '2026-03-30 11:04:44'),
(1782, 'App\\Models\\User', 181, 'auth-token', '4103a461cd6d33d9853b4d4922e48998aacf7085f37d52da17295d66d1c7f03d', '[\"*\"]', NULL, NULL, '2026-03-30 11:22:12', '2026-03-30 11:22:12'),
(1783, 'App\\Models\\User', 173, 'auth-token', '11f12be56c0a69cbe4ce3ac00d1333e483b131689d8b05052096f5c28500e7a7', '[\"*\"]', NULL, NULL, '2026-03-30 12:14:12', '2026-03-30 12:14:12'),
(1817, 'App\\Models\\User', 153, 'auth-token', 'dade9d72122c3da15e8eb617f7e3def73aedfd054a0a4952f9a35787b373d8ac', '[\"*\"]', NULL, NULL, '2026-03-30 14:15:02', '2026-03-30 14:15:02'),
(1845, 'App\\Models\\User', 153, 'auth-token', '769564c4b64c3124e4b6a0f659b7704a558dcbf5d675378b8aeeba972cc08fe6', '[\"*\"]', NULL, NULL, '2026-03-30 15:24:57', '2026-03-30 15:24:57'),
(1862, 'App\\Models\\User', 28, 'auth-token', '3f06efcf5dba0230126e0b67ad1fa331b70c9e46825a2a38c635bbad52965ceb', '[\"*\"]', NULL, NULL, '2026-03-30 16:03:45', '2026-03-30 16:03:45'),
(1888, 'App\\Models\\User', 179, 'auth-token', '885cc5a3973af89676c3e6da00aff05c5329180fdd3dca6e302f2f84e911a51c', '[\"*\"]', NULL, NULL, '2026-04-02 05:04:27', '2026-04-02 05:04:27'),
(1910, 'App\\Models\\User', 153, 'auth-token', 'eca20bdedf8e857cb1b56b1dc515b47efd0f9b9691a8b498f5ae9f1a50f6eeda', '[\"*\"]', NULL, NULL, '2026-04-02 05:38:59', '2026-04-02 05:38:59'),
(1914, 'App\\Models\\User', 153, 'auth-token', '0bf43d3c60fb349b954875b37b20c795cfcdcf8fccb7d93373c43f58a35cfa00', '[\"*\"]', NULL, NULL, '2026-04-02 05:42:19', '2026-04-02 05:42:19'),
(1929, 'App\\Models\\User', 147, 'auth-token', '64471572bf5304a79742a8549dca35c6531d2418412063eca4676d8dde7cac82', '[\"*\"]', NULL, NULL, '2026-04-02 06:10:04', '2026-04-02 06:10:04'),
(1932, 'App\\Models\\User', 147, 'auth-token', '23d6dd83a0697f38dd71b9e9a880901fb7c4a3477af29698b7a1addfe54ac705', '[\"*\"]', NULL, NULL, '2026-04-02 08:08:05', '2026-04-02 08:08:05'),
(1944, 'App\\Models\\User', 28, 'auth-token', '633fc8561f88ea9e72510fd4a8266f31106214044a8061d944f7499ff4f4ec6f', '[\"*\"]', NULL, NULL, '2026-04-02 08:41:04', '2026-04-02 08:41:04'),
(1947, 'App\\Models\\User', 174, 'auth-token', '40ebfd2d918c2e278a0e13dd6176e5e8eb71cfa9bd27d14faf8d0b9440b5f6b7', '[\"*\"]', NULL, NULL, '2026-04-02 08:42:45', '2026-04-02 08:42:45'),
(1952, 'App\\Models\\User', 147, 'auth-token', 'd00b3d3056a8c442a3a415347b434e0bbecef492d068ebe2fba9849aff0b54f2', '[\"*\"]', NULL, NULL, '2026-04-02 08:50:18', '2026-04-02 08:50:18'),
(1953, 'App\\Models\\User', 153, 'auth-token', 'd5ebbd214e90a0a3d8cfdc3c8dd2b24739e0004a8858d6dac8a315d5a3979a9b', '[\"*\"]', NULL, NULL, '2026-04-02 08:50:55', '2026-04-02 08:50:55');
INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1954, 'App\\Models\\User', 147, 'auth-token', '9855d5b959f61c875b080ca4cdcca01aae5baefae4c421cef3ba693da92c5e48', '[\"*\"]', NULL, NULL, '2026-04-02 08:56:57', '2026-04-02 08:56:57'),
(1956, 'App\\Models\\User', 147, 'auth-token', '1f7c5ce48270f42a777d20cdd014f00a713fa6dc52fb72a5d144658ce5836ec1', '[\"*\"]', NULL, NULL, '2026-04-02 09:06:47', '2026-04-02 09:06:47'),
(1957, 'App\\Models\\User', 147, 'auth-token', 'ad8fa6c62022029d84d200223f8c8ac9861e082c80c54aac004ba39b9ee02609', '[\"*\"]', NULL, NULL, '2026-04-02 09:14:33', '2026-04-02 09:14:33'),
(1958, 'App\\Models\\User', 153, 'auth-token', 'b11ed3bbad441dd77f45a02e7d02e8f7b1fdee62a042d144286472d7d1c97c30', '[\"*\"]', NULL, NULL, '2026-04-02 09:30:57', '2026-04-02 09:30:57'),
(1960, 'App\\Models\\User', 147, 'auth-token', '5b45c4e7791dcfb9f4b45179f04d9a725dec77357abad4df07b91839c0f71b8d', '[\"*\"]', NULL, NULL, '2026-04-02 09:37:34', '2026-04-02 09:37:34'),
(1965, 'App\\Models\\User', 160, 'auth-token', 'e62d94bb21b20fb4f4e38114e092e1263bdda1849c9fd1db53b4829798f7e567', '[\"*\"]', NULL, NULL, '2026-04-02 09:46:22', '2026-04-02 09:46:22'),
(1995, 'App\\Models\\User', 153, 'auth-token', 'c1f5f61b9b53aaf846d996728fd75e7c976ea8b6b417022f5936858734413e55', '[\"*\"]', NULL, NULL, '2026-04-02 10:08:19', '2026-04-02 10:08:19'),
(2010, 'App\\Models\\User', 147, 'auth-token', '2437fefc0d2305ea69cd02a6b230baacdcf00513f71bd41cb4fedced5269105f', '[\"*\"]', NULL, NULL, '2026-04-02 11:25:18', '2026-04-02 11:25:18'),
(2020, 'App\\Models\\User', 153, 'auth-token', '940df8146ed2d03533c5a66c7ca34c1357fe44fa7602fcbca9dd8b02097afed9', '[\"*\"]', NULL, NULL, '2026-04-02 11:37:09', '2026-04-02 11:37:09'),
(2021, 'App\\Models\\User', 147, 'auth-token', 'f508c0d0197780d1ea55a67cab35ed6abcda4e9e13e04c3cb884880537a3e974', '[\"*\"]', NULL, NULL, '2026-04-02 11:37:46', '2026-04-02 11:37:46'),
(2024, 'App\\Models\\User', 147, 'auth-token', 'fac9707a7dba0eca59d5d8ced7e6691bc394c3cfe372e86eb0080fcda24cea3c', '[\"*\"]', NULL, NULL, '2026-04-02 11:43:43', '2026-04-02 11:43:43'),
(2028, 'App\\Models\\User', 147, 'auth-token', 'c2c74f0cfa9d006d04f9b8e1c63fdef5083626c84bbe6486dacf38a5e0ed5c7d', '[\"*\"]', NULL, NULL, '2026-04-02 11:46:35', '2026-04-02 11:46:35'),
(2032, 'App\\Models\\User', 153, 'auth-token', 'cf48a27f640d52dccf1c8db3be15b5c54358ff9bab4706c276aefc882794b81a', '[\"*\"]', NULL, NULL, '2026-04-02 11:52:57', '2026-04-02 11:52:57'),
(2033, 'App\\Models\\User', 147, 'auth-token', 'dd892773ad9b13ae1ea2d49bbc050cc7c1f371075ca61c34754b09bbe00edc46', '[\"*\"]', NULL, NULL, '2026-04-02 11:53:09', '2026-04-02 11:53:09'),
(2038, 'App\\Models\\User', 147, 'auth-token', 'ccf74309dfc7084045119e4a7b7da9008d6f307ecb6d4871e672f5ddf5902121', '[\"*\"]', NULL, NULL, '2026-04-02 12:17:49', '2026-04-02 12:17:49'),
(2043, 'App\\Models\\User', 147, 'auth-token', '1296713e001a1ac6a0a2edeb542dcc3991a64b712b493142530b909eeb2c1007', '[\"*\"]', NULL, NULL, '2026-04-02 13:27:34', '2026-04-02 13:27:34'),
(2065, 'App\\Models\\User', 147, 'auth-token', '88cf752e4dde392703f716b5bc545c1ec07ffe6abb82e0a0f842fd42cb15324e', '[\"*\"]', NULL, NULL, '2026-04-02 13:58:36', '2026-04-02 13:58:36'),
(2066, 'App\\Models\\User', 153, 'auth-token', '92346ab3f3499e8105119fccc7022964741ef9e28c18a2736d2d266465e75886', '[\"*\"]', NULL, NULL, '2026-04-02 13:59:34', '2026-04-02 13:59:34'),
(2067, 'App\\Models\\User', 153, 'auth-token', 'b22e70e52ad7a7a6e83063e499de53c2f7382de70fe9485f65d831ab0a3794c8', '[\"*\"]', NULL, NULL, '2026-04-02 14:05:33', '2026-04-02 14:05:33'),
(2086, 'App\\Models\\User', 153, 'auth-token', '061a0ba4b2f2da8f90e6a131481804fc3bfb1bfc72f1a601d2daa5cbfe3b3124', '[\"*\"]', NULL, NULL, '2026-04-02 14:34:45', '2026-04-02 14:34:45'),
(2102, 'App\\Models\\User', 153, 'auth-token', '59053bf0a873cf48037fce4ec9fe9cb17adc4d101d642d11b1e12f6e30e739d7', '[\"*\"]', NULL, NULL, '2026-04-02 14:58:23', '2026-04-02 14:58:23'),
(2109, 'App\\Models\\User', 28, 'auth-token', '58317aa1de3954656fdc44258ebac6a547c311bc179131298e03c2116ea96885', '[\"*\"]', NULL, NULL, '2026-04-03 08:46:55', '2026-04-03 08:46:55'),
(2119, 'App\\Models\\User', 28, 'auth-token', '7bae80bc9a58213b9e35b341637521a38e244d21fe13f1bde3fff2bd727c9ea6', '[\"*\"]', NULL, NULL, '2026-04-03 13:00:48', '2026-04-03 13:00:48'),
(2121, 'App\\Models\\User', 153, 'auth-token', '26453beeec686c5a03aae7a2438a14162651425b9504d7ed152f1d1de0f7b36d', '[\"*\"]', NULL, NULL, '2026-04-03 13:38:27', '2026-04-03 13:38:27'),
(2124, 'App\\Models\\User', 147, 'auth-token', '395f28b8f8074a82b8e884fa9588a4defc5570ed6049a57351adac368ea41459', '[\"*\"]', NULL, NULL, '2026-04-03 14:50:54', '2026-04-03 14:50:54'),
(2125, 'App\\Models\\User', 28, 'auth-token', '8a8e83d1e00a00ffc79bc3dfc20b44063ee50e0bb5bd6fe83ec21dd28e3fdfb8', '[\"*\"]', NULL, NULL, '2026-04-03 14:51:38', '2026-04-03 14:51:38'),
(2126, 'App\\Models\\User', 182, 'auth-token', '4a7fe8cbfd39caa9b67fef0bbd6bb1daed51a129a682738ed8541a026f6ec0b5', '[\"*\"]', NULL, NULL, '2026-04-03 14:55:10', '2026-04-03 14:55:10'),
(2127, 'App\\Models\\User', 182, 'auth-token', 'cc6d56d3faad5c75a9dfac5a8a20023b23e16fff4318483427aa0eb3dfa0e49b', '[\"*\"]', NULL, NULL, '2026-04-03 15:02:17', '2026-04-03 15:02:17'),
(2128, 'App\\Models\\User', 182, 'auth-token', '13c191305644a322c3456120c5af05b300364ef4d7368268c443f99aab821f87', '[\"*\"]', NULL, NULL, '2026-04-03 15:02:57', '2026-04-03 15:02:57'),
(2129, 'App\\Models\\User', 147, 'auth-token', 'a23012575adc5de4a9116d988af542e01521dc286d91727a12b8216ae3a2ee22', '[\"*\"]', NULL, NULL, '2026-04-03 15:09:38', '2026-04-03 15:09:38'),
(2131, 'App\\Models\\User', 182, 'auth-token', 'afad6d27cadf8392aacf093bc9fb68844bcf4c7a2ccc8765960e186006ec724f', '[\"*\"]', NULL, NULL, '2026-04-03 15:18:45', '2026-04-03 15:18:45'),
(2135, 'App\\Models\\User', 28, 'auth-token', '60818938e9305198de533fe35bc388fdf0d0f6045adf438c3f4013af3be6d0d1', '[\"*\"]', NULL, NULL, '2026-04-03 16:08:15', '2026-04-03 16:08:15'),
(2136, 'App\\Models\\User', 147, 'auth-token', 'ff284a9a60389df57706342b08ee444738fdbe7f5febaecf1cd07cb9f34d3fc3', '[\"*\"]', NULL, NULL, '2026-04-03 16:12:57', '2026-04-03 16:12:57'),
(2138, 'App\\Models\\User', 28, 'auth-token', '186277d54bff5f766cc8551ec38b9c5d732e20c011a1e6439ff9919993558036', '[\"*\"]', NULL, NULL, '2026-04-03 16:55:05', '2026-04-03 16:55:05'),
(2139, 'App\\Models\\User', 153, 'auth-token', 'fbedae61d0b00bdc0f706057028b0d0a7389c8e36933045ffea7fc9ebf300d92', '[\"*\"]', NULL, NULL, '2026-04-03 17:11:44', '2026-04-03 17:11:44'),
(2142, 'App\\Models\\User', 28, 'auth-token', '5a873f5399fbec2a638587ca2de4a22bef3056f9c3b5bdc1a1275050cdfa7d17', '[\"*\"]', NULL, NULL, '2026-04-03 17:25:22', '2026-04-03 17:25:22'),
(2146, 'App\\Models\\User', 28, 'auth-token', '5363a04d1db310e9a69f745dc2085502e35aa594ef52d03a4d6731ce9f3627fc', '[\"*\"]', NULL, NULL, '2026-04-03 18:11:51', '2026-04-03 18:11:51'),
(2149, 'App\\Models\\User', 147, 'auth-token', '9175527f7dcacab394827c8a8af856ad77e4633c87e68aee22ccc483135728f0', '[\"*\"]', NULL, NULL, '2026-04-03 18:22:32', '2026-04-03 18:22:32'),
(2151, 'App\\Models\\User', 28, 'auth-token', '44caf5f2727c65500e1ed6d3087c81188429dc4e6639d8e4061b5697bb32a91e', '[\"*\"]', NULL, NULL, '2026-04-03 18:23:35', '2026-04-03 18:23:35'),
(2157, 'App\\Models\\User', 153, 'auth-token', '65acb0c4fd7a90197711302f2d2b30a6822a4aa423748ffb3371439781c76ed3', '[\"*\"]', NULL, NULL, '2026-04-04 07:15:07', '2026-04-04 07:15:07'),
(2159, 'App\\Models\\User', 153, 'auth-token', '98961ccf2813268cd354f423817f60ae94ef7a295c6865b978627bc8cafb4ffe', '[\"*\"]', NULL, NULL, '2026-04-04 07:16:28', '2026-04-04 07:16:28'),
(2161, 'App\\Models\\User', 153, 'auth-token', '6083c618b3810f54bd69bec22592355faf11d5dce1b9101e4844c26e54e51e2d', '[\"*\"]', NULL, NULL, '2026-04-04 07:20:35', '2026-04-04 07:20:35'),
(2165, 'App\\Models\\User', 147, 'auth-token', 'c0973bd4ca1883272bb7a6cb35ffcaa60acdcab150de147ee2773a71d23d73d8', '[\"*\"]', NULL, NULL, '2026-04-04 07:23:57', '2026-04-04 07:23:57'),
(2174, 'App\\Models\\User', 147, 'auth-token', '4e0f9a66a67baec5d0e2ca4a89ff5dc2849dba8c12b2280e460349e7a87b7c60', '[\"*\"]', NULL, NULL, '2026-04-04 07:42:43', '2026-04-04 07:42:43'),
(2186, 'App\\Models\\User', 153, 'auth-token', '73d276c3772ba00a0d44f288db5e2de82ebf7e4a833bbee5ff8cacb1b580abb9', '[\"*\"]', NULL, NULL, '2026-04-04 07:51:22', '2026-04-04 07:51:22'),
(2190, 'App\\Models\\User', 184, 'auth-token', '42dda13b7076163b4adc9d502a04a5d46633aa3f2fd3d4361a737eb6dedc82d5', '[\"*\"]', NULL, NULL, '2026-04-04 09:20:55', '2026-04-04 09:20:55'),
(2192, 'App\\Models\\User', 185, 'auth-token', 'b7f35ea7158c5edde81a0ad3068f6bcfbc7944bd60b6a858f30593d7b57a46fd', '[\"*\"]', NULL, NULL, '2026-04-04 10:21:42', '2026-04-04 10:21:42'),
(2194, 'App\\Models\\User', 187, 'auth-token', 'e15baa675caf5fb0db6ffa968e8d91c48141febd71cc98fc09aea2e119a80d96', '[\"*\"]', NULL, NULL, '2026-04-04 10:51:23', '2026-04-04 10:51:23'),
(2196, 'App\\Models\\User', 28, 'auth-token', '4102ad4eb56b5c1b2733a757e4d17200d9cbfa5014e5a9686b3f60fe641536a1', '[\"*\"]', NULL, NULL, '2026-04-04 10:59:14', '2026-04-04 10:59:14'),
(2197, 'App\\Models\\User', 187, 'auth-token', 'df2a3429f2d1c4b6cd71ae975b4062c44b9235bb1ab5ee7e107fdb593c9b92e9', '[\"*\"]', NULL, NULL, '2026-04-04 11:01:06', '2026-04-04 11:01:06'),
(2199, 'App\\Models\\User', 187, 'auth-token', 'e99386c9b1a14ba1696e452ba263e30938b887cf030f9672b3a458a6e5f8cc35', '[\"*\"]', NULL, NULL, '2026-04-04 12:17:56', '2026-04-04 12:17:56'),
(2200, 'App\\Models\\User', 147, 'auth-token', '501d92ae5644ecb17816982d1cb5b7a285844ad9cf32d066cd82d3bfd449cc89', '[\"*\"]', NULL, NULL, '2026-04-04 12:19:13', '2026-04-04 12:19:13'),
(2201, 'App\\Models\\User', 28, 'auth-token', 'ec3c237170a3caa47c9ecf8a49fb4f2f86eb9d39baa713c2cf6b4f7668dfdfb6', '[\"*\"]', NULL, NULL, '2026-04-04 12:24:59', '2026-04-04 12:24:59'),
(2203, 'App\\Models\\User', 147, 'auth-token', '126df05472c1c26cc069198aed5dd41f7472ad4f64ce9c262bfb5c47fad143d3', '[\"*\"]', NULL, NULL, '2026-04-04 13:02:10', '2026-04-04 13:02:10'),
(2208, 'App\\Models\\User', 187, 'auth-token', '57877470d09ae4574dc788ef617c18144056134f52fc21dcf393c253b20ab663', '[\"*\"]', NULL, NULL, '2026-04-04 13:24:37', '2026-04-04 13:24:37'),
(2209, 'App\\Models\\User', 28, 'auth-token', '5b4d423194b6df53ace28f4f7117d792877c8d1f850d6198116dc38969c5c22f', '[\"*\"]', NULL, NULL, '2026-04-04 13:24:58', '2026-04-04 13:24:58'),
(2210, 'App\\Models\\User', 28, 'auth-token', 'c6bfe9922359db5b586a3e55a4fc3cbe61ed25464bc22daac14373adee48d86a', '[\"*\"]', NULL, NULL, '2026-04-04 13:37:14', '2026-04-04 13:37:14'),
(2228, 'App\\Models\\User', 187, 'auth-token', 'a50a44a4e49a1ae8d315043844842eb84a7c5a4943e12f3a6c14311b867edb4a', '[\"*\"]', NULL, NULL, '2026-04-04 14:36:02', '2026-04-04 14:36:02'),
(2239, 'App\\Models\\User', 147, 'auth-token', '12ddb9bb1881f5e49b303449e45ca395dd9355ee35005e2573799f490b6444c6', '[\"*\"]', NULL, NULL, '2026-04-04 17:03:43', '2026-04-04 17:03:43'),
(2240, 'App\\Models\\User', 28, 'auth-token', 'facd8647123ce4866efc7164d06c0bca78b9f5f48fa0501ae554f01f9b37216e', '[\"*\"]', NULL, NULL, '2026-04-04 17:04:59', '2026-04-04 17:04:59'),
(2241, 'App\\Models\\User', 28, 'auth-token', '451cc4bede3b348880b53faa37e1d8ec6dbb5613b96bd8dfe267ba825a5992bc', '[\"*\"]', NULL, NULL, '2026-04-04 17:10:17', '2026-04-04 17:10:17'),
(2242, 'App\\Models\\User', 28, 'auth-token', '43f0ba365173e756e2c07d8639f8ff99722a6392fbc61e0d93d6b86db19ee779', '[\"*\"]', NULL, NULL, '2026-04-04 17:12:45', '2026-04-04 17:12:45'),
(2243, 'App\\Models\\User', 28, 'auth-token', '3080a86f6bca270065ea89a471ee17ce8c1843ee677eb20c9fd1f42e5bead3a6', '[\"*\"]', NULL, NULL, '2026-04-04 17:13:17', '2026-04-04 17:13:17'),
(2244, 'App\\Models\\User', 28, 'auth-token', 'df2985c879531565a43197bd01f7fe9f1e2b347d4190d470c16d1a0c9138dc61', '[\"*\"]', NULL, NULL, '2026-04-04 19:02:30', '2026-04-04 19:02:30'),
(2245, 'App\\Models\\User', 147, 'auth-token', '53f13c694b268aaab85cad9db126d13ae6484cbb63b60097362d3eaa43e5a6ae', '[\"*\"]', NULL, NULL, '2026-04-04 19:12:47', '2026-04-04 19:12:47'),
(2249, 'App\\Models\\User', 147, 'auth-token', '128182a035913ce58d3486a18319d9e25504ecdab2711d6c94cc2fd003efb06d', '[\"*\"]', NULL, NULL, '2026-04-04 19:41:27', '2026-04-04 19:41:27'),
(2250, 'App\\Models\\User', 147, 'auth-token', '9724222835f222c285c9548f7b921f7834975b44e5ceba0f468ce6ecbfc86f25', '[\"*\"]', NULL, NULL, '2026-04-04 20:06:59', '2026-04-04 20:06:59'),
(2251, 'App\\Models\\User', 147, 'auth-token', 'fcb71683038be733df2af4044b354d4938dbd11ddc43e78feca348b4ab125ba6', '[\"*\"]', NULL, NULL, '2026-04-04 20:21:16', '2026-04-04 20:21:16'),
(2253, 'App\\Models\\User', 28, 'auth-token', '60efbdcefc1116a94e6dc927b54f604d2c05154c56c20ea96510c1e6a53a3e19', '[\"*\"]', NULL, NULL, '2026-04-04 20:25:01', '2026-04-04 20:25:01'),
(2256, 'App\\Models\\User', 147, 'auth-token', 'b14980c206cfd137a17d7a018cf6a4a4e96406185faac5ce5febfb89fcae0cb9', '[\"*\"]', NULL, NULL, '2026-04-04 20:26:25', '2026-04-04 20:26:25'),
(2257, 'App\\Models\\User', 147, 'auth-token', '2db0acbc4185ebebc307fdd4129c4844006270d32e5e74e56b4f6af8023b4062', '[\"*\"]', NULL, NULL, '2026-04-04 20:45:31', '2026-04-04 20:45:31'),
(2258, 'App\\Models\\User', 147, 'auth-token', '2965ce593474dee721a8ae702f829d4dd0383d77e7d55510204f5245e3b7149c', '[\"*\"]', NULL, NULL, '2026-04-04 20:49:18', '2026-04-04 20:49:18'),
(2259, 'App\\Models\\User', 147, 'auth-token', 'aed4b76570a3a217424d27cfd693a5a529c4371c33a90a2a3c1e3aac0da493d3', '[\"*\"]', NULL, NULL, '2026-04-04 21:14:31', '2026-04-04 21:14:31'),
(2260, 'App\\Models\\User', 28, 'auth-token', 'b3fc8226a670453a1b491e6027f276d875b57d9d5922a7501746136a27d56ff0', '[\"*\"]', NULL, NULL, '2026-04-04 21:18:34', '2026-04-04 21:18:34'),
(2262, 'App\\Models\\User', 28, 'auth-token', 'fd32f5477781d11eb69979adddd7f563fa60f7960a462080f2d3bbc74b1f7a9b', '[\"*\"]', NULL, NULL, '2026-04-04 21:52:33', '2026-04-04 21:52:33'),
(2263, 'App\\Models\\User', 147, 'auth-token', 'acf3aac1c7caae92a3486c1241a8f6fe0d79429da70d0ec5e90a26b10f8ac576', '[\"*\"]', NULL, NULL, '2026-04-04 21:53:47', '2026-04-04 21:53:47'),
(2264, 'App\\Models\\User', 147, 'auth-token', '4431b5e9b8d1a5327c2ae7548f0d4f7310482a29b4e2f04f714ec72018779d88', '[\"*\"]', NULL, NULL, '2026-04-04 21:54:26', '2026-04-04 21:54:26'),
(2265, 'App\\Models\\User', 28, 'auth-token', '5e551f43b45e8523489f5a6fb2e2e18967d7b9dd229cb51910039c252d33bba9', '[\"*\"]', NULL, NULL, '2026-04-04 22:38:14', '2026-04-04 22:38:14'),
(2268, 'App\\Models\\User', 28, 'auth-token', 'cac795c42e23650fcb7953db741a1b608d841b2b5ace488970c8939900e4fe14', '[\"*\"]', NULL, NULL, '2026-04-04 23:40:47', '2026-04-04 23:40:47'),
(2269, 'App\\Models\\User', 153, 'auth-token', 'c9c586cde622d28afd38911c1c65b12e5a077a392e2e13637f1d613b632b0805', '[\"*\"]', NULL, NULL, '2026-04-05 01:15:38', '2026-04-05 01:15:38'),
(2273, 'App\\Models\\User', 28, 'auth-token', '99f6bd6491769a5607610791d8fe98fca0345196e00523f16d49a1333878866e', '[\"*\"]', NULL, NULL, '2026-04-05 01:24:16', '2026-04-05 01:24:16'),
(2276, 'App\\Models\\User', 28, 'auth-token', 'e4fe787caee6a057f6a29c29e10ee92c98bacdce48f26b266e6f825d01c1054d', '[\"*\"]', NULL, NULL, '2026-04-05 01:37:22', '2026-04-05 01:37:22'),
(2278, 'App\\Models\\User', 28, 'auth-token', '075853b999f0ce903e72d1482a543f9de925ed1ef25de31416e5cdc0361af088', '[\"*\"]', NULL, NULL, '2026-04-05 01:43:04', '2026-04-05 01:43:04'),
(2280, 'App\\Models\\User', 28, 'auth-token', '4339a1d139f472535b820102a1844ed5fe1a6a133dad0129a4978a575fbda257', '[\"*\"]', NULL, NULL, '2026-04-05 01:51:45', '2026-04-05 01:51:45'),
(2283, 'App\\Models\\User', 28, 'auth-token', 'c34252d8115ac1530142a7bcdb5f8b9dde71457a3cd77e3913e0338f92bb3912', '[\"*\"]', NULL, NULL, '2026-04-05 07:19:00', '2026-04-05 07:19:00'),
(2284, 'App\\Models\\User', 187, 'auth-token', 'cca87e8c35d66289ab41146ac4d055429e54c1215190987d6c4252b0817bab48', '[\"*\"]', NULL, NULL, '2026-04-05 07:23:48', '2026-04-05 07:23:48'),
(2287, 'App\\Models\\User', 160, 'auth-token', '4bccb2a59ad5b2d66e9d2bb0a4e395a1c925fe074ae4a88b2a0d3451ef695450', '[\"*\"]', NULL, NULL, '2026-04-05 07:29:47', '2026-04-05 07:29:47'),
(2289, 'App\\Models\\User', 28, 'auth-token', '2125bd7784a81a79120cb71ecba67d6913b3999375222b6e8b4c29b7e5f7191b', '[\"*\"]', NULL, NULL, '2026-04-05 07:33:34', '2026-04-05 07:33:34'),
(2290, 'App\\Models\\User', 28, 'auth-token', '2cd54ffba4655d3ff73e892e74a39f098c824edb54bc8118ac0ec807a247dc55', '[\"*\"]', NULL, NULL, '2026-04-05 07:41:10', '2026-04-05 07:41:10'),
(2291, 'App\\Models\\User', 193, 'auth-token', 'dde12743ce798e239bf7d1dd83ac1ffe430df82df6659038e96813c6d1280043', '[\"*\"]', NULL, NULL, '2026-04-05 07:41:59', '2026-04-05 07:41:59'),
(2292, 'App\\Models\\User', 193, 'auth-token', '88162a55693ccaf4bd9765cb4c564c7ac8198df735ad148d191b6d3f0a85fb61', '[\"*\"]', NULL, NULL, '2026-04-05 07:42:27', '2026-04-05 07:42:27'),
(2297, 'App\\Models\\User', 153, 'auth-token', '42b515a81e38886f8355548656fe8f0e435013be22d323b6f5f61ec49604f712', '[\"*\"]', NULL, NULL, '2026-04-05 07:51:27', '2026-04-05 07:51:27'),
(2321, 'App\\Models\\User', 153, 'auth-token', '895098d631f55a5da5e505f58041eb6270bfb35c3987402d2a5a2698f74ec9c1', '[\"*\"]', NULL, NULL, '2026-04-05 10:29:44', '2026-04-05 10:29:44'),
(2324, 'App\\Models\\User', 187, 'auth-token', '0638c67b85b4ff05f7a68c8f9ad2ca91a692c00d995f0d086e523a44ee668929', '[\"*\"]', NULL, NULL, '2026-04-05 10:36:36', '2026-04-05 10:36:36'),
(2326, 'App\\Models\\User', 153, 'auth-token', '33caf024f2b4cb10f3dececda2ed1b7856b5d1503a1594cc29124b3ee9e6d290', '[\"*\"]', NULL, NULL, '2026-04-05 11:05:24', '2026-04-05 11:05:24'),
(2336, 'App\\Models\\User', 147, 'auth-token', 'c80befcc21dad0cc4465d2d2b3103fc366c56bb04613f2cea249603f994c4303', '[\"*\"]', NULL, NULL, '2026-04-05 12:04:00', '2026-04-05 12:04:00'),
(2353, 'App\\Models\\User', 28, 'auth-token', '839a05274a11190edf585ea0585637dd00983f8fecf68d57cf9837186edbe4eb', '[\"*\"]', NULL, NULL, '2026-04-05 13:27:05', '2026-04-05 13:27:05'),
(2356, 'App\\Models\\User', 147, 'auth-token', 'd11b822992fcc937d7a9eb27ec75d2187aafad2debf93ebcdc660a7cade6df2e', '[\"*\"]', NULL, NULL, '2026-04-05 13:30:07', '2026-04-05 13:30:07'),
(2359, 'App\\Models\\User', 187, 'auth-token', 'cb7b082a273216abfc09dc9143afe123e9b1480cb76a925a63679b413418789b', '[\"*\"]', NULL, NULL, '2026-04-05 13:31:43', '2026-04-05 13:31:43'),
(2360, 'App\\Models\\User', 187, 'auth-token', 'd7fe4f401b5bfde603203ab4d907070cfd0e12ab244ab11211cbbb354459d646', '[\"*\"]', NULL, NULL, '2026-04-05 13:40:30', '2026-04-05 13:40:30'),
(2366, 'App\\Models\\User', 153, 'auth-token', '7963cda20514d5d386656ce1ac2a3a388a72dd3b7ca2a06c15b7f5340cd40aec', '[\"*\"]', NULL, NULL, '2026-04-05 18:24:09', '2026-04-05 18:24:09'),
(2372, 'App\\Models\\User', 187, 'auth-token', '96f803c96c8ab798753976d6f1b3686fef5ef9ce1bc012bf4aa0143c5fad0ce0', '[\"*\"]', NULL, NULL, '2026-04-05 19:01:32', '2026-04-05 19:01:32'),
(2381, 'App\\Models\\User', 215, 'auth-token', '34f0110d9048096ab92a1f843f4ee7d8275a10df10af49b780daf11a69e31849', '[\"*\"]', NULL, NULL, '2026-04-05 19:48:06', '2026-04-05 19:48:06'),
(2392, 'App\\Models\\User', 222, 'auth-token', '207f82f29498181dc4d06bc1c0c4a5008b4b1dd7ce18b3760605e4860024bb73', '[\"*\"]', NULL, NULL, '2026-04-14 11:25:54', '2026-04-14 11:25:54'),
(2393, 'App\\Models\\User', 222, 'auth-token', '5a92438665df6274d8861739f16b05c1669c3a39af278348129b84365d40249e', '[\"*\"]', NULL, NULL, '2026-04-14 11:31:08', '2026-04-14 11:31:08'),
(2395, 'App\\Models\\User', 223, 'auth-token', 'a1f5c7bd5f25bc05284df50c415c1f1e490698320149a83582e58411a204933f', '[\"*\"]', NULL, NULL, '2026-04-14 11:49:25', '2026-04-14 11:49:25'),
(2396, 'App\\Models\\User', 223, 'auth-token', '06545841d2fcaa5632644fbd0def58a3ba6ec5bcc76125ba7d497e0d3748129b', '[\"*\"]', NULL, NULL, '2026-04-14 11:52:25', '2026-04-14 11:52:25'),
(2399, 'App\\Models\\User', 230, 'auth-token', '04a112738af75e29da5b0bfa1797ef927b9c6dabb1761a9694da9073e522d360', '[\"*\"]', NULL, NULL, '2026-04-14 13:05:38', '2026-04-14 13:05:38'),
(2404, 'App\\Models\\User', 236, 'auth-token', '38e701af19d87a529316510560ba3e94a9e16f720674edb56dbaf95c32b4d856', '[\"*\"]', NULL, NULL, '2026-04-15 03:28:56', '2026-04-15 03:28:56'),
(2405, 'App\\Models\\User', 236, 'auth-token', '9618fbd9ccfbc34f52857db1d6b2fb2312057eb0e9e32cca0e8f975a70ccb9bf', '[\"*\"]', NULL, NULL, '2026-04-15 03:29:27', '2026-04-15 03:29:27'),
(2406, 'App\\Models\\User', 236, 'auth-token', '39f8386852e754829dfbd1fa016dee63496a6d2d37b94ce49f256f1922115e2a', '[\"*\"]', NULL, NULL, '2026-04-15 03:30:10', '2026-04-15 03:30:10'),
(2407, 'App\\Models\\User', 236, 'auth-token', '93ee1532c27d0c935052d62aa78abec2f277bc3dc0e3530400a71f3b74af1115', '[\"*\"]', NULL, NULL, '2026-04-15 03:35:29', '2026-04-15 03:35:29'),
(2422, 'App\\Models\\User', 239, 'auth-token', '21c36f85886895c00d2969cdea7134a0f848ec05330601c30e35a921b8ed1914', '[\"*\"]', NULL, NULL, '2026-04-15 04:49:39', '2026-04-15 04:49:39'),
(2423, 'App\\Models\\User', 239, 'auth-token', 'cbd7b771f762b05221344b3fe5d7052e2fcf3ebec4ddb40e11d48c575e24c43f', '[\"*\"]', NULL, NULL, '2026-04-15 04:50:27', '2026-04-15 04:50:27'),
(2425, 'App\\Models\\User', 147, 'auth-token', '6ef9bc293bf018f52a8b25595f8f76d50b04f9ba8a5630503a2e9763776cacc8', '[\"*\"]', NULL, NULL, '2026-04-15 05:17:43', '2026-04-15 05:17:43'),
(2426, 'App\\Models\\User', 28, 'auth-token', '4a6e297acfe06810a3c1d2c0a90f0320016d642ebf4ad63b3fb3fc496c3f5670', '[\"*\"]', NULL, NULL, '2026-04-15 06:22:36', '2026-04-15 06:22:36'),
(2427, 'App\\Models\\User', 147, 'auth-token', '4a761df157665e818ef6e0ad56d7162cbb0602b097cc8a9c63b7b82a7bbf633e', '[\"*\"]', NULL, NULL, '2026-04-15 06:23:55', '2026-04-15 06:23:55'),
(2429, 'App\\Models\\User', 147, 'auth-token', 'f862ffc7211109f7ae1286386cc3aeec012e99a1213f6fcd845a3629567b72e3', '[\"*\"]', NULL, NULL, '2026-04-15 12:28:52', '2026-04-15 12:28:52'),
(2431, 'App\\Models\\User', 147, 'auth-token', '29ac0b903868eb563da1239c3ca6ed147e12a95efcad75a168d5079cfcdc9650', '[\"*\"]', NULL, NULL, '2026-04-15 12:49:49', '2026-04-15 12:49:49'),
(2442, 'App\\Models\\User', 147, 'auth-token', '35b6f450a0971d48813e88f37c1fad4372b0c2d91dad3ee316c14a0a420fa57c', '[\"*\"]', NULL, NULL, '2026-05-23 09:40:21', '2026-05-23 09:40:21'),
(2466, 'App\\Models\\User', 147, 'auth-token', '68ecc944dfacd338f822a53aa8b2cff024fb38f9bb7c3cf3d65f626793a2d79d', '[\"*\"]', NULL, NULL, '2026-05-26 08:32:10', '2026-05-26 08:32:10'),
(2468, 'App\\Models\\User', 147, 'auth-token', '8bf48b075f39320269494d214c18ec2d794108b6d2099a11206122e2389c1aec', '[\"*\"]', NULL, NULL, '2026-05-26 08:42:52', '2026-05-26 08:42:52'),
(2469, 'App\\Models\\User', 28, 'auth-token', '754bdc5ff1c7630ebb63e1a117a345298e6142087b48e80bed820c04b35d7e65', '[\"*\"]', NULL, NULL, '2026-05-26 08:44:47', '2026-05-26 08:44:47'),
(2470, 'App\\Models\\User', 147, 'auth-token', '1818b83cb393a3607152c0713b3d32097a86c71e85f20bbf1e34026fdd518dc4', '[\"*\"]', NULL, NULL, '2026-05-26 08:46:27', '2026-05-26 08:46:27'),
(2477, 'App\\Models\\User', 147, 'auth-token', '59952709a901aece9d520fafb64bfd5b81e5455aff1bf9cfdc481f334d167797', '[\"*\"]', NULL, NULL, '2026-06-01 02:07:41', '2026-06-01 02:07:41'),
(2482, 'App\\Models\\User', 147, 'auth-token', '9ba4e37cc484c88566dc85b3b46fb278fdb1b212ae1210f50b31f06790dd4355', '[\"*\"]', NULL, NULL, '2026-06-08 03:52:08', '2026-06-08 03:52:08'),
(2484, 'App\\Models\\User', 147, 'auth-token', 'c729ff461e21c8ccb1f45fcdf539989aabfbd5560b1c6de70bea6dc3a5e2486d', '[\"*\"]', NULL, NULL, '2026-06-08 04:40:49', '2026-06-08 04:40:49');

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
  `is_manual` tinyint(1) NOT NULL DEFAULT 0,
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

INSERT INTO `procurement_requests` (`id`, `product_id`, `supplier_id`, `logistics_user_id`, `procurement_user_id`, `finance_user_id`, `quantity`, `price`, `total_amount`, `status`, `receipt_path`, `receipt_uploaded_by`, `receipt_uploaded_at`, `receipt_confirmed`, `receipt_confirmed_by`, `receipt_confirmed_at`, `confirmed_quantity`, `variance_quantity`, `variance_reason`, `variance_reported_at`, `delivery_proof_path`, `is_manual`, `budget_approved`, `supplier_confirmed`, `budget_amount`, `created_at`, `updated_at`, `branch_id`) VALUES
(124, 169, 152, 154, 151, NULL, 10, 40.00, 400.00, 'completed', '/receipts/receipt_124_1775416556.webp', 150, '2026-04-05 19:15:56', 1, 149, '2026-04-05 19:17:31', 10, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_124_1775416848.png', 0, 1, 0, 400.00, '2026-04-05 18:36:51', '2026-04-05 19:20:48', 31),
(125, 166, 152, 154, 151, NULL, 10, 10.00, 100.00, 'completed', '/receipts/receipt_125_1775416516.png', 150, '2026-04-05 19:15:16', 1, 149, '2026-04-05 19:17:43', 9, -1, 'Variance: -1 units', '2026-04-05 19:20:39', '/storage/delivery-proofs/delivery_proof_125_1775416839.png', 0, 1, 0, 100.00, '2026-04-05 18:36:55', '2026-04-05 19:20:39', 31),
(126, 167, 152, 154, 151, NULL, 10, 20.00, 200.00, 'completed', '/receipts/receipt_126_1775416526.jpg', 150, '2026-04-05 19:15:26', 1, 149, '2026-04-05 19:17:36', 10, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_126_1775416825.png', 0, 1, 0, 200.00, '2026-04-05 18:37:07', '2026-04-05 19:20:25', 31),
(127, 168, 152, 154, 151, NULL, 10, 45.00, 450.00, 'completed', '/receipts/receipt_127_1775416539.jpg', 150, '2026-04-05 19:15:39', 1, 149, '2026-04-05 19:17:40', 10, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_127_1775416817.png', 0, 1, 0, 450.00, '2026-04-05 18:37:12', '2026-04-05 19:20:17', 31),
(129, 172, 152, 154, 151, NULL, 10, 200.00, 2000.00, 'completed', '/receipts/receipt_129_1775416570.jpg', 150, '2026-04-05 19:16:10', 1, 149, '2026-04-05 19:17:28', 12, 2, 'Variance: 2 units', '2026-04-05 19:19:59', '/storage/delivery-proofs/delivery_proof_129_1775416799.png', 0, 1, 0, 2000.00, '2026-04-05 18:54:15', '2026-04-05 19:19:59', 31),
(130, 169, NULL, 154, NULL, NULL, 10, 44.00, 440.00, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, '2026-05-23 09:32:48', '2026-05-23 09:32:48', 31),
(131, 169, 152, 150, NULL, 149, 5, 50.00, 250.00, 'pending_order_to_supplier', '/receipts/manual_receipt_131_1779538168.jpg', 150, '2026-05-23 12:09:28', 0, NULL, NULL, NULL, NULL, NULL, NULL, '/product-images/manual_product_131_1779538168.jpg', 0, 1, 0, 250.00, '2026-05-23 12:09:28', '2026-05-23 12:10:52', 31),
(132, 167, 152, 150, NULL, 149, 50, 10.00, 500.00, 'pending_order_to_supplier', '/receipts/manual_receipt_132_1779538741.jpg', 150, '2026-05-23 12:19:01', 0, NULL, NULL, NULL, NULL, NULL, NULL, '/product-images/manual_product_132_1779538741.jpg', 1, 1, 0, 500.00, '2026-05-23 12:19:01', '2026-05-23 12:20:18', 31),
(133, 167, 152, 150, 151, 149, 50, 10.00, 500.00, 'completed', '/receipts/manual_receipt_133_1779539299.jpg', 150, '2026-05-23 12:28:19', 1, 149, '2026-05-23 12:33:34', 50, NULL, NULL, NULL, '/storage/delivery-proofs/delivery_proof_133_1779539750.jpg', 1, 1, 0, 500.00, '2026-05-23 12:28:19', '2026-05-23 12:35:50', 31),
(134, 168, 152, 150, 150, 149, 10, 60.00, 600.00, 'awaiting_inventory_confirmation', '/receipts/manual_receipt_134_1779539561.jpg', 150, '2026-05-23 12:32:41', 1, 149, '2026-05-23 12:33:45', NULL, NULL, NULL, NULL, '/product-images/manual_product_134_1779539561.jpg', 1, 1, 0, 600.00, '2026-05-23 12:32:41', '2026-05-23 12:34:28', 31),
(135, 174, NULL, 154, NULL, NULL, 10, 15.00, 150.00, 'pending', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, NULL, '2026-06-08 05:07:51', '2026-06-08 12:47:01', 31);

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
  `date_made` date DEFAULT NULL,
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

INSERT INTO `products` (`id`, `dish_id`, `name`, `category`, `per_pack_or_individual`, `pack_quantity`, `pack_unit`, `slug`, `created_at`, `updated_at`, `price`, `cost_price`, `stock`, `real_stock`, `open_pack_used`, `expires_at`, `date_made`, `min_stock`, `sku`, `branch_id`, `published_by`, `published_at`, `is_published`, `has_been_ordered`, `is_active`, `is_kitchen_dish`, `is_dish_product`, `supplier_name`, `supplier_id`, `logistics_request_available`, `status`, `requires_logistics`, `approved_by_logistics_main`, `approved_by_owner`, `rejection_reason`, `approved_at`) VALUES
(160, NULL, 'Boneless Chiken', NULL, 'individual', NULL, NULL, 'boneless-chiken-37-1775414111', '2026-04-05 18:35:11', '2026-04-05 18:36:43', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'KITCHEN-DISH-37-3608', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(161, NULL, 'Flour', NULL, 'individual', NULL, NULL, 'flour-37-1775414111', '2026-04-05 18:35:11', '2026-05-23 11:28:41', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'KITCHEN-DISH-37-6080', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(162, NULL, 'Yang Yeom Sauce', NULL, 'individual', NULL, NULL, 'yang-yeom-sauce-37-1775414111', '2026-04-05 18:35:11', '2026-04-05 18:37:12', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'KITCHEN-DISH-37-1383', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(163, NULL, 'Salt', NULL, 'individual', NULL, NULL, 'salt-37-1775414111', '2026-04-05 18:35:11', '2026-04-05 18:37:07', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'KITCHEN-DISH-37-4351', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(164, NULL, 'Pepper', NULL, 'individual', NULL, NULL, 'pepper-37-1775414111', '2026-04-05 18:35:11', '2026-04-05 18:36:55', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'KITCHEN-DISH-37-3054', 31, NULL, NULL, 0, 1, 1, 1, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(165, 37, 'Yang Yeom Chiken', NULL, 'individual', NULL, NULL, 'yang-yeom-chiken', '2026-04-05 18:35:11', '2026-04-14 11:30:53', 16571.25, 12275.00, 0, 0, 0.0000, NULL, NULL, 0, 'YANGYEOM-LNOY', 31, 31, '2026-04-05 18:35:11', 1, 0, 1, 1, 1, NULL, NULL, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(166, NULL, 'Pepper', 'Condiment', 'per_pack', 20.00, 'g', 'pepper', '2026-04-05 18:42:20', '2026-04-05 19:20:39', 11.00, 10.00, 9, 9, 0.0000, '2027-04-06 04:43:00', NULL, 10, 'sku-1775414539-8389', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(167, NULL, 'Salt', 'Condiment', 'per_pack', 1.00, 'kg', 'salt', '2026-04-05 18:43:07', '2026-05-23 12:35:50', 11.00, 10.00, 60, 60, 0.0000, '2027-01-04 02:42:00', NULL, 10, 'sku-1775414587-8650', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(168, NULL, 'Yang Yeom Sauce', 'Other', 'individual', NULL, NULL, 'yang-yeom-sauce', '2026-04-05 18:44:02', '2026-04-05 19:20:17', 49.50, 45.00, 10, 10, 0.0000, '2027-07-06 02:43:00', NULL, 10, 'sku-1775414642-2866', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(169, NULL, 'Flour', 'Other', 'per_pack', 1.00, 'kg', 'flour', '2026-04-05 18:44:43', '2026-05-23 11:28:41', 44.00, 40.00, 5, 10, 0.0000, '2027-01-12 02:44:00', NULL, 10, 'sku-1775414683-7554', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(170, NULL, 'Rice', 'Grain', 'per_pack', NULL, NULL, 'rice', '2026-04-05 18:45:42', '2026-04-05 18:45:42', 60.00, 60.00, 0, 0, 0.0000, '2027-06-12 04:47:00', NULL, 10, 'RICE-JPKT', 31, NULL, NULL, 0, 0, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(172, NULL, 'Boneless Chiken', 'Meat', 'per_pack', 4.00, 'pcs', 'boneless-chiken', '2026-04-05 19:00:31', '2026-04-05 19:19:59', 220.00, 200.00, 12, 12, 0.0000, '2026-11-20 03:00:00', NULL, 10, 'sku-1775415631-1644', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(173, NULL, 'sabon', 'Other', 'both', NULL, NULL, 'sabon', '2026-05-23 09:26:10', '2026-05-23 09:26:10', 25.00, 25.00, 0, 0, 0.0000, '2026-06-02 17:25:00', NULL, 10, 'SABON-FJLT', 31, NULL, NULL, 0, 0, 1, 0, 0, 'John Stalone', 158, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(174, NULL, 'Water', NULL, 'individual', NULL, NULL, 'water-28-1780893781', '2026-06-08 04:43:01', '2026-06-08 05:07:51', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'PRODUCT-REQ-28-7912', 31, NULL, NULL, 1, 1, 1, 0, 0, 'TO BE ASSIGNED', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(175, NULL, 'Water', 'Beverage', 'individual', NULL, NULL, 'water', '2026-06-08 12:47:01', '2026-06-08 12:47:01', 15.00, 15.00, 0, 0, 0.0000, '2027-10-06 20:46:00', '2026-06-01', 10, 'sku-1780922821-9486', 31, NULL, NULL, 1, 0, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL);

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
  `flags` int(11) NOT NULL DEFAULT 0,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `parent_comment_id`, `author`, `text`, `rating`, `ip_address`, `flags`, `is_hidden`, `created_at`, `updated_at`) VALUES
(22, 165, NULL, NULL, 'gab', 'Manok nyo lasang baboy', 2, '127.0.0.1', 1, 1, '2026-06-08 03:49:56', '2026-06-08 03:50:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_comment_flags`
--

CREATE TABLE `product_comment_flags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_comment_id` bigint(20) UNSIGNED NOT NULL,
  `admin_user_id` bigint(20) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_comment_flags`
--

INSERT INTO `product_comment_flags` (`id`, `product_comment_id`, `admin_user_id`, `reason`, `created_at`, `updated_at`) VALUES
(14, 22, 159, 'inappropriate_content', '2026-06-08 03:50:30', '2026-06-08 03:50:30');

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
(27, 'Eggs', NULL, 'pcs', 147, 31, 'pending_approval', 'pending_logistics', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 02:19:33', '2026-06-01 02:19:33'),
(28, 'Water', NULL, 'pcs', 147, 31, 'approved', 'approved', 162, NULL, 31, NULL, NULL, 31, '2026-06-08 04:43:01', NULL, 174, '2026-06-08 04:41:01', '2026-06-08 04:43:01');

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
('ApiU65UWT1uUhvHdezlkclKTvdA9f798ywK3X9WZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaENSN1RkWlJ6aDNVdmwwZFhnQ2tSMTBub3R6NFNSWEEzZXRvZVdGQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9zdGFmZi1sYW5kaW5nIjtzOjU6InJvdXRlIjtOO319', 1780922941);

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
(47, 159, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/159/drug_test_result.jpg', 'account-setup-documents/159/sss_id.jpg', 'account-setup-documents/159/philhealth_id.jpg', NULL, NULL, NULL, '2026-04-15 04:13:25', '2026-04-15 04:13:37'),
(49, 161, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/161/drug_test_result.jpg', 'account-setup-documents/161/sss_id.jpg', 'account-setup-documents/161/philhealth_id.jpg', NULL, NULL, NULL, '2026-04-15 04:17:20', '2026-04-15 04:18:17'),
(50, 31, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 04:18:50', '2026-04-15 04:18:50'),
(51, 147, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/147/drug_test_result.jpg', 'account-setup-documents/147/sss_id.jpg', 'account-setup-documents/147/philhealth_id.jpg', NULL, NULL, NULL, '2026-04-15 05:17:43', '2026-04-15 05:17:56'),
(52, 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 06:22:36', '2026-04-15 06:22:36'),
(53, 150, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/150/drug_test_result.jpg', 'account-setup-documents/150/sss_id.jpg', 'account-setup-documents/150/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-23 09:15:58', '2026-05-23 09:16:23'),
(54, 158, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/158/drug_test_result.jpg', 'account-setup-documents/158/sss_id.jpg', 'account-setup-documents/158/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-23 09:19:21', '2026-05-23 09:20:38'),
(55, 151, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/151/drug_test_result.jpg', 'account-setup-documents/151/sss_id.jpg', 'account-setup-documents/151/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-23 09:29:11', '2026-05-23 09:29:28'),
(56, 149, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/149/drug_test_result.jpg', 'account-setup-documents/149/sss_id.jpg', 'account-setup-documents/149/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-23 12:10:13', '2026-05-23 12:10:26'),
(57, 162, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/162/drug_test_result.jpg', 'account-setup-documents/162/sss_id.jpg', 'account-setup-documents/162/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-23 12:34:48', '2026-06-01 02:20:14'),
(58, 148, NULL, NULL, NULL, NULL, NULL, NULL, 'account-setup-documents/148/drug_test_result.jpg', 'account-setup-documents/148/sss_id.jpg', 'account-setup-documents/148/philhealth_id.jpg', NULL, NULL, NULL, '2026-05-26 08:31:17', '2026-05-26 08:31:35'),
(59, 152, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 03:53:09', '2026-06-08 03:53:09');

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
(2, 152, 'delivery_variance_reported', 'Delivery variance reported for procurement #129', 151, NULL, NULL, NULL, 'warning', NULL, NULL, '{\"procurement_request_id\":129,\"expected_quantity\":10,\"actual_quantity\":12,\"variance\":2}', '2026-04-05 19:19:59', '2026-04-05 19:19:59'),
(3, 152, 'delivery_variance_reported', 'Delivery variance reported for procurement #125', 151, NULL, NULL, NULL, 'warning', NULL, NULL, '{\"procurement_request_id\":125,\"expected_quantity\":10,\"actual_quantity\":9,\"variance\":-1}', '2026-04-05 19:20:39', '2026-04-05 19:20:39');

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
(143, 123, 160, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:38:13', '2026-04-05 18:38:13'),
(144, 124, 169, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 19:20:48', 31, '2026-04-05 18:38:18', '2026-04-05 19:20:48'),
(145, 124, 161, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:38:18', '2026-04-05 18:38:18'),
(146, 127, 168, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 19:20:17', 31, '2026-04-05 18:38:21', '2026-04-05 19:20:17'),
(147, 127, 162, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:38:21', '2026-04-05 18:38:21'),
(148, 126, 167, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 19:20:25', 31, '2026-04-05 18:38:27', '2026-04-05 19:20:25'),
(149, 126, 163, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:38:27', '2026-04-05 18:38:27'),
(150, 125, 166, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 19:20:39', 31, '2026-04-05 18:38:31', '2026-04-05 19:20:39'),
(151, 125, 164, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:38:31', '2026-04-05 18:38:31'),
(154, 129, 172, 152, 10, NULL, 'fulfilled', 0, '2026-04-05 19:19:59', 31, '2026-04-05 18:55:17', '2026-04-05 19:19:59'),
(155, 129, 160, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-04-05 18:55:17', '2026-04-05 18:55:17'),
(156, 135, 175, 152, 10, NULL, 'pending', 1, NULL, 31, '2026-06-08 05:17:23', '2026-06-08 12:47:01'),
(157, 135, 174, 158, 10, NULL, 'pending', 1, NULL, 31, '2026-06-08 05:17:23', '2026-06-08 05:17:23');

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
  `requires_setup` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `required_setup_type` enum('full','documents') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `full_name`, `name`, `password`, `email_verified_at`, `role`, `department`, `permissions`, `branch_id`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `must_change_password`, `requires_setup`, `is_active`, `address`, `latitude`, `longitude`, `avatar_url`, `phone_number`, `required_setup_type`) VALUES
(28, 'superadmin@example.com', 'superadmin', 'Super Administrators', NULL, '$2y$12$f67akYY/xm/H9KJoqytmXeblCxSgZ786slMmHkmqzlMozGUn3Ew7G', '2026-03-05 15:03:55', 'SUPER_ADMIN', NULL, NULL, NULL, NULL, '2026-03-05 15:03:55', '2026-03-05 16:01:06', NULL, 0, 1, 1, NULL, NULL, NULL, '/storage/avatars/avatar_28_1775232518.jpg', NULL, NULL),
(31, 'fowal15977@pmdeal.com', 'Parks', 'Mr.parks', NULL, '$2y$12$/jjxezfu4JAW55dvduVkVu7hpmk5CBXg2GWKtlT17A8jEMfJFpY8y', NULL, 'OWNER', NULL, NULL, NULL, NULL, '2026-03-07 04:30:58', '2026-03-07 04:30:58', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, 'admin', NULL),
(147, 'biteyag645@onbap.com', 'admin_br743957', 'Admin - Dasma Branch', NULL, '$2y$12$8RJHYsL5dHsXJc9.XiEUSuHL8F2DAPRGug1ED5uYU6tZylZwpcjGa', '2026-03-22 11:35:00', 'ADMIN', NULL, NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 11:35:00', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'yawilog234@lxbeta.com', 'hr_br743957', 'HR Manager - Dasma Branch', NULL, '$2y$12$1AUNIAaO6IsU.NgPHyVqfuZmrI7EbeUTnHrku.w9douNkI7STbjfW', '2026-03-22 10:21:24', 'MANAGER', 'HR', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:21:24', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'jiveda9771@lxbeta.com', 'finance_br743957', 'Finance Manager - Dasma Branch', NULL, '$2y$12$9tGgivqfWOXFzZjHh./TNeQnKFXsrvyMHitvfpjtnRH9ctUwYKA5S', '2026-03-22 10:27:01', 'MANAGER', 'FINANCE', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:27:01', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'yenater279@onbap.com', 'procurement_br743957', 'Procurement Manager - Dasma Branch', NULL, '$2y$12$kF..QIb.DvV18ylucsDIHuPPnLwxXpZe4R5/VcwiI5CCZ2XpkCYx.', '2026-03-22 10:33:29', 'MANAGER', 'PROCUREMENT', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 10:33:29', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'rogogaj619@onbap.com', 'logistics_br743957', 'Logistics Manager - Dasma Branch', NULL, '$2y$12$OGJ.SPNyGXorcgKCa/EAaOmnaeqm4sLmZtx6dpGJiijay1uXA6LXq', '2026-03-22 11:37:41', 'MANAGER', 'LOGISTICS', NULL, 31, NULL, '2026-03-22 10:19:22', '2026-03-22 11:37:41', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'xidaya9674@bncinema.com', 'Umberto', 'Umberto Batumbakal', NULL, '$2y$12$eym3kh1XoBRy0Ibjr3qrR.fLvvYUL9bimjdYe.f867drHAYjMURWa', NULL, 'SUPPLIER', NULL, NULL, 31, NULL, '2026-03-22 10:37:58', '2026-03-22 10:38:57', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, '', NULL),
(153, 'mecepey855@lxbeta.com', 'janne', 'Janne De Guzman', NULL, '$2y$12$1SY4y/G62PeI69w/RGa3bu13s/bCYLFpBPEiT8kZD7K.Ebg3UV/ly', '2026-03-22 10:58:41', 'STAFF', 'CASHIER', NULL, 31, NULL, '2026-03-22 10:49:07', '2026-03-22 10:58:41', NULL, 0, 1, 1, '213', NULL, NULL, NULL, '09156818822', NULL),
(154, 'lalaher611@lxbeta.com', 'vince', 'Vince Hannibal Bido', NULL, '$2y$12$.9o2LKOIrQQWcrtHTKCtKOxdGGgLVs41Xr.eaY9OqHDe/iNtJwnUi', '2026-03-22 10:55:12', 'STAFF', 'INVENTORY', NULL, 31, NULL, '2026-03-22 10:50:09', '2026-03-22 10:55:12', NULL, 0, 1, 1, '213', NULL, NULL, NULL, '09156818811', NULL),
(157, 'sevof40551@pmdeal.com', 'charles', 'christian Umbal', NULL, '$2y$12$puxmFYNzX6Yg0Olks0Sa5OWMIPeX6g3H1.RoyJMMkK80b6jdRTe62', '2026-03-23 05:10:23', 'STAFF', 'KITCHEN', NULL, 31, NULL, '2026-03-23 05:07:32', '2026-05-23 09:43:11', NULL, 0, 1, 1, '123', NULL, NULL, NULL, '09156818831', NULL),
(158, 'xiralih141@lxbeta.com', 'John', 'John Stalone', NULL, '$2y$12$LOUp7zlefTxO05AmE6OCAuQtq3vFSVkgFjMUSa2LAyD57UwtF85uC', '2026-03-24 08:58:36', 'SUPPLIER', NULL, NULL, 31, NULL, '2026-03-24 08:57:40', '2026-03-24 08:58:36', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, '', NULL),
(159, 'xecof21486@fun4k.com', 'admin_main_branch', 'Admin Main Branch', NULL, '$2y$12$0mEgYuYslnBkJs79fi7H0OjiH3uTrkDbe2Kvas21ltIJ9BExNMm0C', '2026-03-25 07:07:57', 'ADMIN', NULL, NULL, 32, NULL, '2026-03-25 06:56:11', '2026-03-25 07:07:57', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'radalol730@fun4k.com', 'hr_main_branch', 'HR Main Branch', NULL, '$2y$12$7XE7ggeRv7J3xUcTOgf6qOvZEPs4A8ppDdHk8zb3VxzTggE3EUI2i', '2026-03-25 07:17:25', 'MANAGER', 'HR', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:17:25', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'tavej98512@fabaos.com', 'finance_main_branch', 'Finance Main Branch', NULL, '$2y$12$Hb8m1H0Iau3u0jFJ0Spr5uqsg2Fl0kk.2QbHUAKQ.iMtZ9zKuBpra', '2026-03-25 07:20:21', 'MANAGER', 'FINANCE', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:20:21', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'lefet30141@fun4k.com', 'logistics_main_branch', 'Logistics Main Branch', NULL, '$2y$12$5jgXzzPw90ASb/4TuQnmreNk6jVKcPGX6ELJy6C9yfDFNv67u2j6u', '2026-03-25 07:22:05', 'MANAGER', 'LOGISTICS', NULL, 32, NULL, '2026-03-25 06:56:12', '2026-03-25 07:22:05', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(214, NULL, 'admin_br216033', 'Admin - test', NULL, '$2y$12$Cm6def5Kq4NElULrwrZ8Gezo.kS/sUkMFhFgmbeXjxCMqIZH8yO2y', NULL, 'ADMIN', NULL, NULL, 46, NULL, '2026-04-05 19:44:55', '2026-04-05 19:47:48', NULL, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(215, NULL, 'custom_br216033', 'Custom Account - test', NULL, '$2y$12$BF3h6L52Cu0s6InQ/qYRROAH2WwjwLFUZhcOtnYcdyOonE..4lhGm', NULL, 'CUSTOM', NULL, '{\"modules\":[\"admin\",\"finance\",\"logistics\",\"inventory\",\"procurement\",\"kitchen\",\"cashier\",\"hr\"],\"functions\":[]}', 46, NULL, '2026-04-05 19:44:55', '2026-04-05 19:48:39', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
(231, NULL, 'admin_br687660', 'Admin - San Simon Branch', NULL, '$2y$12$o1LJ..GU13mGlpDOg2gaVefQClGNLWvBEcduocg3GbYOVcgQBiHdq', NULL, 'ADMIN', NULL, NULL, 47, NULL, '2026-04-14 14:13:36', '2026-04-14 14:13:36', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(232, NULL, 'hr_br687660', 'HR Manager - San Simon Branch', NULL, '$2y$12$SUL0/MEe3ejLNwvpQA0uZ.Ji8pZuKQK4ogNeQ1h/YhMK0vvJP7.lm', NULL, 'MANAGER', 'HR', NULL, 47, NULL, '2026-04-14 14:13:36', '2026-04-14 14:13:36', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(233, NULL, 'finance_br687660', 'Finance Manager - San Simon Branch', NULL, '$2y$12$3flgZyKGpB4fHVCzjrn.Y.K2KQ6Ae.x/KHnZYfOIPIBi.T67wJy5.', NULL, 'MANAGER', 'FINANCE', NULL, 47, NULL, '2026-04-14 14:13:36', '2026-04-14 14:13:36', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(234, NULL, 'procurement_br687660', 'Procurement Manager - San Simon Branch', NULL, '$2y$12$GhDK5a/xOz6KlieTdsUmqOwHcE22vH0KnibWJCtaiSneiz0zgAWFy', NULL, 'MANAGER', 'PROCUREMENT', NULL, 47, NULL, '2026-04-14 14:13:36', '2026-04-14 14:13:36', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(235, NULL, 'logistics_br687660', 'Logistics Manager - San Simon Branch', NULL, '$2y$12$BFfjh5oLhi0C/ZaKaxuhOuwnvyvYg9F1Xi6uLABUiYmKq6BqEIbm.', NULL, 'MANAGER', 'LOGISTICS', NULL, 47, NULL, '2026-04-14 14:13:36', '2026-04-14 14:13:36', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL),
(240, 'xmusics77@gmail.com', 'gab', NULL, NULL, '$2y$12$dVtcSpO1fUxFFNw/73Y8L.UUGdHlHvYH0v8a7iYFxLdnnH75rwKxa', '2026-05-26 09:34:09', 'customer', NULL, NULL, NULL, NULL, '2026-05-26 09:34:09', '2026-05-26 09:34:09', NULL, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `product_comment_flags`
--
ALTER TABLE `product_comment_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_comment_flags_product_comment_id_index` (`product_comment_id`),
  ADD KEY `product_comment_flags_admin_user_id_index` (`admin_user_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance_settings`
--
ALTER TABLE `attendance_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `budget_requests`
--
ALTER TABLE `budget_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `dish_ingredients`
--
ALTER TABLE `dish_ingredients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `logistics_transactions`
--
ALTER TABLE `logistics_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2493;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `product_comment_flags`
--
ALTER TABLE `product_comment_flags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_requests`
--
ALTER TABLE `product_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `supplier_audit_logs`
--
ALTER TABLE `supplier_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `supplier_orders`
--
ALTER TABLE `supplier_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=241;

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
-- Constraints for table `product_comment_flags`
--
ALTER TABLE `product_comment_flags`
  ADD CONSTRAINT `product_comment_flags_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_comment_flags_product_comment_id_foreign` FOREIGN KEY (`product_comment_id`) REFERENCES `product_comments` (`id`) ON DELETE CASCADE;

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
