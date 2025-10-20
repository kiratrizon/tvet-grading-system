-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 02:04 PM
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
-- Database: `grading_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `a_id` int(11) NOT NULL,
  `a_name` text NOT NULL,
  `a_user_name` text NOT NULL,
  `a_password` text NOT NULL,
  `a_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`a_id`, `a_name`, `a_user_name`, `a_password`, `a_image`) VALUES
(1, 'Gail E. Pacquiao', 'gail123@gmail.com', 'gail231', 'img_67c2d1d15c05c8.98660813.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`) VALUES
(1, 'DIT', 'Diploma in Information Technology'),
(5, 'DIST', 'Diploma in Information Systems Technology'),
(6, 'DSOT', 'Diploma in Security Operation Technology'),
(7, 'DBOT', 'Diploma in Business Operation Technology');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` varchar(150) NOT NULL,
  `semester` varchar(150) NOT NULL,
  `school_year` varchar(120) NOT NULL,
  `course_code` varchar(150) NOT NULL,
  `descriptive_title` varchar(255) NOT NULL,
  `final_rating` decimal(3,1) NOT NULL,
  `remarks` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `name`, `course`, `year_level`, `semester`, `school_year`, `course_code`, `descriptive_title`, `final_rating`, `remarks`, `teacher_id`, `subject_id`, `section`) VALUES
(186, 30, 'Ana Garcia', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 1.0, 'Passed', 2, 3, 'DBOT 2B'),
(187, 31, 'Isabelino Mabini', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 1.2, 'Passed', 2, 3, 'DBOT 2B'),
(188, 32, 'Trinidad Bonifacio', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 5.0, 'Failed', 2, 3, 'DBOT 2B'),
(189, 33, 'Segundino Jacinto', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 1.5, 'Passed', 2, 3, 'DBOT 2B'),
(190, 34, 'Felicerio Rizal', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 2.5, 'Passed', 2, 3, 'DBOT 2B'),
(191, 35, 'Marceliana Luna', '7', 'First Year', 'First Semester', '2024-2025', 'GE 1', 'Understanding Self', 1.8, 'Passed', 2, 3, 'DBOT 2B'),
(192, 36, 'Amancio del Pilar', '1', 'First Year', 'First Semester', '2024-2025', 'CC 101', 'Introduction to Computing with Keyboard', 5.0, 'Failed', 4, 24, 'DIT 2B'),
(193, 37, 'Luzviminda Aguinaldo', '1', 'First Year', 'First Semester', '2024-2025', 'CC 101', 'Introduction to Computing with Keyboard', 2.5, 'Passed', 4, 24, 'DIT 2B'),
(194, 38, 'Crisanto Silang', '1', 'First Year', 'First Semester', '2024-2025', 'CC 101', 'Introduction to Computing with Keyboard', 3.0, 'Passed', 4, 24, 'DIT 2B'),
(195, 39, 'Severina Gomburza', '1', 'First Year', 'First Semester', '2024-2025', 'CC 101', 'Introduction to Computing with Keyboard', 2.5, 'Passed', 4, 24, 'DIT 2B'),
(196, 40, 'Damiana Malvar', '1', 'First Year', 'First Semester', '2024-2025', 'CC 101', 'Introduction to Computing with Keyboard', 1.8, 'Passed', 4, 24, 'DIT 2B');

-- --------------------------------------------------------

--
-- Table structure for table `student_missing_requirements`
--

CREATE TABLE `student_missing_requirements` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `missing_requirement` text NOT NULL,
  `flagged_by_teacher` int(11) NOT NULL,
  `flagged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `studid` int(11) NOT NULL,
  `remarks` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_users`
--

CREATE TABLE `student_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `s_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_users`
--

INSERT INTO `student_users` (`id`, `name`, `course`, `email`, `password`, `s_image`) VALUES
(30, 'Ana Garcia', '7', 'anagarcia@school.edu', 'pass123', ''),
(31, 'Isabelino Mabini', '7', 'isabelinomabini@school.edu', '$2y$10$WuJjgjFTyuy0vDnQ4cceYOo.0mePDr954YYcZLlUA8y9BlHMiVNoa', ''),
(32, 'Trinidad Bonifacio', '7', 'trinidadbonifacio@school.edu', '$2y$10$l03vZAN9i9Sukj0KGUWQJ.ir9d8uzs1Dn.y/Nm3qllZ2yLzml8dna', ''),
(33, 'Segundino Jacinto', '7', 'segundinojacinto@school.edu', '$2y$10$ASMi5UYSlPrJ38B95hT9v.4u7jFXWIg8ROu5PpYto/zEHJR7fAPp2', ''),
(34, 'Felicerio Rizal', '7', 'feliceriorizal@school.edu', '$2y$10$PX5a0MTaAAcro4xV2A98OOu.rOoJyHK4TAJfjqaVpYvK2OLPURNJm', ''),
(35, 'Marceliana Luna', '7', 'marcelianaluna@school.edu', '$2y$10$eQO9rrNM1jGQCkpsBFkk8.E5rbndRCWDKlRvIa4PJhF46PxM2QvAi', ''),
(36, 'Amancio del Pilar', '1', 'amanciodelpilar@school.edu', '$2y$10$LiN2CFY0EfbM2nVWHbPc4OxgG.y3cU24wQr.0kzklWXDA.JJLi2y.', ''),
(37, 'Luzviminda Aguinaldo', '1', 'luzvimindaaguinaldo@school.edu', '$2y$10$Wp2.zgjItf7CpSMvHIyx.O.b7tmK3t89Ib7g3RbfSmtYZGBX6eT5a', ''),
(38, 'Crisanto Silang', '1', 'crisantosilang@school.edu', '$2y$10$9qOpeMJK3OF7gINmxWjL6.3g9PSEhzR9KSggZV3xf2JiXzhq9iI2a', ''),
(39, 'Severina Gomburza', '1', 'severinagomburza@school.edu', '$2y$10$9E4xB9LyFleY3MJ6zxCzo.6k9ER1ZlnQi7qLVRwFSYYZuAQ4LhbUG', ''),
(40, 'Damiana Malvar', '1', 'damianamalvar@school.edu', '$2y$10$Ie7xyQ4OmXzhJhU0SjtAJ.3/H8hEX36M/iqMWsaYIAhNQjceKUITu', '');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `s_id` int(11) NOT NULL,
  `s_semester` text NOT NULL,
  `s_course_code` text NOT NULL,
  `s_descriptive_title` text NOT NULL,
  `s_nth` text NOT NULL,
  `s_units` text NOT NULL,
  `s_lee` text NOT NULL,
  `s_lab` int(11) NOT NULL DEFAULT 0,
  `s_covered_qualification` text NOT NULL,
  `s_pre_requisite` text NOT NULL,
  `s_year_level` text NOT NULL,
  `s_course` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`s_id`, `s_semester`, `s_course_code`, `s_descriptive_title`, `s_nth`, `s_units`, `s_lee`, `s_lab`, `s_covered_qualification`, `s_pre_requisite`, `s_year_level`, `s_course`) VALUES
(3, 'First Semester', 'GE 1', 'Understanding Self', '54', '3', '3', 0, '', '', 'First Year', '7'),
(4, 'First Semester', 'GE 2', 'Reading in Philipine History', '54', '3', '3', 0, '', '', 'First Year', '7'),
(5, 'First Semester', 'GE 3', 'The Contemporary World', '54', '3', '3', 0, '', '', 'First Year', '7'),
(6, 'First Semester', 'BA 1', 'Basic Microeconomics', '54/301', '3', '3', 0, 'Microfinance NCII', '', 'First Year', '7'),
(7, 'First Semester', 'A 1a', 'Fundamentals of Accounting', '108/284', '6', '6', 0, '', '', 'First Year', '7'),
(8, 'First Semester', 'Path Fit 1', 'Movement Competency Training', '', '2', '2', 0, '', '', 'First Year', '7'),
(9, 'First Semester', 'NSTP 1', 'National Service Training Program', '54', '3', '3', 0, '', '', 'First Year', '7'),
(10, 'Second Semester', 'GE 4', 'Mathematics in the Modern World', '54', '3', '3', 0, '', '', 'First Year', '7'),
(11, 'First Semester', 'GE 7', 'Science Technology & Society', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(12, 'First Semester', 'GE Elic 1', 'Mathematics, Science & Technology', '54', '3', '3', 0, '', '', 'Third Year', '7'),
(13, 'First Semester', 'GE 1', 'Understanding Self', '56', '3', '3', 0, '', '', 'First Year', '6'),
(14, 'First Semester', 'GE 2', 'Readings in Philipine History', '56', '3', '3', 0, '', '', 'First Year', '6'),
(15, 'First Semester', 'GE 3', 'The Contemporary World', '56', '3', '3', 0, '', '', 'First Year', '6'),
(16, 'First Semester', 'GE 4', 'Mathematics in the Modern World', '304', '3', '3', 0, '', '', 'First Year', '6'),
(17, 'First Semester', 'GE 5', 'Purposive Communication', '200', '3', '3', 0, '', '', 'First Year', '6'),
(18, 'First Semester', 'Crim 1', 'Introduction to Criminology', '56', '3', '3', 0, '', '', 'First Year', '6'),
(19, 'First Semester', 'DEFTAC 1', 'Fundamentals of Martial Arts', '56', '2', '2', 0, '', '', 'First Year', '6'),
(20, 'First Semester', 'NSTP 1', 'ROTC', '56', '3', '3', 0, '', '', 'First Year', '6'),
(21, 'First Semester', 'GE 1', 'Understanding Self', '56', '3', '3', 0, '', '', 'First Year', '5'),
(22, 'First Semester', 'GE 2', 'Readings in Philipine History', '56', '3', '3', 0, '', '', 'First Year', '5'),
(23, 'First Semester', 'GE 3', 'The Contemporary World', '56', '3', '3', 0, '', '', 'First Year', '5'),
(24, 'First Semester', 'CC 101', 'Introduction to Computing with Keyboard', '202/210', '3', '3', 0, '', '', 'First Year', '1'),
(25, 'First Semester', 'CC 102', 'Computer Programming 1 (Fundamental of Programming)', '56', '3', '3', 0, '', '', 'First Year', '1'),
(26, 'Second Semester', 'GE 5 ', 'Purposive Communication', '54', '3', '3', 0, '', '', 'First Year', '7'),
(27, 'Second Semester', 'GE 6', 'Art Appreciation', '54', '3', '3', 0, '', '', 'First Year', '7'),
(28, 'Second Semester', 'BA 2', 'Obligations & Contract', '54/120', '3', '3', 0, '', '', 'First Year', '7'),
(29, 'Second Semester', 'A 1', 'Fundamentals of Partnership & Corporation Accounting', '54', '3', '3', 0, 'Bookkeeping NCIII', '', 'First Year', '7'),
(30, 'Second Semester', 'Path Fit 2', 'Exercise-based Fitness', '54', '2', '2', 0, '', '', 'First Year', '7'),
(31, 'Second Semester', 'NSTP 1', 'National Service Training Program', '54', '3', '3', 0, '', '', 'First Year', '7'),
(32, 'Second Semester', 'BA a', 'Principles of Management', '54/400', '3', '3', 0, 'Front Office service NCII', '', 'First Year', '7'),
(33, 'Summer', 'OJT', 'On-the-Job Training', '', '200', '0', 4, '', '', 'First Year', '7'),
(34, 'First Semester', 'GE 8', 'Ethics', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(35, 'First Semester', 'GE 9 ', 'Introduction sa Pag-aaral ng Wika', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(36, 'First Semester', 'BA 3', 'Human Behavior in an Organization with Case Analysis', '54/400', '3', '3', 0, 'Housekeeping NC II', '', 'Second Year', '7'),
(37, 'First Semester', 'MM 101', 'Professional Salesmanship', '54/108', '3', '3', 0, 'Contact Services NC II', '', 'Second Year', '7'),
(38, 'First Semester', 'MM 102', 'Marketing Research', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(39, 'First Semester', 'Path Fit 3', 'Dance and Sport', '54', '2', '2', 0, '', '', 'Second Year', '7'),
(40, 'Second Semester', 'GE 10', 'Panitikan ng Pilipinas', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(41, 'Second Semester', 'GE 11', 'Pagpapahalaga sa Kulturang Pilipino', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(42, 'Second Semester', 'GE 12', 'Life & Works of Rizal', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(43, 'Second Semester', 'BA 4', 'Income Taxation', '54/120', '3', '3', 0, '', '', 'Second Year', '7'),
(44, 'Second Semester', 'BA 5', 'Good Governance and Social Responsibility', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(45, 'Second Semester', 'MM 103', 'Marketing Management', '54', '3', '3', 0, '', '', 'Second Year', '7'),
(46, 'Second Semester', 'MM 104', 'Distribution Management', '54/88', '3', '3', 0, 'Warehousing NC II', '', 'Second Year', '7'),
(47, 'Second Semester', 'Path Fit 4', 'Outdoor and Adventure Activities', '54', '2', '2', 0, '', '', 'Second Year', '7'),
(48, 'Summer', 'OJT 101', 'On-the-Job Training', '0', '6', '0', 4, '', '', 'Second Year', '7'),
(49, 'First Semester', 'GE Elec 2', 'Social Science and Philosophy', '56', '3', '3', 0, '', '', 'Third Year', '7'),
(50, 'First Semester', 'GE Elec 3', 'Arts and Humanities', '56', '3', '3', 0, '', '', 'Third Year', '7'),
(51, 'First Semester', 'BA 6', 'Human Resource Management', '56', '3', '3', 0, '', '', 'Third Year', '7'),
(52, 'First Semester', 'BA 7', 'International Trade and Agreement', '546160', '3', '3', 0, 'Local Guiding Service NC II', '', 'Third Year', '7'),
(53, 'First Semester', 'MM 105', 'Advertising', '56', '3', '3', 0, 'Tourism Promotion Services NC II', '', 'Third Year', '7'),
(54, 'First Semester', 'MM 106', 'Product Management', '54', '3', '3', 0, '', '', 'Third Year', '7'),
(55, 'Second Semester', 'A&B 1', 'Strategic Management', '54/80', '3', '3', 0, 'Driving NC II', '', 'Third Year', '7'),
(56, 'Second Semester', 'Elec 1', 'Enterpreneurial Management', '54/108', '3', '3', 0, 'Events Management Services NC II', '', 'Third Year', '7'),
(57, 'Second Semester', 'BA 8', 'Business Research', '54', '3', '3', 0, '', '', 'Third Year', '7'),
(58, 'Second Semester', 'MM 107', 'Retail Management', '54', '3', '3', 0, '', '', 'Third Year', '7'),
(59, 'Second Semester', 'SIL', 'Supervised Industry Learning/OJT', '200', '2', '0', 0, '', '', 'Third Year', '7'),
(60, 'Second Semester', 'GE 6', 'Art Approach', '290', '3', '3', 0, 'Illustration NC II', '', 'First Year', '6'),
(61, 'Second Semester', 'GE 7', 'Science Technology & Society', '56', '3', '3', 0, '', '', 'First Year', '6'),
(62, 'Second Semester', 'GE 8', 'Ethics', '56', '3', '3', 0, '', '', 'First Year', '6'),
(63, 'Second Semester', 'LEA 1', 'Law Enforcement Orgazination and Administration', '412', '4', '4', 0, '', 'Crim 1', 'First Year', '6'),
(64, 'Second Semester', 'CLJ 1', 'Into to Phil. Criminal Justice System', '56', '3', '3', 0, '', '', 'First Year', '6'),
(65, 'Second Semester', 'Crim 2', 'Theories of Crime Causation', '56', '3', '3', 0, '', 'Crim 1', 'First Year', '6'),
(66, 'Second Semester', 'DEFTAC 2', 'Arnis and Disarming Technique', '56', '3', '3', 0, '', 'Deftac 1', 'First Year', '6'),
(67, 'Second Semester', 'NSTP 2', 'ROTC', '56', '3', '3', 0, '', '', 'First Year', '6'),
(68, 'First Semester', 'LEA 2', 'Comparative Models Policing', '56', '3', '3', 0, '', 'Crim 1', 'Second Year', '6'),
(69, 'Summer', 'CP', 'OJT/SIL', '200', '4', '4', 0, '', '', 'First Year', '6'),
(70, 'First Semester', 'Forensic 1', 'Forensic Photography', '208', '3', '2', 1, 'Photography NC II', 'Crim 1, CDI 1', 'Second Year', '6'),
(71, 'First Semester', 'CLJ 3', 'Criminal Law (Book) 1', '56', '3', '3', 0, '', 'Crim 1, CLJ 1', 'Second Year', '6'),
(72, 'First Semester', 'Crim 3', 'Human Behavior and Victimology', '56', '3', '3', 0, '', 'Crim 1', 'Second Year', '6'),
(73, 'First Semester', 'CLJ 2', 'Human Rights Education', '56', '3', '3', 0, '', 'Crim 1, CLJ 1', 'Second Year', '6'),
(74, 'First Semester', 'CDI 1', 'Fundamentals of Investigation and Intelligence', '56', '3', '3', 0, '', 'Crim 1, LEA 1', 'Second Year', '6'),
(75, 'First Semester', 'AdGE', 'General Chemistry (Organic)', '56', '3', '2', 1, '', '', 'Second Year', '6'),
(76, 'First Semester', 'DEFTAC 3', 'First Aid and Water Safety', '776', '2', '2', 0, 'NC II', '', 'Second Year', '6'),
(77, 'Second Semester', 'Forensic 2 ', 'Personal Identification Techniques', '56', '3', '2', 1, '', 'Crim 1, CDI 1', 'Second Year', '6'),
(78, 'Second Semester', 'Forensic 3', 'Forensic Chemistry and Toxicology', '356', '5', '3', 2, 'Process Operations NC II', 'Crim 1, Forensic 1', 'Second Year', '6'),
(79, 'Second Semester', 'CDI 2', 'Specialized Crime Investigation 1 w/ legal Medicine', '56', '3', '3', 0, '', 'Crim 1, CDI 1', 'Second Year', '6'),
(80, 'Second Semester', 'CLJ 4', 'Criminal Law (Book2)', '56', '3', '3', 0, '', 'Crim 1, CDI 1', 'Second Year', '6'),
(81, 'Second Semester', 'CDI 3', 'Specialized Crime Investigation 2 w/ Simulation on Interrogation and Interview', '56', '3', '3', 0, '', 'Crim 1, CDI 1', 'Second Year', '6'),
(82, 'Second Semester', 'Crim 4', 'Professional Conduct and Ethical Standards ', '56', '3', '3', 0, '', 'Crim 1, CLJ 1', 'Second Year', '6'),
(83, 'Second Semester', 'DEFTAC 4', 'markmanship and Combat Shooting', '162', '2', '2', 0, 'Security Services NC I', '', 'Second Year', '6'),
(84, 'Summer', 'OJT', 'OJT', '240', '4', '4', 0, '', '', 'Second Year', '6'),
(85, 'First Semester', 'Crim 5', 'Juvenile delinquency and Juvenile Justice System', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(86, 'First Semester', 'LEA 3', 'Introduction to Industrial Security Concepts', '221', '3', '3', 0, 'Security Services NC II', '', 'Third Year', '6'),
(87, 'First Semester', 'CDI 4', 'Traffic management and Accident Investigation w/ Driving', '136', '3', '3', 0, 'Driving NC II', '', 'Third Year', '6'),
(88, 'First Semester', 'CLJ 5', 'Evidence', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(89, 'First Semester', 'CDI 5', 'Technical English 1 ( Technical Report Writing & Presentation)', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(90, 'First Semester', 'CDI 6', 'Fire Protection & Arson Investigation', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(91, 'First Semester', 'EC 1 ', 'Elective', '56', '3', '3', 0, '', '', 'Second Year', '6'),
(92, 'Second Semester', 'LEA 4', 'Law Enforcement Operation and Planning w/ Crime Mapping', '508', '3', '3', 0, 'Automotive Servicing NC II', 'Crim 1, LEA', 'Third Year', '6'),
(93, 'Second Semester', 'Crim 6', 'Dispute Resolution and Crises/Incidents management', '736', '3', '3', 0, 'Emergency Medical Services NC III', 'CDI 1', 'Third Year', '6'),
(94, 'Second Semester', 'PC 1', 'Life and Works of Rizal', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(95, 'Second Semester', 'EC 2', 'Elective', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(96, 'Second Semester', 'EC 3', 'Elective', '56', '3', '3', 0, '', '', 'Third Year', '6'),
(97, 'Second Semester', 'SIL', 'SIL', '200', '6', '0', 6, '', '', 'Third Year', '6'),
(98, 'First Semester', 'GE 4', 'Mathematics in the Modern World', '56', '3', '3', 0, '', '', 'First Year', '5'),
(99, 'First Semester', 'GE 5', 'Purposive Communication', '56', '3', '3', 0, 'Contact Center Services NC II', '', 'First Year', '5'),
(100, 'First Semester', 'Path Fit', 'Movement Competency Training', '56', '2', '2', 0, '', '', 'First Year', '5'),
(101, 'First Semester', 'NSTP 1', 'Civic Welfare Training Service 1', '56', '3', '3', 0, '', '', 'First Year', '5'),
(102, 'Second Semester', 'CC 102', 'Computer Programming ! (Fundamentals Of Programming)', '56/80', '3', '2', 3, '', 'CC 101', 'First Year', '5'),
(103, 'Second Semester', 'IS 101', 'Fundamentals of Information Systems', '56/200', '3', '3', 0, '', 'CC 101', 'First Year', '5'),
(104, 'Second Semester', 'CSS 2', 'Set-up Computer Services/Maintain Computer Systems and Network', '56/80', '3', '3', 0, 'Computer Systems Services NC II', 'CSS 1', 'First Year', '5'),
(105, 'Second Semester', 'GE 6', 'Art Appreciation', '56/96', '3', '3', 0, 'Photography NC II', '', 'First Year', '5'),
(106, 'Second Semester', 'GE 7', 'Science Technology & Society', '56', '3', '3', 0, '', '', 'First Year', '5'),
(107, 'Second Semester', 'GE 8', 'Ethics', '56', '3', '3', 0, '', '', 'First Year', '5'),
(108, 'Second Semester', 'GE 9', 'Intoduksyon sa Pag-aaral ng Wika', '56', '3', '3', 0, '', '', 'First Year', '5'),
(109, 'Second Semester', 'Path Fit 2', 'Exercise-based Fitness', '56', '2', '2', 0, '', 'PE 1', 'First Year', '5'),
(110, 'Summer', 'OJT 1', 'On the Job Training', '0', '3', '0', 4, '', '', 'First Year', '5'),
(111, 'First Semester', 'CC 103', 'Computer Programming 2 ( Intermediate Programming)', '56/80', '3', '2', 33, '', 'CC 102', 'Second Year', '5'),
(112, 'First Semester', 'PF 101', 'Object Oriented Programming', '56', '3', '3', 1, 'Animation NC II', 'CC 102', 'Second Year', '5'),
(113, 'First Semester', 'CC 104', 'Data Structures and Algorithms', '56/48', '3', '3', 1, '', 'CC 102', 'Second Year', '5'),
(114, 'First Semester', 'IS 103', 'IT Insfrastructure and Network Technologies', '56', '3', '2', 1, '', 'CC 101', 'Second Year', '5'),
(115, 'First Semester', 'IS 102', 'Proffessional Issues in Information Systems', '56/200', '3', '2', 1, '', 'CC 101', 'Second Year', '5'),
(116, 'First Semester', 'IS 104', 'System Analysis Design and Development', '56', '3', '3', 0, '', '', 'Second Year', '5'),
(117, 'First Semester', 'GE 10', 'Panitikan ng Pilipinas', '56', '3', '1', 0, '', '', 'Second Year', '5'),
(118, 'First Semester', 'GE 11', 'Pagpapahalaga sa Kulturang Pilipino', '56', '3', '3', 0, '', '', 'Second Year', '5'),
(119, 'First Semester', 'Path Fit 3', 'Dance and Sport', '56', '2', '2', 0, '', 'PE 2', 'Second Year', '5'),
(120, 'Second Semester', 'IM 101', 'Fundamentals of Database Systems', '56', '3', '2', 3, '', 'PF 101', 'Second Year', '5'),
(121, 'Second Semester', 'CC 105', 'Information Mangement', '56/160', '3', '2', 3, '', 'CC 104', 'Second Year', '5'),
(122, 'Second Semester', 'IAS 101', 'Information Assurance & Securiy', '56/58', '3', '2', 3, '', 'CC 104', 'Second Year', '5'),
(123, 'Second Semester', 'DM 101', 'Organization and Management Concepts', '56/64', '3', '2', 3, 'Events Management Services NC II', 'CC 101', 'Second Year', '5'),
(124, 'Second Semester', 'HCI 101', 'Human Computer Interaction', '56/202', '3', '2', 3, 'Illustration NC II', 'CC 101', 'Second Year', '5'),
(125, 'Second Semester', 'QUAMET', 'Quantitative Methods', '56', '3', '2', 3, '', 'MS 101', 'Second Year', '5'),
(126, 'Second Semester', 'MATH 3A ', 'Statistic', '56', '3', '3', 0, '', 'Math 2A', 'Second Year', '5'),
(127, 'Second Semester', 'IS 105', 'Enterprise Architecture', '56', '3', '2', 3, '', 'IS 103', 'Second Year', '5'),
(128, 'Second Semester', 'Path Fit 4', 'Outdoor and Adventure Activities', '56', '2', '2', 0, '', '', 'Second Year', '5'),
(129, 'Summer', 'OJT 2', 'On the Job Training', '0', '4', '0', 4, '', 'Regular Second Year', 'Second Year', '5'),
(130, 'First Semester', 'IS Elec 2', 'IS Project Management 2', '56', '3', '2', 3, '', '', 'Third Year', '5'),
(131, 'First Semester', 'DM 102', 'Financial Management', '56/311', '3', '3', 0, 'Micro Finance Technology NC II', 'DM 101', 'Third Year', '5'),
(132, 'First Semester', 'Net 102', 'Networking', '56/80', '3', '2', 3, '', '', 'Third Year', '5'),
(133, 'First Semester', 'IS 106', 'IS PRoject Mangement 1', '56/42', '3', '2', 3, '', 'CC 104', 'Third Year', '5'),
(134, 'First Semester', 'IPT 101', 'Integrative Programming and Technologies', '56', '3', '2', 3, '', '', 'Third Year', '5'),
(135, 'First Semester', 'IS Elec 1', 'Computer Relationship Management', '56/106', '3', '2', 3, '', '', 'Third Year', '5'),
(136, 'First Semester', 'CAP 101', 'Capstone Project 1', '56/56', '3', '3', 0, '', 'CC 106', 'Third Year', '5'),
(137, 'First Semester', 'IS 107', 'IS Stragetegy Management and Acquisiotion', '56', '3', '2', 3, '', 'IAS 101', 'Third Year', '5'),
(138, 'First Semester', 'GE 12', 'Life & Works of Rizal', '56', '3', '3', 0, '', '', 'Third Year', '5'),
(139, 'First Semester', 'Econ', 'Principal of Economics with TAR', '56/124', '3', '3', 0, '', '', 'Third Year', '5'),
(140, 'Second Semester', 'DM 103', 'Business Process Design & Management', '56/120', '3', '2', 1, '', 'DM 102', 'Third Year', '5'),
(141, 'Second Semester', 'IS Elec 3', 'Enterprise Resource Planning', '56', '3', '2', 3, '', '', 'Third Year', '5'),
(142, 'Second Semester', 'CC 106', 'Application Devt & Emerging Technologies', '56/200', '3', '2', 3, 'Development NC ', 'CC 105', 'Third Year', '5'),
(143, 'Second Semester', 'Acctg 1', 'Basic Accounting 1', '56/124', '3', '3', 0, 'NC III', 'Math 1A', 'Third Year', '5'),
(144, 'Second Semester', 'DM 104', 'Evaluation of Business Performance', '56/247', '3', '3', 0, '', 'DM 103', 'Third Year', '5'),
(145, 'First Semester', 'CC 106', 'lezgow', '56', '3', '3', 0, '', '', 'Third Year', '1');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `t_id` int(11) NOT NULL,
  `t_name` text NOT NULL,
  `t_user_name` text NOT NULL,
  `t_password` text NOT NULL,
  `t_gender` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `t_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`t_id`, `t_name`, `t_user_name`, `t_password`, `t_gender`, `status`, `t_image`) VALUES
(2, 'Angel Seclon', 'angel@gmail.com', 'angel231', 'female', 0, 'img_67c1f2ad0b85e3.30140365.jpg'),
(4, 'Bing Bong Abarca', 'bong@gmail.com', 'bong123', 'male', 0, 'img_67c424352b44e9.83923320.jpg'),
(7, 'Genda Necio', 'genda@gmail.com', 'genda123', 'female', 0, 'img_67c531dc4c17f3.71495902.jpg'),
(9, 'Angel Abellanosa', 'rjbrion', '2213', 'male', 1, ''),
(10, 'Joel Miller Go', 'joel123', '2213', 'male', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `course` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `year_level` varchar(100) NOT NULL,
  `semester` varchar(100) NOT NULL,
  `school_year` text NOT NULL,
  `assigned_date` text NOT NULL,
  `schedule_day` varchar(255) NOT NULL,
  `schedule_time_start` time NOT NULL,
  `schedule_time_end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subjects`
--

INSERT INTO `teacher_subjects` (`id`, `teacher_id`, `subject_id`, `course`, `section`, `year_level`, `semester`, `school_year`, `assigned_date`, `schedule_day`, `schedule_time_start`, `schedule_time_end`) VALUES
(35, 2, 3, '7', 'DBOT 2B', 'First Year', 'First Semester', '2024-2025', '2025-03-13 11:43 PM', 'Thursday', '13:30:00', '14:30:00'),
(37, 4, 24, '1', 'DIT 2B', 'First Year', 'First Semester', '2024-2025', '2025-03-13 11:50 PM', 'Monday', '13:30:00', '14:30:00'),
(41, 7, 5, '7', 'DBOT 2D', 'First Year', 'First Semester', '2024-2025', '2025-03-14 12:41 AM', 'Thursday', '13:30:00', '14:30:00'),
(42, 2, 3, '7', 'DBOT 2D', 'First Year', 'First Semester', '2024-2025', '2025-03-14 02:59 AM', 'Tuesday', '13:30:00', '14:30:00'),
(47, 9, 3, '7', '', 'First Year', 'First Semester', '2024-2025', '2025-10-17 08:42 AM', 'Monday', '10:41:00', '12:41:00'),
(50, 10, 24, '1', '', 'First Year', 'First Semester', '2024-2025', '2025-10-17 09:52 AM', 'Monday', '13:00:00', '14:59:00');

-- --------------------------------------------------------

--
-- Table structure for table `web_users`
--

CREATE TABLE `web_users` (
  `web_id` int(11) NOT NULL,
  `email` text NOT NULL,
  `usertype` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_users`
--

INSERT INTO `web_users` (`web_id`, `email`, `usertype`) VALUES
(1, 'gail123@gmail.com', 'a'),
(3, 'angel@gmail.com', 't'),
(5, 'bong@gmail.com', 't'),
(8, 'genda@gmail.com', 't'),
(10, 'rjbrion', 't'),
(11, 'joel123', 't');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_missing_requirements`
--
ALTER TABLE `student_missing_requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_users`
--
ALTER TABLE `student_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`s_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`t_id`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `web_users`
--
ALTER TABLE `web_users`
  ADD PRIMARY KEY (`web_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `a_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `student_missing_requirements`
--
ALTER TABLE `student_missing_requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `student_users`
--
ALTER TABLE `student_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `s_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `t_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `web_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD CONSTRAINT `teacher_subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`t_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`s_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
