<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
requireLogin();
require_once 'includes/header.php';
?>
<script src="assets/js/html5-qrcode.min.js" type="text/javascript"></script>

<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <h2 class="mb-20">Chấm công QR</h2>
            <!-- THÊM CLASS flex-col-mobile -->
            <div class="flex gap-20 flex-col-mobile">
                <div style="flex: 2;">
                    <div class="card p-0" style="overflow: hidden; position: relative;">
                        <div
                            style="padding: 15px; border-bottom: 1px solid var(--border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 600; display: flex; align-items: center; gap: 8px;"><i
                                    class="fa-solid fa-camera"></i></div>
                            <select id="camera-select" class="form-control"
                                style="width: auto; padding: 4px 10px; font-size: 13px; height: 32px;">
                                <option value="" disabled selected>Đang tải camera...</option>
                            </select>
                        </div>
                        <div class="scanner-wrapper">
                            <div id="reader"></div>
                            <div class="scan-overlay">
                                <div class="scan-laser"></div>
                            </div>
                            <div id="camera-placeholder"><i class="fa-solid fa-video-slash"
                                    style="font-size: 48px; color: #cbd5e1;"></i>
                                <p>Vui lòng chọn camera và cấp quyền</p>
                            </div>
                        </div>
                        <div style="padding: 15px; text-align: center; border-top: 1px solid var(--border);">
                            <button id="btn-start" class="btn btn-primary" onclick="startCamera()"><i
                                    class="fa-solid fa-play"></i> Bật Camera</button>
                            <button id="btn-stop" class="btn btn-danger" onclick="stopCamera()"
                                style="display: none;"><i class="fa-solid fa-stop"></i> Tắt</button>
                        </div>
                    </div>
                </div>
                <div style="flex: 1;">
                    <div id="scanResult" class="card" style="display: none; text-align: center;">
                        <div id="resIcon" style="font-size: 40px; margin-bottom: 10px;"></div>
                        <h3 id="resTitle" style="margin-bottom: 5px;"></h3>
                        <p id="resMsg" style="color: var(--text-muted); font-size: 14px;"></p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Hướng dẫn</h3>
                        </div>
                        <ul class="guide-list">
                            <li><span class="step-num">1</span>
                                <div>Chọn <b>Camera sau</b>.</div>
                            </li>
                            <li><span class="step-num">2</span>
                                <div>Di chuyển mã QR vào giữa khung hình.</div>
                            </li>
                            <li><span class="step-num">3</span>
                                <div>Giữ yên thiết bị.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <style>
        .scanner-wrapper {
            position: relative;
            width: 100%;
            min-height: 300px;
            background: #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #reader {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #reader video {
            width: 100% !important;
            height: auto !important;
        }

        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            display: none;
        }

        .scan-laser {
            position: absolute;
            top: 0;
            left: 10%;
            width: 80%;
            height: 2px;
            background: rgba(37, 99, 235, 0.8);
            box-shadow: 0 0 4px rgba(37, 99, 235, 1);
            animation: scanning 2s infinite;
        }

        @keyframes scanning {
            0% {
                top: 10%;
            }

            50% {
                top: 90%;
            }

            100% {
                top: 10%;
            }
        }

        #camera-placeholder {
            position: absolute;
            text-align: center;
            color: #64748b;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .guide-list {
            list-style: none;
            padding: 0;
        }

        .guide-list li {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .step-num {
            background: var(--primary);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
        }
    </style>
    <script>const html5QrCode = new Html5Qrcode("reader"); let currentCameraId = null; Html5Qrcode.getCameras().then(devices => { const select = document.getElementById('camera-select'); select.innerHTML = ""; if (devices && devices.length) { devices.forEach(device => { const option = document.createElement('option'); option.value = device.id; option.text = device.label || `Camera ${select.options.length + 1}`; select.appendChild(option); }); currentCameraId = devices[0].id; if (devices.length > 1) { currentCameraId = devices[devices.length - 1].id; select.value = currentCameraId; } } else { select.innerHTML = "<option>Không tìm thấy camera</option>"; } }).catch(err => { document.getElementById('camera-select').innerHTML = "<option>Lỗi quyền truy cập</option>"; }); function startCamera() { const cameraId = document.getElementById('camera-select').value; if (!cameraId) return alert("Vui lòng chọn camera!"); document.getElementById('camera-placeholder').style.display = 'none'; document.querySelector('.scan-overlay').style.display = 'block'; document.getElementById('btn-start').style.display = 'none'; document.getElementById('btn-stop').style.display = 'inline-flex'; document.getElementById('scanResult').style.display = 'none'; html5QrCode.start(cameraId, { fps: 10, qrbox: 250 }, (decodedText, decodedResult) => { handleScan(decodedText); }, (errorMessage) => { }).catch(err => { alert("Không thể bật camera."); }); } function stopCamera() { html5QrCode.stop().then(() => { document.getElementById('camera-placeholder').style.display = 'flex'; document.querySelector('.scan-overlay').style.display = 'none'; document.getElementById('btn-start').style.display = 'inline-flex'; document.getElementById('btn-stop').style.display = 'none'; }); } let isProcessing = false; function handleScan(qrCode) { if (isProcessing) return; isProcessing = true; stopCamera(); const resBox = document.getElementById('scanResult'); const resTitle = document.getElementById('resTitle'); const resMsg = document.getElementById('resMsg'); const resIcon = document.getElementById('resIcon'); resBox.style.display = 'block'; resBox.style.background = '#f1f5f9'; resIcon.innerHTML = '⏳'; resTitle.innerText = 'Đang xử lý...'; fetch('actions/process_qr.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'qr_code=' + encodeURIComponent(qrCode) }).then(response => response.json()).then(data => { if (data.status === 'success') { resBox.style.background = '#d1fae5'; resIcon.innerHTML = '✅'; resTitle.innerText = 'Thành công!'; resTitle.style.color = '#065f46'; } else { resBox.style.background = '#fee2e2'; resIcon.innerHTML = '❌'; resTitle.innerText = 'Lỗi'; resTitle.style.color = '#b91c1c'; } resMsg.innerText = data.message; }).finally(() => { isProcessing = false; }); }</script>
</body>

</html>
<?php include 'includes/footer.php'; ?>