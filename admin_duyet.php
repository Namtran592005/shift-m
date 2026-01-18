<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);

$sql = "SELECT dkc.id as dk_id, nd.ho_ten, nd.email, cl.ten_ca, cl.ngay, cl.gio_bat_dau, cl.gio_ket_thuc 
        FROM dang_ky_ca dkc
        JOIN nguoi_dung nd ON dkc.nguoi_dung_id = nd.id
        JOIN ca_lam cl ON dkc.ca_lam_id = cl.id
        WHERE dkc.trang_thai = 'cho_duyet'
        ORDER BY dkc.thoi_diem_dang_ky ASC";
$cho_duyet = $pdo->query($sql)->fetchAll();

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Yêu cầu đăng ký ca</h3>
                    <span class="badge cho_duyet" style="font-size: 12px;"><?php echo count($cho_duyet); ?> yêu
                        cầu</span>
                </div>
                <?php if (empty($cho_duyet)): ?>
                    <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-clipboard-check" style="font-size: 48px; color: #d1d5db;"></i>
                        <p style="margin-top: 10px;">Tuyệt vời! Không có yêu cầu nào cần xử lý.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Ca đăng ký</th>
                                <th>Khung giờ</th>
                                <th class="text-right">Xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cho_duyet as $req): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;"><?php echo $req['ho_ten']; ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted);"><?php echo $req['email']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--primary);"><?php echo $req['ten_ca']; ?>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-muted);">
                                            <?php echo date('d/m/Y', strtotime($req['ngay'])); ?>
                                        </div>
                                    </td>
                                    <td><?php echo substr($req['gio_bat_dau'], 0, 5) . ' - ' . substr($req['gio_ket_thuc'], 0, 5); ?>
                                    </td>
                                    <td class="text-right">
                                        <a href="actions/admin_xu_ly.php?id=<?php echo $req['dk_id']; ?>&status=huy"
                                            class="btn btn-outline btn-sm" style="color: var(--danger); margin-right: 5px;"><i
                                                class="fa-solid fa-xmark"></i> Từ chối</a>
                                        <a href="actions/admin_xu_ly.php?id=<?php echo $req['dk_id']; ?>&status=da_duyet"
                                            class="btn btn-primary btn-sm"><i class="fa-solid fa-check"></i> Duyệt</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>

</html>

<?php include 'includes/footer.php'; ?>