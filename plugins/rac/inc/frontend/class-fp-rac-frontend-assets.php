<?php
/**
 * Frontend Assets.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Fronend_Assets' ) ) {

	/**
	 * Class.
	 */
	class FP_RAC_Fronend_Assets {

		/**
		 * Suffix.
		 * 
		 * @var string
		 */
		private static $suffix ;

		/**
		 * Footer.
		 * 
		 * @var string
		 */
		private static $footer = true ;

		/**
		 * Class Initialization.
		 */
		public static function init() {

			self::$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ;

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'external_css_files' ) ) ;
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'external_js_files' ) ) ;
		}

		/**
		 * Enqueue Front end required JS files
		 */
		public static function external_js_files() {
			$enqueue_array = array(
				'rac-datepicker' => array(
					'callable' => array( 'FP_RAC_Fronend_Assets', 'fp_rac_frontend_checkout_external_js' ),
					'restrict' => true,
				),
				'rac-alltabs'    => array(
					'callable' => array( 'FP_RAC_Fronend_Assets', 'fp_rac_frontend_myaccount_external_js' ),
					'restrict' => true,
				),
					) ;

			$enqueue_array = apply_filters( 'fp_rac_frontend_enqueue_scripts', $enqueue_array ) ;
			if ( rac_check_is_array( $enqueue_array ) ) {
				foreach ( $enqueue_array as $key => $enqueue ) {
					if ( rac_check_is_array( $enqueue ) ) {
						if ( $enqueue[ 'restrict' ] ) {
							call_user_func_array( $enqueue[ 'callable' ], array() ) ;
						}
					}
				}
			}
		}

		public static function fp_rac_frontend_checkout_external_js() {
			if ( is_user_logged_in() ) {
				return ;
			}

			if ( 'yes' == get_option( 'rac_enable_guest_add_to_cart_popup' ) ) {
				wp_enqueue_script( 'sweetalert2', RAC_PLUGIN_URL . '/assets/sweetalert2/sweetalert2.min.js', array(), RAC_VERSION ) ;
				wp_enqueue_style( 'sweetalert2', RAC_PLUGIN_URL . '/assets/sweetalert2/sweetalert2' . self::$suffix . '.css', array(), RAC_VERSION ) ;

				//Polyfill
				wp_enqueue_script( 'polyfill', RAC_PLUGIN_URL . '/assets/js/polyfill/polyfill' . self::$suffix . '.js', array( 'jquery' ), RAC_VERSION ) ;
			}

			if ( ( get_option( 'rac_load_script_styles' ) == 'wp_head' ) || ! get_option( 'rac_load_script_styles' ) ) {
				self::$footer = false ;
			}

			wp_register_script( 'rac_guest_handle', RAC_PLUGIN_URL . '/assets/js/fp-rac-guest-checkout.js', array( 'jquery' ), RAC_VERSION ) ;

			$email_name_no = array() ;
			if ( isset( $_COOKIE[ 'raccookie_guest_email' ] ) ) {
				$email_name_no = maybe_unserialize( wc_clean( wp_unslash( $_COOKIE[ 'raccookie_guest_email' ] ) ) ) ;
			}

			if ( isset( $_COOKIE[ 'rac_guest_popup_clicked_time' ] ) ) {
				$delay_time = wc_clean( wp_unslash( $_COOKIE[ 'rac_guest_popup_clicked_time' ] ) ) + get_option( 'rac_popup_delay_time' ) ;
				$check_time = ( time() > $delay_time ) ? 'yes' : 'no' ;
			}

			wp_localize_script( 'rac_guest_handle', 'rac_guest_params', array(
				'console_error'            => __( 'Not a valid e-mail address', 'recoverabandoncart' ),
				'current_lang_code'        => fp_rac_get_current_language(),
				'ajax_url'                 => RAC_ADMIN_AJAX_URL,
				'guest_entry'              => wp_create_nonce( 'guest-entry' ),
				'is_checkout'              => is_checkout(),
				'is_shop'                  => is_shop(),
				'ajax_add_to_cart'         => get_option( 'woocommerce_enable_ajax_add_to_cart' ),
				'enable_popup'             => get_option( 'rac_enable_guest_add_to_cart_popup' ),
				'form_label'               => get_option( 'rac_guest_add_to_cart_popup_heading' ),
				'first_name'               => get_option( 'rac_guest_first_name' ),
				'email_address_not_valid'  => get_option( 'rac_guest_popup_err_msg_for_invalid_email' ),
				'popup_sub_header'         => ( 'yes' == get_option( 'rac_guest_popup_enable_sub_heading', 'no' ) ) ? get_option( 'rac_guest_popup_sub_heading' ) . '<br/> <br/>' : '',
				'enter_email_address'      => get_option( 'rac_guest_popup_err_msg_for_empty' ),
				'enter_first_name'         => get_option( 'rac_guest_popup_err_msg_for_empty_fname' ),
				'enter_phone_no'           => get_option( 'rac_guest_popup_err_msg_for_empty_phoneno' ),
				'enter_valid_phone_no'     => get_option( 'rac_guest_popup_err_msg_for_empty_invalid_phoneno' ),
				'enter_last_name'          => get_option( 'rac_guest_popup_err_msg_for_empty_lname' ),
				'cancel_label'             => get_option( 'rac_guest_popup_cancel_text' ),
				'add_to_cart_label'        => get_option( 'rac_guest_popup_add_to_cart_text' ),
				'force_guest'              => get_option( 'rac_force_guest_to_enter_email_address' ),
				'show_guest_name'          => get_option( 'rac_show_hide_name_in_popup' ) === '2',
				'show_guest_contactno'     => get_option( 'rac_show_hide_contactno_in_popup' ) === '2',
				'force_guest_name'         => ( 'yes' == get_option( 'rac_force_guest_to_enter_first_last_name', 'no' ) && '2' == get_option( 'rac_show_hide_name_in_popup', '1' ) ),
				'force_guest_contactno'    => ( 'yes' == get_option( 'rac_force_guest_to_enter_phoneno', 'no' ) && '2' == get_option( 'rac_show_hide_contactno_in_popup', '1' ) ),
				'popup_already_displayed'  => isset( $_COOKIE[ 'rac_guest_popup_already_displayed' ] ) ? 'yes' : 'no',
				'is_cookie_already_set'    => isset( $_COOKIE[ 'raccookie_guest_email' ] ) ? true : false,
				'fp_rac_popup_email'       => isset( $email_name_no[ 'email' ] ) ? $email_name_no[ 'email' ] : '',
				'fp_rac_first_name'        => isset( $email_name_no[ 'firstname' ] ) ? $email_name_no[ 'firstname' ] : '',
				'fp_rac_last_name'         => isset( $email_name_no[ 'lastname' ] ) ? $email_name_no[ 'lastname' ] : '',
				'fp_rac_phone_no'          => isset( $email_name_no[ 'phone_no' ] ) ? $email_name_no[ 'phone_no' ] : '',
				'fp_rac_disp_notice_check' => fp_rac_check_guest_pages_for_display_notice( 'popup' ),
				'fp_rac_disp_notice'       => get_option( 'rac_guest_notice_msg' ),
				'popup_disp_method'        => get_option( 'rac_popup_display_method', '1' ),
				'popup_cookie_delay_time'  => isset( $check_time ) ? $check_time : 'no',
				'rac_popup_delay_nonce'    => wp_create_nonce( 'rac_popup_delay' ),
				'show_gdpr'                => 'yes' === get_option( 'rac_guest_popup_gdpr_enabled' ),
				'gdpr_description'         => get_option( 'rac_guest_popup_gdpr_field_content' ),
				'gdpr_error'               => get_option( 'rac_guest_popup_gdpr_error_msg' ),
				'checkout_gdpr_field'      => self::get_checkout_gdpr_field(),
				'show_checkout_gdpr'       => 'yes' === get_option( 'rac_guest_checkout_gdpr_enabled' ),
				'gdpr_nonce'               => wp_create_nonce( 'gdpr-nonce' )
			) ) ;
			wp_enqueue_script( 'rac_guest_handle', RAC_PLUGIN_URL . '/assets/js/fp-rac-guest-checkout.js', array( 'jquery' ), RAC_VERSION, self::$footer ) ;
			wp_localize_script( 'rac_guest_handle', 'custom_css_btn_color', array(
				'popupcolor'        => get_option( 'rac_guest_popup_color' ),
				'confirmbtncolor'   => get_option( 'rac_guest_popup_add_to_cart_color' ),
				'cancelbtncolor'    => get_option( 'rac_guest_popup_cancel_color' ),
				'email_placeholder' => get_option( 'rac_guest_add_to_cart_popup_email' ),
				'fname_placeholder' => get_option( 'rac_guest_add_to_cart_popup_fname' ),
				'lname_placeholder' => get_option( 'rac_guest_add_to_cart_popup_lname' ),
				'phone_placeholder' => get_option( 'rac_guest_add_to_cart_popup_phoneno' ),
			) ) ;
		}

		/**
		 * Get the checkout GDPR field.
		 * 
		 * @return string.
		 */
		public static function get_checkout_gdpr_field() {
			if ( 'yes' !== get_option( 'rac_guest_checkout_gdpr_enabled' ) ) {
				return '' ;
			}

			ob_start() ;
			$checked = isset( $_COOKIE[ 'rac_gdpr' ] ) && 'no' === $_COOKIE[ 'rac_gdpr' ] ;
			?>
			<span class="woocommerce-input-wrapper rac-checkout-gdpr-wrapper">
				<input type="checkbox" id="rac-checkout-gdpr-field" class="rac-checkout-gdpr-field" <?php checked( $checked, true ) ; ?>/> <?php echo wp_kses_post( get_option( 'rac_guest_checkout_gdpr_field_content' ) ) ; ?>
			</span>
			<?php
			return ob_get_clean() ;
		}

		public static function fp_rac_frontend_myaccount_external_js() {
			//register script
			wp_register_script( 'fp_unsubscribe', RAC_PLUGIN_URL . '/assets/js/frontend/fp-rac-unsubscribe.js', array( 'jquery' ), RAC_VERSION ) ;
			//localize script
			wp_localize_script( 'fp_unsubscribe', 'fp_unsubscribe_obj', array(
				'rac_current_userid'   => get_current_user_id(),
				'rac_admin_url'        => RAC_ADMIN_AJAX_URL,
				'rac_unsubcribe_nonce' => wp_create_nonce( 'unsubscribe-email' ),
				'rac_unsub_message'    => __( 'Successfully Unsubscribed...', 'recoverabandoncart' ),
				'rac_sub_message'      => __( 'Successfully Subscribed...', 'recoverabandoncart' ),
			) ) ;
		}

		/**
		 * Enqueue external CSS files.
		 */
		public static function external_css_files() {

			//Add inline style.
			self::add_inline_style() ;
			//Add UnSubscribe inline style.
			self::add_unsubscribe_inline_style() ;
		}

		/**
		 * Add Inline Style.
		 * */
		public static function add_inline_style() {
			$contents = get_option( 'rac_custom_css_pop', '' ) ;
			if ( ! $contents ) {
				return ;
			}

			wp_register_style( 'fp-rac-inline-style', false, array(), RAC_VERSION ) ; // phpcs:ignore
			wp_enqueue_style( 'fp-rac-inline-style' ) ;
			//Add custom css as inline style.
			wp_add_inline_style( 'fp-rac-inline-style', $contents ) ;
		}

		/**
		 * Add UnSubscribe Inline Style.
		 * */
		public static function add_unsubscribe_inline_style() {
			$contents = 'p.un_sub_email_css {
		position: fixed;
		left: 0;
		right: 0;
		margin: 0;
		width: 100%;
		font-size: 1em;
		padding: 1em 0;
		text-align: center;
		background-color: #' . get_option( 'rac_unsubscription_message_background_color' ) . ';
		color: #' . get_option( 'rac_unsubscription_message_text_color' ) . ';
		z-index: 99998;
		a {
			color: 0 1px 1em rgba(0, 0, 0, 0.2);
		}
	}

	.admin-bar {
		p.un_sub_email_css {
			top: 32px;
		}
	}
	.unsubscribeContent {
		border: 1px solid #d6d4d4;
		border-radius: 10px;
		box-sizing: border-box;
		margin: 25px auto;
		padding: 45px 60px;
		text-align: center;
		width: 600px;
	}
	.mailSubscribe {
		border-bottom: 1px solid #dbdedf;
		font-size: 18px;
		line-height: 31px;
		margin-bottom: 30px;
		padding-bottom: 30px;
		color: #' . get_option( 'rac_unsubscription_email_text_color' ) . ';
	}
	.msgTitle {
		color: #' . get_option( 'rac_confirm_unsubscription_text_color' ) . ';
		display: inline-block;
		font-size: 36px;
		margin-bottom: 24px;
	}
	.unsubscribe_button {
		background-color: #FF0000;
		border: medium none;
		border-radius: 5px;
		color: #FFFFFF;
		display: inline-block;
		font-size: 25px;
		padding: 10px 24px;
		text-align: center;
		text-decoration: none;
	}
        .fp-rac-subcribe-actions{
                text-align:center;
        }' ;

			wp_register_style( 'fp-rac-unsubscribe-inline-style', false, array(), RAC_VERSION ) ; // phpcs:ignore
			//Add unsubscribe css as inline style.
			wp_add_inline_style( 'fp-rac-unsubscribe-inline-style', $contents ) ;
		}

	}

	FP_RAC_Fronend_Assets::init() ;
}
