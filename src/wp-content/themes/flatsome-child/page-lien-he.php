<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<main class="hkt-page-layout hkt-page-lien-he">
    <section class="hkt-hero hkt-hero-contact">
        <div class="hkt-hero-copy">
            <span class="hkt-overline">Liên hệ</span>
            <h1>Liên hệ với HKT Fashion</h1>
            <p>Đội ngũ HKT luôn sẵn sàng tư vấn, hỗ trợ đổi size và giải đáp thông tin đơn hàng nhanh chóng.</p>
        </div>
    </section>

    <section class="hkt-contact-grid">
        <div class="hkt-contact-card">
            <h3>Thông tin showroom</h3>
            <p><strong>Địa chỉ:</strong> 123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</p>
            <p><strong>Hotline:</strong> <a href="tel:+84901234567">0901 234 567</a></p>
            <p><strong>Email:</strong> <a href="mailto:contact@hktfashion.vn">contact@hktfashion.vn</a></p>
            <p><strong>Giờ mở cửa:</strong></p>
            <ul>
                <li>Thứ 2 - Thứ 7: 09:00 - 20:00</li>
                <li>Chủ nhật: 10:00 - 18:00</li>
            </ul>
            <div class="hkt-map-wrapper">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.818824279788!2d106.69834397465811!3d10.776893192334735!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752ee2b8fddab7%3A0x1d73b48a3e1045d2!2zMTIzIE5ndXnhu4VuIEh1w6JuZSwgUXVhxqFuIMSQ4butbiAxLCBUcC4gSMOyYSBDaOG6oW0gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1700000000000" width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
        <div class="hkt-contact-card hkt-contact-form-card">
            <h3>Gửi tin nhắn cho chúng tôi</h3>
            <p>Nếu bạn cần hỗ trợ, điền thông tin vào form dưới đây và đội ngũ HKT sẽ phản hồi nhanh nhất.</p>
            <?php echo do_shortcode( '[contact-form-7 id="xxxx" title="Form liên hệ"]' ); ?>
        </div>
    </section>
</main>
<?php
get_footer();
