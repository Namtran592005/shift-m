<?php
if (!isset($user)) $user = getCurrentUser($pdo);
$page = basename($_SERVER['PHP_SELF']);
?>

<!-- 1. MOBILE HEADER -->
<div class="mobile-header-toggle">
    <div class="mobile-brand">
        <!-- Tự động lấy Logo theo đường dẫn SEO -->
        <img src="assets/img/logo.png" alt="Logo" class="mobile-logo-img" onerror="this.style.display='none'">
        <span>Shift-M</span>
    </div>
    <button class="btn-toggle-menu" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
</div>

<!-- 2. OVERLAY -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- 3. SIDEBAR DESKTOP -->
<aside class="sidebar" id="mainSidebar">
    <div class="mobile-close-btn-container">
        <button onclick="toggleSidebar()" style="background: none; border: none; color: white; font-size: 20px;"><i class="fa-solid fa-xmark"></i></button>
    </div>
    
    <div class="brand">
        <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo-img" onerror="this.style.display='none'">
        <span>Shift-M</span>
    </div>

    <div class="user-panel">
        <img src="<?php echo getAvatar($user['avatar'], $user['vai_tro']); ?>" class="user-avatar">
        <div style="overflow: hidden;">
            <div style="font-weight: 600; color: white; white-space: nowrap;"><?php echo $user['ho_ten']; ?></div>
            
            <!-- HIỂN THỊ MÃ SỐ NHÂN VIÊN -->
            <div style="font-size: 11px; color: #60a5fa; font-family: monospace; font-weight: bold; margin-bottom: 2px;">
                <?php echo !empty($user['ma_nhan_vien']) ? $user['ma_nhan_vien'] : ''; ?>
            </div>

            <div style="font-size: 12px; color: #9ca3af;">
                <?php echo ($user['vai_tro'] == 'admin') ? 'Quản Trị Viên' : 'Nhân Viên'; ?>
            </div>
        </div>
    </div>

    <nav class="nav-links">
        <a href="dashboard.php" class="nav-item <?php echo $page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge-high"></i> <span>Tổng quan</span>
        </a>

        <?php if ($user['vai_tro'] == 'admin'): ?>
            <div class="menu-label">Quản lý</div>
            <a href="admin_calendar.php" class="nav-item <?php echo $page == 'admin_calendar.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-days"></i> <span>Lịch hệ thống</span>
            </a>
            <a href="admin_ca_lam.php" class="nav-item <?php echo $page == 'admin_ca_lam.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-briefcase"></i> <span>Quản lý Ca làm</span>
            </a>
            <a href="nfc_station.php" class="nav-item <?php echo $page == 'nfc_station.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-id-card-clip"></i> <span>Mở quét NFC</span>
            </a> 
            <a href="admin_duyet.php" class="nav-item <?php echo $page == 'admin_duyet.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list-check"></i> <span>Duyệt đăng ký</span>
            </a>
            <a href="admin_employees.php" class="nav-item <?php echo $page == 'admin_employees.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> <span>Nhân sự</span>
            </a>
            <a href="admin_salary_advance.php" class="nav-item <?php echo $page == 'admin_salary_advance.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-hand-holding-dollar"></i> <span>Duyệt ứng lương</span>
            </a>
            <a href="admin_analysis.php" class="nav-item <?php echo $page == 'admin_analysis.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> <span>Phân tích dữ liệu</span>
            </a>
            <a href="admin_settings.php" class="nav-item <?php echo $page == 'admin_settings.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gears"></i> <span>Cài đặt hệ thống</span>
            </a>
            <a href="admin_logs.php" class="nav-item <?php echo $page == 'admin_logs.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> <span>Nhật ký</span>
            </a>

        <?php else: ?>
            <div class="menu-label">Cá nhân</div>
            <a href="scan_qr.php" class="nav-item <?php echo $page == 'scan_qr.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-qrcode"></i> <span>Chấm công QR</span>
            </a>
            <a href="calendar.php" class="nav-item <?php echo $page == 'calendar.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-calendar-check"></i> <span>Lịch đăng ký</span>
            </a>
            <a href="income.php" class="nav-item <?php echo $page == 'income.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-money-bill-wave"></i> <span>Lương & Thu nhập</span>
            </a>
            <a href="salary_advance.php" class="nav-item <?php echo $page == 'salary_advance.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-piggy-bank"></i> <span>Ứng lương</span>
            </a>
            <a href="profile.php" class="nav-item <?php echo $page == 'profile.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-gear"></i> <span>Tài khoản</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="actions/logout.php" style="color: #ef4444; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <i class="fa-solid fa-right-from-bracket"></i> <span>Đăng xuất</span>
        </a>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('mainSidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
</script>

<style>
    .menu-label { margin: 20px 0 10px 15px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: #6b7280; }
    .nav-item i { width: 24px; text-align: center; font-size: 16px; }
    .nav-item span { margin-left: 5px; }
    .mobile-logo-img { background: white; padding: 2px; border-radius: 4px; }
    @media(max-width: 992px){ .mobile-close-btn-container { display: flex !important; justify-content: flex-end; padding: 10px 15px; } }
</style>