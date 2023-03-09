<?php
/**
 * General Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_General_Tab' ) ) {
	return new RAC_General_Tab() ;
}

if ( ! class_exists( 'RAC_General_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_General_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general' ;
			$this->label = __( 'General', 'recoverabandoncart' ) ;

			add_action( 'update_option_rac_abandon_cron_time', array( __CLASS__, 'reschedule_cron_job' ) ) ;
			add_action( 'update_option_rac_abandon_cart_cron_type', array( __CLASS__, 'reschedule_cron_job' ) ) ;
			add_action( 'woocommerce_admin_field_rac_test_email_sections', array( __CLASS__, 'test_email' ) ) ;
			add_action( 'woocommerce_admin_field_rac_cron_job_information', array( __CLASS__, 'rac_cron_job_information' ) ) ;
			add_action( 'woocommerce_admin_field_rac_drag_drop_product_info', array( __CLASS__, 'product_info_table_column_positioning' ) ) ;

			parent::__construct() ;
		}

		/**
		 * Get the sections.
		 * 
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'general'          => __( 'General', 'recoverabandoncart' ),
				'cartlist'         => __( 'Cart List', 'recoverabandoncart' ),
				'email'            => __( 'Email', 'recoverabandoncart' ),
				'email_template'   => __( 'Email Template', 'recoverabandoncart' ),
				'cartlist_recover' => __( 'Cart List Recovery', 'recoverabandoncart' ),
				'guest_popup'      => __( 'Guest Popup', 'recoverabandoncart' ),
				'unsubscription'   => __( 'Unsubscription', 'recoverabandoncart' )
					) ;

			return apply_filters( $this->get_plugin_slug() . '_get_sections_' . $this->get_id(), $sections ) ;
		}

		/**
		 * Get settings for the general section array.
		 * 
		 * @return array
		 */
		protected function general_section_array() {
			$section_fields = array() ;

			// Time settings section start.
			$section_fields[] = array(
				'name' => __( 'Time Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_time_settings',
					) ;
			$section_fields[] = array(
				'title'     => __( 'Time to consider Cart as Abandoned for Members', 'recoverabandoncart' ),
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_value',
				'default'   => 1,
				'std'       => 1,
				'id'        => 'rac_abandon_cart_time',
				'desc_tip'  => true,
				'desc'      => __( 'This setting controls the minimum waiting time for members after which a cart will be considered as abandoned.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_type',
				'default'   => 'hours',
				'std'       => 'hours',
				'id'        => 'rac_abandon_cart_time_type',
				'options'   => array(
					'minutes' => __( 'Minutes', 'recoverabandoncart' ),
					'hours'   => __( 'Hours', 'recoverabandoncart' ),
					'days'    => __( 'Days', 'recoverabandoncart' )
				),
					) ;
			$section_fields[] = array(
				'title'     => __( 'Time to consider Cart as Abandoned for Guests', 'recoverabandoncart' ),
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_value',
				'default'   => 1,
				'std'       => 1,
				'id'        => 'rac_abandon_cart_time_guest',
				'desc_tip'  => true,
				'desc'      => __( 'This setting controls the minimum waiting time for guests after which a cart will be considered as abandoned.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_type',
				'default'   => 'hours',
				'std'       => 'hours',
				'id'        => 'rac_abandon_cart_time_type_guest',
				'options'   => array(
					'minutes' => __( 'Minutes', 'recoverabandoncart' ),
					'hours'   => __( 'Hours', 'recoverabandoncart' ),
					'days'    => __( 'Days', 'recoverabandoncart' )
				),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_time_settings'
					) ;
			// Time settings section end.
			// Email cron settings section start.
			$section_fields[] = array(
				'name' => __( 'Email Cron Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_cron_settings',
					) ;
			$section_fields[] = array(
				'title'     => __( 'Email Cron Job Running Time', 'recoverabandoncart' ),
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_value',
				'default'   => 1,
				'std'       => 1,
				'id'        => 'rac_abandon_cron_time',
				'desc_tip'  => true,
				'desc'      => __( 'This setting controls the recurrence duration of cron job to run. Used for sending automatic abandoned cart emails. Note: Set to a lesser duration in order to send emails more frequently.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_type',
				'default'   => 'hours',
				'std'       => 'hours',
				'id'        => 'rac_abandon_cart_cron_type',
				'options'   => array(
					'minutes' => __( 'Minutes', 'recoverabandoncart' ),
					'hours'   => __( 'Hours', 'recoverabandoncart' ),
					'days'    => __( 'Days', 'recoverabandoncart' )
				),
					) ;
			$section_fields[] = array(
				'type' => 'rac_cron_job_information'
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_cron_settings'
					) ;
			// Email cron settings section end.

			$section_fields[] = array(
				'type' => 'rac_test_email_sections',
					) ;

			return apply_filters( 'woocommerce_fpracgeneral_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the cart list section array.
		 * 
		 * @return array
		 */
		protected function cartlist_section_array() {
			$section_fields          = array() ;
			$default_cartlist_member = 'no' ;
			$default_cartlist_guest  = 'no' ;
			if ( get_option( 'rac_abandon_cart_time' ) ) {
				$default_cartlist_member = get_option( 'rac_allow_user_cartlist' ) ? 'no' : 'yes' ;
				$default_cartlist_guest  = get_option( 'rac_allow_guest_cartlist' ) ? 'no' : 'yes' ;
			}

			// Cartlist settings section start.
			$section_fields[] = array(
				'name' => __( 'Cart List Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_cartlist_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Capture Carts of Logged-In Users', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => $default_cartlist_member,
				'std'     => $default_cartlist_member,
				'id'      => 'rac_allow_user_cartlist',
				'desc'    => __( 'Enabling this option will capture abandoned carts of logged-in users', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cart Capture Display Notice for Members', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Hide', 'recoverabandoncart' ),
					'yes' => __( 'Show', 'recoverabandoncart' ),
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_user_notice_display',
				'class'   => 'rac_user_notice_info',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Pages to Display', 'recoverabandoncart' ),
				'id'       => 'rac_user_pages_for_disp_notice',
				'type'     => 'multiselect',
				'css'      => 'min-width:150px',
				'options'  => array(
					'shop'     => __( 'Shop Page', 'recoverabandoncart' ),
					'cart'     => __( 'Cart Page', 'recoverabandoncart' ),
					'checkout' => __( 'Checkout Page', 'recoverabandoncart' ),
					'product'  => __( 'Single Product Page', 'recoverabandoncart' ),
					'category' => __( 'Category Page', 'recoverabandoncart' )
				),
				'std'      => array( 'shop', 'cart', 'checkout', 'product', 'category' ),
				'default'  => array( 'shop', 'cart', 'checkout', 'product', 'category' ),
				'class'    => 'rac_user_notice_info fp-rac-select-field',
				'desc_tip' => true,
				'desc'     => __( 'Enter the first three characters of the locations you wish to display the notice.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'User Notice Message', 'recoverabandoncart' ),
				'id'       => 'rac_user_notice_msg',
				'type'     => 'textarea',
				'newids'   => 'rac_user_notice_msg',
				'class'    => 'rac_user_notice_info',
				'std'      => 'Your email will be used for sending Abandoned Cart emails',
				'default'  => 'Your email will be used for sending Abandoned Cart emails',
				'desc_tip' => true,
				'desc'     => __( 'This notice will be displayed on the locations selected above.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Capture Carts of Guests', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => $default_cartlist_guest,
				'std'     => $default_cartlist_guest,
				'id'      => 'rac_allow_guest_cartlist',
				'desc'    => __( 'Enabling this option will capture abandoned carts of guests', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cart Capture Display Notice for Guests', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Hide', 'recoverabandoncart' ),
					'yes' => __( 'Show', 'recoverabandoncart' ),
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_guest_notice_display',
				'class'   => 'rac_guest_notice_info',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Pages to Display', 'recoverabandoncart' ),
				'id'       => 'rac_guest_pages_for_disp_notice',
				'class'    => 'rac_guest_notice_info fp-rac-select-field',
				'type'     => 'multiselect',
				'css'      => 'min-width:153px',
				'options'  => array(
					'shop'           => __( 'Shop Page', 'recoverabandoncart' ),
					'cart'           => __( 'Cart Page', 'recoverabandoncart' ),
					'checkout'       => __( 'Checkout Page', 'recoverabandoncart' ),
					'product'        => __( 'Single Product Page', 'recoverabandoncart' ),
					'category'       => __( 'Category Page', 'recoverabandoncart' ),
					'popup'          => __( 'Popup Window Email', 'recoverabandoncart' ),
					'checkout_email' => __( 'Checkout Page Email', 'recoverabandoncart' )
				),
				'std'      => array( 'shop', 'cart', 'checkout', 'product', 'category', 'popup', 'checkout_email' ),
				'default'  => array( 'shop', 'cart', 'checkout', 'product', 'category', 'popup', 'checkout_email' ),
				'desc_tip' => true,
				'desc'     => __( 'Enter the first three characters of the locations you wish to display the notice.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Guest Notice Message', 'recoverabandoncart' ),
				'id'       => 'rac_guest_notice_msg',
				'class'    => 'rac_guest_notice_info',
				'type'     => 'textarea',
				'std'      => 'Your email will be used for sending Abandoned Cart emails',
				'default'  => 'Your email will be used for sending Abandoned Cart emails',
				'desc_tip' => true,
				'desc'     => __( 'This notice will be displayed on the locations selected above.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'When Multiple Cart Entries are about to be Captured for the Same Email ID then', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'       => __( 'Capture all the carts', 'recoverabandoncart' ),
					'yes'      => __( 'Remove old carts and capture new cart', 'recoverabandoncart' ),
					'pre_cart' => __( "Don't capture any new cart", 'recoverabandoncart' )
				),
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_remove_carts',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Carts with "NEW" Status', 'recoverabandoncart' ),
				'desc'    => __( 'Enabling this option will remove New Carts by same Users', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_remove_new',
				'class'   => 'rac_remove_hide rac_remove_status_yes',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Carts with "ABANDON" Status', 'recoverabandoncart' ),
				'desc'    => __( 'Enabling this option will remove Abandon Carts by same Users', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_remove_abandon',
				'class'   => 'rac_remove_hide rac_remove_status_yes',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Restrict Cart List Capturing when Old Carts of the User is in any One of the Following Status', 'recoverabandoncart' ),
				'id'       => 'rac_dont_capture_for_option',
				'class'    => 'rac_dont_capture_for_option rac_remove_hide rac_remove_status_pre_cart fp-rac-select-field',
				'type'     => 'multiselect',
				'css'      => 'min-width:153px',
				'options'  => array(
					'NEW'       => __( 'NEW', 'recoverabandoncart' ),
					'ABANDON'   => __( 'ABANDON', 'recoverabandoncart' ),
					'RECOVERED' => __( 'RECOVERED', 'recoverabandoncart' )
				),
				'std'      => array( 'NEW', 'ABANDON' ),
				'default'  => array( 'NEW', 'ABANDON' ),
				'desc_tip' => true,
					) ;
			$section_fields[] = array(
				'name'    => __( 'Create an Entry in Cart List Table when the Order reaches "Failed" status', 'recoverabandoncart' ),
				'desc'    => __( 'If enabled, an entry will be added in "Cart List" when the order reaches "Failed" status', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_insert_abandon_cart_when_order_failed',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Create an Entry in Cart List Table when the User Cancels the Order from the Third Party Payment Page', 'recoverabandoncart' ),
				'desc'    => __( 'If enabled, an entry will be added in "Cart List" when the user cancels the order from the third party payment pages', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_insert_abandon_cart_when_os_cancelled',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_cartlist_settings'
					) ;
			// Cartlist settings section end.
			// Guest checkout GDPR settings section start.
			$section_fields[] = array(
				'name' => __( 'Guest Checkout GDPR Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_guest_checkout_gdpr_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Enable GDPR', 'recoverabandoncart' ),
				'id'      => 'rac_guest_checkout_gdpr_enabled',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
					) ;
			$section_fields[] = array(
				'name'    => __( 'GDPR Content', 'recoverabandoncart' ),
				'id'      => 'rac_guest_checkout_gdpr_field_content',
				'std'     => 'Do not store my data for future follow-ups',
				'default' => 'Do not store my data for future follow-ups',
				'type'    => 'textarea',
				'css'     => 'min-height:100px;min-width:400px;',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_checkout_gdpr_settings'
					) ;
			// Guest checkout GDPR settings section end.
			// Cartlist restriction settings section start.
			$section_fields[] = array(
				'name' => __( 'Restriction Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_cartlist_restriction_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Prevent Adding Additional Cart Entry when the Order is Cancelled by the Same User', 'recoverabandoncart' ),
				'desc'    => __( 'Enabling this option will Prevent adding "New" cart when order cancelled in cart page', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_prevent_entry_in_cartlist_while_order_cancelled_in_cart_page',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Restriction Settings', 'recoverabandoncart' ),
				'id'       => 'custom_restrict',
				'type'     => 'select',
				'css'      => 'min-width:153px',
				'options'  => array(
					'user_role'      => __( 'User Role', 'recoverabandoncart' ),
					'name'           => __( 'Name', 'recoverabandoncart' ),
					'mail_id'        => __( 'Email ID', 'recoverabandoncart' ),
					'email_provider' => __( 'Email Provider', 'recoverabandoncart' ),
					'ip_address'     => __( 'IP Address', 'recoverabandoncart' )
				),
				'std'      => 'user_role',
				'default'  => 'user_role',
				'desc_tip' => true,
				'desc'     => __( 'Cart List Entry Capturing Restriction based on the following option(s)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cart List Capturing is', 'recoverabandoncart' ),
				'id'      => 'custom_include_exclude_entry',
				'type'    => 'select',
				'css'     => 'min-width:153px',
				'options' => array(
					'exclude' => __( 'Not allowed for selected option', 'recoverabandoncart' ),
					'include' => __( 'Allowed for selected option', 'recoverabandoncart' ),
				),
				'std'     => 'exclude',
				'default' => 'exclude',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Select User Role', 'recoverabandoncart' ),
				'id'       => 'custom_user_role_for_restrict_in_cart_list',
				'type'     => 'multiselect',
				'std'      => '',
				'default'  => '',
				'css'      => 'min-width:150px',
				'options'  => fp_rac_wp_user_roles(),
				'class'    => 'rac_cart_sh_class rac_show_user_role fp-rac-select-field',
				'desc_tip' => true,
				'desc'     => __( 'Enter the First Three Characters of User Role', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'        => __( 'Select Users', 'recoverabandoncart' ),
				'type'        => 'rac_custom_fields',
				'rac_field'   => 'customer_search',
				'id'          => 'custom_user_name_select_for_restrict_in_cart_list',
				'css'         => 'min-width:400px',
				'std'         => array(),
				'default'     => array(),
				'placeholder' => __( 'Search for a customer&hellip;', 'recoverabandoncart' ),
				'desc_tip'    => true,
				'desc'        => __( 'Enter the First Three Characters of User Name', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Email ID Selected', 'recoverabandoncart' ),
				'id'       => 'custom_mailid_for_restrict_in_cart_list',
				'type'     => 'textarea',
				'std'      => '',
				'css'      => 'min-width:500px;min-height:200px',
				'class'    => 'rac_cart_sh_class rac_show_mail_id',
				'desc_tip' => true,
				'desc'     => __( 'Enter Email ID per line which will be restricted to includes an entry in Cart List', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Email Provider Selected', 'recoverabandoncart' ),
				'id'       => 'custom_email_provider_for_restrict_in_cart_list',
				'type'     => 'textarea',
				'css'      => 'min-width:500px;min-height:200px',
				'std'      => '',
				'class'    => 'rac_cart_sh_class rac_show_email_provider',
				'desc_tip' => true,
				'desc'     => __( 'Enter Mail ID per line which will be restricted to includes an entry in Cart List', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom IP Address Selected', 'recoverabandoncart' ),
				'id'       => 'custom_ip_address_for_restrict_in_cart_list',
				'type'     => 'textarea',
				'std'      => '',
				'css'      => 'min-width:500px;min-height:200px',
				'class'    => 'rac_cart_sh_class rac_show_ip_address',
				'desc_tip' => true,
				'desc'     => __( 'Enter IP Address per line which will be restricted to includes an entry in Cart List', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_cartlist_restriction_settings'
					) ;
			// Cartlist restriction settings section end.
			// Cartlist deletion settings section start.
			$section_fields[] = array(
				'name' => __( 'Deletion Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_cartlist_deletion_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Remove Carts after x Days', 'recoverabandoncart' ),
				'id'       => 'enable_remove_abandon_after_x_days',
				'type'     => 'select',
				'css'      => 'min-width:153px',
				'options'  => array( 'yes' => __( 'Yes', 'recoverabandoncart' ), 'no' => __( 'No', 'recoverabandoncart' ) ),
				'std'      => 'no',
				'default'  => 'no',
				'desc_tip' => true,
				'desc'     => __( 'If "Yes" is selected, you can remove captured carts with specific status after specific number of days', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'              => __( 'Remove Carts after', 'recoverabandoncart' ),
				'id'                => 'rac_remove_abandon_after_x_days',
				'type'              => 'number',
				'std'               => '30',
				'default'           => '30',
				'custom_attributes' => array( 'min' => '1' ),
				'desc'              => __( 'day(s)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'              => __( 'Remove Carts with the Following Status', 'recoverabandoncart' ),
				'id'                => 'rac_delete_cart_selection',
				'class'             => 'fp-rac-select-field',
				'type'              => 'multiselect',
				'css'               => 'min-width:153px',
				'options'           => array(
					'rac-cart-abandon'   => __( 'ABANDON', 'recoverabandoncart' ),
					'rac-cart-recovered' => __( 'RECOVERED', 'recoverabandoncart' )
				),
				'std'               => array( 'rac-cart-abandon' ),
				'default'           => array( 'rac-cart-abandon' ),
				'custom_attributes' => array( 'required' => 'required' ),
				'desc_tip'          => true,
				'desc'              => __( 'carts will be removed from cart list table based on selected status', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_cartlist_deletion_settings'
					) ;
			// Cartlist deletion settings section end.

			return apply_filters( 'woocommerce_rac_cartlist_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the email section array.
		 * 
		 * @return array
		 */
		protected function email_section_array() {
			$section_fields = array() ;

			// Email settings section start.
			$section_fields[] = array(
				'name' => __( 'Email Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Send Email to Members', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_email_use_members',
				'desc'    => __( 'If enabled, emails will be send to members', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Send Email to Guests', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_email_use_guests',
				'desc'    => __( 'If enabled, emails will be send to guests', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Sending Method', 'recoverabandoncart' ),
				'id'      => 'rac_mail_template_send_method',
				'type'    => 'select',
				'css'     => 'min-width:153px',
				'options' => array( 'abandon_time' => __( 'Based on abandoned cart time', 'recoverabandoncart' ), 'template_time' => __( 'Based on previous email sent time', 'recoverabandoncart' ) ),
				'std'     => 'abandon_time',
				'default' => 'abandon_time',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Sending Priority ', 'recoverabandoncart' ),
				'id'      => 'rac_mail_template_sending_priority',
				'type'    => 'select',
				'css'     => 'min-width:153px',
				'options' => array( 'mailduration' => __( 'Email duration', 'recoverabandoncart' ), 'mailsequence' => __( 'Email sequence', 'recoverabandoncart' ) ),
				'std'     => 'mailduration',
				'default' => 'mailduration',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_settings'
					) ;
			// Email settings section end.
			// Email restriction settings section start.
			$section_fields[] = array(
				'name' => __( 'Restriction Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_restriction_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Restrict Automatic Abandoned Cart Emails if Captured Cart Contains a Product which was already Purchased', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_email_restrict_when_cutomer_already_bought_product',
				'desc'    => __( 'If enabled, automatic abandoned cart emails will not be sent if captured cart contains a product which was already purchased', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Restriction Settings', 'recoverabandoncart' ),
				'id'       => 'custom_exclude',
				'type'     => 'select',
				'css'      => 'min-width:153px',
				'options'  => array(
					'user_role'      => __( 'User Role', 'recoverabandoncart' ),
					'name'           => __( 'Name', 'recoverabandoncart' ),
					'mail_id'        => __( 'Email ID', 'recoverabandoncart' ),
					'email_provider' => __( 'Email Provider', 'recoverabandoncart' )
				),
				'std'      => 'user_role',
				'default'  => 'user_role',
				'desc_tip' => true,
				'desc'     => __( 'Email Sending Restriction for the Captured Cart List entry based on the following option(s)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Abandoned Cart Emails are', 'recoverabandoncart' ),
				'id'      => 'custom_include_exclude_email',
				'type'    => 'select',
				'css'     => 'min-width:153px',
				'options' => array(
					'exclude' => __( 'Not sent for the selected option', 'recoverabandoncart' ),
					'include' => __( 'Sent for the selected option', 'recoverabandoncart' ),
				),
				'std'     => 'exclude',
				'default' => 'exclude',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Select User Role', 'recoverabandoncart' ),
				'id'      => 'custom_user_role',
				'css'     => 'min-width:150px',
				'type'    => 'multiselect',
				'std'     => '',
				'default' => '',
				'options' => fp_rac_user_roles(),
				'class'   => 'rac_email_sh_class rac_show_email_setting_user_role fp-rac-select-field',
					) ;
			$section_fields[] = array(
				'name'        => __( 'User Name Selected', 'recoverabandoncart' ),
				'id'          => 'custom_user_name_select',
				'type'        => 'rac_custom_fields',
				'rac_field'   => 'customer_search',
				'css'         => 'min-width:400px',
				'std'         => array(),
				'default'     => array(),
				'placeholder' => __( 'Search for a customer&hellip;', 'recoverabandoncart' ),
				'desc_tip'    => true,
				'desc'        => __( 'Enter the First Three Character of User Name', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Mail ID Selected', 'recoverabandoncart' ),
				'id'       => 'custom_mailid_edit',
				'type'     => 'textarea',
				'std'      => '',
				'default'  => '',
				'css'      => 'min-width:300px',
				'class'    => 'rac_email_sh_class rac_show_email_setting_mail_id',
				'desc_tip' => true,
				'desc'     => __( 'Enter Mail ID per line which will be excluded to receive a mail from Recover Abandon Cart', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Restrict Email Providers', 'recoverabandoncart' ),
				'id'       => 'custom_email_provider_edit',
				'type'     => 'textarea',
				'std'      => '',
				'default'  => '',
				'css'      => 'min-width:300px',
				'class'    => 'rac_email_sh_class rac_show_email_setting_email_provider',
				'desc_tip' => true,
				'desc'     => __( 'Enter the email providers seperated by comma. Do not enter any special characters', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_restriction_settings'
					) ;
			// Email restriction  settings section end.
			// Email log deletion settings section start.
			$section_fields[] = array(
				'name' => __( 'Deletion Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_log_deletion_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Remove email log(s) after x Days', 'recoverabandoncart' ),
				'id'       => 'enable_remove_email_log_after_x_days',
				'type'     => 'select',
				'css'      => 'min-width:153px',
				'options'  => array( 'yes' => __( 'Yes', 'recoverabandoncart' ), 'no' => __( 'No', 'recoverabandoncart' ) ),
				'std'      => 'no',
				'default'  => 'no',
				'desc_tip' => true,
				'desc'     => __( 'If "Yes" is selected, you can remove email log(s) after specific number of days', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'              => __( 'Remove email log(s) after', 'recoverabandoncart' ),
				'id'                => 'rac_remove_email_log_after_x_days',
				'type'              => 'number',
				'std'               => '30',
				'default'           => '30',
				'custom_attributes' => array( 'min' => '1' ),
				'desc'              => __( 'day(s)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_log_deletion_settings'
					) ;
			// Email log deletion settings section end.
			// Admin notification settings section start.
			$section_fields[] = array(
				'name' => __( 'Admin Notification Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_admin_notification_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Notify Admin by Email when an Order is Recovered', 'recoverabandoncart' ),
				'id'      => 'rac_admin_cart_recovered_noti',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Admin Email IDs', 'recoverabandoncart' ),
				'id'       => 'rac_admin_email',
				'std'      => get_option( 'admin_email' ),
				'default'  => get_option( 'admin_email' ),
				'type'     => 'textarea',
				'class'    => 'admin_notification',
				'desc_tip' => true,
				'desc'     => __( 'Enter the email IDs separated by comma', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Sender Option', 'recoverabandoncart' ),
				'id'      => 'rac_recovered_sender_opt',
				'std'     => 'woo',
				'default' => 'woo',
				'type'    => 'radio',
				'options' => array(
					'woo'   => __( 'WooCommerce', 'recoverabandoncart' ),
					'local' => __( 'Local', 'recoverabandoncart' )
				),
				'class'   => 'admin_notifi_sender_opt'
					) ;
			$section_fields[] = array(
				'name'    => __( 'From Name', 'recoverabandoncart' ),
				'id'      => 'rac_recovered_from_name',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
				'class'   => 'local_senders admin_notification'
					) ;
			$section_fields[] = array(
				'name'    => __( 'From Email', 'recoverabandoncart' ),
				'id'      => 'rac_recovered_from_email',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
				'class'   => 'local_senders admin_notification'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Subject', 'recoverabandoncart' ),
				'id'      => 'rac_recovered_email_subject',
				'std'     => 'A cart has been Recovered',
				'default' => 'A cart has been Recovered',
				'type'    => 'text',
				'class'   => 'admin_notification'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Message', 'recoverabandoncart' ),
				'id'      => 'rac_recovered_email_message',
				'css'     => 'min-height:250px;min-width:400px;',
				'std'     => 'A cart has been Recovered. Here is the order ID {rac.recovered_order_id} for Reference and Line Items is here {rac.order_line_items}.',
				'default' => 'A cart has been Recovered. Here is the order ID {rac.recovered_order_id} for Reference and Line Items is here {rac.order_line_items}.',
				'type'    => 'textarea',
				'class'   => 'admin_notification'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Notify Admin by Email when the cart is abandoned', 'recoverabandoncart' ),
				'id'      => 'rac_admin_cart_abandoned_noti',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Admin Email IDs', 'recoverabandoncart' ),
				'id'       => 'rac_ca_admin_email',
				'std'      => get_option( 'admin_email' ),
				'default'  => get_option( 'admin_email' ),
				'type'     => 'textarea',
				'class'    => 'admin_notification_ca',
				'desc_tip' => true,
				'desc'     => __( 'Enter the email IDs separated by comma', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Sender Option', 'recoverabandoncart' ),
				'id'      => 'rac_abandoned_sender_opt',
				'std'     => 'woo',
				'default' => 'woo',
				'type'    => 'radio',
				'options' => array(
					'woo'   => __( 'WooCommerce', 'recoverabandoncart' ),
					'local' => __( 'Local', 'recoverabandoncart' )
				),
				'class'   => 'admin_notifi_sender_opt_ca'
					) ;
			$section_fields[] = array(
				'name'    => __( 'From Name', 'recoverabandoncart' ),
				'id'      => 'rac_abandoned_from_name',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
				'class'   => 'local_senders_ca admin_notification_ca'
					) ;
			$section_fields[] = array(
				'name'    => __( 'From Email', 'recoverabandoncart' ),
				'id'      => 'rac_abandoned_from_email',
				'std'     => '',
				'default' => '',
				'type'    => 'text',
				'class'   => 'local_senders_ca admin_notification_ca'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Subject', 'recoverabandoncart' ),
				'id'      => 'rac_abandoned_email_subject',
				'std'     => 'A cart has been Abandoned',
				'default' => 'A cart has been Abandoned',
				'type'    => 'text',
				'class'   => 'admin_notification_ca'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Message', 'recoverabandoncart' ),
				'id'      => 'rac_abandoned_email_message',
				'css'     => 'min-height:250px;min-width:400px;',
				'std'     => 'A cart has been Abandoned. Here is the details {rac.abandoned_cart}<br>by {rac.abandoned_username}.',
				'default' => 'A cart has been Abandoned. Here is the details {rac.abandoned_cart}<br>by {rac.abandoned_username}.',
				'type'    => 'textarea',
				'class'   => 'admin_notification_ca'
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Restriction Settings', 'recoverabandoncart' ),
				'id'       => 'rac_admin_email_restriction_type',
				'type'     => 'select',
				'css'      => 'min-width:153px',
				'options'  => array(
					'user_role'      => __( 'User Role', 'recoverabandoncart' ),
					'name'           => __( 'Name', 'recoverabandoncart' ),
					'mail_id'        => __( 'Email ID', 'recoverabandoncart' ),
					'email_provider' => __( 'Email Provider', 'recoverabandoncart' )
				),
				'std'      => 'user_role',
				'default'  => 'user_role',
				'desc_tip' => true,
				'desc'     => __( 'Admin Email Sending Restriction for the Abandoned and Recovered status based on the following option(s)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Admin Email Notifications are', 'recoverabandoncart' ),
				'id'      => 'rac_admin_email_restriction_mode',
				'type'    => 'select',
				'css'     => 'min-width:153px',
				'options' => array(
					'exclude' => __( 'Not sent for the selected option', 'recoverabandoncart' ),
					'include' => __( 'Sent for the selected option', 'recoverabandoncart' ),
				),
				'std'     => 'exclude',
				'default' => 'exclude',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Select User Role', 'recoverabandoncart' ),
				'id'      => 'rac_admin_restrict_user_role',
				'css'     => 'min-width:150px',
				'type'    => 'multiselect',
				'std'     => '',
				'default' => '',
				'options' => fp_rac_user_roles(),
				'class'   => 'rac_admin_email_restriction_option rac_admin_email_restrict_user_role fp-rac-select-field',
					) ;
			$section_fields[] = array(
				'name'        => __( 'User Name Selected', 'recoverabandoncart' ),
				'id'          => 'rac_admin_restrict_user_name',
				'type'        => 'rac_custom_fields',
				'rac_field'   => 'customer_search',
				'css'         => 'min-width:400px',
				'std'         => array(),
				'default'     => array(),
				'class'       => 'rac_admin_email_restriction_option rac_admin_email_restrict_name',
				'placeholder' => __( 'Search for a customer&hellip;', 'recoverabandoncart' ),
				'desc_tip'    => true,
				'desc'        => __( 'Enter the First Three Character of User Name', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom Mail ID Selected', 'recoverabandoncart' ),
				'id'       => 'rac_admin_restrict_email_id',
				'type'     => 'textarea',
				'std'      => '',
				'default'  => '',
				'css'      => 'min-width:300px',
				'class'    => 'rac_admin_email_restriction_option rac_admin_email_restrict_mail_id',
				'desc_tip' => true,
				'desc'     => __( 'Enter Mail ID per line which will be excluded to receive a mail from Recover Abandon Cart', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Restrict Email Providers', 'recoverabandoncart' ),
				'id'       => 'rac_admin_restrict_email_providers',
				'type'     => 'textarea',
				'std'      => '',
				'default'  => '',
				'css'      => 'min-width:300px',
				'class'    => 'rac_admin_email_restriction_option rac_admin_email_restrict_email_provider',
				'desc_tip' => true,
				'desc'     => __( 'Enter the email providers seperated by comma. Do not enter any special characters', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_admin_notification_settings'
					) ;
			// Admin notification settings section end.

			return apply_filters( 'woocommerce_rac_email_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the email template section array.
		 * 
		 * @return array
		 */
		protected function email_template_section_array() {
			$section_fields = array() ;

			// Email template product info positioning settings section start.
			$section_fields[] = array(
				'type'    => 'rac_drag_drop_product_info',
				'id'      => 'drag_and_drop_product_info_sortable_column',
				'default' => 'true',
				'std'     => 'true',
					) ;
			// Email template product info positioning settings section end.
			// Email template product info table settings section start.
			$section_fields[] = array(
				'name' => __( 'Product Info Table Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'desc' => __( 'Following Customization options works with the shortcode {rac.Productinfo} in Email Template', 'recoverabandoncart' ),
				'id'   => 'rac_email_template_product_info_table_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Border for Table', 'recoverabandoncart' ),
				'id'      => 'rac_enable_border_for_productinfo_in_email',
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Display Variations for Variable Product', 'recoverabandoncart' ),
				'type'     => 'select',
				'default'  => 'yes',
				'options'  => array( 'yes' => __( 'Show', 'recoverabandoncart' ), 'no' => __( 'Hide', 'recoverabandoncart' ) ),
				'std'      => 'yes',
				'id'       => 'rac_email_product_variation_sh',
				'desc_tip' => true,
				'desc'     => __( 'If "Show" is selected, variation name will be displayed if the product is a variable product)', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Variable Product Display', 'recoverabandoncart' ),
				'id'      => 'rac_var_product_disp_opt',
				'std'     => '1',
				'default' => '1',
				'type'    => 'select',
				'options' => array(
					'1' => __( 'Attribute with Variation', 'recoverabandoncart' ),
					'2' => __( 'Variation Only', 'recoverabandoncart' ),
				),
					) ;
			$section_fields[] = array(
				'name'     => __( 'SKU', 'recoverabandoncart' ),
				'id'       => 'rac_troubleshoot_sku_sh',
				'type'     => 'select',
				'desc'     => __( 'If enabled, SKU will be displayed next to product name in abandoned cart emails and cart list', 'recoverabandoncart' ),
				'desc_tip' => true,
				'options'  => array( 'yes' => __( 'Show', 'recoverabandoncart' ), 'no' => __( 'Hide', 'recoverabandoncart' ) ),
				'std'      => 'yes',
				'default'  => 'yes',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Name Column', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_product_name_product_info_shortcode',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Name Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Product Name',
				'std'     => 'Product Name',
				'id'      => 'rac_product_info_product_name',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Image Column', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_product_image_product_info_shortcode',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Image Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Product Image',
				'std'     => 'Product Image',
				'id'      => 'rac_product_info_product_image',
					) ;
			$section_fields[] = array(
				'name'      => __( 'Product Image Size', 'recoverabandoncart' ),
				'id'        => 'rac_product_img_size',
				'type'      => 'rac_custom_fields',
				'rac_field' => 'image_width',
				'default'   => array( 'width' => 90, 'height' => 90 ),
				'desc'      => __( '(Width X Height)To customize the size of the Product Image to be displayed in Abandoned Cart Email(s)', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Quantity Column', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_product_quantity_product_info_shortcode',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Quantity Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Quantity',
				'std'     => 'Quantity',
				'id'      => 'rac_product_info_quantity',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Product Price Column', 'recoverabandoncart' ),
				'type'     => 'select',
				'options'  => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default'  => 'no',
				'std'      => 'no',
				'id'       => 'rac_hide_product_price_product_info_shortcode',
				'clone_id' => 'rac_hide_product_price_product_info_shortcode',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Product Price Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Product Price',
				'std'     => 'Product Price',
				'id'      => 'rac_product_info_product_price',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Subtotal, Shipping, Tax, Total Rows', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_tax_total_product_info_shortcode',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Subtotal Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Subtotal',
				'std'     => 'Subtotal',
				'id'      => 'rac_product_info_subtotal',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Total Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Total',
				'std'     => 'Total',
				'id'      => 'rac_product_info_total',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Shipping Row', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_shipping_row_product_info_shortcode',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Shipping Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Shipping',
				'std'     => 'Shipping',
				'id'      => 'rac_product_info_shipping',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Tax Row', 'recoverabandoncart' ),
				'type'    => 'select',
				'options' => array(
					'no'  => __( 'Show', 'recoverabandoncart' ),
					'yes' => __( 'Hide', 'recoverabandoncart' )
				),
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_hide_tax_row_product_info_shortcode',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Tax Label', 'recoverabandoncart' ),
				'type'    => 'text',
				'default' => 'Tax',
				'std'     => 'Tax',
				'id'      => 'rac_product_info_tax',
				'class'   => 'rac_hide_total_info'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Display Product Price Including Tax in Emails', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_inc_tax_with_product_price_product_info_shortcode',
				'desc'    => __( 'If enabled, product price will be displayed including tax and if disabled, product price will be displayed excluding tax', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_template_product_info_table_settings'
					) ;
			// Email template product info table settings section end.
			// Email template cart link settings section start.
			$section_fields[] = array(
				'name' => __( 'Email Template Cart Link Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_template_cart_link_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Cart Link Type', 'recoverabandoncart' ),
				'id'       => 'rac_cart_link_options',
				'type'     => 'select',
				'std'      => '1',
				'default'  => '1',
				'options'  => array(
					'1' => __( 'Hyperlink', 'recoverabandoncart' ),
					'2' => __( 'URL', 'recoverabandoncart' ),
					'3' => __( 'Button', 'recoverabandoncart' ),
					'4' => __( 'Image', 'recoverabandoncart' )
				),
				'desc_tip' => true,
				'desc'     => __( 'Customize the cart link in email template', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Button Background Color', 'recoverabandoncart' ),
				'id'      => 'rac_cart_button_bg_color',
				'class'   => 'color racbutton',
				'type'    => 'text',
				'std'     => '000091',
				'default' => '000091',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Button Text Color', 'recoverabandoncart' ),
				'id'      => 'rac_cart_button_link_color',
				'class'   => 'color racbutton',
				'type'    => 'text',
				'std'     => 'ffffff',
				'default' => 'ffffff',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cart Link Color', 'recoverabandoncart' ),
				'id'      => 'rac_email_link_color',
				'class'   => 'color raclink',
				'type'    => 'text',
				'std'     => '1919FF',
				'default' => '1919FF',
					) ;
			$section_fields[] = array(
				'name'         => __( 'Cart Link Image', 'recoverabandoncart' ),
				'id'           => 'fp_rac_email_cartlink_logo_text',
				'class'        => 'fp_rac_class_cartlink_image',
				'type'         => 'rac_custom_fields',
				'rac_field'    => 'upload_image',
				'std'          => '',
				'default'      => '',
				'placeholder'  => __( 'Choose Image', 'recoverabandoncart' ),
				'button_title' => __( 'Choose Image', 'recoverabandoncart' ),
				'button_label' => __( 'Upload Image', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Image Height', 'recoverabandoncart' ),
				'id'      => 'rac_cart_link_image_height',
				'class'   => 'fp_rac_class_cartlink_image',
				'type'    => 'number',
				'step'    => 'any',
				'std'     => '15',
				'default' => '15',
				'desc'    => 'px',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Image Width', 'recoverabandoncart' ),
				'id'      => 'rac_cart_link_image_width',
				'class'   => 'fp_rac_class_cartlink_image',
				'type'    => 'number',
				'step'    => 'any',
				'std'     => '100',
				'default' => '100',
				'desc'    => 'px',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Page to be Redirected on Clicking the Cart Link in Abandoned Cart Emails', 'recoverabandoncart' ),
				'id'       => 'rac_cartlink_redirect',
				'std'      => '1',
				'default'  => '1',
				'type'     => 'radio',
				'options'  => array( '1' => __( 'Cart page', 'recoverabandoncart' ), '2' => __( 'Checkout page', 'recoverabandoncart' ) ),
				'desc_tip' => true,
				'desc'     => __( 'Please Select the page that you want to redirect after clicking the Cart Link in email', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Clear the Cart Content when Cart Link is Clicked', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'std'     => 'yes',
				'id'      => 'rac_cart_content_when_cart_link_is_clicked',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Auto Apply Coupon when Cart Link is Clicked', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_auto_apply_coupon_enabled',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_template_cart_link_settings'
					) ;
			// Email template cart link settings section end.
			// Email template shortcode settings section start.
			$section_fields[] = array(
				'name' => __( 'Shortcode Customization in Email Template', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_email_template_shortcode_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Date Format', 'recoverabandoncart' ),
				'id'       => 'rac_date_format',
				'type'     => 'text',
				'std'      => 'd:m:y',
				'default'  => 'd:m:y',
				'desc_tip' => true,
				'desc'     => __( 'Customize date format for {rac.date}', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Time Format', 'recoverabandoncart' ),
				'id'       => 'rac_time_format',
				'type'     => 'text',
				'std'      => 'h:i:s',
				'default'  => 'h:i:s',
				'desc_tip' => true,
				'desc'     => __( 'Customize time format for {rac.time}', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Shortcode in Email Subject Label', 'recoverabandoncart' ),
				'type'     => 'text',
				'default'  => 'and more',
				'std'      => 'and more',
				'id'       => 'rac_subject_product_shrotcode_customize',
				'desc_tip' => __( 'If the cart list contains more than one product, the label entered here will be displayed along with the name of the first product when using the shortcode {rac.productname} in abandoned cart email subject', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_email_template_shortcode_settings'
					) ;
			// Email template shortcode settings section end.

			return apply_filters( 'woocommerce_rac_email_template_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the cart list recover section array.
		 * 
		 * @return array
		 */
		protected function cartlist_recover_section_array() {
			$section_fields = array() ;

			// Recover settings section start.
			$section_fields[] = array(
				'name' => __( 'Recover Status Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_recover_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Change All the New/Abandon Cart Lists of a User when they Place on Order in the Site', 'recoverabandoncart' ),
				'id'       => 'rac_cartlist_new_abandon_recover',
				'class'    => 'rac_cartlist_new_abandon_recover',
				'type'     => 'checkbox',
				'std'      => 'yes',
				'default'  => 'yes',
				'desc_tip' => true,
				'desc'     => __( 'Recover Cart List based on Order Status', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Allow Manual Orders to Recover Cart List', 'recoverabandoncart' ),
				'id'       => 'rac_cartlist_new_abandon_recover_by_manual_order',
				'class'    => 'rac_cart_depends_parent_new_abandon_option',
				'type'     => 'checkbox',
				'std'      => 'yes',
				'default'  => 'yes',
				'desc_tip' => true,
				'desc'     => __( 'Enable this Option will help to recover cart list based on manually created orders.', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'New Status to Recovered Status', 'recoverabandoncart' ),
				'id'       => 'rac_cartlist_change_from_new_to_recover',
				'class'    => 'rac_cart_depends_parent_new_abandon_option',
				'type'     => 'checkbox',
				'std'      => 'yes',
				'default'  => 'yes',
				'desc_tip' => true,
				'desc'     => __( 'Based on Order Status change New Status to Recovered Status', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Abandon Status to Recovered Status', 'recoverabandoncart' ),
				'id'       => 'rac_cartlist_change_from_abandon_to_recover',
				'class'    => 'rac_cart_depends_parent_new_abandon_option',
				'type'     => 'checkbox',
				'std'      => 'yes',
				'default'  => 'yes',
				'desc_tip' => true,
				'desc'     => __( 'Based on Order Status change Abandon Status to Recovered Status', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Change Status of the Captured Cart Entry to Recovered when Order status becomes', 'recoverabandoncart' ),
				'id'       => 'rac_mailcartlist_change',
				'class'    => 'rac_mailcartlist_change fp-rac-select-field',
				'type'     => 'multiselect',
				'css'      => 'min-width:153px',
				'options'  => fp_rac_get_order_status(),
				'std'      => array( 'completed', 'processing' ),
				'default'  => array( 'completed', 'processing' ),
				'desc_tip' => true,
				'desc'     => __( 'Status of captured cart entry will be changed to "Recovered" if the order status of placed order reaches any one of the selected status', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_recover_settings'
					) ;
			// Recover settings section end.

			return apply_filters( 'woocommerce_rac_cartlist_recover_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the guest popup section array.
		 * 
		 * @return array
		 */
		protected function guest_popup_section_array() {
			$section_fields = array() ;

			// Guest popup settings section start.
			$section_fields[] = array(
				'name' => __( 'Add to Cart Popup Settings for Guest', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_guest_popup_settings'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Enable Add to cart popup', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_enable_guest_add_to_cart_popup',
				'desc'    => __( 'Enabling this option will display popup to get email address when click Add to cart button', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'     => __( 'Popup Display Mode', 'recoverabandoncart' ),
				'type'     => 'select',
				'default'  => '1',
				'std'      => '1',
				'options'  => array(
					'1' => __( 'Instant Display', 'recoverabandoncart' ),
					'2' => __( 'Time Delayed Display', 'recoverabandoncart' ) ),
				'id'       => 'rac_popup_display_method',
				'class'    => 'rac_show_hide_settings_for_guest_popup',
				'desc_tip' => true,
				'desc'     => __( 'Instant Display - The Pop-up will display immediately if add to cart button clicked, Time Delayed Display - The Pop-up will display afer a Specific Time Duration delay', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'id'                => 'rac_popup_delay_time',
				'class'             => 'rac_show_hide_settings_for_guest_popup',
				'type'              => 'number',
				'custom_attributes' => array( 'min' => 1, 'max' => 60 ),
				'default'           => '60',
				'std'               => '60',
				'desc'              => __( 'Seconds', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Enable Popup Sub Heading', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_enable_sub_heading',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Is Email Address Mandatory for Add to Cart', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_force_guest_to_enter_email_address',
				'class'   => 'rac_show_hide_settings_for_guest_popup',
				'desc'    => __( 'Enabling this option will force guest to enter email address', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'To Show or Hide First name and last name in Guest add to cart Pop Up', 'recoverabandoncart' ),
				'id'      => 'rac_show_hide_name_in_popup',
				'std'     => '1',
				'default' => '1',
				'type'    => 'select',
				'options' => array(
					'1' => __( 'Hide', 'recoverabandoncart' ),
					'2' => __( 'Show', 'recoverabandoncart' ),
				),
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Is First/Last Name Mandatory for Add to Cart', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_force_guest_to_enter_first_last_name',
				'class'   => 'rac_show_hide_settings_for_guest_popup',
				'desc'    => __( 'Enabling this option will Force Guest Users to Enter their First Name and Last Name', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'To Show or Hide Contact number in Guest add to cart Pop Up', 'recoverabandoncart' ),
				'id'      => 'rac_show_hide_contactno_in_popup',
				'std'     => '1',
				'default' => '1',
				'type'    => 'select',
				'options' => array(
					'1' => __( 'Hide', 'recoverabandoncart' ),
					'2' => __( 'Show', 'recoverabandoncart' ),
				),
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Is Phone Number Mandatory for Add to Cart', 'recoverabandoncart' ),
				'type'    => 'checkbox',
				'default' => 'no',
				'std'     => 'no',
				'id'      => 'rac_force_guest_to_enter_phoneno',
				'class'   => 'rac_show_hide_settings_for_guest_popup',
				'desc'    => __( 'Enabling this option will Force Guest Users to Enter their Contact Number', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_popup_settings'
					) ;
			// Guest popup settings section end.
			// Guest popup GDPR settings section start.
			$section_fields[] = array(
				'name'  => __( 'GDPR Settings', 'recoverabandoncart' ),
				'type'  => 'title',
				'id'    => 'rac_guest_popup_gdpr_settings',
				'class' => 'rac_show_hide_settings_for_guest_popup',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Enable GDPR', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_gdpr_enabled',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'GDPR Content', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_gdpr_field_content',
				'std'     => 'I agree that my submitted data is being collected for future follow-ups',
				'default' => 'I agree that my submitted data is being collected for future follow-ups',
				'type'    => 'textarea',
				'css'     => 'min-height:100px;min-width:400px;',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_popup_gdpr_settings'
					) ;
			// Guest popup GDPR settings section end.
			// Guest popup color customization settings section start.
			$section_fields[] = array(
				'name'  => __( 'Color Customization', 'recoverabandoncart' ),
				'type'  => 'title',
				'id'    => 'rac_guest_popup_color_customization_settings',
				'class' => 'rac_show_hide_settings_for_guest_popup',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Background Color of Pop_Up field', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_color',
				'class'   => 'color raclink rac_show_hide_settings_for_guest_popup',
				'type'    => 'text',
				'std'     => 'ffffff',
				'default' => 'ffffff',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Add to cart button Color', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_add_to_cart_color',
				'class'   => 'color raclink rac_show_hide_settings_for_guest_popup',
				'type'    => 'text',
				'std'     => '008000',
				'default' => '008000',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cancel button Color', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_cancel_color',
				'class'   => 'color raclink rac_show_hide_settings_for_guest_popup',
				'type'    => 'text',
				'std'     => 'cc2900',
				'default' => 'cc2900',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_popup_color_customization_settings'
					) ;
			// Guest popup color customization settings section end.
			// Guest popup localization settings section start.
			$section_fields[] = array(
				'name'  => __( 'Localizations', 'recoverabandoncart' ),
				'type'  => 'title',
				'id'    => 'rac_guest_popup_localization_settings',
				'class' => 'rac_show_hide_settings_for_guest_popup',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Popup Heading', 'recoverabandoncart' ),
				'id'      => 'rac_guest_add_to_cart_popup_heading',
				'std'     => 'Please enter your Details',
				'default' => 'Please enter your Details',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Popup Sub Heading', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_sub_heading',
				'std'     => 'To add this item to your cart, please enter the details below',
				'default' => 'To add this item to your cart, please enter the details below',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'First Name Label', 'recoverabandoncart' ),
				'id'      => 'rac_guest_add_to_cart_popup_fname',
				'std'     => 'Enter your First Name',
				'default' => 'Enter your First Name',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Last Name Label', 'recoverabandoncart' ),
				'id'      => 'rac_guest_add_to_cart_popup_lname',
				'std'     => 'Enter your Last Name',
				'default' => 'Enter your Last Name',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Phone Number Label', 'recoverabandoncart' ),
				'id'      => 'rac_guest_add_to_cart_popup_phoneno',
				'std'     => 'Enter Your Contact Number',
				'default' => 'Enter Your Contact Number',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Email Address Label', 'recoverabandoncart' ),
				'id'      => 'rac_guest_add_to_cart_popup_email',
				'std'     => 'Enter your Email Address',
				'default' => 'Enter your Email Address',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Add to cart button Text', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_add_to_cart_text',
				'std'     => 'Add to cart',
				'default' => 'Add to cart',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Cancel button Text', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_cancel_text',
				'std'     => 'Cancel',
				'default' => 'Cancel',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_popup_localization_settings'
					) ;
			// Guest popup localization settings section end.
			// Guest popup error message settings section start.
			$section_fields[] = array(
				'name'  => __( 'Error Messages', 'recoverabandoncart' ),
				'type'  => 'title',
				'id'    => 'rac_guest_popup_error_message_settings',
				'class' => 'rac_show_hide_settings_for_guest_popup',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Empty Email field', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_empty',
				'std'     => 'Please Enter your Email Address',
				'default' => 'Please Enter your Email Address',
				'type'    => 'text',
				'newids'  => 'rac_guest_popup_err_msg_for_empty',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Invalid Email Address', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_invalid_email',
				'std'     => 'Please Enter your Valid Email Address',
				'default' => 'Please Enter your Valid Email Address',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Empty First Name field', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_empty_fname',
				'std'     => 'Please Enter your First Name',
				'default' => 'Please Enter your First Name',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Empty Last Name field', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_empty_lname',
				'std'     => 'Please Enter your Last Name',
				'default' => 'Please Enter your Last Name',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Empty Contact Number field', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_empty_phoneno',
				'std'     => 'Please Enter your Contact Number',
				'default' => 'Please Enter your Contact Number',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for Invalid Contact Number', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_err_msg_for_empty_invalid_phoneno',
				'std'     => 'Please Enter valid Contact Number',
				'default' => 'Please Enter valid Contact Number',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'name'    => __( 'Error Message for GDPR', 'recoverabandoncart' ),
				'id'      => 'rac_guest_popup_gdpr_error_msg',
				'std'     => 'Please Confirm the GDPR',
				'default' => 'Please Confirm the GDPR',
				'type'    => 'text',
				'class'   => 'rac_show_hide_settings_for_guest_popup'
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_popup_error_message_settings'
					) ;
			// Guest popup error message settings section end.

			return apply_filters( 'woocommerce_rac_guest_popup_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the unsubscription section array.
		 * 
		 * @return array
		 */
		protected function unsubscription_section_array() {
			$section_fields = array() ;

			// Unsubscription settings section start.
			$section_fields[] = array(
				'name' => __( 'Unsubscription Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_unsubscription_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Display Unsubscription option on My Account Page', 'recoverabandoncart' ),
				'id'      => 'rac_unsub_myaccount_option',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'If enabled, unsubscribe option will be displayed in "My Account Page"', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Customize Unsubscription Heading', 'recoverabandoncart' ),
				'id'      => 'rac_unsub_myaccount_heading',
				'std'     => 'Unsubscription Settings',
				'default' => 'Unsubscription Settings',
				'type'    => 'text',
				'class'   => 'rac_unsubscribe_hide',
				'desc'    => __( 'Customize the heading appeared in My Account Page', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Customize Unsubscription Text', 'recoverabandoncart' ),
				'id'       => 'rac_unsub_myaccount_text',
				'std'      => 'Unsubscribe Here to stop Receiving Emails from Recovered Abandoned Cart',
				'default'  => 'Unsubscribe Here to stop Receiving Emails from Recovered Abandoned Cart',
				'type'     => 'textarea',
				'class'    => 'rac_unsubscribe_hide',
				'desc_tip' => true,
				'desc'     => __( 'Customize the Message appeared in My Account Page', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Link in Email Footer', 'recoverabandoncart' ),
				'id'       => 'fp_unsubscription_link_in_email',
				'type'     => 'checkbox',
				'default'  => 'no',
				'std'      => 'no',
				'desc'     => __( 'If enabled, unsubscription link will be appended to the email footer', 'recoverabandoncart' ),
				'desc_tip' => '</ br> <b> ' . __( 'Note: ', 'recoverabandoncart' ) . '</b>' . __( 'If unsubscription link is not visible in footer of email, then consider using the shortcode <b>{rac.unsubscribe}</b> in text editor for each email template', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Message', 'recoverabandoncart' ),
				'id'       => 'fp_unsubscription_footer_message',
				'type'     => 'textarea',
				'css'      => 'height: 60px; width: 320px',
				'default'  => 'You can {rac_unsubscribe} to stop Receiving Abandon Cart Mail from {rac_site}',
				'std'      => 'You can {rac_unsubscribe} to stop Receiving Abandon Cart Mail from {rac_site}',
				'desc_tip' => true,
				'desc'     => __( 'Enter Unsubscription Message which is visible in Email Footer', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Unsubscription Link Text in Email Template will', 'recoverabandoncart' ),
				'id'       => 'fp_unsubscription_footer_link_text_option',
				'type'     => 'select',
				'options'  => array(
					'1' => __( 'Replace WooCommerce footer text', 'recoverabandoncart' ),
					'2' => __( 'Append to WooCommerce footer text', 'recoverabandoncart' ),
				),
				'default'  => '1',
				'std'      => '1',
				'desc_tip' => true,
				'desc'     => __( 'Choose how the Unsubscription Link from Recovered Abandon Cart will be displayed in emails ', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Link Anchor Text', 'recoverabandoncart' ),
				'id'       => 'fp_unsubscription_footer_link_text',
				'type'     => 'text',
				'default'  => 'Unsubscribe',
				'std'      => 'Unsubscribe',
				'desc_tip' => true,
				'desc'     => __( 'Enter the text to be replaced for {rac_unsubscribe} shortcode', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Unsubscribe Anchor Color', 'recoverabandoncart' ),
				'id'      => 'rac_unsubscribe_link_color',
				'class'   => 'color',
				'type'    => 'text',
				'std'     => '1919FF',
				'default' => '1919FF',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Type', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscription_type',
				'class'    => 'rac_unsubscription_type',
				'std'      => '1',
				'default'  => '1',
				'type'     => 'radio',
				'options'  => array( '1' => __( 'Automatic Unsubscription', 'recoverabandoncart' ), '2' => __( 'Manual Unsubscription', 'recoverabandoncart' ) ),
				'desc_tip' => true,
				'desc'     => __( 'Please Select the Unsubscription Type', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Redirect URL for Automatic Unsubscription', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscription_redirect_url',
				'type'     => 'text',
				'default'  => get_permalink( wc_get_page_id( 'myaccount' ) ),
				'std'      => get_permalink( wc_get_page_id( 'myaccount' ) ),
				'class'    => 'rac_unsub_auto',
				'desc_tip' => true,
				'desc'     => __( 'Enter Redirect Url to redirect when click the Automatic unsubscription link', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Redirect URL for Manual Unsubscription', 'recoverabandoncart' ),
				'id'       => 'rac_manual_unsubscription_redirect_url',
				'type'     => 'text',
				'default'  => get_permalink( wc_get_page_id( 'myaccount' ) ),
				'std'      => get_permalink( wc_get_page_id( 'myaccount' ) ),
				'class'    => 'rac_unsub_manual',
				'desc_tip' => true,
				'desc'     => __( 'Enter Redirect Url to redirect when click the Manual unsubscription link', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Already Unsubscribed Text', 'recoverabandoncart' ),
				'id'       => 'rac_already_unsubscribed_text',
				'type'     => 'text',
				'default'  => 'You have already unsubscribed.',
				'std'      => 'You have already unsubscribed.',
				'desc_tip' => true,
				'desc'     => __( 'Enter Already Unsubscribed Text', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Unsubscribed Successfully Text', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscribed_successfully_text',
				'type'     => 'text',
				'default'  => 'You have successfully unsubscribed from Abandoned cart Emails.',
				'std'      => 'You have successfully unsubscribed from Abandoned cart Emails.',
				'desc_tip' => true,
				'desc'     => __( 'Enter Unsubscribed Successfully Text', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Confirm Unsubscription Text', 'recoverabandoncart' ),
				'id'       => 'rac_confirm_unsubscription_text',
				'type'     => 'text',
				'default'  => 'To stop receiving Abandoned Cart Emails, Click the Unsubscribe button below',
				'std'      => 'To stop receiving Abandoned Cart Emails, Click the Unsubscribe button below',
				'class'    => 'rac_unsub_manual',
				'desc_tip' => true,
				'desc'     => __( 'Enter Confirm Unsubscription Text', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Message Text Color', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscription_message_text_color',
				'clone_id' => 'rac_unsubscription_message_text_color',
				'type'     => 'text',
				'default'  => 'fff',
				'std'      => 'fff',
				'class'    => 'color rac_unsub_auto',
				'desc_tip' => true,
				'desc'     => __( 'Choose Unsubscription Message Text color', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Message Background color', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscription_message_background_color',
				'type'     => 'text',
				'default'  => 'a46497',
				'std'      => 'a46497',
				'class'    => 'color rac_unsub_auto',
				'desc_tip' => true,
				'desc'     => __( 'Choose Background color for Unsubscription Message', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Unsubscription Email Text color', 'recoverabandoncart' ),
				'id'       => 'rac_unsubscription_email_text_color',
				'type'     => 'text',
				'default'  => '000000',
				'std'      => '000000',
				'class'    => 'color rac_unsub_manual',
				'desc_tip' => true,
				'desc'     => __( 'Choose Unsubscription Email Text color', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Confirm Unsubscription Text color', 'recoverabandoncart' ),
				'id'       => 'rac_confirm_unsubscription_text_color',
				'type'     => 'text',
				'default'  => 'ff3f12',
				'std'      => 'ff3f12',
				'class'    => 'color rac_unsub_manual',
				'desc_tip' => true,
				'desc'     => __( 'Choose Confirm Unsubscription Text color', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_unsubscription_settings'
					) ;
			// Unsubscription settings section end.

			return apply_filters( 'woocommerce_rac_add_to_cart_popup_settings', $section_fields ) ;
		}

		/**
		 * Render the cron job information.
		 */
		public static function rac_cron_job_information() {
			?>
			<table class="widefat fp-rac-cron-details-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email Job hook', 'recoverabandoncart' ) ; ?></th>
						<th><?php esc_html_e( 'Next Run', 'recoverabandoncart' ) ; ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php echo 'rac_cron_job' ; ?>
						</td>
						<td>
							<?php
							$timestamp = wp_next_scheduled( 'rac_cron_job' ) ;
							if ( $timestamp ) {
								$date_format      = get_option( 'date_format' ) ;
								$time_format      = get_option( 'time_format' ) ;
								$date             = gmdate( $date_format, $timestamp ) ;
								$time             = gmdate( $time_format, $timestamp ) ;
								$date_time_format = gmdate( 'Y-m-d H:i:s', $timestamp ) ;
								echo wp_kses_post( 'UTC time = ' . $date . ' / ' . $time . '</br>' ) ;
								echo wp_kses_post( 'Local time = ' . get_date_from_gmt( $date_time_format, $date_format ) . ' / ' . get_date_from_gmt( $date_time_format, $time_format ) . '</br>' ) ;
							} else {
								esc_html_e( 'Cron is not set', 'recoverabandoncart' ) ;
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<h4><?php esc_html_e( "Note: Please don/'t consider 'Next Run' if you are using server cron on your site", 'recoverabandoncart' ) ; ?></h4>
			<?php
		}

		/**
		 * Reschedule the cron job when changing the settings.  
		 */
		public static function reschedule_cron_job() {
			wp_clear_scheduled_hook( 'rac_cron_job' ) ;
			if ( wp_next_scheduled( 'rac_cron_job' ) == false ) {
				wp_schedule_event( time(), 'xhourly', 'rac_cron_job' ) ;
			}
		}

		/**
		 * Render the test email fields.
		 */
		public static function test_email() {
			?>
			<h3><?php esc_html_e( 'Test Email Settings', 'recoverabandoncart' ) ; ?></h3>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Email Format', 'recoverabandoncart' ) ; ?></th>
					<td>
						<select name="rac_test_mail_format" id="rac_test_mail_format">
							<option value="1"><?php esc_html_e( 'Plain Text', 'recoverabandoncart' ) ; ?></option>
							<option value="2"><?php esc_html_e( 'HTML', 'recoverabandoncart' ) ; ?></option>
						</select>
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Send Test Email to', 'recoverabandoncart' ) ; ?> </th>
					<td>
						<input type="text" id="testemailto" name="testemailto" value="">
						<input type="button" id="senttestmail" class="button button-primary" value=<?php esc_attr_e( 'Send Test Email', 'recoverabandoncart' ) ; ?>>
					</td>
				</tr>
				<tr>
					<td colspan="2"><p id="test_mail_result fp-rac-hide"></p></td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Render the product info table column positioning.
		 */
		public static function product_info_table_column_positioning() {
			?>
			<h3>
				<label><?php esc_attr_e( 'Product Info Table Column Positioning', 'recoverabandoncart' ) ; ?></label>
			</h3>
			<table class="form-table" id="rac_drag_n_drop_product_info">
				<?php
				$sortable_column = array( 'product_name' => __( 'Product Name', 'recoverabandoncart' ), 'product_image' => __( 'Product Image', 'recoverabandoncart' ), 'product_quantity' => __( 'Quantity', 'recoverabandoncart' ), 'product_price' => __( 'Total', 'recoverabandoncart' ) ) ;
				$priority_array  = get_option( 'drag_and_drop_product_info_sortable_column', true ) ;
				$priority_array  = is_array( $priority_array ) && ! empty( $priority_array ) ? $priority_array : array_keys( $sortable_column ) ;
				if ( rac_check_is_array( $priority_array ) ) {
					foreach ( $priority_array as $key ) {
						?>
						<tbody id="<?php echo esc_attr( $key ) ; ?>">
							<tr class="rac_product_info_drag_n_drop" id="<?php echo esc_attr( $key ) ; ?>">
								<td><?php echo esc_html( $sortable_column[ $key ] ) ; ?></td>
							<tr>
						</tbody>
						<?php
					}
				}
				?>
			</table>
			<?php
		}

	}

}

return new RAC_General_Tab() ;
