<?php
namespace WP_Arvan\CDN\Cache;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\CDN\Domain_Info;
use WP_Arvan\Helper;

class Page_Rules {

    static $endpoint = '/page-rules';

	static $RULES = [];

	const recommended_rules = [
		'/wp-login/*',
		'/wp-admin/*'
	];

	public static function add_rule( $path, $cache_level = 'off', $rule_data = [] ) {

		// maybe WP installed on a subdirectory or subdomain
		if (defined( 'ARVANCLOUD_CDN_DOMAIN' )) {
            $url = Helper::get_site_domain(false) . $path;
        } else {
			$url = preg_replace("(^https?://)", "", get_site_url() ) . $path;
		}

		$domain_info = Domain_Info::get_domain_info();
		if ( ! $domain_info || !isset( $domain_info['id'] ) ) {
			return false;
		}

		$domain_id = $domain_info['id'];


		$data = [
			'domain_id' => $domain_id,
			'url' => $url,
			'cache_level' => $cache_level,
		];

		if ( ! empty( $rule_data ) ) {
			$data = array_merge( $data, $rule_data );
		}

		if (isset($notice) && $notice) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
					<p>'. esc_html__( 'You are not allowed to use cache TTLs less than 60 Minutes in Basic plan. Please upgrade your CDN plan to Enterprise.', 'arvancloud-cdn' ) .'</p>
				</div>');
			} );
		}

		$endpoint = 'domains/' . Helper::get_site_domain() . self::$endpoint;

		$response = Request_Arvan::post($endpoint, json_encode($data));
		$http_code = $response->status_code;

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
			Api_Key::reset_api_key();
		} else if ($http_code == 200) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-success is-dismissible">
						<p>'. esc_html__( "Arvancloud CDN service settings for WordPress added.", 'arvancloud-cdn' ) .'</p>
					</div>');
			} );
		}
		return;
	}

	public static function remove_rule( $path, $rule_id = false ) {
		$rules = self::get_rules();

		// maybe WP installed on a subdirectory or subdomain
		if (defined( 'ARVANCLOUD_CDN_DOMAIN' )) {
			$url = Helper::get_site_domain(false) . $path;
		} else {
			$url = preg_replace("(^https?://)", "", get_site_url() ) . $path;
		}

		if ( gettype( $rules ) != 'array' ) {
			return false;
		}

		foreach ( $rules as $rule ) {

			if (gettype($rule) != 'array') {
				continue;
			}

			if ( $rule['url'] == $url ) {
				$rule_id = $rule['id'];
				break;
			}
		}
		
		if ( ! $rule_id ) {
			return false;
		}
		
		$endpoint = self::$endpoint . '/' . $rule_id;
		$response = Request_Arvan::delete($endpoint);
		$http_code = $response->status_code;

		return $http_code;
	}

	public static function update_status_rule( $path, $status = true, $rule_id = false ) {
		$rules = self::get_rules();

		// maybe WP installed on a subdirectory or subdomain
		if (defined( 'ARVANCLOUD_CDN_DOMAIN' )) {
			$url = Helper::get_site_domain(false) . $path;
		} else {
			$url = preg_replace("(^https?://)", "", get_site_url() ) . $path;
		}

		foreach ( $rules as $rule ) {

			if (gettype($rule) != 'array') {
				continue;
			}

			if ( $rule['url'] == $url ) {
				$rule_id = $rule['id'];
				break;
			}
		}

		if ( ! $rule_id ) {
			return false;
		}

		$endpoint = self::$endpoint . '/' . $rule_id;
		$response = Request_Arvan::patch($endpoint, json_encode(
			['status' => $status]
		));
		$http_code = $response->status_code;

		return $http_code;
	}
		


    public static function is_rule_exist($path) : bool {

		if (defined( 'ARVANCLOUD_CDN_DOMAIN' )) {
            $url = Helper::get_site_domain(false) . $path;
        } else {
			$url = preg_replace("(^https?://)", "", get_site_url() ) . $path;
		}

		$rules = self::get_rules();

		if ( gettype( $rules ) != 'array' ) {
			return false;
		}

		foreach ( $rules as $rule ) {
			if (gettype($rule) != 'array') {
				continue;
			}

			if ($rule['url'] ==  $url) {
				return true;
			}
		}

		return false;
    }

	public static function setup_recommended_rules() {
		foreach(self::recommended_rules as $recommended_rule) {
			if (!self::is_rule_exist($recommended_rule)) {
				self::add_rule($recommended_rule, 'off');
			} else {
				self::update_status_rule($recommended_rule);
			}
		}
	}

	public static function remove_recommended_rules() {
		foreach( self::recommended_rules as $r ) {
			$http_code = self::remove_rule($r);
		}

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

	public static function update_recommended_rules( $status) {
		foreach( self::recommended_rules as $r ) {
			$http_code = self::update_status_rule($r, $status);
		}

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

	public static function is_recommended_rule_enabled() : bool {
		foreach(self::recommended_rules as $recommended_rule) {
			if (!self::is_rule_exist($recommended_rule)) {
				return false;
			}
		}
		return true;
	}

    /**
	 * Retrieve CDN Acceleration Settings
	 *
	 * @return void
	 */
	public static function get_rules() {
		$domain = Helper::get_site_domain();

		if ( empty(self::$RULES) ) {
			$request = Request_Arvan::get("domains/$domain" . self::$endpoint);
			self::$RULES = $request;
		}

        return self::$RULES;
	}
}