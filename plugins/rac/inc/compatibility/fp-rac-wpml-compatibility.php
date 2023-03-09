<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

add_action( 'admin_init', 'rac_register_template_for_wpml' ) ;

if ( ! function_exists( 'rac_register_template_for_wpml' ) ) {

	function rac_register_template_for_wpml() {

		if ( function_exists( 'icl_register_string' ) ) {
			$context         = 'RAC' ;
			$arg             = array( 'posts_per_page' => -1, 'post_status' => array( 'racactive', 'racinactive' ), 'post_type' => 'racemailtemplate', 'fields' => 'ids' ) ;
			$email_templates = fp_rac_check_query_having_posts( $arg ) ;
			if ( rac_check_is_array( $email_templates ) ) {
				foreach ( $email_templates as $email_post ) {
					$each_template  = fp_rac_create_email_template_obj( $email_post ) ;
					$register_array = array(
						'rac_template_' . $each_template->old_id . '_message'     => $each_template->message,
						'rac_template_' . $each_template->old_id . '_subject'     => $each_template->subject,
						'rac_template_' . $each_template->old_id . '_anchor_text' => $each_template->anchor_text,
							) ;
					if ( rac_check_is_array( $register_array ) ) {
						foreach ( $register_array as $name => $value ) {
							icl_register_string( $context, $name, $value ) ; //for registering template String
						}
					}
				}
			}
		}
	}

}

if ( ! function_exists( 'fp_get_wpml_text' ) ) {

	//For WPML
	function fp_get_wpml_text( $option_name, $language, $message, $context = 'RAC' ) {
		if ( '' == $language ) {
			return $message ;
		}

		if ( class_exists( 'WPML_String_Translation' ) ) {
			global $wpdb ;
			$translated = '' ;

			$res = $wpdb->get_results( $wpdb->prepare( "
            SELECT s.name, s.value, t.value AS translation_value, t.status
            FROM  {$wpdb->prefix}icl_strings s
            LEFT JOIN {$wpdb->prefix}icl_string_translations t ON s.id = t.string_id
            WHERE s.context = %s
                AND (t.language = %s OR t.language IS NULL)
            ", $context, $language ), ARRAY_A ) ;

			if ( rac_check_is_array( $res ) ) {
				foreach ( $res as $each_entry ) {
					if ( $each_entry[ 'name' ] == $option_name ) {
						if ( $each_entry[ 'translation_value' ] ) {
							$translated = $each_entry[ 'translation_value' ] ;
						} else {
							$translated = $each_entry[ 'value' ] ;
						}
					}
				}
			}

			return $translated ? $translated : $message ;
		} elseif ( function_exists( 'icl_translate' ) ) {
			$has_translation = null ;

			return icl_translate( $context, $option_name, $message, false, $has_translation, $language ) ;
		} else {
			return $message ;
		}
	}

}

if ( ! function_exists( 'fp_rac_wpml_convert_url' ) ) {

	function fp_rac_wpml_convert_url( $url, $lan = null ) {
		if ( class_exists( 'SitePress' ) ) {
			global $sitepress ;
			$lang_change_url = $sitepress->convert_url( $url, $lan ) ;
			return $lang_change_url ;
		}
		return $url ;
	}

}

if ( ! function_exists( 'fp_rac_wpml_switch_lang' ) ) {

	function fp_rac_wpml_switch_lang( $lan = 'current' ) {
		if ( ! class_exists( 'SitePress' ) ) {
			return ;
		}

		global $sitepress, $fp_current_lang ;

		if ( 'current' != $lan ) {
			$fp_current_lang = $sitepress->get_current_language() ;
		} else {
			$lan = $fp_current_lang ;
		}

		$sitepress->switch_lang( $lan ) ;
	}

}

add_filter( 'weglot_translate_email_languages_forced', 'fp_rac_force_email_translate', 10, 1 ) ;

if ( ! function_exists( 'fp_rac_force_email_translate' ) ) {

	function fp_rac_force_email_translate( $args ) {
		if ( ! FP_RAC_Send_Email_Woocommerce_Mailer::$sending ) {
			return $args ;
		}

		$data = FP_RAC_Send_Email_Woocommerce_Mailer::$sending ;
		if ( ! isset( $data[ 3 ] ) || empty( $data[ 3 ] ) ) {
			return $args ;
		}

		if ( rac_check_is_array( $args ) ) {
			$args[ 'current' ] = $data[ 3 ] ;
		} else {
			$args = array( 'current' => $data[ 3 ] ) ;
		}

		return $args ;
	}

}
