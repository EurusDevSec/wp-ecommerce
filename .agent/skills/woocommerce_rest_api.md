---
name: woocommerce_rest_api
description: Creating secure and efficient custom WP REST API endpoints for WooCommerce utilities
---

# WooCommerce REST API Skill

WordPress REST API endpoints allow decoupled clients or frontend JavaScript to interact with backend data.

## 1. Registering Custom Routes
Always hook registration functions into the `rest_api_init` action. Namespace routes consistently under `hkt/v1`.

```php
add_action( 'rest_api_init', 'dev_register_hkt_routes' );
function dev_register_hkt_routes() {
    register_rest_route( 'hkt/v1', '/districts', array(
        'methods'             => 'GET', // Or WP_REST_Server::READABLE
        'callback'            => 'dev_api_get_districts',
        'permission_callback' => '__return_true', // Public access
    ) );

    register_rest_route( 'hkt/v1', '/update-settings', array(
        'methods'             => 'POST', // Or WP_REST_Server::CREATABLE
        'callback'            => 'dev_api_update_settings',
        'permission_callback' => 'dev_api_check_admin_permissions', // Secure access
    ) );
}
```

## 2. Security and Permission Callbacks
*   **Public Access**: Explicitly pass `'__return_true'` as a string instead of leaving it empty. Leaving it empty is deprecated in newer WP versions.
*   **Restricted Access**: Perform Capability Checks inside a designated callback function.
    ```php
    function dev_api_check_admin_permissions() {
        return current_user_can( 'manage_woocommerce' );
    }
    ```

## 3. Handling Requests and Responses
*   Use `WP_REST_Request` to retrieve query variables, URL parameters, or JSON body payloads.
*   Always respond with a `WP_REST_Response` instance to control HTTP status codes and headers.
*   Perform input sanitization using `sanitize_text_field` or specialized type sanitization.

```php
function dev_api_get_districts( WP_REST_Request $request ) {
    // 1. Retrieve & sanitize parameter
    $province_id = sanitize_text_field( $request->get_param( 'province_id' ) );
    
    if ( empty( $province_id ) ) {
        return new WP_REST_Response( array(
            'code'    => 'missing_parameter',
            'message' => 'Missing province_id parameter.',
        ), 400 );
    }

    // 2. Fetch and filter data
    $districts = dev_fetch_districts_by_province( $province_id );
    
    if ( empty( $districts ) ) {
        return new WP_REST_Response( array(), 404 );
    }

    // 3. Return response with 200 OK
    return new WP_REST_Response( $districts, 200 );
}
```

## 4. Transients and Static Caching
For high-traffic read-only APIs (like address division data), optimize database queries or remote requests using file caching or transients:
```php
function dev_get_cached_data() {
    $cache_key = 'hkt_cached_remote_data';
    $data      = get_transient( $cache_key );

    if ( false === $data ) {
        // Cache missed, fetch data
        $response = wp_remote_get( 'https://api.external.com/data' );
        if ( ! is_wp_error( $response ) ) {
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            // Store transient for 1 day (86400 seconds)
            set_transient( $cache_key, $data, DAY_IN_SECONDS );
        }
    }

    return $data;
}
```
