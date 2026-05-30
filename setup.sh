#!/bin/bash

echo "🚀 Bắt đầu thiết lập môi trường phát triển thương mại điện tử..."

# 0. Tạo các thư mục cần thiết trước ở local để tránh lỗi Permission Denied của Docker trên Windows
mkdir -p src/wp-content/upgrade
mkdir -p src/wp-content/plugins
mkdir -p src/wp-content/uploads

# 1. Khởi chạy Docker containers (MySQL, WordPress, WP-CLI)
docker compose up -d

echo "⏳ Đang chờ Database khởi động (15 giây)..."
sleep 15

# 2. Kiểm tra và tự động tải mã nguồn WordPress Core nếu chưa tồn tại
if [ ! -f "src/wp-settings.php" ]; then
    echo "🌐 Không tìm thấy mã nguồn WordPress trong thư mục src."
    echo "📥 Đang tải WordPress Core tự động qua WP-CLI..."
    docker compose run --rm cli wp core download --allow-root
    echo "✅ Tải WordPress Core thành công!"
fi

# 3. Kiểm tra và tự động tạo file cấu hình wp-config.php nếu chưa tồn tại
if [ ! -f "src/wp-config.php" ]; then
    echo "⚙️ Không tìm thấy file cấu hình wp-config.php."
    echo "📝 Đang tự động tạo file wp-config.php kết nối database..."
    docker compose run --rm cli wp config create \
        --dbname=wordpress \
        --dbuser=wordpress \
        --dbpass=wordpress_password \
        --dbhost=db:3306 \
        --allow-root
    echo "✅ Tạo file wp-config.php thành công!"
fi

# 4. Tự động cài đặt cơ sở dữ liệu WordPress (Bỏ qua giao diện cài đặt thủ công)
if ! docker compose run --rm cli wp core is-installed --allow-root; then
    echo "⚙️ Đang tiến hành cài đặt cơ sở dữ liệu WordPress tự động..."
    docker compose run --rm cli wp core install \
        --url="http://localhost:8000" \
        --title="E-Commerce" \
        --admin_user="admin" \
        --admin_password="admin_password" \
        --admin_email="admin@eurustudio.com" \
        --skip-email \
        --allow-root
    echo "✅ Cài đặt cơ sở dữ liệu WordPress thành công!"
else
    echo "ℹ️ WordPress đã được cài đặt cơ sở dữ liệu từ trước."
fi

# 5. Cài đặt và kích hoạt plugin WooCommerce
echo "📦 Đang kiểm tra và cài đặt WooCommerce..."
docker compose run --rm cli wp plugin install woocommerce --activate --allow-root

# 6. Tự động bỏ qua Setup Wizard của WooCommerce
echo "⚡ Tối ưu hóa cấu hình WooCommerce (Bỏ qua Setup Wizard)..."
docker compose run --rm cli wp option update woocommerce_onboarding_profile '{"skip_tracker":true,"completed":true}' --allow-root
docker compose run --rm cli wp option update woocommerce_onboarding_opt_in 'no' --allow-root

# 7. Tự động Import dữ liệu mẫu của WooCommerce (Mock Products)
if ! docker compose run --rm cli wp post list --post_type=product --format=count --allow-root | grep -q '[1-9]'; then
    echo "📦 Đang import dữ liệu sản phẩm mẫu của WooCommerce..."
    docker compose run --rm cli wp plugin install wordpress-importer --activate --allow-root
    docker compose run --rm cli wp import wp-content/plugins/woocommerce/sample-data/sample_products.xml --authors=skip --allow-root
    docker compose run --rm cli wp plugin deactivate wordpress-importer --allow-root
    echo "✅ Import sản phẩm mẫu thành công!"
else
    echo "ℹ️ Đã có sẵn sản phẩm trong cửa hàng, bỏ qua import mẫu."
fi

# 8. Tự động giải nén Flatsome parent theme từ file zip nếu chưa tồn tại
if [ ! -d "src/wp-content/themes/flatsome" ]; then
    if [ -f "flatsome.zip" ]; then
        echo "📦 Đang giải nén Flatsome parent theme..."
        unzip -q flatsome.zip -d src/wp-content/themes/
        echo "✅ Giải nén Flatsome thành công!"
    else
        echo "⚠️ CẢNH BÁO: Không tìm thấy file flatsome.zip ở thư mục gốc."
        echo "👉 Vui lòng copy file flatsome.zip vào thư mục gốc của dự án này!"
    fi
fi

# 9. Kích hoạt Theme Flatsome Child
if [ -d "src/wp-content/themes/flatsome" ]; then
    echo "🎨 Đang kích hoạt Flatsome Child theme..."
    docker compose run --rm cli wp theme activate flatsome-child --allow-root
else
    echo "❌ LỖI: Không thể kích hoạt Flatsome Child do thiếu Flatsome parent theme."
fi

# 10. Tự động cấu hình Permalinks dạng Post Name (Tránh lỗi 404 trang con)
echo "🔗 Cấu hình đường dẫn tĩnh Permalinks (Post name)..."
docker compose run --rm cli wp rewrite structure '/%postname%/' --hard --allow-root
docker compose run --rm cli wp rewrite rules flush --allow-root
echo "✅ Cấu hình đường dẫn tĩnh thành công!"

# 11. Tự động cấu hình Trang chủ (Front Page) mặc định là trang Cửa hàng (Shop)
echo "🏠 Cấu hình Trang chủ mặc định hiển thị trang Cửa hàng (Shop)..."
docker compose run --rm cli wp eval "update_option('show_on_front', 'page'); \$shop = get_page_by_path('shop'); if (\$shop) { update_option('page_on_front', \$shop->ID); }" --allow-root
echo "✅ Cấu hình Trang chủ thành công!"

echo "--------------------------------------------------------"
echo "✅ THIẾT LẬP HOÀN TẤT!"
echo "💻 Link Website: http://localhost:8000"
echo "🔑 Tài khoản Admin: admin / admin_password"
echo "⚙️ Link Admin: http://localhost:8000/wp-admin"
echo "--------------------------------------------------------"
