<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$user = getCurrentUser($pdo);

// Xử lý lưu cấu hình
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['config'] as $key => $val) {
        $pdo->prepare("UPDATE cai_dat SET gia_tri = ? WHERE ten_cau_hinh = ?")->execute([$val, $key]);
    }
    $msg = "Đã lưu cài đặt thành công!";
}

// Lấy cấu hình hiện tại
$settings = $pdo->query("SELECT * FROM cai_dat")->fetchAll(PDO::FETCH_KEY_PAIR);

require_once 'includes/header.php';
?>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Cài đặt hệ thống</h2>

            <?php if (isset($msg)): ?>
                <div class="alert-success mb-20">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- KHUNG CÀI ĐẶT -->
                <div class="card p-0 setting-card">

                    <!-- MỤC 1: KHÓA AVATAR -->
                    <div class="setting-item">
                        <div class="setting-icon bg-blue">
                            <i class="fa-solid fa-user-lock"></i>
                        </div>
                        <div class="setting-content">
                            <div class="setting-label">Khóa đổi ảnh đại diện</div>
                            <div class="setting-desc">Ngăn nhân viên tự thay đổi Avatar.</div>
                        </div>
                        <div class="setting-action">
                            <label class="ios-switch">
                                <input type="hidden" name="config[khoa_doi_avatar]" value="0">
                                <input type="checkbox" name="config[khoa_doi_avatar]" value="1" <?php echo ($settings['khoa_doi_avatar'] == 1) ? 'checked' : ''; ?>>
                                <span class="ios-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- MỤC 2: ỨNG LƯƠNG -->
                    <div class="setting-item">
                        <div class="setting-icon bg-green">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>
                        <div class="setting-content">
                            <div class="setting-label">Cho phép ứng lương</div>
                            <div class="setting-desc">Mở cổng đăng ký ứng lương cho nhân viên.</div>
                        </div>
                        <div class="setting-action">
                            <label class="ios-switch">
                                <input type="hidden" name="config[cho_phep_ung]" value="0">
                                <input type="checkbox" name="config[cho_phep_ung]" value="1" <?php echo ($settings['cho_phep_ung'] == 1) ? 'checked' : ''; ?>>
                                <span class="ios-slider"></span>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="mt-20">
                    <button type="submit" class="btn btn-primary w-full"
                        style="height: 50px; font-size: 16px; border-radius: 12px;">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </main>
    </div>

    <style>
        /* --- RESET CARD --- */
        .setting-card {
            padding: 0 !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* --- ITEM ROW --- */
        .setting-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: white;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        /* --- ICON --- */
        .setting-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .bg-blue {
            background: #007AFF;
        }

        /* iOS Blue */
        .bg-green {
            background: #34C759;
        }

        /* iOS Green */

        /* --- TEXT CONTENT --- */
        .setting-content {
            flex: 1;
            padding-right: 15px;
        }

        .setting-label {
            font-weight: 600;
            font-size: 16px;
            color: #000;
            margin-bottom: 2px;
        }

        .setting-desc {
            font-size: 13px;
            color: #8e8e93;
            /* iOS Gray Text */
            line-height: 1.3;
        }

        /* --- IOS SWITCH (CỐT LÕI) --- */
        .setting-action {
            flex-shrink: 0;
        }

        .ios-switch {
            position: relative;
            display: inline-block;
            width: 51px;
            /* Chuẩn iOS Width */
            height: 31px;
            /* Chuẩn iOS Height */
        }

        .ios-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .ios-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e9e9ea;
            /* Màu xám khi tắt */
            transition: .3s;
            border-radius: 34px;
        }

        .ios-slider:before {
            position: absolute;
            content: "";
            height: 27px;
            /* Nhỏ hơn chiều cao switch một chút */
            width: 27px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .3s cubic-bezier(0.4, 0.0, 0.2, 1);
            /* Hiệu ứng trượt mượt */
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2), 0 0 1px rgba(0, 0, 0, 0.1);
            /* Bóng đổ chuẩn iOS */
        }

        /* Trạng thái BẬT */
        input:checked+.ios-slider {
            background-color: #34C759;
            /* Màu xanh lá iOS */
        }

        input:checked+.ios-slider:before {
            transform: translateX(20px);
        }

        /* Alert Success */
        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        /* Mobile Adjustment */
        @media (max-width: 480px) {
            .setting-item {
                padding: 12px 15px;
            }

            .setting-icon {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .setting-label {
                font-size: 15px;
            }

            .setting-desc {
                font-size: 12px;
            }
        }
    </style>
</body>

</html>
<?php include 'includes/footer.php'; ?>