<?php

namespace WP_Arvan\CDN;
use WP_Arvan\API\HTTP\Request_Arvan;
use WP_Arvan\API\Key\Api_Key;

class Purge_Cache {
    /**
     * Remove cache if automatic-cleaning is enabled
     *
     * @param $post_id
     * @return void
     */
    public static function purge_arvan_cache_onsave($post_id)
	{

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post') || empty($post_id)) {
			return;
        }

		if (get_option( 'arvan-cloud-cdn-automatic-cleaning' )) {
			self::delete_arvan_cache();
		}

	}



	/**
	 * handler purge ArvanCloud cache request
	 *
	 * @return void
	 */
	public static function purge_arvan_cache() {

		if( ! isset( $_GET[ 'purge_arvan_cache' ] ) || ! isset( $_GET[ '_wpnonce' ] ) ) {
			return false;
		} else if ($_GET[ 'purge_arvan_cache' ] != 1 || !wp_verify_nonce($_GET[ '_wpnonce' ], 'arvan_purge_cache')) {
			add_action( 'admin_notices', function () {
				echo wp_kses_post('<div class="notice notice-error is-dismissible">
						<p>'. esc_html__( 'There was a problem. please try again later.', 'arvancloud-cdn' ) .'</p>
					</div>');
			} );
			return false;
		}

		self::delete_arvan_cache();
		return;

	}



	/**
	 * Send purge ArvanCloud cache request
	 *
	 * @return void
	 */
	private static function delete_arvan_cache()
    {

		$url = '/caching?purge=all';
		$response = Request_Arvan::delete( $url );
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
						<p>'. esc_html__( "Purging CDN cache request sent successfully", 'arvancloud-cdn' ) .'</p>
					</div>');
			} ); 
		}

		return $http_code;

	}

}