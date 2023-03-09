<?php

/**
 * Initialize the plugin.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'FP_RAC_Install' ) ) {

	/**
	 * Class.
	 */
	class FP_RAC_Install {

		/**
		 * Class initialization.
		 */
		public static function init() {
			add_filter( 'woocommerce_init', array( __CLASS__, 'check_version' ) ) ;
			add_action( 'admin_init', array( __CLASS__, 'fp_rac_preview_email_template' ) ) ;
			add_action( 'plugins_loaded', array( __CLASS__, 'fp_rac_background_process_redirect' ) ) ;
			add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 ) ;
			add_action( 'rac_cron_job', array( 'FP_RAC_Automatic_Email', 'fp_rac_cron_job_mailing' ) ) ;
			add_filter( 'plugin_action_links_' . RAC_PLUGIN_BASE_NAME, array( __CLASS__, 'settings_link' ) ) ;
			add_filter( 'woocommerce_attribute_label', array( __CLASS__, 'decode_labels_for_non_english_sites' ), 10, 2 ) ;
			add_action( 'rac_hourly_cron', array( __CLASS__, 'rac_hourly_cron_functions' ), 999 ) ;
		}

		/**
		 * Initializing the Progress Bar
		 *
		 */
		public static function fp_rac_background_process_redirect() {
			$background = get_transient( 'fp_rac_background_process_transient' ) ;
			$coupon     = get_transient( 'fp_rac_coupon_background_process_transient' ) ;
			if ( ! $background && ! $coupon ) {
				return ;
			}
			if ( $background ) {
				delete_transient( 'fp_rac_background_process_transient' ) ;
				FP_RAC_Main_Function_Importing_Part::handle_emailtemplate() ;
			}

			if ( $coupon ) {
				delete_transient( 'fp_rac_coupon_background_process_transient' ) ;
				FP_RAC_WooCommerce_Log::log( 'v' . RAC_VERSION . ' Upgrade Started' ) ;
				FP_RAC_Main_Function_Importing_Part::handle_couponcode() ;
			}

			$admin_url    = admin_url( 'edit.php' ) ;
			$redirect_url = esc_url_raw( add_query_arg( array( 'post_type' => 'raccartlist', 'page' => 'rac_reports', 'rac_updating_action' => 'rac_updating_process' ), $admin_url ) ) ;

			wp_safe_redirect( $redirect_url ) ;
		}

		/**
		 * Preview Email Template
		 *
		 */
		public static function fp_rac_preview_email_template() {
			if ( isset( $_GET[ 'rac_preview_template' ] ) && isset( $_GET[ 'post_id' ] ) ) {
				if ( isset( $_REQUEST[ '_wpnonce' ] ) && ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST[ '_wpnonce' ] ) ), 'rac-preview-template' ) ) {
					die( 'Security check' ) ;
				}
				include_once RAC_PLUGIN_PATH . '/inc/email-template/class-fp-rac-preview-email-template.php' ;
				FP_RAC_Preview_Email_Template::rac_preview_email_template() ;
				exit() ;
			}
		}

		/**
		 * Check current version of the plugin is updated when activating plugin, if not run updater.
		 */
		public static function check_version() {
			if ( version_compare( get_option( 'rac_version' ), RAC_VERSION, '>=' ) ) {
				return ;
			}

			self::install() ;
		}

		/**
		 * Install.
		 */
		public static function install() {
			FP_RAC_Register_Post_Type::fp_rac_register_post_types() ;
			FP_RAC_Register_Post_Status::fp_rac_register_custom_post_status() ;
			self::set_default_values() ;
			self::maybe_create_default_email_template() ;
			self::update_version() ;
		}

		/**
		 * Update current version.
		 */
		private static function update_version() {
			update_option( 'rac_version', RAC_VERSION ) ;
		}

		/**
		 * Initializing the Welcome Page
		 *
		 */
		public static function fp_rac_welcome_screen_activation() {
			set_transient( '_welcome_screen_activation_redirect_recover_abandoned_cart', true, 30 ) ;
		}

		/**
		 * Add the settings link in the plugin table.
		 */
		public static function settings_link( $links ) {
			$setting_page_link = '<a href="' . esc_url( rac_get_settings_page_url() ) . '">' . __( 'Settings', 'recoverabandoncart' ) . '</a>' ;
			array_unshift( $links, $setting_page_link ) ;

			return $links ;
		}

		/**
		 * Initializing the plugin row.
		 */
		public static function plugin_row_meta( $links, $file ) {
			if ( RAC_PLUGIN_BASE_NAME == $file ) {
				$row_meta = array(
					'about'   => '<a href="' . esc_url( admin_url( 'admin.php?page=recover-abandoned-cart-welcome-page' ) ) . '" aria-label="' . esc_attr__( 'About', 'recoverabandoncart' ) . '">' . esc_html__( 'About', 'recoverabandoncart' ) . '</a>',
					'support' => '<a href="' . esc_url( 'http://fantasticplugins.com/support/' ) . '" aria-label="' . esc_attr__( 'Support', 'recoverabandoncart' ) . '">' . esc_html__( 'Support', 'recoverabandoncart' ) . '</a>',
						) ;

				return array_merge( $links, $row_meta ) ;
			}

			return ( array ) $links ;
		}

		/**
		 * Set the setting default values.
		 */
		public static function set_default_values() {
			if ( ! class_exists( 'RAC_Settings' ) ) {
				include_once( RAC_PLUGIN_PATH . '/inc/admin/menu/class-rac-settings.php' ) ;
			}

			// Get the settings.
			$settings = RAC_Settings::get_settings_pages() ;
			foreach ( $settings as $setting ) {
				$sections = $setting->get_sections() ;

				// Update the section settings.
				if ( rac_check_is_array( $sections ) ) {
					foreach ( $sections as $section_key => $section ) {
						$settings_array = $setting->get_settings( $section_key ) ;
						foreach ( $settings_array as $value ) {
							//Check if the default and id key is exists.
							if ( isset( $value[ 'default' ] ) && isset( $value[ 'id' ] ) ) {
								//Check if option are saved or not.
								if ( get_option( $value[ 'id' ] ) === false ) {
									add_option( $value[ 'id' ], $value[ 'default' ] ) ;
								}
							}
						}
					}
				} else {
					$settings_fields = $setting->get_settings( $setting->get_id() ) ;
					foreach ( $settings_fields as $value ) {
						//Check if default and id key is exists.
						if ( isset( $value[ 'default' ] ) && isset( $value[ 'id' ] ) ) {
							//Check if option are saved or not.
							if ( get_option( $value[ 'id' ] ) === false ) {
								add_option( $value[ 'id' ], $value[ 'default' ] ) ;
							}
						}
					}
				}
			}
		}

		/**
		 * May be create a default email template.
		 */
		public static function maybe_create_default_email_template() {
			global $wpdb ;
			$set_cron          = true ;
			$post_arg          = array(
				'posts_per_page' => -1,
				'post_status'    => array( 'racactive', 'racinactive', 'trash' ),
				'post_type'      => 'racemailtemplate',
				'fields'         => 'ids'
					) ;
			$posts             = fp_rac_check_query_having_posts( $post_arg ) ;
			$coupon_code_array = get_option( 'rac_coupon_for_user' ) ;
			$table_count       = FP_RAC_Main_Function_Importing_Part::fp_rac_get_old_table_count() ;
			$coupon_get_option = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE %s', '%abandon_time_of%' ) ) ;
			if ( ( 'yes' != get_option( 'rac_upgrade_success' ) ) && $table_count > 0 ) {
				if ( FP_RAC_Main_Function_Importing_Part::fp_rac_upgrade_file_exists() ) {
					FP_RAC_Main_Function_Importing_Part::initiate_to_background_process() ;
					$set_cron = false ;
				}
			} elseif ( ( 'yes' != get_option( 'rac_coupon_upgrade_success' ) ) && ( ! empty( $coupon_code_array ) || ! empty( $coupon_get_option ) ) ) {
				if ( FP_RAC_Main_Function_Importing_Part::fp_rac_upgrade_file_exists() ) {
					FP_RAC_Main_Function_Importing_Part::initiate_to_coupon_background_process() ;
					$set_cron = false ;
				}
			} else {
				if ( ( empty( $posts ) ) ) {
					$arg        = array(
						'post_status'  => 'racactive',
						'post_type'    => 'racemailtemplate',
						'post_title'   => 'Default',
						'post_content' => "Hi {rac.firstname},<br><br>We noticed you have added the following Products in your Cart, but haven't completed the purchase. {rac.Productinfo}<br><br>We have captured the Cart for your convenience. Please use the following link to complete the purchase {rac.cartlink}<br><br>Thanks.",
							) ;
					$id         = wp_insert_post( $arg ) ;
					$post_array = array(
						'rac_template_status'            => 'ACTIVE',
						'rac_template_mail'              => 'HTML',
						'rac_old_template_id'            => $id,
						'rac_template_link'              => '',
						'rac_template_sender_opt'        => 'woo',
						'rac_template_from_name'         => 'Admin',
						'rac_template_from_email'        => get_option( 'admin_email' ),
						'rac_template_blind_carbon_copy' => '',
						'rac_template_subject'           => 'Recovering Abandon Cart',
						'rac_template_sending_type'      => 'days',
						'rac_template_sending_duration'  => '1',
						'rac_template_anchor_text'       => 'Cart Link',
							) ;

					if ( rac_check_is_array( $post_array ) ) {
						foreach ( $post_array as $name => $value ) {
							update_post_meta( $id, $name, $value ) ;
						}
					}
				}
			}
			if ( $set_cron ) {
				self::fp_rac_welcome_screen_activation() ; //welcome page
			}
		}

		/**
		 * Hourly cron functions.
		 *
		 */
		public static function rac_hourly_cron_functions() {
			self::rac_delete_abandon_carts_after_selected_days() ;
			self::rac_delete_emails_after_selected_days() ;
			FP_RAC_Previous_Order_Data::cron_job_previous_order() ;
		}

		/**
		 * Delete Cart list after Expired
		 */
		public static function rac_delete_abandon_carts_after_selected_days() {
			if ( 'yes' == get_option( 'enable_remove_abandon_after_x_days', 'no' ) ) {
				$post_status = get_option( 'rac_delete_cart_selection', array( 'rac-cart-abandon' ) ) ;
				$post_status = empty( $post_status ) ? array( 'rac-cart-abandon' ) : $post_status ;

				$args = array(
					'post_type'      => 'raccartlist',
					'post_status'    => $post_status,
					'fields'         => 'ids',
					'posts_per_page' => '-1',
						) ;

				$posts = fp_rac_check_query_having_posts( $args ) ;

				if ( ! rac_check_is_array( $posts ) ) {
					return ;
				}

				update_option( 'rac_cartlist_auto_delete_background_updater_data', $posts ) ;
				FP_RAC_Main_Function_Importing_Part::handle_cartlist_auto_delete() ;
			}
		}

		/**
		 * Delete E-mails from Log for after Expired
		 */
		public static function rac_delete_emails_after_selected_days() {
			if ( 'yes' == get_option( 'enable_remove_email_log_after_x_days' ) ) {
				$duration = '-' . get_option( 'rac_remove_email_log_after_x_days', '30' ) . 'days' ;
				$args     = array(
					'posts_per_page' => '-1',
					'post_type'      => 'racmaillog',
					'post_status'    => array( 'publish', 'trash' ),
					'fields'         => 'ids',
					'date_query'     => array(
						'before' => gmdate( 'Y-m-d', strtotime( $duration ) )
					)
						) ;

				$email_logs = fp_rac_check_query_having_posts( $args ) ;

				if ( ! rac_check_is_array( $email_logs ) ) {
					return ;
				}

				update_option( 'rac_email_log_auto_delete_background_updater_data', $email_logs ) ;
				FP_RAC_Main_Function_Importing_Part::handle_email_log_auto_delete() ;
			}
		}

		/**
		 * Decodes labels for non english sites
		 *
		 */
		public static function decode_labels_for_non_english_sites( $label, $name, $product = null ) {
			return rawurldecode( $label ) ;
		}

	}

	FP_RAC_Install::init() ;
}
