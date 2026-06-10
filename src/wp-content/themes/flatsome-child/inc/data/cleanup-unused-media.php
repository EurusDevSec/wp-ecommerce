<?php
/**
 * Script dọn dẹp các tệp ảnh cũ không sử dụng trong thư mục uploads.
 * Thiết kế cho HKT Fashion.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

ini_set('memory_limit', '1024M');
set_time_limit(0);

echo "🔍 Đang lập bản đồ các ảnh đang được sử dụng...\n";

$used_attachment_ids = array();

// 1. Lấy ảnh đại diện (_thumbnail_id) của sản phẩm, biến thể, bài viết và trang đang hoạt động
global $wpdb;
$thumb_ids = $wpdb->get_col("
    SELECT DISTINCT meta_value 
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE pm.meta_key = '_thumbnail_id' 
      AND p.post_status = 'publish'
");
foreach ($thumb_ids as $id) {
    if ($id) {
        $used_attachment_ids[(int)$id] = true;
    }
}

// 2. Lấy ảnh trong thư viện ảnh (_product_image_gallery) của sản phẩm đang hoạt động
$gallery_meta = $wpdb->get_col("
    SELECT DISTINCT meta_value 
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->posts} p ON p.ID = pm.post_id
    WHERE pm.meta_key = '_product_image_gallery'
      AND p.post_status = 'publish'
");
foreach ($gallery_meta as $meta) {
    if (!empty($meta)) {
        $ids = explode(',', $meta);
        foreach ($ids as $id) {
            $used_attachment_ids[(int)$id] = true;
        }
    }
}

// 3. Lấy ảnh logo và các ảnh được cấu hình trong theme mods/options
$theme_mods = get_theme_mods();
if (is_array($theme_mods)) {
    foreach ($theme_mods as $key => $val) {
        if (is_numeric($val)) {
            $used_attachment_ids[(int)$val] = true;
        } elseif (is_string($val) && strpos($val, '/uploads/') !== false) {
            // Nếu là URL, tìm ID của nó
            $attachment_id = attachment_url_to_postid($val);
            if ($attachment_id) {
                $used_attachment_ids[(int)$attachment_id] = true;
            }
        }
    }
}

// Lấy thêm từ options thông dụng (site_icon, logo, etc.)
$site_icon = get_option('site_icon');
if ($site_icon) {
    $used_attachment_ids[(int)$site_icon] = true;
}

// 4. Lấy tất cả attachments hiện có trong hệ thống
$all_attachments = $wpdb->get_results("
    SELECT ID, post_title, guid 
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment'
");

echo "✓ Tìm thấy tổng cộng " . count($all_attachments) . " attachment trong database.\n";
echo "✓ Xác định được " . count($used_attachment_ids) . " ảnh đang được sử dụng thực tế.\n";

$deleted_count = 0;
$skipped_count = 0;

echo "🧹 Bắt đầu dọn dẹp các tệp ảnh không sử dụng...\n";

foreach ($all_attachments as $att) {
    $id = (int)$att->ID;
    
    // Nếu ảnh đang được sử dụng thì bỏ qua
    if (isset($used_attachment_ids[$id])) {
        $skipped_count++;
        continue;
    }
    
    $file_path = get_attached_file($id);
    $filename = basename($file_path);
    
    echo "  - Đang xóa attachment ID {$id}: {$filename}...\n";
    
    // Xóa attachment và tệp tin vật lý trên đĩa (tham số thứ hai = true sẽ xóa hoàn toàn tệp vật lý)
    $result = wp_delete_attachment($id, true);
    
    if ($result) {
        $deleted_count++;
    } else {
        echo "    ❌ Lỗi khi xóa attachment ID {$id}\n";
    }
}

echo "🎉 HOÀN TẤT DỌN DẸP!\n";
echo "- Đã xóa thành công: {$deleted_count} tệp ảnh không sử dụng từ database và ổ đĩa.\n";
echo "- Giữ lại: {$skipped_count} tệp ảnh đang sử dụng.\n";
