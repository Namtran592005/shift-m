<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);

// 1. Xử lý thời gian
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

// 2. Lấy các ca ĐÃ ĐĂNG KÝ của nhân viên trong tháng này
$stmt = $pdo->prepare("SELECT cl.*, dkc.trang_thai as trang_thai_dk 
                       FROM ca_lam cl 
                       JOIN dang_ky_ca dkc ON cl.id = dkc.ca_lam_id 
                       WHERE dkc.nguoi_dung_id = ? AND MONTH(cl.ngay) = ? AND YEAR(cl.ngay) = ? 
                       ORDER BY cl.gio_bat_dau ASC");
$stmt->execute([$user['id'], $thang, $nam]);
$events = [];
foreach ($stmt->fetchAll() as $row) {
    $d = (int) date('j', strtotime($row['ngay']));
    $events[$d][] = $row;
}

// 3. Lấy các ca CÒN TRỐNG (để đăng ký thêm) - Chỉ lấy ca tương lai
$stmt_trong = $pdo->prepare("SELECT * FROM ca_lam 
                             WHERE trang_thai = 'mo' AND ngay >= CURDATE() 
                             AND id NOT IN (SELECT ca_lam_id FROM dang_ky_ca WHERE nguoi_dung_id = ?) 
                             ORDER BY ngay ASC, gio_bat_dau ASC LIMIT 10");
$stmt_trong->execute([$user['id']]);
$ca_trong = $stmt_trong->fetchAll();

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <!-- HEADER CHỌN THÁNG -->
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <div class="flex gap-10 bg-white p-1 rounded border justify-between w-full">
                    <a href="?m=<?php echo $thang - 1; ?>&y=<?php echo $nam; ?>" class="btn btn-outline btn-sm"
                        style="border:none;"><i class="fa-solid fa-chevron-left"></i> Tháng trước</a>
                    <span style="font-weight:700; padding: 0 10px;">Tháng <?php echo "$thang/$nam"; ?></span>
                    <a href="?m=<?php echo $thang + 1; ?>&y=<?php echo $nam; ?>" class="btn btn-outline btn-sm"
                        style="border:none;">Tháng sau <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <!-- PHẦN 1: LỊCH ĐÃ ĐĂNG KÝ (Logic Desktop Grid / Mobile Agenda) -->
            <div class="card p-0-mobile"
                style="border: none; box-shadow: none; background: transparent; margin-bottom: 30px;">
                <div class="calendar-grid">
                    <!-- Header Thứ (Ẩn trên Mobile) -->
                    <?php foreach (['CN', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'] as $d)
                        echo "<div class='cal-head'>$d</div>"; ?>

                    <?php
                    $num_days = cal_days_in_month(CAL_GREGORIAN, $thang, $nam);
                    $start_day = date('w', strtotime("$nam-$thang-01"));

                    // Ô trống đầu tháng (Ẩn trên Mobile)
                    for ($i = 0; $i < $start_day; $i++)
                        echo "<div class='cal-cell empty-cell'></div>";

                    // Vòng lặp các ngày trong tháng
                    for ($d = 1; $d <= $num_days; $d++):
                        $is_today = ($d == date('d') && $thang == date('m') && $nam == date('Y'));
                        $has_event = isset($events[$d]); // Kiểm tra xem ngày này có ca làm không
                    
                        // Lấy tên thứ cho Mobile (T2, T3...)
                        $timestamp = strtotime("$nam-$thang-$d");
                        $day_name = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][date('w', $timestamp)];

                        // Class CSS để lọc hiển thị
                        $classes = 'cal-cell';
                        if ($is_today)
                            $classes .= ' is-today';
                        if ($has_event)
                            $classes .= ' has-event';
                        ?>
                        <div class="<?php echo $classes; ?>">
                            <!-- MOBILE: Cục ngày tháng bên trái -->
                            <div class="mobile-date-badge">
                                <span class="d-name"><?php echo $day_name; ?></span>
                                <span class="d-num"><?php echo $d; ?></span>
                            </div>

                            <!-- DESKTOP: Số ngày góc phải -->
                            <div class="desktop-date">
                                <span><?php echo $d; ?></span>
                                <?php if ($is_today): ?><span class="today-tag">Hôm nay</span><?php endif; ?>
                            </div>

                            <!-- LIST SỰ KIỆN -->
                            <div class="event-list">
                                <?php if ($has_event):
                                    foreach ($events[$d] as $ev):
                                        $is_approved = ($ev['trang_thai_dk'] == 'da_duyet');
                                        // Màu sắc khác nhau cho Đã duyệt / Chờ duyệt
                                        $bg = $is_approved ? '#dcfce7' : '#fef3c7';
                                        $color = $is_approved ? '#166534' : '#b45309';
                                        $icon = $is_approved ? 'fa-check' : 'fa-clock';
                                        ?>
                                        <div class="event"
                                            style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; border-left: 3px solid <?php echo $color; ?>;">
                                            <i class="fa-solid <?php echo $icon; ?>" style="font-size: 12px;"></i>
                                            <div>
                                                <span
                                                    style="font-weight: 700; font-family: monospace;"><?php echo substr($ev['gio_bat_dau'], 0, 5); ?></span>
                                                -
                                                <?php echo $ev['ten_ca']; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- PHẦN 2: ĐĂNG KÝ CA MỚI (Luôn hiển thị bên dưới) -->
            <div>
                <h3 class="mb-20" style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Ca trống sắp tới
                </h3>
                <?php if (empty($ca_trong)): ?>
                    <div class="card text-center" style="padding: 30px; color: var(--text-muted);">
                        <i class="fa-regular fa-calendar-xmark" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <p>Hiện không có ca nào trống.</p>
                    </div>
                <?php else: ?>
                    <div class="shift-grid">
                        <?php foreach ($ca_trong as $ca): ?>
                            <div class="card shift-card">
                                <div class="shift-header">
                                    <div class="shift-name"><?php echo $ca['ten_ca']; ?></div>
                                    <span class="badge mo">Mở</span>
                                </div>
                                <div class="shift-body">
                                    <div class="shift-info"><i class="fa-regular fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($ca['ngay'])); ?></div>
                                    <div class="shift-info"><i class="fa-regular fa-clock"></i>
                                        <?php echo substr($ca['gio_bat_dau'], 0, 5) . ' - ' . substr($ca['gio_ket_thuc'], 0, 5); ?>
                                    </div>
                                    <div class="shift-price"><?php echo formatMoney($ca['luong_gio']); ?> <span
                                            style="font-size: 12px; font-weight: normal; color: var(--text-muted);">/h</span>
                                    </div>
                                </div>
                                <a href="actions/dang_ky_ca.php?id=<?php echo $ca['id']; ?>" class="btn btn-primary w-full">Đăng
                                    ký</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        /* --- CSS CHO DESKTOP (Mặc định) --- */
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .cal-head {
            background: #f8fafc;
            padding: 10px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--text-muted);
        }

        .cal-cell {
            background: white;
            min-height: 100px;
            padding: 8px;
            display: flex;
            flex-direction: column;
        }

        .cal-cell.is-today {
            background: #eff6ff;
        }

        .desktop-date {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: 700;
            color: var(--text-muted);
            font-size: 13px;
        }

        .today-tag {
            font-size: 10px;
            background: var(--primary);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Ẩn Badge mobile trên PC */
        .mobile-date-badge {
            display: none;
        }

        .event {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 4px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Shift Cards Grid */
        .shift-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }

        .shift-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid var(--border);
            margin-bottom: 0;
            padding: 15px;
        }

        .shift-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 10px;
        }

        .shift-name {
            font-weight: 700;
            font-size: 16px;
            color: var(--text-main);
        }

        .shift-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .shift-price {
            margin-top: 10px;
            font-size: 18px;
            font-weight: 700;
            color: var(--primary);
        }


        /* --- CSS CHO MOBILE (BIẾN HÌNH) --- */
        @media (max-width: 992px) {
            .p-0-mobile {
                padding: 0 !important;
            }

            /* 1. Grid thành Cột dọc */
            .calendar-grid {
                display: flex;
                flex-direction: column;
                background: transparent;
                border: none;
                gap: 10px;
            }

            /* 2. Ẩn Header thứ & Ô trống */
            .cal-head,
            .empty-cell {
                display: none;
            }

            /* 3. Style thẻ ngày */
            .cal-cell {
                min-height: auto;
                border-radius: 12px;
                border: 1px solid var(--border);
                flex-direction: row;
                /* Ngày trái, Event phải */
                gap: 15px;
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            }

            /* 4. QUAN TRỌNG: Ẩn ngày không có event và không phải hôm nay */
            .cal-cell:not(.has-event):not(.is-today) {
                display: none;
            }

            /* 5. Ẩn số ngày PC, hiện Badge Mobile */
            .desktop-date {
                display: none;
            }

            .mobile-date-badge {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 50px;
                height: 50px;
                background: #f1f5f9;
                border-radius: 10px;
                flex-shrink: 0;
            }

            .is-today .mobile-date-badge {
                background: var(--primary);
                color: white;
            }

            .d-name {
                font-size: 10px;
                text-transform: uppercase;
                font-weight: 600;
            }

            .d-num {
                font-size: 18px;
                font-weight: 800;
                line-height: 1;
            }

            /* 6. List Event */
            .event-list {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 8px;
                justify-content: center;
            }

            /* Thông báo nếu hôm nay không có ca */
            .is-today:not(.has-event) .event-list::after {
                content: "Hôm nay bạn không có ca làm";
                font-style: italic;
                color: var(--text-muted);
                font-size: 13px;
            }

            .event {
                padding: 10px;
                font-size: 14px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                white-space: normal;
                /* Cho phép xuống dòng */
            }

            /* Shift Grid 1 cột */
            .shift-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
<?php include 'includes/footer.php'; ?>