-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2026 at 09:17 AM
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
CREATE TABLE IF NOT EXISTS `admin_accounts` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) DEFAULT 'Barangay Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_at` datetime DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `ucs_admin_username` (`username`),
  UNIQUE KEY `ucs_admin_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(180) NOT NULL,
  `category` varchar(40) NOT NULL DEFAULT 'Announcement',
  `summary` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` varchar(80) DEFAULT NULL,
  `location` varchar(160) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Published',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`announcement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_admin_accounts`
--

DROP TABLE IF EXISTS `archived_admin_accounts`;
CREATE TABLE IF NOT EXISTS `archived_admin_accounts` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
CREATE TABLE IF NOT EXISTS `archived_users` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL,
  `archived_reason` text DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archived_user_profiles`
--

DROP TABLE IF EXISTS `archived_user_profiles`;
CREATE TABLE IF NOT EXISTS `archived_user_profiles` (
  `archive_profile_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`archive_profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

DROP TABLE IF EXISTS `barangay_officials`;
CREATE TABLE IF NOT EXISTS `barangay_officials` (
  `official_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `committee` varchar(100) DEFAULT NULL,
  `term_start` date NOT NULL,
  `term_end` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`official_id`),
  UNIQUE KEY `ucs_official_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_officials`
--

INSERT INTO `barangay_officials` (`official_id`, `user_id`, `position`, `committee`, `term_start`, `term_end`, `is_active`) VALUES
(1, 2, 'Barangay Captain', NULL, '2023-12-01', NULL, 1),
(2, 5, 'Barangay Captain', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(3, 6, 'Barangay Secretary', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(4, 7, 'Barangay Treasurer', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(5, 8, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(6, 9, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(7, 10, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(8, 11, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(9, 12, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(10, 13, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(11, 14, 'Kagawad', 'Barangay Council', '2023-06-30', '2026-06-30', 1),
(12, 15, 'SK Chairman', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(13, 16, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(14, 17, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(15, 18, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(16, 19, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(17, 20, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(18, 21, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1),
(19, 22, 'SK Kagawad', 'Sangguniang Kabataan Council', '2023-06-30', '2026-06-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `completed_requests`
--

DROP TABLE IF EXISTS `completed_requests`;
CREATE TABLE IF NOT EXISTS `completed_requests` (
  `completed_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_request_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `document_type_name` varchar(100) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `purpose` text NOT NULL,
  `document_fee` decimal(10,2) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`completed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `completed_requests`
--

INSERT INTO `completed_requests` (`completed_id`, `original_request_id`, `user_id`, `document_type_name`, `reference_no`, `purpose`, `document_fee`, `requested_at`, `completed_at`) VALUES
(1, 2, 1, 'Barangay Clearance', 'MK-258F5B', 'COMPANY APPLICATION', 50.00, '2026-06-04 20:44:50', '2026-06-08 10:52:22');

-- --------------------------------------------------------

--
-- Table structure for table `completed_reservations`
--

DROP TABLE IF EXISTS `completed_reservations`;
CREATE TABLE IF NOT EXISTS `completed_reservations` (
  `completed_id` int(11) NOT NULL AUTO_INCREMENT,
  `original_reservations_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `facility_name` varchar(100) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `purpose` text NOT NULL,
  `reservation_fee` decimal(10,2) DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`completed_id`),
  KEY `original_reservations_id` (`original_reservations_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

DROP TABLE IF EXISTS `document_types`;
CREATE TABLE IF NOT EXISTS `document_types` (
  `document_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_fee` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`document_type_id`),
  UNIQUE KEY `ucs_doc_types_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
CREATE TABLE IF NOT EXISTS `facilities` (
  `facility_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_fee` decimal(10,2) NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL,
  `max_guests` int(11) NOT NULL,
  PRIMARY KEY (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_id`, `name`, `description`, `base_fee`, `open_time`, `close_time`, `max_guests`) VALUES
(1, 'Basketball Court', 'Reserve the basketball court for sports activities and events', 150.00, '08:00:00', '20:00:00', 30),
(2, 'Events Hall', 'Book the events hall for celebrations, meetings, and gatherings', 500.00, '09:00:00', '21:00:00', 120);

-- --------------------------------------------------------

--
-- Table structure for table `facility_reservations`
--

DROP TABLE IF EXISTS `facility_reservations`;
CREATE TABLE IF NOT EXISTS `facility_reservations` (
  `reservation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `reservation_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `expected_guests` int(11) NOT NULL,
  `purpose` text NOT NULL,
  `additional_notes` text DEFAULT NULL,
  `reservation_fee` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `facility_id` (`facility_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_reservations`
--

INSERT INTO `facility_reservations` (`reservation_id`, `user_id`, `facility_id`, `reference_no`, `reservation_date`, `start_time`, `end_time`, `expected_guests`, `purpose`, `additional_notes`, `reservation_fee`, `status`, `created_at`) VALUES
(1, 1, 1, 'APP-COURT-DEMO', '2026-06-08', '09:00:00', '10:30:00', 18, 'Basketball practice', 'Seed reservation for admin schedule preview.', 150.00, 'Approved', '2026-06-08 10:58:30'),
(2, 1, 2, 'APP-HALL-DEMO', '2026-06-09', '14:00:00', '17:00:00', 80, 'Community assembly', 'Seed reservation for events hall preview.', 500.00, 'Approved', '2026-06-08 10:58:30'),
(3, 1, 1, 'FR-20260610-749242', '2026-06-20', '11:00:00', '13:00:00', 30, 'SANGGUNIANG KATABATAAN ASSEMBLY', '30 monoblocks, 2 mics, 2 speakers', 150.00, 'Approved', '2026-06-10 03:07:35'),
(4, 1, 1, 'FR-20260610-76FF05', '2026-06-20', '11:00:00', '13:00:00', 30, 'SANGGUNIANG KATABATAAN ASSEMBLY', '30 monoblocks, 2 mics, 2 speakers', 150.00, 'Pending', '2026-06-10 03:09:37'),
(5, 1, 1, 'FR-20260610-972ED5', '2026-06-20', '11:00:00', '13:00:00', 30, 'SANGGUNIANG KATABATAAN ASSEMBLY', '30 monoblocks, 2 mics, 2 speakers', 150.00, 'Pending', '2026-06-10 03:20:48'),
(6, 1, 1, 'FR-20260610-B00F7E', '2026-06-20', '11:00:00', '13:00:00', 30, 'SANGGUNIANG KATABATAAN ASSEMBLY', '30 monoblocks, 2 mics, 2 speakers', 150.00, 'Pending', '2026-06-10 03:20:56'),
(7, 1, 1, 'FR-20260610-CAADC3', '2026-06-20', '11:00:00', '13:00:00', 30, 'SANGGUNIANG KATABATAAN ASSEMBLY', '30 monoblocks, 2 mics, 2 speakers', 150.00, 'Pending', '2026-06-10 03:21:01');

-- --------------------------------------------------------

--
-- Table structure for table `request_barangay_ids`
--

DROP TABLE IF EXISTS `request_barangay_ids`;
CREATE TABLE IF NOT EXISTS `request_barangay_ids` (
  `request_id` int(11) NOT NULL,
  `blood_type` varchar(10) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_relationship` varchar(50) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_business_clearances`
--

DROP TABLE IF EXISTS `request_business_clearances`;
CREATE TABLE IF NOT EXISTS `request_business_clearances` (
  `request_id` int(11) NOT NULL,
  `business_name` varchar(100) NOT NULL,
  `business_location` varchar(255) NOT NULL,
  `business_operator` varchar(100) NOT NULL,
  `business_address` text NOT NULL,
  `business_nature` varchar(100) NOT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_cedulas`
--

DROP TABLE IF EXISTS `request_cedulas`;
CREATE TABLE IF NOT EXISTS `request_cedulas` (
  `request_id` int(11) NOT NULL,
  `cedula_type` varchar(50) DEFAULT NULL,
  `tax_year` varchar(10) DEFAULT NULL,
  `place_issued` varchar(100) DEFAULT NULL,
  `income_source` varchar(100) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `gross_income` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`request_id`)
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
CREATE TABLE IF NOT EXISTS `request_construction_permits` (
  `request_id` int(11) NOT NULL,
  `construction_address` text NOT NULL,
  `construction_purpose` varchar(100) NOT NULL,
  `construction_status` varchar(100) NOT NULL,
  `construction_description` text DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_incident_reports`
--

DROP TABLE IF EXISTS `request_incident_reports`;
CREATE TABLE IF NOT EXISTS `request_incident_reports` (
  `request_id` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `incident_location` text NOT NULL,
  `incident_persons` text NOT NULL,
  `incident_narrative` text NOT NULL,
  `incident_action` text DEFAULT NULL,
  `witness_name` varchar(100) DEFAULT NULL,
  `witness_contact` varchar(20) DEFAULT NULL,
  `witness_address` text DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_incident_reports`
--

INSERT INTO `request_incident_reports` (`request_id`, `incident_date`, `incident_time`, `incident_location`, `incident_persons`, `incident_narrative`, `incident_action`, `witness_name`, `witness_contact`, `witness_address`) VALUES
(5, '2026-06-05', '12:48:00', 'Purok 1 asdsadasdasd', 'Si MAJO', 'sadasdjahbkcjkljhwejfsjldvbcs', '', 'Irene m. atinon', '09367214744', '0616 purok 2, brgy. turbina');

-- --------------------------------------------------------

--
-- Table structure for table `request_remarks`
--

DROP TABLE IF EXISTS `request_remarks`;
CREATE TABLE IF NOT EXISTS `request_remarks` (
  `remark_id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `admin_name` varchar(100) NOT NULL,
  `remark` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`remark_id`),
  KEY `idx_request_remarks_request` (`request_id`),
  KEY `idx_request_remarks_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE IF NOT EXISTS `service_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `purpose` text NOT NULL,
  `document_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(20) DEFAULT 'cash',
  `payment_receipt_path` varchar(255) DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT 'Unpaid',
  `id_path` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `process_status` varchar(30) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `ucs_requests_ref_no` (`reference_no`),
  KEY `fk_requests_users` (`user_id`),
  KEY `fk_requests_doc_types` (`document_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`request_id`, `user_id`, `document_type_id`, `reference_no`, `purpose`, `document_fee`, `payment_method`, `payment_receipt_path`, `payment_status`, `id_path`, `status`, `process_status`, `created_at`) VALUES
(1, 1, 1, 'MK-89BE60', 'COMPANY APPLICATION', 50.00, 'cash', NULL, 'Unpaid', 'assets/uploads/requirements/id_1_1780634408.jpg', 'Rejected', 'Pending', '2026-06-04 20:40:08'),
(2, 1, 1, 'MK-258F5B', 'COMPANY APPLICATION', 50.00, 'cash', NULL, 'Paid at Pickup', 'assets/uploads/requirements/id_1_1780634690.jpg', 'Completed', 'COMPLETED', '2026-06-04 20:44:50'),
(3, 1, 2, 'MK-A069EB', 'SCHOLARSHIP PURPOSES', 0.00, 'cash', NULL, 'Unpaid', 'assets/uploads/requirements/id_1_1780634762.jpg', 'Under Review', 'Pending', '2026-06-04 20:46:02'),
(4, 1, 3, 'MK-204186', 'ssss', 20.00, 'cash', NULL, 'Unpaid', 'assets/uploads/requirements/id_1_1780634818.jpg', 'Processing', 'PROCESSING', '2026-06-04 20:46:58'),
(5, 1, 9, 'MK-B09B9D', 'BLOTTER', 50.00, 'online', NULL, 'Unpaid', 'assets/uploads/requirements/id_1_1780634955.jpg', 'Pending', 'Pending', '2026-06-04 20:49:15'),
(6, 1, 7, 'MK-38A83D', 'CREDIT CARD APPLICATION', 0.00, 'online', 'assets/uploads/receipts/receipt_1_1780636179.png', 'Unpaid', 'assets/uploads/requirements/id_1_1780636179.png', 'Pending', 'Pending', '2026-06-04 21:09:39'),
(7, 23, 3, 'MK-4BD81F', 'Scholarship Purposes', 0.00, 'online', 'assets/uploads/receipts/receipt_23_1781075620.png', 'No Fee', 'assets/uploads/requirements/id_23_1781075620.jpg', 'Pending', 'Pending', '2026-06-10 07:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'Residente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `ucs_users_username` (`username`),
  UNIQUE KEY `ucs_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Juday', 'atinon.jhody@gmail.com', '110505', 'Residente', '2026-05-30 07:39:59'),
(2, 'BC_Kapitana', 'barangaycapt@gmail.com', 'admin123', 'Opisyal', '2026-05-30 07:46:33'),
(5, 'official_01', 'aigrette.panganiban.lajara@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(6, 'official_02', 'teona.lizardo.noprada@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(7, 'official_03', 'rubie.alcantara.olaes@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(8, 'official_04', 'hermano.medalla.de.chavez@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(9, 'official_05', 'virgilio.torres.lopez@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(10, 'official_06', 'diomedes.nemes.austria@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(11, 'official_07', 'rizal.mercado.pascual@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(12, 'official_08', 'freddie.balansay.noprada@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(13, 'official_09', 'marcelo.atienza.molinyawe@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(14, 'official_10', 'antonio.hempesalla.medalla@makiling.gov.ph', 'makikonek2026', 'Opisyal', '2026-06-09 14:49:18'),
(15, 'official_11', 'aaron.klyne.macasadia.magsino@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(16, 'official_12', 'christian.heplan.perez@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(17, 'official_13', 'john.paul.de.castro.evangelista@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(18, 'official_14', 'mark.harold.alferez.burgos@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(19, 'official_15', 'dhanna.marie.macasadia.montes@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(20, 'official_16', 'jaz.elle.carpio.alvarez@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(21, 'official_17', 'ellaine.buena.egloria@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(22, 'official_18', 'jhenie.lee.siman.laude@makiling.gov.ph', 'makikonek2026', 'SK', '2026-06-09 14:49:18'),
(23, 'maryjo', 'magboo.mary@gmail.com', 'resident123', 'Residente', '2026-06-10 06:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_emergency_contacts`
--

DROP TABLE IF EXISTS `user_emergency_contacts`;
CREATE TABLE IF NOT EXISTS `user_emergency_contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `relationship` varchar(50) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `fk_emergency_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_emergency_contacts`
--

INSERT INTO `user_emergency_contacts` (`contact_id`, `user_id`, `name`, `relationship`, `contact_number`, `address`) VALUES
(1, 1, 'IRENE M. ATINON', 'MOTHER', '0936 5498 878', '0616 PUROK 2, MAKILING, CALAMBA CITY, LAGUNA'),
(2, 23, 'Edna Magboo', 'Mother', '0912345678', 'Purok 1');

-- --------------------------------------------------------

--
-- Table structure for table `user_government_ids`
--

DROP TABLE IF EXISTS `user_government_ids`;
CREATE TABLE IF NOT EXISTS `user_government_ids` (
  `id_record_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `id_type` varchar(50) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  PRIMARY KEY (`id_record_id`),
  KEY `fk_gov_ids_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
CREATE TABLE IF NOT EXISTS `user_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `icon` varchar(100) DEFAULT 'bell',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `fk_notifications_users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`profile_id`),
  KEY `fk_profiles_users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `first_name`, `last_name`, `middle_name`, `suffix`, `avatar_path`, `sex`, `civil_status`, `birth_date`, `birth_place`, `religion`, `nationality`, `mobile_number`, `house_no`, `street`, `purok_no`, `subdivision`, `years_residency`, `employed_status`, `date_registration`, `updated_at`) VALUES
(1, 1, 'JHODY', 'ATINON', 'M', '', 'assets/uploads/avatars/avatar_1_1780306281.png', 'FEMALE', 'SINGLE', '2005-11-05', 'CALAMBA CITY', 'ROMAN CATHOLIC', 'FILIPINO', '09625389809', '0616', NULL, '2', NULL, 20, 'STUDENT', NULL, '2026-06-01 01:58:41'),
(2, 2, 'Barangay', 'Captain', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-30 07:46:33'),
(5, 5, 'Aigrette Panganiban', 'Lajara', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0001', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(6, 6, 'Teona Lizardo', 'Noprada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0002', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(7, 7, 'Rubie Alcantara', 'Olaes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0003', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(8, 8, 'Hermano Medalla De', 'Chavez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0004', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(9, 9, 'Virgilio Torres', 'Lopez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0005', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(10, 10, 'Diomedes Nemes', 'Austria', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0006', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(11, 11, 'Rizal Mercado', 'Pascual', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0007', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(12, 12, 'Freddie Balansay', 'Noprada', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0008', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(13, 13, 'Marcelo Atienza', 'Molinyawe', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0009', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(14, 14, 'Antonio Hempesalla', 'Medalla', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0010', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(15, 15, 'Aaron Klyne Macasadia', 'Magsino', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0011', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(16, 16, 'Christian Heplan', 'Perez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0012', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(17, 17, 'John Paul De Castro', 'Evangelista', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0013', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(18, 18, 'Mark Harold Alferez', 'Burgos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0014', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(19, 19, 'Dhanna Marie Macasadia', 'Montes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0015', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(20, 20, 'Jaz Elle Carpio', 'Alvarez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0016', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(21, 21, 'Ellaine Buena', 'Egloria', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0017', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(22, 22, 'Jhenie Lee Siman', 'Laude', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0917 000 0018', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-09', '2026-06-09 14:49:18'),
(23, 23, 'Mary Josephine', 'Magboo', 'Almonte', '', 'assets/uploads/avatars/avatar_23_1781075141.jpg', 'FEMALE', 'SINGLE', '2006-08-07', 'CALAMBA CITY', 'ROMAN CATHOLIC', 'FILIPINO', '09196882025', '0001', '', '1', '', 0, 'STUDENT', NULL, '2026-06-10 07:05:41');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD CONSTRAINT `fk_officials_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `completed_reservations`
--
ALTER TABLE `completed_reservations`
  ADD CONSTRAINT `completed_reservations_ibfk_1` FOREIGN KEY (`original_reservations_id`) REFERENCES `facility_reservations` (`reservation_id`),
  ADD CONSTRAINT `completed_reservations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD CONSTRAINT `facility_reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `facility_reservations_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`);

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
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `fk_notifications_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_profiles_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
