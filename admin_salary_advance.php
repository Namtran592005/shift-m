<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);
if (isset($_POST['toggle_status'])) {
    $val = $_POST['status'];
    $pdo->prepare("UPDATE cai_dat SET gia_tri = ? WHERE ten_cau_hinh = 'cho_phep_ung'")->execute([$val]);
    writeLog($pdo, $user['id'], 'Cấu hình', "Đổi trạng thái ứng lương: $val");
    header("Location: admin_salary_advance.php");
    exit();
}
if (isset($_POST['action_request'])) {
    $req_id = $_POST['req_id'];
    $status = $_POST['status'];
    $amount = $_POST['amount_approve'] ?? 0;
    $pdo->prepare("UPDATE ung_luong SET trang_thai = ?, so_tien_duyet = ? WHERE id = ?")->execute([$status, $amount, $req_id]);
    writeLog($pdo, $user['id'], 'Duyệt Ứng lương', "ID: $req_id | Status: $status");
    header("Location: admin_salary_advance.php");
    exit();
}
$is_open = $pdo->query("SELECT gia_tri FROM cai_dat WHERE ten_cau_hinh = 'cho_phep_ung'")->fetchColumn();
$requests = $pdo->query("SELECT ul.*, u.ho_ten, u.email FROM ung_luong ul JOIN nguoi_dung u ON ul.nguoi_dung_id = u.id ORDER BY ul.ngay_yeu_cau DESC")->fetchAll();
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <!-- HEADER MOBILE: STACK DỌC -->
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <h2>Quản lý Ứng lương</h2>
                <div class="flex gap-10 flex-col-mobile">
                    <a href="actions/export_salary.php?m=<?php echo date('m'); ?>&y=<?php echo date('Y'); ?>"
                        class="btn btn-outline w-full" style="color: #059669; border-color: #059669;">
                        <i class="fa-solid fa-file-excel"></i> Xuất Bảng Lương
                    </a>
                    <form method="POST"
                        class="flex items-center gap-10 bg-white p-2 rounded border justify-between w-full">
                        <span style="font-size: 13px; font-weight: 600;">Cổng ứng:</span>
                        <input type="hidden" name="toggle_status" value="1">
                        <div class="flex gap-10 items-center">
                            <?php if ($is_open == 1): ?>
                                <span class="badge da_duyet">Mở</span><button type="submit" name="status" value="0"
                                    class="btn btn-danger btn-sm">Đóng</button>
                            <?php else: ?>
                                <span class="badge huy">Đóng</span><button type="submit" name="status" value="1"
                                    class="btn btn-primary btn-sm">Mở</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <table style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Nhân viên</th>
                            <th>Ngày gửi</th>
                            <th>Xin ứng</th>
                            <th>Duyệt</th>
                            <th>Trạng thái</th>
                            <th class="text-right">Xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?php echo $r['ho_ten']; ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo $r['email']; ?></div>
                                </td>
                                <td><?php echo date('d/m H:i', strtotime($r['ngay_yeu_cau'])); ?></td>
                                <td style="font-weight: 600; color: var(--primary);">
                                    <?php echo formatMoney($r['so_tien_yeu_cau']); ?></td>
                                <td><?php echo ($r['trang_thai'] == 'da_duyet') ? formatMoney($r['so_tien_duyet']) : '-'; ?>
                                </td>
                                <td><span
                                        class="badge <?php echo $r['trang_thai']; ?>"><?php echo $r['trang_thai']; ?></span>
                                </td>
                                <td class="text-right">
                                    <?php if ($r['trang_thai'] == 'cho_duyet'): ?>
                                        <button
                                            onclick="openApproveModal(<?php echo $r['id']; ?>, <?php echo $r['so_tien_yeu_cau']; ?>, '<?php echo $r['ho_ten']; ?>')"
                                            class="btn btn-primary btn-sm">Duyệt</button>
                                        <form method="POST" style="display: inline-block;"><input type="hidden"
                                                name="action_request" value="1"><input type="hidden" name="req_id"
                                                value="<?php echo $r['id']; ?>"><input type="hidden" name="status"
                                                value="tu_choi"><button type="submit" class="btn btn-outline btn-sm"
                                                style="color: var(--danger);">Hủy</button></form>
                                    <?php else: ?><span>Đã xong</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <!-- Modal Duyệt -->
    <div id="approveModal" class="modal-overlay">
        <div class="card modal-box"
            style="width: 400px; padding: 20px; height: auto !important; max-height: auto !important;">
            <h3>Duyệt đơn</h3>
            <p>Nhân viên: <b id="modalUser"></b></p>
            <form method="POST"><input type="hidden" name="action_request" value="1"><input type="hidden" name="req_id"
                    id="modalReqId"><input type="hidden" name="status" value="da_duyet">
                <div class="input-group"><label>Số tiền duyệt</label><input type="number" name="amount_approve"
                        id="modalAmount" class="form-control" required></div>
                <div class="flex gap-10 mt-20 flex-col-mobile"><button type="button" class="btn btn-outline w-full"
                        onclick="document.getElementById('approveModal').style.display='none'">Đóng</button><button
                        type="submit" class="btn btn-primary w-full">OK</button></div>
            </form>
        </div>
    </div>
    <script>function openApproveModal(id, amount, name) { document.getElementById('modalReqId').value = id; document.getElementById('modalAmount').value = amount; document.getElementById('modalUser').innerText = name; document.getElementById('approveModal').style.display = 'flex'; }</script>
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