<?php
/**
 * Cấu hình SMTP gửi Email tin cậy cho WordPress
 * Chú thích tiếng Việt dễ hiểu. Kết nối trực tiếp vào PHPMailer lõi của WordPress.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hook vào tiến trình khởi tạo PHPMailer để chuyển cấu hình mặc định sang SMTP
 */
add_action( 'phpmailer_init', 'dev_configure_smtp_mail_delivery' );

function dev_configure_smtp_mail_delivery( $phpmailer ) {
    // Chỉ kích hoạt cấu hình SMTP nếu hằng số SMTP_HOST được định nghĩa trong wp-config.php
    if ( ! defined( 'SMTP_HOST' ) ) {
        return;
    }

    // Kích hoạt giao thức gửi thư qua SMTP
    $phpmailer->isSMTP();

    // Địa chỉ máy chủ SMTP (ví dụ: smtp.gmail.com hoặc smtp.sendgrid.net)
    $phpmailer->Host       = defined( 'SMTP_HOST' ) ? constant( 'SMTP_HOST' ) : '';

    // Bật xác thực SMTP (Bắt buộc đối với hầu hết dịch vụ mail hiện nay)
    $phpmailer->SMTPAuth   = true;

    // Cổng kết nối SMTP (thường là 587 cho TLS hoặc 465 cho SSL)
    $phpmailer->Port       = defined( 'SMTP_PORT' ) ? (int) constant( 'SMTP_PORT' ) : 587;

    // Tài khoản đăng nhập SMTP (Email gửi đi)
    $phpmailer->Username   = defined( 'SMTP_USER' ) ? constant( 'SMTP_USER' ) : '';

    // Mật khẩu ứng dụng (App Password) hoặc API Key tương ứng
    $phpmailer->Password   = defined( 'SMTP_PASS' ) ? constant( 'SMTP_PASS' ) : '';

    // Phương thức mã hóa bảo mật ('tls' hoặc 'ssl')
    $phpmailer->SMTPSecure = defined( 'SMTP_SECURE' ) ? constant( 'SMTP_SECURE' ) : 'tls';

    // Địa chỉ Email người gửi hiển thị đối với khách nhận (Thường khớp với Email SMTP)
    $phpmailer->From       = defined( 'SMTP_FROM' ) ? constant( 'SMTP_FROM' ) : $phpmailer->Username;

    // Tên hiển thị người gửi (ví dụ: HKT Fashion Shop)
    $phpmailer->FromName   = defined( 'SMTP_FROM_NAME' ) ? constant( 'SMTP_FROM_NAME' ) : get_bloginfo( 'name' );
}
