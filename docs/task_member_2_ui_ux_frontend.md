# NHIỆM VỤ: TỐI ƯU HÓA GIAO DIỆN UI/UX & TÍNH NĂNG BỔ TRỢ

Nhiệm vụ của bạn là tinh chỉnh giao diện trang thanh toán (Checkout) và chi tiết sản phẩm (Single Product) bằng CSS và JS/PHP bổ sung để tối ưu hóa trải nghiệm khách hàng, thúc đẩy hành động mua hàng và kiểm thử toàn diện hệ thống.

---

## 📁 Chi tiết các nhiệm vụ cần thực hiện:

### 1. Tối ưu hóa trang Thanh toán (Checkout Page):
- **Vị trí code:** Thực hiện ẩn các trường qua filter WooCommerce trong `inc/checkout-customizer.php` hoặc `functions.php`.
- **Yêu cầu:** 
  - Ẩn các trường không cần thiết đối với thị trường Việt Nam bao gồm: *Mã bưu điện (Billing Postcode)*, *Tên công ty (Billing Company)*, *Địa chỉ dòng 2 (Billing Address 2)*.
  - Sử dụng CSS trong `style.css` để dàn trang Checkout thành **2 cột tối giản trên màn hình máy tính** (Cột trái: Form nhập thông tin giao hàng ngắn gọn; Cột phải: Xem lại giỏ hàng và chọn cổng thanh toán Banking/VietQR). Đảm bảo giao diện responsive co giãn tốt trên điện thoại di động (chuyển thành 1 cột dọc).

### 2. Bổ sung Khối Cam kết (Trust Badges) trên Trang sản phẩm:
- **Vị trí code:** Thêm vào trang chi tiết sản phẩm. Có thể hook vào `woocommerce_single_product_summary` sau nút add to cart, hoặc sửa trong file `inc/product-swatches.php`.
- **Yêu cầu:** Thiết kế một block ngang hiển thị các icon cam kết uy tín để khách hàng yên tâm xuống tiền:
  - *Cam kết chính hãng 100%*
  - *Đổi trả dễ dàng trong vòng 7 ngày*
  - *Giao hàng nhanh toàn quốc (Đồng giá ship 30k)*
  - *Hỗ trợ thanh toán VietQR động cực nhanh*
- **Giao diện:** Bo góc khối này `4px` (`border-radius: 4px`), viền mờ nhạt, icon thiết kế tối giản, tinh tế.

### 3. Thanh Mua Hàng Dính (Sticky Add-to-cart Bar) khi cuộn chuột:
- **Vị trí code:** Thêm script JS và CSS vào theme child.
- **Yêu cầu:** Khi người dùng cuộn trang xuống vượt quá vị trí của nút "Mua ngay/Thêm vào giỏ" chính, một thanh bar nhỏ sẽ trượt xuống và dính ở mép trên (hoặc mép dưới) màn hình.
- **Nội dung trên Sticky Bar:** Ảnh thu nhỏ sản phẩm, Tên sản phẩm, Giá bán hiện tại, Dropdown chọn Size/Màu sắc nhanh, và nút **"MUA NGAY"** màu cam nổi bật.

### 4. Cấu hình Wishlist (Danh sách yêu thích) & Quick View (Xem nhanh):
- Cấu hình kích hoạt nút **Quick View** của Flatsome để khi khách hàng rê chuột vào sản phẩm trên trang danh mục sẽ xuất hiện nút xem nhanh popup.
- Tích hợp thêm icon **Yêu thích (Wishlist)** hình trái tim nhỏ góc ảnh trên thẻ sản phẩm để lưu sản phẩm vào danh sách quan tâm.

### 5. Kiểm thử (QA/Testing) cascade Tỉnh thành:
- Thực hiện kiểm thử toàn bộ hành trình chọn **Tỉnh/Thành -> Quận/Huyện -> Phường/Xã** trên trang thanh toán.
- Đảm bảo khi WooCommerce cập nhật giỏ hàng/phí vận chuyển bằng AJAX (`updated_checkout`), các danh sách dropdown không bị trùng lặp phần tử cũ và thông tin xã/phường được lưu chính xác vào đơn hàng.

---

## 🎨 Quy chuẩn thiết kế chung (Design System Guidelines)
Bạn phải tuân thủ nghiêm ngặt các token thiết kế đã được thống nhất của HKT Fashion:
- **Font chữ:** Tiêu đề dùng `Montserrat` (bold 700 trở lên), nội dung dùng `Inter`.
- **Bo góc (Border radius):** Thống nhất bo góc từ `2px` đến `4px` cho toàn bộ các nút bấm, input form và khung hình ảnh.
- **Màu sắc chủ đạo:**
  - Nền trang màu trắng tinh khôi (`#FFFFFF`).
  - Màu nhấn (Accent color) hover/active dùng màu cam thương hiệu (`#f77426`).
  - Màu chữ dùng màu xám đậm thanh lịch (`#1a1a1a` hoặc `#2b2b2b`).
