<?php
namespace WooCommerce_Contact_for_Shipping_Quote\Admin;

use function WooCommerce_Contact_for_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote;

class Admin {

	public $settings = null;


	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		
	}


	/**
	 * Initialize admin parts.
	 *
	 * @since  1.0.0
	 */
	public function init() {
		
		// Include files
		$this->includes();

		// Settings
		$this->settings = new Settings();


	}


	/**
	 * Include files.
	 *
	 * Include/require plugin files/classes.
	 *
	 * @since  1.0.0
	 */
	public function includes() {
				
		require_once plugin_dir_path( WooCommerce_Contact_For_Shipping_Quote()->file ) . 'includes/admin/admin-functions.php';
		require_once plugin_dir_path( WooCommerce_Contact_For_Shipping_Quote()->file ) . 'includes/admin/settings.php';

	}


}