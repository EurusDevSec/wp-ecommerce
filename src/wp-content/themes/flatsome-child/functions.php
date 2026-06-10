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
    'mobile-navigation.php',     // Thanh Bottom Navigation Bar trên mobile
    'sepay-integration.php',    // Tích hợp thanh toán tự động SePay Webhook
    'dashboard-helper.php'      // Các hàm hỗ trợ Dashboard & Mua lại nhanh AJAX
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

/**
 * Tự động tạo cấu trúc Menu cứng bằng code PHP, bỏ qua database hoàn toàn (Code-driven Menu)
 */
add_filter( 'wp_get_nav_menu_items', 'dev_hardcode_hkt_menu', 10, 3 );
function dev_hardcode_hkt_menu( $items, $menu, $args ) {
    // Không can thiệp trong trang quản trị admin
    if ( is_admin() ) {
        return $items;
    }
    
    // Chỉ can thiệp khi menu được gọi là hkt-menu, hoặc location tương ứng
    if ( ( isset($menu->slug) && ($menu->slug === 'hkt-menu' || $menu->slug === 'primary' || $menu->slug === 'primary_mobile') ) 
         || ( isset($menu->name) && ($menu->name === 'HKT Menu' || $menu->name === 'hkt-menu') ) ) {
        
        $items = array();
        
        // Cấu hình menu dạng mảng tĩnh
        $raw_menu = array(
            array( 'id' => 1001, 'title' => 'TRANG CHỦ', 'url' => '/', 'parent' => 0 ),
            array( 'id' => 1002, 'title' => 'GIỚI THIỆU', 'url' => '/gioi-thieu/', 'parent' => 0 ),
            
            array( 'id' => 1003, 'title' => 'ÁO', 'url' => '/product-category/ao/', 'parent' => 0 ),
            array( 'id' => 1031, 'title' => 'Áo thun', 'url' => '/product-category/ao-thun/', 'parent' => 1003 ),
            array( 'id' => 1032, 'title' => 'Áo sơ mi', 'url' => '/product-category/ao-so-mi/', 'parent' => 1003 ),
            array( 'id' => 1033, 'title' => 'Áo khoác', 'url' => '/product-category/ao-khoac/', 'parent' => 1003 ),
            
            array( 'id' => 1004, 'title' => 'BALO – TÚI', 'url' => '/product-category/balo-tui/', 'parent' => 0 ),
            array( 'id' => 1041, 'title' => 'Balo', 'url' => '/product-category/balo/', 'parent' => 1004 ),
            array( 'id' => 1042, 'title' => 'Túi', 'url' => '/product-category/tui/', 'parent' => 1004 ),
            
            array( 'id' => 1005, 'title' => 'QUẦN', 'url' => '/product-category/quan/', 'parent' => 0 ),
            array( 'id' => 1051, 'title' => 'Quần jean', 'url' => '/product-category/quan-jean/', 'parent' => 1005 ),
            array( 'id' => 1052, 'title' => 'Quần short', 'url' => '/product-category/quan-short/', 'parent' => 1005 ),
            
            array( 'id' => 1006, 'title' => 'NÓN', 'url' => '/product-category/non/', 'parent' => 0 ),
            
            array( 'id' => 1007, 'title' => 'GIÀY', 'url' => '/product-category/giay/', 'parent' => 0 ),
            array( 'id' => 1071, 'title' => 'Giày nam', 'url' => '/product-category/giay-nam/', 'parent' => 1007 ),
            array( 'id' => 1072, 'title' => 'Giày nữ', 'url' => '/product-category/giay-nu/', 'parent' => 1007 ),
            
            array( 'id' => 1008, 'title' => 'TÚI', 'url' => '/product-category/tui/', 'parent' => 0 ),
            
            array( 'id' => 1009, 'title' => 'PHỤ KIỆN', 'url' => '/product-category/phu-kien/', 'parent' => 0 ),
            array( 'id' => 1091, 'title' => 'Dây nịt da', 'url' => '/product-category/day-nit-da/', 'parent' => 1009 ),
            array( 'id' => 1092, 'title' => 'Tất – Vớ', 'url' => '/product-category/tat-vo/', 'parent' => 1009 ),
            array( 'id' => 1093, 'title' => 'Ví da', 'url' => '/product-category/vi-da/', 'parent' => 1009 ),
            
            array( 'id' => 1010, 'title' => 'SANDAL – DÉP', 'url' => '/product-category/sandal-dep/', 'parent' => 0 ),
            array( 'id' => 1101, 'title' => 'Sandal nam', 'url' => '/product-category/sandal-nam/', 'parent' => 1010 ),
            array( 'id' => 1102, 'title' => 'Sandal nữ', 'url' => '/product-category/sandal-nu/', 'parent' => 1010 ),
            array( 'id' => 1103, 'title' => 'Dép nam', 'url' => '/product-category/dep-nam/', 'parent' => 1010 ),
            array( 'id' => 1104, 'title' => 'Dép nữ', 'url' => '/product-category/dep-nu/', 'parent' => 1010 ),
            
            array( 'id' => 1011, 'title' => 'TIN TỨC', 'url' => '/tin-tuc/', 'parent' => 0 ),
            array( 'id' => 1012, 'title' => 'LIÊN HỆ', 'url' => '/lien-he/', 'parent' => 0 )
        );
        
        $position = 1;
        foreach ( $raw_menu as $menu_data ) {
            $item = new stdClass();
            $item->ID = $menu_data['id'];
            $item->db_id = $menu_data['id'];
            $item->title = $menu_data['title'];
            $item->url = home_url( $menu_data['url'] );
            $item->menu_item_parent = $menu_data['parent'];
            $item->menu_order = $position++;
            $item->type = 'custom';
            $item->object = 'custom';
            $item->type_label = 'Custom Link';
            $item->classes = array();
            $item->target = '';
            $item->attr_title = '';
            $item->description = '';
            $item->xfn = '';
            $item->status = 'publish';
            $item->post_status = 'publish';
            
            $items[] = $item;
        }
    }
    return $items;
}

/**
 * Điều chỉnh các element trên Header Bottom bằng code (Code-driven layout)
 * Đưa Menu chính sang trái, Ô tìm kiếm sang phải, dọn dẹp các vị trí khác
 */
add_filter( 'theme_mod_header_elements_left', 'dev_filter_header_elements_left' );
function dev_filter_header_elements_left( $value ) {
    return array(); // Bỏ search-form ở header main
}

add_filter( 'theme_mod_header_elements_bottom_left', 'dev_filter_header_bottom_left' );
function dev_filter_header_bottom_left( $value ) {
    return array( 'nav' ); // Đưa Main Menu sang bên trái của header bottom
}

add_filter( 'theme_mod_header_elements_bottom_center', 'dev_filter_header_bottom_center' );
function dev_filter_header_bottom_center( $value ) {
    return array(); // Xóa element ở giữa của header bottom
}

add_filter( 'theme_mod_header_elements_bottom_right', 'dev_filter_header_bottom_right' );
function dev_filter_header_bottom_right( $value ) {
    return array( 'search-form' ); // Đưa ô tìm kiếm (search-form) sang bên phải của header bottom
}

/**
 * Tự động loại trừ các danh mục đồ lót, đồ ngủ khỏi trang chủ
 */
add_action( 'pre_get_posts', 'dev_exclude_lingerie_from_homepage', 99 );
function dev_exclude_lingerie_from_homepage( $query ) {
    if ( is_admin() ) {
        return;
    }
    
    // Chỉ can thiệp vào truy vấn sản phẩm
    if ( $query->get( 'post_type' ) === 'product' ) {
        // Chỉ áp dụng trên trang chủ/custom homepage
        if ( is_front_page() || is_home() || is_page_template( 'template-custom-home.php' ) ) {
            $tax_query = $query->get( 'tax_query' );
            if ( ! is_array( $tax_query ) ) {
                $tax_query = array();
            }
            
            // Các slug danh mục liên quan đồ lót, váy ngủ, áo choàng ngủ
            $excluded_slugs = array( 
                'do-lot', 
                'quan-lot', 
                'quan-lot-ren', 
                'vay-ngu', 
                'vay-ngu-2-day', 
                'ao-choang-ngu'
            );
            
            $tax_query[] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $excluded_slugs,
                'operator' => 'NOT IN',
            );
            
            $query->set( 'tax_query', $tax_query );
        }
    }
}

/**
 * Footer chuyên nghiệp 4 cột - Code-driven (giống ThoiTrang19)
 * Cột 1: Giới thiệu cửa hàng + social icons
 * Cột 2: Về chúng tôi - navigation links
 * Cột 3: Thông tin - policy links
 * Cột 4: Chấp nhận thanh toán - payment icons
 */
add_action( 'wp_footer', 'dev_render_custom_footer', 5 );
function dev_render_custom_footer() {
    ?>
    <style>
        /* Override default footer widgets */
        .footer-widgets { display: none !important; }
    </style>
    <div id="dev-custom-footer" class="dev-custom-footer">
        <div class="dev-footer-inner">
            <!-- Column 1: Store Info -->
            <div class="dev-footer-col dev-footer-about">
                <div class="dev-footer-logo">
                    <span class="dev-logo-text">HKT</span>
                    <span class="dev-logo-sub">FASHION</span>
                </div>
                <p class="dev-footer-desc">
                    Cửa hàng HKT Fashion chuyên kinh doanh các sản phẩm thời trang: Quần áo, Giày dép, Balo, Túi xách, Phụ kiện...
                </p>
                <div class="dev-footer-social">
                    <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook" class="dev-social-icon dev-social-fb">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram" class="dev-social-icon dev-social-ig">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    <a href="https://pinterest.com" target="_blank" rel="noopener" aria-label="Pinterest" class="dev-social-icon dev-social-pin">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.631-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Column 2: Về chúng tôi -->
            <div class="dev-footer-col">
                <h4 class="dev-footer-heading">Về chúng tôi</h4>
                <ul class="dev-footer-links">
                    <li><a href="/gioi-thieu/">› Giới thiệu</a></li>
                    <li><a href="/lien-he/">› Liên hệ</a></li>
                    <li><a href="/my-account/orders/">› Kiểm tra đơn hàng</a></li>
                </ul>
            </div>

            <!-- Column 3: Thông tin -->
            <div class="dev-footer-col">
                <h4 class="dev-footer-heading">Thông tin</h4>
                <ul class="dev-footer-links">
                    <li><a href="/thoa-thuan-dich-vu/">› Thỏa thuận dịch vụ</a></li>
                    <li><a href="/chinh-sach-bao-mat/">› Chính sách bảo mật</a></li>
                    <li><a href="/van-chuyen-va-giao-hang/">› Vận chuyển và Giao hàng</a></li>
                    <li><a href="/chinh-sach-doi-tra/">› Chính sách đổi trả</a></li>
                </ul>
            </div>

            <!-- Column 4: Chấp nhận thanh toán -->
            <div class="dev-footer-col">
                <h4 class="dev-footer-heading">Chấp nhận thanh toán</h4>
                <div class="dev-payment-icons">
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#1A1F71"/><text x="25" y="22" fill="white" font-size="12" font-weight="bold" text-anchor="middle" font-family="Arial">VISA</text></svg>
                    </span>
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#252525"/><circle cx="20" cy="17.5" r="10" fill="#EB001B"/><circle cx="30" cy="17.5" r="10" fill="#F79E1B" opacity="0.8"/></svg>
                    </span>
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#003087"/><text x="25" y="22" fill="white" font-size="10" font-weight="bold" text-anchor="middle" font-family="Arial">PayPal</text></svg>
                    </span>
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#F3F3F3" stroke="#ddd"/><text x="25" y="15" fill="#6772E5" font-size="8" font-weight="bold" text-anchor="middle" font-family="Arial">stripe</text><text x="25" y="26" fill="#333" font-size="7" text-anchor="middle" font-family="Arial">Payments</text></svg>
                    </span>
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#0E4C92"/><text x="25" y="22" fill="white" font-size="10" font-weight="bold" text-anchor="middle" font-family="Arial">JCB</text></svg>
                    </span>
                    <span class="dev-payment-badge">
                        <svg width="40" height="25" viewBox="0 0 50 35" fill="none"><rect width="50" height="35" rx="4" fill="#4CAF50"/><text x="25" y="15" fill="white" font-size="7" font-weight="bold" text-anchor="middle" font-family="Arial">COD</text><text x="25" y="25" fill="white" font-size="5.5" text-anchor="middle" font-family="Arial">Nhận hàng</text></svg>
                    </span>
                </div>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="dev-footer-copyright">
            <div class="dev-footer-copyright-inner">
                <span>Copyright <?php echo date('Y'); ?> HKT Fashion. All rights reserved.</span>
                <div class="dev-footer-copyright-links">
                    <a href="/chinh-sach-bao-mat/">CHÍNH SÁCH BẢO MẬT</a>
                    <a href="/cookies/">COOKIES</a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

