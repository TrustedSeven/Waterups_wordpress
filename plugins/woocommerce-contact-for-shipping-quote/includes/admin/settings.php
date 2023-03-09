<?php
namespace WooCommerce_Contact_for_Shipping_Quote\Admin;


use WooCommerce_Contact_for_Shipping_Quote\Shipping_Quote;

class Settings {


	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		// Add to menu
		add_action( 'admin_menu', array( $this, 'add_to_menu' ), 99 );

		// Keep WC menu open while in WAS edit screen
		add_action( 'admin_head', array( $this, 'menu_highlight' ) );

		// Add 'extra shipping options' shipping section
		add_action( 'woocommerce_get_sections_shipping', array( $this, 'add_section' ) );

		// Settings <= 3.5
		add_action( 'woocommerce_settings_shipping', array( $this, 'section_settings_pre_3_6' ) );
		add_action( 'woocommerce_settings_save_shipping', array( $this, 'update_options_pre_3_6' ) );

		// Add settings >= 3.6
		add_action( 'woocommerce_get_settings_shipping', array( $this, 'section_settings' ), 10, 2 );
	}


	/**
	 * Add to admin menu.
	 *
	 * @since  1.0.0
	 */
	public function add_to_menu() {
		add_submenu_page( 'woocommerce', 'Shipping Quotes', 'Shipping Quotes', 'manage_woocommerce', 'woocommerce-contact-for-shipping-quote', array( $this, 'output' ) );
	}


	/**
	 * Output settings.
	 *
	 * The HTML that is outputted on the settings page.
	 *
	 * @since  1.0.0
	 */
	public function output() {

		?><div class="wrap">

			<h2><?php _e( 'Shipping quote requests', 'woocommerce-contact-for-shipping-quote' ); ?></h2><?php
			settings_errors( "woocommerce-contact-for-shipping-quote_settings_page" );

			$this->quote_requests();

		?></div><?php
	}


	/**
	 * Shipping quote requests table.
	 *
	 * @since  1.0.0
	 */
	public function quote_requests() {
		global $wpdb;

		$quotes = \WooCommerce_Contact_for_Shipping_Quote\get_shipping_quotes( array(
			'status' => $_GET['status'] ?? 'any',
			'page' => $_GET['paged'] ?? 1,
		) );

		$statuses = \WooCommerce_Contact_for_Shipping_Quote\get_statuses();
		$base_url = admin_url( 'admin.php?page=woocommerce-contact-for-shipping-quote' );
		$counts = $wpdb->get_results( "SELECT COUNT(*) as count, status FROM {$wpdb->prefix}woocommerce_shipping_quotes GROUP BY status;" );
		$counts = wp_list_pluck( $counts, 'count', 'status' );

		?><ul class="subsubsub shipping-quote-statuses">
			<li class="all"><a href="<?php echo $base_url; ?>" class="<?php echo ! isset( $_GET['status'] ) ? 'current' : ''; ?>" aria-current="page"><?php _e( 'All', 'woocommerce-contact-for-shipping-quote' ); ?> <span class="count">(<?php echo array_sum( $counts ); ?>)</span></a> |</li><?php

			foreach ( $statuses as $slug => $status ) :
				if ( empty( $counts[ $slug ] ) ) continue;

				?><li class="wc-shipping-quote-<?php echo esc_attr( $slug ); ?>">
					<a href="<?php echo esc_url( add_query_arg( 'status', $slug, $base_url ) ); ?>" class="<?php echo isset( $_GET['status'] ) && $_GET['status'] == $slug ? 'current' : ''; ?>"><?php
						echo wp_kses_post( $status ); ?> <span class="count">(<?php echo absint( $counts[ $slug ] ); ?>)</span>
					</a> <?php echo $status !== end( $statuses ) ? '<span class="separator">|</span>' : ''; ?>
				</li><?php
			endforeach;

        ?></ul>

		<div class="tablenav"><?php
            $this->pagination( array( 'total_items' => array_sum( $counts ), 'total_pages' => ceil( array_sum( $counts ) / 30 ) ) );
        ?></div>
		<table class="wccsq-quotes widefat striped">
			<thead>
				<tr>
					<th class="customer" style="padding: 8px 10px;"><?php _e( 'Customer name', 'woocommerce-contact-for-shipping-quote' ); ?></th>
					<th class="status" style="padding: 8px 10px;"><?php _e( 'Status', 'woocommerce-contact-for-shipping-quote' ); ?></th>
					<th class="cart" style="padding: 8px 10px;"><?php _e( 'Cart', 'woocommerce-contact-for-shipping-quote' ); ?></th>
					<th class="quoted-amount" style="padding: 8px 10px;"><?php _e( 'Quotation', 'woocommerce-contact-for-shipping-quote' ); ?></th>
					<th class="actions" style="padding: 8px 10px;"><?php _e( 'Actions', 'woocommerce-contact-for-shipping-quote' ); ?></th>
				</tr>
			</thead>

			<tbody><?php

				/** @var Shipping_Quote[] $quotes */
				foreach ( $quotes as $quote ) :
					wc_get_template( 'admin/views/html-shipping-quote-row.php', array( 'quote' => $quote ), '', plugin_dir_path( \WooCommerce_Contact_For_Shipping_Quote\WooCommerce_Contact_For_Shipping_Quote()->file ) . '/includes/' );
				endforeach;

				if ( empty( $quotes ) ) :
					?><tr>
						<td colspan="99"><?php _e( 'There are no shipping quote requests yet', 'woocommerce-contact-for-shipping-quote' ); ?></td>
					</tr><?php
				endif;

			?></tbody>
		</table>
		<div class="tablenav botom"><?php
            $this->pagination( array( 'total_items' => array_sum( $counts ), 'total_pages' => ceil( array_sum( $counts ) / 30 ) ) );
        ?></div><?php
	}

	private function pagination( $pagination_args ) {
		wp_enqueue_style( 'list-tables' );

		$total_items = $pagination_args['total_items'];
		$total_pages = $pagination_args['total_pages'];

		$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $_GET['paged'] ?? 1;
		$current_url = remove_query_arg( wp_removable_query_args() );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

		if ( $current == 1 ) {
			$disable_prev = true;
		}
		if ( $current == 1 || $current == 2 ) {
			$disable_first = true;
		}
		if ( $current == $total_pages ) {
			$disable_next = true;
		}
		if ( $current == $total_pages - 1 || $current == $total_pages ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>", esc_url( remove_query_arg( 'paged', $current_url ) ), __( 'First page' ), '&laquo;' );
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>", esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ), __( 'Previous page' ), '&lsaquo;' );
		}

		$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>", '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>', $current, strlen( $total_pages ) );

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>", esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ), __( 'Next page' ), '&rsaquo;' );
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>", esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ), __( 'Last page' ), '&raquo;' );
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class .= ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		$page_class = $total_pages ? ($total_pages < 2 ? ' one-page' : '') : ' no-pages';
		$pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $pagination;

	}



	/**
	 * Settings page array.
	 *
	 * Get settings page fields array.
	 *
	 * @since 1.0.0
	 */
	public function get_settings() {

		$settings = apply_filters( 'WCCSQ/admin/settings', array(

			array(
				'title' => __( 'Contact for Shipping Quote', 'woocommerce-contact-for-shipping-quote' ),
				'type'  => 'title',
			),

			array(
				'id'       => 'shipping_quote_expiration',
				'title'    => __( 'Quote expiration time (days)', 'woocommerce-contact-for-shipping-quote' ),
				'desc_tip' => __( 'Time until a quote expires automatically. Use 0 for no expiration.', 'woocommerce-contact-for-shipping-quote' ),
				'default'  => '2',
				'type'     => 'number',
				'autoload' => false,
				'css'      => 'width:75px;',
				'custom_attributes' => array(
					'step' => 0.01
				)
			),

			array(
				'id'       => 'shipping_quote_required_data',
				'title'    => __( 'Required data', 'woocommerce-contact-for-shipping-quote' ),
				'desc'     => __( 'A notice will be displayed when required data is not available.', 'woocommerce-contact-for-shipping-quote' ),
				'default'  => array(
					'country',
					'postcode',
					'city',
					'email',
				),
				'type'     => 'multiselect',
				'class'    => 'wc-enhanced-select',
				'autoload' => false,
				'options'  => array(
					'first_name' => __( 'First name' ),
					'last_name'  => __( 'Last name' ),
					'country'    => __( 'Country' ),
					'state'      => __( 'State' ),
					'postcode'   => __( 'Postcode' ),
					'city'       => __( 'City' ),
					'address_1'  => __( 'Address' ),
					'email'      => __( 'Email address' ),
					'phone'      => __( 'Phone' ),
				),
			),

			array(
				'id'                => 'shipping_quote_debug_mode',
				'title'             => __( 'Debug mode', 'woocommerce-contact-for-shipping-quote' ),
				'desc_tip'          => __( 'Enable debug mode for shipping quotes', 'woocommerce-contact-for-shipping-quote' ),
				'default'           => 0,
				'type'              => 'checkbox',
				'autoload'          => false,
			),


			array(
				'type' => 'sectionend',
			),

		) );

		return $settings;
	}


	/**
	 * Keep menu open.
	 *
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @since 1.0.0
	 */
	public function menu_highlight() {
		global $parent_file, $submenu_file, $post_type;

		if ( 'quote_options' == $post_type ) {
			$parent_file  = 'woocommerce';
			$submenu_file = 'wc-settings';
		}
	}


	/**
	 * Add shipping section.
	 *
	 * Add a new 'extra shipping options' section under the shipping tab.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $sections List of existing shipping sections.
	 * @return array           List of modified shipping sections.
	 */
	public function add_section( $sections ) {
		$sections['shipping_quotes'] = __( 'Quote options', 'woocommerce-contact-for-shipping-quote' );

		return $sections;
	}


	/**
	 * Shipping validation settings.
	 *
	 * Add the settings to the shipping validation shipping section.
	 * Only here for WC 3.5 support. @todo remove when WC 4.0 releases
	 *
	 * @since 1.0.0
	 */
	public function section_settings_pre_3_6() {
		global $current_section;

		if ( 'shipping_quotes' === $current_section && version_compare( WC()->version, '3.6', '<' ) ) {
			\WC_Admin_Settings::output_fields( $this->get_settings() );
		}
	}


	/**
	 * Save settings.
	 *
	 * Save settings based on WooCommerce save_fields() method.
	 * @todo remove when WC 4.0 releases
	 *
	 * @since 1.0.0
	 */
	public function update_options_pre_3_6() {
		global $current_section;

		if ( $current_section == 'shipping_quotes' && version_compare( WC()->version, '3.6', '<' ) ) {
			\WC_Admin_Settings::save_fields( $this->get_settings() );
		}
	}


	/**
	 * Shipping Quote settings.
	 *
	 * Add settings for the shipping quote plugin settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $settings        Current settings.
	 * @param string $current_section Slug of the current section
	 * @return array                   Modified settings.
	 */
	public function section_settings( $settings, $current_section ) {
		if ( 'shipping_quotes' === $current_section ) {
			$settings = $this->get_settings();
		}

		return $settings;
	}


}
