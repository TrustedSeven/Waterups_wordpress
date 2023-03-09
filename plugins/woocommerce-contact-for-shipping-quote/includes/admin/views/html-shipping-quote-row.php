<?php
/**
 * @var Shipping_Quote $quote
 */
use WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote;
use function WooCommerce_Contact_for_Shipping_Quote\Admin\get_variation_item_data;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$cart = $quote->get_cart_contents();
	$cart_total = array_reduce( $cart, function( $total, $item ) {
		return $total += ($item['line_total'] + $item['line_tax']);
	}, 0 );

	?><tr data-quote-id="<?php echo absint( $quote->get_id() ); ?>">
		<td class="customer">
			<div class="customer-name"><?php
				echo $quote->get_customer_name();
				if ( $customer_id = $quote->get_customer_id() ) :
					echo ' <a href="' . get_edit_user_link( $customer_id ) . '"><small>' . sprintf( '(#%d)', $customer_id ) . '</small></a>';
				endif;
			?></div>

			<div class="customer-email"><?php
				if ( $email = $quote->get_customer_email() ) :
					?>E: <a href="mailto: <?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><?php
				endif;
			?></div>

			<div class="customer-phone"><?php
				if ( $phone = $quote->get_customer_phone() ) :
					?>T: <a href="tel: <?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a><?php
				endif;
			?></div><?php

			?><div class="customer-address"><?php
				$address = WC()->countries->get_formatted_address( $quote->get_address() );
				echo esc_html( preg_replace( '/<br\s*\/?>/i', ', ', $address ) );
			?></div>
		</td>
		<td class="status"><?php
			if ( $order = $quote->get_order() ) :
				?><a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" class="tips" data-tip="<?php echo sprintf( __( 'Order #%s', 'woocommerce-contact-for-shipping-quote' ), $order->get_order_number() ); ?>"><?php
			endif;

			?><mark class="shipping-quote-status status-<?php echo esc_attr( $quote->get_status_slug() ); ?>">
				<span><?php echo esc_html( $quote->get_status() ); ?></span>
			</mark><?php
			if ( ! empty( $quote->get_order_id() ) ) :
				?></a><?php
			endif;

			?><div class="wccsq-request-time tips" data-tip="<?php echo $quote->get_created()->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ); ?>">
				<?php echo sprintf( _x( '%s ago', 'XX time ago', 'woocommerce-contact-for-shipping-quote' ), human_time_diff( $quote->get_created()->format( 'U' ) ) ); ?>
			</div><?php
		?></td>
		<td class="cart">
			<a href="javascript:void(0);" class="wccsq-shipping-quote-items"><?php
				echo implode( ', ', array_map( function( $item ) {
					return sprintf( '%s x %d', $item['name'], $item['quantity'] );
				}, $cart ) );
			?></a>
			<table class="wccsq-products-table widefat striped" style="display: none;">
				<tbody><?php
					foreach ( $cart as $item ) :
						$id = $item['variation_id'] ?: $item['product_id'];
						$item_total = wc_price( ($item['line_total'] + $item['line_tax']) );
						$tip = sprintf( __( '%dx %s', 'woocommerce-contact-for-shipping-quote' ), $item['quantity'], strip_tags( wc_price( ($item['line_total'] + $item['line_tax']) / $item['quantity'] ) ) );
						$product = wc_get_product( $id );
						$name = $product ? $product->get_name() : $item['name'];

						?><tr>
							<td class="quantity"><?php echo absint( $item['quantity'] ) . 'x '; ?></td>
							<td class="image"><?php echo get_the_post_thumbnail( $id, array( 25, 25 ) ); ?></td>
							<td class="name"><?php
								if ( $product ) :
									?><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo wp_kses_post( $name ); ?></a><br/><?php
								else :
									echo wp_kses_post( $name ) . '<br/>';
								endif;

								$item_data = get_variation_item_data( $item, $quote );
								// Output flat or in list format.
								if ( count( $item_data ) > 0 ) :
									foreach ( $item_data as $data ) :
										echo '<strong>' . esc_html( $data['key'] ) . ':</strong> ' . wp_kses_post( $data['value'] ) . "<br/>";
									endforeach;
								endif;

								?>
							</td>
							<td class="total">
								<span class="tips" data-tip="<?php echo $tip; ?>"><?php echo $item_total; ?></span>
							</td>
						</tr><?php
					endforeach;
				?></tbody>
				<tfoot>
					<tr>
						<td colspan="4" style="text-align: right; border-color: #f0f0f1;"><?php _e( 'Subtotal', 'woocommerce-contact-for-shipping-quote' ); ?>: <?php echo wc_price( $cart_total ); ?></td>
					</tr>
				</tfoot>
			</table>
		</td>
		<td class="quoted-amount">
			<form class="shipping-quote-actions">
				<input type="hidden" name="quote_id" value="<?php echo $quote->get_id(); ?>">
				<input type="hidden" name="quote_action" value="update_quotation_amount">

				<div class="quoted-amount-price"><?php
					if ( ! empty( $quote->get_quote_amount() ) ) :
						echo wc_price( $quote->get_quote_amount() );
					else :
						echo '&dash;';
					endif;
				?></div>
				<input type="text" class="short wc_input_price hidden quote_amount" name="quote_amount" value="<?php echo $quote->get_quote_amount(); ?>">
				<a href="javascript:void(0);" class="save-quoted-amount hidden"><?php _e( 'Save', 'woocommerce-contact-for-shipping-quote' ); ?></a>
				<a href="javascript:void(0);" class="cancel-quoted-amount hidden"><?php _e( 'Cancel', 'woocommerce-contact-for-shipping-quote' ); ?></a>
				<a href="javascript:void(0);" class="edit-quoted-amount"><?php _e( 'Edit', 'woocommerce-contact-for-shipping-quote' ); ?></a>
			</form>
		</td>
		<td class="actions">
			<form class="shipping-quote-actions">
				<input type="hidden" name="quote_id" value="<?php echo $quote->get_id(); ?>">

				<select name="quote_action">
					<option value=""><?php _e( 'Choose an action...', 'woocommerce' ); ?></option>

					<optgroup label="Status update"><?php
						foreach ( \WooCommerce_Contact_for_Shipping_Quote\get_statuses() as $slug => $status ) :
							?><option value="update_status-<?php echo esc_attr( $slug ); ?>"><?php echo sprintf( __( 'Update to %s', 'woocommerce-contact-for-shipping-quote' ), $status ); ?></option><?php
						endforeach;
					?></optgroup>

					<option value="delete"><?php _e( 'Delete', 'woocommerce-contact-for-shipping-quote' ); ?></option>
				</select>
				<button class="button wc-reload"><span><?php _e( 'Apply', 'woocommerce' ); ?></span></button>
			</form>
		</td>
	</tr>

<?php

	do_action( 'WCCSQ/admin/shipping_quote/after_row', $quote );

if ( get_option( 'shipping_quote_debug_mode' ) == 'yes' ) {
	?><tr class="notice-error" style="background: #fef7f1;">
		<td colspan="2" style="vertical-align: top; box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.1);">
			<strong>Quote ID:</strong><br/>
			<?php echo $quote->get_id(); ?><br/>
			<strong>Address hash:</strong><br/>
			<?php echo $quote->get_address_hash(); ?><br/>
			<strong>Cart hash:</strong><br/>
			<?php echo $quote->get_cart_hash(); ?><br/>
			<strong>Address</strong><br/>
			<pre><?php print_r( $quote->get_address() ); ?></pre>
		</td>
		<td colspan="2" class="" style="box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.1);">
			<strong>Cart data:</strong><br/>
			<pre><?php print_r( $quote->get_cart_contents() ); ?></pre>
		</td>
		<td style="vertical-align: top;">
			<a target="_blank" href="<?php echo $quote->get_cart_recover_url(); ?>"><?php _e( 'Recover cart', 'woocommerce-contact-for-shipping-quote' ); ?></a><br/><?php
			$url = add_query_arg( array(
				'action' => 'wccsq-email-preview',
				'quote'  => $quote->get_id(),
				'nonce'  => wp_create_nonce( 'wccsq-email-preview' )
			), admin_url() );
			?><a href="<?php echo add_query_arg( 'email', 'shipping_quote_requested', $url ); ?>"><?php
				_e( 'Preview \'Quote requested\' email', 'woocommerce-contact-for-shipping-quote' );
			?></a><br/>
			<a href="<?php echo add_query_arg( 'email', 'customer_shipping_quote_available', $url ); ?>"><?php
				_e( 'Preview \'Quote available\' email', 'woocommerce-contact-for-shipping-quote' );
			?></a><br/>
		</td>
	</tr><?php
}
