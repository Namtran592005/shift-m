<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$thang = $_GET['m'] ?? date('m');
$nam = $_GET['y'] ?? date('Y');

// Ghi log việc xuất file
writeLog($pdo, $_SESSION['user_id'], 'Xuất Báo cáo', "Bảng lương tháng $thang/$nam");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=BangLuong_Thang' . $thang . '_' . $nam . '.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

// Header
fputcsv($output, ['ID', 'Họ Tên', 'Email', 'Tổng Giờ', 'Tổng Lương (Gốc)', 'Đã Ứng', 'Thực Lĩnh']);

// Query phức hợp tính lương có hệ số
$sql = "SELECT u.id, u.ho_ten, u.email,
        (SELECT SUM(cc.so_gio_lam) 
         FROM cham_cong cc JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id 
         WHERE dkc.nguoi_dung_id = u.id AND MONTH(cl.ngay) = ? AND YEAR(cl.ngay) = ?) as tong_gio,
         
        (SELECT SUM(cc.so_gio_lam * cl.luong_gio * cl.he_so_luong) 
         FROM cham_cong cc JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id 
         WHERE dkc.nguoi_dung_id = u.id AND MONTH(cl.ngay) = ? AND YEAR(cl.ngay) = ?) as tong_luong,

        (SELECT SUM(so_tien_duyet) FROM ung_luong WHERE nguoi_dung_id = u.id AND thang = ? AND nam = ? AND trang_thai='da_duyet') as da_ung
        
        FROM nguoi_dung u WHERE u.vai_tro = 'nhan_vien'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$thang, $nam, $thang, $nam, $thang, $nam]);
$data = $stmt->fetchAll();

foreach ($data as $row) {
    $tong_luong = $row['tong_luong'] ?? 0;
    $da_ung = $row['da_ung'] ?? 0;
    $thuc_linh = $tong_luong - $da_ung;

    if ($tong_luong > 0 || $da_ung > 0) { // Chỉ xuất người có lương hoặc có ứng
        fputcsv($output, [
            $row['id'],
            $row['ho_ten'],
            $row['email'],
            number_format($row['tong_gio'], 2),
            number_format($tong_luong),
            number_format($da_ung),
            number_format($thuc_linh)
        ]);
    }
}
fclose($output);
exit();
?>