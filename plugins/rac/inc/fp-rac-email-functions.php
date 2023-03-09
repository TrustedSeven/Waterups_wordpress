<?php
/*
 * Email Commom Functions
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! function_exists( 'rac_send_mail' ) ) {

	function rac_send_mail( $to, $subject, $woo_temp_msg, $headers, $html_template = '', $compact = array() ) {
		global $woocommerce ;
		
		//This hook for email return path header
		add_action( 'phpmailer_init', array( 'FP_RAC_Send_Email_Woocommerce_Mailer', 'fp_rac_phpmailer_init' ), 10, 1 ) ;
		FP_RAC_Send_Email_Woocommerce_Mailer::$sending = $compact ;
		if ( ( float ) $woocommerce->version <= ( float ) ( '2.2.0' ) ) {
			if ( 'webmaster1' == get_option( 'rac_webmaster_mail' ) ) {
				if ( 'wp_mail' == get_option( 'rac_trouble_mail' ) ) {
					return wp_mail( $to, $subject, $woo_temp_msg, $headers ) ;
				} else {
					return mail( $to, $subject, $woo_temp_msg, $headers, '-f' . get_option( 'rac_textarea_mail' ) ) ;
				}
			} else {
				if ( 'wp_mail' == get_option( 'rac_trouble_mail' ) ) {
					return wp_mail( $to, $subject, $woo_temp_msg, $headers ) ;
				} else {
					return mail( $to, $subject, $woo_temp_msg, $headers ) ;
				}
			}
		} else {
			if ( 'HTML' == $html_template ) {
				FP_RAC_Send_Email_Woocommerce_Mailer::send_email_via_woocommerce_mailer( $to, $subject, $woo_temp_msg, $headers, $compact ) ;
				return true ;
			} else {
				wp_mail( $to, $subject, $woo_temp_msg, $headers ) ;
				return true ;
			}
		}

		FP_RAC_Send_Email_Woocommerce_Mailer::$sending = false ;
	}

}

if ( ! function_exists( 'rac_email_inline_style' ) ) {

	function rac_email_inline_style( $content, $css, $full_content = false ) {
		if ( ! $css || ! $content ) {
			return $content ;
		}

		// Return the content with style css when DOMDocument class not exists.
		if ( ! class_exists( 'DOMDocument' ) ) {
			return '<style type="text/css">' . $css . '</style>' . $content ;
		}

		if ( class_exists( '\Pelago\Emogrifier\CssInliner' ) ) {
			// To create a instance with original HTML.
			$css_inliner_class         = 'Pelago\Emogrifier\CssInliner' ;
			$domDocument               = $css_inliner_class::fromHtml( $content )->inlineCss( $css )->getDomDocument() ;
			// Removing the elements with display:none style declaration from the content.
			$html_pruner_class         = 'Pelago\Emogrifier\HtmlProcessor\HtmlPruner' ;
			$html_pruner_class::fromDomDocument( $domDocument )->removeElementsWithDisplayNone() ;
			// Converts a few style attributes values to visual HTML attributes.
			$attribute_converter_class = 'Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter' ;
			$visual_html               = $attribute_converter_class::fromDomDocument( $domDocument )->convertCssToVisualAttributes() ;

			$content = ( $full_content ) ? $visual_html->render() : $visual_html->renderBodyContent() ;
		} elseif ( class_exists( '\Pelago\Emogrifier' ) ) {
			$emogrifier_class = 'Pelago\Emogrifier' ;
			$emogrifier       = new Emogrifier( $content, $css ) ;
			$content          = ( $full_content ) ? $emogrifier->emogrify() : $emogrifier->emogrifyBodyContent() ;
		} elseif ( version_compare( WC_VERSION, '4.0', '<' ) ) {
			$emogrifier_class = 'Emogrifier' ;
			if ( ! class_exists( $emogrifier_class ) ) {
				include_once dirname( WC_PLUGIN_FILE ) . '/includes/libraries/class-emogrifier.php' ;
			}

			$emogrifier = new Emogrifier( $content, $css ) ;
			$content    = ( $full_content ) ? $emogrifier->emogrify() : $emogrifier->emogrifyBodyContent() ;
		}

		return $content ;
	}

}

if ( ! function_exists( 'rac_format_email_headers' ) ) {

	// format email header
	function rac_format_email_headers( $compact = array(), $bcc = false ) {
		$headers = '' ;
		if ( empty( $compact ) ) {
			$sender_opt = 'woo' ;
		} else {
			$sender_opt = $compact[ 0 ] ;
			$from_name  = $compact[ 1 ] ;
			$from_email = $compact[ 2 ] ;
		}

		//header MIME version
		if ( 'none' != get_option( 'rac_mime_mail_header_ts' ) ) {//check for to aviod header duplication
			$headers = "MIME-Version: 1.0\r\n" ;
		}
		//header charset
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n" ;

		$from_name  = 'local' == $sender_opt ? $from_name : get_option( 'woocommerce_email_from_name' ) ;
		$from_email = 'local' == $sender_opt ? $from_email : get_option( 'woocommerce_email_from_address' ) ;

		//header for from
		if ( 'webmaster1' == get_option( 'rac_webmaster_mail' ) ) {
			$headers .= 'From: ' . $from_name . ' <' . $from_email . ">\r\n" ;
		} else {
			$headers .= 'From: ' . $from_name . ' <' . $from_email . ">\r\n" ;
		}

		//header for reply to
		if ( 'none' != get_option( 'rac_replyto_mail_header_ts' ) ) {//check for to aviod header duplication
			$headers .= 'Reply-To: ' . $from_name . ' <' . $from_email . ">\r\n" ;
		}

		//header BCC.
		if ( $bcc ) {
			$headers .= 'Bcc: ' . $bcc . "\r\n" ;
		}

		return $headers ;
	}

}

if ( ! function_exists( 'rac_email_woocommerce_html' ) ) {

	function rac_email_woocommerce_html( $mail_template_post, $subject, $message, $logo = false ) {

		if ( 'HTML' == $mail_template_post ) {
			ob_start() ;
			if ( function_exists( 'wc_get_template' ) ) {
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) ) ;
				echo do_shortcode( $message ) ;
				wc_get_template( 'emails/email-footer.php' ) ;
			} else {

				woocommerce_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) ) ;
				echo do_shortcode( $message ) ;
				woocommerce_get_template( 'emails/email-footer.php' ) ;
			}
			$woo_temp_msg = ob_get_clean() ;
		} elseif ( 'PLAIN' == $mail_template_post ) {

			$woo_temp_msg = $logo . $message ;
		} else {

			$woo_temp_msg = $message ;
		}

		return $woo_temp_msg ;
	}

}

if ( ! function_exists( 'rac_footer_email_customization' ) ) {

	function rac_footer_email_customization( $message ) {
		global $fp_rac_lang ;
		$trans_message = fp_get_wpml_text( 'woocommerce_email_footer_text', $fp_rac_lang, $message, 'admin_texts_woocommerce_email_footer_text' ) ;
		$site_name     = get_bloginfo( 'name' ) ;
		$trans_message = str_replace( '{site_title}', $site_name, $trans_message ) ;
		if ( 'yes' == get_option( 'fp_unsubscription_link_in_email' ) ) {
			if ( get_option( 'fp_unsubscription_footer_link_text_option' ) == '2' ) {
				$replace_footer_text = rac_replace_shortcode_in_custom_footer_text() ;
				$replace_footer_text = $trans_message . ' ' . $replace_footer_text ;
			} else {
				$replace_footer_text = rac_replace_shortcode_in_custom_footer_text() ;
			}
			return $replace_footer_text ;
		} else {
			return $trans_message ;
		}
	}

}

if ( ! function_exists( 'rac_cart_link_button_mode' ) ) {

	function rac_cart_link_button_mode( $cartlink, $cart_text ) {
		ob_start() ;
		?>
		<table class="fp-rac-cart-link-button-table" cellspacing="0" cellpadding="0">
			<tr>
				<td align="center" bgcolor="#<?php echo esc_attr( get_option( 'rac_cart_button_bg_color' ) ) ; ?>" class="fp-rac-cart-link-button-wrapper">
					<a href="<?php echo esc_url( $cartlink ) ; ?>" class="fp-rac-cart-link-button"><span><?php echo esc_html( $cart_text ) ; ?></span></a>
				</td>
			</tr>
		</table>
		<?php
		$results = ob_get_clean() ;
		return $results ;
	}

}

if ( ! function_exists( 'rac_cart_link_image_mode' ) ) {

	function rac_cart_link_image_mode( $cartlink, $cart_text ) {
		ob_start() ;
		?>
		<table class="fp-rac-cart-link-button-table" cellspacing="0" cellpadding="0">
			<tr>
				<td>
					<a href="<?php echo esc_url( $cartlink ) ; ?>"><img src="<?php echo esc_url( get_option( 'fp_rac_email_cartlink_logo_text' ) ) ; ?>" width="<?php echo esc_attr( get_option( 'rac_cart_link_image_width' ) ) ; ?>px" height="<?php echo esc_attr( get_option( 'rac_cart_link_image_height' ) ) ; ?>px" alt="<?php echo esc_attr( $cart_text ) ; ?>"></a>
				</td>
			</tr>
		</table>
		<?php
		$results = ob_get_clean() ;
		return $results ;
	}

}

if ( ! function_exists( 'rac_shortcode_in_subject' ) ) {

	function rac_shortcode_in_subject( $firstname, $lastname, $content, $each_cart = false ) {
		if ( $each_cart ) {
			$custom_product_name = fp_get_wpml_text( 'rac_template_subject_customization', $each_cart->wpml_lang, get_option( 'rac_subject_product_shrotcode_customize' ) ) ;
			$product_details     = fp_rac_get_first_product_title( $each_cart ) ;
			extract( $product_details ) ;
			$product_name        = ( $product_count > 1 ) ? $product_title . ' ' . $custom_product_name : $product_title ;
		} else {
			$product_name = 'Product Name' ;
		}

		$find_array    = array( '{rac.firstname}', '{rac.lastname}', '{rac.productname}' ) ;
		$replace_array = array( $firstname, $lastname, html_entity_decode( $product_name ) ) ;

		$content = str_replace( $find_array, $replace_array, $content ) ;

		return $content ;
	}

}

if ( ! function_exists( 'fp_rac_common_custom_restrict' ) ) {

	function fp_rac_common_custom_restrict( $to, $by ) {
		switch ( $by ) {
			case 'admin_email':
				$restrict_array = array(
					'custom_exclude'          => 'rac_admin_email_restriction_type',
					'custom_user_role'        => 'rac_admin_restrict_user_role',
					'custom_user_name_select' => 'rac_admin_restrict_user_name',
					'custom_mailid_edit'      => 'rac_admin_restrict_email_id',
					'custom_email_provider'   => 'rac_admin_restrict_email_providers',
					'custom_include_exclude'  => 'rac_admin_email_restriction_mode'
						) ;
				break ;
			case 'email':
				$restrict_array = array(
					'custom_exclude'          => 'custom_exclude',
					'custom_user_role'        => 'custom_user_role',
					'custom_user_name_select' => 'custom_user_name_select',
					'custom_mailid_edit'      => 'custom_mailid_edit',
					'custom_email_provider'   => 'custom_email_provider_edit',
					'custom_include_exclude'  => 'custom_include_exclude_email'
						) ;
				break ;
			default:
				$restrict_array = array(
					'custom_exclude'          => 'custom_restrict',
					'custom_user_role'        => 'custom_user_role_for_restrict_in_cart_list',
					'custom_user_name_select' => 'custom_user_name_select_for_restrict_in_cart_list',
					'custom_mailid_edit'      => 'custom_mailid_for_restrict_in_cart_list',
					'custom_email_provider'   => 'custom_email_provider_for_restrict_in_cart_list',
					'custom_ip_address'       => 'custom_ip_address_for_restrict_in_cart_list',
					'custom_include_exclude'  => 'custom_include_exclude_entry'
						) ;
				break ;
		}

		if ( rac_check_is_array( $restrict_array ) ) {
			extract( $restrict_array ) ;
			$getdesiredoption = get_option( $custom_exclude ) ;
			if ( 'mail_id' == $getdesiredoption ) {
				$option_array = get_option( $custom_mailid_edit ) ;
				$option_array = explode( ',', $option_array ) ;
			} elseif ( 'name' == $getdesiredoption ) {
				$option_array = get_option( $custom_user_name_select ) ;
				$getuserby    = get_user_by( 'email', $to ) ;
				if ( $getuserby ) {
					$to = $getuserby->ID ;
				}
			} elseif ( 'email_provider' == $getdesiredoption ) {
				$to           = substr( strrchr( $to, '@' ), 1 ) ;
				$option_array = get_option( $custom_email_provider ) ;
				$option_array = explode( ',', $option_array ) ;
			} elseif ( 'email' != $by && 'ip_address' == $getdesiredoption ) {
				$to           = rac_get_client_ip() ;
				$option_array = get_option( $custom_ip_address ) ;
				$option_array = explode( ',', $option_array ) ;
			} else {
				$option_array = get_option( $custom_user_role ) ;
				$getuserby    = get_user_by( 'email', $to ) ;
				if ( $getuserby ) {
					$to = implode( ',', $getuserby->roles ) ;
				} else {
					$to = 'rac_guest' ;
				}
			}

			if ( ! empty( $option_array ) ) {
				$inlude_exclude = get_option( $custom_include_exclude ) ;
				if ( ! in_array( $to, $option_array ) ) {
					if ( 'include' == $inlude_exclude ) {
						return false ;
					} else {
						return true ;
					}
				} else {
					if ( 'include' == $inlude_exclude ) {
						return true ;
					} else {
						return false ;
					}
				}
			} else {
				return true ;
			}
		}
	}

}

if ( ! function_exists( 'rac_unsubscription_shortcode' ) ) {

	function rac_unsubscription_shortcode( $to, $message, $lang = '' ) {
		$footer_message = rac_replace_shortcode_in_custom_footer_text( $to, $lang ) ;
		$message        = str_replace( '{rac.unsubscribe}', $footer_message, $message ) ;

		return $message ;
	}

}

if ( ! function_exists( 'rac_replace_shortcode_in_custom_footer_text' ) ) {

	function rac_replace_shortcode_in_custom_footer_text( $to = '', $fp_rac_lang = '' ) {
		global $to, $fp_rac_lang ;
		$site_name         = get_bloginfo( 'name' ) ; // Site Name
		$create_nonce      = wp_create_nonce( 'myemail' ) ;
		$footer_message    = get_option( 'fp_unsubscription_footer_message' ) ;
		$trans_footer_msg  = fp_get_wpml_text( 'fp_unsubscription_footer_message', $fp_rac_lang, $footer_message, 'admin_texts_fp_unsubscription_footer_message' ) ;
		$unsublink         = fp_rac_get_unsubscribe( $to, $fp_rac_lang ) ;
		$find_shortcode    = array( '{rac_unsubscribe}', '{rac_site}' ) ;
		$replace_shortcode = array( $unsublink, $site_name ) ;
		$trans_footer_msg  = str_replace( $find_shortcode, $replace_shortcode, $trans_footer_msg ) ;
		return $trans_footer_msg ;
	}

}

if ( ! function_exists( 'fp_rac_extract_cart_list' ) ) {

	function fp_rac_extract_cart_list( $each_list, $single_product = false, $product_id = false ) {
		$product_ids       = array() ;
		$product_names     = array() ;
		$cart_array        = fp_rac_format_cart_details( $each_list->cart_details, $each_list ) ;
		$total             = '0' ;
		$points            = '0' ;
		$shipping_total    = '0' ;
		$shipping_tax_cost = '0' ;
		if ( is_array( $cart_array ) && empty( $each_list->ip_address ) && '0' != $each_list->user_id ) {
			$shipping_total    = ( float ) FP_RAC_Polish_Product_Info::fp_rac_get_shipping_total( $cart_array ) ;
			$shipping_tax_cost = ( float ) FP_RAC_Polish_Product_Info::fp_rac_get_shipping_tax_total( $cart_array ) ;
			if ( isset( $cart_array[ 'shipping_details' ] ) ) {
				unset( $cart_array[ 'shipping_details' ] ) ;
			}
			if ( isset( $cart_array[ 'woocs_is_multipled' ] ) ) {
				unset( $cart_array[ 'woocs_is_multipled' ] ) ;
			}
			if ( rac_check_is_array( $cart_array ) ) {
				foreach ( $cart_array as $cart ) {
					foreach ( $cart as $inside ) {
						foreach ( $inside as $product ) {
							$total         += ( $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ] ) ;
							$product_title = get_the_title( $product[ 'product_id' ] ) ;
							if ( $single_product ) {
								$product_count = count( $inside ) ;
								return array( 'product_title' => $product_title, 'product_count' => $product_count ) ;
							}
							$points          += fp_rac_get_rewards_points( $product ) ;
							$product_ids[]   = $product[ 'variation_id' ] ? $product[ 'variation_id' ] : $product[ 'product_id' ] ;
							$product_names[] = FP_RAC_Polish_Product_Info::fp_rac_format_product_name_by_sku( $product_title, $product ) ;
						}
					}
				}
			}
		} elseif ( is_array( $cart_array ) ) {
			//for cart captured at checkout(GUEST)
			$shipping_total    = ( float ) FP_RAC_Polish_Product_Info::fp_rac_get_shipping_total( $cart_array ) ;
			$shipping_tax_cost = ( float ) FP_RAC_Polish_Product_Info::fp_rac_get_shipping_tax_total( $cart_array ) ;
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
			if ( rac_check_is_array( $cart_array ) ) {
				foreach ( $cart_array as $product ) {
					$total         += ( $product[ 'line_subtotal' ] + $product[ 'line_subtotal_tax' ] ) ;
					$product_title = get_the_title( $product[ 'product_id' ] ) ;
					$product_ids[] = $product[ 'variation_id' ] ? $product[ 'variation_id' ] : $product[ 'product_id' ] ;
					if ( $single_product ) {
						$product_count = count( $cart_array ) ;
						return array( 'product_title' => $product_title, 'product_count' => $product_count ) ;
					}
					$points          += fp_rac_get_rewards_points( $product ) ;
					$product_names[] = FP_RAC_Polish_Product_Info::fp_rac_format_product_name_by_sku( $product_title, $product ) ;
				}
			}
		} elseif ( is_object( $cart_array ) ) { // For Guest
			$old_order_obj = new FP_RAC_Previous_Order_Data( $each_list ) ;
			if ( $old_order_obj->get_cart_content() ) {
				$shipping_tax_cost = $old_order_obj->get_shipping_tax() ;
				$shipping_total    = $old_order_obj->get_total_shipping() ;
				$order_items       = $old_order_obj->get_items() ;
				if ( rac_check_is_array( $order_items ) ) {
					foreach ( $order_items as $item ) {
						$total         += ( $item[ 'line_subtotal' ] + $item[ 'line_subtotal_tax' ] ) ;
						$product_title = get_the_title( $item[ 'product_id' ] ) ;
						$product_ids[] = $item[ 'variation_id' ] ? $item[ 'variation_id' ] : $item[ 'product_id' ] ;
						if ( $single_product ) {
							$product_count = count( $order_items ) ;
							return array( 'product_title' => $product_title, 'product_count' => $product_count ) ;
						}
						$points          += fp_rac_get_rewards_points( $item ) ;
						$product_names[] = FP_RAC_Polish_Product_Info::fp_rac_format_product_name_by_sku( $product_title, $item ) ;
					}
				}
			}
		}

		if ( $product_id ) {
			return $product_ids ;
		}

		$total = $total + $shipping_total + $shipping_tax_cost ;
		return array( 'product_names' => $product_names, 'total' => $total, 'earn_points' => $points ) ;
	}

}

if ( ! function_exists( 'fp_rac_cart_details' ) ) {

	function fp_rac_cart_details( $each_list ) {
		$cart_details    = '' ;
		$product_details = fp_rac_extract_cart_list( $each_list ) ;
		extract( $product_details ) ;
		if ( ! empty( $product_names ) ) {
			$cart_details .= implode( ' , ', $product_names ) ;
			$cart_details .= ' / ' . fp_rac_format_price( $total, $each_list->currency_code, null, $each_list ) ;
			if ( ! empty( $earn_points ) ) {
				$cart_details .= ' / ' . $earn_points . ' Points' ;
			}
		} else {
			$cart_details .= 'no data' ;
		}
		return $cart_details ;
	}

}

if ( ! function_exists( 'fp_rac_get_first_product_title' ) ) {

	function fp_rac_get_first_product_title( $each_list ) {
		return fp_rac_extract_cart_list( $each_list, true ) ;
	}

}

if ( ! function_exists( 'fp_rac_get_cart_list_product_ids' ) ) {

	function fp_rac_get_cart_list_product_ids( $each_list ) {
		return fp_rac_extract_cart_list( $each_list, false, true ) ;
	}

}
