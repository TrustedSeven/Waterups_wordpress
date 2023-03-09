<?php
/**
 * Admin Mail Log Custom Post Type.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'FP_RAC_Maillog_Table' ) ) {

	/**
	 * FP_RAC_Maillog_Table Class.
	 */
	class FP_RAC_Maillog_Table {

		/**
		 * FP_RAC_Maillog_Table Class initialization.
		 */
		public static function init() {
			add_action( 'views_edit-racmaillog' , array( __CLASS__ , 'remove_post_type_views' ) ) ;
			add_action( 'posts_orderby' , array( __CLASS__ , 'fp_rac_post_orderby_functionality' ) , 10 , 2 ) ;
			add_action( 'restrict_manage_posts' , array( __CLASS__ , 'fp_rac_add_emaillog_filter_option' ) ) ;
			add_action( 'posts_join' , array( __CLASS__ , 'fp_rac_email_log_post_inner_join_wordpress' ) , 10 , 2 ) ;
			add_action( 'posts_where' , array( __CLASS__ , 'fp_rac_email_log_posts_sorting_functionality' ) , 10 , 2 ) ;
			add_action( 'posts_distinct' , array( __CLASS__ , 'fp_rac_email_log_post_distinct_functionality' ) , 10 , 2 ) ;
			add_action( 'admin_action_rac-emaillog-delete' , array( __CLASS__ , 'fp_rac_move_all_emaillog_to_trash' ) ) ;
			add_action( 'manage_posts_extra_tablenav' , array( __CLASS__ , 'fp_rac_email_log_manage_posts_extra_table' ) ) ;
			add_action( 'manage_racmaillog_posts_custom_column' , array( __CLASS__ , 'fp_rac_display_maillog_table_data' ) , 10 , 2 ) ;

			add_filter( 'posts_search' , array( __CLASS__ , 'fp_rac_email_log_search' ) ) ;
			add_filter( 'parse_query' , array( __CLASS__ , 'fp_rac_email_log_filters_query' ) ) ;
			add_filter( 'post_row_actions' , array( __CLASS__ , 'fp_rac_maillog_post_row_actions' ) , 10 , 2 ) ;
			add_filter( 'bulk_actions-edit-racmaillog' , array( __CLASS__ , 'fp_rac_maillog_bulk_post_actions' ) ) ;
			add_filter( 'manage_racmaillog_posts_columns' , array( __CLASS__ , 'fp_rac_initialize_maillog_columns' ) ) ;
			add_filter( 'manage_edit-racmaillog_sortable_columns' , array( __CLASS__ , 'fp_rac_maillog_sortable_columns' ) ) ;
		}

		/**
		 * Set the table columns
		 */
		public static function fp_rac_initialize_maillog_columns( $columns ) {
			$columns = array(
				'cb'                => $columns[ 'cb' ] ,
				'id'                => __( 'ID' , 'recoverabandoncart' ) ,
				'rac_email_id'      => __( 'Email ID' , 'recoverabandoncart' ) ,
				'rac_date_time'     => __( 'Date Time' , 'recoverabandoncart' ) ,
				'rac_template_used' => __( 'Template Used' , 'recoverabandoncart' ) ,
				'rac_cart_id'       => __( 'Abandon Cart ID' , 'recoverabandoncart' ) ,
					) ;
			return $columns ;
		}

		/**
		 * Set the sortable columns
		 */
		public static function fp_rac_maillog_sortable_columns( $columns ) {
			$array = array(
				'id'            => 'ID' ,
				'rac_email_id'  => 'rac_email_id' ,
				'rac_date_time' => 'rac_date_time' ,
				'rac_cart_id'   => 'rac_cart_id' ,
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
		 * Display the Maillog table data
		 */
		public static function fp_rac_display_maillog_table_data( $column, $postid ) {

			switch ( $column ) {
				case 'id':
					echo '#' . esc_html( $postid ) ;
					break ;
				case 'rac_email_id':
					$email_id      = get_post_meta( $postid , 'rac_email_id' , true ) ;
					echo esc_html( $email_id ) ;
					break ;
				case 'rac_date_time':
					$date_time     = get_post_meta( $postid , 'rac_date_time' , true ) ;
					echo esc_html( gmdate( get_option( 'date_format' ) , $date_time ) . '/' . gmdate( get_option( 'time_format' ) , $date_time ) ) ;
					break ;
				case 'rac_template_used':
					$template_used = get_post_meta( $postid , 'rac_template_used' , true ) ;
					$manual_mail   = strpos( $template_used , 'Manual' ) ;
					$manual        = false ;
					if ( false !== $manual_mail ) {
						$template_id = explode( '-' , $template_used ) ;
						$template_id = $template_id[ 0 ] ;
						$manual      = true ;
					} else {
						$template_id = $template_used ;
					}
					$template_name = get_post_field( 'post_title' , $template_id ) ;
					if ( ! empty( $template_name ) ) {
						if ( $manual ) {
							$template_id = $template_used ;
						}
						echo esc_html( $template_name . ' (#' . $template_id . ')' ) ;
					} else {
						esc_html_e( 'Template Info not Available' , 'recoverabandoncart' ) ;
					}
					break ;
				case 'rac_cart_id':
					$cart_id = get_post_meta( $postid , 'rac_cart_id' , true ) ;
					if ( $cart_id ) {
						echo esc_html( $cart_id ) ;
					} else {
						esc_html_e( 'Cart List ID not Available' , 'recoverabandoncart' ) ;
					}
					break ;
			}
		}

		/**
		 * Update the Bulk post actions
		 */
		public static function fp_rac_maillog_bulk_post_actions( $action ) {
			global $current_screen ;
			if ( isset( $current_screen->post_type ) ) {
				if ( 'racmaillog' == $current_screen->post_type ) {
					unset( $action[ 'edit' ] ) ;
				}
			}
			return $action ;
		}

		/**
		 * Adding extra filter in cart list table.
		 */
		public static function fp_rac_add_emaillog_filter_option( $post_type ) {
			if ( 'racmaillog' == $post_type ) {
				//display date filter for cart list table 
				$fromdate = '' ;
				$todate   = '' ;
				if ( isset( $_REQUEST[ 'filter_action' ] ) ) {
					$fromdate = isset( $_REQUEST[ 'rac_emaillog_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_emaillog_fromdate' ] ) ) : '' ;
					$todate   = isset( $_REQUEST[ 'rac_emaillog_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_emaillog_todate' ] ) ) : '' ;
				}
				?>
				<input id='rac_from_date' placeholder=<?php esc_attr_e( 'From Date' , 'recoverabandoncart' ) ; ?> type='text' name='rac_emaillog_fromdate' value="<?php echo esc_attr( $fromdate ) ; ?>"/>
				<input id='rac_to_date' type='text' name='rac_emaillog_todate' value="<?php echo esc_attr( $todate ) ; ?>" placeholder=<?php esc_attr_e( 'To Date' , 'recoverabandoncart' ) ; ?>/>
				<?php
			}
		}

		/**
		 * Adding extra table nav
		 */
		public static function fp_rac_email_log_manage_posts_extra_table( $which ) {
			global $post ;
			if ( ( 'top' === $which ) && ( ( ( is_object( $post ) && 'racmaillog' == $post->post_type ) ) || ( isset( $_REQUEST[ 'post_type' ] ) && 'racmaillog' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) ) ) {
				$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
				$button_name = 'trash' == $post_status ? __( 'Restore All Email Logs' , 'recoverabandoncart' ) : __( 'Move all Email Logs to Trash' , 'recoverabandoncart' ) ;
				$query_arg   = isset( $_GET[ 'post_status' ] ) ? '&post_status=' . $post_status : '' ;
				$admin_url   = admin_url( 'edit.php?post_type=racmaillog' . $query_arg ) ;
				$delete_url  = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-emaillog-delete' ) , $admin_url ) ) , 'rac-delete-emaillog' ) ;
				?>
				<a href="<?php echo esc_url( $delete_url ) ; ?>" class="button-primary"><?php echo esc_html( $button_name ) ; ?></a>
				<?php
			}
		}

		/**
		 * Update the post row actions
		 */
		public static function fp_rac_maillog_post_row_actions( $actions, $post ) {
			if ( 'racmaillog' == $post->post_type ) {
				unset( $actions[ 'edit' ] ) ;
				unset( $actions[ 'inline hide-if-no-js' ] ) ;
			}
			return $actions ;
		}

		/**
		 * Inner Join Functionality
		 */
		public static function fp_rac_email_log_post_inner_join_wordpress( $join, $wp_query ) {
			if ( ( isset( $wp_query->query[ 'post_type' ] ) && 'racmaillog' != $wp_query->query[ 'post_type' ] ) ) {
				return $join ;
			}

			if ( ( isset( $_REQUEST[ 'filter_action' ] ) && isset( $_REQUEST[ 'post_type' ] ) && 'racmaillog' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) ) {
				if ( empty( $join ) ) {
					global $wpdb ;
					$table_name    = $wpdb->prefix . 'posts' ;
					$another_table = $wpdb->prefix . 'postmeta' ;
					$join          .= " INNER JOIN $another_table ON ($table_name.ID = $another_table.post_id)" ;
				}
			}
			return $join ;
		}

		/**
		 * Distinct Functionality.
		 */
		public static function fp_rac_email_log_post_distinct_functionality( $distinct, $wp_query ) {
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racmaillog' != $wp_query->query[ 'post_type' ] ) {
				return $distinct ;
			}

			if ( isset( $_REQUEST[ 'filter_action' ] ) && isset( $_REQUEST[ 'post_type' ] ) && 'racmaillog' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) {
				if ( empty( $distinct ) ) {
					$distinct .= 'DISTINCT' ;
				}
			}
			return $distinct ;
		}

		/**
		 * Order By Functionality
		 */
		public static function fp_rac_post_orderby_functionality( $order_by, $wp_query ) {
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'racmaillog' != $wp_query->query[ 'post_type' ] ) {
				return $order_by ;
			}

			if ( isset( $_REQUEST[ 'post_type' ] ) && isset( $_REQUEST[ 'post_type' ] ) && 'racmaillog' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) {
				global $wpdb ;
				if ( ! isset( $_REQUEST[ 'order' ] ) && ! isset( $_REQUEST[ 'orderby' ] ) ) {
					$order    = fp_rac_backward_compatibility_for_table_sorting( 'rac_display_mail_log_basedon_asc_desc' ) ;
					$order_by = "{$wpdb->posts}.ID " . $order ;
				} else {
					$decimal_column = array(
						'rac_date_time' ,
						'rac_cart_id' ,
							) ;

					$orderby = wc_clean( wp_unslash( $_REQUEST[ 'orderby' ] ) ) ;
					if ( in_array( $orderby , $decimal_column ) ) {
						$order    = wc_clean( wp_unslash( $_REQUEST[ 'order' ] ) ) ;
						$order_by = "CAST({$wpdb->postmeta}.meta_value AS DECIMAL) " . $order ;
					}
				}
			}
			return $order_by ;
		}

		/**
		 * Date Filter action Functionality
		 */
		public static function fp_rac_email_log_posts_sorting_functionality( $where, $wp_query ) {
			if ( ( isset( $wp_query->query[ 'post_type' ] ) && 'racmaillog' != $wp_query->query[ 'post_type' ] ) ) {
				return $where ;
			}

			global $wpdb ;
			if ( isset( $_REQUEST[ 'filter_action' ] ) && isset( $_REQUEST[ 'post_type' ] ) && 'racmaillog' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) {
				$fromdate = isset( $_REQUEST[ 'rac_emaillog_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_emaillog_fromdate' ] ) ) : null ;
				$todate   = isset( $_REQUEST[ 'rac_emaillog_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_emaillog_todate' ] ) ) : null ;
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
		 * Delete and Restore email log functionality
		 */
		public static function fp_rac_move_all_emaillog_to_trash() {
			check_admin_referer( 'rac-delete-emaillog' ) ;
			$trash = 0 ;
			if ( isset( $_GET[ 'post_status' ] ) ) {
				$post_status = wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) ;
				$args        = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'racmaillog' ,
					'post_status'    => $post_status ,
					'fields'         => 'ids'
						) ;
				$move        = 'trash' == $post_status ? 1 : 2 ;
			} else {
				$args = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'racmaillog' ,
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
		 * Searching Functionality
		 */
		public static function fp_rac_email_log_search( $where ) {
			global $pagenow , $wpdb , $wp ;

			if ( 'edit.php' != $pagenow || ! is_search() || ! isset( $wp->query_vars[ 's' ] ) || 'racmaillog' != $wp->query_vars[ 'post_type' ] ) {
				return $where ;
			}


			$search_ids = array() ;
			$terms      = explode( ',' , $wp->query_vars[ 's' ] ) ;

			foreach ( $terms as $term ) {
				$term          = $wpdb->esc_like( wc_clean( $term ) ) ;
				$meta_array    = array(
					'rac_email_id' ,
					'rac_date_time' ,
					'rac_template_used' ,
					'rac_template_status' ,
					'rac_cart_id' ,
						) ;
				$implode_array = implode( "','" , $meta_array ) ;
				$post_status   = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
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
		 * Sorting Functionality
		 */
		public static function fp_rac_email_log_filters_query( $query ) {
			global $typenow ;

			if ( isset( $query->query[ 'post_type' ] ) && 'racmaillog' == $query->query[ 'post_type' ] ) {
				if ( 'racmaillog' == $typenow ) {
					if ( isset( $_GET[ 'orderby' ] ) ) {
						$excerpt_array = array( 'ID' , 'title' , 'post_content' ) ;
						$orderby       = wc_clean( wp_unslash( $_GET[ 'orderby' ] ) ) ;

						if ( ! in_array( $orderby , $excerpt_array ) ) {
							$query->query_vars[ 'meta_key' ] = $orderby ;
						}
					}
				}
			}
		}

	}

	FP_RAC_Maillog_Table::init() ;
}
