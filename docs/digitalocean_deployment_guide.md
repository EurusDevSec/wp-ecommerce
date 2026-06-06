# HƯỚNG DẪN TRIỂN KHAI WORDPRESS TRÊN DIGITALOCEAN VPS (CHUẨN DEVOPS)
## Dự án: HKT Fashion (wp-ecommerce)
**Định hướng:** Docker Compose + Nginx Reverse Proxy + SSL Let's Encrypt + GitHub Actions CI/CD

Tài liệu này hướng dẫn chi tiết quy trình thiết lập hạ tầng máy chủ ảo (Droplet) trên **DigitalOcean** và cấu hình quy trình triển khai tự động hóa (CI/CD) chuyên nghiệp cho dự án HKT Fashion.

---

## 🗺️ SƠ ĐỒ KIẾN TRÚC & LUỒNG TRIỂN KHAI (BIG PICTURE)

### 1. Sơ đồ Kiến trúc Hệ thống (System Architecture)
Sơ đồ này mô tả cách người dùng truy cập từ internet vào website WordPress chạy trong Docker thông qua Proxy Nginx trên VPS DigitalOcean.

```mermaid
graph TD
    subgraph Internet [Ngoài Internet]
        User[Khách hàng / Webhook Sepay] -->|HTTPS: Port 443| DuckDNS[Tên miền: hktfashion.duckdns.org]
    end

    subgraph VPS [DigitalOcean Droplet - IP: 165.232.167.88]
        DuckDNS -->|Nginx giải mã SSL| Nginx[Nginx Reverse Proxy]
        Nginx -->|Chuyển tiếp cổng 8000| DockerHost[Docker Host]
        
        subgraph DockerNet [Mạng nội bộ Docker: wpsite]
            DockerHost --> WP[Container: wordpress:latest]
            WP <-->|Cổng 3306 nội bộ| DB[(Container: mysql:8.0)]
        end
    end
```

### 2. Sơ đồ Luồng Tự động hóa CI/CD (CI/CD Deployment Flow)
Sơ đồ này mô tả luồng tự động cập nhật code từ máy của lập trình viên lên VPS mà không cần thao tác thủ công.

```mermaid
sequenceDiagram
    actor Dev as Developer (Local)
    participant Git as GitHub Repository
    participant GHA as GitHub Actions Runner
    participant VPS as DigitalOcean Droplet

    Dev->>Git: git push origin main (Push theme/compose)
    Git->>GHA: Trigger Workflow (deploy.yml)
    GHA->>GHA: Thiết lập SSH Key & Kết nối
    GHA->>VPS: SSH login root@165.232.167.88
    Note over VPS: Di chuyển đến thư mục /var/www/wp-ecommerce
    GHA->>VPS: Chạy lệnh: git pull origin main
    GHA->>VPS: Chạy lệnh: docker compose down && docker compose up -d --build
    Note over VPS: Rebuild & Restart các container
    VPS-->>GHA: Deploy hoàn tất thành công!
    GHA-->>Dev: Cập nhật trạng thái thành công (Green Tick)
```

---

## MỤC LỤC
1. [BƯỚC 1: Khởi tạo Droplet (VPS) trên DigitalOcean](#bước-1-khởi-tạo-droplet-vps-trên-digitalocean)
2. [BƯỚC 2: Cấu hình Trỏ tên miền (DNS)](#bước-2-cấu-hình-trỏ-tên-miền-dns)
3. [BƯỚC 3: Cài đặt Docker & Docker Compose trên VPS](#bước-3-cài-đặt-docker--docker-compose-trên-vps)
4. [BƯỚC 4: Chuẩn bị Thư mục & Chạy Container](#bước-4-chuẩn-bị-thư-mục--chạy-container)
5. [BƯỚC 5: Thiết lập Nginx Reverse Proxy & SSL Let's Encrypt](#bước-5-thiết-lập-nginx-reverse-proxy--ssl-lets-encrypt)
6. [BƯỚC 6: Thiết lập GitHub Actions Auto-Deploy (CI/CD)](#bước-6-thiết-lập-github-actions-auto-deploy-cicd)

---

## BƯỚC 1: Khởi tạo Droplet (VPS) trên DigitalOcean

1. Đăng nhập vào tài khoản [DigitalOcean Console](https://cloud.digitalocean.com/).
2. Chọn **Create** (nút màu xanh lá ở góc trên bên phải) -> Chọn **Droplets**.
3. Cấu hình Droplet như sau:
   * **Region (Vùng máy chủ):** Chọn **Singapore (SGP1)** để tối ưu tốc độ đường truyền về Việt Nam.
   * **Choose an image (Hệ điều hành):** Chọn **Ubuntu 22.04 LTS** (Bản phân phối Linux ổn định nhất).
   * **Size (Cấu hình):**
     * Chọn loại **Basic**.
     * CPU Options: Chọn **Regular SSD**.
     * Chọn gói thấp nhất: **$4/tháng** hoặc **$6/tháng** (1 GB RAM, 1 CPU, 25 GB SSD) là đủ cho môi trường Staging/Development của nhóm.
   * **Authentication (Phương thức đăng nhập):**
     * Bắt buộc chọn **SSH Keys** để đảm bảo bảo mật.
     * Bấm **New SSH Key** -> Copy nội dung key public (`id_rsa.pub`) từ máy cá nhân của bạn và dán vào.
   * **Quantity & Hostname:**
     * Quantity: `1`.
     * Hostname: Đặt tên gợi nhớ (ví dụ: `hkt-fashion-devops-server`).
4. Bấm **Create Droplet**. Chờ khoảng 1 phút, DigitalOcean sẽ cấp cho bạn một địa chỉ **IP Public** (Ví dụ: `128.199.123.45`).

---

## BƯỚC 2: Cấu hình Tên miền miễn phí qua DuckDNS (DNS Settings)

Thay vì mua tên miền thương mại, chúng ta sử dụng dịch vụ tên miền phụ miễn phí **DuckDNS** để phục vụ việc trỏ IP và xin cấp chứng chỉ bảo mật SSL HTTPS:

1. Truy cập vào trang chủ: [https://www.duckdns.org/](https://www.duckdns.org/).
2. Đăng nhập bằng tài khoản **GitHub** hoặc **Google** của bạn.
3. Tại ô **sub domains**, nhập tên miền mong muốn cho dự án (ví dụ: `hktfashion`) và bấm **Add domain**. Tên miền phụ hoàn chỉnh của bạn sẽ là: `hktfashion.duckdns.org`.
4. Trong ô **current ip**, thay thế bằng địa chỉ **IP Public** của Droplet DigitalOcean nhận được ở Bước 1.
5. Bấm nút **Update IP**. Hệ thống sẽ ngay lập tức định tuyến tên miền về máy chủ VPS của bạn.

---

## BƯỚC 3: Cài đặt Docker & Docker Compose trên VPS

1. Mở Terminal (PowerShell/CMD trên Windows) và kết nối SSH vào VPS bằng quyền root:
   ```bash
   ssh root@<IP_VPS_CỦA_BẠN>
   ```
2. Cập nhật hệ thống và cài đặt Docker Engine + Docker Compose:
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install -y docker.io docker-compose
   ```
3. Kích hoạt dịch vụ Docker khởi chạy cùng hệ thống:
   ```bash
   sudo systemctl enable --now docker
   ```

---

## BƯỚC 4: Chuẩn bị Thư mục & Chạy Container

1. Tạo thư mục chứa dự án trên VPS:
   ```bash
   sudo mkdir -p /var/www/wp-ecommerce
   sudo chown -R $USER:$USER /var/www/wp-ecommerce
   ```
2. Khởi tạo cấu hình Docker Compose trên VPS. Khi deploy tự động qua Git, file `docker-compose.yaml` sẽ được đồng bộ lên thư mục `/var/www/wp-ecommerce/`. 
   * *Lưu ý:* Cổng của container `wordpress` được map ra ngoài máy chủ host là cổng `8000` (để tránh xung đột cổng `80/443` với Nginx proxy).
   * Ví dụ cấu hình container wordpress chạy trên host port 8000:
     ```yaml
     ports:
       - "8000:80"
     ```

---

## BƯỚC 5: Thiết lập Nginx Reverse Proxy & SSL Let's Encrypt

Vì website chạy thực tế trên internet cần kết nối HTTPS bảo mật trực tiếp, chúng ta sẽ cài đặt Nginx trên hệ điều hành host để làm Proxy chuyển tiếp yêu cầu từ cổng 80/443 vào container WordPress (cổng 8000).

#### 1. Cài đặt Nginx và Certbot (Let's Encrypt client) trên VPS:
```bash
sudo apt install -y nginx certbot python3-certbot-nginx
```

#### 2. Tạo cấu hình Reverse Proxy cho tên miền DuckDNS:
```bash
sudo nano /etc/nginx/sites-available/hktfashion.duckdns.org
```
*Nhập cấu hình sau (thay đổi `hktfashion.duckdns.org` thành tên miền phụ bạn đã tạo ở Bước 2):*
```nginx
server {
    listen 80;
    server_name hktfashion.duckdns.org;

    location / {
        proxy_pass http://127.0.0.1:8000; # Trỏ ngược vào container WordPress trong Docker
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # Tối ưu hóa upload file dung lượng lớn (ảnh sản phẩm)
        client_max_body_size 64M;
    }
}
```
*Lưu và thoát file (`Ctrl + O`, `Enter`, `Ctrl + X`).*

#### 3. Kích hoạt cấu hình Nginx và xin cấp chứng chỉ SSL Let's Encrypt:
```bash
# Tạo liên kết kích hoạt website
sudo ln -s /etc/nginx/sites-available/hktfashion.duckdns.org /etc/nginx/sites-enabled/

# Kiểm tra cú pháp Nginx xem có lỗi không
sudo nginx -t

# Khởi động lại dịch vụ Nginx để cập nhật proxy mới
sudo systemctl restart nginx

# Chạy Certbot để tự động xin cấp SSL HTTPS cho domain DuckDNS
sudo certbot --nginx -d hktfashion.duckdns.org
```
* Chọn phương thức tự động redirect toàn bộ lượng truy cập HTTP sang HTTPS khi được Certbot hỏi.

---

## BƯỚC 6: Thiết lập GitHub Actions Auto-Deploy (CI/CD)

Hệ thống sẽ tự động cập nhật code mới từ GitHub lên VPS mỗi khi bạn merge code vào nhánh `main`.

#### 1. Khởi tạo Secrets trên GitHub Repository:
Vào repository của bạn trên GitHub -> **Settings** -> **Secrets and variables** -> **Actions** -> Bấm **New repository secret** để thêm các biến bảo mật:
*   `SERVER_HOST`: Địa chỉ **IP Public** của VPS.
*   `SERVER_USER`: Điền `root`.
*   `SSH_PRIVATE_KEY`: Dán nội dung key SSH private (`id_rsa` trên máy cá nhân của bạn, bắt đầu bằng `-----BEGIN OPENSSH PRIVATE KEY-----` và kết thúc bằng `-----END OPENSSH PRIVATE KEY-----`).

#### 2. Khởi tạo file YAML Workflow:
Tạo file `.github/workflows/deploy.yml` trong mã nguồn của bạn:
```yaml
name: Deploy to DigitalOcean Production

on:
  push:
    branches:
      - main # Kích hoạt khi có commit mới push vào main

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Deploy to VPS via SSH
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/wp-ecommerce
            # Cập nhật code mới nhất từ Git
            git pull origin main
            # Restart các container chạy ngầm
            docker compose down
            docker compose up -d --build
```
