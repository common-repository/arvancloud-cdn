<?php
namespace WP_Arvan\Security;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\Helper;

class HTTPS_Settings {

    public $is_HTTPS;
    public $is_HTTPS_Default;
    public $is_HTTPS_Rewrite;
    public $is_HSTS;
    public $hsts_max_age;
    public $is_HSTS_subdomain;
    public $is_HSTS_preload;

    public $data;

    public function __construct( $c = false )
    {

		if ($c) {
			$this->data = self::get_options();
			$this->set_props();
		}

    }

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
        $item = array_map( __CLASS__ . '::maybe_convert_string_to_bool', $item );
		// should send request to arvancloud


		

        $endpoint 	= '/ssl/';

		if ($item['name'] == 'edit_hsts') {
			$data = array(
				'hsts_status'		=> $item['hsts_status'],
				'hsts_max_age' 		=> $item['hsts_max_age'],
				'hsts_preload'		=> $item['hsts_preload'],
				'hsts_subdomain'	=> $item['hsts_subdomain']
			);
		} else if ( $item['name'] == 'ssl_status' && $item['status'] == false ) {
			$data = array(
				$item['name'] => $item['status'],
				'hsts_status'		=> false,
				'https_redirect' 	=> false,
				'replace_http'		=> false,
			);
		} else {
			$data = array(
				$item['name'] => $item['status'],
			);
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


	private static function is_option_allowed( $option ): bool
    {
		$allowed_options = [
			'ssl_status',
			'certificate_mode',
			'tls_version',
			'hsts_status',
			'hsts_max_age',
			'hsts_subdomain',
			'hsts_preload',
			'https_redirect',
			'replace_http',
            'edit_hsts',
		];

		return in_array($option, $allowed_options);
	}

    private static function is_value_allowed( $value ): bool
    {
		$allowed_values = [
			'',
		];

		return in_array($value, $allowed_values);
	}

    /**
	 * Retrieve HTTPS Settings
	 *
	 * @return void
	 */
	public static function get_options() {
		$domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain/ssl");
	}

    public function set_props() {
        if(!$this->data)
            return;
        $this->is_HTTPS         = $this->data['ssl_status'];
        $this->is_HTTPS_Default = $this->data['https_redirect'];
        $this->is_HTTPS_Rewrite = $this->data['replace_http'];
        $this->is_HSTS          = $this->data['hsts_status'];
        $this->is_HSTS_subdomain= $this->data['hsts_subdomain'];
        $this->is_HSTS_preload  = $this->data['hsts_preload'];
        $this->hsts_max_age     = $this->data['hsts_max_age'];
    }

    /**
     * @param $status
     * @return bool|mixed
     */
    private static function maybe_convert_string_to_bool($status)
    {
        if ($status == 'true') {
            $val = true;
        } else if ($status == 'false') {
            $val = false;
        } else {
            $val = $status;
        }
        return $val;
    }

}