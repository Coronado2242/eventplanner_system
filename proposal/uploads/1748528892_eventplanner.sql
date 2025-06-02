-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 03:16 PM
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
-- Database: `eventplanner`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_account`
--

CREATE TABLE `admin_account` (
  `adminid` int(32) NOT NULL,
  `adminuser` varchar(255) NOT NULL,
  `adminpass` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `firstlogin` varchar(10) DEFAULT 'yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_account`
--

INSERT INTO `admin_account` (`adminid`, `adminuser`, `adminpass`, `role`, `firstlogin`) VALUES
(0, 'admin', 'admin123', 'superadmin', 'no'),
(1, 'osas', '123456', 'Osas', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `ccs_department`
--

CREATE TABLE `ccs_department` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `fullname` varchar(255) DEFAULT '',
  `firstlogin` varchar(255) DEFAULT 'yes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ccs_department`
--

INSERT INTO `ccs_department` (`id`, `username`, `password`, `role`, `email`, `fullname`, `firstlogin`, `created_at`) VALUES
(1, 'CCS_dean', '12345678', 'CCSDean', 'james@gmail.com', 'James Leorix', 'no', '2025-05-27 17:46:54'),
(2, 'CCS_facultyadviser', 'user12345', 'CCSFaculty', '', '', 'yes', '2025-05-27 17:46:54'),
(3, 'CCS_sbopresindent', 'user12345', 'CCSPresident', '', '', 'yes', '2025-05-27 17:46:54'),
(4, 'CCS_sbovice', '12345678', 'CCSVice', 'james1@gmail.com', 'James Leorix', 'no', '2025-05-27 17:46:54'),
(5, 'CCS_sbotresurer', 'user12345', 'CCSTresurer', '', '', 'yes', '2025-05-27 17:46:54'),
(6, 'CCS_sboauditor', 'user12345', 'CCSAuditor', '', '', 'yes', '2025-05-27 17:46:54'),
(7, 'CCS_sbosoo', '123456', 'CCSSOO', 'james@gmail.com', 'James Leorix', 'no', '2025-05-27 17:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `client_account`
--

CREATE TABLE `client_account` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `organization` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `time` varchar(50) DEFAULT NULL,
  `adviser_form` varchar(255) DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `financial` varchar(255) DEFAULT NULL,
  `constitution` varchar(255) DEFAULT NULL,
  `reports` varchar(255) DEFAULT NULL,
  `letter_attachment` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `budget_approved` tinyint(1) DEFAULT 0,
  `budget_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `department`, `event_type`, `start_date`, `end_date`, `venue`, `time`, `adviser_form`, `certification`, `financial`, `constitution`, `reports`, `letter_attachment`, `status`, `created_at`, `budget_approved`, `budget_amount`) VALUES
(1, 'CCS', 'CCS night', '2025-05-26', '2025-05-27', 'Gym', '6:00pm-6:00am', 'uploads/1748239802_eventplanner.sql', 'uploads/1748239802_eventplanner.sql', 'uploads/1748239802_eventplanner.sql', 'uploads/1748239802_eventplanner.sql', 'uploads/1748239802_eventplanner.sql', 'uploads/1748239802_eventplanner.sql', 'Pending', '2025-05-26 06:10:02', 0, NULL),
(12, 'CCS', 'CCS Week', '2025-05-29', '2025-05-31', 'Gym', '6:00pm-5:00am', 'uploads/1748474375_1748239802_eventplanner.sql', 'uploads/1748474375_eventplanner.sql', 'uploads/1748474375_eventplanner.sql', 'uploads/1748474375_eventplanner.sql', 'uploads/1748474375_eventplanner.sql', 'uploads/1748474375_1748239802_eventplanner.sql', 'pending', '2025-05-28 23:19:35', 1, 10000.00),
(13, 'CCS', 'CCS MLBB Tourna', '2025-05-29', '2025-05-31', 'Voag', '3:00pm-5:00pm', 'uploads/1748426653_eventplanner.sql', 'uploads/1748426653_eventplanner.sql', 'uploads/1748426653_eventplanner.sql', 'uploads/1748426653_eventplanner.sql', 'uploads/1748426653_eventplanner.sql', 'uploads/1748426653_eventplanner.sql', 'pending', '2025-05-28 10:04:13', 1, 5000.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`adminid`);

--
-- Indexes for table `ccs_department`
--
ALTER TABLE `ccs_department`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `client_account`
--
ALTER TABLE `client_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ccs_department`
--
ALTER TABLE `ccs_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client_account`
--
ALTER TABLE `client_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
