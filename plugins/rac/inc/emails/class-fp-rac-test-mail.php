<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_test_mail' ) ) {

	/**
	 * FP_RAC_Install Class.
	 */
	class FP_RAC_Test_Mail {

		public static function init() {
			add_action( 'wp_ajax_rac_send_test_mail' , array( __CLASS__ , 'rac_send_test_mail' ) ) ;
		}

		public static function rac_send_test_mail() {

			check_ajax_referer( 'test-email' , 'rac_security' ) ;

			$subject       = 'Test E-Mail' ;
			$to            = isset( $_POST[ 'rac_test_mail_to' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_test_mail_to' ] ) ) : '' ;
			$plain_or_html = isset( $_POST[ 'rac_plain_or_html' ] ) ? wc_clean( wp_unslash( $_POST[ 'rac_plain_or_html' ] ) ) : '' ;
			$plain_or_html = '1' == $plain_or_html ? 'PLAIN' : 'HTML' ;
			$message       = 'This is a test E-Mail to Make sure E-Mail are sent successfully from your site.' ;
			$message       = rac_email_woocommerce_html( $plain_or_html , $subject , $message ) ;
			$headers       = rac_format_email_headers() ;

			if ( rac_send_mail( $to , $subject , $message , $headers , $plain_or_html ) ) {
				echo 'sent' ;
			}
			exit() ;
		}

	}

	FP_RAC_Test_Mail::init() ;
}
