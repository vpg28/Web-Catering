-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 04, 2024 at 02:06 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catering_service`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `menu_name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `price` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `menu_name`, `description`, `price`, `date`) VALUES
(7, 'bandeng', 'a;a;aa', 20000, '2024-06-05'),
(8, 'lala', 'lalala', 2000, '2024-06-06'),
(9, 'bbb', 'bbbb', 20000, '2024-06-07'),
(10, 'cccc', 'cccc', 20000, '2024-06-08'),
(11, 'xxx', 'xxx', 20000, '2024-06-09'),
(12, 'yyy', 'yyy', 20000, '2024-06-01'),
(13, 'kenyang', 'gtw', 2000, '2024-06-30'),
(14, 'ikan', 'lala', 2000, '2024-06-05'),
(15, 'koloke', 'ayam asam manis', 20000, '2024-07-01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `confirmed_at` date NOT NULL,
  `payment_status` varchar(200) NOT NULL DEFAULT 'belum bayar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `discount`, `payment_id`, `confirmed_at`, `payment_status`) VALUES
(116, 1, 22000, 0, 1, '2024-06-04', 'selesai'),
(117, 1, 22000, 0, 2, '2024-06-04', 'dibatalkan'),
(118, 1, 22000, 0, 1, '2024-06-04', 'selesai'),
(119, 1, 24000, 0, 1, '2024-06-04', 'selesai'),
(120, 1, 24000, 0, 1, '2024-06-04', 'selesai'),
(121, 1, 24000, 0, 2, '2024-06-04', 'selesai'),
(122, 1, 24000, 0, 2, '2024-06-04', 'selesai'),
(123, 1, 24000, 0, 2, '2024-06-04', 'selesai'),
(124, 1, 24000, 0, 2, '2024-06-04', 'selesai'),
(125, 1, 44000, 0, 1, '2024-06-04', 'belum bayar'),
(126, 1, 22000, 0, 1, '2024-06-04', 'belum bayar'),
(127, 1, 22000, 0, 1, '2024-06-04', 'belum bayar');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  `item_status` varchar(200) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_id`, `item_status`) VALUES
(63, 116, 7, 'selesai'),
(64, 116, 14, 'selesai'),
(65, 117, 7, 'dibatalkan'),
(66, 117, 14, 'dibatalkan'),
(67, 118, 7, 'selesai'),
(68, 118, 14, 'selesai'),
(69, 119, 7, 'selesai'),
(70, 119, 14, 'selesai'),
(71, 119, 8, 'selesai'),
(72, 120, 7, 'selesai'),
(73, 120, 14, 'selesai'),
(74, 120, 8, 'selesai'),
(75, 121, 7, 'selesai'),
(76, 121, 14, 'selesai'),
(77, 121, 8, 'selesai'),
(78, 122, 7, 'selesai'),
(79, 122, 14, 'selesai'),
(80, 122, 8, 'selesai'),
(81, 123, 7, 'selesai'),
(82, 123, 14, 'selesai'),
(83, 123, 8, 'selesai'),
(84, 124, 7, 'selesai'),
(85, 124, 14, 'selesai'),
(86, 124, 8, 'selesai'),
(87, 125, 7, 'belum bayar'),
(88, 125, 14, 'belum bayar'),
(89, 125, 7, 'belum bayar'),
(90, 125, 14, 'belum bayar'),
(91, 126, 7, 'belum bayar'),
(92, 126, 14, 'belum bayar'),
(93, 127, 7, 'belum bayar'),
(94, 127, 14, 'belum bayar');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `method_name` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`) VALUES
(1, 'BCA Virtual Account'),
(2, 'Bayar di Tempat');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `address` text NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `address`, `name`, `phone`) VALUES
(1, 'jalan jalan', 'wili', 85);

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `code` varchar(200) NOT NULL,
  `discount_rate` decimal(10,2) NOT NULL,
  `expiration_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`code`, `discount_rate`, `expiration_date`) VALUES
('abc', 0.10, '2024-06-08'),
('xxx', 0.00, '2024-05-30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_id` (`menu_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=441;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
