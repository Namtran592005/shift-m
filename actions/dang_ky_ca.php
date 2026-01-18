<?php
require_once '../includes/config.php';
session_start();

if (!isset($_SESSION['user_id']))
    die('Lỗi: Chưa đăng nhập');

$ca_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Kiểm tra đã đăng ký chưa
$stmt = $pdo->prepare("SELECT id FROM dang_ky_ca WHERE nguoi_dung_id = ? AND ca_lam_id = ?");
$stmt->execute([$user_id, $ca_id]);

if (!$stmt->fetch()) {
    $insert = $pdo->prepare("INSERT INTO dang_ky_ca (nguoi_dung_id, ca_lam_id, trang_thai) VALUES (?, ?, 'cho_duyet')");
    $insert->execute([$user_id, $ca_id]);
}

header("Location: ../calendar.php");
exit();
?>