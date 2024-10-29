<?php
namespace WP_Arvan\CDN;

use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\Helper;


/**
 * This class add domain to cloud dns if it doesn't exists already
 */
class Auto_Add_Domain{


    /**
     * Store current domain
     * structure is example.com
     * @var string
     */
    private $current_website_domain;
    /**
     * Store domains list from cloud
     * array(
     *    'example1.com',
     *    'example2.com',
     * )
     * @var array
     */
    private $cdn_domain_list;


    /**
     *  Get current website domain
     *  Initialize empty domain list array
     */
    public function __construct(){

        $this->current_website_domain = Helper::get_site_domain();

        $this->cdn_domain_list = [];

    }


    /**
     * Send a Request to cloud to add new domain
     * End point is   domains/dns-service
     * Request type is POST
     * @return void
     */
    public function request_from_arvan_to_add_domain(){

        if ( false != get_transient('currently_requested_for_automatic_add_domain') )
            return;

        if ( !Helper::is_domain_belongs_to_api() )
        {
            Helper::show_admin_notice('This domain is not belongs to api key, It will be added automatically.');

        }

        if( 'false' == $this->check_domain_exists_in_arvan() )
        {
            $data = array(
                'domain'=>$this->current_website_domain,
                'domain_type'=>'full'
            );
            try {
                Request_Arvan::post('domains/dns-service', json_encode($data));
            }catch (Exception $e){

                helper::show_admin_notice(__('Error retrieve data from cloud' ,'arvancloud-cdn'));
                return;
            }

        }
        set_transient('currently_requested_for_automatic_add_domain', 'yes', 5*60);

    }


    /**
     * Check if current domain is exists in domains that fetched from cloud
     *
     * @return bool
     */
    private function check_domain_exists_in_arvan(){

        $existing_domains = $this->get_domain_list_from_arvan();

        if( !is_array($existing_domains))
            return 'false';

        if( in_array( $this->current_website_domain, $existing_domains ) )
            return 'true';

        return 'false';

    }


    /**
     * Fetch domain list from cloud
     * Endpoint is domains
     * extract items from response and fill in array
     * return filled array
     * @return array|void
     */
    private function get_domain_list_from_arvan(){

        try {
            $response = json_decode(wp_remote_retrieve_body(Request_Arvan::get('domains', false)));
        }catch (Exception $e){

            helper::show_admin_notice(__('Error retrieve data from cloud','arvancloud-cdn'));
            return;
        }
        if( !isset($response->data) )
            return;

        if( !is_array($response->data))
            return;

        foreach($response->data as $item)
            $this->cdn_domain_list[] = $item->name;

        return $this->cdn_domain_list;

    }

}
