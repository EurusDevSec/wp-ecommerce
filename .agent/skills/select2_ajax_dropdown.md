---
name: select2_ajax_dropdown
description: Implementing dynamic cascading Select2 fields using AJAX and preventing event-loop collisions on WooCommerce checkout
---

# Select2 Dynamic Cascading Dropdowns Skill

Implementing dynamic cascading fields (e.g., Province → District → Ward) on the WooCommerce checkout page requires handling jQuery events and Select2 element lifecycles.

## 1. Initializing Select2 Elements
WooCommerce integrates Select2 natively. When targeting checkout selects, check if Select2 is loaded, then initialize standard classes or targets.
```javascript
function initializeSelect2Field(selector, placeholderText) {
    var $el = jQuery(selector);
    if ($el.length && typeof jQuery.fn.select2 !== 'undefined') {
        $el.select2({
            placeholder: placeholderText,
            allowClear: true,
            width: '100%'
        });
    }
}
```

## 2. Event Listeners for Cascading Dependencies
When a parent selection changes:
1.  Clear child selectors completely.
2.  Reset values.
3.  Notify Select2 to refresh visually via `trigger('change.select2')`.
4.  Trigger AJAX requests to populate downstream selects.

```javascript
jQuery(document).ready(function($) {
    // Listen for Province select change
    $(document.body).on('change', '#billing_state', function() {
        var provinceId = $(this).val();
        
        // Reset and clear child dropdowns (District & Ward)
        var $districtSelect = $('#billing_city');
        var $wardSelect = $('#billing_address_2');
        
        $districtSelect.html('<option value="">Chọn Quận / Huyện</option>').val('').trigger('change.select2');
        $wardSelect.html('<option value="">Chọn Xã / Phường / Thị trấn</option>').val('').trigger('change.select2');
        
        if (!provinceId) return;

        // Fetch new districts via REST API
        $.ajax({
            url: '/wp-json/hkt/v1/districts?province_id=' + provinceId,
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $districtSelect.prop('disabled', true);
            },
            success: function(response) {
                var options = '<option value="">Chọn Quận / Huyện</option>';
                $.each(response, function(index, item) {
                    options += '<option value="' + item.district_id + '">' + item.district_name + '</option>';
                });
                $districtSelect.html(options).prop('disabled', false).trigger('change.select2');
            },
            error: function() {
                $districtSelect.prop('disabled', false);
            }
        });
    });
});
```

## 3. Handling WooCommerce `updated_checkout` Re-runs
WooCommerce fires the `updated_checkout` event on `$(document.body)` every time checkout fields change or payment methods reload. This replaces parts of the DOM, which breaks raw Select2 setups and strips dynamic values.

### The Guard Pattern
To avoid duplicate initialization or losing selected values, use jQuery `.data()` flags to detect if Select2 is already configured for the lifecycle, and read current values from fields to re-populate them if missing.

```javascript
jQuery(document.body).on('updated_checkout', function() {
    // Prevent double binding by checking a custom data flag
    var $province = jQuery('#billing_state');
    if ($province.length && !$province.data('hkt-initialized')) {
        $province.data('hkt-initialized', true);
        
        // Custom bootstrap logic here
        bootstrapSelects();
    }
});

function bootstrapSelects() {
    // Check if dropdowns have current values (e.g. from previous step or database)
    // and trigger appropriate AJAX requests to restore selected options
    var currentProvince = jQuery('#billing_state').val();
    if (currentProvince) {
        // Fetch and load selected child items, then set values programmatically
    }
}
```

## 4. Resetting Select2 Values Safely
Always update both the underlying HTML `<select>` tag value and trigger the specific event suffix `.select2` so Select2 redraws:
*   *Correct*: `jQuery('#selector').val('12').trigger('change.select2')` or `jQuery('#selector').val('').trigger('change')`
*   *Incorrect*: `jQuery('#selector').val('12')` (Dropdown remains visually unchanged)
