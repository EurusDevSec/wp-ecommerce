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

    // 1.4. Đổi trường Quận/Huyện (City) thành thẻ select động (AC-BE-05) - ẩn khỏi giao diện
    $fields['billing']['billing_city']['type']        = 'select';
    $fields['billing']['billing_city']['label']       = 'Quận / Huyện';
    $fields['billing']['billing_city']['placeholder'] = 'Chọn Quận / Huyện';
    $fields['billing']['billing_city']['options']     = array( '' => 'Chọn Quận / Huyện' );
    $fields['billing']['billing_city']['class']       = array( 'form-row-wide', 'hkt-hidden-field' );
    $fields['billing']['billing_city']['required']    = true;
    $fields['billing']['billing_city']['priority']    = 60; // Sau Province

    $fields['shipping']['shipping_city']['type']        = 'select';
    $fields['shipping']['shipping_city']['label']       = 'Quận / Huyện';
    $fields['shipping']['shipping_city']['placeholder'] = 'Chọn Quận / Huyện';
    $fields['shipping']['shipping_city']['options']     = array( '' => 'Chọn Quận / Huyện' );
    $fields['shipping']['shipping_city']['class']       = array( 'form-row-wide', 'hkt-hidden-field' );
    $fields['shipping']['shipping_city']['required']    = true;
    $fields['shipping']['shipping_city']['priority']    = 60;

    // 1.5. Đổi trường Xã/Phường (Address 2) thành thẻ select động (AC-BE-05)
    $fields['billing']['billing_address_2']['type']        = 'select';
    $fields['billing']['billing_address_2']['label']       = 'Xã / Phường / Thị trấn';
    $fields['billing']['billing_address_2']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['billing']['billing_address_2']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['billing']['billing_address_2']['class']       = array( 'form-row-wide' );
    $fields['billing']['billing_address_2']['required']    = true;
    $fields['billing']['billing_address_2']['priority']    = 70; // Sau District

    $fields['shipping']['shipping_address_2']['type']        = 'select';
    $fields['shipping']['shipping_address_2']['label']       = 'Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_address_2']['placeholder'] = 'Chọn Xã / Phường / Thị trấn';
    $fields['shipping']['shipping_address_2']['options']     = array( '' => 'Chọn Xã / Phường / Thị trấn' );
    $fields['shipping']['shipping_address_2']['class']       = array( 'form-row-wide' );
    $fields['shipping']['shipping_address_2']['required']    = true;
    $fields['shipping']['shipping_address_2']['priority']    = 70;

    // 1.6. Cấu hình địa chỉ nhà (Address 1)
    if ( isset( $fields['billing']['billing_address_1'] ) ) {
        $fields['billing']['billing_address_1']['label']       = 'Địa chỉ nhận hàng (Số nhà, tên đường...)';
        $fields['billing']['billing_address_1']['placeholder'] = 'Ví dụ: 123 Đường Lê Lợi';
        $fields['billing']['billing_address_1']['class']       = array( 'form-row-wide' );
        $fields['billing']['billing_address_1']['priority']    = 80;
    }
    if ( isset( $fields['shipping']['shipping_address_1'] ) ) {
        $fields['shipping']['shipping_address_1']['label']       = 'Địa chỉ nhận hàng (Số nhà, tên đường...)';
        $fields['shipping']['shipping_address_1']['placeholder'] = 'Ví dụ: 123 Đường Lê Lợi';
        $fields['shipping']['shipping_address_1']['class']       = array( 'form-row-wide' );
        $fields['shipping']['shipping_address_1']['priority']    = 80;
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
 * Đảm bảo các trường Tỉnh/Thành, Quận/Huyện, Xã/Phường hiển thị dọc, 100% width, và đúng thứ tự ưu tiên.
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
        'class'    => array( 'form-row-wide' ),
    );
    
    $locale['VN']['city'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Quận / Huyện',
        'priority' => 60,
        'class'    => array( 'form-row-wide', 'hkt-hidden-field' ),
    );
    
    $locale['VN']['address_2'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Xã / Phường / Thị trấn',
        'priority' => 70,
        'class'    => array( 'form-row-wide' ),
    );
    
    $locale['VN']['address_1'] = array(
        'required' => true,
        'hidden'   => false,
        'label'    => 'Địa chỉ nhận hàng (Số nhà, tên đường...)',
        'priority' => 80,
        'class'    => array( 'form-row-wide' ),
    );

    $locale['VN']['postcode'] = array(
        'required' => false,
        'hidden'   => true,
    );
    
    return $locale;
}

/**
 * 5b. Đồng bộ cấu trúc trường địa chỉ mặc định của WooCommerce
 */
add_filter( 'woocommerce_default_address_fields', 'dev_custom_default_address_fields' );

function dev_custom_default_address_fields( $fields ) {
    $fields['state']['priority']     = 50;
    $fields['state']['class']        = array( 'form-row-wide' );
    
    $fields['city']['priority']      = 60;
    $fields['city']['class']         = array( 'form-row-wide', 'hkt-hidden-field' );
    
    $fields['address_2']['priority'] = 70;
    $fields['address_2']['class']    = array( 'form-row-wide' );
    
    $fields['address_1']['priority'] = 80;
    $fields['address_1']['class']    = array( 'form-row-wide' );
    
    $fields['postcode']['required']  = false;
    $fields['postcode']['hidden']    = true;
    
    return $fields;
}

/**
 * 6. Tiêm dữ liệu Locale Việt Nam vào wc_address_i18n_params TRƯỚC KHI country-select.js xử lý
 * Đây là bước BẮT BUỘC để WooCommerce hiển thị trường Tỉnh/Thành phố cho Việt Nam đúng thứ tự và class.
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

            // Inject dữ liệu locale cho Việt Nam
            // Đảm bảo các trường được xếp dọc (form-row-wide) và đúng thứ tự ưu tiên 50 -> 60 -> 70 -> 80
            wc_address_i18n_params.locale['VN'] = {
                "state": {
                    "label": "T\u1ec9nh \/ Th\u00e0nh ph\u1ed1",
                    "required": true,
                    "hidden": false,
                    "priority": 50,
                    "class": ["form-row-wide"]
                },
                "city": {
                    "label": "Qu\u1eadn \/ Huy\u1ec7n",
                    "required": true,
                    "hidden": false,
                    "priority": 60,
                    "class": ["form-row-wide", "hkt-hidden-field"]
                },
                "address_2": {
                    "label": "X\u00e3 \/ Ph\u01b0\u1eddng \/ Th\u1ecb tr\u1ea5n",
                    "required": true,
                    "hidden": false,
                    "priority": 70,
                    "class": ["form-row-wide"]
                },
                "address_1": {
                    "label": "\u0110\u1ecba ch\u1ec9 nh\u1eadn h\u00e0ng (S\u1ed1 nh\u00e0, t\u00ean \u0111\u01b0\u1eddng...)",
                    "required": true,
                    "hidden": false,
                    "priority": 80,
                    "class": ["form-row-wide"]
                },
                "postcode": {
                    "required": false,
                    "hidden": true
                }
            };

            return true;
        }

        // Thử patch ngay lập tức
        if (!patchVNLocale()) {
            // Nếu chưa có wc_address_i18n_params, thử lại sau khi DOM load
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
        /* Ensure city and address_2 look like proper selects */
        #billing_city_field select,
        #billing_address_2_field select,
        #shipping_city_field select,
        #shipping_address_2_field select {
            width: 100%;
        }
    </style>
    <script>
    (function($) {
        'use strict';

        var apiBase = '/wp-json/hkt/v1';

        /**
         * Đảm bảo trường city/address_2 là <select> không phải <input>
         * WooCommerce country-select.js có thể chuyển về input text nên ta cần ép lại thành select
         */
        function devEnsureSelectField(fieldId, placeholder) {
            var field = $('#' + fieldId);
            if (!field.length) return;

            // Nếu đang là input text, tạo một <select> mới thay thế
            if (field.is('input')) {
                var val = field.val();
                var selectEl = $('<select>', {
                    id: fieldId,
                    name: fieldId,
                    class: field.attr('class') || ''
                }).append($('<option>', { value: '', text: placeholder }));
                
                if (val) {
                    selectEl.append($('<option>', { value: val, text: val, selected: true }));
                    selectEl.attr('data-pending-val', val);
                }
                field.replaceWith(selectEl);
            } else if (field.is('select')) {
                // Nếu là select nhưng chưa được đánh dấu loaded, lưu lại giá trị để chọn sau AJAX
                var val = field.val();
                if (val && !field.data('loaded')) {
                    field.attr('data-pending-val', val);
                }
            }
        }

        /**
         * Khởi tạo logic cho một nhóm địa chỉ (billing hoặc shipping)
         */
        function devInitAddressFields(prefix) {
            // Ép city và address_2 về dạng select nếu WooCommerce đã chuyển về input
            devEnsureSelectField(prefix + '_city', 'Chọn Quận / Huyện');
            devEnsureSelectField(prefix + '_address_2', 'Chọn Xã / Phường / Thị trấn');

            var provinceSelect = $('#' + prefix + '_state');
            var districtSelect = $('#' + prefix + '_city');
            var wardSelect     = $('#' + prefix + '_address_2');

            if (!provinceSelect.length) return;

            // Gỡ event cũ trước để tránh gắn trùng khi re-init
            provinceSelect.off('change.devVN select2:select.devVN');
            wardSelect.off('change.devVN');

            // Lắng nghe sự kiện chọn Tỉnh/Thành -> load thẳng Xã/Phường của cả Tỉnh đó
            provinceSelect.on('change.devVN select2:select.devVN', function() {
                var provinceId = $(this).val();

                // Reset các dropdown phụ
                wardSelect.empty().append($('<option>', { value: '', text: 'Chọn Xã / Phường / Thị trấn' }));
                wardSelect.data('loaded', false);
                if (wardSelect.hasClass('select2-hidden-accessible')) {
                    wardSelect.trigger('change.select2');
                }

                // District cũng được reset và ẩn
                districtSelect.empty().append($('<option>', { value: '', text: 'Chọn Quận / Huyện' }));
                if (districtSelect.hasClass('select2-hidden-accessible')) {
                    districtSelect.trigger('change.select2');
                }

                if (!provinceId) return;

                wardSelect.data('loading', true);
                $.ajax({
                    url: apiBase + '/wards',
                    method: 'GET',
                    data: { province_id: provinceId },
                    success: function(data) {
                        wardSelect.data('loading', false);
                        if (!$.isArray(data) || data.length === 0) return;
                        
                        var pendingWardVal = wardSelect.attr('data-pending-val') || wardSelect.val();
                        wardSelect.empty().append($('<option>', { value: '', text: 'Chọn Xã / Phường / Thị trấn' }));
                        
                        $.each(data, function(i, ward) {
                            var optionText = ward.ward_name;
                            var option = $('<option>', {
                                value: ward.ward_name,
                                text:  optionText,
                                'data-district': ward.district_name
                            });
                            if (ward.ward_name === pendingWardVal) {
                                option.attr('selected', 'selected');
                            }
                            wardSelect.append(option);
                        });
                        
                        wardSelect.removeAttr('data-pending-val');
                        wardSelect.data('loaded', true);
                        
                        if (wardSelect.hasClass('select2-hidden-accessible')) {
                            wardSelect.trigger('change.select2');
                        }

                        // Sau khi nạp xã/phường, nếu có xã/phường được chọn (do pending hoặc mặc định)
                        // thì tự động cập nhật Quận/Huyện tương ứng
                        if (wardSelect.val()) {
                            wardSelect.trigger('change.devVN');
                        }
                    },
                    error: function() {
                        wardSelect.data('loading', false);
                    }
                });
            });

            // Khi chọn Xã/Phường -> tự động cập nhật Quận/Huyện (City) ẩn bên dưới
            wardSelect.on('change.devVN', function() {
                var selectedOption = $(this).find('option:selected');
                var districtName   = selectedOption.data('district');

                if (districtName) {
                    // Cập nhật giá trị vào trường Quận/Huyện ẩn
                    districtSelect.empty().append($('<option>', {
                        value: districtName,
                        text:  districtName,
                        selected: true
                    }));
                    if (districtSelect.hasClass('select2-hidden-accessible')) {
                        districtSelect.trigger('change.select2');
                    } else {
                        districtSelect.trigger('change');
                    }
                } else {
                    districtSelect.empty().append($('<option>', { value: '', text: 'Chọn Quận / Huyện' }));
                    if (districtSelect.hasClass('select2-hidden-accessible')) {
                        districtSelect.trigger('change.select2');
                    } else {
                        districtSelect.trigger('change');
                    }
                }
            });

            // Khởi chạy load lần đầu nếu đã chọn Tỉnh/Thành
            if (provinceSelect.val() && !wardSelect.data('loaded') && !wardSelect.data('loading')) {
                // Đảm bảo pending district value cũng được xử lý nếu có
                var pendingDistrictVal = districtSelect.attr('data-pending-val') || districtSelect.val();
                if (pendingDistrictVal) {
                    districtSelect.removeAttr('data-pending-val');
                }
                provinceSelect.trigger('change.devVN');
            }
        }

        function devBootstrap() {
            devInitAddressFields('billing');
            devInitAddressFields('shipping');
        }

        $(document).ready(function() {
            devBootstrap();

            // Re-init sau khi WooCommerce cập nhật lại form (ví dụ: đổi country)
            $(document.body).on('updated_checkout', function() {
                devBootstrap();
            });

            // 6.3. Ẩn các dòng lỗi của từng ô nhập liệu ở danh sách thông báo chung phía trên
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
