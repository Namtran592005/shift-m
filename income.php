<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);
$thang = isset($_GET['m']) ? (int) $_GET['m'] : (int) date('m');
$nam = isset($_GET['y']) ? (int) $_GET['y'] : (int) date('Y');
if ($thang < 1) {
    $thang = 12;
    $nam--;
}
if ($thang > 12) {
    $thang = 1;
    $nam++;
}
$sql = "SELECT cl.*, cc.so_gio_lam FROM cham_cong cc JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE dkc.nguoi_dung_id = ? AND MONTH(cl.ngay) = ? AND YEAR(cl.ngay) = ? ORDER BY cl.ngay DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user['id'], $thang, $nam]);
$history = $stmt->fetchAll();
$tong_tien = 0;
$tong_gio = 0;
$so_ca = count($history);
foreach ($history as $row) {
    $tong_gio += $row['so_gio_lam'];
    $tong_tien += ($row['so_gio_lam'] * $row['luong_gio'] * $row['he_so_luong']);
}
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <h2>Thu nhập & Lương</h2>
                <div class="flex items-center gap-10"
                    style="background: white; padding: 5px; border-radius: 8px; border: 1px solid var(--border);">
                    <a href="?m=<?php echo $thang - 1; ?>&y=<?php echo $nam; ?>" class="btn btn-sm hover-bg"><i
                            class="fa-solid fa-chevron-left"></i></a>
                    <span style="font-weight: bold; min-width: 100px; text-align: center;">Tháng
                        <?php echo $thang . '/' . $nam; ?></span>
                    <a href="?m=<?php echo $thang + 1; ?>&y=<?php echo $nam; ?>" class="btn btn-sm hover-bg"><i
                            class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <!-- STATS CARDS: STACK MOBILE -->
            <div class="flex gap-20 mb-20 flex-col-mobile">
                <div class="card w-full"
                    style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white; border: none;">
                    <div class="flex justify-between items-center">
                        <div>
                            <div style="font-size: 13px; opacity: 0.8;">Tổng thực nhận</div>
                            <div style="font-size: 28px; font-weight: bold; margin-top: 5px;">
                                <?php echo formatMoney($tong_tien); ?></div>
                        </div>
                        <i class="fa-solid fa-wallet" style="font-size: 36px; opacity: 0.5;"></i>
                    </div>
                </div>
                <div class="card w-full">
                    <div class="flex justify-between items-center">
                        <div>
                            <div style="font-size: 13px; color: var(--text-muted);">Tổng giờ công</div>
                            <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?php echo $tong_gio; ?>h
                            </div>
                        </div>
                        <i class="fa-solid fa-clock" style="font-size: 36px; color: #f59e0b; opacity: 0.2;"></i>
                    </div>
                </div>
                <div class="card w-full">
                    <div class="flex justify-between items-center">
                        <div>
                            <div style="font-size: 13px; color: var(--text-muted);">Số ca hoàn thành</div>
                            <div style="font-size: 24px; font-weight: bold; margin-top: 5px;"><?php echo $so_ca; ?>
                            </div>
                        </div>
                        <i class="fa-solid fa-calendar-check"
                            style="font-size: 36px; color: #10b981; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết chấm công tháng <?php echo $thang; ?></h3>
                </div>
                <table style="width: 100%;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 13px;">
                            <th style="padding: 12px 10px;">Ngày / Ca làm</th>
                            <th>Hệ số</th>
                            <th>Giờ công</th>
                            <th>Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 30px; color: var(--text-muted);">Chưa có
                                    dữ liệu.</td>
                            </tr><?php else: ?>
                            <?php foreach ($history as $row):
                                $thanh_tien = $row['so_gio_lam'] * $row['luong_gio'] * $row['he_so_luong']; ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 12px 10px;">
                                        <div style="font-weight: bold;"><?php echo date('d/m/Y', strtotime($row['ngay'])); ?>
                                        </div>
                                        <div style="font-size: 13px; color: var(--text-muted);"><?php echo $row['ten_ca']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $row['he_so_luong'] > 1 ? '<span class="badge cho_duyet">x' . $row['he_so_luong'] . '</span>' : '<span style="color:#9ca3af">x1.0</span>'; ?>
                                    </td>
                                    <td><span
                                            style="font-weight: bold; color: var(--primary); background: #eff6ff; padding: 4px 8px; border-radius: 4px;"><?php echo $row['so_gio_lam']; ?>h</span>
                                    </td>
                                    <td><?php echo formatMoney($row['luong_gio']); ?></td>
                                    <td class="text-right" style="font-weight: bold; color: var(--success); font-size: 15px;">
                                        <?php echo formatMoney($thanh_tien); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>
<?php include 'includes/footer.php'; ?>