<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Kiểm tra xem file thư viện có tồn tại không
if (!file_exists('../includes/SimpleXLSXGen.php')) {
    die("Lỗi: Không tìm thấy file includes/SimpleXLSXGen.php");
}
require_once '../includes/SimpleXLSXGen.php';

// Tắt lỗi sau khi đã debug xong
error_reporting(0);
ini_set('display_errors', 0);

requireAdmin($pdo);

$thang = $_GET['m'] ?? date('m');
$nam = $_GET['y'] ?? date('Y');

// Lấy dữ liệu
$sql = "SELECT u.ma_nhan_vien, u.ho_ten, cl.ngay, cl.ten_ca, cc.gio_check_in, cc.gio_check_out, cc.so_gio_lam, cl.luong_gio, cl.he_so_luong
        FROM cham_cong cc
        JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id
        JOIN ca_lam cl ON dkc.ca_lam_id = cl.id
        JOIN nguoi_dung u ON dkc.nguoi_dung_id = u.id
        WHERE MONTH(cl.ngay) = ? AND YEAR(cl.ngay) = ?
        ORDER BY u.ho_ten ASC, cl.ngay ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$thang, $nam]);
$data = $stmt->fetchAll();

// Chuẩn bị dữ liệu Excel
$excelData = [
    ['BANG LUONG THANG ' . $thang . '/' . $nam], // Tiêu đề
    ['Ngay xuat: ' . date('d/m/Y')],
    [],
    ['Ma NV', 'Ho Ten', 'Ngay', 'Ca Lam', 'Gio Vao', 'Gio Ra', 'So Gio', 'Luong/H', 'He So', 'Thanh Tien'] // Header
];

$total_money = 0;
foreach ($data as $row) {
    $thanh_tien = $row['so_gio_lam'] * $row['luong_gio'] * $row['he_so_luong'];
    $total_money += $thanh_tien;

    $excelData[] = [
        $row['ma_nhan_vien'],
        $row['ho_ten'],
        date('d/m/Y', strtotime($row['ngay'])),
        $row['ten_ca'],
        $row['gio_check_in'] ? date('H:i', strtotime($row['gio_check_in'])) : '-',
        $row['gio_check_out'] ? date('H:i', strtotime($row['gio_check_out'])) : '-',
        $row['so_gio_lam'],
        $row['luong_gio'],
        $row['he_so_luong'],
        $thanh_tien
    ];
}

$excelData[] = ['', '', '', '', '', '', '', '', 'TONG CONG:', $total_money];

// Xuất file
$xlsx = Shuchkin\SimpleXLSXGen::fromArray($excelData);
$xlsx->downloadAs('Bang_luong_T'.$thang.'.xlsx');
exit();
?>