<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
header('Content-Type: application/json');

$uid = $_POST['nfc_uid'] ?? '';

if (empty($uid)) {
    echo json_encode(['status' => 'error', 'message' => 'Mã thẻ trống']);
    exit;
}

// 1. Tìm nhân viên qua mã thẻ
$stmt = $pdo->prepare("SELECT id, ho_ten, avatar, vai_tro FROM nguoi_dung WHERE nfc_uid = ? AND trang_thai = 1");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Thẻ chưa được đăng ký hoặc bị khóa']);
    exit;
}

$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// 2. Tìm ca làm việc ĐÃ DUYỆT của người này trong hôm nay
// Ưu tiên ca đang diễn ra hoặc ca gần nhất
$sql_ca = "SELECT dkc.id as dk_id, cl.ten_ca, cl.gio_bat_dau 
           FROM dang_ky_ca dkc 
           JOIN ca_lam cl ON dkc.ca_lam_id = cl.id 
           WHERE dkc.nguoi_dung_id = ? 
           AND cl.ngay = ? 
           AND dkc.trang_thai = 'da_duyet' 
           ORDER BY ABS(TIMEDIFF(cl.gio_bat_dau, CURRENT_TIME)) ASC LIMIT 1";
$stmt_ca = $pdo->prepare($sql_ca);
$stmt_ca->execute([$user['id'], $today]);
$ca = $stmt_ca->fetch();

if (!$ca) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có ca làm việc nào được duyệt hôm nay']);
    exit;
}

// 3. Kiểm tra trạng thái chấm công
$stmt_cc = $pdo->prepare("SELECT * FROM cham_cong WHERE dang_ky_ca_id = ? ORDER BY id DESC LIMIT 1");
$stmt_cc->execute([$ca['dk_id']]);
$cham_cong = $stmt_cc->fetch();

$res = [
    'status' => 'success',
    'user_name' => $user['ho_ten'],
    'avatar' => getAvatar($user['avatar'], $user['vai_tro']),
    'time' => date('H:i:s')
];

if (!$cham_cong || ($cham_cong['gio_check_in'] && $cham_cong['gio_check_out'])) {
    // THỰC HIỆN CHECK-IN
    $pdo->prepare("INSERT INTO cham_cong (dang_ky_ca_id, gio_check_in) VALUES (?, ?)")
        ->execute([$ca['dk_id'], $now]);

    $res['type'] = 'checkin';
    $res['message'] = "Chào " . $user['ho_ten'] . "! Chúc bạn làm việc vui vẻ.";
    writeLog($pdo, $user['id'], 'NFC Check-in', "Ca: " . $ca['ten_ca']);
} else {
    // THỰC HIỆN CHECK-OUT
    $gio_vao = strtotime($cham_cong['gio_check_in']);
    $gio_ra = strtotime($now);
    $so_gio = round(($gio_ra - $gio_vao) / 3600, 2);

    $pdo->prepare("UPDATE cham_cong SET gio_check_out = ?, so_gio_lam = ? WHERE id = ?")
        ->execute([$now, $so_gio, $cham_cong['id']]);

    $res['type'] = 'checkout';
    $res['message'] = "Tạm biệt! Bạn đã làm được $so_gio giờ.";
    writeLog($pdo, $user['id'], 'NFC Check-out', "Ca: " . $ca['ten_ca'] . " | Tổng: $so_gio h");
}

echo json_encode($res);