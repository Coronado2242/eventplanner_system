-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 10:59 AM
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
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_account`
--

INSERT INTO `admin_account` (`adminid`, `adminuser`, `adminpass`, `role`) VALUES
(0, 'admin', 'admin123', 'superadmin');

-- --------------------------------------------------------

--
-- Table structure for table `cas_department`
--

CREATE TABLE `cas_department` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `fullname` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cas_department`
--

INSERT INTO `cas_department` (`id`, `username`, `password`, `role`, `email`, `fullname`, `created_at`) VALUES
(1, 'CAS_dean', 'user12345', 'CASDean', '', '', '2025-05-20 16:39:09'),
(2, 'CAS_facultyadviser', 'user12345', 'CASFaculty', '', '', '2025-05-20 16:39:09'),
(3, 'CAS_sbopresindent', 'user12345', 'CASPresindent', '', '', '2025-05-20 16:39:09'),
(4, 'CAS_sbovice', 'user12345', 'CASVice', '', '', '2025-05-20 16:39:09'),
(5, 'CAS_sbotresurer', 'user12345', 'CASTresurer', '', '', '2025-05-20 16:39:09'),
(6, 'CAS_sboauditor', 'user12345', 'CASAuditor', '', '', '2025-05-20 16:39:09'),
(7, 'CAS_sbosoo', 'user12345', 'CASSOO', '', '', '2025-05-20 16:39:09');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ccs_department`
--

INSERT INTO `ccs_department` (`id`, `username`, `password`, `role`, `email`, `fullname`, `created_at`) VALUES
(1, 'CCS_dean', 'user12345', 'CCSDean', '', '', '2025-05-19 15:11:51'),
(2, 'CCS_facultyadviser', 'user12345', 'CCSFaculty', '', '', '2025-05-19 15:11:51'),
(3, 'CCS_sbopresindent', 'user12345', 'CCSPresident', '', '', '2025-05-19 15:11:51'),
(4, 'CCS_sbovice', 'user12345', 'CCSVice', '', '', '2025-05-19 15:11:51'),
(5, 'CCS_sbotresurer', 'user12345', 'CCSTresurer', '', '', '2025-05-19 15:11:51'),
(6, 'CCS_sboauditor', 'user12345', 'CCSAuditor', '', '', '2025-05-19 15:11:51'),
(7, 'CCS_sbosoo', 'user12345', 'CCSSOO', '', '', '2025-05-19 15:11:51');

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
-- Table structure for table `cte_department`
--

CREATE TABLE `cte_department` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT '',
  `fullname` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cte_department`
--

INSERT INTO `cte_department` (`id`, `username`, `password`, `role`, `email`, `fullname`, `created_at`) VALUES
(1, 'CTE_dean', 'user12345', 'CTEDean', '', '', '2025-05-23 15:05:44'),
(2, 'CTE_facultyadviser', 'user12345', 'CTEFaculty', '', '', '2025-05-23 15:05:44'),
(3, 'CTE_sbopresindent', 'user12345', 'CTEPresident', '', '', '2025-05-23 15:05:44'),
(4, 'CTE_sbovice', 'user12345', 'CTEVice', '', '', '2025-05-23 15:05:44'),
(5, 'CTE_sbotresurer', 'user12345', 'CTETresurer', '', '', '2025-05-23 15:05:44'),
(6, 'CTE_sboauditor', 'user12345', 'CTEAuditor', '', '', '2025-05-23 15:05:44'),
(7, 'CTE_sbosoo', 'user12345', 'CTESOO', '', '', '2025-05-23 15:05:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_account`
--
ALTER TABLE `admin_account`
  ADD PRIMARY KEY (`adminid`);

--
-- Indexes for table `cas_department`
--
ALTER TABLE `cas_department`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- Indexes for table `cte_department`
--
ALTER TABLE `cte_department`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cas_department`
--
ALTER TABLE `cas_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- AUTO_INCREMENT for table `cte_department`
--
ALTER TABLE `cte_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
