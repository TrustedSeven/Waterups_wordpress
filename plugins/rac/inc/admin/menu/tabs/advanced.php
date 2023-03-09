<?php
/**
 * Advanced Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Advanced_Tab' ) ) {
	return new RAC_Advanced_Tab() ;
}

if ( ! class_exists( 'RAC_Advanced_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Advanced_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'advanced' ;
			$this->label = __( 'Advanced', 'recoverabandoncart' ) ;

			add_action( 'woocommerce_admin_field_rac_troubleshoot_update_data', array( __CLASS__, 'render_update_user_data' ) ) ;

			parent::__construct() ;
		}

		/**
		 * Get the sections.
		 * 
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'general'      => __( 'General', 'recoverabandoncart' ),
				'troubleshoot' => __( 'Troubleshoot', 'recoverabandoncart' ),
				'guest_cart'   => __( 'Guest Cart', 'recoverabandoncart' ),
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
			$user_role      = fp_rac_wp_user_roles() ;
			unset( $user_role[ 'administrator' ] ) ;
			unset( $user_role[ 'customer' ] ) ;

			// Menu display settings section start.
			$section_fields[] = array(
				'name'     => __( 'Menu Display Settings', 'recoverabandoncart' ),
				'type'     => 'title',
				'id'       => 'rac_menu_display_settings',
				'clone_id' => '',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Hide the Recover Abandoned Cart plugin Menu', 'recoverabandoncart' ),
				'id'      => 'rac_menu_show_hide',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Enable this option to Hide the Recover Abandoned Cart Menu for the following selected User Role(s)', 'recoverabandoncart' )
					) ;
			$section_fields[] = array(
				'name'    => __( 'Select the User Role(s) to Hide the Recover Abandoned Cart plugin Menu', 'recoverabandoncart' ),
				'id'      => 'rac_menu_disp_user_roles',
				'css'     => 'min-width:150px',
				'type'    => 'multiselect',
				'std'     => '',
				'default' => '',
				'options' => $user_role,
				'class'   => 'fp-rac-select-field'
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_menu_display_settings'
					) ;
			// Menu display settings section end.
			// Export personal data settings section start.
			$section_fields[] = array(
				'name' => __( 'Personal Data Export Settings for GDPR Compliance', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_export_personal_data_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Personal Data Export Label', 'recoverabandoncart' ),
				'id'      => 'rac_personal_data_export_label',
				'type'    => 'text',
				'std'     => __( 'Cart Captured for Recovery', 'recoverabandoncart' ),
				'default' => __( 'Cart Captured for Recovery', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_export_personal_data_settings'
					) ;
			// Export personal data settings section end.
			// Custom CSS settings section start.
			$section_fields[] = array(
				'name' => __( 'Custom CSS', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_custom_css_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Custom CSS', 'recoverabandoncart' ),
				'css'      => 'min-width:550px;min-height:260px;margin-bottom:80px;',
				'id'       => 'rac_custom_css_pop',
				'type'     => 'textarea',
				'std'      => '#fp_rac_guest_email_in_cookie{

}
#fp_rac_guest_fname_in_cookie{

}
#fp_rac_guest_lname_in_cookie{

}
#fp_rac_guest_phoneno_in_cookie{

}',
				'desc_tip' => true,
				'desc'     => __( 'Customize the popup window using custom CSS', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_custom_css_settings'
					) ;
			// Custom CSS settings section end.

			return apply_filters( 'woocommerce_rac_advanced_general_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the troubleshoot section array.
		 * 
		 * @return array
		 */
		protected function troubleshoot_section_array() {

			$section_fields = array() ;

			// Troubleshoot settings section start.
			$section_fields[] = array(
				'name' => __( 'Troubleshoot Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Email Function to Use', 'recoverabandoncart' ),
				'id'       => 'rac_trouble_mail',
				'css'      => 'min-width:150px;',
				'type'     => 'select',
				'options'  => array( 'mail' => 'mail()', 'wp_mail' => 'wp_mail()' ),
				'std'      => 'wp_mail',
				'default'  => 'wp_mail',
				'desc_tip' => true,
				'desc'     => __( 'Please Select which mail function to use while sending notification', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'     => __( 'Use Email Troubleshoot', 'recoverabandoncart' ),
				'id'       => 'rac_webmaster_mail',
				'css'      => 'min-width:150px;',
				'type'     => 'select',
				'options'  => array( 'webmaster1' => __( 'Enable', 'recoverabandoncart' ), 'webmaster2' => __( 'Disable', 'recoverabandoncart' ) ),
				'std'      => 'webmaster2',
				'default'  => 'webmaster2',
				'desc_tip' => true,
				'desc'     => __( 'Please enable this option if you want to send Emails using Fifth Parameter ', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Use Email as Fifth Parameter', 'recoverabandoncart' ),
				'id'      => 'rac_textarea_mail',
				'std'     => 'webmaster@' . fp_rac_get_server_name(),
				'default' => 'webmaster@' . fp_rac_get_server_name(),
				'type'    => 'text',
					) ;
			$section_fields[] = array(
				'name'    => __( 'MIME Version 1.0 Parameter', 'recoverabandoncart' ),
				'id'      => 'rac_mime_mail_header_ts',
				'css'     => 'min-width:150px;',
				'type'    => 'select',
				'options' => array( 'block' => __( 'Include', 'recoverabandoncart' ), 'none' => __( 'Exclude', 'recoverabandoncart' ) ),
				'std'     => 'block',
				'default' => 'block',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Reply-To Parameter', 'recoverabandoncart' ),
				'id'      => 'rac_replyto_mail_header_ts',
				'css'     => 'min-width:150px;',
				'type'    => 'select',
				'options' => array( 'block' => __( 'Include', 'recoverabandoncart' ), 'none' => __( 'Exclude', 'recoverabandoncart' ) ),
				'std'     => 'block',
				'default' => 'block',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_settings'
					) ;
			// Troubleshoot settings section end.
			// Troubleshoot performance settings section start.
			$section_fields[] = array(
				'name' => __( 'Troubleshoot Performance Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_performance_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Load Recover Abandoned Cart Scripts/Styles in', 'recoverabandoncart' ),
				'id'       => 'rac_load_script_styles',
				'css'      => 'min-width:150px;',
				'type'     => 'select',
				'options'  => array( 'wp_head' => __( 'Header of the site', 'recoverabandoncart' ), 'wp_footer' => __( 'Footer of the site(Experimental)', 'recoverabandoncart' ) ),
				'std'      => 'wp_head',
				'default'  => 'wp_head',
				'desc_tip' => false,
				'desc'     => __( '"Footer of the Site" option is experimental and if your theme doesn\'t contain wp_footer hook then it won\'t work', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_performance_settings'
					) ;
			// Troubleshoot performance settings section end.
			// Troubleshoot ajax chunking settings section start.
			$section_fields[] = array(
				'name' => __( 'Ajax Chunking Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_ajax_chunking_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Chunk Count Per Ajax Call', 'recoverabandoncart' ),
				'id'       => 'rac_chunk_count_per_ajax',
				'std'      => 10,
				'default'  => 10,
				'type'     => 'number',
				'step'     => 1,
				'desc_tip' => __( "Don't Change the Value unless you need", 'recoverabandoncart' ),
				'desc'     => __( 'Applicable for "Check Previous Orders" tab', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_ajax_chunking_settings'
					) ;
			// Troubleshoot ajax chunking settings section end.
			// Troubleshoot cron settings section start.
			$section_fields[] = array(
				'name' => __( 'Cron Troubleshoot Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_cron_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'What Cron will be Used', 'recoverabandoncart' ),
				'id'      => 'rac_cron_troubleshoot_format',
				'css'     => 'min-width:150px;',
				'type'    => 'select',
				'options' => array( 'wp_cron' => __( 'wp_cron', 'recoverabandoncart' ), 'server_cron' => __( 'server_cron', 'recoverabandoncart' ) ),
				'std'     => 'wp_cron',
				'default' => 'wp_cron',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_cron_settings'
					) ;
			// Troubleshoot cron settings section end.
			// Troubleshoot cart capture settings section start.
			$section_fields[] = array(
				'name' => __( 'Remove Old Cart(s) Captured Hook Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_cart_capture_settings',
					) ;
			$section_fields[] = array(
				'name'     => __( 'Hook used to Remove Old Carts', 'recoverabandoncart' ),
				'id'       => 'rac_troubleshoot_cart_capture_hook',
				'css'      => 'min-width:150px;',
				'type'     => 'select',
				'options'  => array( '1' => __( 'WooCommerce_Cart_Updated', 'recoverabandoncart' ), '2' => __( 'Other Hooks', 'recoverabandoncart' ) ),
				'std'      => '1',
				'default'  => '1',
				'desc_tip' => true,
				'desc'     => __( 'Other Hooks - wc add to cart item, wc cart_item_removed, wc cart_item_restored & wc after_cart_item_quantity_update', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_cart_capture_settings'
					) ;
			// Troubleshoot cart capture settings section end.
			// Troubleshoot status management settings section start.
			$section_fields[] = array(
				'name' => __( 'Abandon Cart List Status Management Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_troubleshoot_status_management_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Update Status of Captured Carts when Cart List Table is Accessed', 'recoverabandoncart' ),
				'id'      => 'rac_troubleshoot_update_cart_list_status_auto',
				'type'    => 'checkbox',
				'std'     => 'yes',
				'default' => 'yes',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Display Update Status Button in Cart List Table for Updating the Captured Carts Status', 'recoverabandoncart' ),
				'id'      => 'rac_troubleshoot_update_cart_list_status_manual',
				'type'    => 'checkbox',
				'std'     => 'no',
				'default' => 'no',
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_troubleshoot_status_management_settings'
					) ;
			// Troubleshoot status management settings section end.
			$section_fields[] = array(
				'type' => 'rac_troubleshoot_update_data',
					) ;

			return apply_filters( 'woocommerce_rac_troubleshoot_settings', $section_fields ) ;
		}

		/**
		 * Get settings for the guest cart section array.
		 * 
		 * @return array
		 */
		protected function guest_cart_section_array() {
			$section_fields   = array() ;
			// Guest cart settings section start.
			$section_fields[] = array(
				'name' => __( 'Guest Cart Settings', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_guest_cart_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Pending', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_pending',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Pending', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Failed', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_failed',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Failed', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to On-Hold', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_on-hold',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become On-Hold', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Processing', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_processing',
				'std'     => 'yes',
				'default' => 'yes',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Processing', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Completed', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_completed',
				'std'     => 'yes',
				'default' => 'yes',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Completed', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Refunded', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_refunded',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Refunded', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'name'    => __( 'Remove Guests Cart when the Order Status Changes to Cancelled', 'recoverabandoncart' ),
				'id'      => 'rac_guest_abadon_type_cancelled',
				'std'     => 'no',
				'default' => 'no',
				'type'    => 'checkbox',
				'desc'    => __( 'Guest Cart Captured on place order will be in cart list, it will be removed when order become Cancelled', 'recoverabandoncart' ),
					) ;
			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_guest_cart_settings'
					) ;
			// Guest cart settings section end.

			return apply_filters( 'woocommerce_rac_guest_cart_settings', $section_fields ) ;
		}

		/**
		 * Render the update button for user data.
		 */
		public static function render_update_user_data() {
			$img_src = RAC_PLUGIN_URL . '/assets/images/update.gif' ;
			?>
			<h3><?php esc_html_e( 'Manual Data Updation', 'recoverabandoncart' ) ; ?></h3>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Update User Data for Previous Orders', 'recoverabandoncart' ) ; ?></th>
					<td>
						<input type="button" id="rac-update-data" class="button button-primary" value=<?php esc_attr_e( 'Update', 'recoverabandoncart' ) ; ?>>
						<img class="fp-rac-reload-img" src='<?php echo esc_url( $img_src ) ; ?>' alt='' id='rac_update_data_img'><br>
						<span id="rac-update-data-msg"> </span>
					</td>
				</tr>
			</table>
			<?php
		}

	}

}

return new RAC_Advanced_Tab() ;
