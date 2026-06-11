#!/bin/bash

# Bỏ qua chuyển đổi đường dẫn POSIX trên Windows (Git Bash)
export MSYS_NO_PATHCONV=1

echo "🚀 Bắt đầu thiết lập môi trường phát triển thương mại điện tử..."

# 0. Tạo các thư mục cần thiết trước ở local để tránh lỗi Permission Denied của Docker trên Windows
mkdir -p src/wp-content/upgrade
mkdir -p src/wp-content/plugins
mkdir -p src/wp-content/uploads

# 1. Khởi chạy Docker containers (MySQL, WordPress, WP-CLI)
docker compose up -d

echo "⏳ Đang chờ Database khởi động (15 giây)..."
sleep 15

# 2. Đảm bảo WordPress Core tồn tại và toàn vẹn
echo "🔍 Kiểm tra mã nguồn WordPress Core..."
# Dùng php -d memory_limit=512M để bypass hardcoded 128M trong wordpress:cli wrapper
WP_DL="php -d memory_limit=512M /usr/local/bin/wp core download --allow-root"
if ! docker compose run --rm --user root cli sh -c "$WP_DL" 2>/dev/null; then
    # Download fail = WP Core files partial/sót từ lần chạy trước
    # QUAN TRỌNG: dừng wordpress trước cleanup VÀ download
    # (entrypoint của image wordpress:latest tự copy lại WP files khi container start)
    echo "⚠️  Phát hiện WP Core không đầy đủ, đang dọn dẹp và tải lại..."
    docker compose stop wordpress
    # Xóa WP Core cũ trong CLI container (không có Apache lock nữa)
    docker compose run --rm --user root cli bash -c \
        "find /var/www/html/wp-admin -mindepth 1 -delete 2>/dev/null; rmdir /var/www/html/wp-admin 2>/dev/null; \
         find /var/www/html/wp-includes -mindepth 1 -delete 2>/dev/null; rmdir /var/www/html/wp-includes 2>/dev/null; \
         find /var/www/html -maxdepth 1 -name '*.php' ! -name 'wp-config.php' -delete; \
         find /var/www/html -maxdepth 1 \( -name '*.txt' -o -name '*.html' \) -delete; \
         echo 'Cleanup done'"
    # Download với 512M memory trong khi wordpress vẫn đang stopped
    if ! docker compose run --rm --user root cli sh -c "$WP_DL"; then
        echo "❌ LỖI: Không thể tải WordPress Core. Kiểm tra kết nối mạng."
        docker compose down
        exit 1
    fi
    # Khởi lại wordpress container SAU KHI download xong
    docker compose start wordpress
    sleep 5
fi
echo "✅ WordPress Core sẵn sàng!"

# 3. Tự động tạo/ghi đè file cấu hình wp-config.php
# Dùng --force để luôn ghi đúng credentials, tránh dùng config cũ/sai từ lần trước
echo "📝 Đang tạo/cập nhật file wp-config.php kết nối database..."
docker compose run --rm --user root cli wp config create \
    --dbname=wordpress \
    --dbuser=wordpress \
    --dbpass=wordpress_password \
    --dbhost=db:3306 \
    --force \
    --allow-root
echo "✅ Tạo file wp-config.php thành công!"

# 4. Tự động cài đặt cơ sở dữ liệu WordPress (Bỏ qua giao diện cài đặt thủ công)
if ! docker compose run --rm --user root cli wp core is-installed --allow-root 2>/dev/null; then
    echo "⚙️ Đang tiến hành cài đặt cơ sở dữ liệu WordPress tự động..."
    if ! docker compose run --rm --user root cli wp core install \
        --url="http://localhost:8000" \
        --title="E-Commerce" \
        --admin_user="admin" \
        --admin_password="admin_password" \
        --admin_email="admin@eurustudio.com" \
        --skip-email \
        --allow-root; then
        echo "❌ LỖI NGHIÊM TRỌNG: Không thể cài đặt WordPress. Vui lòng kiểm tra logs database."
        echo "   Thử chạy: docker compose logs db"
        docker compose down
        exit 1
    fi
    echo "✅ Cài đặt cơ sở dữ liệu WordPress thành công!"
else
    echo "ℹ️ WordPress đã được cài đặt cơ sở dữ liệu từ trước."
fi

# 5. Cài đặt và kích hoạt các plugin cần thiết (WooCommerce, Contact Form 7, Nextend Social Login)
echo "📦 Đang kiểm tra và cài đặt các plugin cần thiết..."
docker compose run --rm --user root cli wp plugin install woocommerce contact-form-7 nextend-facebook-connect --activate --allow-root


# 6. Tự động bỏ qua Setup Wizard của WooCommerce và cấu hình Quốc gia/Tiền tệ VN
echo "⚡ Tối ưu hóa cấu hình WooCommerce (Bỏ qua Setup Wizard & Cài đặt Việt Nam)..."
docker compose run --rm --user root cli wp option update woocommerce_onboarding_profile '{"skip_tracker":true,"completed":true}' --allow-root
docker compose run --rm --user root cli wp option update woocommerce_onboarding_opt_in 'no' --allow-root
docker compose run --rm --user root cli wp option update woocommerce_default_country 'VN' --allow-root
docker compose run --rm --user root cli wp option update woocommerce_currency 'VND' --allow-root
docker compose run --rm --user root cli wp option update woocommerce_price_num_decimals '0' --allow-root

# 7. Tự động Import sản phẩm biến thể thực tế từ icondenim.com
if ! docker compose run --rm --user root cli wp post list --post_type=product --format=count --user=admin --allow-root | grep -q '[1-9]'; then
    echo "📦 Đang tải sản phẩm biến thể thực tế từ icondenim.com..."
    echo "⚠️  Lưu ý: Quá trình này sẽ tải hình ảnh từ CDN về máy local, có thể mất từ 3-5 phút tùy tốc độ mạng của bạn. Vui lòng chờ..."
    docker compose run --rm --user root cli wp eval-file wp-content/themes/flatsome-child/inc/data/import-variable-products.php --allow-root
    echo "🧹 Đang dọn dẹp các tệp ảnh không sử dụng..."
    docker compose run --rm --user root cli wp eval-file wp-content/themes/flatsome-child/inc/data/cleanup-unused-media.php --allow-root
    echo "✅ Import sản phẩm thực tế thành công!"
else
    echo "ℹ️ Đã có sẵn sản phẩm trong cửa hàng, bỏ qua import."
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
    docker compose run --rm --user root cli wp theme activate flatsome-child --allow-root
else
    echo "❌ LỖI: Không thể kích hoạt Flatsome Child do thiếu Flatsome parent theme."
fi

# 10. Tự động cấu hình Permalinks dạng Post Name (Tránh lỗi 404 trang con)
echo "🔗 Cấu hình đường dẫn tĩnh Permalinks (Post name)..."
docker compose run --rm --user root cli wp rewrite structure '/%postname%/' --hard --allow-root
docker compose run --rm --user root cli wp rewrite flush --hard --allow-root
echo "✅ Cấu hình đường dẫn tĩnh thành công!"

# 11. Tự động cấu hình Trang chủ (Front Page) mặc định là Custom Homepage
echo "🏠 Cấu hình Trang chủ mặc định hiển thị trang Custom Homepage..."
docker compose run --rm --user root cli wp eval "
\$home = get_page_by_path('trang-chu');
if (!\$home) {
    \$home_id = wp_insert_post(array(
        'post_title' => 'Trang chủ',
        'post_name' => 'trang-chu',
        'post_status' => 'publish',
        'post_type' => 'page',
    ));
    if (\$home_id) {
        update_post_meta(\$home_id, '_wp_page_template', 'template-custom-home.php');
        update_option('show_on_front', 'page');
        update_option('page_on_front', \$home_id);
    }
} else {
    update_post_meta(\$home->ID, '_wp_page_template', 'template-custom-home.php');
    update_option('show_on_front', 'page');
    update_option('page_on_front', \$home->ID);
}
" --user=admin --allow-root
echo "✅ Cấu hình Trang chủ thành công!"

# 12. Khôi phục quyền sở hữu thư mục wp-content cho www-data để webserver có quyền ghi
echo "⚙️ Đang khôi phục quyền sở hữu thư mục wp-content cho www-data..."
docker compose run --rm --user root cli chown -R www-data:www-data /var/www/html/wp-content
echo "✅ Khôi phục quyền sở hữu thành công!"

echo "--------------------------------------------------------"
echo "✅ THIẾT LẬP HOÀN TẤT!"
echo "💻 Link Website: http://localhost:8000"
echo "🔑 Tài khoản Admin: admin / admin_password"
echo "⚙️ Link Admin: http://localhost:8000/wp-admin"
echo "--------------------------------------------------------"
