-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 03:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tastyph2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('admin','user') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`, `usertype`, `created_at`) VALUES
(1, 'admin1@gmail.com', 'admin', 'admin', '2024-11-20 15:22:20'),
(2, 'admin@example.com', '$2y$10$eA9k0VZTjZTeIH9c6u2SjePPlIHt8SHgzd9wYOgD5Ek9TOv6N6iuK', 'admin', '2024-11-20 15:52:14'),
(4, 'admin@xample.com', '$2y$10$gJP5EqPIP6/ZDMF1FemoPOtxk26uNHI9lN6FFz5f9g7mJkkBs2fsG', 'admin', '2024-11-21 04:17:35');

-- --------------------------------------------------------

--
-- Table structure for table `apply_seller`
--

CREATE TABLE `apply_seller` (
  `seller_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `business_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `health_permit` varchar(255) DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `profile_pics` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apply_seller`
--

INSERT INTO `apply_seller` (`seller_id`, `id`, `business_name`, `description`, `address`, `business_permit`, `health_permit`, `application_date`, `status`, `profile_pics`) VALUES
(1, NULL, 'AJM STORE', 'lami na kakanin diri sa toril\r\n', 'asd', 'upload/Screenshot (34).png', 'upload/Screenshot 2024-07-23 222727.png', '2024-11-20 15:07:55', 'approved', '../uploads/1_store_1741706842.jpg'),
(14, NULL, 'Arnel Kakanin', 'Good quality kakanin', 'Upperpiedad', 'upload/jobelle.jpg', 'upload/jobelle.jpg', '2024-12-19 10:19:54', 'approved', NULL),
(18, NULL, 'Salta ', 'kakanin', 'Daliao', 'upload/f50ad21a8af6d8f55fed07471477349e.jpg', 'upload/f50ad21a8af6d8f55fed07471477349e.jpg', '2024-12-19 10:40:44', 'approved', NULL),
(20, NULL, 'Dcc kakanin', 'Kakanin', 'Toril', 'upload/buseiness_permit.jpg', 'upload/images (1).jpg', '2025-02-10 09:30:03', 'approved', NULL),
(21, NULL, 'MIKIIIII', 'HEYYYYY', 'CEBU', '../image/buseiness_permit.jpg', '../image/images (1).jpg', '2025-03-08 06:35:00', 'approved', '../uploads/21_store_1741925792.webp'),
(24, NULL, 'MILI STORE', 'YAWA', 'CEBU', '../image/43eb94095273029b09ed6c9752c56499.jpg', '../image/Banana_Leaves_1600x.jpg', '2025-03-26 08:18:42', 'approved', '../uploads/24_store_1742977295.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `apply_supplier`
--

CREATE TABLE `apply_supplier` (
  `supplier_id` int(11) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `health_permit` varchar(255) DEFAULT NULL,
  `application_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `profile_pics` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apply_supplier`
--

INSERT INTO `apply_supplier` (`supplier_id`, `business_name`, `description`, `address`, `business_permit`, `health_permit`, `application_date`, `status`, `profile_pics`) VALUES
(12, 'DOLLRAIN', 'HAIIIII', 'asd', 'image/jobelle.jpg', 'image/jobelle.jpg', '2024-12-18 21:18:08', 'approved', '../uploads/12_supplier_1741891898.jpg'),
(15, 'Arellano Supplies', 'all types of ingredients', 'Palengke, Toril', 'image/jobelle.jpg', 'image/466163394_532193369639637_7208389942889366768_n.jpg', '2024-12-19 18:24:14', 'approved', NULL),
(23, 'TWICE', 'asd', 'korea', '../image/wallpaper.png', '../image/ube.jpg', '2025-03-14 03:26:48', 'approved', '../uploads/23_supplier_1741897225.png');

-- --------------------------------------------------------

--
-- Table structure for table `business_hours`
--

CREATE TABLE `business_hours` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_type` enum('seller','supplier') NOT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_hours`
--

INSERT INTO `business_hours` (`id`, `user_id`, `business_type`, `day_of_week`, `open_time`, `close_time`, `is_available`) VALUES
(8, 1, 'seller', 'Sunday', NULL, NULL, 0),
(9, 1, 'seller', 'Monday', '07:30:00', '19:30:00', 1),
(10, 1, 'seller', 'Tuesday', '07:30:00', '19:30:00', 1),
(11, 1, 'seller', 'Wednesday', '07:30:00', '19:30:00', 1),
(12, 1, 'seller', 'Thursday', '07:30:00', '19:30:00', 1),
(13, 1, 'seller', 'Friday', '07:30:00', '19:30:00', 1),
(14, 1, 'seller', 'Saturday', NULL, NULL, 0),
(15, 21, 'seller', 'Sunday', NULL, NULL, 1),
(16, 21, 'seller', 'Monday', NULL, NULL, 1),
(17, 21, 'seller', 'Tuesday', NULL, NULL, 1),
(18, 21, 'seller', 'Wednesday', NULL, NULL, 1),
(19, 21, 'seller', 'Thursday', NULL, NULL, 1),
(20, 21, 'seller', 'Friday', NULL, NULL, 1),
(21, 21, 'seller', 'Saturday', NULL, NULL, 1),
(22, 24, 'seller', 'Sunday', '07:00:00', '21:00:00', 1),
(23, 24, 'seller', 'Monday', NULL, NULL, 0),
(24, 24, 'seller', 'Tuesday', NULL, NULL, 0),
(25, 24, 'seller', 'Wednesday', NULL, NULL, 0),
(26, 24, 'seller', 'Thursday', NULL, NULL, 0),
(27, 24, 'seller', 'Friday', NULL, NULL, 0),
(28, 24, 'seller', 'Saturday', '07:00:00', '19:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `ingredient_id`, `variant_id`, `quantity`, `total_price`, `added_at`) VALUES
(30, 21, 34, NULL, NULL, 100, 5000.00, '2025-03-14 17:25:58'),
(31, 21, NULL, 3, NULL, 1, 86.00, '2025-03-14 17:26:09'),
(32, 21, NULL, 5, NULL, 23, 644.00, '2025-03-14 17:28:03'),
(33, 21, NULL, 2, NULL, 1, 180.00, '2025-03-14 17:28:25'),
(35, 21, NULL, 4, NULL, 4, 276.00, '2025-03-14 17:31:25'),
(58, 13, NULL, 7, NULL, 1, 100.00, '2025-03-15 03:59:20'),
(112, 12, 34, NULL, NULL, 1, 50.00, '2025-04-25 21:44:05'),
(116, 12, NULL, 7, NULL, 1, 100.00, '2025-05-11 15:03:53');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(4, 'asd'),
(10, 'fine shyt'),
(8, 'GYATTTTT'),
(7, 'ohio'),
(1, 'Rice Cake'),
(6, 'skibidi rice cake'),
(2, 'Steamed Rice'),
(3, 'sticky rice'),
(9, 'YUTA');

-- --------------------------------------------------------

--
-- Table structure for table `checkout`
--

CREATE TABLE `checkout` (
  `checkout_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('cod','gcash') DEFAULT 'cod',
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `checkout_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `ingredient_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `quantity_value` int(11) NOT NULL DEFAULT 1,
  `unit_type` varchar(50) NOT NULL DEFAULT 'pcs',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `supplier_id`, `ingredient_name`, `description`, `price`, `quantity`, `quantity_value`, `unit_type`, `created_at`, `category_id`, `image_url`) VALUES
(1, 12, 'lumpia wrapper', '30 pcs per Pack ', 28.00, -4, 30, 'pcs', '2024-12-18 14:23:37', 4, '../uploads/1741929792_lumpia.jpg'),
(2, 12, 'Ube', 'Per Kilo', 180.00, 7, 300, 'g', '2024-12-18 14:27:16', 4, '../uploads/1741929804_ube.jpeg'),
(3, 12, 'Condense Milk', '1 can = 300g\r\n', 86.00, 22, 300, 'g', '2024-12-18 14:31:44', 6, '../uploads/1741929823_alaska.jpg'),
(4, 12, 'Ube Flavor', 'per bottle', 69.00, 23, 1, 'bottle', '2024-12-18 14:34:13', 2, '../uploads/1741929833_f50ad21a8af6d8f55fed07471477349e.jpg'),
(5, 12, 'Oil', '1kg = 1000g', 28.00, 23, 1000, 'ml', '2024-12-18 14:38:09', 4, '../uploads/1741929842_135663.jpg'),
(6, 12, 'Cheese', '430g', 230.00, 23, 430, 'g', '2025-02-09 15:59:56', 4, '../uploads/1741929849_71bkb43LnhS.jpg'),
(7, 12, 'Malagkit', '1kg = 1000g\r\n', 100.00, 23, 1000, 'g', '2025-02-09 16:00:50', 4, '../uploads/1741929856_61230b78ac6346892634bc56b1391b75.jpg'),
(8, 12, 'Evaporated Milk', '1 can = 430g', 80.00, 23, 430, 'g', '2025-02-09 16:01:54', 4, '../uploads/1741929865_images (2).jpg'),
(9, 12, 'asdas', 'asdasd', 11.00, 30, 20, 'bottle', '2025-02-09 16:44:59', 4, 'uploads/1739119499_images.jpg'),
(10, 23, 'Sugar', 'asukal', 25.00, 10, 250, 'g', '2025-03-22 06:00:39', 8, '../uploads/1742623239_images (9).jpg'),
(11, 23, 'Rice Flour', '1000g = 1kg', 80.00, 20, 1000, 'g', '2025-03-22 06:15:23', 4, '../uploads/1742624123_images (10).jpg'),
(12, 23, 'Salt', '1000g = 1kg', 40.00, 10, 1000, 'g', '2025-03-22 06:18:42', 4, '../uploads/1742624322_362ad73b4015eb4d0d9e59f6bc8a52fb_large.png'),
(13, 23, 'Baking Powder', 'powder', 55.00, 10, 200, 'g', '2025-03-22 06:20:44', 4, '../uploads/1742624444_4564dc886b28068f441538b7b209eb20_large.png'),
(14, 23, 'Butter', 'asd', 100.00, 10, 100, 'g', '2025-03-22 06:22:22', 4, '../uploads/1742624542_4805358206045-1_1.jpg'),
(15, 23, 'Parmesan Cheese', 'Grated cheese', 400.00, 10, 500, 'g', '2025-03-22 06:25:55', 4, '../uploads/1742624755_armesan135g_Front_211111065339.jpg'),
(16, 23, 'Coconut Milk', 'asd', 50.00, 10, 165, 'ml', '2025-03-22 06:27:32', 4, '../uploads/1742624852_images (11).jpg'),
(17, 23, 'Fresh Milk', 'asd', 50.00, 10, 250, 'ml', '2025-03-22 06:28:32', 4, '../uploads/1742624912_images (12).jpg'),
(18, 23, 'Salted Duck Egg', 'large, 25 pesos per piece', 25.00, 118, 1, 'pcs', '2025-03-22 06:31:20', 4, '../uploads/1742625080_images (13).jpg'),
(19, 23, 'Grated Coconut', 'asd', 180.00, 10, 500, 'g', '2025-03-22 06:32:18', 4, '../uploads/1742625138_images (14).jpg'),
(20, 23, 'Raw egg', 'Large,10 pesos per piece', 10.00, 120, 1, 'pcs', '2025-03-22 06:33:43', 4, '../uploads/1742625223_free-range-brown-eggs-full-tray-30-1.jpg'),
(21, 23, 'Fresh Banana Leaves', '10 inches per cut', 80.00, 50, 250, 'g', '2025-03-22 06:36:03', 4, '../uploads/1742625363_Banana_Leaves_1600x.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `ingredients_inventory`
--

CREATE TABLE `ingredients_inventory` (
  `inventory_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_value` int(11) NOT NULL DEFAULT 1,
  `unit_type` varchar(50) NOT NULL DEFAULT 'pcs',
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `variant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients_inventory`
--

INSERT INTO `ingredients_inventory` (`inventory_id`, `ingredient_id`, `ingredient_name`, `description`, `quantity`, `quantity_value`, `unit_type`, `price`, `seller_id`, `supplier_id`, `user_id`, `date_added`, `variant_id`) VALUES
(1, 5, 'Oil', '1kg = 1000g', 1, 1, 'pcs', 28.00, NULL, 12, NULL, '2025-03-17 16:09:29', NULL),
(2, 4, 'Ube Flavor', 'per bottle', 1, 1, 'pcs', 69.00, NULL, 12, NULL, '2025-03-17 16:10:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ingredient_variants`
--

CREATE TABLE `ingredient_variants` (
  `variant_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `variant_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_value` int(11) NOT NULL,
  `unit_type` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredient_variants`
--

INSERT INTO `ingredient_variants` (`variant_id`, `ingredient_id`, `variant_name`, `price`, `quantity`, `quantity_value`, `unit_type`, `image_url`, `created_at`, `product_id`) VALUES
(1, 1, 'lumpia wrapper	', 20.00, 90, 10, 'pcs', '../uploads/pork-lumpia-shanghai-wrappers.jpg', '2025-03-28 07:30:30', NULL),
(2, 1, 'lumpia wrapper', 15.00, 190, 5, 'pcs', '../uploads/images (5).jpg', '2025-03-28 08:40:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `reply_to` int(11) DEFAULT NULL,
  `pinned` tinyint(1) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `reactions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `message_text`, `timestamp`, `is_read`, `reply_to`, `pinned`, `image_url`, `reactions`) VALUES
(1, 12, 1, 'asd', '2024-12-18 21:44:39', 0, NULL, 0, NULL, NULL),
(2, 1, 12, 'asd', '2024-12-19 00:45:17', 0, NULL, 0, NULL, NULL),
(3, 1, 19, 'asd', '2025-02-09 21:13:59', 0, NULL, 0, NULL, NULL),
(4, 1, 19, 'asdsadas', '2025-02-09 21:14:09', 0, NULL, 0, NULL, NULL),
(5, 19, 1, 'asdsad', '2025-02-09 21:14:31', 0, NULL, 0, NULL, NULL),
(6, 19, 1, 'a', '2025-02-09 21:15:31', 0, NULL, 0, NULL, NULL),
(7, 1, 1, 'helllo i dont have turon but are you still interested e', '2025-02-10 14:46:06', 0, NULL, 0, NULL, NULL),
(8, 1, 20, 'kulang akong turon pero interesado baka na mabuhat nako ugma', '2025-02-10 17:53:41', 0, NULL, 0, NULL, NULL),
(9, 20, 1, 'bubble wrap nalang oi', '2025-02-10 17:54:38', 0, NULL, 0, NULL, NULL),
(10, 1, 12, 'ASD', '2025-03-04 19:31:01', 0, NULL, 0, NULL, NULL),
(11, 1, 12, 'lmao kaba bossing', '2025-03-04 19:52:36', 0, NULL, 0, NULL, NULL),
(12, 1, 12, 'asdasd', '2025-03-04 20:03:56', 0, NULL, 0, NULL, NULL),
(13, 1, 12, 'asd', '2025-03-04 20:16:37', 0, NULL, 0, NULL, NULL),
(14, 1, 12, '', '2025-03-04 20:35:27', 0, NULL, 0, 'image/1741091727_monkey.png', ''),
(15, 1, 12, 'haha ikaw ni?\r\n', '2025-03-04 20:37:49', 0, NULL, 0, 'image/1741091869_DONUT BASE.png', ''),
(16, 1, 12, 'asdasdas', '2025-03-04 20:40:41', 0, NULL, 0, 'image/1741092041_monkey.png', ''),
(17, 1, 13, 'sadas', '2025-03-04 23:31:57', 0, NULL, 0, '', ''),
(18, 1, 13, 'asd', '2025-03-04 23:32:33', 0, NULL, 0, '', ''),
(19, 1, 13, '', '2025-03-04 23:32:41', 0, NULL, 0, 'image/1741102361_RobloxScreenShot20250109_151614763.png', ''),
(20, 1, 13, 'asdasd', '2025-03-04 23:32:44', 0, NULL, 0, '', ''),
(21, 1, 13, 'sadasd', '2025-03-04 23:32:46', 0, NULL, 0, '', ''),
(22, 1, 16, 'kini dai lami kaayo ni sya', '2025-03-04 23:33:28', 0, NULL, 0, 'image/1741102408_Turon.jpg', ''),
(23, 1, 16, 'asdsa', '2025-03-04 23:37:58', 0, NULL, 0, '', ''),
(24, 1, 20, 'kini baks okay ni sya gamiton?', '2025-03-04 23:38:56', 0, NULL, 0, 'image/1741102736_images (4).jpg', ''),
(25, 1, 17, 'asdas', '2025-03-05 13:42:15', 0, NULL, 0, 'image/1741153335_images (4).jpg', ''),
(26, 1, 17, '', '2025-03-05 13:42:34', 0, NULL, 0, 'image/1741153354_475531475_3904091753166435_845772105608925287_n.jpg', ''),
(27, 1, 17, 'LISA', '2025-03-05 13:44:21', 0, NULL, 0, 'image/1741153461_2612b0bf-7b20-487b-a924-d2171c3f9565.jpg', ''),
(28, 21, 1, 'sadsa', '2025-03-08 14:15:21', 0, NULL, 0, '', ''),
(29, 21, 1, 'ohhh diggin\' in me', '2025-03-08 14:16:31', 0, NULL, 0, '', ''),
(30, 21, 1, 'asdasd', '2025-03-08 14:16:42', 0, NULL, 0, '', ''),
(31, 21, 1, 'asdasd', '2025-03-08 14:19:04', 0, NULL, 0, 'image/1741414744_TASTYPH PS STORYBOARD..png', ''),
(32, 21, 1, 'sads', '2025-03-08 14:20:48', 0, NULL, 0, '', ''),
(33, 21, 1, '', '2025-03-08 14:20:52', 0, NULL, 0, 'image/1741414852_miki.jpg', ''),
(37, 1, 12, 'ugma pako ka restock ok ra?\r\n', '2025-03-08 15:37:32', 0, NULL, 0, '', ''),
(38, 12, 1, 'yes', '2025-03-08 15:37:50', 0, NULL, 0, '', ''),
(39, 1, 21, 'ugma pa ok ra?', '2025-03-08 15:40:07', 0, NULL, 0, '', ''),
(41, 1, 21, 'i love you lollll', '2025-03-12 01:09:03', 0, NULL, 0, '', ''),
(42, 1, 21, '', '2025-03-12 01:09:25', 0, NULL, 0, '../image/1741712965_TASTYPH PS STORYBOARD..png', ''),
(43, 1, 21, '', '2025-03-12 01:10:52', 0, NULL, 0, '../image/1741713052_miki.jpg', ''),
(44, 1, 21, 'asd', '2025-03-12 01:12:10', 0, NULL, 0, '', ''),
(45, 1, 21, '', '2025-03-12 01:18:04', 0, NULL, 0, 'uploads/1741713484_miki.jpg', ''),
(46, 1, 21, 'look at that fine shyyttt', '2025-03-12 01:18:16', 0, NULL, 0, '', ''),
(47, 1, 21, '', '2025-03-12 01:25:42', 0, NULL, 0, 'uploads/1741713942_GUTANG_TASTYPH.png', '❤️'),
(50, 21, 1, '1', '2025-03-12 02:37:14', 0, NULL, 0, NULL, ''),
(51, 21, 1, 'hhh', '2025-03-12 02:54:02', 0, NULL, 0, NULL, ''),
(52, 21, 1, 'ite', '2025-03-12 14:31:52', 0, NULL, 0, 'uploads/1741761112_474251895_590224470591878_2850756471589785287_n.jpg', ''),
(53, 1, 21, 'u look good here shawtyy❤️❤️😍😍😍', '2025-03-13 17:48:51', 0, NULL, 0, 'uploads/1741859331_5cc09ad47b7c1c180db37126c30ac6dc.jpg', ''),
(54, 12, 21, 'hi miss', '2025-03-14 01:26:37', 0, NULL, 0, NULL, ''),
(55, 23, 21, 'HIIIIIIIIIII', '2025-03-14 03:23:05', 0, NULL, 0, 'uploads/1741893785_images (5).jpg', ''),
(56, 21, 23, 'deymmm u look hot asf', '2025-03-14 03:23:25', 0, NULL, 0, NULL, ''),
(57, 1, 12, 'weq', '2025-03-15 14:10:57', 0, NULL, 0, 'uploads/1742019057_images (7).jpg', ''),
(58, 1, 23, 'lima lang akong turon pwede ugma mag restock ko', '2025-03-15 14:47:29', 0, NULL, 0, NULL, ''),
(59, 23, 1, 'okay', '2025-03-15 14:48:06', 0, NULL, 0, NULL, ''),
(60, 24, 21, 'HAI', '2025-03-26 16:23:45', 0, NULL, 0, NULL, ''),
(61, 1, 15, '', '2025-05-11 17:03:24', 0, NULL, 0, 'uploads/1746954204_f2678c157ca3b7e20968ce5fb4a96ba3.jpg,uploads/1746954204_Infographic - Protect Your Data.jpg,uploads/1746954204_Untitled design.png', ''),
(62, 1, 15, 'asd', '2025-05-11 17:03:29', 0, NULL, 0, NULL, ''),
(63, 1, 12, 'i love you', '2025-05-11 17:04:34', 0, NULL, 0, NULL, ''),
(64, 1, 21, 'i miss you so much', '2025-05-11 17:04:54', 0, NULL, 0, NULL, ''),
(65, 1, 15, 'asd', '2025-05-11 17:07:20', 0, NULL, 0, NULL, ''),
(66, 1, 15, 'asdas', '2025-05-17 09:00:35', 0, NULL, 0, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `created_at`) VALUES
(1, 19, 1, 'New pre-order request for: asdasd (Qty: 1).', 1, '2025-02-09 11:56:12'),
(2, 1, 1, 'New pre-order request for: asdas (Qty: 1).', 1, '2025-02-09 12:06:54'),
(3, 1, 1, 'New pre-order request for: Turon (Qty: 1).', 1, '2025-02-10 06:45:01'),
(4, 20, 1, 'New pre-order request for: Turon (Qty: 50).', 1, '2025-02-10 09:50:05'),
(5, 12, 1, 'New pre-order request for: bibingka (Qty: 5).', 1, '2025-03-08 07:36:50'),
(6, 21, 1, 'New pre-order request for: Ube Cheese Turon (Qty: 20).', 1, '2025-03-08 07:39:34'),
(7, 1, 1, 'New pre-order request for: Turon (Qty: 1).', 1, '2025-03-13 08:44:30'),
(8, 1, 1, 'New pre-order request for: Turon (Qty: 2).', 1, '2025-03-13 13:25:35'),
(9, 1, 1, 'New pre-order request for: bibingkaa (Qty: 1).', 1, '2025-03-13 13:38:00'),
(10, 1, 1, 'New pre-order request for: Turon (Qty: 1).', 1, '2025-03-13 13:55:56'),
(11, 23, 1, 'New pre-order request for: Turon (Qty: 20).', 1, '2025-03-13 19:11:46'),
(12, 1, 1, 'New order #119 has been placed!', 1, '2025-05-11 10:39:20'),
(13, 1, 1, 'New order #121 has been placed!', 1, '2025-05-11 10:41:27'),
(14, 1, 1, 'New order #122 has been placed!', 1, '2025-05-14 06:46:23');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` enum('cash','gcash') NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Order Confirmed','Packed','Delivered','Cancelled') DEFAULT 'Pending',
  `confirmed` int(11) DEFAULT 0,
  `seller_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `payment_method`, `payment_proof`, `total_price`, `order_date`, `status`, `confirmed`, `seller_id`, `supplier_id`) VALUES
(64, 1, 'cash', NULL, 0.00, '2025-03-14 19:20:49', 'Pending', 0, NULL, NULL),
(65, 1, 'cash', NULL, 1800.00, '2025-03-14 19:58:54', 'Pending', 0, NULL, NULL),
(66, 1, 'cash', NULL, 10.00, '2025-03-14 19:59:52', 'Pending', 0, NULL, NULL),
(67, 1, 'cash', NULL, 10.00, '2025-03-14 20:01:02', 'Pending', 0, NULL, NULL),
(68, 1, 'cash', NULL, 10.00, '2025-03-14 20:16:21', 'Pending', 0, NULL, NULL),
(78, 12, 'cash', NULL, 0.00, '2025-03-15 03:06:41', 'Cancelled', 0, NULL, NULL),
(79, 12, 'cash', NULL, 0.00, '2025-03-15 03:08:38', 'Pending', 0, NULL, NULL),
(80, 12, 'cash', NULL, 0.00, '2025-03-15 03:09:43', 'Pending', 0, NULL, NULL),
(81, 12, 'cash', NULL, 0.00, '2025-03-15 03:13:51', 'Cancelled', 0, NULL, 12),
(82, 12, 'cash', NULL, 0.00, '2025-03-15 03:18:59', 'Pending', 0, 1, NULL),
(83, 12, 'cash', NULL, 0.00, '2025-03-15 03:42:09', 'Pending', 0, 1, NULL),
(84, 12, 'cash', NULL, 0.00, '2025-03-15 03:49:01', 'Pending', 0, 1, NULL),
(85, 13, 'cash', NULL, 0.00, '2025-03-15 03:58:52', 'Pending', 0, 1, NULL),
(86, 1, 'cash', NULL, 0.00, '2025-03-15 05:39:19', 'Delivered', 0, NULL, 12),
(87, 1, 'cash', NULL, 0.00, '2025-03-15 05:44:04', 'Cancelled', 0, NULL, 12),
(88, 1, 'cash', NULL, 0.00, '2025-03-15 05:45:16', 'Pending', 0, 1, NULL),
(89, 1, 'cash', NULL, 0.00, '2025-03-15 05:59:01', 'Delivered', 0, 1, 12),
(90, 1, 'cash', NULL, 0.00, '2025-03-15 06:07:44', 'Delivered', 0, NULL, 12),
(91, 1, 'cash', NULL, 0.00, '2025-03-17 07:09:14', 'Pending', 0, 1, NULL),
(92, 12, 'cash', NULL, 0.00, '2025-03-17 07:49:46', 'Cancelled', 0, NULL, 12),
(93, 12, 'cash', NULL, 0.00, '2025-03-17 08:13:57', 'Delivered', 0, NULL, 12),
(94, 12, 'cash', NULL, 0.00, '2025-03-17 08:22:02', 'Delivered', 0, NULL, 12),
(96, 12, 'cash', NULL, 69.00, '2025-03-17 08:27:04', 'Delivered', 0, NULL, NULL),
(97, 12, 'cash', NULL, 100.00, '2025-03-17 08:27:20', 'Delivered', 0, NULL, NULL),
(98, 12, 'cash', NULL, 128.00, '2025-03-17 10:10:27', 'Delivered', 0, NULL, NULL),
(99, 12, 'cash', NULL, 28.00, '2025-03-17 15:51:14', 'Cancelled', 0, NULL, NULL),
(100, 12, 'cash', NULL, 86.00, '2025-03-17 15:53:00', 'Delivered', 0, NULL, NULL),
(101, 12, 'cash', NULL, 180.00, '2025-03-17 15:53:34', 'Delivered', 0, NULL, NULL),
(102, 12, 'cash', NULL, 69.00, '2025-03-17 16:00:38', 'Delivered', 0, NULL, NULL),
(103, 12, 'cash', NULL, 28.00, '2025-03-17 16:09:18', 'Delivered', 0, NULL, NULL),
(104, 12, 'cash', NULL, 69.00, '2025-03-17 16:10:24', 'Delivered', 0, NULL, NULL),
(105, 1, 'cash', NULL, 200.00, '2025-03-22 05:30:10', 'Cancelled', 0, NULL, NULL),
(106, 1, 'cash', NULL, 20.00, '2025-03-22 05:37:06', 'Pending', 0, NULL, NULL),
(107, 1, 'cash', NULL, 140.00, '2025-03-22 05:37:50', 'Cancelled', 0, NULL, NULL),
(108, 1, 'cash', NULL, 586.00, '2025-03-22 05:49:15', 'Cancelled', 0, NULL, NULL),
(109, 1, 'gcash', '1742968068_images (5).jpg', 10.00, '2025-03-26 05:47:48', 'Pending', 0, NULL, NULL),
(110, 1, '', NULL, 28.00, '2025-03-26 05:48:31', 'Delivered', 0, NULL, NULL),
(111, 1, 'cash', NULL, 10.00, '2025-03-26 08:13:10', 'Pending', 0, NULL, NULL),
(112, 1, 'cash', NULL, 28.00, '2025-04-11 11:05:53', 'Delivered', 0, NULL, NULL),
(113, 1, 'cash', NULL, 20.00, '2025-04-11 11:06:08', 'Delivered', 0, NULL, NULL),
(114, 1, 'cash', NULL, 20.00, '2025-04-11 11:06:32', 'Delivered', 0, NULL, NULL),
(115, 1, 'cash', NULL, 15.00, '2025-04-11 11:08:33', 'Delivered', 0, NULL, NULL),
(116, 1, 'cash', NULL, 20.00, '2025-04-11 11:13:04', 'Delivered', 0, NULL, NULL),
(117, 1, 'cash', NULL, 195.00, '2025-04-11 11:16:06', 'Delivered', 0, NULL, NULL),
(118, 12, 'cash', NULL, 353.00, '2025-04-25 21:29:33', 'Pending', 0, NULL, NULL),
(119, 1, 'cash', NULL, 200.00, '2025-05-11 10:39:20', 'Pending', 0, NULL, NULL),
(120, 1, 'cash', NULL, 180.00, '2025-05-11 10:40:36', 'Pending', 0, NULL, NULL),
(121, 1, 'cash', NULL, 100.00, '2025-05-11 10:41:27', 'Pending', 0, NULL, NULL),
(122, 1, 'cash', NULL, 10.00, '2025-05-14 06:46:23', 'Pending', 0, NULL, NULL),
(123, 1, 'cash', NULL, 25.00, '2025-05-17 01:28:45', 'Pending', 0, NULL, NULL),
(124, 23, 'cash', NULL, 25.00, '2025-05-17 01:30:46', 'Pending', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_delivery_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `streetname` varchar(255) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Order Confirmed','Delivered') DEFAULT 'Pending',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_number` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `ingredient_id`, `quantity`, `total_price`, `seller_id`, `supplier_id`, `variant_id`) VALUES
(109, 78, NULL, 3, 1, 86.00, NULL, 12, NULL),
(110, 78, 31, NULL, 1, 10.00, 1, NULL, NULL),
(111, 79, 31, NULL, 1, 10.00, 1, NULL, NULL),
(112, 80, 34, NULL, 1, 50.00, 1, NULL, NULL),
(113, 81, NULL, 2, 1, 180.00, NULL, 12, NULL),
(114, 82, 31, NULL, 1, 10.00, 1, NULL, NULL),
(115, 83, 33, NULL, 1, 10.00, 1, NULL, NULL),
(116, 84, 33, NULL, 1, 10.00, 1, NULL, NULL),
(117, 85, 34, NULL, 1, 50.00, 1, NULL, NULL),
(118, 86, NULL, 2, 14, 2520.00, NULL, 12, NULL),
(119, 86, NULL, 3, 1, 86.00, NULL, 12, NULL),
(120, 87, NULL, 3, 1, 86.00, NULL, 12, NULL),
(121, 88, 34, NULL, 1, 50.00, 1, NULL, NULL),
(122, 89, NULL, 6, 1, 230.00, NULL, 12, NULL),
(123, 89, 31, NULL, 1, 10.00, 1, NULL, NULL),
(124, 89, 33, NULL, 1, 10.00, 1, NULL, NULL),
(125, 90, NULL, 2, 3, 540.00, NULL, 12, NULL),
(126, 90, NULL, 6, 1, 230.00, NULL, 12, NULL),
(127, 90, NULL, 7, 1, 100.00, NULL, 12, NULL),
(128, 90, NULL, 8, 1, 80.00, NULL, 12, NULL),
(129, 91, 33, NULL, 1, 10.00, 1, NULL, NULL),
(130, 92, NULL, 8, 1, 80.00, NULL, 12, NULL),
(131, 93, NULL, 4, 4, 276.00, NULL, 12, NULL),
(132, 94, NULL, 6, 1, 230.00, NULL, 12, NULL),
(133, 96, NULL, 4, 1, 69.00, NULL, NULL, NULL),
(134, 97, NULL, 7, 1, 100.00, NULL, NULL, NULL),
(135, 98, NULL, 1, 1, 28.00, NULL, NULL, NULL),
(136, 98, NULL, 7, 1, 100.00, NULL, NULL, NULL),
(137, 99, NULL, 5, 1, 28.00, NULL, NULL, NULL),
(138, 100, NULL, 3, 1, 86.00, NULL, NULL, NULL),
(139, 101, NULL, 2, 1, 180.00, NULL, NULL, NULL),
(140, 102, NULL, 4, 1, 69.00, NULL, NULL, NULL),
(141, 103, NULL, 5, 1, 28.00, NULL, NULL, NULL),
(142, 104, NULL, 4, 1, 69.00, NULL, NULL, NULL),
(143, 105, NULL, 2, 1, 180.00, NULL, NULL, NULL),
(144, 105, 33, NULL, 1, 10.00, NULL, NULL, NULL),
(145, 105, 31, NULL, 1, 10.00, NULL, NULL, NULL),
(146, 106, 33, NULL, 2, 20.00, NULL, NULL, NULL),
(147, 107, NULL, 1, 5, 140.00, NULL, NULL, NULL),
(148, 108, NULL, 3, 1, 86.00, NULL, NULL, NULL),
(149, 108, 34, NULL, 10, 500.00, NULL, NULL, NULL),
(150, 109, 33, NULL, 1, 10.00, NULL, NULL, NULL),
(151, 110, NULL, 1, 1, 28.00, NULL, NULL, NULL),
(152, 111, 31, NULL, 1, 10.00, NULL, NULL, NULL),
(153, 112, NULL, 1, 1, 28.00, NULL, NULL, NULL),
(154, 112, NULL, 1, 1, 28.00, NULL, NULL, NULL),
(155, 113, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(156, 113, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(157, 114, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(158, 114, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(159, 115, NULL, NULL, 1, 15.00, NULL, NULL, 2),
(160, 115, NULL, NULL, 1, 15.00, NULL, NULL, 2),
(161, 116, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(162, 116, NULL, NULL, 1, 20.00, NULL, NULL, 1),
(163, 117, NULL, 2, 1, 180.00, NULL, NULL, NULL),
(164, 117, NULL, NULL, 1, 15.00, NULL, NULL, 2),
(165, 118, NULL, 1, 6, 168.00, NULL, NULL, NULL),
(166, 118, NULL, NULL, 4, 80.00, NULL, NULL, 1),
(167, 118, NULL, NULL, 7, 105.00, NULL, NULL, 2),
(168, 119, 34, NULL, 4, 200.00, NULL, NULL, NULL),
(169, 120, NULL, 2, 1, 180.00, NULL, NULL, NULL),
(170, 121, 34, NULL, 2, 100.00, NULL, NULL, NULL),
(171, 122, 31, NULL, 1, 10.00, NULL, NULL, NULL),
(172, 123, NULL, 18, 1, 25.00, NULL, NULL, NULL),
(173, 124, NULL, 18, 1, 25.00, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `Product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `Product_name`, `description`, `price`, `quantity`, `created_at`, `category_id`, `image_url`) VALUES
(31, 1, 'bibingkaa', 'lami', 10.00, 4, '2024-12-07 22:05:11', 8, '../uploads/images (6).jpg'),
(33, 1, 'Turon', 'asd', 10.00, 2, '2024-12-17 08:02:54', 3, '../uploads/images (7).jpg'),
(34, 1, 'Ube Cheese Turon', 'Delicious Ube Turon with cheese', 50.00, 455, '2025-02-09 23:01:20', 1, '../uploads/images (8).jpg'),
(38, 20, 'Ube Cheese Turon', 'Delicious Ube Turon with cheese', 10.00, 1920, '2025-02-13 04:38:19', 1, 'uploads/2612b0bf-7b20-487b-a924-d2171c3f9565.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `products_inventory`
--

CREATE TABLE `products_inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_value` int(11) NOT NULL DEFAULT 1,
  `unit_type` varchar(50) NOT NULL DEFAULT 'pcs',
  `price` decimal(10,2) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_details`
--

CREATE TABLE `product_details` (
  `detail_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `long_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('cash','gcash') DEFAULT 'cash',
  `subtotal` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_paid` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `authorized_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipt_items`
--

CREATE TABLE `receipt_items` (
  `receipt_item_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `servings` varchar(50) DEFAULT NULL,
  `prep_time` varchar(50) DEFAULT NULL,
  `cook_time` varchar(50) DEFAULT NULL,
  `ingredients` text NOT NULL,
  `directions` text NOT NULL,
  `notes` text DEFAULT NULL,
  `recipe_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `user_id`, `title`, `servings`, `prep_time`, `cook_time`, `ingredients`, `directions`, `notes`, `recipe_image`, `created_at`, `updated_at`) VALUES
(3, 1, 'Bibingka', '8', '10 minutes', '35 minutes', '426.67 g rice flour,\r\n266.67 g granulated sugar,\r\n602.67 g coconut milk,\r\n162.67 g fresh milk,\r\n56.67 g grated coconut,\r\n150.67 g grated cheese,\r\n8 tablespoons butter,\r\n8 pieces raw eggs,\r\n2.67 pieces salted duck egg, sliced,\r\n6.67 teaspoons baking powder,\r\n0.35 teaspoon salt,\r\nPre-cut banana leaf (for lining)', 'Preheat oven to 375 degrees Fahrenheit.\r\nCombine rice flour, baking powder, and salt then mix well. Set aside.\r\nCream butter then gradually put-in sugar while whisking.\r\nAdd the eggs then whisk until every ingredient is well incorporated.\r\nGradually add the rice flour, salt, and baking powder mixture then continue mixing.\r\nPour-in coconut milk and fresh milk then whisk some more for 1 to 2 minutes.\r\nArrange the pre-cut banana leaf on a cake pan or baking pan.\r\nPour the mixture on the pan.\r\nBake for 15 minutes.\r\nRemove from the oven then top with sliced salted egg and grated cheese (do not turn the oven off).\r\nPut back in the oven and bake for 15 to 20 minutes or until the color of the top turn medium brown.\r\nRemove from the oven and let cool.\r\nBrush with butter and top with grated coconut.\r\nServe. Share and enjoy!', '📝 Preheating: Traditionally, bibingka is cooked using charcoal above and below the pan. If using an oven, preheat to 375°F (190°C) and line your baking pan with banana leaves for aroma and authenticity.\r\n🍃 Banana Leaves: To make them pliable and aromatic, lightly heat the banana leaves over an open flame or dip in hot water before lining your pan.\r\n🧂 Salted Eggs & Cheese: These toppings add a savory contrast to the sweet rice cake. You can adjust the amount based on taste preference or omit if unavailable.\r\n🥥 Grated Coconut: Freshly grated coconut is best, but frozen grated coconut is a good alternative. It adds richness and texture on top.\r\n🧈 Butter vs. Margarine: Butter gives a richer flavor, but you can use margarine if that’s what you have on hand.\r\n🔁 Texture Tip: For a lighter bibingka, you may separate the eggs and whip the egg whites to soft peaks before folding into the batter.\r\n🎉 Serving Suggestion: Best served warm, topped with more butter, sugar, grated coconut, or even a sprinkle of brown sugar', '../uploads/recipes/1742629587_images (6).jpg', '2025-03-22 15:46:27', '2025-03-22 15:46:27'),
(5, 1, 'asd', 'asd', '11', '1', '11', '11', '11', '../uploads/recipes/1747205083_f2678c157ca3b7e20968ce5fb4a96ba3.jpg', '2025-05-14 14:44:43', '2025-05-14 14:44:43');

-- --------------------------------------------------------

--
-- Table structure for table `rejections`
--

CREATE TABLE `rejections` (
  `rejection_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `rejection_reason` text NOT NULL,
  `rejection_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `rejected_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rejections`
--

INSERT INTO `rejections` (`rejection_id`, `request_id`, `rejection_reason`, `rejection_date`, `rejected_by`) VALUES
(9, 41, 'sorry', '2025-03-13 02:37:13', 1),
(10, 39, 'hehe sorry', '2025-03-13 02:37:54', 1),
(11, 38, 'nah', '2025-03-13 02:46:55', 1),
(12, 45, 'asd', '2025-03-13 05:52:57', 1),
(13, 43, 'asd', '2025-03-13 06:23:14', 1),
(14, 48, 'asd', '2025-03-13 06:38:06', 1);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `request_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `user_id`, `product_name`, `seller_id`, `quantity`, `additional_notes`, `status`, `request_date`) VALUES
(27, 1, NULL, 1, 11, 'a', 'Approved', '2024-12-19 13:47:07'),
(28, 1, NULL, 1, 100, 'aa', 'Approved', '2024-12-19 13:48:45'),
(29, 13, NULL, 1, 10, 't\r\n', 'Rejected', '2024-12-19 13:51:23'),
(30, 13, NULL, 1, 1, '1', 'Approved', '2024-12-19 14:22:26'),
(31, 1, NULL, 1, 2, 'sdasdas', 'Rejected', '2024-12-19 15:00:33'),
(36, 19, 'asd', 1, 1, '                        asd', 'Approved', '2025-02-09 19:33:50'),
(37, 1, 'asd', 1, 1, '              1          ', 'Rejected', '2025-02-09 19:49:37'),
(38, 1, 'asd', 1, 1, '             1           ', 'Rejected', '2025-02-09 19:54:23'),
(39, 19, 'asdasd', 1, 1, '             1           ', 'Rejected', '2025-02-09 19:54:44'),
(40, 19, 'asdasd', 1, 1, '             1           ', 'Approved', '2025-02-09 19:56:12'),
(41, 1, 'asdas', 1, 1, '                        1', 'Rejected', '2025-02-09 20:06:54'),
(42, 1, 'Turon', 1, 1, '                        1', 'Approved', '2025-02-10 14:45:01'),
(43, 20, 'Turon', 1, 50, 'naka bubble wrap\r\n', 'Rejected', '2025-02-10 17:50:05'),
(44, 12, 'bibingka', 1, 5, 'celopin', 'Approved', '2025-03-08 15:36:50'),
(45, 21, 'Ube Cheese Turon', 1, 20, '                      silopin  ', 'Rejected', '2025-03-08 15:39:34'),
(46, 1, 'Turon', 1, 1, '                  1      ', 'Rejected', '2025-03-13 16:44:30'),
(47, 1, 'Turon', 1, 2, '1          ', 'Approved', '2025-03-13 21:25:35'),
(48, 1, 'bibingkaa', 1, 1, '                   1     ', 'Rejected', '2025-03-13 21:38:00'),
(49, 1, 'Turon', 1, 1, '                    1    ', 'Pending', '2025-03-13 21:55:56'),
(50, 23, 'Turon', 1, 20, 'wa             ', 'Pending', '2025-03-14 03:11:46');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `buyer_name` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `profit` decimal(10,2) GENERATED ALWAYS AS (`price` - `cost`) STORED,
  `delivered` tinyint(1) DEFAULT 0,
  `total` decimal(10,2) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `ingredient_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `contact_number` varchar(15) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `streetname` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('user','seller','admin','supplier') DEFAULT 'user',
  `profile_pics` varchar(255) DEFAULT 'path/to/default/profile/pic.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `contact_number`, `country`, `city`, `streetname`, `barangay`, `province`, `email`, `password`, `usertype`, `profile_pics`) VALUES
(1, 'Aaron Jhon', '', 'Gutang', '2025-05-17', '09294999087', 'Philippines', 'asd', 'asd', 'asd', 'asd', 'gutang@gmail.com', '123456', 'seller', '../uploads/IMG20250215175103.jpg'),
(12, 'Darlyn', 'L', 'Hoshino', '2025-03-14', '123123123', 'Philippines', 'Davao', 'Upperpiedad', 'Bato', 'Toril', 'a@gmail.com', 'Asd123', 'supplier', '../uploads/5cc09ad47b7c1c180db37126c30ac6dc.jpg'),
(13, 'lisa', '', 'yahh', '2024-12-19', '1211212121', NULL, NULL, NULL, NULL, NULL, 'lisa@gmail.com', 'Lisa123', 'user', NULL),
(14, 'arnel', '', 'gutang', '2025-01-11', '123123123', NULL, NULL, NULL, NULL, NULL, 'arnel@gmail.com', 'Arnel123', 'seller', NULL),
(15, 'Arellano', '', 'Velasco', '2024-12-19', '123123123', NULL, NULL, NULL, NULL, NULL, 'arellano@gmail.com', 'Arellano1', 'supplier', NULL),
(16, 'Kaye', '', 'Carillo', '2024-12-19', '1211212121', NULL, NULL, NULL, NULL, NULL, 'customer1@gmail.com', 'Customer1', 'user', NULL),
(17, 'Rafaella', '', 'Moncera', '2024-12-19', '1211212121', NULL, NULL, NULL, NULL, NULL, 'customer2@gmail.com', 'Customer2', 'user', NULL),
(18, 'Garjoice', '', 'Salta', '2024-12-19', '123123123', NULL, NULL, NULL, NULL, NULL, 'customer3@gmail.com', 'Customer3@gail.com', 'seller', NULL),
(19, 'asd', 'asd', 'asd', '2025-02-09', '123123123123', NULL, NULL, NULL, NULL, NULL, 'asd@gmail.com', 'Asd123', 'user', NULL),
(20, 'erwin', 'dd', 'llego', '2025-02-10', '123123123213', NULL, NULL, NULL, NULL, NULL, 'e@gmail.com', 'Asd123', 'seller', NULL),
(21, 'frenchfraiss', '', 'Miki', '2025-03-14', '12312312312', 'phillipines', 'Davao', 'Uppierpiedad', 'Bato', 'Toril', 'miki@gmail.com', 'Miki123', 'seller', '../uploads/miki.jpg'),
(23, 'Sana', '', 'Minatozaki ', '2025-03-14', '2131231231', NULL, NULL, NULL, NULL, NULL, 'sana@gmail.com', 'Sana123', 'supplier', '../uploads/images (5).jpg'),
(24, 'quirubin', '', 'militares', '2025-03-26', '12312312312', NULL, NULL, NULL, NULL, NULL, 'mi@gmail.com', 'Mili123', 'seller', '../uploads/481003976_1389865965717943_9091140094497287966_n.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `apply_seller`
--
ALTER TABLE `apply_seller`
  ADD PRIMARY KEY (`seller_id`),
  ADD KEY `id` (`id`);
ALTER TABLE `apply_seller` ADD FULLTEXT KEY `business_name` (`business_name`,`description`);

--
-- Indexes for table `apply_supplier`
--
ALTER TABLE `apply_supplier`
  ADD PRIMARY KEY (`supplier_id`);
ALTER TABLE `apply_supplier` ADD FULLTEXT KEY `business_name` (`business_name`,`description`);

--
-- Indexes for table `business_hours`
--
ALTER TABLE `business_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `fk_variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `checkout`
--
ALTER TABLE `checkout`
  ADD PRIMARY KEY (`checkout_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category_id` (`category_id`);
ALTER TABLE `ingredients` ADD FULLTEXT KEY `ingredient_name` (`ingredient_name`,`description`);

--
-- Indexes for table `ingredients_inventory`
--
ALTER TABLE `ingredients_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `ingredient_variants`
--
ALTER TABLE `ingredient_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `fk_variant_product` (`product_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_delivery_id`),
  ADD KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_variant` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);
ALTER TABLE `products` ADD FULLTEXT KEY `Product_name` (`Product_name`,`description`);

--
-- Indexes for table `products_inventory`
--
ALTER TABLE `products_inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_details`
--
ALTER TABLE `product_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD PRIMARY KEY (`receipt_item_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rejections`
--
ALTER TABLE `rejections`
  ADD PRIMARY KEY (`rejection_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `rejected_by` (`rejected_by`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_request_user` (`user_id`),
  ADD KEY `fk_request_seller` (`seller_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ingredient_id` (`ingredient_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `apply_seller`
--
ALTER TABLE `apply_seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `apply_supplier`
--
ALTER TABLE `apply_supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `business_hours`
--
ALTER TABLE `business_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `checkout`
--
ALTER TABLE `checkout`
  MODIFY `checkout_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `ingredients_inventory`
--
ALTER TABLE `ingredients_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ingredient_variants`
--
ALTER TABLE `ingredient_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=174;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `products_inventory`
--
ALTER TABLE `products_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_details`
--
ALTER TABLE `product_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipt_items`
--
ALTER TABLE `receipt_items`
  MODIFY `receipt_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `rejections`
--
ALTER TABLE `rejections`
  MODIFY `rejection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apply_seller`
--
ALTER TABLE `apply_seller`
  ADD CONSTRAINT `apply_seller_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `business_hours`
--
ALTER TABLE `business_hours`
  ADD CONSTRAINT `business_hours_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`),
  ADD CONSTRAINT `fk_variant_id` FOREIGN KEY (`variant_id`) REFERENCES `ingredient_variants` (`variant_id`) ON DELETE SET NULL;

--
-- Constraints for table `checkout`
--
ALTER TABLE `checkout`
  ADD CONSTRAINT `checkout_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ingredients_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ingredients_inventory`
--
ALTER TABLE `ingredients_inventory`
  ADD CONSTRAINT `ingredients_inventory_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ingredients_inventory_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `apply_seller` (`seller_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ingredients_inventory_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `apply_supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ingredients_inventory_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ingredients_inventory_ibfk_5` FOREIGN KEY (`variant_id`) REFERENCES `ingredient_variants` (`variant_id`);

--
-- Constraints for table `ingredient_variants`
--
ALTER TABLE `ingredient_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `apply_seller` (`seller_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `apply_supplier` (`supplier_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_number`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_variant` FOREIGN KEY (`variant_id`) REFERENCES `ingredient_variants` (`variant_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `apply_seller` (`seller_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `products_inventory`
--
ALTER TABLE `products_inventory`
  ADD CONSTRAINT `products_inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_inventory_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `apply_seller` (`seller_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_inventory_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `apply_supplier` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_inventory_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_details`
--
ALTER TABLE `product_details`
  ADD CONSTRAINT `product_details_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipts_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `apply_seller` (`seller_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receipts_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `apply_supplier` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD CONSTRAINT `receipt_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`receipt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipt_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receipt_items_ibfk_3` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE SET NULL;

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rejections`
--
ALTER TABLE `rejections`
  ADD CONSTRAINT `rejections_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rejections_ibfk_2` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_request_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_request_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
