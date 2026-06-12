<?php
/**
 * Script to create HKT Fashion coupon codes in WooCommerce database.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo "🎟️ Creating HKT coupons...\n";

$coupons = array(
    array(
        'code' => 'HKTNEW10',
        'type' => 'percent',
        'amount' => 10,
        'description' => 'Giảm 10% cho đơn hàng đầu tiên',
        'free_shipping' => 'no'
    ),
    array(
        'code' => 'HKTLOYAL50',
        'type' => 'fixed_cart',
        'amount' => 50000,
        'description' => 'Giảm 50.000đ cho khách hàng thân thiết',
        'free_shipping' => 'no'
    ),
    array(
        'code' => 'VIPFREESHIP',
        'type' => 'fixed_cart',
        'amount' => 0,
        'description' => 'Miễn phí vận chuyển cho đơn hàng VIP',
        'free_shipping' => 'yes'
    ),
    array(
        'code' => 'HKT15OFF',
        'type' => 'percent',
        'amount' => 15,
        'description' => 'Giảm 15% cho khách hàng mới',
        'free_shipping' => 'no'
    )
);

foreach ( $coupons as $coupon ) {
    // Check if coupon already exists
    $existing = get_page_by_title( $coupon['code'], OBJECT, 'shop_coupon' );
    if ( ! $existing ) {
        $coupon_id = wp_insert_post( array(
            'post_title'   => $coupon['code'],
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'shop_coupon',
            'post_author'  => 1
        ) );

        if ( ! is_wp_error( $coupon_id ) ) {
            update_post_meta( $coupon_id, 'discount_type', $coupon['type'] );
            update_post_meta( $coupon_id, 'coupon_amount', $coupon['amount'] );
            update_post_meta( $coupon_id, 'individual_use', 'no' );
            update_post_meta( $coupon_id, 'free_shipping', $coupon['free_shipping'] );
            update_post_meta( $coupon_id, 'description', $coupon['description'] );
            echo "  ✓ Created coupon: {$coupon['code']} (ID: {$coupon_id})\n";
        } else {
            echo "  ❌ Error creating: " . $coupon_id->get_error_message() . "\n";
        }
    } else {
        wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
        update_post_meta( $existing->ID, 'discount_type', $coupon['type'] );
        update_post_meta( $existing->ID, 'coupon_amount', $coupon['amount'] );
        update_post_meta( $existing->ID, 'free_shipping', $coupon['free_shipping'] );
        update_post_meta( $existing->ID, 'description', $coupon['description'] );
        echo "  - Coupon already exists: {$coupon['code']} (ID: {$existing->ID}). Updated config.\n";
    }
}

echo "🎉 Done creating coupons!\n";
