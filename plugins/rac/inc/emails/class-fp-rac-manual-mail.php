<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Manual_Mail' ) ) {

	/**
	 * FP_RAC_Manual_Mail Class.
	 */
	class FP_RAC_Manual_Mail {

		public static function init() {
			add_action( 'wp_ajax_rac_manual_mail_ajax', array( __CLASS__, 'rac_send_manual_mail' ) ) ;
		}

		public static function rac_send_manual_mail() {

			check_ajax_referer( 'manual-send-email-template', 'rac_security' ) ;

			global $woocommerce, $to, $fp_rac_lang ;

			$mail_template_post    = isset( $_POST[ 'rac_template_mail' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_mail' ] ) ) : '' ;  // mail plain or html
			$mail_logo_added       = isset( $_POST[ 'rac_template_link' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_link' ] ) ) : '' ;  // mail logo uploaded
			$sender_option_post    = isset( $_POST[ 'rac_template_sender_opt' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_sender_opt' ] ) ) : '' ;
			$from_name_post        = isset( $_POST[ 'rac_template_from_name' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_from_name' ] ) ) : '' ;
			$from_email_post       = isset( $_POST[ 'rac_template_from_email' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_from_email' ] ) ) : '' ;
			$bcc_post              = isset( $_POST[ 'rac_template_blind_carbon_copy' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_blind_carbon_copy' ] ) ) : '' ;
			$subject_post          = isset( $_POST[ 'rac_template_subject' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_subject' ] ) ) : '' ;
			$anchor_text_post      = isset( $_POST[ 'rac_template_anchor_text' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_anchor_text' ] ) ) : '' ;
			$custom_css_post       = isset( $_POST[ 'rac_template_custom_css' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_custom_css' ] ) ) : '' ;
			$message_post          = isset( $_POST[ 'rac_content' ] ) ? wp_unslash( $_POST[ 'rac_content' ] ) : '' ; //remove backslashes when data retrieved from a database or from an HTML form.
			$message_post          = wpautop( $message_post ) ; //add HTML P tag on message for Email to create Empty Sapce.
			$post_array_ids        = isset( $_POST[ 'rac_email_row_ids' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_email_row_ids' ] ) ) : '' ;
			$template_coupon       = isset( $_POST[ 'rac_template_coupon' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_coupon' ] ) ) : '' ;
			$template_coupon_mode  = isset( $_POST[ 'rac_template_coupon_mode' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_template_coupon_mode' ] ) ) : '' ;
			$row_id_array          = array_filter( explode( ',', $post_array_ids ) ) ;
			$mail_template_id_post = isset( $_POST[ 'post_ID' ] ) ? wc_clean( wp_unslash( ( $_POST[ 'post_ID' ] ) ) ) : '' ;
			if ( rac_check_is_array( $row_id_array ) ) {
				foreach ( $row_id_array as $row_id ) {
					$each_cart             = fp_rac_create_cart_list_obj( $row_id ) ;
					$email_old_template_id = get_post_meta( $mail_template_id_post, 'rac_old_template_id', true ) ;
					$fp_rac_lang           = $each_cart->wpml_lang ;
					fp_rac_wpml_switch_lang( $fp_rac_lang ) ;
					//For Member
					if ( is_object( $each_cart ) ) {
						$cart_array = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
						$cart_url   = rac_get_page_permalink_dependencies( 'cart' ) ;
						if ( '0' != $each_cart->user_id && 'old_order' != $each_cart->user_id ) {
							$urltoclick = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $mail_template_id_post ), $cart_url ) ) ;
							$user       = get_userdata( $each_cart->user_id ) ;
							$to         = $user->user_email ;
							$firstname  = $user->user_firstname ;
							$lastname   = $user->user_lastname ;
						} elseif ( '0' == $each_cart->user_id && empty( $each_cart->ip_address ) ) {
							$urltoclick   = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $mail_template_id_post, 'guest' => 'yes' ), $cart_url ) ) ;
							$order_object = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
							$to           = $order_object->billing_email ;
							$firstname    = $order_object->billing_firstname ;
							$lastname     = $order_object->billing_lastname ;
						} elseif ( '0' == $each_cart->user_id && ! empty( $each_cart->ip_address ) ) {
							$urltoclick   = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $mail_template_id_post, 'guest' => 'yes' ), $cart_url ) ) ;
							$order_object = fp_rac_format_cart_details( $each_cart->cart_details, $each_cart ) ;
							$to           = $order_object[ 'visitor_mail' ] ;
							$firstname    = $order_object[ 'first_name' ] ;
							$lastname     = $order_object[ 'last_name' ] ;
						} elseif ( 'old_order' == $each_cart->user_id && empty( $each_cart->ip_address ) ) {
							$urltoclick    = esc_url_raw( add_query_arg( array( 'abandon_cart' => $each_cart->id, 'email_template' => $mail_template_id_post, 'old_order' => 'yes' ), $cart_url ) ) ;
							$old_order_obj = new FP_RAC_Previous_Order_Data( $each_cart ) ;
							$to            = $old_order_obj->get_billing_email() ;
							$firstname     = $old_order_obj->get_billing_firstname() ;
							$lastname      = $old_order_obj->get_billing_lastname() ;
						}

						$check_email_restrict = fp_rac_common_custom_restrict( $to, 'email' ) ;
						$check_unsub_restrict = fp_rac_check_email_subscribed( $to ) ;

						if ( $check_email_restrict && $check_unsub_restrict ) {
							$sent_mail_templates = maybe_unserialize( $each_cart->mail_template_id ) ;
							if ( ! is_array( $sent_mail_templates ) ) {
								$sent_mail_templates = array() ; // to avoid mail sent/not sent problem for serialization on store
							}
							/*
							 * Start create message for email.
							 *
							 */
							$message = fp_get_wpml_text( 'rac_template_' . $email_old_template_id . '_message', $each_cart->wpml_lang, $message_post ) ;

							if ( strpos( $message, '{rac.coupon}' ) ) {
								require_once RAC_PLUGIN_PATH . '/inc/fp-rac-coupon.php' ;
								$coupon_details = FP_RAC_Coupon::get_coupon_details( $to, $each_cart->cart_abandon_time, $mail_template_id_post, $template_coupon, $template_coupon_mode ) ;
								update_post_meta( $each_cart->id, 'rac_cart_coupon_code', $coupon_details[ 'code' ] ) ;

								$message = str_replace( '{rac.coupon}', $coupon_details[ 'code' ], $message ) ; //replacing shortcode with coupon code
								$message = str_replace( '{rac.coupon_expired_date}', $coupon_details[ 'expiry_date' ], $message ) ; //replacing shortcode with coupon expiry date

								$urltoclick = esc_url_raw( add_query_arg( array( 'auto_apply_coupon' => 'yes' ), $urltoclick ) ) ;
							}

							$url_to_click = apply_filters( 'fp_rac_redirect_url', $urltoclick, $each_cart->id, $each_cart ) ;
							$url_to_click = fp_rac_wpml_convert_url( $url_to_click, $each_cart->wpml_lang ) ;
							$link_options = get_option( 'rac_cart_link_options' ) ;
							if ( '1' == $link_options ) {
								$url_to_click = '<a class="fp-rac-email-cart-link"  href="' . $url_to_click . '">' . fp_get_wpml_text( 'rac_template_' . $email_old_template_id . '_anchor_text', $each_cart->wpml_lang, $anchor_text_post ) . '</a>' ;
							} elseif ( '2' == $link_options ) {
								$url_to_click = $url_to_click ;
							} elseif ( '3' == $link_options ) {
								$cart_Text    = fp_get_wpml_text( 'rac_template_' . $email_old_template_id . '_anchor_text', $each_cart->wpml_lang, $anchor_text_post ) ;
								$url_to_click = rac_cart_link_button_mode( $url_to_click, $cart_Text ) ;
							} else {
								$cart_Text    = fp_get_wpml_text( 'rac_template_' . $email_old_template_id . '_anchor_text', $each_cart->wpml_lang, $anchor_text_post ) ;
								$url_to_click = rac_cart_link_image_mode( $url_to_click, $cart_Text ) ;
							}

							$unsublink         = fp_rac_get_unsubscribe( $to, $fp_rac_lang ) ;
							$date              = date_i18n( rac_date_format(), $each_cart->cart_abandon_time ) ;
							$time              = date_i18n( rac_time_format(), $each_cart->cart_abandon_time ) ;
							$tablecheckproduct = FP_RAC_Polish_Product_Info::fp_rac_extract_cart_details( $each_cart, true ) ;

							$find_array    = array( '{rac.cartlink}', '{rac.date}', '{rac.time}', '{rac.firstname}', '{rac.lastname}', '{rac.Productinfo}', '{rac_unsubscribe}' ) ;
							$find_array    = apply_filters( 'rac_find_manual_email_shortcodes_array', $find_array ) ;
							$replace_array = array( $url_to_click, $date, $time, $firstname, $lastname, $tablecheckproduct, $unsublink ) ;
							$replace_array = apply_filters( 'rac_replace_manual_email_shortcodes_array', $replace_array ) ;

							$message      = str_replace( $find_array, $replace_array, $message ) ;
							$message      = rac_shortcode_in_subject( $firstname, $lastname, $message, $each_cart ) ; // added shortcode replacing from subject content to message content
							$message      = rac_unsubscription_shortcode( $to, $message, $each_cart->wpml_lang ) ;
							add_filter( 'woocommerce_email_footer_text', 'rac_footer_email_customization' ) ;
							$message      = do_shortcode( $message ) ; //shortcode feature
							/*
							 * End create message for email.
							 *
							 */
							$current_time = current_time( 'timestamp' ) ;
							if ( '' == $mail_logo_added ) {
								$logo = '' ;
							} else {
								$logo = '<table><tr><td align="center" valign="top"><p class="fp-rac-email-logo-wrapper"><img class="fp-rac-email-logo" src="' . esc_url( $mail_logo_added ) . '" /></p></td></tr></table>' ; // mail uploaded
							}
							$subject      = fp_get_wpml_text( 'rac_template_' . $email_old_template_id . '_subject', $each_cart->wpml_lang, $subject_post ) ;
							$subject      = rac_shortcode_in_subject( $firstname, $lastname, $subject, $each_cart ) ;
							// mail send plain or html
							$woo_temp_msg = rac_email_woocommerce_html( $mail_template_post, $subject, $message, $logo ) ;
							// mail send plain or html
							$compact      = array( $sender_option_post, $from_name_post, $from_email_post,$each_cart->wpml_lang ) ;
							$headers      = rac_format_email_headers( $compact, $bcc_post ) ;

							$custom_css_post .= fp_rac_get_template_html( 'email-abandoned-cart-css.php' ) ;
							$woo_temp_msg    = rac_email_inline_style( $woo_temp_msg, $custom_css_post ) ;

							do_action( 'rac_before_send_abandoned_cart_email', $woo_temp_msg, $each_cart, $mail_template_id_post ) ;

							if ( rac_send_mail( $to, $subject, $woo_temp_msg, $headers, $mail_template_post, $compact ) ) {
								$sent_mail_templates[] = $email_old_template_id ;
								$store_template_id     = maybe_serialize( array_filter( $sent_mail_templates ) ) ;
								update_post_meta( $row_id, 'rac_cart_email_template_id', $store_template_id ) ;
								//add to mail log
								$template_used         = $mail_template_id_post . '- Manual' ;
								$args                  = array(
									'rac_email_id'      => $to,
									'rac_date_time'     => $current_time,
									'rac_template_used' => $template_used,
									'rac_cart_id'       => $each_cart->id,
										) ;
								//insert emaillog post
								fp_rac_insert_emaillog_post( $args ) ;
								//count mail count
								FP_RAC_Counter::rac_do_mail_count() ;
								//count of sending each mail template count
								FP_RAC_Counter::email_count_by_template( $mail_template_id_post ) ;
							}
						}
					}
					fp_rac_wpml_switch_lang() ;
				}
			}
			echo 'sent' ;
			exit() ;
		}

	}

	FP_RAC_Manual_Mail::init() ;
}
