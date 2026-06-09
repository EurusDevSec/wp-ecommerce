<?php
/**
 * Tùy biến rút gọn Form Checkout và Validate Số điện thoại Việt Nam
 * Chú thích tiếng Việt dễ hiểu, chuẩn WooCommerce hook.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Rút gọn Form thanh toán: loại bỏ các trường không cần thiết như Postcode, Company
 */
add_filter( 'woocommerce_checkout_fields', 'dev_optimize_checkout_fields' );

function dev_optimize_checkout_fields( $fields ) {
    // 1.1. Lọc bỏ các trường thừa trong phần thông tin thanh toán (Billing Info)
    if ( isset( $fields['billing']['billing_company'] ) ) {
        unset( $fields['billing']['billing_company'] ); // Bỏ Tên công ty
    }
    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        unset( $fields['billing']['billing_postcode'] ); // Bỏ Mã bưu điện
    }

    // 1.2. Lọc bỏ các trường thừa trong phần thông tin nhận hàng (Shipping Info)
    if ( isset( $fields['shipping']['shipping_company'] ) ) {
        unset( $fields['shipping']['shipping_company'] );
    }
    if ( isset( $fields['shipping']['shipping_postcode'] ) ) {
        unset( $fields['shipping']['shipping_postcode'] );
    }

    // 1.3. Cấu hình trường Tỉnh/Thành (State) thành thẻ select động
    $fields['billing']['billing_state']['type']        = 'select';
    $fields['billing']['billing_state']['label']       = 'Tỉnh / Thành phố';
    $fields['billing']['billing_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['billing']['billing_state']['class']       = array( 'form-row-wide' );
    $fields['billing']['billing_state']['required']    = true;

    $fields['shipping']['shipping_state']['type']        = 'select';
    $fields['shipping']['shipping_state']['label']       = 'Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['class']       = array( 'form-row-wide' );
    $fields['shipping']['shipping_state']['required']    = true;

    // 1.4. Đổi trường Quận/Huyện (City) thành thẻ select động (AC-BE-05)
    $fields['billing']['billing_city']['type']        = 'select';
    $fields['billing']['billing_city']['label']       = 'Quận / Huyện';
    $fields['billing']['billing_city']['placeholder'] = 'Chọn Quận / Huyện';
    $fields['billing']['billing_city']['options']     = array( '' => 'Chọn Quận / Huyện' );
    $fields['billing']['billing_city']['class']       = array( 'form-row-first' );
    $fields['billing']['billing_city']['required']    = true;

    $fields['shipping']['shipping_city']['type']        = 'select';
    $fields['shipping']['shipping_city']['label']       = 'Quận / Huyện';
    $fields['shipping']['shipping_city']['placeholder'] = 'Chọn Quận / Huyện';
    $fields['shipping']['shipping_city']['options']     = array( '' => 'Chọn Quận / Huyện' );
    $fields['shipping']['shipping_city']['class']       = array( 'form-row-first' );
    $fields['shipping']['shipping_city']['required']    = true;

    // 1.5. Đổi trường Xã/Phường (Address 2) thành thẻ select động (AC-BE-05)
    $fields['billing']['billing_address_2']['type']        = 'select';
    $fields['billing']['billing_address_2']['label']       = 'Xã / Phường / Thị trấn';
    $fields['billing']['billing_address_2']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['billing']['billing_address_2']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['billing']['billing_address_2']['class']       = array( 'form-row-last' );
    $fields['billing']['billing_address_2']['required']    = true;

    $fields['shipping']['shipping_address_2']['type']        = 'select';
    $fields['shipping']['shipping_address_2']['label']       = 'Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_address_2']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_address_2']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['shipping']['shipping_address_2']['class']       = array( 'form-row-last' );
    $fields['shipping']['shipping_address_2']['required']    = true;

    // Định dạng lại chiều rộng tên và họ xếp song song
    if ( isset( $fields['billing']['billing_first_name'] ) ) {
        $fields['billing']['billing_first_name']['class'] = array( 'form-row-first' );
    }
    if ( isset( $fields['billing']['billing_last_name'] ) ) {
        $fields['billing']['billing_last_name']['class'] = array( 'form-row-last' );
    }
    
    return $fields;
}

/**
 * 2. Lưu trữ bổ sung metadata tùy chỉnh để tương thích ngược với code cũ (nếu cần)
 * Đồng thời chuyển mã Tỉnh/Thành (State Code) thành tên đầy đủ trước khi lưu
 */
add_action( 'woocommerce_checkout_create_order', 'dev_save_checkout_address_metadata', 10, 2 );

function dev_save_checkout_address_metadata( $order, $data ) {
    $billing_state = $order->get_billing_state(); // Lúc này là ID (ví dụ: '01')
    $billing_city = $order->get_billing_city();   // Quận/Huyện
    $billing_address_2 = $order->get_billing_address_2(); // Xã/Phường

    // Tìm tên Tỉnh/Thành tương ứng từ ID để lưu dạng chữ tiếng Việt
    if ( ! empty( $billing_state ) && function_exists( 'dev_get_vietnam_divisions_data' ) ) {
        $provinces_data = dev_get_vietnam_divisions_data();
        if ( is_array( $provinces_data ) ) {
            foreach ( $provinces_data as $province ) {
                if ( $province['Id'] === $billing_state ) {
                    $order->set_billing_state( $province['Name'] ); // Lưu tên Tỉnh dạng chữ
                    break;
                }
            }
        }
    }

    $shipping_state = $order->get_shipping_state();
    if ( ! empty( $shipping_state ) && function_exists( 'dev_get_vietnam_divisions_data' ) ) {
        $provinces_data = dev_get_vietnam_divisions_data();
        if ( is_array( $provinces_data ) ) {
            foreach ( $provinces_data as $province ) {
                if ( $province['Id'] === $shipping_state ) {
                    $order->set_shipping_state( $province['Name'] ); // Lưu tên Tỉnh dạng chữ
                    break;
                }
            }
        }
    }

    // Ghi nhận thêm metadata cũ để đảm bảo tương thích ngược
    $updated_billing_state = $order->get_billing_state();
    if ( ! empty( $billing_city ) ) {
        $order->update_meta_data( '_billing_district', $billing_city );
    }
    if ( ! empty( $billing_address_2 ) ) {
        $order->update_meta_data( '_billing_ward', $billing_address_2 );
    }

    $shipping_city = $order->get_shipping_city();
    $shipping_address_2 = $order->get_shipping_address_2();
    if ( ! empty( $shipping_city ) ) {
        $order->update_meta_data( '_shipping_district', $shipping_city );
    }
    if ( ! empty( $shipping_address_2 ) ) {
        $order->update_meta_data( '_shipping_ward', $shipping_address_2 );
    }
}

/**
 * 3. Hỗ trợ định dạng cấu trúc hiển thị địa chỉ Việt Nam chuẩn đẹp
 */
add_filter( 'woocommerce_localisation_address_formats', 'dev_vietnam_address_format_override' );

function dev_vietnam_address_format_override( $formats ) {
    $formats['VN'] = "{name}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
    return $formats;
}

/**
 * 4. Đăng ký danh sách Tỉnh/Thành phố của Việt Nam vào danh sách vùng miền của WooCommerce (states)
 * Giúp WooCommerce tự động hiển thị dropdown chọn Tỉnh/Thành phố trên cả Desktop và Mobile
 */
add_filter( 'woocommerce_states', 'dev_custom_vietnam_states' );

function dev_custom_vietnam_states( $states ) {
    if ( function_exists( 'dev_get_vietnam_divisions_data' ) ) {
        $provinces_data = dev_get_vietnam_divisions_data();
        $vn_states = array();
        if ( is_array( $provinces_data ) ) {
            foreach ( $provinces_data as $province ) {
                $vn_states[$province['Id']] = $province['Name'];
            }
        }
        $states['VN'] = $vn_states;
    }
    return $states;
}

/**
 * 5. Điều chỉnh cấu hình vùng miền của WooCommerce cho Việt Nam (VN)
 * Đảm bảo trường Tỉnh/Thành (state) luôn hiển thị và bắt buộc, không bị country-select.js ẩn đi.
 */
add_filter( 'woocommerce_get_country_locale', 'dev_custom_vietnam_country_locale' );

function dev_custom_vietnam_country_locale( $locale ) {
    if ( ! isset( $locale['VN'] ) ) {
        $locale['VN'] = array();
    }
    
    $locale['VN']['state'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Tỉnh / Thành phố',
    );
    
    $locale['VN']['city'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Quận / Huyện',
    );
    
    $locale['VN']['address_2'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Xã / Phường / Thị trấn',
    );
    
    return $locale;
}

/**
 * 6. Kịch bản Javascript gọi API REST động để cập nhật các dropdown
 */
add_action( 'wp_footer', 'dev_checkout_address_dropdown_script' );

function dev_checkout_address_dropdown_script() {
    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <style>
        .woocommerce-checkout .checkout-inline-error-message {
            color: #d32f2f !important;
            font-size: 13px !important;
            display: block !important;
            margin-top: 5px !important;
            font-weight: 500 !important;
        }
        .woocommerce-checkout .form-row.woocommerce-invalid input.input-text,
        .woocommerce-checkout .form-row.woocommerce-invalid select,
        .woocommerce-checkout .form-row.woocommerce-invalid textarea {
            border-color: #d32f2f !important;
            background-color: #fff9f9 !important;
        }
    </style>
    <script>
        jQuery(document).ready(function($) {
            function devInitAddressFields(prefix) {
                var provinceSelect = $('#' + prefix + '_state');
                var districtSelect = $('#' + prefix + '_city');       // Map Quận/Huyện vào trường City gốc
                var wardSelect = $('#' + prefix + '_address_2');     // Map Xã/Phường vào trường Address_2 gốc

                if (!provinceSelect.length) return;

                // 6.1. Khi chọn Tỉnh/Thành -> Tải danh sách Quận/Huyện tương ứng qua API hkt/v1
                provinceSelect.on('change', function() {
                    var provinceId = provinceSelect.val(); // Giá trị lúc này là ID (ví dụ: '01')

                    districtSelect.empty().append('<option value="">Chọn Quận / Huyện</option>');
                    wardSelect.empty().append('<option value="">Chọn Xã / Phường / Thị trấn</option>');

                    if (!provinceId) {
                        districtSelect.trigger('change.select2');
                        wardSelect.trigger('change.select2');
                        return;
                    }

                    $.ajax({
                        url: '/wp-json/hkt/v1/districts',
                        method: 'GET',
                        data: { province_id: provinceId },
                        success: function(data) {
                            $.each(data, function(i, district) {
                                districtSelect.append($('<option>', {
                                    value: district.district_name,
                                    text: district.district_name,
                                    'data-id': district.district_id
                                }));
                            });
                            districtSelect.trigger('change.select2');
                            wardSelect.trigger('change.select2');
                        }
                    });
                });

                // 6.2. Khi chọn Quận/Huyện -> Tải danh sách Xã/Phường tương ứng qua API hkt/v1
                districtSelect.on('change', function() {
                    var selectedOption = districtSelect.find('option:selected');
                    var districtId = selectedOption.data('id');

                    wardSelect.empty().append('<option value="">Chọn Xã / Phường / Thị trấn</option>');

                    if (!districtId) {
                        wardSelect.trigger('change.select2');
                        return;
                    }

                    $.ajax({
                        url: '/wp-json/hkt/v1/wards',
                        method: 'GET',
                        data: { district_id: districtId },
                        success: function(data) {
                            $.each(data, function(i, ward) {
                                wardSelect.append($('<option>', {
                                    value: ward.ward_name,
                                    text: ward.ward_name
                                }));
                            });
                            wardSelect.trigger('change.select2');
                        }
                    });
                });
                
                // Kích hoạt load lần đầu nếu đã chọn sẵn Tỉnh/Thành
                if (provinceSelect.val()) {
                    provinceSelect.trigger('change');
                }
            }

            // Khởi tạo cho cả Billing và Shipping
            devInitAddressFields('billing');
            devInitAddressFields('shipping');

            // 6.3. Ẩn các dòng lỗi cụ thể của từng ô nhập liệu ở danh sách thông báo chung phía trên
            $(document.body).on('checkout_error', function() {
                var noticeGroup = $('.woocommerce-NoticeGroup-checkout');
                var errorList = noticeGroup.find('.woocommerce-error');
                if (!errorList.length) {
                    errorList = $('.woocommerce-error');
                }

                if (errorList.length) {
                    var visibleErrorCount = 0;
                    
                    errorList.find('li').each(function() {
                        var li = $(this);
                        if (li.attr('data-id')) {
                            li.hide();
                        } else {
                            li.show();
                            visibleErrorCount++;
                        }
                    });

                    if (visibleErrorCount === 0) {
                        noticeGroup.hide();
                    } else {
                        noticeGroup.show();
                    }
                }
            });
        });
    </script>
    <?php
}

/**
 * 7. Xác thực (Validate) Số điện thoại định dạng Việt Nam tại trang Checkout
 * Đảm bảo số điện thoại hợp lệ theo tiêu chuẩn đầu số Việt Nam (tiêu chí AC-BE-03)
 */
add_action( 'woocommerce_checkout_process', 'dev_validate_vietnamese_phone_number' );

function dev_validate_vietnamese_phone_number() {
    $phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';

    if ( ! empty( $phone ) ) {
        $clean_phone = preg_replace( '/[\s\.\-\(\)]/', '', $phone );
        $vietnamese_phone_pattern = '/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/';

        if ( ! preg_match( $vietnamese_phone_pattern, $clean_phone ) ) {
            wc_add_notice( '<strong>Số điện thoại</strong> không hợp lệ. Vui lòng điền đúng số điện thoại Việt Nam (ví dụ: 0912345678 hoặc +84912345678).', 'error' );
        }
    }
}
