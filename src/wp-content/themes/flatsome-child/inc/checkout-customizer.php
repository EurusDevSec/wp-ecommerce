<?php
/**
 * Tùy biến rút gọn Form Checkout và Validate Số điện thoại Việt Nam
 * Chú thích tiếng Việt dễ hiểu, chuẩn WooCommerce hook.
 * Phiên bản sáp nhập mới nhất (Tỉnh/Thành -> Xã/Phường/Thị trấn, ẩn Quận/Huyện).
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Rút gọn Form thanh toán: loại bỏ các trường không cần thiết như Postcode, Company, Address 2
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
    if ( isset( $fields['billing']['billing_address_2'] ) ) {
        unset( $fields['billing']['billing_address_2'] ); // Bỏ Địa chỉ dòng 2 (Xã cũ)
    }

    // 1.2. Lọc bỏ các trường thừa trong phần thông tin nhận hàng (Shipping Info)
    if ( isset( $fields['shipping']['shipping_company'] ) ) {
        unset( $fields['shipping']['shipping_company'] );
    }
    if ( isset( $fields['shipping']['shipping_postcode'] ) ) {
        unset( $fields['shipping']['shipping_postcode'] );
    }
    if ( isset( $fields['shipping']['shipping_address_2'] ) ) {
        unset( $fields['shipping']['shipping_address_2'] );
    }

    // 1.3. Cấu hình trường Tỉnh/Thành (State) - KHÔNG đổi type, WooCommerce tự xử lý thành select
    // khi country có states đã đăng ký (woocommerce_states filter)
    $fields['billing']['billing_state']['label']       = 'Tỉnh / Thành phố';
    $fields['billing']['billing_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['billing']['billing_state']['class']       = array( 'form-row-wide' );
    $fields['billing']['billing_state']['required']    = true;
    $fields['billing']['billing_state']['priority']    = 50; // Xuất hiện sau Country

    $fields['shipping']['shipping_state']['label']       = 'Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['placeholder'] = 'Chọn Tỉnh / Thành phố';
    $fields['shipping']['shipping_state']['class']       = array( 'form-row-wide' );
    $fields['shipping']['shipping_state']['required']    = true;
    $fields['shipping']['shipping_state']['priority']    = 50;

    // 1.4. Đổi trường Quận/Huyện (City) thành thẻ select động hiển thị Xã / Phường / Thị trấn trực tiếp
    $fields['billing']['billing_city']['type']        = 'select';
    $fields['billing']['billing_city']['label']       = 'Xã / Phường / Thị trấn';
    $fields['billing']['billing_city']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['billing']['billing_city']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['billing']['billing_city']['class']       = array( 'form-row-wide' );
    $fields['billing']['billing_city']['required']    = true;
    $fields['billing']['billing_city']['priority']    = 60; // Sau Province

    $fields['shipping']['shipping_city']['type']        = 'select';
    $fields['shipping']['shipping_city']['label']       = 'Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_city']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_city']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['shipping']['shipping_city']['class']       = array( 'form-row-wide' );
    $fields['shipping']['shipping_city']['required']    = true;
    $fields['shipping']['shipping_city']['priority']    = 60;

    // 1.5. Cấu hình trường Địa chỉ chi tiết (Address 1) xuất hiện cuối cùng trong nhóm địa chỉ
    if ( isset( $fields['billing']['billing_address_1'] ) ) {
        $fields['billing']['billing_address_1']['priority'] = 80;
    }
    if ( isset( $fields['shipping']['shipping_address_1'] ) ) {
        $fields['shipping']['shipping_address_1']['priority'] = 80;
    }

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
    $billing_city = $order->get_billing_city();   // Xã/Phường/Thị trấn

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
    if ( ! empty( $billing_city ) ) {
        $order->update_meta_data( '_billing_ward', $billing_city );
        $order->update_meta_data( '_billing_district', $billing_city ); // Đồng bộ luôn trường quận huyện cũ
    }

    $shipping_city = $order->get_shipping_city();
    if ( ! empty( $shipping_city ) ) {
        $order->update_meta_data( '_shipping_ward', $shipping_city );
        $order->update_meta_data( '_shipping_district', $shipping_city );
    }
}

/**
 * 3. Hỗ trợ định dạng cấu trúc hiển thị địa chỉ Việt Nam chuẩn đẹp (Tỉnh -> Xã -> Đường)
 */
add_filter( 'woocommerce_localisation_address_formats', 'dev_vietnam_address_format_override' );

function dev_vietnam_address_format_override( $formats ) {
    $formats['VN'] = "{name}\n{state}\n{city}\n{address_1}\n{country}";
    return $formats;
}

/**
 * 4. Đăng ký danh sách Tỉnh/Thành phố của Việt Nam vào danh sách vùng miền của WooCommerce (states)
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
        'priority' => 50,
    );
    
    $locale['VN']['city'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Xã / Phường / Thị trấn',
        'priority' => 60,
    );
    
    $locale['VN']['address_2'] = array(
        'required' => false,
        'hidden'   => true,
        'priority' => 70,
    );

    $locale['VN']['address_1'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Địa chỉ',
        'priority' => 80,
    );
    
    return $locale;
}

/**
 * 6. Tiêm dữ liệu Locale Việt Nam vào wc_address_i18n_params TRƯỚC KHI country-select.js xử lý
 */
add_action( 'wp_head', 'dev_patch_wc_country_locale_vn', 100 );

function dev_patch_wc_country_locale_vn() {
    if ( ! is_checkout() ) {
        return;
    }
    ?>
    <script>
    // Chạy ngay lập tức - patch locale VN trước khi country-select.js nạp
    (function() {
        function patchVNLocale() {
            if (typeof wc_address_i18n_params === 'undefined') return false;
            if (!wc_address_i18n_params.locale) wc_address_i18n_params.locale = {};

            wc_address_i18n_params.locale['VN'] = {
                "state": {
                    "label": "T\u1ec9nh \/ Th\u00e0nh ph\u1ed1",
                    "required": true,
                    "hidden": false,
                    "priority": 50
                },
                "city": {
                    "label": "X\u00e3 \/ Ph\u01b0\u1eddng \/ Th\u1ecb tr\u1ea5n",
                    "required": true,
                    "hidden": false,
                    "priority": 60
                },
                "postcode": {
                    "required": false,
                    "hidden": true
                },
                "address_2": {
                    "required": false,
                    "hidden": true,
                    "priority": 70
                },
                "address_1": {
                    "required": true,
                    "hidden": false,
                    "priority": 80
                }
            };

            return true;
        }

        if (!patchVNLocale()) {
            document.addEventListener('DOMContentLoaded', patchVNLocale);
        }
    })();
    </script>
    <?php
}

/**
 * 6b. Kịch bản Javascript gọi API REST động để cập nhật các dropdown
 */
add_action( 'wp_footer', 'dev_checkout_address_dropdown_script' );

function dev_checkout_address_dropdown_script() {
    if ( ! is_checkout() ) {
        return;
    }
    $billing_state = WC()->customer->get_billing_state();
    $billing_city = WC()->customer->get_billing_city();

    $shipping_state = WC()->customer->get_shipping_state();
    $shipping_city = WC()->customer->get_shipping_city();
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
        #billing_city_field select,
        #shipping_city_field select {
            width: 100%;
        }
    </style>
    <script>
    (function($) {
        'use strict';

        var apiBase = '/wp-json/hkt/v1';

        // Khởi tạo các giá trị đã chọn từ PHP session
        var devSavedAddress = {
            billing: {
                state: <?php echo json_encode($billing_state); ?>,
                city: <?php echo json_encode($billing_city); ?>
            },
            shipping: {
                state: <?php echo json_encode($shipping_state); ?>,
                city: <?php echo json_encode($shipping_city); ?>
            }
        };

        // Biến lưu trữ trạng thái lựa chọn hiện tại trên client để giữ nguyên sau AJAX re-init
        var devCurrentAddress = {
            billing: { city: '' },
            shipping: { city: '' }
        };

        /**
         * Đảm bảo trường city là <select> không phải <input>
         */
        function devEnsureSelectField(fieldId, placeholder) {
            var field = $('#' + fieldId);
            if (!field.length) return;

            if (field.is('input')) {
                var selectEl = $('<select>', {
                    id: fieldId,
                    name: fieldId,
                    class: field.attr('class') || ''
                }).append($('<option>', { value: '', text: placeholder }));
                field.replaceWith(selectEl);
            }
        }

        /**
         * Khởi tạo logic cho một nhóm địa chỉ (billing hoặc shipping)
         */
        function devInitAddressFields(prefix) {
            devEnsureSelectField(prefix + '_city', 'Chọn Xã / Phường / Thị trấn');

            var provinceSelect = $('#' + prefix + '_state');
            var wardSelect     = $('#' + prefix + '_city');

            if (!provinceSelect.length) return;

            provinceSelect.off('change.devVN select2:select.devVN');
            wardSelect.off('change.devVN');

            var targetWard = devCurrentAddress[prefix].city || devSavedAddress[prefix].city;

            provinceSelect.on('change.devVN select2:select.devVN', function(e, isInit) {
                var provinceId = $(this).val();
                console.log("devVN state change fired: prefix=" + prefix + ", val=" + provinceId);
                var currentWardSelect = $('#' + prefix + '_city');

                if (!isInit) {
                    devCurrentAddress[prefix].city = '';
                    devSavedAddress[prefix].city = '';
                    targetWard = '';
                }

                currentWardSelect.empty().append($('<option>', { value: '', text: 'Chọn Xã / Phường / Thị trấn' }));
                if (currentWardSelect.hasClass('select2-hidden-accessible')) {
                    currentWardSelect.trigger('change.select2');
                }

                if (!provinceId) return;

                $.ajax({
                    url: apiBase + '/districts',
                    method: 'GET',
                    data: { province_id: provinceId },
                    success: function(data) {
                        if (!$.isArray(data) || data.length === 0) return;
                        var dynamicWardSelect = $('#' + prefix + '_city');
                        dynamicWardSelect.empty().append($('<option>', { value: '', text: 'Chọn Xã / Phường / Thị trấn' }));
                        $.each(data, function(i, ward) {
                            var optionProps = {
                                value: ward.district_name,
                                text:  ward.district_name,
                                'data-id': ward.district_id
                            };
                            if (targetWard && targetWard === ward.district_name) {
                                optionProps.selected = true;
                            }
                            dynamicWardSelect.append($('<option>', optionProps));
                        });
                        if (dynamicWardSelect.hasClass('select2-hidden-accessible')) {
                            dynamicWardSelect.trigger('change.select2');
                        }

                        devSavedAddress[prefix].city = '';
                        targetWard = '';
                    }
                });
            });

            wardSelect.on('change.devVN', function() {
                var selectedVal = $(this).val();
                if (selectedVal) {
                    devCurrentAddress[prefix].city = selectedVal;
                }
            });

            var currentProv = provinceSelect.val();
            if (currentProv) {
                provinceSelect.trigger('change.devVN', [true]);
            }
        }

        function devBootstrap() {
            devInitAddressFields('billing');
            devInitAddressFields('shipping');
        }

        $(document).ready(function() {
            devBootstrap();

            $(document.body).on('updated_checkout', function() {
                devBootstrap();
            });

            $(document.body).on('checkout_error', function() {
                var noticeGroup = $('.woocommerce-NoticeGroup-checkout');
                var errorList   = noticeGroup.find('.woocommerce-error');
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

    })(jQuery);
    </script>
    <?php
}

/**
 * 7. Xác thực (Validate) Số điện thoại định dạng Việt Nam tại trang Checkout
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
