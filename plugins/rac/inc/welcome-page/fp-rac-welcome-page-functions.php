<?php

add_action( 'admin_init' , 'rac_welcome_screen_do_activation_redirect' ) ;
add_action( 'admin_menu' , 'rac_welcome_screen_pages' ) ;
add_action( 'admin_head' , 'rac_welcome_screen_remove_menus' ) ;

if ( ! function_exists( 'rac_welcome_screen_do_activation_redirect' ) ) {

	function rac_welcome_screen_do_activation_redirect() {
		if ( ! get_transient( '_welcome_screen_activation_redirect_recover_abandoned_cart' ) ) {
			return ;
		}

		delete_transient( '_welcome_screen_activation_redirect_recover_abandoned_cart' ) ;

		wp_safe_redirect( add_query_arg( array( 'page' => 'recover-abandoned-cart-welcome-page' ) , admin_url( 'admin.php' ) ) ) ;
	}

}

if ( ! function_exists( 'rac_welcome_screen_pages' ) ) {

	function rac_welcome_screen_pages() {
		add_dashboard_page(
				'Welcome To Recover Abandoned Cart' , 'Welcome To Recover Abandoned Cart' , 'read' , 'recover-abandoned-cart-welcome-page' , 'rac_welcome_screen_content'
		) ;
	}

}

if ( ! function_exists( 'rac_welcome_screen_content' ) ) {

	function rac_welcome_screen_content() {

		include 'fp-rac-welcome-page.php' ;
	}

}

if ( ! function_exists( 'rac_welcome_screen_remove_menus' ) ) {

	function rac_welcome_screen_remove_menus() {
		remove_submenu_page( 'index.php' , 'recover-abandoned-cart-welcome-page' ) ;
	}

}
