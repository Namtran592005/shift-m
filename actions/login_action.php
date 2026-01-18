<?php
require_once '../includes/config.php';

$email = $_POST['email'] ?? '';
$pass = $_POST['mat_khau'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($pass, $user['mat_khau'])) {
    if ($user['trang_thai'] == 1) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: ../dashboard.php");
    } else {
        header("Location: ../login.php?error=Khoa");
    }
} else {
    header("Location: ../login.php?error=Sai");
}
exit();
?>