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
	<select id="<?php echo CMD_Analytics_For_Cloudflare::PLUGIN_ID;?>_dashboard_range" name="<?php echo CMD_Analytics_For_Cloudflare::PLUGIN_ID;?>_dashboard_range" onchange="this.form.submit()">
		<?php
		foreach ($time_options as $key => $value) {
			echo '<option value="' . $key . '"" ' . selected( $current_time, $key ) . '">' . $value . '</option>';
		}
		?>
	</select>
	<select id="<?php echo CMD_Analytics_For_Cloudflare::PLUGIN_ID;?>_dashboard_type" name="<?php echo CMD_Analytics_For_Cloudflare::PLUGIN_ID;?>_dashboard_type" onchange="this.form.submit()">
		<?php
		foreach ($display_options as $key => $value) {
			echo '<option value="' . $key . '"" ' . selected( $current_type, $key ) . '">' . $value . '</option>';
		}
		?>
	</select>
</form>

<div class="cmd-afc-wrapper">

	<h3 class="cmd-acf-heading"><?php echo $display_options[$current_type]; ?> / <?php echo $time_options[$current_time]; ?></h3>
	<canvas id="cmd-acf-linechart" class="line-chart" width="545" height="545"></canvas>
	<div id="cmd-acf-js-legend" class="chart-legend"></div>

	<div class="inside">
		<div class="small-box">
			<h3><?php _e('Total Requests', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset($analytics->totals->requests->all ) ? $analytics->totals->requests->all : '--' ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php _e('Total Pageviews', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset($analytics->totals->pageviews->all ) ? $analytics->totals->pageviews->all : '--' ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php _e('Total Unique Visitors', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset($analytics->totals->uniques->all ) ? $analytics->totals->uniques->all : '--' ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php _e('Threats Detected', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset($analytics->totals->threats->all ) ? $analytics->totals->threats->all : '--' ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php _e('Total Bandwidth', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset($analytics->totals->bandwidth->all ) ? CMD_Analytics_For_Cloudflare_Admin_Dashboard::formatBytes( $analytics->totals->bandwidth->all ) : '--' ); ?></p>
		</div>
		<div class="small-box">
			<h3><?php _e('Search Engine Crawls', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h3>
			<p><?php echo ( isset( $analytics->totals->pageviews->search_engine ) ? array_sum( (array)$analytics->totals->pageviews->search_engine ) : '--' ); ?></p>
		</div>
	</div>

	<div class="inside">
		<div class="donut-box">
			<h3><?php _e( 'Bandwidth', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></h3>
			<canvas id="cmd-acf-bwchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php _e( 'SSL Traffic', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></h3>
			<canvas id="cmd-acf-sslchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php _e( 'Content Types', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></h3>
			<canvas id="cmd-acf-ctchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
		<div class="donut-box">
			<h3><?php _e( 'Requests by Country', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></h3>
			<canvas id="cmd-acf-rcchart" class="donut-chart" width="400" height="400"></canvas>
		</div>
	</div>
</div>