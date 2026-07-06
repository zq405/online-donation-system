-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-05-19 04:26:50
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
-- 数据库： `donation`
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
  `Role` varchar(50) DEFAULT 'admin',
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`Admin_ID`, `Name`, `Email`, `Password`, `Role`, `Created_At`) VALUES
(1, 'Admin', 'admin@animalshelter.com', '$2y$10$drdNKOJyQOCwliiyd0fP9eOxTw.bl1Wn784T.35xit9/6bs/ONn2G', 'admin', '2026-05-14 00:12:53');

-- --------------------------------------------------------

--
-- 表的结构 `campaign`
--

CREATE TABLE `campaign` (
  `Campaign_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL COMMENT '创建活动的管理员ID',
  `Title` varchar(200) NOT NULL COMMENT '活动标题',
  `Description` text DEFAULT NULL COMMENT '活动描述',
  `Goal_Amount` decimal(15,2) NOT NULL COMMENT '目标金额',
  `Raised_Amount` decimal(15,2) DEFAULT 0.00 COMMENT '已筹金额',
  `Start_Date` date NOT NULL COMMENT '开始日期',
  `End_Date` date NOT NULL COMMENT '结束日期',
  `Status` varchar(20) DEFAULT 'pending' COMMENT 'pending/active/completed/cancelled',
  `Animal_Type` varchar(100) DEFAULT NULL COMMENT '动物类型 (Dog/Cat/Rabbit/Bird/Other)',
  `Animal_Count` int(11) DEFAULT 1 COMMENT '需要救助的动物数量',
  `Animal_Name` varchar(100) DEFAULT NULL COMMENT '动物名字',
  `Animal_Age` varchar(50) DEFAULT NULL COMMENT '动物年龄',
  `Animal_Image` varchar(500) DEFAULT NULL COMMENT '动物图片URL',
  `Shelter_Name` varchar(200) DEFAULT NULL COMMENT '收容所名称',
  `Shelter_Location` varchar(255) DEFAULT NULL COMMENT '收容所位置',
  `Shelter_Phone` varchar(20) DEFAULT NULL COMMENT '收容所电话',
  `Medical_Need` text DEFAULT NULL COMMENT '医疗需求描述',
  `Urgency_Level` varchar(20) DEFAULT 'normal' COMMENT '紧急程度 (low/normal/high/urgent)',
  `Verified_By` int(11) DEFAULT NULL COMMENT '审核人ID',
  `Verified_At` datetime DEFAULT NULL COMMENT '审核时间',
  `Rejection_Reason` text DEFAULT NULL COMMENT '拒绝原因',
  `Created_At` datetime DEFAULT current_timestamp(),
  `Updated_At` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `donations`
--

CREATE TABLE `donations` (
  `Donation_ID` int(11) NOT NULL,
  `Donors_ID` int(11) NOT NULL,
  `Campaign_ID` int(11) NOT NULL,
  `Amount` decimal(15,2) NOT NULL,
  `Payment_Method` varchar(50) DEFAULT NULL COMMENT 'Credit Card/Debit Card/eWallet',
  `Transaction_ID` varchar(100) DEFAULT NULL,
  `Is_Anonymous` tinyint(1) DEFAULT 0 COMMENT '是否匿名捐赠',
  `Donor_Message` text DEFAULT NULL COMMENT '捐赠留言',
  `Status` varchar(20) DEFAULT 'pending' COMMENT 'pending/completed/failed/refunded',
  `Points_Earned` int(11) DEFAULT 0 COMMENT '获得的积分',
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 触发器 `donations`
--
DELIMITER $$
CREATE TRIGGER `after_donation_insert` AFTER INSERT ON `donations` FOR EACH ROW BEGIN
    -- 如果捐赠状态为 completed，更新活动筹款金额
    IF NEW.Status = 'completed' THEN
        UPDATE campaign 
        SET Raised_Amount = Raised_Amount + NEW.Amount
        WHERE Campaign_ID = NEW.Campaign_ID;
        
        -- 更新捐赠者积分 (每 $1 = 1 积分)
        UPDATE donors 
        SET Points = Points + FLOOR(NEW.Amount),
            Badge = CASE 
                WHEN Points + FLOOR(NEW.Amount) >= 1000 THEN 'Gold'
                WHEN Points + FLOOR(NEW.Amount) >= 500 THEN 'Silver'
                WHEN Points + FLOOR(NEW.Amount) >= 100 THEN 'Bronze'
                ELSE Badge
            END
        WHERE Donors_ID = NEW.Donors_ID;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_donation_update` AFTER UPDATE ON `donations` FOR EACH ROW BEGIN
    -- 如果状态从非 completed 变为 completed
    IF OLD.Status != 'completed' AND NEW.Status = 'completed' THEN
        UPDATE campaign 
        SET Raised_Amount = Raised_Amount + NEW.Amount
        WHERE Campaign_ID = NEW.Campaign_ID;
        
        UPDATE donors 
        SET Points = Points + FLOOR(NEW.Amount),
            Badge = CASE 
                WHEN Points + FLOOR(NEW.Amount) >= 1000 THEN 'Gold'
                WHEN Points + FLOOR(NEW.Amount) >= 500 THEN 'Silver'
                WHEN Points + FLOOR(NEW.Amount) >= 100 THEN 'Bronze'
                ELSE Badge
            END
        WHERE Donors_ID = NEW.Donors_ID;
    END IF;
END
$$
DELIMITER ;

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
  `Points` int(11) DEFAULT 0 COMMENT '捐赠积分',
  `Badge` varchar(50) DEFAULT NULL COMMENT '勋章等级 (Bronze/Silver/Gold)',
  `Register_Date` datetime DEFAULT current_timestamp(),
  `Status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 转存表中的数据 `donors`
--

INSERT INTO `donors` (`Donors_ID`, `Name`, `Email`, `Password`, `Phone`, `Points`, `Badge`, `Register_Date`, `Status`) VALUES
(1, 'qqq', 'qqq@gmail.com', '$2y$10$PbM9CA5/FUrNl5MskSNf0ecDHu8zsUn.7Wc/LuYB3uhWwraak5mru', '01344553332', 0, NULL, '2026-05-14 03:54:34', 'active');

-- --------------------------------------------------------

--
-- 表的结构 `reports`
--

CREATE TABLE `reports` (
  `Report_ID` int(11) NOT NULL,
  `Campaign_ID` int(11) NOT NULL,
  `Admin_ID` int(11) NOT NULL,
  `Type` varchar(50) DEFAULT NULL COMMENT 'donation_summary/campaign_progress/user_activity',
  `Content` text DEFAULT NULL,
  `File_Path` varchar(500) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `requests`
--

CREATE TABLE `requests` (
  `Request_ID` int(11) NOT NULL,
  `Donors_ID` int(11) DEFAULT NULL,
  `Campaign_ID` int(11) DEFAULT NULL,
  `Request_Type` varchar(50) DEFAULT NULL COMMENT 'complaint/inquiry/feedback',
  `Request_Details` text DEFAULT NULL,
  `Reply` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'pending' COMMENT 'pending/replied/closed',
  `Created_At` datetime DEFAULT current_timestamp(),
  `Replied_At` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `system_logs`
--

CREATE TABLE `system_logs` (
  `Log_ID` int(11) NOT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Action` varchar(100) NOT NULL,
  `Description` text DEFAULT NULL,
  `IP_Address` varchar(45) DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `vw_campaign_stats`
-- （参见下面的实际视图）
--
CREATE TABLE `vw_campaign_stats` (
`Campaign_ID` int(11)
,`Title` varchar(200)
,`Goal_Amount` decimal(15,2)
,`Raised_Amount` decimal(15,2)
,`Progress_Percentage` decimal(21,2)
,`Animal_Type` varchar(100)
,`Animal_Count` int(11)
,`Shelter_Name` varchar(200)
,`Status` varchar(20)
,`Urgency_Level` varchar(20)
,`Total_Donations` bigint(21)
,`Unique_Donors` bigint(21)
,`End_Date` date
,`Days_Remaining` int(7)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `vw_donor_ranking`
-- （参见下面的实际视图）
--
CREATE TABLE `vw_donor_ranking` (
`Donors_ID` int(11)
,`Name` varchar(100)
,`Points` int(11)
,`Badge` varchar(50)
,`Donation_Count` bigint(21)
,`Total_Donated` decimal(37,2)
,`Last_Donation_Date` datetime
);

-- --------------------------------------------------------

--
-- 视图结构 `vw_campaign_stats`
--
DROP TABLE IF EXISTS `vw_campaign_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_campaign_stats`  AS SELECT `c`.`Campaign_ID` AS `Campaign_ID`, `c`.`Title` AS `Title`, `c`.`Goal_Amount` AS `Goal_Amount`, `c`.`Raised_Amount` AS `Raised_Amount`, round(`c`.`Raised_Amount` / `c`.`Goal_Amount` * 100,2) AS `Progress_Percentage`, `c`.`Animal_Type` AS `Animal_Type`, `c`.`Animal_Count` AS `Animal_Count`, `c`.`Shelter_Name` AS `Shelter_Name`, `c`.`Status` AS `Status`, `c`.`Urgency_Level` AS `Urgency_Level`, count(`d`.`Donation_ID`) AS `Total_Donations`, count(distinct `d`.`Donors_ID`) AS `Unique_Donors`, `c`.`End_Date` AS `End_Date`, to_days(`c`.`End_Date`) - to_days(curdate()) AS `Days_Remaining` FROM (`campaign` `c` left join `donations` `d` on(`c`.`Campaign_ID` = `d`.`Campaign_ID` and `d`.`Status` = 'completed')) GROUP BY `c`.`Campaign_ID` ;

-- --------------------------------------------------------

--
-- 视图结构 `vw_donor_ranking`
--
DROP TABLE IF EXISTS `vw_donor_ranking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_donor_ranking`  AS SELECT `d`.`Donors_ID` AS `Donors_ID`, `d`.`Name` AS `Name`, `d`.`Points` AS `Points`, `d`.`Badge` AS `Badge`, count(`don`.`Donation_ID`) AS `Donation_Count`, sum(`don`.`Amount`) AS `Total_Donated`, max(`don`.`Created_At`) AS `Last_Donation_Date` FROM (`donors` `d` left join `donations` `don` on(`d`.`Donors_ID` = `don`.`Donors_ID` and `don`.`Status` = 'completed')) GROUP BY `d`.`Donors_ID` ORDER BY sum(`don`.`Amount`) DESC ;

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
  ADD KEY `idx_campaign_dates` (`Start_Date`,`End_Date`),
  ADD KEY `idx_campaign_status` (`Status`),
  ADD KEY `idx_campaign_animal` (`Animal_Type`),
  ADD KEY `idx_campaign_admin` (`Admin_ID`);

--
-- 表的索引 `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`Donation_ID`),
  ADD UNIQUE KEY `Transaction_ID` (`Transaction_ID`),
  ADD KEY `idx_donations_campaign` (`Campaign_ID`),
  ADD KEY `idx_donations_donor` (`Donors_ID`),
  ADD KEY `idx_donations_status` (`Status`);

--
-- 表的索引 `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`Donors_ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `idx_donors_email` (`Email`);

--
-- 表的索引 `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`Report_ID`),
  ADD KEY `idx_report_campaign` (`Campaign_ID`),
  ADD KEY `idx_report_admin` (`Admin_ID`);

--
-- 表的索引 `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`Request_ID`),
  ADD KEY `idx_requests_donor` (`Donors_ID`),
  ADD KEY `idx_requests_campaign` (`Campaign_ID`);

--
-- 表的索引 `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `idx_logs_admin` (`Admin_ID`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `Admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `campaign`
--
ALTER TABLE `campaign`
  MODIFY `Campaign_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `donations`
--
ALTER TABLE `donations`
  MODIFY `Donation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `donors`
--
ALTER TABLE `donors`
  MODIFY `Donors_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `reports`
--
ALTER TABLE `reports`
  MODIFY `Report_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `requests`
--
ALTER TABLE `requests`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 限制导出的表
--

--
-- 限制表 `campaign`
--
ALTER TABLE `campaign`
  ADD CONSTRAINT `campaign_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`) ON DELETE SET NULL;

--
-- 限制表 `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`Donors_ID`) REFERENCES `donors` (`Donors_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`) ON DELETE CASCADE;

--
-- 限制表 `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`) ON DELETE CASCADE;

--
-- 限制表 `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`Donors_ID`) REFERENCES `donors` (`Donors_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`Campaign_ID`) REFERENCES `campaign` (`Campaign_ID`) ON DELETE SET NULL;

--
-- 限制表 `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
