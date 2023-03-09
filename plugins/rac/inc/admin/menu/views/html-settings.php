<?php
/**
 * Admin settings. 
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
?>
<div class = "wrap rac-wrapper-cover woocommerce">
	<form method = "post" enctype = "multipart/form-data">
		<div class = "rac-wrapper">

			<nav class = "nav-tab-wrapper woo-nav-tab-wrapper rac-nav-tab-wrapper">
				<?php foreach ( $tabs as $name => $label ) { ?>
					<a href="<?php echo esc_url( rac_get_settings_page_url( array( 'tab' => $name ) ) ) ; ?>" class="nav-tab rac-nav-tab <?php echo esc_attr( $name ) . '_a ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) ; ?>">
						<span><?php echo esc_html( $label ) ; ?></span>
					</a>
				<?php } ?>
			</nav>

			<div class="rac-tab-content rac_<?php echo esc_attr( $current_tab ) ; ?>-tab-content-wrapper">
				<?php
				// Render the section navigation.
				do_action( sanitize_key( 'rac_sections_' . $current_tab ) ) ;
				?>
				<div class="rac-tab-inner-content rac-<?php echo esc_attr( $current_tab ) ; ?>-tab-inner-content">
					<?php
					do_action( sanitize_key( 'rac_before_tab_sections' ) ) ;

					// Show the success/error messages.
					self::show_messages() ;

					// Render the tab content.
					do_action( sanitize_key( 'rac_settings_' . $current_tab ) ) ;

					// Render the settings buttons.
					do_action( sanitize_key( 'rac_settings_buttons_' . $current_tab ) ) ;

					// Render the extra content after setting buttons.
					do_action( sanitize_key( 'rac_after_setting_buttons_' . $current_tab ) ) ;
					?>
				</div>
			</div>

		</div>
	</form>
	<?php
	do_action( sanitize_key( 'rac_' . $current_tab . '_' . $current_section . '_setting_end' ) ) ;
	do_action( sanitize_key( 'rac_settings_end' ) ) ;
	?>
</div>
<?php
