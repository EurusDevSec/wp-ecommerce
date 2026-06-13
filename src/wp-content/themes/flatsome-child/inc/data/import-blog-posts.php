<?php
/**
 * Script import tin tức/blog thời trang cho HKT Fashion
 * Chạy bằng WP-CLI: wp eval-file import-blog-posts.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo "📝 Bắt đầu nhập tin tức thời trang HKT...\n";

// 1. Tạo chuyên mục "Tin tức & Xu hướng" nếu chưa có
$category_name = 'Tin tức & Xu hướng';
$category_slug = 'tin-tuc-xu-huong';
$cat_id = get_term_by('slug', $category_slug, 'category');
if ( ! $cat_id ) {
    $inserted_cat = wp_insert_term( $category_name, 'category', array( 'slug' => $category_slug ) );
    $cat_id = is_wp_error( $inserted_cat ) ? 1 : $inserted_cat['term_id'];
} else {
    $cat_id = $cat_id->term_id;
}

// 2. Định nghĩa các bài viết thời trang mẫu
$blog_posts = array(
    array(
        'title'   => 'Xu hướng Denim-on-Denim: Cách phối Áo khoác Jeans Duskpath và Nón Bakerfield cực chất',
        'content' => '<!-- wp:paragraph -->
<p>Phong cách <strong>Denim-on-Denim</strong> (mặc nguyên cây denim) chưa bao giờ lỗi mốt trong thế giới thời trang nam. Tuy nhiên, phối thế nào để không bị "quá tải" và giữ được nét tối giản, hiện đại là điều không phải ai cũng biết. Dưới đây là bí quyết phối đồ cực chất từ HKT Fashion.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>1. Phối tông màu lệch nhẹ để tạo điểm nhấn</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Một nguyên tắc vàng khi phối denim là không nên chọn hai món đồ có màu sắc giống hệt nhau nếu bạn không tự tin. Hãy thử kết hợp chiếc <strong>Áo Khoác Jeans Nam Duskpath Form Loose</strong> màu xanh đậm cá tính với chiếc <strong>Nón Lưỡi Trai Nam Bakerfield Denim</strong> màu xanh chàm cổ điển. Sự lệch tông nhẹ này sẽ mang lại chiều sâu cho cả set đồ.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Lớp lót trong (Inner) - Hãy ưu tiên sự cơ bản</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Để set đồ denim của bạn trông mềm mại và năng động hơn, hãy chọn chiếc áo thun trơn màu trung tính bên trong. Các mẫu <strong>Áo Thun Nam Precision Form Regular</strong> màu Trắng hoặc Đen sẽ là mảnh ghép hoàn hảo để cân bằng lại độ thô ráp của denim.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><em>Hãy ghé danh mục sản phẩm của HKT Fashion để mua trọn set đồ Denim độc đáo này nhé!</em></p>
<!-- /wp:paragraph -->',
        'excerpt' => 'Bí quyết phối đồ Denim-on-Denim cực chất cho nam giới từ chiếc áo khoác Duskpath jeans và nón Bakerfield phong cách tối giản.',
    ),
    array(
        'title'   => 'Bí quyết giặt và bảo quản Áo Khoác Gió AirFlex siêu gọn nhẹ luôn như mới',
        'content' => '<!-- wp:paragraph -->
<p>Chiếc <strong>Áo Khoác Gió Active Nam AirFlex Siêu Gọn Nhẹ</strong> của HKT Fashion được sản xuất từ chất liệu sợi công nghệ cao giúp chống nắng, cản gió và trượt nước nhẹ cực kỳ tiện dụng. Tuy nhiên, nếu không giặt và bảo quản đúng cách, lớp phủ trượt nước này có thể bị hao mòn theo thời gian. Hãy cùng bỏ túi các mẹo nhỏ dưới đây:</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>1. Ưu tiên giặt tay hoặc dùng túi giặt chế độ nhẹ</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Lực quay mạnh của máy giặt có thể làm ảnh hưởng đến cấu trúc dệt chặt chẽ của sợi AirFlex. Bạn nên giặt tay nhẹ nhàng bằng nước lạnh. Nếu giặt máy, hãy nhớ kéo hết khóa kéo, lộn trái áo và đặt vào túi giặt lưới, chọn chế độ "giặt nhẹ" (delicates).</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Tuyệt đối không dùng chất tẩy rửa mạnh hoặc nước xả vải</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nước xả vải có chứa chất làm mềm sợi, vô tình phủ một lớp sáp lên bề mặt và làm mất khả năng trượt nước của áo gió. Hãy dùng nước giặt trung tính hoặc xà phòng loãng thông thường.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>3. Phơi trong bóng râm, tránh ánh nắng gắt trực tiếp</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Sợi vải AirFlex khô rất nhanh. Tránh phơi trực tiếp dưới ánh nắng gay gắt giữa trưa để giữ cho màu sắc của áo khoác luôn tươi mới và bền màu lâu hơn.</p>
<!-- /wp:paragraph -->',
        'excerpt' => 'Cách giặt và bảo quản chất liệu áo khoác gió công nghệ AirFlex chống nước, cản gió bền bỉ theo thời gian.',
    ),
    array(
        'title'   => 'Thời trang tối giản 2026: Tại sao chiếc Áo Thun Nam Regular Form lại không bao giờ lỗi mốt?',
        'content' => '<!-- wp:paragraph -->
<p>Khi các xu hướng thời trang nhanh (fast-fashion) thay đổi liên tục, phong cách tối giản (Minimalism) vẫn đứng vững và ngày càng trở thành lối sống của nam giới hiện đại. Trong đó, chiếc <strong>Áo Thun Nam Regular Form</strong> chính là "linh hồn" của tủ đồ tối giản.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>1. Thiết kế Regular Form - Vừa vặn hoàn hảo</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Không quá rộng như Oversize, cũng không quá bó sát như Slim-fit, form dáng Regular mang lại cảm giác thoải mái tối đa mà vẫn giữ được sự lịch sự, tôn dáng tự nhiên của phái mạnh. Mẫu <strong>Áo Thun Nam Precision</strong> hay <strong>Atelier</strong> tại HKT Fashion được nghiên cứu kỹ lưỡng để mang lại tỷ lệ vai và ngực chuẩn nhất.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3>2. Dễ dàng biến hóa với mọi loại trang phục</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Chỉ với một chiếc áo thun trơn basic, bạn có thể biến hóa từ phong cách năng động hàng ngày (khi mặc cùng quần Short Jean) sang phong cách công sở Smart-Casual lịch lãm (khi khoác thêm blazer và mặc quần tây ống đứng).</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Đầu tư vào những chiếc áo thun basic chất lượng cao là bước đầu tiên để xây dựng một tủ đồ thông minh, bền vững.</p>
<!-- /wp:paragraph -->',
        'excerpt' => 'Khám phá lý do áo thun nam phom dáng vừa vặn (Regular Form) luôn là biểu tượng bền vững của phong cách tối giản nam giới.',
    )
);

// 3. Tiến hành chèn các bài viết vào cơ sở dữ liệu
$inserted_count = 0;
foreach ( $blog_posts as $post_data ) {
    // Kiểm tra xem bài viết đã tồn tại chưa để tránh trùng lặp khi chạy lại script
    $existing_post = get_page_by_title( $post_data['title'], OBJECT, 'post' );
    
    if ( ! $existing_post ) {
        $post_id = wp_insert_post( array(
            'post_title'     => $post_data['title'],
            'post_content'   => $post_data['content'],
            'post_excerpt'   => $post_data['excerpt'],
            'post_status'    => 'publish',
            'post_type'      => 'post',
            'post_category'  => array( $cat_id ),
            'post_author'    => 1, // Admin hoặc User đầu tiên
        ) );
        
        if ( ! is_wp_error( $post_id ) ) {
            echo "  ✓ Đã nhập thành công bài viết: '{$post_data['title']}' (ID: {$post_id})\n";
            $inserted_count++;
        } else {
            echo "  ❌ Lỗi nhập bài viết: " . $post_id->get_error_message() . "\n";
        }
    } else {
        echo "  - Bài viết đã tồn tại sẵn: '{$post_data['title']}' (ID: {$existing_post->ID})\n";
    }
}

echo "🎉 Hoàn tất nhập! Đã thêm mới {$inserted_count} bài viết thời trang thành công.\n";
