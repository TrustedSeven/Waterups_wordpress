<?php
/**
 * Email Address.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-quote-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.1.0
 * @var \WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote $quote
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$text_align = is_rtl() ? 'right' : 'left';
$address = apply_filters( 'WCCSQ/quote/formatted_shipping_address', $quote->get_address(), $quote );
$address = WC()->countries->get_formatted_address( $address );


?><table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
			<h2><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>

			<address class="address">
				<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'woocommerce' ) ); ?>
				<?php if ( $quote->get_customer_email() ) : ?>
					<br/><?php echo esc_html( $quote->get_customer_email() ); ?>
				<?php endif; ?>

				<?php if ( $quote->get_customer_phone() ) : ?>
					<br/><?php echo esc_html( $quote->get_customer_phone() ); ?>
				<?php endif; ?>
			</address>
		</td>
	</tr>
</table>
