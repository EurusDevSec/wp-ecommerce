<?php
/**
 * Chức năng hiển thị Swatches, Bảng hướng dẫn chọn Size và Khối Cam kết Tin cậy (Trust Badges)
 * Chú thích tiếng Việt dễ hiểu, chuẩn WooCommerce.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Khối Cam kết Tin cậy (Trust Badges) dưới nút Mua Ngay (AC-FE-01, AC-FE-04)
 */
add_action( 'woocommerce_after_add_to_cart_form', 'dev_render_single_product_trust_badges', 15 );

function dev_render_single_product_trust_badges() {
    ?>
    <div class="dev-trust-badges-wrapper">
        <div class="dev-trust-badge-item">
            <div class="dev-badge-icon-wrapper">
                <svg class="dev-badge-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div class="dev-badge-info">
                <strong>Cam kết chính hãng 100%</strong>
                <span>Hoàn tiền gấp đôi nếu phát hiện giả</span>
            </div>
        </div>
        <div class="dev-trust-badge-item">
            <div class="dev-badge-icon-wrapper">
                <svg class="dev-badge-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
            </div>
            <div class="dev-badge-info">
                <strong>Đổi trả dễ dàng trong 7 ngày</strong>
                <span>Hỗ trợ đổi size tận nơi miễn phí</span>
            </div>
        </div>
        <div class="dev-trust-badge-item">
            <div class="dev-badge-icon-wrapper">
                <svg class="dev-badge-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="dev-badge-info">
                <strong>Giao hàng nhanh toàn quốc</strong>
                <span>Đồng giá ship 30k toàn quốc</span>
            </div>
        </div>
        <div class="dev-trust-badge-item">
            <div class="dev-badge-icon-wrapper">
                <svg class="dev-badge-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div class="dev-badge-info">
                <strong>Thanh toán VietQR động</strong>
                <span>Quét mã thanh toán cực nhanh</span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 2. Đăng ký Bảng hướng dẫn chọn Size (Size Guide Modal HTML)
 */
add_action( 'wp_footer', 'dev_render_size_guide_modal_html' );

function dev_render_size_guide_modal_html() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    <!-- Size Guide Modal Popup -->
    <div id="dev-size-guide-modal" class="dev-modal-overlay">
        <div class="dev-modal-content">
            <button class="dev-modal-close-btn" id="dev-close-size-guide">&times;</button>
            <h3 class="dev-modal-title">📏 BẢNG HƯỚNG DẪN CHỌN SIZE</h3>
            <p style="color:#777; font-size:13px; text-align:center; margin-bottom: 20px;">Thông số chuẩn để bạn chọn size đồ thời trang tối giản HKT Fashion</p>
            
            <div class="dev-modal-table-wrapper">
                <table class="dev-size-guide-table">
                    <thead>
                        <tr>
                            <th>Size</th>
                            <th>Chiều cao (cm)</th>
                            <th>Cân nặng (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>S</strong></td>
                            <td>150cm - 160cm</td>
                            <td>45kg - 52kg</td>
                        </tr>
                        <tr>
                            <td><strong>M</strong></td>
                            <td>160cm - 168cm</td>
                            <td>53kg - 60kg</td>
                        </tr>
                        <tr>
                            <td><strong>L</strong></td>
                            <td>168cm - 175cm</td>
                            <td>61kg - 70kg</td>
                        </tr>
                        <tr>
                            <td><strong>XL</strong></td>
                            <td>175cm - 182cm</td>
                            <td>71kg - 82kg</td>
                        </tr>
                        <tr>
                            <td><strong>XXL</strong></td>
                            <td>182cm - 190cm</td>
                            <td>83kg - 95kg</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="dev-modal-footer">
                * Lưu ý: Nếu bạn nằm giữa 2 khoảng size, hãy ưu tiên chọn size lớn hơn để có cảm giác mặc thoải mái rộng rãi.
            </div>
        </div>
    </div>
    <?php
}

/**
 * 3. Kịch bản jQuery tự động đổi dropdown mặc định thành Swatches trực quan (AC-FE-04)
 */
add_action( 'wp_footer', 'dev_product_swatches_script' );

function dev_product_swatches_script() {
    if ( ! is_product() ) {
        return;
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            var form = $('.variations_form');
            if (!form.length) return;

            // Bản đồ màu sắc HEX cho các swatch màu sắc (Hỗ trợ tiếng Anh & tiếng Việt)
            var colorMap = {
                'black': '#000000', 'den': '#000000', 'đen': '#000000',
                'white': '#ffffff', 'trang': '#ffffff', 'trắng': '#ffffff',
                'blue': '#3182ce', 'xanh duong': '#3182ce', 'xanh dương': '#3182ce',
                'green': '#38a169', 'xanh la': '#38a169', 'xanh lá': '#38a169',
                'red': '#e53e3e', 'do': '#e53e3e', 'đỏ': '#e53e3e',
                'yellow': '#ecc94b', 'vang': '#ecc94b', 'vàng': '#ecc94b',
                'gray': '#718096', 'xam': '#718096', 'xám': '#718096',
                'pink': '#ed64a6', 'hong': '#ed64a6', 'hồng': '#ed64a6',
                'orange': '#ed8936', 'cam': '#ed8936',
                'purple': '#9f7aea', 'tim': '#9f7aea', 'tím': '#9f7aea',
                'brown': '#744210', 'nau': '#744210', 'nâu': '#744210'
            };

            // 3.1. Duyệt qua từng dropdown chọn thuộc tính mặc định để biến đổi thành Swatches
            form.find('.variations select').each(function() {
                var select = $(this);
                var attributeName = select.attr('name'); // Ví dụ: attribute_pa_color
                var selectTd = select.closest('td.value');
                var labelTh = select.closest('tr').find('th.label');

                // Tạo container chứa các nút Swatch thay thế
                var swatchesContainer = $('<div class="dev-swatches-container" data-attribute="' + attributeName + '"></div>');
                selectTd.append(swatchesContainer);

                // Ẩn select dropdown mặc định đi
                select.hide();

                // Tạo các nút Swatch dựa trên các thẻ option của select
                select.find('option').each(function() {
                    var option = $(this);
                    var val = option.val();
                    var text = option.text();

                    if (!val) return; // Bỏ qua option rỗng (ví dụ: "Chọn màu sắc")

                    var swatchItem;

                    // A. Nếu là thuộc tính Màu sắc (pa_color) -> Tạo vòng tròn màu
                    if (attributeName.indexOf('pa_color') !== -1) {
                        var slugNormalized = val.toLowerCase().replace(/[^a-z0-9]/g, '');
                        var hex = colorMap[val.toLowerCase()] || colorMap[slugNormalized] || '#cbd5e0';
                        
                        swatchItem = $('<div class="dev-swatch-item dev-color-swatch" data-value="' + val + '" title="' + text + '"></div>');
                        var colorCircle = $('<span class="dev-swatch-color-circle" style="background-color: ' + hex + ';"></span>');
                        
                        // Nếu là màu trắng, tạo thêm border bên trong để dễ nhìn
                        if (hex === '#ffffff' || val.toLowerCase() === 'white' || val.toLowerCase() === 'trắng') {
                            colorCircle.addClass('white-border');
                        }
                        swatchItem.append(colorCircle);
                    } 
                    // B. Nếu là thuộc tính Kích thước (pa_size) -> Tạo ô vuông chữ
                    else if (attributeName.indexOf('pa_size') !== -1) {
                        swatchItem = $('<div class="dev-swatch-item dev-size-swatch" data-value="' + val + '"><span class="dev-swatch-text">' + text + '</span></div>');
                    } 
                    // C. Thuộc tính khác (nếu có) -> Hiển thị text thông thường
                    else {
                        swatchItem = $('<div class="dev-swatch-item dev-default-swatch" data-value="' + val + '"><span class="dev-swatch-text">' + text + '</span></div>');
                    }

                    // Sự kiện click chọn Swatch
                    swatchItem.on('click', function() {

                        if ($(this).hasClass('active')) {
                            // Click lại nút đang chọn -> Reset hủy chọn
                            $(this).removeClass('active');
                            select.val('').trigger('change');
                        } else {
                            swatchesContainer.find('.dev-swatch-item').removeClass('active');
                            $(this).addClass('active');
                            select.val(val).trigger('change');
                        }
                    });

                    swatchesContainer.append(swatchItem);
                });

                // 3.2. Chèn link "Bảng hướng dẫn chọn size" cạnh nhãn thuộc tính Size
                if (attributeName.indexOf('pa_size') !== -1) {
                    var sizeGuideLink = $('<a href="#" id="dev-open-size-guide-btn" class="dev-size-guide-link">📏 Hướng dẫn chọn size</a>');
                    labelTh.append(sizeGuideLink);
                }
            });

            // Hàm đồng bộ trạng thái active của các swatch dựa trên giá trị select thực tế
            function syncActiveSwatches() {
                form.find('.variations select').each(function() {
                    var select = $(this);
                    var val = select.val();
                    var container = form.find('.dev-swatches-container[data-attribute="' + select.attr('name') + '"]');
                    container.find('.dev-swatch-item').removeClass('active');
                    if (val) {
                        container.find('.dev-swatch-item').each(function() {
                            if (slugify($(this).attr('data-value')) === slugify(val)) {
                                $(this).addClass('active');
                            }
                        });
                    }
                });
            }

            // 3.3. Xử lý đồng bộ class hoạt động (active) khi WooCommerce reset form
            form.on('reset_data', function() {
                syncActiveSwatches();
                updateSwatchStates();
                $('.hkt-buy-now-button').addClass('disabled wc-variation-selection-needed');
            });

            // 3.4. Logic quản lý trạng thái HẾT HÀNG (Out Of Stock) theo tiêu chí AC-FE-04
            // WooCommerce lưu trữ thông tin tồn kho của các biến thể trong data-product_variations
            form.on('change', 'select', function() {
                syncActiveSwatches();
                updateSwatchStates();
            });

            // Hàm chuyển chuỗi tiếng Việt/tiếng Anh về dạng slug để so sánh không phân biệt hoa thường và dấu tiếng Việt
            function slugify(text) {
                if (text === undefined || text === null) return '';
                return text.toString().toLowerCase()
                    .replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/g, 'a')
                    .replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/g, 'e')
                    .replace(/í|ì|ỉ|ĩ|ị/g, 'i')
                    .replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/g, 'o')
                    .replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/g, 'u')
                    .replace(/ý|ỳ|ỷ|ỹ|ỵ/g, 'y')
                    .replace(/đ/g, 'd')
                    .replace(/[^a-z0-9]/g, '');
            }

            function updateSwatchStates() {
                var variations = form.data('product_variations');
                if (!variations) return;

                // Đọc trạng thái các thuộc tính đang được chọn hiện tại
                var currentSelections = {};
                form.find('.variations select').each(function() {
                    currentSelections[$(this).attr('name')] = $(this).val();
                });

                // Kiểm tra từng swatch
                form.find('.dev-swatches-container .dev-swatch-item').each(function() {
                    var swatch = $(this);
                    var container = swatch.parent();
                    var swatchAttr = container.data('attribute');
                    var swatchVal = swatch.data('value');

                    // Giả lập xem nếu chọn swatch này thì giá trị tuyển chọn sẽ thế nào
                    var simulatedSelections = $.extend({}, currentSelections);
                    simulatedSelections[swatchAttr] = swatchVal;

                    // Tìm xem có biến thể nào hợp lệ và CÒN HÀNG tương thích với simulatedSelections không
                    var isAvailableAndInStock = false;

                    $.each(variations, function(i, variation) {
                        var match = true;

                        // Kiểm tra xem biến thể có khớp với tất cả simulatedSelections không
                        $.each(variation.attributes, function(attrKey, attrVal) {
                            var selectedVal = simulatedSelections[attrKey];
                            
                            // Nếu biến thể có gán cụ thể cho thuộc tính này và selectedVal cũng có chọn cụ thể
                            if (attrVal !== '' && attrVal !== null && attrVal !== undefined && selectedVal !== '' && selectedVal !== undefined) {
                                if (slugify(attrVal) !== slugify(selectedVal)) {
                                    match = false;
                                    return false; // Break
                                }
                            }
                        });

                        // Nếu khớp thuộc tính, kiểm tra xem biến thể này có còn hàng không
                        if (match) {
                            // variation.variation_is_active cho biết biến thể đang hoạt động
                            // variation.is_in_stock cho biết trạng thái kho hàng
                            if (variation.variation_is_active && variation.is_in_stock) {
                                isAvailableAndInStock = true;
                                return false; // Break loop
                            }
                        }
                    });

                    // Nếu không có biến thể nào còn hàng cho cấu hình này -> Làm mờ và gạch chéo
                    if (!isAvailableAndInStock) {
                        swatch.addClass('disabled');
                    } else {
                        swatch.removeClass('disabled');
                    }
                });
            }

            // Đồng bộ class active và chạy kiểm tra tồn kho lần đầu tiên lúc load trang
            syncActiveSwatches();
            updateSwatchStates();

            // Đồng bộ trạng thái disabled của nút MUA NGAY
            form.on('show_variation', function(event, variation) {
                $('.hkt-buy-now-button').removeClass('disabled wc-variation-selection-needed');
            });
            form.on('hide_variation', function() {
                $('.hkt-buy-now-button').addClass('disabled wc-variation-selection-needed');
            });

            // Xử lý click nút MUA NGAY: gửi form và chuyển sang checkout
            $(document).on('click', '.hkt-buy-now-button', function(e) {
                e.preventDefault();
                if ($(this).hasClass('disabled') || $(this).hasClass('wc-variation-selection-needed')) {
                    return;
                }
                var buttonForm = $(this).closest('form');
                // Xóa input cũ nếu có
                buttonForm.find('input[name="buy_now"]').remove();
                // Gắn input buy_now = 1 để redirect ở backend
                buttonForm.append('<input type="hidden" name="buy_now" value="1">');
                // Submit form native
                buttonForm[0].submit();
            });

            // 3.5. Xử lý đóng/mở Size Guide Modal
            $(document).on('click', '#dev-open-size-guide-btn', function(e) {
                e.preventDefault();
                $('#dev-size-guide-modal').addClass('open');
                $('body').css('overflow', 'hidden');
            });

            $(document).on('click', '#dev-close-size-guide, #dev-size-guide-modal', function(e) {
                if (e.target === this || e.target.id === 'dev-close-size-guide') {
                    $('#dev-size-guide-modal').removeClass('open');
                    $('body').css('overflow', '');
                }
            });

            // --- Sticky Add to Cart Bar Sync Logic ---
            var stickyBar = $('#dev-sticky-add-to-cart');
            if (stickyBar.length) {
                var mainForm = $('.variations_form');
                var buyBtn = $('.hkt-buy-now-button');
                var showThreshold = buyBtn.length ? (buyBtn.offset().top + buyBtn.outerHeight()) : 400;

                $(window).on('scroll.devSticky', function() {
                    if ($(window).scrollTop() > showThreshold) {
                        stickyBar.addClass('visible');
                    } else {
                        stickyBar.removeClass('visible');
                    }
                });

                // Helper function to normalize attribute names (removing attribute_ prefix if present)
                function getBaseAttr(name) {
                    return name ? name.replace(/^attribute_/, '') : '';
                }

                // Sync selection: sticky dropdown -> main selects/swatches
                $('.dev-sticky-attr-select').on('change', function() {
                    var attrName = $(this).attr('data-attribute') || $(this).data('attribute');
                    var val = $(this).val();
                    var baseName = getBaseAttr(attrName);
                    
                    var mainSelect = mainForm.find('select[name="attribute_' + baseName + '"], select[name="' + baseName + '"]');
                    if (mainSelect.length) {
                        mainSelect.val(val).trigger('change');
                        
                        var swatchContainer = $('.dev-swatches-container[data-attribute="attribute_' + baseName + '"], .dev-swatches-container[data-attribute="' + baseName + '"]');
                        if (swatchContainer.length) {
                            swatchContainer.find('.dev-swatch-item').removeClass('active');
                            if (val) {
                                swatchContainer.find('.dev-swatch-item[data-value="' + val + '"]').addClass('active');
                            }
                        }
                    }
                });

                // Sync selection: main select changes -> update sticky dropdowns
                mainForm.on('change', 'select', function() {
                    var attrName = $(this).attr('name');
                    var val = $(this).val();
                    var baseName = getBaseAttr(attrName);
                    $('.dev-sticky-attr-select[data-attribute="' + baseName + '"]').val(val);
                });


                // Trigger buy now
                $('.dev-sticky-buy-now-btn').on('click', function(e) {
                    e.preventDefault();
                    var allSelected = true;
                    $('.dev-sticky-attr-select').each(function() {
                        if (!$(this).val()) {
                            allSelected = false;
                            return false;
                        }
                    });

                    if (!allSelected) {
                        $('html, body').animate({
                            scrollTop: mainForm.offset().top - 150
                        }, 500);
                        mainForm.addClass('shake-effect');
                        setTimeout(function() {
                            mainForm.removeClass('shake-effect');
                        }, 500);
                        return;
                    }

                    if (buyBtn.length) {
                        buyBtn.trigger('click');
                    }
                });
            }
        });
    </script>
    <?php
}

/**
 * 4. Hiển thị thanh Mua Hàng Dính (Sticky Add-to-cart Bar) khi cuộn trang (AC-FE-03)
 */
add_action( 'wp_footer', 'dev_render_sticky_add_to_cart_bar' );
function dev_render_sticky_add_to_cart_bar() {
    if ( ! is_product() ) {
        return;
    }
    global $product;
    if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
        return;
    }
    
    $title = $product->get_name();
    $price_html = $product->get_price_html();
    $image_id = $product->get_image_id();
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : wc_placeholder_img_src();
    
    ?>
    <div id="dev-sticky-add-to-cart" class="dev-sticky-add-to-cart-bar">
        <div class="dev-sticky-container">
            <div class="dev-sticky-info">
                <img src="<?php echo esc_url( $image_url ); ?>" class="dev-sticky-thumb" alt="<?php echo esc_attr( $title ); ?>">
                <div class="dev-sticky-title-price">
                    <h4 class="dev-sticky-title"><?php echo esc_html( $title ); ?></h4>
                    <span class="dev-sticky-price"><?php echo $price_html; ?></span>
                </div>
            </div>
            
            <div class="dev-sticky-actions">
                <?php if ( $product->is_type( 'variable' ) ) : 
                    $attributes = $product->get_variation_attributes();
                    foreach ( $attributes as $attribute_name => $options ) : 
                        $taxonomy = get_taxonomy( $attribute_name );
                        $label = $taxonomy ? $taxonomy->labels->singular_name : wc_attribute_label( $attribute_name );
                        ?>
                        <select class="dev-sticky-attr-select" data-attribute="<?php echo esc_attr( $attribute_name ); ?>">
                            <option value=""><?php echo esc_html( 'Chọn ' . $label ); ?></option>
                            <?php foreach ( $options as $option ) : 
                                $term = get_term_by( 'slug', $option, $attribute_name );
                                $name = $term ? $term->name : $option;
                                ?>
                                <option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endforeach; 
                endif; ?>
                <button type="button" class="dev-sticky-buy-now-btn">MUA NGAY</button>
            </div>
        </div>
    </div>
    <?php
}
