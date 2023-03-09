<?php

/**
 * Shortcode Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Shortcode_Tab' ) ) {
	return new RAC_Shortcode_Tab() ;
}

if ( ! class_exists( 'RAC_Shortcode_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Shortcode_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id           = 'shortcode' ;
			$this->label        = __( 'Shortcodes', 'recoverabandoncart' ) ;
			$this->show_buttons = false ;

			add_action( 'woocommerce_admin_field_rac_shortcodes_information', array( __CLASS__, 'shortcodes_information' ) ) ;

			parent::__construct() ;
		}

		/**
		 * Get settings for the shortcode section array.
		 * 
		 * @return array
		 */
		protected function shortcode_section_array() {
			$section_fields   = array() ;
			// Shortcodes section start.
			$section_fields[] = array(
				'type'  => 'title',
				'title' => __( 'Shortcodes', 'recoverabandoncart' ),
				'id'    => 'rac_shortcodes_options',
					) ;
			$section_fields[] = array(
				'type' => 'rac_shortcodes_information',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_shortcodes_options',
					) ;
			// Shortcodes section end.

			return apply_filters( 'woocommerce_fpracshortocode_settings', $section_fields ) ;
		}

		/**
		 * Display the shortcode information.
		 * 
		 * @retrun void
		 * */
		public static function shortcodes_information() {
			$shortcodes_info = array(
				'{rac.productname}'              => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Displays the product name in the email subject', 'recoverabandoncart' )
				),
				'{rac.firstname}'                => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows receiver first name', 'recoverabandoncart' )
				),
				'{rac.lastname}'                 => array( 'position' => __(
							'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows receiver last name', 'recoverabandoncart' )
				),
				'{rac.date}'                     => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows abandoned cart date', 'recoverabandoncart' )
				),
				'{rac.time}'                     => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows abandoned cart time', 'recoverabandoncart' )
				),
				'{rac.cartlink}'                 => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Abandoned cart can be loaded using this link from email', 'recoverabandoncart' )
				),
				'{rac.Productinfo}'              => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows product info table', 'recoverabandoncart' )
				),
				'{rac.coupon}'                   => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Coupon code will be generated automatically and included in the email with a coupon options based on the settings from Coupon In Email tab', 'recoverabandoncart' )
				),
				'{rac.coupon_expired_date}'      => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'The expiry date of the coupon code generated based on the settings from Coupon In Email tab', 'recoverabandoncart' )
				),
				'{rac.unsubscribe}'              => array(
					'position' => __( 'Abandoned cart email', 'recoverabandoncart' ),
					'usage'    => __( 'Shows unsubscribe link', 'recoverabandoncart' )
				),
				'{rac.recovered_order_id}'       => array(
					'position' => __( 'Admin order recovered notification email', 'recoverabandoncart' ),
					'usage'    => __( 'Order ID can be inserted in the admin notification email for reference', 'recoverabandoncart' )
				),
				'{rac.order_line_items}'         => array(
					'position' => __( 'Admin order line items in recovered notification email', 'recoverabandoncart' ),
					'usage'    => __( 'Order line items will be displayed in admin notification email for information', 'recoverabandoncart' )
				),
				'{rac.unsubscribe_email_manual}' => array(
					'position' => __( 'Pages', 'recoverabandoncart' ),
					'usage'    => __( 'Manual unsubscription of abandon cart emails done in this page', 'recoverabandoncart' )
				)
					) ;

			include_once( RAC_PLUGIN_PATH . '/inc/admin/menu/views/html-shortcodes-info.php' ) ;
		}

	}

}

return new RAC_Shortcode_Tab() ;
