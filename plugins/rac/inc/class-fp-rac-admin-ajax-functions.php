<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Admin_Ajax' ) ) {

	/**
	 * FP_RAC_Admin_Ajax Class.
	 */
	class FP_RAC_Admin_Ajax {

		/**
		 * FP_RAC_Admin_Ajax Class Initialization.
		 */
		public static function init() {
			add_action( 'wp_ajax_rac_manual_recovered', array( __CLASS__, 'rac_manual_recovered' ) ) ;
			add_action( 'wp_ajax_edit_value_update_now', array( __CLASS__, 'fp_rac_edit_mail_update_data' ) ) ;
			add_action( 'wp_ajax_rac_email_template_status', array( __CLASS__, 'set_email_template_status' ) ) ;
			add_action( 'wp_ajax_mailstatus_cartlist', array( __CLASS__, 'rac_change_cart_list_mailstatus' ) ) ;
			add_action( 'wp_ajax_rac_drag_n_drop_product_info_column', array( __CLASS__, 'fp_rac_admin_request_from_ajax_sortable' ) ) ;
			add_action( 'wp_ajax_rac_cartlist_email_info_popup', array( __CLASS__, 'popup_for_email_info_display' ) ) ;
			add_action( 'wp_ajax_rac_manual_recover_popup', array( __CLASS__, 'popup_for_manual_recover_method' ) ) ;
			add_action( 'wp_ajax_rac_manual_recover_order_entry', array( __CLASS__, 'manual_recover_entry' ) ) ;
			add_action( 'wp_ajax_nopriv_rac_popup_timedelay', array( __CLASS__, 'popup_time_delay_function' ) ) ;
			add_action( 'wp_ajax_nopriv_rac_handle_checkout_gdpr', array( __CLASS__, 'handle_checkout_gdpr' ) ) ;
		}

		/**
		 * Changing Cart list Mail Status Table.
		 */
		public static function rac_change_cart_list_mailstatus() {
			check_ajax_referer( 'mailstatus-cartlist', 'rac_security' ) ;

			if ( isset( $_POST[ 'row_id' ] ) && isset( $_POST[ 'status' ] ) ) {
				$status = wc_clean( wp_unslash( $_POST[ 'status' ] ) ) ;
				update_post_meta( absint( $_POST[ 'row_id' ] ), 'rac_cart_sending_status', $status ) ;
				echo '1' ;
			}
			exit() ;
		}

		/**
		 * Recovered Cart List by Manually in Cart List Table.
		 */
		public static function rac_manual_recovered() {

			check_ajax_referer( 'recover-status', 'rac_security' ) ;

			if ( isset( $_POST[ 'row_id' ] ) ) {
				$row_id = absint( $_POST[ 'row_id' ] ) ;
				$args   = array( 'ID'          => $row_id,
					'post_status' => 'rac-cart-recovered',
					'post_type'   => 'raccartlist'
						) ;
				wp_update_post( $args ) ;
				echo 1 ;
				update_post_meta( $row_id, 'rac_recover_method', '1' ) ;
			}
			exit() ;
		}

		/**
		 * Update Guest Email manually in Cart List Table
		 */
		public static function fp_rac_edit_mail_update_data() {

			check_ajax_referer( 'update-guest-email', 'rac_security' ) ;
			if ( isset( $_POST[ 'id' ] ) && isset( $_POST[ 'email' ] ) && wc_clean( wp_unslash( $_POST[ 'email' ] ) ) ) {
				$row_id                         = absint( $_POST[ 'id' ] ) ;
				$email_value                    = wc_clean( wp_unslash( $_POST[ 'email' ] ) ) ;
				$cart_list                      = fp_rac_create_cart_list_obj( $row_id ) ;
				$cart_details                   = fp_rac_format_cart_details( $cart_list->cart_details, $cart_list ) ;
				$cart_details[ 'visitor_mail' ] = $email_value ;
				$details                        = base64_encode( maybe_serialize( $cart_details ) ) ;
				update_post_meta( $row_id, 'rac_cart_details', $details ) ;
			}
			exit() ;
		}

		/**
		 * Changing Email Template Sending Status in Email Template Table.
		 */
		public static function set_email_template_status() {

			check_ajax_referer( 'email-template-status', 'rac_security' ) ;

			if ( isset( $_POST[ 'row_id' ] ) && isset( $_POST[ 'status' ] ) ) {
				$requesting_state = wc_clean( wp_unslash( $_POST[ 'status' ] ) ) ;
				$post_id          = absint( $_POST[ 'row_id' ] ) ;
				$status           = 'ACTIVE' != $requesting_state ? 'racactive' : 'racinactive' ;
				$new_status       = 'ACTIVE' != $requesting_state ? 'ACTIVE' : 'NOTACTIVE' ;
				$args             = array(
					'ID'          => $post_id,
					'post_status' => $status
						) ;

				wp_update_post( $args ) ;
				echo esc_html( $new_status ) ;
			}
			exit() ;
		}

		/**
		 * Update Sortable column of email settings.
		 */
		public static function fp_rac_admin_request_from_ajax_sortable() {
			check_ajax_referer( 'fp-rac-sortable', 'rac_security' ) ;

			if ( isset( $_REQUEST[ 'data' ] ) ) {
				update_option( 'drag_and_drop_product_info_sortable_column', wc_clean( wp_unslash( $_REQUEST[ 'data' ] ) ) ) ;
			}
			exit() ;
		}

		/**
		 * To Prepare Customized Popup Window for Display email information.
		 */
		public static function popup_for_email_info_display() {
			check_ajax_referer( 'rac_email-info-disp', 'rac_security' ) ;

			try {
				if ( ! isset( $_POST[ 'cart_list_id' ] ) ) {
					throw new exception( __( 'Invalid Arguments', 'recoverabandoncart' ) ) ;
				}
				$cart_list_id       = absint( $_POST[ 'cart_list_id' ] ) ;
				$cart_list          = fp_rac_create_cart_list_obj( $cart_list_id ) ;
				fp_rac_wpml_switch_lang( $cart_list->wpml_lang ) ;
				ob_start() ;
				?>
				<div class="fp_rac_popup_wrapper">
					<div class="fp_rac_email_info_popup_content">
						<div class="fp_rac_email_info_popup_header">
							<label class="rac_email_info_popup_label">
								<?php echo esc_html( __( 'Cart', 'recoverabandoncart' ) . ' #' . $cart_list_id ) ; ?>
							</label> </div>
						<div class="fp_rac_email_info_popup_close"> <img src=<?php echo esc_url( RAC_PLUGIN_URL . '/assets/images/close.png' ) ; ?> class="rac_popup_close"> </div>
						<div class="fp_rac_email_info_popup_body">
							<div class="fp_rac_email_info_popup_body_content">

								<!-- To Display Abandoned product information -->
								<div class="fp_rac_email_info_product">
									<?php echo do_shortcode( FP_RAC_Polish_Product_Info::fp_rac_extract_cart_details( $cart_list, false ) ) ; ?>
								</div>

								<!-- To Display email status information -->
								<div class="fp_rac_email_info_status">
									<table class="fp_rac_email_info_table">
										<tr>
											<th><?php esc_html_e( 'Email Template', 'recoverabandoncart' ) ; ?></th>
											<th><?php esc_html_e( 'Email Status', 'recoverabandoncart' ) ; ?></th>
											<th><?php esc_html_e( 'Cart Link Status', 'recoverabandoncart' ) ; ?></th>
										</tr>
										<?php
										$mail_sent          = maybe_unserialize( $cart_list->mail_template_id ) ;
										$arg                = array( 'posts_per_page' => -1, 'post_status' => array( 'racactive', 'racinactive' ), 'post_type' => 'racemailtemplate', 'order' => 'ASC', 'orderby' => 'ID' ) ;
										$email_template_all = fp_rac_check_query_having_posts( $arg ) ;

										if ( rac_check_is_array( $email_template_all ) ) {
											foreach ( $email_template_all as $check_all_email_temp ) {
												$old_email_id = get_post_meta( $check_all_email_temp->ID, 'rac_old_template_id', true ) ;
												?>
												<tr>
													<td><?php echo esc_html( $check_all_email_temp->post_title ) ; ?></td>

													<td>
														<?php
														if ( ! empty( $mail_sent ) ) {
															if ( in_array( $old_email_id, ( array ) $mail_sent ) ) {
																esc_html_e( 'Sent', 'recoverabandoncart' ) ;
															} else {
																esc_html_e( 'Not Sent', 'recoverabandoncart' ) ;
															}
														} else {
															esc_html_e( 'Not Sent', 'recoverabandoncart' ) ;
														}
														?>
													</td>

													<td>
														<?php
														if ( ! empty( $cart_list->link_status ) ) {
															$mails_clicked = maybe_unserialize( $cart_list->link_status ) ;

															if ( in_array( $old_email_id, ( array ) $mails_clicked ) ) {
																esc_html_e( 'Clicked', 'recoverabandoncart' ) ;
															} else {
																esc_html_e( 'Not Clicked', 'recoverabandoncart' ) ;
															}
														} else {
															esc_html_e( 'Not Clicked', 'recoverabandoncart' ) ;
														}
														?>
													</td>

													<?php
											}
										}
										?>
										</tr>
									</table>
								</div>
							</div>
							<div class="fp_rac_email_info_popup_footer">
								<input type="button" class="rac_email_info_popup_close_btn" value="<?php esc_html_e( 'Close', 'recoverabandoncart' ) ; ?>">
							</div>
						</div>
					</div>
				</div>
				<?php
				fp_rac_wpml_switch_lang() ;

				$popup = ob_get_clean() ;
				wp_send_json_success( array( 'content' => $popup ) ) ;
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) ) ;
			}
		}

		/**
		 * To Prepare Customized Popup Window for manual recover method.
		 */
		public static function popup_for_manual_recover_method() {

			check_ajax_referer( 'rac_manual-order-id', 'rac_security' ) ;
			ob_start() ;
			try {
				if ( ! isset( $_POST[ 'cart_list_id' ] ) ) {
					throw new exception( __( 'Invalid Arguments', 'recoverabandoncart' ) ) ;
				}

				$cart_list_id = absint( $_POST[ 'cart_list_id' ] ) ;
				?>
				<div class="fp_rac_popup_wrapper">
					<div class="fp_rac_popup_content">
						<div class="fp_rac_popup_header"><label class="rac_popup_label"> <?php esc_html_e( 'Manual Recover Form', 'recoverabandoncart' ) ; ?></label> </div>
						<div class="fp_rac_popup_close"> <img src=<?php echo esc_url( RAC_PLUGIN_URL . '/assets/images/close.png' ) ; ?> class="rac_popup_close"> </div>
						<div class="fp_rac_popup_body">
							<div class="fp_rac_popup_body_content">
								<label class="rac_manual_order_id_label"><?php esc_html_e( 'Enter Order ID', 'recoverabandoncart' ) ; ?></label>
								<input type="number" class="rac_manual_order_id_num"><br>
								<input type="hidden" class="rac_cart_list_id" value ="<?php echo esc_attr( $cart_list_id ) ; ?>"/>
							</div>
						</div>
						<div class="fp_rac_popup_footer">
							<input type="button" class="rac_manual_order_entry_btn" value="<?php esc_attr_e( 'Enter Order ID', 'recoverabandoncart' ) ; ?>">
						</div>
					</div>
				</div>
				<?php
				$popup        = ob_get_clean() ;
				wp_send_json_success( array( 'content' => $popup ) ) ;
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) ) ;
			}
			ob_end_clean() ;
			exit() ;
		}

		/**
		 * To add a recover order id to cartlist table & insert a new entry to recovered order table.
		 */
		public static function manual_recover_entry() {
			check_ajax_referer( 'rac_manual-order-id', 'rac_security' ) ;
			try {
				if ( ! isset( $_POST[ 'manual_order_id' ] ) || ! isset( $_POST[ 'cart_list_id' ] ) ) {
					throw new exception( __( 'Invalid Arguments', 'recoverabandoncart' ) ) ;
				}

				$order_id     = absint( $_POST[ 'manual_order_id' ] ) ;
				$cart_list_id = absint( $_POST[ 'cart_list_id' ] ) ;

				$order_obj = fp_rac_get_order_obj( $order_id ) ;
				if ( ! $order_obj ) {
					throw new exception( __( 'Invalid Order ID', 'recoverabandoncart' ) ) ;
				}

				$previous_order_id = get_post_meta( $cart_list_id, 'order_id', true ) ;
				if ( ! empty( $previous_order_id ) ) {
					throw new exception( __( 'Order Id Already Placed', 'recoverabandoncart' ) ) ;
				}

				$product_ids = array() ;
				$get_items   = $order_obj->get_items() ;

				if ( rac_check_is_array( $get_items ) ) {
					foreach ( $get_items as $product ) {
						$product_ids[] = $product[ 'product_id' ] ;
					}
				}
				$args = array(
					'rac_order_id'              => $order_id,
					'rac_cart_id'               => $cart_list_id,
					'rac_product_details'       => implode( ',', $product_ids ),
					'rac_recovered_sales_total' => fp_rac_get_order_obj_data( $order_obj, 'order_total' ),
					'rac_recovered_date'        => strtotime( fp_rac_get_order_obj_data( $order_obj, 'order_date' ) ),
						) ;

				fp_rac_insert_recovered_order_post( $args ) ;

				update_post_meta( $cart_list_id, 'rac_recovered_order_id', $order_id ) ;
				update_post_meta( $cart_list_id, 'rac_cart_payment_details', true ) ;

				wp_send_json_success( array( 'content' => __( 'Order ID Updated', 'recoverabandoncart' ) ) ) ;
			} catch ( Exception $e ) {
				wp_send_json_error( array( 'error' => $e->getMessage() ) ) ;
			}
			exit() ;
		}

		/**
		 * Popup time delay cookie settings.
		 */
		public static function popup_time_delay_function() {
			check_ajax_referer( 'rac_popup_delay', 'rac_security' ) ;

			setcookie( 'rac_guest_popup_clicked_time', time(), time() + 3600, '/' ) ;

			exit() ;
		}

		/**
		 * Accept/Reject the GDPR by the user in the checkout.
		 * 
		 * @return void
		 */
		public static function handle_checkout_gdpr() {
			check_ajax_referer( 'gdpr-nonce', 'rac_security' ) ;

			try {
				$gdpr_accepted = isset( $_REQUEST[ 'gdpr_accepted' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'gdpr_accepted' ] ) ) : '' ; // @codingStandardsIgnoreLine.
				if ( empty( $gdpr_accepted ) ) {
					throw new exception( esc_html__( 'Cannot complete action', 'recoverabandoncart' ) ) ;
				}

				if ( 'no' === $gdpr_accepted ) {
					$cookie_value = 'no' ;
					if ( isset( $_COOKIE[ 'rac_checkout_entry' ] ) ) {
						wp_delete_post( wc_clean( wp_unslash( $_COOKIE[ 'rac_checkout_entry' ] ) ), true ) ;
						setcookie( 'rac_checkout_entry' , null , -1 , '/' ) ;
					}
				} else {
					$cookie_value = 'yes' ;
				}

				setcookie( 'rac_gdpr', $cookie_value, time() + 3600, '/' ) ;

				wp_send_json_success() ;
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) ) ;
			}
		}

	}

	FP_RAC_Admin_Ajax::init() ;
}
