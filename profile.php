<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);
if (!array_key_exists('avatar', $user)) {
    $user['avatar'] = null;
}
$msg = '';
$msg_type = '';
$khoa_avatar = getConfig($pdo, 'khoa_doi_avatar');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $pass_moi = trim($_POST['mat_khau_moi'] ?? '');
    if (empty($ho_ten)) {
        $msg = "Lỗi dữ liệu.";
        $msg_type = "danger";
    } else {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
            if ($khoa_avatar == 1 && $user['vai_tro'] != 'admin') {
                $msg = "Đã bị khóa.";
                $msg_type = "danger";
            } else {
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $new_name = "user_" . $user['id'] . "_" . time() . "." . $ext;
                    move_uploaded_file($_FILES['avatar']['tmp_name'], "assets/uploads/" . $new_name);
                    if (!empty($user['avatar']) && file_exists("assets/uploads/" . $user['avatar'])) {
                        @unlink("assets/uploads/" . $user['avatar']);
                    }
                    $pdo->prepare("UPDATE nguoi_dung SET avatar = ? WHERE id = ?")->execute([$new_name, $user['id']]);
                    $user['avatar'] = $new_name;
                }
            }
        }
        if (empty($msg) || $msg_type == 'success') {
            $sql = "UPDATE nguoi_dung SET ho_ten = ?";
            $params = [$ho_ten];
            if (!empty($pass_moi)) {
                $sql .= ", mat_khau = ?";
                $params[] = password_hash($pass_moi, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = ?";
            $params[] = $user['id'];
            $pdo->prepare($sql)->execute($params);
            $msg = "Cập nhật thành công!";
            $msg_type = "success";
            $user['ho_ten'] = $ho_ten;
        }
    }
}
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Cài đặt tài khoản</h2>
            <?php if ($msg): ?>
                <div
                    style="padding: 12px; margin-bottom: 20px; border-radius: 6px; background: <?php echo $msg_type == 'success' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $msg_type == 'success' ? '#065f46' : '#b91c1c'; ?>;">
                    <?php echo $msg; ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- THÊM CLASS flex-col-mobile và bỏ max-width -->
                <div class="flex gap-20 flex-col-mobile">
                    <div style="flex: 1;">
                        <div class="card text-center">
                            <div style="position: relative; display: inline-block;">
                                <img src="<?php echo getAvatar($user['avatar'], $user['vai_tro']); ?>"
                                    style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f1f5f9;">
                                <?php if ($khoa_avatar == 0 || $user['vai_tro'] == 'admin'): ?>
                                    <label for="fileInput"
                                        style="position: absolute; bottom: 5px; right: 5px; background: var(--primary); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;"><i
                                            class="fa-solid fa-camera"></i></label>
                                    <input type="file" name="avatar" id="fileInput" style="display: none;"
                                        onchange="previewImage(this)">
                                <?php endif; ?>
                            </div>
                            <h3 style="margin: 15px 0 5px 0;"><?php echo htmlspecialchars($user['ho_ten']); ?></h3>
                            <p style="color: var(--text-muted); font-size: 13px;"><?php echo $user['email']; ?></p>
                        </div>
                    </div>
                    <div style="flex: 2;">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cá nhân</h3>
                            </div>
                            <div class="input-group"><label>Email đăng nhập</label><input type="text"
                                    class="form-control" value="<?php echo $user['email']; ?>" disabled
                                    style="background: #f3f4f6;"></div>
                            <div class="input-group"><label>Họ và tên</label><input type="text" name="ho_ten"
                                    class="form-control" value="<?php echo htmlspecialchars($user['ho_ten']); ?>"
                                    required></div>
                            <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">
                            <div class="input-group"><label>Mật khẩu mới</label><input type="password"
                                    name="mat_khau_moi" class="form-control" placeholder="Để trống nếu không muốn đổi">
                            </div>
                            <div class="text-right"><button type="submit" class="btn btn-primary w-full"><i
                                        class="fa-solid fa-floppy-disk"></i> Lưu thay đổi</button></div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
    <script>function previewImage(i) { if (i.files && i.files[0]) { var r = new FileReader(); r.onload = function (e) { document.querySelector('.card img').src = e.target.result; }; r.readAsDataURL(i.files[0]); } }</script>
</body>

</html>
<?php include 'includes/footer.php'; ?>