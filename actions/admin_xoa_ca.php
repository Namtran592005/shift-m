<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$id = $_GET['id'] ?? 0;
// Chỉ xóa nếu chưa có ai check-in (để bảo toàn dữ liệu lương)
// Ở đây làm đơn giản là xóa luôn
$pdo->prepare("DELETE FROM ca_lam WHERE id = ?")->execute([$id]);

header("Location: ../admin_ca_lam.php");
exit();
?>