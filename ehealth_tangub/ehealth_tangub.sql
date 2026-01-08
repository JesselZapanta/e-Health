-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 03:14 PM
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
  `appointment_time` varchar(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `qr_code` int(11) NOT NULL DEFAULT 100000,
  `status` enum('Pending','Approved','Completed','Check-in','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `type`, `reason`, `qr_code`, `status`, `created_at`) VALUES
(37, 10, 19, '2026-01-07', 'morning', 'general', NULL, 100000, 'Completed', '2026-01-07 13:35:48'),
(38, 4, 19, '2026-01-07', 'morning', 'prenatal', NULL, 100001, 'Completed', '2026-01-07 13:37:32');

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
(10, 37, 0, 0, 'Key Points:', 'Key Points:', 'Key Points:', 'Key Points:', '2026-01-07 13:48:22'),
(11, 38, 0, 0, 'dako kaayu ni siya og tiyan sakit iyang likod kay bug at', 'sakit ang likod', 'okay rana kaysa walay likod', 'mawala rana ang sakit inig anak', '2026-01-08 02:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--

CREATE TABLE `doctor_availability` (
  `availability_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `time` varchar(20) NOT NULL,
  `slots` int(11) NOT NULL,
  `status` enum('available','booked','blocked','expired') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_availability`
--

INSERT INTO `doctor_availability` (`availability_id`, `doctor_id`, `available_date`, `start_time`, `end_time`, `time`, `slots`, `status`, `created_at`) VALUES
(8, 19, '2026-01-07', NULL, NULL, 'morning', 3, 'available', '2026-01-07 01:14:00'),
(9, 19, '2026-01-07', NULL, NULL, 'afternoon', 3, 'available', '2026-01-07 01:18:20'),
(10, 19, '2026-01-08', NULL, NULL, 'morning', 9, 'available', '2026-01-07 01:19:41'),
(11, 19, '2026-01-08', NULL, NULL, 'afternoon', 9, 'available', '2026-01-07 01:19:48');

-- --------------------------------------------------------

--
-- Table structure for table `informations`
--

CREATE TABLE `informations` (
  `information_id` int(11) NOT NULL,
  `patients_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `blood_pressure` varchar(10) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `service` enum('dental','checkup','FP','laboratory') DEFAULT NULL,
  `complaints` text DEFAULT NULL,
  `lmp` date DEFAULT NULL,
  `edc` date DEFAULT NULL,
  `gestational_age` varchar(20) DEFAULT NULL,
  `bleeding` enum('oo','dili') DEFAULT NULL,
  `urinary_infection` enum('oo','dili') DEFAULT NULL,
  `discharge` text DEFAULT NULL,
  `abnormal_abdomen` enum('oo','dili') DEFAULT NULL,
  `malpresentation` enum('oo','dili') DEFAULT NULL,
  `absent_fetal_heartbeat` enum('oo','dili') DEFAULT NULL,
  `genital_infection` enum('oo','dili') DEFAULT NULL,
  `fundal_height` decimal(5,2) DEFAULT NULL,
  `fetal_movement_count` varchar(50) DEFAULT NULL,
  `weight_gain` decimal(5,2) DEFAULT NULL,
  `edema` enum('oo','dili') DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `hemoglobin_level` decimal(4,1) DEFAULT NULL,
  `urine_protein` varchar(20) DEFAULT NULL,
  `blood_sugar` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `informations`
--

INSERT INTO `informations` (`information_id`, `patients_id`, `appointment_id`, `blood_pressure`, `temperature`, `heart_rate`, `respiratory_rate`, `weight`, `height`, `oxygen_saturation`, `service`, `complaints`, `lmp`, `edc`, `gestational_age`, `bleeding`, `urinary_infection`, `discharge`, `abnormal_abdomen`, `malpresentation`, `absent_fetal_heartbeat`, `genital_infection`, `fundal_height`, `fetal_movement_count`, `weight_gain`, `edema`, `blood_type`, `hemoglobin_level`, `urine_protein`, `blood_sugar`, `created_at`, `updated_at`) VALUES
(14, 4, 38, '32', 32.0, 32, 32, 32.00, 165.00, 32, 'checkup', ' daSS s ASA s asaS as ', '2025-12-30', '2026-01-28', '30', 'oo', 'oo', 'dsa', 'oo', 'oo', 'oo', 'dili', 213.00, '12', 21.00, 'oo', 'A+', 23.0, 'dsads', NULL, '2026-01-07 13:38:32', '2026-01-07 13:38:32'),
(15, 10, 37, '50/100', 46.0, 47, 74, 74.00, 744.00, 74, 'checkup', 'fdsf sfd sfds fds fd', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-07 13:38:55', '2026-01-07 13:38:55');

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
  `maximum_stock` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_name`, `description`, `quantity`, `minimum_stock`, `maximum_stock`, `created_at`) VALUES
(1, 'Paracetamol', 'for headache', 0, 20, 100, '2026-01-03 15:51:17'),
(2, 'Mefenamic', 'for pain reliever', 10, 25, 100, '2026-01-03 15:51:49'),
(3, 'Dicycloverin', 'for stomacheache', 64, 20, 100, '2026-01-05 05:44:43'),
(12, 'Salbutamol', 'Tambal sa Asthma', 85, 10, 100, '2026-01-08 05:24:19');

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
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `log_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`log_id`, `inventory_id`, `consultation_id`, `action`, `quantity`, `name`, `address`, `log_date`) VALUES
(20, 12, NULL, 'OUT', 5, 'Manok ni Recmar', 'Tubod, Lanao', '2026-01-08 05:31:12'),
(21, 12, NULL, 'OUT', 20, 'Kazuyo Rafol', 'tangub City', '2026-01-08 05:34:49'),
(22, 12, NULL, 'IN', 10, NULL, NULL, '2026-01-08 05:35:07'),
(23, 3, NULL, 'IN', 50, NULL, NULL, '2026-01-08 05:37:05'),
(24, 2, NULL, 'OUT', 30, 'San Pedro', 'Tangub City', '2026-01-08 05:37:28'),
(25, 2, NULL, 'OUT', 10, 'Pemar Sabijon', 'Tubod, Lanao', '2026-01-08 05:38:02');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
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

INSERT INTO `patients` (`patient_id`, `user_id`, `gender`, `address`, `contact_number`, `is_pregnant`, `birth_date`, `blood_type`, `medical_history`, `height`, `weight`) VALUES
(1, 2, 'male', NULL, NULL, 0, NULL, 'O', NULL, '156', '65'),
(2, 6, 'female', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL),
(3, 7, 'male', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL),
(4, 11, 'female', 'Tabid Ozamiz City', '09452212501', 1, '2015-02-10', 'A+', NULL, '165', '65'),
(5, 13, 'male', 'tabid', '093422322', 0, NULL, 'A+', NULL, NULL, NULL),
(6, 14, 'male', 'afdsfdsfdsfdsfdsfds', '42422323232', 0, '2026-01-07', 'A+', NULL, NULL, NULL),
(7, 15, 'male', 'ddddddddddddddddd', '765432345678', 0, '2026-01-01', 'A', NULL, '165', '65'),
(8, 16, 'male', 'afdsfdsfdsfdsfdsfds', '42422323232', 0, '2026-01-07', 'A+', NULL, '432', '32'),
(9, 17, 'female', 'afdsfdsfdsfdsfdsfds', '765432345678', 1, '2026-01-07', 'A+', NULL, '165', '65'),
(10, 18, 'male', 'Tabid Ozamiz City', '09452212501', 0, '2002-10-09', 'A+', NULL, '165', '65');

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
(11, 'Buros Na Patient', 'patient@ehealth.com', '$2y$10$2LjPR8uDhx0N13J/OSPIsOjq8/13jeQIkWg7kusLFERuvASkm.ePe', 'patient', 'active', '2026-01-05 12:48:20'),
(12, 'staff S Staff', 'staff@gmail.com', '$2y$10$ycc8LlSGM5Rqjvl9LuKZq.9dTo8.axD/8Re./TNWsFcWsNcwfg63S', 'staff', 'active', '2026-01-05 12:50:52'),
(13, 'text text test', 'test@ehealth.com', '$2y$10$Lbpzs65XqMOovjoSBgR5Juz3JSkt0iMUYWvVCqUkGhDp8uIPf5wXi', 'patient', 'active', '2026-01-07 00:30:23'),
(14, 'dfdsfds fdsfds fdsfds', 'fdsfdsfdsfdsfdsfds@ehealth.com', '$2y$10$gtFdEvJqk2plw7bZhcdeQO92WVgCvYYLNLahdZhon.gfm.3Bs4HQS', 'patient', 'active', '2026-01-07 00:33:27'),
(15, 'ddddddddddddddddd ddddddddddddddddd ddddddddddddddddd', 'ddddddddddddddddd@ehealth.com', '$2y$10$00qeod194ncLBHB.oXwAdulmnAGxaRoD5EFZwqyLLNEviPFwqeyha', 'patient', 'active', '2026-01-07 00:36:17'),
(16, 'dsa dsada dsadsadsadsadsa', 'dsadsadsadsadsa@ehealth.com', '$2y$10$s1HhXoMdBo9Fev6q17YCqO9L3nGBmhqY0s4kbOJRssfzRZlIPksIq', 'patient', 'active', '2026-01-07 00:45:24'),
(17, 'dsadsadsadsad dsadsadsadsad dsadsadsadsad', 'dsadsadssasaadsad@ehealth.com', '$2y$10$1qLNAAbRhm9n0ncqYsdhzuHYloKCfGDtUQcMmB/GCzIlLSFVq0CjW', 'patient', 'active', '2026-01-07 00:46:13'),
(18, 'Jessel Lomongo Zapanta', 'jeszapanta@ehealth.com', '$2y$10$d5fWyVNgMckfBE0x4NlTHuk9aFNs1tGboQ6h9KadqRTBukjQkB90i', 'patient', 'active', '2026-01-07 00:49:07'),
(19, 'Doctor D WakWak', 'wakwak@ehealth.com', '$2y$10$abLbWo5e4/07SfUDa.aQCOUWI0Nn7uLmGJuTu2ZwvRANr7O7UENVS', 'doctor', 'active', '2026-01-07 00:55:48'),
(20, 'ehealth E Staff', 'ehealthstaff@ehealth.com', '$2y$10$dQpKQuHBgfyP.yBCw2FBautK/FRvxNOHXmXRdUJwymIHdb03M.tZa', 'staff', 'active', '2026-01-07 00:59:02'),
(21, 'aaaaaaaaaaaaaaa aaaaaaaaaaaaaaa aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa@ehealth.com', '$2y$10$bOSgSHX3/vl7ypmibTxf0.uCz6Cz68z7KKoQ.lTD8MGClQiBUkpGa', 'admin', 'active', '2026-01-07 15:03:27');

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
-- Indexes for table `informations`
--
ALTER TABLE `informations`
  ADD PRIMARY KEY (`information_id`),
  ADD KEY `fk_informations_appointments` (`appointment_id`);

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
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `informations`
--
ALTER TABLE `informations`
  MODIFY `information_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
-- Constraints for table `informations`
--
ALTER TABLE `informations`
  ADD CONSTRAINT `fk_informations_appointments` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE;

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
