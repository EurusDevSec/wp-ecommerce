<?php
/* Template Name: Custom Homepage Code */

get_header();
?>

<main id="main" class="">
    <div id="content" role="main" class="content-area">
        <?php
        // WordPress sẽ thực thi đoạn shortcode của Flatsome thông qua hàm này
        echo do_shortcode('
<!-- wp:flatsome/uxbuilder -->
[ux_banner_grid]
    [col_grid span="8"]
        [ux_banner height="500px" bg_color="rgb(247, 116, 38)" bg_overlay="rgba(0, 0, 0, 0.15)"]
            [text_box position_x="10" position_y="50" text_align="left"]
                <h2 class="uppercase" style="color: #ffffff; font-size: 2.2rem; font-weight: 700;"><strong>BỘ SƯU TẬP MỚI</strong></h2>
                <p style="color: #ffffff; font-size: 1.1rem; margin-bottom: 20px;">Trải nghiệm phong cách thời trang trẻ trung, năng động và cá tính của HKT Fashion.</p>
                [button text="Mua ngay" color="white" link="/shop/"]
            [/text_box]
        [/ux_banner]
    [/col_grid]
    [col_grid span="4"]
        [ux_banner height="240px" bg_color="rgb(0, 0, 0)" bg_overlay="rgba(0, 0, 0, 0.2)"]
            [text_box position_x="50" position_y="50"]
                <h3 style="color: #ffffff;" class="uppercase"><strong>Giảm giá tới 50%</strong></h3>
                [button text="Xem ngay" color="white" style="outline" link="/shop/"]
            [/text_box]
        [/ux_banner]
    [/col_grid]
    [col_grid span="4"]
        [ux_banner height="240px" bg_color="rgb(247, 116, 38)" bg_overlay="rgba(0, 0, 0, 0.1)"]
            [text_box position_x="50" position_y="50"]
                <h3 style="color: #ffffff;" class="uppercase"><strong>Hàng Mới Về</strong></h3>
                [button text="Xem ngay" color="white" style="outline" link="/shop/"]
            [/text_box]
        [/ux_banner]
    [/col_grid]
[/ux_banner_grid]

[section padding="40px" background="rgb(255, 255, 255)"]
    [title style="center" text="DANH MỤC NỔI BẬT" icon="icon-menu" size="130"]
    [row col_style="solid" col_bg="rgb(255, 255, 255)" col_bg_radius="15" h_align="center" class="phongvt-hieu-ung-toa-hinh"]
        [col span="2" span__sm="4"]
            <a href="/product-category/ao/" style="text-align: center; display: block; text-decoration: none;">
                <div style="background: #FAF9F6; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <span style="font-size: 2.2rem;">👕</span>
                </div>
                <h5 class="uppercase" style="margin-top: 15px; font-weight: 700; font-size: 0.9rem;">Áo</h5>
            </a>
        [/col]
        [col span="2" span__sm="4"]
            <a href="/product-category/quan/" style="text-align: center; display: block; text-decoration: none;">
                <div style="background: #FAF9F6; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <span style="font-size: 2.2rem;">👖</span>
                </div>
                <h5 class="uppercase" style="margin-top: 15px; font-weight: 700; font-size: 0.9rem;">Quần</h5>
            </a>
        [/col]
        [col span="2" span__sm="4"]
            <a href="/product-category/giay/" style="text-align: center; display: block; text-decoration: none;">
                <div style="background: #FAF9F6; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <span style="font-size: 2.2rem;">👟</span>
                </div>
                <h5 class="uppercase" style="margin-top: 15px; font-weight: 700; font-size: 0.9rem;">Giày</h5>
            </a>
        [/col]
        [col span="2" span__sm="4"]
            <a href="/product-category/balo-tui/" style="text-align: center; display: block; text-decoration: none;">
                <div style="background: #FAF9F6; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <span style="font-size: 2.2rem;">🎒</span>
                </div>
                <h5 class="uppercase" style="margin-top: 15px; font-weight: 700; font-size: 0.9rem;">Balo - Túi</h5>
            </a>
        [/col]
        [col span="2" span__sm="4"]
            <a href="/product-category/phu-kien/" style="text-align: center; display: block; text-decoration: none;">
                <div style="background: #FAF9F6; border-radius: 50%; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #eaeaea; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <span style="font-size: 2.2rem;">⌚</span>
                </div>
                <h5 class="uppercase" style="margin-top: 15px; font-weight: 700; font-size: 0.9rem;">Phụ kiện</h5>
            </a>
        [/col]
    [/row]
[/section]

[section padding="40px" background="rgb(250, 249, 246)"]
    [title style="center" text="ĐANG GIẢM GIÁ" icon="icon-angle-down" size="130" color="rgb(247, 116, 38)"]
    [ux_products type="slider" columns="4" show="onsale" limit="8" orderby="sales"]
[/section]

[section padding="40px" background="rgb(255, 255, 255)"]
    [title style="center" text="SẢN PHẨM BÁN CHẠY" icon="icon-heart" size="130"]
    [ux_products columns="4" show="featured" limit="8" orderby="sales"]
[/section]

[section padding="40px" background="rgb(250, 249, 246)"]
    [title style="center" text="HÀNG MỚI VỀ" icon="icon-star" size="130"]
    [ux_products columns="4" limit="8" orderby="date"]
[/section]

[section padding="40px" background="rgb(255, 255, 255)"]
    [title style="center" text="TIN TỨC MỚI NHẤT" icon="icon-checkmark" size="130"]
    [blog_posts type="row" columns="3" posts="3" image_height="60%" image_hover="zoom" class="home_blog"]
[/section]
<!-- /wp:flatsome/uxbuilder -->
        ');
        ?>
    </div>
</main>

<?php
get_footer();
?>