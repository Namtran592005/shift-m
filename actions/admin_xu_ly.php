<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if (in_array($status, ['da_duyet', 'huy'])) {
    $stmt = $pdo->prepare("UPDATE dang_ky_ca SET trang_thai = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    // Nếu đã duyệt, có thể tự động tạo bản ghi chấm công rỗng (tùy chọn)
    if ($status == 'da_duyet') {
        // Logic mở rộng sau này: Insert vào bảng cham_cong để chuẩn bị checkin
    }
}

header("Location: ../admin_duyet.php");
exit();
?>