<?php

/**
 * Support Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Support_Tab' ) ) {
	return new RAC_Support_Tab() ;
}

if ( ! class_exists( 'RAC_Support_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Support_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id           = 'support' ;
			$this->label        = __( 'Support', 'recoverabandoncart' ) ;
			$this->show_buttons = false ;

			parent::__construct() ;
		}

		/**
		 * Get settings for the support section array.
		 * 
		 * @return array
		 */
		protected function support_section_array() {
			$section_fields  = array() ;
			$welcomepage_url = add_query_arg( array( 'page' => 'recover-abandoned-cart-welcome-page' ), admin_url( 'admin.php' ) ) ;

			// Support section start.
			$section_fields[] = array(
				'name' => __( 'Welcome Page', 'recoverabandoncart' ),
				'type' => 'title',
				'desc' => __( 'For more information on Recover Abadoned Cart please check the <a href="' . $welcomepage_url . '">Welcome Page</a> <br> ', 'recoverabandoncart' ),
				'id'   => 'rac_welcome_page_info'
					) ;
			$section_fields[] = array(
				'name' => __( 'Contact Support', 'recoverabandoncart' ),
				'type' => 'title',
				'desc' => __( 'For support, feature request or any help, please <a href="http://support.fantasticplugins.com/">register and open a support ticket on our site.</a> <br> ', 'recoverabandoncart' ),
				'id'   => 'rac_contact_support_info'
					) ;
			$section_fields[] = array(
				'name' => __( 'Documentation', 'recoverabandoncart' ),
				'type' => 'title',
				'desc' => __( 'Please check the documentation as we have lots of information there. The documentation file can be found inside the documentation folder which you will find when you unzip the downloaded zip file.', 'recoverabandoncart' ),
				'id'   => 'rac_support_documentation_info',
					) ;

			$section_fields[] = array(
				'type' => 'sectionend',
				'id'   => 'rac_welcome_page_info'
					) ;
			// Support section end.

			return apply_filters( 'woocommerce_fpracsupport_settings', $section_fields ) ;
		}

	}

}

return new RAC_Support_Tab() ;
