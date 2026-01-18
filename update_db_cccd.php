<?php
// File: update_db_cccd.php
require_once 'includes/config.php';

try {
    $pdo->exec("ALTER TABLE nguoi_dung 
                ADD COLUMN so_cccd VARCHAR(20) DEFAULT NULL AFTER ma_nhan_vien,
                ADD COLUMN anh_cccd_truoc VARCHAR(255) DEFAULT NULL,
                ADD COLUMN anh_cccd_sau VARCHAR(255) DEFAULT NULL");
    echo "✅ Đã thêm cột CCCD thành công!";
} catch (Exception $e) {
    echo "ℹ️ Cột CCCD có thể đã tồn tại.";
}
?>