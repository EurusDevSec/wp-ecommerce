<?php
/**
 * HKT Store E-Commerce Settings Page
 * Custom general settings for HKT Fashion Store (Google Maps, SePay, Social Logins)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action( 'admin_menu', 'hkt_admin_settings_menu' );
function hkt_admin_settings_menu() {
    add_options_page(
        'HKT Store Settings',   // Page title
        'HKT Store Settings',   // Menu title
        'manage_options',       // Capability
        'hkt-settings',         // Menu slug
        'hkt_render_settings_page' // Callback function
    );
}

function hkt_render_settings_page() {
    // Xử lý lưu cài đặt
    if ( isset( $_POST['hkt_settings_nonce'] ) && wp_verify_nonce( $_POST['hkt_settings_nonce'], 'hkt_save_store_settings' ) ) {
        update_option( 'hkt_sepay_webhook_key', trim( sanitize_text_field( $_POST['hkt_sepay_webhook_key'] ) ) );
        update_option( 'hkt_google_client_id', trim( sanitize_text_field( $_POST['hkt_google_client_id'] ) ) );
        update_option( 'hkt_google_client_secret', trim( sanitize_text_field( $_POST['hkt_google_client_secret'] ) ) );
        update_option( 'hkt_facebook_app_id', trim( sanitize_text_field( $_POST['hkt_facebook_app_id'] ) ) );
        update_option( 'hkt_facebook_app_secret', trim( sanitize_text_field( $_POST['hkt_facebook_app_secret'] ) ) );
        
        echo '<div class="notice notice-success is-dismissible"><p><strong>Cấu hình HKT Store đã được lưu thành công!</strong></p></div>';
    }

    // Lấy giá trị hiện tại
    $sepay_webhook_key   = get_option( 'hkt_sepay_webhook_key', '' );
    $google_client_id    = get_option( 'hkt_google_client_id', '' );
    $google_client_secret= get_option( 'hkt_google_client_secret', '' );
    $facebook_app_id     = get_option( 'hkt_facebook_app_id', '' );
    $facebook_app_secret = get_option( 'hkt_facebook_app_secret', '' );

    // URL chuyển hướng redirect URI cho Social Login
    $google_redirect_uri = home_url( '/?social_callback=google' );
    $facebook_redirect_uri = home_url( '/?social_callback=facebook' );
    ?>
    <div class="wrap">
        <h1>⚙️ Cấu hình Hệ thống HKT Store Settings</h1>
        <p>Bảng điều khiển tập trung giúp quản trị viên cấu hình SePay Webhook và Đăng nhập mạng xã hội mà không cần can thiệp mã nguồn.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field( 'hkt_save_store_settings', 'hkt_settings_nonce' ); ?>

            <!-- SECTION 1: SEPAY INTEGRATION -->
            <div class="card" style="margin-top: 20px; max-width: 800px; padding: 20px; border-left: 4px solid #10B981; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #10B981; display: flex; align-items: center; gap: 8px;">
                    💳 Cổng thanh toán & Tự động đối soát (SePay Webhook)
                </h2>
                <p class="description">Token bảo mật dùng để xác thực webhook tự động cập nhật đơn hàng thành công từ dịch vụ đối soát ngân hàng SePay.</p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">SePay Webhook Token</th>
                        <td>
                            <input type="text" name="hkt_sepay_webhook_key" value="<?php echo esc_attr( $sepay_webhook_key ); ?>" class="large-text" placeholder="Ví dụ: HKTFASHION_SEPAY_KEY_2026..." />
                            <p class="description">Được cấu hình tương ứng trên App SePay của bạn. Nếu để trống sẽ sử dụng giá trị mặc định: <code>HKTFASHION_SEPAY_KEY_2026</code>.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- SECTION 2: SOCIAL LOGIN -->
            <div class="card" style="margin-top: 20px; max-width: 800px; padding: 20px; border-left: 4px solid #EA4335; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0; color: #EA4335; display: flex; align-items: center; gap: 8px;">
                    🔐 Đăng nhập mạng xã hội (Social Login Client Keys)
                </h2>
                <p class="description">Cấu hình API Key của Google và Facebook OAuth Client để bật tính năng đăng nhập nhanh không cần mật khẩu.</p>
                
                <h3 style="border-bottom: 1px solid #eee; padding-bottom: 8px; color: #4285F4; margin-top: 20px;">🔵 Đăng nhập bằng Google</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">Google Client ID</th>
                        <td>
                            <input type="text" name="hkt_google_client_id" value="<?php echo esc_attr( $google_client_id ); ?>" class="large-text" />
                            <p class="description">Authorized Redirect URI cần đăng ký tại Google Console: <code style="background: #f1f1f1; padding: 3px 6px; border-radius: 3px; font-family: monospace; font-size: 12px;"><?php echo esc_url( $google_redirect_uri ); ?></code></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Google Client Secret</th>
                        <td>
                            <input type="password" name="hkt_google_client_secret" value="<?php echo esc_attr( $google_client_secret ); ?>" class="large-text" />
                        </td>
                    </tr>
                </table>

                <h3 style="border-bottom: 1px solid #eee; padding-bottom: 8px; color: #1877F2; margin-top: 30px;">🔵 Đăng nhập bằng Facebook</h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row" style="width: 200px;">Facebook App ID</th>
                        <td>
                            <input type="text" name="hkt_facebook_app_id" value="<?php echo esc_attr( $facebook_app_id ); ?>" class="large-text" />
                            <p class="description">OAuth Redirect URI cần cấu hình tại Facebook Developers: <code style="background: #f1f1f1; padding: 3px 6px; border-radius: 3px; font-family: monospace; font-size: 12px;"><?php echo esc_url( $facebook_redirect_uri ); ?></code></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Facebook App Secret</th>
                        <td>
                            <input type="password" name="hkt_facebook_app_secret" value="<?php echo esc_attr( $facebook_app_secret ); ?>" class="large-text" />
                        </td>
                    </tr>
                </table>
            </div>

            <div style="margin-top: 20px;">
                <?php submit_button( 'Lưu cấu hình hệ thống', 'primary', 'submit_hkt_settings' ); ?>
            </div>
        </form>
    </div>
    <?php
}
