<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Preview_Email_Template' ) ) {

	/**
	 * FP_RAC_Preview_Email_Template Class.
	 */
	class FP_RAC_Preview_Email_Template {

		public static function rac_preview_email_template() {
			$post_id         = isset( $_GET[ 'post_id' ] ) ? absint( $_GET[ 'post_id' ] ) : '' ;
			$mail_logo_added = get_post_meta( $post_id, 'rac_template_link', true ) ;
			$view_template   = get_post_meta( $post_id, 'rac_template_mail', true ) ;
			$post_subject    = get_post_meta( $post_id, 'rac_template_subject', true ) ;
			$custom_css      = get_post_meta( $post_id, 'rac_template_custom_css', true ) ;
			$subject         = rac_shortcode_in_subject( 'First Name', 'Last Name', $post_subject ) ;
			$message         = self::replace_shortcodes_in_template( $post_id ) ;
			$message         = rac_shortcode_in_subject( 'First Name', 'Last Name', $message ) ; // added shortcode replacing from subject content to message content

			if ( '' == $mail_logo_added ) {
				$logo = '' ;
			} else {
				$logo = '<p class="fp-rac-email-logo-wrapper"><img src="' . esc_url( $mail_logo_added ) . '" width="100" height="100"/></a></p>' ;
			}

			$custom_css .= fp_rac_get_template_html( 'email-abandoned-cart-css.php' ) ;

			$message = rac_email_inline_style( $message, $custom_css ) ;

			if ( 'HTML' == $view_template ) {
				echo do_shortcode( self::email_template( $subject, $message ) ) ;
			} else {
				?>
				<div class="fp-rac-block">
					<div class="fp-rac-preview-wrapper"> <?php echo do_shortcode( wpautop( self::template_ready( $message, $logo ) ) ) ; ?> </div>
				</div>
				<?php
			}
		}

		//Load Email Template
		public static function email_template( $subject, $message ) {
			global $woocommerce, $woocommerce_settings ;

			// load the mailer class
			$mailer        = WC()->mailer() ;
			$email_heading = $subject ;
			$abstractClass = new ReflectionClass( 'WC_Email' ) ;
			if ( ! $abstractClass->isAbstract() ) {
				$email      = new WC_Email() ;
				// wrap the content with the email template and then add styles
				$fetch_data = $email->style_inline( $mailer->wrap_message( $email_heading, $message ) ) ;
			} else {
				$fetch_data = $mailer->wrap_message( $email_heading, $message ) ;
			}

			return $fetch_data ;
		}

		//Load Email Template
		public static function template_ready( $message, $link ) {
			global $woocommerce, $woocommerce_settings ;
			$data = $link . $message ;
			return $data ;
		}

		public static function replace_shortcodes_in_template( $post_id ) {
			global $to ;
			$date             = date_i18n( rac_date_format() ) ;
			$time             = date_i18n( rac_time_format() ) ;
			$anchor_text_post = get_post_meta( $post_id, 'rac_template_anchor_text', true ) ;
			$post             = get_post( $post_id ) ;
			$message          = $post->post_content ;
			$user             = get_userdata( get_current_user_id() ) ;
			$to               = $user->user_email ;

			$cart_url     = rac_get_page_permalink_dependencies( 'cart' ) ;
			$urltoclick   = esc_url_raw( add_query_arg( array( 'abandon_cart' => '00', 'email_template' => $post_id ), $cart_url ) ) ;
			$url_to_click = apply_filters( 'fp_rac_redirect_url', $urltoclick ) ;
			$link_options = get_option( 'rac_cart_link_options' ) ;
			if ( '1' == $link_options ) {
				$url_to_click = '<a class="fp-rac-email-cart-link"  href="' . $url_to_click . '">' . $anchor_text_post . '</a>' ;
			} elseif ( '2' == $link_options ) {
				$url_to_click = $url_to_click ;
			} elseif ( '3' == $link_options ) {
				$url_to_click = rac_cart_link_button_mode( $url_to_click, $anchor_text_post ) ;
			} else {
				$url_to_click = rac_cart_link_image_mode( $url_to_click, $anchor_text_post ) ;
			}
			$table_product_info = FP_RAC_Polish_Product_Info::fp_rac_extract_cart_details( false, true ) ;
			$find_array         = array( '{rac.cartlink}', '{rac.date}', '{rac.time}', '{rac.firstname}', '{rac.lastname}', '{rac.Productinfo}', '{rac.coupon}', '{rac.coupon_expired_date}' ) ;
			$replace_array      = array( $url_to_click, $date, $time, 'First Name', 'Last Name', $table_product_info, 'testcoupo.n1234567890', $date ) ;
			$message            = str_replace( $find_array, $replace_array, $message ) ;

			$message = rac_unsubscription_shortcode( $to, $message ) ;
			add_filter( 'woocommerce_email_footer_text', 'rac_footer_email_customization' ) ;
			$message = do_shortcode( $message ) ;

			return $message ;
		}

	}

}
