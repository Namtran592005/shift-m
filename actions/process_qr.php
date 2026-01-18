<?php
require_once '../includes/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập lại']);
    exit();
}

$user_id = $_SESSION['user_id'];
$qr_code = $_POST['qr_code'] ?? '';

try {
    // 1. Tìm ca làm việc tương ứng với QR code
    $stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE ma_qr = ?");
    $stmt->execute([$qr_code]);
    $ca = $stmt->fetch();

    if (!$ca) {
        echo json_encode(['status' => 'error', 'message' => 'Mã QR không hợp lệ!']);
        exit();
    }

    // 2. Kiểm tra xem user có đăng ký và được duyệt ca này không
    $stmt_dk = $pdo->prepare("SELECT id FROM dang_ky_ca WHERE nguoi_dung_id = ? AND ca_lam_id = ? AND trang_thai = 'da_duyet'");
    $stmt_dk->execute([$user_id, $ca['id']]);
    $dang_ky = $stmt_dk->fetch();

    if (!$dang_ky) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng ký hoặc chưa được duyệt ca này!']);
        exit();
    }

    // 3. Kiểm tra xem hôm nay đã check-in chưa
    // Lưu ý: Logic này dựa trên dang_ky_ca_id. 
    // Ta tìm bản ghi chấm công gần nhất của đăng ký này mà chưa check-out
    $stmt_cc = $pdo->prepare("SELECT * FROM cham_cong WHERE dang_ky_ca_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_cc->execute([$dang_ky['id']]);
    $cham_cong = $stmt_cc->fetch();

    $now = date('Y-m-d H:i:s');

    // LOGIC CHÍNH
    if (!$cham_cong || ($cham_cong['gio_check_in'] && $cham_cong['gio_check_out'])) {
        // TRƯỜNG HỢP 1: Chưa check-in bao giờ HOẶC Lần trước đã check-out rồi -> CHECK-IN MỚI
        // Chỉ cho phép check-in đúng ngày (hoặc linh động tùy bạn)
        if (date('Y-m-d') != $ca['ngay']) {
            // Tùy chọn: Có thể chặn nếu sai ngày
            // echo json_encode(['status' => 'error', 'message' => 'Chưa đến ngày làm việc!']); exit;
        }

        $insert = $pdo->prepare("INSERT INTO cham_cong (dang_ky_ca_id, gio_check_in) VALUES (?, ?)");
        $insert->execute([$dang_ky['id'], $now]);

        echo json_encode(['status' => 'success', 'message' => 'Check-in thành công: ' . date('H:i')]);

    } else {
        // TRƯỜNG HỢP 2: Đã check-in nhưng chưa check-out -> CHECK-OUT
        $gio_vao = strtotime($cham_cong['gio_check_in']);
        $gio_ra = strtotime($now);
        $so_gio = round(($gio_ra - $gio_vao) / 3600, 2); // Tính số giờ làm

        $update = $pdo->prepare("UPDATE cham_cong SET gio_check_out = ?, so_gio_lam = ? WHERE id = ?");
        $update->execute([$now, $so_gio, $cham_cong['id']]);

        echo json_encode(['status' => 'success', 'message' => 'Check-out thành công. Tổng: ' . $so_gio . ' giờ']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>