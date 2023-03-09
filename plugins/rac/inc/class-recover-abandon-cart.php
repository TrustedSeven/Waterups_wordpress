<?php

/**
 * Main Class.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'RecoverAbandonCart' ) ) {

	/**
	 * Main Class.
	 */
	final class RecoverAbandonCart {

		/**
		 * Version.
		 *
		 * @var string
		 */
		private $version = '23.8' ;

		/**
		 * Locale.
		 * 
		 * @var string
		 * */
		private $locale = 'recoverabandoncart' ;

		/**
		 * Folder Name.
		 * 
		 * @var string
		 * */
		private $folder_name = 'rac' ;

		/**
		 * WC minimum version.
		 *
		 * @var string
		 */
		public static $wc_minimum_version = '3.0' ;

		/**
		 * WP minimum version.
		 *
		 * @var string
		 */
		public static $wp_minimum_version = '4.6' ;

		/**
		 * The single instance of the class.
		 * 
		 * @var object
		 */
		protected static $_instance = null ;

		/**
		 * Load class in single instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self() ;
			}
			return self::$_instance ;
		}

		/**
		 * Cloning has been forbidden. 
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', esc_html( $this->version ) ) ;
		}

		/**
		 * Unserialize the class data has been forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', esc_html( $this->version ) ) ;
		}

		/**
		 * Class Constructor.
		 */
		public function __construct() {
			$this->header_already_sent_problem() ;
			$this->define_constants() ;
			$this->include_files() ;
			$this->init_hooks() ;
			$this->cron_job_setting() ;
			$this->trigger_cron_job_setting_event() ;
		}

		/**
		 * Function to prevent header error that says you have already sent the header.
		 */
		private function header_already_sent_problem() {
			ob_start() ;
		}

		/**
		 * Load plugin the translate files.
		 * */
		private function load_plugin_textdomain() {
			$locale = determine_locale() ;
			$locale = apply_filters( 'plugin_locale', $locale, RAC_LOCALE ) ;

			// Unload the text domain if other plugins/themes loaded the same text domain by mistake.
			unload_textdomain( RAC_LOCALE ) ;

			// Load the text domain from the "wp-content" languages folder. we have handles the plugin folder in languages folder for easily handle it.
			load_textdomain( RAC_LOCALE, WP_LANG_DIR . '/' . RAC_PLUGIN_FOLDER_NAME . RAC_LOCALE . '-' . $locale . '.mo' ) ;

			// Load the text domain from the current plugin languages folder.
			load_plugin_textdomain( RAC_LOCALE, false, dirname( plugin_basename( RAC_PLUGIN_FILE ) ) . '/languages' ) ;
		}

		/**
		 * Prepare the Constants value array.
		 */
		private function define_constants() {
			$constant_array = array(
				'RAC_VERSION'            => $this->version,
				'RAC_LOCALE'             => $this->locale,
				'RAC_PLUGIN_FOLDER_NAME' => $this->folder_name . '/',
				'RAC_PLUGIN_BASE_NAME'   => plugin_basename( RAC_PLUGIN_FILE ),
				'RAC_PLUGIN_PATH'        => untrailingslashit( plugin_dir_path( RAC_PLUGIN_FILE ) ),
				'RAC_PLUGIN_URL'         => untrailingslashit( plugins_url( '/', RAC_PLUGIN_FILE ) ),
				'RAC_ADMIN_URL'          => admin_url( 'admin.php' ),
				'RAC_ADMIN_AJAX_URL'     => admin_url( 'admin-ajax.php' ),
					) ;
			$constant_array = apply_filters( 'fp_rac_define_constants', $constant_array ) ;

			if ( is_array( $constant_array ) && ! empty( $constant_array ) ) {
				foreach ( $constant_array as $name => $value ) {
					$this->define_constant( $name, $value ) ;
				}
			}
		}

		/**
		 * Define the Constants value.
		 */
		private function define_constant( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value ) ;
			}
		}

		/**
		 * Include required files.
		 */
		public function include_files() {

			require_once 'fp-rac-counter.php' ;
			require_once 'fp-rac-coupon-deletion.php' ;

			include_once 'class-fp-rac-install.php' ;
			include_once 'fp-rac-common-functions.php' ;
			include_once 'class-fp-rac-register-post-type.php' ;
			include_once 'class-fp-rac-polish-product-info.php' ;
			include_once 'fp-rac-custom-post-type-functions.php' ;
			include_once 'class-fp-rac-register-post-status.php' ;
			include_once 'fp-rac-custom-post-type-functions.php' ;
			include_once 'fp-rac-wc-compatibility-functions.php' ;
			include_once 'fp-rac-class-previous-order-data.php' ;
			include_once 'emails/class-fp-rac-automatic-mail.php' ;
			include_once 'class-fp-rac-insert-cartlist-entry.php' ;
			include_once 'class-fp-rac-abandon-order-management.php' ;
			include_once 'fp-rac-add-cancelled-order-immediately.php' ;
			include_once 'admin/menu/class-fp-rac-coupon-handler.php' ;
			include_once 'compatibility/fp-rac-wpml-compatibility.php' ;
			include_once 'compatibility/fp-rac-personal-data-handler.php' ;
			include_once 'woocommerce-log/class-fp-woocommerce-log.php' ;
			include_once 'background-updater/fp-rac-main-background-process.php' ;
			include_once 'compatibility/fp-rac-product-addons-compatibility.php' ;
			include_once 'compatibility/fp-rac-currency-switcher-compatibility.php' ;
			include_once 'emails/class-fp-rac-admin-notification-email.php' ;
						include_once 'class-fp-rac-send-email-by-woocommerce-mailer.php' ;

			include_once 'admin/fp-rac-privacy.php' ;

			if ( is_admin() ) {
				$this->include_admin_files() ;
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->include_frontend_files() ;
			}
		}

		/**
		 * Include required admin files.
		 */
		public function include_admin_files() {

			include_once 'fp-rac-previous-order.php' ;
			include_once 'emails/class-fp-rac-test-mail.php' ;
			include_once 'emails/class-fp-rac-manual-mail.php' ;
			include_once 'class-fp-rac-admin-ajax-functions.php' ;
			include_once 'emails/class-fp-rac-email-template-test.php' ;
			include_once 'welcome-page/fp-rac-welcome-page-functions.php' ;
			include_once 'email-template/class-fp-rac-email-template.php' ;
			include_once 'api/rac-common-function-for-multi-select-search.php' ;
			include_once 'admin/class-fp-rac-admin-assets.php' ;
			//Submenu
			include_once 'admin/menu/class-rac-menu-management.php' ;
			include_once 'admin/menu/class-fp-rac-maillog-submenu.php' ;
			include_once 'admin/menu/class-fp-rac-cartlist-submenu.php' ;
			include_once 'admin/menu/class-fp-rac-emailtemplate-submenu.php' ;
			include_once 'admin/menu/class-fp-rac-recovered-order-submenu.php' ;
		}

		/**
		 * Include required frontend files.
		 */
		public function include_frontend_files() {

			include_once 'frontend/class-fp-rac-frontend-assets.php' ;
			include_once 'frontend/class-fp-rac-unsubscribe-function.php' ;
			include_once 'frontend/class-fp-rac-cart-data-from-mail.php' ;
			include_once 'frontend/class-fp-rac-lightbox-handler.php' ;
			include_once 'frontend/class-fp-rac-frontend-notice-handler.php' ;
		}

		/**
		 * Define the hooks.
		 */
		private function init_hooks() {
			// Init the plugin.
			add_action( 'init', array( $this, 'init' ) ) ;

			add_filter( 'cron_schedules', array( $this, 'add_x_hourly' ) ) ;

			register_activation_hook( RAC_PLUGIN_FILE, array( 'FP_RAC_Install', 'install' ) ) ;
		}

		/**
		 * Init.
		 * */
		public function init() {

			$this->load_plugin_textdomain() ;
		}

		/**
		 * Set Cron Event as "rac_cron_job".
		 * 
		 * Set Cron Event as "rac_hourly_cron"
		 */
		public function cron_job_setting() {
			if ( wp_next_scheduled( 'rac_cron_job' ) == false ) {
				wp_schedule_event( time(), 'xhourly', 'rac_cron_job' ) ;
			}

			if ( wp_next_scheduled( 'rac_hourly_cron' ) == false ) {
				wp_schedule_event( time(), 'yhourly', 'rac_hourly_cron' ) ;
			}
		}

		/**
		 * Initializing Cron Schedules for "rac_cron_job" event.
		 */
		public function add_x_hourly( $schedules ) {
			$interval               = fp_rac_get_interval( 'rac_abandon_cron_time', 'rac_abandon_cart_cron_type' ) ;
			$schedules[ 'xhourly' ] = array(
				'interval' => $interval,
				'display'  => 'X Hourly'
					) ;

			$schedules[ 'yhourly' ] = array(
				'interval' => 3600,
				'display'  => 'Y Hourly'
					) ;

			return $schedules ;
		}

		/**
		 * Trigger the cron job settings.
		 */
		public function trigger_cron_job_setting_event() {
			$wp_array = array(
				'wp_scheduled_delete',
				'wp_version_check',
				'wp_update_plugins',
				'wp_update_themes',
				'wp_scheduled_auto_draft_delete',
				'woocommerce_scheduled_sales'
					) ;
			$wp_array = apply_filters( 'fp_rac_check_is_cron_set', $wp_array ) ;
			if ( is_array( $wp_array ) && ! empty( $wp_array ) ) {
				foreach ( $wp_array as $cron_name ) {
					add_action( $cron_name, array( $this, 'cron_job_setting' ) ) ;
				}
			}
		}

	}

}
