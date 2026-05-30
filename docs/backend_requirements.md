# Đặc tả Yêu cầu kỹ thuật Backend (Backend Requirements)
## Dự án: HKT Fashion (Website Thương mại Điện tử Thời trang)

Tài liệu này đặc tả các yêu cầu kỹ thuật phía Backend (PHP, Database, APIs, Logic xử lý) cho dự án **HKT Fashion**. Mục tiêu là thiết lập một hệ quản trị WooCommerce ổn định, bảo mật cao, tối ưu hóa quy trình đặt hàng và tích hợp chặt chẽ các giải pháp thanh toán/vận chuyển tại thị trường Việt Nam.

---

## 1. Môi trường & Nền tảng Hệ thống

*   **Runtime:** PHP 8.1 / 8.2 (để tối ưu hiệu năng xử lý và tương thích tốt nhất với WordPress/WooCommerce hiện tại).
*   **Database:** MySQL 8.0 (Cấu hình tối ưu chỉ mục index cho các bảng dữ liệu lớn).
*   **CMS:** WordPress Core mới nhất + WooCommerce.
*   **Kiến trúc Database lưu trữ Đơn hàng:**
    *   Hiện tại hệ thống đang chạy ở chế độ **Legacy** (lưu đơn hàng trong bảng `wp_posts` với `post_type = 'shop_order'` và metadata trong `wp_postmeta`).
    *   *Định hướng:* Cấu hình hệ thống sẵn sàng hỗ trợ **HPOS (High-Performance Order Storage)** bằng cách tạo sẵn các bảng `wp_wc_orders`, `wp_wc_order_addresses` để tăng tốc độ truy vấn đơn hàng lên gấp 5-10 lần khi quy mô đơn hàng tăng cao.

---

## 2. Quản lý Dữ liệu & Nghiệp vụ Sản phẩm (Product Logic)

*   **Sản phẩm Biến thể (Variable Products):**
    *   Hỗ trợ đầy đủ các thuộc tính (Attributes): `pa_size` (Size) và `pa_color` (Màu sắc).
    *   Mỗi biến thể (Variation) bắt buộc phải quản lý được: SKU riêng biệt, Giá bán lẻ (Regular Price), Giá khuyến mãi (Sale Price) có set lịch chạy, Số lượng tồn kho (Stock quantity) và Hình ảnh đại diện cho biến thể đó.
*   **Đồng bộ tồn kho:**
    *   Khi khách hàng bấm đặt hàng thành công, hệ thống backend phải tự động trừ kho (Hold stock) của biến thể tương ứng ngay lập tức để tránh tình trạng Overselling (bán quá số lượng thực tế).
    *   Tự động hủy đơn hàng và hoàn lại số lượng kho nếu khách hàng chọn thanh toán chuyển khoản nhưng không thực hiện trong vòng 15-30 phút (cấu hình qua WooCommerce Settings -> Products -> Inventory -> Hold Stock).

---

## 3. Tối ưu hóa Thanh toán & APIs khu vực Việt Nam (Checkout & Payment APIs)

Để đảm bảo trang web hoạt động "thực tế và trơn tru" tại Việt Nam, Backend cần triển khai các tính năng sau:

### 3.1. Địa phương hóa Form địa chỉ (Vietnam Address API)
*   **Yêu cầu:** Không để khách hàng tự nhập tay Tỉnh/Thành phố hay Quận/Huyện để tránh sai lệch địa chỉ giao hàng khi tích hợp với các đơn vị vận chuyển (GHTK, GHN, Viettel Post).
*   **Giải pháp Backend:**
    *   Import cơ sở dữ liệu Tỉnh/Thành, Quận/Huyện, Phường/Xã của Việt Nam vào database (hoặc tích hợp API lấy danh sách động từ bên thứ ba).
    *   Viết API/Controller trả về danh sách Quận/Huyện dựa trên Tỉnh/Thành được chọn, và danh sách Phường/Xã dựa trên Quận/Huyện được chọn.
    *   Cấu hình WooCommerce lưu các dữ liệu này vào meta của đơn hàng (`_billing_state`, `_billing_city`, `_billing_address_2`).

### 3.2. Tích hợp Cổng thanh toán quét mã QR (VietQR / Chuyển khoản ngân hàng tự động)
*   **Yêu cầu:** Bên cạnh phương thức COD (Thanh toán khi nhận hàng), cần hỗ trợ khách hàng quét mã QR thanh toán nhanh bằng ứng dụng ngân hàng.
*   **Giải pháp Backend:**
    *   Cấu hình phương thức thanh toán Chuyển khoản ngân hàng (BACS).
    *   Viết hàm hook vào sự kiện tạo đơn hàng thành công để tự động tạo mã QR VietQR (chuẩn Napas 247).
    *   Link API VietQR tự động sinh ảnh QR Code: 
        `https://api.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-compact2.png?amount=<ORDER_TOTAL>&addInfo=HKTFASHION%20<ORDER_ID>&accountName=<ACCOUNT_NAME>`
    *   Hiển thị mã QR này ở trang **Cảm ơn (Thank you / Order Received)** và trong **Email xác nhận đơn hàng** gửi cho khách hàng.

---

## 4. Hệ thống Mail & SMTP (Notifications System)

*   **Cấu hình SMTP:**
    *   Mặc định hàm `wp_mail()` của WordPress rất dễ bị đưa vào hộp thư rác (Spam) hoặc bị chặn bởi các nhà cung cấp hosting.
    *   Bắt buộc cấu hình SMTP thông qua plugin (như WP Mail SMTP) kết nối với các dịch vụ gửi mail uy tín (Gmail API, SendGrid, Brevo, Mailgun).
*   **Các luồng Email tự động:**
    *   **Email Đang xử lý (Processing Order):** Gửi ngay cho khách hàng sau khi đặt hàng thành công (kèm hóa đơn chi tiết và mã VietQR nếu chọn chuyển khoản).
    *   **Email Đơn hàng hoàn tất (Completed Order):** Gửi khi admin đổi trạng thái đơn hàng sang "Đã hoàn thành" (kèm mã tracking vận đơn nếu có).
    *   **Email thông báo Đơn hàng mới (New Order Notification):** Gửi đến email quản trị viên của HKT Fashion.

---

## 5. Tối ưu hóa hiệu năng & Bảo mật Backend

*   **Caching (Bộ nhớ đệm):**
    *   Cấu hình **Redis Object Cache** để giảm số lượng truy vấn trực tiếp vào database MySQL.
    *   Không cache các trang động như: Giỏ hàng (`/cart`), Thanh toán (`/checkout`), Tài khoản (`/my-account`).
*   **Bảo mật hệ thống (Security):**
    *   Sử dụng chứng chỉ SSL (HTTPS) bắt buộc cho toàn trang để mã hóa thông tin khách hàng và thông tin thanh toán.
    *   Thay đổi đường dẫn đăng nhập mặc định (`/wp-admin` và `/wp-login.php`) thành một đường dẫn tùy chỉnh để tránh các cuộc tấn công brute-force.
    *   Giới hạn số lần đăng nhập sai (Limit Login Attempts).
    *   Sử dụng WP-CLI để thực hiện các thao tác quản trị hệ thống nhanh chóng và an toàn qua SSH.
