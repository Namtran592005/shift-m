<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../assets/vendor/tcpdf/tcpdf.php';
requireAdmin($pdo);

$user_id = $_GET['user_id'];
$thang = date('m');

// Lấy dữ liệu lương chi tiết của nhân viên
$stmt = $pdo->prepare("SELECT u.ho_ten, u.email, cl.ten_ca, cl.ngay, cc.so_gio_lam, cl.luong_gio, cl.he_so_luong 
                       FROM nguoi_dung u 
                       JOIN dang_ky_ca dkc ON u.id = dkc.nguoi_dung_id 
                       JOIN ca_lam cl ON dkc.ca_lam_id = cl.id 
                       JOIN cham_cong cc ON dkc.id = cc.dang_ky_ca_id 
                       WHERE u.id = ? AND MONTH(cl.ngay) = ?");
$stmt->execute([$user_id, $thang]);
$data = $stmt->fetchAll();
$u = $data[0];

// Khởi tạo PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator('ShiftMaster');
$pdf->SetTitle('Phiếu Lương Nhân Viên');
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12); // Font hỗ trợ tiếng Việt

// Nội dung HTML
$html = '
<h1 style="text-align:center;">PHIẾU LƯƠNG CHI TIẾT</h1>
<p>Nhân viên: <b>' . $u['ho_ten'] . '</b> (' . $u['email'] . ')</p>
<p>Tháng: ' . $thang . '/' . date('Y') . '</p>
<table border="1" cellpadding="5">
    <tr style="background-color:#f2f2f2;">
        <th>Ngày</th>
        <th>Ca làm</th>
        <th>Giờ</th>
        <th>Hệ số</th>
        <th>Thành tiền</th>
    </tr>';

$total = 0;
foreach ($data as $row) {
    $sub = $row['so_gio_lam'] * $row['luong_gio'] * $row['he_so_luong'];
    $total += $sub;
    $html .= '<tr>
        <td>' . date('d/m', strtotime($row['ngay'])) . '</td>
        <td>' . $row['ten_ca'] . '</td>
        <td>' . $row['so_gio_lam'] . 'h</td>
        <td>x' . $row['he_so_luong'] . '</td>
        <td>' . number_format($sub) . 'đ</td>
    </tr>';
}

$html .= '</table><h3>TỔNG CỘNG: ' . number_format($total) . ' VNĐ</h3>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('PhieuLuong_' . $u['ho_ten'] . '.pdf', 'D');