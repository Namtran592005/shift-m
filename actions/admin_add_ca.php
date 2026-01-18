<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = $_POST['ten_ca'];
    $ngay = $_POST['ngay'];
    $bd = $_POST['gio_bat_dau'];
    $kt = $_POST['gio_ket_thuc'];
    $luong = $_POST['luong_gio'];
    $he_so = $_POST['he_so_luong'] ?? 1.0; // Lấy hệ số

    $sql = "INSERT INTO ca_lam (ten_ca, ngay, gio_bat_dau, gio_ket_thuc, luong_gio, trang_thai, he_so_luong) VALUES (?, ?, ?, ?, ?, 'mo', ?)";
    $pdo->prepare($sql)->execute([$ten, $ngay, $bd, $kt, $luong, $he_so]);

    // Ghi log
    writeLog($pdo, $_SESSION['user_id'], 'Thêm ca làm', "Tên: $ten | Ngày: $ngay | Hệ số: $he_so");
}

header("Location: ../admin_ca_lam.php");
exit();
?>