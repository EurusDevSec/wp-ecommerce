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
    $fields['billing']['billing_state']['options']     = array( '' => 'Chọn Tỉnh / Thành phố' );
    $fields['billing']['billing_state']['class']       = array( 'form-row-wide' );
    $fields['billing']['billing_state']['required']    = true;

    $fields['shipping']['shipping_state']['type']        = 'select';
    $fields['shipping']['shipping_state']['label']       = 'Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['options']     = array( '' => 'Chọn Tỉnh / Thành phố' );
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
 * Đồng thời đồng bộ các trường chuẩn của WooCommerce
 */
add_action( 'woocommerce_checkout_create_order', 'dev_save_checkout_address_metadata', 10, 2 );

function dev_save_checkout_address_metadata( $order, $data ) {
    $billing_city = $order->get_billing_city();
    $billing_address_2 = $order->get_billing_address_2();

    // Đồng bộ sang metadata cũ để không gây lỗi hiển thị ở các chức năng khác
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
    // Định dạng hiển thị địa chỉ Việt Nam: Họ tên -> Số nhà đường -> Phường Xã -> Quận Huyện -> Tỉnh Thành -> Quốc gia
    $formats['VN'] = "{name}\n{address_1}\n{address_2}\n{city}\n{state}\n{country}";
    return $formats;
}

/**
 * 4. Điều chỉnh cấu hình vùng miền của WooCommerce cho Việt Nam (VN)
 * Đảm bảo trường Tỉnh/Thành (state) luôn hiển thị và bắt buộc, không bị country-select.js ẩn đi.
 */
add_filter( 'woocommerce_get_country_locale', 'dev_custom_vietnam_country_locale' );

function dev_custom_vietnam_country_locale( $locale ) {
    if ( isset( $locale['VN'] ) ) {
        $locale['VN']['state']['required'] = true;
        $locale['VN']['state']['hidden']   = false;
        $locale['VN']['state']['label']    = 'Tỉnh / Thành phố';
        
        $locale['VN']['city']['required'] = true;
        $locale['VN']['city']['hidden']   = false;
        $locale['VN']['city']['label']    = 'Quận / Huyện';
        
        $locale['VN']['address_2']['required'] = true;
        $locale['VN']['address_2']['hidden']   = false;
        $locale['VN']['address_2']['label']    = 'Xã / Phường / Thị trấn';
    }
    return $locale;
}

/**
 * 5. Kịch bản Javascript gọi API REST động để cập nhật các dropdown
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

                // 5.1. Tải danh sách Tỉnh/Thành khi load trang qua API hkt/v1
                $.ajax({
                    url: '/wp-json/hkt/v1/provinces',
                    method: 'GET',
                    success: function(data) {
                        var currentVal = provinceSelect.val();
                        provinceSelect.empty().append('<option value="">Chọn Tỉnh / Thành phố</option>');
                        $.each(data, function(i, province) {
                            var option = $('<option>', {
                                value: province.name,
                                text: province.name,
                                'data-id': province.id
                            });
                            if (province.name === currentVal) {
                                option.attr('selected', 'selected');
                            }
                            provinceSelect.append(option);
                        });
                        provinceSelect.trigger('change.select2');
                    }
                });

                // 5.2. Khi chọn Tỉnh/Thành -> Tải danh sách Quận/Huyện tương ứng qua API hkt/v1
                provinceSelect.on('change', function() {
                    var selectedOption = provinceSelect.find('option:selected');
                    var provinceId = selectedOption.data('id');

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

                // 5.3. Khi chọn Quận/Huyện -> Tải danh sách Xã/Phường tương ứng qua API hkt/v1
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
            }

            // Khởi tạo cho cả Billing và Shipping
            devInitAddressFields('billing');
            devInitAddressFields('shipping');

            // 5.4. Ẩn các dòng lỗi cụ thể của từng ô nhập liệu ở danh sách thông báo chung phía trên
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
 * 6. Xác thực (Validate) Số điện thoại định dạng Việt Nam tại trang Checkout
 * Đảm bảo số điện thoại hợp lệ theo tiêu chuẩn đầu số Việt Nam (tiêu chí AC-BE-03)
 */
add_action( 'woocommerce_checkout_process', 'dev_validate_vietnamese_phone_number' );

function dev_validate_vietnamese_phone_number() {
    // Lấy số điện thoại người dùng nhập vào
    $phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( $_POST['billing_phone'] ) : '';

    if ( ! empty( $phone ) ) {
        // Loại bỏ các ký tự trống, dấu gạch ngang, dấu chấm hoặc ngoặc đơn để lấy chuỗi số thuần
        $clean_phone = preg_replace( '/[\s\.\-\(\)]/', '', $phone );

        // Biểu thức chính quy (Regex) xác thực số điện thoại di động Việt Nam:
        // - Bắt đầu bằng 0 hoặc +84
        // - Theo sau bởi các đầu số di động hiện nay: 3, 5, 7, 8, 9 (ví dụ: Viettel 03x/09x, Vina 08x, Mobi 07x...)
        // - Theo sau bởi đúng 8 ký tự số nữa (tổng cộng 10 số sau khi chuyển từ +84 -> 0)
        $vietnamese_phone_pattern = '/^(0|\+84)(3|5|7|8|9)[0-9]{8}$/';

        // So khớp regex
        if ( ! preg_match( $vietnamese_phone_pattern, $clean_phone ) ) {
            // Thêm thông báo lỗi hiển thị trên đầu trang Checkout
            wc_add_notice( '<strong>Số điện thoại</strong> không hợp lệ. Vui lòng điền đúng số điện thoại Việt Nam (ví dụ: 0912345678 hoặc +84912345678).', 'error' );
        }
    }
}
