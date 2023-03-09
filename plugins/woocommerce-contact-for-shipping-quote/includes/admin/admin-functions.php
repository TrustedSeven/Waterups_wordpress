<?php
namespace WooCommerce_Contact_for_Shipping_Quote\Admin;
use function WooCommerce_Contact_for_Shipping_Quote\get_new_shipping_quote_count;
use WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote;
use function WooCommerce_Contact_for_Shipping_Quote\get_shipping_quote;

/**
 * Enqueue scripts.
 *
 * Enqueue script as javascript and style sheets.
 *
 * @since  1.0.0
 */
function admin_enqueue_scripts( $pagenow ) {
	wp_register_style( 'woocommerce-contact-for-shipping-quote', plugins_url( 'assets/admin/css/style.min.css', \WooCommerce_Contact_for_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote()->file ), array(), \WooCommerce_Contact_for_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote()->version );
	wp_register_script( 'woocommerce-contact-for-shipping-quote', plugins_url( 'assets/admin/js/main.js', \WooCommerce_Contact_for_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote()->file ), array( 'jquery' ), \WooCommerce_Contact_for_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote()->version, true );

	wp_localize_script( 'woocommerce-contact-for-shipping-quote', 'wccsq', array(
		'nonce' => wp_create_nonce( 'wccsq-action' ),
	) );

	if ( $pagenow == 'woocommerce_page_woocommerce-contact-for-shipping-quote' || ( isset( $_GET['tab'], $_GET['instance_id'] ) && $_GET['tab'] === 'shipping' ) ) {
		wp_enqueue_style( 'woocommerce-contact-for-shipping-quote' );
		wp_enqueue_script( 'woocommerce-contact-for-shipping-quote' );
	}
}
add_action( 'admin_enqueue_scripts', 'WooCommerce_Contact_for_Shipping_Quote\Admin\admin_enqueue_scripts' );


/**
 * Add screen ID to WC list.
 *
 * Add the shipping quotes page to the list of WC page screen IDs to load in certain
 * assets by default.
 *
 * @since 1.0.0
 *
 * @param $screen_ids
 * @return array
 */
function add_wc_screen_id( $screen_ids ) {
	$screen_ids[] = 'woocommerce_page_woocommerce-contact-for-shipping-quote';

	return $screen_ids;
}
add_filter( 'woocommerce_screen_ids', 'WooCommerce_Contact_for_Shipping_Quote\Admin\add_wc_screen_id' );


/**
 * AJAX action handler.
 *
 * Handle the AJAX calls for shipping quote actions.
 *
 * @since 1.0.0
 */
function shipping_quote_action_handler() {
	if ( ! isset( $_POST['nonce'] ) ) {
		return;
	}

	check_admin_referer( 'wccsq-action', 'nonce' );

	$action = sanitize_key( $_POST['quote_action'] );
	$quote_id  = absint( $_POST['quote_id'] );
	$quote = Shipping_Quote::read( $quote_id );

	$html = '';
	$success = true;

	switch (true) {
		case 'delete' == $action :
			$quote->delete();
			wp_send_json( array( 'success' => 1, 'html' => null ) );
			break;

		case 'update_quotation_amount' == $action :
			$quote->set_quote_amount( $_POST['quote_amount'] );
			$quote->set_status( 'pending' );
			$quote->update();
			break;

		case strpos( $action, 'update_status-' ) === 0 :
			$status = str_replace( 'update_status-', '', $action );
			$quote->set_status( $status );
			$quote->update();
			break;
	}

	WC()->mailer()->get_emails(); // Init WC Emails to add trigger
	do_action( 'WCCSQ/shipping_quote/action/' . $action, $quote );

	if ( empty( $html ) ) {
		ob_start();
			include 'views/html-shipping-quote-row.php';
		$html = ob_get_clean();

	}

	wp_send_json( array( 'success' => $success, 'html' => $html ) );
	die;
}
add_action( 'wp_ajax_wccsq_shipping_quote_action', '\WooCommerce_Contact_for_Shipping_Quote\Admin\shipping_quote_action_handler' );


/**
 * Add menu count.
 *
 * Add a menu count for new shipping quotes.
 */
function shipping_quote_menu_count() {
	global $submenu;

	if ( isset( $submenu['woocommerce'] ) ) {
		$quote_count = get_new_shipping_quote_count();

		// Add count if user has access.
		if ( apply_filters( 'WCCSQ/shipping_quote_count_in_menu', true ) && current_user_can( 'manage_woocommerce' ) && $quote_count ) {
			foreach ( $submenu['woocommerce'] as $key => $menu_item ) {
				if ( $menu_item[2] == 'woocommerce-contact-for-shipping-quote' ) {
					$submenu['woocommerce'][ $key ][0] .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $quote_count ) . '"><span class="processing-count">' . number_format_i18n( $quote_count ) . '</span></span>';
					break;
				}
			}
		}
	}
}
add_action( 'admin_head', '\WooCommerce_Contact_for_Shipping_Quote\Admin\shipping_quote_menu_count' );


/**
 * Get variation item data.
 *
 * Get the variation item data in a list from a quote item.
 *
 * @since 1.3.0
 *
 * @param  array          $item  Item data as stored in the quote.
 * @param  Shipping_Quote $quote Quote object.
 * @return array                 Variation item data, if any.
 */
function get_variation_item_data( $item, $quote = array() ) {
	$id         = $item['variation_id'] ?: $item['product_id'];
	$product    = wc_get_product( $id );
	$item_data = array();

	// Variation values are shown only if they are not found in the title as of 3.0.
	// This is because variation titles display the attributes.
	if ( $product && $product->is_type( 'variation' ) && is_array( $item['variation'] ) ) {
		foreach ( $item['variation'] as $name => $value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				// If this is a term slug, get the term's nice name.
				$term = get_term_by( 'slug', $value, $taxonomy );
				if ( ! is_wp_error( $term ) && $term && $term->name ) {
					$value = $term->name;
				}
				$label = wc_attribute_label( $taxonomy );
			} else {
				// If this is a custom option slug, get the options name.
				$value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $product );
				$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $product );
			}

			$item_data[] = array(
				'key'   => $label,
				'value' => $value,
			);
		}
	}

	// Filter item data to allow 3rd parties to add more to the array.
	$item_data = apply_filters( 'WCCSQ/admin/shipping_quote/variation_item_data', $item_data, $item, $quote );

	return $item_data;
}


/**
 * Show email preview.
 *
 * Show a email preview of one of the Contact for Shipping Quote emails.
 *
 * @since NEWVERSION
 */
function email_preview () {
	if ( ! isset( $_GET['action'], $_GET['nonce'], $_GET['quote'] ) || $_GET['action'] !== 'wccsq-email-preview' ) {
		return;
	}

	check_admin_referer( 'wccsq-email-preview', 'nonce' );

	$quote_id = absint( $_GET['quote'] );
	$quote    = get_shipping_quote( $quote_id );
	$email    = array_reduce( WC()->mailer()->get_emails(), function ( $email, $m ) {
		return $_GET['email'] == $m->id ? $m : $email;
	} );

	/** @var Shipping_Quote $email */
	if ( $email instanceof \WC_Email ) {
		$email->setup_locale();
		$email->object = $quote;
		echo apply_filters( 'woocommerce_mail_content', $email->style_inline( $email->get_content_html() ) );
		$email->restore_locale();

		die;
	}
}
add_action( 'admin_init', '\WooCommerce_Contact_for_Shipping_Quote\Admin\email_preview' );
