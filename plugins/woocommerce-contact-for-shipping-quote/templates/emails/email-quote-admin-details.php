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

?><table id="quote-details" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 20px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=woocommerce-contact-for-shipping-quote&status=new' ) ); ?>" target="_blank"><?php _e( 'View new shipping quote requests', 'woocommerce-contact-for-shipping-quote' ); ?></a>
			</p>

		</td>
	</tr>
</table>
