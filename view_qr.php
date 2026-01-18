<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);

$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE ma_qr = ?");
$stmt->execute([$code]);
$ca = $stmt->fetch();

if (!$ca)
    die("Mã QR không hợp lệ.");

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$logoUrl = $baseUrl . 'assets/img/logo.png';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR: <?php echo htmlspecialchars($ca['ten_ca']); ?></title>
    <script src="assets/js/qrcode.min.js"></script>
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --primary: #3b82f6;
            --text: #f1f5f9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .qr-card {
            background: var(--card);
            padding: 25px 15px;
            border-radius: 24px;
            width: 100%;
            max-width: 350px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .ca-name {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .ca-time {
            display: inline-block;
            background: rgba(255, 255, 255, 0.05);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        /* KHẮC PHỤC LỖI TRÀN MÀN HÌNH */
        .qr-wrapper {
            position: relative;
            background: white;
            padding: 12px;
            border-radius: 16px;
            margin: 0 auto 20px;
            width: 100%;
            max-width: 260px;
            /* QR sẽ không bao giờ to hơn mức này */
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #qrcode {
            width: 100%;
        }

        #qrcode img,
        #qrcode canvas {
            width: 100% !important;
            /* Ép ảnh QR co giãn theo wrapper */
            height: auto !important;
        }

        .qr-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20%;
            height: 20%;
            background: white;
            padding: 2px;
            border-radius: 5px;
            object-fit: contain;
        }

        .action-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
            max-width: 350px;
            margin-top: 20px;
        }

        .btn {
            background: var(--card);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn:active {
            background: var(--primary);
            transform: scale(0.98);
        }

        .btn-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #64748b;
            font-size: 1.2rem;
        }
    </style>
</head>

<body>

    <div class="qr-card">
        <button class="btn-close" onclick="window.close()"><i class="fa-solid fa-circle-xmark"></i></button>

        <div class="ca-name"><?php echo htmlspecialchars($ca['ten_ca']); ?></div>
        <div class="ca-time"><i class="fa-regular fa-clock"></i> <?php echo substr($ca['gio_bat_dau'], 0, 5); ?> -
            <?php echo substr($ca['gio_ket_thuc'], 0, 5); ?></div>

        <div class="qr-wrapper">
            <div id="qrcode"></div>
            <img src="<?php echo $logoUrl; ?>" class="qr-logo-overlay" id="qrLogo" onerror="this.style.display='none'">
        </div>

        <div style="color: var(--primary); font-weight: bold; font-size: 0.8rem; text-transform: uppercase;">
            <i class="fa-solid fa-qrcode"></i> Quét mã để điểm danh
        </div>
    </div>

    <div class="action-bar">
        <button onclick="window.print()" class="btn"><i class="fa-solid fa-print"></i> In mã</button>
        <button onclick="downloadQRWithLogo()" class="btn"><i class="fa-solid fa-download"></i> Tải ảnh</button>
    </div>

    <script>
        // Tạo QR với kích thước 500 để khi tải về/in ra vẫn sắc nét
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $code; ?>",
            width: 500,
            height: 500,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        function downloadQRWithLogo() {
            const qrImg = document.querySelector("#qrcode img");
            const logoImg = document.getElementById("qrLogo");

            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");
            const size = 600; // Tăng size canvas để ảnh tải về cực nét
            canvas.width = size;
            canvas.height = size;

            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, size, size);

            const img1 = new Image();
            img1.onload = function () {
                ctx.drawImage(img1, 0, 0, size, size);

                if (logoImg.style.display !== 'none') {
                    const img2 = new Image();
                    img2.crossOrigin = "anonymous";
                    img2.onload = function () {
                        const lSize = size * 0.22;
                        const lPos = (size - lSize) / 2;
                        ctx.fillStyle = "#ffffff";
                        ctx.fillRect(lPos - 5, lPos - 5, lSize + 10, lSize + 10);
                        ctx.drawImage(img2, lPos, lPos, lSize, lSize);

                        triggerDownload(canvas);
                    };
                    img2.src = logoImg.src;
                } else {
                    triggerDownload(canvas);
                }
            };
            img1.src = qrImg.src;
        }

        function triggerDownload(canvas) {
            const link = document.createElement("a");
            link.href = canvas.toDataURL("image/png");
            link.download = "QR_Checkin_ShiftM.png";
            link.click();
        }
    </script>
</body>

</html>