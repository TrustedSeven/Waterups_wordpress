<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Add shipping method description.
 *
 * Add a additional description to the shipping method for the 'Contact' link.
 *
 * @since 1.0.0
 *
 * @param \WC_Shipping_Rate $rate
 * @param $index
 */
function add_shipping_method_contact_description( $rate, $index ) {
	if ( $rate->method_id != 'custom_shipping_quote' ) {
		return;
	}

	/** @var WCCSQ_Shipping_Quote_Method $method */
	$method   = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );
	$packages = WC()->cart->get_shipping_packages();
	$package  = $packages[ $index ] ?? null;
	$quote    = $method->get_quote( $package );

	$classes = '';
	if ( $quote && $quote->get_status_slug() === 'new' ) {
		$classes .= 'wccsq-quote-requested'; // Quote exists and is pending
	}

	if ( $quote && ! is_null( $quote->get_quote_amount() ) ) {
		$classes .= ' wccsq-quote-available'; // Quote amount is set
	}

	?><p class="wccsq-quote-description <?php echo esc_attr( $classes ); ?>"><?php echo wp_kses_post( $method->get_description() ); ?></p><?php

	if ( get_option( 'shipping_quote_debug_mode' ) == 'yes' ) {
		?><p>
			<strong>Quote ID:</strong><br/>
			<?php echo $quote ? $quote->get_id() . ' (' . $quote->get_status() . ')' : 'No quote found'; ?><br/>
			<strong>Address:</strong><br/>
			<?php echo get_address_hash(); ?>
			<pre><?php print_r( get_address_data() ); ?></pre><br/>
			<strong>Cart:</strong><br/>
			<?php echo get_cart_hash( $package['contents'] ); ?>
			<pre><?php print_r( get_cart_data( $package['contents'] ) ); ?></pre>
		</p><?php
	}
}
add_filter( 'woocommerce_after_shipping_rate', '\WooCommerce_Contact_for_Shipping_Quote\add_shipping_method_contact_description', 10, 2 );


/**
 * Hide rates when Shipping Quote is available.
 *
 * Hide other rates (based on user settings) when the shipping quote
 * option is available.
 *
 * @since 1.0.0
 *
 * @param array $rates List of existing rates.
 * @param array $package Package data.
 * @return mixed List of filtered rates.
 */
function hide_rates_when_shipping_quote_is_available(  $rates, $package ) {

	$excluded_rates = array();
	$method_ids = wp_list_pluck( $rates, 'method_id' );

	if ( in_array( 'custom_shipping_quote', $method_ids ) ) {
		$rate_index = array_search( 'custom_shipping_quote', $method_ids );
		$rate = $rates[ $rate_index ];
		$shipping_method = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );

		$excluded_rates = $shipping_method->get_option( 'exclude_methods' );
	}

	if ( ! empty( $excluded_rates ) ) {
		foreach ( $rates as $k => $rate ) {
			if ( array_intersect( array( $rate->id, $rate->method_id, $rate->instance_id ), $excluded_rates ) ) {
				unset( $rates[ $k ] );
			}
		}
	}

	return $rates;
}
add_filter( 'woocommerce_package_rates', '\WooCommerce_Contact_for_Shipping_Quote\hide_rates_when_shipping_quote_is_available', 10, 2 );


/**
 * Add time to package.
 *
 * Add a timestamp to the package when the Contact for Shipping Quote option has been selected.
 * This ensures shipping rates (quotes) are re-calculated on refresh to ensure updates/provided
 * quotes are pushed through.
 *
 * @since 1.1.0
 *
 * @param  array $packages Existing packages.
 * @return mixed           Modified packages.
 */
function add_package_shipping_quote_time( $packages ) {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

	foreach ( $packages as $i => $package ) {

		if ( isset( $chosen_methods[ $i ] ) && strpos( $chosen_methods[ $i ], 'custom_shipping_quote' ) === 0 ) {
			$packages[ $i ]['shipping_quote_time'] = time();
		}
	}

	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', '\WooCommerce_Contact_for_Shipping_Quote\add_package_shipping_quote_time' );


/**
 * Hide $0 cost label.
 *
 * Change the shipping label to hide the $0 label when the quote is yet to be made.
 *
 * @since 1.0.0
 *
 * @param string $label Existing label.
 * @param \WC_Shipping_Rate $method Shipping method being displayed.
 * @return string Modified label.
 */
function hide_empty_shipping_cost( $label, $method ) {
	if ( $method->method_id === 'custom_shipping_quote' && $method->cost == 0 ) {
		$label = $method->get_label();
	}

	return $label;
}
add_filter( 'woocommerce_cart_shipping_method_full_label', '\WooCommerce_Contact_for_Shipping_Quote\hide_empty_shipping_cost', 10, 2 );


/**
 * Update quote status.
 *
 * Update the quote status on the thank-you page.
 *
 * @since 1.3.0
 *
 * @param int $order_id ID of the order.
 */
function thankyou_complete_shipping_quote( $order_id ) {
	$order          = wc_get_order( $order_id );
	$shipping_lines = $order->get_shipping_methods();

	foreach ( $shipping_lines as $shipping ) {
		if ( $shipping->get_method_id() != 'custom_shipping_quote' ) {
			continue;
		}

		$quote_id = $shipping->get_meta( 'quote_id' );
		$quote    = Shipping_Quote::read( $quote_id );

		// Update quote to completed when it was pending
		if ( $quote && $quote->get_status_slug() === 'pending' ) {
			$quote->set_status( 'completed' );
			$quote->save();
		}
	}
}
add_action( 'woocommerce_thankyou', '\WooCommerce_Contact_for_Shipping_Quote\thankyou_complete_shipping_quote', 10 );


/**
 * Hide payment gateways.
 *
 * Hide the payment gateways when the 'Additional features' setting is set to hide it and
 * the quote is not yet available.
 *
 * @since 1.4.0
 *
 * @param  bool $needs_payment Whether the order requires payment.
 * @return bool                Modified value of order requiring payment.
 */
function needs_payment( $needs_payment ) {
	$packages = WC()->shipping()->get_packages();

	// Get chosen methods for each package to get our totals.
	foreach ( $packages as $key => $package ) {

		$chosen_method = wc_get_chosen_shipping_method_for_package( $key, $package );
		if ( ! $chosen_method ) {
			continue;
		}

		$rate = $package['rates'][ $chosen_method ];
		if ( ! $rate || $rate->get_method_id() != 'custom_shipping_quote' ) {
			continue;
		}

		$method = new WCCSQ_Shipping_Quote_Method( $rate->get_instance_id() );
		$additional_features = (array) $method->get_option( 'additional_features' );

		if ( ! in_array( 'hide_payment_gateways', $additional_features ) ) {
			return $needs_payment;
		}

		$quote = $method->get_quote();
		if ( ! $quote || $quote->get_status_slug() != 'pending' ) {
			return false;
		}
	}

	return $needs_payment;
}
add_filter( 'woocommerce_cart_needs_payment', '\WooCommerce_Contact_for_Shipping_Quote\needs_payment' );


/**
 * Prevent completion without a quote.
 *
 * Prevent the order being completed when the quote rate is selected, but not quote is set.
 *
 * @since 1.0.0
 */
function validate_checkout_quote() {
	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
	$packages = WC()->shipping()->get_packages();

	$has_shipping_quote = false;
	foreach ( $chosen_methods as $method ) {
		if ( strpos( $method, 'custom_shipping_quote' ) !== false ) {
			$has_shipping_quote = true;
		}
	}

	if ( ! $has_shipping_quote ) {
		return false;
	}

	foreach ( $packages as $package_key => $package ) {
		/** @var \WC_Shipping_Rate $chosen_method */
		$chosen_method_key = $chosen_methods[ $package_key ] ?? false;
		$chosen_method     = $package['rates'][ $chosen_method_key ] ?? false;

		if ( $chosen_method && $chosen_method->get_method_id() == 'custom_shipping_quote' ) {
			$quote = Shipping_Quote::read_by( array( 'status' => array( 'pending' ), 'address_hash' => get_address_hash(), 'cart_hash' => get_cart_hash( $package['contents'] ) ), array( '%s', '%s', '%s' ) );

			$shipping_method     = new WCCSQ_Shipping_Quote_Method( $chosen_method->get_instance_id() );
			$additional_features = (array) $shipping_method->get_option( 'additional_features' );
			$pay_order_later     = in_array( 'pay_order_later', $additional_features );

			if ( ! $quote && ! $pay_order_later ) { // Quote must exist + have 'pending' status (only status that is valid for continuing)
				$text = apply_filters( 'WCCSQ/quote_not_available_error',
					__( 'Your shipping quote is not yet available.', 'woocommerce-contact-for-shipping-quote' ),
					$package,
					$quote
				);

				$text = apply_filters( 'woocommerce_contact_for_shipping_quote/quote_not_available_error', $text, $package, $quote ); // @deprecated

				wc_add_notice( $text, 'error' );
			}
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', '\WooCommerce_Contact_for_Shipping_Quote\validate_checkout_quote' );


/**
 * Output popup.
 *
 * Output the popup HTML.
 *
 * @since 1.0.0
 *
 * @param int $page_id ID of the page content to display in the popup.
 */
function popup( $page_id ) {
	$post = get_post( $page_id );
	$content = $post->post_content;
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);

	?><div class="wccsq-popup-wrap" id="wccsq-popup-<?php echo absint( $page_id ); ?>">
		<div class="wccsq-popup-bg" style="display: none;"></div>
		<div class="wccsq-popup" style="display: none;">
			<span class="wccsq-close"></span>
			<div class="wccsq-body">
				<?php echo $content; ?>
			</div>
		</div>
	</div><?php
}


/**
 * Add popup HTML.
 *
 * Add popup HTML to the cart/checkout pages.
 *
 * @since 1.0.0
 *
 * @param \WC_Shipping_Method $method Shipping method instance.
 * @param \WC_Shipping_Rate   $rate   Rate details.
 */
function add_popup_html() {
	if ( ! is_checkout() && ! is_cart() ) return;

	$packages    = WC()->shipping()->get_packages();
	$shown_popup = false;

	foreach ( $packages as $package ) {
		foreach ( $package['rates'] as $rate ) {

			if ( $rate->get_method_id() == 'custom_shipping_quote' ) {
				$method  = new WCCSQ_Shipping_Quote_Method( $rate->get_instance_id() );
				$page_id = $method->get_option( 'contact_popup_page_id' );
				$page_id = apply_filters( 'wpml_object_id', $page_id, 'page', true );

				if ( $page_id ) {
					popup( $page_id );
				}

				$shown_popup = true;
			}
		}
	}

	// Show a template when no popup is loaded. This ensures the popup appears when a new Quote rate is loaded in.
	if ( ! $shown_popup ) {
		?><div class="wccsq-popup-wrap" id="wccsq-popup-0">
			<div class="wccsq-popup-bg" style="display: none;"></div>
			<div class="wccsq-popup" style="display: none;"></div>
		</div><?php
	}
}
add_action( 'wp_footer', '\WooCommerce_Contact_for_Shipping_Quote\add_popup_html', 10, 2 );


/**
 * Email address field update on change.
 *
 * Make sure the order totals are updated on email address change when the email
 * address is a required quote field.
 *
 * @since 1.1.0
 *
 * @param  array $fields List of billing checkout fields.
 * @return array         List of modified billing checkout fields.
 */
function add_billing_email_field_class( $fields ) {
	$required_quote_fields = get_option( 'shipping_quote_required_data', array() );

	if ( in_array( 'email', $required_quote_fields ) && isset( $fields['billing_email'] ) ) {
		$fields['billing_email']['class'][] = 'update_totals_on_change';
	}

	return $fields;
}
add_filter( 'woocommerce_billing_fields', '\WooCommerce_Contact_for_Shipping_Quote\add_billing_email_field_class' );


/**
 * Get field name.
 *
 * Get the pretty field name based on field ID.
 *
 * @since 1.2.0
 *
 * @param  string $field_id Field ID/key.
 * @return string           Pretty field name.
 */
function get_field_name( $field_id ) {
	$fields = apply_filters( 'WCCSQ/field_names', array(
		'first_name' => __( 'First Name', 'woocommerce' ),
		'last_name'  => __( 'Last Name', 'woocommerce' ),
		'company'    => __( 'Company', 'woocommerce' ),
		'address_1'  => __( 'Address 1', 'woocommerce' ),
		'address_2'  => __( 'Address 2', 'woocommerce' ),
		'city'       => __( 'City', 'woocommerce' ),
		'postcode'   => __( 'Postal/Zip Code', 'woocommerce' ),
		'state'      => __( 'State', 'woocommerce' ),
		'country'    => __( 'Country / Region', 'woocommerce' ),
		'phone'      => __( 'Phone Number', 'woocommerce' ),
		'email'      => __( 'Email Address', 'woocommerce' ),
	) );

	return $fields[ $field_id ] ?? $field_id;
}
