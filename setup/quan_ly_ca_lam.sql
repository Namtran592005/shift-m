-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: localhost
-- Thời gian đã tạo: Th1 18, 2026 lúc 12:56 PM
-- Phiên bản máy phục vụ: 12.1.2-MariaDB
-- Phiên bản PHP: 8.5.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quan_ly_ca_lam`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cai_dat`
--

CREATE TABLE `cai_dat` (
  `ten_cau_hinh` varchar(50) NOT NULL,
  `gia_tri` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cai_dat`
--

INSERT INTO `cai_dat` (`ten_cau_hinh`, `gia_tri`) VALUES
('cho_phep_ung', '0'),
('khoa_doi_avatar', '0');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ca_lam`
--

CREATE TABLE `ca_lam` (
  `id` int(11) NOT NULL,
  `ten_ca` varchar(50) DEFAULT NULL,
  `ngay` date NOT NULL,
  `gio_bat_dau` time NOT NULL,
  `gio_ket_thuc` time NOT NULL,
  `luong_gio` decimal(10,2) NOT NULL,
  `trang_thai` enum('mo','dong') DEFAULT 'mo',
  `ma_qr` varchar(100) DEFAULT NULL,
  `he_so_luong` decimal(3,1) DEFAULT 1.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `ca_lam`
--

INSERT INTO `ca_lam` (`id`, `ten_ca`, `ngay`, `gio_bat_dau`, `gio_ket_thuc`, `luong_gio`, `trang_thai`, `ma_qr`, `he_so_luong`) VALUES
(1, 'Trực Thư Viện', '2026-01-16', '08:00:00', '12:00:00', 25000.00, 'mo', 'be9e7ac40d93c7a499cf6657f480742c', 1.0),
(2, 'Phục vụ Canteen', '2026-01-16', '12:00:00', '16:00:00', 22000.00, 'mo', 'c97311833bda9aaeb45dcf6d491e782c', 1.0),
(3, 'Hỗ trợ Phòng Lab', '2026-01-17', '13:00:00', '17:00:00', 30000.00, 'mo', '6c0ef24c142288325933441a66fca990', 1.0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cham_cong`
--

CREATE TABLE `cham_cong` (
  `id` int(11) NOT NULL,
  `dang_ky_ca_id` int(11) NOT NULL,
  `gio_check_in` datetime DEFAULT NULL,
  `gio_check_out` datetime DEFAULT NULL,
  `so_gio_lam` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dang_ky_ca`
--

CREATE TABLE `dang_ky_ca` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `ca_lam_id` int(11) NOT NULL,
  `trang_thai` enum('cho_duyet','da_duyet','huy') DEFAULT 'cho_duyet',
  `thoi_diem_dang_ky` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `dang_ky_ca`
--

INSERT INTO `dang_ky_ca` (`id`, `nguoi_dung_id`, `ca_lam_id`, `trang_thai`, `thoi_diem_dang_ky`) VALUES
(2, 2, 1, 'da_duyet', '2026-01-16 07:56:05'),
(3, 2, 3, 'da_duyet', '2026-01-16 08:05:54'),
(4, 2, 2, 'huy', '2026-01-16 08:06:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ma_nhan_vien` varchar(20) DEFAULT NULL,
  `so_cccd` varchar(20) DEFAULT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` enum('admin','nhan_vien') NOT NULL DEFAULT 'nhan_vien',
  `trang_thai` tinyint(4) DEFAULT 1,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `nfc_uid` varchar(50) DEFAULT NULL,
  `anh_cccd_truoc` varchar(255) DEFAULT NULL,
  `anh_cccd_sau` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ma_nhan_vien`, `so_cccd`, `ho_ten`, `email`, `mat_khau`, `vai_tro`, `trang_thai`, `ngay_tao`, `avatar`, `nfc_uid`, `anh_cccd_truoc`, `anh_cccd_sau`) VALUES
(1, 'NV001', NULL, 'Admin', 'admin@test.com', '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS', 'admin', 1, '2026-01-16 07:48:37', NULL, NULL, NULL, NULL),
(2, 'NV002', NULL, 'nhân viên', 'nhanvien@test.com', '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS', 'nhan_vien', 1, '2026-01-16 07:48:37', 'user_2_1768533147.png', NULL, NULL, NULL),
(4, 'NV003', '084205007872', 'Nhân viên 2', 'nhanvien2@test.com', '$2y$12$67w5dBLvshiCyjOpMTM4tes5dtaWSlLoqN6wY39HK6TuoCly3HMsy', 'nhan_vien', 0, '2026-01-18 17:55:26', NULL, '', 'cccd_front_4_1768738245.png', 'cccd_back_4_1768738245.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhat_ky_he_thong`
--

CREATE TABLE `nhat_ky_he_thong` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) DEFAULT NULL,
  `hanh_dong` varchar(255) NOT NULL,
  `chi_tiet` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `thoi_gian` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhat_ky_he_thong`
--

INSERT INTO `nhat_ky_he_thong` (`id`, `nguoi_dung_id`, `hanh_dong`, `chi_tiet`, `ip_address`, `thoi_gian`) VALUES
(1, 1, 'Xuất Báo cáo', 'Bảng lương tháng 01/2026', '127.0.0.1', '2026-01-16 11:47:39'),
(2, 1, 'Xuất Báo cáo', 'Bảng lương tháng 01/2026', '127.0.0.1', '2026-01-16 12:51:53'),
(3, 1, 'Tạo QR', 'Ca ID: 3', '127.0.0.1', '2026-01-16 12:59:14'),
(4, 1, 'Tạo QR', 'Ca ID: 1', '127.0.0.1', '2026-01-16 12:59:20'),
(5, 1, 'Tạo QR', 'Ca ID: 1', '127.0.0.1', '2026-01-16 12:59:26'),
(6, 1, 'Đăng nhập', 'Login thành công', '127.0.0.1', '2026-01-17 06:43:44'),
(7, 1, 'Cấu hình', 'Đổi trạng thái ứng lương: 1', '127.0.0.1', '2026-01-18 06:34:01'),
(8, 1, 'Cấu hình', 'Đổi trạng thái ứng lương: 0', '127.0.0.1', '2026-01-18 06:34:02'),
(9, 1, 'Cấu hình', 'Đổi trạng thái ứng lương: 1', '127.0.0.1', '2026-01-18 06:34:20'),
(10, 1, 'Thêm nhân viên', 'Mã: NV003', '127.0.0.1', '2026-01-18 17:55:26'),
(11, 1, 'Sửa nhân viên', 'ID: 4', '127.0.0.1', '2026-01-18 17:55:37'),
(12, 1, 'Sửa nhân viên', 'ID: 4', '127.0.0.1', '2026-01-18 17:56:45'),
(13, 1, 'Cấu hình', 'Đổi trạng thái ứng lương: 1', '127.0.0.1', '2026-01-18 18:04:26'),
(14, 1, 'Cấu hình', 'Đổi trạng thái ứng lương: 0', '127.0.0.1', '2026-01-18 18:04:27'),
(15, 1, 'Xuất Báo cáo', 'Bảng lương tháng 01/2026', '127.0.0.1', '2026-01-18 18:04:42'),
(16, 1, 'Sửa nhân viên', 'ID: 4', '127.0.0.1', '2026-01-18 18:10:02'),
(17, 1, 'Tạo QR', 'Ca ID: 1', '127.0.0.1', '2026-01-18 18:38:27'),
(18, 1, 'Tạo QR', 'Ca ID: 3', '127.0.0.1', '2026-01-18 18:42:38');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ung_luong`
--

CREATE TABLE `ung_luong` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `thang` int(11) NOT NULL,
  `nam` int(11) NOT NULL,
  `so_tien_yeu_cau` decimal(10,2) NOT NULL,
  `so_tien_duyet` decimal(10,2) DEFAULT 0.00,
  `ngay_yeu_cau` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi') DEFAULT 'cho_duyet',
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cai_dat`
--
ALTER TABLE `cai_dat`
  ADD PRIMARY KEY (`ten_cau_hinh`);

--
-- Chỉ mục cho bảng `ca_lam`
--
ALTER TABLE `ca_lam`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cham_cong`
--
ALTER TABLE `cham_cong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cc_dang_ky_ca` (`dang_ky_ca_id`);

--
-- Chỉ mục cho bảng `dang_ky_ca`
--
ALTER TABLE `dang_ky_ca`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nguoi_dung_id` (`nguoi_dung_id`,`ca_lam_id`),
  ADD KEY `fk_dkc_ca_lam` (`ca_lam_id`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nfc_uid` (`nfc_uid`),
  ADD UNIQUE KEY `ma_nhan_vien` (`ma_nhan_vien`);

--
-- Chỉ mục cho bảng `nhat_ky_he_thong`
--
ALTER TABLE `nhat_ky_he_thong`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `ung_luong`
--
ALTER TABLE `ung_luong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ul_user` (`nguoi_dung_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ca_lam`
--
ALTER TABLE `ca_lam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `cham_cong`
--
ALTER TABLE `cham_cong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `dang_ky_ca`
--
ALTER TABLE `dang_ky_ca`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `nhat_ky_he_thong`
--
ALTER TABLE `nhat_ky_he_thong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `ung_luong`
--
ALTER TABLE `ung_luong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `cham_cong`
--
ALTER TABLE `cham_cong`
  ADD CONSTRAINT `fk_cc_dang_ky_ca` FOREIGN KEY (`dang_ky_ca_id`) REFERENCES `dang_ky_ca` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `dang_ky_ca`
--
ALTER TABLE `dang_ky_ca`
  ADD CONSTRAINT `fk_dkc_ca_lam` FOREIGN KEY (`ca_lam_id`) REFERENCES `ca_lam` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dkc_nguoi_dung` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `ung_luong`
--
ALTER TABLE `ung_luong`
  ADD CONSTRAINT `fk_ul_user` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
