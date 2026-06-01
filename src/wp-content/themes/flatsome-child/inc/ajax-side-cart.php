<?php
/**
 * Chức năng Giỏ hàng trượt Side Cart AJAX không cần load lại trang
 * Chú thích tiếng Việt dễ hiểu. Xử lý đồng bộ tự động qua WooCommerce Fragments.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Chèn khung HTML của Side Cart và lớp phủ mờ vào chân trang (Footer)
 */
add_action( 'wp_footer', 'dev_render_side_cart_html' );

function dev_render_side_cart_html() {
    // Chỉ hiển thị đối với khách truy cập bình thường (không hiển thị trong trang Admin)
    if ( is_admin() ) {
        return;
    }
    ?>
    <!-- Lớp phủ tối màn hình khi mở Side Cart -->
    <div id="dev-side-cart-overlay" class="dev-side-cart-overlay"></div>

    <!-- Bảng điều khiển Giỏ hàng trượt bên phải -->
    <div id="dev-side-cart" class="dev-side-cart-panel">
        <!-- Tiêu đề giỏ hàng -->
        <div class="dev-side-cart-header">
            <h3>🛒 Giỏ hàng của bạn</h3>
            <button id="dev-side-cart-close" class="dev-side-cart-close-btn">&times;</button>
        </div>

        <!-- Khung chứa danh sách sản phẩm (Sẽ tự động cập nhật qua AJAX) -->
        <div class="dev-side-cart-content">
            <div class="dev-side-cart-content-inner">
                <?php dev_render_side_cart_content(); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 2. Hàm vẽ giao diện bên trong giỏ hàng (Danh sách item, Tổng tiền, Nút thanh toán)
 * Hàm này dùng chung cho cả lần đầu tải trang và khi cập nhật AJAX qua WooCommerce Fragments.
 */
function dev_render_side_cart_content() {
    $cart = WC()->cart;

    if ( $cart->is_empty() ) {
        ?>
        <div class="dev-side-cart-empty">
            <p>Giỏ hàng của bạn đang trống.</p>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button checkout wc-forward">Tiếp tục mua sắm</a>
        </div>
        <?php
        return;
    }

    ?>
    <div class="dev-side-cart-items">
        <?php
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <div class="dev-side-cart-item" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">
                    <!-- Ảnh sản phẩm -->
                    <div class="dev-item-thumbnail">
                        <?php echo $_product->get_image( array( 60, 60 ) ); ?>
                    </div>
                    
                    <!-- Thông tin chi tiết sản phẩm -->
                    <div class="dev-item-details">
                        <h4 class="dev-item-name">
                            <?php if ( ! $product_permalink ) : ?>
                                <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $product_permalink ); ?>">
                                    <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                                </a>
                            <?php endif; ?>
                        </h4>
                        
                        <!-- Hiển thị phân loại biến thể sản phẩm nếu có -->
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

                        <div class="dev-item-price-qty">
                            <span class="dev-item-price"><?php echo $cart->get_product_price( $_product ); ?></span>
                            
                            <!-- Bộ tăng giảm số lượng AJAX -->
                            <div class="dev-qty-selector">
                                <button class="dev-qty-btn dev-qty-minus" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">-</button>
                                <span class="dev-qty-val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
                                <button class="dev-qty-btn dev-qty-plus" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Nút xóa nhanh sản phẩm -->
                    <button class="dev-item-remove-btn" data-cart-key="<?php echo esc_attr( $cart_item_key ); ?>" title="Xóa sản phẩm này">&times;</button>
                </div>
                <?php
            }
        }
        ?>
    </div>

    <!-- Phần tổng kết đơn hàng và nút thanh toán -->
    <div class="dev-side-cart-summary">
        <div class="dev-summary-row">
            <span>Tạm tính:</span>
            <strong><?php echo $cart->get_cart_subtotal(); ?></strong>
        </div>
        <div class="dev-summary-actions">
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="button wc-forward">Xem giỏ hàng</a>
            <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="button checkout wc-forward" style="background-color: #1e3a8a; color: white;">Thanh toán</a>
        </div>
    </div>
    <?php
}

/**
 * 3. Đăng ký Side Cart thành một "Fragment" của WooCommerce
 * Cơ chế này giúp WooCommerce tự động cập nhật HTML của Side Cart qua AJAX mỗi khi giỏ hàng thay đổi
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'dev_side_cart_register_fragments' );

function dev_side_cart_register_fragments( $fragments ) {
    ob_start();
    ?>
    <div class="dev-side-cart-content-inner">
        <?php dev_render_side_cart_content(); ?>
    </div>
    <?php
    $fragments['div.dev-side-cart-content-inner'] = ob_get_clean();

    // Cập nhật cả số lượng giỏ hàng trên icon giỏ hàng của header (nếu có class .cart-icon hoặc .header-cart-link)
    // Thường Flatsome có cơ chế tự động, nhưng chúng ta hỗ trợ thêm nếu cần.
    return $fragments;
}

/**
 * 4. Xử lý AJAX xóa sản phẩm và cập nhật số lượng
 */
add_action( 'wp_ajax_dev_remove_side_cart_item', 'dev_ajax_remove_side_cart_item' );
add_action( 'wp_ajax_nopriv_dev_remove_side_cart_item', 'dev_ajax_remove_side_cart_item' );

function dev_ajax_remove_side_cart_item() {
    $cart_item_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
    
    if ( ! empty( $cart_item_key ) ) {
        // Thực hiện xóa khỏi giỏ hàng WooCommerce
        if ( WC()->cart->remove_cart_item( $cart_item_key ) ) {
            // Lấy danh sách fragments mới sau khi xóa và trả về định dạng JSON
            WC_AJAX::get_refreshed_fragments();
        }
    }
    wp_die();
}

add_action( 'wp_ajax_dev_update_side_cart_qty', 'dev_ajax_update_side_cart_qty' );
add_action( 'wp_ajax_nopriv_dev_update_side_cart_qty', 'dev_ajax_update_side_cart_qty' );

function dev_ajax_update_side_cart_qty() {
    $cart_item_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
    $new_qty = isset( $_POST['qty'] ) ? intval( $_POST['qty'] ) : 1;
    
    if ( ! empty( $cart_item_key ) && $new_qty > 0 ) {
        // Cập nhật số lượng mới trong giỏ hàng
        if ( WC()->cart->set_quantity( $cart_item_key, $new_qty ) ) {
            WC_AJAX::get_refreshed_fragments();
        }
    }
    wp_die();
}

/**
 * 5. Thêm CSS làm đẹp Side Cart trượt và các hiệu ứng
 */
add_action( 'wp_head', 'dev_side_cart_styles' );

function dev_side_cart_styles() {
    ?>
    <style>
        /* Panel giỏ hàng trượt ra từ bên phải */
        .dev-side-cart-panel {
            position: fixed;
            top: 0;
            right: -420px; /* Bắt đầu ẩn hoàn toàn ở mép phải */
            width: 400px;
            max-width: 90vw;
            height: 100vh;
            background-color: #ffffff;
            z-index: 999999;
            box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            transition: right 0.35s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }
        .dev-side-cart-panel.open {
            right: 0; /* Hiện ra khi có class open */
        }
        
        /* Lớp phủ tối nền */
        .dev-side-cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.5);
            z-index: 999998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .dev-side-cart-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        /* Header giỏ hàng */
        .dev-side-cart-header {
            padding: 20px;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dev-side-cart-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111;
        }
        .dev-side-cart-close-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #888;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }
        .dev-side-cart-close-btn:hover {
            color: #000;
        }

        /* Phần chứa nội dung */
        .dev-side-cart-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* Khi giỏ hàng trống */
        .dev-side-cart-empty {
            text-align: center;
            padding: 40px 10px;
        }
        .dev-side-cart-empty p {
            color: #777;
            font-size: 15px;
            margin-bottom: 20px;
        }
        
        /* Danh sách các item */
        .dev-side-cart-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .dev-side-cart-item {
            display: flex;
            gap: 12px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f3f3f3;
            position: relative;
            align-items: center;
        }
        .dev-item-thumbnail img {
            border-radius: 4px;
            border: 1px solid #eee;
            object-fit: cover;
            width: 60px;
            height: 60px;
        }
        .dev-item-details {
            flex: 1;
        }
        .dev-item-name {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.3;
        }
        .dev-item-name a {
            color: #333 !important;
            text-decoration: none !important;
        }
        .dev-item-name a:hover {
            color: #1e3a8a !important;
        }
        .dev-item-price-qty {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        .dev-item-price {
            font-weight: 700;
            color: #111;
            font-size: 14px;
        }
        
        /* Bộ tăng giảm số lượng */
        .dev-qty-selector {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            background: #fcfcfc;
        }
        .dev-qty-btn {
            background: none;
            border: none;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 14px;
            color: #555;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .dev-qty-btn:hover {
            background-color: #f0f0f0;
            color: #000;
        }
        .dev-qty-val {
            width: 30px;
            text-align: center;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* Nút xóa item */
        .dev-item-remove-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #bbb;
            cursor: pointer;
            padding: 5px;
            position: absolute;
            top: 0;
            right: 0;
            line-height: 1;
        }
        .dev-item-remove-btn:hover {
            color: #d32f2f;
        }

        /* Tổng kết tiền và các nút */
        .dev-side-cart-summary {
            padding: 20px;
            background-color: #fafafa;
            border-top: 1px solid #eaeaea;
        }
        .dev-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .dev-summary-row strong {
            font-size: 18px;
            color: #d32f2f;
        }
        .dev-summary-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .dev-summary-actions .button {
            text-align: center;
            display: block;
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 13px;
            box-sizing: border-box;
            text-decoration: none !important;
        }
        
        /* Loading overlay khi đang xử lý AJAX bên trong side cart */
        .dev-cart-loading {
            opacity: 0.5;
            pointer-events: none;
        }
    </style>
    <?php
}

/**
 * 6. Thêm kịch bản Javascript điều khiển Side Cart
 */
add_action( 'wp_footer', 'dev_side_cart_scripts' );

function dev_side_cart_scripts() {
    if ( is_admin() ) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            var sideCart = $('#dev-side-cart');
            var overlay = $('#dev-side-cart-overlay');
            
            // Hàm mở Side Cart
            function openSideCart() {
                sideCart.addClass('open');
                overlay.addClass('open');
                $('body').css('overflow', 'hidden'); // Ngăn cuộn trang chính
            }

            // Hàm đóng Side Cart
            function closeSideCart() {
                sideCart.removeClass('open');
                overlay.removeClass('open');
                $('body').css('overflow', ''); // Khôi phục cuộn trang
            }

            // Lắng nghe sự kiện click mở giỏ hàng từ Header (Bắt các class giỏ hàng của Flatsome)
            $(document).on('click', '.header-cart-link, .cart-icon, .cart-link, .dev-toggle-side-cart', function(e) {
                e.preventDefault();
                openSideCart();
            });

            // Click nút đóng hoặc click ra ngoài lớp phủ để đóng
            $(document).on('click', '#dev-side-cart-close, #dev-side-cart-overlay', function() {
                closeSideCart();
            });

            // TỰ ĐỘNG MỞ GIỎ HÀNG sau khi thêm sản phẩm thành công bằng AJAX
            $(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
                openSideCart();
            });

            // AJAX XỬ LÝ: Xóa sản phẩm trực tiếp trong Side Cart
            $(document).on('click', '.dev-item-remove-btn', function(e) {
                e.preventDefault();
                var cartKey = $(this).data('cart-key');
                var $cartContent = $('.dev-side-cart-content');

                $cartContent.addClass('dev-cart-loading');

                $.ajax({
                    type: 'POST',
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                    data: {
                        action: 'dev_remove_side_cart_item',
                        cart_key: cartKey
                    },
                    success: function(response) {
                        // WooCommerce Fragments sẽ tự động chèn HTML mới nhận được từ response vào
                        // Nhờ filter woocommerce_add_to_cart_fragments đã đăng ký phía trên.
                        if (response && response.fragments) {
                            $(document.body).trigger('removed_from_cart', [response.fragments, response.cart_hash]);
                        }
                    },
                    complete: function() {
                        $cartContent.removeClass('dev-cart-loading');
                    }
                });
            });

            // AJAX XỬ LÝ: Tăng/giảm số lượng trực tiếp trong Side Cart
            $(document).on('click', '.dev-qty-btn', function(e) {
                e.preventDefault();
                var cartKey = $(this).data('cart-key');
                var isPlus = $(this).hasClass('dev-qty-plus');
                var $item = $(this).closest('.dev-side-cart-item');
                var currentQty = parseInt($item.find('.dev-qty-val').text());
                var newQty = isPlus ? currentQty + 1 : currentQty - 1;
                var $cartContent = $('.dev-side-cart-content');

                if (newQty <= 0) {
                    // Nếu số lượng giảm về 0, kích hoạt nút xóa sản phẩm
                    $item.find('.dev-item-remove-btn').trigger('click');
                    return;
                }

                $cartContent.addClass('dev-cart-loading');

                $.ajax({
                    type: 'POST',
                    url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                    data: {
                        action: 'dev_update_side_cart_qty',
                        cart_key: cartKey,
                        qty: newQty
                    },
                    success: function(response) {
                        if (response && response.fragments) {
                            // Cập nhật lại HTML giỏ hàng tự động
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash]);
                        }
                    },
                    complete: function() {
                        $cartContent.removeClass('dev-cart-loading');
                    }
                });
            });
        });
    </script>
    <?php
}
