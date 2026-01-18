<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);
$is_open = $pdo->query("SELECT gia_tri FROM cai_dat WHERE ten_cau_hinh = 'cho_phep_ung'")->fetchColumn();
$thang = date('m');
$nam = date('Y');
$luong_tam_tinh = $pdo->query("SELECT SUM(so_gio_lam * cl.luong_gio * cl.he_so_luong) FROM cham_cong cc JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE dkc.nguoi_dung_id = {$user['id']} AND MONTH(cl.ngay) = $thang AND YEAR(cl.ngay) = $nam")->fetchColumn() ?? 0;
$stmt_his = $pdo->prepare("SELECT * FROM ung_luong WHERE nguoi_dung_id = ? AND thang = ? AND nam = ?");
$stmt_his->execute([$user['id'], $thang, $nam]);
$history = $stmt_his->fetchAll();
$da_ung = 0;
foreach ($history as $h) {
    if ($h['trang_thai'] == 'da_duyet')
        $da_ung += $h['so_tien_duyet'];
}
$kha_dung = $luong_tam_tinh - $da_ung;
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_open == 1) {
    $amount = $_POST['amount'];
    if ($amount > $kha_dung) {
        $msg = "Vượt quá hạn mức!";
    } elseif ($amount <= 0) {
        $msg = "Không hợp lệ!";
    } else {
        $pdo->prepare("INSERT INTO ung_luong (nguoi_dung_id, thang, nam, so_tien_yeu_cau) VALUES (?, ?, ?, ?)")->execute([$user['id'], $thang, $nam, $amount]);
        header("Location: salary_advance.php");
        exit();
    }
}
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Ứng lương tháng <?php echo "$thang/$nam"; ?></h2>
            <!-- CHIA CỘT MOBILE -->
            <div class="flex gap-20 flex-col-mobile">
                <div style="flex: 1;">
                    <div class="card mb-20"
                        style="background: linear-gradient(135deg, #0f172a, #334155); color: white;">
                        <div style="opacity: 0.8; font-size: 13px;">Hạn mức khả dụng</div>
                        <div style="font-size: 32px; font-weight: bold; margin: 5px 0;">
                            <?php echo formatMoney($kha_dung); ?></div>
                        <div style="font-size: 12px; opacity: 0.6;">Tổng lương:
                            <?php echo formatMoney($luong_tam_tinh); ?> <br> Đã ứng: <?php echo formatMoney($da_ung); ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Yêu cầu ứng mới</h3>
                        </div>
                        <?php if ($is_open == 0): ?>
                            <div class="text-center" style="padding: 20px; color: var(--text-muted);"><i
                                    class="fa-solid fa-lock" style="font-size: 40px;"></i>
                                <p>Cổng đang đóng.</p>
                            </div>
                        <?php else: ?>
                            <?php if ($msg): ?>
                                <div
                                    style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px;">
                                    <?php echo $msg; ?></div><?php endif; ?>
                            <form method="POST">
                                <div class="input-group"><label>Số tiền (VNĐ)</label><input type="number" name="amount"
                                        class="form-control" max="<?php echo $kha_dung; ?>" required></div>
                                <button type="submit" class="btn btn-primary w-full">Gửi yêu cầu</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="flex: 1;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lịch sử</h3>
                        </div>
                        <ul style="list-style: none;">
                            <?php foreach ($history as $h): ?>
                                <li style="border-bottom: 1px solid var(--border); padding: 10px 0;">
                                    <div class="flex justify-between"><span
                                            style="font-weight: 600;"><?php echo formatMoney($h['so_tien_yeu_cau']); ?></span><span
                                            class="badge <?php echo $h['trang_thai']; ?>"><?php if ($h['trang_thai'] == 'cho_duyet')
                                                   echo 'Chờ duyệt';
                                               elseif ($h['trang_thai'] == 'da_duyet')
                                                   echo 'Đã duyệt: ' . formatMoney($h['so_tien_duyet']);
                                               else
                                                   echo 'Từ chối'; ?></span>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        <?php echo date('d/m/Y H:i', strtotime($h['ngay_yeu_cau'])); ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
<?php include 'includes/footer.php'; ?>