<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CMD_Analytics_For_Cloudflare
 * @subpackage CMD_Analytics_For_Cloudflare/includes
 * @author     ChuckMac Development <chuck@chuckmacdev.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class CMD_Analytics_For_Cloudflare {

	/** plugin version number */
	const VERSION = '1.0.0';

	/** plugin id */
	const PLUGIN_ID = 'cmd_analytics_for_cloudflare';

	/** plugin text domain */
	const TEXT_DOMAIN = 'cmd-analytics-for-cloudflare';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->set_locale();

		add_action( 'init', array( $this, 'init' ), 10 );

	}


	/**
	 * Initialize plugin - translations and classes
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( is_admin() ) {
			// Admin Options Page
			require_once( 'class-analytics-for-cloudflare-admin-settings.php' );
			$admin = new CMD_Analytics_For_Cloudflare_Admin_Settings();
	
			// Admin Dashboard Widget
			require_once( 'class-analytics-for-cloudflare-admin-dashboard.php' );
			$admin = new CMD_Analytics_For_Cloudflare_Admin_Dashboard();
		}

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		require_once( 'class-analytics-for-cloudflare-i18n.php' );
		$plugin_i18n = new CMD_Analytics_For_Cloudflare_i18n();
		$plugin_i18n->set_domain( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN );
		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Render a template
	 * 
	 * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
	 * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
	 * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
	 * fully customize the output.
	 * 
	 * @param  string $default_template_path The path to the template, relative to the plugin's `templates` folder
	 * @param  array  $variables             An array of variables to pass into the template's scope, indexed with the variable name so that it can be extract()-ed
	 * @param  string $require               'once' to use require_once() | 'always' to use require()
	 * @return string
	 */
	public static function render_template( $default_template_path, $variables = array(), $require = 'once' ) {
		$template_path = locate_template( basename( $default_template_path ) );
		if ( ! $template_path ) {
			$template_path = dirname( __DIR__ ) . '/templates/' . $default_template_path;
		}
		$template_path = apply_filters( CMD_Analytics_For_Cloudflare::PLUGIN_ID . '_template_path', $template_path );

	
		if ( is_file( $template_path ) ) {
			extract( $variables );
			ob_start();
			
			if ( 'always' == $require ) {
				require( $template_path );
			} else {
				require_once( $template_path );
			}
			
			$template_content = apply_filters(  CMD_Analytics_For_Cloudflare::PLUGIN_ID . '_template_content', ob_get_clean(), $default_template_path, $template_path, $variables );
		} else {
			$template_content = false;
		}
		
		return $template_content;
	}
}
