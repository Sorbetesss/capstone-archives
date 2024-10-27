-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2024 at 02:53 PM
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
-- Table structure for table `class_subjects`
--

CREATE TABLE `class_subjects` (
  `id` int(11) NOT NULL,
  `class_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subjects`
--

INSERT INTO `class_subjects` (`id`, `class_code`, `description`, `created_at`) VALUES
(2, '1111A', 'DOTA Gaming', '2024-10-17 08:56:13'),
(3, '1111B', 'DOTA Gaming 2', '2024-10-17 08:56:23'),
(4, '1111C', 'DOTA2 Mechanics', '2024-10-17 12:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `offercode` varchar(5) NOT NULL,
  `prof_id` int(12) NOT NULL,
  `student_id` int(8) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `semester_id` int(11) NOT NULL,
  `academic_year` varchar(9) DEFAULT NULL,
  `semester` enum('1st','2nd','Off-Sem') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`semester_id`, `academic_year`, `semester`, `start_date`, `end_date`, `is_active`) VALUES
(18, '2024-2025', '1st', '2024-10-17', '2024-12-31', 1),
(19, '2024-2025', '2nd', '2025-01-01', '2025-04-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(8) NOT NULL,
  `first_name` text DEFAULT NULL,
  `last_name` text DEFAULT NULL,
  `course` text DEFAULT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `first_name`, `last_name`, `course`, `student_email`, `password`) VALUES
(20200528, 'Carlo Isidro', 'Mamac', 'BSIT', 'carloisidromamac@sjp2cd.edu.ph', 'hahaxd'),
(20242025, 'Karl', 'Mamak', 'BSIT', 'cdals@gmail.com', 'hahaha'),
(456456465, 'Cal', 'Sidro', 'BSIT', 'calmac@sjp2cd.edu.ph', 'hahaokay');

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

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `prof_id` int(12) NOT NULL,
  `prof_fname` text DEFAULT NULL,
  `prof_lname` text DEFAULT NULL,
  `prof_email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`prof_id`, `prof_fname`, `prof_lname`, `prof_email`, `password`) VALUES
(123123123, 'Macc', 'Loyy', 'mloyy@sjp2cd.edu.ph', 'macloy');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class_subjects`
--
ALTER TABLE `class_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_code` (`class_code`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`offercode`,`student_id`,`semester_id`),
  ADD KEY `prof_id` (`prof_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_course_semester` (`semester_id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`student_id`,`offercode`,`semester_id`),
  ADD KEY `offercode` (`offercode`),
  ADD KEY `fk_semester` (`semester_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`prof_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class_subjects`
--
ALTER TABLE `class_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `semester_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`prof_id`) REFERENCES `teacher` (`prof_id`),
  ADD CONSTRAINT `course_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `fk_course_class_subjects` FOREIGN KEY (`offercode`) REFERENCES `class_subjects` (`class_code`),
  ADD CONSTRAINT `fk_course_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`semester_id`);

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
