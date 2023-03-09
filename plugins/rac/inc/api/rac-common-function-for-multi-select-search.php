<?php
/**
 * Multi select search.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! function_exists( 'rac_customer_search' ) ) {

	function rac_customer_search( $args ) {
		$args = wp_parse_args(
				$args, array(
			'class'       => '',
			'id'          => '',
			'name'        => '',
			'placeholder' => '',
			'options'     => array(),
				)
				) ;

		global $woocommerce ;
		if ( ( float ) $woocommerce->version <= ( float ) ( '2.2.0' ) || ( float ) $woocommerce->version >= ( float ) ( '3.0.0' ) ) {
			?>
			<select name="<?php echo esc_attr( $args[ 'id' ] ) ; ?>[]" multiple="multiple" id="<?php echo esc_attr( $args[ 'id' ] ) ; ?>" class="wc-customer-search <?php echo esc_attr( $args[ 'class' ] ) ; ?>" data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ) ; ?>" data-minimum_input_length='3' data-allow_clear="true">
				<?php
				$json_ids = array() ;
				$user_ids = array_filter( ( array ) $args[ 'options' ] ) ;

				if ( rac_check_is_array( $user_ids ) ) {
					foreach ( $user_ids as $userid ) {
						$user = get_user_by( 'id', $userid ) ;
						?>
						<option value="<?php echo esc_attr( $userid ) ; ?>" selected="selected"><?php echo esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) ; ?></option>
						<?php
					}
				} else {
					?>
					<option value=""></option>
					<?php
				}
				?>
			</select>
		<?php } else { ?>

			<input type="hidden" class="wc-customer-search <?php echo esc_attr( $args[ 'class' ] ) ; ?>" name="<?php echo esc_attr( $args[ 'id' ] ) ; ?>" id="<?php echo esc_attr( $args[ 'id' ] ) ; ?>" data-multiple="true" data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ) ; ?>" data-selected="
			<?php
			$json_ids = array() ;
			$user_ids = array_filter( ( array ) $args[ 'options' ] ) ;

			foreach ( $user_ids as $userid ) {
				$user = get_user_by( 'id', $userid ) ;
				if ( is_object( $user ) ) {
					$json_ids[ $user->ID ] = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) ;
				}
			}

			echo esc_attr( json_encode( $json_ids ) ) ;
			?>
				   " value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ) ; ?>" data-minimum_input_length='3' data-allow_clear="true" />

			<?php
		}
	}

}

if ( ! function_exists( 'rac_product_search' ) ) {

	function rac_product_search( $args ) {
		$args = wp_parse_args(
				$args, array(
			'class'       => '',
			'id'          => '',
			'name'        => '',
			'placeholder' => '',
			'options'     => array(),
				)
				) ;

		global $woocommerce ;

		if ( ( float ) $woocommerce->version > ( float ) ( '2.2.0' ) && ( float ) $woocommerce->version < ( float ) ( '3.0.0' ) ) {
			?>
			<input type="hidden" class="wc-product-search <?php echo esc_attr( $args[ 'class' ] ) ; ?>" id="<?php echo esc_attr( $args[ 'id' ] ) ; ?>"  name="<?php echo esc_attr( $args[ 'id' ] ) ; ?>" data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ) ; ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="
			<?php
			$json_ids    = array() ;
			$product_ids = array_filter( ( array ) $args[ 'options' ] ) ;

			foreach ( $product_ids as $product_id ) {
				$product = fp_rac_get_product( $product_id ) ;
				if ( is_object( $product ) ) {
					$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() ) ;
				}
			}

			echo esc_attr( json_encode( $json_ids ) ) ;
			?>
				   " value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ) ; ?>" />

		<?php } else { ?>
			<select multiple name="<?php echo esc_attr( $args[ 'id' ] ) ; ?>[]" id='<?php echo esc_attr( $args[ 'id' ] ) ; ?>' class="wc-product-search <?php echo esc_attr( $args[ 'class' ] ) ; ?>" data-minimum_input_length='3' data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ) ; ?>" data-action="woocommerce_json_search_products_and_variations">
				<?php
				$product_ids = array_filter( ( array ) $args[ 'options' ] ) ;

				if ( rac_check_is_array( $product_ids ) ) {
					foreach ( $product_ids as $product_id ) {
						echo '<option value="' . esc_attr( $product_id ) . '" ' ;
						selected( 1, 1 ) ;
						echo '> #' . absint( $product_id ) . ' &ndash; '
						. esc_html( get_the_title( $product_id ) ) ;
					}
				} else {
					?>
					<option value=""></option>
					<?php
				}
				?>
			</select>
			<?php
		}
	}

}

if ( ! function_exists( 'fp_rac_common_function_for_search_products' ) ) {

	function fp_rac_common_function_for_search_products( $id_name, $label, $options = false ) {
		global $woocommerce ;

		if ( ( float ) $woocommerce->version > ( float ) ( '2.2.0' ) && ( float ) $woocommerce->version < ( float ) ( '3.0.0' ) ) {
			?>
			<?php if ( $label ) { ?>
				<label><?php echo esc_html( $label ) ; ?>:</label> 
			<?php } ?>
			<input type="hidden" class="wc-product-search" id="<?php echo esc_attr( $id_name ) ; ?>"  name="<?php echo esc_attr( $id_name ) ; ?>[]" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'recoverabandoncart' ) ; ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="
			<?php
			$json_ids        = array() ;
			$list_of_produts = ( $options ) ? $options : get_option( $id_name ) ;
			if ( '' != $list_of_produts ) {
				if ( ! is_array( $list_of_produts ) ) {
					$product_ids = array_filter( array_map( 'absint', ( array ) explode( ',', $list_of_produts ) ) ) ;
				} else {
					$product_ids = $list_of_produts ;
				}

				foreach ( $product_ids as $product_id ) {
					$product = fp_rac_get_product( $product_id ) ;
					if ( is_object( $product ) ) {
						$json_ids[ $product_id ] = wp_kses_post( $product->get_formatted_name() ) ;
					}
				} echo esc_attr( json_encode( $json_ids ) ) ;
			}
			?>
				   " value="<?php echo esc_attr( implode( ',', array_keys( $json_ids ) ) ) ; ?>" />
			   <?php } else { ?>
				   <?php if ( $label ) { ?>
				<label><?php echo esc_html( $label ) ; ?>:</label> 
			<?php } ?>
			<select multiple name="<?php echo esc_attr( $id_name ) ; ?>[]" id='<?php echo esc_attr( $id_name ) ; ?>' class="wc-product-search" data-minimum_input_length='3' data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'recoverabandoncart' ) ; ?>" data-action="woocommerce_json_search_products_and_variations">
				<?php
				$list_of_produts = ( $options ) ? $options : get_option( $id_name ) ;
				if ( rac_check_is_array( $list_of_produts ) ) {
					foreach ( $list_of_produts as $rac_free_id ) {
						echo '<option value="' . esc_attr( $rac_free_id ) . '" ' ;
						echo selected( 1, 1 ) ;
						echo '> #' . absint( $rac_free_id ) . ' &ndash; '
						. esc_html( get_the_title( $rac_free_id ) ) . '</option>' ;
					}
				} else {
					?>
					<option value=""></option>
					<?php
				}
				?>
			</select>                    
			<?php
			   }
	}

}
