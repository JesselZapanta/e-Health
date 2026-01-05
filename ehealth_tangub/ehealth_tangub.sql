-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 06:25 PM
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
-- Database: `ehealth_tangub`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `qr_code`, `status`, `created_at`) VALUES
(1, 1, 4, '2026-01-13', '13:20:00', NULL, NULL, 'Approved', '2026-01-04 16:16:03'),
(2, 1, 4, '2026-01-05', '10:09:00', NULL, NULL, 'Completed', '2026-01-05 02:09:08'),
(3, 1, 4, '2026-01-05', '14:30:00', NULL, NULL, 'Approved', '2026-01-05 04:15:22'),
(4, 1, 4, '2026-01-05', '14:00:00', NULL, NULL, 'Approved', '2026-01-05 05:46:37'),
(5, 1, 9, '2026-01-10', '10:00:00', NULL, NULL, 'Approved', '2026-01-05 05:55:51'),
(27, 4, 4, '2026-01-05', '08:30:00', NULL, 'A4ED942CZA3W', 'Completed', '2026-01-05 16:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`consultation_id`, `appointment_id`, `doctor_id`, `patient_id`, `symptoms`, `diagnosis`, `prescription`, `notes`, `created_at`) VALUES
(1, 2, 0, 0, 'test', ' test', 'test', 'test', '2026-01-05 17:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--

CREATE TABLE `doctor_availability` (
  `availability_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slots` int(11) NOT NULL,
  `status` enum('available','booked','blocked','expired') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_availability`
--

INSERT INTO `doctor_availability` (`availability_id`, `doctor_id`, `available_date`, `start_time`, `end_time`, `slots`, `status`, `created_at`) VALUES
(1, 4, '2026-01-05', '20:59:00', '22:59:00', 9, 'available', '2026-01-05 12:59:25'),
(2, 4, '2026-01-07', '08:59:00', '13:59:00', 10, 'available', '2026-01-05 12:59:57'),
(3, 4, '2026-01-05', '08:30:00', '12:00:00', 0, 'booked', '2026-01-05 13:38:53'),
(4, 4, '2026-01-06', '12:51:00', '18:51:00', 10, 'available', '2026-01-05 13:51:22'),
(5, 4, '2026-01-06', '10:00:00', '12:00:00', 10, 'available', '2026-01-05 14:04:36'),
(7, 4, '2026-01-05', '06:08:00', '07:08:00', 2, 'available', '2026-01-05 16:08:50');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `minimum_stock` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `description`, `quantity`, `minimum_stock`, `created_at`) VALUES
(1, 'Paracetamol', 'for headache', 0, 20, '2026-01-03 15:51:17'),
(2, 'Mefenamic', 'for pain reliever', 50, 25, '2026-01-03 15:51:49'),
(3, 'Dicycloverin', 'for stomacheache', 15, 20, '2026-01-05 05:44:43'),
(4, 'test', 'test', 1, 1, '2026-01-05 15:55:12'),
(5, 'test 2', 'test', 3, 1, '2026-01-05 16:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `log_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `action` enum('IN','OUT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`log_id`, `inventory_id`, `consultation_id`, `action`, `quantity`, `log_date`) VALUES
(8, 3, NULL, 'OUT', 2, '2026-01-05 16:18:59'),
(9, 3, NULL, 'IN', 1, '2026-01-05 16:22:19'),
(10, 3, NULL, 'OUT', 2, '2026-01-05 16:22:25'),
(11, 3, NULL, 'OUT', 2, '2026-01-05 16:22:29'),
(12, 3, NULL, 'OUT', 2, '2026-01-05 16:22:32'),
(13, 3, NULL, 'OUT', 2, '2026-01-05 16:22:35'),
(14, 3, NULL, 'OUT', 2, '2026-01-05 16:22:39'),
(15, 3, NULL, 'OUT', 4, '2026-01-05 16:43:55'),
(16, 3, NULL, 'OUT', 10, '2026-01-05 16:44:07'),
(17, 1, NULL, 'OUT', 100, '2026-01-05 16:44:25'),
(18, 3, NULL, 'OUT', 10, '2026-01-05 16:44:36');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT 0,
  `birth_date` date DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `medical_history` text DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `date_of_birth`, `gender`, `address`, `contact_number`, `is_pregnant`, `birth_date`, `blood_type`, `medical_history`, `height`, `weight`) VALUES
(1, 2, NULL, NULL, NULL, NULL, 0, NULL, 'O', NULL, '156', '65'),
(2, 6, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL),
(3, 7, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL),
(4, 11, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prenatal_records`
--

CREATE TABLE `prenatal_records` (
  `prenatal_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `fetal_heart_rate` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `next_visit` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prenatal_records`
--

INSERT INTO `prenatal_records` (`prenatal_id`, `patient_id`, `visit_date`, `blood_pressure`, `weight`, `fetal_heart_rate`, `notes`, `next_visit`) VALUES
(1, 1, '2026-01-05', '110/80', 65.00, NULL, 'dako ug tiyan', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports_archive`
--

CREATE TABLE `reports_archive` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `generated_by` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','doctor','patient') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'System Administrator', 'admin@ehealth.com', '$2y$10$x/4JzvdA0rnC/ZxoySYCiuqYk44GNqlEcWMAzcIQpM7fSuZX71aF.', 'admin', 'active', '2026-01-03 15:23:25'),
(2, 'Angel R Rafol', 'angelkazu14@gmail.com', '$2y$10$DGAp3j/VXsJlpbZbhMN2qOpUZTQQsOxb/EokYqmSNZlkdxaOov7QG', 'patient', 'active', '2026-01-03 15:46:39'),
(3, 'Dmay Rosales', 'dmay@gmail.com', '$2y$10$4uxq.LZRkoFGR./ot4/eS.KoDR1.Vi0O4PaH3D99efBIz1Jb4gsF6', 'staff', 'active', '2026-01-03 15:47:27'),
(4, 'Luijei Clavite', 'luijei@gmail.com', '$2y$10$ViJsquCj4LMil81W89u4Wene5gWakSvPmwxsV5dPhMGlcrvRFoF5K', 'doctor', 'active', '2026-01-03 15:50:01'),
(5, 'Kwen  E Celebrado', 'kwen@ehealth.com', '$2y$10$Efzo10EQ1OqdjnubaDMB1uDi9EVt4DgkNX9G.TxldFkPhTZuYlIye', 'staff', 'active', '2026-01-04 18:00:39'),
(6, 'Shane R Rafol', 'shane@ehealth.com', '$2y$10$TBql.xOOHDqtgC.BVGpt3eEkgHrvubvs0qB.5tlrN2U8vWRo1c54q', 'patient', 'active', '2026-01-04 18:21:40'),
(7, 'Pemar C Sabijon', 'pemar@gmail.com', '$2y$10$kUPl9UPMQkkCa53dOxQgBeEe5VTn9rB9u6p6oBY6cxJqemgsCmqHm', 'patient', 'active', '2026-01-05 01:51:08'),
(8, 'Giesel Q Ocariza', 'giesel@ehealth.com', '$2y$10$642rvWWVxSNMbuQ903AVa.LLcnVFGG46OrCqw42EQbdlqoWWq2CC.', 'staff', 'active', '2026-01-05 05:42:30'),
(9, 'Criz A Entera', 'criz@gmail.com', '$2y$10$FGCS8RlYNqn/wnSrdk6JbOAVkLnHmBNrjY3oY6kcJ0vvijjOLxQzq', 'doctor', 'active', '2026-01-05 05:55:04'),
(11, 'patient P patient', 'patient@ehealth.com', '$2y$10$2LjPR8uDhx0N13J/OSPIsOjq8/13jeQIkWg7kusLFERuvASkm.ePe', 'patient', 'active', '2026-01-05 12:48:20'),
(12, 'staff S Staff', 'staff@gmail.com', '$2y$10$ycc8LlSGM5Rqjvl9LuKZq.9dTo8.axD/8Re./TNWsFcWsNcwfg63S', 'staff', 'active', '2026-01-05 12:50:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `consultation_id` (`consultation_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `prenatal_records`
--
ALTER TABLE `prenatal_records`
  ADD PRIMARY KEY (`prenatal_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `reports_archive`
--
ALTER TABLE `reports_archive`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prenatal_records`
--
ALTER TABLE `prenatal_records`
  MODIFY `prenatal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports_archive`
--
ALTER TABLE `reports_archive`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD CONSTRAINT `doctor_availability_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `prenatal_records`
--
ALTER TABLE `prenatal_records`
  ADD CONSTRAINT `prenatal_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE;

--
-- Constraints for table `reports_archive`
--
ALTER TABLE `reports_archive`
  ADD CONSTRAINT `reports_archive_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
