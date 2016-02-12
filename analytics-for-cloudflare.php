<?php
/**
 * Plugin Name:       Analytics For Cloudflare
 * Plugin URI:        https://chuckmacdev.com
 * Description:       Access Cloudflare analytics information right from your WordPress dashboard.
 * Version:           1.0.2
 * Author:            ChuckMac Development
 * Author URI:        https://chuckmacdev.com
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cmd-analytics-for-cloudflare
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-analytics-for-cloudflare.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cmd_analytics_for_cloudflare() {

	$plugin = new CMD_Analytics_For_Cloudflare();
	$plugin->set_base_file( __FILE__ );

}
run_cmd_analytics_for_cloudflare();

?>
