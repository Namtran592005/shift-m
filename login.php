<?php
require_once 'includes/config.php';

// --- LOGIC TỰ ĐỘNG ĐĂNG NHẬP (REMEMBER ME) ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['shiftm_token'])) {
    // Giải mã token: ID:HashPass
    $token_parts = explode(':', $_COOKIE['shiftm_token']);
    if (count($token_parts) == 2) {
        $user_id = $token_parts[0];
        $token_hash = $token_parts[1];

        // Kiểm tra trong DB
        $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE id = ? AND trang_thai = 1");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // Nếu khớp mật khẩu (đã hash) -> Tự động login
        if ($user && md5($user['mat_khau']) === $token_hash) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['vai_tro'];
            header("Location: dashboard.php");
            exit();
        }
    }
}

// Nếu đã login rồi thì vào luôn
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Auto SEO
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$logoUrl = $baseUrl . 'assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng nhập | Shift-M</title>
    <link rel="icon" type="image/png" href="<?php echo $logoUrl; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-main: #111827;
            --text-sub: #6b7280;
            --border: #d1d5db;
            --bg: #f9fafb;
            --white: #ffffff;
        }

        body {
            background-color: var(--bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            z-index: 10;
        }

        .login-card {
            background: var(--white);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-title {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            margin-top: 5px;
            font-size: 14px;
            color: var(--text-sub);
        }

        .login-logo-img {
            height: 60px;
            width: auto;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            object-fit: contain;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
            transition: 0.2s;
            color: var(--text-main);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Checkbox Ghi nhớ */
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-me span {
            font-size: 13px;
            color: var(--text-sub);
            user-select: none;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .alert {
            background: #fef2f2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Nút Chấm hỏi (?) */
        .help-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            cursor: pointer;
            transition: 0.3s;
            border: 1px solid var(--border);
            z-index: 100;
        }

        .help-btn:hover {
            transform: scale(1.1);
            background: var(--primary);
            color: white;
        }

        /* Modal Giới thiệu */
        .intro-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 200;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.2s;
        }

        .intro-box {
            background: white;
            width: 90%;
            max-width: 500px;
            border-radius: 16px;
            padding: 30px;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s;
        }

        .intro-header {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .intro-content p {
            font-size: 14px;
            color: var(--text-sub);
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .intro-feature {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .feat-item {
            flex: 1;
            text-align: center;
            background: #f3f4f6;
            padding: 15px;
            border-radius: 12px;
        }

        .feat-icon {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .feat-text {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
        }

        .close-intro {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            color: #9ca3af;
            font-size: 20px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="brand-section">
            <img src="<?php echo $logoUrl; ?>" alt="Logo" class="login-logo-img" onerror="this.style.display='none'">
            <h1 class="brand-title">Shift-M</h1>
            <p class="brand-subtitle">Quản lý nhân sự & Chấm công 4.0</p>
        </div>

        <div class="login-card">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <?php echo ($_GET['error'] == 'Khoa') ? 'Tài khoản đã bị khóa.' : 'Sai thông tin đăng nhập.'; ?>
                </div>
            <?php endif; ?>

            <form action="actions/login_action.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Email hoặc Mã nhân viên</label>
                    <input type="text" name="email" class="form-input" required autofocus
                        placeholder="VD: NV001 hoặc admin@gmail.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="mat_khau" class="form-input" required placeholder="••••••••">
                </div>

                <!-- CHECKBOX GHI NHỚ -->
                <label class="remember-me">
                    <input type="checkbox" name="remember" value="1">
                    <span>Duy trì đăng nhập (30 ngày)</span>
                </label>

                <button type="submit" class="btn-submit">Đăng nhập ngay</button>
            </form>
        </div>

        <div style="margin-top: 24px; text-align: center; font-size: 12px; color: var(--text-sub);">
            © <?php echo date('Y'); ?> Developed by <b>Nam Trần</b>
        </div>
    </div>

    <!-- NÚT HELP -->
    <div class="help-btn" onclick="document.getElementById('introModal').style.display='flex'">
        <i class="fa-solid fa-question"></i>
    </div>

    <!-- MODAL GIỚI THIỆU -->
    <div id="introModal" class="intro-modal" onclick="if(event.target == this) this.style.display='none'">
        <div class="intro-box">
            <i class="fa-solid fa-xmark close-intro"
                onclick="document.getElementById('introModal').style.display='none'"></i>

            <div class="intro-header">
                <i class="fa-solid fa-rocket" style="color: var(--primary);"></i> Về hệ thống Shift-M
            </div>

            <div class="intro-content">
                <p><b>Shift-M</b> là giải pháp quản lý nhân sự toàn diện dành cho các doanh nghiệp vừa và nhỏ (SME),
                    quán Cafe, nhà hàng.</p>
                <p>Hệ thống giúp tối ưu hóa quy trình xếp lịch, chấm công và tính lương tự động, loại bỏ hoàn toàn sổ
                    sách giấy tờ.</p>
            </div>

            <div class="intro-feature">
                <div class="feat-item">
                    <div class="feat-icon"><i class="fa-solid fa-qrcode"></i></div>
                    <div class="feat-text">Chấm công QR</div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="feat-text">Xếp lịch Smart</div>
                </div>
                <div class="feat-item">
                    <div class="feat-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div class="feat-text">Tính lương Auto</div>
                </div>
            </div>

            <div style="margin-top: 20px; font-size: 12px; color: #9ca3af; text-align: center;">
                Phiên bản v1.1.0 (Release)
            </div>
        </div>
    </div>

</body>

</html>