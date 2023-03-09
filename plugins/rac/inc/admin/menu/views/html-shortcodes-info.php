<?php
/**
 * Shortcodes information.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
/*
 * Hook: rac_before_shortcode_contents.
 */
do_action( 'rac_before_shortcode_contents' ) ;
?>
<table class="form-table rac_shortcodes_info widefat striped">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Shortcode', 'recoverabandoncart' ) ; ?></th>
			<th><?php esc_html_e( 'Context where Shortcode is valid', 'recoverabandoncart' ) ; ?></th>
			<th><?php esc_html_e( 'Purpose', 'recoverabandoncart' ) ; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ( rac_check_is_array( $shortcodes_info ) ) :
			foreach ( $shortcodes_info as $shortcode => $details ) :
				?>
				<tr>
					<td><?php echo esc_html( $shortcode ) ; ?></td>
					<td><?php echo esc_html( $details[ 'position' ] ) ; ?></td>
					<td><?php echo esc_html( $details[ 'usage' ] ) ; ?></td>
				</tr>
				<?php
			endforeach ;
		endif ;
		?>
	</tbody>
</table>

<?php
/*
 * Hook: rac_after_shortcodes_content.
 */
do_action( 'rac_after_shortcodes_content' ) ;

