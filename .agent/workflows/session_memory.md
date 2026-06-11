# SESSION MEMORY — HKT Fashion
> Session: 2026-06-11 | Status: Checkout Address Cascade Refactored (Direct Province-to-Ward Selection)

---

## ⚡ Active Task Completed
**Tối ưu hóa bộ chọn địa chỉ Checkout (Tỉnh/Thành -> Xã/Phường)**
- Ẩn trường Quận/Huyện khỏi giao diện Checkout để tối giản quy trình nhập liệu của khách hàng.
- Nâng cấp API `/hkt/v1/wards` hỗ trợ tham số `province_id` để lấy toàn bộ danh sách Xã/Phường của Tỉnh/Thành được chọn trong một lần gọi duy nhất.
- Cấu hình Javascript tự động điền Quận/Huyện ngầm dựa trên metadata của Xã/Phường được chọn, đảm bảo tính hợp lệ dữ liệu của WooCommerce (không làm lỗi phí ship hay lưu đơn hàng).
- Làm sạch nhãn (label) danh sách xã/phường, loại bỏ hậu tố trong ngoặc đơn theo phản hồi người dùng.

---

## 📝 Code Changes (Files modified this session)

| File | Key Change |
|---|---|
| `src/wp-content/themes/flatsome-child/inc/vietnam-divisions.php` | Cập nhật API `/wards` hỗ trợ `province_id`, trả về toàn bộ Xã/Phường kèm tên Quận/Huyện cha tương ứng. |
| `src/wp-content/themes/flatsome-child/inc/checkout-customizer.php` | Thêm class ẩn trường Quận/Huyện; cập nhật kịch bản JS gọi API và tự điền ngầm trường Quận/Huyện khi chọn Xã/Phường. |
| `src/wp-content/themes/flatsome-child/style.css` | Thêm CSS `.hkt-hidden-field { display: none !important; }` để ẩn trường Quận/Huyện khỏi form checkout. |

---

## 🔜 Next Steps (3 immediate technical actions)

### Step 1 — Chạy thử nghiệm đặt hàng thực tế (Staging)
- Thực hiện đặt hàng nháp với các địa chỉ khác nhau (ví dụ: Hà Nội, Bình Dương, TP. HCM) để đảm bảo đơn hàng lưu đúng tên Quận/Huyện và Xã/Phường trong trang quản trị Admin.

### Step 2 — Xác thực tương thích với cổng thanh toán & phí vận chuyển
- Đảm bảo phí vận chuyển vẫn được tính toán bình thường dựa trên dữ liệu tỉnh thành và quận huyện được điền ngầm.

### Step 3 — Kiểm tra hiển thị trên thiết bị di động
- Kiểm tra tính tương thích của Select2 và các trường địa chỉ dọc trên giao diện di động.
