<?php

/*
 * Common functions for Woocommerce compatibility
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! function_exists( 'fp_rac_wc_format_price' ) ) {

	function fp_rac_wc_format_price( $price, $args = array() ) {
		if ( function_exists( 'wc_price' ) ) {
			return wc_price( $price , $args ) ;
		} else {
			return woocommerce_price( $price , $args ) ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_product' ) ) {

	function fp_rac_get_product( $product_id ) {
		if ( function_exists( 'wc_get_product' ) ) {
			return wc_get_product( $product_id ) ;
		} else {
			return get_product( $product_id ) ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_order_used_coupons' ) ) {

	function fp_rac_get_order_used_coupons( $order ) {
		$used_coupons = array() ;
		if ( ! $order || ! is_object( $order ) ) {
			return $used_coupons ;
		}

		if ( version_compare( WC_VERSION , '3.7.0' , '<=' ) ) {
			$used_coupons = $order->get_used_coupons() ;
		} else {
			$used_coupons = $order->get_coupon_codes() ;
		}

		return $used_coupons ;
	}

}

if ( ! function_exists( 'fp_rac_get_order_obj_data' ) ) {

	function fp_rac_get_order_obj_data( $order, $name ) {
		if ( $order && is_object( $order ) ) {
			if ( version_compare( WC_VERSION , '3.0.0' , '>=' ) ) {
				$date = false ;
				if ( 'modified_date' == $name ) {
					$date = true ;
					$get  = 'get_date_modified' ;
				} elseif ( 'order_date' == $name ) {
					$date = true ;
					$get  = 'get_date_created' ;
				} elseif ( 'order_total' == $name ) {
					$get = 'get_total' ;
				} else {
					$get = 'get_' . $name ;
				}
				$value = $order->$get() ;
				if ( $date ) {
					$value = wc_rest_prepare_date_response( $value , false ) ;
				}
				return $value ;
			} else {
				return $order->$name ;
			}
		} else {
			return '' ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_coupon_obj_data' ) ) {

	function fp_rac_get_coupon_obj_data( $coupon, $name ) {
		if ( version_compare( WC_VERSION , '3.0.0' , '>=' ) ) {
			$date = false ;
			if ( 'expiry_date' == $name ) {
				$date = true ;
				$get  = 'get_date_expires' ;
			} else {
				$get = 'get_' . $name ;
			}
			$value = $coupon->$get() ;
			if ( $date ) {
				$value = wc_rest_prepare_date_response( $value , false ) ;
			}
			return $value ;
		} else {
			return $coupon->$name ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_product_obj_data' ) ) {

	function fp_rac_get_product_obj_data( $product, $name ) {
		if ( version_compare( WC_VERSION , '3.0.0' , '>=' ) ) {
			if ( 'product_type' == $name ) {
				$get = 'get_type' ;
			} else {
				$get = 'get_' . $name ;
			}
			return $product->$get() ;
		} else {
			return $product->$name ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_order_obj' ) ) {

	function fp_rac_get_order_obj( $order_id ) {
		if ( function_exists( 'wc_get_order' ) ) {
			return wc_get_order( $order_id ) ;
		} else {
			$result = get_post_status( $order_id ) ;
			if ( false != $result ) {
				$result = new WC_Order( $order_id ) ;
			}
			return $result ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_email_order_item_table' ) ) {

	function fp_rac_get_email_order_item_table( $order, $args = array() ) {
		if ( version_compare( WC_VERSION , '3.0.0' , '>=' ) ) {
			return wc_get_email_order_items( $order ) ;
		} else {
			return $order->email_order_items_table( false , true ) ;
		}
	}

}

if ( ! function_exists( 'fp_rac_get_paid_statuses_of_order' ) ) {

	function fp_rac_get_paid_statuses_of_order() {
		if ( version_compare( WC_VERSION , '3.0.0' , '>=' ) ) {
			$statuses = wc_get_is_paid_statuses() ;
		} else {
			$statuses = array( 'processing' , 'completed' ) ;
		}
		return $statuses ;
	}

}

if ( ! function_exists( 'fp_rac_get_user_persistent_cart' ) ) {

	function fp_rac_get_user_persistent_cart( $user_id ) {
		return array( array( 'cart' => WC()->cart->get_cart_for_session() ) ) ;
	}

}
if ( ! function_exists( 'fp_rac_tool_tip' ) ) {

	function fp_rac_tool_tip( $tip, $allow_html = false, $echo = true ) {
		if ( ( float ) WC()->version >= ( float ) '2.5' ) {
			$return = wc_help_tip( $tip , $allow_html ) ;
		} else {
			$return = '<img class="help_tip" data-tip="' . fp_rac_sanitize_tooltip( $tip ) . '" src="' . WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />' ;
		}

		if ( ! $echo ) {
			return $return ;
		}

		echo wp_kses_post( $return ) ;
	}

}

if ( ! function_exists( 'fp_rac_sanitize_tooltip' ) ) {

	function fp_rac_sanitize_tooltip( $var ) {
		return htmlspecialchars(
				wp_kses(
						html_entity_decode( $var ) , array(
			'br'     => array() ,
			'em'     => array() ,
			'strong' => array() ,
			'small'  => array() ,
			'span'   => array() ,
			'ul'     => array() ,
			'li'     => array() ,
			'ol'     => array() ,
			'p'      => array() ,
						)
				)
				) ;
	}

}
