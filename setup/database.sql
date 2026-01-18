-- --------------------------------------------------------
-- TỰ ĐỘNG TẠO VÀ CÀI ĐẶT DATABASE QUẢN LÝ CA LÀM
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- 1. XOÁ DATABASE CŨ VÀ TẠO MỚI
DROP DATABASE IF EXISTS `quan_ly_ca_lam`;
CREATE DATABASE `quan_ly_ca_lam` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quan_ly_ca_lam`;

-- --------------------------------------------------------

-- 2. CẤU TRÚC BẢNG `nguoi_dung`
CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_nhan_vien` varchar(20) DEFAULT NULL,
  `so_cccd` varchar(20) DEFAULT NULL, --
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `nfc_uid` varchar(50) DEFAULT NULL,
  `anh_cccd_truoc` varchar(255) DEFAULT NULL, --
  `anh_cccd_sau` varchar(255) DEFAULT NULL, --
  `vai_tro` enum('admin','nhan_vien') NOT NULL DEFAULT 'nhan_vien',
  `trang_thai` tinyint(4) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nfc_uid` (`nfc_uid`),
  UNIQUE KEY `ma_nhan_vien` (`ma_nhan_vien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dữ liệu Admin mặc định (Pass: 123456)
INSERT INTO `nguoi_dung` (`ma_nhan_vien`, `ho_ten`, `email`, `mat_khau`, `vai_tro`, `trang_thai`) VALUES
('ADMIN', 'Quản trị hệ thống', 'admin@test.com', '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS', 'admin', 1);

-- --------------------------------------------------------

-- 3. CẤU TRÚC BẢNG `cai_dat`
CREATE TABLE `cai_dat` (
  `ten_cau_hinh` varchar(50) NOT NULL,
  `gia_tri` text DEFAULT NULL,
  PRIMARY KEY (`ten_cau_hinh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cai_dat` (`ten_cau_hinh`, `gia_tri`) VALUES
('cho_phep_ung', '1'),
('khoa_doi_avatar', '0');

-- --------------------------------------------------------

-- 4. CẤU TRÚC BẢNG `ca_lam`
CREATE TABLE `ca_lam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_ca` varchar(100) DEFAULT NULL,
  `ngay` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `luong_gio` decimal(10,2) NOT NULL,
  `he_so_luong` decimal(3,1) DEFAULT 1.0,
  `ma_qr` varchar(100) DEFAULT NULL,
  `trang_thai` enum('mo','dong') DEFAULT 'mo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- 5. CẤU TRÚC BẢNG `dang_ky_ca`
CREATE TABLE `dang_ky_ca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `ca_lam_id` int(11) NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','huy') DEFAULT 'cho_duyet',
  `thoi_diem_dang_ky` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_dk` (`nguoi_dung_id`,`ca_lam_id`),
  CONSTRAINT `fk_dkc_ca_lam` FOREIGN KEY (`ca_lam_id`) REFERENCES `ca_lam` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dkc_nguoi_dung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- 6. CẤU TRÚC BẢNG `cham_cong`
CREATE TABLE `cham_cong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dang_ky_ca_id` int(11) NOT NULL,
  `gio_check_in` datetime DEFAULT NULL,
  `gio_check_out` datetime DEFAULT NULL,
  `so_gio_lam` decimal(5,2) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cc_dang_ky_ca` FOREIGN KEY (`dang_ky_ca_id`) REFERENCES `dang_ky_ca` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- 7. CẤU TRÚC BẢNG `ung_luong`
CREATE TABLE `ung_luong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `thang` int(11) NOT NULL,
  `nam` int(11) NOT NULL,
  `so_tien_yeu_cau` decimal(12,2) NOT NULL,
  `so_tien_duyet` decimal(12,2) DEFAULT 0.00,
  `ngay_yeu_cau` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi') DEFAULT 'cho_duyet',
  `ghi_chu` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ul_user` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- 8. CẤU TRÚC BẢNG `nhat_ky_he_thong`
CREATE TABLE `nhat_ky_he_thong` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `han_dong` varchar(255) NOT NULL,
  `chi_tiet` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `thoi_gian` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;