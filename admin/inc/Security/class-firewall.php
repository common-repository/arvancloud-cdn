<?php
namespace WP_Arvan\Security;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;
use WP_Arvan\Helper;

class Firewall {

    public static $endpoint = '/firewall/';

    public $default_rule;
    public $verify_sni;
    public $rules;

    public $data;

    public function __construct( $c = false )
    {
        if ($c) {
            $this->data = self::get_firewall_config();
            $this->set_props();
        }

        return $this;
    }

    public static function ajax_add_rules() {
        self::checking_nonce();
        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );

        $item['filter_expr'] = str_replace( '\\', '', $item['filter_expr'] );
        $item['is_enabled'] = $item['is_enabled'] ? 1 : 0;

        $data = array(
            'action'        => $item['action'],
            'name'          => $item['name'],
            'filter_expr'   => $item['filter_expr'],
            'is_enabled'    => $item['is_enabled'],
            'note'          => $item['note'] ?? '',
        );

        
        $endpoint = 'domains/' . Helper::get_site_domain() . self::$endpoint . 'rules/';

        $response = Request_Arvan::post( $endpoint, json_encode( $data));

        
        if (!is_wp_error($response)) {

            $yo = (array)$response;

            self::return_based_on_status_code($response->status_code, array(
                'massage' => __('Successfully created new rule.', 'arvancloud-cdn'),
                'data' => $yo['body'], false
            ));
        }

        wp_die();
    }

    public static function ajax_get_rule() {

        
        self::checking_nonce();
        
        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );
        $rule_id = $item['id'];
        
        $response = Request_Arvan::get( 'domains/' . Helper::get_site_domain() . self::$endpoint . 'rules/' . $rule_id . '/', true);

        self::return_based_on_status_code($response['status_code'], $response);

		wp_die();

    }

    public static function ajax_update_rules() {
        self::checking_nonce();

        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );
        $item['filter_expr'] = str_replace( '\\', '', $item['filter_expr'] );
        $item['is_enabled'] = $item['is_enabled'] ? 1 : 0;

        $data = array(
            'action'        => $item['action'],
            'filter_expr'   => $item['filter_expr'],
            'is_enabled'    => $item['is_enabled'],
            'name'          => $item['name'],
            'note'          => $item['note'] ?? '',
        );

        $response = Request_Arvan::patch( self::$endpoint . 'rules/' . $item['id'] . '/', json_encode($data));

        self::return_based_on_status_code($response->status_code, __('Successfully updated the rule.', 'arvancloud-cdn'));

		wp_die();
        
    }

    public static function ajax_delete_rules() {

        self::checking_nonce();
        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );

        $rule_id = $item['id'];

        $response = Request_Arvan::delete( self::$endpoint . 'rules/' . $rule_id . '/' );

        self::return_based_on_status_code($response->status_code, __('Successfully removed the rule.', 'arvancloud-cdn'));

		wp_die();
    }

    public static function change_rule_priority() {
        self::checking_nonce();

        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );
        if ($item['mode'] == 'before') {
            $data = array(
                'rule_id' => $item['e1'],
                'before_rule_id' => $item['e2']
            );
        } else if ($item['mode'] == 'after') {
            $data = array(
                'rule_id' => $item['e1'],
                'after_rule_id' => $item['e2']
            );
        }

        $endpoint = 'domains/' . Helper::get_site_domain() . self::$endpoint . 'actions/reprioritize/';

        $response = Request_Arvan::post( $endpoint, json_encode($data));

        self::return_based_on_status_code($response->status_code, __('Successfully updated priority of the rule', 'arvancloud-cdn'));

		wp_die();
    }

	/**
	 * Save options with ajax req
	 *
	 * @return void
	 */
	public static function ajax_saving_options() {

		self::checking_nonce();

        self::checking_allowed_options();

        
		// request is correct
        $item = array_map( 'sanitize_text_field', $_GET['option_item'] );
        $item = array_map( __CLASS__ . '::maybe_convert_string_to_bool', $item );
        $data = array(
            $item['name'] => ($item['status']) ? 'allow' : 'deny',
        );
        
		$response = Request_Arvan::patch( self::$endpoint, json_encode($data));

        self::return_based_on_status_code($response->status_code);

		wp_die();
	}


	private static function is_option_allowed( $option ): bool
    {
		$allowed_options = [
			'default_action',
		];

		return in_array($option, $allowed_options);
	}

    private static function is_value_allowed( $value ): bool
    {
		$allowed_values = [
			'allow',
            'deny'
		];

		return in_array($value, $allowed_values);
	}

    /**
	 * Retrieve firewall config
	 *
	 * @return void
	 */
	public static function get_firewall_config() {
		$domain = Helper::get_site_domain();
        return Request_Arvan::get("domains/$domain/firewall/settings");
	}

    public function set_props() {
        if(!$this->data)
            return;
        $this->default_rule = $this->data['default_action'];
        $this->verify_sni   = $this->data['verify_sni'];
        $this->rules        = $this->get_rules();

    }


    public function get_rules(){
        $domain = Helper::get_site_domain();
        $rules = Request_Arvan::get("domains/$domain/firewall/rules");
        if(isset($rules['status_code']))
            unset($rules['status_code']);
        return $rules;
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

    private static function checking_nonce() {
        if ( ! check_ajax_referer( 'ar-cdn-options-nonce', 'security', false ) ) {
            
            wp_send_json_error( __('Invalid security token sent.', 'arvancloud-cdn' ), 403 );
            wp_die();

        }
    }

    private static function checking_allowed_options() {
        if (! isset($_GET['option_item']['name']) || !self::is_option_allowed( sanitize_text_field($_GET['option_item']['name'])) ) {

			wp_send_json_error( __('Invalid item sent.', 'arvancloud-cdn' ), 403 );
			wp_die();

		}
    }

    /**
     * Return success if status code is 200 or 201
     *
     * @param $http_code
     * @param $message
     * @return void
     */
    private static function return_based_on_status_code( $http_code, $message = '' ) {
        if ($http_code == 500) {
			wp_send_json_error( __( 'ArvanCloud is not responding right now. please try again later.', 'arvancloud-cdn' ), 403 );
		} else if ($http_code == 401) {
			wp_send_json_error( __( 'ArvanCloud API key is invalid. Please try again.', 'arvancloud-cdn' ), 403 );
			Api_Key::reset_api_key();
		} else if ($http_code == 200) {
            wp_send_json_success( $message );
		} else if ($http_code == 201) {
            wp_send_json_success( $message );
        }
    }

}