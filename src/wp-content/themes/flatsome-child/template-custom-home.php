<?php
/* Template Name: Custom Homepage Code */

get_header();
?>

<main id="main" class="" role="main">
    <div id="content" role="main" class="content-area">

        <?php /* ============================================================
         *  HERO SECTION — Bento Grid Bất đối xứng
         * ============================================================ */ ?>
        <section class="hkt-hero-bento" aria-label="Banner trang chủ HKT Fashion">
            <div class="hkt-bento-grid">

                <?php /* --- Ô 1: Slider chính (lớn nhất) --- */ ?>
                <div class="hkt-bento-cell hkt-bento-main" aria-label="Bộ sưu tập thời trang 2026">
                    <?php echo do_shortcode('
                        [ux_slider timer="4000" arrows="true" bullets="true" nav_size="normal" nav_color="light"]
                            [ux_banner height="600px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/9f44f8b1-7919-0500-e2b6-0017deb04863.jpg" bg_overlay="rgba(0,0,0,0.25)" image_radius="0"]
                                [text_box position_x="10" position_y="80" width="70" animate="fadeInUp"]
                                    <div class="hkt-hero-badge">Bộ Sưu Tập 2026</div>
                                    <h2 class="hkt-hero-title">Phong Cách<br>Định Nghĩa Bạn</h2>
                                    <p class="hkt-hero-sub">Hàng trăm mẫu thời trang mới nhất</p>
                                    <a href="/shop/" class="hkt-hero-cta" aria-label="Khám phá bộ sưu tập mới nhất">Khám Phá Ngay →</a>
                                [/text_box]
                            [/ux_banner]
                            [ux_banner height="600px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/c2a09fac-900e-4652-850b-caf496ed2c45-tuan4-thang5.jpg" bg_overlay="rgba(0,0,0,0.2)" image_radius="0"]
                                [text_box position_x="10" position_y="75" width="70" animate="fadeInUp"]
                                    <div class="hkt-hero-badge hkt-badge-sale">Sale 50%</div>
                                    <h2 class="hkt-hero-title">Áo Sơ Mi<br>Cao Cấp</h2>
                                    <p class="hkt-hero-sub">Vải Kate Ấn Độ siêu mềm mại</p>
                                    <a href="/product-category/ao/" class="hkt-hero-cta" aria-label="Xem bộ sưu tập áo sơ mi">Mua Ngay →</a>
                                [/text_box]
                            [/ux_banner]
                            [ux_banner height="600px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2025/04/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg" bg_overlay="rgba(0,0,0,0.2)" image_radius="0"]
                                [text_box position_x="10" position_y="75" width="70" animate="fadeInUp"]
                                    <div class="hkt-hero-badge">Mới Về Hôm Nay</div>
                                    <h2 class="hkt-hero-title">Thời Trang<br>Năng Động</h2>
                                    <p class="hkt-hero-sub">Cập nhật xu hướng mới nhất mỗi ngày</p>
                                    <a href="/product-category/ao/" class="hkt-hero-cta" aria-label="Xem hàng mới về hôm nay">Xem Hàng Mới →</a>
                                [/text_box]
                            [/ux_banner]
                        [/ux_slider]
                    '); ?>
                </div>

                <?php /* --- Ô 2: Banner Hàng Mới Về (Glassmorphism) --- */ ?>
                <div class="hkt-bento-cell hkt-bento-new">
                    <a href="/product-category/ao/" class="hkt-bento-link" aria-label="Xem áo thời trang mới nhất về cửa hàng">
                        <img src="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/banner-sidebar.jpg"
                             alt="Hàng mới về - HKT Fashion"
                             loading="lazy"
                             class="hkt-bento-img">
                        <div class="hkt-bento-glass-overlay">
                            <span class="hkt-bento-tag">✨ Hàng Mới Về</span>
                            <h3 class="hkt-bento-heading">BST Mùa Hè<br>2026</h3>
                            <p class="hkt-bento-p">Cập nhật mỗi ngày</p>
                        </div>
                    </a>
                </div>

                <?php /* --- Ô 3: Voucher Flash Sale --- */ ?>
                <div class="hkt-bento-cell hkt-bento-voucher">
                    <div class="hkt-bento-voucher-inner">
                        <div class="hkt-bento-voucher-label">⏰ Ưu đãi có thời hạn</div>
                        <div class="hkt-bento-voucher-value">GIẢM 15%</div>
                        <p class="hkt-bento-voucher-desc">Áp dụng đơn từ 500K. Nhập mã khi thanh toán.</p>
                        <div class="hkt-bento-voucher-code-row">
                            <span class="hkt-bento-voucher-code" id="home-voucher-code">HKT15OFF</span>
                            <button class="hkt-bento-copy-btn" data-code="HKT15OFF" aria-label="Sao chép mã giảm giá HKT15OFF">SAO CHÉP</button>
                        </div>
                        <a href="/shop/" class="hkt-bento-voucher-cta" aria-label="Mua sắm ngay để dùng ưu đãi">Mua Sắm Ngay →</a>
                    </div>
                </div>

                <?php /* --- Ô 4: Độc quyền HKT --- */ ?>
                <div class="hkt-bento-cell hkt-bento-exclusive">
                    <a href="/product-category/balo-tui/" class="hkt-bento-link" aria-label="Xem bộ sưu tập túi xách và balo độc quyền">
                        <img src="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg"
                             alt="Balo & Túi Xách - HKT Fashion"
                             loading="lazy"
                             class="hkt-bento-img">
                        <div class="hkt-bento-dark-overlay">
                            <span class="hkt-bento-tag hkt-tag-dark">🔥 Độc Quyền HKT</span>
                            <h3 class="hkt-bento-heading-dark">Balo &amp;<br>Túi Xách</h3>
                            <span class="hkt-bento-arrow">Xem ngay →</span>
                        </div>
                    </a>
                </div>

            </div><!-- .hkt-bento-grid -->
        </section>

        <?php /* ============================================================
         *  SECTION: Trust Bar (Bằng chứng xã hội / USPs)
         * ============================================================ */ ?>
        <section class="hkt-trust-bar" aria-label="Cam kết của HKT Fashion">
            <div class="hkt-trust-bar-inner">
                <div class="hkt-trust-item">
                    <div class="hkt-trust-icon-svg-wrapper">
                        <svg viewBox="0 0 24 24">
                            <rect x="1" y="3" width="15" height="13" rx="2" ry="2"></rect>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                            <circle cx="5.5" cy="18.5" r="2.5"></circle>
                            <circle cx="18.5" cy="18.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <div>
                        <strong>Miễn phí vận chuyển</strong>
                        <span>Đơn từ 499K</span>
                    </div>
                </div>
                <div class="hkt-trust-item">
                    <div class="hkt-trust-icon-svg-wrapper">
                        <svg viewBox="0 0 24 24">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <polyline points="1 20 1 14 7 14"></polyline>
                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                        </svg>
                    </div>
                    <div>
                        <strong>Đổi hàng tận nhà</strong>
                        <span>Trong 7 ngày</span>
                    </div>
                </div>
                <div class="hkt-trust-item">
                    <div class="hkt-trust-icon-svg-wrapper">
                        <svg viewBox="0 0 24 24" style="stroke-width: 0;">
                            <text x="50%" y="62%" dominant-baseline="middle" text-anchor="middle" font-family="'Montserrat', sans-serif" font-weight="900" font-size="8.5" fill="currentColor">COD</text>
                        </svg>
                    </div>
                    <div>
                        <strong>Thanh toán COD</strong>
                        <span>Yên tâm mua sắm</span>
                    </div>
                </div>
                <div class="hkt-trust-item">
                    <div class="hkt-trust-icon-svg-wrapper">
                        <svg viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                    </div>
                    <div>
                        <strong>Hotline: 0999999999</strong>
                        <span>Hỗ trợ từ 8h30 - 24h00</span>
                    </div>
                </div>
            </div>
        </section>

        <?php /* ============================================================
         *  SECTION: Danh mục sản phẩm (Category Slider)
         * ============================================================ */ ?>
        <section class="hkt-home-section hkt-section-categories" aria-label="Danh mục sản phẩm HKT Fashion">
            <div class="hkt-section-inner">
                <h2 class="hkt-section-title">Danh Mục Nổi Bật</h2>
                <?php echo do_shortcode('
                    [ux_slider slide_width="18%" slide_width__md="30%" slide_width__sm="46%" timer="3500" arrows="true" bullets="false" auto_slide="true" infinitive="true" slide_align="left" class="slider-nav-circle slider-nav-light hkt-cat-slider"]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/0c8f79e5-dcbb-442c-b6d3-359ecd822d75-THUMBMOBANSOMI.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/ao/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Áo</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/304b2fcb-7241-0400-fff2-0017fd550a27.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/balo-tui/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Balo - Túi</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f53d7c7e-a5e7-6100-1aa2-00176e5a5977.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/giay/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Giày</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/ea158967-c428-2900-80fa-001770b476d1.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/non/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Nón</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/f0567450-4362-5400-588a-001794113da6.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/quan/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Quần</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/23431488-c80c-6700-c540-00179f9fc1ea-e1626332448674.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/sandal-dep/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Sandal - Dép</h3>
                            [/text_box]
                        [/ux_banner]
                        [ux_banner height="230px" bg="https://thoitrang19.mauthemewp.com/wp-content/uploads/2021/07/1f3ca095-e259-0c00-04d8-0017f18cfb8f.jpg" bg_overlay="rgba(0,0,0,0.28)" image_radius="15" link="/product-category/phu-kien/"]
                            [text_box position_x="50" position_y="85" width="100" text_align="center"]
                                <h3 class="hkt-cat-label">Phụ Kiện</h3>
                            [/text_box]
                        [/ux_banner]
                    [/ux_slider]
                '); ?>
            </div>
        </section>

        <?php /* ============================================================
         *  SECTION: Đang giảm giá (Sale Products)
         * ============================================================ */ ?>
        <section class="hkt-home-section hkt-section-sale" aria-label="Sản phẩm đang giảm giá">
            <div class="hkt-section-inner">
                <div class="hkt-section-head">
                    <div>
                        <h2 class="hkt-section-title">⚡ Đang Giảm Giá</h2>
                        <p class="hkt-section-sub">Ưu đãi có thời hạn — Đừng bỏ lỡ!</p>
                    </div>
                    <a href="/shop/?orderby=popularity&on_sale=1" class="hkt-section-viewall" aria-label="Xem tất cả sản phẩm đang giảm giá">Xem tất cả →</a>
                </div>
                <?php echo do_shortcode('[ux_products type="slider" columns="4" show="onsale" products="8" class="hkt-product-slider"]'); ?>
            </div>
        </section>

        <?php /* ============================================================
         *  SECTION: Sản phẩm bán chạy (Featured / Best Sellers)
         * ============================================================ */ ?>
        <section class="hkt-home-section hkt-section-bestseller" aria-label="Sản phẩm bán chạy nhất">
            <div class="hkt-section-inner">
                <div class="hkt-section-head">
                    <div>
                        <h2 class="hkt-section-title">🔥 Sản Phẩm Bán Chạy</h2>
                        <p class="hkt-section-sub">Được hàng nghìn khách hàng tin chọn</p>
                    </div>
                    <a href="/shop/?orderby=popularity" class="hkt-section-viewall" aria-label="Xem tất cả sản phẩm bán chạy">Xem tất cả →</a>
                </div>
                <?php echo do_shortcode('[ux_products type="slider" columns="5" show="featured" products="10" class="hkt-product-slider"]'); ?>
            </div>
        </section>

        <?php /* ============================================================
         *  SECTION: Hàng mới về (New Arrivals)
         * ============================================================ */ ?>
        <section class="hkt-home-section hkt-section-newarrivals" aria-label="Hàng mới về HKT Fashion">
            <div class="hkt-section-inner">
                <div class="hkt-section-head">
                    <div>
                        <h2 class="hkt-section-title">✨ Hàng Mới Về</h2>
                        <p class="hkt-section-sub">Cập nhật xu hướng mới nhất mỗi ngày</p>
                    </div>
                    <a href="/shop/?orderby=date" class="hkt-section-viewall" aria-label="Xem tất cả hàng mới về">Xem tất cả →</a>
                </div>
                <?php echo do_shortcode('[ux_products type="row" columns="5" products="15" orderby="date" class="hkt-product-grid"]'); ?>
            </div>
        </section>

        <?php /* ============================================================
         *  SECTION: Tin tức mới nhất (Blog)
         * ============================================================ */ ?>
        <section class="hkt-home-section hkt-section-blog" aria-label="Tin tức thời trang mới nhất">
            <div class="hkt-section-inner">
                <div class="hkt-section-head">
                    <div>
                        <h2 class="hkt-section-title">📰 Tin Tức Thời Trang</h2>
                        <p class="hkt-section-sub">Xu hướng & phong cách mới nhất</p>
                    </div>
                    <a href="/tin-tuc/" class="hkt-section-viewall" aria-label="Đọc thêm tin tức thời trang">Đọc thêm →</a>
                </div>
                <?php echo do_shortcode('[blog_posts type="row" columns="3" posts="3" image_height="60%" image_hover="zoom" class="home_blog"]'); ?>
            </div>
        </section>

    </div>
</main>

<?php
get_footer();
?>