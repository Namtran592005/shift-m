<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        :root {
            --primary: #3b82f6;
            --success: #22c55e;
            --danger: #ef4444;
            --bg: #0f172a;
            --card: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            /* Chiều cao khớp mọi trình duyệt mobile */
            font-family: -apple-system, system-ui, sans-serif;
            overflow: hidden;
            padding: 20px;
        }

        .station-container {
            text-align: center;
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #status-icon {
            font-size: clamp(80px, 25vw, 120px);
            color: #1e293b;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            filter: drop-shadow(0 0 15px rgba(0, 0, 0, 0.3));
        }

        #main-msg {
            color: white;
            font-size: clamp(1.5rem, 6vw, 2rem);
            margin-bottom: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }

        #sub-msg {
            color: #64748b;
            font-size: clamp(0.9rem, 4vw, 1.1rem);
            line-height: 1.5;
        }

        #nfc-input {
            position: absolute;
            opacity: 0;
            top: -100px;
        }

        #user-info {
            display: none;
            margin-top: 30px;
            background: var(--card);
            padding: 25px;
            border-radius: 24px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        #u-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid var(--primary);
            object-fit: cover;
            margin-bottom: 15px;
        }

        #u-name {
            color: white;
            margin-bottom: 5px;
            font-size: 1.5rem;
        }

        #u-time {
            color: var(--success);
            font-weight: bold;
            font-size: 1.1rem;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive cho màn hình ngang nhỏ */
        @media (max-height: 450px) {
            .station-container {
                flex-direction: row;
                gap: 20px;
                max-width: 90%;
            }

            #status-icon {
                font-size: 60px;
                margin-bottom: 0;
            }

            #user-info {
                margin-top: 0;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="station-container">
        <div id="status-icon">
            <i class="fa-solid fa-id-card-clip"></i>
        </div>

        <div class="text-group">
            <h1 id="main-msg">SẴN SÀNG QUÉT THẺ</h1>
            <p id="sub-msg">Vui lòng đưa thẻ NFC lại gần đầu đọc</p>
        </div>

        <input type="text" id="nfc-input" autofocus autocomplete="off">

        <div id="user-info">
            <img id="u-avatar" src="" alt="Avatar">
            <h2 id="u-name"></h2>
            <p id="u-time"></p>
        </div>
    </div>

    <script>
        const input = document.getElementById('nfc-input');
        const mainMsg = document.getElementById('main-msg');
        const subMsg = document.getElementById('sub-msg');
        const statusIcon = document.getElementById('status-icon');
        const userInfo = document.getElementById('user-info');

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
            mainMsg.innerText = "ĐANG XỬ LÝ...";
            statusIcon.style.color = "var(--primary)";

            fetch('actions/process_nfc.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'nfc_uid=' + encodeURIComponent(uid)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') successUI(data);
                    else errorUI(data.message);
                })
                .catch(() => errorUI("Lỗi kết nối máy chủ"));
        }

        function successUI(data) {
            statusIcon.style.color = "var(--success)";
            statusIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            mainMsg.innerText = data.type === 'checkin' ? "CHECK-IN XONG" : "CHECK-OUT XONG";
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
</body>

</html>