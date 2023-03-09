<?php
/**
 * Admin Settings Class.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'RAC_Settings' ) ) {

	/**
	 * Class
	 */
	class RAC_Settings {

		/**
		 * Setting pages.
		 * 
		 * @var array
		 */
		private static $settings = array() ;

		/**
		 * Errors.
		 * 
		 * @var array
		 */
		private static $errors = array() ;

		/**
		 * Plugin slug.
		 * 
		 * @var string
		 */
		private static $plugin_slug = 'rac' ;

		/**
		 * Messages.
		 * 
		 * @var array
		 */
		private static $messages = array() ;

		/**
		 * Include the settings page classes.
		 */
		public static function get_settings_pages() {
			if ( ! empty( self::$settings ) ) {
				return self::$settings ;
			}

			include_once RAC_PLUGIN_PATH . '/inc/abstracts/abstract-rac-settings-page.php' ;

			$settings = array() ;
			$tabs     = self::settings_page_tabs() ;

			foreach ( $tabs as $tab_name ) {
				$settings[ str_replace( '-', '_', $tab_name ) ] = include 'tabs/' . sanitize_key( $tab_name ) . '.php' ;
			}

			self::$settings = apply_filters( sanitize_key( self::$plugin_slug . '_get_settings_pages' ), $settings ) ;

			return self::$settings ;
		}

		/**
		 * Add a message.
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text ;
		}

		/**
		 * Add an error.
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text ;
		}

		/**
		 * Output messages + errors.
		 */
		public static function show_messages() {
			if ( count( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					self::error_message( $error ) ;
				}
			} elseif ( count( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					self::success_message( $message ) ;
				}
			}
		}

		/**
		 * Show an success message.
		 */
		public static function success_message( $text, $echo = true ) {
			ob_start() ;
			$contents = '<div id="message " class="updated inline ' . esc_html( self::$plugin_slug ) . '_save_msg"><p><strong>' . esc_html( $text ) . '</strong></p></div>' ;
			ob_end_clean() ;

			if ( $echo ) {
				echo wp_kses_post( $contents ) ;
			} else {
				return $contents ;
			}
		}

		/**
		 * Show an error message.
		 */
		public static function error_message( $text, $echo = true ) {
			ob_start() ;
			$contents = '<div id="message" class="error inline"><p><strong>' . esc_html( $text ) . '</strong></p></div>' ;
			ob_end_clean() ;

			if ( $echo ) {
				echo wp_kses_post( $contents ) ;
			} else {
				return $contents ;
			}
		}

		/**
		 * Settings page tabs.
		 * 
		 * @return array
		 */
		public static function settings_page_tabs() {

			return array(
				'general',
				'advanced',
				'prevoius-orders',
				'coupon',
				'shortcode',
				'support'
					) ;
		}

		/**
		 * Handles the display of the settings page in admin.
		 */
		public static function output() {
			global $current_section, $current_tab ;

			do_action( sanitize_key( self::$plugin_slug . '_settings_start' ) ) ;

			$tabs = rac_get_allowed_setting_tabs() ;

			/* Include admin html settings */
			include_once( 'views/html-settings.php' ) ;
		}

		/**
		 * Handles the display of the settings page buttons in page.
		 */
		public static function output_buttons( $reset = true ) {

			/* Include admin html settings buttons */
			include_once( 'views/html-settings-buttons.php' ) ;
		}

		/**
		 * Output admin fields.
		 */
		public static function output_fields( $value ) {

			if ( ! isset( $value[ 'type' ] ) || 'rac_custom_fields' != $value[ 'type' ] ) {
				return ;
			}

			$value[ 'id' ]                = isset( $value[ 'id' ] ) ? $value[ 'id' ] : '' ;
			$value[ 'css' ]               = isset( $value[ 'css' ] ) ? $value[ 'css' ] : '' ;
			$value[ 'desc' ]              = isset( $value[ 'desc' ] ) ? $value[ 'desc' ] : '' ;
			$value[ 'title' ]             = isset( $value[ 'title' ] ) ? $value[ 'title' ] : '' ;
			$value[ 'class' ]             = isset( $value[ 'class' ] ) ? $value[ 'class' ] : '' ;
			$value[ 'default' ]           = isset( $value[ 'default' ] ) ? $value[ 'default' ] : '' ;
			$value[ 'name' ]              = isset( $value[ 'name' ] ) ? $value[ 'name' ] : $value[ 'id' ] ;
			$value[ 'placeholder' ]       = isset( $value[ 'placeholder' ] ) ? $value[ 'placeholder' ] : '' ;
			$value[ 'without_label' ]     = isset( $value[ 'without_label' ] ) ? $value[ 'without_label' ] : false ;
			$value[ 'custom_attributes' ] = isset( $value[ 'custom_attributes' ] ) ? $value[ 'custom_attributes' ] : '' ;

			// Custom attribute handling.
			$custom_attributes = rac_format_custom_attributes( $value ) ;

			// Description handling.
			$field_description = WC_Admin_Settings::get_field_description( $value ) ;
			$description       = $field_description[ 'description' ] ;
			$tooltip_html      = $field_description[ 'tooltip_html' ] ;

			// Switch based on type
			switch ( $value[ 'rac_field' ] ) {

				case 'button':
					?>
					<tr valign="top">
						<?php if ( ! $value[ 'without_label' ] ) : ?>
							<th scope="row">
								<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
							</th>
						<?php endif ; ?>
						<td>
							<button
								id="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"
								type="<?php echo esc_attr( $value[ 'rac_field' ] ) ; ?>"
								class="<?php echo esc_attr( $value[ 'class' ] ) ; ?>"
								<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ) ; ?>
								><?php echo esc_html( $value[ 'default' ] ) ; ?> </button>
								<?php echo wp_kses_post( $description ) ; ?>
						</td>
					</tr>
					<?php
					break ;

				case 'time_value':
					$option_value = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td>
							<input
								id="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"
								name="<?php echo esc_attr( $value[ 'name' ] ) ; ?>"
								type="text"
								data-min = "0.01"
								class="fp_text_min_max fp-rac-cart-time <?php echo esc_attr( $value[ 'class' ] ) ; ?>"
								<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ) ; ?>
								value="<?php echo esc_html( $option_value ) ; ?>"/>                      
								<?php
					break ;

				case 'time_type':
					$option_value = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
							<select
								id="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"
								name="<?php echo esc_attr( $value[ 'name' ] ) ; ?>"
								class="fp-rac-cart-time-type <?php echo esc_attr( $value[ 'class' ] ) ; ?>"
								<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ) ; ?>
								>
						<?php foreach ( $value[ 'options' ] as $key => $value ) : ?>
									<option value="<?php echo esc_attr( $key ) ; ?>" <?php selected( $key, $option_value ) ; ?>><?php echo esc_html( $value ) ; ?></option>
								<?php endforeach ; ?>
							</select>
							<?php echo wp_kses_post( $description ) ; ?>
						</td>
					</tr>
					<?php
					break ;

				case 'upload_image':
					$option_value = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td>
							<input placeholder="<?php echo esc_attr( $value[ 'placeholder' ] ) ; ?>" type="text" id="<?php echo esc_attr( $value[ 'id' ] ) ; ?>" class="fp-rac-upload-image-text <?php echo esc_attr( $value[ 'class' ] ) ; ?>" name="<?php echo esc_attr( $value[ 'id' ] ) ; ?>" value="<?php echo esc_attr( $option_value ) ; ?>"/>
							<input type="button" class="button-secondary fp-rac-upload-image-btn" data-button="<?php echo esc_attr( $value[ 'button_label' ] ) ; ?>" data-title="<?php echo esc_attr( $value[ 'button_title' ] ) ; ?>" value="<?php echo esc_attr( $value[ 'button_label' ] ) ; ?>"/>
							<?php echo wp_kses_post( $description ) ; ?>
						</td>
					</tr>
					<?php
					break ;

				case 'image_width':
					$option_value = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td>
							<input type="number" name="<?php echo esc_attr( $value[ 'id' ] ) ; ?>[width]" class="rac_product_img_size rac_product_img_size_width" min="0" value="<?php echo esc_attr( $option_value[ 'width' ] ) ; ?>">
							<span class="fp-rac-product-img-size-concat"><b>x</b></span>
							<input type="number" name="<?php echo esc_attr( $value[ 'id' ] ) ; ?>[height]" class="rac_product_img_size rac_product_img_size_height" min="0" value="<?php echo esc_attr( $option_value[ 'height' ] ) ; ?>">
							<span class="fp-rac-product-img-size-concat"><b><?php esc_html_e( 'px', 'recoverabandoncart' ) ; ?></b></span>
							<?php echo wp_kses_post( $description ) ; ?>
						</td>
					</tr>
					<?php
					break ;

				case 'customer_search':
					$option_value       = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td>
							<?php
							$value[ 'options' ] = $option_value ;
							rac_customer_search( $value ) ;
							echo wp_kses_post( $description ) ;
							?>
						</td>
					</tr>
					<?php
					break ;

				case 'product_search':
					$option_value       = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td>
							<?php
							$value[ 'options' ] = $option_value ;
							rac_product_search( $value ) ;
							echo wp_kses_post( $description ) ;
							?>
						</td>
					</tr>
					<?php
					break ;

				case 'orderstatuses':
					$option_value = get_option( $value[ 'id' ], $value[ 'default' ] ) ;
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value[ 'id' ] ) ; ?>"><?php echo esc_html( $value[ 'title' ] ) ; ?></label><?php echo wp_kses_post( $tooltip_html ) ; ?>
						</th>
						<td class="rac_automatic_pre_orders_fields">
							<p><input type = "checkbox" <?php echo ( in_array( 'wc-on-hold', $option_value ) ) ? 'checked="checked"' : '' ; ?>name = "rac_auto_order_status[]" value = "wc-on-hold"><?php esc_html_e( 'On hold status', 'recoverabandoncart' ) ; ?></p>
							<p><input type = "checkbox" <?php echo ( in_array( 'wc-pending', $option_value ) ) ? 'checked="checked"' : '' ; ?>name = "rac_auto_order_status[]" value = "wc-pending"><?php esc_html_e( 'Pending payment status', 'recoverabandoncart' ) ; ?></p>
							<p><input type = "checkbox" <?php echo ( in_array( 'wc-failed', $option_value ) ) ? 'checked="checked"' : '' ; ?>name = "rac_auto_order_status[]" value = "wc-failed" ><?php esc_html_e( 'Failed status', 'recoverabandoncart' ) ; ?></p>
							<p><input type = "checkbox" <?php echo ( in_array( 'wc-cancelled', $option_value ) ) ? 'checked="checked"' : '' ; ?>name = "rac_auto_order_status[]" value = "wc-cancelled"><?php esc_html_e( 'Cancelled status', 'recoverabandoncart' ) ; ?></p>
							<?php echo wp_kses_post( $description ) ; ?>
						</td>
					</tr>
					<?php
					break ;
			}
		}

		/**
		 * Save the setting fields.
		 * 
		 * @return mixed
		 */
		public static function prepare_field_value( $value, $option, $raw_value ) {

			if ( ! isset( $option[ 'type' ] ) || 'rac_custom_fields' != $option[ 'type' ] ) {
				return $value ;
			}

			$value = null ;

			// Format the value based on option type.
			switch ( $option[ 'rac_field' ] ) {
				case 'customer_search':
				case 'image_width':
				case 'product_search':
				case 'orderstatuses':
					$value = array_filter( ( array ) $raw_value ) ;
					break ;
				case 'time_value':
				case 'time_type':
				case 'upload_image':
					$value = $raw_value ;
					break ;
			}

			return $value ;
		}

		/**
		 * Reset the setting fields.
		 * 
		 * @return bool
		 */
		public static function reset_fields( $options ) {
			if ( ! is_array( $options ) ) {
				return false ;
			}

			// Loop options and get values to reset.
			foreach ( $options as $option ) {
				if ( ! isset( $option[ 'id' ] ) || ! isset( $option[ 'type' ] ) || ! isset( $option[ 'default' ] ) ) {
					continue ;
				}

				update_option( $option[ 'id' ], $option[ 'default' ] ) ;
			}

			return true ;
		}

	}

}
