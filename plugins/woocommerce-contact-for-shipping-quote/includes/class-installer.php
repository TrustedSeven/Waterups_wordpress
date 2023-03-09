<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Installer {

	public static function init() {
		self::check_version();
	}

	/**
	 * Check plugin version for updates.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( WooCommerce_Contact_For_Shipping_Quote()->db_version, WooCommerce_Contact_For_Shipping_Quote()->version, '<' ) ) {
			self::perform_updates();
		}
	}

	/**
	 * Runs on plugin install.
	 */
	public static function install() {
		self::create_tables();

		self::update_db_version();
	}

	/**
	 * Update DB version.
	 *
	 * @param null $version
	 */
	public static function update_db_version( $version = null ) {
		if ( is_null( $version ) ) {
			$version = WooCommerce_Contact_For_Shipping_Quote()->version;
		}

		update_option( 'woocommerce_contact_for_shipping_quote_version', $version );
	}

	/**
	 * Create database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		\dbDelta( "
			CREATE TABLE {$wpdb->prefix}woocommerce_shipping_quotes (
			  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  customer_id int(11) NOT NULL,
			  customer_name varchar(100) NOT NULL DEFAULT '',
			  customer_email varchar(255) DEFAULT '',
			  customer_phone varchar(255) DEFAULT '',
			  address_hash varchar(60) NOT NULL,
			  address longtext NOT NULL,
			  cart_hash varchar(60) NOT NULL DEFAULT '',
			  cart_contents longtext NOT NULL,
			  status varchar(25) NOT NULL DEFAULT '',
			  quote_amount double DEFAULT NULL,
			  order_id bigint(20) DEFAULT NULL,
			  created_gmt datetime DEFAULT NULL,
			  PRIMARY KEY (id),
			  KEY customer_id (customer_id),
			  KEY cart_hash (cart_hash),
			  KEY status (status),
			  KEY address_hash (address_hash)
			) $collate;
		" );
	}

	/**
	 * Perform update functions.
	 */
	private static function perform_updates() {
		if ( version_compare( WooCommerce_Contact_For_Shipping_Quote()->db_version, '1.1.0', '<' ) ) {
			self::update_110();
		}

		if ( version_compare( WooCommerce_Contact_For_Shipping_Quote()->db_version, '1.2.1', '<' ) ) {
			self::update_120();
		}

		self::update_db_version();
	}

	/**
	 * Update 1.1.0.
	 *
	 * Add customer email column to shipping quote table.
	 */
	private static function update_110() {
		global $wpdb;

		// Add customer_email column to quotes table
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_shipping_quotes';" ) ) {
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_shipping_quotes` LIKE 'customer_email';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_shipping_quotes ADD COLUMN `customer_email` VARCHAR(255) DEFAULT '' AFTER `customer_name`;" );

			}
		}

		// Set new option to not change existing setups
		update_option( 'shipping_quote_required_data', array() );

		// Do not send out emails for existing users.
		// Must be performed after init
		add_action( 'init', '\WooCommerce_Contact_for_Shipping_Quote\Installer::update_110_disable_email' );
	}

	public static function update_110_disable_email() {
		WC()->mailer(); // Init WC emails
		$available_email = include 'emails/class-email-customer-shipping-quote-available.php';
		$requested_email = include 'emails/class-email-shipping-quote-requested.php';

		// Disable by default for users pre-1.1.0
		$available_email->update_option( 'enabled', 'no' );
		$requested_email->update_option( 'enabled', 'no' );
	}


	/**
	 * Update 1.1.0.
	 *
	 * Add customer phone column to shipping quote table.
	 */
	private static function update_120() {
		global $wpdb;

		// Add customer_email column to quotes table
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_shipping_quotes';" ) ) {
			if ( ! $wpdb->get_var( "SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_shipping_quotes` LIKE 'customer_phone';" ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_shipping_quotes ADD COLUMN `customer_phone` VARCHAR(255) DEFAULT '' AFTER `customer_email`;" );

			}
		}
	}

}
Installer::init();
