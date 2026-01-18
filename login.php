<?php
require_once 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// --- TỰ ĐỘNG NHẬN DIỆN URL (AUTO SEO) ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$path = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$baseUrl = $protocol . $domainName . $path;
$logoUrl = $baseUrl . 'assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Đăng nhập | Shift-M</title>

    <!-- FAVICON & SEO -->
    <link rel="icon" type="image/png" href="<?php echo $logoUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logoUrl; ?>">
    <meta name="description" content="Đăng nhập hệ thống quản lý nhân sự Shift-M">
    <meta property="og:image" content="<?php echo $logoUrl; ?>">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-main: #111827;
            --text-sub: #6b7280;
            --border: #d1d5db;
            --bg: #f9fafb;
            --white: #ffffff;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
        }

        body {
            background-color: var(--bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            margin-top: 8px;
            font-size: 14px;
            color: var(--text-sub);
        }

        .alert {
            background: var(--danger-bg);
            color: var(--danger-text);
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            display: flex;
            align-items: center;
            gap: 8px;
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
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            outline: none;
            transition: 0.2s;
            color: var(--text-main);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
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
    </style>
</head>

<body>
    <div class="login-container">
        <div class="brand-section">
            <!-- LOGO TỰ ĐỘNG TỪ BIẾN PHP -->
            <img src="<?php echo $logoUrl; ?>" alt="Shift-M Logo" class="login-logo-img"
                onerror="this.style.display='none'">
            <h1 class="brand-title">Shift-M</h1>
            <p class="brand-subtitle">Hệ thống quản lý nhân sự</p>
        </div>

        <div class="login-card">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?php echo ($_GET['error'] == 'Khoa') ? 'Tài khoản đã bị khóa.' : 'Sai email hoặc mật khẩu.'; ?>
                </div>
            <?php endif; ?>

            <form action="actions/login_action.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required autofocus
                        placeholder="name@company.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="mat_khau" class="form-input" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn-submit">Đăng nhập</button>
            </form>
        </div>

        <div style="margin-top: 24px; text-align: center; font-size: 12px; color: var(--text-sub);">
            &copy; <?php echo date('Y'); ?> Shift-M System
        </div>
    </div>
</body>

</html>