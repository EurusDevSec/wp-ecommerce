# Đặc tả Yêu cầu kỹ thuật Frontend (Frontend Requirements)
## Dự án: HKT Fashion (Website Thương mại Điện tử Thời trang)

Tài liệu này đặc tả các yêu cầu kỹ thuật và giao diện (UI/UX) phía Frontend cho đội ngũ phát triển giao diện của dự án **HKT Fashion**, sử dụng WordPress làm CMS và **Flatsome (Child theme)** làm Theme chủ đạo. Mục tiêu là xây dựng một trang web thời trang cao cấp, mượt mà trên mọi thiết bị và tối ưu hóa tỷ lệ chuyển đổi (CRO).

---

## 1. Hướng thiết kế & Nhận diện thương hiệu (Brand Identity)

*   **Tên thương hiệu:** HKT Fashion (Thời trang nam/nữ cao cấp, trẻ trung, hiện đại).
*   **Tone màu chủ đạo:**
    *   **Primary (Màu chính):** Trắng tinh khiết (#FFFFFF) - Chiếm 60-70% diện tích bề mặt (background, các khối phân tách, vùng đệm) để tạo cảm giác không gian thoáng đãng, sang trọng và thanh lịch tuyệt đối.
    *   **Secondary (Màu phụ):** Xám nhạt nhẹ (#F8F9FA) hoặc Off-White (#FAF9F6) cho các khung viền mảnh (borders), nền các thẻ sản phẩm (product card background) và chân trang. Chữ viết (Body text) sử dụng màu Xám Slate đậm (#2B2B2B hoặc #333333) để mang lại cảm giác mềm mại, dễ chịu hơn so với màu đen tuyền.
    *   **Accent (Màu điểm nhấn & Nút hành động):** Đen nhám tối giản (#1A1A1A) hoặc Xám Charcoal đậm cho các nút "Mua Ngay" và các nhãn hành động (CTA), tạo sự tương phản cực cao trên nền trắng mà vẫn giữ trọn vẹn tinh thần tối giản (Minimalism).
*   **Typography (Font chữ):**
    *   Font chính: `Montserrat` hoặc `Inter` (Font không chân, hiện đại, dễ đọc trên thiết bị di động).
    *   Cỡ chữ chuẩn: Body text: `14px` - `16px`; Heading (H1-H6): `18px` - `36px` tùy vị trí.
*   **Phong cách thiết kế:** Tối giản (Minimalism), chú trọng không gian âm (White-space / Negative space), đường viền mảnh (hairline borders), bo góc cực nhẹ (2px - 4px) hoặc vuông vức để tạo vẻ thanh lịch, cao cấp. Hình ảnh sản phẩm lớn, sắc nét trên nền trắng là yếu tố chính định hình giao diện.

---

## 2. Đặc tả Chi tiết các Trang chính (Page Spec)

### 2.1. Trang chủ (Homepage)
*Sử dụng Template tùy chỉnh `template-custom-home.php` kết hợp Flatsome UX Builder.*

*   **Header & Navigation:**
    *   Sticky Header (Menu cố định khi cuộn trang) hiển thị Logo HKT Fashion ở trung tâm hoặc góc trái.
    *   Menu Navigation: Trang chủ, Bộ Sưu Tập (New Arrival, BST Hè...), Sản phẩm (Nam, Nữ, Phụ kiện), Tin tức, Liên hệ.
    *   Icon tìm kiếm dạng Ajax (gõ từ khóa hiển thị ngay kết quả sản phẩm kèm ảnh).
    *   Icon Giỏ hàng (Mini-cart) cập nhật số lượng sản phẩm realtime (AJAX cart).
*   **Khối Banner Grid (UX Banner Grid):**
    *   Thiết kế lưới banner bất đối xứng làm nổi bật các chiến dịch lớn (ví dụ: Summer Collection, Clearance Sale).
    *   Nút Kêu gọi Hành động (CTA) trên banner phải có hiệu ứng hover mượt mà (slide-up, change background).
*   **Khối Slider Sản phẩm (Featured Products / New Arrivals):**
    *   Sử dụng slider cảm ứng (touch-drag) mượt mà trên mobile.
    *   Hiển thị thông tin: Ảnh sản phẩm (có hiệu ứng đổi sang ảnh thứ 2 khi di chuột - *second image hover*), tên sản phẩm, giá bán thường và giá sale (nếu có).
    *   Nút "Thêm nhanh" hoặc "Xem nhanh" (Quick View) xuất hiện khi hover vào ảnh sản phẩm.

### 2.2. Trang Cửa hàng (Shop / Catalog Page)
*   **Bố cục (Layout):** Grid 3 hoặc 4 sản phẩm trên Desktop, 2 sản phẩm trên Mobile (tối ưu hóa không gian hiển thị).
*   **Bộ lọc thông minh (Sidebar Filter) - Yêu cầu AJAX:**
    *   Lọc theo danh mục sản phẩm (Áo, Quần, Đầm, Phụ kiện...).
    *   Lọc theo Size (S, M, L, XL, XXL) hiển thị dạng ô vuông nút bấm tiện lợi.
    *   Lọc theo Màu sắc (Trắng, Đen, Đỏ, Be...) hiển thị dạng các vòng tròn màu trực quan (Color Swatches).
    *   Lọc theo Khoảng giá (Price range slider).
*   **Hiển thị thẻ sản phẩm (Product Card):**
    *   Nhãn "SALE" tối giản, nhỏ gọn (dạng chữ nhật màu đen nhám #1A1A1A hoặc xám nhạt với chữ xám đậm) ở góc trên thẻ sản phẩm nếu có giảm giá, thay vì màu đỏ sặc sỡ để duy trì tinh thần Minimalist.
    *   Tính năng Hover Zoom hoặc đổi ảnh mặt sau của sản phẩm.

### 2.3. Trang Chi tiết Sản phẩm (Single Product Page)
*Đây là trang quan trọng nhất quyết định tỷ lệ mua hàng.*

*   **Hình ảnh sản phẩm (Product Gallery):**
    *   Cột bên trái: Gallery ảnh sản phẩm dạng Slider hoặc lưới ảnh lớn có hỗ trợ Zoom khi di chuột và phóng to (Lightbox).
*   **Thông tin sản phẩm (Cột bên phải):**
    *   Tên sản phẩm (H1), giá bán nổi bật (font-size lớn).
    *   **Variation Swatches:** Thay vì sử dụng dropdown mặc định của WooCommerce để chọn Size/Màu sắc, bắt buộc dùng giao diện chọn dạng ô vuông (Swatches) trực quan. Các size hết hàng phải hiển thị mờ đi (disabled) và gạch chéo.
    *   **Nút "Mua Ngay":** Kích thước lớn, sử dụng màu Đen nhám (#1A1A1A) để tạo độ tương phản (contrast) cực mạnh trên nền trắng chủ đạo của trang, text hiển thị rõ và viết hoa: **"MUA NGAY"** (đã cấu hình qua bộ lọc `woocommerce_product_single_add_to_cart_text`).
    *   **Khối Cam kết Tin cậy (Trust Badge):** Hiển thị ngay dưới nút Mua Ngay với thiết kế tối giản, chữ màu xám Slate mảnh, icon tinh tế: `✔ Cam kết hàng chính hãng 100% | ✔ Đổi trả trong 7 ngày nếu lỗi | ✔ Miễn phí vận chuyển từ 500k`.
    *   **Bảng hướng dẫn chọn Size (Size Guide):** Nút mở Pop-up chứa bảng thông số size chi tiết (Chiều cao, Cân nặng, Vòng ngực...).
*   **Tabs Thông tin chi tiết:**
    *   Mô tả sản phẩm (chất liệu, kiểu dáng).
    *   Hướng dẫn giặt ủi và bảo quản.
    *   Đánh giá khách hàng (Review star rating).

### 2.4. Trang Giỏ hàng (Cart) & Thanh toán (Checkout)
*   **Mini-Cart (Giỏ hàng nhanh):**
    *   Khi nhấn "Mua Ngay" hoặc icon giỏ hàng, một Sidebar Mini-Cart trượt ra từ phía bên phải. Khách hàng có thể tăng giảm số lượng hoặc xóa sản phẩm ngay tại đây mà không cần reload trang.
*   **Trang Thanh toán tối giản (Optimized Checkout Page):**
    *   Bố cục 2 cột trên Desktop (Trái: Điền thông tin giao hàng; Phải: Tóm tắt đơn hàng và chọn phương thức thanh toán).
    *   **Tối giản Form điền thông tin (Địa phương hóa cho Việt Nam):**
        *   Loại bỏ hoàn toàn các trường rườm rà: Tên công ty (Company name), Quốc gia (mặc định Việt Nam), Mã bưu điện (Postcode), Địa chỉ dòng 2, Bang/Tỉnh (State - chuyển thành dropdown).
        *   Các trường hiển thị rõ ràng: Họ và tên, Số điện thoại (bắt buộc), Email (tùy chọn nhận hóa đơn), Địa chỉ giao hàng (Tỉnh/Thành phố -> Quận/Huyện -> Phường/Xã dạng dropdown liên kết động).

---

## 3. Trải nghiệm Mobile (Mobile First)

Thời trang là ngành có hơn 80% lưu lượng truy cập đến từ Mobile. Do đó, Frontend cần đảm bảo các yếu tố:
*   Menu Mobile dạng **Off-canvas** (Trượt từ bên trái sang).
*   Thanh điều hướng dưới chân trang (Sticky Mobile Bottom Navigation): Gồm các icon nhanh: *Trang chủ, Danh mục sản phẩm, Tìm kiếm, Giỏ hàng, Tài khoản*.
*   Thao tác vuốt (swipe) mượt mà trên tất cả slider ảnh.
*   Nút "Mua Ngay" trên Mobile có thể cân nhắc cấu hình **Sticky Add to Cart** (luôn ghim ở dưới cùng màn hình khi cuộn qua nút mua chính) để tăng tỷ lệ click.

---

## 4. Yêu cầu Hiệu năng & Tối ưu hóa (Performance)

*   **Tốc độ tải trang:** Đạt tối thiểu 80+ điểm trên Google PageSpeed Insights cho thiết bị di động và 95+ cho máy tính để bàn.
*   **Lazy Load:** Tất cả hình ảnh (đặc biệt là danh sách sản phẩm) phải được cấu hình Lazy Load để tránh chặn hiển thị trang.
*   **Format ảnh:** Ưu tiên sử dụng định dạng ảnh thế hệ mới như `.webp` thay vì `.jpg` hay `.png` để giảm dung lượng tải.
*   **CSS & JS:** Nén (minify) tối đa các file CSS và JS tùy chỉnh. Tận dụng cơ chế gộp file của Flatsome.
