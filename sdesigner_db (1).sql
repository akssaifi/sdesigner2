-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 08:24 AM
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
-- Database: `sdesigner_db`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `generate_order_number` () RETURNS VARCHAR(50) CHARSET utf8mb4 COLLATE utf8mb4_general_ci  BEGIN
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
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Lehengas', 'lehengas'),
(2, 'Sarees', 'sarees'),
(3, 'Salwar Suits', 'salwar-suits'),
(4, 'Gowns', 'gowns'),
(5, 'Kurtas', 'kurtas'),
(6, 'Fabrics', 'fabrics'),
(8, 'Accessories', 'accessories'),
(11, 'Blazers', 'blazers'),
(12, 'Plazo', 'plazo');

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
(16, 'Yellow', '#fae505', '2025-12-18 14:04:54');

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
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_type` enum('stitched','unstitched','accessory') NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
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

INSERT INTO `products` (`id`, `product_type`, `name`, `price`, `category_id`, `size_chart_id`, `stock_quantity`, `is_active`, `created_at`, `reorder_level`, `stock_status`, `last_restocked`) VALUES
(17, 'accessory', 'GIVA Women\'s Classic Solitaire Ring', 3900.00, 8, 4, 65, 1, '2025-12-18 11:15:20', 5, 'in_stock', NULL),
(20, 'stitched', 'Ethnic Set Women Block Printed Kurta set with Dupatta', 5000.00, 5, NULL, 34, 1, '2025-12-18 13:23:02', 5, 'in_stock', NULL),
(21, 'accessory', 'Bracelet', 200.00, 8, NULL, 90, 1, '2025-12-18 13:33:23', 5, 'in_stock', NULL),
(24, 'accessory', 'Chain', 2000.00, 8, NULL, 29, 1, '2025-12-18 13:36:33', 5, 'in_stock', NULL),
(25, 'accessory', 'Necklace', 3000.00, 8, NULL, 30, 1, '2025-12-18 13:37:36', 5, 'in_stock', NULL),
(26, 'stitched', 'Blazers for men', 6000.00, 11, 1, 12, 1, '2025-12-18 13:41:53', 5, 'in_stock', NULL),
(27, 'stitched', 'Blazers for women', 7000.00, 11, 1, 17, 1, '2025-12-18 13:42:42', 5, 'in_stock', NULL),
(28, 'stitched', 'SHUZHXLZANGY Mens Blazers and Sport Coats Slim Fit', 5000.00, 11, 1, 40, 1, '2025-12-18 13:44:04', 5, 'in_stock', NULL),
(29, 'unstitched', 'Wool fabric', 2000.00, 6, NULL, 20, 1, '2025-12-18 13:51:39', 5, 'in_stock', NULL),
(30, 'unstitched', 'Cotton fabric', 300.00, 6, NULL, 16, 1, '2025-12-18 13:52:49', 5, 'in_stock', NULL),
(31, 'unstitched', 'Silk fabric', 400.00, 6, NULL, 16, 1, '2025-12-18 13:53:35', 5, 'in_stock', NULL),
(32, 'stitched', 'Gown', 4000.00, 4, 1, 10, 1, '2025-12-18 13:55:50', 5, 'in_stock', NULL),
(33, 'stitched', 'Royal Blue Gown for women', 7000.00, 4, 1, 30, 1, '2025-12-18 13:56:59', 5, 'in_stock', NULL),
(34, 'stitched', 'Black Colour Gown for Females', 6000.00, 4, 1, 60, 1, '2025-12-18 13:57:45', 5, 'in_stock', NULL),
(35, 'stitched', 'Pure white Gown for Women', 4000.00, 4, 1, 30, 1, '2025-12-18 13:58:44', 5, 'in_stock', NULL),
(36, 'stitched', 'Cherry color Lehanga for girls', 10000.00, 1, 1, 23, 1, '2025-12-18 14:00:51', 5, 'in_stock', NULL),
(38, 'stitched', 'Multi colour Lehanga for Women', 10000.00, 1, 1, 20, 1, '2025-12-18 14:03:20', 5, 'in_stock', NULL),
(39, 'stitched', 'Plazo Suit for Women', 1000.00, 12, 1, 12, 1, '2025-12-18 14:05:42', 5, 'in_stock', NULL),
(40, 'stitched', 'Full Plazo Suit for women', 1000.00, 12, NULL, 32, 1, '2025-12-18 14:06:25', 5, 'in_stock', NULL),
(41, 'stitched', 'Maroon colour Salwar Suit', 5000.00, 3, 1, 12, 1, '2025-12-18 14:07:44', 5, 'in_stock', NULL),
(42, 'stitched', 'Yellow colured Salwar Suit', 1000.00, 3, 1, 20, 1, '2025-12-18 14:08:17', 5, 'in_stock', NULL),
(43, 'stitched', 'Green Colour Saree For Women', 3000.00, 2, 1, 12, 1, '2025-12-18 14:09:40', 5, 'in_stock', NULL),
(44, 'stitched', 'Pink colur Saree For Girls', 4000.00, 2, 1, 34, 1, '2025-12-18 14:10:17', 5, 'in_stock', '2025-12-19 06:15:21');

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
(69, 29, 15),
(70, 30, 2),
(71, 31, 10),
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
(83, 43, 3),
(92, 44, 8),
(91, 44, 14);

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
(43, 29, 15, 'uploads/products/694406eb86d29_product_29_color_15.jpg', 0, 1, '2025-12-18 13:51:39', 1),
(44, 30, 2, 'uploads/products/69440731c65e2_product_30_color_2.jpg', 0, 1, '2025-12-18 13:52:49', 1),
(45, 31, 10, 'uploads/products/6944075fd678b_product_31_color_10.jpg', 0, 1, '2025-12-18 13:53:35', 1),
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
(56, 43, 3, 'uploads/products/69440b244e4fa_product_43_color_3.webp', 0, 1, '2025-12-18 14:09:40', 1),
(63, 44, 14, 'uploads/products/6944d5d512c1e_product_44_color_14.webp', 0, 1, '2025-12-19 04:34:29', 1),
(64, 44, 14, 'uploads/products/6944d5d513b5a_product_44_color_14.webp', 0, 2, '2025-12-19 04:34:29', 0),
(65, 44, 8, 'uploads/products/6944d5f1675ad_product_44_color_8.webp', 0, 1, '2025-12-19 04:34:57', 1);

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
(186, 29, 3),
(187, 29, 4),
(188, 29, 13),
(189, 30, 3),
(190, 30, 4),
(191, 30, 6),
(192, 31, 3),
(193, 31, 5),
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
(247, 43, 1),
(246, 43, 2),
(245, 43, 4),
(258, 44, 2),
(257, 44, 4);

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
(1, 'boutique_name', 'SDesigner', '2025-12-12 11:09:50', '2025-12-19 05:44:41'),
(2, 'location', 'Jalandhar, Punjab', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(3, 'primary_phone', '+917380097250', '2025-12-12 11:09:50', '2025-12-19 06:05:05'),
(4, 'secondary_phone', '+91 9814927250', '2025-12-12 11:09:50', '2025-12-19 06:05:05'),
(5, 'designer_name', 'Dinky Ahuja', '2025-12-12 11:09:50', '2025-12-17 11:47:35'),
(6, 'instagram_url', 'https://instagram.com/sdesigner_boutique', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(7, 'facebook_url', 'https://facebook.com/sdesignerdinky', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(8, 'reorder_level_default', '5', '2025-12-12 11:09:50', '2025-12-12 11:09:50'),
(9, 'show_social_links', '1', '2025-12-12 11:09:50', '2025-12-19 05:44:54'),
(10, 'default_growth_rate', '8', '2025-12-12 11:09:50', '2025-12-15 13:40:39');

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
(1, 'Standard Indian', '{\"S\": {\"bust\": \"34\", \"waist\": \"30\", \"hip\": \"36\"}, \"M\": {\"bust\": \"36\", \"waist\": \"32\", \"hip\": \"38\"}, \"L\": {\"bust\": \"38\", \"waist\": \"34\", \"hip\": \"40\"}}', '2025-12-11 11:56:51'),
(4, 'Europenan style', '{\"S\":{\"bust\":\"Bust(30)\",\"waist\":\"waist(30)\",\"hip\":\"Hip(36)\"},\"M\":{\"bust\":\"Bust(34)\",\"waist\":\"Waist(34)\",\"hip\":\"Hip(38)\"}}', '2025-12-13 09:59:17');

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

--
-- Dumping data for table `stock_transactions`
--

INSERT INTO `stock_transactions` (`id`, `product_id`, `transaction_type`, `quantity`, `previous_quantity`, `new_quantity`, `notes`, `reason`, `reference_number`, `performed_by`, `created_at`) VALUES
(20, 44, 'adjustment', -22, 21, 0, '', NULL, NULL, 'Admin', '2025-12-19 06:14:39'),
(21, 44, 'adjustment', 34, 0, 34, '', NULL, NULL, 'Admin', '2025-12-19 06:15:21');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `product_color_images`
--
ALTER TABLE `product_color_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `product_color_videos`
--
ALTER TABLE `product_color_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_meta_tags`
--
ALTER TABLE `product_meta_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=259;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_transactions`
--
ALTER TABLE `stock_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
