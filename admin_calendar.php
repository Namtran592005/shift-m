<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
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

// Lấy dữ liệu ca làm
$stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE MONTH(ngay) = ? AND YEAR(ngay) = ? ORDER BY ngay ASC, gio_bat_dau ASC");
$stmt->execute([$thang, $nam]);
$shifts = [];
foreach ($stmt->fetchAll() as $row) {
    $d = (int) date('j', strtotime($row['ngay']));
    $shifts[$d][] = $row;
}
require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="flex justify-between items-center mb-20 flex-col-mobile">
                <!-- <h2 style="margin: 0;">Lịch hệ thống</h2> -->
                <div class="flex gap-10 bg-white p-1 rounded border justify-between w-full">
                    <a href="?m=<?php echo $thang - 1; ?>&y=<?php echo $nam; ?>" class="btn btn-outline btn-sm"
                        style="border:none;"><i class="fa-solid fa-chevron-left"></i> Tháng trước</a>
                    <span style="font-weight:700; padding: 0 10px;">Tháng <?php echo "$thang/$nam"; ?></span>
                    <a href="?m=<?php echo $thang + 1; ?>&y=<?php echo $nam; ?>" class="btn btn-outline btn-sm"
                        style="border:none;">Tháng sau <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>

            <div class="card p-0-mobile" style="border: none; box-shadow: none; background: transparent;">
                <div class="calendar-grid">
                    <!-- Header Thứ (Chỉ hiện Desktop) -->
                    <?php foreach (['CN', 'Hai', 'Ba', 'Tư', 'Năm', 'Sáu', 'Bảy'] as $d)
                        echo "<div class='cal-head'>$d</div>"; ?>

                    <?php
                    $num_days = cal_days_in_month(CAL_GREGORIAN, $thang, $nam);
                    $start_day = date('w', strtotime("$nam-$thang-01"));

                    // Ô trống đầu tháng (Chỉ hiện Desktop)
                    for ($i = 0; $i < $start_day; $i++)
                        echo "<div class='cal-cell empty-cell'></div>";

                    for ($d = 1; $d <= $num_days; $d++):
                        $is_today = ($d == date('d') && $thang == date('m') && $nam == date('Y'));
                        $has_event = isset($shifts[$d]);

                        // Xác định thứ để hiện trên mobile
                        $timestamp = strtotime("$nam-$thang-$d");
                        $day_name = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][date('w', $timestamp)];

                        // Class để CSS lọc hiển thị
                        $classes = 'cal-cell';
                        if ($is_today)
                            $classes .= ' is-today';
                        if ($has_event)
                            $classes .= ' has-event';
                        ?>
                        <div class="<?php echo $classes; ?>">
                            <!-- MOBILE DATE BADGE -->
                            <div class="mobile-date-badge">
                                <span class="d-name"><?php echo $day_name; ?></span>
                                <span class="d-num"><?php echo $d; ?></span>
                            </div>

                            <!-- DESKTOP DATE NUMBER -->
                            <div class="desktop-date">
                                <span><?php echo $d; ?></span>
                                <?php if ($is_today): ?><span class="today-tag">Hôm nay</span><?php endif; ?>
                            </div>

                            <!-- EVENT LIST -->
                            <div class="event-list">
                                <?php if ($has_event):
                                    foreach ($shifts[$d] as $s): ?>
                                        <a href="admin_shift_detail.php?id=<?php echo $s['id']; ?>" class="event">
                                            <div class="time-badge"><?php echo substr($s['gio_bat_dau'], 0, 5); ?></div>
                                            <div class="event-name">
                                                <?php echo $s['ten_ca']; ?>
                                                <span
                                                    style="opacity: 0.7;">(<?php echo $s['trang_thai'] == 'mo' ? 'Mở' : 'Đóng'; ?>)</span>
                                            </div>
                                        </a>
                                    <?php endforeach; endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </main>
    </div>

    <style>
        /* --- STYLE CHUNG --- */
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
            min-height: 120px;
            padding: 8px;
            display: flex;
            flex-direction: column;
        }

        .cal-cell.is-today {
            background: #eff6ff;
        }

        /* Date Number Styles */
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

        .mobile-date-badge {
            display: none;
        }

        /* Ẩn trên PC */

        /* Event Styles */
        .event {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            text-decoration: none;
            font-size: 12px;
            transition: 0.2s;
            border-left: 3px solid var(--primary);
        }

        .event:hover {
            background: #bae6fd;
            transform: translateX(2px);
        }

        .time-badge {
            font-weight: bold;
            font-family: monospace;
        }

        .event-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- MOBILE TRANSFORMATION (MAGIC HERE) --- */
        @media (max-width: 992px) {
            .p-0-mobile {
                padding: 0 !important;
            }

            /* 1. Biến Grid thành List dọc */
            .calendar-grid {
                display: flex;
                flex-direction: column;
                background: transparent;
                border: none;
                gap: 15px;
            }

            /* 2. Ẩn Header thứ & Ô trống */
            .cal-head,
            .empty-cell {
                display: none;
            }

            /* 3. Style từng ngày thành Card (Thẻ) */
            .cal-cell {
                min-height: auto;
                border-radius: 12px;
                border: 1px solid var(--border);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
                flex-direction: row;
                /* Xếp ngang: Ngày bên trái - Sự kiện bên phải */
                gap: 15px;
                padding: 15px;
            }

            /* 4. Ẩn các ngày KHÔNG CÓ sự kiện (trừ hôm nay) để gọn danh sách */
            .cal-cell:not(.has-event):not(.is-today) {
                display: none;
            }

            /* 5. Style Cục ngày tháng bên trái */
            .desktop-date {
                display: none;
            }

            /* Ẩn số ngày kiểu PC */
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

            /* 6. Danh sách sự kiện bên phải */
            .event-list {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 8px;
                justify-content: center;
            }

            /* Nếu ngày hôm nay không có sự kiện, hiện thông báo */
            .is-today:not(.has-event) .event-list::after {
                content: "Không có ca làm việc";
                font-style: italic;
                color: var(--text-muted);
                font-size: 13px;
            }

            /* Style Event to hơn trên mobile */
            .event {
                padding: 8px 10px;
                font-size: 14px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-left: 4px solid var(--primary);
            }

            .event-name {
                white-space: normal;
            }

            /* Cho phép xuống dòng */
        }
    </style>
</body>
<?php include 'includes/footer.php'; ?>