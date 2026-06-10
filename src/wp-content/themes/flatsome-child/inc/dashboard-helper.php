<?php
/**
 * Custom Dashboard Helpers for HKT Fashion
 *
 * Implements marketing psychology, personalized recommendations, and AJAX reordering.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Get personalized product recommendations.
 * Prefers sale products, fills with latest products, and attaches social proof tags.
 *
 * @return array Array of WC_Product objects with marketing properties.
 */
function hkt_get_personalized_recommendations() {
    // 1. Fetch sale products
    $args = array(
        'limit'      => 4,
        'status'     => 'publish',
        'visibility' => 'catalog',
        'on_sale'    => true,
    );
    $products = wc_get_products( $args );

    // 2. If less than 4 sale products, fill with newest arrivals
    if ( count( $products ) < 4 ) {
        $exclude_ids = wp_list_pluck( $products, 'id' );
        $fill_args = array(
            'limit'      => 4 - count( $products ),
            'status'     => 'publish',
            'visibility' => 'catalog',
            'exclude'    => $exclude_ids,
            'orderby'    => 'date',
            'order'      => 'DESC',
        );
        $fill_products = wc_get_products( $fill_args );
        $products = array_merge( $products, $fill_products );
    }

    // Slice to ensure exactly 4 products max
    return array_slice( $products, 0, 4 );
}

/**
 * Generate a dynamic social proof/marketing tag for a product.
 *
 * @param WC_Product $product
 * @return string Marketing badge text.
 */
function hkt_get_product_marketing_tag( $product ) {
    if ( ! $product ) {
        return 'HOT';
    }

    // Scarcity if low stock
    if ( $product->is_managing_stock() ) {
        $stock = $product->get_stock_quantity();
        if ( $stock > 0 && $stock <= 3 ) {
            return "⚡ CHỈ CÒN {$stock} SẢN PHẨM";
        }
    }

    // Urgency/Scarcity if on sale
    if ( $product->is_on_sale() ) {
        return "🔥 GIẢM GIÁ SỐC";
    }

    // Recency (New arrival) - within 30 days
    $created_date = $product->get_date_created();
    if ( $created_date ) {
        $days_diff = ( time() - $created_date->getTimestamp() ) / ( 60 * 60 * 24 );
        if ( $days_diff <= 30 ) {
            return "✨ HÀNG MỚI VỀ";
        }
    }

    // Social Proof if best seller
    $total_sales = $product->get_meta( 'total_sales' );
    if ( $total_sales && intval( $total_sales ) > 10 ) {
        return "🔥 BÁN CHẠY NHẤT";
    }

    // Default tag
    return "✨ ĐỘC QUYỀN HKT";
}

/**
 * AJAX Handler for Quick Reorder.
 * Adds all products from an existing order to the WooCommerce cart and redirects to checkout.
 */
add_action( 'wp_ajax_hkt_ajax_reorder', 'hkt_ajax_reorder_callback' );
function hkt_ajax_reorder_callback() {
    // Verify Security
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.' ) );
    }

    check_ajax_referer( 'hkt_dashboard_nonce', 'security' );

    $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
    if ( ! $order_id ) {
        wp_send_json_error( array( 'message' => 'Mã đơn hàng không hợp lệ.' ) );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        wp_send_json_error( array( 'message' => 'Đơn hàng không tồn tại.' ) );
    }

    // Security check: Verify order belongs to the logged-in user
    if ( $order->get_customer_id() !== get_current_user_id() ) {
        wp_send_json_error( array( 'message' => 'Bạn không có quyền sao chép đơn hàng này.' ) );
    }

    // Clear cart first to avoid mixing with previous cart items (Frictionless checkout)
    WC()->cart->empty_cart();

    // Loop through order items and add them to the cart
    $added_any = false;
    foreach ( $order->get_items() as $item_id => $item ) {
        $product_id   = $item->get_product_id();
        $quantity     = $item->get_quantity();
        $variation_id = $item->get_variation_id();
        
        $variations = array();
        if ( $variation_id ) {
            $product_var = wc_get_product( $variation_id );
            if ( $product_var ) {
                $variations = $product_var->get_attributes();
            }
        }

        $result = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations );
        if ( $result ) {
            $added_any = true;
        }
    }

    if ( $added_any ) {
        wp_send_json_success( array(
            'redirect' => wc_get_checkout_url(),
            'message'  => 'Đã thêm sản phẩm vào giỏ hàng và đang chuyển tới thanh toán...'
        ) );
    } else {
        wp_send_json_error( array( 'message' => 'Không thể thêm sản phẩm nào từ đơn hàng này vào giỏ hàng.' ) );
    }
}
