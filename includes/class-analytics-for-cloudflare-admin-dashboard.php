<?php

/**
 * Handle the creation of the admin dashboard widget.
 *
 *
 * @since      1.0.0
 * @package    CMD_Analytics_For_Cloudflare_Admin_Dashboard
 * @subpackage CMD_Analytics_For_Cloudflare_Admin_Dashboard/includes
 * @author     ChuckMac Development <chuck@chuckmacdev.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
class CMD_Analytics_For_Cloudflare_Admin_Dashboard {

	/** value of the plugin options field */
	private $plugin_options;

	/**
	 * Set the dynamic CloudFlare API information needed to build a reuquest.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_scripts_styles' ) );

	}

	/**
	 * Register the CloudFlare for Analytics dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function register_dashboard_widget() {

		wp_add_dashboard_widget(
			CMD_Analytics_For_Cloudflare::PLUGIN_ID . '_dashboard',
			__('Analytics For CloudFlare', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
			array( $this, 'display_dashboard_widget' )
		);

	}

	/**
	 * Enqueue the necessary external javascript and css files needed.
	 *
	 * Only on the admin dashboard screen (index.php)
	 *
	 * @since    1.0.0
	 */
	public function enqueue_dashboard_scripts_styles( $hook ) {

		if ( 'index.php' == $hook ) {
			wp_enqueue_style(  CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-css', plugins_url( 'assets/css/admin.css' , dirname(__FILE__) ) );
			wp_enqueue_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-moment-js-lib', plugins_url( 'lib/Moment.js-2.10.6/moment.js' , dirname(__FILE__) ), array( 'jquery' ) );
			wp_enqueue_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-charts-js-lib', plugins_url( 'lib/Chart.js-1.0.2/Chart.min.js' , dirname(__FILE__) ), array( 'jquery', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-moment-js-lib' ) );
		}

	}

	/**
	 * The main dashboard function.
	 *
	 * Query the CloudFlare API, output all the data to the dashboard widget.
	 *
	 * @since    1.0.0
	 */
	public function display_dashboard_widget() {

		$this->plugin_options = get_option( CMD_Analytics_For_Cloudflare::PLUGIN_ID . "_settings" );

		// Available dropdown display items
		$time_options = array(
							#'-30'  =>  __('Last 30 minutes', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),  ## ENTERPRISE PLAN
							#'-360' => __('Last 6 hours',C MD_Analytics_For_Cloudflare::TEXT_DOMAIN),      ## PRO PLAN
							#'-720' => __('Last 12 hours', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),     ## PRO PLAN
							'-1440'  => __('Last 24 hours', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
							'-10080' => __('Last week', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
							'-43200' => __('Last month', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
						);

		$display_options = array(
							'requests'  => __('Requests', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
							'pageviews' => __('Pageviews', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
							'uniques'    => __('Unique Visitors', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),
							'bandwidth' => __('Bandwidth', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN),

						);

		//Default - last week / requests
		$current_time = '-10080';
		$current_type = 'pageviews';


		//Check if the form was submitted to change the view
		if( ( isset( $_REQUEST['cmd_analytics_for_cloudflare_dashboard_range'] ) ) && ( array_key_exists( $_REQUEST['cmd_analytics_for_cloudflare_dashboard_range'], $time_options ) ) )  {
			$current_time = $_REQUEST['cmd_analytics_for_cloudflare_dashboard_range'];
		}
		if( ( isset( $_REQUEST['cmd_analytics_for_cloudflare_dashboard_type'] ) ) && ( array_key_exists( $_REQUEST['cmd_analytics_for_cloudflare_dashboard_type'], $display_options ) ) )  {
			$current_type = $_REQUEST['cmd_analytics_for_cloudflare_dashboard_type'];
		}

		//Set our caching options		
		$options = get_option( CMD_Analytics_For_Cloudflare::PLUGIN_ID . "_settings" );
		$cache_time = ( isset( $this->plugin_options['cache_time'] ) ? $this->plugin_options['cache_time'] : '900' );

		//Check our transiant for the analytics object for our current view, if not found then pull the data 
		if ( $cache_time == 0 || false === ( $analytics = get_transient( 'cmd_afc_results_' . $current_time . '_' . $current_type  ) ) ) {

			require_once( 'class-analytics-for-cloudflare-api.php' );
			$cloudflare = new CMD_Analytics_For_Cloudflare_Api();
			$analytics = $cloudflare->get_analytics( array( 'since' => $current_time ) );

			//If we encounter an error, show it and don't cache
			if ( is_wp_error( $analytics ) ) {
				echo '<h3 class=>' . __('Unable to connect to CloudFlare', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN) . '</h3>';
				echo '<p>' . $analytics->get_error_message(); '</p>';
				echo '<p>' . __( sprintf( 'View the %sCloudFlare For Analytics settings%s', '<a href="' . admin_url( 'options-general.php?page=cmd_analytics_for_cloudflare') . '">', '</a>' ), CMD_Analytics_For_Cloudflare::TEXT_DOMAIN) . '</p>';
				return;
			}

			//Set the transient cache for the current view
			set_transient( 'cmd_afc_results_' . $current_time . '_' . $current_type, $analytics, $cache_time );

		}

		// Initialize all the javascript data
		$this->parse_analytics_to_js( $analytics, $current_type, $current_time, $display_options );

		do_action( 'cmd_analytics_for_cloudflare_before_dashboard' );

		// Render the display from the dashboard template file
		echo CMD_Analytics_For_Cloudflare::render_template( 'admin/cmd-afc-dashboard-widget.php',
															array(
																'time_options'    => $time_options,
																'current_time'    => $current_time,
																'display_options' => $display_options,
																'current_type'    => $current_type,
																'analytics'       => $analytics
															) );

		
		do_action( 'cmd_analytics_for_cloudflare_after_dashboard' );
	}


	/**
	 * Convert the analytics return data to javascript objects.
	 *
	 * @since    1.0.0
	 */
	public function parse_analytics_to_js( $analytics, $current_type, $current_time, $display_options ) {

		$bandwidth = 	array(
							'0' => 	array(
										'value' =>  $analytics->totals->bandwidth->cached,
										'color' => '#F68B1F',
										'highlight'  => '#F4690C',
										'label' => __( 'Cached', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'display' => $this->formatBytes( $analytics->totals->bandwidth->cached )
									),
							'1' => 	array(
										'value' =>  $analytics->totals->bandwidth->uncached,
										'color' => '#A9A9A9',
										'highlight'  => '#8F9CA8',
										'label' => __( 'Uncached', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'display' => $this->formatBytes( $analytics->totals->bandwidth->uncached )

									)
						);

		$ssl = 			array(
							'0' => 	array(
										'value' =>  $analytics->totals->requests->ssl->encrypted,
										'color' => '#F68B1F',
										'highlight'  => '#F4690C',
										'label' => __( 'Encypted', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
									),
							'1' => 	array(
										'value' =>  $analytics->totals->requests->ssl->unencrypted,
										'color' => '#A9A9A9',
										'highlight'  => '#8F9CA8',
										'label' => __( 'Unencrypted', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
									)
						);

		$colors = apply_filters( 'cmd_analytics_for_cloudflare_chart_colors', array( '#F68B1F', '#4D4D4D', '#5DA5DA', '#60BD68', '#F17CB0', '#B2912F', '#B276B2', '#9BFFE4', '#DECF3F', '#F15854' ) );

		// Create the content type totals, sort by number of requests
		$totals = (array) $analytics->totals->requests->content_type;
		arsort( $totals );
		$i = 0;
		$content_types = array();
		foreach ( $totals as $type => $value ) {
			$content_types[] = 	array(
									'value' => $value,
									'label' => $type,
									'color' => $colors[$i]
								);
			$i++;
			if ( $i > 9 ) {
				$i = 0;
			}
		}

		// Create the country totals, sort by number of requests
		$totals = (array) $analytics->totals->requests->country;
		arsort( $totals );
		$i = 0;
		$countries = array();
		foreach ( $totals as $type => $value ) {
			$countries[] = 	array(
									'value' => $value,
									'label' => $type,
									'color' => $colors[$i]
								);
			$i++;
			if ( $i > 9 ) {
				$i = 0;
			}
		}

		// Configuration for the main line chart
		$interval_chart = array();
		if ( ( 'requests' == $current_type ) || ( 'bandwidth' == $current_type )  ) {
			$interval_chart['datasets'][0] = array (
											'label' => $display_options[$current_type],
											'color' => "rgba(76,255,0,0.2)",
											'fillColor' => "rgba(76,255,0,0.2)",
											'strokeColor' => "rgba(63,211,0,1)",
											'scaleFontSize' => '0',
											'pointColor' => "rgba(50,168,0,1)",
											'pointStrokeColor' => "#fff",
											'pointHighlightFill' => "#fff",
											'pointHighlightStroke' => "rgba(220,220,220,1)"
										);
			$interval_chart['datasets'][1] = array (
											'label' => __( 'Cached', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
											'fillColor' => "rgba(246,139,31,0.2)",
											'strokeColor' => "rgba(234,101,0,1)",
											'scaleFontSize' => '0',
											'pointColor' => "rgba(232,171,127,1)",
											'pointStrokeColor' => "#fff",
											'pointHighlightFill' => "#fff",
											'pointHighlightStroke' => "rgba(220,220,220,1)"
										);
			$interval_chart['datasets'][2] = array (
											'label' => __( 'Uncached', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
											'fillColor' => "rgba(129,129,129,0.2)",
											'strokeColor' => "rgba(143,156,168,1)",
											'scaleFontSize' => '0',
											'pointColor' => "rgba(118,128,137,1)",
											'pointStrokeColor' => "#fff",
											'pointHighlightFill' => "#fff",
											'pointHighlightStroke' => "rgba(220,220,220,1)"
										);
		} else {
			$interval_chart['datasets'][0] = array (
											'label' => __( 'Pageviews', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
											'fillColor' => "rgba(246,139,31,0.2)",
											'strokeColor' => "rgba(234,101,0,1)",
											'scaleFontSize' => '0',
											'pointColor' => "rgba(232,171,127,1)",
											'pointStrokeColor' => "#fff",
											'pointHighlightFill' => "#fff",
											'pointHighlightStroke' => "rgba(220,220,220,1)"
										);
		}


		foreach ( $analytics->timeseries as $interval ) {

			// Set the date format for the chart (hours or dates)			
			if ( '-1440' == $current_time ) {
				$interval_chart['labels'][] = apply_filters( 'cmd_analytics_for_cloudflare_interval_dateformat', date( 'ga', strtotime($interval->since) ), $interval->since, $current_time );
			} else {
				$interval_chart['labels'][] = apply_filters( 'cmd_analytics_for_cloudflare_interval_dateformat', date( 'm/d', strtotime($interval->since) ), $interval->since, $current_time );
			}

			if ( ( 'requests' == $current_type ) || ( 'bandwidth' == $current_type ) ) {
				$interval_chart['datasets'][0]['data'][] = $interval->$current_type->all;
				$interval_chart['datasets'][1]['data'][] = $interval->$current_type->cached;
				$interval_chart['datasets'][2]['data'][] = $interval->$current_type->uncached;
			}else {
				$interval_chart['datasets'][0]['data'][] = $interval->$current_type->all;
			}

		}


		// Register and localize all the script data
		wp_register_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', plugins_url( 'assets/js/admin.js' , dirname(__FILE__) ), array( 'jquery' ), CMD_Analytics_For_Cloudflare::VERSION, true );

		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_interval', $interval_chart );
		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_bandwidth', $bandwidth );
		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_ssl', $ssl );
		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_content_types', $content_types );
		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_countries', $countries );
		wp_localize_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js', 'cmd_afc_current_type', $current_type );

		wp_enqueue_script( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . '-js' );

	}


	/**
	 * Convert bytes to more consise values depending on the size.  
	 *
	 * Add a suffix to the end to denote the converted value
	 *   B  = bytes
	 *   KB = kilobytes
	 *   MB = megabytes
	 *   GB = gigabytes
	 *   TB = terrabytes
	 *
	 * @since     1.0.0
	 * @param     string   $bytes       A numeric value representing a byte count
	 * @param     string   $precision   Number of decimal places to return
	 * @return    string   $return      The formatted value of bytes
	 */
	public static function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
	
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 

		$bytes /= pow(1024, $pow);
	
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	}

}
