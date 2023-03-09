<?php
/**
 * Customer shipping quote available email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-shipping-quote-requested.php.
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


/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html__( 'Hi,', 'woocommerce' ); ?></p>
<p><?php esc_html_e( 'A new shipping quote has been requested by a customer. Details can be found below.', 'woocommerce-contact-for-shipping-quote' ); ?></p>
<?php

/*
 * @hooked \WooCommerce_Contact_for_Shipping_Quote\Emails::quote_details() Shows the order details table.
 * @hooked \WooCommerce_Contact_for_Shipping_Quote\Emails::quote_product_details() Shows the order details table.
 */
do_action( 'woocommerce_email_shipping_quote_details', $quote, $sent_to_admin, $plain_text, $email );

/*
 * @hooked \WooCommerce_Contact_for_Shipping_Quote\Emails::quote_customer_address() Shows customer details
 */
do_action( 'woocommerce_email_shipping_quote_customer_details', $quote, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
