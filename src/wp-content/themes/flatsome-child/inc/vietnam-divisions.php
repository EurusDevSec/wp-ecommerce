<?php
/**
 * Tải cơ sở dữ liệu Địa giới Hành chính Việt Nam và tạo API REST
 * Chú thích tiếng Việt dễ hiểu. Cơ chế tự động tải và cache file JSON tĩnh để tối ưu hiệu năng.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Hàm phụ trợ tải dữ liệu địa giới hành chính từ kho mở Github kenzouno1/DiaGioiHanhChinhVN
 * File dữ liệu được lưu cache tĩnh tại thư mục child theme inc/data/vietnam-divisions.json
 */
function dev_get_vietnam_divisions_data() {
    $data_dir = dirname( __FILE__ ) . '/data';
    
    // Tạo thư mục lưu data nếu chưa tồn tại
    if ( ! file_exists( $data_dir ) ) {
        wp_mkdir_p( $data_dir );
    }

    $file_path = $data_dir . '/vietnam-divisions.json';

    // Nếu chưa có file cache tại local, tải từ Github
    if ( ! file_exists( $file_path ) ) {
        // Link raw dữ liệu địa giới hành chính VN gồm Tỉnh -> Huyện -> Xã
        $url = 'https://raw.githubusercontent.com/kenzouno1/DiaGioiHanhChinhVN/master/data.json';
        
        // Gửi request lấy dữ liệu từ máy chủ Github
        $response = wp_remote_get( $url, array( 'timeout' => 45 ) );

        if ( is_wp_error( $response ) ) {
            // Trường hợp lỗi mạng, trả về dữ liệu mẫu rỗng để tránh làm sập web
            return array();
        }

        $body = wp_remote_retrieve_body( $response );
        
        if ( empty( $body ) ) {
            return array();
        }

        // Lưu cache lại file local để các lần gọi sau chạy nhanh tức thì, không cần tải lại
        file_put_contents( $file_path, $body );
    } else {
        // Nếu đã có file local, đọc trực tiếp từ ổ đĩa
        $body = file_get_contents( $file_path );
    }

    return json_decode( $body, true );
}

/**
 * 2. Đăng ký các Endpoint REST API trong hệ thống WordPress
 * Các endpoint này dùng để cung cấp dữ liệu cho giao diện dropdown Tỉnh/Huyện/Xã tại Checkout
 */
add_action( 'rest_api_init', 'dev_register_vietnam_divisions_routes' );

function dev_register_vietnam_divisions_routes() {
    // 2.1. Lấy danh sách Tỉnh/Thành: GET /wp-json/dev/v1/provinces
    register_rest_route( 'dev/v1', '/provinces', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_provinces_callback',
        'permission_callback' => '__return_true' // Cho phép mọi khách truy cập gọi API này công khai
    ) );

    // 2.2. Lấy danh sách Quận/Huyện theo Tỉnh: GET /wp-json/dev/v1/districts?province_id=XX
    register_rest_route( 'dev/v1', '/districts', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_districts_callback',
        'permission_callback' => '__return_true'
    ) );

    // 2.3. Lấy danh sách Xã/Phường theo Huyện: GET /wp-json/dev/v1/wards?district_id=XXX
    register_rest_route( 'dev/v1', '/wards', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_wards_callback',
        'permission_callback' => '__return_true'
    ) );
}

/**
 * 3. Hàm xử lý logic phản hồi API (Callbacks)
 */

// 3.1. Trả về danh sách tất cả các Tỉnh/Thành
function dev_api_get_provinces_callback() {
    $data = dev_get_vietnam_divisions_data();
    $provinces = array();

    if ( is_array( $data ) ) {
        foreach ( $data as $province ) {
            $provinces[] = array(
                'id'   => $province['Id'],
                'name' => $province['Name']
            );
        }
    }

    return rest_ensure_response( $provinces );
}

// 3.2. Trả về các Quận/Huyện dựa trên mã Tỉnh truyền vào
function dev_api_get_districts_callback( $request ) {
    $province_id = sanitize_text_field( $request->get_param( 'province_id' ) );
    
    if ( empty( $province_id ) ) {
        return new WP_Error( 'missing_parameter', 'Vui lòng cung cấp tham số province_id', array( 'status' => 400 ) );
    }

    $data = dev_get_vietnam_divisions_data();
    $districts = array();

    if ( is_array( $data ) ) {
        foreach ( $data as $province ) {
            if ( $province['Id'] === $province_id ) {
                if ( isset( $province['Districts'] ) && is_array( $province['Districts'] ) ) {
                    foreach ( $province['Districts'] as $district ) {
                        $districts[] = array(
                            'id'   => $district['Id'],
                            'name' => $district['Name']
                        );
                    }
                }
                break; // Tìm thấy tỉnh rồi thì dừng vòng lặp
            }
        }
    }

    return rest_ensure_response( $districts );
}

// 3.3. Trả về các Xã/Phường dựa trên mã Quận/Huyện truyền vào
function dev_api_get_wards_callback( $request ) {
    $district_id = sanitize_text_field( $request->get_param( 'district_id' ) );

    if ( empty( $district_id ) ) {
        return new WP_Error( 'missing_parameter', 'Vui lòng cung cấp tham số district_id', array( 'status' => 400 ) );
    }

    $data = dev_get_vietnam_divisions_data();
    $wards = array();

    if ( is_array( $data ) ) {
        foreach ( $data as $province ) {
            if ( isset( $province['Districts'] ) && is_array( $province['Districts'] ) ) {
                foreach ( $province['Districts'] as $district ) {
                    if ( $district['Id'] === $district_id ) {
                        if ( isset( $district['Wards'] ) && is_array( $district['Wards'] ) ) {
                            foreach ( $district['Wards'] as $ward ) {
                                $wards[] = array(
                                    'id'   => $ward['Id'],
                                    'name' => $ward['Name']
                                );
                            }
                        }
                        break 2; // Dừng cả 2 vòng lặp lồng nhau
                    }
                }
            }
        }
    }

    return rest_ensure_response( $wards );
}
