<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    CMD_Analytics_For_Cloudflare_Api
 * @subpackage CMD_Analytics_For_Cloudflare_Api/includes
 * @author     ChuckMac Development <chuck@chuckmacdev.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class CMD_Analytics_For_Cloudflare_Api {

	/** CloudFlare API key */
	private $api_key;

	/** CloudFlare API email address */
	private $api_email;

	/** CloudFlare API current domain */
	private $domain;

	/** CloudFlare API endpoint URL */
	private static $endpoint_url = 'https://api.cloudflare.com/client/v4/';


	/**
	 * Set the dynamic CloudFlare API information needed to build a reuquest.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$options = get_option( CMD_Analytics_For_Cloudflare::PLUGIN_ID . '_settings' );

		$this->api_key   = apply_filters( 'cmd_analytics_for_cloudflare_set_api_key', ( isset( $options['api_key'] ) ? $options['api_key'] : null ) );
		$this->api_email = apply_filters( 'cmd_analytics_for_cloudflare_set_api_email', ( isset( $options['api_email'] ) ? $options['api_email'] : null ) );
		$this->zone_id   = apply_filters( 'cmd_analytics_for_cloudflare_set_api_domain', ( isset( $options['domain'] ) ? $options['domain'] : null ) );

	}


	/**
	 * Fetch all the domains available from CloudFlare for the current API keys provided.
	 *
	 * @since     1.0.0
	 * @return    array    $domains     Returns the zone ID as the key, and the domain as the value.
	 */
	public function get_domains() {

		$domains = array();

		$response = $this->api_call( 'zones' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( is_array( $response ) ) {
			foreach ( $response as $zone ) {
				$domains[ $zone->id ] = $zone->name;
			}
		}

		do_action( 'cmd_analytics_for_cloudflare_api_get_domains', $domains, $response );

		return $domains;

	}


	/**
	 * Fetch analytics data from CloudFlare.
	 *
	 * Valid arguements documented by ClouldFlare:
	 *   https://api.cloudflare.com/#zone-analytics-properties
	 *
	 * @since     1.0.0
	 * @param     array    $args        Arguements to the API call, as provided by CloudFlare
	 * @return    object   $response    The analytics reponse values from CloudFlare
	 */
	public function get_analytics( $args = array() ) {

		if ( empty( $this->zone_id ) ) {
			return new WP_Error( 'http_request_failed', __( 'Domain not selected in settings.', 'cmd-analytics-for-cloudflare' ) );
		}

		$response = $this->api_call( 'zones/' . $this->zone_id . '/analytics/dashboard?' . http_build_query( $args ) );

		return $response;

	}


	/**
	 * Make a call to the CloudFlare API.
	 *
	 * @since    1.0.0
	 * @param    string    $endpoint    The CloudFlare endpoint.
	 * @param    string    $data        The data to be passed to the endpoint.
	 * @return   object    $response    The reponse values from CloudFlare, or a WP_Error object if failure.
	 */
	public function api_call( $endpoint, $data = null ) {

		if ( empty( $this->api_key ) || empty( $this->api_email ) ) {
			return new WP_Error( 'http_request_failed', __( 'API credentials not populated.', 'cmd-analytics-for-cloudflare' ) );
		}

		$request = array(
						'timeout' => 15,
						'headers' => array(
											'X-Auth-Key' => $this->api_key,
											'X-Auth-Email' => $this->api_email,
											'Content-Type' => 'application/json',
										),
						'body' => $data,
						);

		$response = wp_remote_get(
			self::$endpoint_url . $endpoint,
			$request
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		do_action( 'cmd_analytics_for_cloudflare_api_after_call', $response, $endpoint, $request );

		if ( ( ! is_array( $response ) ) || ( ! isset( $response['body'] ) ) ) {
			return new WP_Error( 'http_request_failed', __( 'Unable to parse response data.', 'cmd-analytics-for-cloudflare' ) );
		}

		$results = apply_filters( 'cmd_analytics_for_cloudflare_api_json_data', json_decode( $response['body'] ) );

		if ( ! isset( $results->success ) ) {
			return new WP_Error( 'json_request_failed', __( 'Success flag not found in json response.', 'cmd-analytics-for-cloudflare' ) );
		}

		if ( false === $results->success ) {
			$message = ( isset( $results->errors[0]->message ) ? $results->errors[0]->message : __( 'Success flag reported as false in jason response.', 'cmd-analytics-for-cloudflare' ) );
			$message = ( isset( $results->errors[0]->error_chain[0]->message ) ? $message . ' - ' . $results->errors[0]->error_chain[0]->message : $message );
			return new WP_Error( 'json_request_failed', $message, CMD_Analytics_For_Cloudflare::TEXT_DOMAIN );
		}

		return $results->result;
	}
}
