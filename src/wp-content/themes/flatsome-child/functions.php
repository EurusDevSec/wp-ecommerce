<?php
// Add custom Theme Functions here
add_action( 'wp_enqueue_scripts', 'dev_enqueue_google_fonts' );
function dev_enqueue_google_fonts() {
    wp_enqueue_style( 'flatsome-child-google-fonts', 'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&family=Lato:ital,wght@0,300;0,400;0,700;1,400&display=swap', array(), null );
}
add_action( 'woocommerce_after_add_to_cart_form', 'dev_add_guarantee_text' );
function dev_add_guarantee_text() {
    echo '<p class="trust-text" style="color: green; font-weight: bold;">✔ Cam kết hàng chính hãng 100%</p>';
}

add_filter( 'woocommerce_product_single_add_to_cart_text', 'dev_custom_cart_button_text' );
function dev_custom_cart_button_text() {
    return __( 'Mua Ngay', 'woocommerce' );
}

// Tự động load các file xử lý chức năng trong thư mục inc/
$custom_inc_files = array(
    'social-login.php',       // Đăng nhập Google/Facebook
    'payment-gateways.php',   // Thanh toán Banking/QR
    'shipping-methods.php',   // Phí vận chuyển tùy chỉnh
    'product-filters.php',    // Bộ lọc AJAX
    'ajax-side-cart.php',     // Giỏ hàng trượt Side Cart
    'order-notifications.php', // Hiệu ứng pháo hoa, thông báo thành công
    'checkout-customizer.php', // Tùy biến rút gọn form thanh toán & validate SĐT
    'vietnam-divisions.php',   // Tải và tạo API Địa giới Hành chính Việt Nam
    'cron-jobs.php',            // Cron Job tự động hủy đơn quá hạn & Hoàn kho
    'vietqr-bacs.php',          // Tích hợp cổng VietQR động cho đơn hàng BACS
    'smtp-settings.php',         // Cấu hình SMTP gửi mail hóa đơn tin cậy
    'product-swatches.php',     // Biến thể swatches màu/size & Size Guide & Trust Badges
    'mobile-navigation.php'     // Thanh Bottom Navigation Bar trên mobile
);

foreach ( $custom_inc_files as $file ) {
    $file_path = locate_template( 'inc/' . $file );
    if ( file_exists( $file_path ) ) {
        require_once $file_path;
    }
}

/**
 * Khai báo tương thích với tính năng HPOS (High-Performance Order Storage) của WooCommerce
 * Theo chuẩn tiêu chí AC-BE-01 và AC-BE-02.
 */
add_action( 'before_woocommerce_init', 'dev_declare_hpos_compatibility' );

function dev_declare_hpos_compatibility() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
}

/**
 * Thêm hiển thị "Đã bán X" cùng thanh tiến độ dưới tiêu đề sản phẩm trong catalog
 */
add_action( 'woocommerce_after_shop_loop_item_title', 'dev_add_sold_count_to_products', 12 );
function dev_add_sold_count_to_products() {
    global $product;
    if ( ! $product ) {
        return;
    }
    
    $product_id = $product->get_id();
    $sold_count = ($product_id % 75) + 8; // Tạo số đã bán ngẫu nhiên trong khoảng 8 - 83
    
    $total_sales = $product->get_total_sales();
    if ( $total_sales > 0 ) {
        $sold_count += $total_sales;
    }
    
    // Tỉ lệ hoàn thành thanh tiến độ (giả lập)
    $percent = min(100, max(15, ($sold_count % 60) + 20));
    
    ?>
    <div class="dev-sold-count-wrapper" style="margin-top: 6px; margin-bottom: 8px; text-align: left;">
        <span style="font-size: 11px; color: #555555; font-weight: 700; display: block; margin-bottom: 2px;">
            🔥 Đã bán <?php echo esc_html( $sold_count ); ?>
        </span>
        <div class="dev-sold-progress-bar" style="background: #eeeeee; border-radius: 6px; height: 5px; width: 100%; overflow: hidden;">
            <div style="background: #f77426; height: 100%; width: <?php echo esc_attr( $percent ); ?>%; border-radius: 6px;"></div>
        </div>
    </div>
    <?php
}



