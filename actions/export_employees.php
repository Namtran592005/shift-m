<?php
require_once '../includes/config.php';
require_once '../includes/SimpleXLSXGen.php'; // Gọi thư viện tạo Excel

// Tắt lỗi để không hỏng file
error_reporting(0);
ini_set('display_errors', 0);

// Check Admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Lấy dữ liệu nhân viên
$stmt = $pdo->query("SELECT ma_nhan_vien, ho_ten, email, vai_tro, trang_thai FROM nguoi_dung ORDER BY id ASC");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Header Excel
$data = [
    ['DANH SÁCH NHÂN VIÊN'], // Tiêu đề lớn
    ['Ngày xuất: ' . date('d/m/Y H:i')],
    [],
    ['Mã NV', 'Họ và Tên', 'Email', 'Vai Trò', 'Trạng Thái'] // Cột
];

foreach ($employees as $emp) {
    $data[] = [
        $emp['ma_nhan_vien'],
        $emp['ho_ten'],
        $emp['email'],
        $emp['vai_tro'] == 'admin' ? 'Quản trị' : 'Nhân viên',
        $emp['trang_thai'] == 1 ? 'Hoạt động' : 'Khóa'
    ];
}

$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->mergeCells('A1:E1'); // Gộp ô tiêu đề
$xlsx->downloadAs('Danh_sach_nhan_vien.xlsx');
exit();
?>