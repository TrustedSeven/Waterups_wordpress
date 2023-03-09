<?php

/**
 * Coupon Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Coupon_Tab' ) ) {
	return new RAC_Coupon_Tab() ;
}

if ( ! class_exists( 'RAC_Coupon_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Coupon_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'coupon' ;
			$this->label = __( 'Coupon in Email', 'recoverabandoncart' ) ;

			add_action( 'woocommerce_admin_field_rac_coupon_exclude_products', array( __CLASS__, 'rac_select_product_to_exclude' ) ) ;
			add_action( 'woocommerce_admin_field_rac_coupon_include_products', array( __CLASS__, 'rac_select_product_to_include' ) ) ;

			parent::__construct() ;
		}

		/**
		 * Get settings for the coupon section array.
		 * 
		 * @return array
		 */
		protected function coupon_section_array() {
			$section_fields = array() ;

			// Coupon creation settings section start.
			$section_fields[] = array(
				'name' => __( 'Coupon Code Creation Global Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_coupon_creation_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Coupon Creation Mode', 'recoverabandoncart' ),
				'id'       => 'rac_coupon_creation_mode',
				'type'     => 'select',
				'std'      => '1',
				'default'  => '1',
				'options'  => array(
					'default'  => __( 'Default', 'recoverabandoncart' ),
					'template' => __( 'Configure on Each Template', 'recoverabandoncart' ),
				),
				'desc_tip' => true,
				'desc'     => __( '"Default" - same coupon code will be sent in all templates unless the user has used the coupon. "Configure on Each Template" - coupons with different values can be configured for each template', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Coupon code Prefix Type', 'recoverabandoncart' ),
				'id'       => 'rac_prefix_coupon',
				'type'     => 'select',
				'std'      => '1',
				'default'  => '1',
				'options'  => array(
					'1' => __( 'Default', 'recoverabandoncart' ),
					'2' => __( 'Custom', 'recoverabandoncart' ),
				),
				'desc_tip' => true,
				'desc'     => __( 'Select Prefix Text in Coupon Code', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Coupon Code Prefix', 'recoverabandoncart' ),
				'id'       => 'rac_manual_prefix_coupon_code',
				'desc_tip' => true,
				'type'     => 'text',
				'std'      => '',
				'default'  => '',
				'desc'     => __( 'Enter Custom Prefix Text for Coupon Code', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Discount Type', 'recoverabandoncart' ),
				'id'       => 'rac_coupon_type',
				'css'      => 'min-width:150px;',
				'type'     => 'select',
				'std'      => 'fixed_cart',
				'default'  => 'fixed_cart',
				'options'  => array( 'fixed_cart' => __( 'Amount', 'recoverabandoncart' ), 'percent' => __( 'Percentage', 'recoverabandoncart' ) ),
				'desc_tip' => true,
				'desc'     => __( 'Please Select which type of discount should be applied', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Value', 'recoverabandoncart' ),
				'id'       => 'rac_coupon_value',
				'std'      => '',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
				'desc'     => __( 'Enter the value to reduce in currency or % based on the Type of Discount Selected without any Symbols', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Validity in Days', 'recoverabandoncart' ),
				'id'       => 'rac_coupon_validity',
				'std'      => '7',
				'default'  => '7',
				'type'     => 'text',
				'desc_tip' => true,
				'desc'     => __( 'Enter a value(days in number) for how long the Coupon should be Active', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Minimum Amount for Coupon Usage', 'recoverabandoncart' ),
				'id'      => 'rac_minimum_spend',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Maximum Amount for Coupon Usage', 'recoverabandoncart' ),
				'id'      => 'rac_maximum_spend',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Individual Use Only', 'recoverabandoncart' ),
				'id'      => 'rac_individual_use_only',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Check this box if the coupon cannot be used in conjunction with other coupons.', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Exclude sale items', 'recoverabandoncart' ),
				'id'      => 'rac_exclude_sale_items',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Allow Free Shipping', 'recoverabandoncart' ),
				'id'      => 'rac_coupon_allow_free_shipping',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Restrict Coupons to Issued Users', 'recoverabandoncart' ),
				'id'      => 'rac_coupon_allow_customer_email',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'When enabled, the issued Coupons can be used only by the users to whom it was issued.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'        => __( 'Include Products', 'recoverabandoncart' ),
				'type'        => 'rac_custom_fields',
				'rac_field'   => 'product_search',
				'id'          => 'rac_include_products_in_coupon',
				'std'         => array(),
				'default'     => array(),
				'placeholder' => __( 'Search for a product&hellip;', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'        => __( 'Exclude Products', 'recoverabandoncart' ),
				'type'        => 'rac_custom_fields',
				'rac_field'   => 'product_search',
				'id'          => 'rac_exclude_products_in_coupon',
				'std'         => array(),
				'default'     => array(),
				'placeholder' => __( 'Search for a product&hellip;', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Include Category', 'recoverabandoncart' ),
				'id'       => 'rac_select_category_to_enable_redeeming',
				'class'    => 'rac_select_category_to_enable_redeeming fp-rac-select-field',
				'css'      => 'min-width:350px',
				'std'      => '',
				'type'     => 'multiselect',
				'options'  => fp_rac_get_category(),
				'desc_tip' => true,
				'desc'     => __( 'Select the Categories to which the coupons from abandoned cart emails can be applied', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Exclude Category', 'recoverabandoncart' ),
				'id'       => 'rac_exclude_category_to_enable_redeeming',
				'class'    => 'rac_exclude_category_to_enable_redeeming fp-rac-select-field',
				'css'      => 'min-width:350px',
				'std'      => '',
				'type'     => 'multiselect',
				'options'  => fp_rac_get_category(),
				'desc_tip' => true,
				'desc'     => __( 'Select the Categories to which the coupons from abandoned cart emails cannot be applied', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_coupon_creation_settings'
					) ;
			// Coupon creation settings section end.
			// Coupon deletion settings section start.
			$section_fields[] = array(
				'name' => __( 'Coupon Code Deletion Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_coupon_deletion_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Delete Coupons once Used', 'recoverabandoncart' ),
				'id'      => 'rac_delete_coupon_after_use',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'If enabled, coupons which are automatically created by Recover Abandoned Cart plugin will be deleted once the coupon is used', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Delete Coupon when', 'recoverabandoncart' ),
				'id'      => 'rac_delete_coupon_after_use_based_on',
				'css'     => 'min-width:150px;',
				'type'    => 'select',
				'options' => array( '1' => __( 'User Place the Order', 'recoverabandoncart' ), '2' => __( 'Placed Order reaches Specific Status', 'recoverabandoncart' ) ),
				'std'     => '1',
				'default' => '1',
				'class'   => 'rac_delete_coupon_by',
					) ;
			$section_fields[] = array(
				'name'              => __( 'Delete Coupon when Order Status becomes', 'recoverabandoncart' ),
				'id'                => 'rac_delete_coupon_after_use_based_on_status',
				'css'               => 'min-width:350px',
				'std'               => array( 'completed' ),
				'default'           => array( 'completed' ),
				'type'              => 'multiselect',
				'options'           => fp_rac_get_order_status(),
				'custom_attributes' => array( 'required' => 'required' ),
				'class'             => 'rac_delete_coupon_by rac_delete_coupon_by_status fp-rac-select-field',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Delete Coupons once Expired', 'recoverabandoncart' ),
				'id'      => 'rac_delete_coupon_expired',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'If enabled, the coupons which created automatically by Recover Abandoned Cart plugin will be deleted once the coupon expired based on the validity configured. If the validity not configured(set as blank), then the coupon will not generate', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_coupon_deletion_settings'
					) ;
			// Coupon deletion settings section end.

			return apply_filters( 'woocommerce_rac_coupon_settings', $section_fields ) ;
		}

	}

}

return new RAC_Coupon_Tab() ;
