<?php
/**
 * Script import và đồng bộ sản phẩm, danh mục mẫu giống ThoiTrang19
 * Không chứa sản phẩm đồ lót, nội y hay đồ ngủ.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo "🧹 Đang dọn dẹp các sản phẩm cũ...\n";
$existing_products = wc_get_products( array( 'limit' => -1 ) );
foreach ( $existing_products as $prod ) {
    $prod->delete( true );
}
echo "✓ Đã xóa tất cả sản phẩm cũ.\n";

// Sideload image helper
function sideload_image_from_url( $url, $post_id = 0 ) {
    if ( empty( $url ) ) {
        return 0;
    }
    
    $filename = basename( current( explode( '?', $url ) ) );
    global $wpdb;
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ) );
    
    if ( $attachment_id ) {
        echo "  - Sử dụng ảnh đã có sẵn: $filename (ID: $attachment_id)\n";
        return $attachment_id;
    }
    
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    echo "  - Đang tải ảnh: $url\n";
    $id = media_sideload_image( $url, $post_id, '', 'id' );
    if ( is_wp_error( $id ) ) {
        echo "    ❌ Lỗi tải ảnh: " . $id->get_error_message() . "\n";
        return 0;
    }
    return $id;
}

// 1. Tạo/đồng bộ danh mục sản phẩm
$categories_data = array(
    'ao' => array(
        'name' => 'Áo',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    'balo-tui' => array(
        'name' => 'Balo - Túi',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg'
    ),
    'giay' => array(
        'name' => 'Giày',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f53d7c7e-a5e7-6100-1aa2-00176e5a5977.jpg'
    ),
    'non' => array(
        'name' => 'Nón',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg'
    ),
    'quan' => array(
        'name' => 'Quần',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg'
    ),
    'sandal-dep' => array(
        'name' => 'Sandal - Dép',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/23431488-c80c-6700-c540-00179f9fc1ea-e1626332448674.jpg'
    ),
    'phu-kien' => array(
        'name' => 'Phụ kiện',
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/1f3ca095-e259-0c00-04d8-0017f18cfb8f.jpg'
    )
);

$cat_ids = array();
echo "📁 Đang tạo danh mục...\n";
foreach ( $categories_data as $slug => $data ) {
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( ! $term ) {
        $result = wp_insert_term( $data['name'], 'product_cat', array( 'slug' => $slug ) );
        if ( is_wp_error( $result ) ) {
            echo "  ❌ Không thể tạo danh mục $slug: " . $result->get_error_message() . "\n";
            continue;
        }
        $term_id = $result['term_id'];
    } else {
        $term_id = $term->term_id;
    }
    $cat_ids[$slug] = $term_id;
    
    // Tải và gán ảnh đại diện danh mục
    $attachment_id = sideload_image_from_url( $data['img'] );
    if ( $attachment_id ) {
        update_term_meta( $term_id, 'thumbnail_id', $attachment_id );
    }
    echo "✓ Danh mục: {$data['name']} (ID: $term_id)\n";
}

// 2. Định nghĩa các sản phẩm để tạo mới
$products_data = array(
    // Section 1: ĐANG GIẢM GIÁ (onsale: true)
    array(
        'name' => 'Balo Eva Nguyễn Bản',
        'categories' => array( 'balo-tui' ),
        'price' => '399000',
        'sale_price' => '390000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/banner-sidebar.jpg'
    ),
    array(
        'name' => 'Áo sơ mi nam tay lỡ ZT12-84',
        'categories' => array( 'ao' ),
        'price' => '560000',
        'sale_price' => '450000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    array(
        'name' => 'Túi xách tay thời trang cao cấp',
        'categories' => array( 'balo-tui' ),
        'price' => '250000',
        'sale_price' => '200000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg'
    ),
    array(
        'name' => 'Nón thời trang classic nam nữ',
        'categories' => array( 'non' ),
        'price' => '150000',
        'sale_price' => '120000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg'
    ),
    
    // Section 2: SẢN PHẨM BÁN CHẠY (featured: true)
    array(
        'name' => 'Áo thun Polo HKT năng động',
        'categories' => array( 'ao' ),
        'price' => '185000',
        'sale_price' => '',
        'featured' => true,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    array(
        'name' => 'Giày Sneaker HKT Classic trắng',
        'categories' => array( 'giay' ),
        'price' => '250000',
        'sale_price' => '',
        'featured' => true,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f53d7c7e-a5e7-6100-1aa2-00176e5a5977.jpg'
    ),
    array(
        'name' => 'Áo khoác Classic nam tính Anubis',
        'categories' => array( 'ao' ),
        'price' => '300000',
        'sale_price' => '280000',
        'featured' => true,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/1f3ca095-e259-0c00-04d8-0017f18cfb8f.jpg'
    ),
    array(
        'name' => 'Quần Short kaki nam thô cao cấp',
        'categories' => array( 'quan' ),
        'price' => '250000',
        'sale_price' => '',
        'featured' => true,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg'
    ),
    
    // Section 3: HÀNG MỚI VỀ (date: latest)
    array(
        'name' => 'Áo sơ mi nam tay lỡ ZT12-4',
        'categories' => array( 'ao' ),
        'price' => '150000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    array(
        'name' => 'Quần kaki dài Hàn Quốc ZP43-9',
        'categories' => array( 'quan' ),
        'price' => '560000',
        'sale_price' => '450000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg'
    ),
    array(
        'name' => 'Áo sơ mi không tay HKT ZT10-14',
        'categories' => array( 'ao' ),
        'price' => '125000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    array(
        'name' => 'Đầm tay lỡ thu đông dáng điệu ZD32-75',
        'categories' => array( 'ao' ),
        'price' => '100000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg'
    ),
    array(
        'name' => 'Chân váy dáng dài qua gối ZS52-36',
        'categories' => array( 'quan' ),
        'price' => '356000',
        'sale_price' => '285000',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg'
    ),
    array(
        'name' => 'Quần ngố lỡ ZP43-33 chất đũi mát',
        'categories' => array( 'quan' ),
        'price' => '150000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg'
    ),
    array(
        'name' => 'Áo khoác Classic ấm áp HKT',
        'categories' => array( 'ao' ),
        'price' => '380000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg'
    ),
    array(
        'name' => 'Túi đeo chéo thời trang nam nữ',
        'categories' => array( 'balo-tui' ),
        'price' => '249000',
        'sale_price' => '',
        'featured' => false,
        'img' => 'https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg'
    )
);

echo "📦 Đang tạo các sản phẩm mới...\n";
foreach ( $products_data as $p_data ) {
    $product = new WC_Product_Simple();
    $product->set_name( $p_data['name'] );
    $product->set_status( 'publish' );
    $product->set_regular_price( $p_data['price'] );
    if ( ! empty( $p_data['sale_price'] ) ) {
        $product->set_sale_price( $p_data['sale_price'] );
    }
    
    // Gán danh mục
    $category_ids = array();
    foreach ( $p_data['categories'] as $cat_slug ) {
        if ( isset( $cat_ids[$cat_slug] ) ) {
            $category_ids[] = $cat_ids[$cat_slug];
        }
    }
    $product->set_category_ids( $category_ids );
    
    // Sideload ảnh sản phẩm
    $attachment_id = sideload_image_from_url( $p_data['img'] );
    if ( $attachment_id ) {
        $product->set_image_id( $attachment_id );
    }
    
    // Đặt trạng thái featured
    if ( $p_data['featured'] ) {
        $product->set_featured( true );
    }
    
    $product_id = $product->save();
    echo "✓ Đã tạo sản phẩm: {$p_data['name']} (ID: $product_id)\n";
}

echo "🎉 ĐỒNG BỘ SẢN PHẨM HOÀN TẤT!\n";
