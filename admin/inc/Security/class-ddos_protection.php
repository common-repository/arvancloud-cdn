<?php
namespace WP_Arvan\Security;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\Helper;
use WP_Arvan\CDN\Domain_Info;

class DDoS_Protection {


	public $ttl;

	/**
	 * Save options with ajax req
	 *
	 * @return void
	 */
	public static function ajax_saving_options() {

		$Domain_Info = new Domain_Info;

		// checking for nonce
		if ( ! check_ajax_referer( 'ar-cdn-options-nonce', 'security', false ) ) {
	  
			wp_send_json_error( __('Invalid security token sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}

		//checking for allowed option name
		if (! isset($_GET['option_item']['name']) || !self::is_option_allowed( sanitize_text_field($_GET['option_item']['name'])) || !self::is_value_allowed( sanitize_text_field($_GET['option_item']['value'])) ) {

			wp_send_json_error( __('Invalid item sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}

        
		// request is correct
		$item = array_map( 'sanitize_text_field', $_GET['option_item'] );
        
		// should send request to arvancloud
		
		$endpoint 	= '/ddos/settings';

		if ($item['name'] == 'ttl') {
			$item['value'] = (int)$item['value'];
		}

		if ( $item['name'] == 'ddos_protection_mode' ) {
			$data = array(
				"protection_mode" => $item['value'],
			);
		} else {
			$data = array(
				$item['name'] => $item['value'],
			);
		}

		if ($item['name'] == 'ttl' && $Domain_Info::$plan_level == 1 && $item['value'] != 100) {
			wp_send_json_error( __( 'You are not allowed to use custom ttl in Basic plan. Please upgrade your CDN plan to Growth.', 'arvancloud-cdn' ), 403 );
			wp_die();
		} else if ($item['value'] == 'recaptcha' && $Domain_Info::$plan_level != 4) {
            wp_send_json_error( __( 'You are not allowed to use 3 mode in Basic plan. Please upgrade your CDN plan to Professional.', 'arvancloud-cdn' ), 403 );
            wp_die();
        }

		
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
			'ddos_protection_mode',
			'ttl',
		];

		return in_array($option, $allowed_options);
	}

    private static function is_value_allowed( $value ) {
		$allowed_values = [
			'off',
			'cookie',
			'javascript',
			'recaptcha',
			'100',
			'0',
			'9000',
			'86400'
		];

		return in_array($value, $allowed_values);
	}

    /**
	 * Retrieve DDoS Protection
	 *
	 * @return void
	 */
	public static function get_options() {
		$domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/ddos/settings");
	}

    public function get_mode() {
        $options = self::get_options();

        $this->ttl = $options['ttl'] ?? false;

        return $options['protection_mode'] ?? false;
    }

    public function get_ddos_settings(){
        $domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/ddos/settings");
    }
    public function get_status(){
        $settings = $this->get_ddos_settings();

        return $settings['is_enabled'] ?? false;
    }
}