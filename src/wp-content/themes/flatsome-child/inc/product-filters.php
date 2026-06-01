<?php
/**
 * Bộ lọc sản phẩm bằng AJAX (Giá, Thương hiệu, Màu sắc) cho trang Cửa hàng WooCommerce
 * Chú thích tiếng Việt dễ hiểu, sử dụng Javascript AJAX thuần (mượt mà, không load lại trang).
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Hiển thị thanh bộ lọc sản phẩm ngay phía trên danh sách sản phẩm trang Cửa hàng (Shop)
 */
add_action( 'woocommerce_before_shop_loop', 'dev_render_ajax_filter_bar', 15 );

function dev_render_ajax_filter_bar() {
    // Chỉ hiển thị trên trang danh mục sản phẩm hoặc trang cửa hàng
    if ( ! is_shop() && ! is_product_category() && ! is_product_taxonomy() ) {
        return;
    }

    // 1.1. Lấy thông tin Thương hiệu tự động
    // Kiểm tra xem website dùng taxonomy nào cho Thương hiệu (product_brand hoặc pa_brand)
    $brand_taxonomy = taxonomy_exists( 'product_brand' ) ? 'product_brand' : ( taxonomy_exists( 'pa_brand' ) ? 'pa_brand' : '' );
    $brands = array();
    if ( ! empty( $brand_taxonomy ) ) {
        $brands = get_terms( array(
            'taxonomy'   => $brand_taxonomy,
            'hide_empty' => false,
        ) );
    }

    // 1.2. Lấy thông tin Màu sắc tự động
    $colors = get_terms( array(
        'taxonomy'   => 'pa_color',
        'hide_empty' => false,
    ) );

    // Hàm phụ trợ để lấy mã màu HEX từ tên tiếng Anh của màu sắc để hiển thị vòng tròn màu sắc đẹp mắt
    function dev_map_color_name_to_hex( $name ) {
        $name_lower = strtolower( trim( $name ) );
        $color_map = array(
            'black'  => '#000000',
            'blue'   => '#3182ce',
            'green'  => '#38a169',
            'red'    => '#e53e3e',
            'yellow' => '#ecc94b',
            'gray'   => '#718096',
            'white'  => '#ffffff',
            'pink'   => '#ed64a6',
            'orange' => '#ed8936',
            'purple' => '#9f7aea',
            'brown'  => '#744210'
        );
        return isset( $color_map[$name_lower] ) ? $color_map[$name_lower] : '#cbd5e0';
    }

    // Lấy các tham số filter hiện tại từ URL để đánh dấu trạng thái "đang chọn" (active)
    $current_min_price = isset( $_GET['min_price'] ) ? sanitize_text_field( $_GET['min_price'] ) : '';
    $current_max_price = isset( $_GET['max_price'] ) ? sanitize_text_field( $_GET['max_price'] ) : '';
    
    // Đối với thuộc tính WooCommerce, query dạng: ?filter_color=black hoặc ?filter_brand=next
    $current_brand = '';
    if ( ! empty( $brand_taxonomy ) ) {
        $url_key = 'filter_' . str_replace( 'pa_', '', $brand_taxonomy );
        $current_brand = isset( $_GET[$url_key] ) ? sanitize_text_field( $_GET[$url_key] ) : '';
    }
    $current_color = isset( $_GET['filter_color'] ) ? sanitize_text_field( $_GET['filter_color'] ) : '';

    // Lấy URL hiện tại để làm base URL khi reset filter
    $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
    ?>
    
    <!-- Giao diện thanh bộ lọc chính -->
    <div class="dev-filter-bar" id="dev-ajax-filter-bar">
        <div class="dev-filter-title">🔎 BỘ LỌC TÌM KIẾM</div>
        
        <div class="dev-filter-sections">
            <!-- PHẦN 1: LỌC THEO GIÁ -->
            <div class="dev-filter-group">
                <span class="dev-group-label">Khoảng giá:</span>
                <div class="dev-filter-options">
                    <a href="<?php echo esc_url( remove_query_arg( array( 'min_price', 'max_price' ) ) ); ?>" class="dev-filter-link <?php echo ( $current_min_price === '' && $current_max_price === '' ) ? 'active' : ''; ?>">Tất cả</a>
                    
                    <a href="<?php echo esc_url( add_query_arg( array( 'min_price' => '', 'max_price' => '100000' ) ) ); ?>" class="dev-filter-link <?php echo ( $current_max_price === '100000' ) ? 'active' : ''; ?>">Dưới 100k</a>
                    
                    <a href="<?php echo esc_url( add_query_arg( array( 'min_price' => '100000', 'max_price' => '500000' ) ) ); ?>" class="dev-filter-link <?php echo ( $current_min_price === '100000' && $current_max_price === '500000' ) ? 'active' : ''; ?>">100k - 500k</a>
                    
                    <a href="<?php echo esc_url( add_query_arg( array( 'min_price' => '500000', 'max_price' => '' ) ) ); ?>" class="dev-filter-link <?php echo ( $current_min_price === '500000' ) ? 'active' : ''; ?>">Trên 500k</a>
                </div>
            </div>

            <!-- PHẦN 2: LỌC THEO THƯƠNG HIỆU -->
            <?php if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) : 
                $brand_url_key = 'filter_' . str_replace( 'pa_', '', $brand_taxonomy );
            ?>
                <div class="dev-filter-group">
                    <span class="dev-group-label">Thương hiệu:</span>
                    <div class="dev-filter-options">
                        <a href="<?php echo esc_url( remove_query_arg( $brand_url_key ) ); ?>" class="dev-filter-link <?php echo empty( $current_brand ) ? 'active' : ''; ?>">Tất cả</a>
                        <?php foreach ( $brands as $brand ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( $brand_url_key, $brand->slug ) ); ?>" class="dev-filter-link <?php echo ( $current_brand === $brand->slug ) ? 'active' : ''; ?>">
                                <?php echo esc_html( $brand->name ); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PHẦN 3: LỌC THEO MÀU SẮC -->
            <?php if ( ! empty( $colors ) && ! is_wp_error( $colors ) ) : ?>
                <div class="dev-filter-group">
                    <span class="dev-group-label">Màu sắc:</span>
                    <div class="dev-filter-options dev-color-options">
                        <a href="<?php echo esc_url( remove_query_arg( 'filter_color' ) ); ?>" class="dev-filter-link <?php echo empty( $current_color ) ? 'active' : ''; ?>">Tất cả</a>
                        <?php foreach ( $colors as $color ) : 
                            $hex = dev_map_color_name_to_hex( $color->name );
                            $is_active = ( $current_color === $color->slug );
                        ?>
                            <a href="<?php echo esc_url( add_query_arg( 'filter_color', $color->slug ) ); ?>" class="dev-color-circle <?php echo $is_active ? 'active' : ''; ?>" title="<?php echo esc_attr( $color->name ); ?>" style="background-color: <?php echo esc_attr( $hex ); ?>;">
                                <?php if ( strtolower($color->name) === 'white' ) : ?>
                                    <span style="border: 1px solid #ddd; border-radius: 50%; width: 100%; height: 100%; display: block; box-sizing: border-box;"></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Nút xóa bộ lọc nhanh -->
        <?php if ( ! empty( $current_min_price ) || ! empty( $current_max_price ) || ! empty( $current_brand ) || ! empty( $current_color ) ) : ?>
            <div class="dev-filter-reset">
                <a href="<?php echo esc_url( $shop_page_url ); ?>" class="dev-reset-btn">❌ Xóa tất cả bộ lọc</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Nhúng CSS & Javascript để xử lý AJAX -->
    <style>
        .dev-filter-bar {
            background-color: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .dev-filter-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            display: inline-block;
        }
        .dev-filter-sections {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .dev-filter-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .dev-group-label {
            font-weight: 600;
            color: #555;
            min-width: 100px;
            font-size: 14px;
        }
        .dev-filter-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .dev-filter-link {
            display: inline-block;
            padding: 6px 14px;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
            border-radius: 20px;
            font-size: 13px;
            color: #4a5568 !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .dev-filter-link:hover, 
        .dev-filter-link.active {
            background-color: #1e3a8a;
            color: #ffffff !important;
            border-color: #1e3a8a;
        }
        .dev-color-options {
            gap: 12px;
        }
        .dev-color-circle {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-block;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .dev-color-circle.active {
            border-color: #1e3a8a;
            transform: scale(1.15);
            box-shadow: 0 0 8px rgba(30,58,138,0.4);
        }
        .dev-color-circle:hover {
            transform: scale(1.1);
        }
        .dev-filter-reset {
            margin-top: 15px;
            text-align: right;
            border-top: 1px solid #eee;
            padding-top: 12px;
        }
        .dev-reset-btn {
            font-size: 13px;
            color: #e53e3e !important;
            font-weight: 600;
            text-decoration: none !important;
            border: 1px solid #fed7d7;
            padding: 6px 12px;
            background-color: #fff5f5;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .dev-reset-btn:hover {
            background-color: #fed7d7;
        }

        /* Lớp phủ mờ (Loading) khi đang AJAX */
        .dev-loading {
            opacity: 0.5;
            pointer-events: none;
            position: relative;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var filterBar = document.getElementById('dev-ajax-filter-bar');
            if (!filterBar) return;

            // Lắng nghe sự kiện click trên toàn bộ thanh bộ lọc
            filterBar.addEventListener('click', function(e) {
                var target = e.target.closest('a');
                if (target && target.getAttribute('href')) {
                    e.preventDefault(); // Ngăn chặn tải lại trang mặc định
                    
                    var targetUrl = target.getAttribute('href');
                    
                    // Thực hiện tải AJAX
                    devFetchFilteredProducts(targetUrl);
                }
            });

            // Hàm xử lý gọi API tải danh sách sản phẩm mới
            function devFetchFilteredProducts(url) {
                // Xác định container chứa sản phẩm của Flatsome hoặc WooCommerce
                // Hỗ trợ nhiều loại container dự phòng để chạy được trên mọi layout
                var shopContainer = document.querySelector('.shop-container') || 
                                    document.querySelector('.products-wrapper') || 
                                    document.querySelector('#primary') || 
                                    document.querySelector('.row.category-page-row');
                                    
                if (!shopContainer) return;

                // Thêm hiệu ứng loading
                shopContainer.classList.add('dev-loading');

                // Dùng Fetch API tải ngầm trang web mới
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        // Tạo một trình phân tích DOM ảo để đọc mã HTML tải về
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');

                        // 1. Cập nhật lại khung sản phẩm
                        var newShopContainer = doc.querySelector('.shop-container') || 
                                               doc.querySelector('.products-wrapper') || 
                                               doc.querySelector('#primary') || 
                                               doc.querySelector('.row.category-page-row');
                        
                        if (newShopContainer && shopContainer) {
                            shopContainer.innerHTML = newShopContainer.innerHTML;
                        }

                        // 2. Cập nhật lại giao diện thanh bộ lọc (để đổi nút active)
                        var newFilterBar = doc.getElementById('dev-ajax-filter-bar');
                        if (newFilterBar && filterBar) {
                            filterBar.innerHTML = newFilterBar.innerHTML;
                        }

                        // 3. Cập nhật lại thanh địa chỉ URL trên trình duyệt để người dùng copy được link
                        window.history.pushState({ path: url }, '', url);

                        // Cuộn trang mượt mà lên đầu danh sách sản phẩm để tiện xem
                        var rect = shopContainer.getBoundingClientRect();
                        var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        window.scrollTo({
                            top: rect.top + scrollTop - 100,
                            behavior: 'smooth'
                        });
                    })
                    .catch(error => {
                        console.error('Lỗi khi tải bộ lọc:', error);
                    })
                    .finally(() => {
                        // Tắt hiệu ứng loading
                        shopContainer.classList.remove('dev-loading');
                    });
            }

            // Hỗ trợ khi người dùng nhấn nút Back/Forward của trình duyệt
            window.addEventListener('popstate', function() {
                window.location.reload();
            });
        });
    </script>
    <?php
}
