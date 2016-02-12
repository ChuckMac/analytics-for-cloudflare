<?php

/**
 * The display output of the dashboard widget
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

?>

<form id="cmd-analytics-for-cloudflare-dash" method="POST">
	<select id="<?php echo esc_attr( CMD_Analytics_For_Cloudflare::PLUGIN_ID );?>_dashboard_range" name="<?php echo esc_attr( CMD_Analytics_For_Cloudflare::PLUGIN_ID );?>_dashboard_range" onchange="this.form.submit()">
		<?php
		foreach ( $time_options as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"" ' . selected( $current_time, $key ) . '">' . esc_html( $value ) . '</option>';
		}
		?>
	</select>
	<select id="<?php echo esc_attr( CMD_Analytics_For_Cloudflare::PLUGIN_ID );?>_dashboard_type" name="<?php echo esc_attr( CMD_Analytics_For_Cloudflare::PLUGIN_ID );?>_dashboard_type" onchange="this.form.submit()">
		<?php
		foreach ( $display_options as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '"" ' . selected( $current_type, $key ) . '">' . esc_html( $value ) . '</option>';
		}
		?>
	</select>
</form>

<div class="cmd-afc-wrapper">

	<h3 class="cmd-acf-heading"><?php echo esc_html( $display_options[ $current_type ] ); ?> / <?php echo esc_html( $time_options[ $current_time ] ); ?></h3>
	<canvas id="cmd-acf-linechart" class="line-chart" width="545" height="545"></canvas>
	<div id="cmd-acf-js-legend" class="chart-legend"></div>

	<div class="inside">
		<div class="small-box">
			<h3><?php esc_html_e( 'Total Requests', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->requests->all ) ? $analytics->totals->requests->all : '--' ) ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php esc_html_e( 'Total Pageviews', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->pageviews->all ) ? $analytics->totals->pageviews->all : '--' ) ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php esc_html_e( 'Total Unique Visitors', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->uniques->all ) ? $analytics->totals->uniques->all : '--' ) ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php esc_html_e( 'Threats Detected', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->threats->all ) ? $analytics->totals->threats->all : '--' ) ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php esc_html_e( 'Total Bandwidth', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->bandwidth->all ) ? CMD_Analytics_For_Cloudflare_Admin_Dashboard::format_bytes( $analytics->totals->bandwidth->all ) : '--' ) ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php esc_html_e( 'Search Engine Crawls', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<p><?php echo esc_html( ( isset( $analytics->totals->pageviews->search_engine ) ? array_sum( (array) $analytics->totals->pageviews->search_engine ) : '--' ) ); ?></p>
		</div>
	</div>

	<div class="inside">
		<div class="donut-box">
			<h3><?php esc_html_e( 'Bandwidth', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<canvas id="cmd-acf-bwchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php esc_html_e( 'SSL Traffic', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<canvas id="cmd-acf-sslchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php esc_html_e( 'Content Types', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<canvas id="cmd-acf-ctchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php esc_html_e( 'Requests by Country', 'cmd-analytics-for-cloudflare' ); ?></h3>
			<canvas id="cmd-acf-rcchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
	</div>
</div>
