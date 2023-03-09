<?php
/**
 * Admin Recovered Order Custom Post Type.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'FP_RAC_Recovered_Order_Table' ) ) {

	/**
	 * FP_RAC_Recovered_Order_Table Class.
	 */
	class FP_RAC_Recovered_Order_Table {

		/**
		 * FP_RAC_Recovered_Order_Table Class initialization.
		 */
		public static function init() {

			add_action( 'views_edit-racrecoveredorder' , array( __CLASS__ , 'remove_post_type_views' ) ) ;
			add_action( 'posts_join' , array( __CLASS__ , 'fp_rac_recovered_order_inner_join_wordpress' ) , 10 , 2 ) ;
			add_action( 'restrict_manage_posts' , array( __CLASS__ , 'fp_rac_add_recovered_order_filter_option' ) ) ;
			add_action( 'posts_where' , array( __CLASS__ , 'fp_rac_recovered_order_sorting_functionality' ) , 10 , 2 ) ;
			add_action( 'manage_posts_extra_tablenav' , array( __CLASS__ , 'fp_rac_recovered_order_extra_tablenav' ) ) ;
			add_action( 'posts_orderby' , array( __CLASS__ , 'fp_rac_recovered_order_orderby_functionality' ) , 10 , 2 ) ;
			add_action( 'posts_distinct' , array( __CLASS__ , 'fp_rac_recovered_order_distinct_functionality' ) , 10 , 2 ) ;
			add_action( 'admin_action_rac-delete-reoveredorder' , array( __CLASS__ , 'fp_rac_move_all_recovered_order_to_trash' ) ) ;
			add_action( 'manage_racrecoveredorder_posts_custom_column' , array( __CLASS__ , 'fp_rac_display_recovered_order_table_data' ) , 10 , 2 ) ;

			add_filter( 'parse_query' , array( __CLASS__ , 'fp_rac_recovered_order_filters_query' ) ) ;
			add_filter( 'posts_search' , array( __CLASS__ , 'fp_rac_recovered_order_search_fields' ) ) ;
			add_filter( 'post_row_actions' , array( __CLASS__ , 'fp_rac_recovered_order_post_row_actions' ) , 10 , 2 ) ;
			add_filter( 'bulk_actions-edit-racrecoveredorder' , array( __CLASS__ , 'fp_rac_recovered_order_bulk_post_actions' ) , 10 , 1 ) ;
			add_filter( 'manage_racrecoveredorder_posts_columns' , array( __CLASS__ , 'fp_rac_initialize_recovered_order_columns' ) ) ;
			add_filter( 'manage_edit-racrecoveredorder_sortable_columns' , array( __CLASS__ , 'fp_rac_recovered_order_sortable_columns' ) ) ;
		}

		/**
		 * Initialization of columns in Recovered Order table
		 */
		public static function fp_rac_initialize_recovered_order_columns( $columns ) {
			$columns = array(
				'cb'                        => $columns[ 'cb' ] ,
				'id'                        => __( 'ID' , 'recoverabandoncart' ) ,
				'rac_order_id'              => __( 'Order ID' , 'recoverabandoncart' ) ,
				'rac_recovered_sales_total' => __( 'Recovered Sales Total' , 'recoverabandoncart' ) ,
				'rac_recovered_date'        => __( 'Date' , 'recoverabandoncart' ) ,
					) ;
			return $columns ;
		}

		/**
		 * Initialization of sortable columns in Recovered Order table
		 */
		public static function fp_rac_recovered_order_sortable_columns( $columns ) {
			$array = array(
				'id'                        => 'ID' ,
				'rac_order_id'              => 'rac_order_id' ,
				'rac_recovered_sales_total' => 'rac_recovered_sales_total' ,
				'rac_recovered_date'        => 'rac_recovered_date' ,
					) ;
			return wp_parse_args( $array , $columns ) ;
		}

		/*
		 * Remove Custom Post Type Views
		 */

		public static function remove_post_type_views( $views ) {

			unset( $views[ 'mine' ] ) ;
			return $views ;
		}

		/**
		 * Display each column data in Recovered Order table
		 */
		public static function fp_rac_display_recovered_order_table_data( $column, $postid ) {
			switch ( $column ) {
				case 'id':
					echo '#' . esc_html( $postid ) ;
					break ;
				case 'rac_order_id':
					$order_id    = get_post_meta( $postid , 'rac_order_id' , true ) ;
					echo '<a href=' . esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ) . '>#' . esc_html( $order_id ) . '</a>' ;
					break ;
				case 'rac_recovered_sales_total':
					$sales_total = get_post_meta( $postid , 'rac_recovered_sales_total' , true ) ;
					echo wp_kses_post( fp_rac_format_price( $sales_total ) ) ;
					break ;
				case 'rac_recovered_date':
					$date        = get_post_meta( $postid , 'rac_recovered_date' , true ) ;
					echo wp_kses_post( self::format_date( $date ) ) ;
					break ;
			}
		}

		/**
		 * Adding extra filter in Recovered Order table.
		 */
		public static function fp_rac_add_recovered_order_filter_option( $post_type ) {
			if ( 'racrecoveredorder' == $post_type ) {
				//display date filter for Recovered Order table
				$fromdate = '' ;
				$todate   = '' ;
				if ( isset( $_REQUEST[ 'filter_action' ] ) ) {
					$fromdate = isset( $_REQUEST[ 'rac_recovered_order_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_recovered_order_fromdate' ] ) ) : '' ;
					$todate   = isset( $_REQUEST[ 'rac_recovered_order_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_recovered_order_todate' ] ) ) : '' ;
				}
				?>
				<input id='rac_from_date' placeholder='From Date' type='text' name='rac_recovered_order_fromdate' value="<?php echo esc_attr( $fromdate ) ; ?>"/>
				<input id='rac_to_date' type='text' name='rac_recovered_order_todate' value="<?php echo esc_attr( $todate ) ; ?>" placeholder='To Date'/>
				<?php
			}
		}

		/**
		 * Adding Extra action in Recovered Order table
		 */
		public static function fp_rac_recovered_order_extra_tablenav( $which ) {
			global $post ;
			if ( ( 'top' === $which ) && ( ( ( is_object( $post ) && 'racrecoveredorder' == $post->post_type ) ) || ( isset( $_REQUEST[ 'post_type' ] ) && 'racrecoveredorder' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) ) ) {
				$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
				$button_name = 'trash' == $post_status ? __( 'Restore all Recovered Orders Log' , 'recoverabandoncart' ) : __( 'Move all Recovered Orders Log to Trash' , 'recoverabandoncart' ) ;
				$query_arg   = isset( $_GET[ 'post_status' ] ) ? '&post_status=' . $post_status : '' ;
				$admin_url   = admin_url( 'edit.php?post_type=racrecoveredorder' . $query_arg ) ;
				$delete_url  = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-delete-reoveredorder' ) , $admin_url ) ) , 'rac-delete-reoveredorder' ) ;
				?>
				<a href="<?php echo esc_url( $delete_url ) ; ?>" class="button-primary"><?php echo esc_html( $button_name ) ; ?></a>
				<?php
			}
		}

		/**
		 * Modify a row post actions in Recovered Order table
		 */
		public static function fp_rac_recovered_order_post_row_actions( $actions, $post ) {
			if ( 'racrecoveredorder' == $post->post_type ) {
				unset( $actions[ 'edit' ] ) ;
				unset( $actions[ 'inline hide-if-no-js' ] ) ;
			}
			return $actions ;
		}

		/**
		 * Modify Bulk post actions in Recovered Order table
		 */
		public static function fp_rac_recovered_order_bulk_post_actions( $actions ) {
			global $post ;
			if ( isset( $post->post_type ) && ( 'racrecoveredorder' == $post->post_type ) ) {
				unset( $actions[ 'edit' ] ) ;
			}
			return $actions ;
		}

		/**
		 * Searching Functionality
		 */
		public static function fp_rac_recovered_order_search_fields( $where ) {
			global $pagenow , $wpdb , $wp ;

			if ( 'edit.php' != $pagenow || ! is_search() || ! isset( $wp->query_vars[ 's' ] ) || 'racrecoveredorder' != $wp->query_vars[ 'post_type' ] ) {
				return $where ;
			}

			$search_ids = array() ;
			$terms      = explode( ',' , $wp->query_vars[ 's' ] ) ;

			foreach ( $terms as $term ) {
				$term          = $wpdb->esc_like( wc_clean( $term ) ) ;
				$meta_array    = array(
					'rac_order_id' ,
					'rac_recovered_sales_total' ,
					'rac_recovered_date' ,
						) ;
				$implode_array = implode( "','" , $meta_array ) ;

				$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
				if ( isset( $_GET[ 'post_status' ] ) && 'all' != $post_status ) {
					$post_status = $post_status ;
				} else {
					$post_status_array = array( 'publish' ) ;
					$post_status       = implode( "','" , $post_status_array ) ;
				}

				$search_ids = $wpdb->get_col( $wpdb->prepare(
								'SELECT DISTINCT ID FROM '
								. "{$wpdb->posts} as p INNER JOIN {$wpdb->postmeta} as pm "
								. 'ON p.ID = pm.post_id '
								. 'WHERE (p.post_status IN (%s)) AND (p.ID LIKE %s '
								. 'OR p.post_title LIKE %s '
								. 'OR p.post_content LIKE %s '
								. 'OR (pm.meta_key IN (%s) '
								. 'AND pm.meta_value LIKE %s))' , "'" . $post_status . "'" , '%' . $term . '%' , '%' . $term . '%' , '%' . $term . '%' , "'" . $implode_array . "'" , '%' . $term . '%' ) ) ;
			}
			$search_ids = array_filter( array_unique( array_map( 'absint' , $search_ids ) ) ) ;
			if ( count( $search_ids ) > 0 ) {
				$where = str_replace( 'AND (((' , "AND ( ({$wpdb->posts}.ID IN (" . implode( ',' , $search_ids ) . ')) OR ((' , $where ) ;
			}

			return $where ;
		}

		/**
		 * Filter Functionality
		 */
		public static function fp_rac_recovered_order_filters_query( $query ) {
			global $typenow ;
			if ( isset( $query->query[ 'post_type' ] ) && 'racrecoveredorder' == $query->query[ 'post_type' ] ) {
				if ( 'racrecoveredorder' == $typenow ) {
					if ( isset( $_GET[ 'orderby' ] ) ) {
						$excerpt_array = array( 'ID' ) ;
						$orderby       = isset( $_GET[ 'orderby' ] ) ? wc_clean( wp_unslash( $_GET[ 'orderby' ] ) ) : '' ;

						if ( ! in_array( $orderby , $excerpt_array ) ) {
							$query->query_vars[ 'meta_key' ] = $orderby ;
						}
					}
				}
			}
		}

		/**
		 * Inner Join  Functionality
		 */
		public static function fp_rac_recovered_order_inner_join_wordpress( $join, $wp_query ) {
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racrecoveredorder' != $wp_query->query[ 'post_type' ] ) {
				return $join ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( ( isset( $_REQUEST[ 'filter_action' ] ) && 'racrecoveredorder' == $post_type ) ) {
				global $wpdb ;
				$table_name    = $wpdb->prefix . 'posts' ;
				$another_table = $wpdb->prefix . 'postmeta' ;
				$join          .= " INNER JOIN $another_table ON ($table_name.ID = $another_table.post_id)" ;
			}
			return $join ;
		}

		/**
		 * Distinct  Functionality
		 */
		public static function fp_rac_recovered_order_distinct_functionality( $distinct, $wp_query ) {
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racrecoveredorder' != $wp_query->query[ 'post_type' ] ) {
				return $distinct ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'filter_action' ] ) && 'racrecoveredorder' == $post_type ) {
				if ( empty( $distinct ) ) {
					$distinct .= 'DISTINCT' ;
				}
			}

			return $distinct ;
		}

		/**
		 * Order By  Functionality
		 */
		public static function fp_rac_recovered_order_orderby_functionality( $order_by, $wp_query ) {

			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racrecoveredorder' != $wp_query->query[ 'post_type' ] ) {
				return $order_by ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'post_type' ] ) && 'racrecoveredorder' == $post_type ) {
				global $wpdb ;
				if ( ! isset( $_REQUEST[ 'order' ] ) && ! isset( $_REQUEST[ 'orderby' ] ) ) {
					$order    = fp_rac_backward_compatibility_for_table_sorting( 'rac_display_recovered_orders_basedon_asc_desc' ) ;
					$order_by = "{$wpdb->posts}.ID " . $order ;
				} else {
					$decimal_column = array(
						'rac_recovered_sales_total' ,
						'rac_order_id' ,
						'rac_recovered_date'
							) ;

					$orderby = isset( $_REQUEST[ 'orderby' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'orderby' ] ) ) : '' ;
					if ( in_array( $orderby , $decimal_column ) ) {
						$order_by = "CAST({$wpdb->postmeta}.meta_value AS DECIMAL) " . $orderby ;
					}
				}
			}
			return $order_by ;
		}

		/**
		 * Date Filter Functionality
		 */
		public static function fp_rac_recovered_order_sorting_functionality( $where, $wp_query ) {
			global $wpdb ;

			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racrecoveredorder' != $wp_query->query[ 'post_type' ] ) {
				return $where ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'filter_action' ] ) && 'racrecoveredorder' == $post_type ) {
				$fromdate = isset( $_REQUEST[ 'rac_recovered_order_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_recovered_order_fromdate' ] ) ) : null ;
				$todate   = isset( $_REQUEST[ 'rac_recovered_order_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_recovered_order_todate' ] ) ) : null ;
				if ( $fromdate ) {
					$from_strtotime = strtotime( $fromdate ) ;
					$fromdate       = gmdate( 'Y-m-d' , $from_strtotime ) . ' 00:00:00' ;
					$where          .= " AND $wpdb->posts.post_date >= '$fromdate'" ;
				}
				if ( $todate ) {
					$to_strtotime = strtotime( $todate ) ;
					$todate       = gmdate( 'Y-m-d' , $to_strtotime ) . ' 23:59:59' ;
					$where        .= " AND $wpdb->posts.post_date <= '$todate'" ;
				}
			}
			return $where ;
		}

		/**
		 * Delete and Restore the Post Functionality
		 */
		public static function fp_rac_move_all_recovered_order_to_trash() {
			check_admin_referer( 'rac-delete-reoveredorder' ) ;
			$trash = 0 ;
			if ( isset( $_GET[ 'post_status' ] ) ) {
				$post_status = isset( $_REQUEST[ 'post_status' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_status' ] ) ) : '' ;
				$args        = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'racrecoveredorder' ,
					'post_status'    => $post_status ,
					'fields'         => 'ids'
						) ;
				$move        = 'trash' == $post_status ? 1 : 2 ;
			} else {
				$args = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'racrecoveredorder' ,
					'post_status'    => array( 'publish' ) ,
					'fields'         => 'ids'
						) ;
				$move = 2 ;
			}

			$posts = fp_rac_check_query_having_posts( $args ) ;
			if ( rac_check_is_array( $posts ) ) {
				foreach ( $posts as $post_id ) {
					if ( 1 == $move ) {
						if ( ! wp_untrash_post( $post_id ) ) {
							wp_die( esc_html__( 'Error in moving to Trash.' ) ) ;
						}
					} else {
						if ( ! wp_trash_post( $post_id ) ) {
							wp_die( esc_html__( 'Error in moving to Trash.' ) ) ;
						}
					}
				}
			}
			$url = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? esc_url_raw( $_SERVER[ 'HTTP_REFERER' ] ) : '' ;
			wp_redirect( $url ) ;
			exit() ;
		}

		/**
		 * Format Date based on Wordpress Date format
		 */
		public static function format_date( $date ) {
			$formatted_date = gmdate( get_option( 'date_format' ) , $date ) . '/' . gmdate( get_option( 'time_format' ) , $date ) ;
			return $formatted_date ;
		}

	}

	FP_RAC_Recovered_Order_Table::init() ;
}
