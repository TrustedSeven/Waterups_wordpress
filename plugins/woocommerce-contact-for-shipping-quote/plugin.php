<?php
/**
 * Plugin Name:     WooCommerce Contact for Shipping Quote
 * Plugin URI:      https://woocommerce.com/products/woocommerce-contact-for-shipping-quote/
 * Description:     Allow customers to requests a shipping quote at the cart/checkout. Fully build in system to provide customers with your custom shipping cost.
 * Version:         1.4.1
 * Author:          Jeroen Sormani
 * Author URI:      https://jeroensormani.com
 * Text Domain:     woocommerce-contact-for-shipping-quote
 *
 * WC requires at least: 5.0
 * WC tested up to: 7.2
 * Woo: 4229507:43f1dd3482682908c8c1c2531e74e6e8
 */


/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

define( 'WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE', __FILE__ );
require 'woocommerce-contact-for-shipping-quote.php';

// Declare HPOS compatibility
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Show a notice at activation.
 */
function wccfsq_activation_notice() {
	global $pagenow;

	if ( $pagenow == 'plugins.php' && get_transient( 'wccfsq_activation_notice' ) ) {
		?><div class="updated notice is-dismissible">
			<p><?php echo sprintf( __( 'To start using the Contact for Shipping Quote plugin, head over to your shipping zones and %screate a \'Contact for shipping quote\' shipping rate%s.', 'woocommerce-contact-for-shipping-quote' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section' ) ) . '">', '</a>'
			);
			?><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'woocommerce-contact-for-shipping-quote' ); ?></span></button></p>
		</div><?php

		delete_transient( 'wccfsq_activation_notice' );
	}

}
add_action( 'admin_notices', 'wccfsq_activation_notice' );

function wccfsq_on_activation() {
	set_transient( 'wccfsq_activation_notice', 1, 30 ); // 30 seconds
}
register_activation_hook( __FILE__, 'wccfsq_on_activation' );
