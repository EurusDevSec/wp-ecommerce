---
name: woocommerce_checkout
description: Customizing WooCommerce checkout fields, validating custom fields, and saving order meta using HPOS-compliant methods
---

# WooCommerce Checkout Customization Skill

Customizing checkout layouts, fields, validation, and order metadata storage.

## 1. Modifying Checkout Fields
Use the `woocommerce_checkout_fields` filter to modify properties, unset fields, or inject custom dropdowns.
*   **Unsetting Fields**: Use `unset` to remove unused fields.
*   **Reordering Fields**: Set the `priority` key on each field to determine visual layout sequence.
*   **Modifying Type**: Change the `type` property (e.g., `'select'` or `'text'`) and provide an `options` array for select fields.

```php
add_filter( 'woocommerce_checkout_fields', 'dev_customize_checkout_fields' );
function dev_customize_checkout_fields( $fields ) {
    // Unset unnecessary fields
    unset( $fields['billing']['billing_company'] );
    unset( $fields['billing']['billing_postcode'] );

    // Convert billing_city (District) to dynamic select
    $fields['billing']['billing_city']['type']        = 'select';
    $fields['billing']['billing_city']['label']       = 'Quận / Huyện';
    $fields['billing']['billing_city']['options']     = array( '' => 'Chọn Quận / Huyện' );
    $fields['billing']['billing_city']['priority']    = 60;
    
    return $fields;
}
```

## 2. Setting Default Country
WooCommerce state fields render dynamically based on the current country. Ensure the country is hardcoded or configured as default:
*   Ensure option `woocommerce_default_country` is set to `VN` in database.
*   Or force the billing country programmatically:
```php
add_filter( 'default_checkout_billing_country', 'dev_default_checkout_country' );
function dev_default_checkout_country() {
    return 'VN';
}
```

## 3. Checkout Field Validation
Use the `woocommerce_after_checkout_validation` hook to inspect posted fields and append error notices via the `$errors` parameter.

```php
add_action( 'woocommerce_after_checkout_validation', 'dev_validate_checkout_fields', 10, 2 );
function dev_validate_checkout_fields( $data, $errors ) {
    // Validate VN phone number format
    if ( ! empty( $data['billing_phone'] ) ) {
        $phone = sanitize_text_field( $data['billing_phone'] );
        if ( ! preg_match( '/^(0[3|5|7|8|9])[0-9]{8}$/', $phone ) ) {
            $errors->add( 'validation', '<strong>Số điện thoại</strong> không hợp lệ. Vui lòng nhập số điện thoại Việt Nam (ví dụ: 0912345678).' );
        }
    }
}
```

## 4. HPOS-Compliant Data Storage
High-Performance Order Storage (HPOS) is standard in modern WooCommerce. Never query or insert directly into the `wp_postmeta` database table. Instead, interact with the WooCommerce order object using its getter and setter APIs.

### A. During Checkout Creation (Recommended)
Hook into `woocommerce_checkout_create_order` to alter properties and add metadata before the order is saved to the database. This hook passes the `$order` object by reference and does not require calling `$order->save()`.
```php
add_action( 'woocommerce_checkout_create_order', 'dev_save_custom_order_meta', 10, 2 );
function dev_save_custom_order_meta( $order, $data ) {
    // Set standard properties
    $order->set_billing_city( sanitize_text_field( $_POST['billing_city'] ) );
    
    // Save custom metadata keys
    $order->update_meta_data( '_custom_tracking_id', 'hkt-' . time() );
}
```

### B. Updating Orders Post-Checkout
If modifying orders asynchronously or on admin events, load the order using `wc_get_order()` and always remember to explicitly invoke `$order->save()`.
```php
$order = wc_get_order( $order_id );
if ( $order ) {
    $order->update_meta_data( '_payment_ref', 'VCB123456' );
    $order->save(); // Commits changes to the database
}
```
