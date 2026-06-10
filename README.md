# wp-ecommerce

![WordPress](https://img.shields.io/badge/WordPress-%23117AC9.svg?style=for-the-badge&logo=WordPress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-%23FF690F.svg?style=for-the-badge&logo=WooCommerce&logoColor=white)
![Flatsome](https://img.shields.io/badge/Flatsome-%232ECC71.svg?style=for-the-badge&logo=Flatsome&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-%232496ED.svg?style=for-the-badge&logo=Docker&logoColor=white)
![Antigravity](https://img.shields.io/badge/Antigravity-%2300D063.svg?style=for-the-badge&logo=Antigravity&logoColor=white)
![Stitch MCP](https://img.shields.io/badge/Stitch%20MCP-%2347A996.svg?style=for-the-badge&logo=Stitch%20MCP&logoColor=white)


## 1. Git clone

```bash
git clone https://github.com/EurusDevSec/wp-ecommerce.git
cd wp-ecommerce
```

## 2. run setup.sh
```bash
./setup.sh
```

## 3. Chạy môi trường Local
* **Trang chủ WordPress:** `http://localhost:8000`
* **Trang quản trị (Admin):** `http://localhost:8000/wp-admin`
* **Công cụ quản lý Database:** `http://localhost:8080` (Kết nối Host: `db`, Database/User/Password: `wordpress`)

---

## 👥 QUY TRÌNH PHỐI HỢP NHÓM & ĐỒNG BỘ MEDIA (TEAM WORKFLOW)

Để tránh xung đột database và tránh việc push hàng Gigabyte ảnh lên Git gây nặng repo, nhóm 3 người thống nhất quy trình làm việc sau:

```mermaid
graph TD
    subgraph LocalDev [MÔI TRƯỜNG PHÁT TRIỂN LOCAL]
        FE[Khoa - Dev FE] -->|Thiết kế UX Builder + CSS| FEDB[(Database Local FE)]
        BE[Dev BE] -->|Lập trình logic PHP trong 'inc/'| BEFiles[Code Logic Local]
    end

    subgraph SyncFE [ĐỒNG BỘ FRONTEND]
        FE -->|1. Xuất DB| FEExport["./db-export.sh"]
        FEExport -->|Tạo ra| FEDBSql["db/init.sql (Chứa giao diện mới)"]
        FEDBSql -->|2. Git Commit & Push| FEBranch[Branch: feature/frontend-xxx]
        FE -->|3. Đẩy ảnh vật lý lên VPS| FESync["./media-vps-sync.sh -> Chọn 1 (Push)"]
    end

    subgraph SyncBE [ĐỒNG BỘ BACKEND]
        BEFiles -->|Git Commit & Push| BEBranch[Branch: feature/backend-xxx]
    end

    subgraph ReviewMerge [PM REVIEW & DUYỆT PR]
        FEBranch -->|Tạo Pull Request| PR[GitHub Pull Request]
        BEBranch -->|Tạo Pull Request| PR
        PR -->|PM Duyệt & Merge| Merge[Nhánh main]
    end

    subgraph DeployStaging [CẬP NHẬT VPS STAGING]
        Merge -->|PM kéo code về VPS| PullVPS["git pull origin main"]
        PullVPS -->|PM import DB trên VPS| VPSImport["./db-import.sh"]
        VPSImport -->|Chọn 'y' và đổi URL thành| LiveDomain["http://167.172.91.249"]
        LiveDomain -->|Kết quả| StagingOK[Staging VPS Live & Hiển thị ảnh 🚀]
    end

    subgraph SyncBack [ĐỒNG BỘ NGƯỢC CHO THÀNH VIÊN]
        Merge -->|Devs kéo code mới| GitPull["git pull origin main"]
        GitPull -->|Chạy ở local| ImportLocal["./db-import.sh"]
        ImportLocal -->|Chọn 'n' (Không đổi URL)| LocalOK[Local Devs hiển thị giao diện mới nhất 💻]
    end
```

### 1. workflow Khoa (Dev FE)
Mỗi khi thiết kế xong layout hoặc thêm sản phẩm kèm ảnh mới ở local:
1. Chạy `./db-export.sh` ở terminal local để xuất DB mới nhất ra file `db/init.sql`.
2. Commit và push file code + `db/init.sql` lên branch cá nhân, tạo Pull Request vào `main`.
3. Chạy `./media-vps-sync.sh` ở terminal local -> chọn **`1` (Push)** để đồng bộ ảnh vật lý mới lên VPS Staging.

### 2. Workflow Tiến (Dev BE)
Khi cần code tiếp dựa trên giao diện mới nhất của Khoa:
1. Chạy `git pull origin main` để lấy code và database mới nhất.
2. Chạy `./db-import.sh` -> chọn **`n`** (Không đổi URL) để nhập giao diện mới vào máy local.
3. **Hình ảnh:** Không cần tải ảnh về máy. Trình duyệt local của BE sẽ tự động tải các ảnh bị thiếu từ VPS Staging nhờ cấu hình chuyển tiếp tự động trong [.htaccess](file:///R:/_Projects/Eurus_Workspace/wp-ecommerce/src/wp-content/uploads/.htaccess).

### 3. workflow Hoàng
Khi duyệt PR và đưa web lên Staging:
1. Merge PR của Khoa/BE trên GitHub vào `main`.
2. SSH vào VPS (`ssh root@167.172.91.249`), di chuyển tới `/var/www/wp-ecommerce/` và chạy:
   ```bash
    git pull origin main
    ./db-import.sh
    ```
    *(Khi import hỏi đổi URL, chọn **`y`**, URL cũ nhấn Enter, URL mới nhập **`http://167.172.91.249`**)*.
3. Nếu trang chủ Staging bị lỗi vỡ ảnh (thiếu ảnh thu nhỏ), chạy lệnh media-regenerate trên VPS:
    ```bash
    docker-compose run --rm --user 33 cli media regenerate --yes --allow-root
    ```

---

## ⚙️ HƯỚNG DẪN GIẢ LẬP THANH TOÁN SEPAY WEBHOOK (DEMO PROJECT)

> [!NOTE]
> **Tại sao phải giả lập (Mocking)?**
> Đối với các dự án môn học hoặc demo nhóm (không có giấy phép kinh doanh, không có tài khoản doanh nghiệp đã ký kết với ngân hàng hoặc cổng thanh toán lớn và không muốn phát sinh chi phí), việc kết nối SePay API/Webhook thực tế là không khả thi. Do đó, hệ thống đã được thiết kế sẵn một cơ chế **Webhook Simulator**.
> Phương pháp này giúp giả lập dữ liệu y hệt như SePay gửi về khi có tiền chuyển khoản thực tế, giúp demo toàn bộ luồng hoạt động tự động 100% (Real-time Confetti & Popup) một cách mượt mà và an toàn.

### 📋 Quy trình 3 bước thực hiện Demo

#### Bước 1: Đặt hàng trên website
1. Truy cập website, chọn sản phẩm và tiến hành Thanh toán.
2. Chọn phương thức thanh toán **Chuyển khoản ngân hàng qua VietQR** và click đặt hàng.
3. Website sẽ chuyển hướng sang trang cảm ơn (Thank You page). Tại đây:
   - Một mã QR Code tự động được sinh ra chứa đúng số tiền và nội dung chuyển khoản (Ví dụ: `HKTFASHION120` với số tiền `685150`).
   - Xuất hiện banner thông báo: `⏳ Đang chờ quét mã thanh toán...`.
   - **Ghi lại: Mã đơn hàng (ví dụ: `120`) và Số tiền đơn hàng (ví dụ: `685150`).**

#### Bước 2: Chạy lệnh giả lập chuyển khoản (Simulator)
Do hệ thống Windows có nhiều loại terminal (Git Bash, Command Prompt, PowerShell) với cách xử lý dấu nháy kép `"` và nháy đơn `'` khác nhau, vui lòng chọn đúng lệnh phù hợp bên dưới để tránh lỗi `Invalid JSON`:

##### A. Dành cho Git Bash (Khuyên dùng)
Nếu bạn dùng Git Bash trên Windows hoặc Terminal trên macOS / Linux:

*   **⚡ Giả lập trên VPS Staging (Domain `167.172.91.249`):**
    ```bash
    curl -X POST http://167.172.91.249/wp-json/sepay/v1/webhook \
      -H "Authorization: Bearer HKTFASHION_SEPAY_KEY_2026" \
      -H "Content-Type: application/json" \
      -d '{"gateway":"Vietcombank","transferType":"in","transferAmount":<SỐ_TIỀN>,"content":"HKTFASHION<MÃ_ĐƠN_HÀNG>","code":"FTVPSNEW123"}'
    ```
    *Ví dụ thực tế cho đơn hàng #120 với số tiền 685.150đ:*
    ```bash
    curl -X POST http://167.172.91.249/wp-json/sepay/v1/webhook \
      -H "Authorization: Bearer HKTFASHION_SEPAY_KEY_2026" \
      -H "Content-Type: application/json" \
      -d '{"gateway":"Vietcombank","transferType":"in","transferAmount":685150,"content":"HKTFASHION120","code":"FTVPSNEW123"}'
    ```

*   **💻 Giả lập dưới Localhost (Port `8000`):**
    ```bash
    curl -X POST http://localhost:8000/wp-json/sepay/v1/webhook \
      -H "Authorization: Bearer HKTFASHION_SEPAY_KEY_2026" \
      -H "Content-Type: application/json" \
      -d '{"gateway":"Vietcombank","transferType":"in","transferAmount":<SỐ_TIỀN>,"content":"HKTFASHION<MÃ_ĐƠN_HÀNG>","code":"FTLOCAL123"}'
    ```

##### B. Dành cho Windows Command Prompt (CMD)
Nếu bạn dùng cmd.exe truyền thống, các tham số JSON dạng nháy kép bắt buộc phải được escape bằng dấu gạch chéo ngược `\"` và bọc toàn bộ body bằng dấu nháy kép `" `:

*   **⚡ Giả lập trên VPS Staging:**
    ```cmd
    curl -X POST http://167.172.91.249/wp-json/sepay/v1/webhook -H "Authorization: Bearer HKTFASHION_SEPAY_KEY_2026" -H "Content-Type: application/json" -d "{\"gateway\":\"Vietcombank\",\"transferType\":\"in\",\"transferAmount\":<SỐ_TIỀN>,\"content\":\"HKTFASHION<MÃ_ĐƠN_HÀNG>\",\"code\":\"FTVPSNEW123\"}"
    ```

##### C. Dành cho Windows PowerShell (Khuyên dùng nếu dùng PowerShell)
Tránh việc escape dấu nháy phức tạp bằng cách chạy lệnh khai báo biến JSON cực kỳ trực quan này:

*   **⚡ Giả lập trên VPS Staging:**
    ```powershell
    $body = @{
        gateway = "Vietcombank"
        transferType = "in"
        transferAmount = <SỐ_TIỀN>
        content = "HKTFASHION<MÃ_ĐƠN_HÀNG>"
        code = "FTVPSNEW123"
    } | ConvertTo-Json -Compress

    Invoke-RestMethod -Uri "http://167.172.91.249/wp-json/sepay/v1/webhook" `
      -Method Post `
      -Headers @{ "Authorization" = "Bearer HKTFASHION_SEPAY_KEY_2026" } `
      -ContentType "application/json; charset=utf-8" `
      -Body $body
    ```

#### Bước 3: Theo dõi phản hồi thời gian thực
Khi lệnh trên chạy thành công, phản hồi từ server sẽ trả về `{"success":true,...}`.
*   **Trang cảm ơn ở trình duyệt của khách hàng** sẽ lập tức nhận biết trạng thái đơn hàng đã được thanh toán hoàn tất (thông qua cơ chế AJAX polling ngầm mỗi 3 giây).
*   Banner `Đang chờ quét mã` biến mất.
*   Hiệu ứng pháo hoa giấy chúc mừng rơi rực rỡ khắp màn hình.
*   Popup Modal "Đặt hàng thành công!" tự động nhảy ra thông báo chuyên nghiệp.
*   Trong trang Quản trị WordPress Admin, đơn hàng chuyển từ trạng thái `Tạm giữ` (On-hold) sang `Đang xử lý` (Processing).
