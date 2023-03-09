<?php
/**
 * Quote details/actions shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-quote-details.php.
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
$order = $quote->get_order();

?><table id="quote-details" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 20px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
			<p><?php
				printf(
					wp_kses(
					/* translators: %1$s Site title */
						__( 'Your shipping quote: %1$s', 'woocommerce-contact-for-shipping-quote' ),
						array( 'a' => array( 'href' => array() ) )
					),
					'<strong>' . wc_price( $quote->get_quote_amount() ) . '</strong>'
				);
				?>
				<br/><?php
				if ( empty( $order ) ) :
					?><a href="<?php echo esc_url( $quote->get_cart_recover_url() ); ?>" target="_blank"><?php _e( 'Complete your order', 'woocommerce-contact-for-shipping-quote' ); ?></a><?php
				else :
					?><a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" target="_blank"><?php _e( 'Pay for your order', 'woocommerce-contact-for-shipping-quote' ); ?></a><?php
				endif;
			?></p>

		</td>
	</tr>
</table>
