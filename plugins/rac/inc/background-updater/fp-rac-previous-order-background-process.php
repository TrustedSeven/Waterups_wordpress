<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ;
}
if ( ! class_exists( 'FP_RAC_Previous_Order_Background_Process' ) ) {

	/**
	 * FP_RAC_Previous_Order_Background_Process Class.
	 */
	class FP_RAC_Previous_Order_Background_Process extends WP_Background_Process {

		/**
		 * Action.
		 * 
		 * @var string
		 */
		protected $action = 'rac_previous_order_background_updater' ;

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $item Queue item to iterate over
		 *
		 * @return mixed
		 */
		protected function task( $item ) {
			$this->insert_cartlist_entry( $item ) ;
			return false ;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete() ;

			$ids    = get_option( 'rac_previous_order_background_updater_data' ) ;
			$offset = get_option( 'rac_previous_order_background_updater_offset' ) ;
			$ids    = array_slice( $ids , $offset , 1000 ) ;

			if ( rac_check_is_array( $ids ) ) {
				FP_RAC_WooCommerce_Log::log( 'Previous Order Upgrade upto ' . $offset ) ;
				FP_RAC_Main_Function_Importing_Part::handle_previous_order( $offset , 1000 ) ;
			} else {
				FP_RAC_WooCommerce_Log::log( 'Previous Order Upgrade Completed' ) ;
				delete_option( 'rac_previous_order_background_updater_offset' ) ;
				delete_option( 'rac_previous_order_background_updater_data' ) ;
			}
		}

		public function insert_cartlist_entry( $order_id ) {
			if ( 'rac_no_data' != $order_id ) {
				$subscription_check = fp_rac_check_is_subscription( $order_id ) ;
				$paymentplan_check  = fp_rac_check_is_payment_plan( $order_id ) ;
				if ( ! $subscription_check && ! $paymentplan_check ) {
					$order_statuses = get_option( 'rac_auto_order_status' , array() ) ;
					//check to, not importing order which are recovered and captured on place order
					if ( in_array( get_post_status( $order_id ) , ( array ) $order_statuses ) ) {
						//check to, not importing order which are recovered and captured on place order
						$insert_id = FP_RAC_Insert_CartList_Entry::fp_rac_insert_old_order_entry( $order_id ) ;
						if ( $insert_id ) {
							update_post_meta( $order_id , 'old_order_updated' , 'yes' ) ; // this makes sure for no duplication
						}
					}
				}
			}
		}

	}

}
