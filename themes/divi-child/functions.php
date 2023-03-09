<?php
function theme_enqueue_styles() {
    wp_enqueue_style( 'Divi-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'Divi-child-style', get_stylesheet_directory_uri() . '/style.css', array('Divi-style')  );

    wp_register_script ( 'custom-script', get_stylesheet_directory_uri() . '/custom.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script ( 'custom-script' );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', PHP_INT_MAX );

/**
 * -----------------------------------------------------------------------------
 * WC remove sidebar for woocommerce pages
 */
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar',10);

/**
 * -----------------------------------------------------------------------------
 * WC Override theme default specification for product # per row
 */
add_filter('loop_shop_columns', 'loop_columns');
if (!function_exists('loop_columns')) {
	function loop_columns() {
		return 3; // 3 products per row
	}
}

/**
 * -----------------------------------------------------------------------------
 * WC Add term and conditions check box on registration form
 */
add_action( 'woocommerce_register_form', 'add_terms_and_conditions_to_registration', 20 );
function add_terms_and_conditions_to_registration() {

    $terms_page_id = wc_get_page_id( 'terms' );

    if ( $terms_page_id > 0 ) {
        $terms         = get_post( $terms_page_id );
        $terms_content = has_shortcode( $terms->post_content, 'woocommerce_checkout' ) ? '' : wc_format_content( $terms->post_content );

        if ( $terms_content ) {
            echo '<div class="woocommerce-terms-and-conditions" style="display: none; max-height: 200px; overflow: auto;">' . $terms_content . '</div>';
        }
        ?>
        <p class="form-row terms wc-terms-and-conditions">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); ?> id="terms" /> <span><?php printf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank" class="woocommerce-terms-and-conditions-link">terms &amp; conditions</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'terms' ) ) ); ?></span> <span class="required">*</span>
            </label>
            <input type="hidden" name="terms-field" value="1" />
        </p>
    <?php
    }
}

/**
 * -----------------------------------------------------------------------------
 * WC Validate required term and conditions check box
 */
add_action( 'woocommerce_register_post', 'terms_and_conditions_validation', 20, 3 );
function terms_and_conditions_validation( $username, $email, $validation_errors ) {
    if ( ! isset( $_POST['terms'] ) )
        $validation_errors->add( 'terms_error', __( 'Terms and condition are not checked!', 'woocommerce' ) );

    return $validation_errors;
}

/**
 * -----------------------------------------------------------------------------
 * WC Disable checkout when no shipping method
 *
function disable_checkout_button_no_shipping() {

     if( WC()->cart->needs_shipping() ) {
        // get shipping packages and their rate counts
        $package_counts = array();
        $packages = WC()->shipping->get_packages();
        foreach( $packages as $key => $pkg ) {
            $package_counts[ $key ] = count( $pkg[ 'rates' ] );
        }

        // remove button if any packages are missing shipping options
        if( in_array( 0, $package_counts ) ) {
            remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
        }
    }

}
add_action( 'woocommerce_proceed_to_checkout', 'disable_checkout_button_no_shipping', 1 );
function prevent_checkout_access_no_shipping() {
    // Check that WC is enabled and loaded
    if( function_exists( 'is_checkout' ) && is_checkout() && WC()->cart->needs_shipping() ) {
        // get shipping packages and their rate counts
        $packages = WC()->cart->get_shipping_packages();
        foreach( $packages as $key => $pkg ) {
            $calculate_shipping = WC()->shipping->calculate_shipping_for_package( $pkg );
            if( empty( $calculate_shipping['rates'] ) ) {
                wp_redirect( esc_url( WC()->cart->get_cart_url() ) );
                exit;
            }
        }
    }
}
add_action( 'wp', 'prevent_checkout_access_no_shipping' );
*/

/**
 * -----------------------------------------------------------------------------
 * WC define the woocommerce_cart_totals_taxes_total_html callback
 */
function filter_woocommerce_cart_totals_taxes_total_html( $wc_price ) {
    $subtotal = WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
    $shipping = WC()->cart->get_shipping_total() + WC()->cart->get_shipping_tax();
    $total_tax = WC()->cart->get_subtotal_tax() + WC()->cart->get_shipping_tax();
    $total = $subtotal + $shipping;

    return "<span>" . wc_price($total) . " <small>(incl. " .wc_price($total_tax). " GST)</small>" . "</span>";
};
add_filter( 'woocommerce_cart_totals_taxes_total_html', 'filter_woocommerce_cart_totals_taxes_total_html', 10, 1 );

/**
 * -----------------------------------------------------------------------------
 * WC change email already registered text
 */
function my_woocommerce_add_error( $error ) {
    return str_replace('An account is already registered with your email address. Please log in','Email is already registered. If you are a returning customer, please log in.',$error);
}
add_filter( 'woocommerce_add_error', 'my_woocommerce_add_error' );

if (! function_exists('push_to_datalayer')){
    function push_to_datalayer() {
        global $wp_query;
        if ( !empty( $wp_query->query_vars[ 'order-received' ] ) ){
			//$order_id = $_GET['order_id'];
            $order = wc_get_order( $wp_query->query_vars[ 'order-received' ] );
            ?>
            <script type='text/javascript'>
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'order_confirmation',
                    'google_tag_params': {
                    'ecomm_prodid': [
                    <?php 
                    foreach ( $order->get_items() as $key => $item ) :
                        echo $item['product_id'] . ',';                            
                    endforeach;
                    ?>
                    ],
                        'ecomm_pagetype': 'Confirmation', 
                        'ecomm_totalvalue': '<?php echo $order->get_total(); ?>'
                    },
                    'transactionId': '<?php echo $order->get_order_number(); ?>',
                    'trasactionAffiliation': '', 
                    'transactionTotal': '<?php echo $order->get_total(); ?>', 
                    'transactionTax': '<?php echo number_format($order->get_total_tax(), 2 ,".", ""); ?>',
                    'transactionShipping': '<?php echo number_format($order->calculate_shipping(), 2 , ".", ""); ?>',
                    'transactionProducts':[
                    <?php
                    foreach ( $order->get_items() as $key => $item ) :
                    $product = $order->get_product_from_item($item);
                    $variant_name = ($item['variation_id']) ? wc_get_product($item['variation_id']) : '';
                        ?>
                    {
                        'name' : '<?php echo $item['name']; ?>',
                        'sku' : '<?php echo $item['product_id']; ?>',
                        'price' : '<?php echo number_format($order->get_line_subtotal($item), 2, ".", ""); ?>',
                        'category' : '<?php echo strip_tags($product->get_categories(', ', '', '')); ?>',
                        'variant' : '<?php echo ($variant_name) ? implode("-" , $variant_name->get_variation_attributes()) : ''; ?>',
                        'quantity' : <?php echo $item['qty']; ?>
                    },
                    <?php endforeach; ?>
                    ]
                });
            </script>
                        
            <?php
        }

        if(is_product()) {
            global $post;
            $product = wc_get_product( $post->ID ); ?>
            <script type='text/javascript'>
                console.log('working');
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    'event': 'view-content',
                    'facebook-pixel-tracking': {
                        'productID': '<?php echo $product->get_sku(); ?>',
                        'price': '<?php echo $product->get_price(); ?>',
                        'name': '<?php echo $product->get_name(); ?>'
                    }
                })
            </script>
            <?php
        }
    }
}

add_action('wp_head' , 'push_to_datalayer');

function facebook_meta_tags() {
    ?><meta name="facebook-domain-verification" content="9c6oqhu9fbvlumxcrartc36ak8hh2u" />
    <?php
}

add_action('wp_head', 'facebook_meta_tags');

add_filter( 'use_widgets_block_editor', '__return_false' );