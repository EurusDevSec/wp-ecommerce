---
name: flatsome_child
description: Customizing Flatsome Child Theme styling, structure overrides, templates, and UI conventions
---

# Flatsome Child Theme Customization Skill

Extending and customizing the Flatsome theme using its child theme (`flatsome-child`).

## 1. Global CSS Styling Rules
Define global CSS variable design tokens inside `flatsome-child/style.css` to override theme style colors consistently and avoid duplicate properties.

```css
/* In flatsome-child/style.css */
:root {
    --hkt-white: #FFFFFF;
    --hkt-dark: #1A1A1A;
    --hkt-gray: #2B2B2B;
    --hkt-border-radius: 4px;
    --hkt-font-headings: 'Montserrat', sans-serif;
    --hkt-font-body: 'Inter', sans-serif;
}

body {
    font-family: var(--hkt-font-body);
}

h1, h2, h3, h4, h5, h6 {
    font-family: var(--hkt-font-headings);
    font-weight: 700;
}
```

## 2. Flatsome Template Overrides
To modify Flatsome layouts or default HTML structures:
1.  Locate the target PHP file inside the parent theme directory (`wp-content/themes/flatsome/`).
2.  Duplicate that template file to the exact same path inside the child theme folder (`wp-content/themes/flatsome-child/`).
3.  Modify the copy inside the child theme; WordPress prioritizes the child theme path.
    *   *Example*: Override child layout `templates/woocommerce/single-product.php` to customize WooCommerce single page outputs.

## 3. Registering Custom Styles/Scripts
Always load custom assets via the `wp_enqueue_scripts` hook inside module files or `functions.php`.

```php
add_action( 'wp_enqueue_scripts', 'dev_enqueue_child_assets' );
function dev_enqueue_child_assets() {
    // Parent theme styles must load first
    wp_enqueue_style( 'flatsome-parent-style', get_template_directory_uri() . '/style.css' );
    
    // Custom JS utilities
    wp_enqueue_script(
        'dev-custom-scripts',
        get_stylesheet_directory_uri() . '/assets/js/custom-utils.js',
        array( 'jquery' ),
        '1.0.0',
        true // Load in footer
    );
}
```

## 4. Flatsome Structural Hooks
Flatsome defines special hooks to inject markup directly into headers, wrappers, and footers.
*   `flatsome_before_header`
*   `flatsome_after_header`
*   `flatsome_absolute_footer` (useful for injecting sticky elements or global components)
*   `flatsome_after_product_images`

```php
// Example: Adding mobile sticky bottom nav to Flatsome footer
add_action( 'flatsome_absolute_footer', 'dev_render_mobile_navigation' );
function dev_render_mobile_navigation() {
    if ( wp_is_mobile() ) {
        ?>
        <div class="mobile-bottom-nav">
             <!-- nav links -->
        </div>
        <?php
    }
}
```
