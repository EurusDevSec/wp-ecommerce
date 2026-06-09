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

