<?php
/**
 * Menu Management.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'RAC_Menu_Management' ) ) {

	include_once( 'class-rac-settings.php' ) ;

	/**
	 * Class.
	 */
	class RAC_Menu_Management {

		/**
		 * Plugin slug.
		 * 
		 * @var string
		 */
		protected static $plugin_slug = 'rac' ;

		/**
		 * Menu slug.
		 * 
		 * @var string
		 */
		protected static $menu_slug = 'edit.php?post_type=raccartlist' ;

		/**
		 * Reports slug.
		 * 
		 * @var string
		 */
		protected static $reports_slug = 'rac_reports' ;

		/**
		 * Settings slug.
		 * 
		 * @var string
		 */
		protected static $settings_slug = 'rac_settings' ;

		/**
		 * Class initialization.
		 */
		public static function init() {
			add_action( 'admin_menu', array( __CLASS__, 'add_custom_menus' ) ) ;

			add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'add_custom_wc_screen_ids' ), 9, 1 ) ;
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( __CLASS__, 'prepare_custom_field_value' ), 10, 3 ) ;
			add_filter( 'screen_settings', array( __CLASS__, 'render_screen_option_extra_settings' ), 10, 2 ) ;
			add_filter( 'init', array( __CLASS__, 'set_screen_option_value' ) ) ;

			//May be add the error messages.
			add_action( 'rac_before_tab_sections', array( __CLASS__, 'handle_error_messages' ) ) ;
		}

		/**
		 * Add the custom screen IDs in WooCommerce.
		 * 
		 * @return array
		 */
		public static function add_custom_wc_screen_ids( $wc_screen_ids ) {
			$screen_ids = fp_rac_page_screen_ids() ;

			$newscreenids = get_current_screen() ;
			$screenid     = str_replace( 'edit-', '', $newscreenids->id ) ;

			// Return if the current page is not a recover abandoned cart page.
			if ( ! in_array( $screenid, $screen_ids ) ) {
				return $wc_screen_ids ;
			}

			$wc_screen_ids[] = $screenid ;

			return $wc_screen_ids ;
		}

		/**
		 * Add the custom menus.
		 * 
		 * @return void
		 */
		public static function add_custom_menus() {

			// Reports sub menu.
			add_submenu_page( self::$menu_slug, esc_html__( 'Reports', 'recoverabandoncart' ), esc_html__( 'Reports', 'recoverabandoncart' ), 'manage_woocommerce', self::$reports_slug, array( __CLASS__, 'reports_page' ) ) ;

			// Settings sub menu.
			$settings_page = add_submenu_page( self::$menu_slug, esc_html__( 'Settings', 'recoverabandoncart' ), esc_html__( 'Settings', 'recoverabandoncart' ), 'manage_woocommerce', self::$settings_slug, array( __CLASS__, 'settings_page' ) ) ;

			add_action( 'load-' . $settings_page, array( __CLASS__, 'settings_page_init' ) ) ;
		}

		/**
		 * Initialize the settings page.
		 */
		public static function settings_page_init() {
			global $current_tab, $current_section, $current_sub_section ;

			// Include settings pages.
			$settings = RAC_Settings::get_settings_pages() ;

			$tabs = rac_get_allowed_setting_tabs() ;

			// Prepare current tab/section.
			$current_tab = key( $tabs ) ;
			if ( ! empty( $_GET[ 'tab' ] ) ) {
				$sanitize_current_tab = sanitize_title( wp_unslash( $_GET[ 'tab' ] ) ) ; // @codingStandardsIgnoreLine.
				if ( array_key_exists( $sanitize_current_tab, $tabs ) ) {
					$current_tab = $sanitize_current_tab ;
				}
			}

			$section = isset( $settings[ $current_tab ] ) ? $settings[ $current_tab ]->get_sections() : array() ;

			$current_section     = empty( $_REQUEST[ 'section' ] ) ? key( $section ) : sanitize_title( wp_unslash( $_REQUEST[ 'section' ] ) ) ; // @codingStandardsIgnoreLine.
			$current_section     = empty( $current_section ) ? $current_tab : $current_section ;
			$current_sub_section = empty( $_REQUEST[ 'subsection' ] ) ? '' : sanitize_title( wp_unslash( $_REQUEST[ 'subsection' ] ) ) ; // @codingStandardsIgnoreLine.

			do_action( sanitize_key( self::$plugin_slug . '_settings_save_' . $current_tab ), $current_section ) ;
			do_action( sanitize_key( self::$plugin_slug . '_settings_reset_' . $current_tab ), $current_section ) ;

			add_action( 'woocommerce_admin_field_rac_custom_fields', array( __CLASS__, 'custom_fields_output' ) ) ;
		}

		/**
		 * Render the screen option extra settings.
		 * 
		 * @return string 
		 */
		public static function render_screen_option_extra_settings( $screen_settings, $screen_object ) {
			if ( ! is_object( $screen_object ) ) {
				return $screen_settings ;
			}

			$screenid          = str_replace( 'edit-', '', $screen_object->id ) ;
			$custom_post_types = array( 'racemailtemplate', 'raccartlist', 'racmaillog', 'racrecoveredorder' ) ;
			// Return if the current page is not a recover abandoned cart page.
			if ( ! in_array( $screenid, $custom_post_types ) ) {
				return $screen_settings ;
			}

			$post_type_array = array(
				'racemailtemplate'  => 'rac_display_template_basedon_asc_desc',
				'raccartlist'       => 'rac_display_cart_list_basedon_asc_desc',
				'racmaillog'        => 'rac_display_mail_log_basedon_asc_desc',
				'racrecoveredorder' => 'rac_display_recovered_orders_basedon_asc_desc',
					) ;

			$option_name     = $post_type_array[ $screenid ] ;
			$option_value    = get_user_option( $option_name ) ;
			ob_start() ;
			?>
			<fieldset>
				<legend><?php esc_html_e( 'Sorting', 'recoverabandoncart' ) ; ?></legend>
				<label for=""><?php esc_html_e( 'Display Table in', 'recoverabandoncart' ) ; ?></label>
				<select id="<?php echo esc_attr( $option_name ) ; ?>" name="fp_rac_screen_options[value]">
					<option value="yes" <?php selected( $option_value, 'yes' ) ; ?>><?php esc_html_e( 'Ascending Order', 'recoverabandoncart' ) ; ?></option>
					<option value="no" <?php selected( $option_value, 'no' ) ; ?>><?php esc_html_e( 'Descending Order', 'recoverabandoncart' ) ; ?></option>
				</select>
				<input type="hidden" name="fp_rac_screen_options[option]" value="<?php echo esc_attr( $option_name ) ; ?>" />
				<input type="hidden" name="fp_rac_screen_options_for[option]" value="<?php echo esc_attr( $screenid ) ; ?>" />
			</fieldset>
			<?php
			$screen_settings .= ob_get_clean() ;

			return $screen_settings ;
		}

		/**
		 * Set the screen option value.
		 */
		public static function set_screen_option_value() {
			$screen_options     = isset( $_REQUEST[ 'fp_rac_screen_options' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'fp_rac_screen_options' ] ) ) : '' ;
			$screen_options_for = isset( $_REQUEST[ 'fp_rac_screen_options_for' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'fp_rac_screen_options_for' ] ) ) : '' ;
			if ( ! rac_check_is_array( $screen_options ) || ! rac_check_is_array( $screen_options_for ) ) {
				return ;
			}

			$user = wp_get_current_user() ;
			if ( ! $user ) {
				return ;
			}

			$post_type_array = array(
				'racemailtemplate'  => 'rac_display_template_basedon_asc_desc',
				'raccartlist'       => 'rac_display_cart_list_basedon_asc_desc',
				'racmaillog'        => 'rac_display_mail_log_basedon_asc_desc',
				'racrecoveredorder' => 'rac_display_recovered_orders_basedon_asc_desc',
					) ;

			$post_type    = $screen_options_for[ 'option' ] ;
			$option_name  = $post_type_array[ $post_type ] ;
			$option_value = $screen_options[ 'value' ] ;

			update_user_meta( $user->ID, $option_name, $option_value ) ;
		}

		/**
		 * Output the settings page.
		 */
		public static function settings_page() {
			RAC_Settings::output() ;
		}

		/**
		 * Output the Reports page.
		 */
		public static function reports_page() {
			$post_type = isset( $_GET[ 'post_type' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_type' ] ) ) : '' ;
			if ( 'raccartlist' != $post_type ) {
				return ;
			}

			$page = isset( $_GET[ 'page' ] ) ? wc_clean( wp_unslash( $_GET[ 'page' ] ) ) : '' ;
			if ( 'rac_reports' != $page ) {
				return ;
			}

			$rac_updating_action = isset( $_GET[ 'rac_updating_action' ] ) ? wc_clean( wp_unslash( $_GET[ 'rac_updating_action' ] ) ) : '' ;
			if ( 'rac_updating_process' == $rac_updating_action ) {
				$obj = new FP_RAC_Updating_Process() ;
				$obj->fp_display_progress_bar() ;
			} else {

				FP_RAC_Reports_Tab::render() ;
			}
		}

		/**
		 * May be add the error messages.
		 * 
		 * @return void. 
		 * */
		public static function handle_error_messages() {
			// Showing the notice when the automatic abandoned cart has been disabled for members.
			if ( 'yes' != fp_rac_get_cartlist_entry_restriction( 'user' ) ) {
				RAC_Settings::error_message( __( 'Automatic Abandoned Cart capture has been Disabled for Members!!!', 'recoverabandoncart' ) ) ;
			}

			// Showing the notice when the automatic abandoned cart has been disabled for guests.
			if ( 'yes' != fp_rac_get_cartlist_entry_restriction( 'guest' ) ) {
				RAC_Settings::error_message( __( 'Automatic Abandoned Cart capture has been Disabled for Guests!!!', 'recoverabandoncart' ) ) ;
			}

			$cron_type = get_option( 'rac_cron_troubleshoot_format' ) ;
			if ( 'server_cron' != $cron_type && defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON == 'true' ) {
				// Showing the notice if the WP cron has been disabled when the cron type is configured as WP cron. 
				RAC_Settings::error_message( __( "wp_cron is disabled in your Site <br />Recover Abandoned Cart will not be able to send Cart Abandoned Emails to Users Automatically<br />To enable wp_cron, edit the config.php of your Wordpress installation and search for 'define('DISABLE_WP_CRON', 'true');' and set the value as false", 'recoverabandoncart' ) ) ;
			} elseif ( 'server_cron' != $cron_type && ! wp_next_scheduled( 'rac_cron_job' ) ) {
				// Showing the notice if the WP cron has been not set when the cron type is configured as WP cron. 
				RAC_Settings::error_message( __( 'Cron has not been set for sending Automatic Emails!!!<br />Try Deactivating the plugin and activating it again.', 'recoverabandoncart' ) ) ;
			}

			//Showing the notice when the Guest/Members email settings have been disabled.
			if ( 'no' == get_option( 'rac_email_use_members' ) || 'no' == get_option( 'rac_email_use_guests' ) ) {
				RAC_Settings::error_message( __( 'Automatic emailing has been disabled for Members/Guests!!!', 'recoverabandoncart' ) ) ;
			}
		}

		/**
		 * Output the custom field settings.
		 */
		public static function custom_fields_output( $options ) {

			RAC_Settings::output_fields( $options ) ;
		}

		/**
		 * Prepare the custom field value.
		 * 
		 * @return mixed
		 */
		public static function prepare_custom_field_value( $value, $option, $raw_value ) {

			return RAC_Settings::prepare_field_value( $value, $option, $raw_value ) ;
		}

	}

	RAC_Menu_Management::init() ;
}
