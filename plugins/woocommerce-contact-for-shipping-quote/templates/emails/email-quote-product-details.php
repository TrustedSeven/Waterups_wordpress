<?php
/**
 * Quote product details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-quote-product-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.1.0
 *
 * @var \WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote $quote
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$text_align = is_rtl() ? 'right' : 'left';

do_action( 'WCCSQ/email/before_quote_table', $quote, $sent_to_admin, $plain_text, $email ); ?>

<h2><?php echo wp_kses_post( __( 'Quote items', 'woocommerce-contact-for-shipping-quote' ) ); ?></h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" bquote="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$text_align  = is_rtl() ? 'right' : 'left';
			$margin_side = is_rtl() ? 'left' : 'right';

			$items               = $quote->get_cart_contents();
			$show_sku            = false;
			$show_image          = false;
			$image_size          = array( 32, 32 );
			$plain_text          = false;

			foreach ( $items as $item_id => $item ) :
				$product       = wc_get_product( $item['variation_id'] ?: $item['product_id'] );
				$sku           = '';
				$purchase_note = '';
				$image         = '';

				if ( ! apply_filters( 'WCCSQ/email/quote_item_visible', true, $item ) ) {
					continue;
				}

				if ( is_object( $product ) ) {
					$sku           = $product->get_sku();
					$image         = $product->get_image( $image_size );
				}

				?>
				<tr class="<?php echo esc_attr( apply_filters( 'WCCSQ/email/item_class', 'order_item', $item, $quote ) ); ?>">
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
					<?php

					// Show title/image etc.
					if ( $show_image ) {
						echo wp_kses_post( apply_filters( 'WCCSQ/email/item_thumbnail', $image, $item ) );
					}

					// Product name.
					echo wp_kses_post( apply_filters( 'WCCSQ/email/item_name', $product->get_name(), $item, false ) );

					// SKU.
					if ( $show_sku && $sku ) {
						echo wp_kses_post( ' (#' . $sku . ')' );
					}

					// allow other plugins to add additional product information here.
					do_action( 'WCCSQ/email/quote_item_meta_start', $item_id, $item, $quote, $plain_text );

					// allow other plugins to add additional product information here.
					do_action( 'WCCSQ/email/quote_item_meta_end', $item_id, $item, $quote, $plain_text );

					?>
					</td>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
						<?php echo wp_kses_post( apply_filters( 'WCCSQ/email/quote_item_quantity', $item['quantity'], $item ) ); ?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>

<?php do_action( 'WCCSQ/email/after_quote_table', $quote, $sent_to_admin, $plain_text, $email ); ?>
