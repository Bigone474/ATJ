-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2024 at 03:52 PM
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
-- Database: `atjsytem_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `created_at`) VALUES
(36, 4, 'ให้สิทธิแอดมินแก่ผู้ใช้', '2024-10-04 03:33:10'),
(37, 4, 'ให้สิทธิแอดมินแก่ผู้ใช้', '2024-10-04 03:41:15'),
(38, 3, 'ให้สิทธิแอดมินแก่ผู้ใช้', '2024-10-04 05:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`) VALUES
(403, 60, 8, 'จริงครับ แมนยูทุกวันนี้นึกว่าบอล อบต. แถวบ้าน', '2024-10-03 23:09:40'),
(404, 60, 3, 'ใช่ทีมที่แพ้ 3-0 ไหมครับ', '2024-10-03 23:14:52'),
(405, 61, 9, 'ชิลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลลล4', '2024-10-03 23:21:44'),
(406, 58, 3, 'หมูเด้งงงงงง', '2024-10-03 23:22:44'),
(407, 62, 10, 'อย่าๆ คนล้มอย่าซ้ํา ครับคนไทยด้วยกัน', '2024-10-03 23:25:47'),
(408, 62, 11, 'จ่าฝูแดนใต้หรือเปล่าเนี้ย', '2024-10-03 23:32:31'),
(409, 58, 11, 'อยากกินซอยจุ๊หมูเด้ง', '2024-10-03 23:33:45'),
(410, 57, 11, 'รักษาสุขภาพกันด้วยนะครับ', '2024-10-03 23:34:06'),
(411, 66, 13, 'ทีมกากเกินครับ', '2024-10-03 23:42:10'),
(412, 75, 14, '150 น่าจะได้แต่กริปนะครับพี่', '2024-10-03 23:55:58'),
(413, 66, 15, 'จริงครับ', '2024-10-03 23:58:26'),
(414, 61, 16, 'เล่นไม่ยากมือใหม่ก็เล่นได้', '2024-10-04 00:39:19'),
(416, 60, 4, 'ไอ่โล้นนนนนนนนนนนนนน~~~~~~', '2024-10-04 03:36:16'),
(417, 79, 4, '😊😊😊', '2024-10-04 03:42:17'),
(418, 70, 4, '🎧🎧🎧', '2024-10-04 03:42:56'),
(419, 58, 4, '😍😍😍', '2024-10-04 03:44:07'),
(420, 75, 8, 'เงินดิจิตอลออกซื้อไป 4000 บาท 😂😂😂🤣', '2024-10-04 04:05:32'),
(421, 79, 4, 'ทดสอบ', '2024-10-04 04:50:42');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(114, 3, 60, '2024-10-03 23:13:08'),
(115, 3, 59, '2024-10-03 23:14:58'),
(116, 3, 58, '2024-10-03 23:15:01'),
(118, 11, 63, '2024-10-03 23:31:29'),
(119, 11, 62, '2024-10-03 23:31:31'),
(120, 11, 61, '2024-10-03 23:32:54'),
(121, 11, 60, '2024-10-03 23:32:56'),
(122, 11, 59, '2024-10-03 23:32:58'),
(123, 11, 58, '2024-10-03 23:33:00'),
(125, 10, 63, '2024-10-03 23:33:20'),
(126, 10, 62, '2024-10-03 23:33:21'),
(127, 10, 61, '2024-10-03 23:33:22'),
(128, 10, 60, '2024-10-03 23:33:24'),
(129, 11, 57, '2024-10-03 23:33:49'),
(130, 10, 66, '2024-10-03 23:38:14'),
(131, 13, 66, '2024-10-03 23:41:48'),
(132, 14, 75, '2024-10-03 23:57:08'),
(133, 4, 62, '2024-10-04 03:43:37'),
(135, 8, 78, '2024-10-04 04:04:04'),
(137, 3, 79, '2024-10-04 05:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `login_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `is_admin`, `login_time`) VALUES
(2, 3, 0, '2024-10-01 01:43:53'),
(3, 3, 0, '2024-10-01 10:59:50'),
(4, 3, 0, '2024-10-02 00:19:20'),
(5, 4, 1, '2024-10-02 00:24:26'),
(7, 6, 0, '2024-10-02 02:09:39'),
(8, 4, 1, '2024-10-02 02:09:50'),
(9, 4, 0, '2024-10-02 02:30:24'),
(10, 4, 0, '2024-10-02 02:32:15'),
(11, 4, 1, '2024-10-02 02:33:21'),
(12, 4, 1, '2024-10-02 02:36:32'),
(13, 6, 0, '2024-10-02 02:36:43'),
(14, 4, 1, '2024-10-02 02:36:59'),
(15, 3, 2, '2024-10-02 02:41:56'),
(16, 4, 1, '2024-10-02 13:28:28'),
(17, 3, 2, '2024-10-02 13:29:31'),
(18, 7, 0, '2024-10-02 19:46:10'),
(19, 7, 0, '2024-10-02 20:09:43'),
(20, 4, 1, '2024-10-02 22:21:21'),
(21, 4, 1, '2024-10-04 01:24:42'),
(22, 7, 0, '2024-10-04 01:24:51'),
(23, 4, 1, '2024-10-04 01:26:31'),
(24, 3, 2, '2024-10-04 03:30:56'),
(25, 3, 2, '2024-10-04 03:32:00'),
(26, 8, 0, '2024-10-04 05:24:52'),
(27, 7, 0, '2024-10-04 05:30:29'),
(28, 8, 0, '2024-10-04 05:37:07'),
(29, 3, 2, '2024-10-04 05:45:01'),
(30, 4, 1, '2024-10-04 05:58:43'),
(31, 7, 0, '2024-10-04 06:12:29'),
(32, 3, 2, '2024-10-04 06:12:47'),
(33, 9, 0, '2024-10-04 06:21:25'),
(34, 10, 0, '2024-10-04 06:24:55'),
(35, 11, 0, '2024-10-04 06:25:21'),
(36, 4, 1, '2024-10-04 06:40:06'),
(37, 12, 0, '2024-10-04 06:41:14'),
(38, 13, 0, '2024-10-04 06:41:38'),
(39, 4, 1, '2024-10-04 06:43:47'),
(40, 14, 0, '2024-10-04 06:47:55'),
(41, 15, 0, '2024-10-04 06:48:35'),
(42, 16, 0, '2024-10-04 07:20:51'),
(43, 3, 2, '2024-10-04 07:50:09'),
(44, 3, 2, '2024-10-04 08:48:53'),
(45, 16, 0, '2024-10-04 08:54:45'),
(46, 7, 0, '2024-10-04 08:57:16'),
(47, 16, 0, '2024-10-04 09:02:53'),
(48, 4, 1, '2024-10-04 09:11:38'),
(49, 3, 2, '2024-10-04 10:20:46'),
(50, 8, 0, '2024-10-04 10:21:45'),
(51, 3, 2, '2024-10-04 10:22:17'),
(52, 3, 2, '2024-10-04 10:23:25'),
(53, 4, 1, '2024-10-04 10:23:48'),
(54, 3, 2, '2024-10-04 10:23:52'),
(55, 12, 0, '2024-10-04 10:24:45'),
(56, 8, 0, '2024-10-04 10:27:19'),
(57, 8, 0, '2024-10-04 10:31:01'),
(58, 8, 0, '2024-10-04 10:39:06'),
(59, 8, 0, '2024-10-04 10:40:40'),
(60, 3, 2, '2024-10-04 10:40:56'),
(61, 16, 0, '2024-10-04 10:41:11'),
(62, 9, 2, '2024-10-04 10:41:23'),
(63, 8, 2, '2024-10-04 10:41:37'),
(64, 4, 1, '2024-10-04 10:48:09'),
(65, 3, 1, '2024-10-04 11:10:25'),
(66, 9, 2, '2024-10-04 11:26:13'),
(67, 3, 1, '2024-10-04 11:33:09'),
(68, 4, 1, '2024-10-04 11:45:11'),
(69, 4, 1, '2024-10-04 11:51:18'),
(70, 4, 1, '2024-10-04 12:09:02'),
(71, 4, 1, '2024-10-04 12:17:42'),
(72, 3, 1, '2024-10-04 12:22:19'),
(73, 3, 1, '2024-10-04 12:25:31'),
(74, 7, 0, '2024-10-04 12:29:00'),
(75, 7, 0, '2024-10-04 12:29:00'),
(76, 17, 0, '2024-10-04 12:42:15'),
(77, 3, 1, '2024-10-04 13:15:19'),
(78, 4, 1, '2024-10-12 20:43:34');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(50) NOT NULL,
  `subcategory` varchar(50) DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `views_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `category`, `subcategory`, `image_url`, `created_at`, `updated_at`, `views_count`) VALUES
(57, 3, 'มะเร็งกล้ามเนื้อหัวใจ เกิดได้ยาก แต่อันตรายถึงชีวิต', 'หัวใจ อวัยวะที่สำคัญที่สุดในระบบไหลเวียนเลือด หัวใจจะสูบฉีดเลือดที่รับออกซิเจนจากปอดไปเลี้ยงเซลล์ต่างๆ ของร่างกาย แต่เมื่อพูดถึงโรคมะเร็ง หลายคนคงอาจไม่ทราบว่าหัวใจก็สามารถเป็นที่เกิดของมะเร็งได้เช่นกัน แม้ว่าโรคมะเร็งกล้ามเนื้อหัวใจจะเป็นโรคที่พบได้น้อยมาก แต่ถ้าได้เป็นแล้วก็อันตรายถึงชีวิต\r\n\r\nโรคมะเร็งกล้ามเนื้อหัวใจ\r\nโรคมะเร็งกล้ามเนื้อหัวใจ หรือ “Primary Cardiac Tumor” เป็นโรคที่พบได้ยากมากเมื่อเทียบกับมะเร็งชนิดอื่นๆ ที่เกิดขึ้นในร่างกาย เนื่องจากกล้ามเนื้อหัวใจมีเซลล์ที่มีลักษณะพิเศษที่ทำให้มีความเสี่ยงต่อการเกิดมะเร็งต่ำ อย่างไรก็ตาม เมื่อเกิดขึ้นแล้ว โรคมะเร็งชนิดนี้จะส่งผลกระทบอย่างมากต่อสุขภาพและการทำงานของหัวใจโดยรวม', 'ประชาสัมพันธ์', NULL, 'https://img-s-msn-com.akamaized.net/tenant/amp/entityid/AA1r9Vx0.img?w=768&h=432&m=6', '2024-10-03 22:50:02', '2024-10-03 22:50:02', 0),
(58, 3, 'เรื่องนี้มีเหตุผลทางวิทยาศาสตร์ ว่าทำไมเราจึงถูก \"หมูเด้ง\" ตกเข้าด้อม', 'น่ารักจนเกินห้ามใจ ทำไมบางคนถึงแสดงออกกับ \"หมูเด้ง\" อย่างรุนแรง\r\n\r\nจากกระแสความน่ารัก ทำให้นักท่องเที่ยวหลั่งไหลเข้าไปเยี่ยมเยือนหมูเด้งถึงริมรั้วกันอย่างเนืองแน่น และยังมีข่าวว่าบางคนตะโกนส่งเสียงดัง เทน้ำ หรือปาเปลือกหอยใส่ เพื่อให้หมูเด้งตื่น หรือเดินออกมาโชว์ตัว ซึ่งพฤติกรรมนี้ทำให้หลายคนกังวลว่าจะทำให้หมูเด้งเกิดความกังวลใจ เกิดความเครียดได้\r\n\r\n\"ความน่ารักของทารกไม่ว่าจะเป็นมนุษย์หรือฮิปโปแคระสามารถกระตุ้นให้เกิดปฏิกิริยาที่รุนแรงในตัวเราได้ ในการศึกษาทางวิทยาศาสตร์จิตวิทยาในปี 2015 พบว่าผู้ที่มีความรู้สึกเชิงบวกต่อภาพเด็กทารกที่น่ารัก อาจมีการแสดงออกถึงความรู้สึกที่ก้าวร้าวมากขึ้น เช่น ต้องการหยิกแก้มของทารก โดยไม่มีเจตนาที่จะทำร้ายจริงๆ หรือเรียกอีกอย่างว่า ความก้าวร้าวที่น่ารัก\" โอเรียนา อารากอน นักจิตวิทยาสังคม อธิบาย\r\n\r\nสำหรับการแสดงออกแบบนี้ถ้าพูดกันแบบไทยๆ คงคล้ายกับอารมณ์ \"หมั่นเขี้ยว\" นั่นเอง ทำให้หลายคนที่ไปดูหมูเด้ง จึงต้องการจะทำบางอย่างเพื่อให้ตนเองได้เข้าใกล้ ได้ชมความน่ารัก จนกลายเป็นสิ่งที่ไปก่อกวนสัตว์แบบไม่รู้ตัว ซึ่งเป็นพฤติกรรมที่ไม่เหมาะสมและควรหลีกเลี่ยง', 'ประชาสัมพันธ์', NULL, 'https://img-s-msn-com.akamaized.net/tenant/amp/entityid/AA1r1Fg6.img?w=768&h=576&m=6', '2024-10-03 22:51:18', '2024-10-03 22:51:18', 0),
(59, 8, 'อาหาร 5 หมู่มีอะไรบ้าง', 'อาหารหลัก 5 หมู่มีอะไรบ้าง ที่ควรทานในแต่ละวัน\r\nอาหารหลักหมู่ที่ 1 โปรตีน ( เนื้อสัตว์ ไข่ นม ถั่ว )\r\nอาหารหลักหมู่ที่ 2 คาร์โบไฮเดรต ( ข้าว แป้ง น้ำตาล เผือก มัน )\r\nอาหารหลักหมู่ที่ 3 เกลือแร่ และแร่ธาตุต่าง ๆ ( พืชผัก )\r\nอาหารหลักหมู่ที่ 4 วิตามิน ( ผลไม้ )\r\nอาหารหลักหมู่ที่ 5 ไขมัน ( ไขมันจากพืชและสัตว์ )', 'lifestyle', 'อาหาร', 'uploads/66ff22c671726_thong1.jpg', '2024-10-03 23:03:34', '2024-10-03 23:03:34', 0),
(60, 4, 'มาแล้ว เทน ฮาก งัดข้ออ้างล่าสุด ชี้สาเหตุแมนยูฯ ผลงานแย่ในช่วงต้นฤดูกาลนี้ ทำส่อแววตกงานก่อนเกมยูโรปาลีก', 'วันที่ 3 ตุลาคม 2567 เอริก เทน ฮาก ผู้จัดการทีมแมนเชสเตอร์ ยูไนเต็ด อันดับ 13 พรีเมียร์ลีก อังกฤษ ให้สัมภาษณ์ถึงสาเหตุที่ทีมมีผลงานแย่ในช่วงออกสตาร์ตฤดูกาลนี้ ก่อนไปเยือน ปอร์โต รองจ่าฝูงลีกโปรตุเกส ในศึกยูโรปาลีก รอบลีกเฟส นัดที่ 2 คืนนี้    \r\n\r\nแมนยูฯ ภายใต้การคุมทีมของ เทน ฮาก เก็บได้แค่ 7 แต้มจาก 6 นัดแรกในพรีเมียร์ลีก จนร่วงลงมาอยู่อันดับ 13 พรีเมียร์ลีก และทำให้เขาโดนกระแสกดดันให้ถูกปลดออกจากตำแหน่ง โดยมีรายงานว่า ปิศาจแดง อาจประเมินผลงานอีกแค่ 2 นัด ก่อนตัดสินอนาคต\r\n\r\nด้าน เทน ฮาก ระบุว่าสาเหตุที่ แมนยูฯ มีผลงานน่าผิดหวังในช่วงต้นฤดูกาล เนื่องจากจบสกอร์กันไม่คม หลังยิงในพรีเมียร์ลีกได้แค่ 5 ประตู จาก 6 เกมแรก จากการยิงทั้งหมด 79 ครั้ง พลาดโอกาสทองถึง 17 ครั้ง มากที่สุดเป็นอันดับ 2 ต่อจาก สเปอร์ส (18 ครั้ง)\r\n\r\nกุนซือแมนยูฯ พูดถึงปัญหาเรื่องการจบสกอร์ที่ต้องแก้ไขโดยเร็วว่า ถ้าเราสร้างโอกาสไม่ได้ ผมควรจะสงสัย (ถ้านักเตะทำตามแท็กติกของเขา) แต่เราสร้างโอกาสได้มากมาย แต่ผมคิดว่าเรามีค่าเฉลี่ยในการลุ้นประตูสูงเป็นอันดับ 3 ในพรีเมียร์ลีก', 'sports', 'ฟุตบอล', 'uploads/66ff236d89a73_dFQROr7oWzulq5Fa6rBj2p78f57NggyAe8CfpHggxzbwz6QwndDhkW4GtnYkpF4PUWU.jpg', '2024-10-03 23:06:21', '2024-10-03 23:07:12', 0),
(61, 8, 'เกมนี้เล่นยากไหมคับ', 'อยากเล่นเกมนี้แต่เล่นยิงปืนไม่เก่งอยากรู้ว่ามันเล่นยากไหมคับ', 'games', 'เกมคอมพิวเตอร์', 'uploads/66ff2693c1603_OIP (1).jpg', '2024-10-03 23:19:47', '2024-10-03 23:19:47', 0),
(62, 4, 'ไม่ไหวๆ', 'เป็นอีกครั้งที่แมนยูแพ้ที่ โอแทบฟอร์ด !!!', 'sports', 'ฟุตบอล', 'uploads/66ff277353733_20240930_1pi9oc2p9c_u6djda63s.jpg', '2024-10-03 23:23:31', '2024-10-03 23:23:31', 0),
(63, 11, 'วิจัยมาแล้ว!! เปิดฟัง 10 เพลงนี้ช่วยคลายเครียด', 'หากคุณกำลังอยู่ในภาวะเครียดไม่ว่าจากเรื่องใดก็ตาม ลองมาผ่อนคลายด้วยเสียงเพลงกันครับ เพราะล่าสุดมีผลการวิจัยชี้วัดว่า “ดนตรี” สามารถช่วยคลายความเครียดได้มากถึง 65% \r\n\r\nโดยเมื่อไม่นานมานี้ David Lewis-Hodgson นักประสาทวิทยาชาวอังกฤษได้ทำการวิจัยและค้นพบว่า “เพลง” ช่วยคลายเครียดได้ แต่ทั้งนี้เพลงแต่ละเพลงก็ให้ผลลัพธ์ไม่เท่ากัน และนี่คือ 10 เพลงจากการวิจัยแล้วว่า “ช่วยคลายเครียดได้จริง”\r\n\r\nเพลง Weightlessness โดย Marconi Union\r\nเพลง Electra โดย Airstream\r\nเพลง Mellomaniac (Chill Out Mix) โดย DJ Shah\r\nเพลง Watermark โดย Enya\r\nเพลง Strawberry Swing โดย Coldplay\r\nเพลง Please Don’t Go โดย Barcelona\r\nเพลง Pure Shores โดย All Saints\r\nเพลง Someone Like You โดย Adele\r\nเพลง Canzonnetta Sull’aria โดย Mozart\r\nเพลง We Can Fly โดย Rue du Soleil (Cafe Del Mar) \r\nสำหรับเหตุผลที่ว่าทำไมเสียงเพลงจึงช่วยคลายเครียดได้นั้น เป็นเพราะว่าการประสานของจังหวะและเสียงเบสมีส่วนช่วยให้คนฟังรู้สึกผ่อนคลาย ซึ่งศาสตร์นี้เป็นที่พูดถึงกันมายาวนานในชื่อ ‘Music Therapy’ หรือดนตรีบำบัดนั่นเอง ซึ่งเสียงเพลงสามารถลดความดันโลหิต ชะลอจังหวะการเต้นของหัวใจ ไปจนถึงลดระดับฮอร์โมนในร่างกายได้อีกด้วยครับ\r\n\r\nดังนั้นหากรู้สึกเครียดลองเปิด Playlist 10 เพลงที่แนะนำนี้ หรือจะเปิดคลอระหว่างทำงาน ขับรถ หรืออ่านหนังสือก็ได้นะครับ ', 'music', 'คลาสสิก', 'uploads/66ff2877c919d_Ananda_Dec2021_Content_Music-Chill_01-02.jpg', '2024-10-03 23:27:51', '2024-10-03 23:27:51', 0),
(65, 9, 'นักวิทยาศาสตร์ในสกอตแลนด์เผยว่า ดาวเคราะห์น้อยที่พุ่งชนโลก จนทำให้ไดโนเสาร์สูญพันธุ์นั้น ไม่ได้มาแค่ดวงเดียว แต่มาถึง 2 ดวง', 'นักวิทยาศาสตร์ในสกอตแลนด์เผยว่า ดาวเคราะห์น้อยที่พุ่งชนโลก จนทำให้ไดโนเสาร์สูญพันธุ์นั้น ไม่ได้มาแค่ดวงเดียว แต่มาถึง 2 ดวง\r\n\r\nสำนักข่าวต่างประเทศรายงานว่า กลุ่มนักวิทยาศาสตร์ออกมายืนยันว่า ดาวเคราะห์น้อยขนาดใหญ่ที่พุ่งชนโลกจนทำให้ไดโนเสาร์สูญพันธุ์ไปเมื่อ 66 ล้านปีก่อน ไม่ได้มาแค่ดวงเดียว แต่มีดาวเคราะห์น้อยขนาดเล็กกว่าอีกดวง พุ่งตกลงในทะเลนอกชายฝั่งของแอฟริกาตะวันตกในยุคเดียวกันด้วย จนทำให้เกิดเหตุหลุมขนาดใหญ่ที่ได้รับชื่อว่า “นาเดียร์”\r\n\r\nนักวิทยาศาสตร์ระบุว่า การตกของดาวเคราะห์น้อยดวงที่ 2 คือมหันตภัย มันทำให้เกิดคลื่นยักษ์สึนามิสูงอย่างน้อย 800 เมตร กระจายไปทั่วมหาสมุทรแอตแลนติก\r\n\r\nดร.ยูอิสดีน นิโคลสัน จากมหาวิทยาลัย แฮเรียต-วัตต์ ในสกอตแลนด์ เป็นคนแรกที่พบ “แอ่งนาเดียร์” (Nadir crater) เมื่อปี 2565 ซึ่งตอนนั้นเขายังไม่รู้ว่า มันเกิดขึ้นได้อย่างไร แต่ตอนนี้ ดร.นิโคลสันกับเพื่อนร่วมงานของเขามั่นใจแล้วว่า รอยยุบกว่า 9 กิโลเมตรแห่งนี้ มีสาเหตุจากดาวเคราะห์น้อยพุ่งชนก้นทะเล\r\n\r\nพวกเขายังไม่สามารถระบุเวลาที่แน่นอนได้ว่าเหตุการณ์นี้เกิดขึ้นเมื่อไร หรือว่าดาวเคราะห์น้อยดวงนี้มาก่อนหรือหลังจากการพุ่งชนโลกของดาวเคราะห์น้อยขนาดยักษ์ ซึ่งทำให้เกิด “แอ่งชิกซูลับ” (Chicxulub crater) ขนาด 180 กิโลเมตรในเม็กซิโก และทำให้ยุคสมัยของไดโนเสาร์ต้องจบลง\r\n\r\nอย่างไรก็ตาม ทีมนักวิทยาศาสตร์ระบุว่า ดาวเคราะห์น้อยขนาดเล็กกว่านี้ มาในช่วงสุดท้ายของยุคครีเตเชีนสเช่นเดียวกัน และการตกลงมาของมันจะทำให้มันกลายเป็นลูกไฟขนาดมหึมา\r\n\r\n“ลองจินตนาการว่า ดาวเคราะห์น้อยพุ่งชนที่กลาสโกว์ แล้วคุณอยู่ที่เอดินบะระ ห่างไปประมาณ 50 กม. ลูกไฟนี้จะใหญ่กว่าขนาดของดวงอาทิตย์บนท้องฟ้าถึง 24 เท่า รุนแรงพอทำให้ต้นไม้ในเอดินบะระเกิดไฟลุกไหม้” ดร.นิโคลสันกล่าว และเสริมว่า สิ่งที่ตามมาหลังจากนั้นคือเสียงดังสนั่นหวั่นไหว ก่อนจะเกิดแรงกระแทกเท่าแผ่นดินไหวระดับ 7 แมกนิจูด', 'news', 'ต่างประเทศ', 'uploads/66ff299087390_OIP (2).jpg', '2024-10-03 23:32:32', '2024-10-03 23:32:32', 0),
(66, 10, 'เล่นบอลหรือไปเดินเล่น แรชฟอร์ด', 'ไม่ไหวขออนุญาต เก็บเสื้อไว้ให้ตู้ อายคน', 'sports', 'ฟุตบอล', 'uploads/66ff2a9d6c745_S__67059718.jpg', '2024-10-03 23:37:01', '2024-10-03 23:37:01', 0),
(70, 11, '10 ศิลปิน T-Pop รุ่นใหม่น่าจับตาประจำปี 2024 โดย Spotify RADAR', 'ในช่วงไม่กี่ปีที่ผ่านมา เรียกได้ว่าวงการ T-Pop บ้านเราเติบโตขึ้นอย่างต่อเนื่อง โดยเฉพาะศิลปินหน้าใหม่มากมายที่ปล่อยผลงานเพลงออกมาให้ทุกคนได้ทำความรู้จัก จนส่งให้ชื่อของพวกเขากลายเป็นที่จับตามอง \r\n\r\n \r\n\r\nล่าสุดทาง Spotify ได้เปิดตัวไลน์อัพ 10 ศิลปินไทยหน้าใหม่ที่น่าจับตามองประจำปี 2024 ผ่านโปรแกรม RADAR ที่สนับสนุนศิลปินหน้าใหม่ให้ได้เข้าถึงกลุ่มผู้ฟังใหม่ๆ ทั่วโลก วันนี้ THE STANDARD POP ถือโอกาสรวบรวมรายชื่อ 10 ศิลปินหน้าใหม่จากโปรแกรม RADAR มาให้ทุกคนได้ไปติดตามกัน พร้อมสามารถเข้าไปรับฟังผลงานของพวกเขากันได้ที่เพลย์ลิสต์ RADAR Thailand 2024', 'music', 'ป๊อป', 'uploads/66ff2b2126a44_T-Pop.jpg', '2024-10-03 23:39:13', '2024-10-03 23:39:13', 0),
(75, 14, 'แนะนำไม้แบดหน่อยครับ', 'อยากได้ไม้แบดทุกสายครับวัสดุแข็งแรงทนทาน งบ150', 'sports', 'เทนนิส', 'uploads/66ff2e6e96c2d_images.jpg', '2024-10-03 23:53:18', '2024-10-03 23:53:18', 0),
(77, 14, 'หาเพื่อนร่วมทริปภูพาน', 'หาเพื่อนร่วมทริปภูพานประมาณ 10 คน หาเก็บเห็ดเผาะครับ', 'lifestyle', 'ท่องเที่ยว', 'uploads/66ff330777852_001.jpg', '2024-10-04 00:12:55', '2024-10-04 00:12:55', 0),
(78, 12, 'หุ้นไทยวันนี้ 3 ต.ค. 67 ปิดตลาดหุ้นบ่าย ลดลง 8.67 จุด ดัชนีอยู่ที่ 1,442.73 จุด', 'หุ้นไทยวันนี้ ปิดตลาดหุ้นบ่าย ลดลง 8.67 จุด ดัชนีอยู่ที่ 1,442.73 จุด มูลค่าการซื้อขาย 57,538.87 ล้านบาท\r\n\r\n\r\nการเคลื่อนไหวของตลาดหลักทรัพย์แห่งประเทศไทย หรือหุ้นไทยวันนี้ ประจำวันที่ 3 ต.ค. 67 ครึ่งวันบ่าย พบว่า ดัชนีลดลง 8.67 จุด เปลี่ยนแปลง -0.60% ดัชนีอยู่ที่ 1,442.73 จุด มูลค่าการซื้อขาย 57,538.87 ล้านบาท\r\n\r\n\r\nทั้งนี้ สำหรับหลักทรัพย์ที่มีมูลค่าการซื้อขาย 5 อันดับแรก ได้แก่\r\n\r\nบริษัท อินทัช โฮลดิ้งส์ จำกัด (มหาชน) หรือหุ้น INTUCH\r\nบริษัท กรุงเทพดุสิตเวชการ จำกัด(มหาชน) หรือหุ้น BDMS\r\nบริษัท ซีพี ออลล์ จำกัด (มหาชน) หรือหุ้น CPALL\r\nบริษัท ปตท.สำรวจและผลิตปิโตรเลียม จำกัด (มหาชน) หรือหุ้น PTTEP\r\nบริษัท ท่าอากาศยานไทย จำกัด (มหาชน) หรือหุ้น AOT', 'news', 'เศรษฐกิจ', 'uploads/66ff344b0a253_dFQROr7oWzulq5Fa6rBj2qYW9l4wOLnMflHg9MXVtXqsWcB6CYQ2jnLWqEIrJh1Kf8f.jpg', '2024-10-04 00:18:19', '2024-10-04 00:18:19', 0),
(79, 16, 'Solo Leveling: ARISE จับมือกับ AIS ให้คุณแลกคะแนน 1 คะแนนเพื่้อใช้กับไอเท็มเกมสุดฮิต', 'Solo Leveling: ARISE เกมแอ็กชันสุดมันส์จากค่ายเน็ตมาร์เบิ้ล จับมือร่วมกับ AIS มอบสิทธิสุดพิเศษเอาใจเหล่าฮันเตอร์ลูกค้า AIS  เพียงแลก เอไอเอส พอยท์ แค่ 1 คะแนน ผ่านแอปพลิเคชัน myAIS รับไอเทมเกม Solo Leveling: ARISE อย่าง หินเวทสกัด x500 และ โกลด์ x25,000 สุดคุ้มไปได้เลย !  สามารถดูรายละเอียดเพิ่มเติมได้ที่ >> AIS  อย่ารอช้า สิทธิ์มีจำนวนจำกัด เหล่าฮันเตอร์ไม่ควรพลาด สามารถร่วมสนุกและคว้าสิทธิสุดพิเศษได้แล้วตั้งแต่วันที่ 1 - 31 ก.ค. 2024 เท่านั้น หรือจนกว่าสิทธิ์จะหมด\r\n\r\nวิธีการรับโค้ดไอเทม\r\n1.เข้าไปที่แอปพลิเคชัน myAIS > เมนูสิทธิพิเศษ > บันเทิง\r\n2.กดแลกคะแนนเพื่อรับสิทธิ์ไอเทมฟรี เกม Solo Leveling:ARISE\r\n3.คัดลอกรหัสโค้ดที่ได้รับไปกรอกในเกมเพื่อรับรางวัล\r\nวิธีการเติมโค้ดไอเทม\r\n1.เข้าเกม > เลือก [ออปชัน] > [ตั้งค่าบัญชี] > [กรอกโค้ด] > กรอกโค้ดไอเทม [สำหรับผู้ใช้งานอุปกรณ์ iOS จะต้องเติมโค้ดไอเทมผ่านเว็บไซต์ >> https://coupon.netmarble.com/sololv พร้อมกรอกหมายเลขสมาชิกและโค้ดไอเทม (สามารถตรวจสอบหมายเลขสมาชิกได้ที่ [ออปชัน] > [ตั้งค่าบัญชี] > คัดลอกข้อมูลบัญชี)]\r\n2.ตรวจสอบและรับรางวัลผ่านทางกล่องจดหมาย\r\nข้อกำหนดและเงื่อนไข\r\n1.ระยะเวลาร่วมกิจกรรมตั้งแต่ 01 กรกฎาคม 2567 - 31 กรกฎาคม 2567 เท่านั้น\r\n2.ผู้เล่นสามารถเติมโค้ดไอเทมได้ถึง 31 กรกฎาคม 2567 เวลา 23 : 59 น.\r\n3.จำกัดจำนวน 4,000 สิทธิ์ ตลอดระยะเวลาแคมเปญ (1 โค้ด / 1 หมายเลข / 1 ครั้ง)\r\n4.สามารถเติมโค้ดไอเทมได้ทั้งระบบ AOS , iOS และ PC\r\n5.สำหรับลูกค้า iOS สามารถกรอกโค้ดไอเทมได้ที่ >> https://coupon.netmarble.com/sololv\r\n6.สงวนสิทธิ์ในการเปลี่ยนแปลงเงื่อนไข โดยไม่ต้องแจ้งให้ทราบล่วงหน้า และไม่สามารถใช้ร่วมกับรายการส่งเสริมการขาย หรือส่วนลดอื่นๆได้\r\n7.สิทธิพิเศษนี้ไม่สามารถเปลี่ยนแปลงเป็นเงินสดได้\r\n8.กิจกรรมนี้เป็นกิจกรรมที่จัดขึ้นในประเทศไทยเท่านั้น\r\n \r\n\r\nSolo Leveling: ARISE เป็นเกมแนวแอ็กชันที่ดัดแปลงมาจาก Solo Leveling เว็บตูนยอดนิยม สวมบทบาทเป็น ซองจินอู ผู้เล่นจะได้สัมผัสการเลเวลอัปของเขาและมุ่งหน้าเผชิญความท้าทายผ่านเรื่องราวอนิเมะอันเป็นที่โปรดปรานได้โดยตรง วัดฝีมือไปกับการต่อสู้อันทรงพลังและสร้างสไตล์การต่อสู้เป็นของตนเองโดยอาศัยหลากหลายสกิลและอาวุธต่าง ๆ เข้าด้วยกัน ผู้เล่นไม่เพียงแต่ที่จะได้ร่วมทีมกับเหล่าฮันเตอร์จากเว็บตูนเท่านั้น แต่ผู้เล่นยังสามารถสัมผัสประสบการณ์การอัญเชิญ ‘กองทัพทหารเงา’ ของตนเองได้เหมือนกับที่ซองจินอูทำเมื่อเขาเปล่งคำพูดเด็ดอันโด่งดังอย่าง “จงตื่น…” เป็นครั้งแรกในเว็บตูนอีกด้วย', 'games', 'เกมมือถือ', 'uploads/66ff38d551cad_slaxaisphase2_1920x1080.jpg', '2024-10-04 00:37:41', '2024-10-04 00:37:41', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `report_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `post_id`, `user_id`, `reason`, `report_time`) VALUES
(13, 59, 4, 'gkk', '2024-10-04 06:19:53'),
(14, 70, 10, 'โพส์ตเยอะเกินๆ', '2024-10-04 06:39:41'),
(16, 66, 8, 'w,jgs,ktl,', '2024-10-04 10:31:28'),
(17, 66, 8, 'ไม่เหมาะสม', '2024-10-04 10:31:55'),
(18, 66, 8, '0-7 แม่มึงอะ', '2024-10-04 11:25:16'),
(19, 66, 9, 'แอดมินลบให้หน่อยครับเห็นแล้วมันแทงใจแฮง', '2024-10-04 11:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category`, `name`, `created_at`) VALUES
(1, 'sports', 'ฟุตบอล', '2024-09-30 20:52:30'),
(2, 'sports', 'บาสเกตบอล', '2024-09-30 20:52:30'),
(3, 'sports', 'วอลเลย์บอล', '2024-09-30 20:52:30'),
(4, 'sports', 'เทนนิส', '2024-09-30 20:52:30'),
(5, 'sports', 'กอล์ฟ', '2024-09-30 20:52:30'),
(6, 'sports', 'มวย', '2024-09-30 20:52:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT 'default.png',
  `about` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `firstname`, `lastname`, `email`, `password`, `phone`, `created_at`, `is_admin`, `profile_image`, `about`) VALUES
(3, 'jo', 'รัเกียรติ', 'โพธิ์ศรี', 'rakkiadphosi@gmail.com', '$2y$10$nQYmqOOCCvJ1DJnwgro5reJQoJ43iltKsvlwSBE3G8wKCnVZvJ2zC', '0639076435', '2024-09-30 18:43:18', 1, 'uploads/66feff1290f78_9EFBDED4-BBDF-4414-81DC-CA6F211FE5D6.jpeg', NULL),
(4, 'อานนท์', 'Phuwarin', 'Seevarom', 'admin@a.ad', '$2y$10$xD.08jDIsi5vL17LmP/76OjWbOljCXEnFEGFIiywSfsMRGJ.rcLCa', '-', '2024-10-01 17:22:43', 1, 'uploads/66fee1a07ca5b_Screenshot 2024-07-20 223744.png', NULL),
(6, 'KOKO', 'ฟหกฟไก', 'ฟไกฟไกฟไก', 'poomir111@gmail.com', '$2y$10$nLdOp7f9Rm4hIsWyl2eILOHX2bpn/IxZ2174xdAZlQHH1qaHf7/UO', '-', '2024-10-01 19:09:34', 0, 'default.png', NULL),
(7, 'rakkiad', 'rakkiad', 'phosee', 'rakkiad.ph@rmuti.ac.th', '$2y$10$ohIs7eBFB6CJTK/gAGF8nuPdhQ1L59Qfn47crEjIDXqgv6WRXffMu', '0636281918', '2024-10-02 12:46:01', 0, 'uploads/66fee1a974c2d_9EFBDED4-BBDF-4414-81DC-CA6F211FE5D6.jpeg', NULL),
(8, 'geenus', 'geenus', 'booso', 'geenus@gmail.com', '$2y$10$U99G98eFx8ey3t1i96wyjO2jyYnWYwRP3iEyHZWfOkosdjkZcuY7C', '2', '2024-10-03 22:24:46', 2, 'uploads/66ff19e0647d3_images.jpg', NULL),
(9, 'nikorus', 'niro', 'kooki', 'niro@gmail.com', '$2y$10$0Z85Jq4.GxkE0v3N0FKZ3uZvFayqxXXI9CJ1/5i8fu/4mobw3osFi', '-', '2024-10-03 23:21:18', 2, 'uploads/66ff6faa2b6b9_dFQROr7oWzulq5Fa6rBj2dYgboqKIcf6T2JhCydR4PUX1pT1q12lv9nXShPYm1RmkxJ.webp', NULL),
(10, 'ส้มผัก', 'asdawd', 'asdasdad', 'asd@ad.sa', '$2y$10$r8y1TJp/hKDoRPa.kxn14e2w9WfXtwefcTMGRlJqf64vDq9/iW3/.', '-', '2024-10-03 23:24:50', 0, 'uploads/66ff2b1722f54_pngtree-corgi-cartoon-png-image_2521598.jpg', NULL),
(11, 'ง่วงนอน', 'ง่วงนอน', 'บ่ส่วง', 'ddd123@gmail.com', '$2y$10$RtQwGcJSIG8Nz3Tf2/squ.PE0YmnA5e8LuiTTzdL3zgAa4mcyoRUi', '-', '2024-10-03 23:25:11', 0, 'uploads/profile_images/66ff27d75376b.jpeg', NULL),
(12, 'คุณหนูบ๊อง', 'ฟกฟหกฟหก', 'หกดหกดหกด', 'asdf@gmail.com', '$2y$10$6xhnn3QckAIzo2eSEUhEgOyZnJdyXLGo7oGCfX7y.xKkJQNMoUiEm', '-', '2024-10-03 23:41:08', 0, 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png', NULL),
(13, 'กรแปด', 'klsadjfl', 'asdoasdo', 'qwe@as.a', '$2y$10$R3c6dbzYb6IqMioh2l8NT.VL9JhUz/8ml4DYEiRq/f9GlVFoM4uqa', '-', '2024-10-03 23:41:34', 0, 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png', NULL),
(14, 'เสี่ยโอ', 'สนสน', 'สนสน', 'oo112@gmail.com', '$2y$10$IZ4VjcrqRw2zWUWegwkV1OxPIt27zrHhPwTEF.IlXX.VAfSH3VPjy', '-', '2024-10-03 23:47:42', 0, 'uploads/profile_images/66ff2d1ec08de.jpg', NULL),
(15, 'โลกนี้มีแต่ฉัน', 'ฟหกฟ', 'ฟหกฟหก', 'qw22@qw.s', '$2y$10$Mb2nuiQNa/vxoH09/fxOnOvGYAtQBCGJXDMRalyge1fzDNeWcG4Ha', '-', '2024-10-03 23:48:30', 0, 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png', NULL),
(16, 'gamerตัวจริง', 'adadasda', 'asdasdada', 'game@gmail.com', '$2y$10$3lW9AmJqtGiavP8BJMmlc.Q29TYaC.cU2e.qp7jBfuYoGFQSClSFO', '-', '2024-10-04 00:20:46', 2, 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png', NULL),
(17, 'dsfsdfsf', 'dsfdsfdsf', 'dsfsdfdsf', 'asdfg@gmail.com', '$2y$10$yMWbuEeWOSy/2tCut57rIulOGH/JLQARSLI8wOJpumMnDnq.3aq2m', '484848', '2024-10-04 05:42:01', 0, 'http://26.120.114.83/test/ATJ/uploads/profile_images/default.png', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=422;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`);

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
