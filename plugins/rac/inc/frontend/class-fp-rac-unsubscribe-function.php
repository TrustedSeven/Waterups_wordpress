<?php
/**
 * Unsubscribe Related Functions.
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Unsubscribe' ) ) {

	/**
	 *  FP_RAC_Unsubscribe Class.
	 *
	 */
	class FP_RAC_Unsubscribe {

		/**
		 * Initialize the FP_RAC_Unsubscribe class.
		 * 
		 */
		public static function init() {
			add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'set_stop_cart_capture_cookie' ) ) ;
			add_action( 'woocommerce_cart_updated', array( __CLASS__, 'unsubscribed_user_from_rac_mail' ), 1 ) ;
			add_shortcode( 'rac.unsubscribe_email_manual', array( __CLASS__, 'manual_unsubscribe_option' ) ) ;
			add_action( 'wp_ajax_fp_rac_undo_unsubscribe', array( __CLASS__, 'response_unsubscribe_option_myaccount' ) ) ;

			if ( get_option( 'rac_unsub_myaccount_option' ) == 'yes' ) {
				add_action( 'woocommerce_before_my_account', array( __CLASS__, 'add_undo_unsubscribe_option_myaccount' ) ) ;
			}
		}

		/**
		 * Display Unsubscribe Option in MyAccount Page 
		 * 
		 */
		public static function add_undo_unsubscribe_option_myaccount() {
			//enqueue script
			wp_enqueue_script( 'fp_unsubscribe' ) ;
			?>
			<h3><?php echo esc_html( get_option( 'rac_unsub_myaccount_heading' ) ) ; ?></h3>
			<p>
				<input type="checkbox" name="fp_rac_unsubscribe_option" id="fp_rac_unsubscribe_option" value="yes" <?php checked( 'yes', get_user_meta( get_current_user_id(), 'fp_rac_mail_unsubscribed', true ) ) ; ?>/>    <?php echo esc_html( get_option( 'rac_unsub_myaccount_text' ) ) ; ?>
			</p>
			<?php
		}

		/**
		 * Response of unsubscribe option
		 * 
		 */
		public static function response_unsubscribe_option_myaccount() {

			check_ajax_referer( 'unsubscribe-email', 'rac_security' ) ;

			if ( isset( $_POST[ 'getcurrentuser' ] ) && isset( $_POST[ 'dataclicked' ] ) ) {
				$userid      = absint( $_POST[ 'getcurrentuser' ] ) ;
				$email_id    = isset( $_POST[ 'email_id' ] ) ? wc_clean( wp_unslash( $_POST[ 'email_id' ] ) ) : '' ;
				$dataclicked = wc_clean( wp_unslash( $_POST[ 'dataclicked' ] ) ) ;
				if ( $userid ) {
					if ( 'false' == $dataclicked ) {
						update_user_meta( $userid, 'fp_rac_mail_unsubscribed', 'yes' ) ;
						echo '1' ;
					} else {
						delete_user_meta( $userid, 'fp_rac_mail_unsubscribed' ) ;
						echo '2' ;
					}
				} else {
					$user_id_from_email = rac_check_is_member_or_guest( $email_id, true ) ;
					if ( $user_id_from_email ) {
						if ( 'false' == $dataclicked ) {
							update_user_meta( $user_id_from_email, 'fp_rac_mail_unsubscribed', 'yes' ) ;
							echo '1' ;
						} else {
							delete_user_meta( $user_id_from_email, 'fp_rac_mail_unsubscribed' ) ;
							echo '2' ;
						}
					} else {
						$email_array = ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ;
						if ( 'false' == $dataclicked ) {
							if ( ! in_array( $email_id, $email_array ) ) {
								$email_array[] = $email_id ;
							}
							update_option( 'fp_rac_mail_unsubscribed', $email_array ) ;
							echo '1' ;
						} else {
							$key = array_search( $email_id, $email_array ) ;
							if ( false !== $key ) {
								unset( $email_array[ $key ] ) ;
							}
							update_option( 'fp_rac_mail_unsubscribed', $email_array ) ;
							echo '2' ;
						}
					}
				}
				exit() ;
			}
		}

		/**
		 * Set the stop capture cart cookie fro the email
		 */
		public static function set_stop_cart_capture_cookie() {
			if ( isset( $_GET[ 'email' ] ) && isset( $_GET[ 'action' ] ) && isset( $_GET[ '_mynonce' ] ) ) {
				setcookie( 'dont_insert_when_un_sub_email', 'yes', time() + 3600 ) ;
			}
		}

		/**
		 * User manually unsubscribe recover abandon cart notification from the email.
		 */
		public static function unsubscribed_user_from_rac_mail() {
			if ( isset( $_GET[ 'email' ] ) && isset( $_GET[ 'action' ] ) && isset( $_GET[ '_mynonce' ] ) ) {
				$to        = wc_clean( wp_unslash( $_GET[ 'email' ] ) ) ;
				$permalink = remove_query_arg( array( 'email', 'action', '_mynonce' ), get_permalink() ) ;
				if ( '2' != get_option( 'rac_unsubscription_type' ) ) {
					// Automatic Unsubscription
					$check = rac_check_is_member_or_guest( $to ) ;
					if ( $check ) {
						// For Member
						$member_userid = rac_return_user_id( $to ) ;
						$check_already = get_user_meta( $member_userid, 'fp_rac_mail_unsubscribed', true ) ;
						if ( 'yes' == $check_already ) {
							setcookie( 'already_unsubscribed', 'yes', time() + 3600 ) ;
							$_COOKIE[ 'already_unsubscribed' ] = 'yes' ;
						} else {
							unset( $_COOKIE[ 'already_unsubscribed' ] ) ;
							setcookie( 'already_unsubscribed', null ) ;
							update_user_meta( $member_userid, 'fp_rac_mail_unsubscribed', 'yes' ) ;
						}
					} else {
						// For Guest
						$old_array    = array_filter( array_unique( ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ) ) ;
						$listofemails = ( array ) $to ;
						$merge_arrays = array_merge( $listofemails, $old_array ) ;
						if ( in_array( $to, $old_array ) ) {
							setcookie( 'already_unsubscribed', 'yes', time() + 3600 ) ;
							$_COOKIE[ 'already_unsubscribed' ] = 'yes' ;
						} else {
							unset( $_COOKIE[ 'already_unsubscribed' ] ) ;
							setcookie( 'already_unsubscribed', null ) ;
							update_option( 'fp_rac_mail_unsubscribed', $merge_arrays ) ;
						}
					}
					setcookie( 'un_sub_email_auto', $to, time() + 3600 ) ;
					$_COOKIE[ 'un_sub_email_auto' ] = $to ;
					setcookie( 'dont_insert_when_un_sub_email', 'yes', time() + 3600 ) ;
					unset( $_COOKIE[ 'un_sub_email_manual' ] ) ;
					setcookie( 'un_sub_email_manual', null ) ;
					wp_redirect( $permalink ) ;
					exit() ;
				} else {
					// Manual Unsubscription
					setcookie( 'un_sub_email_manual', $to, time() + 3600 ) ;
					setcookie( 'dont_insert_when_un_sub_email', 'yes', time() + 3600 ) ;
					unset( $_COOKIE[ 'un_sub_email_auto' ] ) ;
					setcookie( 'un_sub_email_auto', null ) ;
					wp_redirect( $permalink ) ;
					exit() ;
				}
			}
			if ( isset( $_COOKIE[ 'un_sub_email_auto' ] ) ) {
				wp_enqueue_style( 'fp-rac-unsubscribe-inline-style' ) ;

				if ( '' != $_COOKIE[ 'un_sub_email_auto' ] ) {
					if ( isset( $_COOKIE[ 'already_unsubscribed' ] ) ) {
						echo '<p class="un_sub_email_css">' . esc_html( get_option( 'rac_already_unsubscribed_text' ) ) . '</p>' ;
					} else {
						echo '<p class="un_sub_email_css">' . esc_html( get_option( 'rac_unsubscribed_successfully_text' ) ) . '</p>' ;
					}
				}
				unset( $_COOKIE[ 'un_sub_email_auto' ] ) ;
				setcookie( 'un_sub_email_auto', null ) ;
			}
		}

		/**
		 * Manual unsubscribe Display Message
		 * 
		 */
		public static function manual_unsubscribe_option() {
			if ( isset( $_COOKIE[ 'un_sub_email_manual' ] ) ) {
				$mail_id_to_unsub = wc_clean( wp_unslash( $_COOKIE[ 'un_sub_email_manual' ] ) ) ;
				if ( '' != $mail_id_to_unsub ) {
					wp_enqueue_style( 'fp-rac-unsubscribe-inline-style' ) ;
					?>
					<form method="post" id="manual_unsubscibe_form">
						<input type="hidden" name="email_id_at_session" id="email_id_at_session" value="<?php echo esc_attr( $mail_id_to_unsub ) ; ?>">
						<?php
						$user = get_user_by( 'email', $mail_id_to_unsub ) ;
						if ( isset( $user->data->ID ) ) {
							$user_id       = $user->data->ID ;
							$check_already = get_user_meta( $user_id, 'fp_rac_mail_unsubscribed', true ) ;
							if ( 'yes' == $check_already ) {
								?>
								<div class="unsubscribeContent">
									<div class="mailSubscribe">
										<strong><?php echo esc_html( $mail_id_to_unsub ) ; ?></strong>
										<br>
									</div>
									<div class="subsInnerContent">
										<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_already_unsubscribed_text' ) ) ; ?></strong>
										<br>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="unsubscribeContent">
									<div class="mailSubscribe">
										<strong><?php echo esc_html( $mail_id_to_unsub ) ; ?></strong>
										<br>
									</div>
									<?php if ( ! isset( $_REQUEST[ 'email_id_at_session' ] ) ) { ?>
										<div class="subsInnerContent">
											<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_confirm_unsubscription_text' ) ) ; ?></strong>
											<br>
										</div>
										<div class="fp-rac-subcribe-actions">
											<input type="submit" class="unsubscribe_button" id="rac_unsubscribe_manually" value="<?php esc_attr_e( 'Unsubscribe', 'recoverabandoncart' ) ; ?>">
										</div>
										<?php
									} else {
										?>
										<div class="subsInnerContent">
											<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_unsubscribed_successfully_text' ) ) ; ?></strong>
										</div>
										<?php
										update_user_meta( $user_id, 'fp_rac_mail_unsubscribed', 'yes' ) ;
										unset( $_COOKIE[ 'un_sub_email_manual' ] ) ;
										setcookie( 'un_sub_email_manual', null ) ;
									}
									?>
								</div>
								<?php
							}
						} else {
							$old_array = array_filter( array_unique( ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ) ) ;
							if ( in_array( $mail_id_to_unsub, $old_array ) ) {
								?>
								<div class="unsubscribeContent">
									<div class="mailSubscribe">
										<strong><?php echo esc_html( $mail_id_to_unsub ) ; ?></strong>
										<br>
									</div>
									<div class="subsInnerContent">
										<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_already_unsubscribed_text' ) ) ; ?></strong>
										<br>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="unsubscribeContent">
									<div class="mailSubscribe">
										<strong><?php echo esc_html( $mail_id_to_unsub ) ; ?></strong>
										<br>
									</div>
									<?php if ( ! isset( $_REQUEST[ 'email_id_at_session' ] ) ) { ?>
										<div class="subsInnerContent">
											<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_confirm_unsubscription_text' ) ) ; ?></strong>
											<br>
										</div>
										<div class="fp-rac-subcribe-actions">
											<input type="submit" class="unsubscribe_button" id="rac_unsubscribe_manually" value="<?php esc_attr_e( 'Unsubscribe', 'recoverabandoncart' ) ; ?>">
										</div>
										<?php
									} else {
										?>
										<div class="subsInnerContent">
											<strong class="msgTitle"><?php echo esc_html( get_option( 'rac_unsubscribed_successfully_text' ) ) ; ?></strong>
										</div>
										<?php
										$old_array    = array_filter( array_unique( ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ) ) ;
										$listofemails = ( array ) $mail_id_to_unsub ;
										$merge_arrays = array_merge( $listofemails, $old_array ) ;
										update_option( 'fp_rac_mail_unsubscribed', $merge_arrays ) ;
										unset( $_COOKIE[ 'un_sub_email_manual' ] ) ;
										setcookie( 'un_sub_email_manual', null ) ;
									}
									?>
								</div>
								<?php
							}
						}
						?>
					</form>
					<?php
				}
			}
		}

	}

	FP_RAC_Unsubscribe::init() ;
}
