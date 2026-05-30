-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2026 at 06:03 PM
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
-- Database: `makikonek_db`
--
CREATE DATABASE IF NOT EXISTS `makikonek_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `makikonek_db`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--
-- Creation: May 30, 2026 at 02:58 PM
-- Last update: May 30, 2026 at 03:00 PM
--

DROP TABLE IF EXISTS `admin_accounts`;
CREATE TABLE `admin_accounts` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Super Admin','Barangay Staff') DEFAULT 'Barangay Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `admin_accounts`:
--

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`admin_id`, `username`, `email`, `password_hash`, `role`, `created_at`, `archived_at`) VALUES
(1, 'jhody_admin', 'atinon.jhody@gmail.com', '$2y$10$vyj3WX5lcdc4juz3lnmjEe7qOmhOLAonD1yaF3qvBWTUlSkaB7I16', 'Super Admin', '2026-05-23 17:41:48', NULL),
(2, 'mary_admin', 'mary@makikonek.ph', '$2y$10$WpVPVSMDqkCG7Jwp1BdMNulcUhoaRssOubqB9uD51fepjk8d.RRsq', 'Super Admin', '2026-05-23 17:41:48', NULL),
(3, 'shem_admin', 'shem@makikonek.ph', '$2y$10$U8/j9JbsvTbdpLynlc44o.h9NjYnuyr660UhjFIvZX773pvYaZXzG', 'Barangay Staff', '2026-05-23 17:41:49', NULL),
(4, 'nat_admin', 'nat@makikonek.ph', '$2y$10$TICGoNjwm/Vh8EEqH/nh0O13V7sSlQ2A6rQrV0tAgDZvJl7nwpfx2', 'Barangay Staff', '2026-05-23 17:41:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: May 30, 2026 at 03:39 PM
-- Last update: May 30, 2026 at 03:46 PM
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'Residente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `users`:
--

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Juday', 'atinon.jhody@gmail.com', '$2y$10$1U1Pw0lSncvyRLcoDc4vFefBlchiuvNUVF6JYMszAhpje0N6X9.fK', 'Residente', '2026-05-30 15:39:59'),
(2, 'BC_Kapitana', 'barangaycapt@gmail.com', '$2y$10$7sRTzJ8k9oTB7zAgu7ZYmewaw2of335zYN6IxkF88eAhBxXX1zgLy', 'Opisyal', '2026-05-30 15:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--
-- Creation: May 30, 2026 at 03:38 PM
-- Last update: May 30, 2026 at 03:46 PM
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `sex` varchar(20) DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(150) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `house_no` varchar(50) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `purok_no` varchar(50) DEFAULT NULL,
  `subdivision` varchar(100) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_relationship` varchar(50) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `emergency_address` varchar(255) DEFAULT NULL,
  `national_id` varchar(50) DEFAULT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `voters_id` varchar(50) DEFAULT NULL,
  `sss_no` varchar(50) DEFAULT NULL,
  `tin_no` varchar(50) DEFAULT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `years_residency` int(11) DEFAULT NULL,
  `employed_status` varchar(50) DEFAULT NULL,
  `date_registration` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELATIONSHIPS FOR TABLE `user_profiles`:
--   `user_id`
--       `users` -> `user_id`
--

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `first_name`, `last_name`, `middle_name`, `suffix`, `avatar_path`, `sex`, `civil_status`, `birth_date`, `birth_place`, `religion`, `nationality`, `mobile_number`, `house_no`, `street`, `purok_no`, `subdivision`, `emergency_name`, `emergency_relationship`, `emergency_contact`, `emergency_address`, `national_id`, `philhealth_no`, `voters_id`, `sss_no`, `tin_no`, `pagibig_no`, `years_residency`, `employed_status`, `date_registration`, `updated_at`) VALUES
(1, 1, 'Jhody', 'Atinon', 'M', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30 15:39:59'),
(2, 2, 'Barangay', 'Captain', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30 15:46:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
