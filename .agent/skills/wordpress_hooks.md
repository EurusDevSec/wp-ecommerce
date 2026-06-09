---
name: wordpress_hooks
description: Standard patterns and rules for registering and overriding actions and filters in WordPress child themes
---

# WordPress Action & Filter Hooks Skill

WordPress uses a hook-based architecture. To maintain clean, scalable, and modular child theme code, adhere to the following patterns.

## 1. Modular Hook Organization
*   **Zero Logic in `functions.php`**: `functions.php` should only act as a bootstrap file that imports individual module files from the `inc/` directory.
    ```php
    // In functions.php
    $custom_inc_files = array(
        'inc/checkout-customizer.php',
        'inc/vietnam-divisions.php',
        'inc/payment-gateways.php',
    );
    foreach ( $custom_inc_files as $file ) {
        $filepath = locate_template( $file );
        if ( $filepath ) {
            require_once $filepath;
        }
    }
    ```
*   **Direct File Exit Guard**: Every file in the `inc/` directory must start with a direct execution guard:
    ```php
    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    ```

## 2. Naming Conventions & Namespace Isolation
*   **Function Prefix Rule**: All custom hook callbacks must be prefixed with `dev_` to prevent namespace collisions with core WordPress, parent theme (Flatsome), or third-party plugins.
    *   *Correct*: `dev_optimize_checkout_fields`, `dev_api_get_provinces_callback`
    *   *Incorrect*: `optimize_checkout_fields`, `get_provinces`
*   **Action & Filter Registration**: Place `add_action()` and `add_filter()` calls directly above the function declaration they invoke for maximum readability.
    ```php
    add_filter( 'woocommerce_checkout_fields', 'dev_optimize_checkout_fields' );
    function dev_optimize_checkout_fields( $fields ) {
        // logic
        return $fields;
    }
    ```

## 3. Prioritizing Hooks
*   **Execution Order**: The third parameter of hook registration controls priority (default is `10`).
    *   Use lower numbers (e.g., `5`) to run earlier (e.g., custom setup, routing registration).
    *   Use higher numbers (e.g., `20`, `99`, `999`) to run later or override values set by other plugins.
    ```php
    // Overriding a default WooCommerce checkout field layout requires a later priority
    add_filter( 'woocommerce_checkout_fields', 'dev_optimize_checkout_fields', 20 );
    ```

## 4. Removing Hooks
*   To disable a core or parent theme hook, call `remove_action` or `remove_filter` using the **exact same priority** and **callback name** registered by the original source.
    ```php
    // Removing parent theme action
    remove_action( 'woocommerce_after_single_product_summary', 'flatsome_output_related_products', 20 );
    ```
*   **Timing Guard**: You must run the removal inside an action hook that triggers after the theme has loaded, such as `init` or `after_setup_theme`.
    ```php
    add_action( 'init', 'dev_remove_parent_theme_actions' );
    function dev_remove_parent_theme_actions() {
        remove_action( 'woocommerce_after_single_product_summary', 'flatsome_output_related_products', 20 );
    }
    ```
