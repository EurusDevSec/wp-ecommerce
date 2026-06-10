# NHIỆM VỤ: SOẠN THẢO NỘI DUNG & THIẾT KẾ CÁC TRANG TĨNH (STATIC PAGES)

Nhiệm vụ của bạn là tạo mới, thiết kế giao diện và soạn thảo nội dung đầy đủ cho các trang thông tin trên website **HKT Fashion**. Tất cả các trang phải được thiết kế chuyên nghiệp, chuẩn SEO, sử dụng font chữ hệ thống (`Montserrat` cho tiêu đề và `Inter` cho nội dung).

---

## 🛠️ Quy trình thực hiện (Quan trọng):
Để thuận tiện cho việc đồng bộ tự động qua Git lên VPS mà không cần export/import database hay kéo thả lại giao diện, chúng ta **không sử dụng UX Builder** trên Admin của Flatsome. Bạn sẽ viết code cấu trúc HTML/CSS trực tiếp trong file PHP của Child Theme theo cơ chế **WordPress Template Hierarchy**:
1. Vào admin WordPress -> **Trang** -> **Thêm trang mới**. Nhập tiêu đề trang và thiết lập Đường dẫn tĩnh (Slug) tương ứng (ví dụ: `gioi-thieu`). Nội dung trang để trống và bấm **Đăng**.
2. Tạo các file PHP tương ứng trong thư mục Child Theme: `src/wp-content/themes/flatsome-child/`.
3. Viết code HTML/CSS cấu trúc trang trực tiếp vào các file PHP này. Giao diện sẽ tự động hiển thị khi khách hàng truy cập đường dẫn tĩnh.

---

## 📁 Chi tiết các trang cần tạo:

### 1. Trang Giới thiệu (`page-gioi-thieu.php`)
- **Đường dẫn tĩnh (Slug):** `gioi-thieu`
- **Nội dung:** Viết câu chuyện thương hiệu thời trang tối giản HKT Fashion, sứ mệnh chất lượng sản phẩm, tầm nhìn thời trang bền vững và phong cách thiết kế tối giản (minimalism).
- **Yêu cầu giao diện:** Banner lớn phía trên, nội dung phân đoạn rõ ràng bằng thẻ `<h3>`, `<h4>`, căn lề cân đối, khoảng cách thông thoáng.

### 2. Trang Liên hệ (`page-lien-he.php`)
- **Đường dẫn tĩnh (Slug):** `lien-he`
- **Nội dung:** 
  - Địa chỉ showroom, hotline, email liên hệ, thời gian làm việc.
  - Tích hợp một bản đồ Google Maps (nhúng thẻ `<iframe>` chỉ đường của showroom).
  - Tích hợp Form liên hệ bằng cách gọi shortcode của Contact Form 7:
    `<?php echo do_shortcode('[contact-form-7 id="xxxx" title="Form liên hệ"]'); ?>`
- **Yêu cầu giao diện:** Chia làm 2 cột rõ ràng trên PC (Cột 1: Thông tin liên hệ & bản đồ, Cột 2: Form nhập thông tin gửi tin nhắn cho shop).

### 3. Các trang chính sách Footer (Tạo 4 file template):
- **Chính sách đổi trả (`page-chinh-sach-doi-tra.php`):** Quy định đổi trả hàng trong 7 ngày, quy trình đổi size, điều kiện nhãn mác sản phẩm. (Slug: `chinh-sach-doi-tra`).
- **Chính sách bảo mật (`page-chinh-sach-bao-mat.php`):** Quy định bảo mật thông tin tài khoản khách hàng, lịch sử giao dịch. (Slug: `chinh-sach-bao-mat`).
- **Vận chuyển & giao hàng (`page-van-chuyen-va-giao-hang.php`):** Phí ship đồng giá 30k, miễn phí ship đơn hàng trên 500k, thời gian giao hàng dự kiến 2-4 ngày. (Slug: `van-chuyen-va-giao-hang`).
- **Thỏa thuận dịch vụ (`page-thoa-thuan-dich-vu.php`):** Các thỏa thuận chung giữa người mua và HKT Fashion. (Slug: `thoa-thuan-dich-vu`).

### 4. Trang lỗi 404 (`404.php` - Sửa file có sẵn trong theme):
- Sửa trực tiếp tệp `404.php` trong thư mục theme.
- Thiết kế một trang thông báo lỗi 404 thân thiện, có nút bấm **"Quay về trang chủ"** nổi bật và tích hợp thêm shortcode hiển thị danh sách sản phẩm bán chạy để giữ chân khách hàng:
  `<?php echo do_shortcode('[best_selling_products limit="4" columns="4"]'); ?>`

---

## 🎨 Quy chuẩn thiết kế chung (Design System Guidelines)
Bạn phải tuân thủ nghiêm ngặt các token thiết kế đã được thống nhất của HKT Fashion:
- **Font chữ:** Tiêu đề dùng `Montserrat` (bold 700 trở lên), nội dung dùng `Inter`.
- **Bo góc (Border radius):** Thống nhất bo góc từ `2px` đến `4px` cho toàn bộ các nút bấm, input form và khung hình ảnh.
- **Màu sắc chủ đạo:**
  - Nền trang màu trắng tinh khôi (`#FFFFFF`).
  - Màu nhấn (Accent color) hover/active dùng màu cam thương hiệu (`#f77426`).
  - Màu chữ dùng màu xám đậm thanh lịch (`#1a1a1a` hoặc `#2b2b2b`).
