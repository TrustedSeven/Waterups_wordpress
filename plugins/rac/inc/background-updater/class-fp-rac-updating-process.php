<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Updating_Process' ) ) {

	/**
	 * FP_RAC_Updating_Process Class.
	 */
	class FP_RAC_Updating_Process {

		public $progress_batch ;
		public $identifier = 'fp_progress_ui' ;

		public function __construct() {
			$this->progress_batch = ( int ) get_site_option( 'fp_background_process_' . $this->identifier . '_progress' , 0 ) ;

			add_action( 'wp_ajax_fp_progress_bar_status' , array( $this , 'fp_updating_status' ) ) ;
		}

		/*
		 * Get Updated Details using ajax
		 * 
		 */

		public function fp_updating_status() {
			check_ajax_referer( 'fp-rac-upgrade' , 'fp_rac_security' ) ;

			try {
				if ( ! isset( $_POST ) ) {
					throw new exception( esc_html__( 'Invalid Request' , 'recoverabandoncart' ) ) ;
				}
				// Return if the current user does not have permission.
				if ( ! current_user_can( 'edit_posts' ) ) {
					throw new exception( esc_html__( "You don't have permission to do this action" , 'recoverabandoncart' ) ) ;
				}

				$percent = ( int ) get_site_option( 'fp_background_process_' . $this->identifier . '_progress' , 0 ) ;

				wp_send_json_success( array( 'percentage' => $percent ) ) ;
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) ) ;
			}
		}

		public function fp_delete_option() {
			delete_site_option( 'fp_background_process_' . $this->identifier . '_progress' ) ;
		}

		public function fp_increase_progress( $progress = 0 ) {
			update_site_option( 'fp_background_process_' . $this->identifier . '_progress' , $progress ) ;
		}

		/*
		 * Get Updated Details using ajax
		 * 
		 */

		public function fp_display_progress_bar() {
			$percent = $this->progress_batch ;
			$url     = add_query_arg( array( 'page' => 'recover-abandoned-cart-welcome-page' ) , admin_url( 'admin.php' ) ) ;
			?>
			<div class="fp_prograssbar_wrapper">
				<h1><?php esc_html_e( 'Recover Abandoned Cart' , 'recoverabandoncart' ) ; ?></h1>
				<div id="fp_uprade_label">
					<h3 class="fp-rac-upgrade-msg">
						<?php
						/* translators: %s-Version */
						echo wp_kses_post( sprintf( __( 'Upgrade to v%s is under Process...' , 'recoverabandoncart' ) , RAC_VERSION ) ) ;
						?>
					</h3>
				</div>
				<div class = "fp_outer">
					<div class = "fp_inner fp-progress-bar">

					</div>
				</div>
				<div id="fp_progress_status">
					<span id = "fp_currrent_status"><?php echo esc_html( $percent ) ; ?> </span><?php esc_html_e( '% Completed' , 'recoverabandoncart' ) ; ?>
				</div>
			</div>
			<?php
		}

	}

}
