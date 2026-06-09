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
 * Tự động loại trừ các danh mục đồ lót, nội y, đồ ngủ khỏi trang chủ
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
            
            // Các slug danh mục liên quan đồ lót, nội y, váy ngủ, áo choàng ngủ
            $excluded_slugs = array( 
                'do-lot', 
                'do-noi-y', 
                'quan-lot', 
                'quan-lot-ren', 
                'vay-ngu', 
                'vay-ngu-2-day', 
                'ao-choang-ngu',
                'noi-y'
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





