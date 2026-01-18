<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);
if (isset($_GET['gen_qr'])) {
    $id = $_GET['gen_qr'];
    try {
        $code = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $code = uniqid('secure_', true);
    }
    $pdo->prepare("UPDATE ca_lam SET ma_qr = ? WHERE id = ?")->execute([$code, $id]);
    writeLog($pdo, $user['id'], 'Tạo QR', "Ca ID: $id");
    header("Location: admin_ca_lam.php");
    exit();
}
$stmt = $pdo->query("SELECT * FROM ca_lam ORDER BY ngay DESC");
$list_ca = $stmt->fetchAll();
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="card mb-20">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa-solid fa-plus-circle"></i> Tạo ca làm việc mới</h3>
                </div>

                <!-- FORM ĐÃ ĐƯỢC CẬP NHẬT MOBILE -->
                <form action="actions/admin_add_ca.php" method="POST">
                    <div class="flex gap-20 flex-col-mobile" style="margin-bottom: 15px;">
                        <div class="input-group" style="flex: 2;">
                            <label>Tên ca</label><input type="text" name="ten_ca" class="form-control" required>
                        </div>
                        <div class="input-group" style="flex: 1;">
                            <label>Ngày</label><input type="date" name="ngay" class="form-control" required>
                        </div>
                        <div class="input-group" style="flex: 1;">
                            <label>Lương/Giờ</label><input type="number" name="luong_gio" class="form-control"
                                value="25000" step="1000" required>
                        </div>
                    </div>
                    <div class="flex gap-20 items-center flex-col-mobile">
                        <div class="input-group" style="flex: 1;">
                            <label>Bắt đầu</label><input type="time" name="gio_bat_dau" class="form-control" required>
                        </div>
                        <div class="input-group" style="flex: 1;">
                            <label>Kết thúc</label><input type="time" name="gio_ket_thuc" class="form-control" required>
                        </div>
                        <div class="input-group" style="flex: 1;">
                            <label>Hệ số lương</label>
                            <select name="he_so_luong" class="form-control"
                                style="font-weight: bold; color: var(--primary);">
                                <option value="1.0">x 1.0 (Thường)</option>
                                <option value="1.5">x 1.5 (CN/Đêm)</option>
                                <option value="2.0">x 2.0 (Lễ tết)</option>
                                <option value="3.0">x 3.0 (Đặc biệt)</option>
                            </select>
                        </div>
                        <div style="flex: 2; display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary w-full"
                                style="height: 42px; margin-top: 14px;"><i class="fa-solid fa-floppy-disk"></i> Lưu
                                ca</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách ca</h3>
                </div>
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Thông tin</th>
                            <th>Thời gian</th>
                            <th>Lương & Hệ số</th>
                            <th>QR Code</th>
                            <th class="text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list_ca as $ca): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo $ca['ten_ca']; ?></div>
                                    <div style="font-size: 13px; color: var(--text-muted);">
                                        <?php echo date('d/m/Y', strtotime($ca['ngay'])); ?></div>
                                </td>
                                <td><span
                                        class="badge"><?php echo substr($ca['gio_bat_dau'], 0, 5) . '-' . substr($ca['gio_ket_thuc'], 0, 5); ?></span>
                                </td>
                                <td>
                                    <div><?php echo formatMoney($ca['luong_gio']); ?></div>
                                    <?php if ($ca['he_so_luong'] > 1.0): ?>
                                        <span class="badge cho_duyet"
                                            style="font-size: 11px;">x<?php echo $ca['he_so_luong']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($ca['ma_qr'])): ?><a href="?gen_qr=<?php echo $ca['id']; ?>"
                                            class="btn btn-outline btn-sm">Tạo mã</a><?php else: ?>
                                        <div class="flex gap-10"><a href="view_qr.php?code=<?php echo $ca['ma_qr']; ?>"
                                                target="_blank" class="btn btn-primary btn-sm" style="background: #0f172a;">Mở
                                                QR</a><a href="?gen_qr=<?php echo $ca['id']; ?>" class="btn btn-outline btn-sm"
                                                onclick="return confirm('Tạo lại mã?')"><i class="fa-solid fa-refresh"></i></a>
                                        </div><?php endif; ?>
                                </td>
                                <td class="text-right"><a href="actions/admin_xoa_ca.php?id=<?php echo $ca['id']; ?>"
                                        onclick="return confirm('Xóa?')" class="btn btn-danger btn-sm"><i
                                            class="fa-solid fa-trash"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>
<?php include 'includes/footer.php'; ?>