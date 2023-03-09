<?php
/*
 * Send Recovered Mail to admin after order placed by clicked link on email
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_ADMIN_Notification_Email' ) ) {

	/**
	 * FP_RAC_ADMIN_Notification_Email Class.
	 */
	class FP_RAC_ADMIN_Notification_Email {

		public static function fp_rac_mail_admin_cart_recovered( $order_id, $cart_id ) {
			if ( 'yes' !== get_option( 'rac_admin_cart_recovered_noti' ) ) {
				return ;
			}

			$to          = get_option( 'rac_admin_email' ) ;
			$to_mail_ids = explode( ',', $to ) ;
			if ( ! rac_check_is_array( $to_mail_ids ) ) {
				return ;
			}

			$cart_object = fp_rac_create_cart_list_obj( $cart_id ) ;
			if ( ! fp_rac_common_custom_restrict( $cart_object->email_id, 'admin_email' ) ) {
				return ;
			}

			$subject       = get_option( 'rac_recovered_email_subject' ) ;
			$message       = get_option( 'rac_recovered_email_message' ) ;
			$from_name     = get_option( 'rac_recovered_from_name' ) ;
			$from_email    = get_option( 'rac_recovered_from_email' ) ;
			$sender_opt    = get_option( 'rac_recovered_sender_opt' ) ;
			$compact       = array( $sender_opt, $from_name, $from_email ) ;
			$headers       = rac_format_email_headers( $compact ) ;
			$html_template = ( 'woo' == $sender_opt ) ? 'HTML' : 'PLAIN' ;
			ob_start() ;
			$order         = fp_rac_get_order_obj( $order_id ) ;
			?>
			<table cellspacing="0" cellpadding="6" class="fp-rac-admin-email-table" border="1">
				<thead>
					<tr>
						<th scope="col" class="fp-rac-admin-email-col"><?php esc_html_e( 'Product', 'woocommerce' ) ; ?></th>
						<th scope="col" class="fp-rac-admin-email-col"><?php esc_html_e( 'Quantity', 'woocommerce' ) ; ?></th>
						<th scope="col" class="fp-rac-admin-email-col"><?php esc_html_e( 'Price', 'woocommerce' ) ; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php echo do_shortcode( fp_rac_get_email_order_item_table( $order ) ) ; ?>
				</tbody>
				<tfoot>
					<?php
					$totals        = $order->get_order_item_totals() ;
					if ( $totals ) {
						$i = 0 ;
						foreach ( $totals as $total ) {
							$i ++ ;
							$class_names = array( 'fp-rac-admin-email-col' ) ;
							if ( 1 == $i ) {
								$class_names[] = 'fp-rac-admin-email-col-value' ;
							}
							?>
							<tr>
								<th scope="row" colspan="2" class="<?php echo esc_attr( implode( ' ', $class_names ) ) ; ?>"><?php echo esc_html( $total[ 'label' ] ) ; ?></th>
								<td class="<?php echo esc_attr( implode( ' ', $class_names ) ) ; ?>"><?php echo wp_kses_post( $total[ 'value' ] ) ; ?></td>
							</tr>
							<?php
						}
					}
					?>
				</tfoot>
			</table>

			<?php
			$newdata = ob_get_clean() ;

			ob_start() ;
			$find_array    = array( '{rac.recovered_order_id}', '{rac.order_line_items}', '{rac.firstname}', '{rac.lastname}' ) ;
			$replace_array = array( $order_id, $newdata, $order->billing_first_name, $order->billing_last_name ) ;
			$message       = str_replace( $find_array, $replace_array, $message ) ;
			$woo_temp_msg  = rac_email_woocommerce_html( $html_template, $subject, $message ) ;
			$woo_temp_msg  = rac_email_inline_style( $woo_temp_msg, self::get_custom_css() ) ;

			foreach ( $to_mail_ids as $to ) {
				if ( ! fp_rac_check_email_subscribed( $to ) ) {
					continue ;
				}

				rac_send_mail( $to, $subject, $woo_temp_msg, $headers, $html_template, $compact ) ; //send email
			}
		}

		public static function get_custom_css() {
			$custom_css = fp_rac_get_template_html( 'email-abandoned-cart-css.php' ) ;
			$custom_css .= '.fp-rac-admin-email-table{
                width: 100%; 
                border: 1px solid #eee;
            }
            .fp-rac-admin-email-col{
            text-align:left; 
            border: 1px solid #eee;
            }
            .fp-rac-admin-email-col-value{
            border-top-width: 4px;
            }
            ' ;

			return $custom_css ;
		}

		public static function fp_rac_mail_admin_cart_abandoned( $cart_id ) {
			if ( 'yes' !== get_option( 'rac_admin_cart_abandoned_noti' ) ) {
				return ;
			}

			if ( 'yes' === get_post_meta( $cart_id, 'fp_rac_admin_notification_sent_for_cart_abandoned', true ) ) {
				return ;
			}

			$to          = get_option( 'rac_ca_admin_email' ) ;
			$to_mail_ids = explode( ',', $to ) ;
			if ( ! rac_check_is_array( $to_mail_ids ) ) {
				return ;
			}

			$cart_object = fp_rac_create_cart_list_obj( $cart_id ) ;
			if ( ! fp_rac_common_custom_restrict( $cart_object->email_id, 'admin_email' ) ) {
				return ;
			}

			$tablecheckproduct = FP_RAC_Polish_Product_Info::fp_rac_extract_cart_details( $cart_object, true ) ;
			$user_name         = self::fp_rac_display_cart_list_user_name( $cart_object ) ;
			$user_email        = self::fp_rac_display_cart_list_user_name( $cart_object, true ) ;

			$subject       = get_option( 'rac_abandoned_email_subject' ) ;
			$message       = get_option( 'rac_abandoned_email_message' ) ;
			$from_name     = get_option( 'rac_abandoned_from_name' ) ;
			$from_email    = get_option( 'rac_abandoned_from_email' ) ;
			$sender_opt    = get_option( 'rac_abandoned_sender_opt' ) ;
			$compact       = array( $sender_opt, $from_name, $from_email ) ;
			$headers       = rac_format_email_headers( $compact ) ;
			$html_template = ( 'woo' == $sender_opt ) ? 'HTML' : 'PLAIN' ;
			$first_name    = '' ;
			$last_name     = '' ;
			$user_info     = get_userdata( $cart_object->user_id ) ;

			if ( is_object( $user_info ) ) {
				$first_name = $user_info->user_firstname ;
				$last_name  = $user_info->user_lastname ;
			} elseif ( '0' == $cart_object->user_id ) {
				$cart_array = fp_rac_format_cart_details( $cart_object->cart_details, $cart_object ) ;

				if ( is_array( $cart_array ) ) {
					//for cart captured at checkout(GUEST)
					$first_name = $cart_array[ 'first_name' ] ;
					$last_name  = $cart_array[ 'last_name' ] ;
				} elseif ( is_object( $cart_array ) ) { // For Guest
					$first_name = fp_rac_get_order_obj_data( $cart_array, 'billing_first_name' ) ;
					$last_name  = fp_rac_get_order_obj_data( $cart_array, 'billing_last_name' ) ;
				}
			} elseif ( 'old_order' == $cart_object->user_id ) {
				$old_order_obj = new FP_RAC_Previous_Order_Data( $cart_object ) ;

				if ( $old_order_obj->get_cart_content() ) {
					$user_id  = $old_order_obj->get_user_id() ;
					$user_obj = get_userdata( $user_id ) ;

					if ( is_object( $user_obj ) ) {
						$first_name = $user_obj->user_firstname ;
						$last_name  = $user_obj->user_lastname ;
					} else {
						$first_name = $old_order_obj->get_billing_firstname() ;
						$last_name  = $old_order_obj->get_billing_lastname() ;
					}
				}
			}

			$find_array    = array( '{rac.abandoned_cart}', '{rac.abandoned_username}', '{rac.abandoned_useremail}', '{rac.abandoned_user_phone_number}', '{rac.firstname}', '{rac.lastname}' ) ;
			$replace_array = array( $tablecheckproduct, $user_name, $user_email, $cart_object->phone_number, $first_name, $last_name ) ;
			$message       = str_replace( $find_array, $replace_array, $message ) ;
			$woo_temp_msg  = rac_email_woocommerce_html( $html_template, $subject, $message ) ;

			$custom_css   = fp_rac_get_template_html( 'email-abandoned-cart-css.php' ) ;
			$woo_temp_msg = rac_email_inline_style( $woo_temp_msg, $custom_css ) ;

			foreach ( $to_mail_ids as $to ) {
				if ( ! fp_rac_check_email_subscribed( $to ) ) {
					continue ;
				}

				rac_send_mail( $to, $subject, $woo_temp_msg, $headers, $html_template, $compact ) ; //send email

				update_post_meta( $cart_id, 'fp_rac_admin_notification_sent_for_cart_abandoned', 'yes' ) ;
			}
		}

		public static function fp_rac_display_cart_list_user_name( $each_list, $email = false ) {
			$user_info = get_userdata( $each_list->user_id ) ;
			$user_name = '' ;
			if ( is_object( $user_info ) ) {
				$user_name = $email ? $user_info->user_email : $user_info->user_login ;
			} elseif ( '0' == $each_list->user_id ) {
				$cart_array = fp_rac_format_cart_details( $each_list->cart_details, $each_list ) ;
				if ( is_array( $cart_array ) ) {
					//for cart captured at checkout(GUEST)
					$first_name       = $cart_array[ 'first_name' ] ;
					$last_name        = $cart_array[ 'last_name' ] ;
					$guest_first_last = $first_name . ' ' . $last_name ;

					unset( $cart_array[ 'visitor_mail' ] ) ;
					unset( $cart_array[ 'first_name' ] ) ;
					unset( $cart_array[ 'last_name' ] ) ;
					if ( isset( $cart_array[ 'visitor_phone' ] ) ) {
						unset( $cart_array[ 'visitor_phone' ] ) ;
					}
					if ( isset( $cart_array[ 'shipping_details' ] ) ) {
						unset( $cart_array[ 'shipping_details' ] ) ;
					}
					if ( isset( $cart_array[ 'woocs_is_multipled' ] ) ) {
						unset( $cart_array[ 'woocs_is_multipled' ] ) ;
					}
				} elseif ( is_object( $cart_array ) ) { // For Guest
					$guest_first_last = $cart_array->billing_first_name . ' ' . $cart_array->billing_last_name ;
				}
				$user_name = $guest_first_last ;
				$user_name = str_replace( ' ', '', $user_name ) ;
				if ( ! $user_name || $email ) {
					$details = fp_rac_format_cart_details( $each_list->cart_details, $each_list ) ;
					if ( is_object( $details ) ) {
						$user_name = $details->billing_email ;
					} elseif ( is_array( $details ) ) {
						$user_name = $details[ 'visitor_mail' ] ;
					}
				}
			}
			return $user_name ;
		}

	}

}
