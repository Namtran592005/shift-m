<?php 
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireAdmin($pdo);

$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM ca_lam WHERE ma_qr = ?");
$stmt->execute([$code]);
$ca = $stmt->fetch();

if (!$ca) die("Mã QR không hợp lệ hoặc ca làm đã bị xóa.");

// Tự động lấy đường dẫn logo chuẩn SEO/Tuyệt đối
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
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
        :root { --bg-dark: #111827; --bg-card: #1f2937; --text-light: #f9fafb; --text-gray: #9ca3af; --primary: #3b82f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-light); height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; overflow: hidden; }

        .qr-container {
            background: var(--bg-card); padding: 30px; border-radius: 24px; text-align: center;
            width: 90%; max-width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
            position: relative; display: flex; flex-direction: column; align-items: center;
        }

        .ca-info { margin-bottom: 25px; }
        .ca-name { font-size: 22px; font-weight: 700; margin-bottom: 8px; color: white; }
        .ca-time { font-size: 15px; color: var(--text-gray); background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 20px; }

        /* --- WRAPPER ĐỂ CHỒNG LOGO --- */
        .qr-wrapper {
            position: relative;
            background: white;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            margin-bottom: 20px;
            display: inline-block;
        }

        #qrcode img {
            width: 240px !important;
            height: 240px !important;
            display: block;
        }

        /* --- LOGO Ở GIỮA --- */
        .qr-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;  /* Kích thước logo */
            height: 50px;
            background: white;
            padding: 4px; /* Viền trắng quanh logo để tách khỏi mã QR */
            border-radius: 8px;
            object-fit: contain;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .guide-text { color: var(--primary); font-weight: 600; font-size: 14px; text-transform: uppercase; animation: pulse 2s infinite; }
        .btn-close { position: absolute; top: 15px; right: 15px; color: var(--text-gray); background: none; border: none; font-size: 24px; cursor: pointer; padding: 5px; }
        .action-bar { margin-top: 30px; display: flex; gap: 20px; }
        .action-btn { background: transparent; color: var(--text-gray); border: 1px solid var(--text-gray); padding: 8px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
        .action-btn:hover { border-color: white; color: white; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        
        @media (max-width: 380px) {
            .qr-container { padding: 20px; width: 95%; }
            #qrcode img { width: 200px !important; height: 200px !important; }
            .qr-logo-overlay { width: 40px; height: 40px; }
        }
    </style>
</head>
<body>

    <div class="qr-container">
        <button class="btn-close" onclick="window.close()"><i class="fa-solid fa-xmark"></i></button>

        <div class="ca-info">
            <div class="ca-name"><?php echo htmlspecialchars($ca['ten_ca']); ?></div>
            <div class="ca-time">
                <i class="fa-regular fa-clock"></i> 
                <?php echo substr($ca['gio_bat_dau'], 0, 5) . ' - ' . substr($ca['gio_ket_thuc'], 0, 5); ?>
            </div>
        </div>

        <div class="qr-wrapper">
            <div id="qrcode"></div>
            <!-- Logo nằm đè lên QR -->
            <img src="<?php echo $logoUrl; ?>" class="qr-logo-overlay" id="qrLogo" onerror="this.style.display='none'">
        </div>

        <div class="guide-text"><i class="fa-solid fa-camera"></i> Quét để điểm danh</div>
    </div>

    <div class="action-bar">
        <button onclick="window.print()" class="action-btn"><i class="fa-solid fa-print"></i> In QR</button>
        <button onclick="downloadQRWithLogo()" class="action-btn"><i class="fa-solid fa-download"></i> Tải ảnh</button>
    </div>

    <script>
        // Tạo QR Code
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $code; ?>",
            width: 500, // Size gốc lớn để nét
            height: 500,
            colorDark : "#000000",
            colorLight : "#ffffff",
            // QUAN TRỌNG: Mức sửa lỗi cao nhất (High) để chèn logo không bị hỏng mã
            correctLevel : QRCode.CorrectLevel.H
        });

        // Hàm xử lý tải ảnh (Gộp QR và Logo thành 1 ảnh duy nhất)
        function downloadQRWithLogo() {
            const qrImg = document.querySelector("#qrcode img");
            const logoImg = document.getElementById("qrLogo");
            
            if (qrImg && qrImg.src) {
                // Tạo Canvas ảo để vẽ
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");
                
                // Set kích thước canvas bằng ảnh QR gốc (500x500)
                const size = 500;
                canvas.width = size;
                canvas.height = size;

                // 1. Vẽ nền trắng
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, size, size);

                // 2. Vẽ mã QR
                const img1 = new Image();
                img1.onload = function() {
                    ctx.drawImage(img1, 0, 0, size, size);

                    // 3. Vẽ Logo ở giữa
                    const img2 = new Image();
                    // Để tránh lỗi CORS khi vẽ ảnh từ URL khác (nếu có)
                    img2.crossOrigin = "anonymous"; 
                    img2.onload = function() {
                        // Tính toán vị trí logo (20% kích thước QR)
                        const logoSize = size * 0.22; 
                        const logoPos = (size - logoSize) / 2;

                        // Vẽ viền trắng cho logo
                        ctx.fillStyle = "#ffffff";
                        // Bo tròn viền (giả lập) bằng cách vẽ hình chữ nhật bo góc hoặc vuông
                        ctx.fillRect(logoPos - 5, logoPos - 5, logoSize + 10, logoSize + 10);

                        ctx.drawImage(img2, logoPos, logoPos, logoSize, logoSize);

                        // 4. Tải về
                        const link = document.createElement("a");
                        link.href = canvas.toDataURL("image/png");
                        link.download = "QR_<?php echo $ca['id']; ?>_ShiftM.png";
                        link.click();
                    };
                    img2.src = logoImg.src;
                };
                img1.src = qrImg.src;
            }
        }
    </script>

</body>
</html>