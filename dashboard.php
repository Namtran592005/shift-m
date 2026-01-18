<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);

if ($user['vai_tro'] == 'admin') {
    $hom_nay = date('Y-m-d');
    $count_nv = $pdo->query("SELECT count(*) FROM nguoi_dung WHERE vai_tro='nhan_vien'")->fetchColumn();
    $count_ca = $pdo->query("SELECT count(*) FROM ca_lam WHERE ngay = '$hom_nay'")->fetchColumn();
    $count_wait = $pdo->query("SELECT count(*) FROM dang_ky_ca WHERE trang_thai='cho_duyet'")->fetchColumn();
    $sql_in = "SELECT COUNT(DISTINCT dkc.nguoi_dung_id) FROM dang_ky_ca dkc JOIN cham_cong cc ON dkc.id = cc.dang_ky_ca_id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE cl.ngay = '$hom_nay' AND cc.gio_check_in IS NOT NULL";
    $count_checked_in = $pdo->query($sql_in)->fetchColumn();
    $sql_absent = "SELECT COUNT(*) FROM dang_ky_ca dkc JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE cl.ngay = '$hom_nay' AND dkc.trang_thai = 'da_duyet' AND dkc.id NOT IN (SELECT dang_ky_ca_id FROM cham_cong WHERE gio_check_in IS NOT NULL)";
    $count_absent = $pdo->query($sql_absent)->fetchColumn();
}

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="flex justify-between items-center mb-20">
                <!-- <div>
                    <h2 style="font-size: 24px;">Xin chào, <?php echo $user['ho_ten']; ?> 👋</h2>
                    <p style="color: var(--text-muted);">Chúc bạn một ngày làm việc hiệu quả!</p>
                </div> -->
                <div
                    style="font-size: 13px; color: var(--text-muted); background: white; padding: 8px 16px; border-radius: 20px; border: 1px solid var(--border);">
                    <i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?php echo date('d/m/Y'); ?>
                </div>
            </div>

            <?php if ($user['vai_tro'] == 'admin'): ?>
                <!-- THÊM CLASS flex-col-mobile -->
                <div class="flex gap-20 mb-20 flex-col-mobile">
                    <div class="card w-full">
                        <div style="color: var(--text-muted); font-size: 13px;">Nhân viên hệ thống</div>
                        <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin-top: 5px;">
                            <?php echo $count_nv; ?>
                        </div>
                    </div>
                    <div class="card w-full">
                        <div style="color: var(--text-muted); font-size: 13px;">Ca làm hôm nay</div>
                        <div style="font-size: 32px; font-weight: 700; color: var(--text-main); margin-top: 5px;">
                            <?php echo $count_ca; ?>
                        </div>
                    </div>
                    <div class="card w-full">
                        <div style="color: var(--text-muted); font-size: 13px;">Đơn chờ duyệt</div>
                        <div style="font-size: 32px; font-weight: 700; color: var(--warning); margin-top: 5px;">
                            <?php echo $count_wait; ?>
                        </div>
                    </div>
                </div>

                <!-- THÊM CLASS flex-col-mobile -->
                <div class="flex gap-20 mb-20 flex-col-mobile">
                    <div class="card" style="flex: 2;">
                        <h3 class="card-title mb-20">Tình hình nhân sự hôm nay</h3>
                        <div style="max-height: 250px; position: relative;"><canvas id="dashChart"></canvas></div>
                    </div>
                    <div class="card" style="flex: 1;">
                        <h3 class="card-title mb-20">Lối tắt</h3>
                        <a href="admin_duyet.php" class="btn btn-primary w-full mb-20" style="justify-content: center;"><i
                                class="fa-solid fa-check-double"></i> Duyệt đăng ký</a>
                        <a href="admin_ca_lam.php" class="btn btn-outline w-full" style="justify-content: center;"><i
                                class="fa-solid fa-calendar-plus"></i> Thêm ca làm mới</a>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        const ctxDash = document.getElementById('dashChart');
                        if (ctxDash) {
                            new Chart(ctxDash, {
                                type: 'doughnut',
                                data: { labels: ['Đã check-in', 'Vắng mặt', 'Chờ duyệt'], datasets: [{ data: [<?php echo (int) $count_checked_in; ?>, <?php echo (int) $count_absent; ?>, <?php echo (int) $count_wait; ?>], backgroundColor: ['#10b981', '#ef4444', '#f59e0b'], borderWidth: 0 }] },
                                options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                            });
                        }
                    });
                </script>
            <?php else:
                $sql_tien = "SELECT SUM(TIME_TO_SEC(TIMEDIFF(cl.gio_ket_thuc, cl.gio_bat_dau))/3600 * cl.luong_gio * cl.he_so_luong) FROM dang_ky_ca dkc JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE dkc.nguoi_dung_id = ? AND dkc.trang_thai = 'da_duyet' AND MONTH(cl.ngay) = MONTH(CURRENT_DATE())";
                $stmt = $pdo->prepare($sql_tien);
                $stmt->execute([$user['id']]);
                $tien_thang = $stmt->fetchColumn() ?? 0;
                ?>
                <!-- THÊM CLASS flex-col-mobile -->
                <div class="flex gap-20 mb-20 flex-col-mobile">
                    <div class="card w-full"
                        style="background: linear-gradient(135deg, #4f46e5, #4338ca); color: white; border: none;">
                        <div style="opacity: 0.9; font-size: 13px;">Thu nhập ước tính tháng này</div>
                        <div style="font-size: 32px; font-weight: 700; margin-top: 5px;">
                            <?php echo formatMoney($tien_thang); ?>
                        </div>
                    </div>
                    <div class="card w-full">
                        <div style="color: var(--text-muted); font-size: 13px;">Trạng thái tài khoản</div>
                        <div
                            style="font-size: 18px; font-weight: 600; margin-top: 10px; color: var(--success); display: flex; align-items: center; gap: 5px;">
                            <i class="fa-solid fa-circle-check"></i> Đang hoạt động
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ca làm việc sắp tới</h3>
                    </div>
                    <?php
                    $list_ca = $pdo->prepare("SELECT cl.* FROM ca_lam cl JOIN dang_ky_ca dkc ON cl.id = dkc.ca_lam_id WHERE dkc.nguoi_dung_id = ? AND dkc.trang_thai = 'da_duyet' AND cl.ngay >= CURDATE() ORDER BY cl.ngay ASC LIMIT 5");
                    $list_ca->execute([$user['id']]);
                    $ds = $list_ca->fetchAll();
                    ?>
                    <?php if (empty($ds)): ?>
                        <p style="color: var(--text-muted);">Bạn chưa có ca làm sắp tới.</p><?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Ca làm</th>
                                    <th>Thời gian</th>
                                    <th>Lương dự kiến</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ds as $ca): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo $ca['ten_ca']; ?></div>
                                            <div style="font-size: 12px; color: var(--text-muted);">
                                                <?php echo date('d/m/Y', strtotime($ca['ngay'])); ?>
                                            </div>
                                        </td>
                                        <td><?php echo substr($ca['gio_bat_dau'], 0, 5) . ' - ' . substr($ca['gio_ket_thuc'], 0, 5); ?>
                                        </td>
                                        <td style="color: var(--primary); font-weight: 600;">
                                            <?php echo formatMoney($ca['luong_gio'] * $ca['he_so_luong']); ?>/h
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>