-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 08:40 AM
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
-- Database: `skillup_academy`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `u_id` int(11) NOT NULL,
  `level` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`u_id`, `level`) VALUES
(4, 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_notices`
--

CREATE TABLE `admin_notices` (
  `n_id` mediumint(9) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` varchar(500) NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `u_id` int(11) NOT NULL,
  `audience` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notices`
--

INSERT INTO `admin_notices` (`n_id`, `title`, `message`, `date`, `u_id`, `audience`) VALUES
(1, 'System Maintenance', 'Scheduled downtime on July 25th.', '2025-07-01', 4, 'everyone'),
(2, 'New Courses Added', 'Check out the new courses available!', '2025-07-15', 4, 'student'),
(3, 'New Instructor Joined', 'Welcome Frank Miller to the team!', '2025-07-18', 4, 'everyone'),
(4, 'Holiday Announcement', 'Academy will be closed on August 15th.', '2025-07-19', 4, 'everyone'),
(5, 'New Course Launch', 'Data Analysis with Python course now available.', '2025-04-01', 4, 'everyone'),
(6, 'System Upgrade', 'Maintenance scheduled on July 30th.', '2025-07-01', 4, 'everyone');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `c_id` smallint(6) NOT NULL,
  `title` varchar(100) NOT NULL,
  `amount` smallint(6) NOT NULL,
  `description` varchar(500) NOT NULL,
  `domain` varchar(50) NOT NULL,
  `duration` smallint(6) NOT NULL,
  `u_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `status` varchar(7) NOT NULL,
  `url` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`c_id`, `title`, `amount`, `description`, `domain`, `duration`, `u_id`, `start_date`, `status`, `url`) VALUES
(1, 'Full Stack Web Development', 1500, 'Learn front-end and back-end development.', 'web-development', 12, 1, '2024-09-01', 'offered', 'course-details/full-stack-web-dev.php'),
(2, 'Data Science Bootcamp', 2000, 'Master data analysis and visualization.', 'data-science', 16, 2, '2024-10-01', 'offered', NULL),
(3, 'Android App Development', 1800, 'Build native Android applications.', 'android-development', 14, 6, '2024-11-01', 'offered', NULL),
(4, 'Introduction to Python', 1000, 'Basics of Python programming.', 'programming', 8, 1, '2024-09-15', 'offered', NULL),
(5, 'Introduction to Cybersecurity', 2200, 'Learn the basics of cybersecurity and protection techniques.', 'cybersecurity', 10, 7, '2024-09-20', 'offered', NULL),
(6, 'React for Beginners', 1600, 'Comprehensive course on ReactJS framework.', 'web-development', 8, 8, '2024-10-10', 'offered', NULL),
(7, 'Building Scalable APIs', 1800, 'Learn to build robust APIs using Node.js.', 'web-development', 12, 9, '2024-11-05', 'offered', NULL),
(8, 'iOS Development Fundamentals', 2100, 'Introduction to app development on iOS.', 'ios-development', 14, 10, '2024-12-01', 'offered', NULL),
(9, 'AI and Machine Learning Basics', 2500, 'Explore AI concepts and ML algorithms.', 'data-science', 15, 11, '2025-01-10', 'offered', NULL),
(10, 'UX/UI Design Principles', 1400, 'Learn key UX/UI design skills and tools.', 'design', 9, 12, '2024-11-15', 'offered', NULL),
(11, 'Data Analysis with Python', 2300, 'Learn how to analyze data using Python libraries.', 'data-science', 12, 13, '2025-04-01', 'offered', NULL),
(12, 'Laravel Framework Mastery', 2500, 'Master Laravel for robust web applications.', 'web-development', 14, 14, '2025-05-15', 'offered', NULL),
(13, 'Android App Development', 2100, 'Build real Android apps from scratch.', 'android-development', 10, 15, '2025-06-01', 'offered', NULL),
(14, 'Graphic Design Basics', 1800, 'Start your journey in creative graphic design.', 'design', 8, 16, '2025-04-20', 'offered', NULL),
(15, 'AWS Cloud Practitioner', 2700, 'Introduction to AWS cloud services and architecture.', 'cloud-computing', 12, 17, '2025-05-05', 'offered', NULL),
(16, 'Ethical Hacking 101', 2600, 'Basics of penetration testing and cybersecurity.', 'cybersecurity', 15, 18, '2025-06-15', 'offered', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

CREATE TABLE `credentials` (
  `u_id` int(11) NOT NULL,
  `pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `credentials`
--

INSERT INTO `credentials` (`u_id`, `pass`) VALUES
(1, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(2, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(3, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(4, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(5, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(6, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(7, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(8, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(9, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(10, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(11, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(12, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(13, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(14, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(15, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(16, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(17, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C'),
(18, '$2b$12$cbNGS4WAgFCMaWKlb.rbg.BE6M8Sod0oR5JLdKKNgmwJTiAMgx26C');

-- --------------------------------------------------------

--
-- Table structure for table `enrolls`
--

CREATE TABLE `enrolls` (
  `u_id` int(11) NOT NULL,
  `c_id` smallint(6) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolls`
--

INSERT INTO `enrolls` (`u_id`, `c_id`, `rating`) VALUES
(3, 1, 5),
(3, 2, 4),
(3, 3, NULL),
(3, 5, 3),
(3, 6, NULL),
(3, 10, NULL),
(3, 12, NULL),
(3, 15, NULL),
(5, 1, 4),
(5, 3, NULL),
(5, 4, 3),
(5, 6, 4);

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `u_id` int(11) NOT NULL,
  `bio` varchar(500) DEFAULT NULL,
  `domain` varchar(50) NOT NULL,
  `title` varchar(10) NOT NULL,
  `skills` varchar(100) DEFAULT NULL,
  `image` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`u_id`, `bio`, `domain`, `title`, `skills`, `image`) VALUES
(1, 'Expert in web technologies and programming.', 'web-development', 'instructor', 'software dev', 'image-assets/Instructors/Alice-Johnson.webp'),
(2, 'Data Science and Machine Learning specialist.', 'data-science', 'instructor', 'software dev', 'image-assets/Instructors/Bob-Smith.webp'),
(6, 'Mobile development enthusiast.', 'android-development', 'instructor', 'software dev', 'image-assets/Instructors/Eve-Adams.webp'),
(7, 'Cybersecurity expert with 10 years experience.', 'cybersecurity', 'instructor', 'software dev', 'image-assets/Instructors/Frank-Miller.webp'),
(8, 'Front-end developer specializing in React.', 'web-development', 'instructor', 'software dev', 'image-assets/Instructors/Grace-Hopper.webp'),
(9, 'Backend developer passionate about scalable APIs.', 'web-development', 'instructor', 'software dev', 'image-assets/Instructors/Henry-Ford.webp'),
(10, 'Mobile app developer expert in iOS.', 'ios-development', 'instructor', 'software dev', 'image-assets/Instructors/Irene-Adler.webp'),
(11, 'AI researcher and Python guru.', 'data-science', 'instructor', 'software dev', 'image-assets/Instructors/Jack-Ryan.webp'),
(12, 'UX/UI designer focused on user-centered design.', 'design', 'instructor', 'software dev', 'image-assets/Instructors/Kate-Winslet.webp'),
(13, 'Experienced data analyst and Python coder.', 'data-science', 'instructor', 'software dev', 'image-assets/Instructors/Linda-Smith.webp'),
(14, 'Full stack web developer and teacher.', 'web-development', 'instructor', 'software dev', 'image-assets/Instructors/Mike-Jahnson.webp'),
(15, 'Mobile developer specializing in Android apps.', 'android-development', 'instructor', 'software dev', 'image-assets/Instructors/Nancy-Drew.webp'),
(16, 'Creative graphic designer and illustrator.', 'design', 'instructor', 'software dev', 'image-assets/Instructors/Oliver-Twist.webp'),
(17, 'Cloud computing and DevOps specialist.', 'cloud-computing', 'instructor', 'software dev', 'image-assets/Instructors/Paula-Abdul.webp'),
(18, 'Cybersecurity analyst and penetration tester.', 'cybersecurity', 'instructor', 'software dev', 'image-assets/Instructors/Quentin-Tarantino.webp');

-- --------------------------------------------------------

--
-- Table structure for table `instructors_notices`
--

CREATE TABLE `instructors_notices` (
  `n_id` mediumint(9) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` varchar(500) NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `u_id` int(11) NOT NULL,
  `c_id` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors_notices`
--

INSERT INTO `instructors_notices` (`n_id`, `title`, `message`, `date`, `u_id`, `c_id`) VALUES
(1, 'Course Update', 'Please update your syllabus.', '2025-07-05', 1, 1),
(2, 'Meeting Reminder', 'Monthly instructor meeting on July 20.', '2025-07-10', 2, 2),
(3, 'React Course Update', 'Please add hooks section.', '2025-07-18', 8, 6),
(4, 'API Course Material', 'Update the API documentation.', '2025-07-19', 9, 7),
(5, 'Laravel Course Update', 'Add testing and deployment modules.', '2025-05-10', 14, 12),
(6, 'Graphic Design Feedback', 'Student feedback for module 3.', '2025-04-25', 16, 14);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `u_id` int(11) NOT NULL,
  `bio` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`u_id`, `bio`) VALUES
(3, 'Computer science student.'),
(5, 'Aspiring web developer.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `u_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `reg_date` date NOT NULL DEFAULT curdate(),
  `dob` date NOT NULL,
  `gender` varchar(6) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `email`, `contact`, `reg_date`, `dob`, `gender`, `name`) VALUES
(1, 'alice@example.com', '1234567890', '2024-01-10', '1995-05-20', 'Female', 'Alice Johnson'),
(2, 'bob@example.com', '0987654321', '2024-02-15', '1990-07-11', 'Male', 'Bob Smith'),
(3, 'charlie@example.com', '1112223333', '2024-03-05', '1992-12-03', 'Male', 'Charlie Lee'),
(4, 'admin@example.com', '4445556666', '2024-01-01', '1985-06-15', 'Male', 'Admin User'),
(5, 'diana@example.com', '7778889999', '2024-04-20', '1997-08-09', 'Female', 'Diana Prince'),
(6, 'eve@example.com', '2223334444', '2024-05-12', '1993-11-22', 'Female', 'Eve Adams'),
(7, 'frank@example.com', '5556667777', '2024-06-01', '1988-03-12', 'Male', 'Frank Miller'),
(8, 'grace@example.com', '8889990000', '2024-06-15', '1994-07-07', 'Female', 'Grace Hopper'),
(9, 'henry@example.com', '1114447777', '2024-07-01', '1991-02-20', 'Male', 'Henry Ford'),
(10, 'irene@example.com', '2225558888', '2024-07-05', '1993-09-30', 'Female', 'Irene Adler'),
(11, 'jack@example.com', '3336669999', '2024-07-10', '1990-11-11', 'Male', 'Jack Ryan'),
(12, 'kate@example.com', '4447771111', '2024-07-15', '1992-05-05', 'Female', 'Kate Winslet'),
(13, 'linda@example.com', '7778889990', '2025-01-10', '1995-04-22', 'Female', 'Linda Smith'),
(14, 'mike@example.com', '6665554443', '2025-01-15', '1987-10-05', 'Male', 'Mike Johnson'),
(15, 'nancy@example.com', '5554443332', '2025-02-01', '1990-08-18', 'Female', 'Nancy Drew'),
(16, 'oliver@example.com', '4443332221', '2025-02-10', '1993-12-12', 'Male', 'Oliver Twist'),
(17, 'paula@example.com', '3332221110', '2025-03-05', '1992-03-03', 'Female', 'Paula Abdul'),
(18, 'quentin@example.com', '2221110009', '2025-03-20', '1989-07-07', 'Male', 'Quentin Tarantino');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `admin_notices`
--
ALTER TABLE `admin_notices`
  ADD PRIMARY KEY (`n_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`c_id`),
  ADD KEY `u_id` (`u_id`);

--
-- Indexes for table `credentials`
--
ALTER TABLE `credentials`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `enrolls`
--
ALTER TABLE `enrolls`
  ADD PRIMARY KEY (`u_id`,`c_id`),
  ADD KEY `c_id` (`c_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `image` (`image`);

--
-- Indexes for table `instructors_notices`
--
ALTER TABLE `instructors_notices`
  ADD PRIMARY KEY (`n_id`),
  ADD KEY `u_id` (`u_id`),
  ADD KEY `c_id` (`c_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`u_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact` (`contact`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notices`
--
ALTER TABLE `admin_notices`
  MODIFY `n_id` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `c_id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `instructors_notices`
--
ALTER TABLE `instructors_notices`
  MODIFY `n_id` mediumint(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_notices`
--
ALTER TABLE `admin_notices`
  ADD CONSTRAINT `admin_notices_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `admins` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `instructors` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `credentials`
--
ALTER TABLE `credentials`
  ADD CONSTRAINT `credentials_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrolls`
--
ALTER TABLE `enrolls`
  ADD CONSTRAINT `enrolls_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `students` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrolls_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `courses` (`c_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructors_notices`
--
ALTER TABLE `instructors_notices`
  ADD CONSTRAINT `instructors_notices_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `instructors` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `instructors_notices_ibfk_2` FOREIGN KEY (`c_id`) REFERENCES `courses` (`c_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
