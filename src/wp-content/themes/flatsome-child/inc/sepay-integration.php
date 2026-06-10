<?php
/**
 * Tích hợp SePay Webhook tự động đối soát và cập nhật đơn hàng WooCommerce
 * Hỗ trợ API REST endpoint nhận webhook từ SePay và API kiểm tra trạng thái đơn từ client.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class HKT_SePay_Integration {

    public function __construct() {
        // Đăng ký REST API Route
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Đăng ký REST API endpoints
     */
    public function register_routes() {
        // Webhook nhận thông báo giao dịch chuyển khoản từ SePay
        register_rest_route( 'sepay/v1', '/webhook', array(
            'methods'             => WP_REST_Server::CREATABLE, // POST
            'callback'            => array( $this, 'handle_webhook' ),
            'permission_callback' => '__return_true', // Kiểm tra xác thực token bên trong callback để trả về lỗi chi tiết hơn
        ) );

        // API kiểm tra trạng thái đơn hàng (cho frontend polling)
        register_rest_route( 'hkt/v1', '/order-status', array(
            'methods'             => WP_REST_Server::READABLE, // GET
            'callback'            => array( $this, 'get_order_status' ),
            'permission_callback' => '__return_true', // Đã bảo mật bằng order_key của WooCommerce
        ) );
    }

    /**
     * Nhận và xử lý Webhook từ SePay
     */
    public function handle_webhook( WP_REST_Request $request ) {
        $body = $request->get_json_params();

        // 1. Kiểm tra xác thực Token từ Headers
        $auth_header = $request->get_header( 'Authorization' );
        $x_api_key   = $request->get_header( 'X-API-Key' );
        
        $token = '';
        if ( ! empty( $auth_header ) && preg_match( '/Bearer\s+(.*)$/i', $auth_header, $matches ) ) {
            $token = trim( $matches[1] );
        } elseif ( ! empty( $x_api_key ) ) {
            $token = trim( $x_api_key );
        }

        // Lấy cấu hình Token từ database (Lưu ở cấu hình cổng dev_qr)
        $qr_settings = get_option( 'woocommerce_dev_qr_settings', array() );
        $configured_token = isset( $qr_settings['sepay_webhook_key'] ) ? trim( $qr_settings['sepay_webhook_key'] ) : '';

        // Dự phòng mặc định: HKTFASHION_SEPAY_KEY_2026 cho phép test nhanh
        $fallback_token = 'HKTFASHION_SEPAY_KEY_2026';

        if ( empty( $token ) ) {
            return new WP_Error( 'sepay_unauthorized', 'Missing Webhook API Token', array( 'status' => 401 ) );
        }

        $valid_token = ! empty( $configured_token ) ? $configured_token : $fallback_token;
        if ( $token !== $valid_token ) {
            return new WP_Error( 'sepay_forbidden', 'Invalid Webhook API Token', array( 'status' => 403 ) );
        }

        // 2. Kiểm tra dữ liệu chuyển khoản nhận vào
        if ( empty( $body ) || ! is_array( $body ) ) {
            return new WP_Error( 'sepay_bad_request', 'Invalid JSON payload', array( 'status' => 400 ) );
        }

        // Chỉ xử lý giao dịch nạp tiền vào (transferType = "in")
        $transfer_type = isset( $body['transferType'] ) ? strtolower( $body['transferType'] ) : '';
        if ( 'in' !== $transfer_type ) {
            return array(
                'success' => true,
                'message' => 'Ignored transferType: ' . $transfer_type,
            );
        }

        $content        = isset( $body['content'] ) ? trim( $body['content'] ) : '';
        $transfer_amount = isset( $body['transferAmount'] ) ? floatval( $body['transferAmount'] ) : 0;
        $transaction_code = isset( $body['code'] ) ? trim( $body['code'] ) : '';
        $gateway        = isset( $body['gateway'] ) ? trim( $body['gateway'] ) : 'N/A';

        if ( empty( $content ) || $transfer_amount <= 0 ) {
            return new WP_Error( 'sepay_bad_request', 'Content or transferAmount is missing/invalid', array( 'status' => 400 ) );
        }

        // 3. Khớp mã đơn hàng từ nội dung chuyển khoản
        $order_id = 0;
        // Đầu tiên kiểm tra theo định dạng chuẩn: HKTFASHION<OrderID>
        if ( preg_match( '/HKTFASHION(\d+)/i', $content, $matches ) ) {
            $order_id = intval( $matches[1] );
        } else {
            // Dự phòng: Tìm bất cứ dãy số nào xuất hiện trong nội dung chuyển khoản
            if ( preg_match( '/(\d+)/', $content, $matches ) ) {
                $order_id = intval( $matches[1] );
            }
        }

        if ( ! $order_id ) {
            return new WP_Error( 'sepay_order_not_found', 'Cannot parse Order ID from content: ' . $content, array( 'status' => 404 ) );
        }

        // Tải đơn hàng qua WooCommerce CRUD API
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return new WP_Error( 'sepay_order_not_found', 'Order #' . $order_id . ' does not exist in database', array( 'status' => 404 ) );
        }

        // 4. Kiểm tra trạng thái đơn hàng (chỉ duyệt đơn khi đang ở trạng thái chờ thanh toán)
        $allowed_statuses = array( 'on-hold', 'pending' );
        if ( ! in_array( $order->get_status(), $allowed_statuses, true ) ) {
            return array(
                'success' => true,
                'message' => 'Order #' . $order_id . ' already processed. Current status: ' . $order->get_status(),
            );
        }

        // 5. Đối soát số tiền (để chống fake bill hoặc chuyển thiếu tiền)
        $order_total = floatval( $order->get_total() );
        
        // Chấp nhận sai số làm tròn trong khoảng 1000 VNĐ để tránh lỗi do dấu phẩy thập phân
        if ( abs( $transfer_amount - $order_total ) > 1000 && $transfer_amount < $order_total ) {
            $order->add_order_note( sprintf(
                '⚠️ Cảnh báo SePay: Khách hàng chuyển khoản thiếu tiền đơn hàng! Số tiền chuyển: %s, Tổng đơn: %s. Mã GD: %s',
                wc_price( $transfer_amount ),
                wc_price( $order_total ),
                $transaction_code
            ) );
            return new WP_Error( 'sepay_amount_mismatch', 'Amount transfer is less than order total', array( 'status' => 400 ) );
        }

        // 6. Cập nhật trạng thái đơn hàng thành công
        $order->update_status( 
            'processing', 
            sprintf( 'Thanh toán tự động thành công qua SePay. Ngân hàng: %s, Mã GD: %s, Số tiền: %s.', $gateway, $transaction_code, wc_price( $transfer_amount ) ) 
        );

        return array(
            'success' => true,
            'message' => 'Order #' . $order_id . ' successfully paid and updated to processing',
        );
    }

    /**
     * REST API kiểm tra trạng thái đơn hàng cho Client (AJAX Polling)
     */
    public function get_order_status( WP_REST_Request $request ) {
        $order_id  = $request->get_param( 'order_id' );
        $order_key = $request->get_param( 'order_key' );

        if ( empty( $order_id ) || empty( $order_key ) ) {
            return new WP_Error( 'hkt_missing_params', 'Missing order_id or order_key parameters', array( 'status' => 400 ) );
        }

        $order = wc_get_order( intval( $order_id ) );
        if ( ! $order ) {
            return new WP_Error( 'hkt_order_not_found', 'Order not found', array( 'status' => 404 ) );
        }

        // Xác thực bảo mật bằng order_key tránh lộ thông tin đơn hàng của khách hàng khác
        if ( $order->get_order_key() !== $order_key ) {
            return new WP_Error( 'hkt_forbidden', 'Invalid order key', array( 'status' => 403 ) );
        }

        return array(
            'success'      => true,
            'order_id'     => $order->get_id(),
            'order_status' => $order->get_status(),
        );
    }
}

// Khởi tạo Class
new HKT_SePay_Integration();
