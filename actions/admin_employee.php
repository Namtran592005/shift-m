<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$stmt = $pdo->prepare("SELECT vai_tro FROM nguoi_dung WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$u = $stmt->fetch();
if (!$u || $u['vai_tro'] !== 'admin') {
    die("Không có quyền.");
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- THÊM NHÂN VIÊN ---
if ($action == 'add') {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $vai_tro = $_POST['vai_tro'];

    // Tạo Mã NV mới
    $stmt = $pdo->query("SELECT ma_nhan_vien FROM nguoi_dung WHERE ma_nhan_vien LIKE 'NV%' ORDER BY LENGTH(ma_nhan_vien) DESC, ma_nhan_vien DESC LIMIT 1");
    $lastCode = $stmt->fetchColumn();

    $num = 1;
    if ($lastCode) {
        $num = (int) substr($lastCode, 2) + 1;
    }
    $msnv = 'NV' . str_pad($num, 3, '0', STR_PAD_LEFT);
    $mat_khau = password_hash('123456', PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO nguoi_dung (ma_nhan_vien, ho_ten, email, mat_khau, vai_tro, trang_thai) VALUES (?, ?, ?, ?, ?, 1)";
        $pdo->prepare($sql)->execute([$msnv, $ho_ten, $email, $mat_khau, $vai_tro]);
        writeLog($pdo, $_SESSION['user_id'], 'Thêm nhân viên', "Mã: $msnv");
        header("Location: ../admin_employees.php?msg=success");
    } catch (PDOException $e) {
        header("Location: ../admin_employees.php?error=exist");
    }
}

// --- SỬA NHÂN VIÊN ---
elseif ($action == 'edit') {
    $id = $_POST['id'];
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $vai_tro = $_POST['vai_tro'];
    $trang_thai = $_POST['trang_thai'];
    $nfc_uid = trim($_POST['nfc_uid']);
    $mat_khau_moi = trim($_POST['mat_khau']);

    $params = [$ho_ten, $email, $vai_tro, $trang_thai, $nfc_uid];
    $avatar_sql = "";

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $new_name = "user_" . $id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], "../assets/uploads/" . $new_name);
            $avatar_sql = ", avatar = ?";
            $params[] = $new_name;
        }
    }

    $pass_sql = "";
    if (!empty($mat_khau_moi)) {
        $pass_sql = ", mat_khau = ?";
        $params[] = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
    }

    $params[] = $id; // ID ở cuối cùng cho WHERE
    $sql = "UPDATE nguoi_dung SET ho_ten = ?, email = ?, vai_tro = ?, trang_thai = ?, nfc_uid = ? $avatar_sql $pass_sql WHERE id = ?";
    $pdo->prepare($sql)->execute($params);

    writeLog($pdo, $_SESSION['user_id'], 'Sửa nhân viên', "ID: $id");
    header("Location: ../admin_employees.php?msg=updated");
}

// --- XÓA NHÂN VIÊN ---
elseif ($action == 'delete') {
    $id = $_GET['id'];
    try {
        // Xóa dữ liệu liên quan để tránh lỗi Foreign Key
        $pdo->prepare("DELETE FROM dang_ky_ca WHERE nguoi_dung_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM ung_luong WHERE nguoi_dung_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM nhat_ky_he_thong WHERE nguoi_dung_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM nguoi_dung WHERE id = ?")->execute([$id]);

        writeLog($pdo, $_SESSION['user_id'], 'Xóa nhân viên', "ID: $id");
        header("Location: ../admin_employees.php?msg=deleted");
    } catch (Exception $e) {
        header("Location: ../admin_employees.php?error=constraint");
    }
}
?>