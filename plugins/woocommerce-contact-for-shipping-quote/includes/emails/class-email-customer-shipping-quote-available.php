<?php
namespace WooCommerce_Contact_for_Shipping_Quote;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( '\WooCommerce_Contact_for_Shipping_Quote\Email_Customer_Shipping_Quote_Available' ) ) :

	/**
	 * Customer Completed Order Email.
	 *
	 * Order complete emails are sent to the customer when the order is marked complete and usual indicates that the order has been shipped.
	 *
	 * @since 1.1.0
	 */
	class Email_Customer_Shipping_Quote_Available extends \WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_shipping_quote_available';
			$this->customer_email = true;
			$this->title          = __( 'Shipping quote available', 'woocommerce-contact-for-shipping-quote' );
			$this->description    = __( 'Shipping quote available emails are sent to customers when a quote amount has been provided for their requested shipping quote.', 'woocommerce-contact-for-shipping-quote' );
			$this->template_html  = 'emails/customer-shipping-quote-available.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
			);

			// Triggers for this email.
			add_action( 'WCCSQ/shipping_quote/action/update_quotation_amount', array( $this, 'trigger' ), 10 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Force HTML type.
		 */
		public function get_email_type() {
			return 'html';
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param Shipping_Quote $quote Shipping quote that has been created.
		 */
		public function trigger( $quote ) {
			$this->setup_locale();

			if ( is_a( $quote, '\WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote' ) ) {
				$this->object                          = $quote;
				$this->recipient                       = $this->object->get_customer_email();
				$this->placeholders['{quote_created}'] = wc_format_datetime( $this->object->get_created() );
				$this->placeholders['{quote_amount}']  = $this->object->get_quote_amount();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get email subject.
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your {site_title} shipping quote is now available', 'woocommerce-contact-for-shipping-quote' );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Shipping quote available', 'woocommerce-contact-for-shipping-quote' );
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html, array(
					'quote'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $this,
				), '', plugin_dir_path( WOOCOMMERCE_CONTACT_FOR_SHIPPING_QUOTE_FILE ) . 'templates/'
			);
		}


		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'woocommerce-contact-for-shipping-quote' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'woocommerce-contact-for-shipping-quote' ),
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'woocommerce-contact-for-shipping-quote' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce-contact-for-shipping-quote' ), '<code>{site_title}, {quote_date}, {quote_amount}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'woocommerce-contact-for-shipping-quote' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce-contact-for-shipping-quote' ), '<code>{site_title}, {quote_date}, {quote_amount}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
			);
		}
	}

endif;

return new Email_Customer_Shipping_Quote_Available();
