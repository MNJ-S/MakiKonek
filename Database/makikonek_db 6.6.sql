-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 12:32 PM
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

DROP TABLE IF EXISTS `admin_accounts`;
CREATE TABLE `admin_accounts` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) DEFAULT 'Barangay Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` datetime DEFAULT NULL
) ;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`admin_id`, `username`, `email`, `password`, `role`, `created_at`, `archived_at`) VALUES
(1, 'jhody_admin', 'atinon.jhody@gmail.com', 'admin123', 'Super Admin', '2026-05-23 09:41:48', NULL),
(2, 'mary_admin', 'mary@makikonek.ph', 'admin123', 'Super Admin', '2026-05-23 09:41:48', NULL),
(3, 'shem_admin', 'shem@makikonek.ph', 'admin123', 'Super Admin', '2026-05-23 09:41:49', NULL),
(4, 'nat_admin', 'nat@makikonek.ph', 'admin123', 'Super Admin', '2026-05-23 09:41:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `archived_admin_accounts`
--

DROP TABLE IF EXISTS `archived_admin_accounts`;
CREATE TABLE `archived_admin_accounts` (
  `archive_id` int(11) NOT NULL,
  `original_admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_admin_accounts`
--

INSERT INTO `archived_admin_accounts` (`archive_id`, `original_admin_id`, `username`, `email`, `role`, `archived_at`) VALUES
(1, 5, 'XxTimmy_MasterxX', 'atinonjm@students.nu-laguna.edu.ph', 'Super Admin', '2026-06-06 10:15:22');

-- --------------------------------------------------------

--
-- Table structure for table `archived_users`
--

DROP TABLE IF EXISTS `archived_users`;
CREATE TABLE `archived_users` (
  `archive_id` int(11) NOT NULL,
  `original_user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL,
  `archived_reason` text DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_user_profiles`
--

DROP TABLE IF EXISTS `archived_user_profiles`;
CREATE TABLE `archived_user_profiles` (
  `archive_profile_id` int(11) NOT NULL,
  `original_user_id` int(11) NOT NULL,
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
  `years_residency` int(11) DEFAULT NULL,
  `employed_status` varchar(50) DEFAULT NULL,
  `date_registration` date DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

DROP TABLE IF EXISTS `barangay_officials`;
CREATE TABLE `barangay_officials` (
  `official_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `committee` varchar(100) DEFAULT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_officials`
--

INSERT INTO `barangay_officials` (`official_id`, `user_id`, `position`, `committee`, `term_start`, `term_end`, `is_active`) VALUES
(1, 2, 'Barangay Captain', NULL, '2023-12-01', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `completed_requests`
--

DROP TABLE IF EXISTS `completed_requests`;
CREATE TABLE `completed_requests` (
  `completed_id` int(11) NOT NULL,
  `original_request_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `document_type_name` varchar(100) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `purpose` text NOT NULL,
  `document_fee` decimal(10,2) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

DROP TABLE IF EXISTS `document_types`;
CREATE TABLE `document_types` (
  `document_type_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`document_type_id`, `category`, `name`, `description`, `base_fee`) VALUES
(1, 'Certificates', 'Barangay Clearance', NULL, 50.00),
(2, 'Certificates', 'Certificate of Indigency', NULL, 0.00),
(3, 'Certificates', 'Certificate of Residency', NULL, 20.00),
(4, 'Certificates', 'Good Moral Certificate', NULL, 30.00),
(5, 'Business', 'Business Clearance', NULL, 100.00),
(6, 'Permits', 'Building/Construction Permit', NULL, 500.00),
(7, 'Permits', 'Cedula', NULL, 0.00),
(8, 'Others', 'Barangay ID', NULL, 100.00),
(9, 'Others', 'Incident Report', NULL, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `request_barangay_ids`
--

DROP TABLE IF EXISTS `request_barangay_ids`;
CREATE TABLE `request_barangay_ids` (
  `request_id` int(11) NOT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_relationship` varchar(50) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `valid_until` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_business_clearances`
--

DROP TABLE IF EXISTS `request_business_clearances`;
CREATE TABLE `request_business_clearances` (
  `request_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `business_location` varchar(255) NOT NULL,
  `business_operator` varchar(100) NOT NULL,
  `business_address` text NOT NULL,
  `business_nature` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_cedulas`
--

DROP TABLE IF EXISTS `request_cedulas`;
CREATE TABLE `request_cedulas` (
  `request_id` int(11) NOT NULL,
  `cedula_type` varchar(50) DEFAULT NULL,
  `tax_year` varchar(10) DEFAULT NULL,
  `place_issued` varchar(100) DEFAULT NULL,
  `income_source` varchar(100) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `gross_income` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_cedulas`
--

INSERT INTO `request_cedulas` (`request_id`, `cedula_type`, `tax_year`, `place_issued`, `income_source`, `height`, `weight`, `gross_income`) VALUES
(6, 'Business', '2024', 'Barangay Makiling', 'Self-Employed', '5\'2', '64 kg', 250000.00);

-- --------------------------------------------------------

--
-- Table structure for table `request_construction_permits`
--

DROP TABLE IF EXISTS `request_construction_permits`;
CREATE TABLE `request_construction_permits` (
  `request_id` int(11) NOT NULL,
  `construction_address` text NOT NULL,
  `construction_purpose` varchar(100) NOT NULL,
  `construction_status` varchar(100) NOT NULL,
  `construction_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_incident_reports`
--

DROP TABLE IF EXISTS `request_incident_reports`;
CREATE TABLE `request_incident_reports` (
  `request_id` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `incident_location` text NOT NULL,
  `incident_persons` text NOT NULL,
  `incident_narrative` text NOT NULL,
  `incident_action` text DEFAULT NULL,
  `witness_name` varchar(100) DEFAULT NULL,
  `witness_contact` varchar(20) DEFAULT NULL,
  `witness_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_incident_reports`
--

INSERT INTO `request_incident_reports` (`request_id`, `incident_date`, `incident_time`, `incident_location`, `incident_persons`, `incident_narrative`, `incident_action`, `witness_name`, `witness_contact`, `witness_address`) VALUES
(5, '2026-06-05', '12:48:00', 'Purok 1 asdsadasdasd', 'Si MAJO', 'sadasdjahbkcjkljhwejfsjldvbcs', '', 'Irene m. atinon', '09367214744', '0616 purok 2, brgy. turbina');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `purpose` text NOT NULL,
  `document_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) DEFAULT 'cash',
  `payment_receipt_path` varchar(255) DEFAULT NULL,
  `id_path` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `process_status` varchar(30) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`request_id`, `user_id`, `document_type_id`, `reference_no`, `purpose`, `document_fee`, `payment_method`, `payment_receipt_path`, `id_path`, `status`, `process_status`, `created_at`) VALUES
(1, 1, 1, 'MK-89BE60', 'COMPANY APPLICATION', 50.00, 'cash', NULL, 'assets/uploads/requirements/id_1_1780634408.jpg', 'Rejected', 'Pending', '2026-06-04 20:40:08'),
(2, 1, 1, 'MK-258F5B', 'COMPANY APPLICATION', 50.00, 'cash', NULL, 'assets/uploads/requirements/id_1_1780634690.jpg', 'APPROVED', 'Pending', '2026-06-04 20:44:50'),
(3, 1, 2, 'MK-A069EB', 'SCHOLARSHIP PURPOSES', 0.00, 'cash', NULL, 'assets/uploads/requirements/id_1_1780634762.jpg', 'Pending', 'Pending', '2026-06-04 20:46:02'),
(4, 1, 3, 'MK-204186', 'ssss', 20.00, 'cash', NULL, 'assets/uploads/requirements/id_1_1780634818.jpg', 'Pending', 'Pending', '2026-06-04 20:46:58'),
(5, 1, 9, 'MK-B09B9D', 'BLOTTER', 50.00, 'online', NULL, 'assets/uploads/requirements/id_1_1780634955.jpg', 'Pending', 'Pending', '2026-06-04 20:49:15'),
(6, 1, 7, 'MK-38A83D', 'CREDIT CARD APPLICATION', 0.00, 'online', 'assets/uploads/receipts/receipt_1_1780636179.png', 'assets/uploads/requirements/id_1_1780636179.png', 'Pending', 'Pending', '2026-06-04 21:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'Residente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Juday', 'atinon.jhody@gmail.com', '110505', 'Residente', '2026-05-30 07:39:59'),
(2, 'BC_Kapitana', 'barangaycapt@gmail.com', 'admin123', 'Opisyal', '2026-05-30 07:46:33');

-- --------------------------------------------------------

--
-- Table structure for table `user_emergency_contacts`
--

DROP TABLE IF EXISTS `user_emergency_contacts`;
CREATE TABLE `user_emergency_contacts` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_emergency_contacts`
--

INSERT INTO `user_emergency_contacts` (`contact_id`, `user_id`, `name`, `relationship`, `contact_number`, `address`) VALUES
(1, 1, 'IRENE M. ATINON', 'MOTHER', '0936 5498 878', '0616 PUROK 2, MAKILING, CALAMBA CITY, LAGUNA');

-- --------------------------------------------------------

--
-- Table structure for table `user_government_ids`
--

DROP TABLE IF EXISTS `user_government_ids`;
CREATE TABLE `user_government_ids` (
  `id_record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id_type` varchar(50) NOT NULL,
  `id_number` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_government_ids`
--

INSERT INTO `user_government_ids` (`id_record_id`, `user_id`, `id_type`, `id_number`) VALUES
(1, 1, 'National ID', '5949848945'),
(2, 1, 'PhilHealth', '51657897897'),
(3, 1, 'Voters ID', '65462261654'),
(4, 1, 'SSS', '564654988979'),
(5, 1, 'TIN', '65498798789'),
(6, 1, 'Pag-IBIG', '06546548678');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
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
  `years_residency` int(11) DEFAULT NULL,
  `employed_status` varchar(50) DEFAULT NULL,
  `date_registration` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `first_name`, `last_name`, `middle_name`, `suffix`, `avatar_path`, `sex`, `civil_status`, `birth_date`, `birth_place`, `religion`, `nationality`, `mobile_number`, `house_no`, `street`, `purok_no`, `subdivision`, `years_residency`, `employed_status`, `date_registration`, `updated_at`) VALUES
(1, 1, 'JHODY', 'ATINON', 'M', '', 'assets/uploads/avatars/avatar_1_1780306281.png', 'FEMALE', 'SINGLE', '2005-11-05', 'CALAMBA CITY', 'ROMAN CATHOLIC', 'FILIPINO', '09625389809', '0616', NULL, '2', NULL, 20, 'STUDENT', NULL, '2026-06-01 01:58:41'),
(2, 2, 'Barangay', 'Captain', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30 07:46:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `ucs_admin_username` (`username`),
  ADD UNIQUE KEY `ucs_admin_email` (`email`);

--
-- Indexes for table `archived_admin_accounts`
--
ALTER TABLE `archived_admin_accounts`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archived_users`
--
ALTER TABLE `archived_users`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `archived_user_profiles`
--
ALTER TABLE `archived_user_profiles`
  ADD PRIMARY KEY (`archive_profile_id`);

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`official_id`),
  ADD UNIQUE KEY `ucs_official_user` (`user_id`);

--
-- Indexes for table `completed_requests`
--
ALTER TABLE `completed_requests`
  ADD PRIMARY KEY (`completed_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`document_type_id`),
  ADD UNIQUE KEY `ucs_doc_types_name` (`name`);

--
-- Indexes for table `request_barangay_ids`
--
ALTER TABLE `request_barangay_ids`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `request_business_clearances`
--
ALTER TABLE `request_business_clearances`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `request_cedulas`
--
ALTER TABLE `request_cedulas`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `request_construction_permits`
--
ALTER TABLE `request_construction_permits`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `request_incident_reports`
--
ALTER TABLE `request_incident_reports`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `ucs_requests_ref_no` (`reference_no`),
  ADD KEY `fk_requests_users` (`user_id`),
  ADD KEY `fk_requests_doc_types` (`document_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `ucs_users_username` (`username`),
  ADD UNIQUE KEY `ucs_users_email` (`email`);

--
-- Indexes for table `user_emergency_contacts`
--
ALTER TABLE `user_emergency_contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `fk_emergency_users` (`user_id`);

--
-- Indexes for table `user_government_ids`
--
ALTER TABLE `user_government_ids`
  ADD PRIMARY KEY (`id_record_id`),
  ADD KEY `fk_gov_ids_users` (`user_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD KEY `fk_profiles_users` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_admin_accounts`
--
ALTER TABLE `archived_admin_accounts`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archived_users`
--
ALTER TABLE `archived_users`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `archived_user_profiles`
--
ALTER TABLE `archived_user_profiles`
  MODIFY `archive_profile_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  MODIFY `official_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `completed_requests`
--
ALTER TABLE `completed_requests`
  MODIFY `completed_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `document_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_emergency_contacts`
--
ALTER TABLE `user_emergency_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_government_ids`
--
ALTER TABLE `user_government_ids`
  MODIFY `id_record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD CONSTRAINT `fk_officials_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_barangay_ids`
--
ALTER TABLE `request_barangay_ids`
  ADD CONSTRAINT `fk_brgy_id_requests` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_business_clearances`
--
ALTER TABLE `request_business_clearances`
  ADD CONSTRAINT `fk_business_requests` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_cedulas`
--
ALTER TABLE `request_cedulas`
  ADD CONSTRAINT `fk_cedulas_requests` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_construction_permits`
--
ALTER TABLE `request_construction_permits`
  ADD CONSTRAINT `fk_construction_requests` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_incident_reports`
--
ALTER TABLE `request_incident_reports`
  ADD CONSTRAINT `fk_incidents_requests` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `fk_requests_doc_types` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`document_type_id`),
  ADD CONSTRAINT `fk_requests_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_emergency_contacts`
--
ALTER TABLE `user_emergency_contacts`
  ADD CONSTRAINT `fk_emergency_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_government_ids`
--
ALTER TABLE `user_government_ids`
  ADD CONSTRAINT `fk_gov_ids_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_profiles_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
