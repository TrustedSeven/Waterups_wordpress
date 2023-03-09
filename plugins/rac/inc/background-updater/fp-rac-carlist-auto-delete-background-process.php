<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ;
}
if ( ! class_exists( 'FP_RAC_Cartlist_Auto_Delete_Background_Process' ) ) {

	/**
	 * FP_RAC_Cartlist_Auto_Delete_Background_Process Class.
	 */
	class FP_RAC_Cartlist_Auto_Delete_Background_Process extends WP_Background_Process {

		/**
		 * Action.
		 * 
		 * @var string
		 */
		protected $action = 'rac_cartlist_auto_delete_background_updater' ;

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
			$this->delete_cartlist_entry( $item ) ;
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
			$ids    = get_option( 'rac_cartlist_auto_delete_background_updater_data' ) ;
			$offset = get_option( 'rac_cartlist_auto_delete_background_updater_offset' ) ;
			$ids    = array_slice( $ids , $offset , 1000 ) ;

			if ( rac_check_is_array( $ids ) ) {
				FP_RAC_WooCommerce_Log::log( 'Cartlist automatic delete upto ' . $offset ) ;
				FP_RAC_Main_Function_Importing_Part::handle_cartlist_auto_delete( $offset , 1000 ) ;
			} else {
				FP_RAC_WooCommerce_Log::log( 'Cartlist automatic delete completed' ) ;
				delete_option( 'rac_cartlist_auto_delete_background_updater_offset' ) ;
				delete_option( 'rac_cartlist_auto_delete_background_updater_data' ) ;
			}
		}

		public function delete_cartlist_entry( $post_id ) {

			if ( 'rac_no_data' != $post_id ) {
				$duration          = '+' . get_option( 'rac_remove_abandon_after_x_days' , '30' ) . ' day' ;
				$cart_abandon_time = get_post_meta( $post_id , 'rac_cart_abandoned_time' , true ) ;
				$date              = strtotime( $duration , $cart_abandon_time ) ;

				if ( $date <= current_time( 'timestamp' ) ) {
					wp_delete_post( $post_id , true ) ;
				}
			}
		}

	}

}
