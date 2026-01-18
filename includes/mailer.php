<?php
// Sử dụng các namespace của PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Load file thủ công (vì không dùng Composer)
// Đảm bảo bạn đã tải PHPMailer về thư mục assets/vendor/PHPMailer
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../assets/vendor/PHPMailer/src/SMTP.php';

function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // 1. Cấu hình Server (Dùng Gmail SMTP làm ví dụ)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';       // SMTP server của Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com'; // <--- THAY EMAIL CỦA BẠN VÀO ĐÂY
        $mail->Password = 'your_app_password';    // <--- THAY MẬT KHẨU ỨNG DỤNG (APP PASSWORD)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // 2. Người gửi và Người nhận
        $mail->setFrom('no-reply@shiftmaster.com', 'ShiftMaster System');
        $mail->addAddress($to); // Email người nhận

        // 3. Nội dung
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body); // Nội dung text thuần nếu client không hỗ trợ HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Ghi log lỗi nếu cần: $mail->ErrorInfo
        return false;
    }
}
?>