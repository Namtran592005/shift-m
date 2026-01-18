<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);

// Lấy 50 log mới nhất
$limit = 50;
$logs = $pdo->query("SELECT l.*, u.ho_ten, u.email FROM nhat_ky_he_thong l LEFT JOIN nguoi_dung u ON l.nguoi_dung_id = u.id ORDER BY l.id DESC LIMIT $limit")->fetchAll();

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Nhật ký hoạt động</h2>

            <div class="card p-0" style="padding: 0; overflow: hidden;">
                <!-- Gợi ý cho mobile -->
                <div style="padding: 10px 15px; background: #fff7ed; color: #c2410c; font-size: 12px; border-bottom: 1px solid #fed7aa; display: none;"
                    class="mobile-hint">
                    <i class="fa-solid fa-arrows-left-right"></i> Vuốt bảng sang trái để xem chi tiết
                </div>
                <style>
                    @media(max-width: 992px) {
                        .mobile-hint {
                            display: block !important;
                        }
                    }
                </style>

                <!-- Wrapper để scroll bảng -->
                <div style="overflow-x: auto; width: 100%;">
                    <table style="width: 100%; white-space: nowrap;">
                        <thead>
                            <tr>
                                <th style="padding: 15px;">Thời gian</th>
                                <th style="padding: 15px;">Người thực hiện</th>
                                <th style="padding: 15px;">Hành động</th>
                                <th style="padding: 15px;">Chi tiết</th>
                                <th style="padding: 15px;">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td style="padding: 15px; color: var(--text-muted);">
                                        <?php echo date('d/m/Y H:i:s', strtotime($l['thoi_gian'])); ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?php if ($l['ho_ten']): ?>
                                            <div style="font-weight: 600;"><?php echo $l['ho_ten']; ?></div>
                                            <div style="font-size: 12px; color: var(--text-muted);"><?php echo $l['email']; ?>
                                            </div>
                                        <?php else:
                                            echo "Hệ thống/Khách";
                                        endif; ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge mo" style="color: var(--primary); background: #eff6ff;">
                                            <?php echo $l['hanh_dong']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px; max-width: 300px; white-space: normal; min-width: 200px;">
                                        <?php echo $l['chi_tiet']; ?>
                                    </td>
                                    <td style="padding: 15px; font-family: monospace; color: var(--text-muted);">
                                        <?php echo $l['ip_address']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
<?php include 'includes/footer.php'; ?>