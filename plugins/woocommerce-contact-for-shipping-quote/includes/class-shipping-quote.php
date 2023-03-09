<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Shipping_Quote {

	private $id = null;
	private $customer_id = null;
	private $customer_name = null;
	private $customer_email = null;
	private $customer_phone = null;
	private $address_hash = null;
	private $address = null;
	private $cart_hash = null;
	private $cart_contents = null;
	private $status = null;
	private $quote_amount = null;
	private $order_id = null;
	private $created_gmt = null;

	public function get_id() {
		return $this->id;
	}

	public function get_customer_id() {
		return absint( $this->customer_id );
	}

	public function set_customer_id( $customer_id ) {
		$this->customer_id = absint( $customer_id );

		return $this;
	}

	public function get_customer_name() {
		if ( empty( $this->customer_name ) && ! empty( $this->customer_id ) ) {
			$customer = new \WC_Customer( $this->customer_id );
			$this->set_customer_name( $customer->get_display_name() );
		}

		return wc_clean( $this->customer_name );
	}

	public function set_customer_name( $name ) {
		$this->customer_name = wc_clean( $name );
		return $this;
	}

	public function get_customer_email() {
		if ( empty( $this->customer_email ) && ! empty( $this->customer_id ) ) {
			$customer = new \WC_Customer( $this->customer_id );
			$this->set_customer_email( $customer->get_email() );
		}

		return sanitize_email( $this->customer_email );
	}

	public function set_customer_email( $email ) {
		$this->customer_email = sanitize_email( $email );
		return $this;
	}

	public function get_customer_phone() {
		if ( empty( $this->customer_phone ) && ! empty( $this->customer_id ) ) {
			$customer = new \WC_Customer( $this->customer_id );
			$this->set_customer_email( $customer->get_billing_phone() );
		}

		return $this->sanitize_phone( $this->customer_phone );
	}

	public function set_customer_phone( $phone ) {
		$this->customer_phone = $this->sanitize_phone( $phone );

		return $this;
	}

	private function sanitize_phone( $phone ) {
		if ( function_exists( 'wc_sanitize_phone_number' ) ) {
			$sanitized_value = wc_sanitize_phone_number( $phone );
		} else { // < WC 3.6 compatibility
			$sanitized_value = preg_replace( '/[^\d+]/', '', $phone );
		}

		return $sanitized_value;
	}

	public function get_address_hash() {
		return sanitize_key( $this->address_hash );
	}

	public function set_address_hash( $hash ) {
		$this->address_hash = sanitize_key( $hash );

		return $this;
	}

	public function get_address() {
		return $this->address;
	}

	public function set_address( $address ) {
		$address = wc_clean( apply_filters( 'WCCSQ/quote/set_address', $address, $this ) );

		$this->address = array(
			'country'   => $address['country'],
			'state'     => $address['state'],
			'postcode'  => $address['postcode'],
			'city'      => $address['city'],
			'address_1' => $address['address_1'],
			'address_2' => $address['address_2'],
		);

		return $this;
	}

	public function get_cart_hash() {
		return $this->cart_hash;
	}

	public function set_cart_hash( $cart_hash ) {
		$this->cart_hash = sanitize_key( $cart_hash );

		return $this;
	}

	public function get_cart_contents() {
		return $this->cart_contents;
	}

	public function set_cart_contents( $cart ) {
		$this->cart_contents = $cart;

		return $this;
	}

	public function get_status_slug() {
		return wc_clean( $this->status );
	}

	public function set_status_slug( $status ) {
		if ( ! array_key_exists( $status, get_statuses() ) ) {
			error_log( sprintf( 'Unknown status %s cannot be assigned to shipping quote', $status ) );
			return $this;
		}

		$this->status = strtolower( $status );

		return $this;
	}

	public function set_status( $status ) {
		$this->set_status_slug( $status );

		return $this;
	}

	public function get_status() {
		$statuses = get_statuses();

		return $statuses[ $this->get_status_slug() ] ?? false;
	}

	public function get_quote_amount() {
		return is_null( $this->quote_amount ) ? null : wc_format_decimal( $this->quote_amount );
	}

	public function set_quote_amount( $cost ) {
		$this->quote_amount = wc_format_decimal( $cost );

		return $this;
	}

	public function get_order_id() {
		return absint( $this->order_id );
	}

	public function set_order_id( $order_id ) {
		$this->order_id = absint( $order_id );

		return $this;
	}

	public function get_order() {
		return wc_get_order( $this->get_order_id() );
	}

	public function get_created_gmt() {
		return $this->created_gmt;
	}

	public function set_created_gmt( $created ) {
		$this->created_gmt = (new \DateTime( $created ))->format( 'Y-m-d H:i:s' );
	}

	public function get_created() {
		$tz_string = get_option( 'timezone_string' );
		$gmt_offset = sprintf( '%+d00', get_option( 'gmt_offset' ) );

		return (new \DateTime( $this->get_created_gmt(), new \DateTimeZone( $tz_string ?: $gmt_offset ) ) );
	}

	/**
	 * Cart recovery URL.
	 *
	 * Get the unique cart/customer recovery URL for this quote.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_cart_recover_url() {
		$quote_key = md5( $this->get_cart_hash() . $this->get_address_hash() ); // Something unpredictable to prevent anyone from recovering any cart

		return apply_filters( 'WCCSQ/quote/cart_recover_url', add_query_arg( array(
			'action' => 'complete-quote',
			'quote'  => $this->get_id(),
			'key'    => $quote_key,
		), wc_get_cart_url() ), $this );
	}

	/**
	 * Recover cart items.
	 *
	 * Recover the cart items from the quote. Does not recover the customer address (see self::recover_customer).
	 * NOTE: this function will empty the cart from all existing products.
	 *
	 * @since 1.1.0
	 */
	public function recover_cart() {
		// Clear the cart
		WC()->cart->empty_cart();

		$items = $this->get_cart_contents();
		foreach ( $items as $key => $item ) {

			if ( ! in_array( get_post_type( $item['product_id'] ), array( 'product', 'product_variation' ) ) ) {
				continue;
			}

			$product_id     = $item['product_id'];
			$quantity       = isset( $item['quantity'] ) ? $item['quantity'] : '1';
			$variation_id   = isset( $item['variation_id'] ) ? $item['variation_id'] : '';
			$cart_item_data = array_diff_key( $item, array_flip( array( 'product_id', 'variation_id', 'variation', 'quantity', 'name', 'line_total', 'line_tax' ) ) );

			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $item['variation'], $cart_item_data );
		}

		do_action( 'WCCSQ/quote/recover_cart', $this );
	}

	/**
	 * Recover customer address.
	 *
	 * Recover the customer address from this quote.
	 *
	 * @since 1.1.0
	 */
	public function recover_customer() {
		$address = $this->get_address();

		foreach ( $address as $k => $v ) {
			// If billing city is not set, use shipping address as billing
			if ( method_exists( WC()->customer, 'set_billing_' . $k ) ) {
				call_user_func( array( WC()->customer, 'set_billing_' . $k ), $v );
			}
			if ( method_exists( WC()->customer, 'set_shipping_' . $k ) ) {
				call_user_func( array( WC()->customer, 'set_shipping_' . $k ), $v );
			}
		}

		if ( empty( WC()->customer->get_billing_email() ) ) {
			WC()->customer->set_billing_email( $this->get_customer_email() );
		}
		if ( empty( WC()->customer->get_billing_phone() ) ) {
			WC()->customer->set_billing_phone( $this->get_customer_phone() );
		}

		do_action( 'WCCSQ/quote/recover_customer', $this );
	}

	/**************************************************************
	 * CRUD
	 *************************************************************/


	/**
	 * Create new shipping quote.
	 *
	 * Insert a new shipping quote in the database.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of arguments.
	 * @return bool|Shipping_Quote Shipping_Quote instance when successful. False otherwise.
	 */
	public static function create( $args ) {
		global $wpdb;

		$customer_name = sprintf( '%s %s', get_value_for_quote( 'first_name' ), get_value_for_quote( 'last_name' ) );

		$wpdb->insert( "{$wpdb->prefix}woocommerce_shipping_quotes", wp_parse_args( $args, array(
			'customer_id'    => WC()->customer->get_id() ?: 0,
			'customer_name'  => $customer_name,
			'customer_email' => WC()->customer->get_billing_email(),
			'customer_phone' => WC()->customer->get_billing_phone(),
			'address_hash'   => get_address_hash(),
			'address'        => maybe_serialize( get_address_data() ),
			'cart_hash'      => sanitize_key( get_cart_hash() ),
			'cart_contents'  => maybe_serialize( get_cart_data() ),
			'status'         => 'new',
			'quote_amount'   => null,
			'order_id'       => null,
			'created_gmt'    => current_time( 'mysql' ),
		) ), array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s' ) );

		if ( $wpdb->insert_id ) {
			return (new self())->read( $wpdb->insert_id );
		}

		return false;
	}


	/**
	 * Get shipping quote.
	 *
	 * Read the shipping quote data from the database.
	 *
	 * @since 1.0.0
	 *
	 * @param int $quote_id Shipping quote ID.
	 * @param null|object $data Raw data when mass reading from the database.
	 * @return Shipping_Quote Instance.
	 */
	public static function read( $quote_id, $data = null ) {
		$quote_id = absint( $quote_id );

		if ( $quote = wp_cache_get( $quote_id, 'wc.shipping_quotes' ) ) {
			return $quote;
		}

		if ( is_null( $data ) || ! is_object( $data ) ) {
			global $wpdb;
			$data = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_quotes WHERE id = %d", $quote_id )
			);
		}

		if ( ! $data ) {
			return false;
		}

		$quote = new self();

		$quote->id             = $data->id;
		$quote->customer_id    = $data->customer_id;
		$quote->customer_name  = $data->customer_name;
		$quote->customer_email = $data->customer_email;
		$quote->customer_phone = $data->customer_phone;
		$quote->address_hash   = $data->address_hash;
		$quote->address        = maybe_unserialize( $data->address );
		$quote->cart_hash      = $data->cart_hash;
		$quote->cart_contents  = maybe_unserialize( $data->cart_contents );
		$quote->status         = $data->status;
		$quote->quote_amount   = $data->quote_amount;
		$quote->order_id       = $data->order_id;
		$quote->created_gmt    = $data->created_gmt;

		wp_cache_set( $quote_id, $quote, 'wc.shipping_quotes' );

		return $quote;
	}


	/**
	 * Read quote by custom fields.
	 *
	 * Get/read a shipping quote by custom field values.
	 *
	 * @since 1.0.0
	 *
	 * @param array $where Key => Value list to search for.
	 * @param array $where_format SQL prepare where format.
	 * @return Shipping_Quote|bool Shipping quote instance when a quote was found. False otherwise.
	 */
	public static function read_by( $where, $where_format ) {
		global $wpdb;

		$conditions = array();
		foreach ( $where as $field => $value ) {
			$format = array_shift( $where_format );

			if ( $field == 'status' ) {
				$conditions[] = "`$field` IN ('" . implode( "', '", array_map( 'esc_sql', $value ) ) . "')";
				unset( $where[ $field ] );
			} else {
				$conditions[] = "`$field` = " . $format;
			}
		}

		$conditions = implode( ' AND ', $conditions );
		$data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_quotes WHERE {$conditions};", array_values( $where ) ) );

		if ( $data ) {
			return self::read( $data->id, $data );
		}

		return false;
	}


	/**
	 * Update shipping quote.
	 *
	 * Update a shipping quote with the latest values.
	 *
	 * @since 1.0.0
	 *
	 * @return Shipping_Quote Own instance.
	 */
	public function update() {
		global $wpdb;

		$wpdb->update( "{$wpdb->prefix}woocommerce_shipping_quotes", array(
			'customer_id'    => $this->get_customer_id(),
			'customer_name'  => $this->get_customer_name(),
			'customer_email' => $this->get_customer_email(),
			'customer_phone' => $this->get_customer_phone(),
			'address_hash'   => $this->get_address_hash(),
			'address'        => maybe_serialize( $this->get_address() ),
			'cart_hash'      => sanitize_key( $this->get_cart_hash() ),
			'cart_contents'  => maybe_serialize( $this->get_cart_contents() ),
			'status'         => $this->get_status_slug(),
			'quote_amount'   => $this->get_quote_amount(),
			'order_id'       => $this->get_order_id(),
			'created_gmt'    => $this->get_created_gmt(),
		),
			array( 'id' => $this->get_id() ),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%s' ),
			array( '%d' )
		);

		wp_cache_set( $this->get_id(), $this, 'wc.shipping_quotes' );

		return $this;
	}


	public function save() {
		return $this->update();
	}


	/**
	 * Delete shipping quote.
	 *
	 * Delete the shipping quote from the database.
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb;

		$wpdb->delete( "{$wpdb->prefix}woocommerce_shipping_quotes", array(
			'id' => $this->id,
		), array( '%d' ) );

		wp_cache_delete( $this->id, 'wc.shipping_quotes' );
	}

}
