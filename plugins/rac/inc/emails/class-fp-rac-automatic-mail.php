<?php

/**
 *  Send Mail Automatically by Cron Job
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'FP_RAC_Automatic_Email' ) ) {

	/**
	 *  FP_RAC_Automatic_Email Class.
	 *
	 */
	class FP_RAC_Automatic_Email {

		/**
		 *  Prepare mail to automatic send.
		 *
		 */
		public static function fp_rac_cron_job_mailing() {
			include RAC_PLUGIN_PATH . '/inc/class-fp-rac-segmentation.php' ;
			$email_templates = self::fp_rac_get_email_templates() ;
			if ( rac_check_is_array( $email_templates ) ) {
				// For Members
				if ( 'yes' == get_option( 'rac_email_use_members' ) ) {
					$find_user     = 'member' ;
					$abandon_carts = self::fp_rac_get_cart_list_ids( $find_user ) ;
					if ( rac_check_is_array( $abandon_carts ) ) {
						foreach ( $abandon_carts as $cart_id ) {
							$each_cart = fp_rac_create_cart_list_obj( $cart_id ) ;
							foreach ( $email_templates as $email_template_id ) {
								$emails = fp_rac_create_email_template_obj( $email_template_id ) ;
								if ( FP_RAC_Segmentations::check_send_mail_based_on( $each_cart, $emails, $each_cart->user_id, $each_cart->email_id ) ) {
									self::send_mail_by_mail_sending_option( $each_cart, $emails, $find_user ) ;
								}
							}
						}
					}
				}
				// FOR GUEST
				if ( 'yes' == get_option( 'rac_email_use_guests' ) ) {
					$find_user     = 'guest1' ;
					$abandon_carts = self::fp_rac_get_cart_list_ids( $find_user ) ;
					if ( rac_check_is_array( $abandon_carts ) ) {
						foreach ( $abandon_carts as $cart_id ) {
							$each_cart = fp_rac_create_cart_list_obj( $cart_id ) ;
							foreach ( $email_templates as $email_template_id ) {
								$emails = fp_rac_create_email_template_obj( $email_template_id ) ;
								if ( FP_RAC_Segmentations::check_send_mail_based_on( $each_cart, $emails, $each_cart->user_id, $each_cart->email_id ) ) {
									self::send_mail_by_mail_sending_option( $each_cart, $emails, $find_user ) ;
								}
							}
						}
					}
					//FOR Guest Captured in chcekout page
					$find_user     = 'guest2' ;
					$abandon_carts = self::fp_rac_get_cart_list_ids( $find_user ) ;
					if ( rac_check_is_array( $abandon_carts ) ) {
						foreach ( $abandon_carts as $cart_id ) {
							$each_cart = fp_rac_create_cart_list_obj( $cart_id ) ;
							foreach ( $email_templates as $email_template_id ) {
								$emails     = fp_rac_create_email_template_obj( $email_template_id ) ;
								$cart_array = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
								if ( FP_RAC_Segmentations::check_send_mail_based_on( $each_cart, $emails, $each_cart->user_id, $each_cart->email_id ) ) {
									self::send_mail_by_mail_sending_option( $each_cart, $emails, $find_user ) ;
								}
							}
						}
					}
				}
				// FOR ORDER UPDATED FROM OLD
				$find_user     = 'old_order' ;
				$abandon_carts = self::fp_rac_get_cart_list_ids( $find_user ) ;
				if ( rac_check_is_array( $abandon_carts ) ) {
					foreach ( $abandon_carts as $cart_id ) {
						$each_cart = fp_rac_create_cart_list_obj( $cart_id ) ;
						foreach ( $email_templates as $email_template_id ) {
							$emails        = fp_rac_create_email_template_obj( $email_template_id ) ;
							$old_order_obj = new FP_RAC_Previous_Order_Data( $each_cart ) ;
							if ( $old_order_obj->get_cart_content() ) {
								$main_check = '0' ;
								$user_id    = $old_order_obj->get_user_id() ;
								$email_id   = $old_order_obj->get_billing_email() ;
								if ( '' != $user_id ) {
									if ( 'yes' == get_option( 'rac_email_use_members' ) ) {
										// For Controlling Email Id for Member/Guest
										$main_check = '1' ;
									}
								} else {
									if ( 'yes' == get_option( 'rac_email_use_guests' ) ) {
										$main_check = '1' ;
									}
								}
								if ( '0' != $main_check ) {
									if ( FP_RAC_Segmentations::check_send_mail_based_on( $each_cart, $emails, $user_id, $email_id ) ) {
										self::send_mail_by_mail_sending_option( $each_cart, $emails, $find_user ) ;
									}
								}
							}
						}
					}
				}
			}
		}

		/**
		 *  Send Email For Each template.
		 *
		 */
		public static function send_mail_by_mail_sending_option( $each_cart, $email_template, $find_user ) {
			global $to, $fp_rac_lang ;
			$fp_rac_lang              = $each_cart->wpml_lang ;
			$current_time             = current_time( 'timestamp' ) ;
			$sent_mail_template       = get_post_meta( $each_cart->id, 'rac_cart_email_template_id', true ) ;
			$sent_mail_templates      = maybe_unserialize( $sent_mail_template ) ;
			$sent_mail_template_time  = get_post_meta( $each_cart->id, 'rac_cart_email_template_sending_time', true ) ;
			$store_sending_time       = maybe_unserialize( $sent_mail_template_time ) ;
			$send_mail_template_check = self::fp_rac_check_send_mail_template_check( $sent_mail_templates, $store_sending_time, $email_template, $current_time, $each_cart ) ;
			if ( ! in_array( $email_template->old_id, ( array ) $sent_mail_templates ) ) {
				fp_rac_wpml_switch_lang( $fp_rac_lang ) ;
				if ( $send_mail_template_check ) {
					$cart_url = rac_get_page_permalink_dependencies( 'cart' ) ;
					if ( 'member' == $find_user ) {
						$urltoclick = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $email_template->id ), $cart_url ) ) ;
						$user_id    = $each_cart->user_id ;
						$user       = get_userdata( $user_id ) ;
						$to         = $user->user_email ;
						$firstname  = $user->user_firstname ;
						$lastname   = $user->user_lastname ;
					} elseif ( 'guest1' == $find_user ) {
						$urltoclick   = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $email_template->id, 'guest' => 'yes' ), $cart_url ) ) ;
						$user_id      = 0 ;
						@$order_object = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
						$to           = $order_object->billing_email ;
						$firstname    = $order_object->billing_first_name ;
						$lastname     = $order_object->billing_last_name ;
					} elseif ( 'guest2' == $find_user ) {
						$urltoclick   = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $email_template->id, 'guest' => 'yes' ), $cart_url ) ) ;
						@$order_object = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
						$user_id      = 0 ;
						$to           = $order_object[ 'visitor_mail' ] ;
						$firstname    = $order_object[ 'first_name' ] ;
						$lastname     = $order_object[ 'last_name' ] ;
					} elseif ( 'old_order' == $find_user ) {
						$urltoclick    = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $email_template->id, 'old_order' => 'yes' ), $cart_url ) ) ;
						$old_order_obj = new FP_RAC_Previous_Order_Data( $each_cart ) ;
						$user_id       = $old_order_obj->get_user_id() ;
						$to            = $old_order_obj->get_billing_email() ;
						$firstname     = $old_order_obj->get_billing_firstname() ;
						$lastname      = $old_order_obj->get_billing_lastname() ;
					}

					if ( fp_rac_common_custom_restrict( $to, 'email' ) && fp_rac_check_email_subscribed( $to ) && fp_rac_check_user_already_bought( $to, $user_id, $each_cart ) ) {
						/*
						 * start create message for email.
						 */
						$message = fp_get_wpml_text( 'rac_template_' . $email_template->old_id . '_message', $each_cart->wpml_lang, $email_template->message ) ;
						$message = stripslashes( $message ) ; //remove backslashes when data retrieved from a database or from an HTML form.
						$message = wpautop( $message ) ; //add HTML P tag on message for Email to create Empty Sapce.

						if ( strpos( $message, '{rac.coupon}' ) ) {
							require_once RAC_PLUGIN_PATH . '/inc/fp-rac-coupon.php' ;
							$coupon_details = FP_RAC_Coupon::get_coupon_details( $to, $each_cart->cart_abandon_time, $email_template->id, $email_template->coupon, $email_template->coupon_mode ) ;
							update_post_meta( $each_cart->id, 'rac_cart_coupon_code', $coupon_details[ 'code' ] ) ;

							$message = str_replace( '{rac.coupon}', $coupon_details[ 'code' ], $message ) ; //replacing shortcode with coupon code
							$message = str_replace( '{rac.coupon_expired_date}', $coupon_details[ 'expiry_date' ], $message ) ; //replacing shortcode with coupon expiry date

							$urltoclick = esc_url_raw( add_query_arg( array( 'auto_apply_coupon' => 'yes' ), $urltoclick ) ) ;
						}

						$url_to_click = apply_filters( 'fp_rac_redirect_url', $urltoclick, $each_cart->id, $each_cart ) ;
						$url_to_click = fp_rac_wpml_convert_url( $url_to_click, $each_cart->wpml_lang ) ;
						$link_options = get_option( 'rac_cart_link_options' ) ;
						if ( '1' == $link_options ) {
							$url_to_click = '<a class="fp-rac-email-cart-link" href = "' . $url_to_click . '">' . fp_get_wpml_text( 'rac_template_' . $email_template->old_id . '_anchor_text', $each_cart->wpml_lang, $email_template->anchor_text ) . '</a>' ;
						} elseif ( '2' == $link_options ) {
							$url_to_click = $url_to_click ;
						} elseif ( '3' == $link_options ) {
							$cart_Text    = fp_get_wpml_text( 'rac_template_' . $email_template->old_id . '_anchor_text', $each_cart->wpml_lang, $email_template->anchor_text ) ;
							$url_to_click = rac_cart_link_button_mode( $url_to_click, $cart_Text ) ;
						} else {
							$cart_Text    = fp_get_wpml_text( 'rac_template_' . $email_template->old_id . '_anchor_text', $each_cart->wpml_lang, $email_template->anchor_text ) ;
							$url_to_click = rac_cart_link_image_mode( $url_to_click, $cart_Text ) ;
						}

						$date              = date_i18n( rac_date_format(), $each_cart->cart_abandon_time ) ;
						$time              = date_i18n( rac_time_format(), $each_cart->cart_abandon_time ) ;
						$unsublink         = fp_rac_get_unsubscribe( $to, $fp_rac_lang ) ;
						$tablecheckproduct = FP_RAC_Polish_Product_Info::fp_rac_extract_cart_details( $each_cart, true ) ;

						$find_array    = array( '{rac.cartlink}', '{rac.date}', '{rac.time}', '{rac.firstname}', '{rac.lastname}', '{rac.Productinfo}', '{rac_unsubscribe}' ) ;
						$find_array    = apply_filters( 'rac_find_automatic_email_shortcodes_array', $find_array ) ;
						$replace_array = array( $url_to_click, $date, $time, $firstname, $lastname, $tablecheckproduct, $unsublink ) ;
						$replace_array = apply_filters( 'rac_replace_automatic_email_shortcodes_array', $replace_array ) ;

						$message = str_replace( $find_array, $replace_array, $message ) ;
						$message = rac_shortcode_in_subject( $firstname, $lastname, $message, $each_cart ) ; // added shortcode replacing from subject content to message content

						$message = rac_unsubscription_shortcode( $to, $message, $each_cart->wpml_lang ) ;
						add_filter( 'woocommerce_email_footer_text', 'rac_footer_email_customization' ) ;
						$message = do_shortcode( $message ) ; //shortcode feature
						/*
						 * End create message for email.
						 */
						if ( '' == $email_template->link ) {
							$logo = '' ;
						} else {
							$logo = '<table><tr><td align = "center" valign = "top"><p class="fp-rac-email-logo-wrapper"><img class="fp-rac-email-logo" src = "' . esc_url( $email_template->link ) . '" /></p></td></tr></table>' ; // mail uploaded
						}

						$html_template = $email_template->mail ; // mail send plain or html
						$subject       = fp_get_wpml_text( 'rac_template_' . $email_template->old_id . '_subject', $each_cart->wpml_lang, $email_template->subject ) ;
						$subject       = rac_shortcode_in_subject( $firstname, $lastname, $subject, $each_cart ) ;
						$woo_temp_msg  = rac_email_woocommerce_html( $html_template, $subject, $message, $logo ) ; // mail send plain or html
						$compact       = array( $email_template->sender_opt, $email_template->from_name, $email_template->from_email, $each_cart->wpml_lang ) ;
						$headers       = rac_format_email_headers( $compact, $email_template->rac_blind_carbon_copy ) ;
						$custom_css    = $email_template->custom_css . fp_rac_get_template_html( 'email-abandoned-cart-css.php' ) ;
						$woo_temp_msg  = rac_email_inline_style( $woo_temp_msg, $custom_css ) ;

						do_action( 'rac_before_send_abandoned_cart_email', $woo_temp_msg, $each_cart, $email_template->id ) ;

						if ( rac_send_mail( $to, $subject, $woo_temp_msg, $headers, $html_template, $compact ) ) {
							$sent_mail_templates                           = is_array( $sent_mail_templates ) ? $sent_mail_templates : ( array ) $sent_mail_templates ;
							$sent_mail_templates[]                         = $email_template->old_id ;
							$store_sending_time                            = is_array( $store_sending_time ) ? $store_sending_time : ( array ) $store_sending_time ;
							$store_sending_time[ $email_template->old_id ] = $current_time ;
							$serialize_sending_time                        = maybe_serialize( array_filter( $store_sending_time ) ) ;
							$store_template_id                             = maybe_serialize( array_filter( $sent_mail_templates ) ) ;
							update_post_meta( $each_cart->id, 'rac_cart_email_template_id', $store_template_id ) ;
							update_post_meta( $each_cart->id, 'rac_cart_email_template_sending_time', $serialize_sending_time ) ;
							$args                                          = array(
								'rac_email_id'      => $to,
								'rac_date_time'     => $current_time,
								'rac_template_used' => $email_template->id,
								'rac_cart_id'       => $each_cart->id,
									) ;
							//insert emaillog post
							fp_rac_insert_emaillog_post( $args ) ;
							FP_RAC_Counter::rac_do_mail_count() ;
							FP_RAC_Counter::email_count_by_template( $email_template->id ) ;
						}
					}
				}
				fp_rac_wpml_switch_lang() ;
			}
		}

		/**
		 *  Check email template already send and cross current time.
		 *
		 */
		public static function fp_rac_check_send_mail_template_check( $sent_mail_template, $store_sending_time, $email_template, $current_time, $cart ) {
			$duration = self::sending_duration( $email_template ) ;
			//check for sending time duration and duplicate template id
			if ( empty( $sent_mail_template ) ) { // IF EMPTY IT IS NOT SENT FOR ANY SINGLE TEMPLATE
				$cut_off_time = $cart->cart_abandon_time + $duration ;
			} elseif ( ! empty( $sent_mail_template ) ) {// IF EMPTY IT IS NOT SENT FOR ANY SINGLE TEMPLATE END
				if ( 'template_time' == get_option( 'rac_mail_template_send_method' ) ) {
					if ( empty( $store_sending_time ) ) {
						$cut_off_time = $cart->cart_abandon_time + $duration ;
					} else {
						$cut_off_time = end( $store_sending_time ) + $duration ;
					}
				} else {
					$cut_off_time = $cart->cart_abandon_time + $duration ;
				}
			}//end

			if ( $current_time > $cut_off_time ) {
				return true ;
			} else {
				return false ;
			}
		}

		/**
		 *  Cart list sending duration
		 *
		 */
		public static function sending_duration( $emails ) {
			if ( 'hours' == $emails->sending_type ) {
				$duration = $emails->sending_duration * 3600 ;
			} else if ( 'minutes' == $emails->sending_type ) {
				$duration = $emails->sending_duration * 60 ;
			} else if ( 'days' == $emails->sending_type ) {
				$duration = $emails->sending_duration * 86400 ;
			}//duration is finished
			return $duration ;
		}

		/**
		 *  Get Email Templates
		 *
		 */
		public static function fp_rac_get_email_templates() {
			if ( 'template_time' == get_option( 'rac_mail_template_send_method' ) ) {
				if ( 'mailsequence' != get_option( 'rac_mail_template_sending_priority' ) ) {
					$args = array(
						'posts_per_page' => -1,
						'post_type'      => 'racemailtemplate',
						'post_status'    => 'racactive',
						'meta_query'     => array(
							'relation'                      => 'AND',
							'rac_template_sending_type'     => array(
								'key'     => 'rac_template_sending_type',
								'compare' => 'EXISTS',
							),
							'rac_template_sending_duration' => array(
								'key'     => 'rac_template_sending_duration',
								'compare' => 'EXISTS',
							),
						),
						'orderby'        => array(
							'rac_template_sending_type'     => 'DESC',
							'rac_template_sending_duration' => 'ASC',
						),
						'fields'         => 'ids'
							) ;
				} else {
					$args = array(
						'posts_per_page' => -1,
						'post_type'      => 'racemailtemplate',
						'post_status'    => 'racactive',
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'fields'         => 'ids'
							) ;
				}
			} else {
				$args = array(
					'posts_per_page' => -1,
					'post_type'      => 'racemailtemplate',
					'post_status'    => 'racactive',
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids'
						) ;
			}

			$email_template_ids = fp_rac_check_query_having_posts( $args ) ;
			return $email_template_ids ;
		}

		public static function fp_rac_get_cart_list_ids( $type ) {
			$ipaddress = 'guest2' == $type ? 'EXISTS' : 'NOT EXISTS' ;
			if ( 'member' == $type ) {
				$guest    = '!=' ;
				$oldorder = '!=' ;
			} elseif ( 'old_order' == $type ) {
				$guest    = '!=' ;
				$oldorder = '=' ;
			} else {
				$guest    = '=' ;
				$oldorder = '!=' ;
			}
			$args       = array(
				'posts_per_page' => -1,
				'post_type'      => 'raccartlist',
				'post_status'    => array( 'rac-cart-abandon' ),
				'meta_query'     => array(
					'relation'                  => 'AND',
					'rac_guest_user_details'    => array(
						'key'     => 'rac_user_details',
						'value'   => '0',
						'compare' => $guest,
					),
					'rac_oldorder_user_details' => array(
						'key'     => 'rac_user_details',
						'value'   => 'old_order',
						'compare' => $oldorder,
					),
					'rac_cart_sending_status'   => array(
						'key'   => 'rac_cart_sending_status',
						'value' => 'SEND',
					),
					'rac_recovered_order_id'    => array(
						'key'     => 'rac_recovered_order_id',
						'compare' => 'NOT EXISTS',
					),
					'rac_cart_payment_details'  => array(
						'key'     => 'rac_cart_payment_details',
						'compare' => 'NOT EXISTS',
					),
					'rac_cart_ip_address'       => array(
						'key'     => 'rac_cart_ip_address',
						'compare' => $ipaddress,
					),
				),
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC'
					) ;
			$last_carts = fp_rac_check_query_having_posts( $args ) ;
			return $last_carts ;
		}

	}

}
