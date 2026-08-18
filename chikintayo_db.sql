-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 07:59 AM
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
(187, NULL, 'bread', NULL, 'individual', NULL, NULL, 'bread-29-1786093843', '2026-08-07 09:10:43', '2026-08-07 09:11:08', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'PRODUCT-REQ-29-3583', 31, NULL, NULL, 1, 1, 1, 0, 0, 'TO BE ASSIGNED', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(188, NULL, 'bread', 'Grain', 'per_pack', 6.00, 'pcs', 'bread', '2026-08-07 09:13:28', '2026-08-07 09:35:05', 88.00, 80.00, 10, 10, 0.0000, NULL, '2026-08-15', 10, 'sku-1786094008-4017', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(189, NULL, 'frozen hotdog (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'frozen-hotdog-dish-ingredient-41-1786098208', '2026-08-07 10:23:28', '2026-08-07 10:39:19', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'ING-41-KADJ2M', 31, NULL, NULL, 0, 1, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(190, NULL, 'Hotdog Buns (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'hotdog-buns-dish-ingredient-41-1786098208', '2026-08-07 10:23:28', '2026-08-07 10:39:21', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'ING-41-CU6QVC', 31, NULL, NULL, 0, 1, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(191, NULL, 'frozen hotdog (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'frozen-hotdog-dish-ingredient-41-32', '2026-08-07 10:23:28', '2026-08-07 10:23:28', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'DISH-41-ING-0-B32', 32, NULL, NULL, 0, 0, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(192, NULL, 'Hotdog Buns (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'hotdog-buns-dish-ingredient-41-32', '2026-08-07 10:23:28', '2026-08-07 10:23:28', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'DISH-41-ING-1-B32', 32, NULL, NULL, 0, 0, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(193, NULL, 'frozen hotdog (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'frozen-hotdog-dish-ingredient-41-48', '2026-08-07 10:23:28', '2026-08-07 10:23:28', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'DISH-41-ING-0-B48', 48, NULL, NULL, 0, 0, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(194, NULL, 'Hotdog Buns (Dish Ingredient)', NULL, 'individual', NULL, NULL, 'hotdog-buns-dish-ingredient-41-48', '2026-08-07 10:23:28', '2026-08-07 10:23:28', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 10, 'DISH-41-ING-1-B48', 48, NULL, NULL, 0, 0, 1, 0, 0, 'KITCHEN', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(195, 41, 'hotdog', NULL, 'individual', NULL, NULL, 'hotdog', '2026-08-07 10:23:28', '2026-08-12 05:10:22', 70.20, 52.00, 10, 0, 0.0000, NULL, NULL, 0, 'HOTDOG-ZZ4Z', 31, 31, '2026-08-07 10:23:28', 1, 0, 1, 1, 1, NULL, NULL, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(198, NULL, 'Hotdog Buns (Dish Ingredient)', 'Grain', 'per_pack', 6.00, 'pcs', 'hotdog-buns-dish-ingredient', '2026-08-07 11:21:45', '2026-08-12 05:10:22', 132.00, 120.00, 4, 10, 4.0000, NULL, '2026-08-06', 10, 'sku-1786101705-4180', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(199, NULL, 'frozen hotdog (Dish Ingredient)', 'Meat', 'per_pack', 5.00, 'pcs', 'frozen-hotdog-dish-ingredient', '2026-08-07 11:22:58', '2026-08-12 05:10:22', 176.00, 160.00, 2, 10, 0.0000, NULL, '2026-08-05', 10, 'sku-1786101778-6995', 31, NULL, NULL, 1, 1, 1, 0, 0, 'Umberto Batumbakal', 152, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(202, NULL, 'water', NULL, 'individual', NULL, NULL, 'water-31-1786945425', '2026-08-17 05:43:45', '2026-08-17 05:46:20', 0.00, 0.00, 0, 0, 0.0000, NULL, NULL, 0, 'PRODUCT-REQ-31-4285', 31, NULL, NULL, 1, 1, 1, 0, 0, 'TO BE ASSIGNED', NULL, 1, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(203, NULL, 'water', 'Beverage', 'individual', NULL, NULL, 'water', '2026-08-17 05:45:58', '2026-08-18 05:58:47', 13.20, 12.00, 0, 0, 0.0000, NULL, '2026-08-03', 10, 'sku-1786945558-5282', 31, NULL, NULL, 1, 1, 1, 0, 0, 'John Stalone', 158, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL),
(204, NULL, 'water', 'Beverage', 'individual', NULL, NULL, 'water-1', '2026-08-17 05:57:21', '2026-08-18 05:58:47', 15.00, 15.00, 10, 10, 0.0000, NULL, '2026-08-11', 10, 'sku-1786946241-2848', 31, NULL, NULL, 1, 0, 1, 0, 0, 'John Stalone', 158, 0, 'pending_owner', 0, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `1` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
