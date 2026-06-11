<?php
/**
 * Tải cơ sở dữ liệu Địa giới Hành chính Việt Nam và tạo API REST
 * Chú thích tiếng Việt dễ hiểu. Phiên bản sáp nhập mới nhất (34 tỉnh thành và 3321 xã phường).
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Hàm đọc dữ liệu địa giới hành chính sáp nhập từ file json cục bộ
 */
function dev_get_vietnam_divisions_data() {
    $file_path = dirname( __FILE__ ) . '/data/vietnam-divisions.json';

    if ( ! file_exists( $file_path ) ) {
        return array();
    }

    $body = file_get_contents( $file_path );
    return json_decode( $body, true );
}

/**
 * 2. Đăng ký các Endpoint REST API trong hệ thống WordPress
 */
add_action( 'rest_api_init', 'dev_register_vietnam_divisions_routes' );

function dev_register_vietnam_divisions_routes() {
    // --- KHÔNG GIAN TÊN CŨ (dev/v1) ---
    register_rest_route( 'dev/v1', '/provinces', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_provinces_callback',
        'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'dev/v1', '/districts', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_districts_callback',
        'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'dev/v1', '/wards', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_wards_callback',
        'permission_callback' => '__return_true'
    ) );

    // --- KHÔNG GIAN TÊN MỚI CHUẨN ĐẶC TẢ (hkt/v1) ---
    register_rest_route( 'hkt/v1', '/provinces', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_provinces_callback',
        'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'hkt/v1', '/districts', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_hkt_districts_callback',
        'permission_callback' => '__return_true'
    ) );

    register_rest_route( 'hkt/v1', '/wards', array(
        'methods'             => 'GET',
        'callback'            => 'dev_api_get_hkt_wards_callback',
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

// 3.2. Trả về danh sách xã/phường cho dev/v1/districts?province_id=XX
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
                if ( isset( $province['Wards'] ) && is_array( $province['Wards'] ) ) {
                    foreach ( $province['Wards'] as $ward ) {
                        $districts[] = array(
                            'id'   => $ward['Id'],
                            'name' => $ward['Name']
                        );
                    }
                }
                break;
            }
        }
    }

    return rest_ensure_response( $districts );
}

// 3.3. Fallback cho dev/v1/wards
function dev_api_get_wards_callback( $request ) {
    return rest_ensure_response( array() );
}

// 3.4. Trả về danh sách xã/phường định dạng hkt/v1/districts?province_id=XX
function dev_api_get_hkt_districts_callback( $request ) {
    $province_id = sanitize_text_field( $request->get_param( 'province_id' ) );
    
    if ( empty( $province_id ) ) {
        return new WP_Error( 'missing_parameter', 'Vui lòng cung cấp tham số province_id', array( 'status' => 400 ) );
    }

    $data = dev_get_vietnam_divisions_data();
    $districts = array();

    if ( is_array( $data ) ) {
        foreach ( $data as $province ) {
            if ( $province['Id'] === $province_id ) {
                if ( isset( $province['Wards'] ) && is_array( $province['Wards'] ) ) {
                    foreach ( $province['Wards'] as $ward ) {
                        $districts[] = array(
                            'district_id'   => $ward['Id'],
                            'district_name' => $ward['Name']
                        );
                    }
                }
                break;
            }
        }
    }

    return rest_ensure_response( $districts );
}

// 3.5. Fallback cho hkt/v1/wards
function dev_api_get_hkt_wards_callback( $request ) {
    return rest_ensure_response( array() );
}
