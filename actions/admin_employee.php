<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin($pdo);

$action = $_REQUEST['action'] ?? '';

// --- 1. THÊM NHÂN VIÊN ---
if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $role = $_POST['vai_tro'];
    $pass = password_hash('123456', PASSWORD_DEFAULT); // Mật khẩu mặc định

    // Kiểm tra trùng email
    $check = $pdo->prepare("SELECT id FROM nguoi_dung WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch())
        die("Lỗi: Email đã tồn tại trong hệ thống.");

    $sql = "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro, trang_thai) VALUES (?, ?, ?, ?, 1)";
    $pdo->prepare($sql)->execute([$ho_ten, $email, $hash, $role]);

    writeLog($pdo, $_SESSION['user_id'], 'Thêm nhân sự', "Đã tạo tài khoản cho: $ho_ten");
    header("Location: ../admin_employees.php");
}

// --- 2. SỬA NHÂN VIÊN (NFC + AVATAR + INFO) ---
elseif ($action == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $role = $_POST['vai_tro'];
    $status = $_POST['trang_thai'];
    $nfc_uid = trim($_POST['nfc_uid']) ?: null; // Nếu trống thì lưu NULL

    // 2.1 Cập nhật thông tin cơ bản & NFC
    $sql = "UPDATE nguoi_dung SET ho_ten = ?, email = ?, vai_tro = ?, trang_thai = ?, nfc_uid = ?";
    $params = [$ho_ten, $email, $role, $status, $nfc_uid];

    // Cập nhật mật khẩu nếu có nhập
    if (!empty($_POST['mat_khau'])) {
        $sql .= ", mat_khau = ?";
        $params[] = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
    }

    // 2.2 Xử lý Upload Avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = "user_" . $id . "_" . time() . "." . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], "../assets/uploads/" . $new_name)) {
                // Xóa ảnh cũ
                $stmt_old = $pdo->prepare("SELECT avatar FROM nguoi_dung WHERE id = ?");
                $stmt_old->execute([$id]);
                $old = $stmt_old->fetchColumn();
                if ($old && file_exists("../assets/uploads/" . $old))
                    @unlink("../assets/uploads/" . $old);

                $sql .= ", avatar = ?";
                $params[] = $new_name;
            }
        }
    }

    $sql .= " WHERE id = ?";
    $params[] = $id;

    try {
        $pdo->prepare($sql)->execute($params);
        writeLog($pdo, $_SESSION['user_id'], 'Cập nhật nhân sự', "ID: $id | Tên: $ho_ten | NFC: $nfc_uid");
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'nfc_uid'))
            die("Lỗi: Mã thẻ NFC này đã được gán cho người khác!");
        die("Lỗi hệ thống: " . $e->getMessage());
    }

    header("Location: ../admin_employees.php");
}

// --- 3. XÓA NHÂN VIÊN ---
elseif ($action == 'delete') {
    $id = (int) $_GET['id'];
    $pdo->prepare("DELETE FROM nguoi_dung WHERE id = ?")->execute([$id]);
    writeLog($pdo, $_SESSION['user_id'], 'Xóa nhân sự', "Đã xóa nhân viên ID: $id");
    header("Location: ../admin_employees.php");
}

header("Location: ../admin_employees.php");