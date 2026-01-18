<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
// Check quyền admin... (đoạn này giữ nguyên như cũ)

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- HÀM HỖ TRỢ UPLOAD ẢNH ---
function uploadImage($fileInputName, $prefix, $id) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $newName = $prefix . "_" . $id . "_" . time() . "." . $ext;
            $dest = "../assets/uploads/" . $newName;
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $dest)) {
                return $newName;
            }
        }
    }
    return null;
}

// --- XỬ LÝ THÊM ---
if ($action == 'add') {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $vai_tro = $_POST['vai_tro'];
    $so_cccd = trim($_POST['so_cccd']); // Mới
    
    // Sinh mã NV
    $msnv = generateEmployeeCode($pdo);
    $mat_khau = password_hash('123456', PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO nguoi_dung (ma_nhan_vien, ho_ten, email, mat_khau, vai_tro, so_cccd, trang_thai) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $pdo->prepare($sql)->execute([$msnv, $ho_ten, $email, $mat_khau, $vai_tro, $so_cccd]);
        $new_id = $pdo->lastInsertId();

        // Upload CCCD (Sau khi có ID)
        $cccd_truoc = uploadImage('cccd_truoc', 'cccd_front', $new_id);
        $cccd_sau = uploadImage('cccd_sau', 'cccd_back', $new_id);
        
        if ($cccd_truoc || $cccd_sau) {
            $pdo->prepare("UPDATE nguoi_dung SET anh_cccd_truoc = ?, anh_cccd_sau = ? WHERE id = ?")
                ->execute([$cccd_truoc, $cccd_sau, $new_id]);
        }

        header("Location: ../admin_employees.php?msg=success");
    } catch (PDOException $e) {
        header("Location: ../admin_employees.php?error=exist");
    }
}

// --- XỬ LÝ SỬA ---
elseif ($action == 'edit') {
    $id = $_POST['id'];
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $vai_tro = $_POST['vai_tro'];
    $trang_thai = $_POST['trang_thai'];
    $nfc_uid = trim($_POST['nfc_uid']);
    $so_cccd = trim($_POST['so_cccd']); // Mới
    $mat_khau_moi = trim($_POST['mat_khau']);

    // Build câu SQL update cơ bản
    $sql = "UPDATE nguoi_dung SET ho_ten=?, email=?, vai_tro=?, trang_thai=?, nfc_uid=?, so_cccd=? WHERE id=?";
    $params = [$ho_ten, $email, $vai_tro, $trang_thai, $nfc_uid, $so_cccd, $id];
    $pdo->prepare($sql)->execute($params);

    // Update riêng lẻ từng ảnh nếu có upload
    $avt = uploadImage('avatar', 'user', $id);
    if ($avt) $pdo->prepare("UPDATE nguoi_dung SET avatar = ? WHERE id = ?")->execute([$avt, $id]);

    $f = uploadImage('cccd_truoc', 'cccd_front', $id);
    if ($f) $pdo->prepare("UPDATE nguoi_dung SET anh_cccd_truoc = ? WHERE id = ?")->execute([$f, $id]);

    $b = uploadImage('cccd_sau', 'cccd_back', $id);
    if ($b) $pdo->prepare("UPDATE nguoi_dung SET anh_cccd_sau = ? WHERE id = ?")->execute([$b, $id]);

    // Update mật khẩu
    if (!empty($mat_khau_moi)) {
        $pdo->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?")->execute([password_hash($mat_khau_moi, PASSWORD_DEFAULT), $id]);
    }

    header("Location: ../admin_employees.php?msg=updated");
}

elseif ($action == 'delete') {
    // ... (Giữ nguyên logic xóa cũ)
    $id = $_GET['id'];
    $pdo->prepare("DELETE FROM dang_ky_ca WHERE nguoi_dung_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM ung_luong WHERE nguoi_dung_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM nhat_ky_he_thong WHERE nguoi_dung_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM nguoi_dung WHERE id = ?")->execute([$id]);
    header("Location: ../admin_employees.php?msg=deleted");
}
?>