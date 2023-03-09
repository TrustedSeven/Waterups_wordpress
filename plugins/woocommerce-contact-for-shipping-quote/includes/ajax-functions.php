<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Request quote handler.
 *
 * AJAX handler when a shipping quote is requested.
 *
 * @since 1.0.0
 */
function request_shipping_quote() {
	check_ajax_referer( 'contact-for-shipping-quote', 'nonce' );

	// Check for field requirements
	$required_data = get_option( 'shipping_quote_required_data', array() );
	if ( ! empty( $required_data ) ) {

		$missing_fields = array();
		foreach ( $required_data as $k ) {
			$v = get_value_for_quote( $k );

			if ( empty( $v ) ) {
				$missing_fields[] = $k;
			}
		}

		if ( ! empty( $missing_fields ) ) {
			if ( $_POST['cart'] ) {
				wc_add_notice( __( 'Not all required data is available to request a shipping quote. Please enter your information at the checkout and try again.', 'woocommerce-contact-for-shipping-quote' ), 'error' );
			} else {
				foreach ( $missing_fields as $k ) {
					$field_name = get_field_name( $k );
					wc_add_notice( sprintf( __( '%s is a required field to request a shipping quote', 'woocommerce-contact-for-shipping-quote' ), '<strong>' . $field_name . '</strong>' ), 'error' );
				}
			}

			wp_send_json( array(
				'success' => false,
				'notice' => wc_print_notices( true ),
			) );
			die;
		}
	}

	$cart = null;
	$package_id = absint( $_POST['package'] ) ?? null;
	$packages = WC()->cart->get_shipping_packages();

	if ( isset( $package_id, $packages[ $package_id ] ) ) {
		$cart = $packages[ $package_id ]['contents'];
	}

	$address_hash = get_address_hash();
	$cart_hash = get_cart_hash( $cart );

	// Try to get a existing quote
	$quote = Shipping_Quote::read_by( array( 'address_hash' => $address_hash, 'cart_hash' => $cart_hash, 'status' => array( 'new', 'pending' ) ), array( '%s', '%s', '%s' ) );

	// If not available, create one.
	if ( ! $quote ) {

		$quote = Shipping_Quote::create( array(
			'customer_email' => get_value_for_quote( 'email' ),
			'customer_phone' => get_value_for_quote( 'phone' ),
			'cart_contents'  => maybe_serialize( get_cart_data( $cart ) ),
			'cart_hash'      => sanitize_key( get_cart_hash( $cart ) ),
		) );

		WC()->mailer()->get_emails(); // Init WC Emails to add trigger
		do_action( 'WCCSQ/requested_shipping_quote', $quote );
	}

	wp_send_json( array(
		'success'      => $quote !== false,
		'cart_hash'    => $quote->get_cart_hash(),
		'cart_data'    => get_cart_data( $cart ),
		'address_hash' => $quote->get_address_hash(),
		'update_cart'  => true,
	) );
}
add_action( 'wp_ajax_wccsq_request_shipping_quote', '\WooCommerce_Contact_for_Shipping_Quote\request_shipping_quote' );
add_action( 'wp_ajax_nopriv_wccsq_request_shipping_quote', '\WooCommerce_Contact_for_Shipping_Quote\request_shipping_quote' );


/**
 * Update order review.
 *
 * Set customer fields that are normally not updated (e.g. name).
 *
 * @since 1.2.0
 */
function update_order_review_set_customer_fields( $post ) {
	$post_data = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '';
	$post_data = wp_parse_args( $post_data );

	WC()->customer->set_props(
		array(
			'billing_first_name' => isset( $post_data['billing_first_name'] ) ? wc_clean( wp_unslash( $post_data['billing_first_name'] ) ) : null,
			'billing_last_name'  => isset( $post_data['billing_last_name'] ) ? wc_clean( wp_unslash( $post_data['billing_last_name'] ) ) : null,
		)
	);

	if ( wc_ship_to_billing_address_only() ) {
		WC()->customer->set_props(
			array(
				'shipping_first_name' => isset( $post_data['billing_first_name'] ) ? wc_clean( wp_unslash( $post_data['billing_first_name'] ) ) : null,
				'shipping_last_name'  => isset( $post_data['billing_last_name'] ) ? wc_clean( wp_unslash( $post_data['billing_last_name'] ) ) : null,
			)
		);
	} else {
		WC()->customer->set_props(
			array(
				'shipping_first_name' => isset( $_POST['shipping_first_name'] ) ? wc_clean( wp_unslash( $_POST['shipping_first_name'] ) ) : null,
				'shipping_last_name'  => isset( $_POST['shipping_last_name'] ) ? wc_clean( wp_unslash( $_POST['shipping_last_name'] ) ) : null,
			)
		);
	}
}
add_action( 'woocommerce_checkout_update_order_review', '\WooCommerce_Contact_for_Shipping_Quote\update_order_review_set_customer_fields' );


/**
 * Update popup.
 *
 * Update the popup when the cart/checkout refreshes.
 *
 * @since 1.4.0
 *
 * @param  array $fragments List of existing fragments.
 * @return array            List of modified fragments,
 */
function checkout_fragments_update_popup( $fragments ) {
    $packages = WC()->shipping()->get_packages();

	foreach ( $packages as $package ) {
		foreach ( $package['rates'] as $rate ) {
			if ( $rate->get_method_id() != 'custom_shipping_quote' ) continue;

			$method  = new WCCSQ_Shipping_Quote_Method( $rate->get_instance_id() );
			$page_id = $method->get_option( 'contact_popup_page_id' );

			ob_start();
				popup( $page_id );
			$fragments['.wccsq-popup-wrap'] = ob_get_clean();
		}
	}

	return $fragments;
}
add_action( 'woocommerce_update_order_review_fragments', '\WooCommerce_Contact_for_Shipping_Quote\checkout_fragments_update_popup' );
