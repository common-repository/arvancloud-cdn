<?php
namespace WP_Arvan\API\Key;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\CDN\Domain_Info;
use WP_Arvan\CDN\Cache\Page_Rules;


class Api_Key {
    /**
	 * check and validate api key by sending request to ArvanCloud
	 *
	 * @param string $api_key
	 * @return boolean
    */
    public static function validate_api_key($api_key = null) {
		
		$response = Request_Arvan::get('domains/', false, $api_key);
		$http_code = wp_remote_retrieve_response_code($response);


		if ($http_code == 500) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( 'ArvanCloud is not responding right now. please try again later.', 'arvancloud-cdn' ) .'</p>
					</div>');
			} );
		} else if ($http_code == 401) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
					<p>'. esc_html__( 'ArvanCloud API key is invalid. Please try again.', 'arvancloud-cdn' ) .'</p>
				</div>');
			} );

			self::reset_api_key();
		}

		return $http_code == 200 ? true : false;
	}

    /**
	 * Sets the access control system and saves it to an option after encryption
	 *
	 * @since 0.0.1
	 * @return void
    */
	public static function set_accdn_api_key() {

		if( ! isset( $_POST[ 'config_arvancloud_api_key' ] ) ) {
			return;
		}

		$api_key = sanitize_key( $_POST[ 'accdn-api-key' ] );
		if ( $api_key == null || ( ! empty( $api_key ) && $api_key === __( "-- not shown --", 'arvancloud-cdn' )) ) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( "Enter your API key", 'arvancloud-cdn' ) .'</p>
					</div>');
			} ); 
			return false;
		}

		if ( !self::validate_api_key((new Encryption)->encrypt($api_key)) ) {
			return false;
		}

		$save_settings = update_option( 'arvan-cloud-cdn-api_key', (new Encryption)->encrypt($api_key) );

		if( $save_settings ) {
			update_option( 'arvan-cloud-cdn-status', 'connected');
			Domain_Info::check_dns_cdn_service_status();
			Page_Rules::setup_recommended_rules();
			
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-success is-dismissible">
						<p>'. esc_html__( "settings saved.", 'arvancloud-cdn' ) .'</p>
					</div>');
			} ); 
		}

	}

	/**
	 * Get ArvanCloud Api key 
	 *
	 * @return string
	 */
	private static function get_accdn_api_key() {
    	$api_key = get_option( 'arvan-cloud-cdn-api_key' );

		if( empty( $api_key ) ) { 
			return;
		}
		return (new Encryption)->decrypt( $api_key );
	}


	/**
	 * reset plugin by delete api key and status
	 *
	 * @return void
	 */
	public static function reset_api_key() {
		delete_option( 'arvan-cloud-cdn-status' );
		delete_option( 'arvan-cloud-cdn-api_key' );
	}
}