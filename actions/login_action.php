<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = trim($_POST['email']);
    $password = $_POST['mat_khau'];
    $remember = isset($_POST['remember']); // Kiểm tra có tích ô ghi nhớ không

    // 1. Tìm user bằng Email HOẶC Mã NV
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE (email = ? OR ma_nhan_vien = ?) LIMIT 1");
    $stmt->execute([$input, $input]);
    $user = $stmt->fetch();

    // 2. Xác thực mật khẩu
    if ($user && password_verify($password, $user['mat_khau'])) {
        
        // 3. Kiểm tra khóa tài khoản
        if ($user['trang_thai'] == 0) {
            header("Location: ../login.php?error=Khoa");
            exit();
        }

        // 4. Set Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['vai_tro'];

        // 5. Xử lý "Ghi nhớ đăng nhập" (Set Cookie)
        if ($remember) {
            // Token = ID + Hash(Mật khẩu hiện tại)
            // Nếu đổi mật khẩu, hash sẽ đổi -> Token cũ vô hiệu -> An toàn
            $token = $user['id'] . ':' . md5($user['mat_khau']);
            
            // Set cookie 30 ngày (86400 * 30)
            setcookie('shiftm_token', $token, time() + (86400 * 30), "/", "", false, true);
        } else {
            // Nếu không tích -> Xóa cookie cũ (nếu có)
            if (isset($_COOKIE['shiftm_token'])) {
                setcookie('shiftm_token', '', time() - 3600, "/");
            }
        }

        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../login.php?error=Sai");
        exit();
    }
}
?>