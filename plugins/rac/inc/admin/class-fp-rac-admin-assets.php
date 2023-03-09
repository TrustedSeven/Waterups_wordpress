<?php

/**
 * Admin Assets.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Admin_Assets' ) ) {

	/**
	 * Class.
	 */
	class FP_RAC_Admin_Assets {

		/**
		 * Suffix.
		 * 
		 * @var string
		 */
		private static $suffix ;

		/**
		 * Class Initialization.
		 */
		public static function init() {
			self::$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ;

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_js_files' ), 20 ) ;
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_css_files' ) ) ;
		}

		/**
		 * Enqueue external JS files.
		 */
		public static function external_css_files() {
			$screen_ids   = fp_rac_page_screen_ids() ;
			$newscreenids = get_current_screen() ;
			$screenid     = str_replace( 'edit-', '', $newscreenids->id ) ;

			if ( ! in_array( $screenid, $screen_ids ) ) {
				return ;
			}

			wp_enqueue_style( 'jquery_smoothness_ui', RAC_PLUGIN_URL . '/assets/css/jquery_smoothness_ui.css', array(), RAC_VERSION ) ;
			wp_enqueue_style( 'fp-rac-admin', RAC_PLUGIN_URL . '/assets/css/admin.css', array(), RAC_VERSION ) ;
			wp_enqueue_style( 'fp-rac-welcome-page', RAC_PLUGIN_URL . '/assets/css/fp-rac-welcome-page.css', array(), RAC_VERSION ) ;
		}

		/**
		 * Enqueue external JS files.
		 */
		public static function external_js_files() {
			$screen_ids   = fp_rac_page_screen_ids() ;
			$newscreenids = get_current_screen() ;
			$screenid     = str_replace( 'edit-', '', $newscreenids->id ) ;

			if ( ! in_array( $screenid, $screen_ids ) ) {
				return ;
			}

			$enqueue_array = array(
				'rac-datepicker'    => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'datepicker_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-admin'         => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'admin_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-validate'      => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'validate_text_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-template'      => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'email_template_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-emailtab'      => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'email_tab_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-emailtab_ajax' => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'email_template_ajax_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-jscolor'       => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'jscolor_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-generaltab'    => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'general_tab_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-cartlist'      => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'cartlist_tab_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-updatetab'     => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'previous_orders_tab_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-trouble'       => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'troubleshoot_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-coupon'        => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'coupon_tab_enqueue_scripts' ),
					'restrict' => true,
				),
				'rac-graph'         => array(
					'callable' => array( 'FP_RAC_Admin_Assets', 'graph_enqueue_scripts' ),
					'restrict' => 'raccartlist_page_rac_reports' == $screenid,
				),
					) ;

			$enqueue_array = apply_filters( 'fp_rac_admin_assets', $enqueue_array ) ;
			if ( ! rac_check_is_array( $enqueue_array ) ) {
				return ;
			}

			foreach ( $enqueue_array as $key => $enqueue ) {
				if ( ! rac_check_is_array( $enqueue ) ) {
					continue ;
				}

				if ( $enqueue[ 'restrict' ] ) {
					call_user_func_array( $enqueue[ 'callable' ], array() ) ;
				}
			}
		}

		public static function validate_text_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp-rac-settings-validation', RAC_PLUGIN_URL . '/assets/js/fp-rac-settings-validation.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp-rac-settings-validation', 'fp_validate_text_params', array(
				'rac_warning_message' => esc_html__( 'Please enter a value greater than ', 'recoverabandoncart' ),
			) ) ;
		}

		public static function jscolor_enqueue_scripts() {
			wp_enqueue_script( 'jscolor', RAC_PLUGIN_URL . '/assets/js/jscolor/jscolor.js', array( 'jquery' ), RAC_VERSION ) ;
		}

		public static function datepicker_enqueue_scripts() {
			wp_enqueue_script( 'fp-rac-datepicker-enhanced', RAC_PLUGIN_URL . '/assets/js/fp-rac-datepicker-enhanced.js', array( 'jquery', 'jquery-ui-datepicker' ), RAC_VERSION ) ;
			wp_enqueue_script( 'iris' ) ;
		}

		public static function general_tab_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_general_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-general-tab.js', array( 'jquery' ), RAC_VERSION ) ;
		}

		public static function email_tab_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_advance_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-advance-tab.js', array( 'jquery' ), RAC_VERSION ) ;
		}

		public static function email_template_enqueue_scripts() {
			wp_enqueue_media() ;
			wp_enqueue_script( 'fp_email_template', RAC_PLUGIN_URL . '/assets/js/fp-rac-email-templates.js', array( 'jquery' ), RAC_VERSION ) ;
		}

		public static function email_template_ajax_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_email_template_ajax', RAC_PLUGIN_URL . '/assets/js/fp-rac-email-template-ajax.js', array( 'jquery', 'jquery-blockui' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp_email_template_ajax', 'fp_email_template_ajax_obj', array(
				'rac_valid_email_id_msg'     => __( 'Please enter email id', 'recoverabandoncart' ),
				'rac_valid_text_field_msg'   => __( 'Please Enter any Value', 'recoverabandoncart' ),
				'rac_valid_search_field_msg' => __( 'Please select any Product/Category', 'recoverabandoncart' ),
				'manual_send_email_template' => wp_create_nonce( 'manual-send-email-template' ),
				'email_template_status'      => wp_create_nonce( 'email-template-status' ),
			) ) ;
		}

		public static function cartlist_tab_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_cartlist_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-cart-list-tab.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp_rac_cartlist_tab', 'fp_rac_cartlist_tab_obj', array(
				'rac_cart_list_manual_recovered_alert' => __( 'Do you want to change the status of this cart to Recovered?', 'recoverabandoncart' ),
				'rac_save_label'                       => __( 'save', 'recoverabandoncart' ),
				'update_guest_email'                   => wp_create_nonce( 'update-guest-email' ),
				'recover_status'                       => wp_create_nonce( 'recover-status' ),
				'mailstatus_cartlist'                  => wp_create_nonce( 'mailstatus-cartlist' ),
				'rac_unsubcribe_nonce'                 => wp_create_nonce( 'unsubscribe-email' ),
				'rac_manual_order_id_nonce'            => wp_create_nonce( 'rac_manual-order-id' ),
				'rac_cart_list_email_info_disp_nonce'  => wp_create_nonce( 'rac_email-info-disp' ),
			) ) ;
		}

		public static function previous_orders_tab_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_previous_order_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-previous-orders-tab.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp_rac_previous_order_tab', 'fp_rac_previous_order_tab_obj', array(
				'rac_updated_count'       => __( 'Orders found and added to Abandon List', 'recoverabandoncart' ),
				'rac_empty_order_message' => __( 'No Orders found', 'recoverabandoncart' ),
				'rac_chunk_count'         => get_option( 'rac_chunk_count_per_ajax', true ),
				'oldorder_cartlist'       => wp_create_nonce( 'oldorder-cartlist' ),
			) ) ;
		}

		public static function troubleshoot_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_troubleshoot_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-troubleshoot-tab.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp_rac_troubleshoot_tab', 'fp_rac_troubleshoot_tab_obj', array(
				'rac_mail_success_message'   => __( 'Mail has been Sent, but this doesn\'t mean mail will be delivered Successfully. Check Wordpress Codex for More info on Mail', 'recoverabandoncart' ),
				'rac_mail_failure_message'   => __( 'Mail not Sent.', 'recoverabandoncart' ),
				'rac_email_function_msg'     => __( 'For WooCommerce 2.3 or higher version mail() function will not load the woocommerce default template. This option will be deprecated', 'recoverabandoncart' ),
				'test_email'                 => wp_create_nonce( 'test-email' ),
				'update_data'                => wp_create_nonce( 'oldorder-update' ),
				'rac_alert_message'          => __( 'Are you sure you want to proceed with the updation? Based on data available, it may take some time to update', 'recoverabandoncart' ),
				'rac_chunk_count'            => get_option( 'rac_chunk_count_per_ajax', true ),
				'rac_update_success_message' => __( 'Update Completed', 'recoverabandoncart' ),
			) ) ;
		}

		public static function coupon_tab_enqueue_scripts() {
			//enqueue script
			wp_enqueue_script( 'fp_rac_coupon_tab', RAC_PLUGIN_URL . '/assets/js/tabs/fp-rac-coupon-tab.js', array( 'jquery' ), RAC_VERSION ) ;
		}

		public static function admin_enqueue_scripts() {
			global $woocommerce ;
			//enqueue script
			wp_enqueue_script( 'fp-rac-admin', RAC_PLUGIN_URL . '/assets/js/fp-rac-admin.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp-rac-admin', 'fp_rac_admin_params', array(
				'upgrade_nonce'       => wp_create_nonce( 'fp-rac-upgrade' ),
				'sortable_nonce'      => wp_create_nonce( 'fp-rac-sortable' ),
				'rac_wc_version'      => ( float ) $woocommerce->version,
				'rac_chunk_count'     => get_option( 'rac_chunk_count_per_ajax', true ),
				'double_click_msg'    => __( 'Double Click here to Edit Email ID for Guest', 'recoverabandoncart' ),
				/* translators: %s - version */
				'upgrade_success_msg' => sprintf( __( '<h4>Upgrade to v%s Completed Successfully.</h4>', 'recoverabandoncart' ), RAC_VERSION ),
				'upgrade_success_url' => add_query_arg( array( 'page' => 'recover-abandoned-cart-welcome-page' ), admin_url( 'admin.php' ) ),
				'upgrade_alert_msg'   => __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'recoverabandoncart' ),
			) ) ;
		}

		public static function graph_enqueue_scripts() {
			include_once RAC_PLUGIN_PATH . '/inc/admin/menu/class-fp-rac-fpracreport-submenu.php' ;

			wp_enqueue_script( 'flot', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot' . self::$suffix . '.js', array( 'jquery' ), WC_VERSION ) ;
			wp_enqueue_script( 'flot-resize', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.resize' . self::$suffix . '.js', array( 'jquery', 'flot' ), WC_VERSION ) ;
			wp_enqueue_script( 'flot-time', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.time' . self::$suffix . '.js', array( 'jquery', 'flot' ), WC_VERSION ) ;
			wp_enqueue_script( 'flot-pie', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.pie' . self::$suffix . '.js', array( 'jquery', 'flot' ), WC_VERSION ) ;
			wp_enqueue_script( 'flot-stack', WC()->plugin_url() . '/assets/js/jquery-flot/jquery.flot.stack' . self::$suffix . '.js', array( 'jquery', 'flot' ), WC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp-rac-admin', 'fp_rac_graph_params', array(
				'mail_log'              => FP_RAC_Reports_Tab::get_mail_logs(),
				'recovered_orders'      => FP_RAC_Reports_Tab::get_recovered_logs(),
				'abandon_cart'          => FP_RAC_Reports_Tab::get_abandoned_carts(),
				'tick_size'             => FP_RAC_Reports_Tab::get_tick_size(),
				'email_log_label'       => __( 'Email Log', 'recoverabandoncart' ),
				'abandon_cart_label'    => __( 'Abandoned Carts', 'recoverabandoncart' ),
				'recovered_order_label' => __( 'Recovered Orders', 'recoverabandoncart' ),
			) ) ;
		}

	}

	FP_RAC_Admin_Assets::init() ;
}
