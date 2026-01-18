<?php
// File: update_db_msnv.php
require_once 'includes/config.php';

try {
    // 1. Thêm cột ma_nhan_vien nếu chưa có
    $pdo->exec("ALTER TABLE nguoi_dung ADD COLUMN ma_nhan_vien VARCHAR(20) UNIQUE AFTER id");
    echo "✅ Đã thêm cột ma_nhan_vien.<br>";
} catch (Exception $e) {
    echo "ℹ️ Cột ma_nhan_vien có thể đã tồn tại.<br>";
}

// 2. Tạo mã cho các user cũ chưa có mã
$users = $pdo->query("SELECT id FROM nguoi_dung WHERE ma_nhan_vien IS NULL OR ma_nhan_vien = '' ORDER BY id ASC")->fetchAll();
foreach ($users as $u) {
    // Format: NV + ID được đệm số 0 (VD: ID 1 -> NV001, ID 15 -> NV015)
    $msnv = 'NV' . str_pad($u['id'], 3, '0', STR_PAD_LEFT);
    $pdo->prepare("UPDATE nguoi_dung SET ma_nhan_vien = ? WHERE id = ?")->execute([$msnv, $u['id']]);
    echo "👉 Đã cập nhật User ID {$u['id']} -> $msnv<br>";
}

echo "🎉 <b>Hoàn tất! Hãy xóa file này đi.</b>";
?>