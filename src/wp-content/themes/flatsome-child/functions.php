<?php
// Add custom Theme Functions here
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


