<?php
/**
 * My Account page - HKT Fashion Child Theme Customization
 *
 * Overrides the default My Account template to prevent duplicate navigation sidebars
 * and ensure full-width rendering of the account content columns.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-MyAccount-content" style="width: 100%; float: none;">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
</div>
