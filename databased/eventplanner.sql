-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 03:31 PM
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
-- Table structure for table `budget_plans`
--

CREATE TABLE `budget_plans` (
  `proposal_id` int(11) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `particulars` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `grand_total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_plans`
--

INSERT INTO `budget_plans` (`proposal_id`, `event_name`, `particulars`, `qty`, `amount`, `total`, `grand_total`) VALUES
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, 'Test', 'Example', 12, 240, 2880, 2880),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, '123', 'sadada', 23, 21, 483, 483),
(0, 'example', 'example', 3, 245, 735, 735),
(0, 'example', 'example', 3, 245, 735, 735),
(0, 'example', 'example', 3, 245, 735, 735),
(24, 'Example', 'test', 4, 232, 928, 928),
(24, 'Example', 'test', 4, 232, 928, 928),
(25, 'test1', 'test2', 34, 20, 680, 680),
(25, 'test1', 'test2', 34, 20, 680, 680),
(25, 'test1', 'test2', 34, 20, 680, 680),
(26, 'test4', 'test', 321, 21, 6741, 6741),
(26, 'test4', 'test', 321, 21, 6741, 6741),
(26, 'test4', 'test', 321, 21, 6741, 6741),
(27, 'wda', 'dsadas', 23, 231, 5313, 5313),
(27, 'wda', 'dsadas', 23, 231, 5313, 5313),
(27, 'wda', 'dsadas', 23, 231, 5313, 5313),
(28, '213', '23asd', 2, 1234, 2468, 2468),
(28, '213', '23asd', 2, 1234, 2468, 2468),
(29, 'sda', 'sadkao', 12, 12, 144, 144),
(29, 'sda', 'sadkao', 12, 12, 144, 144),
(29, 'sda', 'sadkao', 12, 12, 144, 144),
(30, 'das', 'sadas', 12, 31, 372, 372),
(30, 'das', 'sadas', 12, 31, 372, 372),
(31, 'sdoiuauiod', 'ioasdhuasu', 23, 122, 2806, 2806),
(31, 'sdoiuauiod', 'ioasdhuasu', 23, 122, 2806, 2806),
(32, 'saddss', 'dsfsdf', 324, 34, 11016, 11016),
(32, 'saddss', 'dsfsdf', 324, 34, 11016, 11016),
(33, 'Example', 'Test1', 12, 500, 6000, 18000),
(33, '', 'Test2', 12, 1000, 12000, 18000),
(33, 'Example', 'Test1', 12, 500, 6000, 18000),
(33, '', 'Test2', 12, 1000, 12000, 18000),
(33, 'Example', 'Test1', 12, 500, 6000, 18000),
(33, '', 'Test2', 12, 1000, 12000, 18000),
(34, 'example', 'example', 12, 132, 1584, 1584),
(34, 'example', 'example', 12, 132, 1584, 1584),
(34, 'example', 'example', 12, 132, 1584, 1584),
(35, 'example', 'example', 12, 12, 144, 144),
(35, 'example', 'example', 12, 12, 144, 144),
(36, 'example', 'example', 12, 31, 372, 372),
(36, 'example', 'example', 12, 31, 372, 372),
(37, 'example', 'example', 13, 200, 2600, 2600),
(37, 'example', 'example', 13, 200, 2600, 2600),
(37, 'example', 'example', 13, 200, 2600, 2600),
(37, 'example', 'example', 13, 200, 2600, 2600),
(37, 'example', 'example', 13, 200, 2600, 2600),
(38, 'test1', 'test1', 123, 50, 6150, 6150),
(38, 'test1', 'test1', 123, 50, 6150, 6150),
(38, 'test1', 'test1', 123, 50, 6150, 6150),
(38, 'test1', 'test1', 123, 50, 6150, 6150),
(38, 'test1', 'test1', 123, 50, 6150, 6150),
(39, 'Test', 'Test', 13, 3000, 39000, 39000),
(39, 'Test', 'Test', 13, 3000, 39000, 39000),
(39, 'Test', 'Test', 13, 3000, 39000, 39000),
(40, 'Test', 'Test1', 13, 3000, 39000, 39000),
(40, 'Test', 'Test1', 13, 3000, 39000, 39000),
(40, 'Test', 'Test1', 13, 3000, 39000, 39000),
(41, 'adsa', 'sadas', 12, 341, 4092, 4092),
(41, 'adsa', 'sadas', 12, 341, 4092, 4092),
(42, 'Test1', 'Ewan', 123, 400, 49200, 49200),
(42, 'Test1', 'Ewan', 123, 400, 49200, 49200),
(43, 'test', 'njbhjfvg', 12, 123, 1476, 1476),
(43, 'test', 'njbhjfvg', 12, 123, 1476, 1476);

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
(2, 'CCS_facultyadviser', '12345678', 'CCSFaculty', 'james@gmail.com', 'ASijdijas', 'no', '2025-05-27 17:46:54'),
(3, 'CCS_sbopresident', '12345678', 'CCSSBOPresident', 'SKDasnmkd@gmail.com', 'James Rix', 'no', '2025-05-27 17:46:54'),
(4, 'CCS_sbovice', '12345678', 'CCSSBOVice', 'james1@gmail.com', 'James Leorix', 'no', '2025-05-27 17:46:54'),
(5, 'CCS_sbotreasurer', '12345678', 'CCSSBOTreasurer', 'sheesh@gmail.com', 'James', 'no', '2025-05-27 17:46:54'),
(6, 'CCS_sboauditor', '12345678', 'CCSSBOAuditor', 'sadoa@gmail.com', 'Leorix', 'no', '2025-05-27 17:46:54'),
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
-- Table structure for table `cte_department`
--

CREATE TABLE `cte_department` (
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
-- Dumping data for table `cte_department`
--

INSERT INTO `cte_department` (`id`, `username`, `password`, `role`, `email`, `fullname`, `firstlogin`, `created_at`) VALUES
(1, 'CTE_dean', 'user12345', 'CTEDean', '', '', 'yes', '2025-06-06 05:47:24'),
(2, 'CTE_facultyadviser', 'user12345', 'CTEFaculty', '', '', 'yes', '2025-06-06 05:47:24'),
(3, 'CTE_sbopresindent', 'user12345', 'CTEPresident', '', '', 'yes', '2025-06-06 05:47:24'),
(4, 'CTE_sbovice', 'user12345', 'CTEVice', '', '', 'yes', '2025-06-06 05:47:24'),
(5, 'CTE_sbotresurer', 'user12345', 'CTETresurer', '', '', 'yes', '2025-06-06 05:47:24'),
(6, 'CTE_sboauditor', 'user12345', 'CTEAuditor', '', '', 'yes', '2025-06-06 05:47:24'),
(7, 'CTE_sbosoo', '123456', 'CTESOO', 'james@gmail.com', 'James Leo', 'no', '2025-06-06 05:47:24');

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
  `time` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `adviser_form` varchar(255) DEFAULT NULL,
  `certification` varchar(255) DEFAULT NULL,
  `financial` varchar(255) DEFAULT NULL,
  `reports` varchar(255) DEFAULT NULL,
  `letter_attachment` varchar(255) DEFAULT NULL,
  `constitution` varchar(255) DEFAULT NULL,
  `budget_approved` varchar(50) DEFAULT NULL,
  `budget_amount` decimal(10,2) DEFAULT NULL,
  `budget_status` enum('pending','approved','disapproved') DEFAULT 'pending',
  `status_level1` enum('Pending','Approved','Disapproved') NOT NULL DEFAULT 'Pending',
  `status_level2` enum('Pending','Approved','Disapproved') NOT NULL DEFAULT 'Pending',
  `status_level3` enum('Pending','Approved','Disapproved') NOT NULL DEFAULT 'Pending',
  `level` varchar(50) DEFAULT 'VP',
  `budget_file` varchar(255) NOT NULL,
  `submit` varchar(255) NOT NULL,
  `notified` tinyint(1) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `disapproved_by` varchar(255) DEFAULT NULL,
  `activity_plan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `venue_db`
--

CREATE TABLE `venue_db` (
  `id` int(11) NOT NULL,
  `organizer` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `venue` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_db`
--

INSERT INTO `venue_db` (`id`, `organizer`, `email`, `venue`) VALUES
(0, 'James', 'sad@yahoo.com', 'Voag');

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
-- Indexes for table `cte_department`
--
ALTER TABLE `cte_department`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
-- AUTO_INCREMENT for table `cte_department`
--
ALTER TABLE `cte_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
