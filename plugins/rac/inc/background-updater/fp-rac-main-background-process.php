<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit() ;
}

if ( ! class_exists( 'FP_RAC_Main_Function_Importing_Part' ) ) {

	/**
	 * FP_RAC_Main_Function_Importing_Part Class.
	 */
	class FP_RAC_Main_Function_Importing_Part {

		public static $process_cartlist ;
		public static $process_emailtemplate ;
		public static $process_maillog ;
		public static $process_recoveredorder ;
		public static $progress_bar ;
		public static $process_couponcode ;
		public static $process_getoption ;
		public static $process_previous_order ;
		public static $process_cartlist_auto_delete ;
		public static $process_email_log_auto_delete ;

		public static function init() {

			if ( self::fp_rac_upgrade_file_exists() ) {
				$background_files = array(
					'WP_Async_Request'                                => untrailingslashit( WP_PLUGIN_DIR ) . '/woocommerce/includes/libraries/wp-async-request.php' ,
					'WP_Background_Process'                           => untrailingslashit( WP_PLUGIN_DIR ) . '/woocommerce/includes/libraries/wp-background-process.php' ,
					'FP_RAC_Email_Template_Background_Process'        => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-emailtemplate-background-process.php' ,
					'FP_RAC_Cartlist_Background_Process'              => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-cartlist-background-process.php' ,
					'FP_RAC_Emaillog_Background_Process'              => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-maillog-background-process.php' ,
					'FP_RAC_Recovered_Order_Background_Process'       => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-recoveredorder-background-process.php' ,
					'FP_RAC_Coupon_Code_Background_Process'           => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-couponcode-background-process.php' ,
					'FP_RAC_Get_Option_Background_Process'            => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-get-option-background-process.php' ,
					'FP_RAC_Previous_Order_Background_Process'        => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-previous-order-background-process.php' ,
					'FP_RAC_Cartlist_Auto_Delete_Background_Process'  => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-carlist-auto-delete-background-process.php' ,
					'FP_RAC_Email_Log_Auto_Delete_Background_Process' => RAC_PLUGIN_PATH . '/inc/background-updater/fp-rac-email-log-auto-delete-background-process.php' ,
					'FP_RAC_Updating_Process'                         => RAC_PLUGIN_PATH . '/inc/background-updater/class-fp-rac-updating-process.php' ,
						) ;
				if ( rac_check_is_array( $background_files ) ) {
					foreach ( $background_files as $classname => $file_path ) {
						if ( ! class_exists( $classname ) ) {
							include_once($file_path) ;
						}
					}
				}
				add_action( 'wp_ajax_rac_database_upgrade_process' , array( __CLASS__ , 'initiate_to_background_process_ajax' ) ) ;
				add_action( 'wp_ajax_rac_database_coupon_upgrade_process' , array( __CLASS__ , 'initiate_to_coupon_background_process_ajax' ) ) ;

				self::$process_cartlist              = new FP_RAC_Cartlist_Background_Process() ;
				self::$process_emailtemplate         = new FP_RAC_Email_Template_Background_Process() ;
				self::$process_maillog               = new FP_RAC_Emaillog_Background_Process() ;
				self::$process_recoveredorder        = new FP_RAC_Recovered_Order_Background_Process() ;
				self::$process_couponcode            = new FP_RAC_Coupon_Code_Background_Process() ;
				self::$process_getoption             = new FP_RAC_Get_Option_Background_Process() ;
				self::$process_previous_order        = new FP_RAC_Previous_Order_Background_Process() ;
				self::$process_cartlist_auto_delete  = new FP_RAC_Cartlist_Auto_Delete_Background_Process() ;
				self::$process_email_log_auto_delete = new FP_RAC_Email_Log_Auto_Delete_Background_Process() ;
				self::$progress_bar                  = new FP_RAC_Updating_Process() ;
			}
			add_action( 'admin_head' , array( __CLASS__ , 'display_notice_in_top' ) ) ;
		}

		/*
		 * Get Overal Count of Old table data.
		 */

		public static function fp_rac_get_old_table_count() {
			global $wpdb ;
			$table_count     = 0 ;
			$tablename_array = array(
				'templates_email' ,
				'email_logs' ,
				'abandoncart' ,
					) ;
			foreach ( $tablename_array as $value ) {
				$tablename     = $wpdb->prefix . 'rac_' . $value ;
				$table_exists  = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s' , $tablename ) ) ;
				$template_data = ! empty( $table_exists ) ? $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %s' , $tablename ) ) : array() ;

				$table_count += count( $template_data ) ;
			}
			$recovered_data = get_option( 'fp_rac_recovered_order_ids' ) ;
			$recovered_ids  = array_filter( $recovered_data ? $recovered_data : array() ) ;

			$table_count += count( $recovered_ids ) ;

			return $table_count ;
		}

		/*
		 * Check if Background Related Files exists
		 */

		public static function fp_rac_upgrade_file_exists() {
			$async_file      = file_exists( untrailingslashit( WP_PLUGIN_DIR ) . '/woocommerce/includes/libraries/wp-async-request.php' ) ;
			$background_file = file_exists( untrailingslashit( WP_PLUGIN_DIR ) . '/woocommerce/includes/libraries/wp-background-process.php' ) ;

			if ( $async_file && $background_file ) {
				return true ;
			}

			return false ;
		}

		/*
		 * Display when required some updates for this plugin
		 */

		public static function display_notice_in_top() {
			global $wpdb ;
			$return = false ;

			if ( 'yes' == get_option( 'rac_coupon_upgrade_success' ) && 'yes' == get_option( 'rac_upgrade_success' ) ) {
				return ;
			}

			if ( 'yes' != get_option( 'rac_upgrade_success' ) ) {
				$table_count = self::fp_rac_get_old_table_count() ;
				if ( $table_count > 0 ) {
					$return = 1 ;
				} else {
					update_option( 'rac_upgrade_success' , 'yes' ) ;
				}
			} else {
				$coupon_code_array = get_option( 'rac_coupon_for_user' ) ;
				$coupon_get_option = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE %s' , '%abandon_time_of%' ) ) ;
				if ( ! empty( $coupon_code_array ) || ! empty( $coupon_get_option ) ) {
					$return = 2 ;
				} else {
					update_option( 'rac_coupon_upgrade_success' , 'yes' ) ;
				}
			}

			if ( $return ) {
				if ( self::fp_rac_upgrade_file_exists() ) {
					$action = ( 1 == $return ) ? 'rac_database_upgrade_process' : 'rac_database_coupon_upgrade_process' ;
					$link   = "<a id='rac_display_notice' data-methd='cron' data-action='" . $action . "' href='#'>" . __( 'Click here' , 'recoverabandoncart' ) . '</a>' ;
					?>
					<div id="message" class="notice notice-warning">
						<p>
							<strong> 
								<?php
								/* translators: %s- link */
								echo wp_kses_post( sprintf( __( 'Recover Abandoned Cart requires Database Upgrade, %s to proceed with the Upgrade' , 'recoverabandoncart' ) , $link ) ) ;
								?>
							</strong>
						</p>
					</div>
					<div id="updating_message" class="updated notice-warning fp-rac-hide"><p><strong> <?php esc_html_e( 'Recover Abandoned Cart Data Update - Your database is being updated in the background.' , 'recoverabandoncart' ) ; ?></strong></p></div>
					<?php
				} else {
					$support_link = '<a href="http://fantasticplugins.com/support">' . __( 'Support' , 'recoverabandoncart' ) . '</a>' ;
					?>
					<div id="message" class="notice notice-warning"><p><strong> 
								<?php
								/* translators: %1s- version, %2s-support link */
								echo wp_kses_post( sprintf( __( 'Upgrade to v%1$s has failed. Please contact our %2$s' , 'recoverabandoncart' ) , RAC_VERSION , $support_link ) ) ;
								?>
							</strong></p></div>
					<?php
				}
			}
		}

		/**
		 * Handle Cart List 
		 */
		public static function handle_cartlist( $offset = 0, $limit = 500 ) {
			global $wpdb ;
			$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_abandoncart ORDER BY ID ASC LIMIT %d,%d' , $offset , $limit ) ) ;
			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_cartlist->push_to_queue( $id ) ;
				}
			} else {
				self::$process_cartlist->push_to_queue( 'rac_no_data' ) ;
			}
			//update offset 
			update_option( 'rac_cartlist_background_updater_offset' , $limit + $offset ) ;
			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'Cart Lists Upgrade Started' ) ;
				self::$progress_bar->fp_increase_progress( 15 ) ;
			}
			self::$process_cartlist->save()->dispatch() ;
		}

		/**
		 * Handle Previous Order
		 */
		public static function handle_previous_order( $offset = 0, $limit = 1000 ) {
			$ids = get_option( 'rac_previous_order_background_updater_data' ) ;
			$ids = array_slice( $ids , $offset , $limit ) ;
			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_previous_order->push_to_queue( $id ) ;
				}
			} else {
				self::$process_previous_order->push_to_queue( 'rac_no_data' ) ;
			}
			//update offset 
			update_option( 'rac_previous_order_background_updater_offset' , $limit + $offset ) ;
			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'Previous Order Upgrade Started' ) ;
			}
			self::$process_previous_order->save()->dispatch() ;
		}

		/**
		 * Handle Cart List Auto Remove
		 */
		public static function handle_cartlist_auto_delete( $offset = 0, $limit = 1000 ) {
			$ids = get_option( 'rac_cartlist_auto_delete_background_updater_data' ) ;
			$ids = array_slice( $ids , $offset , $limit ) ;

			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_cartlist_auto_delete->push_to_queue( $id ) ;
				}
			} else {
				self::$process_cartlist_auto_delete->push_to_queue( 'rac_no_data' ) ;
			}

			//update offset 
			update_option( 'rac_cartlist_auto_delete_background_updater_offset' , $limit + $offset ) ;

			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'Cartlist automatic delete started' ) ;
			}

			self::$process_cartlist_auto_delete->save()->dispatch() ;
		}

		/**
		 * Handle E-mail Log Auto Remove
		 */
		public static function handle_email_log_auto_delete( $offset = 0, $limit = 1000 ) {
			$ids = get_option( 'rac_email_log_auto_delete_background_updater_data' ) ;
			$ids = array_slice( $ids , $offset , $limit ) ;

			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_email_log_auto_delete->push_to_queue( $id ) ;
				}
			} else {
				self::$process_email_log_auto_delete->push_to_queue( 'rac_no_data' ) ;
			}

			//update offset 
			update_option( 'rac_email_log_auto_delete_background_updater_offset' , $limit + $offset ) ;

			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'E-mail log automatic delete started' ) ;
			}

			self::$process_email_log_auto_delete->save()->dispatch() ;
		}

		/**
		 * Handle Email Template 
		 */
		public static function handle_emailtemplate( $offset = 0, $limit = 1000 ) {
			global $wpdb ;
			$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_templates_email ORDER BY ID ASC LIMIT %d,%d' , $offset , $limit ) ) ;
			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_emailtemplate->push_to_queue( $id ) ;
				}
			} else {
				self::$process_emailtemplate->push_to_queue( 'rac_no_data' ) ;
			}
			//update offset 
			update_option( 'rac_emailtemplate_background_updater_offset' , $limit + $offset ) ;
			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'v' . RAC_VERSION . ' Upgrade Started' ) ;
				FP_RAC_WooCommerce_Log::log( 'Email Templates Upgrade Started' ) ;
				self::$progress_bar->fp_increase_progress( 5 ) ;
			}
			self::$process_emailtemplate->save()->dispatch() ;
		}

		/**
		 * Handle Mail Log 
		 */
		public static function handle_maillog( $offset = 0, $limit = 1000 ) {
			global $wpdb ;
			$ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_email_logs ORDER BY ID ASC LIMIT %d,%d' , $offset , $limit ) ) ;
			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id ) {
					self::$process_maillog->push_to_queue( $id ) ;
				}
			} else {
				self::$process_maillog->push_to_queue( 'rac_no_data' ) ;
			}
			//update offset 
			update_option( 'rac_maillog_background_updater_offset' , $limit + $offset ) ;
			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'Email Log Upgrade Started' ) ;
				self::$progress_bar->fp_increase_progress( 35 ) ;
			}
			self::$process_maillog->save()->dispatch() ;
		}

		/**
		 * Handle Recovered Order 
		 */
		public static function handle_recoveredorder() {
			$ids = array_filter( get_option( 'fp_rac_recovered_order_ids' ) ? get_option( 'fp_rac_recovered_order_ids' ) : array() ) ;
			if ( rac_check_is_array( $ids ) ) {
				foreach ( $ids as $id => $value ) {
					self::$process_recoveredorder->push_to_queue( $id ) ;
				}
			} else {
				self::$process_recoveredorder->push_to_queue( 'rac_no_data' ) ;
			}

			FP_RAC_WooCommerce_Log::log( 'Recovered Orders Upgrade Started' ) ;
			self::$progress_bar->fp_increase_progress( 55 ) ;
			self::$process_recoveredorder->save()->dispatch() ;
		}

		/**
		 * Handle Coupon Code 
		 */
		public static function handle_couponcode() {
			$get_datas = array_filter( get_option( 'rac_coupon_for_user' ) ? get_option( 'rac_coupon_for_user' ) : array() ) ;
			if ( rac_check_is_array( $get_datas ) ) {
				foreach ( $get_datas as $email => $value ) {
					self::$process_couponcode->push_to_queue( $email ) ;
				}
			} else {
				self::$process_couponcode->push_to_queue( 'rac_no_data' ) ;
			}
			FP_RAC_WooCommerce_Log::log( 'Coupon Code Upgrade Started' ) ;
			self::$progress_bar->fp_increase_progress( 75 ) ;
			self::$process_couponcode->save()->dispatch() ;
		}

		/**
		 * Handle Coupon Code 
		 */
		public static function handle_get_option( $offset = 0, $limit = 1000 ) {
			global $wpdb ;
			$get_datas = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT ID FROM {$wpdb->posts} as p INNER JOIN {$wpdb->postmeta} as p1 ON p.ID=p1.post_id WHERE p.post_type = 'raccartlist' AND p.post_status IN('rac-cart-new', 'rac-cart-abandon', 'rac-cart-recovered', 'trash') LIMIT %d,%d" , $offset , $limit ) ) ;
			if ( rac_check_is_array( $get_datas ) ) {
				foreach ( $get_datas as $cart_id ) {
					self::$process_getoption->push_to_queue( $cart_id ) ;
				}
			} else {
				self::$process_getoption->push_to_queue( 'rac_no_data' ) ;
			}
			//update offset 
			update_option( 'rac_get_option_background_updater_offset' , $limit + $offset ) ;
			if ( 0 == $offset ) {
				FP_RAC_WooCommerce_Log::log( 'Get Option Upgrade Started' ) ;
				self::$progress_bar->fp_increase_progress( 90 ) ;
			}
			self::$process_getoption->save()->dispatch() ;
		}

		/**
		 * Push to queue coupon details.
		 */
		public static function initiate_to_coupon_background_process_ajax() {
			check_ajax_referer( 'fp-rac-upgrade' , 'fp_rac_security' ) ;

			try {
				if ( ! isset( $_POST ) ) {
					throw new exception( esc_html__( 'Invalid Request' , 'recoverabandoncart' ) ) ;
				}
				// Return if the current user does not have permission.
				if ( ! current_user_can( 'edit_posts' ) ) {
					throw new exception( esc_html__( "You don't have permission to do this action" , 'recoverabandoncart' ) ) ;
				}

				self::initiate_to_coupon_background_process() ;

				$redirect_url = esc_url_raw( add_query_arg( array( 'post_type' => 'raccartlist' , 'page' => 'rac_reports' , 'rac_updating_action' => 'rac_updating_process' ) , admin_url( 'edit.php' ) ) ) ;

				wp_send_json_success( array( 'url' => $redirect_url ) ) ;
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) ) ;
			}
		}

		/**
		 * Push to queue coupon details.
		 */
		public static function initiate_to_coupon_background_process() {
			self::fp_rac_reset_option() ;
			self::$progress_bar->fp_increase_progress( 30 ) ;
			set_transient( 'fp_rac_coupon_background_process_transient' , true , 30 ) ;
		}

		/**
		 * Push to queue all ids
		 */
		public static function initiate_to_background_process_ajax() {
			check_ajax_referer( 'fp-rac-upgrade' , 'fp_rac_security' ) ;

			try {
				if ( ! isset( $_POST ) ) {
					throw new exception( esc_html__( 'Invalid Request' , 'recoverabandoncart' ) ) ;
				}
				// Return if the current user does not have permission.
				if ( ! current_user_can( 'edit_posts' ) ) {
					throw new exception( esc_html__( "You don't have permission to do this action" , 'recoverabandoncart' ) ) ;
				}

				self::initiate_to_background_process() ;

				$redirect_url = esc_url_raw( add_query_arg( array( 'post_type' => 'raccartlist' , 'page' => 'rac_reports' , 'rac_updating_action' => 'rac_updating_process' ) , admin_url( 'edit.php' ) ) ) ;

				wp_send_json_success( array( 'url' => $redirect_url ) ) ;
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) ) ;
			}
		}

		/**
		 * Push to queue all ids
		 */
		public static function initiate_to_background_process() {
			$total = self::fp_rac_overall_batch_count() ;
			if ( ! empty( $total ) ) {
				self::fp_rac_reset_option() ;
				set_transient( 'fp_rac_background_process_transient' , true , 30 ) ;
			}
		}

		public static function fp_rac_reset_option() {
			self::$progress_bar->fp_delete_option() ;
			delete_option( 'rac_emailtemplate_background_updater_offset' ) ;
			delete_option( 'rac_cartlist_background_updater_offset' ) ;
			delete_option( 'rac_maillog_background_updater_offset' ) ;
			delete_option( 'rac_get_option_background_updater_offset' ) ;
		}

		public static function fp_rac_overall_batch_count() {
			global $wpdb ;
			$cartlist_ids    = $wpdb->get_col( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_abandoncart ORDER BY ID ASC' ) ;
			$template_ids    = $wpdb->get_col( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_templates_email ORDER BY ID ASC' ) ;
			$emaillog_ids    = $wpdb->get_col( 'SELECT ID FROM ' . $wpdb->prefix . 'rac_email_logs ORDER BY ID ASC' ) ;
			$recover_ids     = get_option( 'fp_rac_recovered_order_ids' ) ;
			$recovered_order = is_array( $recover_ids ) ? array_filter( $recover_ids ) : array() ;

			$total = count( $cartlist_ids ) + count( $template_ids ) + count( $emaillog_ids ) + count( $recovered_order ) ;
			return $total ;
		}

	}

	FP_RAC_Main_Function_Importing_Part::init() ;
}
