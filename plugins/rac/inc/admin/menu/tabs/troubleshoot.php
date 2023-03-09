<?php

/**
 * Troubleshoot Tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Troubleshoot_Tab' ) ) {
	return new RAC_Troubleshoot_Tab() ;
}

if ( ! class_exists( 'RAC_Troubleshoot_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Troubleshoot_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'troubleshoot' ;
			$this->label = __( 'Troubleshoot', 'recoverabandoncart' ) ;

		   

			parent::__construct() ;
		}

	}

}

return new RAC_Troubleshoot_Tab() ;
