<?php
namespace WP_Arvan\CDN;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\Helper;

class Acceleration {
	/**
	 * Save options with ajax req
	 *
	 * @return void
	 */
	public static function ajax_saving_options() {

		// checking for nonce
		if ( ! check_ajax_referer( 'ar-cdn-options-nonce', 'security', false ) ) {
	  
			wp_send_json_error( __('Invalid security token sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}

		//checking for allowed option name
		if (! isset($_GET['option_item']['name']) || !self::is_option_allowed( sanitize_text_field($_GET['option_item']['name'])) ) {

			wp_send_json_error( __('Invalid item sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}

		// request is correct
		$item = array_map( 'sanitize_text_field', $_GET['option_item'] );

		// should send request to arvancloud
		
		$endpoint 	= '/acceleration';
		$js_status 	= ($item['js'] == 'true');
		$css_status = ($item['css'] == 'true');
		$data = array(
			"status"=> "on",
			"extensions"=> []
		);
		if ($js_status) array_push( $data['extensions'], 'js' ); 
		if ($css_status) array_push( $data['extensions'], 'css' ); 

		$response = Request_Arvan::patch($endpoint, json_encode($data));
		$http_code = $response->status_code;

		if ($http_code == 500) {
			wp_send_json_error( __( 'ArvanCloud is not responding right now. please try again later.', 'arvancloud-cdn' ), 403 );
		} else if ($http_code == 401) {
			wp_send_json_error( __( 'ArvanCloud API key is invalid. Please try again.', 'arvancloud-cdn' ), 403 );
			Api_Key::reset_api_key();
		} else if ($http_code == 200) {
			wp_send_json_success();
		}
		wp_die();
	}


	private static function is_option_allowed( $option ) {
		$allowed_options = [
			'js_optimization',
			'css_optimization',
		];

		return in_array($option, $allowed_options);
	}

    /**
	 * Retrieve CDN Acceleration Settings
	 *
	 * @return void
	 */
	public static function get_options() {
		$domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/acceleration");
	}
}