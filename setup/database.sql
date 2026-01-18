DROP DATABASE IF EXISTS quan_ly_ca_lam;

CREATE DATABASE quan_ly_ca_lam CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE quan_ly_ca_lam;

-- 1. Bảng NGƯỜI DÙNG (Nhân viên & Admin)
CREATE TABLE
    nguoi_dung (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ho_ten VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        mat_khau VARCHAR(255) NOT NULL,
        avatar VARCHAR(255) DEFAULT NULL,
        vai_tro ENUM ('admin', 'nhan_vien') NOT NULL DEFAULT 'nhan_vien',
        trang_thai TINYINT DEFAULT 1, -- 1: Active, 0: Locked
        ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
        nfc_uid VARCHAR(50) UNIQUE NULL
    ) ENGINE = InnoDB;

-- 2. Bảng CA LÀM VIỆC
CREATE TABLE
    ca_lam (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ten_ca VARCHAR(100),
        ngay DATE NOT NULL,
        gio_bat_dau TIME NOT NULL,
        gio_ket_thuc TIME NOT NULL,
        luong_gio DECIMAL(10, 2) NOT NULL,
        he_so_luong DECIMAL(3, 1) DEFAULT 1.0, -- Hệ số (x1.5, x2.0...)
        ma_qr VARCHAR(100) NULL, -- Mã QR check-in
        trang_thai ENUM ('mo', 'dong') DEFAULT 'mo'
    ) ENGINE = InnoDB;

-- 3. Bảng ĐĂNG KÝ CA
CREATE TABLE
    dang_ky_ca (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nguoi_dung_id INT NOT NULL,
        ca_lam_id INT NOT NULL,
        trang_thai ENUM ('cho_duyet', 'da_duyet', 'huy') DEFAULT 'cho_duyet',
        thoi_diem_dang_ky DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (nguoi_dung_id, ca_lam_id),
        CONSTRAINT fk_dkc_user FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id) ON DELETE CASCADE,
        CONSTRAINT fk_dkc_ca FOREIGN KEY (ca_lam_id) REFERENCES ca_lam (id) ON DELETE CASCADE
    ) ENGINE = InnoDB;

-- 4. Bảng CHẤM CÔNG (Timekeeping)
CREATE TABLE
    cham_cong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dang_ky_ca_id INT NOT NULL,
        gio_check_in DATETIME,
        gio_check_out DATETIME,
        so_gio_lam DECIMAL(5, 2) DEFAULT 0,
        CONSTRAINT fk_cc_dkc FOREIGN KEY (dang_ky_ca_id) REFERENCES dang_ky_ca (id) ON DELETE CASCADE
    ) ENGINE = InnoDB;

-- 5. Bảng ỨNG LƯƠNG (Salary Advance)
CREATE TABLE
    ung_luong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nguoi_dung_id INT NOT NULL,
        thang INT NOT NULL,
        nam INT NOT NULL,
        so_tien_yeu_cau DECIMAL(10, 2) NOT NULL,
        so_tien_duyet DECIMAL(10, 2) DEFAULT 0,
        ngay_yeu_cau DATETIME DEFAULT CURRENT_TIMESTAMP,
        trang_thai ENUM ('cho_duyet', 'da_duyet', 'tu_choi') DEFAULT 'cho_duyet',
        ghi_chu TEXT,
        CONSTRAINT fk_ul_user FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id) ON DELETE CASCADE
    ) ENGINE = InnoDB;

-- 6. Bảng CÀI ĐẶT HỆ THỐNG
CREATE TABLE
    cai_dat (
        ten_cau_hinh VARCHAR(50) PRIMARY KEY,
        gia_tri TEXT
    ) ENGINE = InnoDB;

-- 7. Bảng NHẬT KÝ HOẠT ĐỘNG (System Logs)
CREATE TABLE
    nhat_ky_he_thong (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nguoi_dung_id INT,
        hanh_dong VARCHAR(255) NOT NULL,
        chi_tiet TEXT,
        ip_address VARCHAR(45),
        thoi_gian DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB;

-- --- DỮ LIỆU MẪU ---
-- 1. Tài khoản Admin & Nhân viên
-- Mật khẩu mặc định là: 123456
INSERT INTO
    nguoi_dung (ho_ten, email, mat_khau, vai_tro)
VALUES
    (
        'Administrator',
        'admin@test.local',
        '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS',
        'admin'
    ),
    (
        'Nam Trần',
        'nhanvien@test.local',
        '$2a$12$myqDbxwd0nQIEwt4y8J49.pW1ag1WAsKAO88I1LKfWUCa7qGMLeTS',
        'nhan_vien'
    );

-- 2. Cài đặt mặc định
INSERT INTO
    cai_dat (ten_cau_hinh, gia_tri)
VALUES
    ('cho_phep_ung', '1'), -- Mở cổng ứng lương
    ('khoa_doi_avatar', '0');

-- Cho phép đổi avatar