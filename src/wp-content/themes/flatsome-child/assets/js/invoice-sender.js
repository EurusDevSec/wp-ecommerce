/**
 * AJAX Invoice Sender for HKT Fashion
 * Handle "Gửi hóa đơn vào Email" button click event.
 */
jQuery(document).ready(function($) {
    $(document).on('click', '.hkt-btn-send-invoice', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $msg = $btn.siblings('.hkt-invoice-message');
        var orderId = $btn.data('order-id');
        var orderKey = $btn.data('order-key');

        if (!orderId || !orderKey) {
            return;
        }

        // Reset message state
        $msg.removeClass('success error').hide().text('');

        // Set loading state
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: hktInvoiceData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'hkt_ajax_send_invoice',
                nonce: hktInvoiceData.nonce,
                order_id: orderId,
                order_key: orderKey
            },
            success: function(response) {
                $btn.removeClass('loading');

                if (response.success) {
                    $msg.addClass('success').text(response.data.message || 'Hóa đơn đã được gửi thành công đến email của bạn!').fadeIn();
                    $btn.html('<span>Đã gửi hóa đơn ✓</span>');
                } else {
                    $btn.prop('disabled', false);
                    $msg.addClass('error').text(response.data.message || 'Đã xảy ra lỗi, vui lòng thử lại sau.').fadeIn();
                }
            },
            error: function(xhr, status, error) {
                $btn.removeClass('loading').prop('disabled', false);
                $msg.addClass('error').text('Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng.').fadeIn();
                console.error('HKT Invoice AJAX Error:', error);
            }
        });
    });
});
