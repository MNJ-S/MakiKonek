-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 07:13 AM
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
-- Creation: Jun 01, 2026 at 06:08 AM
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
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`admin_id`, `username`, `email`, `password_hash`, `role`, `created_at`, `archived_at`) VALUES
(1, 'jhody_admin', 'atinon.jhody@gmail.com', '$2y$10$vyj3WX5lcdc4juz3lnmjEe7qOmhOLAonD1yaF3qvBWTUlSkaB7I16', 'Super Admin', '2026-05-23 17:41:48', NULL),
(2, 'mary_admin', 'mary@makikonek.ph', '$2y$10$WpVPVSMDqkCG7Jwp1BdMNulcUhoaRssOubqB9uD51fepjk8d.RRsq', 'Super Admin', '2026-05-23 17:41:48', NULL),
(3, 'shem_admin', 'shem@makikonek.ph', '$2y$10$U8/j9JbsvTbdpLynlc44o.h9NjYnuyr660UhjFIvZX773pvYaZXzG', 'Super Admin', '2026-05-23 17:41:49', NULL),
(4, 'nat_admin', 'nat@makikonek.ph', '$2y$10$TICGoNjwm/Vh8EEqH/nh0O13V7sSlQ2A6rQrV0tAgDZvJl7nwpfx2', 'Super Admin', '2026-05-23 17:41:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--
-- Creation: Jun 05, 2026 at 04:18 AM
-- Last update: Jun 05, 2026 at 05:09 AM
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` varchar(20) NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `purpose` text NOT NULL,
  `occupation` varchar(50) DEFAULT NULL,
  `document_fee` varchar(20) NOT NULL,
  `payment_method` varchar(20) DEFAULT 'cash',
  `payment_receipt_path` varchar(255) DEFAULT NULL,
  `id_path` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `business_name` varchar(100) DEFAULT NULL,
  `business_location` varchar(255) DEFAULT NULL,
  `business_operator` varchar(100) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `business_nature` varchar(100) DEFAULT NULL,
  `business_permit_for` varchar(100) DEFAULT NULL,
  `construction_address` text DEFAULT NULL,
  `construction_purpose` varchar(100) DEFAULT NULL,
  `construction_other_purpose` varchar(100) DEFAULT NULL,
  `construction_status` varchar(100) DEFAULT NULL,
  `construction_other_status` varchar(100) DEFAULT NULL,
  `construction_description` text DEFAULT NULL,
  `cedula_type` varchar(50) DEFAULT NULL,
  `cedula_tax_year` varchar(10) DEFAULT NULL,
  `cedula_place_issued` varchar(100) DEFAULT NULL,
  `cedula_income_source` varchar(100) DEFAULT NULL,
  `cedula_tin` varchar(50) DEFAULT NULL,
  `cedula_birthplace` varchar(100) DEFAULT NULL,
  `cedula_height` varchar(20) DEFAULT NULL,
  `cedula_weight` varchar(20) DEFAULT NULL,
  `cedula_gross_income` varchar(50) DEFAULT NULL,
  `id_emergency_name` varchar(100) DEFAULT NULL,
  `id_emergency_relationship` varchar(50) DEFAULT NULL,
  `id_emergency_contact` varchar(20) DEFAULT NULL,
  `id_blood_type` varchar(10) DEFAULT NULL,
  `id_valid_until` date DEFAULT NULL,
  `incident_date` date DEFAULT NULL,
  `incident_time` time DEFAULT NULL,
  `incident_location` text DEFAULT NULL,
  `incident_persons` text DEFAULT NULL,
  `incident_narrative` text DEFAULT NULL,
  `incident_action` text DEFAULT NULL,
  `incident_witness_name` varchar(100) DEFAULT NULL,
  `incident_witness_contact` varchar(20) DEFAULT NULL,
  `incident_witness_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`request_id`, `user_id`, `reference_no`, `document_type`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `phone`, `birth_date`, `gender`, `civil_status`, `address`, `province`, `city`, `barangay`, `purpose`, `occupation`, `document_fee`, `payment_method`, `payment_receipt_path`, `id_path`, `status`, `created_at`, `business_name`, `business_location`, `business_operator`, `business_address`, `business_nature`, `business_permit_for`, `construction_address`, `construction_purpose`, `construction_other_purpose`, `construction_status`, `construction_other_status`, `construction_description`, `cedula_type`, `cedula_tax_year`, `cedula_place_issued`, `cedula_income_source`, `cedula_tin`, `cedula_birthplace`, `cedula_height`, `cedula_weight`, `cedula_gross_income`, `id_emergency_name`, `id_emergency_relationship`, `id_emergency_contact`, `id_blood_type`, `id_valid_until`, `incident_date`, `incident_time`, `incident_location`, `incident_persons`, `incident_narrative`, `incident_action`, `incident_witness_name`, `incident_witness_contact`, `incident_witness_address`) VALUES
(1, 1, 'MK-89BE60', 'Barangay Clearance', 'JHODY', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '09625389809', '2005-11-05', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'COMPANY APPLICATION', '', 'P50.00', 'cash', NULL, 'assets/uploads/requirements/id_1_1780634408.jpg', 'REJECTED', '2026-06-05 04:40:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, 'MK-258F5B', 'Barangay Clearance', 'JHODY', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '09625389809', '2005-11-05', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'COMPANY APPLICATION', '', 'P50.00', 'cash', NULL, 'assets/uploads/requirements/id_1_1780634690.jpg', 'PENDING', '2026-06-05 04:44:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, 'MK-A069EB', 'Certificate of Indigency', 'JHODY', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '+639367214744', '2005-11-14', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'SCHOLARSHIP PURPOSES', '', 'Free', 'cash', NULL, 'assets/uploads/requirements/id_1_1780634762.jpg', 'PENDING', '2026-06-05 04:46:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 'MK-204186', 'Certificate of Residency', 'JHODY', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '09625389809', '2005-11-05', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'ssss', '', 'P20.00', 'cash', NULL, 'assets/uploads/requirements/id_1_1780634818.jpg', 'PENDING', '2026-06-05 04:46:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 1, 'MK-B09B9D', 'Incident Report', 'JHODY', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '09625389809', '2005-11-05', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'BLOTTER', '', 'P50.00', 'online', NULL, 'assets/uploads/requirements/id_1_1780634955.jpg', 'PENDING', '2026-06-05 04:49:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-05', '12:48:00', 'Purok 1 asdsadasdasd', 'Si MAJO', 'sadasdjahbkcjkljhwejfsjldvbcs', '', 'Irene m. atinon', '09367214744', '0616 purok 2, brgy. turbina'),
(6, 1, 'MK-38A83D', 'Cedula', 'IRENE', 'M', 'ATINON', '', 'atinon.jhody@gmail.com', '09625389809', '2005-11-05', 'Female', 'Single', '0616 Purok 2', 'Laguna', 'Calamba', 'Makiling', 'CREDIT CARD APPLICATION', '', 'Based on LGU', 'online', 'assets/uploads/receipts/receipt_1_1780636179.png', 'assets/uploads/requirements/id_1_1780636179.png', 'PENDING', '2026-06-05 05:09:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Business', '2024', 'Barangay Makiling', 'Self-Employed', '', 'Calamba City', '5\'2', '64 kg', '250000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Jun 01, 2026 at 06:08 AM
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Juday', 'atinon.jhody@gmail.com', '$2y$10$1U1Pw0lSncvyRLcoDc4vFefBlchiuvNUVF6JYMszAhpje0N6X9.fK', 'Residente', '2026-05-30 15:39:59'),
(2, 'BC_Kapitana', 'barangaycapt@gmail.com', '$2y$10$7sRTzJ8k9oTB7zAgu7ZYmewaw2of335zYN6IxkF88eAhBxXX1zgLy', 'Opisyal', '2026-05-30 15:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--
-- Creation: Jun 01, 2026 at 06:08 AM
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
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `first_name`, `last_name`, `middle_name`, `suffix`, `avatar_path`, `sex`, `civil_status`, `birth_date`, `birth_place`, `religion`, `nationality`, `mobile_number`, `house_no`, `street`, `purok_no`, `subdivision`, `emergency_name`, `emergency_relationship`, `emergency_contact`, `emergency_address`, `national_id`, `philhealth_no`, `voters_id`, `sss_no`, `tin_no`, `pagibig_no`, `years_residency`, `employed_status`, `date_registration`, `updated_at`) VALUES
(1, 1, 'JHODY', 'ATINON', 'M', '', 'assets/uploads/avatars/avatar_1_1780306281.png', 'FEMALE', 'SINGLE', '2005-11-05', 'CALAMBA CITY', 'ROMAN CATHOLIC', 'FILIPINO', '09625389809', '0616', '', '2', '', 'IRENE M. ATINON', 'MOTHER', '0936 5498 878', '0616 PUROK 2, MAKILING, CALAMBA CITY, LAGUNA', '5949848945', '51657897897', '65462261654', '564654988979', '65498798789', '06546548678', 20, 'STUDENT', NULL, '2026-06-01 09:58:41'),
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
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
