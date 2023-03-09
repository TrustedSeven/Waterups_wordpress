<?php

namespace AutomateWoo\Admin;

/**
 * AutomateWoo Analytics.
 * Formerly AutomateWoo > Reports.
 *
 * @since    x.x.x
 */
class Analytics {

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'setup' ) );
	}

	/**
	 * Setup Analytics.
	 * Add report items and register scripts.
	 */
	public static function setup() {
		if ( self::is_enabled() ) {
			// Analytics init.
			add_filter( 'woocommerce_analytics_report_menu_items', array( __CLASS__, 'add_report_menu_item' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_script' ) );
		}
	}

	/**
	 * Add "Bundles" as a Analytics submenu item.
	 *
	 * @param  array $report_pages  Report page menu items.
	 * @return array
	 */
	public static function add_report_menu_item( $report_pages ) {

		$report_pages[] = array(
			'id'     => 'automatewoo-analytics-runs-by-date',
			'title'  => '<automatewoo-icon aria-label="AutomateWoo"></automatewoo-icon>' . __( 'Workflows', 'automatewoo' ),
			'parent' => 'woocommerce-analytics',
			'path'   => '/analytics/automatewoo-runs-by-date',
		);
		$report_pages[] = array(
			'id'     => 'automatewoo-analytics-email-tracking',
			'title'  => '<automatewoo-icon aria-label="AutomateWoo"></automatewoo-icon>' . __( 'Email & SMS Tracking', 'automatewoo' ),
			'parent' => 'woocommerce-analytics',
			'path'   => '/analytics/automatewoo-email-tracking',
		);
		$report_pages[] = array(
			'id'     => 'automatewoo-analytics-conversions',
			'title'  => '<automatewoo-icon aria-label="AutomateWoo"></automatewoo-icon>' . __( 'Conversions', 'automatewoo' ),
			'parent' => 'woocommerce-analytics',
			'path'   => '/analytics/automatewoo-conversions',
		);
		$report_pages[] = array(
			'id'     => 'automatewoo-analytics-conversions-list',
			'title'  => '<automatewoo-icon aria-label="AutomateWoo"></automatewoo-icon>' . __( 'Conversions List', 'automatewoo' ),
			'parent' => 'woocommerce-analytics',
			'path'   => '/analytics/automatewoo-conversions-list',
		);
		return $report_pages;
	}

	/**
	 * Register analytics JS.
	 */
	public static function register_script() {
		$script_asset = require AW()->admin_path( '/assets/build/analytics.asset.php' );

		wp_register_script(
			'automatewoo-analytics',
			AW()->admin_assets_url( '/build/analytics.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Load JS translations.
		wp_set_script_translations( 'automatewoo-analytics', 'automatewoo', AW()->path( '/languages' ) );

		// Enqueue script.
		wp_enqueue_script( 'automatewoo-analytics' );
	}

	/**
	 * Whether or not the new Analytics reports are enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		// Hide the pages by default, until the feature is complete.
		// See https://github.com/woocommerce/automatewoo/issues/1152
		// $is_enabled = WC()->is_wc_admin_active();
		$is_enabled = false;

		/**
		 * Whether AutomateWoo's analytics reports should be added to the WooCommerce Analytics menu.
		 *
		 * @filter automatewoo/admin/analytics_enabled
		 */
		return (bool) apply_filters( 'automatewoo/admin/analytics_enabled', $is_enabled );
	}
}
