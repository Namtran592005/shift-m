<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);

$ca_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE id = ?");
$stmt->execute([$ca_id]);
$ca = $stmt->fetch();

if (!$ca)
    die("Ca làm không tồn tại");

// Lấy danh sách nhân viên trong ca
$sql_users = "SELECT dk.id as dk_id, u.ho_ten, u.email, u.avatar, u.vai_tro, cc.gio_check_in, cc.gio_check_out 
              FROM dang_ky_ca dk 
              JOIN nguoi_dung u ON dk.nguoi_dung_id = u.id 
              LEFT JOIN cham_cong cc ON dk.id = cc.dang_ky_ca_id
              WHERE dk.ca_lam_id = ? AND dk.trang_thai = 'da_duyet'";
$stmt_users = $pdo->prepare($sql_users);
$stmt_users->execute([$ca_id]);
$participants = $stmt_users->fetchAll();

// Lấy nhân viên CÓ THỂ thêm (chưa có trong ca này)
$sql_avail = "SELECT * FROM nguoi_dung 
              WHERE vai_tro = 'nhan_vien' AND trang_thai = 1 
              AND id NOT IN (SELECT nguoi_dung_id FROM dang_ky_ca WHERE ca_lam_id = ?) 
              ORDER BY ho_ten ASC";
$stmt_avail = $pdo->prepare($sql_avail);
$stmt_avail->execute([$ca_id]);
$available_users = $stmt_avail->fetchAll();

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">

            <!-- HEADER MOBILE: XẾP DỌC -->
            <div class="flex justify-between items-start mb-20 flex-col-mobile">
                <div style="width: 100%;">
                    <a href="admin_calendar.php"
                        style="font-size: 13px; color: var(--text-muted); display: flex; align-items: center; gap: 5px; margin-bottom: 5px;">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại lịch
                    </a>
                    <h2 style="margin: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <?php echo $ca['ten_ca']; ?>
                        <span class="badge <?php echo $ca['trang_thai']; ?>"
                            style="font-size: 12px; vertical-align: middle;">
                            <?php echo $ca['trang_thai'] == 'mo' ? 'Đang mở' : 'Đóng'; ?>
                        </span>
                    </h2>
                    <div style="color: var(--text-muted); margin-top: 5px; font-size: 14px;">
                        <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i>
                        <?php echo date('d/m/Y', strtotime($ca['ngay'])); ?>
                        &nbsp;|&nbsp;
                        <i class="fa-regular fa-clock" style="margin-right: 5px;"></i>
                        <?php echo substr($ca['gio_bat_dau'], 0, 5) . ' - ' . substr($ca['gio_ket_thuc'], 0, 5); ?>
                    </div>
                </div>

                <!-- BUTTON GROUP: Full width on mobile -->
                <div class="flex gap-10 w-full flex-col-mobile">
                    <?php if (!empty($ca['ma_qr'])): ?>
                        <a href="view_qr.php?code=<?php echo $ca['ma_qr']; ?>" target="_blank"
                            class="btn btn-outline btn-sm w-full">
                            <i class="fa-solid fa-qrcode"></i> QR Code
                        </a>
                    <?php endif; ?>
                    <button onclick="openModal()" class="btn btn-primary w-full">
                        <i class="fa-solid fa-user-plus"></i> Thêm nhân sự
                    </button>
                </div>
            </div>

            <!-- DANH SÁCH NHÂN VIÊN -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách nhân viên (<?php echo count($participants); ?>)</h3>
                </div>

                <!-- Table tự động scroll ngang nhờ CSS -->
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Avatar</th>
                            <th>Họ tên & Email</th>
                            <th>Giờ vào</th>
                            <th>Giờ ra</th>
                            <th class="text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getAvatar($p['avatar'], $p['vai_tro']); ?>"
                                        style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?php echo $p['ho_ten']; ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo $p['email']; ?></div>
                                </td>
                                <td>
                                    <?php echo $p['gio_check_in'] ? "<span class='badge da_duyet'>" . date('H:i', strtotime($p['gio_check_in'])) . "</span>" : "<span style='color:#9ca3af'>--:--</span>"; ?>
                                </td>
                                <td>
                                    <?php echo $p['gio_check_out'] ? "<span class='badge' style='background:#e0e7ff; color:#3730a3'>" . date('H:i', strtotime($p['gio_check_out'])) . "</span>" : "<span style='color:#9ca3af'>--:--</span>"; ?>
                                </td>
                                <td class="text-right">
                                    <a href="actions/admin_remove_user.php?dk_id=<?php echo $p['dk_id']; ?>&ca_id=<?php echo $ca_id; ?>"
                                        onclick="return confirm('Xóa nhân viên khỏi ca này?')"
                                        class="btn btn-outline btn-sm" style="color: var(--danger); border-color: #fecaca;">
                                        <i class="fa-solid fa-xmark"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($participants)): ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                                    Chưa có nhân viên nào.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- MODAL THÊM NHÂN VIÊN (Responsive) -->
    <div id="addModal" class="modal-overlay">
        <!-- class modal-box đã được CSS xử lý full màn hình trên mobile -->
        <div class="card modal-box"
            style="width: 500px; max-height: 90vh; display: flex; flex-direction: column; padding: 0;">
            <div
                style="padding: 15px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f9fafb;">
                <h3 style="margin: 0; font-size: 16px;">Chọn nhân viên</h3>
                <i class="fa-solid fa-xmark" onclick="closeModal()"
                    style="cursor: pointer; color: var(--text-muted); font-size: 20px; padding: 5px;"></i>
            </div>

            <form action="actions/admin_assign_user.php" method="POST"
                style="display: flex; flex-direction: column; height: 100%;">
                <input type="hidden" name="ca_id" value="<?php echo $ca_id; ?>">

                <div style="padding: 15px;">
                    <input type="text" id="searchInput" onkeyup="filterUsers()" class="form-control"
                        placeholder="🔍 Tìm theo tên hoặc email..." style="width: 100%;">
                </div>

                <div style="flex: 1; overflow-y: auto; padding: 0 15px;" id="userList">
                    <?php foreach ($available_users as $u): ?>
                        <label class="user-item"
                            style="display: flex; align-items: center; padding: 12px 10px; border-bottom: 1px solid #f3f4f6; cursor: pointer;">
                            <input type="checkbox" name="user_ids[]" value="<?php echo $u['id']; ?>"
                                style="width: 20px; height: 20px; margin-right: 15px;">
                            <img src="<?php echo getAvatar($u['avatar']); ?>"
                                style="width: 40px; height: 40px; border-radius: 50%; margin-right: 12px; object-fit: cover;">
                            <div>
                                <div style="font-weight: 600; font-size: 14px;"><?php echo $u['ho_ten']; ?></div>
                                <div style="font-size: 12px; color: var(--text-muted);"><?php echo $u['email']; ?></div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div
                    style="padding: 15px 20px; border-top: 1px solid var(--border); text-align: right; background: #fff;">
                    <button type="button" class="btn btn-outline" onclick="closeModal()"
                        style="margin-right: 10px;">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm đã chọn</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('addModal').style.display = 'none'; }
        function filterUsers() {
            var input, filter, container, labels, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            container = document.getElementById("userList");
            labels = container.getElementsByTagName("label");
            for (i = 0; i < labels.length; i++) {
                txtValue = labels[i].textContent || labels[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) { labels[i].style.display = "flex"; } else { labels[i].style.display = "none"; }
            }
        }
        window.onclick = function (event) { if (event.target.classList.contains('modal-overlay')) { closeModal(); } }
    </script>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
    </style>
</body>

</html>
<?php include 'includes/footer.php'; ?>