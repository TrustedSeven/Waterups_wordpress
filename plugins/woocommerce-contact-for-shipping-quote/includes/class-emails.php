<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class Emails {

	public function __construct() {
		// Register emails
		add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ), 10, 2 );

		// Add email actions
		add_action( 'woocommerce_email_shipping_quote_details', array( $this, 'quote_details' ), 5, 4 );
		add_action( 'woocommerce_email_shipping_quote_details', array( $this, 'quote_product_details' ), 10, 4 );
		add_action( 'woocommerce_email_shipping_quote_customer_details', array( $this, 'quote_customer_address' ), 20, 4 );
	}


	/**
	 * Register quote emails.
	 *
	 * @since 1.1.0
	 *
	 * @param  array $emails Existing emails list.
	 * @return array         Modified emails list.
	 */
	public function register_emails( $emails ) {
		$emails['WCCSQ_Email_Customer_Shipping_Quote_Available'] = include 'emails/class-email-customer-shipping-quote-available.php';
		$emails['WCCSQ_Email_Shipping_Quote_Requested']          = include 'emails/class-email-shipping-quote-requested.php';

		return $emails;
	}


	/**
	 * Display quote details.
	 *
	 * Quote amount and complete order link.
	 *
	 * @since 1.1.0
	 *
	 * @param        $quote
	 * @param bool   $sent_to_admin
	 * @param bool   $plain_text
	 * @param string $email
	 */
	public function quote_details( $quote, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		if ( ! $sent_to_admin ) {
			wc_get_template( 'emails/email-quote-details.php', array(
				'quote'         => $quote,
				'sent_to_admin' => $sent_to_admin,
				'plain_text'    => $plain_text,
				'email'         => $email,
			), '', plugin_dir_path( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE ) . '/templates/' );
		} else {
			wc_get_template( 'emails/email-quote-admin-details.php', array(
				'quote'         => $quote,
				'sent_to_admin' => $sent_to_admin,
				'plain_text'    => $plain_text,
				'email'         => $email,
			), '', plugin_dir_path( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE ) . '/templates/' );
		}
	}


	/**
	 * Product table.
	 *
	 * @since 1.1.0
	 *
	 * @param        $quote
	 * @param bool   $sent_to_admin
	 * @param bool   $plain_text
	 * @param string $email
	 */
	public function quote_product_details( $quote, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		wc_get_template( 'emails/email-quote-product-details.php', array(
			'quote'         => $quote,
			'sent_to_admin' => $sent_to_admin,
			'plain_text'    => $plain_text,
			'email'         => $email,
		), '', plugin_dir_path( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE ) . '/templates/' );
	}


	/**
	 * Customer address.
	 *
	 * @since 1.1.0
	 *
	 * @param        $quote
	 * @param bool   $sent_to_admin
	 * @param bool   $plain_text
	 * @param string $email
	 */
	public function quote_customer_address( $quote, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		wc_get_template( 'emails/email-quote-address.php', array(
			'quote'         => $quote,
			'sent_to_admin' => $sent_to_admin,
			'plain_text'    => $plain_text,
			'email'         => $email,
		), '', plugin_dir_path( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE ) . '/templates/' );
	}

}
