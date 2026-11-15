-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 05, 2026 at 10:16 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `community_connectdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE DATABASE IF NOT EXISTS community_connectdb;
USE community_connectdb;

DROP TABLE IF EXISTS `activities`;
CREATE TABLE IF NOT EXISTS `activities` (
  `activityID` int NOT NULL AUTO_INCREMENT,
  `activity` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`activityID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`activityID`, `activity`) VALUES
(1, 'Community Clean-up'),
(2, 'Sports Day'),
(3, 'Tutoring'),
(4, 'Donation Drive'),
(5, 'Community Beautification'),
(6, 'Senior Citizen Outreach');

-- --------------------------------------------------------

--
-- Table structure for table `registrants`
--

DROP TABLE IF EXISTS `registrants`;
CREATE TABLE IF NOT EXISTS `registrants` (
  `email` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int DEFAULT NULL,
  `phoneNumber` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrants_skills`
--

DROP TABLE IF EXISTS `registrants_skills`;
CREATE TABLE IF NOT EXISTS `registrants_skills` (
  `email` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skill` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`email`,`skill`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE IF NOT EXISTS `registrations` (
  `registrationID` int NOT NULL AUTO_INCREMENT,
  `email` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activityID` int NOT NULL,
  `parish` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `participationDate` date DEFAULT NULL,
  `registrationDate` date NOT NULL,
  PRIMARY KEY (`registrationID`),
  KEY `email` (`email`),
  KEY `activityID` (`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
