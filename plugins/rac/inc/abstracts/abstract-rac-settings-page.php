<?php

/**
 * Settings Page/Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly
}

if ( ! class_exists( 'RAC_Settings_Page' ) ) {

	/**
	 * Class.
	 */
	abstract class RAC_Settings_Page {

		/**
		 * Setting page id.
		 * 
		 * @var string
		 */
		protected $id = '' ;

		/**
		 * Setting page label.
		 * 
		 * @var string
		 */
		protected $label = '' ;

		/**
		 * Show buttons.
		 * 
		 * @var bool
		 */
		protected $show_buttons = true ;

		/**
		 * Show reset button.
		 * 
		 * @var bool
		 */
		protected $show_reset_button = true ;

		/**
		 * Plugin slug.
		 * 
		 * @var string
		 */
		protected $plugin_slug = 'rac' ;

		/**
		 * Constructor.
		 */
		public function __construct() {
			add_filter( sanitize_key( $this->get_plugin_slug() . '_settings_tabs_array' ), array( $this, 'add_settings_page' ), 20 ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_sections_' . $this->get_id() ), array( $this, 'output_sections' ) ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_settings_' . $this->get_id() ), array( $this, 'output' ) ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_settings_buttons_' . $this->get_id() ), array( $this, 'output_buttons' ) ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_settings_save_' . $this->get_id() ), array( $this, 'save' ) ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_settings_reset_' . $this->get_id() ), array( $this, 'reset' ) ) ;
			add_action( sanitize_key( $this->get_plugin_slug() . '_after_setting_buttons_' . $this->get_id() ), array( $this, 'output_extra_fields' ) ) ;
		}

		/**
		 * Get the settings page ID.
		 * 
		 * @return string
		 */
		public function get_id() {
			return $this->id ;
		}

		/**
		 * Get settings page label.
		 * 
		 * @return string
		 */
		public function get_label() {
			return $this->label ;
		}

		/**
		 * Get the plugin slug.
		 * 
		 * @return string
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug ;
		}

		/**
		 * Show reset button?.
		 * 
		 * @return string
		 */
		public function show_reset_button() {
			return $this->show_reset_button ;
		}

		/**
		 * Show buttons?.
		 * 
		 * @return string
		 */
		public function show_buttons() {
			return $this->show_buttons ;
		}

		/**
		 * Add this page to settings.
		 * 
		 * @return array
		 */
		public function add_settings_page( $pages ) {

			$pages[ $this->get_id() ] = $this->get_label() ;

			return $pages ;
		}

		/**
		 * Get the settings array.
		 * 
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			$settings = array() ;
			$function = $current_section . '_section_array' ;

			if ( method_exists( $this, $function ) ) {
				$settings = $this->$function() ;
			}

			return apply_filters( sanitize_key( $this->get_plugin_slug() . '_get_settings_' . $this->get_id() ), $settings, $current_section ) ;
		}

		/**
		 * Get the sections.
		 * 
		 * @return array
		 */
		public function get_sections() {
			return apply_filters( sanitize_key( $this->get_plugin_slug() . '_get_sections_' . $this->get_id() ), array() ) ;
		}

		/**
		 * Output sections.
		 */
		public function output_sections() {
			global $current_section ;

			$sections = $this->get_sections() ;
			if ( ! rac_check_is_array( $sections ) || 1 === count( $sections ) ) {
				return ;
			}

			$section = '<ul class="subsubsub ' . $this->get_plugin_slug() . '_sections ' . $this->get_plugin_slug() . '_subtab">' ;

			foreach ( $sections as $id => $label ) {
				$section .= '<li>'
						. '<a href="' . esc_url(
								rac_get_settings_page_url(
										array(
											'tab'     => $this->get_id(),
											'section' => sanitize_title( $id ),
										)
								)
						) . '" '
						. 'class="' . ( $current_section == $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a></li> | ' ;
			}

			$section = rtrim( $section, '| ' ) ;

			$section .= '</ul><br class="clear">' ;

			echo wp_kses_post( $section ) ;
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section, $current_sub_section ;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section ;

			$settings = $this->get_settings( $section ) ;

			WC_Admin_Settings::output_fields( $settings ) ;

			do_action( sanitize_key( $this->get_plugin_slug() . '_' . $this->get_id() . '_' . $section . '_display' ) ) ;
		}

		/**
		 * Output the settings buttons.
		 */
		public function output_buttons() {

			if ( ! $this->show_buttons() ) {
				return ;
			}

			RAC_Settings::output_buttons( $this->show_reset_button() ) ;
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section, $current_sub_section ;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section ;

			if ( ! isset( $_POST[ 'save' ] ) || empty( $_POST[ 'save' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				return ;
			}

			check_admin_referer( 'rac_save_settings', '_rac_nonce' ) ;

			$settings = $this->get_settings( $section ) ;

			WC_Admin_Settings::save_fields( $settings ) ;
			RAC_Settings::add_message( esc_html__( 'Your settings have been saved', 'coming-soon-products-notifier-for-woocommerce' ) ) ;

			do_action( sanitize_key( $this->get_plugin_slug() . '_' . $this->get_id() . '_settings_after_save' ) ) ;
		}

		/**
		 * Reset settings.
		 */
		public function reset() {
			global $current_section, $current_sub_section ;

			$section = ( $current_sub_section ) ? $current_sub_section : $current_section ;

			if ( ! isset( $_POST[ 'reset' ] ) || empty( $_POST[ 'reset' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				return ;
			}

			check_admin_referer( 'rac_reset_settings', '_rac_nonce' ) ;

			$settings = $this->get_settings( $section ) ;
			RAC_Settings::reset_fields( $settings ) ;
			RAC_Settings::add_message( esc_html__( 'Your settings have been reset', 'coming-soon-products-notifier-for-woocommerce' ) ) ;

			do_action( sanitize_key( $this->get_plugin_slug() . '_' . $this->get_id() . '_settings_after_reset' ) ) ;
		}

		/**
		 * Output the extra fields
		 */
		public function output_extra_fields() {
			
		}

		/**
		 * Get the option key.
		 * 
		 * @return string
		 */
		public function get_option_key( $key ) {
			return sanitize_key( $this->get_plugin_slug() . '_' . $this->get_id() . '_' . $key ) ;
		}

	}

}
