-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 05, 2015 at 06:40 PM
-- Server version: 5.5.40
-- PHP Version: 5.5.18-1+deb.sury.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sitetrackv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(32) NOT NULL,
  `type` enum('Site','Client') NOT NULL,
  `company_id` int(11) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`role_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `type`, `company_id`, `status`, `created_date`, `modified_date`) VALUES
(1, 'Administrator', 'Site', 1, 0, '2012-09-08 16:53:46', '2015-01-05 12:58:41'),
(2, 'Poweruser', 'Site', 3, 1, '2012-09-08 16:54:20', '2015-01-05 12:56:46'),
(3, 'Employee', 'Site', 1, 1, '2013-07-24 00:00:00', '2015-01-05 12:58:17'),
(4, 'Super Admin', 'Client', 1, 0, '0000-00-00 00:00:00', '2015-01-05 12:59:31'),
(5, 'Admin', 'Client', 1, 0, '0000-00-00 00:00:00', '2015-01-05 12:58:58');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
