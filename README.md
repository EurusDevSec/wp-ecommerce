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

## 3. Install database-client extension from VS Code Marketplace
## or antigravity, cursor, kiro,...
## Connect to database
Host: localhost
Port: 8080
Database: wordpress
User: wordpress
Password: [PASSWORD]



# Access WordPress admin
http://localhost:8000/wp-admin

# Access WordPress site
http://localhost:8000

# Access database client
http://localhost:8080



# How to contribute

## 👥 How to contribute

### 🌿 Git Branching Convention
Để tránh xung đột code, cả nhóm thống nhất không push trực tiếp lên nhánh `main`. Quy trình làm việc như sau:
1. Tạo nhánh mới từ `main` để làm tính năng:
   * Tính năng mới: `feature/[tên-tính-năng]` (Ví dụ: `feature/custom-header`, `feature/payment-momo`)
   * Sửa lỗi: `bugfix/[lỗi-cần-sửa]` (Ví dụ: `bugfix/checkout-mobile-broken`)
   * Cải tiến/Refactor: `refactor/[tên-cải-tiến]`
2. Sau khi làm xong ở local và test kỹ ➔ Push nhánh đó lên GitHub và tạo **Pull Request (PR)** vào nhánh `main`.
3. Người review code ➔ Merge vào `main` ➔ GitHub Actions sẽ tự động deploy lên server staging (`tt.phung.vn`).

### 💬 Commit Message Convention
Viết commit message rõ ràng theo định dạng: `<type>: <description>`
*   `feat`: Thêm tính năng mới (Ví dụ: `feat: add momo payment gateway`)
*   `fix`: Sửa lỗi (Ví dụ: `fix: resolve mobile layout breaking on cart page`)
*   `style`: Thay đổi giao diện, CSS (Ví dụ: `style: change primary button color to green`)
*   `chore`: Cập nhật cấu hình, thư viện, script (Ví dụ: `chore: update docker-compose for wp-cli`)
*   `docs`: Cập nhật tài liệu, README (Ví dụ: `docs: update git convention`)
