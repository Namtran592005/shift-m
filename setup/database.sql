-- phpMyAdmin SQL Dump
-- Version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2025
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quan_ly_ca_lam`
--
CREATE DATABASE IF NOT EXISTS `quan_ly_ca_lam` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `quan_ly_ca_lam`;

-- --------------------------------------------------------

--
-- Table structure for table `nguoi_dung`
--

DROP TABLE IF EXISTS `nguoi_dung`;
CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_nhan_vien` varchar(20) DEFAULT NULL COMMENT 'Mã nhân viên (VD: NV001)',
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `vai_tro` enum('admin','nhan_vien') NOT NULL DEFAULT 'nhan_vien',
  `nfc_uid` varchar(50) DEFAULT NULL COMMENT 'Mã thẻ từ NFC',
  `trang_thai` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Khóa',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `ma_nhan_vien` (`ma_nhan_vien`),
  UNIQUE KEY `nfc_uid` (`nfc_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ma_nhan_vien`, `ho_ten`, `email`, `mat_khau`, `avatar`, `vai_tro`, `nfc_uid`, `trang_thai`) VALUES
(1, 'ADMIN', 'Administrator', 'admin@test.com', '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS', NULL, 'admin', NULL, 1),
(2, 'NV001', 'Nguyễn Văn A', 'nhanvien@test.com', '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS', NULL, 'nhan_vien', NULL, 1);
-- Mật khẩu là 123456

-- --------------------------------------------------------

--
-- Table structure for table `ca_lam`
--

DROP TABLE IF EXISTS `ca_lam`;
CREATE TABLE `ca_lam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_ca` varchar(100) NOT NULL,
  `ngay` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `luong_gio` decimal(10,2) NOT NULL DEFAULT 25000.00,
  `he_so_luong` decimal(3,1) NOT NULL DEFAULT 1.0 COMMENT '1.0: Thường, 1.5: Đêm/CN, 2.0: Lễ',
  `ma_qr` varchar(255) DEFAULT NULL COMMENT 'Mã QR để check-in',
  `trang_thai` enum('mo','dong') NOT NULL DEFAULT 'mo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dang_ky_ca`
--

DROP TABLE IF EXISTS `dang_ky_ca`;
CREATE TABLE `dang_ky_ca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `ca_lam_id` int(11) NOT NULL,
  `thoi_diem_dang_ky` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` enum('cho_duyet','da_duyet','huy') NOT NULL DEFAULT 'cho_duyet',
  PRIMARY KEY (`id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  KEY `ca_lam_id` (`ca_lam_id`),
  CONSTRAINT `dang_ky_ca_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dang_ky_ca_ibfk_2` FOREIGN KEY (`ca_lam_id`) REFERENCES `ca_lam` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cham_cong`
--

DROP TABLE IF EXISTS `cham_cong`;
CREATE TABLE `cham_cong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dang_ky_ca_id` int(11) NOT NULL,
  `gio_check_in` datetime DEFAULT NULL,
  `gio_check_out` datetime DEFAULT NULL,
  `so_gio_lam` decimal(5,2) DEFAULT 0.00,
  `ghi_chu` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dang_ky_ca_id` (`dang_ky_ca_id`),
  CONSTRAINT `cham_cong_ibfk_1` FOREIGN KEY (`dang_ky_ca_id`) REFERENCES `dang_ky_ca` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ung_luong`
--

DROP TABLE IF EXISTS `ung_luong`;
CREATE TABLE `ung_luong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `thang` int(2) NOT NULL,
  `nam` int(4) NOT NULL,
  `so_tien_yeu_cau` decimal(12,2) NOT NULL,
  `so_tien_duyet` decimal(12,2) DEFAULT 0.00,
  `ngay_yeu_cau` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi') NOT NULL DEFAULT 'cho_duyet',
  PRIMARY KEY (`id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  CONSTRAINT `ung_luong_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cai_dat`
--

DROP TABLE IF EXISTS `cai_dat`;
CREATE TABLE `cai_dat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_cau_hinh` varchar(50) NOT NULL,
  `gia_tri` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ten_cau_hinh` (`ten_cau_hinh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cai_dat`
--

INSERT INTO `cai_dat` (`ten_cau_hinh`, `gia_tri`) VALUES
('khoa_doi_avatar', '0'),
('cho_phep_ung', '1');

-- --------------------------------------------------------

--
-- Table structure for table `nhat_ky_he_thong`
--

DROP TABLE IF EXISTS `nhat_ky_he_thong`;
CREATE TABLE `nhat_ky_he_thong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `hanh_dong` varchar(100) NOT NULL,
  `chi_tiet` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `thoi_gian` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;