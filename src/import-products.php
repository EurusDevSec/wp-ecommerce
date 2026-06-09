<?php
/**
 * Script import và đồng bộ sản phẩm từ file CSV
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CSV_FILE_PATH', __DIR__ . '/woocommerce-dummy-products-2026-06-07.csv' );

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

// Helper lấy hoặc tạo term
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

// 1. Khởi tạo danh mục gốc tiếng Việt
$default_categories = array(
    'ao' => 'Áo',
    'quan' => 'Quần',
    'balo-tui' => 'Balo - Túi',
    'giay' => 'Giày',
    'non' => 'Nón',
    'sandal-dep' => 'Sandal - Dép',
    'phu-kien' => 'Phụ kiện'
);

$cat_ids = array();
foreach ( $default_categories as $slug => $name ) {
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( ! $term ) {
        $result = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
        if ( ! is_wp_error( $result ) ) {
            $cat_ids[$slug] = $result['term_id'];
        }
    } else {
        $cat_ids[$slug] = $term->term_id;
    }
}

// Ánh xạ danh mục con tiếng Anh sang danh mục chính tiếng Việt
$category_mapping = array(
    'Shirts' => 'ao',
    'Knitwear' => 'ao',
    'Outerwear' => 'ao',
    'Trench Coats' => 'ao',
    'Wool Overcoats' => 'ao',
    'Puffer Jackets' => 'ao',
    'Denim Jackets' => 'ao',
    'Leather Jackets' => 'ao',
    'Sports Tops' => 'ao',
    'Trousers' => 'quan',
    'Leggings' => 'quan'
);

// 2. Mở file CSV và import
if ( ! file_exists( CSV_FILE_PATH ) ) {
    echo "❌ Không tìm thấy file CSV tại: " . CSV_FILE_PATH . "\n";
    exit;
}

$file = fopen( CSV_FILE_PATH, 'r' );
if ( ! $file ) {
    echo "❌ Không thể mở file CSV.\n";
    exit;
}

// Đọc dòng đầu tiên (Header)
$header = fgetcsv( $file );
if ( ! $header ) {
    echo "❌ File CSV rỗng.\n";
    fclose( $file );
    exit;
}

$header_map = array_flip( $header );

echo "📦 Đang import sản phẩm từ file CSV...\n";
$count = 0;

while ( ( $row = fgetcsv( $file ) ) !== false ) {
    // Bỏ qua nếu dòng rỗng hoặc không có đủ dữ liệu
    if ( count( $row ) < count( $header_map ) ) {
        continue;
    }

    $type = isset( $header_map['Type'] ) ? $row[$header_map['Type']] : 'simple';
    $sku = isset( $header_map['SKU'] ) ? $row[$header_map['SKU']] : '';
    $name = isset( $header_map['Name'] ) ? $row[$header_map['Name']] : '';
    $published = isset( $header_map['Published'] ) ? $row[$header_map['Published']] : '1';
    $featured = isset( $header_map['Is featured?'] ) ? $row[$header_map['Is featured?']] : '0';
    $visibility = isset( $header_map['Visibility in catalog'] ) ? $row[$header_map['Visibility in catalog']] : 'visible';
    $short_desc = isset( $header_map['Short description'] ) ? $row[$header_map['Short description']] : '';
    $description = isset( $header_map['Description'] ) ? $row[$header_map['Description']] : '';
    $in_stock = isset( $header_map['In stock?'] ) ? $row[$header_map['In stock?']] : '1';
    $stock = isset( $header_map['Stock'] ) ? $row[$header_map['Stock']] : '';
    $sale_price = isset( $header_map['Sale price'] ) ? $row[$header_map['Sale price']] : '';
    $regular_price = isset( $header_map['Regular price'] ) ? $row[$header_map['Regular price']] : '';
    $categories_raw = isset( $header_map['Categories'] ) ? $row[$header_map['Categories']] : '';
    $tags_raw = isset( $header_map['Tags'] ) ? $row[$header_map['Tags']] : '';
    $images_raw = isset( $header_map['Images'] ) ? $row[$header_map['Images']] : '';

    if ( empty( $name ) ) {
        continue;
    }

    echo "👉 Đang tạo sản phẩm: $name...\n";

    $product = new WC_Product_Simple();
    $product->set_name( $name );
    $product->set_sku( $sku );
    $product->set_status( $published == '1' ? 'publish' : 'draft' );
    $product->set_catalog_visibility( $visibility );
    
    // Đặt giá
    $product->set_regular_price( $regular_price );
    if ( $sale_price !== '' ) {
        $product->set_sale_price( $sale_price );
    }

    // Đặt kho hàng
    if ( $stock !== '' ) {
        $product->set_manage_stock( true );
        $product->set_stock_quantity( intval( $stock ) );
    } else {
        $product->set_manage_stock( false );
        $product->set_stock_status( $in_stock == '1' ? 'instock' : 'outofstock' );
    }

    // Mô tả
    $product->set_short_description( $short_desc );
    $product->set_description( $description );

    // Gắn nhãn featured
    if ( $featured == '1' ) {
        $product->set_featured( true );
    }

    // Xử lý danh mục
    $product_cat_ids = array();
    if ( ! empty( $categories_raw ) ) {
        $csv_categories = array_map( 'trim', explode( ',', $categories_raw ) );
        foreach ( $csv_categories as $cat_name ) {
            $cat_id = get_or_create_term( $cat_name, 'product_cat' );
            if ( $cat_id ) {
                $product_cat_ids[] = $cat_id;
            }

            // Ánh xạ sang danh mục gốc tiếng Việt nếu có trong cấu hình
            if ( isset( $category_mapping[$cat_name] ) ) {
                $vietnamese_slug = $category_mapping[$cat_name];
                if ( isset( $cat_ids[$vietnamese_slug] ) ) {
                    $product_cat_ids[] = $cat_ids[$vietnamese_slug];
                }
            }
        }
    }
    // Loại bỏ các ID trùng lặp và gắn vào sản phẩm
    $product->set_category_ids( array_unique( $product_cat_ids ) );

    // Xử lý Tags
    if ( ! empty( $tags_raw ) ) {
        $csv_tags = array_map( 'trim', explode( ',', $tags_raw ) );
        $product_tag_ids = array();
        foreach ( $csv_tags as $tag_name ) {
            $tag_id = get_or_create_term( $tag_name, 'product_tag' );
            if ( $tag_id ) {
                $product_tag_ids[] = $tag_id;
            }
        }
        $product->set_tag_ids( $product_tag_ids );
    }

    // Tải và gắn ảnh
    if ( ! empty( $images_raw ) ) {
        // Lấy ảnh đầu tiên nếu có nhiều ảnh phân tách bằng dấu phẩy
        $img_urls = array_map( 'trim', explode( ',', $images_raw ) );
        $first_img_url = $img_urls[0];
        $attachment_id = sideload_image_from_url( $first_img_url );
        if ( $attachment_id ) {
            $product->set_image_id( $attachment_id );
        }
    }

    $product_id = $product->save();
    $count++;
    echo "   ✓ Đã lưu sản phẩm ID: $product_id (SKU: $sku)\n";
}

fclose( $file );
echo "🎉 ĐỒNG BỘ SẢN PHẨM HOÀN TẤT! Đã nhập thành công $count sản phẩm.\n";
