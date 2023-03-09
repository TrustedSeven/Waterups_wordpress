<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Main plugin class.
 */
class WooCommerce_Contact_For_Shipping_Quote {

	/** @var string Plugin version */
	public $version = '1.4.1';

	/** @var string|null Database plugin version. */
	public $db_version = null;

	/** @var string Plugin file */
	public $file = __FILE__;

	/** @var WooCommerce_Contact_For_Shipping_Quote Plugin instance */
	private static  $instance;


	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		$this->db_version = get_option( 'woocommerce_contact_for_shipping_quote_version', '1.0.0' );
	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since  1.0.0
	 * @return WooCommerce_Contact_For_Shipping_Quote Instance of the class.
	 */
	public static  function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Initialize plugin parts.
	 *
	 * @since  1.0.0
	 */
	public function init() {
		if ( ! \is_woocommerce_active() ) {
			return;
		}

		// Load textdomain
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Include files
		$this->includes();

		// Register shipping method
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );

		$this->emails = new Emails();

		// Admin
		if ( is_admin() ) {
			$this->admin = new \WooCommerce_Contact_for_Shipping_Quote\Admin\Admin();
			$this->admin->init();
		}
	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.2.0
	 */
	public function load_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale(); // @todo Remove when start supporting WP 5.0 or later.
		}

		$locale = apply_filters( 'plugin_locale', $locale, 'woocommerce-contact-for-shipping-quote' );

		load_textdomain( 'woocommerce-contact-for-shipping-quote', WP_LANG_DIR . '/woocommerce-contact-for-shipping-quote-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-contact-for-shipping-quote', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Include files.
	 *
	 * Include/require plugin files/classes.
	 *
	 * @since  1.0.0
	 */
	public function includes() {

		require_once plugin_dir_path( $this->file ) . 'includes/class-emails.php';
		require_once plugin_dir_path( $this->file ) . 'woocommerce-contact-for-shipping-quote.php';
		require_once plugin_dir_path( $this->file ) . 'includes/class-shipping-quote.php';
		require_once plugin_dir_path( $this->file ) . 'includes/admin/admin.php';
		require_once plugin_dir_path( $this->file ) . 'includes/core-functions.php';
		require_once plugin_dir_path( $this->file ) . 'includes/checkout-functions.php';
		require_once plugin_dir_path( $this->file ) . 'includes/quote-functions.php';
		require_once plugin_dir_path( $this->file ) . 'includes/ajax-functions.php';
		require_once plugin_dir_path( $this->file ) . 'includes/cron-functions.php';
		require_once plugin_dir_path( $this->file ) . 'includes/class-installer.php';
	}


	/**
	 * Register the shipping method.
	 *
	 * Register the shipping method to WooCommerce.
	 *
	 * @since 1.0.0
	 *
	 * @param array $methods List of existing shipping methods.
	 * @return array List of modified shipping methods.
	 */
	public function add_shipping_method( $methods ) {
		require_once plugin_dir_path( $this->file ) . 'includes/class-shipping-method.php';

		$methods['custom_shipping_quote'] = '\WooCommerce_Contact_for_Shipping_Quote\WCCSQ_Shipping_Quote_Method';

		return $methods;
	}

}

/**
 * The main function responsible for returning the WooCommerce_Contact_For_Shipping_Quote object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WooCommerce_Contact_For_Shipping_Quote()->method_name(); ?>
 *
 * @since 1.0.0
 *
 * @return WooCommerce_Contact_For_Shipping_Quote Return the singleton WooCommerce_Contact_For_Shipping_Quote object.
 */
function WooCommerce_Contact_For_Shipping_Quote() {
	return WooCommerce_Contact_For_Shipping_Quote::instance();
}
WooCommerce_Contact_For_Shipping_Quote()->init();


register_activation_hook( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE, function() {
	Installer::install();
});
