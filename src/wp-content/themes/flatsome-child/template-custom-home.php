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

[col_grid span="6"]

[ux_banner height="500px"]

[text_box position_x="50" position_y="50"]

<h3 class="uppercase"><strong>This is a simple banner</strong></h3>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>

[/text_box]

[/ux_banner]

[/col_grid]
[col_grid span="2"]

[ux_image]


[/col_grid]
[col_grid span="3" height="1-2"]

[ux_slider]


[/ux_slider]

[/col_grid]
[col_grid span="3" height="1-2"]

[ux_banner height="500px" bg_overlay="rgba(0, 0, 0, 0.31)"]

[text_box width="40" width__sm="60" position_x="5" position_y="50" text_align="left"]

<h2 class="uppercase"><strong>Main Headline</strong></h2>
<h3>Smaller Headline</h3>
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>
[button text="Primary"]

[button text="Secondary" color="white" style="outline"]


[/text_box]

[/ux_banner]

[/col_grid]

[/ux_banner_grid]
<!-- /wp:flatsome/uxbuilder -->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->
        ');
        ?>
    </div>
</main>

<?php
get_footer();
?>