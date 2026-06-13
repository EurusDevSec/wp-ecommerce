<?php
/**
 * HTML Email Invoice Template for HKT Fashion
 *
 * Variables available:
 * @var WC_Order $order
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$order_id       = $order->get_id();
$order_date     = $order->get_date_created()->date_i18n( 'd/m/Y H:i' );
$billing_name   = $order->get_formatted_billing_full_name();
$billing_phone  = $order->get_billing_phone();
$billing_email  = $order->get_billing_email();

// Build billing address cascade format
$billing_address_1 = $order->get_billing_address_1();
$billing_ward      = $order->get_meta( '_billing_ward' ) ? $order->get_meta( '_billing_ward' ) : $order->get_billing_address_2();
$billing_district  = $order->get_meta( '_billing_district' ) ? $order->get_meta( '_billing_district' ) : $order->get_billing_city();
$billing_province  = $order->get_meta( '_billing_province' ) ? $order->get_meta( '_billing_province' ) : $order->get_billing_state();

// Fallback to standard WooCommerce addresses if metadata not set
if ( empty( $billing_ward ) ) $billing_ward = $order->get_billing_address_2();
if ( empty( $billing_district ) ) $billing_district = $order->get_billing_city();
if ( empty( $billing_province ) ) $billing_province = $order->get_billing_state();

$full_address_parts = array_filter( array( $billing_address_1, $billing_ward, $billing_province ) );
$full_address       = implode( ', ', $full_address_parts );

$currency = $order->get_currency();
$payment_method_title = $order->get_payment_method_title();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn điện tử #<?php echo esc_html( $order_id ); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f6f6; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f6f6f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border: 1px solid #e9e9e9; border-radius: 0px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); overflow: hidden;">
                    <!-- HEADER -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; background-color: #ffffff; border-bottom: 2px solid #1a1a1a; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <h1 style="font-family: 'Montserrat', 'Helvetica Neue', Arial, sans-serif; font-size: 26px; font-weight: 800; margin: 0; color: #1a1a1a; letter-spacing: 2px; text-transform: uppercase;">HKT FASHION</h1>
                                        <p style="font-size: 12px; color: #777777; margin: 5px 0 0 0; font-family: 'Inter', sans-serif;">MINIMALIST FASHION STORE</p>
                                    </td>
                                    <td align="right" style="vertical-align: top;">
                                        <span style="display: inline-block; padding: 6px 12px; background-color: #1a1a1a; color: #ffffff; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">ĐÃ THANH TOÁN</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- INVOICE INFO -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #fafafa; border-bottom: 1px solid #eeeeee;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="50%" style="vertical-align: top;">
                                        <h2 style="font-size: 12px; text-transform: uppercase; color: #888888; margin: 0 0 8px 0; letter-spacing: 1px;">Hóa đơn từ</h2>
                                        <p style="font-size: 14px; font-weight: bold; color: #1a1a1a; margin: 0 0 4px 0;">CÔNG TY CỔ PHẦN HKT COMPANY</p>
                                        <p style="font-size: 12px; color: #555555; margin: 0; line-height: 1.5;">
                                            180 Cao Lỗ, Phường 4, Quận 8, TP.HCM<br>
                                            Hotline: 0999.999.999<br>
                                            Email: support@hktfashion.vn
                                        </p>
                                    </td>
                                    <td width="50%" style="vertical-align: top; text-align: right;">
                                        <h2 style="font-size: 12px; text-transform: uppercase; color: #888888; margin: 0 0 8px 0; letter-spacing: 1px;">Thông tin hóa đơn</h2>
                                        <p style="font-size: 14px; font-weight: bold; color: #1a1a1a; margin: 0 0 4px 0;">Mã đơn hàng: #<?php echo esc_html( $order_id ); ?></p>
                                        <p style="font-size: 12px; color: #555555; margin: 0; line-height: 1.5;">
                                            Ngày lập: <?php echo esc_html( $order_date ); ?><br>
                                            Thanh toán: <?php echo esc_html( $payment_method_title ); ?><br>
                                            Loại hóa đơn: Hóa đơn bán hàng điện tử
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- BILL TO -->
                    <tr>
                        <td style="padding: 30px 40px; background-color: #ffffff; border-bottom: 1px solid #eeeeee;">
                            <h2 style="font-size: 12px; text-transform: uppercase; color: #888888; margin: 0 0 10px 0; letter-spacing: 1px;">Khách hàng nhận hóa đơn</h2>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="font-size: 14px; line-height: 1.6; color: #333333;">
                                        <strong>Họ và tên:</strong> <?php echo esc_html( $billing_name ); ?><br>
                                        <strong>Số điện thoại:</strong> <?php echo esc_html( $billing_phone ); ?><br>
                                        <strong>Email:</strong> <?php echo esc_html( $billing_email ); ?><br>
                                        <strong>Địa chỉ giao nhận:</strong> <?php echo esc_html( $full_address ); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- PRODUCTS TABLE -->
                    <tr>
                        <td style="padding: 30px 40px 20px 40px; background-color: #ffffff;">
                            <h2 style="font-size: 12px; text-transform: uppercase; color: #888888; margin: 0 0 15px 0; letter-spacing: 1px;">Chi tiết đơn hàng</h2>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 1px solid #1a1a1a;">
                                        <th align="left" style="padding: 10px 0; font-size: 12px; text-transform: uppercase; color: #1a1a1a; font-weight: bold;">Sản phẩm</th>
                                        <th align="center" style="padding: 10px 0; font-size: 12px; text-transform: uppercase; color: #1a1a1a; font-weight: bold; width: 60px;">SL</th>
                                        <th align="right" style="padding: 10px 0; font-size: 12px; text-transform: uppercase; color: #1a1a1a; font-weight: bold; width: 100px;">Đơn giá</th>
                                        <th align="right" style="padding: 10px 0; font-size: 12px; text-transform: uppercase; color: #1a1a1a; font-weight: bold; width: 110px;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ( $order->get_items() as $item_id => $item ) :
                                        if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
                                            continue;
                                        }
                                        $product      = $item->get_product();
                                        $name         = $item->get_name();
                                        $quantity     = $item->get_quantity();
                                        $subtotal     = $item->get_subtotal();
                                        $total        = $item->get_total();
                                        $formatted_subtotal = wc_price( $subtotal, array( 'currency' => $currency ) );
                                        $formatted_total    = wc_price( $total, array( 'currency' => $currency ) );

                                        // Render attributes (size, color, etc.)
                                        $meta_display = '';
                                        $meta_data = $item->get_formatted_meta_data();
                                        if ( ! empty( $meta_data ) ) {
                                            $meta_parts = array();
                                            foreach ( $meta_data as $meta ) {
                                                $meta_parts[] = $meta->display_key . ': ' . strip_tags( $meta->display_value );
                                            }
                                            $meta_display = '<div style="font-size: 11px; color: #777777; margin-top: 3px;">' . implode( ' | ', $meta_parts ) . '</div>';
                                        }

                                        // Try to get SKU
                                        $sku = $product ? $product->get_sku() : '';
                                        $sku_display = ! empty( $sku ) ? ' <span style="font-size: 11px; color: #999999;">(SKU: ' . $sku . ')</span>' : '';
                                        ?>
                                        <tr style="border-bottom: 1px solid #eeeeee;">
                                            <td style="padding: 15px 0; font-size: 13px; color: #1a1a1a; vertical-align: middle;">
                                                <div style="font-weight: 600;"><?php echo esc_html( $name ) . $sku_display; ?></div>
                                                <?php echo $meta_display; ?>
                                            </td>
                                            <td align="center" style="padding: 15px 0; font-size: 13px; color: #555555; vertical-align: middle; font-family: 'Inter', sans-serif;">
                                                <?php echo esc_html( $quantity ); ?>
                                            </td>
                                            <td align="right" style="padding: 15px 0; font-size: 13px; color: #555555; vertical-align: middle; font-family: 'Inter', sans-serif;">
                                                <?php echo wp_strip_all_tags( wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $currency ) ) ); ?>
                                            </td>
                                            <td align="right" style="padding: 15px 0; font-size: 13px; font-weight: 600; color: #1a1a1a; vertical-align: middle; font-family: 'Inter', sans-serif;">
                                                <?php echo wp_strip_all_tags( $formatted_total ); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- INVOICE TOTALS -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px; background-color: #ffffff;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="50%"></td>
                                    <td width="50%">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 13px; color: #555555;">
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee;">Tạm tính</td>
                                                <td align="right" style="padding: 8px 0; border-bottom: 1px solid #eeeeee; font-family: 'Inter', sans-serif;"><?php echo wp_strip_all_tags( wc_price( $order->get_subtotal(), array( 'currency' => $currency ) ) ); ?></td>
                                            </tr>
                                            <?php if ( $order->get_shipping_total() > 0 ) : ?>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee;">Phí vận chuyển</td>
                                                <td align="right" style="padding: 8px 0; border-bottom: 1px solid #eeeeee; font-family: 'Inter', sans-serif;"><?php echo wp_strip_all_tags( wc_price( $order->get_shipping_total(), array( 'currency' => $currency ) ) ); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php if ( $order->get_discount_total() > 0 ) : ?>
                                            <tr>
                                                <td style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #c62828;">Giảm giá</td>
                                                <td align="right" style="padding: 8px 0; border-bottom: 1px solid #eeeeee; color: #c62828; font-family: 'Inter', sans-serif;">-<?php echo wp_strip_all_tags( wc_price( $order->get_discount_total(), array( 'currency' => $currency ) ) ); ?></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <td style="padding: 12px 0; font-size: 15px; font-weight: bold; color: #1a1a1a;">Tổng thanh toán</td>
                                                <td align="right" style="padding: 12px 0; font-size: 17px; font-weight: 800; color: #1a1a1a; font-family: 'Inter', sans-serif;"><?php echo wp_strip_all_tags( wc_price( $order->get_total(), array( 'currency' => $currency ) ) ); ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- FOOTER & THANKS -->
                    <tr>
                        <td style="padding: 40px; background-color: #1a1a1a; color: #ffffff; text-align: center;">
                            <p style="font-family: 'Montserrat', Arial, sans-serif; font-size: 14px; font-weight: 700; margin: 0 0 10px 0; letter-spacing: 1.5px; text-transform: uppercase;">CẢM ƠN BẠN ĐÃ MUA SẮM CÙNG HKT FASHION!</p>
                            <p style="font-size: 12px; color: #aaaaaa; margin: 0 0 20px 0; line-height: 1.6; font-family: 'Inter', sans-serif;">
                                Đơn hàng của bạn đã được thanh toán và đang được xử lý để giao nhận nhanh nhất.<br>
                                Cam kết đổi trả hàng trong vòng 7 ngày nếu không vừa size hoặc có lỗi từ nhà sản xuất.
                            </p>
                            <p style="font-size: 11px; color: #777777; margin: 0; border-top: 1px solid #333333; padding-top: 20px; font-family: 'Inter', sans-serif;">
                                Đây là email hóa đơn tự động từ HKT Fashion. Vui lòng không trả lời trực tiếp email này.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
