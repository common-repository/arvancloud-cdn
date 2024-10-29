<?php
namespace WP_Arvan\CDN;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\CDN\Cache\Page_Rules;
use WP_Arvan\Helper;

class CDN_Options {
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
		if (! isset($_GET['option_item']['name']) || !self::is_caching_option_allowed( sanitize_text_field($_GET['option_item']['name'])) ) {

			wp_send_json_error( __('Invalid item sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}

		if ( sanitize_text_field($_GET['option_item']['name']) == 'setup_recommended_rules' ) {
			if ( $_GET['option_item']['status'] == 'true' ) {
				Page_Rules::setup_recommended_rules();
			} else {
				Page_Rules::update_recommended_rules(false);
			}

			wp_send_json_success();
			wp_die();
		}

		// request is correct
		$item = array_map( 'sanitize_text_field', $_GET['option_item'] );

		if ( isset($item[ 'name' ] ) && $item[ 'name' ] == 'automatic_cleaning' ) {
			update_option('arvan-cloud-cdn-automatic-cleaning', ($item['status'] == 'true') ? 1 : 0);
			wp_send_json_success();
			wp_die();
		}


		// should send request to arvancloud
		
		$endpoint = '/caching';
		$data = array($item[ 'name' ] => ($item['status'] == 'true') ? 1 : 0);
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


	private static function is_caching_option_allowed( $option ) {
		$allowed_options = [
			'automatic_cleaning',
			'cache_consistent_uptime',
			'cache_developer_mode',
			'cache_status',
			'cache_page_200',
			'cache_page_any',
			'cache_browser',
			'cache_scheme',
			'cache_ignore_sc',
			'cache_cookie',
			'cache_args',
			'cache_arg',
			'setup_recommended_rules'
		];

		return in_array($option, $allowed_options);
	}

    /**
	 * Retrieve CDN Settings
	 *
	 * @return void
	 */
	public static function get_cdn_options() {
		$domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/caching");
	}
}