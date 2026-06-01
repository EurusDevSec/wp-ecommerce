<?php
/**
 * Chức năng Đăng nhập bằng Google và Facebook cho WordPress / WooCommerce
 * Chú thích tiếng Việt dễ hiểu, logic rõ ràng, hỗ trợ chế độ giả lập (Demo) khi chạy local.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Hiển thị các nút đăng nhập mạng xã hội trên form đăng nhập WooCommerce và WordPress
 */
add_action( 'woocommerce_login_form_end', 'dev_add_social_login_buttons' );
add_action( 'login_form', 'dev_add_social_login_buttons' );

function dev_add_social_login_buttons() {
    // Chỉ hiển thị trên trang đăng nhập
    ?>
    <div class="dev-social-login-container">
        <div class="dev-social-separator">
            <span>or</span>
        </div>
        
        <!-- Nút Đăng nhập bằng Google -->
        <a href="<?php echo esc_url( add_query_arg( 'social_login', 'google', home_url() ) ); ?>" class="dev-social-btn dev-btn-google">
            <svg class="dev-social-icon" viewBox="0 0 24 24" width="20" height="20">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v3.92h6.69c-.29 1.5-.1.14-1.14 2.83l3.3 2.56c1.93-1.78 3.03-4.4 3.03-7.24z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.3-2.56c-.92.62-2.1 1-3.63 1-3.11 0-5.74-2.11-6.68-4.96l-3.4 2.63C4.81 20.3 8.11 24 12 24z"/>
                <path fill="#FBBC05" d="M5.32 14.57c-.24-.7-.38-1.46-.38-2.25s.14-1.55.38-2.25L1.92 7.44C1.07 9.15.6 11.02.6 13s.47 3.85 1.32 5.56l3.4-2.63z"/>
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.93 1.19 15.24 0 12 0 8.11 0 4.81 3.7 2.92 7.44l3.4 2.63c.94-2.85 3.57-4.96 6.68-4.96z"/>
            </svg>
            <span>Sign in with Google</span>
        </a>

        <!-- Nút Đăng nhập bằng Facebook -->
        <a href="<?php echo esc_url( add_query_arg( 'social_login', 'facebook', home_url() ) ); ?>" class="dev-social-btn dev-btn-facebook">
            <svg class="dev-social-icon" viewBox="0 0 24 24" width="20" height="20" fill="#ffffff">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            <span>Sign in with Facebook</span>
        </a>
    </div>
    <?php
}

/**
 * 2. Nhúng CSS làm đẹp các nút mạng xã hội
 */
add_action( 'wp_head', 'dev_social_login_styles' );
add_action( 'login_enqueue_scripts', 'dev_social_login_styles' );

function dev_social_login_styles() {
    ?>
    <style>
        .dev-social-login-container {
            margin: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .dev-social-separator {
            display: flex;
            align-items: center;
            text-align: center;
            color: #777;
            font-size: 14px;
            margin: 10px 0;
        }
        .dev-social-separator::before,
        .dev-social-separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        .dev-social-separator:not(:empty)::before {
            margin-right: .5em;
        }
        .dev-social-separator:not(:empty)::after {
            margin-left: .5em;
        }
        .dev-social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none !important;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #ddd;
            width: 100%;
            box-sizing: border-box;
        }
        .dev-btn-google {
            background-color: #ffffff;
            color: #3c4043 !important;
        }
        .dev-btn-google:hover {
            background-color: #f8f9fa;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-color: #ccc;
        }
        .dev-btn-facebook {
            background-color: #1877F2;
            color: #ffffff !important;
            border-color: #1877F2;
        }
        .dev-btn-facebook:hover {
            background-color: #166fe5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .dev-social-icon {
            display: inline-block;
            vertical-align: middle;
        }
        /* Style cho trang wp-login.php mặc định của WP */
        #loginform .dev-social-login-container {
            padding-bottom: 10px;
        }
    </style>
    <?php
}

/**
 * 3. Bộ lọc điều hướng xử lý logic Đăng nhập (OAuth thực tế hoặc Giả lập)
 */
add_action( 'template_redirect', 'dev_handle_social_login' );
add_action( 'login_init', 'dev_handle_social_login' );

function dev_handle_social_login() {
    // 3.1. Nếu có yêu cầu chuyển sang trang đăng nhập giả lập (?social_login=...)
    if ( isset( $_GET['social_login'] ) ) {
        $provider = sanitize_text_field( $_GET['social_login'] );
        if ( in_array( $provider, array( 'google', 'facebook' ), true ) ) {
            
            // KIỂM TRA: Nếu đã cấu hình API key trong wp-config.php thì chạy OAuth thật
            if ( defined( 'GOOGLE_CLIENT_ID' ) && $provider === 'google' ) {
                dev_redirect_to_real_google_oauth();
                exit;
            }
            if ( defined( 'FACEBOOK_APP_ID' ) && $provider === 'facebook' ) {
                dev_redirect_to_real_facebook_oauth();
                exit;
            }

            // MẶC ĐỊNH: Chạy chế độ GIẢ LẬP (Demo) để test local không cần API keys
            dev_render_mock_consent_screen( $provider );
            exit;
        }
    }

    // 3.2. Nếu có phản hồi OAuth từ Google / Facebook thật (có tham số code và state)
    if ( isset( $_GET['code'] ) && isset( $_GET['social_callback'] ) && isset( $_GET['state'] ) ) {
        $provider = sanitize_text_field( $_GET['social_callback'] );
        $code     = sanitize_text_field( $_GET['code'] );
        $state    = sanitize_text_field( $_GET['state'] );

        if ( $provider === 'google' && defined( 'GOOGLE_CLIENT_ID' ) ) {
            // Kiểm tra tính hợp lệ của mã Nonce (State) để chống tấn công CSRF
            if ( ! wp_verify_nonce( $state, 'google_oauth_state' ) ) {
                wp_die( 'Yêu cầu không hợp lệ! Xác thực mã bảo mật (CSRF State) thất bại.', 'Lỗi Bảo Mật', array( 'back_link' => true ) );
            }
            dev_handle_real_google_callback( $code );
            exit;
        }
        if ( $provider === 'facebook' && defined( 'FACEBOOK_APP_ID' ) ) {
            // Kiểm tra tính hợp lệ của mã Nonce (State) để chống tấn công CSRF
            if ( ! wp_verify_nonce( $state, 'facebook_oauth_state' ) ) {
                wp_die( 'Yêu cầu không hợp lệ! Xác thực mã bảo mật (CSRF State) thất bại.', 'Lỗi Bảo Mật', array( 'back_link' => true ) );
            }
            dev_handle_real_facebook_callback( $code );
            exit;
        }
    }

    // 3.3. Nếu có yêu cầu phản hồi callback sau khi đăng nhập giả lập (?social_callback=...)
    if ( isset( $_GET['social_callback'] ) ) {
        $provider = sanitize_text_field( $_GET['social_callback'] );
        $email = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';
        $name = isset( $_GET['name'] ) ? sanitize_text_field( $_GET['name'] ) : 'Demo User';

        if ( ! empty( $email ) && is_email( $email ) ) {
            dev_process_user_login( $email, $name, $provider );
            exit;
        } else {
            wp_die( 'Email không hợp lệ! Vui lòng quay lại thử lại.', 'Lỗi đăng nhập', array( 'back_link' => true ) );
        }
    }
}

/**
 * 4. Xử lý đăng nhập / đăng ký tài khoản WordPress từ dữ liệu mạng xã hội thu nhận được
 */
function dev_process_user_login( $email, $name, $provider ) {
    // Tìm kiếm xem email này đã tồn tại trong hệ thống chưa
    $user = get_user_by( 'email', $email );

    if ( ! $user ) {
        // Tình huống 1: Người dùng chưa có tài khoản -> Tự động đăng ký mới
        
        // Tạo username dựa trên phần trước của email
        $email_parts = explode( '@', $email );
        $base_username = sanitize_user( $email_parts[0] );
        $username = $base_username;
        
        // Đảm bảo username không trùng lặp trong database
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        // Tạo mật khẩu ngẫu nhiên an toàn cho tài khoản mới
        $password = wp_generate_password( 12, true );

        // Đăng ký user vào WordPress
        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_die( 'Không thể tạo tài khoản người dùng: ' . esc_html( $user_id->get_error_message() ) );
        }

        $user = get_user_by( 'id', $user_id );
        
        // Phân quyền tài khoản này là Customer (khách hàng WooCommerce)
        $user->set_role( 'customer' );

        // Cập nhật họ tên của tài khoản từ mạng xã hội
        $name_parts = explode( ' ', $name, 2 );
        $first_name = isset( $name_parts[0] ) ? $name_parts[0] : $name;
        $last_name = isset( $name_parts[1] ) ? $name_parts[1] : '';

        wp_update_user( array(
            'ID'         => $user_id,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'display_name'=> $name
        ) );

        // Lưu thông tin nguồn đăng nhập để tiện quản lý (Meta data)
        update_user_meta( $user_id, '_social_login_provider', $provider );
    }

    // Tình huống 2: Người dùng đã có tài khoản (hoặc vừa được đăng ký xong) -> Đăng nhập vào hệ thống
    wp_clear_auth_cookie(); // Xóa cookie phiên làm việc cũ
    wp_set_current_user( $user->ID ); // Thiết lập phiên người dùng hiện tại
    wp_set_auth_cookie( $user->ID, true ); // Thiết lập cookie đăng nhập (nhớ tài khoản)

    // Chuyển hướng người dùng về trang Tài khoản của tôi hoặc trang chủ
    $redirect_url = wc_get_page_permalink( 'myaccount' );
    if ( ! $redirect_url ) {
        $redirect_url = home_url();
    }

    // Thêm tham số thông báo đăng nhập thành công để hiển thị thông báo
    $redirect_url = add_query_arg( 'login_status', 'social_success', $redirect_url );
    
    wp_safe_redirect( $redirect_url );
    exit;
}

/**
 * 5. Thiết kế trang màn hình giả lập (Mock Consent Screen) cực đẹp mắt và tiện lợi
 */
function dev_render_mock_consent_screen( $provider ) {
    $provider_title = ( $provider === 'google' ) ? 'Google' : 'Facebook';
    $provider_color = ( $provider === 'google' ) ? '#4285F4' : '#1877F2';
    
    // Tạo sẵn danh sách tài khoản demo tiện lợi để click nhanh
    $demo_accounts = array(
        array(
            'email' => 'nguyenvanan.demo@gmail.com',
            'name'  => 'Nguyễn Văn An',
            'img'   => 'https://api.dicebear.com/7.x/adventurer/svg?seed=An'
        ),
        array(
            'email' => 'lethib.demo@gmail.com',
            'name'  => 'Lê Thị B',
            'img'   => 'https://api.dicebear.com/7.x/adventurer/svg?seed=B'
        ),
        array(
            'email' => 'tony.stark@gmail.com',
            'name'  => 'Tony Stark (Ironman)',
            'img'   => 'https://api.dicebear.com/7.x/adventurer/svg?seed=Tony'
        )
    );
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sign in with <?php echo esc_html( $provider_title ); ?> (Simulated)</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Outfit', sans-serif;
                background-color: #f0f2f5;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .consent-box {
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                width: 420px;
                padding: 30px;
                box-sizing: border-box;
                border-top: 5px solid <?php echo esc_attr( $provider_color ); ?>;
            }
            .header {
                text-align: center;
                margin-bottom: 24px;
            }
            .logo-text {
                font-size: 26px;
                font-weight: 700;
                color: <?php echo esc_attr( $provider_color ); ?>;
                margin-bottom: 5px;
            }
            .subtitle {
                color: #5f6368;
                font-size: 14px;
            }
            .app-info {
                background: #f8f9fa;
                border-radius: 6px;
                padding: 12px;
                text-align: center;
                margin-bottom: 20px;
                font-size: 13px;
                color: #444;
                border: 1px solid #eaeaea;
            }
            .section-title {
                font-size: 14px;
                font-weight: 600;
                color: #3c4043;
                margin-bottom: 10px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .account-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-bottom: 20px;
            }
            .account-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px;
                border: 1px solid #dadce0;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .account-item:hover {
                background-color: #f7fafe;
                border-color: <?php echo esc_attr( $provider_color ); ?>;
            }
            .avatar {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background-color: #e8eaed;
            }
            .details {
                flex: 1;
            }
            .name {
                font-size: 14px;
                font-weight: 600;
                color: #202124;
            }
            .email {
                font-size: 12px;
                color: #5f6368;
            }
            .custom-login-form {
                border-top: 1px solid #dadce0;
                padding-top: 15px;
                margin-top: 15px;
            }
            .input-group {
                margin-bottom: 12px;
            }
            .input-group label {
                display: block;
                font-size: 13px;
                color: #5f6368;
                margin-bottom: 5px;
                font-weight: 500;
            }
            .input-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #dadce0;
                border-radius: 4px;
                box-sizing: border-box;
                font-size: 14px;
                outline: none;
            }
            .input-group input:focus {
                border-color: <?php echo esc_attr( $provider_color ); ?>;
            }
            .submit-btn {
                background-color: <?php echo esc_attr( $provider_color ); ?>;
                color: #ffffff;
                border: none;
                padding: 12px;
                border-radius: 4px;
                width: 100%;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            .submit-btn:hover {
                opacity: 0.9;
            }
            .cancel-link {
                display: block;
                text-align: center;
                margin-top: 15px;
                font-size: 13px;
                color: #5f6368;
                text-decoration: none;
            }
            .cancel-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="consent-box">
            <div class="header">
                <div class="logo-text"><?php echo esc_html( $provider_title ); ?></div>
                <div class="subtitle">Đăng nhập bằng tài khoản <?php echo esc_html( $provider_title ); ?> của bạn</div>
            </div>

            <div class="app-info">
                Ứng dụng <strong>E-Commerce (Local Dev)</strong> yêu cầu quyền truy cập Email và Tên hiển thị công khai của bạn.
            </div>

            <div class="section-title">Chọn tài khoản mẫu có sẵn</div>
            <div class="account-list">
                <?php foreach ( $demo_accounts as $acc ) : ?>
                    <div class="account-item" onclick="selectAccount('<?php echo esc_js( $acc['email'] ); ?>', '<?php echo esc_js( $acc['name'] ); ?>')">
                        <img class="avatar" src="<?php echo esc_url( $acc['img'] ); ?>" alt="Avatar">
                        <div class="details">
                            <div class="name"><?php echo esc_html( $acc['name'] ); ?></div>
                            <div class="email"><?php echo esc_html( $acc['email'] ); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-title">Hoặc nhập email kiểm thử tùy ý</div>
            <form class="custom-login-form" method="GET" action="<?php echo esc_url( home_url() ); ?>">
                <input type="hidden" name="social_callback" value="<?php echo esc_attr( $provider ); ?>">
                
                <div class="input-group">
                    <label for="name-input">Họ và tên</label>
                    <input type="text" id="name-input" name="name" placeholder="Nguyễn Văn A" required>
                </div>
                
                <div class="input-group">
                    <label for="email-input">Địa chỉ email</label>
                    <input type="email" id="email-input" name="email" placeholder="example@gmail.com" required>
                </div>

                <button type="submit" class="submit-btn">Xác nhận đăng nhập (Simulated)</button>
            </form>

            <a class="cancel-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ? wc_get_page_permalink( 'myaccount' ) : home_url() ); ?>">Hủy bỏ</a>
        </div>

        <script>
            function selectAccount(email, name) {
                var url = '<?php echo esc_js( home_url() ); ?>/?social_callback=<?php echo esc_js( $provider ); ?>&email=' + encodeURIComponent(email) + '&name=' + encodeURIComponent(name);
                window.location.href = url;
            }
        </script>
    </body>
    </html>
    <?php
}

/**
 * 6. Hiện thực hóa tích hợp API Đăng nhập thật (Google OAuth 2.0 & Facebook Graph API)
 */
function dev_redirect_to_real_google_oauth() {
    $client_id = defined( 'GOOGLE_CLIENT_ID' ) ? constant( 'GOOGLE_CLIENT_ID' ) : '';
    $redirect_uri = home_url( '/?social_callback=google' );
    
    // Tạo link chuyển hướng đến trang đăng nhập Google
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( array(
        'client_id'     => $client_id,
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => wp_create_nonce( 'google_oauth_state' )
    ) );
    
    wp_redirect( $auth_url );
    exit;
}

function dev_handle_real_google_callback( $code ) {
    $client_id     = defined( 'GOOGLE_CLIENT_ID' ) ? constant( 'GOOGLE_CLIENT_ID' ) : '';
    $client_secret = defined( 'GOOGLE_CLIENT_SECRET' ) ? constant( 'GOOGLE_CLIENT_SECRET' ) : '';
    $redirect_uri  = home_url( '/?social_callback=google' );

    // Gửi POST request đến Google đổi Code lấy Access Token
    $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
        'body' => array(
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
            'grant_type'    => 'authorization_code',
        )
    ) );

    if ( is_wp_error( $response ) ) {
        wp_die( 'Không thể kết nối với máy chủ Google: ' . esc_html( $response->get_error_message() ) );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $access_token = isset( $body['access_token'] ) ? $body['access_token'] : '';

    if ( empty( $access_token ) ) {
        wp_die( 'Không tìm thấy Access Token từ Google. Vui lòng cấu hình lại Client ID và Secret trong wp-config.php.' );
    }

    // Gửi GET request lấy thông tin Profile người dùng
    $profile_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . urlencode( $access_token ) );

    if ( is_wp_error( $profile_response ) ) {
        wp_die( 'Không lấy được thông tin Profile từ Google.' );
    }

    $profile_data = json_decode( wp_remote_retrieve_body( $profile_response ), true );
    $email = isset( $profile_data['email'] ) ? sanitize_email( $profile_data['email'] ) : '';
    $name  = isset( $profile_data['name'] ) ? sanitize_text_field( $profile_data['name'] ) : 'Google User';

    if ( ! empty( $email ) ) {
        dev_process_user_login( $email, $name, 'google' );
    } else {
        wp_die( 'Tài khoản Google của bạn không công khai email.' );
    }
}

function dev_redirect_to_real_facebook_oauth() {
    $app_id = defined( 'FACEBOOK_APP_ID' ) ? constant( 'FACEBOOK_APP_ID' ) : '';
    $redirect_uri = home_url( '/?social_callback=facebook' );
    
    // Tạo link chuyển hướng đến trang đăng nhập Facebook
    $auth_url = 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query( array(
        'client_id'    => $app_id,
        'redirect_uri' => $redirect_uri,
        'scope'        => 'email,public_profile',
        'state'        => wp_create_nonce( 'facebook_oauth_state' )
    ) );
    
    wp_redirect( $auth_url );
    exit;
}

function dev_handle_real_facebook_callback( $code ) {
    $app_id       = defined( 'FACEBOOK_APP_ID' ) ? constant( 'FACEBOOK_APP_ID' ) : '';
    $app_secret   = defined( 'FACEBOOK_APP_SECRET' ) ? constant( 'FACEBOOK_APP_SECRET' ) : '';
    $redirect_uri = home_url( '/?social_callback=facebook' );

    // Đổi code lấy Access Token của Facebook
    $token_url = 'https://graph.facebook.com/v12.0/oauth/access_token?' . http_build_query( array(
        'client_id'     => $app_id,
        'redirect_uri'  => $redirect_uri,
        'client_secret' => $app_secret,
        'code'          => $code,
    ) );

    $response = wp_remote_get( $token_url );

    if ( is_wp_error( $response ) ) {
        wp_die( 'Không thể kết nối với máy chủ Facebook.' );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $access_token = isset( $body['access_token'] ) ? $body['access_token'] : '';

    if ( empty( $access_token ) ) {
        wp_die( 'Không lấy được Access Token từ Facebook. Vui lòng kiểm tra lại App ID và Secret.' );
    }

    // Lấy thông tin Profile
    $profile_url = 'https://graph.facebook.com/me?' . http_build_query( array(
        'fields'       => 'id,name,email',
        'access_token' => $access_token,
    ) );

    $profile_response = wp_remote_get( $profile_url );

    if ( is_wp_error( $profile_response ) ) {
        wp_die( 'Không lấy được thông tin từ Facebook.' );
    }

    $profile_data = json_decode( wp_remote_retrieve_body( $profile_response ), true );
    $email = isset( $profile_data['email'] ) ? sanitize_email( $profile_data['email'] ) : '';
    $name  = isset( $profile_data['name'] ) ? sanitize_text_field( $profile_data['name'] ) : 'Facebook User';

    if ( ! empty( $email ) ) {
        dev_process_user_login( $email, $name, 'facebook' );
    } else {
        // Nếu facebook ko có email (đăng ký bằng SĐT), tạo email ảo
        $facebook_id = isset( $profile_data['id'] ) ? sanitize_text_field( $profile_data['id'] ) : time();
        $email = $facebook_id . '@facebook.demo.com';
        dev_process_user_login( $email, $name, 'facebook' );
    }
}


/**
 * 7. Hiển thị thông báo Toast đẹp mắt sau khi đăng nhập thành công
 */
add_action( 'wp_footer', 'dev_social_login_toast_notification' );

function dev_social_login_toast_notification() {
    if ( isset( $_GET['login_status'] ) && $_GET['login_status'] === 'social_success' ) {
        $current_user = wp_get_current_user();
        if ( $current_user->ID ) {
            $welcome_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
            ?>
            <div id="dev-welcome-toast" class="dev-toast">
                <div class="dev-toast-content">
                    <span class="dev-toast-icon">👋</span>
                    <div class="dev-toast-text">
                        <strong>Đăng nhập thành công!</strong>
                        <span>Chào mừng quay trở lại, <?php echo esc_html( $welcome_name ); ?>.</span>
                    </div>
                </div>
                <div class="dev-toast-progress"></div>
            </div>
            
            <style>
                .dev-toast {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    background-color: #ffffff;
                    color: #333333;
                    padding: 16px 20px;
                    border-radius: 8px;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
                    z-index: 99999;
                    display: flex;
                    flex-direction: column;
                    overflow: hidden;
                    width: 320px;
                    animation: dev-toast-slide-in 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
                    border-left: 4px solid #4caf50;
                }
                .dev-toast-content {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                .dev-toast-icon {
                    font-size: 24px;
                }
                .dev-toast-text {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                }
                .dev-toast-text strong {
                    font-size: 15px;
                    color: #1a1a1a;
                }
                .dev-toast-text span {
                    font-size: 13px;
                    color: #666666;
                }
                .dev-toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    width: 100%;
                    background-color: #4caf50;
                    animation: dev-toast-progress-bar 4s linear forwards;
                }
                
                @keyframes dev-toast-slide-in {
                    from { transform: translateX(120%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes dev-toast-slide-out {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(120%); opacity: 0; }
                }
                @keyframes dev-toast-progress-bar {
                    from { width: 100%; }
                    to { width: 0%; }
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var toast = document.getElementById('dev-welcome-toast');
                    
                    // Ẩn toast sau 4 giây
                    setTimeout(function() {
                        toast.style.animation = 'dev-toast-slide-out 0.5s ease forwards';
                        setTimeout(function() {
                            toast.remove();
                        }, 500);
                    }, 4000);
                });
            </script>
            <?php
        }
    }
}
