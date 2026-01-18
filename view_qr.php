<?php require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);
$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE ma_qr = ?");
$stmt->execute([$code]);
$ca = $stmt->fetch();
if (!$ca)
    die("Mã lỗi."); ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>QR: <?php echo $ca['ten_ca']; ?></title>
    <script src="assets/js/qrcode.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #111827;
            color: white;
            font-family: sans-serif;
        }

        .qr-box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .ca-name {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .ca-time {
            color: #6b7280;
            margin-bottom: 20px;
            font-size: 16px;
        }

        #qrcode img {
            margin: 0 auto;
        }

        .footer {
            margin-top: 30px;
            color: #9ca3af;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="qr-box">
        <div class="ca-name"><?php echo $ca['ten_ca']; ?></div>
        <div class="ca-time"><?php echo date('d/m/Y', strtotime($ca['ngay'])); ?> •
            <?php echo substr($ca['gio_bat_dau'], 0, 5) . ' - ' . substr($ca['gio_ket_thuc'], 0, 5); ?>
        </div>
        <div id="qrcode"></div>
        <div style="margin-top: 20px; font-weight: bold; color: #2563eb;">QUÉT ĐỂ ĐIỂM DANH</div>
    </div>
    <div class="footer">Cửa sổ dành cho quản lý.</div>
    <script>new QRCode(document.getElementById("qrcode"), { text: "<?php echo $code; ?>", width: 300, height: 300 });</script>
</body>

</html>

<?php include 'includes/footer.php'; ?>