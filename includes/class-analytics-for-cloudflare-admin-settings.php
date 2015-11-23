<?php

/**
 * Define the admin settings.
 *
 * Create an admin settings page on the dashboard.
 *
 * @since      1.0.0
 * @package    CMD_Analytics_For_Cloudflare_Admin_Settings
 * @subpackage CMD_Analytics_For_Cloudflare_Admin_Settings/includes
 * @author     ChuckMac Development<chuck@chuckmacdev.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class CMD_Analytics_For_Cloudflare_Admin_Settings {

	/** name for the settings group */
	private $settings_group;

	/** name for the settings options */
	private $settings_options;

	/** value of the plugin options field */
	private $plugin_options;

	/** CloudFlare connection status */
	private $is_connected = false;

	/** Error messages */
	private $error_message;

	/** Domains assocaited with account */
	private $domains;

	/**
	 * Set the dynamic CloudFlare API information needed to build a reuquest.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->settings_group   = CMD_Analytics_For_Cloudflare::TEXT_DOMAIN . "-settings";
		$this->settings_options = CMD_Analytics_For_Cloudflare::PLUGIN_ID . "_settings";
		$this->plugin_options   = get_option( $this->settings_options );

		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( CMD_Analytics_For_Cloudflare::$BASEFILE ), array( &$this, 'plugin_settings_link' ), 10, 4 );

	}

	/**
	 * Add the admin munder under Settings -> Analytics For Cloudflare.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {

		add_options_page( 
			__('Analytics For CloudFlare', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN), 
			__('Analytics For CloudFlare', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN), 
			'manage_options', 
			CMD_Analytics_For_Cloudflare::PLUGIN_ID, 
			array( $this, 'display_options_page' ) 
		);
	
	}
	
	/**
	 * Initialize the settings fields for the plugin.
	 *
	 * api_key
	 * api_email
	 * domain
	 * cache time
	 *
	 * @since    1.0.0
	 */
	public function settings_init() { 
	
		global $pagenow;
		if ( ( 'options-general.php' == $pagenow ) && ( $_GET['page'] == CMD_Analytics_For_Cloudflare::PLUGIN_ID )) {
			$this->setup_options_page();
		}

		register_setting( 
			CMD_Analytics_For_Cloudflare::PLUGIN_ID, 
			$this->settings_options,
			array( $this, 'sanitize_options' )
		);

		add_settings_section(
			$this->settings_group, 
			null, 
			array( $this, 'settings_section_callback' ), 
			CMD_Analytics_For_Cloudflare::TEXT_DOMAIN
		);
	
		add_settings_field( 
			$this->settings_group . '_api_key', 
			__( 'CloudFlare API Key', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ), 
			array($this, 'cmd_analytics_for_cloudflare_api_key_render'), 
			CMD_Analytics_For_Cloudflare::TEXT_DOMAIN,
			$this->settings_group
		);

		add_settings_field( 
			$this->settings_group . '_api_email', 
			__( 'CloudFlare Email Address', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ), 
			array($this, 'cmd_analytics_for_cloudflare_api_email_render'), 
			CMD_Analytics_For_Cloudflare::TEXT_DOMAIN,
			$this->settings_group
		);
	
		if ( true === $this->is_connected ) {
			add_settings_field( 
				$this->settings_group . '_domain', 
				__( 'CloudFlare Domain', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ), 
				array($this, 'cmd_analytics_for_cloudflare_domain_render'), 
				CMD_Analytics_For_Cloudflare::TEXT_DOMAIN,
				$this->settings_group
			);

			add_settings_field( 
				$this->settings_group . '_cache_time', 
				__( 'Cache Results For', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ), 
				array($this, 'cmd_analytics_for_cloudflare_cache_time_render'), 
				CMD_Analytics_For_Cloudflare::TEXT_DOMAIN,
				$this->settings_group
			);
		}

	}

	/**
	 * Render the API key field for the settings page
	 *
	 * @since    1.0.0
	 */
	public function cmd_analytics_for_cloudflare_api_key_render() {

		$value = ( isset( $this->plugin_options['api_key'] ) ? $this->plugin_options['api_key'] : '' );

		?>
		<input type='text' size="50" maxlength="48" name='<?php echo $this->settings_options; ?>[api_key]' value='<?php echo $value; ?>'>
		(<a href="https://www.cloudflare.com/my-websites"><?php _e( 'Get This?', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></a>)
		<?php
	
	}
	
	/**
	 * Render the Email key field for the settings page
	 *
	 * @since    1.0.0
	 */	
	public function cmd_analytics_for_cloudflare_api_email_render() {
	
		$value = ( isset( $this->plugin_options['api_email'] ) ? $this->plugin_options['api_email'] : '' );

		?>
		<input type='text' size="50" maxlength="100" name='<?php echo $this->settings_options; ?>[api_email]' value='<?php echo $value; ?>'>
		(<a href="https://www.cloudflare.com/my-account.html"><?php _e( 'Get This?', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ); ?></a>)
		<?php

	}
	
	/**
	 * Render the Domain field for the settings page
	 *
	 * @since    1.0.0
	 */		
	public function cmd_analytics_for_cloudflare_domain_render() {
	
		$value = ( isset( $this->plugin_options['domain'] ) ? $this->plugin_options['domain'] : '' );

		?>
		<select name='<?php echo $this->settings_options; ?>[domain]'>
		<?php
		foreach ($this->domains as $key => $domain) {
			?><option value='<?php echo $key; ?>' <?php selected( $value, $key ); ?>><?php echo $domain; ?></option><?php
		}
		?>
		</select>
		<?php
	
	}

	/**
	 * Render the Cache Time field for the settings page
	 *
	 * @since    1.0.0
	 */	
	public function cmd_analytics_for_cloudflare_cache_time_render() {
	
		$value = ( isset( $this->plugin_options['cache_time'] ) ? $this->plugin_options['cache_time'] : '900' );

		$available_options = apply_filters( 
								'cmd_analytics_for_cloudflare_admin_settings_cache_options',
								array (
										'0' => __( 'Do Not Cache', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'300' => __( '5 Minutes', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'900' => __( '15 Minutes', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'3600' => __( '1 Hour', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'10400' => __( '4 Hours', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
										'55200' => __( '12 Hours', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ),
									  )
								);

		?>
		<select name='<?php echo $this->settings_options; ?>[cache_time]'>
		<?php
		foreach ($available_options as $key => $time) {
			?><option value='<?php echo $key; ?>' <?php selected( $value, $key ); ?>><?php echo $time; ?></option><?php
		}
		?>
		</select>
		<?php
	
	}

	/**
	 * Display an error notice
	 *
	 * @since    1.0.0
	 */	
	function admin_error_notice( ) {
		echo '<div class="error"><p>' . $this->error_message . '</p></div>'; 
	}

	/**
	 * Display a success notice
	 *
	 * @since    1.0.0
	 */	
	function admin_success_notice( ) {
		echo '<div class="updated"><p>' . $this->error_message . '</p></div>'; 
	}	
	

	/**
	 * Display the settings page description
	 *
	 * @since    1.0.0
	 */	
	public function settings_section_callback() {


		echo '<p>' . __( sprintf("By %sChuckMac Development%s", '<a href="https://chuckmacdev.com" target="_BLANK">', '</a>'), CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ) . '</p>';
		echo '<p>' . __( 'Please enter your CloudFlare API credentials below.  Once connected you will be able to select the domain for which the site should be linked to.', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ) . '</p>';

		do_action('cmd_analytics_for_cloudflare_admin_settings_after_desc');
		
	}
	

	/**
	 * Check the settings against the CloudFlare API.  If we can connect then display a success message, otherwise display an error.
	 *
	 * @since    1.0.0
	 */	
	public function setup_options_page() {

		//Check settings against Cloudflare
		require_once( 'class-analytics-for-cloudflare-api.php' );
		$cloudflare = new CMD_Analytics_For_Cloudflare_Api();
		$this->domains = $cloudflare->get_domains();

		if ( is_wp_error($this->domains) ) {
			$this->error_message =  __('Unable to connect to CloudFlare :: ', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN) . $this->domains->get_error_message();
			add_action( 'cmd_analytics_for_cloudflare_admin_settings_after_desc', array( $this, 'admin_error_notice' ) );
		} else {
			$this->is_connected = true;
			$this->error_message = __('Successfully connected to CloudFlare! ', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN);
			add_action( 'cmd_analytics_for_cloudflare_admin_settings_after_desc', array( $this, 'admin_success_notice' ) );
		}
		
	}

	/**
	 * The main framework for the settings option page.  Load all the settings fields.
	 *
	 * @since    1.0.0
	 */		
	public function display_options_page() {
	
		?>
		<form action='options.php' method='post'>
			
			<h2><?php _e('Analytics For Cloudflare', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN); ?></h2>
			
			<?php
			settings_fields( CMD_Analytics_For_Cloudflare::PLUGIN_ID );
			do_settings_sections( CMD_Analytics_For_Cloudflare::TEXT_DOMAIN );
			submit_button();
			?>
			
		</form>
		<?php
	
	}

	/**
	 * Sanatize the user input data from the settings form.
	 *
	 * @since     1.0.0
	 * @return    array    $settings     Sanitized form data.
	 */
	public function sanitize_options( $input ) {

		$settings = array();
		$settings['api_key']   = ( isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : null);
		$settings['api_email'] = ( isset( $input['api_email'] ) ? sanitize_email( $input['api_email'] ) : null);
		$settings['domain']    = ( isset( $input['domain'] ) ? sanitize_text_field( $input['domain'] ) : null);
		$settings['cache_time']    = ( isset( $input['cache_time'] ) ? sanitize_text_field( $input['cache_time'] ) : null);

		return $settings;

	}

	/**
	 * Add action links to the plugin page
	 *
	 * @since     1.0.1
	 * @param     array    $actions      associative array of action names to anchor tags
	 * @param     string   $plugin_file  plugin file name, ie my-plugin/my-plugin.php
	 * @param     array    $plugin_data  associative array of plugin data from the plugin file headers
	 * @param     string   $context      plugin status context, ie 'all', 'active', 'inactive', 'recently_active'
	 * 
	 * @return    array    $settings     Sanitized form data.
	 */
	public function plugin_settings_link( $actions, $plugin_file, $plugin_data, $context ) {

		return array_merge( array( 'settings' => 
								'<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=' . CMD_Analytics_For_Cloudflare::PLUGIN_ID ) ) . '">' .
								__( 'Settings', CMD_Analytics_For_Cloudflare::TEXT_DOMAIN ) . 
								'</a>' ),
                            $actions );

	}


}
