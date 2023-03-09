<?php

/**
 * Plugin Name: Recover Abandoned Cart
 * Plugin URI:
 * Description: Recover Abandoned Cart is a WooCommerce Extension Plugin which will help you Recover the Abandoned Carts and bring more sales.
 * Version: 23.8
 * Author: FantasticPlugins
 * Author URI: http://fantasticplugins.com
 * Text Domain: recoverabandoncart
 * Domain Path: /languages
 * 
 * WC tested up to: 6.6.1
 */
/*
  Copyright 2014 Fantastic Plugins. All Rights Reserved.
  This Software should not be used or changed without the permission
  of Fantastic Plugins.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

/* Include once will help to avoid fatal error by load the files when you call init hook */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ) ;

// Include main class file.
if ( ! class_exists( 'RecoverAbandonCart' ) ) {
	include_once( 'inc/class-recover-abandon-cart.php' ) ;
}

if ( ! function_exists( 'rac_is_plugin_active' ) ) {

	/**
	 * Is plugin active?
	 * 
	 * @return bool
	 */
	function rac_is_plugin_active() {
		if ( rac_is_valid_wordpress_version() && rac_is_woocommerce_active() && rac_is_valid_woocommerce_version() ) {
			return true ;
		}

		add_action( 'admin_notices', 'rac_display_warning_message' ) ;

		return false ;
	}

}

if ( ! function_exists( 'rac_is_woocommerce_active' ) ) {

	/**
	 * Function to check whether WooCommerce is active or not.
	 * 
	 * @return bool
	 */
	function rac_is_woocommerce_active() {
		$return = true ;
		// This condition is for multi site installation.
		if ( is_multisite() && ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$return = false ;
			// This condition is for single site installation.
		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$return = false ;
		}

		return $return ;
	}

}

if ( ! function_exists( 'rac_is_valid_wordpress_version' ) ) {

	/**
	 * Is valid WordPress version?
	 * 
	 * @return bool
	 */
	function rac_is_valid_wordpress_version() {
		if ( version_compare( get_bloginfo( 'version' ), RecoverAbandonCart::$wp_minimum_version, '<' ) ) {
			return false ;
		}

		return true ;
	}

}

if ( ! function_exists( 'rac_is_valid_woocommerce_version' ) ) {

	/**
	 * Is valid WooCommerce version?
	 * 
	 * @return bool
	 */
	function rac_is_valid_woocommerce_version() {
		if ( version_compare( get_option( 'woocommerce_version' ), RecoverAbandonCart::$wc_minimum_version, '<' ) ) {
			return false ;
		}

		return true ;
	}

}

if ( ! function_exists( 'rac_display_warning_message' ) ) {

	/**
	 * Display the WooCommere is not active warning message.
	 */
	function rac_display_warning_message() {
		$notice = '' ;

		if ( ! rac_is_valid_wordpress_version() ) {
			$notice = sprintf( 'This version of Recover Abandoned Cart requires WordPress %1s or newer.', RecoverAbandonCart::$wp_minimum_version ) ;
		} elseif ( ! rac_is_woocommerce_active() ) {
			$notice = 'Recover Abandoned Cart will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin.' ;
		} elseif ( ! rac_is_valid_woocommerce_version() ) {
			$notice = sprintf( 'This version of Recover Abandoned Cart requires WooCommerce %1s or newer.', RecoverAbandonCart::$wc_minimum_version ) ;
		}

		if ( $notice ) {
			echo '<div class="error">' ;
			echo '<p>' . wp_kses_post( $notice ) . '</p>' ;
			echo '</div>' ;
		}
	}

}

// Return if the plugin is not active.
if ( ! rac_is_plugin_active() ) {
	return ;
}

// Define constant.
if ( ! defined( 'RAC_PLUGIN_FILE' ) ) {
	define( 'RAC_PLUGIN_FILE', __FILE__ ) ;
}

// Return initiated recover abandon cart main class object.
if ( ! function_exists( 'RAC' ) ) {

	function RAC() {
		return RecoverAbandonCart::instance() ;
	}

}

// Initialize the plugin.
RAC() ;
