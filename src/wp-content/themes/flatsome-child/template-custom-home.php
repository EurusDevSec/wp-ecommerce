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
[section padding="20px" background="rgb(255, 255, 255)" class="home-hero-section"]
[ux_banner_grid spacing="20" height="500"]
    [col_grid span="8"]
        [ux_banner height="500px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/9f44f8b1-7919-0500-e2b6-0017deb04863.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15"]
        [/ux_banner]
    [/col_grid]
    [col_grid span="4"]
        [ux_banner height="500px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/c2a09fac-900e-4652-850b-caf496ed2c45-tuan4-thang5.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15"]
        [/ux_banner]
    [/col_grid]
[/ux_banner_grid]
[/section]

[section padding="20px" background="rgb(255, 255, 255)" class="home-category-cards-section"]
[row columns="5" col_spacing="normal" v_align="equal" h_align="center"]
    [col]
        [ux_banner height="100%" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg" bg_overlay="rgba(0, 0, 0, 0.15)" image_radius="15" link="/shop/"]
            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2;">TUẦN 3 / THÁNG 5</h4>
            [/text_box]
        [/ux_banner]
    [/col]
    [col]
        [ux_banner height="100%" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15" link="/shop/"]
        [/ux_banner]
    [/col]
    [col]
        [ux_banner height="100%" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f53d7c7e-a5e7-6100-1aa2-00176e5a5977.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15" link="/shop/"]
        [/ux_banner]
    [/col]
    [col]
        [ux_banner height="100%" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15" link="/shop/"]
        [/ux_banner]
    [/col]
    [col]
        [ux_banner height="100%" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg" bg_overlay="rgba(0, 0, 0, 0)" image_radius="15" link="/shop/"]
        [/ux_banner]
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