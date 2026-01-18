<?php
// Tự động nhận diện JS theo trang
$currentPage = basename($_SERVER['PHP_SELF']);
$jsFiles = [];

switch ($currentPage) {
    case 'scan_qr.php':
        $jsFiles[] = 'assets/js/html5-qrcode.min.js';
        break;
    case 'admin_ca_lam.php':
    case 'admin_shift_detail.php':
    case 'admin_analysis.php':
    case 'dashboard.php':
    case 'view_qr.php':
        $jsFiles[] = 'assets/js/qrcode.min.js';
        break;
}

// THÊM CHART.JS CHO TRANG PHÂN TÍCH
if ($currentPage == 'admin_analysis.php' || $currentPage == 'dashboard.php') {
    $jsFiles[] = 'assets/js/chart.min.js';
}

if (!empty($jsFiles)) {
    if (DEV_MODE) {
        // Chế độ DEV: Hiện từng file JS gốc
        foreach ($jsFiles as $file) {
            echo '<script src="' . $file . '?v=' . time() . '"></script>';
        }
    } else {
        // Chế độ PROD: Hiện file đã gộp
        if (!isset($assets))
            $assets = new AssetManager();
        echo '<script src="' . $assets->getUrl('js', $jsFiles) . '"></script>';
    }
}
?>

<?php
if (!DEV_MODE && ob_get_level() > 0) {
    ob_end_flush();
}
?>