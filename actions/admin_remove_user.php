<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$dk_id = $_GET['dk_id'] ?? 0;
$ca_id = $_GET['ca_id'] ?? 0;

if ($dk_id) {
    // Xóa bản ghi đăng ký (các bản ghi chấm công liên quan sẽ tự xóa nhờ FOREIGN KEY ON DELETE CASCADE đã thiết lập ở DB)
    $stmt = $pdo->prepare("DELETE FROM dang_ky_ca WHERE id = ?");
    $stmt->execute([$dk_id]);
}

header("Location: ../admin_shift_detail.php?id=" . $ca_id);
exit();
?>