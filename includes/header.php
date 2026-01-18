<?php
// Cấu hình Session an toàn
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// --- TỰ ĐỘNG NHẬN DIỆN URL (AUTO SEO) ---
// 1. Xác định giao thức (HTTP hoặc HTTPS)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
// 2. Tên miền (VD: localhost hoặc shift-m.com)
$domainName = $_SERVER['HTTP_HOST'];
// 3. Đường dẫn thư mục gốc (VD: /quanly/ hoặc /)
$path = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
// 4. URL Gốc tuyệt đối (VD: https://domain.com/quanly/)
$baseUrl = $protocol . $domainName . $path;
// 5. URL hiện tại
$currentUrl = $protocol . $domainName . $_SERVER['REQUEST_URI'];
// 6. Đường dẫn Logo tuyệt đối (Cho SEO hình ảnh)
$logoUrl = $baseUrl . 'assets/img/logo.png';

// --- SECURITY HEADERS ---
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content Security Policy
$csp = "default-src 'self'; script-src 'self' 'unsafe-inline' data:; style-src 'self' 'unsafe-inline'; font-src 'self' data:; img-src 'self' data:; connect-src 'self'; frame-src 'none'; object-src 'none';";
header("Content-Security-Policy: " . $csp);

require_once 'includes/AssetManager.php';
require_once 'includes/HtmlMinifier.php';

if (!DEV_MODE) {
    ob_start(['HtmlMinifier', 'process']);
}

$assets = new AssetManager();
$cssFiles = ['assets/vendor/fontawesome/css/all.min.css', 'assets/css/style.css'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#2563eb">

    <!-- SEO META TAGS TỰ ĐỘNG -->
    <title>Shift-M | Hệ thống quản lý nhân sự</title>
    <meta name="description" content="Hệ thống chấm công, xếp lịch và quản lý nhân sự thông minh Shift-M.">
    <meta name="author" content="Shift-M Team">
    <link rel="canonical" href="<?php echo $currentUrl; ?>">

    <!-- FAVICON (LOGO TRÊN TAB TRÌNH DUYỆT) -->
    <link rel="icon" type="image/png" href="<?php echo $logoUrl; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logoUrl; ?>">

    <!-- OPEN GRAPH (HIỂN THỊ KHI CHIA SẺ FACEBOOK/ZALO) -->
    <meta property="og:title" content="Shift-M Dashboard">
    <meta property="og:description" content="Quản lý ca làm việc và chấm công hiệu quả.">
    <meta property="og:image" content="<?php echo $logoUrl; ?>">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:type" content="website">

    <!-- CSS -->
    <?php if (DEV_MODE): ?>
        <?php foreach ($cssFiles as $file): ?>
            <link rel="stylesheet" href="<?php echo $file; ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <link rel="stylesheet" href="<?php echo $assets->getUrl('css', $cssFiles); ?>">
    <?php endif; ?>

    <style>
        :root {
            --font-stack: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: var(--font-stack) !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>