-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 01, 2026 at 04:41 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u211483608_sdesigner`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`u211483608_s`@`127.0.0.1` FUNCTION `generate_order_number` () RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DETERMINISTIC BEGIN
    DECLARE new_number VARCHAR(50);
    SET new_number = CONCAT('PO-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', 
                           LPAD((SELECT COUNT(*) + 1 FROM purchase_orders 
                                WHERE DATE(created_at) = CURDATE()), 4, '0'));
    RETURN new_number;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT 'fas fa-folder'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'MEN’S ETHNIC WEAR', 'mens\'-ethnic-wear', 'fas fa-folder'),
(3, 'WESTERN & INDO-WESTERN', 'western-&-indo-western', 'fas fa-folder'),
(4, 'CUSTOM STITCHING', 'custom-stitching', 'fa fa-cut'),
(5, 'EMBROIDERY SERVICE', 'Embroidery-Services', 'fa fa-scissors'),
(6, 'NEW ARRIVALS', 'new-arrival', 'fas fa-folder'),
(8, 'ACCESSORIES', 'accessories', 'fas fa-gem'),
(11, 'UNSTITCHED SUITS', 'unstitched-suits', 'fas fa-folder'),
(12, 'TRADITIONAL WEAR', 'traditional-wear', 'fas fa-folder'),
(13, 'READY TO WEAR', 'ready-to-wear', 'fas fa-folder'),
(14, 'Pagri (Full Voile)', 'pagri-full-voile', 'fas fa-crown');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `hex_code` varchar(7) DEFAULT '#000000',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `hex_code`, `created_at`) VALUES
(1, 'Red', '#FF0000', '2025-12-11 11:56:51'),
(2, 'Blue', '#0000FF', '2025-12-11 11:56:51'),
(3, 'Green', '#00FF00', '2025-12-11 11:56:51'),
(4, 'Black', '#000000', '2025-12-11 11:56:51'),
(5, 'White', '#FFFFFF', '2025-12-11 11:56:51'),
(6, 'Gold', '#FFD700', '2025-12-11 11:56:51'),
(7, 'Silver', '#C0C0C0', '2025-12-11 11:56:51'),
(8, 'Pink', '#FFC0CB', '2025-12-11 11:56:51'),
(9, 'Purple', '#800080', '2025-12-11 11:56:51'),
(10, 'Maroon', '#800000', '2025-12-11 11:56:51'),
(12, 'Royal', '#3e6879', '2025-12-11 13:56:02'),
(13, 'Cyan', '#00ffff', '2025-12-17 11:18:11'),
(14, 'Grey', '#808080', '2025-12-18 13:41:12'),
(15, 'Brown', '#964b00', '2025-12-18 13:50:41'),
(16, 'Yellow', '#fae505', '2025-12-18 14:04:54'),
(17, 'Sap Green', '#507d2a', '2025-12-20 10:23:22'),
(18, 'Deep Navy Blue', '#0f204e', '2025-12-30 06:06:33'),
(19, 'White', '#ffffff', '2025-12-30 06:13:33'),
(20, 'Off-White/Cream', '#f5f5dc', '2025-12-30 06:14:06'),
(21, 'Royal Blue', '#1f3c88', '2025-12-30 06:15:22'),
(22, 'Sky Blue', '#87ceeb', '2025-12-30 06:18:41'),
(23, 'Maroon', '#800000', '2025-12-30 06:19:04'),
(24, 'Wine', '#722f37', '2025-12-30 06:19:24'),
(25, 'Burgundy', '#5c1a1b', '2025-12-30 06:19:49'),
(26, 'Rani Pink', '#c2185b', '2025-12-30 06:20:24'),
(27, 'Deep Red', '#8b0000', '2025-12-30 06:20:33'),
(28, 'Mustard', '#d4a017', '2025-12-30 06:20:51'),
(29, 'Kesrai / Saffron', '#ff9933', '2025-12-30 06:22:09'),
(30, 'Bottle Green', '#0b3d2e', '2025-12-30 06:22:36'),
(31, 'Olive Green', '#556b2f', '2025-12-30 06:23:00'),
(32, 'Charcoal Grey', '#36454f', '2025-12-30 06:23:34'),
(33, 'Ash Grey', '#b2beb5', '2025-12-30 06:23:53'),
(34, 'Steel Grey', '#71797e', '2025-12-30 06:24:11'),
(35, 'Pastel Peach', '#fadadd', '2025-12-30 06:24:44'),
(36, 'Pastel Lavender', '#c3b1e1', '2025-12-30 06:25:04'),
(37, 'Midnight Blue', '#191970', '2025-12-30 06:27:58'),
(38, 'Prussian Blue', '#003153', '2025-12-30 06:28:18'),
(39, 'Ink Blue', '#1c2e4a', '2025-12-30 06:29:26'),
(40, 'Aubergine', '#3b0918', '2025-12-30 06:29:55'),
(41, 'Rosewood', '#65000b', '2025-12-30 06:30:18'),
(42, 'Blood Red', '#660000', '2025-12-30 06:30:41'),
(43, 'Haldi Yellow', '#ffb300', '2025-12-30 06:31:16'),
(44, 'Antique Gold', '#c9a227', '2025-12-30 06:31:29'),
(45, 'Rust Orange', '#b7410e', '2025-12-30 06:31:49'),
(46, 'Copper Brown', '#7c482b', '2025-12-30 06:32:09'),
(47, 'Mehndi Green', '#6b8e23', '2025-12-30 06:32:37'),
(48, 'Peacock Green', '#005f56', '2025-12-30 06:32:57'),
(49, 'Slate Grey', '#708090', '2025-12-30 06:33:18'),
(50, 'Taupe', '#483c32', '2025-12-30 06:33:34'),
(51, 'Sand Beige', '#d8cfc4', '2025-12-30 06:33:52'),
(52, 'Chocolate Brown', '#3f000f', '2025-12-30 06:34:12'),
(53, 'Steel Grey', '#71797e', '2025-12-30 06:34:30'),
(54, 'Powder Blue', '#b0e0e6', '2025-12-30 06:34:52'),
(55, 'Blush Pink', '#f2acb9', '2025-12-30 06:35:21'),
(56, 'Pistachio Green', '#93c572', '2025-12-30 06:35:33'),
(57, 'Mint Green', '#98ff98', '2025-12-30 06:35:53'),
(58, 'Lavender Mist', '#e6e6fa', '2025-12-30 06:36:23'),
(59, 'Sapphire Blue', '#0f52ba', '2025-12-30 06:37:54'),
(60, 'Oxford Blue', '#002147', '2025-12-30 06:38:17'),
(61, 'Imperial Purple', '#3c1361', '2025-12-30 06:38:59'),
(62, 'Garnet Red', '#733635', '2025-12-30 06:39:24'),
(63, 'Mahogany', '#4a0100', '2025-12-30 06:39:43'),
(64, 'Bronze Gold', '#cd7f32', '2025-12-30 06:40:02'),
(65, 'Champagne', '#f7e7ce', '2025-12-30 06:40:24'),
(66, 'Pearl Grey', '#eae0c8', '2025-12-30 06:40:44'),
(67, 'Rose Gold', '#b76e79', '2025-12-30 06:41:05'),
(68, 'Dusty Rose', '#c08081', '2025-12-30 06:41:22'),
(69, 'Soft Gold', '#e6be8a', '2025-12-30 06:41:51'),
(70, 'Clay Brown', '#8a4b2d', '2025-12-30 06:42:10'),
(71, 'Terracotta', '#e2725b', '2025-12-30 06:42:29'),
(72, 'Coffee Brown', '#4b3621', '2025-12-30 06:42:49'),
(73, 'Moss Green', '#556b2f', '2025-12-30 06:43:17'),
(74, 'Forest Green', '#014421', '2025-12-30 06:43:33'),
(75, 'Ice Blue', '#afdbf5', '2025-12-30 06:43:53'),
(76, 'Cloud Grey', '#d3d3d3', '2025-12-30 06:44:24'),
(77, 'Sage Green', '#9caf88', '2025-12-30 06:44:43'),
(78, 'Butter Yellow', '#fff1a8', '2025-12-30 06:44:54'),
(79, 'Lilac', '#c8a2c8', '2025-12-30 06:45:24');

-- --------------------------------------------------------

--
-- Table structure for table `marquee_messages`
--

CREATE TABLE `marquee_messages` (
  `id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `badge_text` varchar(50) DEFAULT NULL,
  `badge_color` varchar(50) DEFAULT 'secondary',
  `icon_class` varchar(100) DEFAULT 'fas fa-bullhorn',
  `icon_color` varchar(50) DEFAULT 'secondary',
  `priority` int(11) DEFAULT 1 COMMENT '1=Low, 2=Medium, 3=High',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marquee_messages`
--

INSERT INTO `marquee_messages` (`id`, `message_text`, `badge_text`, `badge_color`, `icon_class`, `icon_color`, `priority`, `display_order`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'NEW COLLECTION LAUNCHED! Get 20% off on all bridal lehengas', 'NEW', 'secondary', 'fas fa-fire', 'secondary', 3, 1, 1, NULL, NULL, '2025-12-19 06:03:30', '2025-12-19 06:08:32'),
(2, 'Exclusive Sale! Buy 2 sarees, get 1 dupatta free', 'SALE', 'secondary', 'fas fa-gift', 'secondary', 3, 2, 1, NULL, NULL, '2025-12-19 06:03:30', '2025-12-19 06:03:30'),
(3, 'Free shipping on orders above ₹5000', 'FREE SHIPPING', 'secondary', 'fas fa-truck', 'secondary', 2, 3, 1, NULL, NULL, '2025-12-19 06:03:30', '2025-12-19 06:03:30'),
(4, 'Custom tailoring available on all unstitched fabrics', 'CUSTOM', 'secondary', 'fas fa-star', 'secondary', 1, 4, 1, NULL, NULL, '2025-12-19 06:03:30', '2025-12-19 06:03:30'),
(6, 'LOHRI OFFER', 'SALE', 'secondary', 'fas fa-gift', 'secondary', 2, 5, 1, '2025-12-19', '0000-00-00', '2025-12-19 06:10:04', '2025-12-19 06:13:24');

-- --------------------------------------------------------

--
-- Table structure for table `meta_tags`
--

CREATE TABLE `meta_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('occasion','fabric','style','work') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meta_tags`
--

INSERT INTO `meta_tags` (`id`, `name`, `slug`, `type`, `created_at`) VALUES
(1, 'Wedding', 'wedding', 'occasion', '2025-12-11 13:13:16'),
(2, 'Party', 'party', 'occasion', '2025-12-11 13:13:16'),
(3, 'Casual', 'casual', 'occasion', '2025-12-11 13:13:16'),
(4, 'Formal', 'formal', 'occasion', '2025-12-11 13:13:16'),
(5, 'Silk', 'silk', 'fabric', '2025-12-11 13:13:16'),
(6, 'Cotton', 'cotton', 'fabric', '2025-12-11 13:13:16'),
(7, 'Georgette', 'georgette', 'fabric', '2025-12-11 13:13:16'),
(8, 'Chiffon', 'chiffon', 'fabric', '2025-12-11 13:13:16'),
(9, 'Embroidery', 'embroidery', 'work', '2025-12-11 13:13:16'),
(10, 'Zari Work', 'zari-work', 'work', '2025-12-11 13:13:16'),
(11, 'Stone Work', 'stone-work', 'work', '2025-12-11 13:13:16'),
(12, 'Traditional', 'traditional', 'style', '2025-12-11 13:13:16'),
(13, 'Contemporary', 'contemporary', 'style', '2025-12-11 13:13:16');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_city` varchar(50) DEFAULT NULL,
  `customer_state` varchar(50) DEFAULT NULL,
  `customer_pincode` varchar(10) DEFAULT NULL,
  `customer_notes` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `payment_status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `customer_city`, `customer_state`, `customer_pincode`, `customer_notes`, `payment_method`, `total_amount`, `order_date`, `status`, `payment_status`, `created_at`) VALUES
(1, 'SD-20251219103833-93C577', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdafdgdgdfgdfg', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 10:38:33', 'paid', 'pending', '2025-12-19 09:38:33'),
(2, 'SD-20251219104109-5618EE', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdahdthhhhdfg', 'Moga', 'Punjab', '142001', '', 'phonepe', 3000.00, '2025-12-19 10:41:09', 'pending', 'pending', '2025-12-19 09:41:09'),
(3, 'SD-20251219110834-27F463', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdasdsdsdsdsdsd', 'Moga', 'Punjab', '142001', 'f', 'phonepe', 4000.00, '2025-12-19 11:08:34', 'pending', 'pending', '2025-12-19 10:08:34'),
(4, 'SD-20251219110906-2813C6', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdaffffffffffffffffffff', 'Moga', 'Punjab', '142001', '', 'phonepe', 1000.00, '2025-12-19 11:09:06', 'pending', 'pending', '2025-12-19 10:09:06'),
(5, 'SD-20251219111056-0160C7', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdaddddddddddddddddddddd', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 11:10:56', 'pending', 'pending', '2025-12-19 10:10:56'),
(6, 'SD-20251219104725-D8A11D', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdasgsdgsdgs', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 10:47:25', 'pending', 'pending', '2025-12-19 10:47:25'),
(7, 'SD-20251219111515-391561', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdasgsdgsdgs', 'Moga', 'Punjab', '142001', '', 'phonepe', 3000.00, '2025-12-19 11:15:15', 'paid', 'pending', '2025-12-19 11:15:15'),
(8, 'SD-20251219125928-0C3539', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 12:59:28', 'pending', 'pending', '2025-12-19 12:59:28'),
(9, 'SD-20251219130031-FCF1E0', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdasgsdgsdgs', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 13:00:31', 'pending', 'pending', '2025-12-19 13:00:31'),
(10, 'SD-20251219135328-8DF48C', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'sdasgsdgsdgs', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-19 13:53:28', 'pending', 'pending', '2025-12-19 13:53:28'),
(11, 'SD-20251219135537-95C009', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', '', 'phonepe', 3000.00, '2025-12-19 13:55:37', 'pending', 'pending', '2025-12-19 13:55:37'),
(12, 'SD-20251219141139-B67891', 'Laiba', 'laibasaifi458@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', 'Please Stich it', 'phonepe', 3000.00, '2025-12-19 14:11:39', 'pending', 'pending', '2025-12-19 14:11:39'),
(13, 'SD-20251219141406-E19655', 'Saifi', 'drmssaifi@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', 'I need premium quality', 'phonepe', 5000.00, '2025-12-19 14:14:06', 'pending', 'pending', '2025-12-19 14:14:06'),
(14, 'SD-20251219141506-A6C58B', 'Saifi', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', 'Premium quality', 'phonepe', 1000.00, '2025-12-19 14:15:06', 'pending', 'pending', '2025-12-19 14:15:06'),
(15, 'SD-20251220081702-E341DE', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-20 08:17:02', 'pending', 'failed', '2025-12-20 08:17:02'),
(16, 'SD-20251220081916-46BA9A', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', '', 'phonepe', 4000.00, '2025-12-20 08:19:16', 'pending', 'failed', '2025-12-20 08:19:16'),
(17, 'SD-20251220082458-AEE345', 'SEEMA', 'cccomputermoga@gmail.com', '9988320674', 'moga', 'Moga', 'Punjab', '142001', '', 'phonepe', 10.00, '2025-12-20 08:24:58', 'pending', 'failed', '2025-12-20 08:24:58'),
(18, 'SD-20251220082853-5C0B2A', 'Aks', 'akssaifi95@gmail.com', '9417628634', 'Moga, Moga Tahsil, Moga, Punjab, 142001, India', 'Moga', 'Punjab', '142001', '', 'phonepe', 10.00, '2025-12-20 08:28:53', 'pending', 'failed', '2025-12-20 08:28:53'),
(19, 'SD-20251225102248-8B641C', 'Daljeet singh', 'daljeetjal81@gmail.com', '9814927250', 'jalandhar', 'jalandhar', 'Punjab', '144002', '', 'phonepe', 0.50, '2025-12-25 10:22:48', 'pending', 'failed', '2025-12-25 10:22:48'),
(20, 'TEST-20251225111044-ORDER', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'This is a test order', 'phonepe', 5999.98, '2025-12-25 11:10:44', 'pending', 'pending', '2025-12-25 11:10:44'),
(21, 'TEST-20251225111210-ORDER', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'This is a test order', 'phonepe', 5999.98, '2025-12-25 11:12:10', 'pending', 'pending', '2025-12-25 11:12:10'),
(22, 'TEST-20251225111212-ORDER', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'This is a test order', 'phonepe', 5999.98, '2025-12-25 11:12:12', 'pending', 'pending', '2025-12-25 11:12:12'),
(23, 'TEST-20251225111213-ORDER', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'This is a test order', 'phonepe', 5999.98, '2025-12-25 11:12:13', 'pending', 'pending', '2025-12-25 11:12:13'),
(26, 'TEST-20251225111214-ORDER', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'This is a test order', 'phonepe', 5999.98, '2025-12-25 11:12:14', 'pending', 'pending', '2025-12-25 11:12:14'),
(27, 'TEST-20251225113630', 'Test Customer', 'customer@example.com', '9876543210', '123 Test Street, Test City', 'Jalandhar', 'Punjab', '144001', 'Test order for email verification', 'phonepe', 3999.98, '2025-12-25 11:36:30', 'pending', 'pending', '2025-12-25 11:36:30'),
(29, 'TEST-20251225115213', 'Test Customer', 'test@example.com', '9876543210', '123 Test Street, Test Area', 'Jalandhar', 'Punjab', '144001', 'Test order for email verification', 'phonepe', 3999.98, '2025-12-25 11:52:13', 'completed', 'success', '2025-12-25 11:52:13'),
(30, 'TEST-20251225115322', 'Test Customer', 'test@example.com', '9876543210', '123 Test Street, Test Area', 'Jalandhar', 'Punjab', '144001', 'Test order for email verification', 'phonepe', 3999.98, '2025-12-25 11:53:22', 'completed', 'success', '2025-12-25 11:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `stitching_option` varchar(20) DEFAULT NULL,
  `stitch_charges` decimal(10,2) DEFAULT 0.00,
  `selected_meters` decimal(5,2) DEFAULT NULL,
  `is_unstitched` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `quantity`, `price`, `total_price`, `stitching_option`, `stitch_charges`, `selected_meters`, `is_unstitched`) VALUES
(1, 'SD-20251219103833-93C577', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(2, 'SD-20251219104109-5618EE', 'Green Colour Saree For Women', 1, 3000.00, 3000.00, NULL, 0.00, NULL, 0),
(3, 'SD-20251219110834-27F463', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(4, 'SD-20251219110906-2813C6', 'Yellow colured Salwar Suit', 1, 1000.00, 1000.00, NULL, 0.00, NULL, 0),
(5, 'SD-20251219111056-0160C7', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(6, 'SD-20251219104725-D8A11D', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(7, 'SD-20251219111515-391561', 'Green Colour Saree For Women', 1, 3000.00, 3000.00, NULL, 0.00, NULL, 0),
(8, 'SD-20251219125928-0C3539', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(9, 'SD-20251219130031-FCF1E0', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(10, 'SD-20251219135328-8DF48C', 'Pink colur Saree For Girls', 1, 4000.00, 4000.00, NULL, 0.00, NULL, 0),
(11, 'SD-20251219135537-95C009', 'Green Colour Saree For Women', 1, 3000.00, 3000.00, NULL, 0.00, NULL, 0),
(12, 'SD-20251219141139-B67891', 'Green Colour Saree For Women', 1, 3000.00, 3000.00, NULL, 0.00, NULL, 0),
(13, 'SD-20251219141406-E19655', 'Maroon colour Salwar Suit', 1, 5000.00, 5000.00, NULL, 0.00, NULL, 0),
(14, 'SD-20251219141506-A6C58B', 'Yellow colured Salwar Suit', 1, 1000.00, 1000.00, NULL, 0.00, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `status` varchar(20) DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `phonepe_transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_id`, `amount`, `currency`, `status`, `payment_method`, `phonepe_transaction_id`, `created_at`) VALUES
(1, 'SD-20251219103833-93C577', NULL, 4000.00, 'INR', 'pending', 'phonepe', NULL, '2025-12-19 09:38:33'),
(2, 'SD-20251219104109-5618EE', NULL, 3000.00, 'INR', 'pending', 'phonepe', NULL, '2025-12-19 09:41:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_type` enum('stitched','unstitched','accessory') NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_per_meter` decimal(10,2) DEFAULT NULL,
  `length_in_meters` decimal(10,2) DEFAULT NULL,
  `stitch_charges` decimal(10,2) DEFAULT 0.00,
  `category_id` int(11) DEFAULT NULL,
  `size_chart_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reorder_level` int(11) DEFAULT 10,
  `stock_status` enum('in_stock','low_stock','out_of_stock') DEFAULT 'in_stock',
  `last_restocked` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_type`, `name`, `description`, `price`, `price_per_meter`, `length_in_meters`, `stitch_charges`, `category_id`, `size_chart_id`, `stock_quantity`, `is_active`, `created_at`, `reorder_level`, `stock_status`, `last_restocked`) VALUES
(17, 'accessory', 'GIVA Women\'s Classic Solitaire Ring', NULL, 3900.00, NULL, NULL, 0.00, 8, NULL, 65, 1, '2025-12-18 11:15:20', 5, 'in_stock', NULL),
(20, 'stitched', 'Ethnic Set Women Block Printed Kurta set with Dupatta', NULL, 5000.00, NULL, NULL, 0.00, 5, NULL, 34, 1, '2025-12-18 13:23:02', 5, 'in_stock', NULL),
(21, 'accessory', 'Bracelet', NULL, 200.00, NULL, NULL, 0.00, 8, NULL, 90, 1, '2025-12-18 13:33:23', 5, 'in_stock', NULL),
(24, 'accessory', 'Chain', NULL, 2000.00, NULL, NULL, 0.00, 8, NULL, 29, 1, '2025-12-18 13:36:33', 5, 'in_stock', NULL),
(25, 'accessory', 'Necklace', NULL, 3000.00, NULL, NULL, 0.00, 8, NULL, 30, 1, '2025-12-18 13:37:36', 5, 'in_stock', NULL),
(26, 'stitched', 'Blazers for men', NULL, 6000.00, NULL, NULL, 0.00, 11, NULL, 12, 1, '2025-12-18 13:41:53', 5, 'in_stock', NULL),
(27, 'stitched', 'Blazers for women', NULL, 7000.00, NULL, NULL, 0.00, 11, NULL, 17, 1, '2025-12-18 13:42:42', 5, 'in_stock', NULL),
(28, 'stitched', 'SHUZHXLZANGY Mens Blazers and Sport Coats Slim Fit', NULL, 5000.00, NULL, NULL, 0.00, 11, NULL, 40, 1, '2025-12-18 13:44:04', 5, 'in_stock', NULL),
(32, 'stitched', 'Gown', NULL, 4000.00, NULL, NULL, 0.00, 4, NULL, 10, 1, '2025-12-18 13:55:50', 5, 'in_stock', NULL),
(33, 'stitched', 'Royal Blue Gown for women', NULL, 7000.00, NULL, NULL, 0.00, 4, NULL, 30, 1, '2025-12-18 13:56:59', 5, 'in_stock', NULL),
(34, 'stitched', 'Black Colour Gown for Females', NULL, 6000.00, NULL, NULL, 0.00, 4, NULL, 60, 1, '2025-12-18 13:57:45', 5, 'in_stock', NULL),
(35, 'stitched', 'Pure white Gown for Women', NULL, 4000.00, NULL, NULL, 0.00, 4, NULL, 30, 1, '2025-12-18 13:58:44', 5, 'in_stock', NULL),
(36, 'stitched', 'Cherry color Lehanga for girls', NULL, 10000.00, NULL, NULL, 0.00, 1, NULL, 23, 1, '2025-12-18 14:00:51', 5, 'in_stock', NULL),
(38, 'stitched', 'Multi colour Lehanga for Women', NULL, 10000.00, NULL, NULL, 0.00, 1, NULL, 20, 1, '2025-12-18 14:03:20', 5, 'in_stock', NULL),
(39, 'stitched', 'Plazo Suit for Women', NULL, 1000.00, NULL, NULL, 0.00, 12, NULL, 12, 1, '2025-12-18 14:05:42', 5, 'in_stock', NULL),
(40, 'stitched', 'Full Plazo Suit for women', NULL, 1000.00, NULL, NULL, 0.00, 12, NULL, 32, 1, '2025-12-18 14:06:25', 5, 'in_stock', NULL),
(41, 'stitched', 'Maroon colour Salwar Suit', NULL, 5000.00, NULL, NULL, 0.00, 3, NULL, 12, 1, '2025-12-18 14:07:44', 5, 'in_stock', NULL),
(42, 'stitched', 'Yellow colured Salwar Suit', NULL, 1000.00, NULL, NULL, 0.00, 3, NULL, 20, 1, '2025-12-18 14:08:17', 5, 'in_stock', NULL),
(49, 'stitched', 'Pashmina Kurti', 'Experience timeless elegance with our exquisite Pashmina Kurti in a sophisticated sap green hue. Meticulously crafted from premium pashmina wool, this kurti embodies the perfect fusion of traditional craftsmanship and contemporary design sensibilities.\r\n\r\nPremium Quality Fabric:\r\n\r\n100% genuine pashmina wool for exceptional softness and warmth\r\n\r\nLightweight yet insulating, perfect for year-round wear\r\n\r\nNatural breathability ensures comfort throughout the day\r\n\r\nLuxurious drape that complements every body type\r\n\r\nDesign Excellence:\r\n\r\nA-line silhouette with subtle flared hem for graceful movement\r\n\r\nExpertly tailored neckline with delicate piping detail\r\n\r\nFull-length sleeves with gentle gathers at the cuffs\r\n\r\nSide slits for enhanced mobility and comfort\r\n\r\nPrecision stitching with reinforced seams for durability\r\n\r\nColor Profile:\r\n\r\nSap Green—A refined, earthy green tone inspired by nature\'s palette\r\n\r\nVersatile shade that transitions seamlessly from day to evening wear\r\n\r\nComplements a wide range of skin tones\r\n\r\nCreates a statement while maintaining sophistication\r\n\r\nCraftsmanship Details:\r\n\r\nHand-finished hemline with invisible stitching\r\n\r\nTraditional embroidery accents on the neckline and cuffs\r\n\r\nSustainably sourced materials with ethical production practices\r\n\r\nQuality assurance through multiple inspection stages\r\n\r\nOccasion Versatility:\r\n\r\nFormal workplace settings - projects professional elegance\r\n\r\nEvening gatherings—transitions beautifully with accessories\r\n\r\nCultural events - celebrates heritage with modern flair\r\n\r\nDinner parties make a sophisticated style statement\r\n\r\nCare Instructions:\r\n\r\nDry cleaning is recommended for optimal fabric care\r\n\r\nStore folded in a cool, dry place\r\n\r\nAvoid direct sunlight for prolonged periods\r\n\r\nSteam rather than iron for best results\r\n\r\nAbout the Collection:\r\nThis Sap Green Pashmina Kurti is part of our Heritage Fusion collection, where traditional weaving techniques meet contemporary design aesthetics. Each piece is individually crafted by skilled artisans with over 15 years of expertise, ensuring you receive a garment of exceptional quality and lasting beauty.\r\n\r\nElevate your wardrobe with this versatile kurti that promises comfort without compromising on style. The Sap Green color adds a refreshing touch of sophistication to your ensemble, making it a valuable addition to any fashion-conscious wardrobe.\r\n\r\nNote: Color may vary slightly due to screen display settings. Each piece is unique, carrying the subtle variations that characterize handcrafted luxury.', 999.00, NULL, NULL, 0.00, 13, 13, 15, 1, '2025-12-20 10:39:08', 5, 'in_stock', NULL),
(74, 'unstitched', 'Deep Navy Blue (Full-Voile)', 'Deep Navy Blue Full Voile Pagri\r\n\r\nProduct Name: Deep Navy Blue Full Voile Pagri Fabric\r\n\r\nColour: Deep Navy Blue (#0F204E)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Clean, sharp, and well-defined folds for a neat pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Suitable for long hours of comfortable wear\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, weddings, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Product colour may slightly vary due to digital screen display, lighting conditions, or photography', 100.00, 100.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 15:06:19', 5, 'in_stock', NULL),
(75, 'unstitched', 'Antique Gold (Full-Voile)', 'Product Name: Antique Gold Full Voile Pagri Fabric\r\n\r\nColour: Antique Gold (#C9A227)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Rich antique gold tone with clean, sharp, and well-defined folds for an elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, formal occasions, religious events, traditional functions, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 16:40:14', 5, 'in_stock', NULL),
(76, 'unstitched', 'Ash Grey (Full-Voile)', 'Product Name: Ash Grey Full Voile Pagri Fabric\r\n\r\nColour: Ash Grey (#B2BEB5)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Subtle ash grey shade with clean, sharp, and well-defined folds for a refined pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-30 16:43:50', 5, 'in_stock', NULL),
(77, 'unstitched', 'Aubergine (Full-Voile)', 'Product Name: Aubergine Full Voile Pagri Fabric\r\n\r\nColour: Aubergine (#3B0918)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep aubergine shade with rich tone and clean, sharp, well-defined folds for a bold and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Formal occasions, religious events, weddings, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 80.00, 80.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-30 16:46:12', 5, 'in_stock', NULL),
(78, 'unstitched', 'Black (Full-Voile)', 'Product Name: Black Full Voile Pagri Fabric\r\n\r\nColour: Black (#000000)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep black shade with clean, sharp, and well-defined folds for a classic and timeless pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 75.00, 75.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-30 16:53:12', 5, 'in_stock', NULL),
(79, 'unstitched', 'Blood Red (Full-Voile)', 'Product Name: Blood Red Full Voile Pagri Fabric\r\n\r\nColour: Blood Red (#660000)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Intense blood red shade with rich depth and clean, sharp, well-defined folds for a bold and striking pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, formal occasions, religious events, traditional ceremonies, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-30 16:55:32', 5, 'in_stock', NULL),
(80, 'unstitched', 'Blush Pink', 'Product Name: Blush Pink Full Voile Pagri Fabric\r\n\r\nColour: Blush Pink (#F2ACB9)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Elegant blush pink shade with clean, sharp, and well-defined folds for a soft and graceful pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Weddings, formal occasions, religious events, festive wear, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 90.00, 90.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 16:59:41', 5, 'in_stock', NULL),
(81, 'unstitched', 'Bottle Green', 'Product Name: Bottle Green Full Voile Pagri Fabric\r\n\r\nColour: Bottle Green (#0B3D2E)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep bottle green shade with rich tone and clean, sharp, well-defined folds for a refined and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Formal occasions, religious events, weddings, traditional functions, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 17:05:59', 5, 'in_stock', NULL),
(82, 'unstitched', 'Bronze Gold', 'Product Name: Bronze Gold Full Voile Pagri Fabric\r\n\r\nColour: Bronze Gold (#CD7F32)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Rich bronze gold tone with a subtle metallic warmth and clean, sharp, well-defined folds for a royal and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, formal occasions, religious events, festive wear, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 90.00, 90.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-30 17:08:08', 5, 'in_stock', NULL),
(83, 'unstitched', 'Burgundy', 'Product Name: Burgundy Full Voile Pagri Fabric\r\n\r\nColour: Burgundy (#5C1A1B)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep burgundy shade with rich depth and clean, sharp, well-defined folds for a bold and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, formal occasions, religious events, traditional ceremonies, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 17:10:27', 5, 'in_stock', NULL),
(84, 'unstitched', 'Butter Yellow', 'Product Name: Butter Yellow Full Voile Pagri Fabric\r\n\r\nColour: Butter Yellow (#FFF1A8)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Soft butter yellow shade with a gentle, warm tone and clean, sharp, well-defined folds for a bright and graceful pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, religious events, festive occasions, traditional functions, and summer wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 17:12:42', 5, 'in_stock', NULL),
(85, 'unstitched', 'Champagne', 'Product Name: Champagne Full Voile Pagri Fabric\r\n\r\nColour: Champagne (#F7E7CE)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Elegant champagne shade with a subtle warm tone and clean, sharp, well-defined folds for a refined and graceful pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Weddings, formal occasions, religious events, festive wear, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-30 17:14:56', 5, 'in_stock', NULL),
(86, 'unstitched', 'Charcoal Grey', 'Charcoal Grey Full Voile Pagri\r\n\r\nProduct Name: Charcoal Grey Full Voile Pagri Fabric\r\n\r\nColour: Charcoal Grey (#36454F)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep charcoal grey shade with a modern tone and clean, sharp, well-defined folds for a sophisticated pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:02:58', 5, 'in_stock', NULL),
(87, 'unstitched', 'Chocolate Brown', 'Chocolate Brown Full Voile Pagri\r\n\r\nProduct Name: Chocolate Brown Full Voile Pagri Fabric\r\n\r\nColour: Chocolate Brown (#3F000F)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Rich chocolate brown shade with deep tone and clean, sharp, well-defined folds for a warm and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Formal occasions, religious events, traditional functions, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:04:47', 5, 'in_stock', NULL),
(88, 'unstitched', 'Clay Brown', 'Clay Brown Full Voile Pagri\r\n\r\nProduct Name: Clay Brown Full Voile Pagri Fabric\r\n\r\nColour: Clay Brown (#8A4B2D)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Earthy clay brown shade with warm undertones and clean, sharp, well-defined folds for a natural and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Daily wear, religious events, traditional functions, and formal occasions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:08:13', 5, 'in_stock', NULL),
(89, 'unstitched', 'Cloud Grey', 'Cloud Grey Full Voile Pagri\r\n\r\nProduct Name: Cloud Grey Full Voile Pagri Fabric\r\n\r\nColour: Cloud Grey (#D3D3D3)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Light cloud grey shade with a clean, subtle tone and sharp, well-defined folds for a calm and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:10:20', 5, 'in_stock', NULL),
(90, 'unstitched', 'Coffee Brown', 'Coffee Brown Full Voile Pagri\r\n\r\nProduct Name: Coffee Brown Full Voile Pagri Fabric\r\n\r\nColour: Coffee Brown (#4B3621)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep coffee brown shade with rich warmth and clean, sharp, well-defined folds for a classic and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, traditional functions, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:14:47', 5, 'in_stock', NULL),
(91, 'unstitched', 'Copper Brown', 'Copper Brown Full Voile Pagri\r\n\r\nProduct Name: Copper Brown Full Voile Pagri Fabric\r\n\r\nColour: Copper Brown (#7C482B)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Warm copper brown shade with rich undertones and clean, sharp, well-defined folds for a refined and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Formal occasions, religious events, traditional functions, and festive wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:16:39', 5, 'in_stock', NULL),
(93, 'unstitched', 'Deep Navy Blue', 'Deep Navy Blue Full Voile Pagri\r\n\r\nProduct Name: Deep Navy Blue Full Voile Pagri Fabric\r\n\r\nColour: Deep Navy Blue (#0F204E)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Rich deep navy blue shade with excellent depth and clean, sharp, well-defined folds for a bold and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Daily wear, formal occasions, religious events, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:22:46', 5, 'in_stock', NULL),
(94, 'unstitched', 'Classic & Daily-Wear Colours – Full Voile Pagri Collection', 'Classic & Daily-Wear Colours – Full Voile Pagri Collection\r\n\r\nOur Classic & Daily-Wear Colours collection features timeless shades crafted for everyday comfort, elegance, and versatility. This range includes White, Off White/Cream, Black, Deep Navy Blue, Royal Blue, and Sky Blue, carefully selected to suit daily wear as well as formal and religious occasions.\r\n\r\nMade from premium Full Voile fabric, these pagris are soft, lightweight, breathable, and easy to manage, offering clean, sharp, and well-defined folds throughout the day. Each colour is produced using fast colouring (Guaranteed Colour) to ensure long-lasting brightness and durability.\r\n\r\nTo maintain colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for daily wear, office use, religious events, formal occasions, and traditional functions, making it a must-have range for every wardrobe.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:35:44', 5, 'in_stock', NULL),
(95, 'unstitched', 'Wedding & Royal Colours – Full Voile Pagri Collection', 'Wedding & Royal Colours – Full Voile Pagri Collection\r\n\r\nThe Wedding & Royal Colours collection is specially curated for grand occasions where elegance, richness, and tradition matter the most. This range features luxurious shades such as Maroon, Wine, Burgundy, Rani Pink, and Deep Red, chosen to reflect royalty, celebration, and timeless heritage.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring comfort even during long ceremonies. The fabric provides clean, sharp, and well-defined folds, enhancing the regal appearance of the pagri. All colours are produced using fast colouring (Guaranteed Colour) for long-lasting richness and vibrancy.\r\n\r\nTo preserve colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for weddings, receptions, religious ceremonies, festive celebrations, and royal or formal occasions, making it a perfect choice for special moments.', 90.00, 90.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:42:15', 5, 'in_stock', NULL),
(96, 'unstitched', 'Traditional & Punjabi Colours – Full Voile Pagri Collection', 'Traditional & Punjabi Colours – Full Voile Pagri Collection\r\n\r\nThe Traditional & Punjabi Colours collection celebrates the rich cultural heritage and vibrant spirit of Punjab. This range features classic and culturally significant shades such as Kesari / Saffron, Basanti Yellow, Mustard, Bottle Green, Mehndi Green, Olive Green, and other earthy traditional tones, widely worn during religious, festive, and cultural occasions.\r\n\r\nMade from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, offering all-day comfort with clean, sharp, and well-defined folds. Each colour is created using fast colouring (Guaranteed Colour) to maintain brightness and depth over time, even with regular use.\r\n\r\nTo retain colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for Punjabi traditional wear, religious ceremonies, Gurpurabs, cultural functions, festivals, and everyday ethnic use, making it a timeless and essential part of any pagri collection.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 05:56:09', 5, 'in_stock', NULL),
(97, 'unstitched', 'Modern & Trending Shades – Full Voile Pagri Collection', 'Modern & Trending Shades – Full Voile Pagri Collection\r\n\r\nThe Modern & Trending Shades collection is designed for those who prefer a contemporary look while staying rooted in tradition. This range features stylish and fashionable colours such as Blush Pink, Pastel Lavender, Lilac, Mint Green, Pistachio Green, Powder Blue, Ice Blue, Dusty Rose, and other soft modern tones that are currently in trend.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, providing superior comfort with clean, sharp, and well-defined folds. Each shade is produced using fast colouring (Guaranteed Colour) to ensure long-lasting colour vibrancy and a refined finish.\r\n\r\nTo maintain the quality and appearance of the colour, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for modern weddings, casual outings, festive events, formal gatherings, and everyday stylish wear, making it a perfect choice for contemporary pagri styling.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 06:36:33', 5, 'in_stock', NULL),
(98, 'unstitched', 'Pastel & Soft Elegant Colours – Full Voile Pagri Collection', 'Pastel & Soft Elegant Colours – Full Voile Pagri Collection\r\n\r\nThe Pastel & Soft Elegant Colours collection is curated for those who appreciate subtle elegance and refined style. This range features gentle, soothing shades such as Pastel Peach, Pastel Lavender, Lavender Mist, Powder Blue, Ice Blue, Blush Pink, Pearl Grey, and other soft pastel tones that offer a graceful and contemporary look.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring all-day comfort with clean, sharp, and well-defined folds. Each colour is produced using fast colouring (Guaranteed Colour), maintaining softness and clarity over time.\r\n\r\nTo preserve the colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for formal gatherings, elegant weddings, religious events, summer wear, and everyday refined styling, making it a perfect choice for a calm and sophisticated appearance.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 06:42:33', 5, 'in_stock', NULL),
(99, 'unstitched', 'Rare & Premium Shades (High-End Demand) – Full Voile Pagri Collection', 'Rare & Premium Shades (High-End Demand) – Full Voile Pagri Collection\r\n\r\nThe Rare & Premium Shades (High-End Demand) collection is exclusively curated for those who seek distinction, luxury, and uniqueness in their pagri selection. This range features rich and uncommon shades such as Aubergine, Emerald Green, Teal Blue, Prussian Blue, Rust Orange, and Antique Gold, chosen for their depth, elegance, and premium appeal.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring superior comfort while maintaining a royal structure with clean, sharp, and well-defined folds. Each shade is produced using fast colouring (Guaranteed Colour) to preserve its richness and intensity over time.\r\n\r\nTo maintain the premium finish and colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is strongly recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for high-end weddings, royal ceremonies, luxury events, festive occasions, and premium traditional wear, making it a preferred choice for customers with refined taste.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 06:48:10', 5, 'in_stock', NULL),
(100, 'unstitched', 'Luxury & Statement Shades (High-End Appeal) – Full Voile Pagri Collection', 'Luxury & Statement Shades (High-End Appeal) – Full Voile Pagri Collection\r\n\r\nThe Luxury & Statement Shades (High-End Appeal) collection is curated for individuals who prefer bold, powerful colours that make a lasting impression. This exclusive range features rich and commanding shades such as Sapphire Blue, Oxford Blue, Imperial Purple, Garnet Red, Mahogany, and Bronze Gold, selected to reflect confidence, prestige, and timeless luxury.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, offering superior comfort while maintaining strong structure with clean, sharp, and well-defined folds. Each shade is produced using fast colouring (Guaranteed Colour) to ensure depth, vibrancy, and long-lasting colour performance.\r\n\r\nTo preserve the premium finish, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for luxury weddings, grand receptions, royal ceremonies, premium festive occasions, and high-profile traditional events, making it the perfect choice for a bold and distinguished pagri look.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 06:59:23', 5, 'in_stock', NULL),
(101, 'unstitched', 'Soft Royal & Wedding-Friendly Shades – Full Voile Pagri Collection', 'Soft Royal & Wedding-Friendly Shades – Full Voile Pagri Collection\r\n\r\nThe Soft Royal & Wedding-Friendly Shades collection is thoughtfully curated for those who prefer understated luxury and graceful elegance. This range features refined and elegant tones such as Champagne, Pearl Grey, Rose Gold, Dusty Rose, and Soft Gold, making it ideal for weddings and special occasions where subtle sophistication is desired.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring superior comfort throughout long ceremonies. The fabric delivers clean, sharp, and well-defined folds, enhancing the overall regal yet gentle appearance. Each shade is produced using fast colouring (Guaranteed Colour) for lasting softness and colour stability.\r\n\r\nTo preserve the colour quality and finish, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is perfect for weddings, receptions, engagement ceremonies, religious functions, and elegant traditional events, offering a refined and graceful pagri look.', 90.00, 90.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:07:41', 5, 'in_stock', NULL),
(102, 'unstitched', 'Earthy & Heritage Tones (Traditional Yet Fresh) – Full Voile Pagri Collection', 'Earthy & Heritage Tones (Traditional Yet Fresh) – Full Voile Pagri Collection\r\n\r\nThe Earthy & Heritage Tones collection is inspired by nature, tradition, and timeless Punjabi heritage, offering shades that feel both classic and refreshing. This range features rich, grounded colours such as Clay Brown, Terracotta, Coffee Brown, Moss Green, and Forest Green, carefully selected for their natural warmth and cultural significance.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring comfort while maintaining clean, sharp, and well-defined folds. Each shade is produced using fast colouring (Guaranteed Colour) to preserve depth and consistency over time.\r\n\r\nTo maintain the fabric’s quality and colour, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for traditional Punjabi wear, religious ceremonies, cultural gatherings, festivals, and everyday ethnic styling, making it a versatile choice rooted in heritage yet modern in appeal.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:12:05', 5, 'in_stock', NULL),
(103, 'unstitched', 'Modern Pastel & Trend Shades (Youth Favourite) – Full Voile Pagri Collection', 'Modern Pastel & Trend Shades (Youth Favourite) – Full Voile Pagri Collection\r\n\r\nThe Modern Pastel & Trend Shades collection is curated for the younger generation and style-conscious wearers who prefer fresh, subtle, and contemporary colours. This range features trendy pastel tones such as Ice Blue, Cloud Grey, Sage Green, Butter Yellow, and Lilac, offering a modern yet elegant look.\r\n\r\nCrafted from premium Full Voile fabric, these pagris are soft, lightweight, and breathable, ensuring superior comfort with clean, sharp, and well-defined folds. Each shade is produced using fast colouring (Guaranteed Colour), delivering lasting colour clarity and smooth finish.\r\n\r\nTo maintain colour quality, avoid prolonged exposure to direct sunlight and do not wash harshly. Gentle handling is recommended. Please note that actual colour may slightly vary due to digital screen display and lighting conditions.\r\n\r\nThis collection is ideal for youth styling, casual wear, modern functions, festive gatherings, and everyday contemporary looks, making it a perfect blend of tradition and trend.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:17:06', 5, 'in_stock', NULL),
(104, 'unstitched', 'Deep Red', 'Deep Red Full Voile Pagri\r\n\r\nProduct Name: Deep Red Full Voile Pagri Fabric\r\n\r\nColour: Deep Red (#8B0000)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Rich deep red shade with strong depth and clean, sharp, well-defined folds for a bold and traditional pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, religious events, traditional ceremonies, festive occasions, and formal wear\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:31:49', 5, 'in_stock', NULL),
(105, 'unstitched', 'Dusty Rose', 'Dusty Rose Full Voile Pagri\r\n\r\nProduct Name: Dusty Rose Full Voile Pagri Fabric\r\n\r\nColour: Dusty Rose (#C08081)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Soft dusty rose shade with muted elegance and clean, sharp, well-defined folds for a graceful and refined pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Weddings, receptions, formal occasions, festive wear, and elegant traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:34:35', 5, 'in_stock', NULL),
(106, 'unstitched', 'Forest Green', 'Forest Green Full Voile Pagri\r\n\r\nProduct Name: Forest Green Full Voile Pagri Fabric\r\n\r\nColour: Forest Green (#014421)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep forest green shade with rich natural depth and clean, sharp, well-defined folds for a classic and elegant pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Traditional wear, religious events, formal occasions, weddings, and festive functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:37:09', 5, 'in_stock', NULL),
(107, 'unstitched', 'Garnet Red', 'Garnet Red Full Voile Pagri\r\n\r\nProduct Name: Garnet Red Full Voile Pagri Fabric\r\n\r\nColour: Garnet Red (#733635)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Deep garnet red shade with rich undertones and clean, sharp, well-defined folds for a luxurious and refined pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear without heaviness\r\n\r\nIdeal For: Weddings, receptions, formal occasions, religious events, and festive celebrations\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:38:59', 5, 'in_stock', NULL),
(108, 'unstitched', 'Haldi Yellow', 'Haldi Yellow Full Voile Pagri\r\n\r\nProduct Name: Haldi Yellow Full Voile Pagri Fabric\r\n\r\nColour: Haldi Yellow (#FFB300)\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, breathable, and smooth texture\r\n\r\nFinish & Look: Bright haldi yellow shade with a vibrant tone and clean, sharp, well-defined folds for a festive and traditional pagri appearance\r\n\r\nColour Quality: Fast colouring with Guaranteed Colour retention\r\n\r\nComfort Level: Comfortable for long hours of wear, suitable for all-day use\r\n\r\nIdeal For: Haldi ceremonies, weddings, religious events, festive occasions, and traditional functions\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to sustain colour\r\n\r\nDo not wash harshly; gentle washing and handling recommended\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 07:42:29', 5, 'in_stock', NULL),
(109, 'unstitched', 'Ice Blue', 'Ice Blue Full Voile Pagri Fabric\r\n\r\nProduct Name: Ice Blue Full Voile Pagri Fabric\r\n\r\nColour: Ice Blue\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA refreshing ice blue shade with a calm, elegant tone. The fabric delivers a clean and refined finish with sharp, well-defined folds, enhancing the traditional pagri look with a graceful touch.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining clarity and freshness of the shade over time.\r\n\r\nComfort Level:\r\nHighly comfortable for long hours of wear, making it suitable for all-day traditional and ceremonial use.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional functions.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:37:28', 5, 'in_stock', NULL),
(110, 'unstitched', 'Imperial Purple', 'Imperial Purple Full Voile Pagri Fabric\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear.\r\n\r\nFinish & Look: A refreshing ice blue shade with a calm, elegant tone. The fabric delivers a clean and refined finish with sharp, well-defined folds, enhancing the traditional pagri look with a graceful touch.\r\n\r\nColour Quality: Fast colouring with guaranteed colour retention, maintaining clarity and freshness of the shade over time.\r\n\r\nComfort Level: Highly comfortable for long hours of wear, making it suitable for all-day traditional and ceremonial use.\r\n\r\nIdeal For: Weddings, religious ceremonies, festive occasions, cultural events, and traditional functions.\r\n\r\nCare Instructions:\r\n- Avoid prolonged exposure to direct sunlight to maintain colour quality\r\n- Gentle washing and careful handling recommended\r\n- Do not wash harshly\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:47:19', 5, 'in_stock', NULL),
(111, 'unstitched', 'Ink Blue', 'Ink Blue Full Voile Pagri Fabric\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear.\r\n\r\nFinish & Look: A deep ink blue shade with a calm, elegant tone. The fabric delivers a clean and refined finish with sharp, well-defined folds, enhancing the traditional pagri look with a graceful touch.\r\n\r\nColour Quality: Fast colouring with guaranteed colour retention, maintaining clarity and freshness of the shade over time.\r\n\r\nComfort Level: Highly comfortable for long hours of wear, making it suitable for all-day traditional and ceremonial use.\r\n\r\nIdeal For: Weddings, religious ceremonies, festive occasions, cultural events, and traditional functions.\r\n\r\nCare Instructions:\r\n- Avoid prolonged exposure to direct sunlight to maintain colour quality\r\n- Gentle washing and careful handling recommended\r\n- Do not wash harshly\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:51:02', 5, 'in_stock', NULL),
(112, 'unstitched', 'Kesrai / Saffron', 'Kesrai / Saffron Full Voile Pagri Fabric\r\n\r\nProduct Name: Kesrai / Saffron Full Voile Pagri Fabric\r\n\r\nColour: Kesrai / Saffron\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA rich kesrai saffron shade with a warm, auspicious tone. The fabric provides a bright, festive finish with clean, sharp, and well-defined folds, creating a bold and traditional pagri appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining vibrancy and depth even after extended use.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it ideal for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, haldi functions, festive occasions, and cultural celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour brightness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 0.00, 14, NULL, 100, 1, '2025-12-31 09:52:26', 5, 'in_stock', NULL);
INSERT INTO `products` (`id`, `product_type`, `name`, `description`, `price`, `price_per_meter`, `length_in_meters`, `stitch_charges`, `category_id`, `size_chart_id`, `stock_quantity`, `is_active`, `created_at`, `reorder_level`, `stock_status`, `last_restocked`) VALUES
(113, 'unstitched', 'Lavender Mist', 'Lavender Mist Full Voile Pagri Fabric\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features: Soft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear.\r\n\r\nFinish & Look: A soft lavender mist shade with a calm, elegant tone. The fabric delivers a clean and refined finish with sharp, well-defined folds, enhancing the traditional pagri look with a graceful touch.\r\n\r\nColour Quality: Fast colouring with guaranteed colour retention, maintaining clarity and freshness of the shade over time.\r\n\r\nComfort Level: Highly comfortable for long hours of wear, making it suitable for all-day traditional and ceremonial use.\r\n\r\nIdeal For: Weddings, religious ceremonies, festive occasions, cultural events, and traditional functions.\r\n\r\nCare Instructions:\r\n- Avoid prolonged exposure to direct sunlight to maintain colour quality\r\n- Gentle washing and careful handling recommended\r\n- Do not wash harshly\r\n\r\nDisclaimer: Actual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:54:30', 85, 'in_stock', NULL),
(114, 'unstitched', 'Lilac', 'Lilac Full Voile Pagri Fabric\r\n\r\nProduct Name: Lilac Full Voile Pagri Fabric\r\n\r\nColour: Lilac\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, offering superior comfort throughout wear.\r\n\r\nFinish & Look:\r\nA soft lilac shade with a gentle, elegant tone. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a graceful and traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring lasting softness and clarity of the colour.\r\n\r\nComfort Level:\r\nComfortable for extended wear, suitable for all-day ceremonies and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:55:53', 5, 'in_stock', NULL),
(115, 'unstitched', 'Mahogany', 'Mahogany Full Voile Pagri Fabric\r\n\r\nProduct Name: Mahogany Full Voile Pagri Fabric\r\n\r\nColour: Mahogany\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring superior comfort throughout wear.\r\n\r\nFinish & Look:\r\nA deep mahogany shade with warm, earthy undertones. The fabric features a polished finish with clean, sharp, and well-defined folds, delivering a sophisticated and traditional pagri appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, preserving the richness and depth of the colour over time.\r\n\r\nComfort Level:\r\nDesigned for long hours of wear, offering comfort and ease for all-day traditional and ceremonial use.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional functions.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour vibrancy\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 09:58:52', 5, 'in_stock', NULL),
(116, 'unstitched', 'Maroon Full Voile Pagri Fabric', '', 85.00, 0.00, 0.00, 0.00, 14, NULL, 100, 0, '2025-12-31 09:59:56', 5, 'in_stock', NULL),
(117, 'unstitched', 'Mehndi Green Full Voile Pagri Fabric', '', 85.00, 0.00, 0.00, 0.00, 14, NULL, 100, 0, '2025-12-31 10:00:34', 5, 'in_stock', NULL),
(118, 'unstitched', 'Midnight Blue Full Voile Pagri Fabric', '', 85.00, 0.00, 0.00, 0.00, 14, NULL, 100, 0, '2025-12-31 10:01:08', 5, 'in_stock', NULL),
(119, 'unstitched', 'Mint Green Full Voile Pagri Fabric', '', 85.00, 0.00, 0.00, 0.00, 14, NULL, 100, 0, '2025-12-31 10:02:08', 5, 'in_stock', NULL),
(120, 'unstitched', 'Maroon', 'Maroon Full Voile Pagri Fabric\r\n\r\nProduct Name: Maroon Full Voile Pagri Fabric\r\n\r\nColour: Maroon\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing lasting comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA rich maroon shade with a deep, regal tone. The fabric offers a refined finish with clean, sharp, and well-defined folds, giving the pagri a classic and elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring long-lasting depth and vibrancy of the colour.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it suitable for all-day ceremonies and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:10:53', 5, 'in_stock', NULL),
(121, 'unstitched', 'Mehndi Green', 'Mehndi Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Mehndi Green Full Voile Pagri Fabric\r\n\r\nColour: Mehndi Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA rich mehndi green shade with deep, earthy tones. The fabric offers a clean and polished finish with sharp, well-defined folds, giving the pagri a classic and traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining the depth and richness of the shade over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:12:27', 5, 'in_stock', NULL),
(122, 'unstitched', 'Midnight Blue', 'Midnight Blue Full Voile Pagri Fabric\r\n\r\nProduct Name: Midnight Blue Full Voile Pagri Fabric\r\n\r\nColour: Midnight Blue\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing all-day comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA deep midnight blue shade with a rich, elegant tone. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a sophisticated and traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring long-lasting depth and vibrancy of the shade.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it ideal for all-day weddings, religious ceremonies, and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:13:32', 5, 'in_stock', NULL),
(123, 'unstitched', 'Mint Green', 'Mint Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Mint Green Full Voile Pagri Fabric\r\n\r\nColour: Mint Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing all-day comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA fresh mint green shade with a cool, soothing tone. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a graceful and elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining the subtle vibrancy and clarity of the shade over time.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it ideal for all-day weddings and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:14:32', 5, 'in_stock', NULL),
(124, 'unstitched', 'Moss Green', 'Moss Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Moss Green Full Voile Pagri Fabric\r\n\r\nColour: Moss Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing lasting comfort throughout wear.\r\n\r\nFinish & Look:\r\nA natural moss green shade with earthy, subtle tones. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a classic and elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring the richness and vibrancy of the colour over time.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:15:33', 5, 'in_stock', NULL),
(125, 'unstitched', 'Mustard', 'Mustard Full Voile Pagri Fabric\r\n\r\nProduct Name: Mustard Full Voile Pagri Fabric\r\n\r\nColour: Mustard\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA rich mustard shade with a warm, vibrant tone. The fabric offers a clean and refined finish with sharp, well-defined folds, giving the pagri a bold yet traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining the depth and vibrancy of the colour over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:16:26', 5, 'in_stock', NULL),
(126, 'unstitched', 'Off-White/Cream', 'Off-White / Cream Full Voile Pagri Fabric\r\n\r\nProduct Name: Off-White / Cream Full Voile Pagri Fabric\r\n\r\nColour: Off-White / Cream\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, offering exceptional comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA classic off-white cream shade with a soft, elegant tone. The fabric provides a clean, polished finish with sharp, well-defined folds, creating a timeless and refined traditional pagri appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring long-lasting purity and consistency of the shade.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it ideal for all-day weddings and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:18:21', 5, 'in_stock', NULL),
(127, 'unstitched', 'Olive Green', 'Olive Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Olive Green Full Voile Pagri Fabric\r\n\r\nColour: Olive Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA rich olive green shade with earthy, understated tones. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a classic and dignified traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining depth and stability of the colour over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:20:31', 5, 'in_stock', NULL),
(128, 'unstitched', 'Oxford Blue', 'Oxford Blue Full Voile Pagri Fabric\r\n\r\nProduct Name: Oxford Blue Full Voile Pagri Fabric\r\n\r\nColour: Oxford Blue\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing lasting comfort throughout wear.\r\n\r\nFinish & Look:\r\nA deep oxford blue shade with a rich, classic tone. The fabric offers a clean and refined finish with sharp, well-defined folds, giving the pagri a dignified and elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring long-lasting depth and richness of the colour.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it suitable for all-day ceremonies and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:23:22', 5, 'in_stock', NULL),
(129, 'unstitched', 'Pastel Lavender', 'Pastel Lavender Full Voile Pagri Fabric\r\n\r\nProduct Name: Pastel Lavender Full Voile Pagri Fabric\r\n\r\nColour: Pastel Lavender\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA soft pastel lavender shade with a calm, elegant tone. The fabric features a clean and refined finish with sharp, well-defined folds, giving the pagri a graceful and sophisticated traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining the subtle vibrancy and clarity of the shade over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day weddings and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:25:21', 5, 'in_stock', NULL),
(130, 'unstitched', 'Pastel Peach', 'Pastel Peach Full Voile Pagri Fabric\r\n\r\nProduct Name: Pastel Peach Full Voile Pagri Fabric\r\n\r\nColour: Pastel Peach\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, providing all-day comfort and ease of wear.\r\n\r\nFinish & Look:\r\nA soft pastel peach shade with a warm, gentle tone. The fabric offers a clean and elegant finish with sharp, well-defined folds, giving the pagri a graceful and refined traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring lasting softness and subtle vibrancy of the shade.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it suitable for all-day weddings and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:26:25', 5, 'in_stock', NULL),
(131, 'unstitched', 'Peacock Green', 'Peacock Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Peacock Green Full Voile Pagri Fabric\r\n\r\nColour: Peacock Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring lasting comfort throughout wear.\r\n\r\nFinish & Look:\r\nA rich peacock green shade with a deep, jewel-toned vibrancy. The fabric features a refined finish with clean, sharp, and well-defined folds, giving the pagri a bold yet elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, preserving the depth and brilliance of the colour over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour richness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:27:33', 5, 'in_stock', NULL),
(132, 'unstitched', 'Pearl grey', 'Pearl Grey Full Voile Pagri Fabric\r\n\r\nProduct Name: Pearl Grey Full Voile Pagri Fabric\r\n\r\nColour: Pearl Grey\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring comfort and ease of wear throughout the day.\r\n\r\nFinish & Look:\r\nA subtle pearl grey shade with a refined, sophisticated tone. The fabric offers a clean, polished finish with sharp, well-defined folds, giving the pagri a modern yet traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining the elegance and clarity of the shade over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day ceremonial and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour vibrancy\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:28:21', 5, 'in_stock', NULL),
(133, 'unstitched', 'Blush Pink', 'Blush Pink Full Voile Pagri Fabric\r\n\r\nProduct Name: Blush Pink Full Voile Pagri Fabric\r\n\r\nColour: Blush Pink\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, offering exceptional comfort throughout wear.\r\n\r\nFinish & Look:\r\nA delicate blush pink shade with a soft, romantic tone. The fabric features a clean and elegant finish with sharp, well-defined folds, giving the pagri a graceful and refined traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, ensuring long-lasting softness and vibrancy of the shade.\r\n\r\nComfort Level:\r\nComfortable for extended wear, making it suitable for all-day weddings and traditional functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to maintain colour quality\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:30:09', 5, 'in_stock', NULL),
(134, 'unstitched', 'Pistachio Green', 'Pistachio Green Full Voile Pagri Fabric\r\n\r\nProduct Name: Pistachio Green Full Voile Pagri Fabric\r\n\r\nColour: Pistachio Green\r\n\r\nFabric Type: Premium Full Voile Pagri Fabric\r\n\r\nMaterial Features:\r\nSoft, lightweight, and breathable fabric with a smooth texture, ensuring lasting comfort throughout wear.\r\n\r\nFinish & Look:\r\nA refreshing pistachio green shade with a soft, soothing tone. The fabric offers a clean and refined finish with sharp, well-defined folds, giving the pagri a fresh and elegant traditional appearance.\r\n\r\nColour Quality:\r\nFast colouring with guaranteed colour retention, maintaining brightness and clarity of the shade over time.\r\n\r\nComfort Level:\r\nComfortable for long hours of wear, making it suitable for all-day traditional and ceremonial functions.\r\n\r\nIdeal For:\r\nWeddings, religious ceremonies, festive occasions, cultural events, and traditional celebrations.\r\n\r\nCare Instructions:\r\n\r\nAvoid prolonged exposure to direct sunlight to preserve colour freshness\r\n\r\nGentle washing and careful handling recommended\r\n\r\nDo not wash harshly\r\n\r\nDisclaimer:\r\nActual colour may slightly vary due to digital screen display, lighting conditions, or photography.', 85.00, 85.00, 100.00, 30.00, 14, NULL, 100, 1, '2025-12-31 10:35:51', 5, 'in_stock', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color_id`) VALUES
(55, 17, 7),
(60, 20, 13),
(61, 21, 7),
(64, 24, 6),
(65, 25, 6),
(66, 26, 14),
(67, 27, 7),
(68, 28, 7),
(72, 32, 10),
(73, 33, 2),
(74, 34, 4),
(75, 35, 5),
(76, 36, 10),
(78, 38, 4),
(79, 39, 16),
(80, 40, 1),
(81, 41, 10),
(82, 42, 16),
(119, 49, 17),
(146, 74, 18),
(149, 75, 44),
(148, 76, 33),
(147, 77, 40),
(150, 78, 4),
(152, 79, 42),
(399, 80, 55),
(398, 81, 30),
(157, 82, 64),
(159, 83, 25),
(161, 84, 78),
(167, 85, 65),
(166, 86, 32),
(165, 87, 52),
(169, 88, 70),
(171, 89, 76),
(173, 90, 72),
(175, 91, 46),
(179, 93, 18),
(231, 94, 4),
(232, 94, 18),
(236, 94, 19),
(233, 94, 20),
(234, 94, 21),
(235, 94, 22),
(228, 95, 23),
(230, 95, 24),
(226, 95, 25),
(229, 95, 26),
(227, 95, 27),
(224, 96, 28),
(222, 96, 29),
(220, 96, 30),
(225, 96, 31),
(221, 96, 43),
(223, 96, 47),
(245, 97, 32),
(244, 97, 33),
(249, 97, 34),
(248, 97, 49),
(250, 97, 50),
(247, 97, 51),
(246, 97, 52),
(260, 98, 54),
(256, 98, 55),
(259, 98, 56),
(258, 98, 57),
(257, 98, 58),
(268, 99, 38),
(267, 99, 40),
(266, 99, 44),
(269, 99, 45),
(270, 99, 59),
(281, 100, 9),
(277, 100, 42),
(282, 100, 59),
(280, 100, 60),
(279, 100, 63),
(278, 100, 64),
(288, 101, 65),
(290, 101, 66),
(291, 101, 67),
(289, 101, 68),
(292, 101, 69),
(300, 102, 71),
(297, 102, 72),
(299, 102, 73),
(298, 102, 74),
(308, 103, 75),
(307, 103, 76),
(310, 103, 77),
(306, 103, 78),
(309, 103, 79),
(312, 104, 27),
(314, 105, 68),
(316, 106, 74),
(318, 107, 62),
(321, 108, 43),
(324, 109, 75),
(397, 110, 61),
(396, 111, 39),
(394, 112, 29),
(393, 113, 58),
(391, 114, 79),
(395, 115, 63),
(336, 116, 10),
(337, 117, 47),
(338, 118, 37),
(340, 119, 57),
(344, 120, 10),
(378, 121, 47),
(389, 122, 37),
(377, 123, 57),
(388, 124, 73),
(387, 125, 28),
(386, 126, 20),
(373, 127, 31),
(372, 128, 60),
(385, 129, 36),
(384, 130, 35),
(383, 131, 48),
(382, 132, 66),
(381, 133, 8),
(380, 134, 56);

-- --------------------------------------------------------

--
-- Table structure for table `product_color_images`
--

CREATE TABLE `product_color_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_video` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_main_image` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_color_images`
--

INSERT INTO `product_color_images` (`id`, `product_id`, `color_id`, `image_url`, `is_video`, `display_order`, `created_at`, `is_main_image`) VALUES
(22, 17, 7, 'uploads/products/6943e248d1616_product_17_color_7.webp', 0, 1, '2025-12-18 11:15:20', 1),
(23, 17, 7, 'uploads/products/6943e248d2234_product_17_color_7.webp', 0, 2, '2025-12-18 11:15:20', 0),
(34, 20, 13, 'uploads/products/694400363e34c_product_20_color_13.jpg', 0, 1, '2025-12-18 13:23:02', 1),
(35, 21, 7, 'uploads/products/694402a30de97_product_21_color_7.webp', 0, 1, '2025-12-18 13:33:23', 1),
(38, 24, 6, 'uploads/products/69440361b2206_product_24_color_6.jpg', 0, 1, '2025-12-18 13:36:33', 1),
(39, 25, 6, 'uploads/products/694403a1bfd10_product_25_color_6.jpg', 0, 1, '2025-12-18 13:37:37', 1),
(40, 26, 14, 'uploads/products/694404a151a8e_product_26_color_14.jpg', 0, 1, '2025-12-18 13:41:53', 1),
(41, 27, 7, 'uploads/products/694404d241512_product_27_color_7.jpg', 0, 1, '2025-12-18 13:42:42', 1),
(42, 28, 7, 'uploads/products/6944052444cb9_product_28_color_7.jpg', 0, 1, '2025-12-18 13:44:04', 1),
(46, 32, 10, 'uploads/products/694407e632f4f_product_32_color_10.jpg', 0, 1, '2025-12-18 13:55:50', 1),
(47, 33, 2, 'uploads/products/6944082b3bffa_product_33_color_2.jpg', 0, 1, '2025-12-18 13:56:59', 1),
(48, 34, 4, 'uploads/products/69440859aa4c0_product_34_color_4.jpg', 0, 1, '2025-12-18 13:57:45', 1),
(49, 35, 5, 'uploads/products/69440894205c1_product_35_color_5.webp', 0, 1, '2025-12-18 13:58:44', 1),
(50, 36, 10, 'uploads/products/694409138221b_product_36_color_10.jpg', 0, 1, '2025-12-18 14:00:51', 1),
(51, 38, 4, 'uploads/products/694409a8226fe_product_38_color_4.jpg', 0, 1, '2025-12-18 14:03:20', 1),
(52, 39, 16, 'uploads/products/69440a36b2869_product_39_color_16.jpg', 0, 1, '2025-12-18 14:05:42', 1),
(53, 40, 1, 'uploads/products/69440a61a1495_product_40_color_1.jpg', 0, 1, '2025-12-18 14:06:25', 1),
(54, 41, 10, 'uploads/products/69440ab10008b_product_41_color_10.jpg', 0, 1, '2025-12-18 14:07:45', 1),
(55, 42, 16, 'uploads/products/69440ad19a276_product_42_color_16.jpg', 0, 1, '2025-12-18 14:08:17', 1),
(73, 49, 17, 'uploads/products/69467ccc87e42_product_49_color_17.png', 0, 1, '2025-12-20 10:39:08', 1),
(89, 49, 5, 'uploads/products/694baf304757b_product_49_color_5.jpg', 0, 1, '2025-12-24 09:15:28', 0),
(95, 74, 44, 'uploads/products/6953ea6ba8dbd_product_74_color_44.png', 0, 1, '2025-12-30 15:06:19', 1),
(96, 74, 18, 'uploads/products/6953ea6baa219_product_74_color_18.png', 0, 1, '2025-12-30 15:06:19', 1),
(97, 75, 44, 'uploads/products/6954006ec6588_product_75_color_44.png', 0, 1, '2025-12-30 16:40:14', 1),
(98, 76, 33, 'uploads/products/69540146dfa85_product_76_color_33.png', 0, 1, '2025-12-30 16:43:50', 1),
(99, 77, 40, 'uploads/products/695401d4f36c8_product_77_color_40.png', 0, 1, '2025-12-30 16:46:13', 1),
(100, 78, 4, 'uploads/products/695403781539a_product_78_color_4.png', 0, 1, '2025-12-30 16:53:12', 1),
(101, 79, 42, 'uploads/products/6954041e2a2bb_product_79_color_42.png', 0, 1, '2025-12-30 16:55:58', 1),
(102, 80, 55, 'uploads/products/695404fd47643_product_80_color_55.png', 0, 1, '2025-12-30 16:59:41', 1),
(103, 81, 30, 'uploads/products/695406770393b_product_81_color_30.png', 0, 1, '2025-12-30 17:05:59', 1),
(104, 82, 64, 'uploads/products/695406f82aacf_product_82_color_64.png', 0, 1, '2025-12-30 17:08:08', 1),
(105, 83, 25, 'uploads/products/6954078360db4_product_83_color_25.png', 0, 1, '2025-12-30 17:10:27', 1),
(106, 84, 78, 'uploads/products/6954080a7e7b4_product_84_color_78.png', 0, 1, '2025-12-30 17:12:42', 1),
(107, 85, 65, 'uploads/products/6954089024f6a_product_85_color_65.png', 0, 1, '2025-12-30 17:14:56', 1),
(108, 86, 32, 'uploads/products/6954ae82e410c_product_86_color_32.png', 0, 1, '2025-12-31 05:02:58', 1),
(109, 87, 52, 'uploads/products/6954aeef7cb35_product_87_color_52.png', 0, 1, '2025-12-31 05:04:47', 1),
(110, 88, 70, 'uploads/products/6954afbddbf05_product_88_color_70.png', 0, 1, '2025-12-31 05:08:13', 1),
(111, 89, 76, 'uploads/products/6954b03c3564c_product_89_color_76.png', 0, 1, '2025-12-31 05:10:20', 1),
(112, 90, 72, 'uploads/products/6954b147cc78e_product_90_color_72.png', 0, 1, '2025-12-31 05:14:47', 1),
(113, 91, 46, 'uploads/products/6954b1b7a74bc_product_91_color_46.png', 0, 1, '2025-12-31 05:16:39', 1),
(115, 93, 18, 'uploads/products/6954b3264daa8_product_93_color_18.png', 0, 1, '2025-12-31 05:22:46', 1),
(116, 94, 4, 'uploads/products/6954b63037795_product_94_color_4.png', 0, 1, '2025-12-31 05:35:44', 1),
(117, 94, 18, 'uploads/products/6954b630386bb_product_94_color_18.png', 0, 1, '2025-12-31 05:35:44', 1),
(118, 94, 20, 'uploads/products/6954b63039526_product_94_color_20.png', 0, 1, '2025-12-31 05:35:44', 1),
(119, 94, 21, 'uploads/products/6954b6303a1fd_product_94_color_21.png', 0, 1, '2025-12-31 05:35:44', 1),
(120, 94, 22, 'uploads/products/6954b6303b3c3_product_94_color_22.png', 0, 1, '2025-12-31 05:35:44', 1),
(121, 94, 19, 'uploads/products/6954b6303be01_product_94_color_19.png', 0, 1, '2025-12-31 05:35:44', 1),
(122, 95, 25, 'uploads/products/6954b7b75a098_product_95_color_25.png', 0, 1, '2025-12-31 05:42:15', 1),
(123, 95, 27, 'uploads/products/6954b7b75b3b5_product_95_color_27.png', 0, 1, '2025-12-31 05:42:15', 1),
(124, 95, 23, 'uploads/products/6954b7b75c5cc_product_95_color_23.png', 0, 1, '2025-12-31 05:42:15', 1),
(125, 95, 26, 'uploads/products/6954b7b75d8ca_product_95_color_26.png', 0, 1, '2025-12-31 05:42:15', 1),
(126, 95, 24, 'uploads/products/6954b7b75e442_product_95_color_24.png', 0, 1, '2025-12-31 05:42:15', 1),
(127, 96, 30, 'uploads/products/6954baf99d093_product_96_color_30.png', 0, 1, '2025-12-31 05:56:09', 1),
(128, 96, 43, 'uploads/products/6954baf99dd7c_product_96_color_43.png', 0, 1, '2025-12-31 05:56:09', 1),
(129, 96, 29, 'uploads/products/6954baf99ea35_product_96_color_29.png', 0, 1, '2025-12-31 05:56:09', 1),
(130, 96, 47, 'uploads/products/6954baf99f3c8_product_96_color_47.png', 0, 1, '2025-12-31 05:56:09', 1),
(131, 96, 28, 'uploads/products/6954baf9a037c_product_96_color_28.png', 0, 1, '2025-12-31 05:56:09', 1),
(132, 96, 31, 'uploads/products/6954baf9a1390_product_96_color_31.png', 0, 1, '2025-12-31 05:56:09', 1),
(133, 97, 33, 'uploads/products/6954c471466ee_product_97_color_33.png', 0, 1, '2025-12-31 06:36:33', 1),
(134, 97, 32, 'uploads/products/6954c47147751_product_97_color_32.png', 0, 1, '2025-12-31 06:36:33', 1),
(135, 97, 52, 'uploads/products/6954c471487c3_product_97_color_52.png', 0, 1, '2025-12-31 06:36:33', 1),
(136, 97, 51, 'uploads/products/6954c47149814_product_97_color_51.png', 0, 1, '2025-12-31 06:36:33', 1),
(137, 97, 49, 'uploads/products/6954c4714a7d0_product_97_color_49.png', 0, 1, '2025-12-31 06:36:33', 1),
(138, 97, 34, 'uploads/products/6954c4714b61d_product_97_color_34.png', 0, 1, '2025-12-31 06:36:33', 1),
(139, 97, 50, 'uploads/products/6954c4714c8f9_product_97_color_50.png', 0, 1, '2025-12-31 06:36:33', 1),
(140, 98, 55, 'uploads/products/6954c5d9532d0_product_98_color_55.png', 0, 1, '2025-12-31 06:42:33', 1),
(141, 98, 58, 'uploads/products/6954c5d953fb7_product_98_color_58.png', 0, 1, '2025-12-31 06:42:33', 1),
(142, 98, 57, 'uploads/products/6954c5d954df0_product_98_color_57.png', 0, 1, '2025-12-31 06:42:33', 1),
(143, 98, 56, 'uploads/products/6954c5d955a30_product_98_color_56.png', 0, 1, '2025-12-31 06:42:33', 1),
(144, 98, 54, 'uploads/products/6954c5d956a6e_product_98_color_54.png', 0, 1, '2025-12-31 06:42:33', 1),
(145, 99, 44, 'uploads/products/6954c72a09a16_product_99_color_44.png', 0, 1, '2025-12-31 06:48:10', 1),
(146, 99, 40, 'uploads/products/6954c72a0ac86_product_99_color_40.png', 0, 1, '2025-12-31 06:48:10', 1),
(147, 99, 38, 'uploads/products/6954c72a0bae1_product_99_color_38.png', 0, 1, '2025-12-31 06:48:10', 1),
(148, 99, 45, 'uploads/products/6954c72a0c956_product_99_color_45.png', 0, 1, '2025-12-31 06:48:10', 1),
(149, 99, 59, 'uploads/products/6954c72a0d88c_product_99_color_59.png', 0, 1, '2025-12-31 06:48:10', 1),
(150, 100, 42, 'uploads/products/6954c9cbb64fc_product_100_color_42.png', 0, 1, '2025-12-31 06:59:23', 1),
(151, 100, 64, 'uploads/products/6954c9cbb7282_product_100_color_64.png', 0, 1, '2025-12-31 06:59:23', 1),
(152, 100, 63, 'uploads/products/6954c9cbb8101_product_100_color_63.png', 0, 1, '2025-12-31 06:59:23', 1),
(153, 100, 60, 'uploads/products/6954c9cbb8ea6_product_100_color_60.png', 0, 1, '2025-12-31 06:59:23', 1),
(154, 100, 9, 'uploads/products/6954c9cbb9b66_product_100_color_9.png', 0, 1, '2025-12-31 06:59:23', 1),
(155, 100, 59, 'uploads/products/6954c9cbba96e_product_100_color_59.png', 0, 1, '2025-12-31 06:59:23', 1),
(156, 101, 65, 'uploads/products/6954cbbdeb934_product_101_color_65.png', 0, 1, '2025-12-31 07:07:41', 1),
(157, 101, 68, 'uploads/products/6954cbbdec4bf_product_101_color_68.png', 0, 1, '2025-12-31 07:07:41', 1),
(158, 101, 66, 'uploads/products/6954cbbded60e_product_101_color_66.png', 0, 1, '2025-12-31 07:07:41', 1),
(159, 101, 67, 'uploads/products/6954cbbdee570_product_101_color_67.png', 0, 1, '2025-12-31 07:07:41', 1),
(160, 101, 69, 'uploads/products/6954cbbdef65a_product_101_color_69.png', 0, 1, '2025-12-31 07:07:41', 1),
(161, 102, 72, 'uploads/products/6954ccc5bf093_product_102_color_72.png', 0, 1, '2025-12-31 07:12:05', 1),
(162, 102, 74, 'uploads/products/6954ccc5bfde6_product_102_color_74.png', 0, 1, '2025-12-31 07:12:05', 1),
(163, 102, 73, 'uploads/products/6954ccc5c09fe_product_102_color_73.png', 0, 1, '2025-12-31 07:12:05', 1),
(164, 102, 71, 'uploads/products/6954ccc5c1a89_product_102_color_71.png', 0, 1, '2025-12-31 07:12:05', 1),
(165, 103, 78, 'uploads/products/6954cdf2746af_product_103_color_78.png', 0, 1, '2025-12-31 07:17:06', 1),
(166, 103, 76, 'uploads/products/6954cdf2758c2_product_103_color_76.png', 0, 1, '2025-12-31 07:17:06', 1),
(167, 103, 75, 'uploads/products/6954cdf2767a1_product_103_color_75.png', 0, 1, '2025-12-31 07:17:06', 1),
(168, 103, 79, 'uploads/products/6954cdf2775e5_product_103_color_79.png', 0, 1, '2025-12-31 07:17:06', 1),
(169, 103, 77, 'uploads/products/6954cdf27853d_product_103_color_77.png', 0, 1, '2025-12-31 07:17:06', 1),
(170, 104, 27, 'uploads/products/6954d16551a75_product_104_color_27.png', 0, 1, '2025-12-31 07:31:49', 1),
(171, 105, 68, 'uploads/products/6954d20b49165_product_105_color_68.png', 0, 1, '2025-12-31 07:34:35', 1),
(172, 106, 74, 'uploads/products/6954d2a5743eb_product_106_color_74.png', 0, 1, '2025-12-31 07:37:09', 1),
(173, 107, 62, 'uploads/products/6954d3138010d_product_107_color_62.png', 0, 1, '2025-12-31 07:38:59', 1),
(174, 108, 43, 'uploads/products/6954d3e523292_product_108_color_43.png', 0, 1, '2025-12-31 07:42:29', 1),
(175, 109, 75, 'uploads/products/6954eed851878_product_109_color_75.png', 0, 1, '2025-12-31 09:37:28', 1),
(176, 110, 61, 'uploads/products/6954f1a64857f_product_110_color_61.png', 0, 1, '2025-12-31 09:49:26', 1),
(177, 111, 39, 'uploads/products/6954f2425636f_product_111_color_39.png', 0, 1, '2025-12-31 09:52:02', 1),
(178, 120, 10, 'uploads/products/6954f6adb8d68_product_120_color_10.png', 0, 1, '2025-12-31 10:10:53', 1),
(179, 121, 47, 'uploads/products/6954f70be6c40_product_121_color_47.png', 0, 1, '2025-12-31 10:12:27', 1),
(180, 123, 57, 'uploads/products/6954f788de540_product_123_color_57.png', 0, 1, '2025-12-31 10:14:32', 1),
(181, 124, 73, 'uploads/products/6954f7c58c754_product_124_color_73.png', 0, 1, '2025-12-31 10:15:33', 1),
(182, 125, 28, 'uploads/products/6954f7fa5894b_product_125_color_28.png', 0, 1, '2025-12-31 10:16:26', 1),
(183, 126, 20, 'uploads/products/6954f86d3b488_product_126_color_20.png', 0, 1, '2025-12-31 10:18:21', 1),
(184, 127, 31, 'uploads/products/6954f8ef80506_product_127_color_31.png', 0, 1, '2025-12-31 10:20:31', 1),
(185, 128, 60, 'uploads/products/6954f99a0433d_product_128_color_60.png', 0, 1, '2025-12-31 10:23:22', 1),
(186, 129, 36, 'uploads/products/6954fa112c06d_product_129_color_36.png', 0, 1, '2025-12-31 10:25:21', 1),
(187, 130, 35, 'uploads/products/6954fa5166d05_product_130_color_35.png', 0, 1, '2025-12-31 10:26:25', 1),
(188, 131, 48, 'uploads/products/6954fa9536ae2_product_131_color_48.png', 0, 1, '2025-12-31 10:27:33', 1),
(189, 132, 66, 'uploads/products/6954fac5c772b_product_132_color_66.png', 0, 1, '2025-12-31 10:28:21', 1),
(190, 133, 8, 'uploads/products/6954fb314dd7e_product_133_color_8.png', 0, 1, '2025-12-31 10:30:09', 1),
(191, 134, 56, 'uploads/products/6954fc8720b4d_product_134_color_56.png', 0, 1, '2025-12-31 10:35:51', 1),
(192, 122, 37, 'uploads/products/69550a98c3a64_product_122_color_37.png', 0, 1, '2025-12-31 11:35:52', 1),
(193, 115, 63, 'uploads/products/69550abeac2cb_product_115_color_63.png', 0, 1, '2025-12-31 11:36:30', 1),
(194, 114, 79, 'uploads/products/69550ae5f1190_product_114_color_79.png', 0, 1, '2025-12-31 11:37:09', 1),
(195, 113, 58, 'uploads/products/69550b1003045_product_113_color_58.png', 0, 1, '2025-12-31 11:37:52', 1),
(196, 112, 29, 'uploads/products/69550b41c24d1_product_112_color_29.png', 0, 1, '2025-12-31 11:38:41', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_color_videos`
--

CREATE TABLE `product_color_videos` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_main_video` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_meta_tags`
--

CREATE TABLE `product_meta_tags` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `meta_tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_meta_tags`
--

INSERT INTO `product_meta_tags` (`id`, `product_id`, `meta_tag_id`) VALUES
(130, 17, 3),
(131, 17, 7),
(132, 17, 11),
(145, 20, 2),
(146, 20, 6),
(148, 20, 9),
(147, 20, 12),
(149, 21, 3),
(151, 21, 11),
(150, 21, 13),
(161, 24, 4),
(163, 24, 10),
(162, 24, 13),
(165, 25, 1),
(164, 25, 2),
(167, 25, 11),
(166, 25, 12),
(170, 26, 1),
(169, 26, 2),
(168, 26, 4),
(171, 26, 6),
(173, 26, 9),
(174, 26, 10),
(172, 26, 13),
(177, 27, 1),
(176, 27, 2),
(175, 27, 4),
(178, 27, 5),
(180, 27, 9),
(179, 27, 13),
(183, 28, 1),
(182, 28, 2),
(181, 28, 4),
(184, 28, 7),
(185, 28, 13),
(195, 32, 1),
(194, 32, 2),
(196, 32, 8),
(198, 32, 9),
(197, 32, 12),
(200, 33, 1),
(199, 33, 2),
(201, 33, 8),
(204, 33, 9),
(203, 33, 12),
(202, 33, 13),
(206, 34, 1),
(205, 34, 2),
(207, 34, 8),
(209, 34, 9),
(208, 34, 12),
(211, 35, 1),
(210, 35, 2),
(212, 35, 8),
(214, 35, 9),
(213, 35, 12),
(216, 36, 1),
(215, 36, 2),
(217, 36, 5),
(219, 36, 9),
(218, 36, 12),
(226, 38, 1),
(225, 38, 2),
(227, 38, 8),
(229, 38, 9),
(228, 38, 12),
(232, 39, 1),
(231, 39, 2),
(230, 39, 4),
(233, 39, 8),
(235, 39, 9),
(234, 39, 12),
(237, 40, 2),
(236, 40, 4),
(238, 40, 8),
(240, 40, 9),
(239, 40, 12),
(242, 41, 2),
(241, 41, 4),
(244, 42, 2),
(243, 42, 4),
(304, 49, 3),
(305, 49, 6),
(307, 49, 9),
(306, 49, 13),
(343, 74, 2),
(344, 74, 12),
(347, 75, 1),
(348, 75, 13),
(345, 76, 4),
(346, 76, 12),
(349, 78, 3),
(350, 78, 13),
(354, 79, 2),
(355, 79, 6),
(356, 79, 13),
(769, 80, 1),
(770, 80, 6),
(771, 80, 12),
(765, 81, 3),
(766, 81, 6),
(768, 81, 11),
(767, 81, 12),
(373, 82, 1),
(372, 82, 2),
(374, 82, 6),
(375, 82, 12),
(379, 83, 2),
(380, 83, 6),
(381, 83, 12),
(386, 84, 3),
(387, 84, 4),
(388, 84, 6),
(389, 84, 12),
(405, 85, 4),
(406, 85, 6),
(407, 85, 13),
(402, 86, 3),
(403, 86, 6),
(404, 86, 13),
(399, 87, 3),
(400, 87, 6),
(401, 87, 13),
(411, 88, 3),
(412, 88, 6),
(413, 88, 13),
(417, 89, 3),
(418, 89, 6),
(419, 89, 13),
(423, 90, 3),
(424, 90, 6),
(425, 90, 13),
(429, 91, 3),
(430, 91, 6),
(431, 91, 13),
(437, 93, 3),
(438, 93, 6),
(439, 93, 12),
(470, 94, 3),
(471, 94, 6),
(472, 94, 13),
(467, 95, 1),
(466, 95, 2),
(468, 95, 6),
(469, 95, 12),
(463, 96, 3),
(464, 96, 6),
(465, 96, 12),
(476, 97, 3),
(477, 97, 6),
(478, 97, 13),
(482, 98, 3),
(483, 98, 6),
(484, 98, 13),
(488, 99, 1),
(489, 99, 6),
(490, 99, 12),
(494, 100, 2),
(495, 100, 6),
(496, 100, 12),
(502, 101, 1),
(501, 101, 2),
(503, 101, 6),
(504, 101, 12),
(508, 102, 3),
(509, 102, 6),
(510, 102, 13),
(514, 103, 3),
(515, 103, 6),
(516, 103, 13),
(522, 104, 2),
(521, 104, 3),
(523, 104, 6),
(524, 104, 13),
(528, 105, 3),
(529, 105, 6),
(530, 105, 13),
(534, 106, 3),
(535, 106, 6),
(536, 106, 12),
(540, 107, 3),
(541, 107, 6),
(542, 107, 13),
(549, 108, 3),
(550, 108, 6),
(551, 108, 13),
(558, 109, 3),
(559, 109, 6),
(560, 109, 13),
(762, 110, 3),
(763, 110, 6),
(764, 110, 13),
(759, 111, 3),
(760, 111, 6),
(761, 111, 13),
(753, 112, 3),
(754, 112, 6),
(755, 112, 13),
(750, 113, 3),
(751, 113, 6),
(752, 113, 13),
(744, 114, 3),
(745, 114, 6),
(746, 114, 13),
(756, 115, 3),
(757, 115, 6),
(758, 115, 13),
(603, 120, 3),
(604, 120, 6),
(605, 120, 13),
(705, 121, 3),
(706, 121, 6),
(707, 121, 13),
(738, 122, 3),
(739, 122, 6),
(740, 122, 13),
(702, 123, 3),
(703, 123, 6),
(704, 123, 13),
(735, 124, 3),
(736, 124, 6),
(737, 124, 13),
(732, 125, 3),
(733, 125, 6),
(734, 125, 13),
(729, 126, 3),
(730, 126, 6),
(731, 126, 13),
(690, 127, 3),
(691, 127, 6),
(692, 127, 13),
(687, 128, 3),
(688, 128, 6),
(689, 128, 13),
(726, 129, 3),
(727, 129, 6),
(728, 129, 13),
(723, 130, 3),
(724, 130, 6),
(725, 130, 13),
(720, 131, 3),
(721, 131, 6),
(722, 131, 13),
(717, 132, 3),
(718, 132, 6),
(719, 132, 13),
(714, 133, 3),
(715, 133, 6),
(716, 133, 13),
(711, 134, 3),
(712, 134, 6),
(713, 134, 13);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','ordered','received','cancelled') DEFAULT 'pending',
  `order_date` date DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `received_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'boutique_name', 'SDesigner by Dinky', '2025-12-12 11:09:50', '2025-12-24 07:55:59'),
(2, 'location', 'Dilbagh Nagar, Jalandhar, Punjab', '2025-12-12 11:09:50', '2025-12-19 13:46:44'),
(3, 'primary_phone', '+917380097250', '2025-12-12 11:09:50', '2025-12-19 06:05:05'),
(4, 'secondary_phone', '+91 8968636373', '2025-12-12 11:09:50', '2025-12-19 13:46:44'),
(5, 'designer_name', 'Dinky Ahuja', '2025-12-12 11:09:50', '2025-12-17 11:47:35'),
(6, 'instagram_url', 'https://instagram.com/sdesigner_boutique', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(7, 'facebook_url', 'https://facebook.com/sdesignerdinky', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(8, 'reorder_level_default', '5', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(9, 'show_social_links', '1', '2025-12-12 11:09:50', '2025-12-19 05:44:54'),
(10, 'default_growth_rate', '8', '2025-12-12 11:09:50', '2025-12-23 12:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `size_charts`
--

CREATE TABLE `size_charts` (
  `id` int(11) NOT NULL,
  `chart_name` varchar(100) NOT NULL,
  `measurements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`measurements`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `size_charts`
--

INSERT INTO `size_charts` (`id`, `chart_name`, `measurements`, `created_at`) VALUES
(13, 'Indian', '{\"S\":{\"bust\":\"50\",\"waist\":\"32\",\"hips\":\"30\",\"length\":\"90\",\"shoulder\":\"12\"}}', '2025-12-24 08:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `stock_alerts`
--

CREATE TABLE `stock_alerts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `alert_type` enum('low_stock','out_of_stock','expiring') NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transactions`
--

CREATE TABLE `stock_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('purchase','sale','return','adjustment','damage','transfer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `performed_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `email`, `phone`, `address`, `status`, `created_at`) VALUES
(1, 'Fabric Paradise', 'Rajesh Kumar', 'rajesh@fabricparadise.com', '9876543210', NULL, 'active', '2025-12-11 11:59:40'),
(2, 'Silk World', 'Priya Sharma', 'priya@silkworld.com', '9876543211', NULL, 'active', '2025-12-11 11:59:40'),
(3, 'Zari Emporium', 'Amit Patel', 'amit@zariemporium.com', '9876543212', NULL, 'active', '2025-12-11 11:59:40'),
(4, 'Accessory House', 'Neha Gupta', 'neha@accessoryhouse.com', '9876543213', NULL, 'active', '2025-12-11 11:59:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marquee_messages`
--
ALTER TABLE `marquee_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meta_tags`
--
ALTER TABLE `meta_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `products_ibfk_2` (`size_chart_id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_color` (`product_id`,`color_id`),
  ADD KEY `color_id` (`color_id`);

--
-- Indexes for table `product_color_images`
--
ALTER TABLE `product_color_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_color_image` (`product_id`,`color_id`,`image_url`),
  ADD KEY `color_id` (`color_id`);

--
-- Indexes for table `product_color_videos`
--
ALTER TABLE `product_color_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_color_video` (`product_id`,`color_id`,`video_url`),
  ADD KEY `color_id` (`color_id`);

--
-- Indexes for table `product_meta_tags`
--
ALTER TABLE `product_meta_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_meta` (`product_id`,`meta_tag_id`),
  ADD KEY `meta_tag_id` (`meta_tag_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `size_charts`
--
ALTER TABLE `size_charts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `marquee_messages`
--
ALTER TABLE `marquee_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `meta_tags`
--
ALTER TABLE `meta_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=400;

--
-- AUTO_INCREMENT for table `product_color_images`
--
ALTER TABLE `product_color_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `product_color_videos`
--
ALTER TABLE `product_color_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_meta_tags`
--
ALTER TABLE `product_meta_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=772;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `size_charts`
--
ALTER TABLE `size_charts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`size_chart_id`) REFERENCES `size_charts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_colors_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_color_images`
--
ALTER TABLE `product_color_images`
  ADD CONSTRAINT `product_color_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_color_images_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_color_videos`
--
ALTER TABLE `product_color_videos`
  ADD CONSTRAINT `product_color_videos_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_color_videos_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_meta_tags`
--
ALTER TABLE `product_meta_tags`
  ADD CONSTRAINT `product_meta_tags_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_meta_tags_ibfk_2` FOREIGN KEY (`meta_tag_id`) REFERENCES `meta_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD CONSTRAINT `stock_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  ADD CONSTRAINT `stock_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
