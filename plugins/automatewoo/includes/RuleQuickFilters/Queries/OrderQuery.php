<?php

namespace AutomateWoo\RuleQuickFilters\Queries;

use Automattic\WooCommerce\Utilities\OrderUtil;
use WC_Order;

defined( 'ABSPATH' ) || exit;

/**
 * Class OrderQuery.
 *
 * @since   5.0.0
 * @package AutomateWoo\RuleQuickFilters\Queries
 */
class OrderQuery extends AbstractQuery {

	/**
	 * Get data type for quick filtering.
	 *
	 * @return string
	 */
	public function get_data_type() {
		return 'order';
	}

	/**
	 * Get filter result object from ID.
	 *
	 * @param int $id
	 *
	 * @return WC_Order|false
	 */
	public function get_result_object( $id ) {
		return wc_get_order( $id );
	}

	/**
	 * Get the datastore type to use for queries.
	 *
	 * @return DatastoreTypeInterface
	 *
	 * @since 5.5.23
	 */
	protected function get_datastore_type() {
		if ( class_exists( OrderUtil::class ) && OrderUtil::custom_orders_table_usage_is_enabled() ) {
			return new OrderHighPerformanceDatastoreType();
		}

		return new OrderPostDatastoreType();
	}

}
