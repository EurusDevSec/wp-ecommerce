---
name: workflow_efficiency
description: Lessons learned on workflow efficiency, preventing repetitive errors, and optimizing token usage for e-commerce tasks.
---

# Cẩm Nang Rút Kinh Nghiệm & Tối Ưu Hóa Quy Trình (Workflow Efficiency & Token Optimization)

Tài liệu này ghi lại các bài học kinh nghiệm và quy tắc làm việc tối ưu nhằm tránh lặp lại lỗi, giảm thời gian xử lý và tiết kiệm token tối đa cho các tác vụ phát triển web / e-commerce.

---

## 1. Xác thực Tài nguyên & Dữ liệu Tĩnh (Asset Validation)
*   **Bài học:** Tránh việc import "mù" (import blind) hình ảnh hoặc asset từ các link ngoài khi chưa kiểm tra kỹ. Việc import nhầm ảnh lỗi/nội y dẫn đến việc phải đổi link, xóa sản phẩm và chạy lại script nhiều lần.
*   **Quy tắc:**
    *   Trước khi chạy lệnh import hoặc chèn link ảnh vào code, phải kiểm tra thủ công hoặc tải thử một vài link đại diện để kiểm tra xem ảnh đó có đúng định dạng, đúng nội dung và phù hợp hay không.
    *   Liệt kê danh sách link ảnh dự kiến cho khách hàng duyệt nhanh trước khi tiến hành import hàng loạt.

## 2. Đồng bộ Cú pháp Dòng lệnh theo Môi trường (Shell Compatibility)
*   **Bài học:** Lỗi cú pháp lệnh khi chạy trên Windows PowerShell (sử dụng các toán tử Linux như `&&` hoặc lồng các dấu nháy đơn/nháy kép trong chuỗi JSON của WP-CLI) làm lệnh thất bại liên tục.
*   **Quy tắc:**
    *   Xác định rõ môi trường đầu cuối đang chạy lệnh (mặc định là Windows PowerShell nếu chạy local, hoặc Linux Bash nếu chạy trong SSH/Docker).
    *   Hạn chế viết các câu lệnh inline quá phức tạp chứa nhiều dấu nháy trên PowerShell. Thay vào đó, hãy viết code PHP thuần trong một file script tạm, rồi chạy bằng `wp eval-file`.

## 3. Quy trình Chẩn đoán Lỗi Hệ thống (Sanity Checklist First)
*   **Bài học:** Khi gặp lỗi chức năng (như trang chi tiết bị Coming Soon, lỗi checkout), việc đi thẳng vào chỉnh sửa code theme/plugin mà chưa kiểm tra cấu hình Admin/Database dẫn đến đi đường vòng và tốn nhiều token vô ích.
*   **Quy tắc:**
    *   **Ưu tiên Kiểm tra Cấu hình trước:** Chạy lệnh `wp option get <option_name>` hoặc `wp option list` để kiểm tra trạng thái cấu hình hệ thống (ví dụ: `woocommerce_coming_soon`, `woocommerce_store_pages_only`, `woocommerce_currency`).
    *   Chỉ can thiệp chỉnh sửa code PHP/CSS của theme/child-theme sau khi đã xác định chắc chắn cấu hình Admin/Database đã đúng mà lỗi vẫn xảy ra.

## 4. Tối ưu hóa Cập nhật Cục bộ (Targeted Updates)
*   **Bài học:** Chạy lại toàn bộ script import sản phẩm lớn chỉ để sửa 1-2 trường dữ liệu hoặc ảnh sản phẩm gây lãng phí tài nguyên và rủi ro lỗi dữ liệu.
*   **Quy tắc:**
    *   Khi cần cập nhật lẻ tẻ, viết câu lệnh WP-CLI hoặc đoạn code PHP cập nhật trực tiếp (`wp post update <id> ...` or `wp eval "wp_update_post(...)"`) thay vì xóa đi và import lại toàn bộ.

## 5. Tối ưu hóa Token qua Phân tích Độc lập trước khi Gọi Tool
*   **Bài học:** Gọi quá nhiều tool nhỏ nhặt (đọc file, grep search liên tục) khi chưa định hình rõ sơ đồ cấu trúc file làm lãng phí token.
*   **Quy tắc:**
    *   Đọc và phân tích file `README.md` hoặc cấu trúc thư mục để hiểu rõ luồng đi trước khi tìm kiếm.
    *   Gộp các chỉnh sửa không liên tiếp trong cùng một file vào một cuộc gọi `multi_replace_file_content` duy nhất thay vì gọi `replace_file_content` nhiều lần liên tục.
