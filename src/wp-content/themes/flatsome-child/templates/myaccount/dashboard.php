<?php
/**
 * My Account Dashboard - HKT Fashion Premium Redesign
 *
 * Overrides the standard WooCommerce My Account dashboard with an conversion-oriented
 * user interface applying scarcity, social proof, and frictionless reordering.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Query 3 recent orders
$recent_orders = wc_get_orders( array(
    'customer' => $user_id,
    'limit'    => 3,
    'orderby'  => 'date',
    'order'    => 'DESC',
) );

$total_orders = wc_get_customer_order_count( $user_id );

// Get recommendations
$recommendations = function_exists( 'hkt_get_personalized_recommendations' ) ? hkt_get_personalized_recommendations() : array();
?>

<style>
/* Premium Dashboard CSS Custom Styles */
.hkt-dashboard-wrapper {
    font-family: 'Lato', sans-serif;
    color: #2b2b2b;
    margin-top: 10px;
}

/* Glassmorphism Welcome Hero */
.hkt-dashboard-hero {
    background: linear-gradient(135deg, #1a1a1a 0%, #3a3a3a 100%);
    color: #ffffff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 35px;
    position: relative;
    overflow: hidden;
}

.hkt-dashboard-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(247, 116, 38, 0.15) 0%, transparent 70%);
    pointer-events: none;
}

.hkt-hero-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.hkt-hero-avatar img {
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.15);
    width: 70px;
    height: 70px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.hkt-hero-welcome h2 {
    color: #ffffff !important;
    font-size: 24px !important;
    margin: 0 0 5px 0 !important;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.hkt-hero-welcome p {
    color: #cccccc !important;
    font-size: 13.5px !important;
    margin: 0 !important;
}

.hkt-hero-welcome p a {
    color: #f77426 !important;
    font-weight: 600;
    text-decoration: underline;
}

.hkt-hero-stats {
    display: flex;
    gap: 20px;
}

.hkt-stat-box {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 12px 20px;
    text-align: center;
    min-width: 90px;
    transition: transform 0.25s ease;
}

.hkt-stat-box:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.12);
}

.hkt-stat-num {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #f77426;
    margin-bottom: 2px;
}

.hkt-stat-label {
    display: block;
    font-size: 11px;
    color: #dddddd;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Coupon Wallet section (Scarcity & Urgency) */
.hkt-dashboard-section {
    margin-bottom: 40px;
}

.hkt-section-header {
    border-bottom: 2px solid #ececec;
    margin-bottom: 25px;
    padding-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.hkt-section-header h3 {
    margin: 0 !important;
    font-size: 18px !important;
    text-transform: uppercase;
    font-weight: 700;
    color: #1a1a1a;
    position: relative;
}

.hkt-section-header h3::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 60px;
    height: 2px;
    background-color: #f77426;
}

.hkt-coupon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.hkt-coupon-card {
    background: #ffffff;
    border: 1px solid #eaeaea;
    border-left: 5px solid #f77426;
    border-radius: 8px;
    padding: 18px 20px;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    transition: all 0.3s ease;
}

.hkt-coupon-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.hkt-coupon-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #fff0eb;
    color: #f77426;
    font-size: 9px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.hkt-coupon-badge.alert {
    background-color: #ffebeb;
    color: #cc0000;
}

.hkt-coupon-value {
    font-size: 20px;
    font-weight: 800;
    color: #1a1a1a;
    margin-top: 5px;
    margin-bottom: 5px;
}

.hkt-coupon-desc {
    font-size: 12px;
    color: #666;
    margin-bottom: 15px;
}

.hkt-coupon-action {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fafafa;
    border-radius: 4px;
    padding: 8px 12px;
    border: 1px dashed #ddd;
}

.hkt-coupon-code {
    font-family: monospace;
    font-size: 14px;
    font-weight: 700;
    color: #1a1a1a;
}

.hkt-copy-coupon-btn {
    background: #1a1a1a;
    color: #ffffff;
    border: none;
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    outline: none;
}

.hkt-copy-coupon-btn:hover {
    background: #f77426;
}

.hkt-copy-coupon-btn.copied {
    background: #7a9c59;
}

/* Recent Orders & Reorder Section */
.hkt-order-card {
    background: #ffffff;
    border: 1px solid #eaeaea;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.01);
    transition: all 0.25s ease;
}

.hkt-order-card:hover {
    box-shadow: 0 6px 15px rgba(0,0,0,0.03);
}

.hkt-order-meta {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f2f2f2;
    padding-bottom: 12px;
    margin-bottom: 12px;
    gap: 10px;
}

.hkt-order-info {
    font-size: 13.5px;
}

.hkt-order-info strong {
    color: #1a1a1a;
}

.hkt-order-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.hkt-order-badge.completed { background: #eaf5e1; color: #588f27; }
.hkt-order-badge.processing { background: #e6f3ff; color: #1e70bf; }
.hkt-order-badge.on-hold { background: #fff4e5; color: #b26a00; }
.hkt-order-badge.pending { background: #f2f2f2; color: #666666; }

.hkt-order-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.hkt-order-price {
    font-size: 15px;
}

.hkt-order-price strong {
    font-size: 18px;
    color: #f77426;
}

.hkt-reorder-btn {
    background-color: #1a1a1a;
    color: #ffffff;
    border: 1px solid #1a1a1a;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.hkt-reorder-btn:hover {
    background-color: #f77426;
    border-color: #f77426;
    color: #ffffff;
}

/* Recommended Products Grid */
.hkt-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.hkt-prod-card {
    background: #ffffff;
    border: 1px solid #eaeaea;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.hkt-prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(247, 116, 38, 0.15);
}

.hkt-prod-tag {
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: #f77426;
    color: #ffffff;
    font-size: 9px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 4px;
    z-index: 10;
    box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    letter-spacing: 0.5px;
}

.hkt-prod-img-link {
    display: block;
    width: 100%;
    aspect-ratio: 1/1;
    overflow: hidden;
    background-color: #fcfcfc;
}

.hkt-prod-img-link img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.hkt-prod-card:hover .hkt-prod-img-link img {
    transform: scale(1.06);
}

.hkt-prod-info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.hkt-prod-title {
    font-size: 13.5px;
    font-weight: 700;
    margin: 0 0 8px 0;
    line-height: 1.4;
    height: 38px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.hkt-prod-title a {
    color: #1a1a1a !important;
    text-decoration: none;
}

.hkt-prod-title a:hover {
    color: #f77426 !important;
}

.hkt-prod-price {
    font-size: 14px;
    font-weight: 700;
    color: #f77426;
    margin-bottom: 12px;
    margin-top: auto;
}

.hkt-prod-price del {
    color: #999;
    font-weight: 400;
    font-size: 12px;
    margin-right: 5px;
}

.hkt-prod-price ins {
    text-decoration: none;
}

.hkt-prod-btn {
    background-color: transparent;
    color: #1a1a1a;
    border: 1px solid #1a1a1a;
    text-align: center;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 4px;
    display: block;
    text-decoration: none !important;
    transition: all 0.25s ease;
}

.hkt-prod-btn:hover {
    background-color: #1a1a1a;
    color: #ffffff !important;
}

/* Spinner Animation */
.hkt-spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #ffffff;
    animation: hkt-spin 0.8s ease-in-out infinite;
}

@keyframes hkt-spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .hkt-dashboard-hero {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
    }
    .hkt-hero-stats {
        width: 100%;
        justify-content: space-between;
    }
    .hkt-stat-box {
        flex: 1;
        padding: 8px 10px;
    }
}
</style>

<div class="hkt-dashboard-wrapper">
    
    <!-- Welcome Hero Banner -->
    <div class="hkt-dashboard-hero">
        <div class="hkt-hero-left">
            <div class="hkt-hero-avatar">
                <?php echo get_avatar( $user_id, 70 ); ?>
            </div>
            <div class="hkt-hero-welcome">
                <h2>Chào mừng trở lại, <?php echo esc_html( $current_user->display_name ); ?>! ✨</h2>
                <p>Từ bảng điều khiển của bạn, bạn có thể dễ dàng quản lý <a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">đơn hàng gần đây</a>, chỉnh sửa <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>">địa chỉ thanh toán</a> và <a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-account' ) ); ?>">thông tin mật khẩu tài khoản</a>.</p>
            </div>
        </div>
        <div class="hkt-hero-stats">
            <div class="hkt-stat-box">
                <span class="hkt-stat-num"><?php echo esc_html( $total_orders ); ?></span>
                <span class="hkt-stat-label">Đơn hàng</span>
            </div>
            <div class="hkt-stat-box">
                <span class="hkt-stat-num">3</span>
                <span class="hkt-stat-label">Vouchers</span>
            </div>
            <div class="hkt-stat-box">
                <span class="hkt-stat-num">1%</span>
                <span class="hkt-stat-label">Tích điểm</span>
            </div>
        </div>
    </div>

    <!-- Exclusive Coupon Wallet (Urgency & Scarcity) -->
    <div class="hkt-dashboard-section">
        <div class="hkt-section-header">
            <h3>Ví Voucher Khẩn Cấp Của Bạn</h3>
        </div>
        <div class="hkt-coupon-grid">
            
            <!-- Voucher 1 -->
            <div class="hkt-coupon-card">
                <span class="hkt-coupon-badge">Chào mừng</span>
                <div class="hkt-coupon-value">GIẢM 10%</div>
                <div class="hkt-coupon-desc">Áp dụng cho mọi đơn hàng mới tại HKT Fashion. Không giới hạn chi tiêu.</div>
                <div class="hkt-coupon-action">
                    <span class="hkt-coupon-code">HKTNEW10</span>
                    <button class="hkt-copy-coupon-btn" data-coupon-code="HKTNEW10">Sao chép</button>
                </div>
            </div>

            <!-- Voucher 2 -->
            <div class="hkt-coupon-card">
                <span class="hkt-coupon-badge alert">SẮP HẾT HẠN - CÒN 12H</span>
                <div class="hkt-coupon-value">GIẢM 50K</div>
                <div class="hkt-coupon-desc">Đơn hàng tối thiểu từ 500k. Đang ở ví ưu đãi khẩn cấp của bạn.</div>
                <div class="hkt-coupon-action">
                    <span class="hkt-coupon-code">HKTLOYAL50</span>
                    <button class="hkt-copy-coupon-btn" data-coupon-code="HKTLOYAL50">Sao chép</button>
                </div>
            </div>

            <!-- Voucher 3 -->
            <div class="hkt-coupon-card">
                <span class="hkt-coupon-badge">Ưu đãi giới hạn</span>
                <div class="hkt-coupon-value">FREESHIP TOÀN QUỐC</div>
                <div class="hkt-coupon-desc">Miễn phí vận chuyển cho đơn hàng tiếp theo. Số lượng sử dụng có hạn.</div>
                <div class="hkt-coupon-action">
                    <span class="hkt-coupon-code">VIPFREESHIP</span>
                    <button class="hkt-copy-coupon-btn" data-coupon-code="VIPFREESHIP">Sao chép</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Recent Orders (Frictionless reordering) -->
    <div class="hkt-dashboard-section">
        <div class="hkt-section-header">
            <h3>Đơn Hàng Gần Đây</h3>
            <?php if ( $total_orders > 3 ) : ?>
                <a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>" class="view-all-orders" style="font-size: 13px; color: #f77426; font-weight: 700;">Xem tất cả ></a>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $recent_orders ) ) : ?>
            <div class="hkt-orders-list">
                <?php foreach ( $recent_orders as $order ) : 
                    $order_status = $order->get_status();
                    $status_label = wc_get_order_status_name( $order_status );
                    $formatted_total = $order->get_formatted_order_total();
                    $item_count = $order->get_item_count();
                    ?>
                    <div class="hkt-order-card">
                        <div class="hkt-order-meta">
                            <div class="hkt-order-info">
                                Đơn hàng <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> đặt ngày <strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
                            </div>
                            <span class="hkt-order-badge <?php echo esc_attr( $order_status ); ?>">
                                <?php echo esc_html( $status_label ); ?>
                            </span>
                        </div>
                        <div class="hkt-order-details">
                            <div class="hkt-order-price">
                                Tổng cộng: <strong><?php echo wp_kses_post( $formatted_total ); ?></strong> cho <?php echo esc_html( $item_count ); ?> sản phẩm
                            </div>
                            <button class="hkt-reorder-btn" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
                                Mua lại đơn này
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="hkt-no-orders" style="text-align: center; padding: 30px; border: 1px dashed #eaeaea; border-radius: 8px; background: #fafafa;">
                <p style="margin-bottom: 15px;">Bạn chưa thực hiện bất kỳ đơn hàng nào.</p>
                <a href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>" class="button" style="background-color: #f77426; border-color: #f77426; border-radius: 4px; font-weight: 700;">Bắt đầu mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recommended Products (Social Proof & Personalization) -->
    <?php if ( ! empty( $recommendations ) ) : ?>
        <div class="hkt-dashboard-section">
            <div class="hkt-section-header">
                <h3>Được Đề Xuất Riêng Cho Bạn</h3>
            </div>
            <div class="hkt-products-grid">
                <?php foreach ( $recommendations as $product ) : 
                    $product_id = $product->get_id();
                    $product_link = get_permalink( $product_id );
                    $tag_label = function_exists( 'hkt_get_product_marketing_tag' ) ? hkt_get_product_marketing_tag( $product ) : 'HOT';
                    ?>
                    <div class="hkt-prod-card">
                        <span class="hkt-prod-tag"><?php echo esc_html( $tag_label ); ?></span>
                        <a href="<?php echo esc_url( $product_link ); ?>" class="hkt-prod-img-link">
                            <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
                        </a>
                        <div class="hkt-prod-info">
                            <h4 class="hkt-prod-title">
                                <a href="<?php echo esc_url( $product_link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
                            </h4>
                            <div class="hkt-prod-price">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <a href="<?php echo esc_url( $product_link ); ?>" class="hkt-prod-btn">
                                Xem chi tiết
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
jQuery(document).ready(function($) {
    // AJAX Quick Reorder handler
    $('.hkt-reorder-btn').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var orderId = btn.data('order-id');
        if (btn.hasClass('loading')) return;

        btn.addClass('loading').html('<span class="hkt-spinner"></span> Đang tải...');

        $.post(window.ajaxurl || '/wp-admin/admin-ajax.php', {
            action: 'hkt_ajax_reorder',
            order_id: orderId,
            security: '<?php echo wp_create_nonce("hkt_dashboard_nonce"); ?>'
        }, function(res) {
            if (res.success) {
                window.location.href = res.redirect;
            } else {
                alert(res.data.message || 'Có lỗi xảy ra.');
                btn.removeClass('loading').text('Mua lại đơn này');
            }
        }).fail(function() {
            alert('Kết nối máy chủ thất bại.');
            btn.removeClass('loading').text('Mua lại đơn này');
        });
    });

    // Copy Voucher Code handler
    $('.hkt-copy-coupon-btn').on('click', function() {
        var btn = $(this);
        var code = btn.data('coupon-code');
        
        // Copy to clipboard
        navigator.clipboard.writeText(code).then(function() {
            var originalText = btn.text();
            btn.addClass('copied').text('Đã chép! ✓');
            setTimeout(function() {
                btn.removeClass('copied').text(originalText);
            }, 2000);
        }).catch(function(err) {
            // Fallback for older browsers
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(code).select();
            document.execCommand('copy');
            tempInput.remove();
            
            var originalText = btn.text();
            btn.addClass('copied').text('Đã chép! ✓');
            setTimeout(function() {
                btn.removeClass('copied').text(originalText);
            }, 2000);
        });
    });
});
</script>
