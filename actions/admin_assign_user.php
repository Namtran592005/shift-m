<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$ca_id = $_POST['ca_id'] ?? 0;
// Lấy danh sách ID (dạng mảng) từ checkbox
$user_ids = $_POST['user_ids'] ?? [];

if ($ca_id && !empty($user_ids)) {
    // Chuẩn bị câu lệnh SQL
    $stmt = $pdo->prepare("INSERT INTO dang_ky_ca (nguoi_dung_id, ca_lam_id, trang_thai) VALUES (?, ?, 'da_duyet')");

    foreach ($user_ids as $uid) {
        try {
            // Thêm từng người vào vòng lặp
            $stmt->execute([$uid, $ca_id]);
        } catch (Exception $e) {
            // Nếu lỗi (ví dụ đã tồn tại) thì bỏ qua, tiếp tục người sau
            continue;
        }
    }
}

header("Location: ../admin_shift_detail.php?id=" . $ca_id);
exit();
?>