<?php
/**
 * Admin HTML settings buttons.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
?>
<p class = 'submit'>
	<?php if ( ! isset( $GLOBALS[ 'hide_save_button' ] ) ) : ?>
		<input name='rac_save' class='button-primary rac-save-btn' type='submit' value="<?php esc_attr_e( 'Save changes', 'recoverabandoncart' ) ; ?>" />
		<input type="hidden" name="save" value="save"/>
		<?php
		wp_nonce_field( self::$plugin_slug . '_save_settings', '_' . self::$plugin_slug . '_nonce', false, true ) ;
	endif ;
	?>
</p>
<?php if ( $reset ) : ?>
	</form>
	<form method='post' action='' enctype='multipart/form-data' class="rac-reset-form">
		<input id='reset' name='rac_reset' class='button-secondary rac-reset-btn' type='submit' value="<?php esc_attr_e( 'Reset', 'recoverabandoncart' ) ; ?>"/>
		<input type="hidden" name="reset" value="reset"/>
		<?php
		wp_nonce_field( 'rac_reset_settings', '_rac_nonce', false, true ) ;
	endif;
