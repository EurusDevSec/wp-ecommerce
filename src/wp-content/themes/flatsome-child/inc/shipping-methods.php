<?php
/**
 * Phương thức Vận chuyển tùy chỉnh cho WooCommerce
 * Chú thích tiếng Việt dễ hiểu. Logic: Đồng giá 30,000đ, miễn phí giao hàng cho đơn từ 500,000đ trở lên.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Khởi tạo phương thức vận chuyển tùy chỉnh bằng cách kế thừa WC_Shipping_Method
 */
add_action( 'woocommerce_shipping_init', 'dev_custom_shipping_method_init' );

function dev_custom_shipping_method_init() {
    if ( ! class_exists( 'WC_Shipping_Method' ) ) {
        return;
    }

    class WC_Shipping_Custom_Method extends WC_Shipping_Method {

        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'dev_custom_shipping';
            $this->instance_id        = absint( $instance_id );
            $this->method_title       = 'Giao hàng Tiêu Chuẩn (Custom)';
            $this->method_description = 'Phí giao hàng đồng giá 30k. Miễn phí vận chuyển cho đơn hàng từ 500k trở lên.';
            
            $this->init_form_fields();
            $this->init_settings();

            $this->title              = $this->get_option( 'title', 'Giao Hàng Tiêu Chuẩn (Freeship đơn > 500k)' );
            $this->enabled            = $this->get_option( 'enabled', 'yes' );

            // Lưu cài đặt của Admin
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        // Khởi tạo các trường cài đặt trong Admin (nếu muốn chỉnh sửa tiêu đề)
        public function init_form_fields() {
            $this->form_fields = array(
                'title' => array(
                    'title'       => 'Tiêu đề hiển thị',
                    'type'        => 'text',
                    'description' => 'Tên phương thức vận chuyển khách hàng nhìn thấy ở Checkout.',
                    'default'     => 'Giao Hàng Tiêu Chuẩn',
                ),
            );
        }

        /**
         * Logic tính toán phí vận chuyển
         */
        public function calculate_shipping( $package = array() ) {
            // Lấy tổng tiền hàng trong giỏ (contents_cost)
            $cart_total = isset( $package['contents_cost'] ) ? $package['contents_cost'] : 0;
            
            // Phí ship mặc định là 30,000 VND
            $cost = 30000;
            $label = $this->title;

            // Nếu đơn hàng từ 500,000 VND trở lên thì miễn phí ship (0đ)
            if ( $cart_total >= 500000 ) {
                $cost = 0;
                $label .= ' (Miễn phí vận chuyển)';
            } else {
                $label .= ' (Phí ship: 30.000đ)';
            }

            // Gửi thông tin phí ship ngược lại cho WooCommerce
            $rate = array(
                'id'       => $this->get_rate_id(),
                'label'    => $label,
                'cost'     => $cost,
                'package'  => $package,
            );

            $this->add_rate( $rate );
        }
    }
}

/**
 * 2. Đăng ký phương thức vận chuyển này vào WooCommerce
 */
add_filter( 'woocommerce_shipping_methods', 'dev_register_custom_shipping_method' );

function dev_register_custom_shipping_method( $methods ) {
    $methods['dev_custom_shipping'] = 'WC_Shipping_Custom_Method';
    return $methods;
}

/**
 * 3. BỘ INJECTOR TỰ ĐỘNG (RẤT QUAN TRỌNG):
 * Để đảm bảo phương thức vận chuyển này HIỂN THỊ NGAY LẬP TỨC trên trang Checkout/Cart
 * mà khách hàng không cần cấu hình Shipping Zone phức tạp trong Admin.
 */
add_filter( 'woocommerce_package_rates', 'dev_inject_custom_shipping_rate', 10, 2 );

function dev_inject_custom_shipping_rate( $rates, $package ) {
    // Kiểm tra xem phương thức vận chuyển tùy chỉnh của chúng ta đã được tính toán trong rates chưa
    // Nếu chưa có (ví dụ: do chưa cấu hình Shipping Zone), chúng ta sẽ tự động chèn trực tiếp vào danh sách rates hiển thị.
    
    $has_our_shipping = false;
    foreach ( $rates as $rate_id => $rate ) {
        if ( strpos( $rate_id, 'dev_custom_shipping' ) !== false ) {
            $has_our_shipping = true;
            break;
        }
    }

    // Nếu chưa có, chúng ta tự tính toán và inject trực tiếp
    if ( ! $has_our_shipping ) {
        $cart_total = isset( $package['contents_cost'] ) ? $package['contents_cost'] : 0;
        
        $cost = 30000;
        $label = 'Giao Hàng Tiêu Chuẩn';

        if ( $cart_total >= 500000 ) {
            $cost = 0;
            $label .= ' (Miễn phí vận chuyển)';
        } else {
            $label .= ' (Phí ship: 30.000đ)';
        }

        // Tạo đối tượng rate vận chuyển mới
        $rate_id = 'dev_custom_shipping:0'; // 0 đại diện cho instance ID mặc định
        
        // WooCommerce yêu cầu một đối tượng WC_Shipping_Rate
        $custom_rate = new WC_Shipping_Rate(
            $rate_id,
            $label,
            $cost,
            array(), // Thuế suất (không tính thuế)
            'dev_custom_shipping'
        );

        $rates[ $rate_id ] = $custom_rate;
    }

    return $rates;
}
