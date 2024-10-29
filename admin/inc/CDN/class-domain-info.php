<?php

namespace WP_Arvan\CDN;

use WP_Arvan\Helper;
use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;

class Domain_Info {

	public static $DNS_Cloud;

    /**
     * All domain info API response
     * @var $info
     */
	public static $info;

	public static $plan_level;

	public function __construct()
	{
		self::$info 		= self::get_domain_info();
		self::$DNS_Cloud 	= self::$info['dns_cloud']??null;
		self::$plan_level	= self::$info['plan_level'] ?? false;
	}

	/**
	 * Check DNS and CDN service status for current domain.
	 * and update arvan-cloud-cdn-status option
	 *
	 * @return bool
	 */
	public static function check_dns_cdn_service_status(): bool
    {
		$domain = Helper::get_site_domain();
		$dns_service_status = 'disabled';
		$cdn_service_status = false;

		$response = Request_Arvan::get('domains/'. Helper::get_site_domain(), false);

		if ( is_wp_error($response) || !isset($response['body'])) {
			Api_Key::validate_api_key();
			return false;
		} else {
			$response = json_decode($response['body'], true)['data'];
			if ($domain === $response['domain']) {
				$dns_service_status = $response['status'];
				$cdn_service_status = $response['dns_cloud'];
			}

			if ( $dns_service_status != 'active' ) {
				update_option( 'arvan-cloud-cdn-status', 'pending_dns');
			} else if ( !$cdn_service_status ) {
				update_option( 'arvan-cloud-cdn-status', 'pending_cdn');
			} else {
				update_option( 'arvan-cloud-cdn-status', 'activated');
			}
			return true;
		}
	}


	public static function get_domain_info() {
		$domain = Helper::get_site_domain();

        return Request_Arvan::get("domains/$domain");
    }

    public static function get_cdn_plan_level() {
		$domain_info = self::get_domain_info();

		return $domain_info['plan_level'] ?? false;
	}

    /**
	 * Test Plugin fully activated or not
	 *
	 * @return void
	 */
	public static function arvan_connection_test() {
		self::check_dns_cdn_service_status();
		return;
	}
	
}