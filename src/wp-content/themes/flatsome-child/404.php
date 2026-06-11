<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<main class="hkt-page-layout hkt-404-page">
    <section class="hkt-hero hkt-hero-404">
        <div class="hkt-hero-copy">
            <span class="hkt-overline">404 - Không tìm thấy trang</span>
            <h1>Xin lỗi, trang bạn tìm không tồn tại</h1>
            <p>Có thể đường dẫn đã thay đổi hoặc trang đã bị xóa. Hãy quay về trang chủ và tiếp tục tìm những mẫu thời trang yêu thích.</p>
            <a class="hkt-btn hkt-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Quay về trang chủ</a>
        </div>
    </section>

    <section class="hkt-content-block hkt-404-products">
        <div class="hkt-section-header">
            <h3>Sản phẩm bán chạy</h3>
            <p>Khám phá những item best seller được nhiều khách hàng HKT yêu thích.</p>
        </div>
        <div class="hkt-best-sellers">
            <?php echo do_shortcode( '[best_selling_products limit="4" columns="4"]' ); ?>
        </div>
    </section>
</main>
<?php
get_footer();
