<?php
namespace WP_Arvan\API\HTTP;
use WP_Arvan\API\Key\Encryption;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\Helper;



/**
 * Manage request to Arvancloud API
 */
class Request_Arvan {

    /**
     * ArvanCloud API Endpoint
     *
     * @var string
     */
    public static $arvan_cloud = 'https://napi.arvancloud.ir/cdn/4.0/';

    /**
     * @param string|null $api_key
     * @return array
     */
    private static function get_headers(?string $api_key): array
    {
        $api_key = (new Encryption)->decrypt($api_key == null ? get_option('arvan-cloud-cdn-api_key') : $api_key);
        $api_key = 'Apikey ' . (substr($api_key, 0, 6) == 'Apikey' || substr($api_key, 0, 6) == 'apikey' ? substr($api_key, 6) : $api_key);
        $headers = array(
            'Authorization' => $api_key,
            'Content-Type' => 'application/json',
        );
        return $headers;
    }

    /**
     * @param array $response
     * @return false|mixed
     */
    private static function validate_get_response($response)
    {
        if (is_wp_error($response) || !isset($response['body'])) {
            return false;
        }

        $res = json_decode($response['body'], true)['data'];
        $res['status_code'] = wp_remote_retrieve_response_code($response);

        if ($res['status_code'] === 401) {
            // reset api key
            Api_Key::reset_api_key();
        }

        return $res;
    }

    /**
     * Send Get request to ArvanCloud
     *
     * @param string $endpoint
     * @param bool $should_validate
     * @param string|null $api_key
     * @return false|mixed
     */
	public static function get(string $endpoint = 'domains/', $should_validate = true, string $api_key = null )
    {

		$args = ['headers' => $headers = self::get_headers($api_key)];
        $response = wp_remote_get( self::$arvan_cloud . $endpoint, $args );

		return $should_validate ? self::validate_get_response($response) : $response;

	}

    /**
     * Send Post request to ArvanCloud
     * @param string $endpoint
     * @param string $data
     * @param string|null $api_key
     * @return mixed
     */
    public static function post(string $endpoint = 'domains/', string $data, string $api_key = null)
    {

		$headers = self::get_headers($api_key);
        $response = \Requests::post( self::$arvan_cloud . $endpoint, $headers, $data );

		return $response;

	}

    /**
     * Send Patch request to ArvanCloud
     *
     * @param string $endpoint
     * @param string $new_cache_setting
     * @param string|null $api_key
     * @return response|Requests_Response
     */
    public static function patch(string $endpoint, string $new_cache_setting, string $api_key = null)
    {

		$headers = self::get_headers($api_key);
		$url = self::$arvan_cloud . 'domains/' . Helper::get_site_domain() . $endpoint;

		return \Requests::patch( $url,  $headers,  $new_cache_setting );

    }

    /**
     * Send Delete request to ArvanCloud
     *
     * @param string $endpoint
     * @param string|null $api_key
     * @return response|Requests_Response
     */
    public static function delete(string $endpoint, string $api_key = null)
    {

        $headers = self::get_headers($api_key);

        $url = self::$arvan_cloud . 'domains/' . Helper::get_site_domain() . $endpoint;
		return \Requests::delete( $url,  $headers);

    }
}