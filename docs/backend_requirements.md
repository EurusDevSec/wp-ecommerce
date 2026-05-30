# Tài liệu Đặc tả Yêu cầu Hệ thống Backend (Backend Requirements Specification)
## Dự án: HKT Fashion (Thương hiệu Thời trang Tối giản)
**Trạng thái:** Sẵn sàng phát triển | **Nền tảng:** WordPress Core + WooCommerce

Tài liệu này xác định các yêu cầu nghiệp vụ phía máy chủ (PHP), cấu trúc dữ liệu, thiết kế APIs và các tích hợp hệ thống cho đội ngũ Backend. Dev Backend có nhiệm vụ tự triển khai mã nguồn dựa trên các đặc tả logic và tiêu chí nghiệm thu dưới đây.

---

## 📌 BẢN ĐỒ TỆP TIN & TRANG CẦN XỬ LÝ (SCOPE OF WORK)

### Các Trang / Luồng Nghiệp vụ Backend cần xử lý:
1.  **Trang Thanh toán (Checkout Page):** Lọc bỏ các trường nhập không cần thiết, viết logic validate số điện thoại.
2.  **Trang Cảm ơn (Thank You / Order Received Page):** Tích hợp mã VietQR động đối với đơn hàng thanh toán BACS (Chuyển khoản).
3.  **Hệ thống Email (WooCommerce Emails):** Nhúng mã VietQR động vào email gửi khách hàng.
4.  **Hệ thống API nội bộ (REST API):** Cung cấp API trả về dữ liệu Tỉnh/Thành -> Quận/Huyện -> Phường/Xã Việt Nam.
5.  **Tiến trình chạy ngầm (Cron Jobs):** Logic tự động hủy đơn và hoàn kho.

### Các Tệp tin (Files) Backend chịu trách nhiệm:
| Đường dẫn tệp tin | Trạng thái | Nhiệm vụ chính |
| :--- | :--- | :--- |
| `wp-content/themes/flatsome-child/functions.php` | `MODIFY` | Khai báo nạp (`require_once`) các module xử lý nghiệp vụ từ thư mục `inc/`. Không viết logic trực tiếp tại đây. |
| `inc/custom-checkout.php` | `NEW` | Viết logic ẩn các trường checkout thừa, validate định dạng Số điện thoại Việt Nam. |
| `inc/custom-address.php` | `NEW` | Đăng ký các endpoints REST API (`/districts`, `/wards`), xử lý dữ liệu địa chính tĩnh/JSON và lưu thông tin địa chỉ tiếng Việt vào Order Metadata. |
| `inc/custom-vietqr.php` | `NEW` | Hook vào sự kiện checkout/email để tạo link ảnh VietQR động và render hiển thị. |

---

## 1. Kiến trúc Mã nguồn & Quy hoạch Database

### 1.1. Cấu trúc Module hóa Child Theme
*   **Yêu cầu:** Tuyệt đối không viết dồn tất cả mã nguồn tùy biến vào file `wp-content/themes/flatsome-child/functions.php`.
*   **Giải pháp:** Chia nhỏ các khối logic nghiệp vụ ra các file chuyên biệt trong thư mục `inc/` (ví dụ: `custom-checkout.php` cho checkout, `custom-vietqr.php` cho VietQR, `custom-address.php` cho dữ liệu địa chính) và nạp vào file `functions.php` bằng lệnh `require_once`.

### 1.2. Kiến trúc Database Đơn hàng (HPOS)
*   **Mục tiêu:** Tối ưu hóa hiệu năng truy vấn đơn hàng của database.
*   **Yêu cầu:** Hệ thống phải tương thích và chạy ổn định khi kích hoạt tính năng **HPOS (High-Performance Order Storage)** của WooCommerce. Mọi thao tác ghi/đọc dữ liệu đơn hàng (ví dụ: lấy tổng tiền đơn hàng, ghi nhận thông tin địa chỉ khách hàng) phải sử dụng các phương thức chuẩn của WooCommerce CRUD API (ví dụ: `$order->get_meta()`, `$order->update_meta_data()`) thay vì truy vấn trực tiếp bằng SQL thô vào bảng `wp_postmeta`.

---

## 2. Đặc tả Nghiệp vụ Dropdown Địa chỉ Việt Nam (APIs Design)

Để hỗ trợ tính năng chọn địa chỉ 3 cấp (Tỉnh/Thành -> Quận/Huyện -> Phường/Xã) động ở Frontend, Backend cần thiết kế các Endpoint REST API nội bộ như sau:

### 2.1. API Lấy Danh sách Quận/Huyện
*   **Endpoint:** `/wp-json/hkt/v1/districts`
*   **Phương thức:** `GET`
*   **Tham số truyền lên (Query Parameters):**
    *   `province_id` (string, bắt buộc): Mã ID của Tỉnh/Thành phố được chọn.
*   **Định dạng phản hồi mong muốn (JSON Response):**
    ```json
    [
      { "district_id": "294", "district_name": "Quận 1" },
      { "district_id": "295", "district_name": "Quận 3" }
    ]
    ```

### 2.2. API Lấy Danh sách Phường/Xã
*   **Endpoint:** `/wp-json/hkt/v1/wards`
*   **Phương thức:** `GET`
*   **Tham số truyền lên (Query Parameters):**
    *   `district_id` (string, bắt buộc): Mã ID của Quận/Huyện được chọn.
*   **Định dạng phản hồi mong muốn (JSON Response):**
    ```json
    [
      { "ward_id": "1024", "ward_name": "Phường Bến Nghé" },
      { "ward_id": "1025", "ward_name": "Phường Đa Kao" }
    ]
    ```

### 2.3. Quy tắc Lưu trữ Địa chỉ Đơn hàng
*   Khi đơn hàng được tạo thành công, dữ liệu Tên Tỉnh, Tên Huyện, Tên Xã dưới dạng chuỗi văn bản thuần Việt (Ví dụ: "Thành phố Hồ Chí Minh", "Quận 1", "Phường Bến Nghé") phải được lưu vào các trường metadata tương ứng của đơn hàng:
    *   Tỉnh/Thành phố -> Lưu vào meta key: `_billing_state` (và `_shipping_state` nếu có giao hàng khác địa chỉ).
    *   Quận/Huyện -> Lưu vào meta key: `_billing_city` (và `_shipping_city`).
    *   Phường/Xã -> Lưu vào meta key: `_billing_address_2` (và `_shipping_address_2`).

---

## 3. Đặc tả Tối giản hóa Checkout & Xác thực dữ liệu (Validation)

*   **Bộ lọc trường dữ liệu (Checkout Fields Filtering):** Loại bỏ hoàn toàn các trường dữ liệu mặc định của WooCommerce không dùng ở VN (Company, Country, Postcode, Address Line 2 mặc định).
*   **Xác thực Số điện thoại (Phone Validation):**
    *   *Tiêu chí nghiệm thu:* Hệ thống phải kiểm tra số điện thoại nhập vào tại trang thanh toán. Nếu số điện thoại không bắt đầu bằng số 0, không đủ 10 chữ số hoặc chứa ký tự đặc biệt, hệ thống phải chặn tiến trình đặt hàng và hiển thị thông báo lỗi rõ ràng bên ngoài giao diện.

---

## 4. Tự động sinh mã VietQR chuyển khoản (Dynamic VietQR)

Khi khách hàng chọn phương thức thanh toán **Chuyển khoản ngân hàng (BACS)**, hệ thống phải tự động tạo mã QR để khách quét nhanh.

```
[Đơn hàng được tạo thành công] 
       │
       ▼
[Backend đọc ID & Tổng tiền đơn hàng]
       │
       ▼
[Tạo chuỗi Memo: "HKTFASHION <ID>"]
       │
       ▼
[Sinh link QR động qua VietQR API]
       │
       ▼
[Hiển thị QR tại Trang cảm ơn & Email]
```

### 4.1. Cấu trúc thông tin chuyển khoản HKT Fashion
*   **Ngân hàng thụ hưởng:** Vietcombank (VCB)
*   **Số tài khoản:** `0123456789999`
*   **Tên chủ tài khoản:** `CONG TY CO PHAN HKT FASHION`

### 4.2. Logic xử lý mã QR động
*   Nội dung chuyển khoản (Memo) tự động phải theo cấu trúc viết liền không dấu: `HKTFASHION<ID_ĐƠN_HÀNG>` (Ví dụ đơn hàng ID là 60 thì Memo là `HKTFASHION60`).
*   Backend phải tự động tính toán tổng số tiền của đơn hàng bao gồm cả tiền hàng, phí vận chuyển và giảm giá coupon (nếu có).
*   Nhúng link ảnh QR động được sinh từ VietQR API (`https://img.vietqr.io/image/...`) vào hai vị trí:
    1.  **Trang Cảm ơn (Thank You page):** Chỉ hiển thị khi phương thức thanh toán được chọn là Chuyển khoản ngân hàng.
    2.  **Email xác nhận đơn hàng:** Đính kèm trực tiếp vào nội dung email gửi đến khách hàng ngay khi đơn hàng chuyển sang trạng thái "Đang xử lý/Chờ thanh toán".

---

## 5. Tự động hủy đơn hàng quá hạn (Inventory Stock Release)

Để tránh tình trạng khách hàng đặt ảo giữ sản phẩm trong kho làm ảnh hưởng đến việc mua hàng của khách khác:
*   **Logic nghiệp vụ:** Thiết lập một tiến trình nền chạy tự động (Cron Job) định kỳ 15 phút một lần.
*   **Tiêu chí quét:** Lọc ra tất cả đơn hàng có trạng thái `pending` hoặc `on-hold`, phương thức thanh toán là Chuyển khoản ngân hàng (BACS) và thời gian tạo đơn đã vượt quá **30 phút** nhưng chưa hoàn tất thanh toán.
*   **Hành động:** Chuyển trạng thái đơn hàng sang `cancelled` (Đã hủy), đồng thời kích hoạt sự kiện hoàn lại số lượng tồn kho (Restock) cho sản phẩm đó và lưu lại ghi chú đơn hàng (Order Note).

---

## 6. Tiêu chí Nghiệm thu Hệ thống (Acceptance Criteria - AC)

Đội ngũ phát triển Backend và bộ phận kiểm thử (QA/QC) sử dụng danh sách này làm tiêu chí nghiệm thu API và các xử lý logic:

*   **[ ] AC-BE-01: Module hóa Child Theme**
    *   Mã nguồn tùy chỉnh bắt buộc phải được tách riêng thành các file riêng biệt trong thư mục `inc/` và nạp vào `wp-content/themes/flatsome-child/functions.php`.
*   **[ ] AC-BE-02: Đồng bộ cấu trúc dữ liệu HPOS**
    *   Mọi hành vi lưu trữ và đọc dữ liệu đơn hàng phải thông qua WooCommerce CRUD API (không sử dụng trực tiếp hàm SQL insert/select trên bảng postmeta).
*   **[ ] AC-BE-03: Xác thực Số điện thoại tại Checkout**
    *   Nếu số điện thoại khách hàng nhập không bắt đầu bằng số 0, không đủ 10 chữ số hoặc chứa ký tự chữ/ký tự đặc biệt, hệ thống phải trả về lỗi `woocommerce_checkout_process` và ngăn chặn tiến trình đặt hàng.
*   **[ ] AC-BE-04: API Dropdown Địa chỉ động**
    *   API `/wp-json/hkt/v1/districts?province_id=<ID>` trả về đúng danh sách các Quận/Huyện thuộc Tỉnh/Thành đó với định dạng JSON chuẩn.
    *   API `/wp-json/hkt/v1/wards?district_id=<ID>` trả về đúng danh sách các Phường/Xã thuộc Quận/Huyện đó với định dạng JSON chuẩn.
*   **[ ] AC-BE-05: Đồng bộ thông tin địa lý vào Order Metadata**
    *   Khi tạo đơn hàng thành công, thông tin Tỉnh/Thành (chuỗi văn bản tiếng Việt) lưu chính xác vào meta `_billing_state`, Quận/Huyện lưu vào `_billing_city`, Phường/Xã lưu vào `_billing_address_2`.
*   **[ ] AC-BE-06: Sinh VietQR chuyển khoản động**
    *   Tổng số tiền trong mã QR phải trùng khớp 100% với tổng số tiền khách hàng phải thanh toán ở đơn hàng (đã cộng ship, trừ coupon).
    *   Nội dung chuyển khoản (Memo) hiển thị trên QR và trang cảm ơn bắt buộc phải là: `HKTFASHION<ID_ĐƠN_HÀNG>` (Ví dụ: `HKTFASHION60`).
    *   Mã QR phải tự động xuất hiện trên trang Cảm ơn sau khi đặt hàng thành công và hiển thị rõ nét bên trong Email xác nhận gửi cho khách hàng.
*   **[ ] AC-BE-07: Hủy đơn hàng quá hạn & Hoàn kho**
    *   Các đơn hàng chuyển khoản (BACS) trạng thái pending/on-hold quá 30 phút phải tự động chuyển sang trạng thái `cancelled`.
    *   Ngay khi đơn chuyển trạng thái sang `cancelled`, số lượng hàng hóa trong kho của sản phẩm đó phải tự động được cộng trả lại đúng bằng số lượng đã đặt.

