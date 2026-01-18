<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'quan_ly_ca_lam');
define('DB_USER', 'root');
define('DB_PASS', '');

// Tắt/Bật chế độ gỡ lỗi (true: Gỡ lỗi - tải file gốc | false: Production - nén file)
define('DEV_MODE', false);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
if (session_status() === PHP_SESSION_NONE)
    session_start();
?>