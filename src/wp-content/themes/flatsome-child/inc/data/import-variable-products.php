<?php
/**
 * Script import và đồng bộ sản phẩm biến thể từ Shopify JSON (icondenim.com)
 * Thiết kế cho HKT Fashion E-Commerce.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cấu hình tăng giới hạn bộ nhớ & thời gian chạy để sideload ảnh
ini_set('memory_limit', '2048M');
set_time_limit(0);

echo "🧹 Đang dọn dẹp các sản phẩm cũ...\n";
$existing_products = wc_get_products( array( 'limit' => -1 ) );
foreach ( $existing_products as $prod ) {
    $prod->delete( true );
}
echo "✓ Đã xóa tất cả sản phẩm cũ thành công.\n";

// Caching ảnh đã tải để tránh tải lại trùng lặp
global $sideloaded_images_cache;
$sideloaded_images_cache = array();

/**
 * Tải hình ảnh từ URL và đưa vào thư mục Media Library của WP (có hỗ trợ cache)
 */
function sideload_image_cached( $url, $post_id = 0 ) {
    global $sideloaded_images_cache;
    if ( empty( $url ) ) {
        return 0;
    }
    
    // Làm sạch URL, bỏ query string
    $clean_url = strtok($url, '?');
    
    if ( isset( $sideloaded_images_cache[$clean_url] ) ) {
        return $sideloaded_images_cache[$clean_url];
    }
    
    // Kiểm tra xem tên ảnh đã có trong thư viện chưa
    $filename = basename( $clean_url );
    global $wpdb;
    $attachment_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
        '%' . $filename
    ) );
    
    if ( $attachment_id ) {
        echo "  - Sử dụng ảnh đã tải sẵn: $filename (ID: $attachment_id)\n";
        $sideloaded_images_cache[$clean_url] = $attachment_id;
        return $attachment_id;
    }
    
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    echo "  - Đang tải mới ảnh từ CDN: $filename...\n";
    $id = media_sideload_image( $clean_url, $post_id, '', 'id' );
    if ( is_wp_error( $id ) ) {
        echo "    ❌ Lỗi tải ảnh: " . $id->get_error_message() . "\n";
        return 0;
    }
    
    $sideloaded_images_cache[$clean_url] = $id;
    return $id;
}

/**
 * Đăng ký thuộc tính toàn cục (Global Product Attribute Taxonomy)
 */
function get_or_create_attribute_taxonomy( $name, $slug ) {
    global $wpdb;
    
    // Kiểm tra trong bảng thuộc tính WooCommerce
    $attribute_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s",
        $slug
    ) );
    
    if ( ! $attribute_id ) {
        $attribute_id = wc_create_attribute( array(
            'name'         => $name,
            'slug'         => $slug,
            'type'         => 'select',
            'order_by'     => 'menu_order',
            'has_archives' => true,
        ) );
    }
    
    $taxonomy = wc_attribute_taxonomy_name( $slug ); // e.g. pa_color
    if ( ! taxonomy_exists( $taxonomy ) ) {
        register_taxonomy(
            $taxonomy,
            apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
            apply_filters( 'woocommerce_taxonomy_args_' . $taxonomy, array(
                'labels'       => array( 'name' => $name ),
                'hierarchical' => true,
                'show_ui'      => false,
                'query_var'    => true,
                'rewrite'      => false,
            ) )
        );
    }
    
    return $taxonomy;
}

/**
 * Đăng ký giá trị thuộc tính (Term)
 */
function get_or_create_term( $name, $taxonomy ) {
    $name = trim( $name );
    if ( empty( $name ) ) {
        return 0;
    }
    $term = get_term_by( 'name', $name, $taxonomy );
    if ( ! $term ) {
        $term = get_term_by( 'slug', sanitize_title( $name ), $taxonomy );
    }
    if ( ! $term ) {
        $result = wp_insert_term( $name, $taxonomy, array( 'slug' => sanitize_title( $name ) ) );
        if ( is_wp_error( $result ) ) {
            return 0;
        }
        return $result['term_id'];
    }
    return $term->term_id;
}

/**
 * Tạo/Cập nhật danh mục sản phẩm (Product Category) hỗ trợ danh mục cha
 */
function get_or_create_category( $name, $slug, $parent_slug = '' ) {
    $parent_id = 0;
    if ( ! empty( $parent_slug ) ) {
        $parent_term = get_term_by( 'slug', $parent_slug, 'product_cat' );
        if ( $parent_term ) {
            $parent_id = $parent_term->term_id;
        }
    }
    
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( ! $term ) {
        $result = wp_insert_term( $name, 'product_cat', array(
            'slug'   => $slug,
            'parent' => $parent_id
        ) );
        if ( is_wp_error( $result ) ) {
            return 0;
        }
        return $result['term_id'];
    }
    
    if ( $term->parent != $parent_id ) {
        wp_update_term( $term->term_id, 'product_cat', array( 'parent' => $parent_id ) );
    }
    
    return $term->term_id;
}

/**
 * Tự động phân loại danh mục dựa trên title và product_type của Shopify
 */
function detect_product_category( $title, $product_type, $default_cat ) {
    $search_str = mb_strtolower( $title . ' ' . $product_type, 'UTF-8' );
    
    if ( strpos( $search_str, 'thun' ) !== false || strpos( $search_str, 't-shirt' ) !== false || strpos( $search_str, 'tee' ) !== false ) {
        return 'ao-thun';
    }
    if ( strpos( $search_str, 'polo' ) !== false ) {
        return 'ao-polo';
    }
    if ( strpos( $search_str, 'sơ mi' ) !== false || strpos( $search_str, 'somi' ) !== false || strpos( $search_str, 'sơmi' ) !== false || strpos( $search_str, 'shirt' ) !== false ) {
        return 'ao-so-mi';
    }
    if ( strpos( $search_str, 'khoác' ) !== false || strpos( $search_str, 'jacket' ) !== false || strpos( $search_str, 'hoodie' ) !== false || strpos( $search_str, 'sweater' ) !== false || strpos( $search_str, 'cardigan' ) !== false ) {
        return 'ao-khoac';
    }
    if ( strpos( $search_str, 'jean' ) !== false ) {
        return 'quan-jean';
    }
    if ( strpos( $search_str, 'quần tây' ) !== false || strpos( $search_str, 'tây' ) !== false || strpos( $search_str, 'slacks' ) !== false || strpos( $search_str, 'trousers' ) !== false ) {
        return 'quan-tay';
    }
    if ( strpos( $search_str, 'short' ) !== false || strpos( $search_str, 'lửng' ) !== false ) {
        return 'quan-short';
    }
    if ( strpos( $search_str, 'giày' ) !== false || strpos( $search_str, 'sneaker' ) !== false || strpos( $search_str, 'giay' ) !== false || strpos( $search_str, 'shoes' ) !== false ) {
        return 'giay-nam';
    }
    if ( strpos( $search_str, 'sandal' ) !== false ) {
        return 'sandal-nam';
    }
    if ( strpos( $search_str, 'dép' ) !== false || strpos( $search_str, 'slide' ) !== false ) {
        return 'dep-nam';
    }
    if ( strpos( $search_str, 'balo' ) !== false || strpos( $search_str, 'backpack' ) !== false ) {
        return 'balo';
    }
    if ( strpos( $search_str, 'ví' ) !== false || strpos( $search_str, 'vi' ) !== false || strpos( $search_str, 'wallet' ) !== false ) {
        return 'vi-da';
    }
    if ( strpos( $search_str, 'túi' ) !== false || strpos( $search_str, 'bag' ) !== false || strpos( $search_str, 'crossbody' ) !== false ) {
        return 'tui';
    }
    if ( strpos( $search_str, 'nón' ) !== false || strpos( $search_str, 'mũ' ) !== false || strpos( $search_str, 'hat' ) !== false || strpos( $search_str, 'cap' ) !== false ) {
        return 'non';
    }
    if ( strpos( $search_str, 'thắt lưng' ) !== false || strpos( $search_str, 'dây nịt' ) !== false || strpos( $search_str, 'belt' ) !== false ) {
        return 'day-nit-da';
    }
    if ( strpos( $search_str, 'vớ' ) !== false || strpos( $search_str, 'tất' ) !== false || strpos( $search_str, 'sock' ) !== false ) {
        return 'tat-vo';
    }
    if ( strpos( $search_str, 'kính' ) !== false || strpos( $search_str, 'sunglasses' ) !== false || strpos( $search_str, 'mat-kinh' ) !== false ) {
        return 'mat-kinh';
    }
    
    // Phân loại mặc định dựa trên collection nguồn
    if ( $default_cat === 'best-seller' ) {
        if ( strpos( $search_str, 'áo' ) !== false ) {
            return 'ao-thun';
        }
        if ( strpos( $search_str, 'quần' ) !== false ) {
            return 'quan-jean';
        }
        return 'ao-thun'; // fallback tuyệt đối
    }
    
    return $default_cat;
}

// 1. Tạo cấu trúc danh mục HKT Fashion
$categories_structure = array(
    'ao'         => array( 'name' => 'Áo', 'parent' => '' ),
    'quan'       => array( 'name' => 'Quần', 'parent' => '' ),
    'balo-tui'   => array( 'name' => 'Balo - Túi', 'parent' => '' ),
    'giay'       => array( 'name' => 'Giày', 'parent' => '' ),
    'sandal-dep' => array( 'name' => 'Sandal - Dép', 'parent' => 'giay' ),
    'phu-kien'   => array( 'name' => 'Phụ kiện', 'parent' => '' ),
    
    'ao-thun'    => array( 'name' => 'Áo thun', 'parent' => 'ao' ),
    'ao-polo'    => array( 'name' => 'Áo polo', 'parent' => 'ao' ),
    'ao-so-mi'   => array( 'name' => 'Áo sơ mi', 'parent' => 'ao' ),
    'ao-khoac'   => array( 'name' => 'Áo khoác', 'parent' => 'ao' ),
    'quan-jean'  => array( 'name' => 'Quần jean', 'parent' => 'quan' ),
    'quan-tay'   => array( 'name' => 'Quần tây', 'parent' => 'quan' ),
    'quan-short' => array( 'name' => 'Quần short', 'parent' => 'quan' ),
    
    'balo'       => array( 'name' => 'Balo', 'parent' => 'balo-tui' ),
    'tui'        => array( 'name' => 'Túi', 'parent' => 'balo-tui' ),
    'vi-da'      => array( 'name' => 'Ví da', 'parent' => 'balo-tui' ),
    
    'giay-nam'   => array( 'name' => 'Giày nam', 'parent' => 'giay' ),
    'giay-nu'    => array( 'name' => 'Giày nữ', 'parent' => 'giay' ),
    'sandal-nam' => array( 'name' => 'Sandal', 'parent' => 'sandal-dep' ),
    'dep-nam'    => array( 'name' => 'Dép', 'parent' => 'sandal-dep' ),
    
    'non'        => array( 'name' => 'Nón', 'parent' => 'phu-kien' ),
    'day-nit-da' => array( 'name' => 'Dây nịt da', 'parent' => 'phu-kien' ),
    'tat-vo'     => array( 'name' => 'Tất – Vớ', 'parent' => 'phu-kien' ),
    'mat-kinh'   => array( 'name' => 'Mắt kính', 'parent' => 'phu-kien' )
);

echo "📦 Đang thiết lập cấu trúc danh mục...\n";
$cat_ids = array();
foreach ( $categories_structure as $slug => $info ) {
    $cat_ids[$slug] = get_or_create_category( $info['name'], $slug, $info['parent'] );
}
echo "✓ Đã thiết lập xong danh mục.\n";

// 2. Định nghĩa các collection cần quét từ icondenim.com
$collections = array(
    'ao-thun'       => array( 'url' => 'https://icondenim.com/collections/ao-thun/products.json?limit=8', 'cat' => 'ao-thun' ),
    'ao-polo'       => array( 'url' => 'https://icondenim.com/collections/ao-polo/products.json?limit=8', 'cat' => 'ao-polo' ),
    'ao-somi'       => array( 'url' => 'https://icondenim.com/collections/ao-somi/products.json?limit=8', 'cat' => 'ao-so-mi' ),
    'ao-khoac'      => array( 'url' => 'https://icondenim.com/collections/ao-khoac/products.json?limit=8', 'cat' => 'ao-khoac' ),
    'quan-jean'     => array( 'url' => 'https://icondenim.com/collections/quan-jean/products.json?limit=8', 'cat' => 'quan-jean' ),
    'quan-tay'      => array( 'url' => 'https://icondenim.com/collections/quan-tay/products.json?limit=15', 'cat' => 'quan-tay' ),
    'giay'          => array( 'url' => 'https://icondenim.com/collections/nhom-giay/products.json?limit=12', 'cat' => 'giay-nam' ),
    'tui-vi-balo'   => array( 'url' => 'https://icondenim.com/collections/tui-vi-balo/products.json?limit=12', 'cat' => 'tui' ),
    'non'           => array( 'url' => 'https://icondenim.com/collections/non/products.json?limit=12', 'cat' => 'non' ),
    'that-lung'     => array( 'url' => 'https://icondenim.com/collections/that-lung/products.json?limit=12', 'cat' => 'day-nit-da' ),
    'vo'            => array( 'url' => 'https://icondenim.com/collections/vo/products.json?limit=12', 'cat' => 'tat-vo' ),
    'mat-kinh'      => array( 'url' => 'https://icondenim.com/collections/mat-kinh/products.json?limit=12', 'cat' => 'mat-kinh' ),
    
    // Sản phẩm bán chạy (3 trang)
    'best-seller-p1' => array( 'url' => 'https://icondenim.com/collections/best-seller/products.json?page=1&limit=24', 'cat' => 'best-seller' ),
    'best-seller-p2' => array( 'url' => 'https://icondenim.com/collections/best-seller/products.json?page=2&limit=24', 'cat' => 'best-seller' ),
    'best-seller-p3' => array( 'url' => 'https://icondenim.com/collections/best-seller/products.json?page=3&limit=24', 'cat' => 'best-seller' )
);

$http_args = array(
    'headers' => array(
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ),
    'timeout' => 45
);

// Khởi tạo các thuộc tính toàn cục pa_color và pa_size
$color_taxonomy = get_or_create_attribute_taxonomy( 'Màu sắc', 'color' );
$size_taxonomy = get_or_create_attribute_taxonomy( 'Kích thước', 'size' );

$imported_count = 0;
$seen_titles = array(); // Tránh trùng sản phẩm nếu xuất hiện ở nhiều collection

foreach ( $collections as $slug_key => $col_info ) {
    echo "🌐 Đang tải danh sách sản phẩm từ collection: {$slug_key}...\n";
    $response = wp_remote_get( $col_info['url'], $http_args );
    
    if ( is_wp_error( $response ) ) {
        echo "  ❌ Lỗi kết nối HTTP: " . $response->get_error_message() . "\n";
        continue;
    }
    
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    
    if ( ! isset( $data['products'] ) || empty( $data['products'] ) ) {
        echo "  ⚠️ Không tìm thấy sản phẩm nào hoặc phản hồi không đúng cấu trúc.\n";
        continue;
    }
    
    $products = $data['products'];
    echo "  ✓ Tìm thấy " . count($products) . " sản phẩm. Bắt đầu xử lý nhập...\n";
    
    foreach ( $products as $shopify_prod ) {
        $title = trim( $shopify_prod['title'] );
        
        // Tránh trùng lặp tiêu đề sản phẩm
        if ( isset( $seen_titles[$title] ) ) {
            continue;
        }
        $seen_titles[$title] = true;
        
        // Phân loại danh mục
        $shopify_type = isset($shopify_prod['product_type']) ? $shopify_prod['product_type'] : '';
        $detected_slug = detect_product_category( $title, $shopify_type, $col_info['cat'] );
        
        echo "  👉 Nhập sản phẩm biến thể: {$title} (Phân loại: {$detected_slug})...\n";
        
        // 1. Sideload ảnh & xây dựng sơ đồ ánh xạ hình ảnh (giới hạn tối đa 4 ảnh để tiết kiệm tài nguyên)
        $image_mapping = array();
        $limit_images = array_slice( $shopify_prod['images'], 0, 4 );
        $main_image_id = 0;
        $gallery_ids = array();
        
        foreach ( $limit_images as $index => $img ) {
            $attachment_id = sideload_image_cached( $img['src'] );
            if ( $attachment_id ) {
                $image_mapping[$img['id']] = $attachment_id;
                if ( $index === 0 ) {
                    $main_image_id = $attachment_id;
                } else {
                    $gallery_ids[] = $attachment_id;
                }
            }
        }
        
        // 2. Xác định các chỉ số của Color và Size từ cấu trúc options của Shopify
        $color_option_index = -1;
        $size_option_index = -1;
        
        foreach ( $shopify_prod['options'] as $idx => $opt ) {
            $opt_name = mb_strtolower( $opt['name'], 'UTF-8' );
            if ( strpos( $opt_name, 'màu' ) !== false || strpos( $opt_name, 'color' ) !== false ) {
                $color_option_index = $idx;
            } elseif ( strpos( $opt_name, 'kích' ) !== false || strpos( $opt_name, 'size' ) !== false || strpos( $opt_name, 'cỡ' ) !== false ) {
                $size_option_index = $idx;
            }
        }
        
        $product_colors = array();
        $product_sizes = array();
        
        if ( $color_option_index !== -1 ) {
            foreach ( $shopify_prod['options'][$color_option_index]['values'] as $val ) {
                get_or_create_term( $val, $color_taxonomy );
                $product_colors[] = $val;
            }
        }
        
        if ( $size_option_index !== -1 ) {
            foreach ( $shopify_prod['options'][$size_option_index]['values'] as $val ) {
                get_or_create_term( $val, $size_taxonomy );
                $product_sizes[] = $val;
            }
        }
        
        // 3. Khởi tạo sản phẩm biến thể WC_Product_Variable
        $product = new WC_Product_Variable();
        $product->set_name( $title );
        $product->set_status( 'publish' );
        $product->set_description( $shopify_prod['body_html'] );
        
        // Gắn danh mục
        $product_cat_ids = array();
        if ( isset( $cat_ids[$detected_slug] ) ) {
            $product_cat_ids[] = $cat_ids[$detected_slug];
            // Thêm danh mục cha tương ứng
            $parent_slug = $categories_structure[$detected_slug]['parent'];
            if ( ! empty( $parent_slug ) && isset( $cat_ids[$parent_slug] ) ) {
                $product_cat_ids[] = $cat_ids[$parent_slug];
                // Thêm danh mục ông (ví dụ: sandal-nam -> sandal-dep -> giay)
                $grandparent_slug = $categories_structure[$parent_slug]['parent'];
                if ( ! empty( $grandparent_slug ) && isset( $cat_ids[$grandparent_slug] ) ) {
                    $product_cat_ids[] = $cat_ids[$grandparent_slug];
                }
            }
        }
        $product->set_category_ids( $product_cat_ids );
        
        // Gắn hình ảnh đại diện và thư viện ảnh
        if ( $main_image_id ) {
            $product->set_image_id( $main_image_id );
        }
        if ( ! empty( $gallery_ids ) ) {
            $product->set_gallery_image_ids( $gallery_ids );
        }
        
        // Cấu hình thuộc tính của sản phẩm
        $attributes = array();
        
        if ( ! empty( $product_colors ) ) {
            $attr_color = new WC_Product_Attribute();
            $attr_color->set_id( wc_attribute_taxonomy_id_by_name( $color_taxonomy ) );
            $attr_color->set_name( $color_taxonomy );
            $attr_color->set_options( $product_colors );
            $attr_color->set_position( 0 );
            $attr_color->set_visible( true );
            $attr_color->set_variation( true );
            $attributes[$color_taxonomy] = $attr_color;
        }
        
        if ( ! empty( $product_sizes ) ) {
            $attr_size = new WC_Product_Attribute();
            $attr_size->set_id( wc_attribute_taxonomy_id_by_name( $size_taxonomy ) );
            $attr_size->set_name( $size_taxonomy );
            $attr_size->set_options( $product_sizes );
            $attr_size->set_position( 1 );
            $attr_size->set_visible( true );
            $attr_size->set_variation( true );
            $attributes[$size_taxonomy] = $attr_size;
        }
        
        $product->set_attributes( $attributes );
        $product_id = $product->save();
        
        // 4. Tạo các biến thể WooCommerce (Variations) tương ứng
        if ( isset( $shopify_prod['variants'] ) && ! empty( $shopify_prod['variants'] ) ) {
            foreach ( $shopify_prod['variants'] as $shopify_var ) {
                $variant_color = '';
                $variant_size = '';
                
                $opt_values = array( $shopify_var['option1'], $shopify_var['option2'], $shopify_var['option3'] );
                
                if ( $color_option_index !== -1 ) {
                    $variant_color = $opt_values[$color_option_index];
                }
                if ( $size_option_index !== -1 ) {
                    $variant_size = $opt_values[$size_option_index];
                }
                
                $variation = new WC_Product_Variation();
                $variation->set_parent_id( $product_id );
                
                $variation_attribs = array();
                if ( ! empty( $variant_color ) ) {
                    $variation_attribs[$color_taxonomy] = sanitize_title( $variant_color );
                }
                if ( ! empty( $variant_size ) ) {
                    $variation_attribs[$size_taxonomy] = sanitize_title( $variant_size );
                }
                $variation->set_attributes( $variation_attribs );
                
                // Thiết lập giá bán
                $price = floatval( $shopify_var['price'] );
                $variation->set_regular_price( $price );
                
                // Xử lý giá khuyến mãi (Sale Price)
                if ( ! empty( $shopify_var['compare_at_price'] ) ) {
                    $compare_price = floatval( $shopify_var['compare_at_price'] );
                    if ( $compare_price > $price ) {
                        $variation->set_regular_price( $compare_price );
                        $variation->set_sale_price( $price );
                    }
                }
                
                // Thiết lập SKU
                $var_sku = $shopify_var['sku'];
                if ( empty( $var_sku ) ) {
                    $var_sku = sanitize_title( $shopify_prod['title'] ) . '-' . sanitize_title( $variant_color ) . '-' . sanitize_title( $variant_size );
                }
                $variation->set_sku( $var_sku );
                
                // Quản lý kho hàng của biến thể
                $variation->set_manage_stock( true );
                $variation->set_stock_quantity( rand( 5, 20 ) ); // Giả lập số lượng tồn kho ngẫu nhiên
                $variation->set_stock_status( 'instock' );
                
                // Gán ảnh riêng của biến thể (nếu khớp ảnh swatch của Shopify)
                if ( ! empty( $shopify_var['image_id'] ) && isset( $image_mapping[$shopify_var['image_id']] ) ) {
                    $variation->set_image_id( $image_mapping[$shopify_var['image_id']] );
                }
                
                $variation->save();
            }
        }
        
        // Đồng bộ khoảng giá cho sản phẩm cha
        WC_Product_Variable::sync( $product_id );
        
        $imported_count++;
        echo "  ✓ Đã hoàn tất nhập sản phẩm: {$title} (ID: {$product_id})\n";
    }
}

echo "🎉 ĐỒNG BỘ SẢN PHẨM HOÀN TẤT! Đã nhập thành công {$imported_count} sản phẩm biến thể thực tế.\n";
