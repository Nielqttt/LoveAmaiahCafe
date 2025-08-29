-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2025 at 12:21 PM
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
-- Database: `amaihatest`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(11) NOT NULL,
  `CustomerFN` varchar(50) NOT NULL,
  `CustomerLN` varchar(50) NOT NULL,
  `C_Username` varchar(50) NOT NULL,
  `C_Password` varchar(255) NOT NULL,
  `C_PhoneNumber` varchar(20) NOT NULL,
  `C_Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `CustomerFN`, `CustomerLN`, `C_Username`, `C_Password`, `C_PhoneNumber`, `C_Email`) VALUES
(1, 'Bijou', 'Biboo', 'Bejoo', '$2y$10$NCy4MhOuUjkDu7IZAwclJORRyzu520xFwMx9K9WbxHzb0WwCYB3q6', '09666332114', 'biboo@gmail.com'),
(2, 'Fuwawa', 'Mococo', 'FuwaMoco', '$2y$10$XJC3rDaQWPpPiN0B0ipRpefjlVo7Vh.AScqpS1gKXGNM1bs.dHhhq', '09431213532', 'fuwamoco@gmai.com'),
(3, 'Test', 'test', 'test', '$2y$10$Faw/MP8pp/pG3z/8A0PaJuyfvDVJOGanVB75sSoDac7eMsUrmMC5S', '09132888433', 'test@mgila.com'),
(4, 'dummy', 'dumyy', 'dummy', '$2y$10$mMjWFOWNqMLENO/CScf0ZOdFamPJCB4brSQQf8YuCDrDjymc7.kk.', '09234234123', 'dummy@gmail.com'),
(5, 'ame', 'ame', 'ame', '$2y$10$pKh0ZjE34K5ktx.YjIgcnOIDpk8MNzC7CkJWg.cDAeqIKoh5HqsH6', '09234234322', 'ame@gmil.com'),
(6, 'Niel', 'Cerezo', 'Niel', '$2y$10$3ZrrxppDUCI2b9QPK9.Lb.G.97vMVcDjxfbfItnz9mxQFgF7FKImS', '09494893869', 'cerezoniel@gmail.com'),
(7, 'Niel', 'Cerezo', 'Nielqt', '$2y$10$uC4H3np9r7QMnoyLPSC/W.63hWYEsC8bPGESyfab9tcP.JPn8LvN6', '09494893869', 'cerezoanielle@gmail.com'),
(8, 'Faby', 'Sugarol', 'faby', '$2y$10$VRVo.yZp/i3s/xlOh0XuBuuH40bN8gLcsFcpkj0K..zVRCFFGX2fq', '09123456789', 'fabysugarol@tite.com'),
(9, 'Vilma', 'Cerezo', 'Vilma', '$2y$10$qEMtyWcn5O/UhJZkB66nke0MEgKuWF.1av20OF8WTqZFAy3zJ5HL.', '09494893869', 'vilmacerezo@gmail.com'),
(10, 'Meishie', 'Lavarez', 'Mei', '$2y$10$lv7lcFdteKoMZBc8QSM71.NknGVlcHBKHPUNmpvPIYs0AC/EkTTVW', '09090909090', 'meishieL@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` int(11) NOT NULL,
  `OwnerID` int(11) NOT NULL,
  `EmployeeFN` varchar(255) NOT NULL,
  `EmployeeLN` varchar(255) NOT NULL,
  `E_Username` varchar(50) NOT NULL,
  `E_Password` varchar(255) NOT NULL,
  `Role` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `E_PhoneNumber` varchar(15) NOT NULL,
  `E_Email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeID`, `OwnerID`, `EmployeeFN`, `EmployeeLN`, `E_Username`, `E_Password`, `Role`, `is_active`, `E_PhoneNumber`, `E_Email`) VALUES
(9, 2, 'GG', 'Murin', 'CC', '$2y$10$n3dLbkUG/42VVaROUc7SFeWFB5pHFyhVPBY2lssMKEz60gki5UH7S', 'Cashier', 0, 'gmurin@gmail.co', '09766336211'),
(10, 2, 'Liz', 'Roseflame', 'LizloveRisa', '$2y$10$9/ZjkhtX4SySdAVJOTm8YOZLYSNFgPSSokbRWRU6jsUQAGclTWQT.', 'Barista', 1, 'lizloverisa@gma', '09234236886'),
(11, 2, 'test', 'test', 'test', '$2y$10$kYfaq9Hs49quuSdC/CecK.iCa0gnS5V2ohoVD.i.vFu6n1vvFzJdm', 'Barista', 1, 'test@gmail.com', '09123121222'),
(12, 2, 'dummy', 'duymyy', 'dummy', '$2y$10$AhFz69t4T1slHcR3QsPVJ.ZQMFLH0TJ83Ky75mJ9M.Znhr4Y.6DHS', 'Barista', 1, 'gumy@gmail.com', '09234223563'),
(13, 2, 'raorra', 'chatini', 'raora', '$2y$10$IcaIICxYvuFxVOo.2iiLae3zk1TnXF0DUeprCbZ5Ojw/AbYPDs0Pi', 'Barista', 1, '09131132433', 'raora@gmail.com'),
(14, 2, 'Niel', 'Cerezo', 'NielEmployee', '$2y$10$oliVombhJY6G6HPR93PZk.rgEuHAsN7HpDI7BOxcvOcIIsmQ0r1pm', 'Cashier', 0, '09494893869', 'cerezoniel@gmail.com'),
(15, 2, 'Teng', 'ina', 'Teng', '$2y$10$xT/aQfzNBU1z6WlwPyyPWuTZv3tP7fc5tduep/vJ2WyLBJrE1lZ9y', 'Barista', 1, '09494893869', 'tengina@gmail.com'),
(16, 2, 'Karl Deempee', 'Briones', 'Karl', '$2y$10$RFVPOTIfpU/JtJTdOBGrdetxQgEDK3fEQik0OhO8ESgoPN0zBE5Si', 'Barista', 1, '09123456789', 'karllovestrixel@123.com'),
(17, 2, 'Red', 'Cutiepie', 'Redqt', '$2y$10$g4CRYfnKPHaaXvm1QTY/muf4vf3Ad24Ulnsa4LQtlJvfiOGwK3iAO', 'Cashier', 1, '09696969696', 'redqt@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderDetailID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `PriceID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`OrderDetailID`, `OrderID`, `ProductID`, `PriceID`, `Quantity`, `Subtotal`) VALUES
(16, 19, 3, 3, 1, 100.00),
(17, 19, 2, 2, 1, 125.00),
(18, 19, 1, 1, 1, 90.00),
(19, 19, 31, 31, 1, 140.00),
(20, 19, 30, 30, 1, 130.00),
(21, 20, 3, 3, 1, 100.00),
(22, 20, 2, 2, 1, 125.00),
(23, 21, 3, 3, 1, 100.00),
(24, 21, 2, 2, 1, 125.00),
(25, 22, 3, 3, 1, 100.00),
(26, 22, 2, 2, 1, 125.00),
(27, 23, 32, 32, 1, 140.00),
(28, 23, 31, 31, 1, 140.00),
(29, 24, 3, 3, 1, 100.00),
(30, 25, 3, 3, 1, 100.00),
(31, 25, 2, 2, 1, 125.00),
(32, 26, 3, 3, 1, 100.00),
(33, 26, 2, 2, 1, 125.00),
(34, 27, 3, 3, 1, 100.00),
(35, 27, 2, 2, 2, 250.00),
(36, 28, 3, 3, 1, 100.00),
(37, 28, 2, 2, 2, 250.00),
(38, 29, 3, 3, 1, 100.00),
(41, 31, 3, 3, 2, 200.00),
(42, 31, 2, 2, 2, 250.00),
(43, 32, 2, 2, 1, 125.00),
(44, 32, 1, 1, 2, 180.00),
(45, 33, 3, 3, 1, 100.00),
(46, 33, 2, 2, 1, 125.00),
(47, 34, 4, 4, 1, 115.00),
(48, 35, 3, 3, 1, 100.00),
(49, 35, 2, 2, 1, 125.00),
(50, 36, 4, 4, 1, 115.00),
(51, 36, 3, 3, 1, 100.00),
(52, 36, 2, 2, 1, 125.00),
(53, 36, 1, 1, 1, 90.00),
(54, 37, 1, 1, 1, 90.00),
(55, 38, 1, 1, 2, 180.00),
(56, 41, 4, 4, 1, 115.00),
(57, 42, 3, 3, 1, 100.00),
(58, 43, 2, 2, 1, 125.00),
(59, 44, 1, 1, 1, 90.00),
(60, 45, 1, 1, 1, 90.00),
(61, 46, 2, 2, 1, 125.00),
(62, 47, 3, 3, 29, 2900.00),
(63, 47, 4, 4, 1, 115.00),
(64, 47, 7, 7, 1, 100.00),
(65, 47, 8, 8, 1, 50.00),
(66, 47, 2, 2, 1, 125.00),
(67, 47, 6, 6, 1, 120.00),
(68, 47, 1, 1, 1, 90.00),
(69, 47, 5, 5, 1, 100.00),
(70, 48, 8, 8, 1, 50.00),
(71, 49, 3, 3, 1, 100.00),
(72, 50, 54, 54, 1, 3.00),
(73, 51, 12, 12, 1, 130.00),
(74, 51, 50, 50, 1, 140.00),
(75, 51, 47, 47, 1, 145.00),
(76, 52, 3, 3, 1, 100.00),
(77, 53, 41, 41, 1, 70.00),
(78, 53, 50, 50, 1, 140.00),
(79, 54, 10, 10, 1, 135.00),
(80, 54, 50, 50, 1, 140.00),
(81, 55, 3, 3, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `OrderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `TotalAmount` decimal(10,2) NOT NULL,
  `OrderSID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `OrderDate`, `TotalAmount`, `OrderSID`) VALUES
(19, '2025-06-15 05:43:47', 585.00, 2),
(20, '2025-06-15 05:51:53', 225.00, 3),
(21, '2025-06-15 05:55:28', 225.00, 4),
(22, '2025-06-15 05:56:57', 225.00, 5),
(23, '2025-06-15 06:07:13', 280.00, 6),
(24, '2025-06-15 06:09:04', 100.00, 7),
(25, '2025-06-15 21:17:28', 225.00, 8),
(26, '2025-06-15 21:18:19', 225.00, 9),
(27, '2025-06-15 21:24:47', 350.00, 10),
(28, '2025-06-15 21:37:31', 350.00, 11),
(29, '2025-06-15 21:46:00', 100.00, 12),
(31, '2025-06-15 21:57:39', 450.00, 14),
(32, '2025-06-15 21:58:06', 305.00, 15),
(33, '2025-06-16 02:33:14', 225.00, 16),
(34, '2025-06-16 03:06:44', 115.00, 17),
(35, '2025-06-16 03:11:35', 225.00, 18),
(36, '2025-06-16 03:13:00', 430.00, 19),
(37, '2025-06-16 10:42:23', 90.00, 20),
(38, '2025-06-19 12:27:54', 180.00, 21),
(39, '2025-06-20 12:30:14', 150.00, 1),
(40, '2025-06-20 12:30:31', 150.00, 1),
(41, '2025-06-20 15:00:39', 115.00, 22),
(42, '2025-06-20 15:00:49', 100.00, 23),
(43, '2025-06-20 15:00:56', 125.00, 24),
(44, '2025-06-20 15:01:07', 90.00, 25),
(45, '2025-06-22 15:08:02', 90.00, 26),
(46, '2025-06-22 15:29:44', 125.00, 27),
(47, '2025-06-22 15:30:23', 3600.00, 28),
(48, '2025-06-22 17:12:56', 50.00, 29),
(49, '2025-06-22 17:14:06', 100.00, 30),
(50, '2025-06-22 17:20:20', 3.00, 31),
(51, '2025-07-06 17:29:53', 415.00, 32),
(52, '2025-07-06 17:39:11', 100.00, 33),
(53, '2025-08-09 05:20:32', 210.00, 34),
(54, '2025-08-09 06:08:56', 275.00, 35),
(55, '2025-08-15 01:17:36', 100.00, 36);

-- --------------------------------------------------------

--
-- Table structure for table `ordersection`
--

CREATE TABLE `ordersection` (
  `OrderSID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `EmployeeID` int(11) DEFAULT NULL,
  `OwnerID` int(11) DEFAULT NULL,
  `UserTypeID` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordersection`
--

INSERT INTO `ordersection` (`OrderSID`, `CustomerID`, `EmployeeID`, `OwnerID`, `UserTypeID`) VALUES
(1, NULL, NULL, 2, 1),
(2, NULL, NULL, 2, 1),
(3, NULL, NULL, 2, 1),
(4, NULL, 12, NULL, 2),
(5, NULL, 12, NULL, 2),
(6, 4, NULL, NULL, 3),
(7, 4, NULL, NULL, 3),
(8, NULL, NULL, 2, 1),
(9, NULL, NULL, 2, 1),
(10, NULL, NULL, 2, 1),
(11, NULL, NULL, 2, 1),
(12, NULL, NULL, 2, 1),
(14, NULL, NULL, 2, 1),
(15, NULL, NULL, 2, 1),
(16, NULL, 13, 2, 2),
(17, 5, NULL, NULL, 3),
(18, 5, NULL, NULL, 3),
(19, 5, NULL, NULL, 3),
(20, 6, NULL, 1, 3),
(21, NULL, NULL, 2, 1),
(22, 7, NULL, 1, 3),
(23, 7, NULL, 1, 3),
(24, 7, NULL, 1, 3),
(25, 7, NULL, 1, 3),
(26, 7, NULL, 1, 3),
(27, 7, NULL, 1, 3),
(28, 7, NULL, 1, 3),
(29, 7, NULL, 1, 3),
(30, NULL, 14, 2, 2),
(31, 7, NULL, 1, 3),
(32, 8, NULL, 1, 3),
(33, NULL, 16, 2, 2),
(34, 9, NULL, 1, 3),
(35, 10, NULL, 1, 3),
(36, 6, NULL, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `owner`
--

CREATE TABLE `owner` (
  `OwnerID` int(11) NOT NULL,
  `OwnerFN` varchar(255) NOT NULL,
  `OwnerLN` varchar(255) NOT NULL,
  `O_PhoneNumber` varchar(15) NOT NULL,
  `O_Email` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner`
--

INSERT INTO `owner` (`OwnerID`, `OwnerFN`, `OwnerLN`, `O_PhoneNumber`, `O_Email`, `Username`, `Password`) VALUES
(1, 'Gigi ', 'Murin', '09999999999', 'ccismywife@gmail.com', 'Auotfister', '$2y$10$ncjL23xd600M6OHyDjO7ceZKQwwMqgzkVgKkC9oNMnuY3fQNYymZa'),
(2, 'Cece', 'Immerhate', '09766336211', 'ggismywife@murin.com', 'ImmerHater', '$2y$10$BQviXtvFVVI0Jb73KPb.FeGVuc4qDwUd5DhxwNHcXmS63m4htR/ou'),
(3, 'Test', 'test', '09123131311', 'tes@gmail.com', 'test', '$2y$10$iMsa/kZI4xr/GtYJyfuCO..MXyEy8krVie3Jg01Ni5NpnHrX.l0sO'),
(4, 'test', 'test', '09131331111', 'test@gmail.com', 'testtt', '$2y$10$jh30XVL9Wrkx3Z5IWRx8FOUK7WTv5ys8PoEbILvIxxnC64yc4jvgO');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `PaymentDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PaymentMethod` varchar(255) NOT NULL,
  `PaymentAmount` decimal(10,2) NOT NULL,
  `PaymentStatus` tinyint(1) DEFAULT NULL,
  `ReferenceNo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `OrderID`, `PaymentDate`, `PaymentMethod`, `PaymentAmount`, `PaymentStatus`, `ReferenceNo`) VALUES
(7, 31, '2025-06-15 21:57:39', 'cash', 450.00, 0, 'LA684F41D3A36698755'),
(8, 32, '2025-06-15 21:58:06', 'cash', 305.00, 0, 'LA684F41EE2AED78095'),
(9, 33, '2025-06-16 02:33:14', 'cash', 225.00, 0, 'EMP684F826AA83EB7527'),
(10, 34, '2025-06-16 03:06:44', 'gcash', 115.00, 0, 'CUST684F8A4467BE58012'),
(11, 35, '2025-06-16 03:11:35', 'gcash', 225.00, 0, 'CUST684F8B67835054541'),
(12, 36, '2025-06-16 03:13:00', 'gcash', 430.00, 0, 'CUST684F8BBC40CE87399'),
(13, 37, '2025-06-16 10:42:23', 'gcash', 90.00, 0, 'CUST684FF50FE59347329'),
(14, 38, '2025-06-19 12:27:54', 'cash', 180.00, 0, 'LA6854024A8979A5947'),
(15, 41, '2025-06-20 15:00:39', 'gcash', 115.00, 0, 'CUST685577974E8A56093'),
(16, 42, '2025-06-20 15:00:49', 'gcash', 100.00, 0, 'CUST685577A14EF9B9403'),
(17, 43, '2025-06-20 15:00:56', 'gcash', 125.00, 0, 'CUST685577A861FA99644'),
(18, 44, '2025-06-20 15:01:07', 'gcash', 90.00, 0, 'CUST685577B3B30715621'),
(19, 45, '2025-06-22 15:08:02', 'gcash', 90.00, 0, 'CUST68581C52C1C672512'),
(20, 46, '2025-06-22 15:29:44', 'gcash', 125.00, 1, 'CUST68582168835A45093'),
(21, 47, '2025-06-22 15:30:23', 'gcash', 3600.00, 0, 'CUST6858218F198465981'),
(22, 48, '2025-06-22 17:12:56', 'gcash', 50.00, 0, 'CUST68583998614B66258'),
(23, 49, '2025-06-22 17:14:06', 'cash', 100.00, 0, 'EMP685839DEBDCAA1515'),
(24, 50, '2025-06-22 17:20:20', 'gcash', 3.00, 0, 'CUST68583B5410D4A8080'),
(25, 51, '2025-07-06 17:29:53', 'gcash', 415.00, 1, 'CUST686AB29145DE39642'),
(26, 52, '2025-07-06 17:39:11', 'cash', 100.00, 1, 'EMP686AB4BFBBE902225'),
(27, 53, '2025-08-09 05:20:32', 'gcash', 210.00, 1, 'CUST6896DAA09907E7886'),
(28, 54, '2025-08-09 06:08:56', 'gcash', 275.00, 1, 'CUST6896E5F8E71138226'),
(29, 55, '2025-08-15 01:17:36', 'gcash', 100.00, 1, 'CUST689E8AB03F8553362');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(255) NOT NULL,
  `ProductCategory` varchar(255) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `Created_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `ImagePath` varchar(255) NOT NULL,
  `Allergen` varchar(100) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `ProductName`, `ProductCategory`, `is_available`, `Created_AT`, `ImagePath`, `Allergen`, `Description`) VALUES
(1, 'Hot Americano', 'COFFEE', 1, '2025-06-15 03:08:22', 'hotamericano_20250813_184447_689cc0ff638b5.jpg', 'None', NULL),
(2, 'Hot Caramel Macchiato', 'COFFEE', 1, '2025-06-15 03:08:22', 'hotcaramelmacchiato_20250813_183559_689cbeef87a34.jfif', 'Milk, Soy, Tree Nuts', NULL),
(3, 'Hot Spanish Latte', 'COFFEE', 1, '2025-06-15 03:08:22', 'hot_spanish_latte_20250815_020228_689e791403b82.jpg', 'Milk', NULL),
(4, 'Hot White Mocha', 'COFFEE', 1, '2025-06-15 03:08:22', 'hot_white_mocha_20250815_020245_689e79258fb9f.jpg', 'Milk, Soy', NULL),
(5, 'Hot Cappuccino', 'COFFEE', 1, '2025-06-15 03:08:22', 'HC_20250815_022753_689e7f091ceed.jpg', 'Milk', NULL),
(6, 'Hot Dark Chocolate Mocha', 'COFFEE', 1, '2025-06-15 03:08:22', 'Screenshot 2025-08-15 082735_20250815_022805_689e7f1521374.png', 'Milk, Soy', NULL),
(7, 'Hot Flat White/Latte', 'COFFEE', 1, '2025-06-15 03:08:22', 'Screenshot 2025-08-15 082834_20250815_022846_689e7f3e78338.png', 'Milk', NULL),
(8, 'Hot Kapeng Barako', 'COFFEE', 1, '2025-06-15 03:08:22', 'Screenshot 2025-08-15 082959_20250815_023011_689e7f93ad60e.png', 'None', NULL),
(9, 'Iced Americano', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'None', NULL),
(10, 'Iced Caramel Macchiato', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk, Soy, Tree Nuts', NULL),
(11, 'Iced Spanish Latte', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk', NULL),
(12, 'Iced White Mocha', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk, Soy', NULL),
(13, 'Iced Double Chocolate Latte', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk, Soy', NULL),
(14, 'Iced Vanilla Latte', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk', NULL),
(15, 'Iced Hazelnut Latte', 'COFFEE', 1, '2025-06-15 03:08:22', '', 'Milk, Tree Nuts', NULL),
(16, 'Hot Chocolate', 'NON COFFEE', 1, '2025-06-15 03:08:22', 'hot_chocolate_20250815_020425_689e7989804e9.jpg', 'Milk, Soy', NULL),
(17, 'Choco Hazelnut', 'NON COFFEE', 1, '2025-06-15 03:08:22', 'choco_hazelnut_20250815_020441_689e79992ef6c.jpg', 'Milk, Tree Nuts', NULL),
(18, 'Strawberry Milk', 'NON COFFEE', 1, '2025-06-15 03:08:22', 'strawberry_milk_20250815_020455_689e79a7bfb9b.jpg', 'Milk', NULL),
(19, 'Dirty Matcha', 'MATCHA', 1, '2025-06-15 03:08:22', 'dirty_matcha_20250815_020512_689e79b84c3ed.jpg', 'Milk', NULL),
(20, 'Dulce Matcha', 'MATCHA', 1, '2025-06-15 03:08:22', 'dulce_matcha_20250815_020527_689e79c774b2b.jpg', 'Milk', NULL),
(21, 'Hot Matcha Latte', 'MATCHA', 1, '2025-06-15 03:08:22', 'hot_matcha_latte_20250815_020550_689e79de7916b.jpg', 'Milk', NULL),
(22, 'Strawberry Matcha', 'MATCHA', 1, '2025-06-15 03:08:22', 'strawberry_matcha_20250815_020615_689e79f7f03ca.jpg', 'Milk', NULL),
(23, 'White Chocolate Matcha', 'MATCHA', 1, '2025-06-15 03:08:22', 'white_chocolate_matcha_20250815_020631_689e7a0755561.jpg', 'Milk, Soy', NULL),
(24, 'Caramel Coffee Jelly', 'FRAPPE', 1, '2025-06-15 03:08:22', 'caramel_coffee_jelly_20250815_020725_689e7a3d18cd3.jpg', 'Milk, Soy, Gelatin', NULL),
(25, 'Java Chip Mocha', 'FRAPPE', 1, '2025-06-15 03:08:22', 'java_chip_mocha_20250815_020739_689e7a4bde1b8.jpg', 'Milk, Soy', NULL),
(26, 'We’re a Matcha', 'FRAPPE', 1, '2025-06-15 03:08:22', 'we_re_a_matcha_20250815_020756_689e7a5c01633.jpg', 'Milk', NULL),
(27, 'Oh My, Oreo', 'FRAPPE', 1, '2025-06-15 03:08:22', 'oh_my_oreo_20250815_020821_689e7a75eba82.jpg', 'Milk, Wheat, Soy', NULL),
(28, 'Strawberry Burst', 'FRAPPE', 1, '2025-06-15 03:08:22', 'strawberry_burst_20250815_020834_689e7a8287c3d.jpg', 'Milk', NULL),
(29, 'White Chocolate', 'FRAPPE', 1, '2025-06-15 03:08:22', 'white_chocolate_20250815_020848_689e7a90c12a6.jpg', 'Milk, Soy', NULL),
(30, 'Love, Amaiah Drink', 'SIGNATURES', 0, '2025-06-15 03:08:22', '', 'Milk', NULL),
(31, 'Affogato', 'SIGNATURES', 1, '2025-06-15 03:08:22', 'affogato_20250815_020907_689e7aa3a11e5.png', 'Milk', NULL),
(32, 'Caramel Cloud', 'SIGNATURES', 1, '2025-06-15 03:08:22', 'caramel_cloud_latte_20250815_020928_689e7ab896efe.png', 'Milk, Soy', NULL),
(33, 'Cinnamon Macchiato', 'SIGNATURES', 1, '2025-06-15 03:08:22', 'cinnamon_macchiato_20250815_020941_689e7ac59c978.png', 'Milk', NULL),
(34, 'Iced Shaken Brownie', 'SIGNATURES', 1, '2025-06-15 03:08:22', 'iced_shaken_brownie_20250815_020959_689e7ad702a0b.png', 'Milk, Wheat, Soy, Eggs', NULL),
(35, 'Kori Kohi', 'SIGNATURES', 1, '2025-06-15 03:08:22', 'kori_kohi_20250815_021042_689e7b025c64f.jpg', 'Milk', NULL),
(36, 'Mud Mocha', 'SIGNATURES', 1, '2025-06-15 03:08:22', '', 'Milk, Soy', NULL),
(37, 'Blueberry Soda', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'blueberry_soda_20250815_021130_689e7b32e640b.jpg', 'None', NULL),
(38, 'Strawberry Soda', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'strawberry_soda_20250815_021154_689e7b4acd660.jpg', 'None', NULL),
(39, 'Green Apple Soda', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'green_apple_soda_20250815_021317_689e7b9d7a72b.jpg', 'None', NULL),
(40, 'Strawberry Yakult', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'strawberry_yakult_20250815_021717_689e7c8dab724.jpg', 'Milk', NULL),
(41, 'Green Apple Yakult', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'green_apple_yakult_20250815_021729_689e7c999befa.jpg', 'Milk', NULL),
(42, 'Lychee Yakult', 'REFRESHMENTS', 1, '2025-06-15 03:08:22', 'lychee_yakult_20250815_021745_689e7ca9da3e4.jpg', 'Milk', NULL),
(43, 'Classic', 'WAFFLES', 1, '2025-06-15 03:08:22', 'classic_waffle_20250815_021841_689e7ce1c40f8.jpg', 'Wheat, Eggs, Milk', NULL),
(44, 'Chocolate Chip', 'WAFFLES', 1, '2025-06-15 03:08:22', 'chocolate_chip_waffle_20250815_021857_689e7cf1247dd.jpg', 'Wheat, Eggs, Milk, Soy', NULL),
(45, 'Blueberry', 'WAFFLES', 1, '2025-06-15 03:08:22', 'blueberry_waffle_20250815_021931_689e7d13296a5.jpg', 'Wheat, Eggs, Milk', NULL),
(46, 'Nutty Caramel', 'WAFFLES', 1, '2025-06-15 03:08:22', 'nutty_caramel_waffle_20250815_021956_689e7d2c2b1a1.jpg', 'Wheat, Eggs, Milk, Tree Nuts', NULL),
(47, 'Bacon', 'WAFFLES', 1, '2025-06-15 03:08:22', 'bacon_waffle_20250815_022010_689e7d3a1365e.jpg', 'Wheat, Eggs, Milk', NULL),
(48, 'Love, Amaiah Special', 'SANDWICHES', 1, '2025-06-15 03:08:22', 'love_amaiah_special_sandwich_20250815_022055_689e7d674cabf.jpg', 'Wheat, Eggs, Milk', NULL),
(49, 'Ham-tastic (White Bread)', 'SANDWICHES', 1, '2025-06-15 03:08:22', 'ham_and_cheese_sandwich_20250815_022122_689e7d829848d.jpg', 'Wheat, Eggs, Milk', NULL),
(50, 'Ham-tastic (Croissant)', 'SANDWICHES', 1, '2025-06-15 03:08:22', '', 'Wheat, Eggs, Milk', NULL),
(51, 'Tuna Melt (White Bread)', 'SANDWICHES', 1, '2025-06-15 03:08:22', 'tuna_cheese_sandwich_20250815_022154_689e7da2db9f6.jpg', 'Wheat, Eggs, Milk, Fish', NULL),
(52, 'Tuna Melt (Croissant)', 'SANDWICHES', 0, '2025-06-15 03:08:22', '', 'Wheat, Eggs, Milk, Fish', NULL),
(53, 'test', 'COFFEE', 0, '2025-06-20 03:36:26', '', NULL, NULL),
(54, '3am Coffee', 'COFFEE', 1, '2025-06-22 17:19:54', '', NULL, NULL),
(55, 'Hot Maria Clara', 'SANDWICHES', 0, '2025-08-19 00:10:29', 'image_20250819_021029_68a3c0f5e0abb.png', NULL, NULL),
(56, 'Hot Maria Clare', 'SANDWICHES', 1, '2025-08-19 01:31:11', 'image_20250819_033110_68a3d3def18f3.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `productprices`
--

CREATE TABLE `productprices` (
  `PriceID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `Effective_From` date NOT NULL,
  `Effective_To` date DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productprices`
--

INSERT INTO `productprices` (`PriceID`, `ProductID`, `UnitPrice`, `Effective_From`, `Effective_To`, `Created_At`) VALUES
(1, 1, 90.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(2, 2, 125.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(3, 3, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(4, 4, 115.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(5, 5, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(6, 6, 120.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(7, 7, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(8, 8, 50.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(9, 9, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(10, 10, 135.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(11, 11, 110.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(12, 12, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(13, 13, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(14, 14, 110.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(15, 15, 110.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(16, 16, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(17, 17, 120.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(18, 18, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(19, 19, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(20, 20, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(21, 21, 110.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(22, 22, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(23, 23, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(24, 24, 150.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(25, 25, 150.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(26, 26, 150.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(27, 27, 160.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(28, 28, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(29, 29, 150.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(30, 30, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(31, 31, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(32, 32, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(33, 33, 130.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(34, 34, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(35, 35, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(36, 36, 150.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(37, 37, 60.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(38, 38, 70.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(39, 39, 60.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(40, 40, 80.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(41, 41, 70.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(42, 42, 70.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(43, 43, 50.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(44, 44, 115.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(45, 45, 120.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(46, 46, 120.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(47, 47, 145.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(48, 48, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(49, 49, 100.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(50, 50, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(51, 51, 105.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(52, 52, 140.00, '2025-06-15', NULL, '2025-06-15 03:08:37'),
(53, 53, 123.00, '2025-06-25', '2025-06-10', '2025-06-20 03:36:26'),
(54, 54, 3.00, '2025-06-23', NULL, '2025-06-22 17:19:54'),
(55, 55, 100.00, '2025-08-19', NULL, '2025-08-19 00:10:29'),
(56, 56, 150.00, '2025-08-19', NULL, '2025-08-19 01:31:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`),
  ADD UNIQUE KEY `C_Username` (`C_Username`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD UNIQUE KEY `E_Username` (`E_Username`),
  ADD KEY `OwnerID` (`OwnerID`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderDetailID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `PriceID` (`PriceID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `fk_orders_section` (`OrderSID`);

--
-- Indexes for table `ordersection`
--
ALTER TABLE `ordersection`
  ADD PRIMARY KEY (`OrderSID`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `EmployeeID` (`EmployeeID`),
  ADD KEY `fk_ordersection_owner` (`OwnerID`);

--
-- Indexes for table `owner`
--
ALTER TABLE `owner`
  ADD PRIMARY KEY (`OwnerID`),
  ADD KEY `Username` (`Username`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`);

--
-- Indexes for table `productprices`
--
ALTER TABLE `productprices`
  ADD PRIMARY KEY (`PriceID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `EmployeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orderdetails`
--
ALTER TABLE `orderdetails`
  MODIFY `OrderDetailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `ordersection`
--
ALTER TABLE `ordersection`
  MODIFY `OrderSID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `owner`
--
ALTER TABLE `owner`
  MODIFY `OwnerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `productprices`
--
ALTER TABLE `productprices`
  MODIFY `PriceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`OwnerID`) REFERENCES `owner` (`OwnerID`);

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`),
  ADD CONSTRAINT `orderdetails_ibfk_3` FOREIGN KEY (`PriceID`) REFERENCES `productprices` (`PriceID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_section` FOREIGN KEY (`OrderSID`) REFERENCES `ordersection` (`OrderSID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ordersection`
--
ALTER TABLE `ordersection`
  ADD CONSTRAINT `fk_orders_old_owner` FOREIGN KEY (`OwnerID`) REFERENCES `owner` (`OwnerID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ordersection_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ordersection_employee` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmployeeID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ordersection_owner` FOREIGN KEY (`OwnerID`) REFERENCES `owner` (`OwnerID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `productprices`
--
ALTER TABLE `productprices`
  ADD CONSTRAINT `productprices_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
