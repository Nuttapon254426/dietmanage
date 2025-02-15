-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2025 at 06:15 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dietmanage`
--

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `food_id` int(11) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `calories` float NOT NULL,
  `protein` float DEFAULT NULL,
  `carbohydrates` float DEFAULT NULL,
  `fat` float DEFAULT NULL,
  `fiber` float DEFAULT NULL,
  `serving_size` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`food_id`, `food_name`, `calories`, `protein`, `carbohydrates`, `fat`, `fiber`, `serving_size`, `created_by`, `created_at`) VALUES
(16, 'ข้าวสวย', 130, 2.7, 28, 0.3, 0.4, '100 กรัม', 2, '2025-02-14 08:38:26'),
(17, 'ข้าวผัด', 165, 3.5, 30, 3.2, 0.8, '100 กรัม', 2, '2025-02-14 08:38:26'),
(18, 'ผัดกะเพราไก่', 220, 15, 12, 14, 2, '1 จาน (150 กรัม)', 2, '2025-02-14 08:38:26'),
(19, 'ข้าวต้มหมู', 180, 12, 25, 4.5, 1, '1 ชาม (200 กรัม)', 2, '2025-02-14 08:38:26'),
(20, 'โจ๊กหมู', 150, 8, 28, 2.5, 0.5, '1 ชาม (200 กรัม)', 2, '2025-02-14 08:38:26'),
(21, 'ผัดไทย', 300, 12, 45, 10, 2, '1 จาน (250 กรัม)', 2, '2025-02-14 08:38:26'),
(22, 'ก๋วยเตี๋ยวหมู', 250, 15, 40, 5, 1.5, '1 ชาม (300 กรัม)', 2, '2025-02-14 08:38:26'),
(23, 'สุกี้แห้งทะเล', 280, 18, 35, 8, 3, '1 จาน (250 กรัม)', 2, '2025-02-14 08:38:26'),
(24, 'ส้มตำ', 85, 3, 12, 3.5, 4, '1 จาน (150 กรัม)', 2, '2025-02-14 08:38:26'),
(25, 'ขนมปังปิ้ง', 120, 4, 22, 2, 1, '2 แผ่น', 2, '2025-02-14 08:38:26'),
(26, 'ปลาทอดกระเทียม', 200, 22, 8, 10, 0, '100 กรัม', 2, '2025-02-14 08:38:26'),
(27, 'กุ้งผัดผงกะหรี่', 180, 15, 12, 9, 1, '100 กรัม', 2, '2025-02-14 08:38:26'),
(28, 'ผัดผักบุ้งไฟแดง', 90, 2, 12, 4, 3, '1 จาน (100 กรัม)', 2, '2025-02-14 08:38:26'),
(29, 'ผัดคะน้าหมูกรอบ', 220, 12, 15, 14, 3, '1 จาน (150 กรัม)', 2, '2025-02-14 08:38:26'),
(30, 'ผัดถั่วงอกใส่เต้าหู้', 120, 8, 10, 6, 2.5, '1 จาน (120 กรัม)', 2, '2025-02-14 08:38:26');

-- --------------------------------------------------------

--
-- Table structure for table `meal_records`
--

CREATE TABLE `meal_records` (
  `record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `serving_amount` float NOT NULL,
  `meal_date` date NOT NULL,
  `meal_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_records`
--

INSERT INTO `meal_records` (`record_id`, `user_id`, `food_id`, `meal_type`, `serving_amount`, `meal_date`, `meal_time`, `notes`, `created_at`) VALUES
(1, 2, 16, 'breakfast', 1, '2025-02-14', '22:55:00', '', '2025-02-14 15:56:18');

-- --------------------------------------------------------

--
-- Table structure for table `nutrition_plans`
--

CREATE TABLE `nutrition_plans` (
  `plan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nutritionist_id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `target_calories` float DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nutrition_plans`
--

INSERT INTO `nutrition_plans` (`plan_id`, `user_id`, `nutritionist_id`, `plan_name`, `start_date`, `end_date`, `target_calories`, `notes`, `created_at`) VALUES
(37, 43, 2, 'แผนลดน้ำหนัก 30 วัน', '2025-02-15', '2025-03-15', 2100, '- ทานอาหารเช้าเน้นโปรตีนและผักผลไม้\r\n- งดอาหารหวานและแป้ง\r\n- ออกกำลังกาย 30 นาทีต่อวัน\r\n- ดื่มน้ำอย่างน้อยวันละ 2 ลิตร', '2025-02-14 10:42:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('user','nutritionist','admin') DEFAULT 'user',
  `height` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `role`, `height`, `weight`, `age`, `gender`, `created_at`, `updated_at`) VALUES
(2, 'admin', '12345', 'test03@gmail.com', 'นายเทส', 'nutritionist', 175, 84, 18, 'female', '2025-02-14 15:16:41', '2025-02-14 18:47:08'),
(43, 'user1', '482c811da5d5b4bc6d497ffa98491e38', 'user1@example.com', 'สมชาย ใจดี', 'user', 170, 65, 25, 'male', '2025-02-14 11:27:09', ''),
(44, 'user2', '482c811da5d5b4bc6d497ffa98491e38', 'user2@example.com', 'สมหญิง รักสุขภาพ', 'user', 160, 55, 30, 'female', '2025-02-14 11:27:09', ''),
(45, 'user3', '482c811da5d5b4bc6d497ffa98491e38', 'user3@example.com', 'วีระ สุขสันต์', 'user', 175, 80, 45, 'male', '2025-02-14 11:27:09', ''),
(46, 'user4', '482c811da5d5b4bc6d497ffa98491e38', 'user4@example.com', 'นภา ดาวเด่น', 'user', 158, 48, 28, 'female', '2025-02-14 11:27:09', ''),
(47, 'user5', '482c811da5d5b4bc6d497ffa98491e38', 'user5@example.com', 'พิชัย แข็งแรง', 'user', 172, 70, 35, 'male', '2025-02-14 11:27:09', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `meal_records`
--
ALTER TABLE `meal_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `nutrition_plans`
--
ALTER TABLE `nutrition_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `nutritionist_id` (`nutritionist_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `food_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `meal_records`
--
ALTER TABLE `meal_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nutrition_plans`
--
ALTER TABLE `nutrition_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `food_items`
--
ALTER TABLE `food_items`
  ADD CONSTRAINT `food_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `meal_records`
--
ALTER TABLE `meal_records`
  ADD CONSTRAINT `meal_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `meal_records_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `food_items` (`food_id`);

--
-- Constraints for table `nutrition_plans`
--
ALTER TABLE `nutrition_plans`
  ADD CONSTRAINT `nutrition_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `nutrition_plans_ibfk_2` FOREIGN KEY (`nutritionist_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
