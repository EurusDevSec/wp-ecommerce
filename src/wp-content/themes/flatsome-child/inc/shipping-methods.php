<?php
/**
 * Phương thức Vận chuyển tùy chỉnh cho WooCommerce
 * Tính phí ship dựa trên khu vực tỉnh thành người nhận:
 * - Nội thành TP. HCM: 20k
 * - Khu vực Miền Nam: 30k
 * - Các tỉnh xa (Miền Trung/Bắc): 40k
 * - Đơn hàng từ 500k trở lên: Miễn phí vận chuyển
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 0. Hàm phụ trợ tính toán phí ship dựa trên địa chỉ của khách hàng
 */
function hkt_calculate_shipping_cost_and_label( $package, $base_title = 'Giao Hàng Tiêu Chuẩn' ) {
    $cart_total = isset( $package['contents_cost'] ) ? $package['contents_cost'] : 0;
    
    // Nếu đơn hàng từ 500,000 VND trở lên thì miễn phí ship (0đ)
    if ( $cart_total >= 500000 ) {
        return array(
            'cost'  => 0,
            'label' => $base_title . ' (Miễn phí vận chuyển)'
        );
    }

    $state = isset( $package['destination']['state'] ) ? trim( $package['destination']['state'] ) : '';
    
    $cost = 40000; // Mặc định các tỉnh xa (Miền Trung, Miền Bắc) là 40k
    $region_name = 'Toàn quốc';

    if ( ! empty( $state ) ) {
        // Hàm chuẩn hóa chuỗi viết thường không dấu để so sánh
        $normalized_state = preg_replace( '/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $state );
        $normalized_state = preg_replace( '/[èéẹẻẽêềếệểễ]/u', 'e', $normalized_state );
        $normalized_state = preg_replace( '/[ìíịỉĩ]/u', 'i', $normalized_state );
        $normalized_state = preg_replace( '/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $normalized_state );
        $normalized_state = preg_replace( '/[ùúụủũưừứựửữ]/u', 'u', $normalized_state );
        $normalized_state = preg_replace( '/[ỳýỵỷỹ]/u', 'y', $normalized_state );
        $normalized_state = preg_replace( '/[đ]/u', 'd', $normalized_state );
        $normalized_state = strtolower( trim( $normalized_state ) );

        // 1. Kiểm tra nếu là Hồ Chí Minh (Nội thành)
        if ( $state === '79' || strpos( $normalized_state, 'ho chi minh' ) !== false ) {
            $cost = 20000;
            $region_name = 'Nội thành TP. HCM';
        } else {
            // 2. Kiểm tra nếu là các tỉnh Miền Nam
            $southern_ids = array( '70', '72', '74', '75', '77', '80', '82', '83', '84', '86', '87', '89', '91', '92', '93', '94', '95', '96' );
            $southern_names = array(
                'binh phuoc', 'tay ninh', 'binh duong', 'dong nai', 'ba ria', 'vung tau', 
                'long an', 'tien giang', 'ben tre', 'tra vinh', 'vinh long', 'dong thap', 
                'an giang', 'can tho', 'hau giang', 'soc trang', 'bac lieu', 'kien giang', 'ca mau'
            );

            $is_southern = in_array( $state, $southern_ids, true );
            if ( ! $is_southern ) {
                foreach ( $southern_names as $name ) {
                    if ( strpos( $normalized_state, $name ) !== false ) {
                        $is_southern = true;
                        break;
                    }
                }
            }

            if ( $is_southern ) {
                $cost = 30000;
                $region_name = 'Khu vực Miền Nam';
            } else {
                $region_name = 'Các tỉnh xa';
            }
        }
    }

    return array(
        'cost'  => $cost,
        'label' => $base_title . ' (' . $region_name . ': ' . number_format( $cost, 0, ',', '.' ) . 'đ)'
    );
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
            $this->method_description = 'Phí giao hàng tính theo vùng (HCM: 20k, Miền Nam: 30k, Tỉnh xa: 40k). Freeship cho đơn từ 500k.';
            
            $this->init_form_fields();
            $this->init_settings();

            $this->title              = $this->get_option( 'title', 'Giao Hàng Tiêu Chuẩn' );
            $this->enabled            = $this->get_option( 'enabled', 'yes' );

            // Lưu cài đặt của Admin
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        // Khởi tạo các trường cài đặt trong Admin
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
            $result = hkt_calculate_shipping_cost_and_label( $package, $this->title );

            // Gửi thông tin phí ship ngược lại cho WooCommerce
            $rate = array(
                'id'       => $this->get_rate_id(),
                'label'    => $result['label'],
                'cost'     => $result['cost'],
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
    $has_our_shipping = false;
    foreach ( $rates as $rate_id => $rate ) {
        if ( strpos( $rate_id, 'dev_custom_shipping' ) !== false ) {
            $has_our_shipping = true;
            break;
        }
    }

    // Nếu chưa có, chúng ta tự tính toán và inject trực tiếp
    if ( ! $has_our_shipping ) {
        $result = hkt_calculate_shipping_cost_and_label( $package, 'Giao Hàng Tiêu Chuẩn' );

        // Tạo đối tượng rate vận chuyển mới
        $rate_id = 'dev_custom_shipping:0'; // 0 đại diện cho instance ID mặc định
        
        $custom_rate = new WC_Shipping_Rate(
            $rate_id,
            $result['label'],
            $result['cost'],
            array(), // Thuế suất
            'dev_custom_shipping'
        );

        $rates[ $rate_id ] = $custom_rate;
    }

    return $rates;
}
