<?php
/**
 * Cron Job tự động hủy đơn hàng quá hạn & Hoàn kho sản phẩm
 * Chú thích tiếng Việt dễ hiểu. Đảm bảo tương thích HPOS và sử dụng CRUD API.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Đăng ký chu kỳ chạy (Schedule) mới: Mỗi 15 phút
 */
add_filter( 'cron_schedules', 'dev_add_15_minutes_cron_schedule' );

function dev_add_15_minutes_cron_schedule( $schedules ) {
    $schedules['every_15_minutes'] = array(
        'interval' => 15 * MINUTE_IN_SECONDS, // 15 phút = 900 giây
        'display'  => 'Mỗi 15 phút'
    );
    return $schedules;
}

/**
 * 2. Lập lịch cho sự kiện chạy ngầm tự động (WP-Cron)
 */
add_action( 'wp', 'dev_setup_expired_orders_cron' );

function dev_setup_expired_orders_cron() {
    // Nếu sự kiện chưa được lên lịch, tiến hành tạo mới lịch chạy mỗi 15 phút
    if ( ! wp_next_scheduled( 'dev_cancel_expired_orders_event' ) ) {
        wp_schedule_event( time(), 'every_15_minutes', 'dev_cancel_expired_orders_event' );
    }
}

/**
 * 3. Đăng ký Callback thực thi khi sự kiện ngầm được kích hoạt
 * Quét các đơn hàng thanh toán Chuyển khoản (dev_banking) hoặc QR (dev_qr) quá 30 phút chưa thanh toán
 */
add_action( 'dev_cancel_expired_orders_event', 'dev_cancel_expired_orders_callback' );

function dev_cancel_expired_orders_callback() {
    // Ngưỡng thời gian: Hiện tại trừ đi 30 phút
    $threshold_time = time() - 30 * MINUTE_IN_SECONDS;
    $date_limit     = date( 'Y-m-d H:i:s', $threshold_time );

    // Cấu hình tham số truy vấn đơn hàng (Sử dụng CRUD API tương thích HPOS)
    $args = array(
        'status'         => array( 'on-hold', 'pending' ), // Đơn đang chờ thanh toán hoặc tạm giữ hàng
        'payment_method' => array( 'dev_banking', 'dev_qr' ), // Chỉ quét 2 cổng thanh toán custom của chúng ta
        'date_created'   => '<' . $date_limit, // Được tạo trước ngưỡng 30 phút
        'limit'          => -1, // Lấy tất cả các đơn thỏa mãn
    );

    // Lấy danh sách đơn hàng thỏa mãn điều kiện
    $orders = wc_get_orders( $args );

    if ( ! empty( $orders ) ) {
        foreach ( $orders as $order ) {
            // Chuyển trạng thái đơn sang Bị hủy (cancelled)
            // Việc thay đổi trạng thái sang "cancelled" của WooCommerce sẽ tự động kích hoạt
            // hook hoàn trả lại số lượng tồn kho của các sản phẩm có trong đơn hàng.
            $order->update_status( 'cancelled', 'Đơn hàng bị hệ thống tự động hủy do quá hạn 30 phút chưa thanh toán chuyển khoản.' );
            
            // Ghi chú thêm log lịch sử đơn hàng để admin nắm được thông tin hoàn kho
            $order->add_order_note( 'Hệ thống đã tự động hoàn trả số lượng sản phẩm vào kho hàng.' );
        }
    }
}

/**
 * 4. TÍNH NĂNG TIỆN ÍCH CHO KIỂM THỬ (TESTING TOOL):
 * Hỗ trợ Quản trị viên (Admin) kích hoạt chạy thử thủ công tức thì qua URL
 * Truy cập link: http://localhost:8000/?trigger_cancel_cron=1
 */
add_action( 'init', 'dev_manual_trigger_cancel_cron' );

function dev_manual_trigger_cancel_cron() {
    if ( isset( $_GET['trigger_cancel_cron'] ) && current_user_can( 'manage_options' ) ) {
        // Gọi trực tiếp hàm xử lý
        dev_cancel_expired_orders_callback();
        
        // Xuất thông báo ra màn hình
        wp_die( 
            '<div style="text-align:center; margin-top:50px; font-family:sans-serif;">
                <h2 style="color:#2e7d32;">⚙️ Kích hoạt Cron Job thành công!</h2>
                <p>Hệ thống đã thực hiện quét và tự động Hủy + Hoàn kho các đơn hàng quá hạn 30 phút.</p>
                <p><a href="' . esc_url( admin_url( 'edit.php?post_type=shop_order' ) ) . '">Quay lại quản lý đơn hàng</a></p>
            </div>', 
            'Kết quả chạy thử Cron Job' 
        );
    }
}
