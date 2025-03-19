-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 28, 2025 at 09:17 PM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u265056410_sparkup`
--

-- --------------------------------------------------------

--
-- Table structure for table `2025_01_12_00_35_22_matches`
--

CREATE TABLE `2025_01_12_00_35_22_matches` (
  `COL 1` varchar(32) DEFAULT NULL,
  `COL 2` varchar(28) DEFAULT NULL,
  `COL 3` varchar(32) DEFAULT NULL,
  `COL 4` varchar(32) DEFAULT NULL,
  `COL 5` varchar(32) DEFAULT NULL,
  `COL 6` varchar(28) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `2025_01_12_00_35_22_matches`
--

INSERT INTO `2025_01_12_00_35_22_matches` (`COL 1`, `COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`) VALUES
('Email', 'Match 1', 'Match 2', 'Match 3', 'Match 4', 'Match 5'),
('aryapatil8221@gmail.com', 'aakashpatil45321@gmail.com', 'arya.patil241@vit.edu', 'pagox98299@nausard.com', 'Vaibhavvishwakarma1500@gmail.com', 'atharvp105@gmail.com'),
('riya.bajwala@gmail.com', 'Arya.chavan2006@gmail.com', 'arnavshah2203@gmail.com', 'tushartribhuwan7@gmail.com', 'muktamohite2506@gmail.com', 'aarya.sawant150205@gmail.com'),
('pagox98299@nausard.com', 'aryapatil8221@gmail.com', 'aakashpatil45321@gmail.com', 'arya.patil241@vit.edu', 'Vaibhavvishwakarma1500@gmail.com', 'atharvp105@gmail.com'),
('Arya.chavan2006@gmail.com', 'tushartribhuwan7@gmail.com', 'riya.bajwala@gmail.com', 'ghostlyparth26@gmail.com', 'muktamohite2506@gmail.com', 'arnavshah2203@gmail.com'),
('shivamshirsat5@gmail.com', 'atharvp105@gmail.com', 'Vaibhavvishwakarma1500@gmail.com', 'pagox98299@nausard.com', 'ghostlyparth26@gmail.com', 'ritik.arora24@vit.edu'),
('atharvp105@gmail.com', 'ritik.arora24@vit.edu', 'shivamshirsat5@gmail.com', 'Vaibhavvishwakarma1500@gmail.com', 'pagox98299@nausard.com', 'ghostlyparth26@gmail.com'),
('Daksh.bais34@gmail.com', 'tushartribhuwan7@gmail.com', 'shivamshirsat5@gmail.com', 'riya.bajwala@gmail.com', 'Arya.chavan2006@gmail.com', 'muktamohite2506@gmail.com'),
('k.harikavarma2006@gmail.com', 'ritik.arora24@vit.edu', 'tps08111@tpsmumbai.in', 'abhijeetbhr8@gmail.com', 'gulunjkarrashi@gmail.com', 'aarya.sawant150205@gmail.com'),
('muktamohite2506@gmail.com', 'aarya.sawant150205@gmail.com', 'ayushbhat812@gmail.com', 'tushartribhuwan7@gmail.com', 'arnavshah2203@gmail.com', 'Arya.chavan2006@gmail.com'),
('sakksheegandhi17@gmail.com', 'bapatameya9@gmail.com', 'arindambose5678@gmail.com', 'gulunjkarrashi@gmail.com', 'ayushbhat812@gmail.com', 'hansikajarande@gmail.com'),
('abhijeetbhr8@gmail.com', 'tps08111@tpsmumbai.in', 'jskunal.01@gmail.com', 'aarya.sawant150205@gmail.com', 'k.harikavarma2006@gmail.com', 'arnavshah2203@gmail.com'),
('bapatameya9@gmail.com', 'sakksheegandhi17@gmail.com', 'gulunjkarrashi@gmail.com', 'arindambose5678@gmail.com', 'ayushbhat812@gmail.com', 'ritik.arora24@vit.edu'),
('hansikajarande@gmail.com', 'sakksheegandhi17@gmail.com', 'ritik.arora24@vit.edu', 'ayushbhat812@gmail.com', 'gulunjkarrashi@gmail.com', 'bapatameya9@gmail.com'),
('meethpalresha2109@gmail.com', 'sheetalbhat927@gmail.com', 'ankushdigra1419@gmail.com', 'ritik.arora24@vit.edu', 'Vaibhavvishwakarma1500@gmail.com', 'arnavshah2203@gmail.com'),
('tushartribhuwan7@gmail.com', 'Arya.chavan2006@gmail.com', 'ghostlyparth26@gmail.com', 'muktamohite2506@gmail.com', 'riya.bajwala@gmail.com', 'aarya.sawant150205@gmail.com'),
('jskunal.01@gmail.com', 'ritik.arora24@vit.edu', 'arnavshah2203@gmail.com', 'abhijeetbhr8@gmail.com', 'ankushdigra1419@gmail.com', 'sheetalbhat927@gmail.com'),
('gulunjkarrashi@gmail.com', 'ayushbhat812@gmail.com', 'aarya.sawant150205@gmail.com', 'sakksheegandhi17@gmail.com', 'bapatameya9@gmail.com', 'ritik.arora24@vit.edu'),
('ghostlyparth26@gmail.com', 'arnavshah2203@gmail.com', 'tushartribhuwan7@gmail.com', 'Arya.chavan2006@gmail.com', 'aarya.sawant150205@gmail.com', 'ayushbhat812@gmail.com'),
('arnavshah2203@gmail.com', 'sheetalbhat927@gmail.com', 'ankushdigra1419@gmail.com', 'jskunal.01@gmail.com', 'ghostlyparth26@gmail.com', 'riya.bajwala@gmail.com'),
('aakashpatil45321@gmail.com', 'arya.patil241@vit.edu', 'aryapatil8221@gmail.com', 'pagox98299@nausard.com', 'Vaibhavvishwakarma1500@gmail.com', 'atharvp105@gmail.com'),
('aarya.sawant150205@gmail.com', 'muktamohite2506@gmail.com', 'ayushbhat812@gmail.com', 'arnavshah2203@gmail.com', 'gulunjkarrashi@gmail.com', 'abhijeetbhr8@gmail.com'),
('Vaibhavvishwakarma1500@gmail.com', 'pagox98299@nausard.com', 'ritik.arora24@vit.edu', 'atharvp105@gmail.com', 'sheetalbhat927@gmail.com', 'ayushbhat812@gmail.com'),
('ayushbhat812@gmail.com', 'aarya.sawant150205@gmail.com', 'gulunjkarrashi@gmail.com', 'muktamohite2506@gmail.com', 'ritik.arora24@vit.edu', 'arnavshah2203@gmail.com'),
('sheetalbhat927@gmail.com', 'ankushdigra1419@gmail.com', 'arnavshah2203@gmail.com', 'ritik.arora24@vit.edu', 'meethpalresha2109@gmail.com', 'ayushbhat812@gmail.com'),
('arindambose5678@gmail.com', 'sakksheegandhi17@gmail.com', 'bapatameya9@gmail.com', 'gulunjkarrashi@gmail.com', 'abhijeetbhr8@gmail.com', 'ayushbhat812@gmail.com'),
('ankushdigra1419@gmail.com', 'sheetalbhat927@gmail.com', 'arnavshah2203@gmail.com', 'jskunal.01@gmail.com', 'ritik.arora24@vit.edu', 'meethpalresha2109@gmail.com'),
('tps08111@tpsmumbai.in', 'abhijeetbhr8@gmail.com', 'aarya.sawant150205@gmail.com', 'k.harikavarma2006@gmail.com', 'gulunjkarrashi@gmail.com', 'muktamohite2506@gmail.com'),
('ritik.arora24@vit.edu', 'arya.chavan24@vit.edu', 'atharvp105@gmail.com', 'sheetalbhat927@gmail.com', 'Vaibhavvishwakarma1500@gmail.com', 'ankushdigra1419@gmail.com'),
('arya.patil241@vit.edu', 'aakashpatil45321@gmail.com', 'aryapatil8221@gmail.com', 'pagox98299@nausard.com', 'Vaibhavvishwakarma1500@gmail.com', 'atharvp105@gmail.com'),
('arya.chavan24@vit.edu', 'ritik.arora24@vit.edu', 'riya.bajwala@gmail.com', 'ajinkya.nirmale24@vit.edu', 'ghostlyparth26@gmail.com', 'arya.patil24@vit.edu');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
