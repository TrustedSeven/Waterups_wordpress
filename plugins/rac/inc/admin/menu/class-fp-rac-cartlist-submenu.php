<?php
/**
 * Admin Cart List Custom Post Type.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}

if ( ! class_exists( 'FP_RAC_Cartlist_Table' ) ) {

	/**
	 * FP_RAC_Cartlist_Table Class.
	 */
	class FP_RAC_Cartlist_Table {

		/**
		 * FP_RAC_Cartlist_Table Class initialization.
		 */
		public static function init() {
			add_action( 'posts_join' , array( __CLASS__ , 'fp_rac_post_inner_join_wordpress' ) , 10 , 2 ) ;
			add_action( 'posts_orderby' , array( __CLASS__ , 'fp_rac_post_orderby_functionality' ) , 10 , 2 ) ;
			add_action( 'admin_action_rac-export-csv' , array( __CLASS__ , 'fp_rac_cartlist_export_csv' ) ) ;
			add_action( 'restrict_manage_posts' , array( __CLASS__ , 'fp_rac_add_cartlist_filter_option' ) ) ;
			add_action( 'posts_distinct' , array( __CLASS__ , 'fp_rac_post_distinct_functionality' ) , 10 , 2 ) ;
			add_action( 'admin_action_rac-update-status' , array( __CLASS__ , 'fp_rac_update_cart_list' ) ) ;
			add_action( 'manage_posts_extra_tablenav' , array( __CLASS__ , 'fp_rac_manage_posts_extra_table' ) ) ;
			add_action( 'posts_where' , array( __CLASS__ , 'fp_rac_pre_get_posts_sorting_functionality' ) , 10 , 2 ) ;
			add_action( 'posts_where' , array( __CLASS__ , 'fp_rac_subscribe_emails_filter' ) , 10 , 2 ) ;
			add_action( 'views_edit-raccartlist' , array( __CLASS__ , 'remove_post_type_views' ) ) ;
			add_action( 'admin_action_rac-send-email-cartlist' , array( __CLASS__ , 'fp_rac_send_all_cartlist' ) ) ;
			add_action( 'admin_action_rac-delete-cartlist' , array( __CLASS__ , 'fp_rac_move_all_cartlist_to_trash' ) ) ;
			add_action( 'admin_action_rac_send_single_cart_email' , array( __CLASS__ , 'fp_rac_send_each_row_cart_email' ) ) ;
			add_action( 'manage_raccartlist_posts_custom_column' , array( __CLASS__ , 'fp_rac_display_cartlist_table_data' ) , 10 , 2 ) ;

			add_filter( 'parse_query' , array( __CLASS__ , 'fp_rac_cartlist_filters_query' ) ) ;
			add_filter( 'posts_search' , array( __CLASS__ , 'fp_rac_cartlist_search_fields' ) ) ;
			add_filter( 'post_row_actions' , array( __CLASS__ , 'fp_rac_cartlist_post_row_actions' ) , 10 , 2 ) ;
			add_filter( 'bulk_post_updated_messages' , array( __CLASS__ , 'fp_rac_update_cartlist_status' ) , 10 , 2 ) ;
			add_filter( 'manage_raccartlist_posts_columns' , array( __CLASS__ , 'fp_rac_initialize_cartlist_columns' ) ) ;
			add_filter( 'bulk_actions-edit-raccartlist' , array( __CLASS__ , 'fp_rac_cartlist_bulk_post_actions' ) , 10 , 1 ) ;
			add_filter( 'manage_edit-raccartlist_sortable_columns' , array( __CLASS__ , 'fp_rac_cartlist_sortable_columns' ) ) ;
			add_filter( 'handle_bulk_actions-edit-raccartlist' , array( __CLASS__ , 'fp_rac_bulk_actions_functionality' ) , 10 , 3 ) ;
		}

		/**
		 * Initialization of columns in cart list table
		 */
		public static function fp_rac_initialize_cartlist_columns( $columns ) {
			$columns = array(
				'cb'                         => $columns[ 'cb' ] ,
				'id'                         => __( 'ID' , 'recoverabandoncart' ) ,
				'rac_cart_details'           => __( 'Cart Details / Cart Total' , 'recoverabandoncart' ) ,
				'rac_user_details'           => __( 'User Name / First Last Name' , 'recoverabandoncart' ) ,
				'rac_cart_email_id'          => __( 'Email ID / Phone Number' , 'recoverabandoncart' ) ,
				'rac_capture_by'             => __( 'Email Captured By' , 'recoverabandoncart' ) ,
				'rac_cart_abandoned_time'    => __( 'Abandoned Date / Time' , 'recoverabandoncart' ) ,
				'rac_cart_status'            => __( 'Status' , 'recoverabandoncart' ) ,
				'rac_cart_email_template_id' => __( 'Email Template / Email Status / Cart Link in Email' , 'recoverabandoncart' ) ,
				'rac_recovered_order_id'     => __( 'Recovered Order ID' , 'recoverabandoncart' ) ,
				'rac_coupon_details'         => __( 'Coupon Used' , 'recoverabandoncart' ) ,
				'rac_cart_payment_details'   => __( 'Payment Status' , 'recoverabandoncart' ) ,
				'cart_email_status'          => __( 'Email Status' , 'recoverabandoncart' ) ,
					) ;
			return $columns ;
		}

		/**
		 * Initialization of sortable columns in cart list table
		 */
		public static function fp_rac_cartlist_sortable_columns( $columns ) {
			$array = array(
				'id'                       => 'ID' ,
				'rac_cart_email_id'        => 'rac_cart_email_id' ,
				'rac_capture_by'           => 'rac_capture_by' ,
				'rac_cart_abandoned_time'  => 'rac_cart_abandoned_time' ,
				'rac_cart_status'          => 'post_status' ,
				'rac_recovered_order_id'   => 'rac_recovered_order_id' ,
				'rac_cart_payment_details' => 'rac_cart_payment_details' ,
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
		 * Display each column data in cart list table
		 */
		public static function fp_rac_display_cartlist_table_data( $column, $postid ) {
			$cart_list = fp_rac_create_cart_list_obj( $postid ) ;
			switch ( $column ) {
				case 'id':
					echo '#' . esc_html( $postid ) ;
					break ;
				case 'rac_cart_details':
					self::fp_rac_display_cart_list_details_column( $cart_list ) ;
					break ;
				case 'rac_user_details':
					echo wp_kses_post( self::fp_rac_display_cart_list_user_details_column( $cart_list ) ) ;
					break ;
				case 'rac_cart_email_id':
					self::fp_rac_display_cart_list_user_email_column( $cart_list ) ;
					break ;
				case 'rac_capture_by':
					self::display_abandon_email_capture_by( $cart_list ) ;
					break ;
				case 'rac_cart_abandoned_time':
					echo wp_kses_post( self::fp_rac_display_cart_list_abandon_time_column( $cart_list ) ) ;
					break ;
				case 'rac_cart_status':
					self::fp_rac_display_cart_list_status_column( $cart_list ) ;
					break ;
				case 'rac_cart_email_template_id':
					self::fp_rac_display_cart_list_email_template_column( $cart_list ) ;
					break ;
				case 'rac_recovered_order_id':
					self::fp_rac_display_cart_list_recovered_orderid_column( $cart_list ) ;
					break ;
				case 'rac_coupon_details':
					self::fp_rac_display_cart_list_coupon_status_column( $cart_list ) ;
					break ;
				case 'rac_cart_payment_details':
					self::fp_rac_display_cart_list_payment_status_column( $cart_list ) ;
					break ;
				case 'cart_email_status':
					self::fp_rac_display_cart_list_mail_sending_column( $cart_list ) ;
					break ;
			}
		}

		/**
		 * Modify Bulk post actions in cart list table
		 */
		public static function fp_rac_cartlist_bulk_post_actions( $actions ) {
			global $post ;

			if ( 'raccartlist' != $post->post_type ) {
				return $actions ;
			}

			$extra_actions = array() ;
			if ( 'trash' != $post->post_status ) {
				$extra_actions = array(
					'rac-send'              => 'Send Manual Email(s)' ,
					'rac-start-emailstatus' => 'Start Automatic Email(s)' ,
					'rac-stop-emailstatus'  => 'Stop Automatic Email(s)'
						) ;
			}
			unset( $actions[ 'edit' ] ) ;
			$actions = array_merge( $extra_actions , $actions ) ;

			return $actions ;
		}

		/**
		 * Adding extra filter in cart list table.
		 */
		public static function fp_rac_add_cartlist_filter_option( $post_type ) {
			if ( 'raccartlist' == $post_type ) {
				//display tag filter for cart list table
				$unsubcribe_count = count( self::fp_rac_subcribe_email_count( 'IN' ) ) ;
				$subcribe_count   = count( self::fp_rac_subcribe_email_count() ) ;
				$users_count      = count( self::get_cartlist_ids() ) ;
				$guests_count     = count( self::get_cartlist_ids( true ) ) ;
				$selected_value   = isset( $_REQUEST[ 'fprac_cartlist_tag' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'fprac_cartlist_tag' ] ) ) : '' ;
				?>
				<select name="fprac_cartlist_tag">
					<option value='' <?php selected( $selected_value , '' ) ; ?>><?php esc_html_e( 'All' , 'recoverabandoncart' ) ; ?></option>
					<option value = 'subscribe' <?php selected( $selected_value , 'subscribe' ) ; ?> ><?php echo esc_html( __( 'Subscribed' , 'recoverabandoncart' ) . ' (' . $subcribe_count . ')' ) ; ?></option>
					<option value = 'unsubscribe' <?php selected( $selected_value , 'unsubscribe' ) ; ?> ><?php echo esc_html( __( 'Unsubscribed' , 'recoverabandoncart' ) . ' (' . $unsubcribe_count . ')' ) ; ?></option>
					<option value = 'users' <?php selected( $selected_value , 'users' ) ; ?> ><?php echo esc_html( __( 'Users' , 'recoverabandoncart' ) . ' (' . $users_count . ')' ) ; ?></option>
					<option value = 'guest' <?php selected( $selected_value , 'guest' ) ; ?> ><?php echo esc_html( __( 'Guests' , 'recoverabandoncart' ) . ' (' . $guests_count . ')' ) ; ?></option>
				</select>
				<?php
				//display date filter for cart list table
				$fromdate         = '' ;
				$todate           = '' ;
				if ( isset( $_REQUEST[ 'filter_action' ] ) ) {
					$fromdate = isset( $_REQUEST[ 'rac_cartlist_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_cartlist_fromdate' ] ) ) : '' ;
					$todate   = isset( $_REQUEST[ 'rac_cartlist_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_cartlist_todate' ] ) ) : '' ;
				}
				?>
				<input id='rac_from_date' placeholder=<?php esc_attr_e( 'From Date' , 'recoverabandoncart' ) ; ?> type='text' name='rac_cartlist_fromdate' value="<?php echo esc_attr( $fromdate ) ; ?>"/>
				<input id='rac_to_date' type='text' name='rac_cartlist_todate' value="<?php echo esc_attr( $todate ) ; ?>" placeholder=<?php esc_attr_e( 'To Date' , 'recoverabandoncart' ) ; ?>/>
				<?php
			}
		}

		private static function fp_rac_subcribe_email_count( $in = 'NOT IN' ) {
			$unsubscribe_emails = self::fp_rac_get_unsub_email_array() ;

			$args = array(
				'posts_per_page' => -1 ,
				'post_type'      => 'raccartlist' ,
				'post_status'    => array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ,
				'fields'         => 'ids' ,
				'meta_key'       => 'rac_cart_email_id' ,
				'meta_value'     => $unsubscribe_emails ,
				'meta_compare'   => $in
					) ;

			return get_posts( $args ) ;
		}

		private static function fp_rac_get_unsub_email_array() {
			global $wpdb ;
			$guest_unsub_emails  = array_filter( array_unique( ( array ) get_option( 'fp_rac_mail_unsubscribed' ) ) ) ;
			$member_unsub_emails = $wpdb->get_results( $wpdb->prepare( "SELECT users.user_email FROM  {$wpdb->users} AS users "
							. "LEFT JOIN {$wpdb->usermeta} AS meta ON users.ID = meta.user_id "
							. 'WHERE meta.meta_key=%s '
							. 'AND meta_value=%s' , 'fp_rac_mail_unsubscribed' , 'yes' ) , ARRAY_A ) ;

			$member_unsub_emails = fp_rac_array_column_function( $member_unsub_emails ) ;
			$unsub_email_array   = array_merge( $member_unsub_emails , $guest_unsub_emails ) ;
			return $unsub_email_array ;
		}

		/**
		 * Get user and guest id's for cartlist filter option.
		 */
		private static function get_cartlist_ids( $guest = false ) {

			$args = array(
				'posts_per_page' => -1 ,
				'post_type'      => 'raccartlist' ,
				'post_status'    => array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ,
				'fields'         => 'ids' ,
					) ;

			if ( $guest ) {
				$args[ 'author__in' ] = array( 0 ) ;
			} else {
				$args[ 'author__not_in' ] = array( 0 ) ;
			}

			return get_posts( $args ) ;
		}

		/**
		 * Adding Extra action in cart list table
		 */
		public static function fp_rac_manage_posts_extra_table( $which ) {
			global $post ;
			if ( ( 'top' === $which ) && ( ( ( is_object( $post ) && 'raccartlist' == $post->post_type ) ) || ( isset( $_REQUEST[ 'post_type' ] ) && 'raccartlist' == wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) ) ) ) {
				$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
				$button_name = 'trash' == $post_status ? __( 'Restore all Cart Lists' , 'recoverabandoncart' ) : __( 'Move all Cart Lists to Trash' , 'recoverabandoncart' ) ;
				$query_arg   = ! empty( $post_status ) ? '&post_status=' . $post_status : '' ;
				$admin_url   = admin_url( 'edit.php?post_type=raccartlist' . $query_arg ) ;
				$export_url  = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-export-csv' ) , $admin_url ) ) , 'rac-exportcsv' ) ;
				$update_url  = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-update-status' ) , $admin_url ) ) , 'rac-update-status' ) ;
				$delete_url  = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-delete-cartlist' ) , $admin_url ) ) , 'rac-delete-cartlist' ) ;
				$send_url    = wp_nonce_url( esc_url_raw( add_query_arg( array( 'action' => 'rac-send-email-cartlist' ) , $admin_url ) ) , 'rac-send-email' ) ;

				if ( ! isset( $_GET[ 'post_status' ] ) || 'trash' != $post_status ) {
					?>
					<a href="<?php echo esc_url( $send_url ) ; ?>" class="button-primary"><?php esc_html_e( 'Send Manual Email for all Carts' , 'recoverabandoncart' ) ; ?></a>
					<?php
				}
				?>
				<a href="<?php echo esc_url( $delete_url ) ; ?>" class="button-primary"><?php esc_html_e( $button_name , 'recoverabandoncart' ) ; ?></a>
				<?php
				if ( ! isset( $_GET[ 'post_status' ] ) || 'trash' != $post_status ) {
					?>
					<a href="<?php echo esc_url( $export_url ) ; ?>" class="button-primary"><?php esc_html_e( 'Export as CSV' , 'recoverabandoncart' ) ; ?></a>
					<?php
				}
				if ( 'yes' == get_option( 'rac_troubleshoot_update_cart_list_status_manual' ) ) {
					?>
					<a href="<?php echo esc_url( $update_url ) ; ?>" class="button-primary"><?php esc_html_e( 'Update Status' , 'recoverabandoncart' ) ; ?></a>

					<?php
				}
			}
		}

		/**
		 * Modify Row post actions in cart list table
		 */
		public static function fp_rac_cartlist_post_row_actions( $actions, $post ) {
			if ( 'raccartlist' == $post->post_type ) {
				$post_status = get_post_status( $post->ID ) ;
				$send_url    = wp_nonce_url( admin_url( 'post.php?post=raccartlist&action=rac_send_single_cart_email&amp;post=' . $post->ID ) , 'rac-send-email-' . $post->ID ) ;
				$send_link   = '<a href="' . $send_url . '" title="' . esc_attr__( 'Send Manual Cart Recovery Email' , 'recoverabandoncart' ) . '">' . __( 'Send Email' , 'recoverabandoncart' ) . '</a>' ;
				if ( 'trash' != $post->post_status ) {
					$actions[ 'rac-cartlist-send-email' ] = $send_link ;
				}
				unset( $actions[ 'edit' ] ) ;
				unset( $actions[ 'inline hide-if-no-js' ] ) ;
			}
			return $actions ;
		}

		/**
		 * Update the Cart list status.
		 */
		public static function fp_rac_update_cartlist_status( $messages, $count ) {
			if ( isset( $_GET[ 'post_type' ] ) && 'raccartlist' == wc_clean( wp_unslash( $_GET[ 'post_type' ] ) ) ) {
				// FOR ALL USER STATUS - - UPDATE ONLY
				//Members
				fp_rac_update_cartlist_status( 'member' ) ;
				//guest
				fp_rac_update_cartlist_status( 'guest' ) ;
				// FOR ALL USER STATUS - UPDATE ONLY END
			}
			return $messages ;
		}

		public static function fp_rac_subscribe_emails_filter( $where, $wp_query ) {
			global $pagenow , $wpdb ;

			if ( 'edit.php' != $pagenow || ! isset( $_REQUEST[ 'fprac_cartlist_tag' ] ) || 'raccartlist' != $wp_query->query[ 'post_type' ] ) {
				return $where ;
			}

			if ( ! empty( $_REQUEST[ 'fprac_cartlist_tag' ] ) ) {
				$post_ids      = array() ;
				$cart_list_tag = isset( $_REQUEST[ 'fprac_cartlist_tag' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'fprac_cartlist_tag' ] ) ) : '' ;
				if ( 'unsubscribe' == $cart_list_tag ) {
					$post_ids = self::fp_rac_subcribe_email_count( 'IN' ) ;
				} elseif ( 'subscribe' == $cart_list_tag ) {
					$post_ids = self::fp_rac_subcribe_email_count() ;
				} elseif ( 'users' == $cart_list_tag ) {
					$post_ids = self::get_cartlist_ids() ;
				} elseif ( 'guest' == $cart_list_tag ) {
					$post_ids = self::get_cartlist_ids( true ) ;
				}

				$post_ids = array_filter( array_unique( array_map( 'absint' , $post_ids ) ) ) ;

				$where .= " AND $wpdb->posts.ID IN (" . implode( ',' , $post_ids ) . ')' ;
			}

			return $where ;
		}

		/**
		 *  Searching Functionality
		 */
		public static function fp_rac_cartlist_search_fields( $where ) {
			global $pagenow , $wpdb , $wp ;

			if ( 'edit.php' != $pagenow || ! is_search() || ! isset( $wp->query_vars[ 's' ] ) || 'raccartlist' != $wp->query_vars[ 'post_type' ] ) {
				return $where ;
			}

			$search_ids = array() ;
			$terms      = explode( ',' , $wp->query_vars[ 's' ] ) ;

			foreach ( $terms as $term ) {
				$term          = $wpdb->esc_like( wc_clean( $term ) ) ;
				$meta_array    = array(
					'rac_recovered_order_id' ,
					'rac_cart_payment_details' ,
					'rac_cart_email_id' ,
					'rac_user_info' ,
					'rac_phone_number' ,
					'rac_coupon_details' ,
						) ;
				$implode_array = implode( "','" , $meta_array ) ;
				if ( isset( $_GET[ 'post_status' ] ) && 'all' != wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) ) {
					$post_status = wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) ;
				} else {
					$post_status_array = array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ;
					$post_status       = implode( "','" , $post_status_array ) ;
				}

				$product_search = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pm.post_id
                                 FROM {$wpdb->postmeta} as pm
                                 INNER JOIN {$wpdb->posts} as p
                                 ON FIND_IN_SET(p.ID, pm.meta_value)
                                 WHERE pm.meta_key=%s AND p.post_type=%s
                                 AND p.post_title LIKE %s" , 'rac_product_details' , 'product' , '%' . $term . '%' ) ) ;

				$user_displayname_search = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pm.post_id
                                 FROM {$wpdb->base_prefix}postmeta as pm
                                 INNER JOIN {$wpdb->base_prefix}users as user ON pm.meta_value=user.ID
                                 WHERE pm.meta_key=%s AND user.user_login LIKE %s" , 'rac_user_info' , '%' . $term . '%' ) ) ;

				$user_name_search = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT pm.post_id
                                 FROM {$wpdb->base_prefix}postmeta as pm
                                 INNER JOIN {$wpdb->base_prefix}usermeta as user_meta ON pm.meta_value=user_meta.user_id
                                 WHERE pm.meta_key=%s AND user_meta.meta_key IN(%s,%s) AND user_meta.meta_value LIKE %s" , 'rac_user_info' , 'first_name' , 'last_name' , '%' . $term . '%' ) ) ;

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
			$search_ids = array_merge( $search_ids , $product_search , $user_displayname_search , $user_name_search ) ;
			$search_ids = array_filter( array_unique( array_map( 'absint' , $search_ids ) ) ) ;
			if ( count( $search_ids ) > 0 ) {
				$where = str_replace( 'AND (((' , "AND ( ({$wpdb->posts}.ID IN (" . implode( ',' , $search_ids ) . ')) OR ((' , $where ) ;
			}

			return $where ;
		}

		/**
		 *  Sorting Functionality
		 */
		public static function fp_rac_cartlist_filters_query( $query ) {
			if ( isset( $_REQUEST[ 'post_type' ] ) && 'raccartlist' == wp_unslash( $_REQUEST[ 'post_type' ] ) && 'raccartlist' == $query->query[ 'post_type' ] ) {
				$excerpt_array = array( 'ID' , 'post_status' ) ;
				$order_by      = isset( $_GET[ 'orderby' ] ) ? wc_clean( wp_unslash( $_GET[ 'orderby' ] ) ) : '' ;

				if ( $order_by && ! in_array( $order_by , $excerpt_array ) ) {
					$query->query_vars[ 'meta_key' ] = $order_by ;
				}
			}
		}

		/**
		 *  Inner Join Functionality
		 */
		public static function fp_rac_post_inner_join_wordpress( $join, $wp_query ) {
			global $wp ;
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'raccartlist' != $wp_query->query[ 'post_type' ] ) {
				return $join ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( ( isset( $_REQUEST[ 'filter_action' ] ) && 'raccartlist' == $post_type ) && empty( $join ) ) {
				global $wpdb ;
				$table_name    = $wpdb->prefix . 'posts' ;
				$another_table = $wpdb->prefix . 'postmeta' ;
				$join          .= " INNER JOIN $another_table ON ($table_name.ID = $another_table.post_id)" ;
			}
			return $join ;
		}

		/**
		 *  Distinct Functionality
		 */
		public static function fp_rac_post_distinct_functionality( $distinct, $wp_query ) {
			global $wp ;
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'raccartlist' != $wp_query->query[ 'post_type' ] ) {
				return $distinct ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'filter_action' ] ) && 'raccartlist' == $post_type ) {
				if ( empty( $distinct ) ) {
					$distinct .= 'DISTINCT' ;
				}
			}
			return $distinct ;
		}

		/**
		 *  Orderby Functionality
		 */
		public static function fp_rac_post_orderby_functionality( $order_by, $wp_query ) {
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'raccartlist' != $wp_query->query[ 'post_type' ] ) {
				return $order_by ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'post_type' ] ) && 'raccartlist' == $post_type ) {
				global $wpdb ;
				if ( ! isset( $_REQUEST[ 'order' ] ) && ! isset( $_REQUEST[ 'orderby' ] ) ) {
					$order    = fp_rac_backward_compatibility_for_table_sorting( 'rac_display_cart_list_basedon_asc_desc' ) ;
					$order_by = "{$wpdb->posts}.ID " . $order ;
				} else {
					$decimal_column = array(
						'rac_cart_abandoned_time' ,
						'rac_recovered_order_id' ,
							) ;

					$order_by = wc_clean( wp_unslash( $_REQUEST[ 'orderby' ] ) ) ;
					$order    = wc_clean( wp_unslash( $_REQUEST[ 'order' ] ) ) ;
					if ( in_array( $order_by , $decimal_column ) ) {
						$order_by = "CAST({$wpdb->postmeta}.meta_value AS DECIMAL) " . $order ;
					} elseif ( 'post_status' == $order_by ) {
						$order_by = "{$wpdb->posts}.post_status " . $order ;
					}
				}
			}

			return $order_by ;
		}

		/**
		 *  Sorting Functionality
		 */
		public static function fp_rac_pre_get_posts_sorting_functionality( $where, $wp_query ) {
			global $wpdb ;
			if ( isset( $wp_query->query[ 'post_type' ] ) && 'raccartlist' != $wp_query->query[ 'post_type' ] ) {
				return $where ;
			}

			$post_type = isset( $_REQUEST[ 'post_type' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'post_type' ] ) ) : '' ;
			if ( isset( $_REQUEST[ 'filter_action' ] ) && 'raccartlist' == $post_type ) {
				$fromdate = isset( $_REQUEST[ 'rac_cartlist_fromdate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_cartlist_fromdate' ] ) ) : null ;
				$todate   = isset( $_REQUEST[ 'rac_cartlist_todate' ] ) ? wc_clean( wp_unslash( $_REQUEST[ 'rac_cartlist_todate' ] ) ) : null ;
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
		 *  Bulk actions functionality.
		 */
		public static function fp_rac_bulk_actions_functionality( $url, $action, $post_ids ) {
			global $wpdb ;
			if ( 'rac-send' == $action ) {
				self::fp_rac_send_cartlist( $post_ids , 'post_ids' , true ) ;
			} elseif ( 'rac-start-emailstatus' == $action || 'rac-stop-emailstatus' == $action ) {
				$status = 'rac-start-emailstatus' == $action ? 'SEND' : "DON'T" ;
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value=%s WHERE meta_key='rac_cart_sending_status' AND post_id IN(%s)" , $status , "'" . implode( ',' , $post_ids ) ) ) . "'" ;
			}
			return $url ;
		}

		/**
		 *  Update Status by Update status button
		 */
		public static function fp_rac_update_cart_list() {
			check_admin_referer( 'rac-update-status' ) ;
			// FOR ALL USER STATUS - - UPDATE ONLY
			//Members
			fp_rac_update_cartlist_status( 'member' ) ;
			//guest
			fp_rac_update_cartlist_status( 'guest' ) ;
			// FOR ALL USER STATUS - UPDATE ONLY END

			$url = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? esc_url_raw( $_SERVER[ 'HTTP_REFERER' ] ) : '' ;
			wp_redirect( $url ) ;
			exit() ;
		}

		/**
		 *  Delete and Restore the cart list.
		 */
		public static function fp_rac_move_all_cartlist_to_trash() {
			check_admin_referer( 'rac-delete-cartlist' ) ;
			$trash = 0 ;
			if ( isset( $_GET[ 'post_status' ] ) ) {
				$post_status = wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) ;
				$args        = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'raccartlist' ,
					'post_status'    => $post_status ,
					'fields'         => 'ids'
						) ;
				$move        = 'trash' == $post_status ? 1 : 2 ;
			} else {
				$args = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'raccartlist' ,
					'post_status'    => array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ,
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

					$trash ++ ;
				}
			}
			$url = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? esc_url_raw( $_SERVER[ 'HTTP_REFERER' ] ) : '' ;
			wp_redirect( $url ) ;
			exit() ;
		}

		/**
		 *  Send email to each cart list.
		 */
		public static function fp_rac_send_each_row_cart_email() {
			if ( empty( $_REQUEST[ 'post' ] ) ) {
				wp_die( esc_html__( 'No Cart List to send email!' , 'recoverabandoncart' ) ) ;
			}

			// Get the original page
			$id = isset( $_REQUEST[ 'post' ] ) ? absint( $_REQUEST[ 'post' ] ) : '' ;

			check_admin_referer( 'rac-send-email-' . $id ) ;

			$id = absint( $id ) ;
			if ( ! $id ) {
				return false ;
			}

			self::fp_rac_send_cartlist( $id ) ;
		}

		/**
		 *  Sending Email to all Cart list manually.
		 */
		public static function fp_rac_send_all_cartlist() {
			check_admin_referer( 'rac-send-email' ) ;
			$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
			if ( isset( $_GET[ 'post_status' ] ) && 'trash' != $post_status ) {
				$post_status = $post_status ;
			} else {
				$post_status = array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ;
			}
			self::fp_rac_send_cartlist( $post_status , 'rac_post_status' ) ;
		}

		public static function fp_rac_send_cartlist( $post_ids, $post_name = 'post_ids', $redirect = false ) {
			$templateid   = false ;
			$template_ids = fp_rac_get_template_ids() ;
			if ( rac_check_is_array( $template_ids ) ) {
				$templateid = ( int ) $template_ids[ 0 ] ;
			}
			if ( ! $templateid && ! $redirect ) {
				$url = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? esc_url_raw( $_SERVER[ 'HTTP_REFERER' ] ) : '' ;
				wp_redirect( $url ) ;
				exit() ;
			}
			if ( $templateid ) {
				$query     = http_build_query( array( $post_name => $post_ids ) ) ;
				$admin_url = admin_url( 'post.php?post=' . $templateid . '&action=edit&rac_send_email=yes&' . $query ) ;
				wp_redirect( $admin_url ) ;
				exit() ;
			}
		}

		/**
		 *  Export Cart list Data as csv file
		 */
		public static function fp_rac_cartlist_export_csv() {
			check_admin_referer( 'rac-exportcsv' ) ;
			$post_status = isset( $_GET[ 'post_status' ] ) ? wc_clean( wp_unslash( $_GET[ 'post_status' ] ) ) : '' ;
			if ( isset( $_GET[ 'post_status' ] ) && 'trash' != $post_status ) {
				$args = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'raccartlist' ,
					'post_status'    => $post_status
						) ;
			} else {
				$args = array(
					'posts_per_page' => -1 ,
					'post_type'      => 'raccartlist' ,
					'post_status'    => array( 'rac-cart-new' , 'rac-cart-abandon' , 'rac-cart-recovered' ) ,
						) ;
			}

			$posts = fp_rac_check_query_having_posts( $args ) ;
			$array = array() ;
			if ( rac_check_is_array( $posts ) ) {
				foreach ( $posts as $post ) {
					$obj_cart_lists = fp_rac_create_cart_list_obj( $post->ID ) ;
					$details        = fp_rac_format_cart_details( $obj_cart_lists->cart_details , $obj_cart_lists ) ;

					if ( rac_check_is_array( $details ) ) {
						if ( '0' == $obj_cart_lists->user_id ) {
							$obj_cart_lists->phone_no = isset( $details[ 'visitor_phone' ] ) ? $details[ 'visitor_phone' ] : '-' ;
						} else {
							$user_info = get_userdata( $obj_cart_lists->user_id ) ;
							if ( is_object( $user_info ) ) {
								$obj_cart_lists->phone_no = isset( $user_info->billing_phone ) ? $user_info->billing_phone : '-' ;
							}
						}
					}

					if ( is_object( $details ) ) {
						$old_order_obj = new FP_RAC_Previous_Order_Data( $obj_cart_lists ) ;

						if ( $old_order_obj->get_cart_content() ) {
							$user_id  = $old_order_obj->get_user_id() ;
							$user_obj = get_userdata( $user_id ) ;

							if ( is_object( $user_obj ) ) {
								$obj_cart_lists->phone_no = $user_obj->billing_phone ;
							} else {
								$phone_number             = $old_order_obj->get_billing_phoneno() ;
								$obj_cart_lists->phone_no = isset( $phone_number ) ? $phone_number : '-' ;
							}
						}
					}

					$cart_lists     = ( array ) $obj_cart_lists ;
					$export_default = array(
						'id'                => false,
						'cart_details'      => false,
						'user_id'           => false,
						'user_name'         => false,
						'email_id'          => false,
						'phone_no'          => false,
						'cart_abandon_time' => false,
						'cart_status'       => false,
						'mail_template_id'  => false,
						'ip_address'        => false,
						'link_status'       => false,
						'sending_status'    => false,
						'wpml_lang'         => false,
						'placed_order'      => false,
						'completed'         => false
							) ;
					$cart_lists     = array_merge( $export_default , $cart_lists ) ;
					$new_array      = array() ;
					if ( rac_check_is_array( $cart_lists ) ) {
						foreach ( $cart_lists as $key => $cart_list ) {
							if ( isset( $export_default[ $key ] ) ) {
								if ( 'cart_status' == $key ) {
									$new_array[ $key ] = fp_rac_get_cart_status_name( $cart_list ) ;
								} elseif ( 'user_name' == $key ) {
									$new_array[ $key ] = self::fp_rac_display_cart_list_user_details_column( $obj_cart_lists ) ;
								} elseif ( 'cart_abandon_time' == $key ) {
									$new_array[ $key ] = self::fp_rac_display_cart_list_abandon_time_column( $obj_cart_lists ) ;
								} elseif ( 'cart_details' != $key ) {
									if ( 'mail_template_sending_time' != $key && 'cart_link_clicked_time_log' != $key && 'currency_code' != $key ) {
										$new_array[ $key ] = $cart_list ;
									}
								} else {
									ob_start() ;
									$product_details   = fp_rac_cart_details( $obj_cart_lists ) ;
									$products          = ( 'no data' != $product_details ) ? $product_details : esc_html_e( 'Product Details not Available' , 'recoverabandoncart' ) ;
									echo wp_kses_post( $products ) ;
									$string            = ob_get_clean() ;
									$string1           = str_replace( ' ' , '' , html_entity_decode( strip_tags( $string ) ) ) ;
									$new_array[ $key ] = $string1 ;
								}
							}
						}
						$new_array = apply_filters( 'fp_rac_export_custom_row' , $new_array , $cart_lists ) ;
					}
					array_push( $array , $new_array ) ;
				}
			}
			ob_end_clean() ;
			header( 'Content-type: text/csv' ) ;
			header( 'Content-Disposition: attachment; filename=rac_cartlist' . date_i18n( 'Y-m-d H:i:s' ) . '.csv' ) ;
			header( 'Pragma: no-cache' ) ;
			header( 'Expires: 0' ) ;

			$output      = fopen( 'php://output' , 'w' ) ;
			$delimiter   = ',' ;
			$delimiter   = apply_filters( 'fp_rac_export_delimiter' , $delimiter ) ;
			$enclosure   = '"' ;
			$enclosure   = apply_filters( 'fp_rac_export_enclosure', $enclosure ) ;
			$row_heading = array( 'id', 'cart_details', 'user_id', 'user_name', 'email_id', 'phone_number', 'cart_abandon_time', 'cart_status', 'mail_template_id', 'ip_address', 'link_status', 'sending_status', 'wpml_lang', 'placed_order', 'completed' ) ;
			$row_heading = apply_filters( 'fp_rac_export_headings', $row_heading ) ;
			fputcsv( $output, $row_heading, $delimiter, $enclosure ) ; // here you can change delimiter/enclosure
			foreach ( $array as $row ) {
				$row = apply_filters( 'fp_rac_export_row' , $row ) ;
				fputcsv( $output , $row , $delimiter , $enclosure ) ; // here you can change delimiter/enclosure
			}
			fclose( $output ) ;
			exit() ;
		}

		/**
		 * Cart list product details
		 */
		public static function fp_rac_display_cart_list_details_column( $each_list ) {
			$product_details = fp_rac_cart_details( $each_list ) ;
			if ( 'no data' == $product_details ) {
				esc_html_e( 'Product Details not Available' , 'recoverabandoncart' ) ;
			} else {
				echo '<p class="fp-rac-link">' . wp_kses_post( fp_rac_cart_details( $each_list ) ) . '</p>' ;
			}
		}

		/**
		 * Cart list User Details.
		 */
		public static function fp_rac_display_cart_list_user_details_column( $each_list ) {
			$user_details = '' ;
			$user_info    = get_userdata( $each_list->user_id ) ;
			if ( is_object( $user_info ) ) {
				$user_details .= $user_info->user_login ;
				$user_details .= " /  $user_info->user_firstname $user_info->user_lastname" ;
			} elseif ( '0' == $each_list->user_id ) {
				$cart_array   = fp_rac_format_cart_details( $each_list->cart_details, $each_list ) ;
				$user_details .= __( 'Guest', 'recoverabandoncart' ) ;
				if ( is_array( $cart_array ) ) {
					//for cart captured at checkout(GUEST)
					$first_name   = $cart_array[ 'first_name' ] ;
					$last_name    = $cart_array[ 'last_name' ] ;
					$user_details .= " / $first_name $last_name" ;

					unset( $cart_array[ 'visitor_mail' ] ) ;
					unset( $cart_array[ 'first_name' ] ) ;
					unset( $cart_array[ 'last_name' ] ) ;
					if ( isset( $cart_array[ 'visitor_phone' ] ) ) {
						unset( $cart_array[ 'visitor_phone' ] ) ;
					}
					if ( isset( $cart_array[ 'shipping_details' ] ) ) {
						unset( $cart_array[ 'shipping_details' ] ) ;
					}
					if ( isset( $cart_array[ 'woocs_is_multipled' ] ) ) {
						unset( $cart_array[ 'woocs_is_multipled' ] ) ;
					}
				} elseif ( is_object( $cart_array ) ) { // For Guest
					$user_details .= ' / ' . fp_rac_get_order_obj_data( $cart_array, 'billing_first_name' ) . ' ' . fp_rac_get_order_obj_data( $cart_array, 'billing_last_name' ) ;
				}
			} elseif ( 'old_order' == $each_list->user_id ) {
				$old_order_obj = new FP_RAC_Previous_Order_Data( $each_list ) ;
				if ( $old_order_obj->get_cart_content() ) {
					$user_id  = $old_order_obj->get_user_id() ;
					$user_obj = get_userdata( $user_id ) ;
					if ( is_object( $user_obj ) ) {
						$user_details .= $user_obj->user_login ;
						$user_details .= " / $user_obj->user_firstname $user_obj->user_lastname" ;
					} else {
						$billing_first_name = $old_order_obj->get_billing_firstname() ;
						$billing_last_name  = $old_order_obj->get_billing_lastname() ;
						$user_details       .= __( 'Guest', 'recoverabandoncart' ) ;
						$user_details       .= ' / ' . $billing_first_name . ' ' . $billing_last_name ;
					}
				} else {
					$user_details .= __( 'Order Details not Available', 'recoverabandoncart' ) ;
				}
			}

			return $user_details ;
		}

		/**
		 * Cart list User Email and Phone number
		 */
		private static function fp_rac_display_cart_list_user_email_column( $each_list ) {
			$guest_email = '' ;
			$userid      = 0 ;
			if ( '0' == $each_list->user_id ) {
				$details = fp_rac_format_cart_details( $each_list->cart_details , $each_list ) ;
				if ( is_object( $details ) ) {
					?>
					<div class="rac_tool_info"><p class="rac_edit_option" data-id="<?php echo esc_attr( $each_list->id ) ; ?>" >
							<?php
							$guest_email = fp_rac_get_order_obj_data( $details , 'billing_email' ) ;
							echo esc_html( $guest_email ) ; // Order Object. Works for both old order and rac captured order
							?>
						</p><div class="tooltip"><?php esc_html_e( 'Double Click to Change an Editable' , 'recoverabandoncart' ) ; ?></div></div>
					<?php
					echo '</br>&nbsp' . esc_html( fp_rac_get_order_obj_data( $details , 'billing_phone' ) ) ;
				} elseif ( is_array( $details ) ) {
					?>
					<div class="rac_tool_info"><p class="rac_edit_option" data-id="<?php echo esc_attr( $each_list->id ) ; ?>">
							<?php
							echo esc_html( $details[ 'visitor_mail' ] ) ; //checkout order
							$guest_email = $details[ 'visitor_mail' ] ;
							?>
						</p><div class="tooltip"><?php esc_html_e( 'Double Click to Change an Editable' , 'recoverabandoncart' ) ; ?></div></div>
					<?php
					echo '</br>&nbsp' ;
					if ( isset( $details[ 'visitor_phone' ] ) ) {
						echo esc_html( $details[ 'visitor_phone' ] ) ;
					} else {
						echo '-' ;
					}
				}
			} elseif ( 'old_order' == $each_list->user_id ) {
				$old_order_obj = new FP_RAC_Previous_Order_Data( $each_list ) ;
				if ( $old_order_obj->get_cart_content() ) {
					$user_id  = $old_order_obj->get_user_id() ;
					$user_obj = get_userdata( $user_id ) ;
					if ( is_object( $user_obj ) ) {
						echo esc_html( $user_obj->user_email ) ;
						$userid = $user_id ;
						echo '</br> &nbsp' . esc_html( $user_obj->billing_phone ) ;
					} else {
						$billing_email = $old_order_obj->get_billing_email() ;
						$guest_email   = $billing_email ;
						$phone_number  = $old_order_obj->get_billing_phoneno() ;
						if ( ! empty( $billing_email ) ) {
							echo esc_html( $billing_email ) ;
							echo '</br> &nbsp' . esc_html( $phone_number ) ;
						} else {
							echo '-' ;
						}
					}
				} else {
					esc_html_e( 'Order Details not Available' , 'recoverabandoncart' ) ;
				}
			} else {
				$user_infor = get_userdata( $each_list->user_id ) ;
				$userid     = $each_list->user_id ;
				if ( is_object( $user_infor ) ) {
					echo esc_html( $user_infor->user_email ) ;
					echo '</br> &nbsp' . esc_html( $user_infor->billing_phone ) ;
				}
			}
			$param = $guest_email ? $guest_email : $userid ;
			$slug  = fp_rac_check_email_subscribed( $param ) ;
			if ( ! $slug ) {
				echo '</br> </br>' . esc_html__( 'Email ID has been Unsubscribed' , 'recoverabandoncart' ) ;
				echo '<br><div class="button rac_customer_email_subscribe" data-value="true" data-email_id="' . esc_attr( $guest_email ) . '" data-user_id="' . esc_attr( $userid ) . '">' . esc_html__( 'Subscribe' , 'recoverabandoncart' ) . '</div>' ;
			} else {
				echo '<br><div class="button rac_customer_email_subscribe" data-value="false" data-email_id="' . esc_attr( $guest_email ) . '" data-user_id="' . esc_attr( $userid ) . '">' . esc_html__( 'Unsubscribe' , 'recoverabandoncart' ) . '</div>' ;
			}
		}

		/**
		 * Abandon Email Captured By
		 */
		private static function display_abandon_email_capture_by( $each_list ) {
			if ( 1 == $each_list->capture_by ) {
				esc_html_e( 'Cart Page' , 'recoverabandoncart' ) ;
			} elseif ( 2 == $each_list->capture_by ) {
				esc_html_e( 'Checkout Page' , 'recoverabandoncart' ) ;
			} elseif ( 3 == $each_list->capture_by ) {
				esc_html_e( 'Order Page' , 'recoverabandoncart' ) ;
			} elseif ( 4 == $each_list->capture_by ) {
				esc_html_e( 'Popup Page' , 'recoverabandoncart' ) ;
			} else {
				esc_html_e( 'its available on latest version' , 'recoverabandoncart' ) ;
			}
		}

		/**
		 * Cart list abandon time
		 */
		private static function fp_rac_display_cart_list_abandon_time_column( $each_list ) {
			return esc_html( gmdate( get_option( 'date_format' ), $each_list->cart_abandon_time ) . '/' . gmdate( get_option( 'time_format' ), $each_list->cart_abandon_time ) ) ;
		}

		/**
		 * Cart list Status
		 */
		private static function fp_rac_display_cart_list_status_column( $each_list ) {
			$post_status = get_post_status( $each_list->id ) ;
			$img_src     = RAC_PLUGIN_URL . '/assets/images/update.gif' ;

			if ( 'trash' == $post_status ) {
				echo esc_html__( 'Trashed' , 'recoverabandoncart' ) ;
			} else {
				$post_name          = fp_rac_get_cart_status_name( $post_status ) ;
				$post_status_method = get_post_meta( $each_list->id , 'rac_recover_method' , true ) ;
				if ( 1 == $post_status_method ) {
					echo esc_html( $post_name ) . '<br>(' . esc_html__( 'Manually Recovered' , 'recoverabandoncart' ) . ')' ;
				} else {
					echo esc_html( $post_name ) ;
				}
			}
			if ( 'rac-cart-new' == $post_status || 'rac-cart-abandon' == $post_status ) {
				?>
				<p>
					<a href="#" class="button rac_manual_recovered" data-racmrid="<?php echo esc_attr( $each_list->id ) ; ?>"><?php esc_html_e( 'Mark as Recovered' , 'recoverabandoncart' ) ; ?></a>
					<img src='<?php echo esc_url( $img_src ) ; ?>' class="fp-rac-reload-img" target="" id="rac_load_image<?php echo esc_attr( $each_list->id ) ; ?>">

				</p>
				<?php
			}
		}

		/**
		 *  Template status for cart list
		 */
		private static function fp_rac_display_cart_list_email_template_column( $each_list ) {
			?>
			<a href="#" class="button rac_cartlist_email_status_info rac_mail_status_btn" data-rac_cart_list_id="<?php echo esc_attr( $each_list->id ) ; ?>">
				<img src="<?php echo esc_url( RAC_PLUGIN_URL . '/assets/images/view.png' ) ; ?>">
			</a>
			<?php
		}

		/**
		 * Cart list recovered order id
		 */
		private static function fp_rac_display_cart_list_recovered_orderid_column( $each_list ) {
			$post_status = get_post_status( $each_list->id ) ;

			if ( 'rac-cart-recovered' == $post_status && empty( $each_list->placed_order ) ) {
				?>
				<a href="#" class="button rac_cartlist_manual_recover_popup_link" data-rac_cart_list_id="<?php echo esc_attr( $each_list->id ) ; ?>"><?php echo esc_html__( 'Enter Order ID' , 'recoverabandoncart' ) ; ?></a>
				<?php
			} else {
				echo esc_html(  ! empty( $each_list->placed_order ) ? ' #' . $each_list->placed_order : __( 'Not Yet' , 'recoverabandoncart' )  ) ;
			}
		}

		/**
		 * Cart list coupon Status
		 */
		private static function fp_rac_display_cart_list_coupon_status_column( $each_list ) {
			if ( 'rac-cart-recovered' == $each_list->cart_status ) {
				$coupon_code = get_post_meta( $each_list->id , 'rac_cart_coupon_code' , true ) ;
				$order       = fp_rac_get_order_obj( $each_list->placed_order ) ;

				if ( ! empty( $each_list->placed_order ) ) {
					if ( $order ) {
						$coupons_used = fp_rac_get_order_used_coupons( $order ) ;
						if ( ! empty( $coupons_used ) ) {
							if ( in_array( strtolower( $coupon_code ) , $coupons_used ) ) {
								echo wp_kses_post( $coupon_code ) . ' - ' ;
								esc_html_e( 'Success' , 'recoverabandoncart' ) ;
							} else {
								esc_html_e( 'Not Used' , 'recoverabandoncart' ) ;
							}
						} else {
							esc_html_e( 'Not Used' , 'recoverabandoncart' ) ;
						}
					} else {
						esc_html_e( 'Order details not available' , 'recoverabandoncart' ) ;
					}
				} else {
					esc_html_e( 'Not Used' , 'recoverabandoncart' ) ;
				}
			} else {
				esc_html_e( 'Not Yet' , 'recoverabandoncart' ) ;
			}
		}

		/**
		 * Cart list Payment Status
		 */
		private static function fp_rac_display_cart_list_payment_status_column( $each_list ) {
			echo esc_html(  ! empty( $each_list->completed ) ? __( 'Completed' , 'recoverabandoncart' ) : __( 'Not Yet' , 'recoverabandoncart' )  ) ;
		}

		/**
		 * Cart list email sending button.
		 */
		private static function fp_rac_display_cart_list_mail_sending_column( $each_list ) {
			if ( 'trash' != $each_list->cart_status ) {
				if ( empty( $each_list->completed ) ) {
					//check if order completed,if completed don't show mail sending button'
					$status = 'SEND' == $each_list->sending_status ? 'DONT' : 'SEND' ;
					?>
					<input type="checkbox" class="rac_mail_status_checkboxes" data-racid="<?php echo esc_attr( $each_list->id ) ; ?>"/>
					<a href="#" class="button rac_mailstatus_check_indi" data-racmoptid="<?php echo esc_attr( $each_list->id ) ; ?>" data-currentsate="<?php echo esc_attr( $status ) ; ?>" disabled="disabled">
						<?php
						if ( 'SEND' == $each_list->sending_status ) {
							esc_html_e( 'Stop Emailing' , 'recoverabandoncart' ) ;
						} else {
							esc_html_e( 'Start Emailing' , 'recoverabandoncart' ) ;
						}
						?>
					</a>
					<?php
				} else {
					esc_html_e( 'Recovered' , 'recoverabandoncart' ) ;
				}
			} else {
				esc_html_e( 'Trashed' , 'recoverabandoncart' ) ;
			}
		}

	}

	FP_RAC_Cartlist_Table::init() ;
}
