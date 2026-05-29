#!/bin/bash

echo "🚀 Bắt đầu thiết lập môi trường phát triển thương mại điện tử..."

# 1. Khởi chạy Docker containers (MySQL, WordPress, WP-CLI)
docker compose up -d

echo "⏳ Đang chờ Database khởi động (15 giây)..."
sleep 15

# 2. Tự động cài đặt WordPress Core (Bỏ qua giao diện cài đặt thủ công)
if ! docker compose run --rm cli wp core is-installed --allow-root; then
    echo "⚙️ Đang tiến hành cài đặt WordPress tự động..."
    docker compose run --rm cli wp core install \
        --url="http://localhost:8000" \
        --title="E-Commerce" \
        --admin_user="admin" \
        --admin_password="admin_password" \
        --admin_email="admin@eurustudio.com" \
        --skip-email \
        --allow-root
    echo "✅ Cài đặt WordPress thành công!"
else
    echo "ℹ️ WordPress đã được cài đặt từ trước."
fi

# 3. Cài đặt và kích hoạt plugin WooCommerce
echo "📦 Đang kiểm tra và cài đặt WooCommerce..."
docker compose run --rm cli wp plugin install woocommerce --activate --allow-root

# 4. Tự động bỏ qua Setup Wizard của WooCommerce
echo "⚡ Tối ưu hóa cấu hình WooCommerce (Bỏ qua Setup Wizard)..."
docker compose run --rm cli wp option update woocommerce_onboarding_profile '{"skip_tracker":true,"completed":true}' --allow-root
docker compose run --rm cli wp option update woocommerce_onboarding_opt_in 'no' --allow-root

# 5. Tự động giải nén Flatsome parent theme từ file zip nếu chưa tồn tại
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

# 6. Kích hoạt Theme Flatsome Child
if [ -d "src/wp-content/themes/flatsome" ]; then
    echo "🎨 Đang kích hoạt Flatsome Child theme..."
    docker compose run --rm cli wp theme activate flatsome-child --allow-root
else
    echo "❌ LỖI: Không thể kích hoạt Flatsome Child do thiếu Flatsome parent theme."
fi

echo "--------------------------------------------------------"
echo "✅ THIẾT LẬP HOÀN TẤT!"
echo "💻 Link Website: http://localhost:8000"
echo "🔑 Tài khoản Admin: admin / admin_password"
echo "⚙️ Link Admin: http://localhost:8000/wp-admin"
echo "--------------------------------------------------------"
