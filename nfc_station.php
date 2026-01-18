<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<body
    style="background: #0f172a; display: flex; align-items: center; justify-content: center; height: 100vh; overflow: hidden;">
    <div class="text-center" style="max-width: 600px; width: 100%;">
        <div id="status-icon" style="font-size: 120px; color: #1e293b; margin-bottom: 20px;">
            <i class="fa-solid fa-id-card-clip"></i>
        </div>

        <h1 id="main-msg" style="color: white; font-size: 32px; margin-bottom: 10px;">SẴN SÀNG QUÉT THẺ</h1>
        <p id="sub-msg" style="color: #64748b; font-size: 18px;">Vui lòng đưa thẻ NFC lại gần đầu đọc</p>

        <!-- Ô input ẩn để nhận dữ liệu từ đầu đọc thẻ -->
        <input type="text" id="nfc-input" style="position: absolute; opacity: 0;" autofocus>

        <div id="user-info"
            style="display: none; margin-top: 30px; background: #1e293b; padding: 20px; border-radius: 15px; animation: slideUp 0.3s;">
            <img id="u-avatar" src=""
                style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid var(--primary);">
            <h2 id="u-name" style="color: white; margin-top: 10px;"></h2>
            <p id="u-time" style="color: var(--success); font-weight: bold;"></p>
        </div>
    </div>

    <script>
        const input = document.getElementById('nfc-input');
        const mainMsg = document.getElementById('main-msg');
        const subMsg = document.getElementById('sub-msg');
        const statusIcon = document.getElementById('status-icon');
        const userInfo = document.getElementById('user-info');

        // Luôn giữ ô input được focus
        document.addEventListener('click', () => input.focus());
        setInterval(() => input.focus(), 1000);

        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const uid = this.value.trim();
                if (uid) processNFC(uid);
                this.value = '';
            }
        });

        function processNFC(uid) {
            // Hiệu ứng đang xử lý
            mainMsg.innerText = "ĐANG XỬ LÝ...";
            statusIcon.style.color = "var(--primary)";

            fetch('actions/process_nfc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'nfc_uid=' + encodeURIComponent(uid)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        successUI(data);
                    } else {
                        errorUI(data.message);
                    }
                })
                .catch(() => errorUI("Lỗi kết nối máy chủ"));
        }

        function successUI(data) {
            statusIcon.style.color = "var(--success)";
            statusIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            mainMsg.innerText = data.type === 'checkin' ? "CHECK-IN THÀNH CÔNG" : "CHECK-OUT THÀNH CÔNG";
            subMsg.innerText = data.message;

            document.getElementById('u-name').innerText = data.user_name;
            document.getElementById('u-avatar').src = data.avatar;
            document.getElementById('u-time').innerText = "Lúc: " + data.time;
            userInfo.style.display = 'block';

            resetUI(4000);
        }

        function errorUI(msg) {
            statusIcon.style.color = "var(--danger)";
            statusIcon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
            mainMsg.innerText = "LỖI CHẤM CÔNG";
            subMsg.innerText = msg;
            userInfo.style.display = 'none';

            resetUI(3000);
        }

        function resetUI(delay) {
            setTimeout(() => {
                statusIcon.style.color = "#1e293b";
                statusIcon.innerHTML = '<i class="fa-solid fa-id-card-clip"></i>';
                mainMsg.innerText = "SẴN SÀNG QUÉT THẺ";
                subMsg.innerText = "Vui lòng đưa thẻ NFC lại gần đầu đọc";
                userInfo.style.display = 'none';
            }, delay);
        }
    </script>

    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>