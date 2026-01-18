<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/SimpleXLSX.php'; // Thư viện ĐỌC Excel

// Check Admin
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['import']) && isset($_FILES['file'])) {

    // Đọc file Excel tải lên
    if ($xlsx = Shuchkin\SimpleXLSX::parse($_FILES['file']['tmp_name'])) {

        $count_success = 0;
        $count_fail = 0;
        $rows = $xlsx->rows(); // Lấy toàn bộ dữ liệu

        // Bỏ qua dòng tiêu đề (Dòng 0)
        unset($rows[0]);

        foreach ($rows as $r) {
            $ho_ten = trim($r[0] ?? '');
            $email = trim($r[1] ?? '');
            $vai_tro = strtolower(trim($r[2] ?? 'nhan_vien'));

            if (empty($ho_ten) || empty($email)) {
                $count_fail++;
                continue;
            }

            // Check trùng Email
            $stmt = $pdo->prepare("SELECT count(*) FROM nguoi_dung WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $count_fail++;
                continue;
            }

            // Tạo dữ liệu
            $msnv = generateEmployeeCode($pdo);
            $mat_khau = password_hash('123456', PASSWORD_DEFAULT);
            if ($vai_tro !== 'admin')
                $vai_tro = 'nhan_vien';

            try {
                $sql = "INSERT INTO nguoi_dung (ma_nhan_vien, ho_ten, email, mat_khau, vai_tro, trang_thai) VALUES (?, ?, ?, ?, ?, 1)";
                $pdo->prepare($sql)->execute([$msnv, $ho_ten, $email, $mat_khau, $vai_tro]);
                $count_success++;
            } catch (Exception $e) {
                $count_fail++;
            }
        }

        $msg = "Nhập thành công: $count_success. Lỗi/Trùng: $count_fail.";
        header("Location: ../admin_employees.php?msg=" . urlencode($msg));

    } else {
        // Lỗi không đọc được file
        header("Location: ../admin_employees.php?error=read_error");
    }
}
?>