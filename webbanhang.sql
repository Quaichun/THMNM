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
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.account: ~2 rows (approximately)
INSERT INTO `account` (`id`, `username`, `fullname`, `password`, `role`, `avatar`) VALUES
	(1, 'user123', 'Nguyễn Hoài Trung', '$2y$10$YjbSmSEV50U5bVPSbMu2zex2w2fKKAcQvDWqqigTFdTZKzE8.fFEG', 'user', 'uploads/avatars/avatar_1_1779633062.jpg'),
	(2, 'admin@gmail.com', 'NTT', '$2y$10$5swG6r01qgzPVhkjaWXwK.2hIkkcY7Y8uuWbwKXLcmYhsnwSe/1UO', 'admin', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.orders: ~1 rows (approximately)
INSERT INTO `orders` (`id`, `name`, `phone`, `email`, `payment_method`, `address`, `status`, `created_at`, `user_id`) VALUES
	(1, 'Nguyễn Hoài Trung', '0125698764', NULL, 'cod', 'Thủ đức', 'delivered', '2026-05-23 14:26:09', NULL),
	(2, 'Nguyễn Hoài Trung', '0125698764', 'tn@gmail.com', 'cod', 'Thu Duc', 'pending', '2026-05-31 17:21:19', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table my_store.order_details: ~2 rows (approximately)
INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
	(1, 1, 3, 1, 20000000.00),
	(2, 1, 5, 1, 41000000.00),
	(3, 1, 8, 1, 27000000.00),
	(4, 2, 5, 1, 41000000.00);

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

-- Dumping data for table my_store.product: ~7 rows (approximately)
INSERT INTO `product` (`id`, `name`, `description`, `price`, `image`, `category_id`) VALUES
	(2, 'IPhone 15 pro max', 'tttt', 28000000.00, 'uploads/iphone15pr.jpg', 1),
	(3, 'Macbook', 'jj', 20000000.00, 'uploads/macbook.jpg', 2),
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

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
