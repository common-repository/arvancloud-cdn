<?php

namespace WP_Arvan;

// If this file is called directly, abort.
use WP_Arvan\API\HTTP\Request_Arvan;

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Helper {


    private function __construct()
	{

	}

    /**
     * Get site domain
     *
     * @return string
     */
    public static function get_site_domain( $subdomain_check = true ) {

        $pieces = parse_url(home_url());

        if (defined( 'ARVANCLOUD_CDN_DOMAIN' )) {
            $pieces = parse_url(ARVANCLOUD_CDN_DOMAIN);
        }

        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if ( $subdomain_check && preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs) ) {
            return $regs['domain'];
        }

        return $domain;
    }


    public static function bytes_to_kb($bytes){
        return $bytes / 1024;
    }

    public static function size_convertor( $size=0 ) {
        if($size < 1024) {
            return "{$size} " . esc_html__( 'Bytes', 'arvancloud-cdn' );
        } else if ( $size < 1048576 ) {
            $size_kb = round($size/1024);
            return "{$size_kb} " . esc_html__( 'Kilobyte', 'arvancloud-cdn' );
        } else if ( $size < 1073741824 ) {
            $size_mb = round($size/1048576, 1);
            return "{$size_mb} " . esc_html__( 'Megabyte', 'arvancloud-cdn' );
        } else {
            $size_gb = round($size/1048576/1024, 1);
            return "{$size_gb} " . esc_html__( 'Gigabyte', 'arvancloud-cdn' );
        }
    }

    public static function digits_enToFa($string) {
        return strtr($string, array('0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹'));
    }


    public static function is_site_persian() {
        return get_user_locale() == 'fa_IR';
    }

    public static function print_logs( $array ) {
        echo '<pre>';
        var_export( $array );
        echo '</pre>';
    }

    public static function all_countries() {
        $country_endpoint = 'https://napi.arvancloud.ir/cdn/countries?sort_by_lang=' . (self::is_site_persian() ? 'fa' : 'en');

        $countries = wp_remote_get( $country_endpoint );
        
        if (is_wp_error($countries) || !isset($countries['body']) || wp_remote_retrieve_response_code($countries) !== 200) {
            return false;
        }

        $countries = json_decode($countries['body'], true);
        $countries_data = [];
        foreach($countries as $country) {
            $countries_data[$country['alpha2Code']] = self::is_site_persian() ? $country['translations']['fa'] : $country['name'];
        }

        return $countries_data;
    }


    public static function is_domain_belongs_to_api(){

        $domain = Helper::get_site_domain(false);

        $response = json_decode(wp_remote_retrieve_body(Request_Arvan::get('domains/', false)));

        if( !isset($response->data) )
            return;

        if( !is_array($response->data))
            return;

        $domains = [];

        foreach ($response->data as $data)
        {
            $domains[] = $data->domain;
        }

        if( in_array( $domain, $domains ) )
            return true;
        return false;
    }

    public static function show_admin_notice($message){

        if(!defined('DOMAIN_NOT_BELONG_TO_API_ADMIN_NOTICE')) {

            add_action('admin_notices', function () use ($message) {
                echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>' . __($message, 'arvancloud-cdn') . '</p>
					</div>');
            });

            define('DOMAIN_NOT_BELONG_TO_API_ADMIN_NOTICE', true);
        }

    }

}
