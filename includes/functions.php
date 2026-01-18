<?php
// ... (Các hàm requireLogin, requireAdmin, formatMoney, getConfig, getAvatar GIỮ NGUYÊN) ...
// Copy đè hoặc thêm vào cuối file các hàm sau:

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin($pdo)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $stmt = $pdo->prepare("SELECT vai_tro FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['vai_tro'] !== 'admin') {
        die("Bạn không có quyền truy cập.");
    }
}

function getCurrentUser($pdo)
{
    if (!isset($_SESSION['user_id']))
        return null;
    $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function formatMoney($amount)
{
    return number_format($amount, 0, ',', '.') . ' đ';
}

function getConfig($pdo, $key)
{
    $stmt = $pdo->prepare("SELECT gia_tri FROM cai_dat WHERE ten_cau_hinh = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn();
}

function getAvatar($fileName, $role = 'nhan_vien')
{
    if (!empty($fileName) && file_exists(__DIR__ . '/../assets/uploads/' . $fileName)) {
        return "assets/uploads/" . $fileName;
    }
    if ($role === 'admin')
        return "assets/img/default_admin.png";
    return "assets/img/default.png";
}

// --- [MỚI] HÀM GHI LOG HỆ THỐNG ---
function writeLog($pdo, $user_id, $action, $detail = '')
{
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO nhat_ky_he_thong (nguoi_dung_id, hanh_dong, chi_tiet, ip_address) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$user_id, $action, $detail, $ip]);
}

// --- [MỚI] HÀM GỬI MAIL (Dummy - Cần cài PHPMailer để hoạt động thật) ---
function sendEmailNotification($to, $subject, $message)
{
    // Để gửi mail thật, bạn cần tải PHPMailer về assets/vendor/PHPMailer
    // Ở đây mình chỉ ghi vào Log để giả lập
    // writeLog($pdo, 0, 'Gửi Email', "To: $to | Sub: $subject");
    return true;
}
?>