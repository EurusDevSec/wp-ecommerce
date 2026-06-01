<?php
/**
 * Tích hợp cổng thanh toán VietQR động cho phương thức BACS (Chuyển khoản mặc định của WooCommerce)
 * Chú thích tiếng Việt dễ hiểu. Nội dung chuyển khoản định dạng HKTFASHION<ID> theo AC-BE-06.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Hiển thị mã QR VietQR động tại trang Đơn hàng đã nhận (Thank You Page)
 * Sử dụng hook cụ thể cho cổng thanh toán BACS (woocommerce_thankyou_bacs)
 */
add_action( 'woocommerce_thankyou_bacs', 'dev_add_vietqr_to_bacs_thankyou', 5 );

function dev_add_vietqr_to_bacs_thankyou( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Cấu hình tài khoản Vietcombank (VCB) thụ hưởng của HKT FASHION
    $bank_id      = 'VCB'; // Vietcombank
    $account_no   = '0011004123456'; // Số tài khoản mẫu
    $account_name = 'CONG TY HKT FASHION'; // Tên chủ tài khoản (Không dấu)

    $amount = (int) $order->get_total();
    
    // Nội dung chuyển khoản theo chuẩn AC-BE-06: HKTFASHION<ID>
    $memo = 'HKTFASHION' . $order->get_id();

    // Sinh link ảnh QR động từ API VietQR
    $qr_url = sprintf(
        'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
        esc_attr( $bank_id ),
        esc_attr( $account_no ),
        $amount,
        rawurlencode( $memo ),
        rawurlencode( $account_name )
    );
    ?>
    <div style="background-color: #f7fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 8px; margin: 25px 0; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <h3 style="color: #1e3a8a; margin-top: 0; margin-bottom: 10px; font-weight: 700;">⚡ Thanh toán quét mã QR Vietcombank</h3>
        <p style="color: #4a5568; font-size: 14px; margin-bottom: 15px;">Quét mã QR dưới đây bằng ứng dụng ngân hàng của bạn để thanh toán tự động.</p>
        
        <div style="background: #ffffff; display: inline-block; padding: 15px; border-radius: 12px; border: 1px solid #edf2f7; box-shadow: 0 2px 4px rgba(0,0,0,0.04); margin-bottom: 15px;">
            <img src="<?php echo esc_url( $qr_url ); ?>" alt="VietQR Vietcombank" style="max-width: 240px; width: 100%; height: auto; display: block; margin: 0 auto;">
        </div>
        
        <div style="max-width: 320px; margin: 0 auto; text-align: left; font-size: 13px; color: #2d3748; line-height: 1.6; border-top: 1px dashed #e2e8f0; padding-top: 12px;">
            <div>🏦 <strong>Ngân hàng:</strong> Vietcombank (VCB)</div>
            <div>💳 <strong>Số tài khoản:</strong> <span style="font-family: monospace; font-weight: bold; font-size: 14px;"><?php echo esc_html( $account_no ); ?></span></div>
            <div>👤 <strong>Chủ tài khoản:</strong> <?php echo esc_html( $account_name ); ?></div>
            <div>💰 <strong>Số tiền:</strong> <span style="color: #e53e3e; font-weight: bold;"><?php echo $order->get_formatted_order_total(); ?></span></div>
            <div>📝 <strong>Nội dung chuyển khoản:</strong> <span style="color: #1e3a8a; font-family: monospace; font-weight: bold; font-size: 14px;"><?php echo esc_html( $memo ); ?></span></div>
        </div>
        
        <div style="color: #dd6b20; font-size: 12px; font-weight: 600; margin-top: 10px;">
            * Vui lòng giữ nguyên nội dung chuyển khoản để hệ thống đối soát đơn hàng chính xác!
        </div>
    </div>
    <?php
}

/**
 * 2. Nhúng mã QR VietQR động vào Email thông báo đơn hàng gửi cho khách hàng
 * Sử dụng hook woocommerce_email_before_order_table
 */
add_action( 'woocommerce_email_before_order_table', 'dev_add_vietqr_to_bacs_email', 10, 4 );

function dev_add_vietqr_to_bacs_email( $order, $sent_to_admin, $plain_text, $email ) {
    // Chỉ chèn vào email định dạng HTML gửi cho khách hàng (không chèn cho admin hoặc email text thô)
    if ( $plain_text || $sent_to_admin ) {
        return;
    }

    // Chỉ áp dụng đối với phương thức thanh toán Chuyển khoản ngân hàng mặc định (bacs)
    if ( $order->get_payment_method() !== 'bacs' ) {
        return;
    }

    // Cấu hình tài khoản Vietcombank (VCB) thụ hưởng của HKT FASHION
    $bank_id      = 'VCB';
    $account_no   = '0011004123456';
    $account_name = 'CONG TY HKT FASHION';

    $amount = (int) $order->get_total();
    
    // Nội dung chuyển khoản theo chuẩn AC-BE-06: HKTFASHION<ID>
    $memo = 'HKTFASHION' . $order->get_id();

    // Sinh link ảnh QR động từ API VietQR
    $qr_url = sprintf(
        'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
        esc_attr( $bank_id ),
        esc_attr( $account_no ),
        $amount,
        rawurlencode( $memo ),
        rawurlencode( $account_name )
    );
    ?>
    <div style="background-color: #f7fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-family: sans-serif;">
        <h3 style="color: #1e3a8a; margin-top: 0; margin-bottom: 8px; font-size: 16px;">⚡ Hướng dẫn thanh toán quét mã VietQR</h3>
        <p style="color: #4a5568; font-size: 13px; margin-bottom: 12px;">Bạn có thể mở ứng dụng ngân hàng quét mã dưới đây để chuyển khoản nhanh chóng:</p>
        
        <div style="background: #ffffff; display: inline-block; padding: 10px; border-radius: 8px; border: 1px solid #edf2f7; margin-bottom: 12px;">
            <img src="<?php echo esc_url( $qr_url ); ?>" alt="VietQR Vietcombank" style="max-width: 200px; width: 100%; height: auto; display: block; margin: 0 auto;">
        </div>
        
        <div style="max-width: 280px; margin: 0 auto; text-align: left; font-size: 12px; color: #2d3748; line-height: 1.5; border-top: 1px dashed #e2e8f0; padding-top: 10px;">
            <div>🏦 <strong>Ngân hàng:</strong> Vietcombank (VCB)</div>
            <div>💳 <strong>Số tài khoản:</strong> <span style="font-family: monospace; font-weight: bold;"><?php echo esc_html( $account_no ); ?></span></div>
            <div>👤 <strong>Chủ tài khoản:</strong> <?php echo esc_html( $account_name ); ?></div>
            <div>💰 <strong>Số tiền:</strong> <span style="color: #e53e3e; font-weight: bold;"><?php echo strip_tags( $order->get_formatted_order_total() ); ?></span></div>
            <div>📝 <strong>Nội dung CK:</strong> <span style="color: #1e3a8a; font-family: monospace; font-weight: bold;"><?php echo esc_html( $memo ); ?></span></div>
        </div>
    </div>
    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;" />
    <?php
}
