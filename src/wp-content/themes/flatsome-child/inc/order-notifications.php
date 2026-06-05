<?php
/**
 * Chức năng Thông báo và Hiệu ứng Pháo hoa Confetti sau khi đặt hàng thành công
 * Chú thích tiếng Việt dễ hiểu. Sử dụng thư viện canvas-confetti qua CDN.
 */

// Ngăn chặn truy cập trực tiếp vào file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lắng nghe khi tải trang và chèn hiệu ứng + thông báo thành công ở chân trang cảm ơn
 */
add_action( 'wp_footer', 'dev_order_success_notification' );

function dev_order_success_notification() {
    // Chỉ kích hoạt tại trang Đơn hàng đã nhận (Thank You Page) của WooCommerce
    if ( ! is_order_received_page() ) {
        return;
    }

    global $wp;
    // Lấy ID đơn hàng từ URL
    $order_id = isset( $wp->query_vars['order-received'] ) ? intval( $wp->query_vars['order-received'] ) : 0;
    
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $order_number = $order->get_order_number();
    $total_formatted = $order->get_formatted_order_total();
    $payment_method_title = $order->get_payment_method_title();
    ?>

    <!-- Nhúng thư viện bắn pháo hoa giấy Canvas-Confetti siêu nhẹ từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <!-- Khung giao diện Popup thông báo đặt hàng thành công -->
    <div id="dev-order-success-modal-overlay" class="dev-modal-overlay"></div>
    <div id="dev-order-success-modal" class="dev-modal-box">
        <!-- Vòng tròn Checkmark hoạt họa -->
        <div class="dev-success-checkmark">
            <div class="dev-check-icon">
                <span class="dev-icon-line dev-line-tip"></span>
                <span class="dev-icon-line dev-line-long"></span>
                <div class="dev-icon-circle"></div>
                <div class="dev-icon-fix"></div>
            </div>
        </div>

        <h2 class="dev-modal-title">Đặt hàng thành công!</h2>
        <p class="dev-modal-desc">Cảm ơn bạn đã mua hàng. Đơn hàng <strong>#<?php echo esc_html( $order_number ); ?></strong> của bạn đã được tiếp nhận và đang được xử lý.</p>

        <!-- Bảng tóm tắt thông tin đơn hàng nhanh -->
        <div class="dev-modal-details">
            <div class="dev-detail-row">
                <span>Mã đơn hàng:</span>
                <strong>#<?php echo esc_html( $order_number ); ?></strong>
            </div>
            <div class="dev-detail-row">
                <span>Phương thức thanh toán:</span>
                <strong><?php echo esc_html( $payment_method_title ); ?></strong>
            </div>
            <div class="dev-detail-row">
                <span>Tổng cộng:</span>
                <strong style="color: #d32f2f; font-size: 16px;"><?php echo wp_kses_post( $total_formatted ); ?></strong>
            </div>
        </div>

        <button id="dev-close-success-modal" class="dev-modal-btn">Tuyệt vời, mua sắm tiếp thôi!</button>
    </div>

    <!-- Nhúng CSS tạo giao diện và hoạt ảnh Modal -->
    <style>
        .dev-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 9999998;
            opacity: 0;
            animation: dev-fade-in 0.3s forwards ease;
        }
        
        .dev-modal-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background-color: #ffffff;
            border-radius: 12px;
            width: 450px;
            max-width: 90vw;
            padding: 35px 25px;
            box-sizing: border-box;
            z-index: 9999999;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            text-align: center;
            opacity: 0;
            animation: dev-scale-in 0.4s 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .dev-modal-title {
            margin: 20px 0 10px 0;
            color: #2e7d32;
            font-size: 24px;
            font-weight: 700;
        }

        .dev-modal-desc {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .dev-modal-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #eee;
            text-align: left;
        }

        .dev-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #444;
        }

        .dev-detail-row:last-child {
            margin-bottom: 0;
            border-top: 1px dashed #ddd;
            padding-top: 8px;
            margin-top: 8px;
        }

        .dev-modal-btn {
            background-color: #2e7d32;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            outline: none;
        }

        .dev-modal-btn:hover {
            background-color: #1b5e20;
        }

        /* Hiệu ứng hoạt họa Checkmark xanh lục */
        .dev-success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }
        
        .dev-check-icon {
            width: 80px;
            height: 80px;
            position: relative;
            border-radius: 50%;
            box-sizing: content-box;
            border: 4px solid rgba(76, 175, 80, .5);
        }
        
        .dev-check-icon::before {
            top: 3px;
            left: -2px;
            width: 30px;
            transform-origin: 100% 50%;
            border-radius: 100px 0 0 100px;
        }
        
        .dev-check-icon::after {
            top: 0;
            left: 30px;
            width: 60px;
            transform-origin: 0 50%;
            border-radius: 0 100px 100px 0;
        }
        
        .dev-icon-line {
            height: 5px;
            background-color: #4CAF50;
            display: block;
            border-radius: 2px;
            position: absolute;
            z-index: 10;
        }
        
        .dev-icon-line.dev-line-tip {
            width: 25px;
            left: 14px;
            top: 46px;
            transform: rotate(45deg);
        }
        
        .dev-icon-line.dev-line-long {
            width: 47px;
            right: 8px;
            top: 38px;
            transform: rotate(-45deg);
        }
        
        .dev-icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(76, 175, 80, .5);
            box-sizing: content-box;
            position: absolute;
            top: -4px;
            left: -4px;
            z-index: 2;
        }

        /* Keyframes Hoạt họa */
        @keyframes dev-fade-in {
            to { opacity: 1; }
        }
        @keyframes dev-scale-in {
            to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        }
    </style>

    <!-- Kịch bản bắn pháo hoa giấy và đóng Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Kích hoạt hiệu ứng bắn pháo hoa giấy Confetti (Multi-burst)
            if (typeof confetti === 'function') {
                var duration = 4 * 1000;
                var animationEnd = Date.now() + duration;
                var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 10000000 };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                // Thực hiện bắn pháo hoa liên tục từ 2 góc màn hình trong 4 giây
                var interval = setInterval(function() {
                    var timeLeft = animationEnd - Date.now();

                    if (timeLeft <= 0) {
                        return clearInterval(interval);
                    }

                    var particleCount = 50 * (timeLeft / duration);
                    
                    // Pháo hoa góc trái dưới
                    confetti(Object.assign({}, defaults, { 
                        particleCount: particleCount, 
                        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } 
                    }));
                    
                    // Pháo hoa góc phải dưới
                    confetti(Object.assign({}, defaults, { 
                        particleCount: particleCount, 
                        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } 
                    }));
                }, 250);

                // Bắn thêm một phát cực đại chính giữa màn hình làm điểm nhấn ngay khi trang load xong
                confetti({
                    particleCount: 150,
                    spread: 80,
                    origin: { y: 0.6 },
                    zIndex: 10000000
                });
            }

            // 2. Logic đóng Popup khi click vào nút hoặc click ra ngoài lớp phủ
            var modal = document.getElementById('dev-order-success-modal');
            var overlay = document.getElementById('dev-order-success-modal-overlay');
            var closeBtn = document.getElementById('dev-close-success-modal');

            function closeModal() {
                if (modal) modal.style.display = 'none';
                if (overlay) overlay.style.display = 'none';
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', closeModal);
            }
            if (overlay) {
                overlay.addEventListener('click', closeModal);
            }
        });
    </script>
    <?php
}
