<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( '\WooCommerce_Contact_for_Shipping_Quote\WCCSQ_Shipping_Quote_Method' ) ) {
	class WCCSQ_Shipping_Quote_Method extends \WC_Shipping_Method {

		/**
		 * @var string Shipping method title when the quote is ready.
		 */
		public $title_quote_ready;

		/**
		 * Constructor for your shipping class
		 *
		 * @param int $instance_id
		 */
		public function __construct( $instance_id = 0 ) {
			parent::__construct( $instance_id );

			$this->id                 = 'custom_shipping_quote';
			$this->method_title       = __( 'Contact for shipping quote', 'woocommerce-contact-for-shipping-quote' );
			$this->method_description = __( 'Allow customers to contact you for a shipping quote.', 'woocommerce-contact-for-shipping-quote' );
			$this->enabled            = "yes";

			$this->supports           = array(
				'shipping-zones',
				'instance-settings',
			);
			$this->init();

			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

			// Change order button text (maybe)
			add_filter( 'woocommerce_order_button_text', array( $this, 'order_button_text' ) );

			// Change order button behaviour (maybe)
			add_filter( 'woocommerce_order_button_html', array( $this, 'order_button_html' ) );
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			$this->instance_form_fields = $this->get_settings();
			$this->title                = $this->get_option( 'title' );
			$this->title_quote_ready    = $this->get_option( 'title_quote_ready' );
			$this->tax_status           = $this->get_option( 'tax_status' );
			$this->cost                 = $this->get_option( 'cost' );
		}

		public function get_settings() {

			$categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
			$category_options = ! is_wp_error( $categories ) ? wp_list_pluck( $categories, 'name', 'term_id' ) : array();
			$shipping_classes = get_terms( 'product_shipping_class', array( 'hide_empty' => false ) );
			$shipping_class_options = ! is_wp_error( $shipping_classes ) ? wp_list_pluck( $shipping_classes, 'name', 'term_id' ) : array();

			$settings = array(
				'title'                 => array(
					'title'       => __( 'Method title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'Contact us to get a shipping quote', 'woocommerce' ),
					'desc_tip'    => true,
				),
                'title_quote_ready' => array(
					'title'       => __( 'Title when quote is available', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'A optional different title when the shipping quote is available for the customer.', 'woocommerce' ),
					'default'     => __( '', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'description'           => array(
					'title'       => __( 'Description', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'The description is displayed below the shipping title on the cart/checkout pages.', 'woocommerce' ),
					'default'     => '[request_link text="Request a quote"] [refresh_link text="Refresh"]',
					'desc_tip'    => true,
				),
				'tax_status'            => array(
					'title'   => __( 'Tax status', 'woocommerce' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select-nostd',
					'default' => 'taxable',
					'options' => array(
						'taxable' => __( 'Taxable', 'woocommerce' ),
						'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
					),
				),
				'contact_popup_page_id' => array(
					'title'             => __( 'Popup page', 'woocommerce' ),
					'type'              => 'single_select_page',
					'class'             => 'wc-enhanced-select',
					'placeholder'       => '',
					'description'       => __( 'Select a page to display in a popup after someone presses the \'request\' link. Leave empty to not open a popup.', 'woocommerce-contact-for-shipping-quote' ),
					'default'           => '',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'data-allow_clear' => true,
					),
				),
				'enable_when'           => array(
					'title'             => __( 'Enable when cart contains', 'woocommerce-contact-for-shipping-quote' ),
					'type'              => 'multiselect',
					'placeholder'       => __( '', 'woocommerce-contact-for-shipping-quote' ),
					'description'       => __( 'Only enable this shipping option when the cart contains one of the selected categories/shipping classes.', 'woocommerce-contact-for-shipping-quote' ),
					'default'           => '',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select shipping classes/Categories', 'woocommerce-contact-for-shipping-quote' ),
					),
					'class'             => 'wc-enhanced-select',
					'options'           => array( 'Shipping class' => $shipping_class_options, 'Category' => $category_options ),
				),
				'exclude_methods'       => array(
					'title'             => __( 'Exclude other methods', 'woocommerce-contact-for-shipping-quote' ),
					'type'              => 'multiselect',
					'placeholder'       => __( 'Shipping options to exclude', 'woocommerce-contact-for-shipping-quote' ),
					'description'       => 'Exclude other shipping options when this option is available.',
					'default'           => '',
					'desc_tip'          => true,
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select shipping options to exclude when this option is available.', 'woocommerce-contact-for-shipping-quote' ),
					),
					'class'             => 'wc-enhanced-select',
					'options'           => $this->get_shipping_options(),
				),
				'additional_features'    => array(
					'title'             => __( 'Additional features', 'woocommerce-contact-for-shipping-quote' ),
					'type'              => 'additional_features',
					'placeholder'       => __( '', 'woocommerce-contact-for-shipping-quote' ),
					'description'       => __( 'Additional features only applied when this rate is selected', 'woocommerce-contact-for-shipping-quote' ),
					'default'           => '',
					'desc_tip'          => true,
					'class'             => 'wc-enhanced-select',
					'options'           => array(
						'hide_payment_gateways'       => __( 'Hide payment gateways', 'woocommerce-contact-for-shipping-quote' ),
						'order_button_hidden'         => __( 'Hide order button', 'woocommerce-contact-for-shipping-quote' ),
						'order_button_request_quote'  => __( 'Order button requests shipping quote', 'woocommerce-contact-for-shipping-quote' ),
						'order_button_text'           => __( 'Change order button text', 'woocommerce-contact-for-shipping-quote' ),
						'order_button_text_requested' => __( 'Change order button text (quote requested)', 'woocommerce-contact-for-shipping-quote' ),
//						'pay_order_later'             => __( 'Pay order later *experimental*', 'woocommerce-contact-for-shipping-quote' ),
					),
				),
				'order_button_text'     => array(
					'type'              => 'hidden',
					'sanitize_callback' => function( $v ) { return $this->validate_text_field( 'order_button_text', $v ); },
				),
				'order_button_text_requested' => array(
					'type'              => 'hidden',
					'sanitize_callback' => function( $v ) { return $this->validate_text_field( 'order_button_text_requested', $v ); },
				),
			);

			return apply_filters( 'WCCSQ/shipping_method/settings', $settings, $this );
		}


		/**
		 * Get shipping options list.
		 *
		 * Get a list of the shipping options within the zone.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		protected function get_shipping_options() {
			$shipping_options = array();
			foreach ( WC()->shipping()->get_shipping_methods() as $method ) {
				$shipping_options['Methods (all options of this type)'][ $method->id ] = $method->get_method_title();
			}

			return $shipping_options;
		}


		/**
		 * Get chosen methods.
		 *
		 * Get (all) the chosen methods for the cart.
		 *
		 * @since 1.4.0
		 *
		 * @return array List of chosen shipping rate IDs.
		 */
		protected function get_chosen_methods() {
			$packages = WC()->shipping()->get_packages();

			$chosen_methods = array();
			// Get chosen methods for each package to get our totals.
			foreach ( $packages as $key => $package ) {
				$chosen_method = wc_get_chosen_shipping_method_for_package( $key, $package );
				if ( $chosen_method ) {
					$chosen_methods[ $key ] = $package['rates'][ $chosen_method ]->get_instance_id();
				}
			}

			return $chosen_methods;
		}


		/**
		 * Is this rate chosen?
		 *
		 * @since 1.4.0
		 *
		 * @return bool Whether this shipping rate is the chosen one.
		 */
		protected function method_chosen() {
			return in_array( $this->get_instance_id(), $this->get_chosen_methods() );
		}


		/**
		 * Change order button text.
		 *
		 * Maybe change the order button text when the quote is not yet available.
		 *
		 * @since 1.4.0
		 *
		 * @param  string $text Original button text.
		 * @return string       Modified button text.
		 */
		public function order_button_text( $text ) {
			if ( ! $this->method_chosen() ) {
				return $text;
			}

			$quote = $this->get_quote();
			$additional_features = (array) $this->get_option( 'additional_features' );

			if ( ( ! $quote || $quote->get_status_slug() !== 'pending' ) && in_array( 'order_button_text', $additional_features ) ) {
				$new_text = $this->get_option( 'order_button_text' );
			}

			if ( $quote && $quote->get_status_slug() === 'new' && in_array( 'order_button_text_requested', $additional_features ) ) {
				$new_text = $this->get_option( 'order_button_text_requested' );
			}

			if ( ! empty( $new_text ) ) {
				$text = $new_text;
			}

			return $text;
		}


		/**
		 * Change order button HTML.
		 *
		 * Maybe change the order button html when to request the shipping quote or hide it.
		 *
		 * @since 1.4.0
		 *
		 * @param  string $html Original button HTML.
		 * @return string       Modified button HTML.
		 */
		public function order_button_html( $html ) {
			if ( ! $this->method_chosen() ) {
				return $html;
			}

			$additional_features = (array) $this->get_option( 'additional_features' );

			$quote = $this->get_quote();
			// Replace order button with custom class when quote does not exist
			if ( ! $quote && in_array( 'order_button_request_quote', $additional_features ) ) {
				$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
				$html = '<button type="submit" class="button alt wccsq-contact-link" id="place_order">' . wp_kses_post( $order_button_text ) . '</button>';
			}

			if ( in_array( 'order_button_hidden', $additional_features ) ) {
				$html = '';
			}

			return $html;
		}


		/**
		 * Calculate the shipping costs.
		 *
		 * @param array $package Package of items from cart.
		 */
		public function calculate_shipping( $package = array() ) {
			$rate = array(
				'id'      => $this->get_rate_id(),
				'label'   => $this->title,
				'cost'    => 0,
				'package' => $package,
			);

			// Try to get an existing quote
			$quote = Shipping_Quote::read_by( array( 'address_hash' => get_address_hash(), 'cart_hash' => get_cart_hash( $package['contents'] ), 'status' => array( 'new', 'pending' )  ), array( '%s', '%s', '%s' ) );

			if ( $quote ) {
			    // Set cost
				$rate['cost'] = $quote->get_quote_amount();

				// Set title
				if ( $quote->get_status_slug() == 'pending' && $this->title_quote_ready ) {
				    $rate['label'] = $this->title_quote_ready;
			    }

				$rate['meta_data']['quote_id'] = $quote->get_id();
			}

			$this->add_rate( $rate );

			/**
			 * Developers can add additional flat rates based on this one via this action since @version 2.4.
			 *
			 * Previously there were (overly complex) options to add additional rates however this was not user.
			 * friendly and goes against what Flat Rate Shipping was originally intended for.
			 */
			do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $rate );
		}


		/**
		 * Check if method meets requirements.
		 *
		 * Check if the shipping rate meets the configured shipping class / category requirements.
		 *
		 * @since 1.0.0
		 *
		 * @param array $package Package details.
		 * @return mixed|void
		 */
		public function is_available( $package ) {
			if ( ! parent::is_available( $package ) ) {
				return false;
			}

			$is_available = false; // False until proven true.
			$enable_when  = $this->get_option( 'enable_when' );

			if ( empty( $enable_when ) ) {
				$is_available = true;
			} else {
				$shipping_class_ids = array_filter( $enable_when, function ( $requirement ) {
					$term = get_term( $requirement );
					return $term && $term->taxonomy == 'product_shipping_class';
				} );

				$categories = array_filter( $enable_when, function ( $requirement ) {
					$term = get_term( $requirement );
					return $term && $term->taxonomy == 'product_cat';
				} );

				// WPML @todo - does this work?
				$categories = array_map( function ( $value ) {
					return apply_filters( 'wpml_object_id', $value, 'product_cat', true );
				}, $categories );

				// WPML @todo - does this work?
				$shipping_class_ids = array_map( function ( $value ) {
					return apply_filters( 'wpml_object_id', $value, 'product_shipping_class', true );
				}, $shipping_class_ids );

				foreach ( $package['contents'] as $cart_item ) {
					/** @var \WC_Product $product */
					$product = $cart_item['data'];

					if ( ! empty( $shipping_class_ids ) && in_array( $product->get_shipping_class_id(), $shipping_class_ids ) ) {
						$is_available = true;
						break;
					}

					$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
					if ( ! empty( $categories ) && has_term( $categories, 'product_cat', $product_id ) ) {
						$is_available = true;
						break;
					}
				}
			}

			// Check for field requirements
			$required_fields = get_option( 'shipping_quote_required_fields', array() );
			if ( ! empty( $required_fields ) ) {

				foreach ( $required_fields as $k ) {

					$post_data = isset( $_POST['post_data'] ) ? wp_unslash( $_POST['post_data'] ) : '';
					$post_data = wp_parse_args( $post_data );

					if ( method_exists( WC()->customer, 'get_shipping_' . $k ) ) {
						$v = call_user_func( array( WC()->customer, 'get_shipping_' . $k ) );
					} elseif ( $k === 'email' ) {
						$v = WC()->customer->get_billing_email() ?: $post_data['billing_email'] ?? '';
					} elseif ( $k === 'phone' ) {
						$v = WC()->customer->get_billing_phone() ?: $post_data['billing_phone'] ?? '';
					}

					if ( empty( $v ) ) {
						$is_available = false;
					}
				}
			}

			return apply_filters( 'WCCSQ/shipping_method/is_available', $is_available, $this, $package );
		}


		/**
		 * Get formatted shipping description.
		 *
		 * Get a formatted description with update/contact links accordingly.
		 *
		 * @since 1.0.0
		 *
		 * @return string
		 */
		public function get_description() {

			add_shortcode( 'request_link', function( $atts ) {
				$atts = shortcode_atts( array(
					'text' => __( 'Request a quote', 'woocommerce-contact-for-shipping-quote' ),
					'hide_when_requested' => false,
					'hide_when_available' => false,
					'class'               => '',
				), $atts );

				return '<a href="#" class="wccsq-contact-link ' . esc_attr( $atts['class'] ) . ($atts['hide_when_requested'] ? ' hide-if-requested' : '') . ( $atts['hide_when_available'] ? ' hide-if-available' : '' ) . '">' . wp_kses_post( $atts['text'] ) . '</a>';
			});

			add_shortcode( 'refresh_link', function( $atts ) {
				$atts = shortcode_atts( array(
					'text' => __( 'Refresh', 'woocommerce-contact-for-shipping-quote' ),
					'show_when_requested' => true,
					'hide_when_available' => false,
					'class'               => '',
				), $atts );

				return '<a href="#" class="wccsq-refresh-link ' . esc_attr( $atts['class'] ) . ($atts['show_when_requested'] ? ' show-if-requested' : '') . ( $atts['hide_when_available'] ? ' hide-if-available' : '' ) . '">' . wp_kses_post( $atts['text'] ) . '</a>';
			});

			$description = do_shortcode( $this->get_option( 'description' ) );

			remove_shortcode( 'refresh_link' );
			remove_shortcode( 'request_link' );

			return $description;
		}


		/**
		 * Get the quote.
		 *
		 * Get the quote of the shipping method instance.
		 *
		 * @since 1.0.0
		 *
		 * @param  array               $package Shipping package list.
		 * @return bool|Shipping_Quote          Shipping quote instance.
		 */
		public function get_quote( $package = null ) {
			return Shipping_Quote::read_by( array( 'status' => array( 'new', 'pending' ), 'address_hash' => get_address_hash(), 'cart_hash' => get_cart_hash( $package['contents'] ?? null ) ), array( '%s', '%s', '%s' ) );
		}


		/**
		 * Single select page field.
		 *
		 * Custom field type for single select page, inspired by WC Cores function with changes;
		 * - Allow custom attributes
		 * - Different name/ID (needed?)
		 *
		 * Close duplicate of WC_Admin_Settings::output_fields()
		 *
		 * @param $key
		 * @param $data
		 * @return false|string
		 */
		public function generate_single_select_page_html( $key, $data ) {
			$field_key = $this->get_field_key( $key );

			$data = wp_parse_args( $data, array(
				'name'             => $field_key,
				'id'               => $key,
				'sort_column'      => 'menu_order',
				'sort_order'       => 'ASC',
				'show_option_none' => ' ',
				'echo'             => false,
				'selected'         => $this->get_option( $key, $data['default'] ),
				'post_status'      => 'publish,private,draft',

				'title'             => '',
				'desc'              => '',
				'disabled'          => false,
				'class'             => '',
				'css'               => '',
				'placeholder'       => '',
				'type'              => 'text',
				'desc_tip'          => false,
				'description'       => '',
				'custom_attributes' => array(),
			) );

			// Custom attribute handling.
			$custom_attributes = array();

			if ( ! empty( $data['custom_attributes'] ) && is_array( $data['custom_attributes'] ) ) {
				foreach ( $data['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling.
			$field_description = \WC_Admin_Settings::get_field_description( $data );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];

			ob_start();
				?><tr valign="top" class="single_select_page">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
					</th>
					<td class="forminp">
						<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'woocommerce' ) . "' style='" . $data['css'] . "' class='" . $data['class'] . "' " . $this->get_custom_attribute_html( $data ) . " id=", wp_dropdown_pages( $data ) ); ?> <?php echo $description; ?>
					</td>
				</tr><?php

			return ob_get_clean();
		}


		/**
		 * Checkout behaviour field.
		 *
		 * A custom field type for 'additional_features'. The setting itself is a standard dropdown,
		 * but it is also desirable to have additional fields that have classes at the table-row level.
		 *
		 * @since 1.4.0
		 *
		 * @param  string $key  Field ID.
		 * @param  array  $data List of data.
		 * @return string       Field HTML.
		 */
		public function generate_additional_features_html( $key, $data ) {
			ob_start();
			echo $this->generate_multiselect_html( $key, $data );

			$extra_options = array(
				'order_button_text'     => array(
					'title'             => __( 'Order button text', 'woocommerce-contact-for-shipping-quote' ),
					'type'              => 'text',
					'placeholder'       => __( '', 'woocommerce-contact-for-shipping-quote' ),
					'description'       => __( 'Order button text when quote rate is selected and the quote has not been requested ', 'woocommerce-contact-for-shipping-quote' ),
					'default'           => '',
					'desc_tip'          => true,
				),
				'order_button_text_requested' => array(
					'title'             => __( 'Order button text - Quote requested', 'woocommerce-contact-for-shipping-quote' ),
					'type'              => 'text',
					'placeholder'       => __( '', 'woocommerce-contact-for-shipping-quote' ),
					'description'       => __( 'Order button text when quote rate is selected and the quote has been requested', 'woocommerce-contact-for-shipping-quote' ),
					'default'           => '',
					'desc_tip'          => true,
				),
			);

			foreach ( $extra_options as $key => $data ) {
				$field_key = $this->get_field_key( $key );
				$defaults  = array(
					'title'             => '',
					'disabled'          => false,
					'class'             => '',
					'css'               => '',
					'placeholder'       => '',
					'type'              => 'text',
					'desc_tip'          => false,
					'description'       => '',
					'custom_attributes' => array(),
				);

				$data = wp_parse_args( $data, $defaults );

				?><tr valign="top" class="hidden show-if-checkout-behaviour-<?php echo esc_attr( $key ); ?>">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?> />
						<?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
					</fieldset>
				</td>
				</tr><?php
			}

			return ob_get_clean();
		}

		public function generate_hidden_html( $key, $data ) {
			return;
		}

		/**
		 * Validate custom field type.
		 *
		 * @param $key
		 * @param $value
		 * @return array|string
		 */
		public function validate_additional_features_field( $key, $value ) {
			return $this->validate_multiselect_field( $key, $value );
		}

	}
}
