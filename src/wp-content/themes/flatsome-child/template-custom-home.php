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
[row style="max-width:1300px" col_spacing="normal" v_align="equal" h_align="center"]
    [col span="9" span__sm="12"]
        [ux_slider timer="4000" arrows="true" bullets="true" auto_slide="true"]
            [ux_banner height="460px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/9f44f8b1-7919-0500-e2b6-0017deb04863.jpg" bg_overlay="rgba(0,0,0,0)" image_radius="15"]
            [/ux_banner]
            [ux_banner height="460px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/c2a09fac-900e-4652-850b-caf496ed2c45-tuan4-thang5.jpg" bg_overlay="rgba(0,0,0,0)" image_radius="15"]
            [/ux_banner]
            [ux_banner height="460px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg" bg_overlay="rgba(0,0,0,0)" image_radius="15"]
            [/ux_banner]
        [/ux_slider]
    [/col]
    [col span="3" span__sm="12"]
        [ux_banner height="460px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/banner-sidebar.jpg" bg_overlay="rgba(0,0,0,0)" image_radius="15" class="phongvt-hieu-ung-toa-hinh"]
        [/ux_banner]
    [/col]
[/row]
[/section]

[section padding="20px" background="rgb(255, 255, 255)" class="home-category-cards-section"]
[ux_slider slide_width="19%" slide_width__md="31%" slide_width__sm="48%" timer="4000" arrows="true" bullets="false" auto_slide="false" infinitive="true" slide_align="left" class="slider-nav-circle slider-nav-light"]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/ao/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">ÁO</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/balo-tui/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">BALO - TÚI</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f53d7c7e-a5e7-6100-1aa2-00176e5a5977.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/giay/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">GIÀY</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/non/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">NÓN</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/quan/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">QUẦN</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/23431488-c80c-6700-c540-00179f9fc1ea-e1626332448674.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/sandal-dep/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">SANDAL - DÉP</h4>
        [/text_box]
    [/ux_banner]
    [ux_banner height="220px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/1f3ca095-e259-0c00-04d8-0017f18cfb8f.jpg" bg_overlay="rgba(0, 0, 0, 0.2)" image_radius="15" link="/product-category/phu-kien/"]
        [text_box position_x="50" position_y="50" width="100" text_align="center"]
            <h4 style="color: #ffffff; font-weight: 700; font-size: 1.1rem; margin: 0; line-height: 1.2; text-transform: uppercase;">PHỤ KIỆN</h4>
        [/text_box]
    [/ux_banner]
[/ux_slider]
[/section]
[row style="max-width:1300px" class="home-section-row"]
    [col span="12" bg_color="rgb(255, 255, 255)" padding="20px" class="home-section-col"]
        [title style="normal" text="ĐANG GIẢM GIÁ" size="115"]
        [ux_products type="slider" columns="4" show="onsale" limit="8" orderby="sales"]
    [/col]
[/row]

[row style="max-width:1300px" class="home-section-row"]
    [col span="12" bg_color="rgb(255, 255, 255)" padding="20px" class="home-section-col"]
        [title style="normal" text="SẢN PHẨM BÁN CHẠY" size="115"]
        [ux_products columns="4" show="featured" limit="8" orderby="sales"]
    [/col]
[/row]

[row style="max-width:1300px" class="home-section-row"]
    [col span="12" bg_color="rgb(255, 255, 255)" padding="20px" class="home-section-col"]
        [title style="normal" text="HÀNG MỚI VỀ" size="115"]
        [ux_products columns="4" limit="8" orderby="date"]
    [/col]
[/row]

[section padding="40px 0px" background="rgba(0,0,0,0)"]
    [row style="max-width:1300px"]
        [col span="12"]
            [title style="normal" text="TIN TỨC MỚI NHẤT" size="115"]
            [blog_posts type="row" columns="3" posts="3" image_height="60%" image_hover="zoom" class="home_blog"]
        [/col]
    [/row]
[/section]
<!-- /wp:flatsome/uxbuilder -->
        ');
        ?>
    </div>
</main>

<?php
get_footer();
?>