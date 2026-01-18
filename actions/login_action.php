<?php
// File: actions/login_action.php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = trim($_POST['email']); // Ô input này giờ nhập Email hoặc Mã đều được
    $password = $_POST['mat_khau'];

    // Sửa câu SQL: Tìm theo Email HOẶC Mã nhân viên
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE (email = ? OR ma_nhan_vien = ?) LIMIT 1");
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mat_khau'])) {
        if ($user['trang_thai'] == 0) {
            header("Location: ../login.php?error=Khoa");
            exit();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['vai_tro'];
        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../login.php?error=Sai");
        exit();
    }
}