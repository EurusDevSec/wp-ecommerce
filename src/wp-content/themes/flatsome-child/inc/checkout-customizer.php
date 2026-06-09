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
 * 1. Rút gọn Form thanh toán: loại bỏ các trường không cần thiết như Postcode, Company, Address 2...
 */
add_filter( 'woocommerce_checkout_fields', 'dev_optimize_checkout_fields' );

function dev_optimize_checkout_fields( $fields ) {
    // 1.1. Lọc bỏ các trường thừa trong phần thông tin thanh toán (Billing Info)
    if ( isset( $fields['billing']['billing_company'] ) ) {
        unset( $fields['billing']['billing_company'] ); // Bỏ Tên công ty
    }
    if ( isset( $fields['billing']['billing_address_2'] ) ) {
        unset( $fields['billing']['billing_address_2'] ); // Bỏ Địa chỉ dòng 2 mặc định
    }
    if ( isset( $fields['billing']['billing_postcode'] ) ) {
        unset( $fields['billing']['billing_postcode'] ); // Bỏ Mã bưu điện
    }

    // 1.2. Lọc bỏ các trường thừa trong phần thông tin nhận hàng (Shipping Info)
    if ( isset( $fields['shipping']['shipping_company'] ) ) {
        unset( $fields['shipping']['shipping_company'] );
    }
    if ( isset( $fields['shipping']['shipping_address_2'] ) ) {
        unset( $fields['shipping']['shipping_address_2'] );
    }
    if ( isset( $fields['shipping']['shipping_postcode'] ) ) {
        unset( $fields['shipping']['shipping_postcode'] );
    }

    // 1.3. Chuyển đổi trường Tỉnh/Thành (State) mặc định thành thẻ select động
    $fields['billing']['billing_state']['type']        = 'select';
    $fields['billing']['billing_state']['label']       = 'Tỉnh / Thành phố';
    $fields['billing']['billing_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['billing']['billing_state']['options']     = array( '' => 'Chọn Tỉnh / Thành phố' );
    $fields['billing']['billing_state']['class']       = array( 'form-row-wide' );

    $fields['shipping']['shipping_state']['type']        = 'select';
    $fields['shipping']['shipping_state']['label']       = 'Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['options']     = array( '' => 'Chọn Tỉnh / Thành phố' );
    $fields['shipping']['shipping_state']['class']       = array( 'form-row-wide' );

    // 1.4. Đăng ký thêm trường Quận/Huyện (District) dưới dạng thẻ select động
    $fields['billing']['billing_district'] = array(
        'label'       => 'Quận / Huyện',
        'required'    => true,
        'class'       => array( 'form-row-first' ),
        'type'        => 'select',
        'options'     => array( '' => 'Chọn Quận / Huyện' ),
        'priority'    => 75 // Xếp sau trường Tỉnh/Thành
    );

    $fields['shipping']['shipping_district'] = array(
        'label'       => 'Quận / Huyện',
        'required'    => true,
        'class'       => array( 'form-row-first' ),
        'type'        => 'select',
        'options'     => array( '' => 'Chọn Quận / Huyện' ),
        'priority'    => 75
    );

    // 1.5. Đăng ký thêm trường Xã/Phường (Ward) dưới dạng thẻ select động
    $fields['billing']['billing_ward'] = array(
        'label'       => 'Xã / Phường / Thị trấn',
        'required'    => true,
        'class'       => array( 'form-row-last' ),
        'type'        => 'select',
        'options'     => array( '' => 'Chọn Xã / Phường / Thị trấn' ),
        'priority'    => 76 // Xếp sau trường Quận/Huyện
    );

    $fields['shipping']['shipping_ward'] = array(
        'label'       => 'Xã / Phường / Thị trấn',
        'required'    => true,
        'class'       => array( 'form-row-last' ),
        'type'        => 'select',
        'options'     => array( '' => 'Chọn Xã / Phường / Thị trấn' ),
        'priority'    => 76
    );

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
 * 2. Lưu trữ chuỗi chữ Tỉnh/Quận/Xã vào metadata đơn hàng khi tạo đơn (AC-BE-05)
 * Sử dụng CRUD API của đối tượng WC_Order để tương thích hoàn toàn với HPOS
 */
add_action( 'woocommerce_checkout_create_order', 'dev_save_checkout_address_metadata', 10, 2 );

function dev_save_checkout_address_metadata( $order, $data ) {
    $district = isset( $_POST['billing_district'] ) ? sanitize_text_field( $_POST['billing_district'] ) : '';
    $ward     = isset( $_POST['billing_ward'] ) ? sanitize_text_field( $_POST['billing_ward'] ) : '';

    // Lưu dạng chuỗi chữ vào meta tùy chỉnh để tương thích code cũ
    if ( ! empty( $district ) ) {
        $order->update_meta_data( '_billing_district', $district );
        // ĐỒNG BỘ VÀO BẢNG CHUẨN HPOS/METADATA THEO TIÊU CHÍ AC-BE-05:
        $order->set_billing_city( $district );
    }
    if ( ! empty( $ward ) ) {
        $order->update_meta_data( '_billing_ward', $ward );
        // ĐỒNG BỘ VÀO BẢNG CHUẨN HPOS/METADATA THEO TIÊU CHÍ AC-BE-05:
        $order->set_billing_address_2( $ward );
    }

    // Xử lý thông tin nhận hàng (Shipping) nếu chọn địa chỉ khác
    $shipping_district = isset( $_POST['shipping_district'] ) ? sanitize_text_field( $_POST['shipping_district'] ) : '';
    $shipping_ward     = isset( $_POST['shipping_ward'] ) ? sanitize_text_field( $_POST['shipping_ward'] ) : '';

    if ( ! empty( $shipping_district ) ) {
        $order->update_meta_data( '_shipping_district', $shipping_district );
        $order->set_shipping_city( $shipping_district );
    } elseif ( ! empty( $district ) ) {
        // Fallback về billing nếu không nhập shipping riêng biệt
        $order->set_shipping_city( $district );
    }

    if ( ! empty( $shipping_ward ) ) {
        $order->update_meta_data( '_shipping_ward', $shipping_ward );
        $order->set_shipping_address_2( $shipping_ward );
    } elseif ( ! empty( $ward ) ) {
        // Fallback về billing nếu không nhập shipping riêng biệt
        $order->set_shipping_address_2( $ward );
    }
}

/**
 * 3. Nhúng dữ liệu địa giới hành chính mới vào cấu trúc hiển thị địa chỉ (Checkout, My Account, Email)
 */
add_filter( 'woocommerce_formatted_billing_address', 'dev_custom_formatted_billing_address_display', 10, 2 );

function dev_custom_formatted_billing_address_display( $address, $order ) {
    $district = $order->get_meta( '_billing_district' );
    $ward     = $order->get_meta( '_billing_ward' );

    if ( ! empty( $ward ) ) {
        $address['address_2'] = $ward;
    }
    if ( ! empty( $district ) ) {
        $address['city'] = $district . ', ' . $address['city'];
    }

    return $address;
}

add_filter( 'woocommerce_formatted_shipping_address', 'dev_custom_formatted_shipping_address_display', 10, 2 );

function dev_custom_formatted_shipping_address_display( $address, $order ) {
    $district = $order->get_meta( '_shipping_district' );
    $ward     = $order->get_meta( '_shipping_ward' );

    if ( ! empty( $ward ) ) {
        $address['address_2'] = $ward;
    }
    if ( ! empty( $district ) ) {
        $address['city'] = $district . ', ' . $address['city'];
    }

    return $address;
}

/**
 * 4. Kịch bản Javascript gọi API REST động để cập nhật các dropdown
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
                var districtSelect = $('#' + prefix + '_district');
                var wardSelect = $('#' + prefix + '_ward');

                if (!provinceSelect.length) return;

                // 4.1. Tải danh sách Tỉnh/Thành khi load trang qua API hkt/v1
                $.ajax({
                    url: '/wp-json/hkt/v1/provinces',
                    method: 'GET',
                    success: function(data) {
                        provinceSelect.empty().append('<option value="">Chọn Tỉnh / Thành phố</option>');
                        $.each(data, function(i, province) {
                            provinceSelect.append($('<option>', {
                                value: province.name,
                                text: province.name,
                                'data-id': province.id
                            }));
                        });
                        provinceSelect.trigger('change.select2');
                    }
                });

                // 4.2. Khi chọn Tỉnh/Thành -> Tải danh sách Quận/Huyện tương ứng qua API hkt/v1
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

                // 4.3. Khi chọn Quận/Huyện -> Tải danh sách Xã/Phường tương ứng qua API hkt/v1
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

            // 4.4. Ẩn các dòng lỗi cụ thể của từng ô nhập liệu ở danh sách thông báo chung phía trên
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
 * 2. Xác thực (Validate) Số điện thoại định dạng Việt Nam tại trang Checkout
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
