<?php

/*
 * Common functions  for cart status and email
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

include_once 'admin/rac-admin-functions.php' ;
include_once 'fp-rac-email-functions.php' ;
include_once 'fp-rac-compatibility-functions.php' ;

if ( ! function_exists( 'fp_rac_check_is_array' ) ) {

	function fp_rac_check_is_array( $array ) {
		if ( is_array( $array ) ) {
			$array = $array ;
		} else {
			$array = explode( ',', $array ) ;
		}
		return $array ;
	}

}

if ( ! function_exists( 'rac_check_is_array' ) ) {

	function rac_check_is_array( $array ) {
		if ( is_array( $array ) && ! empty( $array ) ) {
			return true ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'rac_date_format' ) ) {

	function rac_date_format() {
		$date_format = get_option( 'rac_date_format' ) ;
		if ( '' == $date_format ) {
			$date_format = 'd:m:y' ;
		}
		return $date_format ;
	}

}

if ( ! function_exists( 'rac_time_format' ) ) {

	function rac_time_format() {
		$time_format = get_option( 'rac_time_format' ) ;
		if ( '' == $time_format ) {
			$time_format = 'h:i:s' ;
		}
		return $time_format ;
	}

}

if ( ! function_exists( 'rac_get_client_ip' ) ) {

	function rac_get_client_ip() {
		$ipaddress = '' ;
		if ( ! empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) {
			$ipaddress = wc_clean( wp_unslash( $_SERVER[ 'HTTP_CLIENT_IP' ] ) ) ;
		} else if ( ! empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) {
			$ipaddress = wc_clean( wp_unslash( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) ) ;
		} else if ( ! empty( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
			$ipaddress = wc_clean( wp_unslash( $_SERVER[ 'REMOTE_ADDR' ] ) ) ;
		}

		return $ipaddress ;
	}

}

if ( ! function_exists( 'fp_rac_url_for_checkout_or_cart_with_lan' ) ) {

	function fp_rac_url_for_checkout_or_cart_with_lan( $lang_code ) {
		if ( get_option( 'rac_cartlink_redirect' ) == '2' ) {
			$redirect_url = rac_get_page_permalink_dependencies( 'checkout' ) ;
			if ( null != $lang_code ) {
				$redirect_url = 'en' == $lang_code ? $redirect_url : fp_rac_wpml_convert_url( $redirect_url, $lang_code ) ;
			}
		} else {
			$redirect_url = rac_get_page_permalink_dependencies( 'cart' ) ;

			if ( null != $lang_code ) {

				$redirect_url = 'en' == $lang_code ? $redirect_url : fp_rac_wpml_convert_url( $redirect_url, $lang_code ) ;
			}
		}
		return $redirect_url ;
	}

}
if ( ! function_exists( 'rac_get_page_permalink_dependencies' ) ) {

	function rac_get_page_permalink_dependencies( $page ) {
		$redirect_url = get_permalink( get_option( 'woocommerce_' . $page . '_page_id' ) ) ;
		return $redirect_url ;
	}

}

if ( ! function_exists( 'fp_rac_placeholder_img' ) ) {

	function fp_rac_placeholder_img() {
		$imagesize = fp_rac_get_product_image_size() ;
		$size      = array(
			'width'  => $imagesize[ 'width' ],
			'height' => $imagesize[ 'height' ],
			'crop'   => 1
				) ;

		return '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="' . esc_attr( $size[ 'width' ] ) . '" height="' . esc_attr( $size[ 'height' ] ) . '" />' ;
	}

}

if ( ! function_exists( 'fp_rac_get_product_image_size' ) ) {

	function fp_rac_get_product_image_size( $attribute = false ) {
		$imagesize = get_option( 'rac_product_img_size' ) ;

		if ( $attribute ) {
			return isset( $imagesize[ $attribute ] ) ? $imagesize[ $attribute ] : 90 ;
		}

		$width  = isset( $imagesize[ 'width' ] ) ? $imagesize[ 'width' ] : 90 ;
		$height = isset( $imagesize[ 'height' ] ) ? $imagesize[ 'height' ] : 90 ;

		return array( 'width' => $width, 'height' => $height ) ;
	}

}

if ( ! function_exists( 'fp_rac_get_current_language' ) ) {

	function fp_rac_get_current_language() {

		if ( function_exists( 'icl_register_string' ) ) {
			$currentuser_lang = ( isset( $_SESSION[ 'wpml_globalcart_language' ] ) ) ? wc_clean( wp_unslash( $_SESSION[ 'wpml_globalcart_language' ] ) ) : ( defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : 'en' ) ;
		} elseif ( function_exists( 'weglot_get_service' ) ) {
			$currentuser_lang = weglot_get_service( 'Request_Url_Service_Weglot' )->get_current_language()->getInternalCode() ;
		} else {
			$currentuser_lang = 'en' ;
		}

		return $currentuser_lang ;
	}

}

if ( ! function_exists( 'fp_rac_update_cartlist_status' ) ) {

	function fp_rac_update_cartlist_status( $user ) {
		$current_time = current_time( 'timestamp' ) ;

		if ( 'member' == $user ) {
			$check        = '!=' ;
			$cut_off_time = fp_rac_get_interval( 'rac_abandon_cart_time', 'rac_abandon_cart_time_type' ) ;
		} else {//guest
			$check        = '=' ;
			$cut_off_time = fp_rac_get_interval( 'rac_abandon_cart_time_guest', 'rac_abandon_cart_time_type_guest' ) ;
		}
		$args            = array(
			'posts_per_page' => -1,
			'post_type'      => 'raccartlist',
			'post_status'    => array( 'rac-cart-new' ),
			'meta_query'     => array(
				array(
					'key'     => 'rac_user_details',
					'value'   => '0',
					'compare' => $check
				),
			),
			'fields'         => 'ids'
				) ;
		$status_new_list = fp_rac_check_query_having_posts( $args ) ;
		if ( rac_check_is_array( $status_new_list ) ) {
			foreach ( $status_new_list as $cart_id ) {
				$cart_abandoned_time = ( int ) $cut_off_time + ( int ) get_post_meta( $cart_id, 'rac_cart_abandoned_time', true ) ;
				if ( $current_time > $cart_abandoned_time ) {
					$args = array( 'ID' => $cart_id, 'post_type' => 'raccartlist', 'post_status' => 'rac-cart-abandon' ) ;
					wp_update_post( $args ) ;
					FP_RAC_ADMIN_Notification_Email::fp_rac_mail_admin_cart_abandoned( $cart_id ) ;
					FP_RAC_Counter::rac_do_abandoned_count() ;
				}
			}
		}
	}

}

if ( ! function_exists( 'fp_rac_get_interval' ) ) {

	function fp_rac_get_interval( $interval_time, $interval_type ) {
		$interval = ( float ) get_option( $interval_time, 12 ) ;
		$type     = get_option( $interval_type, 'hours' ) ;
		if ( 'minutes' == $type ) {
			$interval = $interval * 60 ;
		} else if ( 'hours' == $type ) {
			$interval = $interval * 3600 ;
		} else if ( 'days' == $type ) {
			$interval = $interval * 86400 ;
		}
		return $interval ;
	}

}

if ( ! function_exists( 'fp_rac_array_column_function' ) ) {

	function fp_rac_array_column_function( $array, $function = 'fp_rac_array_map', $id = 'user_email' ) {
		if ( function_exists( 'array_column' ) ) {
			$array = array_column( $array, $id ) ;
		} else {
			$array = array_map( $function, $array ) ;
		}
		return $array ;
	}

}

if ( ! function_exists( 'fp_rac_array_map' ) ) {

	function fp_rac_array_map( $array ) {

		return $array[ 'user_email' ] ;
	}

}

if ( ! function_exists( 'fp_rac_array_map_post_ids' ) ) {

	function fp_rac_array_map_post_ids( $array ) {

		return $array[ 'ID' ] ;
	}

}

if ( ! function_exists( 'fp_rac_check_user_already_bought' ) ) {

	function fp_rac_check_user_already_bought( $email, $user_id, $cart ) {
		if ( 'yes' == get_option( 'rac_email_restrict_when_cutomer_already_bought_product' ) ) {
			$product_ids = fp_rac_get_cart_list_product_ids( $cart ) ;
			return fp_rac_customer_bought_product( $email, $user_id, $product_ids ) ;
		} else {
			return true ;
		}
	}

}

if ( ! function_exists( 'fp_rac_customer_bought_product' ) ) {

	function fp_rac_customer_bought_product( $customer_email, $user_id, $product_array ) {
		global $wpdb ;
		$dummy_array   = array() ;
		$customer_data = array( $user_id ) ;

		if ( $user_id ) {
			$user = get_user_by( 'id', $user_id ) ;

			if ( isset( $user->user_email ) ) {
				$customer_data[] = $user->user_email ;
			}
		}

		if ( is_email( $customer_email ) ) {
			$customer_data[] = $customer_email ;
		}
		$customer_data = array_map( 'esc_sql', array_filter( array_unique( $customer_data ) ) ) ;
		$statuses      = array_map( 'esc_sql', fp_rac_get_paid_statuses_of_order() ) ;

		if ( 0 == count( $customer_data ) ) {
			return false ;
		}

		$result = $wpdb->get_col( $wpdb->prepare( "
			SELECT im.meta_value FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
			WHERE p.post_status IN (%s)
			AND pm.meta_key IN ( '_billing_email', '_customer_user' )
			AND im.meta_key IN ( '_product_id', '_variation_id' )
			AND im.meta_value != 0
			AND pm.meta_value IN (%s)
		", "'" . implode( "','", $customer_data ) . "'", "'wc-" . implode( "' , 'wc-", $statuses ) . "'" ) ) ;

		$result = array_map( 'absint', $result ) ;

		if ( empty( $result ) ) {
			return true ;
		}
		$dummy_array = array_intersect( $product_array, $result ) ;
		if ( empty( $dummy_array ) ) {
			return true ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'fp_rac_check_email_subscribed' ) ) {

	function fp_rac_check_email_subscribed( $to ) {
		$check_user_id = 0 ;
		$email_id      = '' ;
		if ( filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
			$email_id      = $to ;
			$check_user_id = rac_check_is_member_or_guest( $to, true ) ;
		} else {
			$check_user_id = $to ;
		}

		if ( $check_user_id ) {
			// for member
			$status = get_user_meta( $check_user_id, 'fp_rac_mail_unsubscribed', true ) ;
			if ( 'yes' != $status ) {
				return true ;
			} else {
				return false ;
			}
		} else {
			// for guest
			$needle               = $email_id ;
			$email_array          = ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ;
			$filtered_email_array = array_filter( $email_array ) ;
			if ( ! in_array( $needle, $filtered_email_array ) ) {
				return true ;
			} else {
				return false ;
			}
		}
	}

}

if ( ! function_exists( 'rac_get_user_id_from_cart_list' ) ) {

	function rac_get_user_id_from_cart_list( $cart ) {
		if ( 'old_order' == $cart->user_id ) {
			$old_order_obj = new FP_RAC_Previous_Order_Data( $cart ) ;
			if ( $old_order_obj->get_cart_content() ) {
				$id = $old_order_obj->get_user_id() ;
				return $id ;
			}
		}
		return $cart->user_id ;
	}

}

if ( ! function_exists( 'rac_check_is_member_or_guest' ) ) {

	function rac_check_is_member_or_guest( $to, $bool = false ) {

		$get_user_by_email = get_user_by( 'email', $to ) ;

		if ( $get_user_by_email ) {
			return $bool ? $get_user_by_email->ID : true ;
		} else {
			return 0 ;
		}
	}

}

if ( ! function_exists( 'rac_return_user_id' ) ) {

	function rac_return_user_id( $memberemail ) {

		$get_user_by_email = get_user_by( 'email', $memberemail ) ;

		return $get_user_by_email->ID ;
	}

}

if ( ! function_exists( 'fp_rac_restirct_insert_cart_based_on' ) ) {

	function fp_rac_restirct_insert_cart_based_on( $user_email ) {
		$insert_cart_based = get_option( 'rac_remove_carts' ) ;
		$status            = array() ;
		if ( 'no' == $insert_cart_based ) {
			return true ;
		} elseif ( 'pre_cart' == $insert_cart_based ) {
			$dont_capture_option = get_option( 'rac_dont_capture_for_option' ) ;
			if ( empty( $dont_capture_option ) ) {
				return true ;
			} else {
				$new_status = array( 'NEW'       => 'rac-cart-new',
					'ABANDON'   => 'rac-cart-abandon',
					'RECOVERED' => 'rac-cart-recovered' ) ;
				foreach ( $new_status as $key => $value ) {
					if ( in_array( $key, ( array ) $dont_capture_option ) ) {
						$status = $value ;
					}
				}
			}
			$args  = array(
				'posts_per_page' => -1,
				'post_type'      => 'raccartlist',
				'post_status'    => $status,
				'meta_query'     => array(
					array(
						'key'   => 'rac_cart_email_id',
						'value' => $user_email,
					),
				),
				'fields'         => 'ids'
					) ;
			$check = fp_rac_check_query_having_posts( $args ) ;
			if ( ( ( ! is_null( $check ) ) && ( ! empty( $check ) ) ) ) {
				return false ;
			} else {
				return true ;
			}
		} else {
			$new_carts     = array() ;
			$abandon_carts = array() ;
			$overall_carts = array() ;
			$args          = array(
				'posts_per_page' => -1,
				'post_type'      => 'raccartlist',
				'meta_query'     => array(
					array(
						'key'   => 'rac_cart_email_id',
						'value' => $user_email,
					),
				),
				'fields'         => 'ids'
					) ;
			if ( 'yes' == get_option( 'rac_remove_new' ) ) {
				$args[ 'post_status' ] = 'rac-cart-new' ;
				$new_carts             = fp_rac_check_query_having_posts( $args ) ;
			}
			if ( 'yes' == get_option( 'rac_remove_abandon' ) ) {
				$args[ 'post_status' ] = 'rac-cart-abandon' ;
				$abandon_carts         = fp_rac_check_query_having_posts( $args ) ;
			}
			$overall_carts = array_merge( $new_carts, $abandon_carts ) ;
			if ( rac_check_is_array( $overall_carts ) ) {
				foreach ( $overall_carts as $new_cart_id ) {
					wp_delete_post( $new_cart_id, true ) ;
				}
			}
			return true ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_cart_list' ) ) {

	function fp_rac_get_cart_list( $email, $type = 'email' ) {
		$user_key = ( 'email' == $type ) ? 'rac_cart_email_id' : 'rac_user_details' ;

		$args = array(
			'post_type'   => 'raccartlist',
			'post_status' => array( 'rac-cart-new', 'trash', 'rac-cart-abandon', 'rac-cart-recovered' ),
			'meta_query'  => array(
				array(
					'key'   => $user_key,
					'value' => $email,
				),
			),
			'fields'      => 'ids'
				) ;

		return fp_rac_check_query_having_posts( $args ) ;
	}

}

if ( ! function_exists( 'fp_rac_extract_cartlist_content' ) ) {

	function fp_rac_extract_cartlist_content( $cart_array, $cartlist = array(), $bool = false ) {
		$product_ids = array() ;
		if ( is_array( $cart_array ) && ( ! empty( $cart_array ) ) ) {
			if ( isset( $cart_array[ 'shipping_details' ] ) ) {
				unset( $cart_array[ 'shipping_details' ] ) ;
			}
			if ( isset( $cart_array[ 'woocs_is_multipled' ] ) ) {
				unset( $cart_array[ 'woocs_is_multipled' ] ) ;
			}
			if ( isset( $cart_array[ 0 ][ 'cart' ] ) ) {
				$cart_array = $cart_array[ 0 ][ 'cart' ] ;
				if ( rac_check_is_array( $cart_array ) ) {
					foreach ( $cart_array as $product ) {
						$product_ids[] = $product[ 'product_id' ] ;
					}
				}
			} elseif ( is_array( $cart_array ) && ( ! empty( $cart_array ) ) ) {
				if ( isset( $cart_array[ 'visitor_mail' ] ) ) {
					unset( $cart_array[ 'visitor_mail' ] ) ;
				}
				if ( isset( $cart_array[ 'first_name' ] ) ) {
					unset( $cart_array[ 'first_name' ] ) ;
				}
				if ( isset( $cart_array[ 'last_name' ] ) ) {
					unset( $cart_array[ 'last_name' ] ) ;
				}
				if ( isset( $cart_array[ 'visitor_phone' ] ) ) {
					unset( $cart_array[ 'visitor_phone' ] ) ;
				}
				foreach ( $cart_array as $product ) {
					$product_ids[] = $product[ 'product_id' ] ;
				}
			}
		} else {
			$get_items = array() ;
			if ( $bool ) {
				$old_order_obj = new FP_RAC_Previous_Order_Data( $cartlist ) ;
				if ( $old_order_obj->get_cart_content() ) {
					$get_items = $old_order_obj->get_items() ;
				}
			} else {
				$order = fp_rac_get_order_obj( fp_rac_get_order_obj_data( $cart_array, 'id' ) ) ;
				if ( is_object( $order ) ) {
					$get_items = $order->get_items() ;
				}
			}

			if ( rac_check_is_array( $get_items ) ) {
				foreach ( $get_items as $product ) {
					$product_ids[] = $product[ 'product_id' ] ;
				}
			}
		}

		return $product_ids ;
	}

}

if ( ! function_exists( 'fp_rac_get_cartlist_user_details' ) ) {

	function fp_rac_get_cartlist_user_details( $cartlist, $type = 'phone' ) {
		$phone_number = '' ;
		$first_name   = '' ;
		$last_name    = '' ;
		$cart_content = fp_rac_format_cart_details( $cartlist->cart_details, $cartlist ) ;
		if ( '0' == $cartlist->user_id && ! empty( $cartlist->ip_address ) ) {
			$phone_number = isset( $cart_content[ 'phone_number' ] ) ? $cart_content[ 'phone_number' ] : '' ;
			$first_name   = isset( $cart_content[ 'first_name' ] ) ? $cart_content[ 'first_name' ] : '' ;
			$last_name    = isset( $cart_content[ 'last_name' ] ) ? $cart_content[ 'last_name' ] : '' ;
		} elseif ( '0' == $cartlist->user_id && empty( $cartlist->ip_address ) ) {
			$old_order_obj       = new FP_RAC_Previous_Order_Data( $cartlist ) ;
			$cart_content_exists = $old_order_obj->get_cart_content() ;
			$phone_number        = ( $cart_content_exists ) ? $old_order_obj->get_billing_phoneno() : '' ;
			$first_name          = ( $cart_content_exists ) ? $old_order_obj->get_billing_firstname() : '' ;
			$last_name           = ( $cart_content_exists ) ? $old_order_obj->get_billing_lastname() : '' ;
		} elseif ( 'old_order' == $cartlist->user_id ) {
			$old_order_obj       = new FP_RAC_Previous_Order_Data( $cartlist ) ;
			$cart_content_exists = $old_order_obj->get_cart_content() ;
			$phone_number        = ( $cart_content_exists ) ? $old_order_obj->get_billing_phoneno() : '' ;
			$first_name          = ( $cart_content_exists ) ? $old_order_obj->get_billing_firstname() : '' ;
			$last_name           = ( $cart_content_exists ) ? $old_order_obj->get_billing_lastname() : '' ;
		} else {
			$user_inf     = get_userdata( $cartlist->user_id ) ;
			$phone_number = is_object( $user_inf ) ? $user_inf->billing_phone : '' ;
			$first_name   = is_object( $user_inf ) ? $user_inf->user_firstname : '' ;
			$last_name    = is_object( $user_inf ) ? $user_inf->user_lastname : '' ;
		}

		if ( 'phone' == $type ) {
			return $phone_number ;
		}

		return array( $first_name, $last_name ) ;
	}

}

if ( ! function_exists( 'fp_rac_update_coupon_code' ) ) {

	function fp_rac_update_coupon_code( $cart_id, $order_id ) {
		$coupon_code     = '' ;
		$rac_coupon_code = get_post_meta( $cart_id, 'rac_cart_coupon_code', true ) ;
		$order           = fp_rac_get_order_obj( $order_id ) ;
		if ( ! empty( $order_id ) ) {
			if ( $order ) {
				$coupons_used = fp_rac_get_order_used_coupons( $order ) ;
				if ( ! empty( $coupons_used ) ) {
					if ( in_array( $rac_coupon_code, $coupons_used ) ) {
						$coupon_code = $rac_coupon_code ;
					}
				}
			}
		}

		update_post_meta( $cart_id, 'rac_coupon_details', $coupon_code ) ;
	}

}

if ( ! function_exists( 'fp_rac_get_compatible_product_name' ) ) {

	function fp_rac_get_compatible_product_name( $product, $curreny_code, $lang ) {

		if ( ! empty( $product[ 'addons' ] ) ) {
			$product_name = fp_rac_product_addons_compatibility( $product, $curreny_code ) ;
		} else {
			$product_name = FP_RAC_Polish_Product_Info::get_product_name( $product ) ;
		}

		$product_name = fp_rac_extra_details_in_product_name( $product_name, $product, $lang ) ;
		return $product_name ;
	}

}

if ( ! function_exists( 'fp_rac_backward_compatibility_for_table_sorting' ) ) {

	function fp_rac_backward_compatibility_for_table_sorting( $id ) {
		//backward compatibility of this plugin version 18.0
		$user_option_value = get_user_option( $id ) ;
		$option_value      = get_option( $id ) ;
		if ( $user_option_value ) {
			$order = ( 'yes' == $user_option_value ) ? 'ASC' : 'DESC' ;
		} elseif ( $option_value ) {
			$order = ( 'yes' == $option_value ) ? 'ASC' : 'DESC' ;
		} else {
			$order = 'ASC' ;
		}
		return $order ;
	}

}

if ( ! function_exists( 'fp_rac_get_order_status' ) ) {

	function fp_rac_get_order_status() {
		if ( function_exists( 'wc_get_order_statuses' ) ) {
			$order_list_keys   = array_keys( wc_get_order_statuses() ) ;
			$order_list_values = array_values( wc_get_order_statuses() ) ;
			$orderlist_replace = str_replace( 'wc-', '', $order_list_keys ) ;
			$orderlist_combine = array_combine( $orderlist_replace, $order_list_values ) ;
		} else {
			$order_status = ( array ) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) ) ;
			if ( rac_check_is_array( $order_status ) ) {
				foreach ( $order_status as $value ) {
					$status_name[] = $value->name ;
					$status_slug[] = $value->slug ;
				}
			}
			$orderlist_combine = array_combine( $status_slug, $status_name ) ;
		}

		return $orderlist_combine ;
	}

}

if ( ! function_exists( 'fp_rac_get_order_capture_permission' ) ) {
	/*
	 * Get the Order Capture Permission.
	 *
	 */

	function fp_rac_get_order_capture_permission( $order_id ) {
		$user_permission  = fp_rac_get_cartlist_entry_restriction( 'user' ) ;
		$guest_permission = fp_rac_get_cartlist_entry_restriction( 'guest' ) ;
		$customer_type    = get_post_meta( $order_id, '_customer_user', true ) ;
		if ( ( '0' == $customer_type && 'no' != $guest_permission ) || ( '0' != $customer_type && 'no' != $user_permission ) ) {
			return true ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_user_display_notice_permission' ) ) {
	/*
	 * Get the user display notice permission.
	 */

	function fp_rac_get_user_display_notice_permission() {
		$user_permission    = fp_rac_get_cartlist_entry_restriction( 'user' ) ;
		$user_message_allow = get_option( 'rac_user_notice_display' ) ;
		$user_list_of_pages = get_option( 'rac_user_pages_for_disp_notice' ) ;
		$user_notice_msg    = get_option( 'rac_user_notice_msg' ) ;

		if ( 'no' != $user_permission && 'yes' == $user_message_allow && ! empty( $user_list_of_pages ) && ! empty( $user_notice_msg ) && is_user_logged_in() ) {
			return true ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_guest_display_notice_permission' ) ) {
	/*
	 * Get the guest display notice permission.
	 */

	function fp_rac_get_guest_display_notice_permission() {
		$guest_permission    = fp_rac_get_cartlist_entry_restriction( 'guest' ) ;
		$guest_message_allow = get_option( 'rac_guest_notice_display' ) ;
		$guest_disp_of_pages = get_option( 'rac_guest_pages_for_disp_notice' ) ;
		$guest_notice_msg    = get_option( 'rac_guest_notice_msg' ) ;

		if ( 'no' != $guest_permission && 'yes' == $guest_message_allow && ! empty( $guest_disp_of_pages ) && ! empty( $guest_notice_msg ) && ! is_user_logged_in() ) {
			return true ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'fp_rac_check_guest_pages_for_display_notice' ) ) {
	/*
	 * Check Guest pages for display notice.
	 */

	function fp_rac_check_guest_pages_for_display_notice( $page ) {
		if ( fp_rac_get_guest_display_notice_permission() ) {
			$guest_disp_of_pages = get_option( 'rac_guest_pages_for_disp_notice' ) ;
			if ( in_array( $page, $guest_disp_of_pages ) ) {
				return true ;
			}
		}

		return false ;
	}

}

if ( ! function_exists( 'fp_rac_get_cartlist_entry_restriction' ) ) {
	/*
	 * Assign default value to user and guest restriction checkbox.
	 */

	function fp_rac_get_cartlist_entry_restriction( $member_id ) {
		$id            = 'rac_allow_' . $member_id . '_cartlist' ;
		$allow_members = get_option( $id ) ;

		if ( $allow_members ) {
			return $allow_members ;
		}

		return fp_rac_get_cartlist_entry_restriction_default_value( $member_id ) ;
	}

}

if ( ! function_exists( 'fp_rac_get_cartlist_entry_restriction_default_value' ) ) {
	/*
	 * Check default value of user and guest restriction checkbox.
	 */

	function fp_rac_get_cartlist_entry_restriction_default_value( $member_id ) {
		if ( ! get_option( 'rac_abandon_cart_time' ) ) { // This check is used for find the users types (Existing or New)
			return 'no' ;
		}

		$id = 'rac_allow_' . $member_id . '_cartlist' ;

		return get_option( $id ) ? 'no' : 'yes' ;
	}

}

if ( ! function_exists( 'fp_rac_get_current_user_role' ) ) {
	/*
	 * Get current user role.
	 */

	function fp_rac_get_current_user_role() {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user() ;
			$role = ( array ) $user->roles ;
			return $role[ 0 ] ;
		} else {
			return false ;
		}
	}

}

if ( ! function_exists( 'fp_rac_add_extra_cart_content' ) ) {
	/*
	 * Get Woocommerce Currency switcher is multiple value allowed or not.
	 */

	function fp_rac_add_extra_cart_content( $cartlist ) {
		$extra_content = array( 'shipping', 'woocs_is_multipled' ) ;
		foreach ( $extra_content as $content ) {
			switch ( $content ) {
				case 'shipping':
					$cartlist[ 'shipping_details' ] = FP_RAC_Insert_CartList_Entry::fp_rac_get_shipping_details() ;
					break ;

				case 'woocs_is_multipled':
					if ( class_exists( 'WOOCS' ) ) {
						global $WOOCS ;
						$cartlist[ 'woocs_is_multipled' ] = ( ! $WOOCS->is_multiple_allowed ) ? true : false ;
					}
					break ;
			}
		}
		return $cartlist ;
	}

}

if ( ! function_exists( 'fp_rac_format_cart_details' ) ) {
	/*
	 * Format Cart Details.
	 */

	function fp_rac_format_cart_details( $cart_details, $cart_list ) {

		$old_method_details = maybe_unserialize( $cart_details ) ;
		$new_method_details = maybe_unserialize( base64_decode( $cart_details ) ) ;

		if ( $old_method_details && ! is_string( $old_method_details ) ) {
			return $old_method_details ;
		} elseif ( $new_method_details && ! is_string( $new_method_details ) ) {

			return $new_method_details ;
		} elseif ( 'old_order' == $cart_list->user_id ) {

			return new FP_RAC_Previous_Order_Data( $cart_list ) ;
		}

		return false ;
	}

}
if ( ! function_exists( 'fp_rac_get_unsubscribe' ) ) {

	function fp_rac_get_unsubscribe( $to, $fp_rac_lang ) {

		if ( '2' != get_option( 'rac_unsubscription_type' ) ) {
			$site_url = get_option( 'rac_unsubscription_redirect_url' ) ;
			$site_url = ( $site_url ) ? $site_url : get_permalink( wc_get_page_id( 'myaccount' ) ) ;
		} else {
			$site_url = get_option( 'rac_manual_unsubscription_redirect_url' ) ;
			$site_url = ( $site_url ) ? $site_url : get_permalink( wc_get_page_id( 'myaccount' ) ) ;
		}

		$footer_link_text       = get_option( 'fp_unsubscription_footer_link_text' ) ;
		$trans_footer_link_text = fp_get_wpml_text( 'fp_unsubscription_footer_link_text', $fp_rac_lang, $footer_link_text, 'admin_texts_fp_unsubscription_footer_link_text' ) ;
		$unsublink              = esc_url( add_query_arg( array( 'email' => $to, 'action' => 'unsubscribe', '_mynonce' => wp_create_nonce( 'myemail' ) ), $site_url ) ) ;
		$unsublink              = '<a class="fp-rac-unsubscribe-link" href="' . $unsublink . '">' . $trans_footer_link_text . '</a>' ;
		return $unsublink ;
	}

}

if ( ! function_exists( 'fp_rac_get_template' ) ) {

	/**
	 *  Get the other templates from the themes/WordPress.
	 * 
	 * @return void
	 */
	function fp_rac_get_template( $template_name, $args = array() ) {

		wc_get_template( $template_name, $args, RAC_PLUGIN_FOLDER_NAME . '/', RAC_PLUGIN_PATH . '/templates/' ) ;
	}

}

if ( ! function_exists( 'fp_rac_get_template_html' ) ) {

	/**
	 *  Like dey_get_template, but returns the HTML instead of outputting.
	 *
	 *  @return mixed
	 */
	function fp_rac_get_template_html( $template_name, $args = array() ) {

		ob_start() ;
		fp_rac_get_template( $template_name, $args ) ;
		return ob_get_clean() ;
	}

}
