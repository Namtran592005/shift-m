<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$st1 = $pdo->query("SELECT u.ho_ten, IFNULL(SUM(cc.so_gio_lam), 0) as tong_gio FROM nguoi_dung u LEFT JOIN dang_ky_ca dkc ON u.id = dkc.nguoi_dung_id LEFT JOIN cham_cong cc ON dkc.id = cc.dang_ky_ca_id WHERE u.vai_tro = 'nhan_vien' GROUP BY u.id ORDER BY tong_gio DESC");
$data_gio = $st1->fetchAll();
$st2 = $pdo->query("SELECT MONTH(cl.ngay) as thang, SUM(cc.so_gio_lam * cl.luong_gio * cl.he_so_luong) as tong_tien FROM cham_cong cc JOIN dang_ky_ca dkc ON cc.dang_ky_ca_id = dkc.id JOIN ca_lam cl ON dkc.ca_lam_id = cl.id WHERE YEAR(cl.ngay) = YEAR(CURDATE()) GROUP BY MONTH(cl.ngay)");
$data_luong = $st2->fetchAll();
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Phân tích & Thống kê dữ liệu</h2>
            <!-- THÊM CLASS flex-col-mobile -->
            <div class="flex gap-20 flex-col-mobile">
                <div class="card" style="flex: 1;">
                    <div class="card-header">
                        <h3 class="card-title">Năng suất nhân viên (Giờ công)</h3>
                    </div>
                    <div style="position: relative; height:300px;"><canvas id="chartGio"></canvas></div>
                </div>
                <div class="card" style="flex: 1;">
                    <div class="card-header">
                        <h3 class="card-title">Biến động quỹ lương năm <?php echo date('Y'); ?></h3>
                    </div>
                    <div style="position: relative; height:300px;"><canvas id="chartLuong"></canvas></div>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctxGio = document.getElementById('chartGio');
            if (ctxGio) { new Chart(ctxGio, { type: 'bar', data: { labels: <?php echo json_encode(array_column($data_gio, 'ho_ten')); ?>, datasets: [{ label: 'Tổng giờ làm', data: <?php echo json_encode(array_column($data_gio, 'tong_gio')); ?>, backgroundColor: '#2563eb', borderRadius: 4 }] }, options: { maintainAspectRatio: false, responsive: true, scales: { y: { beginAtZero: true } } } }); }
            const ctxLuong = document.getElementById('chartLuong');
            if (ctxLuong) {
                const salaryData = new Array(12).fill(0);
                <?php foreach ($data_luong as $d): ?> salaryData[<?php echo (int) $d['thang'] - 1; ?>] = <?php echo (float) $d['tong_tien']; ?>; <?php endforeach; ?>
                new Chart(ctxLuong, { type: 'line', data: { labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'], datasets: [{ label: 'Tổng tiền lương (VNĐ)', data: salaryData, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', tension: 0.3, fill: true }] }, options: { maintainAspectRatio: false, responsive: true, scales: { y: { beginAtZero: true } } } });
            }
        });
    </script>
    <?php include 'includes/footer.php'; ?>