<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Schedule events.
 *
 * Add events to the schedule related to shipping quotes.
 *
 * @since 1.0.0
 */
function schedule_events() {
	// Expire shipping quotes
	if ( ! wp_next_scheduled( 'wccsq/quote-expiration' ) ) {
		wp_schedule_event( 1407110400, 'hourly', 'wccsq/quote-expiration' ); // 1407110400 is 08 / 4 / 2014 @ 0:0:0 UTC
	}
}
add_action( 'init', '\WooCommerce_Contact_for_Shipping_Quote\schedule_events' );


/**
 * Expire shipping quotes.
 *
 * Expire shipping quotes that are outside the window set in the plugin settings.
 *
 * @since 1.0.0
 */
function expire_shipping_quotes() {
	global $wpdb;

	$expiration_days = get_option( 'shipping_quote_expiration', 2 );
	$expiration_date = date( 'Y-m-d H:i:s', strtotime( '-' . floatval( $expiration_days ) . ' days' ) );

	if ( $expiration_days <= 0 ) {
		return;
	}

	$results = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}woocommerce_shipping_quotes WHERE status IN ('new', 'pending') AND created_gmt <= '{$expiration_date}'" );
	$expired_quote_ids = array_column( $results, 'id' );

	if ( ! empty( $expired_quote_ids ) ) {
		$wpdb->query( "UPDATE {$wpdb->prefix}woocommerce_shipping_quotes set status = 'expired' WHERE status IN ('new', 'pending') AND created_gmt <= '{$expiration_date}' AND id IN (" . implode( ', ', $expired_quote_ids ) . ")" );

		do_action( 'WCCSQ/expired_shipping_quotes', $expired_quote_ids );
	}

}
add_action( 'wccsq/quote-expiration', '\WooCommerce_Contact_for_Shipping_Quote\expire_shipping_quotes' );
