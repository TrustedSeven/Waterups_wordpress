<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'FP_RAC_Reports_Tab' ) ) {

	/**
	 * FP_RAC_Reports_Tab Class.
	 */
	class FP_RAC_Reports_Tab {

		private static $tickSize ;
		private static $maillogData ;
		private static $recoveredData ;
		private static $abandonData ;
		private static $minData ;
		private static $maxData ;

		public static function init() {
			self::rac_get_overall_data() ;
			self::rac_get_min_max() ;
			self::rac_add_start_end_data() ;
		}

		public static function render() {
			self::fp_rac_reports() ;
			self::fp_rac_display_graph_for_reports() ;
		}

		public static function get_mail_logs() {
			return self::$maillogData ;
		}

		public static function get_recovered_logs() {
			return self::$recoveredData ;
		}

		public static function get_abandoned_carts() {
			return self::$abandonData ;
		}

		public static function get_tick_size() {
			return self::$tickSize ;
		}

		public static function fp_rac_reports() {
			global $wpdb ;
			if ( isset( $_REQUEST[ 'rac_clear_reports' ] ) ) {
				delete_option( 'rac_abandoned_count' ) ;
				delete_option( 'rac_mail_count' ) ;
				delete_option( 'rac_link_count' ) ;
				delete_option( 'rac_recovered_count' ) ;
				$args  = array(
					'post_type'   => 'racrecoveredorder',
					'post_status' => 'publish',
					'fields'      => 'ids'
						) ;
				$query = new WP_Query( $args ) ;
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post() ;
						wp_delete_post( $query->post, true ) ;
					}
				}
			}
			add_thickbox() ;
			?>
			<form id='mainform' method='post' enctype="multipart/form-data">
				<h2> <?php esc_html_e( 'Abandoned Cart Reports', 'recoverabandoncart' ) ; ?></h2>

				<table class="rac_reports form-table">
					<tr>
						<th>
							<?php esc_html_e( 'Number of Abandoned Carts Captured', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							if ( get_option( 'rac_abandoned_count' ) ) {
								echo esc_html( get_option( 'rac_abandoned_count' ) ) ;
							} else {// if it is boolean false then there is no value. so give 0
								echo '0' ;
							} ;
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Number of Total Emails Sent', 'recoverabandoncart' ) ; ?> 
						</th>
						<td>
							<?php
							if ( get_option( 'rac_mail_count' ) ) {
								echo esc_html( get_option( 'rac_mail_count' ) ) ;
							} else {
								echo '0' ;
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Number of Total Email Links Clicked', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							if ( get_option( 'rac_link_count' ) ) {
								echo esc_html( get_option( 'rac_link_count' ) ) ;
							} else {
								echo '0' ;
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Number of Carts Recovered', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							if ( get_option( 'rac_recovered_count' ) ) {
								$fpracrecoveredorderids = esc_url_raw( add_query_arg( array( 'post_type' => 'racrecoveredorder' ), admin_url( 'edit.php' ) ) ) ;
								echo '<a class="fp-rac-link" href="' . esc_url( $fpracrecoveredorderids ) . '">' . esc_html( get_option( 'rac_recovered_count' ) ) . '</a>&nbsp;' ;
							} else {
								echo '0' ;
							}
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Total Sales Amount Recovered', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							$total_sum = $wpdb->get_var( $wpdb->prepare( "
                                  SELECT sum(pm.meta_value) 
                                  FROM $wpdb->postmeta as pm 
                                  INNER JOIN $wpdb->posts as p 
                                  ON p.ID=pm.post_id     
                                  WHERE pm.meta_key = %s 
                                  AND p.post_status='publish'
                                  AND p.post_type='racrecoveredorder'", 'rac_recovered_sales_total' )
									) ;
							echo wp_kses_post( fp_rac_format_price( $total_sum ) ) ;
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Frequently Abandoned Products', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							$title     = __( 'Frequently Abandoned Products', 'recoverabandoncart' ) ;
							$url       = add_query_arg( array( 'wc-ajax' => 'fp_rac_dislay_top_abandoned_products', 'TB_iframe' => 'true', 'width' => '800', 'height' => '500' ), home_url() ) ;
							echo '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html__( 'Products which are frequently abandoned by the user', 'recoverabandoncart' ) . '</a>' ;
							?>
						</td>
					</tr>
					<tr>
						<th>
							<?php esc_html_e( 'Frequently Recovered Products', 'recoverabandoncart' ) ; ?>
						</th>
						<td>
							<?php
							$title     = __( 'Frequently Recovered Products', 'recoverabandoncart' ) ;
							$url       = add_query_arg( array( 'wc-ajax' => 'fp_rac_dislay_top_recovered_products', 'TB_iframe' => 'true', 'width' => '800', 'height' => '500' ), home_url() ) ;
							echo '<a href="' . esc_url( $url ) . '" class="thickbox" title="' . esc_attr( $title ) . '">' . esc_html__( 'Products which are frequently recovered by the user', 'recoverabandoncart' ) . '</a>' ;
							?>
						</td>
					</tr>
				</table>
				<br>
				<input type="submit" name="rac_clear_reports" id="rac_clear_reports" class="rac_clear_reports button-primary" value="<?php esc_attr_e( 'Clear Reports', 'recoverabandoncart' ) ; ?>" onclick="return confirm( '<?php esc_attr_e( 'Are you sure to clear the reports ?', 'recoverabandoncart' ) ; ?>' )">
			</form>
			<?php
		}

		// Display Reports Graph
		public static function fp_rac_display_graph_for_reports() {
			$period_formatted_options = '' ;
			$data_formatted_options   = '' ;
			$period_options           = array( 'alltime'   => 'All Time',
				'last7days' => 'Last 7 Days',
				'thismonth' => 'This Month',
				'lastmonth' => 'Last Month',
				'3months'   => '3 Months',
				'6months'   => '6 Months',
				'thisyear'  => 'This Year',
				'lastyear'  => 'Last Year'
					) ;
			foreach ( $period_options as $key => $option ) {
				if ( isset( $_REQUEST[ 'rac_reports_period_selection' ] ) ) {
					if ( wc_clean( wp_unslash( $_REQUEST[ 'rac_reports_period_selection' ] ) ) == $key ) {
						$selected = 'selected=selected' ;
					} else {
						$selected = '' ;
					}
				} else {
					$selected = '' ;
				}
				$period_formatted_options .= '<option value=' . $key . ' ' . $selected . '>' . $option . '</option>' ;
			}
			$data_options = array( 'alldata'         => 'All Data',
				'abandonedcarts'  => 'Abandoned Carts',
				'maillog'         => 'Email Log',
				'recovceredorder' => 'Recovered Orders',
					) ;
			foreach ( $data_options as $key => $option ) {
				if ( isset( $_REQUEST[ 'rac_reports_data_selection' ] ) ) {
					if ( wc_clean( wp_unslash( $_REQUEST[ 'rac_reports_period_selection' ] ) ) == $key ) {
						$selected = 'selected=selected' ;
					} else {
						$selected = '' ;
					}
				} else {
					$selected = '' ;
				}
				$data_formatted_options .= '<option value=' . $key . ' ' . $selected . '>' . $option . '</option>' ;
			}
			?>
			<form id='mainform' method='post'>
				<div id="poststuff">
					<div class="postbox ">
						<h3><?php esc_html_e( 'Reports Graph', 'recoverabandoncart' ) ; ?></h3>
						<div class="inside">
							<div class="rac_selection_area">
								<p>
									<select class="rac_reports_data_selection" id="rac_reports_data_selection" name="rac_reports_data_selection"><?php echo do_shortcode( $data_formatted_options ) ; ?></select>
									<select class="rac_reports_period_selection" id="rac_reports_period_selection" name="rac_reports_period_selection"><?php echo do_shortcode( $period_formatted_options ) ; ?></select>
									<input type="submit" value="<?php esc_attr_e( 'Filter', 'recoverabandoncart' ) ; ?>" name="rac_submit_view_reports" class="button-secondary"/>
								</p>
							</div>
							<div class="rac_each_container">
								<div id="rac_each_container_details" class="rac_each_container_details" ></div>
							</div>                     
						</div>
					</div>   
				</div>
			</form>
			<?php
		}

		private static function rac_get_overall_data() {
			if ( isset( $_REQUEST[ 'rac_reports_data_selection' ] ) ) {
				$data_selection = wc_clean( wp_unslash( $_REQUEST[ 'rac_reports_data_selection' ] ) ) ;
				if ( 'alldata' == $data_selection ) {
					$mail_log_format_data         = self::rac_get_graph_data( 'racmaillog', 'publish', 'rac_date_time' ) ;
					$abandon_cart_format_data     = self::rac_get_graph_data( 'raccartlist', 'rac-cart-abandon', 'rac_cart_abandoned_time' ) ;
					$recovered_orders_format_data = self::rac_get_graph_data( 'racrecoveredorder', 'publish', 'rac_recovered_date' ) ;
				} elseif ( 'abandonedcarts' == $data_selection ) {
					$abandon_cart_format_data     = self::rac_get_graph_data( 'raccartlist', 'rac-cart-abandon', 'rac_cart_abandoned_time' ) ;
					$recovered_orders_format_data = array() ;
					$mail_log_format_data         = array() ;
				} elseif ( 'maillog' == $data_selection ) {
					$mail_log_format_data         = self::rac_get_graph_data( 'racmaillog', 'publish', 'rac_date_time' ) ;
					$recovered_orders_format_data = array() ;
					$abandon_cart_format_data     = array() ;
				} else {
					$recovered_orders_format_data = self::rac_get_graph_data( 'racrecoveredorder', 'publish', 'rac_recovered_date' ) ;
					$mail_log_format_data         = array() ;
					$abandon_cart_format_data     = array() ;
				}
			} else {
				$mail_log_format_data         = self::rac_get_graph_data( 'racmaillog', 'publish', 'rac_date_time' ) ;
				$abandon_cart_format_data     = self::rac_get_graph_data( 'raccartlist', 'rac-cart-abandon', 'rac_cart_abandoned_time' ) ;
				$recovered_orders_format_data = self::rac_get_graph_data( 'racrecoveredorder', 'publish', 'rac_recovered_date' ) ;
			}

			self::$maillogData   = $mail_log_format_data ;
			self::$abandonData   = $abandon_cart_format_data ;
			self::$recoveredData = $recovered_orders_format_data ;
		}

		private static function rac_get_min_max() {
			$maillog_data   = self::$maillogData ;
			$abandon_data   = self::$abandonData ;
			$recovered_data = self::$recoveredData ;
			if ( is_array( self::$maillogData ) && ! empty( self::$maillogData ) ) {
				$maillog_min = $maillog_data[ 0 ] ;
				$maillog_max = end( $maillog_data ) ;
			}
			if ( is_array( self::$abandonData ) && ! empty( self::$abandonData ) ) {
				$abandon_min = $abandon_data[ 0 ] ;
				$abandon_max = end( $abandon_data ) ;
			}
			if ( is_array( self::$recoveredData ) && ! empty( self::$recoveredData ) ) {
				$recovered_min = $recovered_data[ 0 ] ;
				$recovered_max = end( $recovered_data ) ;
			}
			if ( empty( self::$maillogData ) && empty( self::$recoveredData ) && empty( self::$abandonData ) ) {
				self::$minData = array() ;
				self::$maxData = array() ;
			} elseif ( empty( self::$maillogData ) && empty( self::$recoveredData ) ) {
				self::$minData = $abandon_min[ 0 ] ;
				self::$maxData = $abandon_max[ 0 ] ;
			} elseif ( empty( self::$maillogData ) && empty( self::$abandonData ) ) {
				self::$minData = $recovered_min[ 0 ] ;
				self::$maxData = $recovered_max[ 0 ] ;
			} elseif ( empty( self::$recoveredData ) && empty( self::$abandonData ) ) {
				self::$minData = $maillog_min[ 0 ] ;
				self::$maxData = $maillog_max[ 0 ] ;
			} elseif ( ! empty( self::$maillogData ) && ! empty( self::$recoveredData ) && empty( self::$abandonData ) ) {
				self::$minData = min( $maillog_min[ 0 ], $recovered_min[ 0 ] ) ;
				self::$maxData = max( $maillog_max[ 0 ], $recovered_max[ 0 ] ) ;
			} elseif ( ! empty( self::$maillogData ) && empty( self::$recoveredData ) && ! empty( self::$abandonData ) ) {
				self::$minData = min( $maillog_min[ 0 ], $abandon_min[ 0 ] ) ;
				self::$maxData = max( $maillog_max[ 0 ], $abandon_max[ 0 ] ) ;
			} elseif ( empty( self::$maillogData ) && ! empty( self::$recoveredData ) && ! empty( self::$abandonData ) ) {
				self::$minData = min( $abandon_min[ 0 ], $recovered_min[ 0 ] ) ;
				self::$maxData = max( $abandon_max[ 0 ], $recovered_max[ 0 ] ) ;
			} else {
				self::$minData = min( $abandon_min[ 0 ], $recovered_min[ 0 ], $maillog_min[ 0 ] ) ;
				self::$maxData = max( $abandon_max[ 0 ], $recovered_max[ 0 ], $maillog_max[ 0 ] ) ;
			}
		}

		private static function rac_add_start_end_data() {
			if ( ! empty( self::$minData ) ) {
				$mindata = gmdate( 'Y-m-d', self::$minData / 1000 ) ;
				$maxdata = gmdate( 'Y-m-d', self::$maxData / 1000 ) ;
				if ( 'day' == self::$tickSize ) {
					$before_date = strtotime( $mindata . '-1 day' ) * 1000 ;
					$after_date  = strtotime( $maxdata . '+1 day' ) * 1000 ;
				} else {
					$before_date = strtotime( $mindata . '-1 month' ) * 1000 ;
					$after_date  = strtotime( $maxdata . '+1 month' ) * 1000 ;
				}

				$first_value = array( $before_date, 0 ) ;
				$last_value  = array( $after_date, 0 ) ;

				if ( is_array( self::$maillogData ) && ! empty( self::$maillogData ) ) {
					array_unshift( self::$maillogData, $first_value ) ;
					array_push( self::$maillogData, $last_value ) ;
				}
				if ( is_array( self::$abandonData ) && ! empty( self::$abandonData ) ) {
					array_unshift( self::$abandonData, $first_value ) ;
					array_push( self::$abandonData, $last_value ) ;
				}
				if ( is_array( self::$recoveredData ) && ! empty( self::$recoveredData ) ) {
					array_unshift( self::$recoveredData, $first_value ) ;
					array_push( self::$recoveredData, $last_value ) ;
				}
			}
		}

		private static function rac_get_graph_data( $post_type, $post_status, $meta_key ) {
			global $wpdb ;
			$json_format = array() ;
			$between     = self::rac_get_between() ;
			if ( empty( $between ) ) {
				$data = $wpdb->get_results( $wpdb->prepare( "SELECT 
                         UNIX_TIMESTAMP(from_unixtime(pm.meta_value,%s)) as Date_Time ,
                         count(pm.meta_value) as count 
                         FROM {$wpdb->postmeta} as pm INNER JOIN {$wpdb->posts} as p 
                         ON p.ID=pm.post_id 
                         WHERE p.post_status= %s 
                         AND p.post_type= %s AND pm.meta_key= %s 
                         GROUP BY UNIX_TIMESTAMP(from_unixtime(pm.meta_value,%s)) 
                         ORDER BY pm.meta_value ASC ", '%Y-%m-%d', $post_status, $post_type, $meta_key, '%Y-%m-%d' ), ARRAY_A ) ;
			} else {
				$from_date = $between[ 0 ] ;
				$to_date   = $between[ 1 ] ;
				$data      = $wpdb->get_results( $wpdb->prepare( "SELECT 
                        UNIX_TIMESTAMP(from_unixtime(pm.meta_value,%s)) as Date_Time ,
                        count(pm.meta_value) as count 
                        FROM {$wpdb->postmeta} as pm INNER JOIN {$wpdb->posts} as p 
                        ON p.ID=pm.post_id 
                        WHERE p.post_status= %s
                        AND p.post_type= %s AND pm.meta_key= %s 
                        AND pm.meta_value >= %s AND pm.meta_value <= %s
                        GROUP BY UNIX_TIMESTAMP(from_unixtime(pm.meta_value,%s)) 
                        ORDER BY pm.meta_value ASC ", '%Y-%m-%d', $post_status, $post_type, $meta_key, $from_date, $to_date, '%Y-%m-%d' ), ARRAY_A ) ;
			}
			if ( ! empty( $data ) ) {
				foreach ( $data as $newkey => $newvalue ) {
					$json_format[] = array( ( $newvalue[ 'Date_Time' ] ) * 1000, $newvalue[ 'count' ] ) ;
				}
			}
			return $json_format ;
		}

		private static function rac_get_between() {
			if ( isset( $_REQUEST[ 'rac_reports_period_selection' ] ) ) {
				$period_selection = wc_clean( wp_unslash( $_REQUEST[ 'rac_reports_period_selection' ] ) ) ;

				switch ( $period_selection ) {
					case 'alltime':
						$between        = array() ;
						self::$tickSize = 'month' ;

						break ;

					case 'last7days':
						$start_date     = strtotime( gmdate( 'Y-m-d', strtotime( 'midnight -6 days', current_time( 'timestamp' ) ) ) ) ;
						$end_date       = strtotime( gmdate( 'Y-m-d', strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ) ) ) ;
						$between        = array( $start_date, $end_date ) ;
						self::$tickSize = 'day' ;

						break ;

					case 'thismonth':
						$start_date     = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ;
						$end_date       = strtotime( gmdate( 'Y-m-d', strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ) ) ) ;
						$between        = array( $start_date, $end_date ) ;
						self::$tickSize = 'day' ;

						break ;

					case 'lastmonth':
						$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ;
						$start_date              = strtotime( gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ) ) ;
						$end_date                = strtotime( 'midnight', $first_day_current_month ) - 1 ;
						$between                 = array( $start_date, $end_date ) ;
						self::$tickSize          = 'day' ;
						break ;

					case '3months':
						$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ;
						$start_date              = strtotime( gmdate( 'Y-m-01', strtotime( '-2 months', $first_day_current_month ) ) ) ;
						$end_date                = strtotime( gmdate( 'Y-m-d', strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ) ) ) ;
						$between                 = array( $start_date, $end_date ) ;
						self::$tickSize          = 'month' ;
						break ;
					case '6months':
						$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ;
						$start_date              = strtotime( gmdate( 'Y-m-01', strtotime( '-5 months', $first_day_current_month ) ) ) ;
						$end_date                = strtotime( gmdate( 'Y-m-d', strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ) ) ) ;
						$between                 = array( $start_date, $end_date ) ;
						self::$tickSize          = 'month' ;
						break ;
					case 'month':
						$start_date              = strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) ) ;
						$end_date                = strtotime( gmdate( 'Y-m-d', strtotime( 'tomorrow midnight', current_time( 'timestamp' ) ) ) ) ;
						$between                 = array( $start_date, $end_date ) ;
						self::$tickSize          = 'month' ;
						break ;

					default:
						$first_day_current_year = strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) ) ;
						$start_date             = strtotime( gmdate( 'Y-01-01', strtotime( '-1 year', $first_day_current_year ) ) ) ;
						$end_date               = strtotime( 'midnight', $first_day_current_year ) ;
						$between                = array( $start_date, $end_date ) ;
						self::$tickSize         = 'month' ;
						break ;
				}
			} else {
				$between        = array() ;
				self::$tickSize = 'month' ;
			}
			return $between ;
		}

	}

	FP_RAC_Reports_Tab::init() ;
}
