<?php
/**
 * Previous Orders tab.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( class_exists( 'RAC_Previous_Orders_Tab' ) ) {
	return new RAC_Previous_Orders_Tab() ;
}

if ( ! class_exists( 'RAC_Previous_Orders_Tab' ) ) {

	/**
	 * Class.
	 */
	class RAC_Previous_Orders_Tab extends RAC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id                = 'previous_orders' ;
			$this->label             = __( 'Check Previous Orders', 'recoverabandoncart' ) ;
			$this->show_reset_button = false ;

			add_action( 'woocommerce_admin_field_rac_automatic_order_statuses', array( __CLASS__, 'render_automatic_order_statuses_field' ) ) ;

			parent::__construct() ;
		}

		/**
		 * Get settings for the previous orders section array.
		 * 
		 * @return array
		 */
		protected function previous_orders_section_array() {
			// Automatic previous orders settings section start.
			$section_fields[] = array(
				'name' => __( 'Automatic', 'recoverabandoncart' ),
				'type' => 'title',
				'id'   => 'rac_automatic_previous_orders_settings',
					) ;
			$section_fields[] = array(
				'name'    => __( 'Automatic Cron Job', 'recoverabandoncart' ),
				'id'      => 'rac_auto_recover_previous_order',
				'std'     => '1',
				'default' => '1',
				'type'    => 'select',
				'options' => array(
					'1' => __( 'Disable', 'recoverabandoncart' ),
					'2' => __( 'Enable', 'recoverabandoncart' ),
				),
					) ;
			$section_fields[] = array(
				'title'     => __( 'Check Previous Order After', 'recoverabandoncart' ),
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_value',
				'default'   => 12,
				'std'       => 12,
				'id'        => 'rac_previous_order_cron_time',
				'class'     => 'rac_automatic_pre_orders_fields fp-rac-cart-time'
					) ;
			$section_fields[] = array(
				'type'      => 'rac_custom_fields',
				'rac_field' => 'time_type',
				'default'   => 'hours',
				'std'       => 'hours',
				'id'        => 'rac_previous_cart_cron_type',
				'class'     => 'rac_automatic_pre_orders_fields fp-rac-cart-time-type',
				'options'   => array(
					'hours' => __( 'Hours', 'recoverabandoncart' ),
					'days'  => __( 'Days', 'recoverabandoncart' )
				),
				'desc'      => __( 'of placing the order', 'recoverabandoncart' ),
					) ;

			$section_fields[] = array(
				'title'     => __( 'Add Old WooCommerce Orders to Cart List which are in', 'recoverabandoncart' ),
				'id'        => 'rac_auto_order_status',
				'type'      => 'rac_custom_fields',
				'rac_field' => 'orderstatuses',
				'default'   => array(),
				'std'       => array(),
					) ;

			$section_fields[] = array(
				'type' => 'title',
				'id'   => 'rac_automatic_previous_orders_settings',
					) ;
			// Automatic previous orders settings section end.
			return apply_filters( 'woocommerce_rac_previous_orders_settings', $section_fields ) ;
		}

		/**
		 * Render manual check previous orders settings.
		 */
		public function output_extra_fields() {

			include_once RAC_PLUGIN_PATH . '/inc/fp-rac-previous-order.php' ;
			?>
			<h2><?php esc_html_e( 'Manual', 'recoverabandoncart' ) ; ?></h2>
			<table class="form-table">

				<tr>
					<th><?php esc_html_e( 'Add Old WooCommerce Orders to Cart List which are in', 'recoverabandoncart' ) ; ?></th>
					<td>
						<p><input type = "checkbox" name = "order_status[]" value = "wc-on-hold"><?php esc_html_e( 'On hold status', 'recoverabandoncart' ) ; ?></p>
						<p><input type = "checkbox" name = "order_status[]" value = "wc-pending"><?php esc_html_e( 'Pending payment status', 'recoverabandoncart' ) ; ?></p>
						<p><input type = "checkbox" name = "order_status[]" value = "wc-failed" checked><?php esc_html_e( 'Failed status', 'recoverabandoncart' ) ; ?></p>
						<p><input type = "checkbox" name = "order_status[]" value = "wc-cancelled"><?php esc_html_e( 'Cancelled status', 'recoverabandoncart' ) ; ?></p>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Time Duration', 'recoverabandoncart' ) ; ?></th>
					<td>
						<select id = "order_time">
							<option value = "all"><?php esc_html_e( 'All time', 'recoverabandoncart' ) ; ?></option>
							<option value = "specific"><?php esc_html_e( 'Specific', 'recoverabandoncart' ) ; ?></option>
						</select>
					</td>
				</tr>
				<tr class="fp-rac-hide" id = "specific_row">
					<th><?php esc_html_e( 'Specific Time', 'recoverabandoncart' ) ; ?></th>
					<td>
						<label><?php esc_html_e( 'From', 'recoverabandoncart' ) ; ?></label>
						<input type = "text" name = "from_date" id = "from_time" class = "rac_date">
						<label><?php esc_html_e( 'To', 'recoverabandoncart' ) ; ?></label>
						<input type = "text" id = "to_time" name = "to_date" class = "rac_date">
					</td>
				</tr>
				<tr>
					<td>
						<input type = "button" class = "button button-primary" name = "update_order" id = "update_order" value = "<?php esc_html_e( 'Check for Abandoned Cart', 'recoverabandoncart' ) ; ?>">
					</td>
					<td>
						<img class = "perloader_image fp-rac-reload-img" src = "<?php echo esc_url( RAC_PLUGIN_URL ) ; ?>/assets/images/update.gif"/>
						<p id = "update_response"></p>
					</td>
				</tr>
			</table>
			<?php
		}

	}

}

return new RAC_Previous_Orders_Tab() ;
