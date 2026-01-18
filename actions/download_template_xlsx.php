<?php
require_once '../includes/SimpleXLSXGen.php';

// Tắt lỗi
error_reporting(0);
ini_set('display_errors', 0);

$data = [
    ['Họ Tên', 'Email', 'Vai Trò (admin/nhan_vien)'], // Header
    ['Nguyen Van A', 'user1@gmail.com', 'nhan_vien'], // Mẫu 1
    ['Tran Thi B', 'user2@gmail.com', 'nhan_vien'],   // Mẫu 2
    ['Quan Ly C', 'admin@gmail.com', 'admin']         // Mẫu 3
];

$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('Mau_nhap_nhan_vien.xlsx');
exit();
?>