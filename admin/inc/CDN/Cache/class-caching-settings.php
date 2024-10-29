<?php
namespace WP_Arvan\CDN\Cache;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\CDN\Domain_Info;
use WP_Arvan\Helper;

class Caching_Settings {
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
		
		$endpoint = '/caching';
		if ( $item['name'] === 'cache_status' ) {
			$data = array($item[ 'name' ] => $item['value']);
		} else if ( $item['name'] === 'edit_cache' ) {
			$data = array(
				'cache_args'	=> ($item['cache_args'] == 'true') ? 1 : 0,
				'cache_scheme'	=> ($item['cache_scheme'] == 'true') ? 1 : 0,
				'cache_arg'		=> $item['cache_arg'],
				'cache_cookie'	=> $item['cache_cookie'],
			);
		} else {
			wp_send_json_error( __( 'There is a problem', 'arvancloud-cdn' ), 500 );
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

	public static function save_expiration_time_options() {

		if (!(isset($_POST['expiration_times_data']) && isset($_POST['expiration_times_nonce']))) {
			return;
		}
		$data = [
			'cache_ignore_sc'  => (sanitize_text_field( $_POST['cache_ignore_sc'] ) == 'on') ? 1 : 0,
			'cache_browser'  => sanitize_text_field( $_POST['cache_browser'] ),
			'cache_page_any'  => sanitize_text_field( $_POST['cache_page_any'] ),
			'cache_page_200'  => sanitize_text_field( $_POST['cache_page_200'] ),
		];
		// checking for nonce
		if ( ! wp_verify_nonce( $_POST['expiration_times_nonce'], 'expiration_times') ) {
			return false;
		}

		// checking for allowed option based on plan level
		if ( Domain_Info::get_cdn_plan_level() == 1 ) {
			$notice = false;
			foreach($data as $key => $value) {
				if ( in_array( $key, ['cache_page_200'] ) && (substr($value, -1) == 's' || substr($value, -1) == 'm' ) && $value[0] != '0' ) {
					unset($data[$key]);
					$notice = true;
				}
			}
		}

		if (isset($notice) && $notice) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
					<p>'. esc_html__( 'You are not allowed to use cache TTLs less than 60 Minutes in Basic plan. Please upgrade your CDN plan to Enterprise.', 'arvancloud-cdn' ) .'</p>
				</div>');
			} );
		}

		$endpoint = '/caching';
		$response = Request_Arvan::patch($endpoint, json_encode($data));
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
						<p>'. esc_html__( "settings saved.", 'arvancloud-cdn' ) .'</p>
					</div>');
			} );
		}
		return;
	}


	private static function is_option_allowed( $option ) {
		$allowed_options = [
			'edit_cache',
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

        return Request_Arvan::get("domains/$domain/caching");
	}
}