-- --------------------------------------------------------
-- Máy chủ:                      127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Phiên bản:           12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for my_store
CREATE DATABASE IF NOT EXISTS `my_store` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `my_store`;

-- Dumping structure for table my_store.account
CREATE TABLE IF NOT EXISTS `account` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','locked') NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `remember_expires_at` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires_at` datetime DEFAULT NULL,
  `email_verify_token` varchar(255) DEFAULT NULL,
  `email_verify_expires_at` datetime DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `uq_account_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.account: ~4 rows (approximately)
INSERT INTO `account` (`id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `avatar`, `remember_token`, `remember_expires_at`, `reset_token`, `reset_expires_at`, `email_verify_token`, `email_verify_expires_at`, `email_verified_at`, `created_at`) VALUES
	(1, 'user1', 'Nguyễn Hoài Trung', 'user1@gmail.com', '$2y$10$b0kE1xonN1sOWt/TC8St/ezRM6GymFWxjDcMsqW5L7mHm5qyifHW.', 'user', 'active', 'uploads/avatars/avatar_1_1779633062.jpg', NULL, NULL, 'f734d21a4fc20753c50d948a701d84be31ff710aca1505fcff7c76e8830efd90', '2026-06-01 11:20:50', NULL, NULL, '2026-06-01 10:44:55', '2026-06-01 01:37:15'),
	(2, 'admin', 'NTT', 'admin@gmail.com', '$2y$10$5swG6r01qgzPVhkjaWXwK.2hIkkcY7Y8uuWbwKXLcmYhsnwSe/1UO', 'admin', 'active', 'uploads/avatars/avatar_2_1780290709.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 10:46:39', '2026-06-01 01:37:15'),
	(3, 'user2', 'user2', 'user2@gmail.com', '$2y$10$ot5DajYEUbeHxXVV.Qv3se.SPCKqMO9Ms7iye.7X4OCG/M0ZdtNPa', 'user', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-01 09:24:41', '2026-06-01 02:24:21'),
	(4, 'user3', 'user3', 'user3@gmail.com', '$2y$10$QEreu63Oiv2GeJDpABFgReC8jkIF9w6fSnQ8C.Ehtfld004ZcwZPS', 'user', 'active', NULL, NULL, NULL, 'a312f2f82dcf2af70e759a9bfa2c3af273133982e8729e041a52d62f4ffb0047', '2026-06-07 02:37:09', NULL, NULL, '2026-06-01 10:51:50', '2026-06-01 03:51:47'),
	(5, 'Quaichun', 'Nguyễn Hoài Trung', 'user4@gmail.com', '$2y$10$sXlXpPBMJJA03asfTHM4guXOdtnVYnnnhKGW.vCvEfAWsvvkkN4KC', 'user', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-07 02:08:43', '2026-06-06 19:08:32');

-- Dumping structure for table my_store.category
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.category: ~5 rows (approximately)
INSERT INTO `category` (`id`, `name`, `description`) VALUES
	(1, 'Điện thoại', 'Danh mục các loại điện thoại'),
	(2, 'Laptop', 'Danh mục các loại laptop'),
	(3, 'Máy tính bảng', 'Danh mục các loại máy tính bảng'),
	(4, 'Phụ kiện', 'Danh mục phụ kiện điện tử'),
	(5, 'Thiết bị âm thanh', 'Danh mục loa, tai nghe, micro');

-- Dumping structure for table my_store.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `address` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.orders: ~3 rows (approximately)
INSERT INTO `orders` (`id`, `name`, `phone`, `email`, `payment_method`, `address`, `status`, `created_at`, `user_id`) VALUES
	(1, 'Nguyễn Hoài Trung', '0125698764', NULL, 'cod', 'Thủ đức', 'delivered', '2026-05-23 14:26:09', NULL),
	(2, 'Nguyễn Hoài Trung', '0125698764', 'tn@gmail.com', 'cod', 'Thu Duc', 'delivered', '2026-05-31 17:21:19', 1),
	(3, 'Nguyen Van 1', '0216598745', 'user1@gmail.com', 'cod', 'Thu Duc', 'shipping', '2026-06-01 03:45:19', 1);

-- Dumping structure for table my_store.order_details
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.order_details: ~5 rows (approximately)
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
	(1, 1, 3, 1, 20000000.00),
	(2, 1, 5, 1, 41000000.00),
	(3, 1, 8, 1, 27000000.00),
	(4, 2, 5, 1, 41000000.00),
	(5, 3, 5, 1, 41000000.00);

-- Dumping structure for table my_store.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.product: ~17 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`) VALUES
	(2, 'IPhone 15 pro max', 'Điện thoại cao cấp của Apple với chip A17 Pro mạnh mẽ, camera chuyên nghiệp và màn hình Super Retina XDR 6.7 inch.', 28000000.00, 'uploads/iphone15pr.jpg', 1),
	(3, 'MacBook Pro M3', 'Laptop Apple hiệu năng cao với chip M3, màn hình Liquid Retina XDR sắc nét và thời lượng pin ấn tượng.', 20000000.00, 'uploads/macbook.jpg', 2),
	(5, 'Samsung Galaxy Z Fold 6', 'Điện thoại màn hình gập cao cấp Samsung', 41000000.00, 'uploads/Samsung Galaxy Z Fold 6(1).JPG', 1),
	(7, 'Lenovo Legion 5', 'Laptop gaming cấu hình cao', 32000000.00, 'uploads/Lenovo Legion 5.JPG', 2),
	(8, 'MacBook Air M2', 'Laptop mỏng nhẹ dành cho học tập và làm việc', 27000000.00, 'uploads/MacBook Air M2.JPG', 2),
	(9, 'iPad Pro 11', 'Máy tính bảng Apple màn hình Liquid Retina', 26000000.00, 'uploads/iPad Pro 11.JPG', 3),
	(10, 'Tai nghe Sony WH-1000XM5', 'Tai nghe chống ồn cao cấp Sony', 8900000.00, 'uploads/Tai nghe Sony WH-1000XM5.JPG', 5),
	(12, 'iPhone 16 Pro', 'Điện thoại Apple hiệu năng mạnh mẽ với chip A18 Pro', 35000000.00, 'uploads/iPhone16Pro.jpg', 1),
	(13, 'Samsung Galaxy S25 Ultra', 'Flagship Samsung với camera zoom vượt trội', 38000000.00, 'uploads/GalaxyS25Ultra.jpg', 1),
	(14, 'Xiaomi 15 Ultra', 'Điện thoại cao cấp Xiaomi chụp ảnh chuyên nghiệp', 29000000.00, 'uploads/Xiaomi15Ultra.jpg', 1),
	(15, 'ASUS ROG Strix G18', 'Laptop gaming màn hình lớn hiệu năng cực cao', 42000000.00, 'uploads/ROGStrixG18.jpg', 2),
	(16, 'Dell XPS 15', 'Laptop cao cấp dành cho doanh nhân và lập trình viên', 36000000.00, 'uploads/DellXPS15.jpg', 2),
	(17, 'HP Spectre x360', 'Laptop cảm ứng xoay gập sang trọng', 34000000.00, 'uploads/HPSpectrex360.jpg', 2),
	(18, 'iPad Air M3', 'Máy tính bảng Apple mỏng nhẹ cho học tập và giải trí', 22000000.00, 'uploads/iPadAirM3.jpg', 3),
	(19, 'Samsung Galaxy Tab S10', 'Máy tính bảng Android cao cấp màn hình AMOLED', 21000000.00, 'uploads/GalaxyTabS10.jpg', 3),
	(20, 'AirPods Pro 2', 'Tai nghe không dây chống ồn chủ động của Apple', 6500000.00, 'uploads/AirPodsPro2.jpg', 5),
	(21, 'Logitech MX Master 3S', 'Chuột không dây cao cấp dành cho dân văn phòng', 2800000.00, 'uploads/MXMaster3S.jpg', 5);

-- Dumping structure for table my_store.product_specs
CREATE TABLE IF NOT EXISTS `product_specs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `spec_name` varchar(255) NOT NULL,
  `spec_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_specs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.product_specs: ~7 rows (approximately)
INSERT INTO `product_specs` (`id`, `product_id`, `spec_name`, `spec_value`) VALUES
	(13, 2, 'Màn hình', '6.7 inch, LTPO Super Retina XDR OLED'),
	(14, 2, 'Chipset', 'Apple A17 Pro (3 nm)'),
	(15, 2, 'RAM', '8 GB'),
	(16, 2, 'Bộ nhớ trong', '256 GB / 512 GB / 1 TB'),
	(17, 2, 'Camera sau', '48 MP + 12 MP + 12 MP'),
	(18, 2, 'Pin', '4441 mAh'),
	(20, 3, 'CPU', 'Apple M3 Pro'),
	(21, 3, 'RAM', '18 GB'),
	(22, 3, 'SSD', '512 GB SSD'),
	(23, 3, 'Màn hình', '14.2 inch Liquid Retina XDR'),
	(24, 3, 'Pin', '22 giờ sử dụng'),
	(25, 3, 'Hệ điều hành', 'macOS'),
	(26, 3, 'CPU', 'Apple M3 Pro'),
	(27, 3, 'RAM', '18 GB'),
	(28, 3, 'SSD', '512 GB SSD'),
	(29, 3, 'Màn hình', '14.2 inch Liquid Retina XDR'),
	(30, 3, 'Pin', '22 giờ sử dụng'),
	(31, 3, 'Hệ điều hành', 'macOS'),
	(32, 5, 'Màn hình', '7.6 inch Dynamic AMOLED 2X'),
	(33, 5, 'Chipset', 'Snapdragon 8 Gen 3'),
	(34, 5, 'RAM', '12 GB'),
	(35, 5, 'Bộ nhớ trong', '256 GB / 512 GB / 1 TB'),
	(36, 5, 'Camera sau', '50 MP + 12 MP + 10 MP'),
	(37, 5, 'Pin', '4400 mAh'),
	(38, 7, 'CPU', 'AMD Ryzen 7 8845HS'),
	(39, 7, 'GPU', 'RTX 4060 8GB'),
	(40, 7, 'RAM', '16 GB DDR5'),
	(41, 7, 'Ổ cứng', '512 GB SSD'),
	(42, 7, 'Màn hình', '15.6 inch 165Hz'),
	(43, 7, 'Hệ điều hành', 'Windows 11'),
	(44, 8, 'CPU', 'Apple M2'),
	(45, 8, 'RAM', '8 GB'),
	(46, 8, 'SSD', '256 GB'),
	(47, 8, 'Màn hình', '13.6 inch Liquid Retina'),
	(48, 8, 'Pin', '18 giờ sử dụng'),
	(49, 8, 'Hệ điều hành', 'macOS'),
	(50, 9, 'Màn hình', '11 inch Liquid Retina'),
	(51, 9, 'Chip', 'Apple M4'),
	(52, 9, 'RAM', '8 GB'),
	(53, 9, 'Bộ nhớ', '256 GB'),
	(54, 9, 'Camera', '12 MP'),
	(55, 9, 'Pin', '31.29 Wh'),
	(56, 10, 'Loại tai nghe', 'Over-ear'),
	(57, 10, 'Kết nối', 'Bluetooth 5.2'),
	(58, 10, 'Chống ồn', 'ANC'),
	(59, 10, 'Pin', '30 giờ'),
	(60, 10, 'Sạc nhanh', '3 phút dùng 3 giờ'),
	(61, 10, 'Trọng lượng', '250 g'),
	(62, 12, 'Màn hình', '6.3 inch Super Retina XDR'),
	(63, 12, 'Chipset', 'Apple A18 Pro'),
	(64, 12, 'RAM', '8 GB'),
	(65, 12, 'Bộ nhớ trong', '128 GB / 256 GB / 512 GB / 1 TB'),
	(66, 12, 'Camera sau', '48 MP + 48 MP + 12 MP'),
	(67, 12, 'Pin', '3582 mAh'),
	(68, 13, 'Màn hình', '6.9 inch Dynamic AMOLED 2X'),
	(69, 13, 'Chipset', 'Snapdragon 8 Elite'),
	(70, 13, 'RAM', '12 GB'),
	(71, 13, 'Bộ nhớ trong', '256 GB / 512 GB / 1 TB'),
	(72, 13, 'Camera sau', '200 MP + 50 MP + 50 MP + 10 MP'),
	(73, 13, 'Pin', '5000 mAh'),
	(74, 14, 'Màn hình', '6.73 inch AMOLED'),
	(75, 14, 'Chipset', 'Snapdragon 8 Elite'),
	(76, 14, 'RAM', '16 GB'),
	(77, 14, 'Bộ nhớ trong', '512 GB'),
	(78, 14, 'Camera sau', '50 MP + 50 MP + 200 MP + 50 MP'),
	(79, 14, 'Pin', '5410 mAh'),
	(80, 15, 'CPU', 'Intel Core i9-14900HX'),
	(81, 15, 'GPU', 'RTX 4070'),
	(82, 15, 'RAM', '32 GB DDR5'),
	(83, 15, 'SSD', '1 TB SSD'),
	(84, 15, 'Màn hình', '18 inch 240Hz'),
	(85, 15, 'Hệ điều hành', 'Windows 11'),
	(86, 16, 'CPU', 'Intel Core Ultra 7'),
	(87, 16, 'GPU', 'RTX 4050'),
	(88, 16, 'RAM', '16 GB DDR5'),
	(89, 16, 'SSD', '512 GB SSD'),
	(90, 16, 'Màn hình', '15.6 inch OLED'),
	(91, 16, 'Hệ điều hành', 'Windows 11'),
	(92, 17, 'CPU', 'Intel Core Ultra 7'),
	(93, 17, 'RAM', '16 GB LPDDR5'),
	(94, 17, 'SSD', '1 TB SSD'),
	(95, 17, 'Màn hình', '14 inch OLED cảm ứng'),
	(96, 17, 'Thiết kế', 'Xoay gập 360 độ'),
	(97, 17, 'Hệ điều hành', 'Windows 11'),
	(98, 18, 'Màn hình', '11 inch Liquid Retina'),
	(99, 18, 'Chip', 'Apple M3'),
	(100, 18, 'RAM', '8 GB'),
	(101, 18, 'Bộ nhớ', '128 GB'),
	(102, 18, 'Camera', '12 MP'),
	(103, 18, 'Pin', '28.93 Wh'),
	(104, 19, 'Màn hình', '12.4 inch AMOLED'),
	(105, 19, 'Chipset', 'MediaTek Dimensity 9300+'),
	(106, 19, 'RAM', '12 GB'),
	(107, 19, 'Bộ nhớ trong', '256 GB'),
	(108, 19, 'Camera', '13 MP + 8 MP'),
	(109, 19, 'Pin', '10090 mAh'),
	(110, 20, 'Loại tai nghe', 'True Wireless'),
	(111, 20, 'Chip', 'Apple H2'),
	(112, 20, 'Chống ồn', 'ANC'),
	(113, 20, 'Chuẩn chống nước', 'IP54'),
	(114, 20, 'Pin', '30 giờ với hộp sạc'),
	(115, 20, 'Kết nối', 'Bluetooth 5.3'),
	(116, 21, 'Loại', 'Chuột không dây'),
	(117, 21, 'DPI', '8000 DPI'),
	(118, 21, 'Kết nối', 'Bluetooth / Receiver'),
	(119, 21, 'Pin', '70 ngày'),
	(120, 21, 'Cổng sạc', 'USB-C'),
	(121, 21, 'Tương thích', 'Windows / macOS / Linux');

-- Dumping structure for table my_store.reviews
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.reviews: ~2 rows (approximately)
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `image`, `created_at`) VALUES
	(1, 5, 2, 3, 'Hì\r\n', NULL, '2026-06-06 17:40:32'),
	(2, 2, 1, 5, 'Đẹp', 'uploads/meolike.jpg', '2026-06-06 17:52:37');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
