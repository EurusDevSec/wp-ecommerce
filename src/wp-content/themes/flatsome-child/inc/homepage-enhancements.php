<?php
/**
 * Homepage Enhancements for HKT Fashion
 *
 * Provides:
 *  - Enqueue homepage-effects.js with localized ajaxUrl & nonce
 *  - AJAX Live Search endpoint (action: hkt_live_search)
 *  - Secondary image data injection into product card markup
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue JS only on the custom homepage template.
 */
add_action( 'wp_enqueue_scripts', 'hkt_enqueue_homepage_scripts' );
function hkt_enqueue_homepage_scripts() {
    wp_enqueue_script(
        'hkt-homepage-effects',
        get_stylesheet_directory_uri() . '/assets/js/homepage-effects.js',
        array( 'jquery' ),
        '1.0.0',
        true // load in footer
    );

    wp_localize_script( 'hkt-homepage-effects', 'hktHomepageData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'hkt_live_search_nonce' ),
    ) );
}

/**
 * Thay thế nút mua hàng ở danh sách sản phẩm (loop) thành nút "Xem chi tiết" trỏ về trang chi tiết sản phẩm.
 */
add_filter( 'woocommerce_loop_add_to_cart_link', 'hkt_loop_add_to_cart_to_detail_link', 20, 2 );
function hkt_loop_add_to_cart_to_detail_link( $html, $product ) {
    return sprintf(
        '<a href="%s" class="button product_type_%s">%s</a>',
        esc_url( $product->get_permalink() ),
        esc_attr( $product->get_type() ),
        esc_html__( 'Xem chi tiết', 'woocommerce' )
    );
}


/**
 * AJAX Live Search Handler.
 * Returns up to 6 matching products as JSON.
 *
 * Accepts: POST { action, query, nonce }
 * Returns: JSON [ { title, url, image, price } ... ]
 */
add_action( 'wp_ajax_hkt_live_search',        'hkt_live_search_handler' );
add_action( 'wp_ajax_nopriv_hkt_live_search', 'hkt_live_search_handler' );

function hkt_live_search_handler() {
    // Verify nonce
    if ( ! check_ajax_referer( 'hkt_live_search_nonce', 'nonce', false ) ) {
        wp_send_json_error( array( 'message' => 'Invalid nonce' ), 403 );
    }

    $query_term = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

    if ( strlen( $query_term ) < 3 ) {
        wp_send_json_success( array() );
    }

    // WC product search
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        's'              => $query_term,
        'orderby'        => 'relevance',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $product = wc_get_product( get_the_ID() );
            if ( ! $product || ! $product->is_visible() ) {
                continue;
            }

            // Image
            $image_url = '';
            $thumbnail_id = $product->get_image_id();
            if ( $thumbnail_id ) {
                $image_data = wp_get_attachment_image_src( $thumbnail_id, 'woocommerce_thumbnail' );
                $image_url  = $image_data ? $image_data[0] : '';
            }

            // Price
            $price_html = $product->get_price_html();
            $price_text = wp_strip_all_tags( $price_html );

            $results[] = array(
                'title' => get_the_title(),
                'url'   => get_permalink(),
                'image' => $image_url,
                'price' => $price_text,
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success( $results );
}


/**
 * Output the secondary image directly inside Flatsome's image wrapper
 * so that the CSS hover swap can work cleanly without JS.
 */
add_action( 'flatsome_woocommerce_shop_loop_images', 'hkt_output_secondary_image_in_card', 15 );
function hkt_output_secondary_image_in_card() {
    global $product;
    if ( ! $product ) {
        return;
    }

    $gallery_ids = $product->get_gallery_image_ids();
    if ( empty( $gallery_ids ) ) {
        return;
    }

    $secondary_data = wp_get_attachment_image_src( $gallery_ids[0], 'woocommerce_thumbnail' );
    if ( ! $secondary_data ) {
        return;
    }

    echo '<img src="' . esc_url( $secondary_data[0] ) . '" '
       . 'alt="' . esc_attr( $product->get_name() ) . '" '
       . 'class="secondary-image" '
       . 'loading="lazy" '
       . 'aria-hidden="true">';
}

// Remove Flatsome's default hover image hook to prevent duplicate output and conflicts
remove_action( 'flatsome_woocommerce_shop_loop_images', 'flatsome_woocommerce_get_alt_product_thumbnail', 11 );

