-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 07:22 AM
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
-- Database: `seminar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `date`, `max_participants`, `created_at`) VALUES
(1, 'อบรมการป้องกันการติดเชื้อ', 'อบรมเกี่ยวกับการป้องกันการแพร่กระจายของเชื้อโรคในสถานพยาบาล', '2025-06-10', 5, '2025-05-20 05:13:12'),
(2, 'สัมมนาการพยาบาลผู้ป่วยวิกฤต', 'การจัดการและดูแลผู้ป่วยในภาวะวิกฤตสำหรับพยาบาล', '2025-06-15', 8, '2025-05-20 05:13:12'),
(3, 'ประชุมวิชาการการพยาบาลชุมชน', 'นำเสนอผลงานวิชาการและการแลกเปลี่ยนประสบการณ์การพยาบาลในชุมชน', '2025-07-01', 10, '2025-05-20 05:13:12'),
(4, 'อบรมเทคนิคการสื่อสารกับผู้ป่วย', 'เรียนรู้วิธีการสื่อสารกับผู้ป่วยและครอบครัวในสถานการณ์ต่าง ๆ', '2025-07-05', 6, '2025-05-20 05:13:12'),
(5, 'เวิร์กช็อปการทำแผนการพยาบาล', 'ฝึกปฏิบัติการจัดทำแผนการพยาบาลแบบมีส่วนร่วม', '2025-07-10', 4, '2025-05-20 05:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `event_id`, `fullname`, `email`, `phone`, `registered_at`) VALUES
(1, 1, 'Pachara Satthawatchara', 'iampacharaps@gmail.com', '0987477891', '2025-05-20 05:14:27'),
(2, 1, 'Pachara Satthawatchara', 'pachasa@kku.ac.th', '0987477891', '2025-05-20 05:16:18'),
(3, 1, 'ffff', 'ffffff@doctor.com', '0987477891', '2025-05-20 05:17:10'),
(4, 1, 'บ้าบอ', 'aaaa@teacher.com', '0987477891', '2025-05-20 05:23:30'),
(5, 1, 'aaa', 'docfilm@doc.com', '0987477891', '2025-05-20 05:54:03'),
(6, 3, 'Pachara Satthawatchara', 'iampacharaps@gmail.com', '0987477891', '2025-05-20 05:59:56'),
(7, 5, 'Pachara Satthawatchara', 'iampacharaps@gmail.com', '0987477891', '2025-05-20 06:02:28'),
(8, 2, 'Pachara Satthawatchara', 'iampacharaps@gmail.com', '0987477891', '2025-05-20 06:05:49'),
(9, 2, 'hhhhh', 'hhhh@email.com', '0987477891', '2025-05-20 07:25:29'),
(10, 4, 'Pachara Satthawatchara', 'iampacharaps@gmail.com', '0987477891', '2025-05-20 09:21:30'),
(18, 3, 'aaa', 'aaaa@teacher.com', '0987477891', '2025-05-21 04:08:02'),
(21, 4, 'หหห', 'hhhh@email.com', '0987477891', '2025-05-21 04:13:45'),
(22, 3, 'ddd', 'ddd@mail.com', '0987477891', '2025-05-21 04:14:22'),
(23, 2, 'ssss', 'ssss@sss.com', '0987477891', '2025-05-21 04:52:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`,`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
