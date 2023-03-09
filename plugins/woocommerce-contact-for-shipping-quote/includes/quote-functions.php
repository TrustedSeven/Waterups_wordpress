<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Get a shipping quote.
 *
 * Get a single shipping quote instance.
 *
 * @since 1.0.0
 *
 * @param int $quote_id Quote ID.
 * @return Shipping_Quote
 */
function get_shipping_quote( $quote_id ) {
	return Shipping_Quote::read( $quote_id );
}


/**
 * Get shipping quotes.
 *
 * Get a list of shipping quotes based on the arguments.
 *
 * @since 1.0.0
 *
 * @param $args
 * @return Shipping_Quote[]
 */
function get_shipping_quotes( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args( $args, array(
		'per_page' => 30,
		'page'     => 1,
		'status'   => 'any',
	) );

	$page = --$args['page'];
	$offset = $page * $args['per_page'];
	$where = array( '1=1');

	if ( array_intersect( (array) $args['status'], array_keys( get_statuses() ) ) ) {
//		$where[] = $wpdb->prepare( "AND status IN (" . implode( ', ', array_fill( 0, count( (array) $args['status'] ), '%s' ) ) . ")", $args['status'] );
		$sql = "AND status IN (" . implode( ', ', array_fill( 0, count( (array) $args['status'] ), '%s' ) ) . ")";
		$where[] = call_user_func_array( array( $wpdb, 'prepare' ), array_merge( array( $sql ), (array) $args['status'] ) );
	}

	$where = implode( ' ', $where );
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_quotes WHERE {$where} ORDER BY created_gmt DESC LIMIT %d,%d", $offset, $args['per_page'] ) );

	$quotes = array_map( function( $quote ) {
		return Shipping_Quote::read( $quote->id, (object) $quote );
	}, $results );

	return $quotes;
}


/**
 * Get statuses.
 *
 * Get a list of the valid/available statuses of a shipping quote.
 *
 * @since 1.0.0
 *
 * @return array List of valid and available shipping quote statuses.
 */
function get_statuses() {
	$statuses = array(
		'new'       => __( 'New', 'woocommerce-contact-for-shipping-quote' ),
		'pending'   => __( 'Pending', 'woocommerce-contact-for-shipping-quote' ),
		'completed' => __( 'Completed', 'woocommerce-contact-for-shipping-quote' ),
		'closed'    => __( 'Closed', 'woocommerce-contact-for-shipping-quote' ),
		'revoked'   => __( 'Revoked', 'woocommerce-contact-for-shipping-quote' ),
		'expired'   => __( 'Expired', 'woocommerce-contact-for-shipping-quote' ),
	);

	return apply_filters( 'WCCSQ/shipping_quote/statuses', $statuses );
}


/**
 * New shipping quote count.
 *
 * Get the number of new shipping quotes, used primarily for the menu counter.
 *
 * @since 1.0.0
 *
 * @return int
 */
function get_new_shipping_quote_count() {
	$quotes = get_shipping_quotes( array( 'status' => 'new', 'per_page' => 999 ) );

	return count( $quotes );
}


/**
 * Change default selected method.
 *
 * Change the default chosen shipping method. Used to change to the quote option when recovering.
 *
 * @since 1.3.0
 *
 * @param  string              $default Current default shipping rate ID.
 * @param  \WC_Shipping_Rate[] $rates   Available rates for package.
 * @param  string              $chosen  Chosen method (not set)
 * @return mixed                        Modified default shipping rate ID.
 */
function change_default_chosen_method( $default, $rates, $chosen ) {
	foreach ( $rates as $rate ) {
		if ( $rate->get_method_id() === 'custom_shipping_quote' ) {
			$default = $rate->get_id();
		}
	}

	return $default;
}
