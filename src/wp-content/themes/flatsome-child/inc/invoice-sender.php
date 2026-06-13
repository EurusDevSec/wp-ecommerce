<?php
/**
 * Module xử lý gửi Hóa đơn điện tử qua Email cho HKT Fashion
 * Hỗ trợ tự động gửi khi thanh toán thành công và cho phép người dùng click gửi thủ công.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HKT_Invoice_Sender {

    public function __construct() {
        // 1. Tự động gửi hóa đơn khi đơn hàng chuyển sang trạng thái Processing hoặc Completed
        add_action( 'woocommerce_order_status_processing', array( $this, 'auto_send_invoice' ), 20, 1 );
        add_action( 'woocommerce_order_status_completed', array( $this, 'auto_send_invoice' ), 20, 1 );

        // 2. Đăng ký AJAX endpoint gửi hóa đơn thủ công
        add_action( 'wp_ajax_hkt_ajax_send_invoice',        array( $this, 'ajax_send_invoice' ) );
        add_action( 'wp_ajax_nopriv_hkt_ajax_send_invoice', array( $this, 'ajax_send_invoice' ) );

        // 3. Hiển thị nút bấm gửi hóa đơn ở trang cảm ơn & trang tài khoản chi tiết đơn
        add_action( 'woocommerce_thankyou',   array( $this, 'render_invoice_button' ), 15, 1 );
        add_action( 'woocommerce_view_order', array( $this, 'render_invoice_button' ), 15, 1 );

        // 4. Enqueue file JS hỗ trợ AJAX
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Tự động gửi hóa đơn khi thanh toán thành công
     */
    public function auto_send_invoice( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Kiểm tra xem đơn hàng đã từng được gửi hóa đơn tự động chưa (tránh spam email nếu chuyển đổi trạng thái liên tục)
        $invoice_sent = $order->get_meta( '_hkt_invoice_sent' );
        if ( 'yes' === $invoice_sent ) {
            return;
        }

        // Thực hiện gửi hóa đơn
        $result = $this->send_invoice_email( $order );

        if ( $result ) {
            // Đánh dấu đã gửi
            $order->update_meta_data( '_hkt_invoice_sent', 'yes' );
            $order->add_order_note( __( '✓ Hóa đơn điện tử đã được tự động gửi thành công qua email của khách hàng.', 'woocommerce' ) );
            $order->save();
        } else {
            $order->add_order_note( __( '❌ Lỗi: Không thể gửi email hóa đơn tự động cho khách hàng.', 'woocommerce' ) );
        }
    }

    /**
     * Hàm lõi xử lý tạo nội dung và gửi email hóa đơn HTML
     */
    public function send_invoice_email( $order ) {
        $to = $order->get_billing_email();
        if ( empty( $to ) || ! is_email( $to ) ) {
            return false;
        }

        $order_id = $order->get_id();
        $subject  = sprintf( '[HKT Fashion] Hóa đơn điện tử đơn hàng #%s', $order_id );

        // Buffer HTML content từ template
        ob_start();
        $template_path = locate_template( 'inc/templates/email-invoice-html.php' );
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            ob_end_clean();
            return false;
        }
        $message = ob_get_clean();

        // Cài đặt Headers gửi email HTML
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        // Sử dụng hàm wp_mail của WordPress
        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Endpoint AJAX xử lý gửi hóa đơn thủ công khi click nút
     */
    public function ajax_send_invoice() {
        // 1. Kiểm tra bảo mật Nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'hkt_invoice_sender_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Lỗi bảo mật (Invalid Nonce).' ), 403 );
        }

        // 2. Nhận tham số
        $order_id  = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
        $order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';

        if ( ! $order_id || empty( $order_key ) ) {
            wp_send_json_error( array( 'message' => 'Tham số đơn hàng không hợp lệ.' ), 400 );
        }

        // 3. Truy xuất đơn hàng và xác thực key
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            wp_send_json_error( array( 'message' => 'Đơn hàng không tồn tại.' ), 444 );
        }

        if ( $order->get_order_key() !== $order_key ) {
            wp_send_json_error( array( 'message' => 'Bạn không có quyền truy cập đơn hàng này.' ), 403 );
        }

        // 4. Kiểm tra trạng thái đơn hàng (Chỉ cho phép xuất hóa đơn nếu đã thanh toán/processing/completed)
        $allowed_statuses = array( 'processing', 'completed' );
        if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
            wp_send_json_error( array( 'message' => 'Đơn hàng chưa được thanh toán. Không thể xuất hóa đơn.' ), 400 );
        }

        // 5. Gửi hóa đơn
        $success = $this->send_invoice_email( $order );

        if ( $success ) {
            $order->add_order_note( sprintf( __( 'Khách hàng yêu cầu gửi lại hóa đơn thủ công. Hóa đơn đã gửi thành công tới: %s', 'woocommerce' ), $order->get_billing_email() ) );
            $order->save();
            wp_send_json_success( array( 'message' => 'Hóa đơn điện tử đã được gửi thành công đến email: ' . $order->get_billing_email() ) );
        } else {
            wp_send_json_error( array( 'message' => 'Không thể gửi email hóa đơn. Vui lòng kiểm tra lại cấu hình email hệ thống.' ) );
        }
    }

    /**
     * Render nút gửi hóa đơn trên Frontend (Thank you và My account)
     */
    public function render_invoice_button( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Chỉ hiển thị nút nếu đơn hàng đã được thanh toán (processing hoặc completed)
        $allowed_statuses = array( 'processing', 'completed' );
        if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
            return;
        }

        $billing_email = $order->get_billing_email();
        if ( empty( $billing_email ) ) {
            return;
        }
        ?>
        <div class="hkt-invoice-actions">
            <p>Đơn hàng của bạn đã thanh toán thành công. Bạn muốn lưu trữ hóa đơn điện tử?</p>
            <button class="hkt-btn-send-invoice" 
                    data-order-id="<?php echo esc_attr( $order->get_id() ); ?>" 
                    data-order-key="<?php echo esc_attr( $order->get_order_key() ); ?>">
                <span class="hkt-spinner"></span>
                <span>📧 Gửi hóa đơn về Email</span>
            </button>
            <div class="hkt-invoice-message"></div>
        </div>
        <?php
    }

    /**
     * Enqueue script JS
     */
    public function enqueue_assets() {
        // Chỉ load script trên trang Thankyou (checkout received) hoặc trang chi tiết đơn hàng (view order)
        if ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'view-order' ) ) {
            wp_enqueue_script(
                'hkt-invoice-sender',
                get_stylesheet_directory_uri() . '/assets/js/invoice-sender.js',
                array( 'jquery' ),
                '1.0.0',
                true
            );

            wp_localize_script( 'hkt-invoice-sender', 'hktInvoiceData', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'hkt_invoice_sender_nonce' ),
            ) );
        }
    }
}

// Khởi tạo module
new HKT_Invoice_Sender();
