/**
 * HKT Fashion - Checkout Address Cascade Dropdowns (Province -> Ward)
 */
jQuery(document).ready(function($) {
    'use strict';

    var $stateSelect = $('#billing_state');
    var $wardSelect = $('#billing_address_2');

    if ($stateSelect.length && $wardSelect.length) {
        
        // Khởi tạo Select2 cho Phường / Xã nếu thư viện được nạp sẵn
        function initWardSelect2() {
            if ($.fn.select2) {
                $wardSelect.select2({
                    placeholder: 'Chọn Phường / Xã',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        // Lưu giá trị Phường/Xã ban đầu khi load trang (đối với user đã điền sẵn hoặc lưu địa chỉ)
        var initialWardVal = $wardSelect.val() || '';

        // Hàm tải danh sách xã phường của một Tỉnh/Thành
        function loadWards(provinceId, callback) {
            if (!provinceId) {
                $wardSelect.html('<option value="">Chọn Phường / Xã</option>').trigger('change');
                return;
            }

            // Disable tạm thời trường chọn xã phường khi đang tải
            $wardSelect.prop('disabled', true);

            $.ajax({
                url: hktCheckoutData.apiBaseUrl + '/all-wards',
                method: 'GET',
                data: { province_id: provinceId },
                success: function(data) {
                    $wardSelect.prop('disabled', false);
                    var html = '<option value="">Chọn Phường / Xã</option>';
                    if ($.isArray(data)) {
                        $.each(data, function(i, item) {
                            html += '<option value="' + item.ward_name + '" data-district="' + item.district_name + '">' + item.ward_name + '</option>';
                        });
                    }
                    $wardSelect.html(html);
                    
                    if (typeof callback === 'function') {
                        callback();
                    } else {
                        $wardSelect.trigger('change');
                    }
                },
                error: function() {
                    $wardSelect.prop('disabled', false);
                    $wardSelect.html('<option value="">Không thể tải danh sách. Thử lại</option>').trigger('change');
                }
            });
        }

        // Khởi tạo UI
        initWardSelect2();

        // Lắng nghe sự kiện khi thay đổi Tỉnh / Thành phố
        $stateSelect.on('change', function() {
            var provinceId = $(this).val();
            loadWards(provinceId);
        });

        // Lắng nghe sự kiện khi chọn Phường / Xã -> tự động cập nhật Quận/Huyện ẩn (city) và kích hoạt update_checkout
        $wardSelect.on('change', function() {
            var $selectedOption = $(this).find('option:selected');
            var districtName = $selectedOption.data('district') || '';
            var $cityInput = $('#billing_city');
            
            if ($cityInput.length) {
                if ($cityInput.is('select')) {
                    $cityInput.empty().append($('<option>', {
                        value: districtName,
                        text: districtName,
                        selected: true
                    })).val(districtName);
                } else {
                    $cityInput.val(districtName);
                }
            }
            $(document.body).trigger('update_checkout');
        });

        // Xử lý tự động load xã phường nếu trường Tỉnh đã được chọn sẵn từ đầu
        var currentProvince = $stateSelect.val();
        if (currentProvince) {
            loadWards(currentProvince, function() {
                if (initialWardVal) {
                    $wardSelect.val(initialWardVal).trigger('change');
                } else {
                    $wardSelect.trigger('change');
                }
            });
        }
    }
});
