<?php

/**
 * Common functions.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! function_exists( 'fp_rac_page_screen_ids' ) ) {

	/**
	 * Get the page screen IDs.
	 *
	 * @return array
	 */
	function fp_rac_page_screen_ids() {
		return apply_filters(
				'fp_rac_page_screen_ids', array(
			'raccartlist',
			'racmaillog',
			'racemailtemplate',
			'racrecoveredorder',
			'raccartlist_page_rac_reports',
			'raccartlist_page_rac_settings',
			'dashboard_page_recover-abandoned-cart-welcome-page'
				)
				) ;
	}

}

if ( ! function_exists( 'rac_get_allowed_setting_tabs' ) ) {

	/**
	 * Get the setting tabs.
	 *
	 * @return array
	 */
	function rac_get_allowed_setting_tabs() {

		return apply_filters( 'rac_settings_tabs_array', array() ) ;
	}

}

if ( ! function_exists( 'rac_get_settings_page_url' ) ) {

	/**
	 * Get the settings page link.
	 *
	 * @return URL
	 */
	function rac_get_settings_page_url( $args = array() ) {

		$url = add_query_arg( array( 'post_type' => 'raccartlist', 'page' => 'rac_settings' ), admin_url( 'edit.php' ) ) ;

		if ( rac_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url ) ;
		}

		return $url ;
	}

}

if ( ! function_exists( 'rac_get_reports_page_url' ) ) {

	/**
	 * Get the reports page link.
	 *
	 * @return URL
	 */
	function rac_get_reports_page_url( $args = array() ) {

		$url = add_query_arg( array( 'page' => 'rac_reports' ), admin_url( 'admin.php' ) ) ;

		if ( rac_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url ) ;
		}

		return $url ;
	}

}

if ( ! function_exists( 'fp_rac_wp_user_roles' ) ) {

	/**
	 * Get the WP user roles.
	 * 
	 * @return array
	 */
	function fp_rac_wp_user_roles() {
		static $user_roles ;
		if ( isset( $user_roles ) ) {
			return $user_roles ;
		}

		global $wp_roles ;
		$user_roles = array() ;
		if ( ! isset( $wp_roles->roles ) || ! rac_check_is_array( $wp_roles->roles ) ) {
			return $user_roles ;
		}

		foreach ( $wp_roles->roles as $slug => $role ) {
			$user_roles[ $slug ] = $role[ 'name' ] ;
		}

		return $user_roles ;
	}

}


if ( ! function_exists( 'fp_rac_user_roles' ) ) {

	/**
	 * Get the user roles.
	 * 
	 * @return array
	 */
	function fp_rac_user_roles() {
		static $user_roles ;
		if ( isset( $user_roles ) ) {
			return $user_roles ;
		}

		$user_roles = array_merge( fp_rac_wp_user_roles(), array( 'rac_guest' => __( 'Guest', 'woocommerce' ) ) ) ;

		return $user_roles ;
	}

}

if ( ! function_exists( 'fp_rac_get_server_name' ) ) {

	/**
	 * Get the server name.
	 * 
	 * @return array
	 */
	function fp_rac_get_server_name() {
		return isset( $_SERVER[ 'SERVER_NAME' ] ) ? wc_clean( wp_unslash( $_SERVER[ 'SERVER_NAME' ] ) ) : '' ;
	}

}

if ( ! function_exists( 'fp_rac_get_category' ) ) {

	/**
	 * Get the product categories.
	 * 
	 * @return array
	 */
	function fp_rac_get_category() {
		static $categories ;
		if ( isset( $categories ) ) {
			return $categories ;
		}

		$categories    = array() ;
		$wc_categories = get_terms( 'product_cat' ) ;

		if ( ! rac_check_is_array( $wc_categories ) ) {
			return $categories ;
		}

		foreach ( $wc_categories as $category ) {
			$categories[ $category->term_id ] = $category->name ;
		}

		return $categories ;
	}

}

if ( ! function_exists( 'fp_rac_select_options' ) ) {

	/**
	 * Prepre the select options.
	 * 
	 * @return string
	 */
	function fp_rac_select_options( $select_array, $selected_value = false ) {
		$option = '' ;
		if ( rac_check_is_array( $select_array ) ) {
			foreach ( $select_array as $key => $value ) {
				$selected = '' ;
				if ( rac_check_is_array( $selected_value ) ) {
					if ( in_array( $key, $selected_value ) ) {
						$selected = 'selected=selected' ;
					}
				} else {
					if ( $selected_value && $selected_value == $key ) {
						$selected = 'selected=selected' ;
					}
				}
				$option .= '<option value=' . $key . ' ' . $selected . '>' . $value . '</option>' ;
			}
		}

		return $option ;
	}

}

if ( ! function_exists( 'rac_format_custom_attributes' ) ) {

	/**
	 * Format Custom Attributes.
	 *
	 * @return array
	 */
	function rac_format_custom_attributes( $value ) {
		$custom_attributes = array() ;

		if ( ! empty( $value[ 'custom_attributes' ] ) && is_array( $value[ 'custom_attributes' ] ) ) {
			foreach ( $value[ 'custom_attributes' ] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '' ;
			}
		}

		return $custom_attributes ;
	}

}
