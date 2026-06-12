<?php
/**
 * Tích hợp Cổng thanh toán Chuyển khoản Ngân hàng & Quét mã QR cho WooCommerce
 * Chú thích tiếng Việt dễ hiểu, code tiêu chuẩn WooCommerce dễ mở rộng.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Đăng ký hai cổng thanh toán mới vào danh sách WooCommerce
 */
add_filter( 'woocommerce_payment_gateways', 'dev_register_custom_gateways' );

function dev_register_custom_gateways( $gateways ) {
    $gateways[] = 'WC_Gateway_Custom_Banking';
    $gateways[] = 'WC_Gateway_Custom_QR';
    return $gateways;
}

/**
 * 2. Khởi tạo các class Cổng thanh toán
 */
add_action( 'init', 'dev_init_custom_gateways' );

function dev_init_custom_gateways() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    // ----------------------------------------------------
    // CỔNG 1: CHUYỂN KHOẢN NGÂN HÀNG (BANKING)
    // ----------------------------------------------------
    class WC_Gateway_Custom_Banking extends WC_Payment_Gateway {
        public $instructions;

        public function __construct() {
            $this->id                 = 'dev_banking';
            $this->icon               = ''; // Có thể thêm link ảnh icon nếu muốn
            $this->has_fields         = false;
            $this->method_title       = 'Banking (Chuyển khoản)';
            $this->method_description = 'Cho phép khách hàng thanh toán qua chuyển khoản ngân hàng thủ công.';

            // Tải cấu hình cài đặt của Admin
            $this->init_form_fields();
            $this->init_settings();

            // Định nghĩa các biến cấu hình hiển thị
            $this->title        = $this->get_option( 'title', 'Chuyển khoản Ngân hàng (Banking)' );
            $this->description  = $this->get_option( 'description', 'Chuyển tiền trực tiếp vào tài khoản ngân hàng của chúng tôi.' );
            $this->instructions = $this->get_option( 'instructions', 'Vui lòng chuyển khoản đúng số tiền đơn hàng với nội dung là Mã đơn hàng.' );

            // Lưu cài đặt của Admin khi cấu hình trong trang quản trị
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            
            // Hiển thị thông tin chuyển khoản trên trang cảm ơn (Thank You page) và Email
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page_instructions' ) );
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

            // Tự động kích hoạt cổng thanh toán này nếu chưa được thiết lập trước đó
            if ( 'yes' !== $this->get_option( 'enabled' ) && ! get_option( 'woocommerce_dev_banking_settings' ) ) {
                $this->update_option( 'enabled', 'yes' );
            }
        }

        // Tạo form cấu hình trong trang wp-admin
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Kích hoạt/Tắt',
                    'type'    => 'checkbox',
                    'label'   => 'Kích hoạt cổng thanh toán Chuyển khoản',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'       => 'Tiêu đề hiển thị',
                    'type'        => 'text',
                    'description' => 'Khách hàng sẽ nhìn thấy tiêu đề này khi thanh toán.',
                    'default'     => 'Chuyển khoản Ngân hàng (Banking)',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Mô tả',
                    'type'        => 'textarea',
                    'description' => 'Mô tả chi tiết phương thức thanh toán.',
                    'default'     => 'Chuyển tiền trực tiếp vào tài khoản ngân hàng của chúng tôi.',
                ),
                'bank_name' => array(
                    'title'       => 'Tên Ngân hàng',
                    'type'        => 'text',
                    'default'     => 'MB Bank (Ngân hàng Quân Đội)',
                ),
                'bank_account' => array(
                    'title'       => 'Số tài khoản',
                    'type'        => 'text',
                    'default'     => '999999999999',
                ),
                'bank_owner' => array(
                    'title'       => 'Chủ tài khoản',
                    'type'        => 'text',
                    'default'     => 'NGUYEN VAN A',
                ),
                'instructions' => array(
                    'title'       => 'Hướng dẫn thanh toán',
                    'type'        => 'textarea',
                    'description' => 'Hiển thị trên trang Cảm ơn và Email đơn hàng.',
                    'default'     => 'Quý khách vui lòng chuyển khoản theo thông tin ngân hàng bên dưới. Sử dụng Mã đơn hàng làm nội dung chuyển khoản.',
                ),
            );
        }

        // Hiển thị giao diện hướng dẫn thanh toán tại trang Checkout khi chọn cổng này
        public function payment_fields() {
            if ( $this->description ) {
                echo '<p>' . esc_html( $this->description ) . '</p>';
            }
            ?>
            <div style="background-color: #f7f9fa; border: 1px dashed #ccc; padding: 15px; border-radius: 6px; margin-top: 10px;">
                <h5 style="margin-top: 0; color: #333;">Thông tin chuyển khoản nhanh:</h5>
                <ul style="list-style: none; padding-left: 0; margin-bottom: 0; font-size: 14px; line-height: 1.6;">
                    <li>🏦 <strong>Ngân hàng:</strong> <?php echo esc_html( $this->get_option( 'bank_name' ) ); ?></li>
                    <li>💳 <strong>Số tài khoản:</strong> <span style="font-family: monospace; font-size: 15px; font-weight: bold; color: #111;"><?php echo esc_html( $this->get_option( 'bank_account' ) ); ?></span></li>
                    <li>👤 <strong>Chủ tài khoản:</strong> <?php echo esc_html( $this->get_option( 'bank_owner' ) ); ?></li>
                </ul>
            </div>
            <?php
        }

        // Xử lý đơn hàng khi khách hàng nhấn nút "Đặt hàng"
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            // Chuyển trạng thái đơn hàng sang "Đang chờ thanh toán" (On Hold) để admin kiểm tra tiền vào
            $order->update_status( 'on-hold', 'Khách hàng đặt hàng qua Chuyển khoản ngân hàng. Đang chờ chuyển khoản.' );

            // Giảm số lượng tồn kho sản phẩm
            wc_reduce_stock_levels( $order_id );

            // Xóa sạch giỏ hàng của khách
            WC()->cart->empty_cart();

            // Chuyển hướng đến trang nhận đơn hàng (Thank You Page)
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

        // Hiển thị thông tin chuyển khoản tại trang nhận đơn hàng (Thank You Page)
        public function thankyou_page_instructions( $order_id ) {
            $order = wc_get_order( $order_id );
            ?>
            <section style="margin: 25px 0; padding: 20px; background-color: #fcfcfc; border: 1px solid #eaeaea; border-radius: 8px;">
                <h3 style="color: #4caf50; margin-top: 0;">Thông tin chuyển khoản đơn hàng của bạn</h3>
                <p><?php echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) ); ?></p>
                <table class="woocommerce-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; font-weight: 600;">Số tiền cần chuyển:</td>
                        <td style="padding: 10px 0; font-weight: bold; color: #d32f2f; font-size: 18px;"><?php echo $order->get_formatted_order_total(); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; font-weight: 600;">Nội dung chuyển khoản:</td>
                        <td style="padding: 10px 0; font-family: monospace; font-size: 16px; font-weight: bold; color: #1e3a8a;"><?php echo $order->get_order_number(); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; font-weight: 600;">Tên ngân hàng:</td>
                        <td style="padding: 10px 0;"><?php echo esc_html( $this->get_option( 'bank_name' ) ); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 0; font-weight: 600;">Số tài khoản:</td>
                        <td style="padding: 10px 0; font-weight: bold; font-family: monospace;"><?php echo esc_html( $this->get_option( 'bank_account' ) ); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: 600;">Chủ tài khoản:</td>
                        <td style="padding: 10px 0; text-transform: uppercase;"><?php echo esc_html( $this->get_option( 'bank_owner' ) ); ?></td>
                    </tr>
                </table>
            </section>
            <?php
        }

        // Hiển thị thông tin chuyển khoản trong Email gửi khách hàng
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
            if ( $plain_text || $order->get_payment_method() !== $this->id ) {
                return;
            }
            ?>
            <h2>Thông tin chuyển khoản đơn hàng #<?php echo esc_html( $order->get_order_number() ); ?></h2>
            <p><?php echo esc_html( $this->instructions ); ?></p>
            <ul>
                <li><strong>Số tiền:</strong> <?php echo strip_tags( $order->get_formatted_order_total() ); ?></li>
                <li><strong>Nội dung chuyển khoản:</strong> <?php echo esc_html( $order->get_order_number() ); ?></li>
                <li><strong>Ngân hàng:</strong> <?php echo esc_html( $this->get_option( 'bank_name' ) ); ?></li>
                <li><strong>Số tài khoản:</strong> <?php echo esc_html( $this->get_option( 'bank_account' ) ); ?></li>
                <li><strong>Chủ tài khoản:</strong> <?php echo esc_html( $this->get_option( 'bank_owner' ) ); ?></li>
            </ul>
            <hr/>
            <?php
        }
    }


    // ----------------------------------------------------
    // CỔNG 2: THANH TOÁN QUÉT MÃ QR (VIETQR)
    // ----------------------------------------------------
    class WC_Gateway_Custom_QR extends WC_Payment_Gateway {
        public $instructions;

        public function __construct() {
            $this->id                 = 'dev_qr';
            $this->icon               = ''; 
            $this->has_fields         = false;
            $this->method_title       = 'Quét mã QR Code (VietQR)';
            $this->method_description = 'Tạo mã QR VietQR tự động điền sẵn số tiền và nội dung đơn hàng.';

            $this->init_form_fields();
            $this->init_settings();

            $this->title        = $this->get_option( 'title', 'Quét mã QR Code' );
            $this->description  = $this->get_option( 'description', 'Mở ứng dụng ngân hàng quét mã QR để thanh toán nhanh chóng.' );
            $this->instructions = $this->get_option( 'instructions', 'Quét mã QR hiển thị bên dưới bằng ứng dụng ngân hàng của bạn.' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page_instructions' ) );

            // Tự động kích hoạt cổng thanh toán này nếu chưa được thiết lập trước đó
            if ( 'yes' !== $this->get_option( 'enabled' ) && ! get_option( 'woocommerce_dev_qr_settings' ) ) {
                $this->update_option( 'enabled', 'yes' );
            }
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Kích hoạt/Tắt',
                    'type'    => 'checkbox',
                    'label'   => 'Kích hoạt cổng quét mã QR',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'       => 'Tiêu đề hiển thị',
                    'type'        => 'text',
                    'default'     => 'Quét mã QR Code',
                ),
                'description' => array(
                    'title'       => 'Mô tả',
                    'type'        => 'textarea',
                    'default'     => 'Mở ứng dụng ngân hàng quét mã QR để thanh toán nhanh chóng và chính xác.',
                ),
                'bank_id' => array(
                    'title'       => 'Mã định danh ngân hàng (Bank ID)',
                    'type'        => 'text',
                    'description' => 'Mã ngân hàng (ví dụ: MB, VCB, TCB, ACB, VPB...). Tra cứu mã tại VietQR.',
                    'default'     => 'VCB',
                ),
                'bank_account' => array(
                    'title'       => 'Số tài khoản thụ hưởng',
                    'type'        => 'text',
                    'default'     => '012345678999',
                ),
                'bank_owner' => array(
                    'title'       => 'Chủ tài khoản (Không dấu)',
                    'type'        => 'text',
                    'default'     => 'CONG TY CO PHAN HKT COMPANY',
                ),
                'instructions' => array(
                    'title'       => 'Hướng dẫn',
                    'type'        => 'textarea',
                    'default'     => 'Vui lòng mở ứng dụng ngân hàng và sử dụng chức năng quét mã QR để thanh toán.',
                ),
                'sepay_webhook_key' => array(
                    'title'       => 'SePay Webhook Token (API Key)',
                    'type'        => 'text',
                    'description' => 'Mã xác thực API Key / Webhook Token do SePay cấp để bảo mật đường truyền. (Mặc định dự phòng nếu để trống: HKTFASHION_SEPAY_KEY_2026)',
                    'default'     => 'HKTFASHION_SEPAY_KEY_2026',
                ),
            );
        }

        // Hiển thị khi khách chọn cổng thanh toán QR tại Checkout
        public function payment_fields() {
            if ( $this->description ) {
                echo '<p>' . esc_html( $this->description ) . '</p>';
            }
            
            // Lấy tổng số tiền tạm tính hiện tại của giỏ hàng để vẽ mã QR demo trước khi đặt hàng (nếu muốn)
            $cart_total = WC()->cart->get_total( 'edit' );
            $bank_id = $this->get_option( 'bank_id' );
            $bank_account = $this->get_option( 'bank_account' );
            $bank_owner = rawurlencode( $this->get_option( 'bank_owner' ) );
            
            // URL mã QR tạm thời cho giỏ hàng
            $qr_url = sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                esc_attr( $bank_id ),
                esc_attr( $bank_account ),
                $cart_total,
                'Thanh toan gio hang',
                $bank_owner
            );
            
            ?>
            <div style="background-color: #f7f9fa; border: 1px dashed #ccc; padding: 15px; border-radius: 6px; margin-top: 10px; text-align: center;">
                <p style="margin-top: 0; font-size: 13px; color: #666;">Sau khi bạn bấm đặt hàng, mã QR chính thức kèm mã đơn hàng sẽ được hiển thị để bạn thực hiện quét thanh toán.</p>
                <div style="background: white; display: inline-block; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin: 10px 0;">
                    <img src="<?php echo esc_url( $qr_url ); ?>" alt="QR Code" style="max-width: 180px; height: auto; display: block; margin: 0 auto; filter: blur(1px) grayscale(100%); opacity: 0.7;">
                    <span style="font-size: 11px; color: #ff5722; display: block; margin-top: 5px;">Mã QR chính thức sẽ hiển thị sau khi Đặt Hàng</span>
                </div>
            </div>
            <?php
        }

        // Xử lý đơn hàng
        public function process_payment( $order_id ) {
            $order = wc_get_order( $order_id );

            $order->update_status( 'on-hold', 'Khách hàng chọn thanh toán quét mã QR. Đang chờ chuyển khoản.' );

            wc_reduce_stock_levels( $order_id );

            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            );
        }

        // Giao diện hiển thị mã QR VietQR chính xác của đơn hàng tại trang Thank You
        public function thankyou_page_instructions( $order_id ) {
            $order = wc_get_order( $order_id );
            
            $bank_id = $this->get_option( 'bank_id' );
            $bank_account = $this->get_option( 'bank_account' );
            $bank_owner = rawurlencode( $this->get_option( 'bank_owner' ) );
            
            // Lấy tổng tiền đơn hàng (chỉ lấy số để truyền vào API)
            $amount = (int) $order->get_total();
            
            // Nội dung chuyển khoản là mã số đơn hàng
            $add_info = rawurlencode( $order->get_order_number() );

            // Gọi API tạo mã QR động chuyên nghiệp của VietQR
            $qr_api_url = sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                esc_attr( $bank_id ),
                esc_attr( $bank_account ),
                $amount,
                $add_info,
                $bank_owner
            );
            ?>
            <section style="margin: 25px 0; padding: 25px; background-color: #ffffff; border: 1px solid #eaeaea; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
                <h3 style="color: #1e3a8a; margin-top: 0;">Quét mã QR để thanh toán đơn hàng</h3>
                <p style="color: #666; font-size: 14px;"><?php echo esc_html( $this->instructions ); ?></p>
                
                <div style="background: #ffffff; display: inline-block; padding: 15px; border-radius: 12px; border: 1px solid #e0e0e0; margin: 15px 0; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    <!-- Ảnh QR Code động hiển thị -->
                    <img src="<?php echo esc_url( $qr_api_url ); ?>" alt="Mã QR VietQR" style="max-width: 250px; width: 100%; height: auto; display: block; margin: 0 auto;">
                    
                    <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px; text-align: left; font-size: 13px;">
                        <div style="margin-bottom: 5px;">🏦 <strong>Ngân hàng:</strong> <?php echo esc_html( $this->get_option( 'bank_id' ) ); ?></div>
                        <div style="margin-bottom: 5px;">💳 <strong>Số tài khoản:</strong> <span style="font-family: monospace; font-weight: bold;"><?php echo esc_html( $this->get_option( 'bank_account' ) ); ?></span></div>
                        <div style="margin-bottom: 5px;">👤 <strong>Chủ tài khoản:</strong> <?php echo esc_html( $this->get_option( 'bank_owner' ) ); ?></div>
                        <div style="margin-bottom: 5px;">💰 <strong>Số tiền:</strong> <span style="color: #d32f2f; font-weight: bold; font-size: 15px;"><?php echo $order->get_formatted_order_total(); ?></span></div>
                        <div>📝 <strong>Nội dung CK:</strong> <span style="color: #1e3a8a; font-family: monospace; font-weight: bold; font-size: 15px;"><?php echo esc_html( $order->get_order_number() ); ?></span></div>
                    </div>
                </div>

                <div style="color: #ff9800; font-size: 13px; font-weight: 500; margin-top: 5px;">
                    ⚠️ Quý khách lưu ý giữ nguyên nội dung chuyển khoản là mã đơn hàng để hệ thống tự động xác nhận nhanh nhất!
                </div>
            </section>
            <?php
        }
    }
}
