# Shift-M | Hệ thống Quản lý Nhân sự & Chấm công

**Shift-M** là giải pháp quản lý lịch làm việc, chấm công và tính lương tự động dành cho các mô hình kinh doanh vừa và nhỏ (F&B, Bán lẻ, Cửa hàng tiện lợi...).

Dự án được xây dựng với tiêu chí: **Nhẹ - Nhanh - Tối ưu hóa cho thiết bị di động (Mobile-First).**

---

## 🚀 Tính năng nổi bật

### 📱 Giao diện & Trải nghiệm (UI/UX)
*   **Responsive 100%:** Tương thích hoàn hảo trên Laptop, Tablet và Mobile.
*   **Lịch thông minh:**
    *   *Desktop:* Hiển thị dạng Lưới (Grid) tổng quan.
    *   *Mobile:* Tự động chuyển sang dạng Danh sách (Agenda), ẩn ngày trống để tối ưu không gian.
*   **Giao diện hiện đại:** Thiết kế Clean UI, sử dụng các thành phần chuẩn UX (Modal, Toast, Switch iOS...).

### 🛠 Dành cho Quản trị viên (Admin)
*   **Quản lý ca làm:** Tạo ca, xóa ca, tạo mã QR Code cho từng ca.
*   **Quản lý nhân sự:** Thêm/Sửa/Xóa nhân viên, cấp quyền, tích hợp mã thẻ NFC.
*   **Duyệt yêu cầu:** Duyệt đăng ký ca làm và yêu cầu ứng lương.
*   **Thống kê:** Biểu đồ năng suất, quỹ lương và nhật ký hoạt động hệ thống.
*   **Cấu hình:** Tùy chỉnh hệ thống (Bật/tắt ứng lương, Khóa avatar...).

### 👤 Dành cho Nhân viên
*   **Đăng ký ca:** Xem lịch trống và đăng ký ca làm việc trực tuyến.
*   **Chấm công 4.0:** Quét mã QR hoặc thẻ từ NFC để Check-in/Check-out.
*   **Theo dõi thu nhập:** Xem bảng công, lương tạm tính và lịch sử ứng lương.
*   **Cá nhân hóa:** Quản lý hồ sơ, đổi mật khẩu, đổi avatar.

---

## 🛠 Công nghệ sử dụng

*   **Ngôn ngữ:** PHP (Native/Thuần) - Hiệu năng cao, dễ triển khai.
*   **Database:** MySQL.
*   **Frontend:** HTML5, CSS3, Vanilla Javascript (Không dùng Framework nặng).
*   **Thư viện hỗ trợ:**
    *   *Chart.js:* Vẽ biểu đồ.
    *   *Html5-QRCode:* Quét mã QR trên trình duyệt.
    *   *FontAwesome:* Icon hệ thống.

---

## ⚙️ Cài đặt & Triển khai

### Yêu cầu hệ thống
*   PHP >= 8.4
*   MySQL/MariaDB
*   Web Server (Apache/Nginx/XAMPP/Laragon)

### Hướng dẫn cài đặt
1.  **Clone source code:**
    ```bash
    git clone https://github.com/namtran592005/shift-m.git
    ```
2.  **Cấu hình Database:**
    *   Tạo database mới tên `quan_ly_ca_lam`.
    *   Import file `database.sql` vào database vừa tạo.
3.  **Kết nối:**
    *   Mở file `includes/config.php`.
    *   Cập nhật thông tin `DB_HOST`, `DB_USER`, `DB_PASS`.
4.  **Chạy dự án:**
    *   Truy cập qua trình duyệt: `http://localhost/shift-m`

---

## 🔐 Tài khoản Demo

| Vai trò | Email | Mật khẩu |
| :--- | :--- | :--- |
| **Quản trị viên** | `admin@test.com` | `123456` |
| **Nhân viên** | `nhanvien@test.com` | `123456` |

---

## 📝 Ghi chú Đồ án

Dự án này được phát triển như một **Đồ án Tốt nghiệp / Đồ án Môn học**.
*   **Thực hiện bởi:** Nam trần
*   **Năm thực hiện:** 2026
*   **Phiên bản:** 1.1.0 (Release)

---

> *Sản phẩm được xây dựng với mục đích học tập và nghiên cứu. Mọi đóng góp và phát triển thêm đều được hoan nghênh.*