-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2024 at 03:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `student_id` int(11) NOT NULL,
  `offercode` varchar(255) NOT NULL,
  `prelim` decimal(5,2) DEFAULT 0.00,
  `midterm` decimal(5,2) DEFAULT 0.00,
  `final` decimal(5,2) DEFAULT 0.00,
  `ffg` decimal(5,2) DEFAULT 0.00,
  `prelim_posted` tinyint(1) DEFAULT 0,
  `midterm_posted` tinyint(1) DEFAULT 0,
  `final_posted` tinyint(1) DEFAULT 0,
  `ffg_posted` tinyint(1) DEFAULT 0,
  `is_posted` tinyint(1) DEFAULT 0,
  `semester_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`student_id`, `offercode`, `prelim`, `midterm`, `final`, `ffg`, `prelim_posted`, `midterm_posted`, `final_posted`, `ffg_posted`, `is_posted`, `semester_id`) VALUES
(20200528, '1111A', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 18),
(20242025, '1111A', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 19),
(20242025, '1111B', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 18);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`student_id`,`offercode`,`semester_id`),
  ADD KEY `offercode` (`offercode`),
  ADD KEY `fk_semester` (`semester_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `fk_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`),
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`offercode`) REFERENCES `course` (`offercode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
