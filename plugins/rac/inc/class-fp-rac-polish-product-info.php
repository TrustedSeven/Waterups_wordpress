<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Polish_Product_Info' ) ) {

	/**
	 * FP_RAC_Polish_Product_Info Class.
	 *
	 */
	class FP_RAC_Polish_Product_Info {

		//Polish the Product Info using Cart Details

		public static function fp_rac_extract_cart_details( $each_cart, $fp_rac_mail_purpose ) {
			ob_start() ;
			$class_names           = $fp_rac_mail_purpose ? array( 'fp-rac-email-product-info-table' ) : array( 'fp-rac-product-info-table' ) ;
			$td_class_names        = $fp_rac_mail_purpose ? array( 'fp-rac-email-product-info-td' ) : array( 'fp-rac-product-info-td' ) ;
			$td_class_names[]      = $fp_rac_mail_purpose ? self::enable_border() : 'td' ;
			$lang                  = is_object( $each_cart ) && isset( $each_cart->wpml_lang ) ? $each_cart->wpml_lang : 'en' ;
			$curreny_code          = is_object( $each_cart ) && isset( $each_cart->currency_code ) ? $each_cart->currency_code : '' ;
			?>
			<table class="<?php echo esc_attr( implode( ' ' , $class_names ) ) ; ?> " cellspacing="0" cellpadding="6">
				<thead>
					<?php self::fp_rac_get_sortable_column_name( $lang ) ; ?>
				</thead>
				<tbody>
					<?php
					$tax                   = '0' ;
					$subtotal              = '0' ;
					$shipping              = '0' ;
					$tax_total             = '0' ;
					$total_points          = '0' ;
					$shipping_total        = '0' ;
					$shipping_tax_cost     = '0' ;
					$shipping_method_title = '' ;
					if ( $each_cart ) {
						$cart_array = fp_rac_format_cart_details( $each_cart->cart_details , $each_cart ) ;
						if ( is_array( $cart_array ) && ( ! empty( $cart_array ) ) ) {
							$shipping_total        = ( float ) self::fp_rac_get_shipping_total( $cart_array ) ;
							$shipping_tax_cost     = ( float ) self::fp_rac_get_shipping_tax_total( $cart_array ) ;
							$shipping_method_title = self::fp_rac_get_shipping_method_tilte( $cart_array , $lang ) ;
							$shipping              = self::fp_rac_get_shipping_details( $shipping_total , $shipping_method_title , $shipping_tax_cost , $curreny_code , $each_cart ) ;
							if ( isset( $cart_array[ 'shipping_details' ] ) ) {
								unset( $cart_array[ 'shipping_details' ] ) ;
							}
							if ( isset( $cart_array[ 'woocs_is_multipled' ] ) ) {
								unset( $cart_array[ 'woocs_is_multipled' ] ) ;
							}
							if ( isset( $cart_array[ 0 ][ 'cart' ] ) ) {
								$cart_array = $cart_array[ 0 ][ 'cart' ] ;
								if ( rac_check_is_array( $cart_array ) ) {
									$compact_total = self::fp_rac_add_table_rows( $cart_array , $curreny_code , $lang , $each_cart ) ;
									extract( $compact_total ) ;
								}
							} elseif ( is_array( $cart_array ) && ( ! empty( $cart_array ) ) ) {
								if ( isset( $cart_array[ 'visitor_mail' ] ) ) {
									unset( $cart_array[ 'visitor_mail' ] ) ;
								}
								if ( isset( $cart_array[ 'first_name' ] ) ) {
									unset( $cart_array[ 'first_name' ] ) ;
								}
								if ( isset( $cart_array[ 'last_name' ] ) ) {
									unset( $cart_array[ 'last_name' ] ) ;
								}
								if ( isset( $cart_array[ 'visitor_phone' ] ) ) {
									unset( $cart_array[ 'visitor_phone' ] ) ;
								}
								$compact_total = self::fp_rac_add_table_rows( $cart_array , $curreny_code , $lang , $each_cart ) ;
								extract( $compact_total ) ;
							}
						} elseif ( is_object( $cart_array ) ) {
							$old_order_obj = new FP_RAC_Previous_Order_Data( $each_cart ) ;
							if ( $old_order_obj->get_cart_content() ) {
								$cart_array            = $old_order_obj->get_items() ;
								$shipping_tax_cost     = $old_order_obj->get_shipping_tax() ;
								$shipping_total        = $old_order_obj->get_total_shipping() ;
								$shipping_method_title = $old_order_obj->get_shipping_method() ;
								$shipping              = self::fp_rac_get_shipping_details( $shipping_total , $shipping_method_title , $shipping_tax_cost , $curreny_code , $each_cart ) ;
								if ( rac_check_is_array( $cart_array ) ) {
									$compact_total = self::fp_rac_add_table_rows( $cart_array , $curreny_code , $lang , $each_cart ) ;
									extract( $compact_total ) ;
								}
							}
						}
						$shipping_check = '' != $shipping_method_title && 'yes' != get_option( 'rac_hide_shipping_row_product_info_shortcode' ) ;
						$tax_check      = $tax > 0 && 'yes' != get_option( 'rac_hide_tax_row_product_info_shortcode' ) ;
						$tax_total      = $tax + $shipping_tax_cost ;
						$total_coupon   = fp_rac_check_sumo_coupon_exists( $subtotal + $tax_total + $shipping_total ) ;
					} else {
						$product_name = 'Product A' ;
						if ( 'no' != get_option( 'rac_troubleshoot_sku_sh' ) ) {
							$product_name = $product_name . ' (#PRODSAMP-SKU)' ;
						}
						$shipping_check = 'yes' != get_option( 'rac_hide_shipping_row_product_info_shortcode' ) ;
						$tax_check      = 'no' == get_option( 'rac_inc_tax_with_product_price_product_info_shortcode' ) && 'yes' != get_option( 'rac_hide_tax_row_product_info_shortcode' ) ;
						$subtotal       = 10 ;
						$shipping       = 10 ;
						$shipping_total = 10 ;
						$tax_total      = 1 ;
						$total_points   = 0 ;
						$total_coupon   = 0 ;
						self::fp_split_rac_items_in_cart( $product_name , fp_rac_placeholder_img() , '1' , fp_rac_format_price( 10 ) ) ;
					}
					?>
				</tbody>
				<?php if ( 'yes' != get_option( 'rac_hide_tax_total_product_info_shortcode' ) ) { ?>
					<tfoot>
						<tr>
							<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( fp_get_wpml_text( 'rac_product_info_subtotal' , $lang , get_option( 'rac_product_info_subtotal' , 'Subtotal' ) , 'admin_texts_rac_product_info_subtotal' ) ) ; ?></th>
							<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( fp_rac_format_price( $subtotal , $curreny_code , null , $each_cart ) ) ; ?></td>
						</tr>
						<?php if ( $total_points > 0 ) { ?>
							<tr>
								<?php
								$rewards_mesaage = fp_get_wpml_text( 'rs_total_earned_point_caption' , $lang , get_option( 'rs_total_earned_point_caption' ) , 'admin_texts_rs_total_earned_point_caption' ) ;
								?>
								<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( $rewards_mesaage ) ; ?></th>
								<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( $total_points ) ; ?></td>
							</tr>
						<?php } ?>
						<?php if ( ! empty( $total_coupon ) ) { ?>
							<tr>
								<?php
								$coupon_message = get_option( 'sumo_earn_purchase_message_in_cart_page_for_cart_total' ) ;
								$coupon_message = fp_get_wpml_text( 'sumo_earn_purchase_message_in_cart_page_for_cart_total' , $lang , $coupon_message , 'admin_texts_sumo_earn_purchase_message_in_cart_page_for_cart_total' ) ;
								$coupon_message = str_replace( '[coupon_value]' , '' , $coupon_message ) ;
								$coupon_value   = ( 'percent' == $total_coupon[ 'coupon_type' ] ) ? $total_coupon[ 'coupon_value' ] . '%' : fp_rac_format_price( $total_coupon[ 'coupon_value' ] , $curreny_code , null , $each_cart ) ;
								?>
								<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( $coupon_message ) ; ?></th>
								<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( $coupon_value ) ; ?></td>
							</tr>
						<?php } ?>
						<?php if ( $shipping_check ) { ?>
							<tr>
								<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( fp_get_wpml_text( 'rac_product_info_shipping' , $lang , get_option( 'rac_product_info_shipping' , 'Shipping' ) , 'admin_texts_rac_product_info_shipping' ) ) ; ?></th>
								<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( $shipping ) ; ?></td>
							</tr>
						<?php } ?>
						<?php if ( $tax_check ) { ?>
							<tr>
								<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( fp_get_wpml_text( 'rac_product_info_tax' , $lang , get_option( 'rac_product_info_tax' , 'Tax' ) , 'admin_texts_rac_product_info_tax' ) ) ; ?></th>
								<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( fp_rac_format_price( $tax_total , $curreny_code , null , $each_cart ) ) ; ?></td>
							</tr>
						<?php } ?>
						<tr>
							<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php echo esc_html( fp_get_wpml_text( 'rac_product_info_total' , $lang , get_option( 'rac_product_info_total' , 'Total' ) , 'admin_texts_rac_product_info_total' ) ) ; ?></th>
							<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( fp_rac_format_price( ( $subtotal + $tax_total + $shipping_total ) , $curreny_code , null , $each_cart ) ) ; ?></td>
						</tr>
						<?php
						if ( class_exists( 'SUMOPaymentPlans' ) ) {
							$sumo_pp_balance_payable = '' ;
							foreach ( $cart_array as $each_cart ) {
								$saved_array = ( $each_cart[ 'sumo_plugins' ][ 'sumo_pp' ] ) ;
								if ( isset( $saved_array[ 'balance_payable' ] ) && ! empty( $saved_array[ 'balance_payable' ] ) ) {
									$sumo_pp_balance_payable = $saved_array[ 'balance_payable' ] ;
								}
							}
							if ( $sumo_pp_balance_payable ) {
								?>
								<tr>
									<th class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>" scope="row" colspan="<?php echo esc_attr( fp_rac_get_column_span_count() ) ; ?>"><?php esc_html_e( 'Balance Payable Amount' , 'sumopaymentplans' ) ; ?></th>
									<td class="<?php echo esc_attr( implode( ' ' , $td_class_names ) ) ; ?>"><?php echo wp_kses_post( fp_rac_format_price( ( $sumo_pp_balance_payable ) , $curreny_code , null , $each_cart ) ) ; ?></td>
								</tr>
								<?php
							}
						}
						?>
					</tfoot>
				<?php } ?>
			</table>
			<?php
			$contents = ob_get_contents() ;
			ob_end_clean() ;

			return $contents ;
		}

		public static function fp_split_rac_items_in_cart( $product_name, $image, $quantity, $price ) {
			?>
			<tr>
				<?php
				$default_column  = array( 'product_name' , 'product_image' , 'product_quantity' , 'product_price' ) ;
				$sortable_column = get_option( 'drag_and_drop_product_info_sortable_column' ) ;
				$sortable_column = is_array( $sortable_column ) && ! empty( $sortable_column ) ? $sortable_column : $default_column ;
				if ( rac_check_is_array( $sortable_column ) ) {
					foreach ( $sortable_column as $column_key_name ) {
						$product_details = 'product_name' == $column_key_name ? $product_name : ( 'product_image' == $column_key_name ? $image : ( 'product_quantity' == $column_key_name ? $quantity : $price ) ) ;
						if ( 'yes' != get_option( 'rac_hide_' . $column_key_name . '_product_info_shortcode' ) ) {
							?>
							<td class="fp-rac-preview-product-td <?php echo esc_attr( self::enable_border() ) ; ?>" colspan="<?php echo esc_attr( fp_rac_get_column_span_count( 2 ) ) ; ?>">
								<?php echo wp_kses_post( $product_details ) ; ?>
							</td>
							<?php
						}
					}
				}
				?>
			</tr>
			<?php
		}

		/**
		 * Adding table rows and get total and subtotal values.
		 *
		 * @param  Array $cart_array.
		 * @return Array
		 */
		public static function fp_rac_add_table_rows( $cart_array, $curreny_code, $lang, $each_cart ) {
			$tax          = '0' ;
			$subtotal     = '0' ;
			$total_points = '0' ;
			foreach ( $cart_array as $eachproduct ) {
				$product_name = fp_rac_get_compatible_product_name( $eachproduct , $curreny_code , $lang ) ;
				$image        = self::get_product_image( $eachproduct ) ;
				$quantity     = isset( $eachproduct[ 'quantity' ] ) ? $eachproduct[ 'quantity' ] : $eachproduct[ 'qty' ] ;

				if ( 'yes' === get_option( 'rac_inc_tax_with_product_price_product_info_shortcode' ) ) {
					$price    = $eachproduct[ 'line_subtotal' ] + $eachproduct[ 'line_subtotal_tax' ] ;
					$tax      = 0 ;
					$subtotal += $eachproduct[ 'line_subtotal' ] + $eachproduct[ 'line_subtotal_tax' ] ;
				} else {
					$price    = $eachproduct[ 'line_subtotal' ] ;
					$tax      += $eachproduct[ 'line_subtotal_tax' ] ;
					$subtotal += $eachproduct[ 'line_subtotal' ] ;
				}

				$price_total  = fp_rac_get_format_product_price( $price , $curreny_code , $eachproduct , $each_cart ) ;
				extract( $price_total ) ;
				$total_points += $points ;
				self::fp_split_rac_items_in_cart( $product_name , $image , $quantity , $price ) ;
			}

			return array( 'tax' => $tax , 'subtotal' => $subtotal , 'total_points' => $total_points ) ;
		}

		/**
		 * Get Product Name.
		 *
		 * @param  Object $product
		 * @return string $product_name
		 */
		public static function get_product_name( $product ) {
			if ( '2' == get_option( 'rac_var_product_disp_opt' , '1' ) ) {
				$product_id = ( 'no' != get_option( 'rac_email_product_variation_sh' , 'yes' ) && isset( $product[ 'variation_id' ] ) && ! empty( $product[ 'variation_id' ] ) ) ? $product[ 'variation_id' ] : $product[ 'product_id' ] ;
				$_product   = wc_get_product( $product_id ) ;

				if ( is_object( $_product ) ) {
					$product_name = $_product->get_name() ;
					$product_name = self::fp_rac_format_product_name_by_sku( $product_name , $product ) ;
				}
			} else {
				$product_name = get_the_title( $product[ 'product_id' ] ) ;
				$product_name = self::fp_rac_format_product_name_by_sku( $product_name , $product ) ;

				if ( 'no' != get_option( 'rac_email_product_variation_sh' ) ) {

					if ( isset( $product[ 'variation_id' ] ) && ( ! empty( $product[ 'variation_id' ] ) ) ) {
						$product_name = $product_name . '<br />' . self::fp_rac_get_formatted_variation( $product ) ;
					}
				}
			}

			return $product_name ;
		}

		/**
		 * Get Product Image.
		 *
		 * @param  Object $product
		 * @return string $image
		 */
		public static function get_product_image( $product ) {
			$productid              = $product[ 'product_id' ] ;
			$imageurl               = '' ;
			$imagesize              = fp_rac_get_product_image_size() ;
			$variation_thumbnail_id = get_post_thumbnail_id( $product[ 'variation_id' ] ) ;
			if ( ! empty( $variation_thumbnail_id ) ) {
				$image_urls = wp_get_attachment_image_src( $variation_thumbnail_id ) ;
				$imageurl   = $image_urls[ 0 ] ;
			}
			if ( '' == $imageurl ) {
				$thumbnail_id = get_post_thumbnail_id( $productid ) ;
				if ( ! empty( $thumbnail_id ) ) {
					$image_urls = wp_get_attachment_image_src( $thumbnail_id ) ;
					$imageurl   = $image_urls[ 0 ] ;
				} else {
					$imageurl = esc_url( wc_placeholder_img_src() ) ;
				}
			}
			$image = '<img src="' . $imageurl . '" alt="' . get_the_title( $productid ) . '" height="' . $imagesize[ 'height' ] . '" width="' . $imagesize[ 'width' ] . '" />' ;
			return $image ;
		}

		/**
		 * Get the formatted Attribute variations.
		 *
		 * @param  Object Variations.
		 * @return String
		 */
		public static function fp_rac_get_formatted_variation( $variations ) {
			$formatted_attributes = '' ;
			$product_id           = $variations[ 'product_id' ] ;
			$product              = fp_rac_get_product( $variations[ 'variation_id' ] ) ;
			$html_variations      = wc_get_formatted_variation( $product , false ) ;
			$formatted_variations = strip_tags( $html_variations , '<dd><dt>' ) ;
			$attributes           = explode( '</dd>' , $formatted_variations ) ;
			if ( rac_check_is_array( $attributes ) ) {
				foreach ( $attributes as $each_attribute ) {
					$explode_data = explode( ':</dt>' , $each_attribute ) ;
					if ( isset( $explode_data[ 0 ] ) && isset( $explode_data[ 1 ] ) ) {
						$variation            = strip_tags( $explode_data[ 1 ] ) ;
						$attribute            = strip_tags( $explode_data[ 0 ] ) ;
						$formatted_attributes .= wc_attribute_label( $explode_data[ 0 ] , $product ) . ':' . $variation . '<br />' ;
					}
				}
			}
			return $formatted_attributes ;
		}

		/**
		 * Get the Shipping Total.
		 *
		 * @param  array CartContents.
		 * @return float
		 */
		public static function fp_rac_get_shipping_total( $cart_array ) {
			if ( isset( $cart_array[ 'shipping_details' ][ 'shipping_cost' ] ) ) {
				$shipping_total = '' != $cart_array[ 'shipping_details' ][ 'shipping_cost' ] ? $cart_array[ 'shipping_details' ][ 'shipping_cost' ] : ( float ) 0 ;
				return $shipping_total ;
			}
			return '' ;
		}

		/**
		 * Get the Shipping Tax Total.
		 *
		 * @param  array CartContents.
		 * @return float
		 */
		public static function fp_rac_get_shipping_tax_total( $cart_array ) {
			if ( isset( $cart_array[ 'shipping_details' ][ 'shipping_tax_cost' ] ) ) {
				$shipping_tax_cost = '' != $cart_array[ 'shipping_details' ][ 'shipping_tax_cost' ] ? $cart_array[ 'shipping_details' ][ 'shipping_tax_cost' ] : ( float ) 0 ;
				return $shipping_tax_cost ;
			}
			return '' ;
		}

		/**
		 * Get the Shipping method Title.
		 *
		 * @param  array CartContents.
		 * @return string
		 */
		public static function fp_rac_get_shipping_method_tilte( $cart_array, $lang ) {
			if ( isset( $cart_array[ 'shipping_details' ][ 'shipping_method' ] ) ) {
				$current_chosen_method = $cart_array[ 'shipping_details' ][ 'shipping_method' ] ;
				$shipping_method_title = self::fp_rac_api_get_shipping_method_title( $current_chosen_method , $lang ) ;
				return $shipping_method_title ;
			}
			return '' ;
		}

		/**
		 * Get the Shipping method Title.
		 *
		 * @param  boolean $current_chosen_method
		 * @return string
		 */
		public static function fp_rac_api_get_shipping_method_title( $current_chosen_method, $lang ) {
			if ( '' != $current_chosen_method ) {
				$explode_shipping_method_id = explode( ':' , $current_chosen_method ) ;
				$method_id                  = $explode_shipping_method_id[ 0 ] ;
				$instance_id                = isset( $explode_shipping_method_id[ 1 ] ) ? $explode_shipping_method_id[ 1 ] : '' ;
				$wc_shipping                = WC_Shipping::instance() ;
				$wpml_translation_name      = $explode_shipping_method_id[ 0 ] . $explode_shipping_method_id[ 1 ] . '_shipping_method_title' ;
				if ( method_exists( 'WC_Shipping' , 'load_shipping_methods' ) ) {
					$allowed_classes = $wc_shipping->load_shipping_methods() ;
				} else {
					$allowed_classes = $wc_shipping->get_shipping_method_class_names() ;
				}
				if ( ! empty( $method_id ) && in_array( $method_id , array_keys( $allowed_classes ) ) ) {
					$class_name = $allowed_classes[ $method_id ] ;
					if ( is_object( $class_name ) ) {
						$class_name = get_class( $class_name ) ;
					}
					$method_object = new $class_name( $instance_id ) ;
					if ( is_object( $method_object ) ) {
						$shipping_method_title = fp_get_wpml_text( $wpml_translation_name , $lang , $method_object->title , 'woocommerce' ) ;
						return $shipping_method_title ;
					}
				}
			}
			return '' ;
		}

		/**
		 * Get the Shipping cost Details.
		 *
		 * @param  string $total
		 * @param  string $method_title
		 * @return string
		 */
		public static function fp_rac_get_shipping_details( $total, $method_title, $shipping_tax_cost, $curreny_code, $each_cart ) {
			if ( $total > 0 ) {
				if ( 'no' != get_option( 'rac_inc_tax_with_product_price_product_info_shortcode' ) ) {
					$total = $shipping_tax_cost + $total ;
				}
				return $method_title . ': ' . fp_rac_format_price( $total , $curreny_code , null , $each_cart ) ;
			} else {
				return $method_title ;
			}
		}

		/**
		 * Get sortable coulmn name.
		 *
		 * @param  string $lang
		 * @return string
		 */
		public static function fp_rac_get_sortable_column_name( $lang ) {
			$default_column  = array( 'product_name' , 'product_image' , 'product_quantity' , 'product_price' ) ;
			$sortable_column = get_option( 'drag_and_drop_product_info_sortable_column' ) ;
			$sortable_column = rac_check_is_array( $sortable_column ) ? $sortable_column : $default_column ;
			$new_array       = array( 'product_name' => 'rac_product_info_product_name' , 'product_image' => 'rac_product_info_product_image' , 'product_quantity' => 'rac_product_info_quantity' , 'product_price' => 'rac_product_info_product_price' ) ;
			if ( rac_check_is_array( $sortable_column ) ) {
				foreach ( $sortable_column as $column_key_name ) {
					if ( 'product_name' == $column_key_name ) {
						$product_details = get_option( 'rac_product_info_product_name' ) ;
					} elseif ( 'product_image' == $column_key_name ) {
						$product_details = get_option( 'rac_product_info_product_image' ) ;
					} elseif ( 'product_quantity' == $column_key_name ) {
						$product_details = get_option( 'rac_product_info_quantity' ) ;
					} elseif ( 'product_price' == $column_key_name ) {
						$product_details = get_option( 'rac_product_info_product_price' ) ;
					}
					if ( 'yes' != get_option( 'rac_hide_' . $column_key_name . '_product_info_shortcode' ) ) {
						?>
						<th class="fp-rac-email-product-td <?php echo esc_attr( self::enable_border() ) ; ?>" colspan="<?php echo esc_attr( fp_rac_get_column_span_count( 2 ) ) ; ?>" scope="col">
							<?php echo esc_html( fp_get_wpml_text( $new_array[ $column_key_name ] , $lang , $product_details , 'admin_texts_' . $new_array[ $column_key_name ] ) ) ; ?>
						</th>
						<?php
					}
				}
			}
		}

		public static function fp_rac_format_product_name_by_sku( $product_name, $product ) {

			if ( 'no' != get_option( 'rac_troubleshoot_sku_sh' ) ) {
				$sku = self::fp_rac_get_product_sku( $product ) ;
				if ( ! empty( $sku ) ) {
					$product_name = $product_name . ' (#' . $sku . ')' ;
				}
			}

			return $product_name ;
		}

		public static function fp_rac_get_product_sku( $product ) {
			$sku = '' ;
			if ( isset( $product[ 'variation_id' ] ) && ( ! empty( $product[ 'variation_id' ] ) ) ) {
				$product_object = fp_rac_get_product( $product[ 'variation_id' ] ) ;
				if ( is_object( $product_object ) ) {
					$sku = $product_object->get_sku() ;
				}
			} else {
				$product_object = fp_rac_get_product( $product[ 'product_id' ] ) ;
				if ( is_object( $product_object ) ) {
					$sku = $product_object->get_sku() ;
				}
			}
			return $sku ;
		}

		public static function enable_border() {
			$enable_border = get_option( 'rac_enable_border_for_productinfo_in_email' ) ;
			if ( 'no' != $enable_border ) {
				return 'td' ;
			}

			return '' ;
		}

	}

}

function fp_rac_get_column_span_count( $value = '' ) {
	$i = 3 ;
	$i = 'yes' == get_option( 'rac_hide_product_name_product_info_shortcode' ) ? $i - 1 : $i ;
	$i = 'yes' == get_option( 'rac_hide_product_image_product_info_shortcode' ) ? $i - 1 : $i ;
	$i = 'yes' == get_option( 'rac_hide_product_quantity_product_info_shortcode' ) ? $i - 1 : $i ;
	$i = 'yes' == get_option( 'rac_hide_product_price_product_info_shortcode' ) ? $i - 1 : $i ;
	if ( $i <= 0 && '' != $value ) {
		return $value ;
	}
	if ( '' == $value ) {
		return $i ;
	} else {
		return '' ;
	}
}
