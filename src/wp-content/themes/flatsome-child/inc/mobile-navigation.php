<?php
/**
 * Chức năng hiển thị Thanh Bottom Navigation Bar cố định ở chân trang trên Thiết bị di động
 * Chú thích tiếng Việt dễ hiểu, chuẩn SEO và tối ưu trải nghiệm người dùng di động (AC-FE-07).
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Chèn HTML của Bottom Navigation Bar vào Footer
 */
add_action( 'wp_footer', 'dev_render_mobile_bottom_navigation' );

function dev_render_mobile_bottom_navigation() {
    // Không chạy trong trang quản trị
    if ( is_admin() ) {
        return;
    }

    $home_url = esc_url( home_url( '/' ) );
    $cart_url = esc_url( wc_get_cart_url() );
    $account_url = esc_url( get_permalink( get_option('woocommerce_myaccount_page_id') ) );
    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    ?>
    <!-- Thanh Bottom Navigation cho Mobile -->
    <div class="dev-mobile-bottom-nav" id="dev-mobile-bottom-toolbar">
        <!-- 1. Trang chủ -->
        <a href="<?php echo $home_url; ?>" class="dev-nav-item" id="dev-btn-mobile-nav-home">
            <span class="dev-nav-icon">🏠</span>
            <span class="dev-nav-label">Trang chủ</span>
        </a>

        <!-- 2. Tìm kiếm (Kích hoạt popup search của Flatsome hoặc chuyển hướng) -->
        <a href="#" class="dev-nav-item search-trigger" data-open="#search-menu" id="dev-btn-mobile-nav-search">
            <span class="dev-nav-icon">🔍</span>
            <span class="dev-nav-label">Tìm kiếm</span>
        </a>

        <!-- 3. Giỏ hàng trượt (Đồng bộ với ajax-side-cart.php) -->
        <a href="<?php echo $cart_url; ?>" class="dev-nav-item dev-toggle-side-cart" id="dev-btn-mobile-nav-cart">
            <div class="dev-nav-icon-container">
                <span class="dev-nav-icon">🛒</span>
                <span class="dev-cart-bubble dev-cart-count-bubble"><?php echo esc_html( $cart_count ); ?></span>
            </div>
            <span class="dev-nav-label">Giỏ hàng</span>
        </a>

        <!-- 4. Tài khoản -->
        <a href="<?php echo $account_url; ?>" class="dev-nav-item" id="dev-btn-mobile-nav-account">
            <span class="dev-nav-icon">👤</span>
            <span class="dev-nav-label">Tài khoản</span>
        </a>
    </div>

    <!-- Cập nhật số lượng giỏ hàng trên mobile nav bằng AJAX qua fragments -->
    <script>
        jQuery(document).ready(function($) {
            // Cập nhật huy hiệu số lượng giỏ hàng khi có sự kiện thay đổi giỏ hàng từ WooCommerce
            $(document.body).on('added_to_cart removed_from_cart updated_cart_totals', function() {
                // WooCommerce sẽ tự động tải các fragments, nhưng ta có thể chạy AJAX riêng nếu cần
                // Hoặc ta đã hook vào fragments trong ajax-side-cart.php.
                // Để bảo đảm cập nhật ngay, ta đăng ký thêm fragment cho thanh mobile nav:
            });
        });
    </script>
    <?php
}

/**
 * 2. Đăng ký fragment cập nhật số lượng giỏ hàng trên Mobile Bottom Nav
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'dev_mobile_nav_register_fragments' );

function dev_mobile_nav_register_fragments( $fragments ) {
    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    
    ob_start();
    ?>
    <span class="dev-cart-bubble dev-cart-count-bubble"><?php echo esc_html( $cart_count ); ?></span>
    <?php
    $fragments['span.dev-cart-count-bubble'] = ob_get_clean();
    
    return $fragments;
}

/**
 * 3. Chèn HTML của Widget liên hệ nhanh (Call, Zalo, Messenger) vào Footer
 */
add_action( 'wp_footer', 'dev_render_floating_contact_widget' );

function dev_render_floating_contact_widget() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <div class="dev-floating-contact">
        <!-- Zalo Button -->
        <a href="https://zalo.me/0987654321" target="_blank" rel="noopener nofollow" class="dev-contact-btn dev-zalo-btn" title="Chat Zalo">
            <span class="contact-icon">💬</span>
            <span class="contact-text">Zalo</span>
        </a>
        <!-- Messenger Button -->
        <a href="https://m.me/yourpage" target="_blank" rel="noopener nofollow" class="dev-contact-btn dev-messenger-btn" title="Chat Messenger">
            <span class="contact-icon">✉️</span>
            <span class="contact-text">Messenger</span>
        </a>
        <!-- Call Button -->
        <a href="tel:0987654321" class="dev-contact-btn dev-phone-btn" title="Gọi ngay">
            <span class="contact-icon">📞</span>
            <span class="contact-text">Gọi ngay</span>
        </a>
    </div>
    <?php
}

