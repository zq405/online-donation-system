-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-05-07 12:02:16
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `donation_system`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `Admin_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` varchar(50) DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `campaign`
--

CREATE TABLE `campaign` (
  `Campaign_ID` int(11) NOT NULL,
  `Recipient_ID` int(11) NOT NULL,
  `Created_by` int(11) DEFAULT NULL,
  `Title` varchar(200) NOT NULL,
  `Description` text DEFAULT NULL,
  `goal_amount` decimal(15,2) NOT NULL,
  `raised_amount` decimal(15,2) DEFAULT 0.00,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `destination` varchar(255) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- 表的结构 `campaign_categories`
--

CREATE TABLE `campaign_categories` (
  `Campaign_ID` int(11) NOT NULL,
  `category_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `categories`
--

CREATE TABLE `categories` (
  `category_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `donations`
--

CREATE TABLE `donations` (
  `Donation_ID` int(11) NOT NULL,
  `Donors_ID` int(11) NOT NULL,
  `Campaign_ID` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_Method` varchar(50) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'pending',
  `transaction_ID` varchar(100) DEFAULT NULL,
  `transaction_location` varchar(255) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- 表的结构 `donors`
--

CREATE TABLE `donors` (
  `Donors_ID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Register` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `recipient`
--

CREATE TABLE `recipient` (
  `Recipient_ID` int(11) NOT NULL,
  `Organization_Name` varchar(200) NOT NULL,
  `Contact_Person` varchar(100) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `reports`
--

CREATE TABLE `reports` (
  `report_ID` int(11) NOT NULL,
  `campaign_ID` int(11) NOT NULL,
  `admin_ID` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `requests`
--

CREATE TABLE `requests` (
  `Request_ID` int(11) NOT NULL,
  `Donors_ID` int(11) DEFAULT NULL,
  `Campaign_ID` int(11) DEFAULT NULL,
  `Request_Type` varchar(50) DEFAULT NULL,
  `Request_Details` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`Admin_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- 表的索引 `campaign`
--
ALTER TABLE `campaign`
  ADD PRIMARY KEY (`Campaign_ID`),
  ADD KEY `Created_by` (`Created_by`),
  ADD KEY `idx_campaign_recipient` (`Recipient_ID`),
  ADD KEY `idx_campaign_dates` (`start_date`,`end_date`);

--
-- 表的索引 `campaign_categories`
--
ALTER TABLE `campaign_categories`
  ADD PRIMARY KEY (`Campaign_ID`,`category_ID`),
  ADD KEY `category_ID` (`category_ID`);

--
-- 表的索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_ID`);

--
-- 表的索引 `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`Donation_ID`),
  ADD UNIQUE KEY `transaction_ID` (`transaction_ID`),
  ADD KEY `idx_donations_campaign` (`Campaign_ID`),
  ADD KEY `idx_donations_donor` (`Donors_ID`);

--
-- 表的索引 `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`Donors_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_donors_email` (`Email`);

--
-- 表的索引 `recipient`
--
ALTER TABLE `recipient`
  ADD PRIMARY KEY (`Recipient_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_recipient_email` (`Email`);

--
-- 表的索引 `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_ID`),
  ADD KEY `campaign_ID` (`campaign_ID`),
  ADD KEY `admin_ID` (`admin_ID`);

--
-- 表的索引 `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`Request_ID`),
  ADD KEY `Donors_ID` (`Donors_ID`),
  ADD KEY `Campaign_ID` (`Campaign_ID`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `campaign`
--
ALTER TABLE `campaign`
  MODIFY `Campaign_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `categories`
--
ALTER TABLE `categories`
  MODIFY `category_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `donations`
--
ALTER TABLE `donations`
  MODIFY `Donation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `donors`
--
ALTER TABLE `donors`
  MODIFY `Donors_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `recipient`
--
ALTER TABLE `recipient`
  MODIFY `Recipient_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `reports`
--
ALTER TABLE `reports`
  MODIFY `report_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `requests`
--
ALTER TABLE `requests`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `campaign`
--
ALTER TABLE `campaign`
  ADD CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`Recipient_ID`) REFERENCES `recipient` (`Recipient_ID`),
  ADD CONSTRAINT `campaign_ibfk_2` FOREIGN KEY (`Created_by`) REFERENCES `admin` (`Admin_ID`);

--
-- 限制表 `campaign_categories`
--
ALTER TABLE `campaign_categories`
  ADD CONSTRAINT `campaign_categories_ibfk_1` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`),
  ADD CONSTRAINT `campaign_categories_ibfk_2` FOREIGN KEY (`category_ID`) REFERENCES `categories` (`category_ID`);

--
-- 限制表 `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`Donors_ID`) REFERENCES `donors` (`Donors_ID`),
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`);

--
-- 限制表 `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`campaign_ID`) REFERENCES `campaign` (`Campaign_ID`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`admin_ID`) REFERENCES `admin` (`Admin_ID`);

--
-- 限制表 `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`Donors_ID`) REFERENCES `donors` (`Donors_ID`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;