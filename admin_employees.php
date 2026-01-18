<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);
$stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id != ? ORDER BY id DESC");
$stmt->execute([$user['id']]);
$employees = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <!-- HEADER: XẾP DỌC TRÊN MOBILE -->
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <!-- <h2 style="margin: 0;">Quản lý Nhân sự</h2> -->
                <button onclick="openAddModal()" class="btn btn-primary w-full">
                    <i class="fa-solid fa-user-plus"></i> Thêm nhân viên mới
                </button>
            </div>

            <div class="card">
                <!-- Bảng sẽ tự động có thanh cuộn ngang nhờ CSS -->
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Avatar</th>
                            <th>Họ tên & Email</th>
                            <th>Mã thẻ NFC</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th class="text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><img src="<?php echo getAvatar($emp['avatar'], $emp['vai_tro']); ?>"
                                        style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;"></td>
                                <td>
                                    <div style="font-weight: 600; font-size: 14px;">
                                        <?php echo htmlspecialchars($emp['ho_ten']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo $emp['email']; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($emp['nfc_uid']): ?>
                                        <span class="badge mo"
                                            style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;"><i
                                                class="fa-solid fa-id-card"></i> <?php echo $emp['nfc_uid']; ?></span>
                                    <?php else: ?><span style="color: #cbd5e1; font-size: 12px;"><i>Chưa
                                                gán</i></span><?php endif; ?>
                                </td>
                                <td><span
                                        class="badge <?php echo $emp['vai_tro'] == 'admin' ? 'cho_duyet' : 'mo'; ?>"><?php echo $emp['vai_tro'] == 'admin' ? 'Quản trị' : 'Nhân viên'; ?></span>
                                </td>
                                <td><?php echo $emp['trang_thai'] == 1 ? '<span style="color: var(--success); font-size: 12px;">● Hoạt động</span>' : '<span style="color: var(--danger); font-size: 12px;">● Đã khóa</span>'; ?>
                                </td>
                                <td class="text-right">
                                    <button onclick='openEditModal(<?php echo json_encode($emp); ?>)'
                                        class="btn btn-outline btn-sm"><i class="fa-solid fa-pen-to-square"></i></button>
                                    <a href="actions/admin_employee.php?action=delete&id=<?php echo $emp['id']; ?>"
                                        onclick="return confirm('Xóa nhân viên này?')" class="btn btn-outline btn-sm"
                                        style="color: var(--danger); border-color: #fecaca;"><i
                                            class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL 1: THÊM MỚI -->
    <div id="addModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Thêm nhân viên</h3><i class="fa-solid fa-xmark close-btn" onclick="closeAllModals()"></i>
            </div>
            <form action="actions/admin_employee.php" method="POST" style="padding: 20px;">
                <input type="hidden" name="action" value="add">
                <div class="input-group"><label>Họ tên</label><input type="text" name="ho_ten" class="form-control"
                        required></div>
                <div class="input-group"><label>Email</label><input type="email" name="email" class="form-control"
                        required></div>
                <div class="input-group"><label>Vai trò</label><select name="vai_tro" class="form-control">
                        <option value="nhan_vien">Nhân viên</option>
                        <option value="admin">Quản trị viên</option>
                    </select></div>
                <button type="submit" class="btn btn-primary w-full">Tạo tài khoản</button>
            </form>
        </div>
    </div>

    <!-- MODAL 2: SỬA -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <h3>Cập nhật nhân viên</h3><i class="fa-solid fa-xmark close-btn" onclick="closeAllModals()"></i>
            </div>
            <form action="actions/admin_employee.php" method="POST" enctype="multipart/form-data"
                style="padding: 20px;">
                <input type="hidden" name="action" value="edit"><input type="hidden" name="id" id="edit_id">
                <div class="text-center mb-20">
                    <img id="edit_avatar_preview" src=""
                        style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--border);">
                    <div style="margin-top: 10px;">
                        <label for="admin_upload_avt" class="btn btn-outline btn-sm" style="cursor: pointer;"><i
                                class="fa-solid fa-camera"></i> Đổi ảnh</label>
                        <input type="file" name="avatar" id="admin_upload_avt" style="display: none;"
                            onchange="previewEditImage(this)">
                    </div>
                </div>
                <!-- Flex mobile cho 2 ô trên 1 dòng -->
                <div class="flex gap-10 flex-col-mobile">
                    <div class="input-group" style="flex:1"><label>Họ tên</label><input type="text" name="ho_ten"
                            id="edit_name" class="form-control" required></div>
                    <div class="input-group" style="flex:1"><label>Mã NFC (UID)</label><input type="text" name="nfc_uid"
                            id="edit_nfc" class="form-control" placeholder="Quét thẻ..."
                            style="background: #fffbeb; border-color: #fde68a;"></div>
                </div>

                <div class="input-group"><label>Email</label><input type="email" name="email" id="edit_email"
                        class="form-control" required></div>
                <div class="input-group"><label>Mật khẩu mới</label><input type="password" name="mat_khau"
                        class="form-control" placeholder="••••••"></div>

                <div class="flex gap-10 flex-col-mobile">
                    <div class="input-group" style="flex:1"><label>Vai trò</label><select name="vai_tro" id="edit_role"
                            class="form-control">
                            <option value="nhan_vien">Nhân viên</option>
                            <option value="admin">Quản trị viên</option>
                        </select></div>
                    <div class="input-group" style="flex:1"><label>Trạng thái</label><select name="trang_thai"
                            id="edit_status" class="form-control">
                            <option value="1">Hoạt động</option>
                            <option value="0">Khóa</option>
                        </select></div>
                </div>
                <button type="submit" class="btn btn-primary w-full">Lưu thay đổi</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeAllModals() { document.getElementById('addModal').style.display = 'none'; document.getElementById('editModal').style.display = 'none'; }
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id; document.getElementById('edit_name').value = data.ho_ten; document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_role').value = data.vai_tro; document.getElementById('edit_status').value = data.trang_thai; document.getElementById('edit_nfc').value = data.nfc_uid || '';
            let src = (data.avatar) ? 'assets/uploads/' + data.avatar : (data.vai_tro === 'admin' ? 'assets/img/default_admin.png' : 'assets/img/default.png');
            document.getElementById('edit_avatar_preview').src = src;
            document.getElementById('editModal').style.display = 'flex'; setTimeout(() => document.getElementById('edit_nfc').focus(), 200);
        }
        function previewEditImage(input) { if (input.files && input.files[0]) { var reader = new FileReader(); reader.onload = function (e) { document.getElementById('edit_avatar_preview').src = e.target.result; }; reader.readAsDataURL(input.files[0]); } }
        window.onclick = function (e) { if (e.target.classList.contains('modal-overlay')) closeAllModals(); }
    </script>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }

        .modal-box {
            background: white;
            width: 500px;
            border-radius: 12px;
            overflow: hidden;
            max-height: 95vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }

        .close-btn {
            cursor: pointer;
            color: #94a3b8;
        }
    </style>
</body>
<?php include 'includes/footer.php'; ?>